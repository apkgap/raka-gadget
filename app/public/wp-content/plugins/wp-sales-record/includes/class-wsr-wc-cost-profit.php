<?php
/**
 * WooCommerce Cost of Goods & Profit Integration with WSR
 *
 * @package WP_Sales_Record
 */

if (! defined('ABSPATH')) {
    exit;
}

class WSR_WooCommerce_Cost_Profit
{
    public function init()
    {
        // 1. Add Cost Price Field to Simple Products
        add_action('woocommerce_product_options_pricing', array($this, 'add_cost_price_field_simple'));
        add_action('woocommerce_admin_process_product_object', array($this, 'save_cost_price_field_simple'));

        // 2. Add Cost Price Field to Variable Product Variations
        add_action('woocommerce_variation_options_pricing', array($this, 'add_cost_price_field_variation'), 10, 3);
        add_action('woocommerce_save_product_variation', array($this, 'save_cost_price_field_variation'), 10, 2);

        // Keep wsr_default_price in sync with WooCommerce product price
        add_action('woocommerce_process_product_meta', array($this, 'sync_wc_price_to_wsr_default_price'), 20, 1);
        add_action('woocommerce_save_product_variation', array($this, 'sync_wc_variation_price_to_wsr_default_price'), 20, 2);

        // 3. Show Cost & Profit in WooCommerce Order Item Row (Admin Order Screen)
        add_action('woocommerce_admin_order_item_headers', array($this, 'order_item_headers'));
        add_action('woocommerce_admin_order_item_values', array($this, 'order_item_values'), 10, 3);

        // 4. Save Cost Snapshot on Order Creation / Item addition
        add_action('woocommerce_checkout_create_order_line_item', array($this, 'snapshot_line_item_cost'), 10, 4);

        // 5. Add Profit Column to Admin Orders List (HPOS & Classic compatible)
        add_filter('manage_woocommerce_page_wc-orders_columns', array($this, 'add_order_profit_column'));
        add_action('manage_woocommerce_page_wc-orders_custom_column', array($this, 'render_order_profit_column'), 10, 2);

        add_filter('manage_edit-shop_order_columns', array($this, 'add_order_profit_column'));
        add_action('manage_shop_order_posts_custom_column', array($this, 'render_order_profit_column_classic'), 10, 2);

        // 6. Auto Sync Completed / Processing WooCommerce Order to Sales Record
        add_action('woocommerce_order_status_processing', array($this, 'sync_order_to_sales_record'), 10, 1);
        add_action('woocommerce_order_status_completed', array($this, 'sync_order_to_sales_record'), 10, 1);
        add_action('woocommerce_order_status_refunded', array($this, 'sync_order_status_refunded'), 10, 1);
        add_action('woocommerce_order_status_cancelled', array($this, 'sync_order_status_refunded'), 10, 1);

        // 7. Add Manual Sync Button to Admin Order Action
        add_action('woocommerce_order_actions', array($this, 'add_order_sync_action'));
        add_action('woocommerce_order_action_wsr_sync_to_sales_record', array($this, 'process_manual_order_sync'));
    }

    /**
     * Add Cost Price Field to Simple Product Edit Page
     */
    public function add_cost_price_field_simple()
    {
        global $post;
        $product_id = $post ? $post->ID : 0;
        $cost = $product_id ? get_post_meta($product_id, 'wsr_default_cost', true) : '';

        echo '<div class="options_group pricing show_if_simple">';
        woocommerce_wp_text_input(array(
            'id'          => 'wsr_default_cost',
            'label'       => 'ราคาทุน (Cost Price ฿)',
            'placeholder' => '0.00',
            'desc_tip'    => true,
            'description' => 'ระบุต้นทุนสินค้าชิ้นนี้ เพื่อใช้คำนวณกำไรสุทธิในคำสั่งซื้อและหน้ารายงาน',
            'type'        => 'text',
            'data_type'   => 'price',
            'value'       => $cost !== '' ? wc_format_localized_price($cost) : '',
        ));
        echo '</div>';
    }

