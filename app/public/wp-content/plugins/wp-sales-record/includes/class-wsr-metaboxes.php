<?php

/**
 * Classic Meta Boxes Registration
 */

if (! defined('ABSPATH')) {
    exit;
}

class WSR_MetaBoxes
{

    public function init()
    {
        add_action('add_meta_boxes', array($this, 'add_react_meta_boxes'));

        // Save Post Hooks for Classic Editor
        add_action('save_post_customer', array($this, 'save_customer_meta'));
        add_action('save_post_product', array($this, 'save_product_meta'));
        add_action('save_post_sales_record', array($this, 'save_sales_record_meta'));
    }

    public function add_react_meta_boxes()
    {
        add_meta_box(
            'wsr-customer-meta',
            'ข้อมูลลูกค้า (Customer Details)',
            array($this, 'render_customer_meta_box'),
            'customer',
            'normal',
            'high'
        );

        add_meta_box(
            'wsr-product-meta',
            'กำหนดราคาสินค้า',
            array($this, 'render_product_meta_box'),
            'product',
            'normal',
            'high'
        );

        add_meta_box(
            'wsr-sales-record-meta',
            'จัดการข้อมูลการขาย',
            array($this, 'render_sales_record_meta_box'),
            'sales_record',
            'normal',
            'high'
        );

        // Remove default WP Custom Fields meta box to keep the UI clean
        remove_meta_box('postcustom', 'customer', 'normal');
        remove_meta_box('postcustom', 'product', 'normal');
        remove_meta_box('postcustom', 'sales_record', 'normal');
    }

    public function render_customer_meta_box($post)
    {
?>
        <div id="wsr-customer-root" data-post-id="<?php echo esc_attr($post->ID); ?>">
            <p>กำลังโหลดฟอร์มข้อมูลลูกค้า...</p>
        </div>
    <?php
    }

    public function render_product_meta_box($post)
    {
    ?>
        <div id="wsr-product-root" data-post-id="<?php echo esc_attr($post->ID); ?>">
            <p>กำลังโหลดฟอร์มกำหนดราคา...</p>
        </div>
    <?php
    }

    public function render_sales_record_meta_box($post)
    {
    ?>
        <div id="wsr-sales-record-root" data-post-id="<?php echo esc_attr($post->ID); ?>">
            <p>กำลังโหลดตัวจัดการข้อมูลการขาย...</p>
        </div>
<?php
    }

    public function save_customer_meta($post_id)
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

        $text_fields = array(
            'wsr_social_name',
            'wsr_social_url',
            'wsr_sales_channel',
            'wsr_fb_avatar_url',
            'wsr_fb_flag',
            'wsr_fb_flag_note',
        );
        foreach ($text_fields as $field) {
            if (isset($_POST[$field])) {
                update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
            }
        }

        if (isset($_POST['wsr_fb_avatar_urls']) && is_array($_POST['wsr_fb_avatar_urls'])) {
            $urls = array_filter(array_map('esc_url_raw', $_POST['wsr_fb_avatar_urls']));
            update_post_meta($post_id, 'wsr_fb_avatar_urls', array_values($urls));
        } elseif (isset($_POST['wsr_fb_avatar_urls_empty']) && $_POST['wsr_fb_avatar_urls_empty'] == '1') {
            update_post_meta($post_id, 'wsr_fb_avatar_urls', array());
        }

        if (isset($_POST['wsr_fb_avatar_spam'])) {
            update_post_meta($post_id, 'wsr_fb_avatar_spam', sanitize_text_field($_POST['wsr_fb_avatar_spam']));
        }

        if (isset($_POST['wsr_fb_avatar_id']) && $_POST['wsr_fb_avatar_id'] !== '') {
            update_post_meta($post_id, 'wsr_fb_avatar_id', (int)$_POST['wsr_fb_avatar_id']);
        } elseif (isset($_POST['wsr_fb_avatar_id']) && $_POST['wsr_fb_avatar_id'] === '') {
            delete_post_meta($post_id, 'wsr_fb_avatar_id');
        }
    }

    public function save_product_meta($post_id)
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

        if (isset($_POST['wsr_purchase_date'])) {
            update_post_meta($post_id, 'wsr_purchase_date', sanitize_text_field($_POST['wsr_purchase_date']));
        }
        if (isset($_POST['wsr_lazada_order'])) {
            update_post_meta($post_id, 'wsr_lazada_order', sanitize_text_field($_POST['wsr_lazada_order']));
        }
        if (isset($_POST['wsr_default_cost'])) {
            update_post_meta($post_id, 'wsr_default_cost', (float)$_POST['wsr_default_cost']);
        }
        if (isset($_POST['wsr_default_price'])) {
            update_post_meta($post_id, 'wsr_default_price', (float)$_POST['wsr_default_price']);
        }
    }

    public function save_sales_record_meta($post_id)
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

        $text_fields = array(
            'wsr_sale_date',
            'wsr_status',
            'wsr_lazada_order',
            'wsr_delivery_method',
            'wsr_observed_social_url',
            'wsr_observed_avatar_url',
        );
        foreach ($text_fields as $field) {
            if (isset($_POST[$field])) {
                update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
            }
        }

        $numeric_fields = array(
            'wsr_customer_id',
            'wsr_cost_price',
            'wsr_selling_price',
            'wsr_bargained_price',
            'wsr_prev_bargain_price',
            'wsr_shipping_paid',
            'wsr_shipping_actual',
            'wsr_observed_avatar_id',
        );
        foreach ($numeric_fields as $field) {
            if (isset($_POST[$field]) && $_POST[$field] !== '') {
                update_post_meta($post_id, $field, (float)$_POST[$field]);
            } elseif (isset($_POST[$field]) && $_POST[$field] === '') {
                delete_post_meta($post_id, $field);
            }
        }

        if (isset($_POST['wsr_notes'])) {
            update_post_meta($post_id, 'wsr_notes', sanitize_textarea_field($_POST['wsr_notes']));
        }

        // Handle Product IDs Array
        if (isset($_POST['wsr_product_ids']) && is_array($_POST['wsr_product_ids'])) {
            $product_ids = array_map('intval', $_POST['wsr_product_ids']);
            update_post_meta($post_id, 'wsr_product_ids', $product_ids);
        } elseif (isset($_POST['wsr_product_ids_empty']) && $_POST['wsr_product_ids_empty'] == '1') {
            update_post_meta($post_id, 'wsr_product_ids', array());
        }
    }
}
