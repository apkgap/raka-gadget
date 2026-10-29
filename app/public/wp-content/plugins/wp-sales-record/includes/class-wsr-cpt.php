<?php

/**
 * Custom Post Types Registration Class
 */

if (! defined('ABSPATH')) {
    exit;
}

class WSR_CPT
{

    public function init()
    {
        add_action('init', array($this, 'register_customer_cpt'));
        add_action('init', array($this, 'register_product_cpt'));
        add_action('init', array($this, 'register_product_taxonomy'));
        add_action('init', array($this, 'register_sales_record_cpt'));
        add_action('rest_api_init', array($this, 'register_rest_fields'));
        add_action('rest_api_init', array($this, 'register_graph_api_endpoints'));

        add_filter('manage_sales_record_posts_columns', array($this, 'sales_record_admin_columns'));
        add_action('manage_sales_record_posts_custom_column', array($this, 'render_sales_record_admin_column'), 10, 2);
        add_filter('manage_edit-sales_record_sortable_columns', array($this, 'sales_record_sortable_columns'));
        add_action('pre_get_posts', array($this, 'sales_record_admin_sorting'));

        add_filter('views_edit-sales_record', array($this, 'sales_record_admin_tools_view_link'));
        add_filter('views_edit-product', array($this, 'product_admin_tools_view_link'));
        add_action('admin_post_wsr_backfill_sales_record_lazada_order', array($this, 'handle_backfill_sales_record_lazada_order'));
        add_action('admin_post_wsr_migrate_products_taxonomy', array($this, 'handle_migrate_products_taxonomy'));
        add_action('admin_post_wsr_sync_product_prices', array($this, 'handle_sync_product_prices'));
        add_action('admin_notices', array($this, 'sales_record_admin_notices'));
        add_action('admin_notices', array($this, 'product_admin_notices'));
    }

    public function sales_record_admin_tools_view_link($views)
    {
        if (! current_user_can('edit_posts')) {
            return $views;
        }

        $url = wp_nonce_url(
            admin_url('admin-post.php?action=wsr_backfill_sales_record_lazada_order'),
            'wsr_backfill_sales_record_lazada_order'
        );

        $views['wsr_backfill_lazada_order'] = '<a href="' . esc_url($url) . '">Backfill Lazada Order</a>';

        return $views;
    }

    public function product_admin_tools_view_link($views)
    {
        if (! current_user_can('edit_posts')) {
            return $views;
        }

        $migrate_url = wp_nonce_url(
            admin_url('admin-post.php?action=wsr_migrate_products_taxonomy'),
            'wsr_migrate_products_taxonomy'
        );
        $views['wsr_migrate_taxonomy'] = '<a href="' . esc_url($migrate_url) . '">Migrate Product Taxonomy</a>';

        $sync_url = wp_nonce_url(
            admin_url('admin-post.php?action=wsr_sync_product_prices'),
            'wsr_sync_product_prices'
        );
        $views['wsr_sync_prices'] = '<a href="' . esc_url($sync_url) . '" style="color:#f59e0b;font-weight:600;">&rsaquo; Sync Prices → Orders</a>';

        return $views;
    }

    public function handle_backfill_sales_record_lazada_order()
    {
        if (! current_user_can('edit_posts')) {
            wp_die(esc_html__('You are not allowed to perform this action.', 'wp-sales-record'));
        }

        check_admin_referer('wsr_backfill_sales_record_lazada_order');

        $args = array(
            'post_type'      => 'sales_record',
            'post_status'    => 'any',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'meta_query'     => array(
                array(
                    'key'     => 'wsr_lazada_order',
                    'compare' => 'NOT EXISTS',
                ),
            ),
        );

        $query = new WP_Query($args);

        $updated = 0;
        $skipped = 0;

        foreach ($query->posts as $sales_record_id) {
            $product_ids = get_post_meta($sales_record_id, 'wsr_product_ids', true);
            if (! is_array($product_ids) || empty($product_ids)) {
                $skipped++;
                continue;
            }

            $orders = array();
            foreach ($product_ids as $pid) {
                $pid = (int) $pid;
                if (! $pid) {
                    continue;
                }

                $order = get_post_meta($pid, 'wsr_lazada_order', true);
                if (! is_string($order) || trim($order) === '') {
                    continue;
                }

                $orders[] = trim($order);
            }

            $orders = array_values(array_unique($orders));
            if (empty($orders)) {
                $skipped++;
                continue;
            }

            update_post_meta($sales_record_id, 'wsr_lazada_order', implode(', ', $orders));
            $updated++;
        }

        wp_reset_postdata();

        $redirect = add_query_arg(
            array(
                'post_type' => 'sales_record',
                'wsr_backfill_lazada_order' => '1',
                'wsr_updated' => $updated,
                'wsr_skipped' => $skipped,
            ),
            admin_url('edit.php')
        );

        wp_safe_redirect($redirect);
        exit;
    }

    public function sales_record_admin_notices()
    {
        if (! is_admin()) {
            return;
        }

        if (! isset($_GET['post_type']) || $_GET['post_type'] !== 'sales_record') {
            return;
        }

        if (! isset($_GET['wsr_backfill_lazada_order']) || $_GET['wsr_backfill_lazada_order'] !== '1') {
            return;
        }

        $updated = isset($_GET['wsr_updated']) ? (int) $_GET['wsr_updated'] : 0;
        $skipped = isset($_GET['wsr_skipped']) ? (int) $_GET['wsr_skipped'] : 0;

        echo '<div class="notice notice-success is-dismissible"><p>' .
            esc_html(sprintf('Backfill Lazada Order completed. Updated: %d. Skipped: %d.', $updated, $skipped)) .
            '</p></div>';
    }