    /**
     * Save Cost Price for Simple Product
     *
     * @param WC_Product $product
     */
    public function save_cost_price_field_simple($product)
    {
        if (isset($_POST['wsr_default_cost'])) {
            $cost = wc_clean(wp_unslash($_POST['wsr_default_cost']));
            $cost = $cost !== '' ? (float) wc_format_decimal($cost) : '';
            $product->update_meta_data('wsr_default_cost', $cost);
            $product->update_meta_data('_cost', $cost); // Also support standard _cost key
        }
    }

    /**
     * Add Cost Price Field to Variation Edit Row
     *
     * @param int     $loop
     * @param array   $variation_data
     * @param WP_Post $variation
     */
    public function add_cost_price_field_variation($loop, $variation_data, $variation)
    {
        $cost = get_post_meta($variation->ID, 'wsr_default_cost', true);
        if ($cost === '') {
            $cost = get_post_meta($variation->post_parent, 'wsr_default_cost', true);
        }

        woocommerce_wp_text_input(array(
            'id'            => "wsr_default_cost_{$loop}",
            'name'          => "wsr_variable_cost[{$loop}]",
            'value'         => $cost !== '' ? wc_format_localized_price($cost) : '',
            'label'         => 'ราคาทุน (Cost ฿)',
            'desc_tip'      => true,
            'description'   => 'ระบุต้นทุนของตัวเลือกสินค้านี้',
            'data_type'     => 'price',
            'wrapper_class' => 'form-row form-row-first',
        ));
    }

    /**
     * Save Cost Price for Product Variation
     *
     * @param int $variation_id
     * @param int $i
     */
    public function save_cost_price_field_variation($variation_id, $i)
    {
        if (isset($_POST['wsr_variable_cost'][$i])) {
            $cost = wc_clean(wp_unslash($_POST['wsr_variable_cost'][$i]));
            $cost = $cost !== '' ? (float) wc_format_decimal($cost) : '';
            update_post_meta($variation_id, 'wsr_default_cost', $cost);
            update_post_meta($variation_id, '_cost', $cost);
        }
    }

    /**
     * Snapshot cost into Order Item Meta at checkout
     *
     * @param WC_Order_Item_Product $item
     * @param string                $cart_item_key
     * @param array                 $values
     * @param WC_Order              $order
     */
    public function snapshot_line_item_cost($item, $cart_item_key, $values, $order)
    {
        $product = $item->get_product();
        if (! $product) {
            return;
        }

        $cost = $product->get_meta('wsr_default_cost');
        if ($cost === '' && $product->is_type('variation')) {
            $parent = wc_get_product($product->get_parent_id());
            if ($parent) {
                $cost = $parent->get_meta('wsr_default_cost');
            }
        }

        if ($cost !== '') {
            $item->add_meta_data('_line_item_cost', (float) $cost, true);
        }
    }

    /**
     * Get line item unit cost helper
     *
     * @param WC_Order_Item_Product $item
     * @return float
     */
    public function get_item_unit_cost($item)
    {
        // 1. Check if snapshot cost exists in line item meta
        $snap = $item->get_meta('_line_item_cost');
        if ($snap !== '' && $snap !== false) {
            return (float) $snap;
        }

        // 2. Fallback to product current cost
        $product = $item->get_product();
        if ($product) {
            $cost = $product->get_meta('wsr_default_cost');
            if ($cost === '' && $product->is_type('variation')) {
                $parent = wc_get_product($product->get_parent_id());
                if ($parent) {
                    $cost = $parent->get_meta('wsr_default_cost');
                }
            }
            if ($cost !== '') {
                return (float) $cost;
            }
        }

        return 0.0;
    }

