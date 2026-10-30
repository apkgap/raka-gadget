<?php

if (! defined('ABSPATH')) {
    exit;
}

class WSR_POS
{
    public function init()
    {
        add_action('admin_menu', array($this, 'register_menu_page'));
        add_action('rest_api_init', array($this, 'register_pos_api'));
    }

    public function register_menu_page()
    {
        add_submenu_page(
            'wsr-dashboard',
            'Mini POS / เพิ่มออเดอร์',
            'เพิ่มออเดอร์ (POS)',
            'manage_options',
            'wsr-pos',
            array($this, 'render_pos_page')
        );
    }

    public function render_pos_page()
    {
        ?>
        <div class="wrap wsr-pos-wrap" style="max-width: 1400px;">
            <div id="wsr-pos-root">
                <p>Loading POS System...</p>
            </div>
        </div>
        <style>
            /* Hide WP default notices in POS view to keep it clean */
            .wsr-pos-wrap .notice { display: none !important; }
        </style>
        <?php
    }

    public function register_pos_api()
    {
        register_rest_route('wsr/v1', '/pos/search-customers', array(
            'methods' => 'GET',
            'callback' => array($this, 'search_customers'),
            'permission_callback' => function () { return current_user_can('edit_posts'); }
        ));

        register_rest_route('wsr/v1', '/pos/search-products', array(
            'methods' => 'GET',
            'callback' => array($this, 'search_products'),
            'permission_callback' => function () { return current_user_can('edit_posts'); }
        ));

        register_rest_route('wsr/v1', '/pos/submit-order', array(
            'methods' => 'POST',
            'callback' => array($this, 'submit_order'),
            'permission_callback' => function () { return current_user_can('edit_posts'); }
        ));
    }

    public function search_customers($request)
    {
        $keyword = sanitize_text_field($request->get_param('q'));
        
        $args = array(
            'post_type' => 'customer',
            'posts_per_page' => -1,
            'post_status' => 'publish',
        );

        if (!empty($keyword)) {
            // Search by title or metadata
            $args['meta_query'] = array(
                'relation' => 'OR',
                array(
                    'key' => 'wsr_customer_phone',
                    'value' => $keyword,
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => 'wsr_customer_email',
                    'value' => $keyword,
                    'compare' => 'LIKE'
                )
            );
            
            // To also search title, we have to do a somewhat complex query, 
            // but WP Core search works primarily on title/content. We'll use a hack or just rely on standard WP search plus filter
            // Actually, querying both is slow. Let's just do a manual WP_Query with search
            $args['_s'] = $keyword; // We will use a filter to include meta search
        }

        add_filter('posts_search', array($this, 'custom_search_query'), 10, 2);
        $query = new WP_Query($args);
        remove_filter('posts_search', array($this, 'custom_search_query'), 10);

        $results = array();
        foreach ($query->posts as $post) {
            $phone = get_post_meta($post->ID, 'wsr_customer_phone', true);
            $results[] = array(
                'id' => $post->ID,
                'name' => $post->post_title,
                'phone' => $phone ? $phone : '-'
            );
        }
        
        // Return dummy customer if none matched but keyword looks like a new customer? 
        // POS UI can handle "Create new customer"

        return rest_ensure_response($results);
    }