    public function handle_migrate_products_taxonomy()
    {
        if (! current_user_can('edit_posts')) {
            wp_die(esc_html__('You are not allowed to perform this action.', 'wp-sales-record'));
        }

        check_admin_referer('wsr_migrate_products_taxonomy');

        $taxonomies = array('wsr_product_cat', 'wsr_brand', 'wsr_model');

        // Clear all term relationships from products first
        $product_args = array(
            'post_type' => 'product',
            'posts_per_page' => -1,
            'fields' => 'ids',
        );
        $product_ids = get_posts($product_args);
        foreach ($product_ids as $product_id) {
            foreach ($taxonomies as $taxonomy) {
                wp_set_object_terms($product_id, array(), $taxonomy, false);
            }
        }

        // Then delete all existing terms in those taxonomies
        foreach ($taxonomies as $taxonomy) {
            $terms = get_terms(array(
                'taxonomy' => $taxonomy,
                'hide_empty' => false,
            ));
            if ($terms && ! is_wp_error($terms)) {
                foreach ($terms as $term) {
                    wp_delete_term($term->term_id, $taxonomy);
                }
            }
        }

        $json_file = WSR_PLUGIN_DIR . 'src/products_migration_20260416_115939.json';

        if (! file_exists($json_file)) {
            wp_die(esc_html__('Migration file not found.', 'wp-sales-record'));
        }

        $json_data = file_get_contents($json_file);
        $products = json_decode($json_data, true);

        if (! is_array($products)) {
            wp_die(esc_html__('Invalid JSON data.', 'wp-sales-record'));
        }

        $updated = 0;
        $skipped = 0;
        $errors = array();

        foreach ($products as $product_data) {
            $post_id = isset($product_data['id']) ? (int) $product_data['id'] : 0;

            if ($post_id <= 0 || get_post_type($post_id) !== 'product') {
                $skipped++;
                $errors[] = "Invalid or missing product ID: {$post_id}";
                continue;
            }

            // Update post title
            $title = isset($product_data['title']) ? sanitize_text_field($product_data['title']) : '';
            if ($title) {
                wp_update_post(array(
                    'ID' => $post_id,
                    'post_title' => $title,
                ));
            }

            // Assign category term (use term names to avoid accidental numeric term creation)
            $category = isset($product_data['category']) ? sanitize_text_field($product_data['category']) : '';
            if ($category !== '') {
                $term = term_exists($category, 'wsr_product_cat');
                if (! $term) {
                    $result = wp_insert_term($category, 'wsr_product_cat');
                    if (is_wp_error($result)) {
                        $errors[] = "Failed to create category term: {$category} - " . $result->get_error_message();
                    }
                }
                wp_set_object_terms($post_id, array($category), 'wsr_product_cat', false);
            }

            // Assign brand term
            $brand = isset($product_data['brand']) ? sanitize_text_field($product_data['brand']) : '';
            if ($brand !== '') {
                $term = term_exists($brand, 'wsr_brand');
                if (! $term) {
                    $result = wp_insert_term($brand, 'wsr_brand');
                    if (is_wp_error($result)) {
                        $errors[] = "Failed to create brand term: {$brand} - " . $result->get_error_message();
                    }
                }
                wp_set_object_terms($post_id, array($brand), 'wsr_brand', false);
            }

            // Assign model term
            $model = isset($product_data['model']) ? sanitize_text_field($product_data['model']) : '';
            if ($model !== '') {
                $term = term_exists($model, 'wsr_model');
                if (! $term) {
                    $result = wp_insert_term($model, 'wsr_model');
                    if (is_wp_error($result)) {
                        $errors[] = "Failed to create model term: {$model} - " . $result->get_error_message();
                    }
                }
                wp_set_object_terms($post_id, array($model), 'wsr_model', false);
            }

            $updated++;
        }

        $redirect = add_query_arg(
            array(
                'post_type' => 'product',
                'wsr_migrate_taxonomy' => '1',
                'wsr_updated' => $updated,
                'wsr_skipped' => $skipped,
                'wsr_errors' => count($errors),
            ),
            admin_url('edit.php')
        );

        wp_safe_redirect($redirect);
        exit;
    }

    public function product_admin_notices()
    {
        if (! is_admin()) {
            return;
        }

        if (! isset($_GET['post_type']) || $_GET['post_type'] !== 'product') {
            return;
        }

        // Migrate Taxonomy notice
        if (isset($_GET['wsr_migrate_taxonomy']) && $_GET['wsr_migrate_taxonomy'] === '1') {
            $updated = isset($_GET['wsr_updated']) ? (int) $_GET['wsr_updated'] : 0;
            $skipped = isset($_GET['wsr_skipped']) ? (int) $_GET['wsr_skipped'] : 0;
            $errors  = isset($_GET['wsr_errors'])  ? (int) $_GET['wsr_errors']  : 0;

            $message = sprintf('Product Taxonomy Migration completed. Updated: %d. Skipped: %d.', $updated, $skipped);
            if ($errors > 0) {
                $message .= sprintf(' Errors: %d.', $errors);
            }
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($message) . '</p></div>';
        }

        // Sync Prices notice
        if (isset($_GET['wsr_sync_prices']) && $_GET['wsr_sync_prices'] === '1') {
            $updated_orders = isset($_GET['wsr_orders_updated']) ? (int) $_GET['wsr_orders_updated'] : 0;
            $skipped        = isset($_GET['wsr_skipped'])        ? (int) $_GET['wsr_skipped']        : 0;

            $message = sprintf(
                'Sync Prices → Orders เสร็จแล้ว! อัปเดต %d orders, ข้าม %d orders.',
                $updated_orders,
                $skipped
            );
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($message) . '</p></div>';
        }
    }

    /**
     * Handle Sync Product Prices to Sales Records
     *
     * วน Loop ทุก sales_record ที่มีสินค้า
     * และคำนวณ wsr_cost_price / wsr_selling_price ใหม่
     * จาก wsr_default_cost / wsr_default_price ของ product แต่ละชิ้น
     * ไม่แตะ wsr_bargained_price
     */
    public function handle_sync_product_prices()
    {
        if (! current_user_can('edit_posts')) {
            wp_die(esc_html__('You are not allowed to perform this action.', 'wp-sales-record'));
        }

        check_admin_referer('wsr_sync_product_prices');

        // โหลด default cost/price ของทุก product ไว้ใน cache
        $product_price_cache = array();
        $all_products = get_posts(array(
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids',
        ));
        foreach ($all_products as $pid) {
            $pid = (int) $pid;
            $product_price_cache[$pid] = array(
                'cost'  => (float) get_post_meta($pid, 'wsr_default_cost', true),
                'price' => (float) get_post_meta($pid, 'wsr_default_price', true),
            );
        }

        // ดึง sales records ทั้งหมด
        $records = get_posts(array(
            'post_type'      => 'sales_record',
            'post_status'    => 'any',
            'posts_per_page' => -1,
            'fields'         => 'ids',
        ));

        $updated_orders = 0;
        $skipped        = 0;

        foreach ($records as $record_id) {
            $product_ids = get_post_meta($record_id, 'wsr_product_ids', true);

            if (! is_array($product_ids) || empty($product_ids)) {
                $skipped++;
                continue;
            }

            $total_cost  = 0.0;
            $total_price = 0.0;
            $has_data    = false;

            foreach ($product_ids as $pid) {
                $pid = (int) $pid;
                if (! $pid || ! isset($product_price_cache[$pid])) {
                    continue;
                }
                $total_cost  += $product_price_cache[$pid]['cost'];
                $total_price += $product_price_cache[$pid]['price'];
                $has_data     = true;
            }

            if (! $has_data) {
                $skipped++;
                continue;
            }

            update_post_meta($record_id, 'wsr_cost_price', $total_cost);
            update_post_meta($record_id, 'wsr_selling_price', $total_price);

            $updated_orders++;
        }

        wp_reset_postdata();

        $redirect = add_query_arg(
            array(
                'post_type'          => 'product',
                'wsr_sync_prices'    => '1',
                'wsr_orders_updated' => $updated_orders,
                'wsr_skipped'        => $skipped,
            ),
            admin_url('edit.php')
        );

        wp_safe_redirect($redirect);
        exit;
    }

