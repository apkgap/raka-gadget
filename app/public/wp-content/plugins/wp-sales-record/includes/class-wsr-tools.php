<?php

if (! defined('ABSPATH')) {
    exit;
}

class WSR_Tools
{
    public function init()
    {
        add_action('admin_menu', array($this, 'register_menu_page'));
        add_action('admin_post_wsr_export_products', array($this, 'handle_export_products'));
        add_action('admin_post_wsr_import_products', array($this, 'handle_import_products'));
    }

    public function register_menu_page()
    {
        add_submenu_page(
            'wsr-dashboard',
            'จัดการหมวดหมู่สินค้า',
            'จัดการข้อมูล (Tools)',
            'manage_options',
            'wsr-tools',
            array($this, 'render_tools_page')
        );
    }

    public function render_tools_page()
    {
        ?>
        <div class="wrap">
            <h1>เครื่องมือจัดการและจัดกลุ่มสินค้า (Migration Tools)</h1>
            <p>ระบบนี้ใช้สำหรับส่งออกสินค้ารายชิ้นออกมาเป็นไฟล์ JSON เพื่อให้คุณนำไปจัดหมวดหมู่ (Category), ยี่ห้อ (Brand), และรุ่น (Model) ในไฟล์ได้อย่างรวดเร็ว แล้วอัพโหลดกลับเข้ามาเพื่ออัพเดทฐานข้อมูล</p>

            <?php if (isset($_GET['imported'])): ?>
                <div class="notice notice-success is-dismissible">
                    <p>อัพเดทข้อมูลสินค้าเรียบร้อยแล้ว จำนวน <?php echo intval($_GET['count']); ?> รายการ</p>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div class="notice notice-error is-dismissible">
                    <p>เกิดข้อผิดพลาด: <?php echo esc_html($_GET['error']); ?></p>
                </div>
            <?php endif; ?>

            <div style="display: flex; gap: 20px; margin-top: 30px;">
                <!-- Export Box -->
                <div style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 4px; flex: 1;">
                    <h2>1. ส่งออกข้อมูลสินค้า (Export)</h2>
                    <p>ดาวน์โหลดไฟล์ JSON ที่มีรายการสินค้าทั้งหมด ระบบจะสร้างท่อเปล่าๆ (category, brand, model) เตรียมไว้ให้คุณ</p>
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                        <input type="hidden" name="action" value="wsr_export_products">
                        <?php wp_nonce_field('wsr_export_products_nonce'); ?>
                        <button type="submit" class="button button-primary">ดาวน์โหลดไฟล์ JSON</button>
                    </form>
                </div>

                <!-- Import Box -->
                <div style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 4px; flex: 1;">
                    <h2>2. อัพเดทข้อมูล (Import)</h2>
                    <p>อัพโหลดไฟล์ JSON ที่คุณกรอกข้อมูลหมวดหมู่, แบรนด์, รุ่น เรียบร้อยแล้ว (จำเป็นต้องมี id ของสินค้าอยู่เหมือนเดิม)</p>
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="wsr_import_products">
                        <?php wp_nonce_field('wsr_import_products_nonce'); ?>
                        <input type="file" name="import_file" accept=".json" required style="margin-bottom: 15px; display: block;">
                        <button type="submit" class="button button-primary">เริ่มอัพเดทข้อมูล</button>
                    </form>
                </div>
            </div>
            
            <div style="margin-top: 30px; background: #f0f0f1; padding: 15px; border-left: 4px solid #00a0d2;">
                <h3>ตัวอย่างรูปแบบ JSON:</h3>
                <pre style="background: #fff; padding: 10px; border: 1px solid #ccc;">
[
  {
    "id": 1234,
    "title": "AULA F98 Pro V3 White transparent",
    "category": "คีย์บอร์ดไร้สาย",
    "brand": "AULA",
    "model": "F98 Pro"
  }
]
                </pre>
            </div>
        </div>
        <?php
    }

    public function handle_export_products()
    {
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        check_admin_referer('wsr_export_products_nonce');

        $products = get_posts(array(
            'post_type' => 'product',
            'posts_per_page' => -1,
            'post_status' => 'any'
        ));

        $data = array();
        foreach ($products as $p) {
            // Get existing terms to pre-fill
            $cats = wp_get_post_terms($p->ID, 'wsr_product_cat', array('fields' => 'names'));
            $brands = wp_get_post_terms($p->ID, 'wsr_brand', array('fields' => 'names'));
            $models = wp_get_post_terms($p->ID, 'wsr_model', array('fields' => 'names'));

            $data[] = array(
                'id' => $p->ID,
                'title' => $p->post_title,
                'category' => !is_wp_error($cats) && !empty($cats) ? implode(', ', $cats) : '',
                'brand' => !is_wp_error($brands) && !empty($brands) ? implode(', ', $brands) : '',
                'model' => !is_wp_error($models) && !empty($models) ? implode(', ', $models) : ''
            );
        }

        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename=products_migration_' . date('Ymd_His') . '.json');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function handle_import_products()
    {
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        check_admin_referer('wsr_import_products_nonce');

        if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
            wp_redirect(admin_url('admin.php?page=wsr-tools&error=Upload+Failed'));
            exit;
        }

        $json_data = file_get_contents($_FILES['import_file']['tmp_name']);
        $items = json_decode($json_data, true);

        if (!is_array($items)) {
            wp_redirect(admin_url('admin.php?page=wsr-tools&error=Invalid+JSON+Format'));
            exit;
        }

        $count = 0;
        foreach ($items as $item) {
            if (empty($item['id'])) continue;

            $post_id = intval($item['id']);
            
            // Assign Category
            if (isset($item['category']) && trim($item['category']) !== '') {
                // Split by comma if multiple
                $cats = array_map('trim', explode(',', $item['category']));
                wp_set_object_terms($post_id, $cats, 'wsr_product_cat', false); // false = replace existing
            } else if (isset($item['category'])) {
                wp_set_object_terms($post_id, array(), 'wsr_product_cat', false); // clear
            }

            // Assign Brand
            if (isset($item['brand']) && trim($item['brand']) !== '') {
                $brands = array_map('trim', explode(',', $item['brand']));
                wp_set_object_terms($post_id, $brands, 'wsr_brand', false);
            } else if (isset($item['brand'])) {
                wp_set_object_terms($post_id, array(), 'wsr_brand', false); // clear
            }

            // Assign Model
            if (isset($item['model']) && trim($item['model']) !== '') {
                $models = array_map('trim', explode(',', $item['model']));
                wp_set_object_terms($post_id, $models, 'wsr_model', false);
            } else if (isset($item['model'])) {
                wp_set_object_terms($post_id, array(), 'wsr_model', false); // clear
            }

            $count++;
        }

        wp_redirect(admin_url('admin.php?page=wsr-tools&imported=1&count=' . $count));
        exit;
    }
}