    public function custom_search_query($search, $wp_query) {
        global $wpdb;
        $q = $wp_query->query_vars;
        if (isset($q['_s']) && !empty($q['_s'])) {
            $keyword = esc_sql($wpdb->esc_like($q['_s']));
            $search .= " OR EXISTS (
                SELECT 1 FROM {$wpdb->postmeta}
                WHERE post_id = {$wpdb->posts}.ID
                AND (meta_key = 'wsr_customer_phone' AND meta_value LIKE '%{$keyword}%')
            )";
        }
        return $search;
    }

    public function search_products($request)
    {
        $keyword = sanitize_text_field($request->get_param('q'));
        
        $args = array(
            'post_type' => 'product',
            'posts_per_page' => -1,
            'post_status' => 'publish',
        );

        if (!empty($keyword)) {
            $args['s'] = $keyword;
        }

        $include = array_filter(array_map('intval', explode(',', (string) $request->get_param('include'))));
        if (!empty($include)) {
            $args['post__in'] = $include;
            $args['post_status'] = 'any';
            $args['post_type'] = array('product', 'product_variation');
            $args['orderby'] = 'post__in';
        }

        $query = new WP_Query($args);
        $results = array();

        $push = function ($product, $name) use (&$results) {
            $id = $product->get_id();
            $price = $product->get_price();
            if ($price === '' || (float) $price <= 0) {
                $price = $product->get_regular_price();
            }
            if ($price === '' || (float) $price <= 0) {
                $price = get_post_meta($id, 'wsr_default_price', true);
            }
            $cost = get_post_meta($id, 'wsr_default_cost', true);
            if ($cost === '') {
                $cost = get_post_meta($id, '_cost', true);
            }
            if ($cost === '' && $product->is_type('variation')) {
                $cost = get_post_meta($product->get_parent_id(), 'wsr_default_cost', true);
            }
            $lazada_order = get_post_meta($id, 'wsr_lazada_order', true);
            $stock = $product->managing_stock() ? $product->get_stock_quantity() : null;

            $results[] = array(
                'id' => $id,
                'name' => $name,
                'price' => (float) $price,
                'cost' => (float) $cost,
                'lazada_order' => is_string($lazada_order) ? trim($lazada_order) : '',
                'stock' => $stock !== null ? (int) $stock : null,
            );
        };

        foreach ($query->posts as $post) {
            $product = function_exists('wc_get_product') ? wc_get_product($post->ID) : null;
            if (! $product) {
                continue;
            }
            if ($product->is_type('variable')) {
                foreach ($product->get_children() as $vid) {
                    $variation = wc_get_product($vid);
                    if ($variation && $variation->get_status() === 'publish') {
                        $push($variation, wp_strip_all_tags($variation->get_name()));
                    }
                }
            } else {
                $push($product, wp_strip_all_tags($product->get_name()));
            }
        }
        return rest_ensure_response($results);
    }

    public function submit_order($request)
    {
        $params = $request->get_json_params();
        
        $customer_id = isset($params['customer_id']) ? (int)$params['customer_id'] : 0;
        $customer_name = isset($params['customer_name']) ? sanitize_text_field($params['customer_name']) : '';
        $customer_phone = isset($params['customer_phone']) ? sanitize_text_field($params['customer_phone']) : '';
        $products = isset($params['products']) ? (array)$params['products'] : array();
        $shipping_paid = isset($params['shipping_paid']) ? (float)$params['shipping_paid'] : 0;
        $shipping_actual = isset($params['shipping_actual']) ? (float)$params['shipping_actual'] : 0;
        $status = isset($params['status']) ? sanitize_text_field($params['status']) : 'paid';

        $sale_date = isset($params['sale_date']) ? sanitize_text_field($params['sale_date']) : '';
        $delivery_method = isset($params['delivery_method']) ? sanitize_text_field($params['delivery_method']) : '';
        $lazada_order = isset($params['lazada_order']) ? sanitize_text_field($params['lazada_order']) : '';
        $bargained_price = isset($params['bargained_price']) ? (float)$params['bargained_price'] : null;
        $previous_bargained_price = isset($params['previous_bargained_price']) ? (float)$params['previous_bargained_price'] : null;
        $notes = isset($params['notes']) ? sanitize_textarea_field($params['notes']) : '';

        $observed_social_url = isset($params['observed_social_url']) ? esc_url_raw($params['observed_social_url']) : '';
        $observed_avatar_url = isset($params['observed_avatar_url']) ? esc_url_raw($params['observed_avatar_url']) : '';
        $observed_avatar_id = isset($params['observed_avatar_id']) ? (int)$params['observed_avatar_id'] : 0;

        // 1. Create Customer if new
        if ($customer_id === 0 && !empty($customer_name)) {
            $cid = wp_insert_post(array(
                'post_type' => 'customer',
                'post_title' => $customer_name,
                'post_status' => 'publish'
            ));
            if (!is_wp_error($cid)) {
                $customer_id = $cid;
                update_post_meta($customer_id, 'wsr_customer_phone', $customer_phone);
            }
        }

        if (empty($products)) {
            return new WP_Error('no_products', 'ไม่พบสินค้าในออเดอร์', array('status' => 400));
        }

        // Calculate totals and format product list
        $total_price = 0;
        $total_cost = 0;
        $product_ids = array();

        $lazada_orders = array();

        foreach ($products as $p) {
            $pid = (int)$p['id'];
            $qty = (int)$p['qty'];
            if ($pid > 0 && $qty > 0) {
                // To support quantity, we duplicate the ID in the array $qty times, 
                // since the current WSR plugin supports an array of product IDs.
                for ($i = 0; $i < $qty; $i++) {
                    $product_ids[] = $pid;
                }
                $total_price += (float)$p['price'] * $qty;
                $total_cost += (float)$p['cost'] * $qty;

                if (isset($p['lazada_order'])) {
                    $order = is_string($p['lazada_order']) ? trim($p['lazada_order']) : '';
                    if ($order !== '') {
                        $lazada_orders[] = $order;
                    }
                }
            }
        }

        $lazada_orders = array_values(array_unique($lazada_orders));

        // 2. Create Sales Record
        $post_id = wp_insert_post(array(
            'post_type'   => 'sales_record',
            'post_title'  => 'Order ' . current_time('Ymd-His'), // Temporary title, will be updated to display correctly 
            'post_status' => 'publish',
        ));

        if (is_wp_error($post_id)) {
            return $post_id;
        }

        // Update meta
        if ($sale_date === '') {
            $sale_date = current_time('Y-m-d');
        }
        update_post_meta($post_id, 'wsr_sale_date', $sale_date);
        update_post_meta($post_id, 'wsr_customer_id', $customer_id);
        // Keep legacy key for compatibility, but prefer wsr_product_ids (used by sales_record edit screen)
        update_post_meta($post_id, 'wsr_products', $product_ids);
        update_post_meta($post_id, 'wsr_product_ids', $product_ids);
        update_post_meta($post_id, 'wsr_selling_price', $total_price);
        update_post_meta($post_id, 'wsr_cost_price', $total_cost);
        if ($bargained_price === null) {
            $bargained_price = $total_price;
        }
        update_post_meta($post_id, 'wsr_bargained_price', (float)$bargained_price);

        if ($previous_bargained_price !== null) {
            update_post_meta($post_id, 'wsr_prev_bargain_price', (float)$previous_bargained_price);
        }
        update_post_meta($post_id, 'wsr_shipping_paid', $shipping_paid);
        update_post_meta($post_id, 'wsr_shipping_actual', $shipping_actual);
        update_post_meta($post_id, 'wsr_status', $status);
        update_post_meta($post_id, 'wsr_delivery_method', $delivery_method);

        if ($lazada_order === '' && ! empty($lazada_orders)) {
            $lazada_order = implode(', ', $lazada_orders);
        }
        update_post_meta($post_id, 'wsr_lazada_order', $lazada_order);
        update_post_meta($post_id, 'wsr_notes', $notes);
        update_post_meta($post_id, 'wsr_record_source', 'pos');

        if ($observed_social_url !== '') {
            update_post_meta($post_id, 'wsr_observed_social_url', $observed_social_url);
        }
        if ($observed_avatar_url !== '') {
            update_post_meta($post_id, 'wsr_observed_avatar_url', $observed_avatar_url);
        }
        if ($observed_avatar_id > 0) {
            update_post_meta($post_id, 'wsr_observed_avatar_id', $observed_avatar_id);
        }

        // Note: The total amounts might be overridden by JS calculation but we passed it explicitly just in case.

        // Trigger notification action now that meta is saved
        do_action('save_post_sales_record', $post_id, get_post($post_id), false);

        return rest_ensure_response(array(
            'success' => true,
            'order_id' => $post_id,
            'message' => 'บันทึกออเดอร์สำเร็จ'
        ));
    }
}