    public function sales_record_admin_columns($columns)
    {
        $new_columns = array();

        foreach ($columns as $key => $label) {
            $new_columns[$key] = $label;

            if ($key === 'title') {
                $new_columns['wsr_customer'] = 'Customer';
                $new_columns['wsr_sale_date'] = 'Sale Date';
                $new_columns['wsr_lazada_order'] = 'Lazada Order';
                $new_columns['wsr_delivery_method'] = 'สถานะ / รูปแบบการจัดส่ง';
                $new_columns['wsr_selling_price'] = 'Selling Price';
                // $new_columns['wsr_shipping_paid'] = 'Shipping Paid';
            }
        }

        return $new_columns;
    }

    public function render_sales_record_admin_column($column, $post_id)
    {
        if ($column === 'wsr_customer') {
            $customer_id = (int) get_post_meta($post_id, 'wsr_customer_id', true);
            if (! $customer_id) {
                echo esc_html('—');
                return;
            }

            $title = get_the_title($customer_id);
            if (! $title) {
                echo esc_html('—');
                return;
            }

            $edit_link = get_edit_post_link($customer_id);
            if ($edit_link) {
                echo '<a href="' . esc_url($edit_link) . '">' . esc_html($title) . '</a>';
                return;
            }

            echo esc_html($title);
            return;
        }

        if ($column === 'wsr_sale_date') {
            $sale_date = (string) get_post_meta($post_id, 'wsr_sale_date', true);
            echo $sale_date !== '' ? esc_html($sale_date) : esc_html('—');
            return;
        }

        if ($column === 'wsr_lazada_order') {
            $order = (string) get_post_meta($post_id, 'wsr_lazada_order', true);
            $has_order = $order !== '';

            if ($has_order) {
                echo '<span style="color: white; background-color: #10b981; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;">มี</span>';
            } else {
                echo '<span style="color: white; background-color: #9ca3af; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 300;">ไม่มี</span>';
            }

            $product_ids = get_post_meta($post_id, 'wsr_product_ids', true);
            if (is_array($product_ids) && !empty($product_ids)) {
                $product_names = array();
                foreach ($product_ids as $pid) {
                    $pid = (int) $pid;
                    if ($pid) {
                        $title = get_the_title($pid);
                        if ($title) {
                            $product_names[] = $title;
                        }
                    }
                }

                if (!empty($product_names)) {
                    echo '<div style="font-size: 11px; color: #666; margin-top: 4px; max-width: 200px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; line-height: 1.3;">';
                    echo esc_html(implode(', ', $product_names));
                    echo '</div>';
                }
            }

            return;
        }


        if ($column === 'wsr_delivery_method') {
            $method = (string) get_post_meta($post_id, 'wsr_delivery_method', true);
            $status = (string) get_post_meta($post_id, 'wsr_status', true);

            $method_labels = array(
                'pickup' => 'นัดรับ',
                'ems' => 'ส่ง EMS',
                'messenger' => 'แมสเซนเจอร์',
            );

            $status_labels = array(
                'pending' => 'รอโอนเงิน',
                'paid' => 'ชำระแล้ว',
                'shipped' => 'จัดส่งแล้ว',
                'refunded' => 'คืนเงิน',
            );

            $status_colors = array(
                'pending' => '#f59e0b',
                'paid' => '#10b981',
                'shipped' => '#3b82f6',
                'refunded' => '#ef4444',
            );

            $method_text = ($method !== '' && isset($method_labels[$method])) ? $method_labels[$method] : '—';
            $status_text = ($status !== '' && isset($status_labels[$status])) ? $status_labels[$status] : '—';
            $status_color = ($status !== '' && isset($status_colors[$status])) ? $status_colors[$status] : '#666';

            echo '<div style="display: flex; flex-direction: column; gap: 4px;">';

            if ($status !== '') {
                echo '<span style="color: white; background-color: ' . esc_attr($status_color) . '; padding: 3px 6px; border-radius: 3px; font-size: 11px; font-weight: 600; display: inline-block; width: fit-content;">' . esc_html($status_text) . '</span>';
            }

            echo '<span style="font-size: 12px; color: #666;">' . esc_html($method_text) . '</span>';
            echo '</div>';

            return;
        }

        if ($column === 'wsr_selling_price') {
            $value = get_post_meta($post_id, 'wsr_selling_price', true);
            if ($value === '' || $value === null) {
                echo esc_html('—');
                return;
            }
            echo esc_html(number_format_i18n((float) $value, 2));
            return;
        }

        if ($column === 'wsr_shipping_paid') {
            $value = get_post_meta($post_id, 'wsr_shipping_paid', true);
            if ($value === '' || $value === null) {
                echo esc_html('—');
                return;
            }
            echo esc_html(number_format_i18n((float) $value, 2));
            return;
        }
    }

    public function sales_record_sortable_columns($columns)
    {
        $columns['wsr_sale_date'] = 'wsr_sale_date';
        $columns['wsr_lazada_order'] = 'wsr_lazada_order';
        $columns['wsr_selling_price'] = 'wsr_selling_price';

        return $columns;
    }

    public function sales_record_admin_sorting($query)
    {
        if (! is_admin() || ! $query->is_main_query()) {
            return;
        }

        $post_type = $query->get('post_type');
        if ($post_type !== 'sales_record') {
            return;
        }

        $orderby = $query->get('orderby');
        if (! is_string($orderby) || $orderby === '') {
            return;
        }

        if ($orderby === 'wsr_sale_date') {
            $query->set('meta_key', 'wsr_sale_date');
            $query->set('orderby', 'meta_value');
            return;
        }

        if ($orderby === 'wsr_lazada_order') {
            $query->set('meta_key', 'wsr_lazada_order');
            $query->set('orderby', 'meta_value');
            return;
        }

        if ($orderby === 'wsr_selling_price') {
            $query->set('meta_key', 'wsr_selling_price');
            $query->set('orderby', 'meta_value_num');
            return;
        }
    }

    public function register_customer_cpt()
    {
        $labels = array(
            'name'                  => 'Customers',
            'singular_name'         => 'Customer',
            'menu_name'             => 'Customers',
            'add_new'               => 'Add New Customer',
            'add_new_item'          => 'Add New Customer',
            'edit_item'             => 'Edit Customer',
            'new_item'              => 'New Customer',
            'view_item'             => 'View Customer',
            'search_items'          => 'Search Customers',
            'not_found'             => 'No customers found',
        );

        $args = array(
            'label'                 => 'Customer',
            'labels'                => $labels,
            'supports'              => array('title', 'custom-fields'),
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => 'wsr-dashboard', // Show under main dashboard menu
            'menu_position'         => 5,
            'show_in_rest'          => true, // Expose to REST API for Gutenberg/React
            'rest_base'             => 'customers',
            'has_archive'           => false,
            'rewrite'               => false,
            'capability_type'       => 'post',
        );

        register_post_type('customer', $args);
    }