    /**
     * Order Item Table Header (Inside Order Detail Page)
     */
    public function order_item_headers()
    {
        echo '<th class="item_cost sortable" style="text-align:right;width:120px;">ต้นทุน / ชิ้น</th>';
        echo '<th class="line_profit sortable" style="text-align:right;width:130px;">กำไรสุทธิ</th>';
    }

    /**
     * Order Item Table Values (Inside Order Detail Page)
     *
     * @param WC_Product|null       $product
     * @param WC_Order_Item_Product $item
     * @param int                   $item_id
     */
    public function order_item_values($product, $item, $item_id)
    {
        if (! is_a($item, 'WC_Order_Item_Product')) {
            echo '<td></td><td></td>';
            return;
        }

        $qty       = (int) $item->get_quantity();
        $subtotal  = (float) $item->get_subtotal();
        $unit_cost = $this->get_item_unit_cost($item);
        $total_cost = $unit_cost * $qty;
        $profit     = $subtotal - $total_cost;
        $margin     = $subtotal > 0 ? ($profit / $subtotal) * 100 : 0;

        $profit_color = $profit >= 0 ? '#16a34a' : '#dc2626';
        $profit_sign  = $profit >= 0 ? '+' : '';

        echo '<td class="item_cost" style="text-align:right;font-family:monospace;font-size:12px;">';
        if ($unit_cost > 0) {
            echo '฿' . number_format($unit_cost, 2);
            if ($qty > 1) {
                echo '<br><small style="color:#64748b;">(รวม ฿' . number_format($total_cost, 2) . ')</small>';
            }
        } else {
            echo '<span style="color:#94a3b8;">ไม่ระบุ</span>';
        }
        echo '</td>';

        echo '<td class="line_profit" style="text-align:right;font-family:monospace;font-size:12px;font-weight:600;color:' . esc_attr($profit_color) . ';">';
        if ($unit_cost > 0) {
            echo esc_html($profit_sign . '฿' . number_format($profit, 2));
            echo '<br><small style="font-weight:normal;opacity:0.85;">(' . number_format($margin, 1) . '%)</small>';
        } else {
            echo '<span style="color:#94a3b8;font-weight:normal;">-</span>';
        }
        echo '</td>';
    }

    /**
     * Calculate Total Order Profit & Cost
     *
     * @param WC_Order $order
     * @return array
     */
    public function calculate_order_profit($order)
    {
        $total_cost = 0.0;
        $has_cost   = false;

        foreach ($order->get_items() as $item) {
            if (! is_a($item, 'WC_Order_Item_Product')) {
                continue;
            }
            $unit_cost = $this->get_item_unit_cost($item);
            if ($unit_cost > 0) {
                $has_cost = true;
            }
            $total_cost += ($unit_cost * (int) $item->get_quantity());
        }

        $total_revenue   = (float) $order->get_total();
        $shipping_total  = (float) $order->get_shipping_total();
        // Base profit = Product revenue - Product cost
        $items_subtotal  = (float) $order->get_subtotal();
        $profit          = $items_subtotal - $total_cost;
        $margin          = $items_subtotal > 0 ? ($profit / $items_subtotal) * 100 : 0;

        return array(
            'has_cost'       => $has_cost,
            'total_cost'     => $total_cost,
            'items_subtotal' => $items_subtotal,
            'profit'         => $profit,
            'margin'         => $margin,
        );
    }

    /**
     * Add Profit Column to Orders List Table
     */
    public function add_order_profit_column($columns)
    {
        $new_cols = array();
        foreach ($columns as $k => $v) {
            $new_cols[$k] = $v;
            if ($k === 'order_total') {
                $new_cols['order_profit'] = 'กำไรสุทธิ (Profit)';
            }
        }
        if (! isset($new_cols['order_profit'])) {
            $new_cols['order_profit'] = 'กำไรสุทธิ (Profit)';
        }
        return $new_cols;
    }

    /**
     * Render Order Profit Column (HPOS Compatible)
     */
    public function render_order_profit_column($column, $order)
    {
        if ($column !== 'order_profit') {
            return;
        }

        if (! is_a($order, 'WC_Order')) {
            $order = wc_get_order($order);
        }
        if (! $order) {
            return;
        }

        $res = $this->calculate_order_profit($order);
        if (! $res['has_cost']) {
            echo '<span style="color:#94a3b8;font-size:12px;">ไม่ได้ระบุทุน</span>';
            return;
        }

        $color = $res['profit'] >= 0 ? '#16a34a' : '#dc2626';
        $sign  = $res['profit'] >= 0 ? '+' : '';

        echo '<div style="font-family:monospace;font-size:12px;">';
        echo '<strong style="color:' . esc_attr($color) . ';">' . esc_html($sign . '฿' . number_format($res['profit'], 2)) . '</strong>';
        echo '<br><small style="color:#64748b;">(ทุน ฿' . number_format($res['total_cost'], 2) . ' | ' . number_format($res['margin'], 1) . '%)</small>';
        echo '</div>';
    }

    /**
     * Render Order Profit Column (Classic Posts Table fallback)
     */
    public function render_order_profit_column_classic($column, $post_id)
    {
        if ($column === 'order_profit') {
            $order = wc_get_order($post_id);
            if ($order) {
                $this->render_order_profit_column('order_profit', $order);
            }
        }
    }

    /**
     * Add manual order sync action in order detail dropdown
     */
    public function add_order_sync_action($actions)
    {
        $actions['wsr_sync_to_sales_record'] = 'ซิงค์ออเดอร์นี้ไปยัง Sales Record';
        return $actions;
    }

    /**
     * Process manual order sync
     */
    public function process_manual_order_sync($order)
    {
        $this->sync_order_to_sales_record($order->get_id());
        $order->add_order_note('ซิงค์ข้อมูลไปยัง Sales Record สำเร็จเรียบร้อย');
    }