    public function register_product_cpt()
    {
        if (post_type_exists('product')) {
            global $wp_post_types;
            if (isset($wp_post_types['product'])) {
                $wp_post_types['product']->show_in_rest = true;
                if (empty($wp_post_types['product']->rest_base)) {
                    $wp_post_types['product']->rest_base = 'products';
                }
            }
            return;
        }

        $labels = array(
            'name'                  => 'Products',
            'singular_name'         => 'Product',
            'menu_name'             => 'Products',
            'add_new'               => 'Add New Product',
            'add_new_item'          => 'Add New Product',
            'edit_item'             => 'Edit Product',
            'new_item'              => 'New Product',
            'view_item'             => 'View Product',
            'search_items'          => 'Search Products',
            'not_found'             => 'No products found',
        );

        $args = array(
            'label'                 => 'Product',
            'labels'                => $labels,
            'supports'              => array('title', 'thumbnail', 'custom-fields'),
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => 'wsr-dashboard',
            'menu_position'         => 6,
            'show_in_rest'          => true,
            'rest_base'             => 'products',
            'has_archive'           => false,
            'rewrite'               => false,
            'capability_type'       => 'post',
        );

        register_post_type('product', $args);
    }

        // ... (We add the taxonomy registration method here) ...
    public function register_product_taxonomy()
    {
        $labels = array(
            'name'              => _x('หมวดหมู่สินค้า', 'taxonomy general name', 'wp-sales-record'),
            'singular_name'     => _x('หมวดหมู่', 'taxonomy singular name', 'wp-sales-record'),
            'search_items'      => __('ค้นหาหมวดหมู่', 'wp-sales-record'),
            'all_items'         => __('หมวดหมู่ทั้งหมด', 'wp-sales-record'),
            'parent_item'       => __('หมวดหมู่หลัก', 'wp-sales-record'),
            'parent_item_colon' => __('หมวดหมู่หลัก:', 'wp-sales-record'),
            'edit_item'         => __('แก้ไขหมวดหมู่', 'wp-sales-record'),
            'update_item'       => __('อัพเดทหมวดหมู่', 'wp-sales-record'),
            'add_new_item'      => __('เพิ่มหมวดหมู่ใหม่', 'wp-sales-record'),
            'new_item_name'     => __('ชื่อหมวดหมู่ใหม่', 'wp-sales-record'),
            'menu_name'         => __('หมวดหมู่', 'wp-sales-record'),
        );

        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'product-category'),
            'show_in_rest'      => true,
        );

        register_taxonomy('wsr_product_cat', array('product'), $args);

        // Brand
        $brand_labels = array(
            'name' => 'แบรนด์ (Brand)',
            'singular_name' => 'แบรนด์',
            'menu_name' => 'แบรนด์',
        );
        register_taxonomy('wsr_brand', array('product'), array(
            'hierarchical' => true,
            'labels' => $brand_labels,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_rest' => true,
        ));

        // Model
        $model_labels = array(
            'name' => 'รุ่น (Model)',
            'singular_name' => 'รุ่น',
            'menu_name' => 'รุ่น',
        );
        register_taxonomy('wsr_model', array('product'), array(
            'hierarchical' => true,
            'labels' => $model_labels,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_rest' => true,
        ));
    }

    public function register_sales_record_cpt()
    {
        $labels = array(
            'name'                  => 'Sales Records',
            'singular_name'         => 'Sales Record',
            'menu_name'             => 'Sales Records',
            'add_new'               => 'Add New Record',
            'add_new_item'          => 'Add New Sales Record',
            'edit_item'             => 'Edit Sales Record',
            'new_item'              => 'New Sales Record',
            'view_item'             => 'View Sales Record',
            'search_items'          => 'Search Sales Records',
            'not_found'             => 'No sales records found',
        );

        $args = array(
            'label'                 => 'Sales Record',
            'labels'                => $labels,
            'supports'              => array('title', 'custom-fields'), // We will map title to auto SKU
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => 'wsr-dashboard',
            'menu_icon'             => 'dashicons-chart-bar',
            'menu_position'         => 7,
            'show_in_rest'          => true,
            'rest_base'             => 'sales_records',
            'has_archive'           => false,
            'rewrite'               => false,
            'capability_type'       => 'post',
        );

        register_post_type('sales_record', $args);
    }

    public function register_rest_fields()
    {
        // Register Meta Fields for Customer
        register_post_meta('customer', 'wsr_social_name', array(
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'string',
        ));
        register_post_meta('customer', 'wsr_social_url', array(
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'string',
        ));
        register_post_meta('customer', 'wsr_sales_channel', array(
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'string',
        ));

        register_post_meta('customer', 'wsr_fb_avatar_url', array(
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'string',
        ));
        register_post_meta('customer', 'wsr_fb_avatar_urls', array(
            'show_in_rest' => array(
                'schema' => array(
                    'type'  => 'array',
                    'items' => array(
                        'type' => 'string',
                    ),
                ),
            ),
            'single'       => true,
            'type'         => 'array',
        ));
        register_post_meta('customer', 'wsr_fb_avatar_id', array(
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'integer',
        ));
        register_post_meta('customer', 'wsr_fb_avatar_spam', array(
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'string',
        ));
        register_post_meta('customer', 'wsr_fb_flag', array(
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'string',
        ));
        register_post_meta('customer', 'wsr_fb_flag_note', array(
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'string',
        ));

        // Register Meta Fields for Product
        register_post_meta('product', 'wsr_default_cost', array(
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'number',
        ));
        register_post_meta('product', 'wsr_default_price', array(
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'number',
        ));
        register_post_meta('product', 'wsr_lazada_order', array(
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'string',
        ));
        register_post_meta('product', 'wsr_purchase_date', array(
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'string',
        ));

        // Register Meta Fields for Sales Record
        register_post_meta('sales_record', 'wsr_customer_id', array(
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'integer',
        ));
        register_post_meta('sales_record', 'wsr_product_ids', array(
            'show_in_rest' => array(
                'schema' => array(
                    'type'  => 'array',
                    'items' => array(
                        'type' => 'integer',
                    ),
                ),
            ),
            'single'       => true,
            'type'         => 'array',
        ));
        register_post_meta('sales_record', 'wsr_lazada_order', array(
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'string',
        ));
        register_post_meta('sales_record', 'wsr_delivery_method', array(
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'string',
        ));
        register_post_meta('sales_record', 'wsr_sale_date', array(
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'string', // ISO format date string
        ));
        register_post_meta('sales_record', 'wsr_cost_price', array(
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'number',
        ));
        register_post_meta('sales_record', 'wsr_selling_price', array(
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'number',
        ));
        register_post_meta('sales_record', 'wsr_bargained_price', array(
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'number',
        ));
        register_post_meta('sales_record', 'wsr_prev_bargain_price', array(
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'number',
        ));
        register_post_meta('sales_record', 'wsr_shipping_paid', array(
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'number',
        ));
        register_post_meta('sales_record', 'wsr_shipping_actual', array(
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'number',
        ));
        register_post_meta('sales_record', 'wsr_status', array(
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'string',
        ));
        register_post_meta('sales_record', 'wsr_gallery', array(
            'show_in_rest' => array(
                'schema' => array(
                    'type'  => 'array',
                    'items' => array(
                        'type' => 'integer', // Attachment IDs
                    ),
                ),
            ),
            'single'       => true,
            'type'         => 'array',
        ));
        register_post_meta('sales_record', 'wsr_notes', array(
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'string',
        ));

        // Snapshot buyer social/avatar at time of sale (for scammer tracking)
        register_post_meta('sales_record', 'wsr_observed_social_url', array(
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'string',
        ));
        register_post_meta('sales_record', 'wsr_observed_avatar_url', array(
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'string',
        ));
        register_post_meta('sales_record', 'wsr_observed_avatar_id', array(
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'integer',
        ));
    }

    public function register_graph_api_endpoints()
    {
        register_rest_route(
            'wsr/v1',
            '/graph/overview',
            array(
                'methods'  => 'GET',
                'callback' => array($this, 'get_graph_overview_data'),
                'permission_callback' => function () {
                    return current_user_can('edit_posts');
                },
            )
        );

        register_rest_route(
            'wsr/v1',
            '/graph/monthly-sales',
            array(
                'methods'  => 'GET',
                'callback' => array($this, 'get_monthly_sales_data'),
                'permission_callback' => function () {
                    return current_user_can('edit_posts');
                },
            )
        );

        register_rest_route(
            'wsr/v1',
            '/graph/status',
            array(
                'methods'  => 'GET',
                'callback' => array($this, 'get_status_data'),
                'permission_callback' => function () {
                    return current_user_can('edit_posts');
                },
            )
        );

        register_rest_route(
            'wsr/v1',
            '/graph/delivery',
            array(
                'methods'  => 'GET',
                'callback' => array($this, 'get_delivery_data'),
                'permission_callback' => function () {
                    return current_user_can('edit_posts');
                },
            )
        );

        register_rest_route(
            'wsr/v1',
            '/graph/products',
            array(
                'methods'  => 'GET',
                'callback' => array($this, 'get_products_data'),
                'permission_callback' => function () {
                    return current_user_can('edit_posts');
                },
            )
        );

        register_rest_route(
            'wsr/v1',
            '/graph/customers',
            array(
                'methods'  => 'GET',
                'callback' => array($this, 'get_customers_data'),
                'permission_callback' => function () {
                    return current_user_can('edit_posts');
                },
            )
        );

        register_rest_route(
            'wsr/v1',
            '/graph/profit',
            array(
                'methods'  => 'GET',
                'callback' => array($this, 'get_profit_data'),
                'permission_callback' => function () {
                    return current_user_can('edit_posts');
                },
            )
        );
        register_rest_route(
            'wsr/v1',
            '/graph/export-csv',
            array(
                'methods'  => 'GET',
                'callback' => array($this, 'export_sales_csv'),
                'permission_callback' => function () {
                    return current_user_can('edit_posts');
                },
            )
        );

        register_rest_route(
            'wsr/v1',
            '/graph/orders',
            array(
                'methods'  => 'GET',
                'callback' => array($this, 'get_graph_orders_data'),
                'permission_callback' => function () {
                    return current_user_can('edit_posts');
                },
            )
        );
    }

    public function get_graph_orders_data($request)
    {
        $group = (string) $request->get_param('group');
        $group = $group !== '' ? $group : 'day';
        $label = (string) $request->get_param('label');

        $date_from = '';
        $date_to = '';

        if ($group === 'day') {
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $label)) {
                $date_from = $label;
                $date_to = $label;
            }
        } elseif ($group === 'month') {
            if (preg_match('/^\d{4}-\d{2}$/', $label)) {
                $date_from = $label . '-01';
                $date_to = gmdate('Y-m-t', strtotime($date_from));
            }
        } elseif ($group === 'week') {
            if (preg_match('/^(\d{4})-W(\d{2})$/', $label, $m)) {
                $year = (int) $m[1];
                $week = (int) $m[2];
                try {
                    $dt = new DateTime();
                    $dt->setISODate($year, $week);
                    $date_from = $dt->format('Y-m-d');
                    $dt->modify('+6 days');
                    $date_to = $dt->format('Y-m-d');
                } catch (Exception $e) {
                    $date_from = '';
                    $date_to = '';
                }
            }
        }

        if ($date_from === '' || $date_to === '') {
            return rest_ensure_response(array(
                'label' => $label,
                'group' => $group,
                'date_from' => $date_from,
                'date_to' => $date_to,
                'orders' => array(),
            ));
        }

        $ids = $this->wsr_graph_get_sales_record_ids($date_from, $date_to);
        $orders = array();

        foreach ($ids as $post_id) {
            $sale_date = (string) get_post_meta($post_id, 'wsr_sale_date', true);
            if ($sale_date === '') {
                continue;
            }

            $bargained_price = get_post_meta($post_id, 'wsr_bargained_price', true);
            $selling_price = get_post_meta($post_id, 'wsr_selling_price', true);
            $net_amount = $bargained_price !== '' ? (float) $bargained_price : (float) $selling_price;

            $customer_id = (int) get_post_meta($post_id, 'wsr_customer_id', true);
            $customer_name = $customer_id > 0 ? get_the_title($customer_id) : 'ไม่ระบุ';

            $product_ids = get_post_meta($post_id, 'wsr_product_ids', true);
            $product_titles = array();
            if (is_array($product_ids)) {
                foreach ($product_ids as $pid) {
                    $pid = (int) $pid;
                    if ($pid > 0) {
                        $product_titles[] = get_the_title($pid);
                    }
                }
            }

            $orders[] = array(
                'id' => (int) $post_id,
                'title' => get_the_title($post_id),
                'sale_date' => $sale_date,
                'customer_id' => $customer_id,
                'customer_name' => $customer_name,
                'amount' => $net_amount,
                'bargained_price' => $bargained_price !== '' ? (float) $bargained_price : null,
                'selling_price' => $selling_price !== '' ? (float) $selling_price : null,
                'products' => $product_titles,
                'edit_link' => admin_url('post.php?post=' . $post_id . '&action=edit'),
            );
        }

        usort($orders, function ($a, $b) {
            return strcmp($b['sale_date'], $a['sale_date']);
        });

        return rest_ensure_response(array(
            'label' => $label,
            'group' => $group,
            'date_from' => $date_from,
            'date_to' => $date_to,
            'orders' => $orders,
        ));
    }

    private function wsr_graph_get_date_range_from_request($request)
    {
        $preset = (string) $request->get_param('preset');
        $preset = $preset !== '' ? $preset : 'all';

        $from = (string) $request->get_param('from');
        $to = (string) $request->get_param('to');

        $today = current_time('Y-m-d');

        if ($preset === 'today') {
            return array($today, $today);
        }

        if ($preset === 'last_7_days') {
            $from_calc = gmdate('Y-m-d', strtotime($today . ' -6 days'));
            return array($from_calc, $today);
        }

        if ($preset === 'this_month') {
            $from_calc = gmdate('Y-m-01', strtotime($today));
            $to_calc = gmdate('Y-m-t', strtotime($today));
            return array($from_calc, $to_calc);
        }

        if ($preset === 'this_year') {
            $from_calc = gmdate('Y-01-01', strtotime($today));
            $to_calc = gmdate('Y-12-31', strtotime($today));
            return array($from_calc, $to_calc);
        }

        if ($preset === 'custom') {
            $from = preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) ? $from : '';
            $to = preg_match('/^\d{4}-\d{2}-\d{2}$/', $to) ? $to : '';

            if ($from !== '' && $to !== '') {
                return array($from, $to);
            }
        }

        return array('', '');
    }

    public function wsr_graph_get_sales_record_ids($date_from, $date_to)
    {
        $args = array(
            'post_type'      => 'sales_record',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids',
        );

        if ($date_from !== '' && $date_to !== '') {
            $args['meta_query'] = array(
                array(
                    'key'     => 'wsr_sale_date',
                    'value'   => array($date_from, $date_to),
                    'compare' => 'BETWEEN',
                    'type'    => 'DATE',
                ),
            );
        }

        return get_posts($args);
    }

    public function get_graph_overview_data($request)
    {
        list($date_from, $date_to) = $this->wsr_graph_get_date_range_from_request($request);
        $sales_records = $this->wsr_graph_get_sales_record_ids($date_from, $date_to);

        $all_customers = get_posts(array(
            'post_type'      => 'customer',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids',
        ));
        $all_products = get_posts(array(
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids',
        ));

        $total_sales = 0;
        $total_orders = count($sales_records);
        $shipped_count = 0;
        $active_customers = array();
        $active_products = array();

        foreach ($sales_records as $post_id) {
            $selling_price = get_post_meta($post_id, 'wsr_selling_price', true);
            $status = get_post_meta($post_id, 'wsr_status', true);
            $customer_id = (int) get_post_meta($post_id, 'wsr_customer_id', true);
            $product_ids = get_post_meta($post_id, 'wsr_product_ids', true);

            if ($selling_price) {
                $total_sales += (float) $selling_price;
            }

            if ($status === 'shipped') {
                $shipped_count++;
            }

            if ($customer_id > 0) {
                $active_customers[$customer_id] = true;
            }

            if (is_array($product_ids)) {
                foreach ($product_ids as $pid) {
                    $pid = (int) $pid;
                    if ($pid > 0) {
                        $active_products[$pid] = true;
                    }
                }
            }
        }

        $average_order = $total_orders > 0 ? $total_sales / $total_orders : 0;

        $seven_days_ago = gmdate('Y-m-d', current_time('timestamp') - (7 * 24 * 60 * 60));
        $recent_args = array(
            'post_type'      => 'sales_record',
            'post_status'    => 'publish',
            'posts_per_page' => 20,
            'meta_query' => array(
                array(
                    'key'     => 'wsr_sale_date',
                    'value'   => $seven_days_ago,
                    'compare' => '>=',
                    'type'    => 'DATE',
                ),
            ),
            'meta_key' => 'wsr_sale_date',
            'orderby'  => 'meta_value',
            'order'    => 'DESC',
        );
        $recent_posts = get_posts($recent_args);
        $recent_orders = array();
        
        $status_labels = array(
            'pending' => 'รอโอนเงิน',
            'paid' => 'ชำระแล้ว',
            'shipped' => 'จัดส่งแล้ว',
            'refunded' => 'คืนเงิน',
        );

        foreach ($recent_posts as $post) {
            $cid = (int) get_post_meta($post->ID, 'wsr_customer_id', true);
            $c_name = $cid > 0 ? get_the_title($cid) : 'ไม่ระบุ';
            $r_status = get_post_meta($post->ID, 'wsr_status', true);
            
            $recent_orders[] = array(
                'id' => $post->ID,
                'title' => get_the_title($post->ID) ?: '#' . $post->ID,
                'date' => get_post_meta($post->ID, 'wsr_sale_date', true),
                'price' => (float) get_post_meta($post->ID, 'wsr_selling_price', true),
                'status' => $r_status,
                'status_label' => isset($status_labels[$r_status]) ? $status_labels[$r_status] : $r_status,
                'customer' => $c_name,
                'edit_link' => admin_url('post.php?post=' . $post->ID . '&action=edit'),
            );
        }

        return rest_ensure_response(array(
            'total_sales'     => $total_sales,
            'total_orders'    => $total_orders,
            'average_order'   => $average_order,
            'shipped_count'   => $shipped_count,
            'total_customers' => count($all_customers),
            'total_products'  => count($all_products),
            'active_customers' => count($active_customers),
            'active_products'  => count($active_products),
            'recent_orders'    => $recent_orders,
        ));
    }

    public function get_monthly_sales_data($request)
    {
        list($date_from, $date_to) = $this->wsr_graph_get_date_range_from_request($request);
        $group = (string) $request->get_param('group');
        $group = $group !== '' ? $group : 'month';

        $sales_records = $this->wsr_graph_get_sales_record_ids($date_from, $date_to);
        $monthly_data = array();
        $weekly_data = array();
        $daily_data = array();

        foreach ($sales_records as $post_id) {
            $date = get_post_meta($post_id, 'wsr_sale_date', true);
            $bargained_price = get_post_meta($post_id, 'wsr_bargained_price', true);
            $selling_price = get_post_meta($post_id, 'wsr_selling_price', true);

            $net_amount = $bargained_price !== '' ? (float) $bargained_price : (float) $selling_price;

            if ($date && $net_amount) {
                $ts = strtotime($date);
                if ($group === 'day') {
                    $month_key = gmdate('Y-m-d', $ts);
                } elseif ($group === 'week') {
                    $month_key = gmdate('o-\WW', $ts);
                } else {
                    $month_key = gmdate('Y-m', $ts);
                }

                if (! isset($monthly_data[$month_key])) {
                    $monthly_data[$month_key] = 0;
                }

                $monthly_data[$month_key] += $net_amount;

                // Collect weekly data for monthly grouping (week of month: 1-5)
                if ($group === 'month') {
                    $month_key = gmdate('Y-m', $ts);
                    $day_of_month = (int) gmdate('j', $ts);
                    $week_of_month = (int) ceil($day_of_month / 7);
                    $week_key = $month_key . '-W' . $week_of_month;
                    $week_label = 'สัปดาห์ ' . $week_of_month;

                    if (! isset($weekly_data[$month_key])) {
                        $weekly_data[$month_key] = array();
                    }
                    if (! isset($weekly_data[$month_key][$week_key])) {
                        $weekly_data[$month_key][$week_key] = array(
                            'label' => $week_label,
                            'amount' => 0,
                        );
                    }
                    $weekly_data[$month_key][$week_key]['amount'] += $net_amount;
                }

                // Collect daily data for weekly grouping
                if ($group === 'week') {
                    $week_key = gmdate('o-\WW', $ts);
                    $week_label = 'สัปดาห์ ' . gmdate('W', $ts);
                    $day_key = gmdate('Y-m-d', $ts);
                    $day_label = gmdate('j M', $ts);

                    // Use the same key as monthly_data for consistency
                    if (! isset($daily_data[$month_key])) {
                        $daily_data[$month_key] = array();
                    }
                    if (! isset($daily_data[$month_key][$day_key])) {
                        $daily_data[$month_key][$day_key] = array(
                            'label' => $day_label,
                            'amount' => 0,
                        );
                    }
                    $daily_data[$month_key][$day_key]['amount'] += $net_amount;
                }
            }
        }

        ksort($monthly_data);

        $formatted_data = array();
        foreach ($monthly_data as $month => $amount) {
            $item = array(
                'label'  => $month,
                'amount' => $amount,
            );

            // Add weekly breakdown if available
            if ($group === 'month' && isset($weekly_data[$month])) {
                ksort($weekly_data[$month]);
                $item['weeks'] = array_values($weekly_data[$month]);
            }

            // Add daily breakdown if available
            if ($group === 'week' && isset($daily_data[$month])) {
                ksort($daily_data[$month]);
                $item['days'] = array_values($daily_data[$month]);
            }

            $formatted_data[] = $item;
        }

        return rest_ensure_response($formatted_data);
    }

    public function get_status_data($request)
    {
        list($date_from, $date_to) = $this->wsr_graph_get_date_range_from_request($request);
        $sales_records = $this->wsr_graph_get_sales_record_ids($date_from, $date_to);
        $status_counts = array(
            'pending'  => 0,
            'paid'     => 0,
            'shipped'  => 0,
            'refunded' => 0,
        );

        foreach ($sales_records as $post_id) {
            $status = get_post_meta($post_id, 'wsr_status', true);

            if ($status && isset($status_counts[$status])) {
                $status_counts[$status]++;
            }
        }

        return rest_ensure_response($status_counts);
    }

    public function get_delivery_data($request)
    {
        list($date_from, $date_to) = $this->wsr_graph_get_date_range_from_request($request);
        $sales_records = $this->wsr_graph_get_sales_record_ids($date_from, $date_to);
        $delivery_counts = array(
            'pickup'    => 0,
            'ems'       => 0,
            'messenger' => 0,
        );

        foreach ($sales_records as $post_id) {
            $method = get_post_meta($post_id, 'wsr_delivery_method', true);

            if ($method && isset($delivery_counts[$method])) {
                $delivery_counts[$method]++;
            }
        }

        return rest_ensure_response($delivery_counts);
    }

    public function get_products_data($request)
    {
        list($date_from, $date_to) = $this->wsr_graph_get_date_range_from_request($request);
        $limit = (int) $request->get_param('limit');
        $limit = $limit > 0 ? min(500, $limit) : 100;

        $group_by = sanitize_text_field($request->get_param('group_by'));
        if (!in_array($group_by, array('product', 'wsr_product_cat', 'wsr_brand', 'wsr_model'), true)) {
            $group_by = 'product';
        }

        $sales_records = $this->wsr_graph_get_sales_record_ids($date_from, $date_to);
        $product_stats = array();

        if ($group_by === 'product') {
            $products = get_posts(array(
                'post_type'      => 'product',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'fields'         => 'ids',
            ));
            foreach ($products as $pid) {
                $pid = (int) $pid;
                $product_stats[$pid] = array(
                    'id' => $pid,
                    'name' => get_the_title($pid),
                    'orders' => 0,
                    'revenue' => 0,
                    'edit_link' => admin_url('post.php?post=' . $pid . '&action=edit'),
                );
            }
        } else {
            $terms = get_terms(array('taxonomy' => $group_by, 'hide_empty' => false));
            foreach ($terms as $t) {
                if ($t instanceof WP_Term) {
                    $product_stats[$t->term_id] = array(
                        'id' => 'term_' . $t->term_id,
                        'name' => $t->name,
                        'orders' => 0,
                        'revenue' => 0,
                        'edit_link' => admin_url('term.php?taxonomy=' . $group_by . '&tag_ID=' . $t->term_id . '&post_type=product'),
                    );
                }
            }
            $product_stats['unassigned'] = array(
                'id' => 'unassigned',
                'name' => 'Unassigned',
                'orders' => 0,
                'revenue' => 0,
                'edit_link' => '#',
            );
        }

        foreach ($sales_records as $post_id) {
            $product_ids = get_post_meta($post_id, 'wsr_product_ids', true);
            $selling_price = (float) get_post_meta($post_id, 'wsr_selling_price', true);

            if (! is_array($product_ids) || count($product_ids) === 0) {
                continue;
            }

            $count_products_in_order = count($product_ids);
            $revenue_per_item = $count_products_in_order > 0 ? ($selling_price / $count_products_in_order) : 0;

            foreach ($product_ids as $product_id) {
                $product_id = (int) $product_id;
                if ($product_id <= 0) continue;

                if ($group_by === 'product') {
                    if (isset($product_stats[$product_id])) {
                        $product_stats[$product_id]['orders'] += 1;
                        $product_stats[$product_id]['revenue'] += $revenue_per_item;
                    }
                } else {
                    $term_ids = wp_get_post_terms($product_id, $group_by, array('fields' => 'ids'));
                    if (is_wp_error($term_ids) || empty($term_ids)) {
                        $product_stats['unassigned']['orders'] += 1;
                        $product_stats['unassigned']['revenue'] += $revenue_per_item;
                    } else {
                        // Brand/Model should be single-term (enforced in admin), so we take the first.
                        if (in_array($group_by, array('wsr_brand', 'wsr_model'), true)) {
                            $tid = (int) $term_ids[0];
                            if (isset($product_stats[$tid])) {
                                $product_stats[$tid]['orders'] += 1;
                                $product_stats[$tid]['revenue'] += $revenue_per_item;
                            }
                        } else {
                            // Categories can be multi-term; split revenue across terms to avoid double counting.
                            $cats_count = count($term_ids);
                            $rev_per_cat = $cats_count > 0 ? ($revenue_per_item / $cats_count) : 0;
                            foreach ($term_ids as $tid) {
                                $tid = (int) $tid;
                                if (isset($product_stats[$tid])) {
                                    $product_stats[$tid]['orders'] += 1;
                                    $product_stats[$tid]['revenue'] += $rev_per_cat;
                                }
                            }
                        }
                    }
                }
            }
        }

        if (isset($product_stats['unassigned']) && $product_stats['unassigned']['orders'] === 0) {
            unset($product_stats['unassigned']);
        }

        $all = array_values($product_stats);
        usort($all, function ($a, $b) {
            if ($a['orders'] === $b['orders']) {
                return $b['revenue'] <=> $a['revenue'];
            }
            return $b['orders'] <=> $a['orders'];
        });

        // Filter out 0 orders logically
        $all = array_filter($all, function($item) { return $item['orders'] > 0; });

        $top = array_slice($all, 0, $limit);
        return rest_ensure_response(array(
            'items' => array_values($top),
            'group_by' => $group_by,
            'total_items' => count($product_stats),
        ));
    }

    public function get_customers_data($request)
    {
        list($date_from, $date_to) = $this->wsr_graph_get_date_range_from_request($request);
        $limit = (int) $request->get_param('limit');
        $limit = $limit > 0 ? min(500, $limit) : 100;

        $customers = get_posts(array(
            'post_type'      => 'customer',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids',
        ));

        $customer_stats = array();
        foreach ($customers as $cid) {
            $cid = (int) $cid;
            $customer_stats[$cid] = array(
                'id' => $cid,
                'name' => get_the_title($cid),
                'orders' => 0,
                'revenue' => 0,
                'edit_link' => admin_url('post.php?post=' . $cid . '&action=edit'),
            );
        }

        $sales_records = $this->wsr_graph_get_sales_record_ids($date_from, $date_to);

        foreach ($sales_records as $post_id) {
            $customer_id = get_post_meta($post_id, 'wsr_customer_id', true);
            $selling_price = (float) get_post_meta($post_id, 'wsr_selling_price', true);
            $customer_id = (int) $customer_id;

            if ($customer_id <= 0 || ! isset($customer_stats[$customer_id])) {
                continue;
            }

            $customer_stats[$customer_id]['orders'] += 1;
            $customer_stats[$customer_id]['revenue'] += $selling_price;
        }

        $all = array_values($customer_stats);
        usort($all, function ($a, $b) {
            if ($a['orders'] === $b['orders']) {
                return $b['revenue'] <=> $a['revenue'];
            }
            return $b['orders'] <=> $a['orders'];
        });

        $top = array_slice($all, 0, $limit);

        foreach ($top as &$c) {
            $r = (float) $c['revenue'];
            if ($r >= 10000) {
                $c['rank'] = 'VIP';
                $c['rankColor'] = '#f59e0b';
            } elseif ($r >= 5000) {
                $c['rank'] = 'Gold';
                $c['rankColor'] = '#10b981';
            } elseif ($r >= 1000) {
                $c['rank'] = 'Silver';
                $c['rankColor'] = '#94a3b8';
            } else {
                $c['rank'] = 'Standard';
                $c['rankColor'] = '#64748b';
            }
        }
        $without_orders = 0;
        foreach ($customer_stats as $row) {
            if ((int) $row['orders'] === 0) {
                $without_orders++;
            }
        }

        return rest_ensure_response(array(
            'items' => $top,
            'total_customers' => count($customers),
            'customers_without_orders' => $without_orders,
        ));
    }

    public function get_profit_data($request)
    {
        list($date_from, $date_to) = $this->wsr_graph_get_date_range_from_request($request);
        $group = (string) $request->get_param('group');
        $group = $group !== '' ? $group : 'month';

        $sales_records = $this->wsr_graph_get_sales_record_ids($date_from, $date_to);
        $monthly_data = array();

        $total_revenue = 0;
        $total_cost = 0;
        $total_profit = 0;

        foreach ($sales_records as $post_id) {
            $date            = get_post_meta($post_id, 'wsr_sale_date', true);
            $selling_price   = (float) get_post_meta($post_id, 'wsr_selling_price', true);
            $bargained_price = get_post_meta($post_id, 'wsr_bargained_price', true);
            $cost_price      = (float) get_post_meta($post_id, 'wsr_cost_price', true);
            $shipping_paid   = (float) get_post_meta($post_id, 'wsr_shipping_paid', true);
            $shipping_actual = (float) get_post_meta($post_id, 'wsr_shipping_actual', true);
            $status          = get_post_meta($post_id, 'wsr_status', true);

            if ($status === 'refunded') {
                continue;
            }

            // ใช้ราคาที่ตกลงจริง (bargained) ถ้ามีค่า > 0 ไม่เช่นนั้น fallback ไปที่ selling_price
            $net_price = ($bargained_price !== '' && (float) $bargained_price > 0)
                ? (float) $bargained_price
                : $selling_price;

            $revenue = $net_price + $shipping_paid;
            $cost    = $cost_price + $shipping_actual;
            $profit  = $revenue - $cost;

            $total_revenue += $revenue;
            $total_cost += $cost;
            $total_profit += $profit;

            if ($date) {
                $ts = strtotime($date);
                if ($group === 'day') {
                    $month_key = gmdate('Y-m-d', $ts);
                } elseif ($group === 'week') {
                    $month_key = gmdate('o-\WW', $ts);
                } else {
                    $month_key = gmdate('Y-m', $ts);
                }

                if (! isset($monthly_data[$month_key])) {
                    $monthly_data[$month_key] = array('revenue' => 0, 'cost' => 0, 'profit' => 0);
                }

                $monthly_data[$month_key]['revenue'] += $revenue;
                $monthly_data[$month_key]['cost'] += $cost;
                $monthly_data[$month_key]['profit'] += $profit;
            }
        }

        ksort($monthly_data);

        $formatted_data = array();
        foreach ($monthly_data as $month => $data) {
            $formatted_data[] = array(
                'label'   => $month,
                'revenue' => $data['revenue'],
                'cost'    => $data['cost'],
                'profit'  => $data['profit'],
            );
        }

        return rest_ensure_response(array(
            'total_revenue' => $total_revenue,
            'total_cost'    => $total_cost,
            'total_profit'  => $total_profit,
            'items'         => $formatted_data
        ));
    }
    public function export_sales_csv($request) {
        list($date_from, $date_to) = $this->wsr_graph_get_date_range_from_request($request);
        $sales_records = $this->wsr_graph_get_sales_record_ids($date_from, $date_to);
        $csv_data = "\xEF\xBB\xBF"; 
        $csv_data .= "Order ID,Date,Customer,Selling Price,Cost,Shipping Paid,Shipping Actual,Status\n";
        $status_labels = array('pending' => 'รอโอนเงิน','paid' => 'ชำระแล้ว','shipped' => 'จัดส่งแล้ว','refunded' => 'คืนเงิน');
        foreach ($sales_records as $post_id) {
            $date = get_post_meta($post_id, 'wsr_sale_date', true);
            $customer_id = (int) get_post_meta($post_id, 'wsr_customer_id', true);
            $customer_name = $customer_id > 0 ? get_the_title($customer_id) : 'ไม่ระบุ';
            $selling_price = (float) get_post_meta($post_id, 'wsr_selling_price', true);
            $cost_price = (float) get_post_meta($post_id, 'wsr_cost_price', true);
            $shipping_paid = (float) get_post_meta($post_id, 'wsr_shipping_paid', true);
            $shipping_actual = (float) get_post_meta($post_id, 'wsr_shipping_actual', true);
            $status = get_post_meta($post_id, 'wsr_status', true);
            $status_label = isset($status_labels[$status]) ? $status_labels[$status] : $status;
            $row = array($post_id, $date, '"' . str_replace('"', '""', $customer_name) . '"', $selling_price, $cost_price, $shipping_paid, $shipping_actual, '"' . $status_label . '"');
            $csv_data .= implode(',', $row) . "\n";
        }
        $response = new \WP_REST_Response($csv_data, 200);
        $response->header('Content-Type', 'text/csv; charset=utf-8');
        $response->header('Content-Disposition', 'attachment; filename="sales-export-' . gmdate('Y-m-d') . '.csv"');
        return $response;
    }
}