    /**
     * Auto Sync WooCommerce Order into Sales Record (CPT)
     *
     * @param int $order_id
     */
    public function sync_order_to_sales_record($order_id)
    {
        $order = wc_get_order($order_id);
        if (! $order) {
            return;
        }

        $order_number = $order->get_order_number();
        $sr_title     = 'WC-' . $order_number;

        // 1. Resolve or Create Customer in WSR
        $billing_first = $order->get_billing_first_name();
        $billing_last  = $order->get_billing_last_name();
        $cust_name     = trim($billing_first . ' ' . $billing_last);
        if (empty($cust_name)) {
            $cust_name = 'ลูกค้า Order #' . $order_number;
        }

        $cust_phone = $order->get_billing_phone();
        $cust_email = $order->get_billing_email();

        $customer_id = 0;
        $matched_cust = get_page_by_title($cust_name, OBJECT, 'customer');
        if ($matched_cust) {
            $customer_id = $matched_cust->ID;
        } else {
            $new_c = wp_insert_post(array(
                'post_title'  => $cust_name,
                'post_type'   => 'customer',
                'post_status' => 'publish',
            ));
            if (! is_wp_error($new_c)) {
                $customer_id = $new_c;
                update_post_meta($customer_id, 'wsr_social_name', $cust_name);
                update_post_meta($customer_id, 'wsr_sales_channel', 'woocommerce');
                if ($cust_phone) {
                    update_post_meta($customer_id, 'wsr_phone', $cust_phone);
                }
                if ($cust_email) {
                    update_post_meta($customer_id, 'wsr_email', $cust_email);
                }
            }
        }

        // 2. Resolve Product IDs and Costs
        $product_ids = array();
        $total_cost  = 0.0;
        $subtotal    = (float) $order->get_subtotal();
        $total       = (float) $order->get_total();
        $shipping    = (float) $order->get_shipping_total();

        foreach ($order->get_items() as $item) {
            if (! is_a($item, 'WC_Order_Item_Product')) {
                continue;
            }
            $product_id = $item->get_product_id();
            if ($product_id) {
                $product_ids[] = $product_id;
            }
            $unit_cost = $this->get_item_unit_cost($item);
            $total_cost += ($unit_cost * (int) $item->get_quantity());
        }

        $date_created = $order->get_date_created();
        $sale_date    = $date_created ? $date_created->date('Y-m-d') : current_time('Y-m-d');

        // Status mapping
        $wc_status = $order->get_status();
        $sr_status = 'paid';
        if ($wc_status === 'completed') {
            $sr_status = 'shipped';
        } elseif ($wc_status === 'pending' || $wc_status === 'on-hold') {
            $sr_status = 'pending';
        } elseif ($wc_status === 'refunded' || $wc_status === 'cancelled') {
            $sr_status = 'refunded';
        }

        // 3. Find or Create Sales Record
        $existing = get_page_by_title($sr_title, OBJECT, 'sales_record');
        if ($existing) {
            $sr_id = $existing->ID;
            wp_update_post(array(
                'ID'        => $sr_id,
                'post_date' => $date_created ? $date_created->date('Y-m-d H:i:s') : current_time('mysql'),
            ));
        } else {
            $sr_id = wp_insert_post(array(
                'post_title'  => $sr_title,
                'post_type'   => 'sales_record',
                'post_status' => 'publish',
                'post_date'   => $date_created ? $date_created->date('Y-m-d H:i:s') : current_time('mysql'),
            ));
        }

        if ($sr_id && ! is_wp_error($sr_id)) {
            update_post_meta($sr_id, 'wsr_sale_date', $sale_date);
            update_post_meta($sr_id, 'wsr_status', $sr_status);
            update_post_meta($sr_id, 'wsr_customer_id', $customer_id);
            update_post_meta($sr_id, 'wsr_product_ids', array_values(array_unique($product_ids)));
            update_post_meta($sr_id, 'wsr_cost_price', (float) $total_cost);
            update_post_meta($sr_id, 'wsr_selling_price', (float) $subtotal);
            update_post_meta($sr_id, 'wsr_bargained_price', (float) $subtotal);
            update_post_meta($sr_id, 'wsr_shipping_paid', (float) $shipping);
            update_post_meta($sr_id, 'wsr_shipping_actual', (float) $shipping);
            update_post_meta($sr_id, 'wsr_delivery_method', 'ems');
            update_post_meta($sr_id, 'wsr_notes', 'สั่งซื้อผ่านหน้าเว็บไซต์ WooCommerce (Order #' . $order_number . ')');
            update_post_meta($sr_id, 'wsr_wc_order_id', $order_id);
        }
    }

    /**
     * Mark sales record refunded if WC order is refunded/cancelled
     *
     * @param int $order_id
     */
    public function sync_order_status_refunded($order_id)
    {
        $order = wc_get_order($order_id);
        if (! $order) {
            return;
        }

        $sr_title = 'WC-' . $order->get_order_number();
        $matched  = get_page_by_title($sr_title, OBJECT, 'sales_record');
        if ($matched) {
            update_post_meta($matched->ID, 'wsr_status', 'refunded');
        }
    }

    /**
     * Keep wsr_default_price synchronized with WooCommerce price on save
     */
    public function sync_wc_price_to_wsr_default_price($product_id)
    {
        $product = wc_get_product($product_id);
        if (! $product) return;
        $price = $product->get_price();
        if ($price === '') {
            $price = $product->get_regular_price();
        }
        if ($price !== '' && (float) $price > 0) {
            update_post_meta($product_id, 'wsr_default_price', (float) $price);
        }
    }

    public function sync_wc_variation_price_to_wsr_default_price($variation_id, $i)
    {
        $variation = wc_get_product($variation_id);
        if (! $variation) return;
        $price = $variation->get_price();
        if ($price !== '' && (float) $price > 0) {
            update_post_meta($variation_id, 'wsr_default_price', (float) $price);
        }
    }
}

