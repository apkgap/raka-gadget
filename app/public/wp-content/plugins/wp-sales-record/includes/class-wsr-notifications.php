<?php

/**
 * Notifications and Telegram Integration
 */

if (! defined('ABSPATH')) {
    exit;
}

class WSR_Notifications
{
    public function init()
    {
        add_action('admin_menu', array($this, 'register_settings_page'));
        add_action('admin_init', array($this, 'register_settings'));

        // Handle Test Message
        add_action('admin_post_wsr_test_telegram', array($this, 'handle_test_telegram'));

        // Hook on saving sales record
        add_action('save_post_sales_record', array($this, 'trigger_new_order_notification'), 10, 3);

        // Cron for daily summary
        add_action('wsr_daily_summary_event', array($this, 'send_daily_summary'));
        if (!wp_next_scheduled('wsr_daily_summary_event')) {
            // Schedule for 23:00 everyday
            // Current time in Bangkok timezone (UTC+7)
            $target_time = strtotime('today 23:00:00', current_time('timestamp'));
            if ($target_time < current_time('timestamp')) {
                $target_time += DAY_IN_SECONDS;
            }
            // convert back to UTC for cron scheduling
            $target_time_utc = $target_time - (get_option('gmt_offset') * HOUR_IN_SECONDS);
            wp_schedule_event($target_time_utc, 'daily', 'wsr_daily_summary_event');
        }
    }

    public function register_settings_page()
    {
        add_submenu_page(
            'wsr-dashboard',
            'Telegram Notifications',
            'การแจ้งเตือน (Telegram)',
            'manage_options',
            'wsr-notifications',
            array($this, 'render_settings_page')
        );
    }

    public function register_settings()
    {
        register_setting('wsr_notifications_group', 'wsr_tg_token');
        register_setting('wsr_notifications_group', 'wsr_tg_chat_id');
        register_setting('wsr_notifications_group', 'wsr_tg_notify_new');
        register_setting('wsr_notifications_group', 'wsr_tg_notify_daily');

        add_settings_section('wsr_tg_main_section', 'ตั้งค่า Telegram Bot', null, 'wsr-notifications');

        add_settings_field('wsr_tg_token', 'Bot Token', function() {
            $val = get_option('wsr_tg_token');
            echo '<input type="text" name="wsr_tg_token" value="' . esc_attr($val) . '" class="regular-text" placeholder="123456789:ABCdefGHI..."/>';
            echo '<p class="description">รับ Token จากการสร้าง Bot ผ่าน @BotFather ใน Telegram</p>';
        }, 'wsr-notifications', 'wsr_tg_main_section');

        add_settings_field('wsr_tg_chat_id', 'Chat ID', function() {
            $val = get_option('wsr_tg_chat_id');
            echo '<input type="text" name="wsr_tg_token" value="' . esc_attr($val) . '" class="regular-text" placeholder="-10012345... หรือ ID ส่วนตัว"/>';
            echo '<p class="description">Chat ID ของกลุ่มหรือบุคคลที่จะให้แชทบอทส่งข้อความไป</p>';
        }, 'wsr-notifications', 'wsr_tg_main_section');

        add_settings_field('wsr_tg_notify_new', 'ออเดอร์ใหม่', function() {
            $val = get_option('wsr_tg_notify_new');
            echo '<label><input type="checkbox" name="wsr_tg_notify_new" value="1" ' . checked(1, $val, false) . ' /> แจ้งเตือนเมื่อมีการบันทึกออเดอร์ใหม่ หรือออเดอร์ถูกเปลี่ยนสถานะเป็น "ชำระแล้ว"</label>';
        }, 'wsr-notifications', 'wsr_tg_main_section');

        add_settings_field('wsr_tg_notify_daily', 'สรุปยอดประจำวัน', function() {
            $val = get_option('wsr_tg_notify_daily');
            echo '<label><input type="checkbox" name="wsr_tg_notify_daily" value="1" ' . checked(1, $val, false) . ' /> ส่งสรุปยอดขายเข้า Telegram อัตโนมัติทุกวันในเวลา 23:00 น.</label>';
        }, 'wsr-notifications', 'wsr_tg_main_section');
    }

    public function render_settings_page()
    {
        ?>
        <div class="wrap">
            <h1>การแจ้งเตือน (Telegram Integration)</h1>
            
            <?php if (isset($_GET['tg_test'])): ?>
                <div class="notice notice-<?php echo $_GET['tg_test'] === 'success' ? 'success' : 'error'; ?> is-dismissible">
                    <p><?php echo $_GET['tg_test'] === 'success' ? 'ส่งข้อความทดสอบสำเร็จ! ลองตรวจสอบในแอป Telegram ของคุณ' : 'ส่งข้อความล้มเหลว โปรดตรวจสอบ Token และ Chat ID อีกครั้ง'; ?></p>
                </div>
            <?php endif; ?>

            <p>ตั้งค่าการส่งข้อความแจ้งเตือนผ่าน Telegram เพื่อให้คุณไม่พลาดทุกออเดอร์และยอดขายประจำวัน</p>
            <form method="post" action="options.php">
                <?php
                settings_fields('wsr_notifications_group');
                do_settings_sections('wsr-notifications');
                submit_button('บันทึกการตั้งค่า');
                ?>
            </form>
            <hr>
            <h3>ทดสอบส่งข้อความ</h3>
            <p>หลังจากบันทึก Token และ Chat ID แล้ว สามารถกดปุ่มด้านล่างเพื่อทดสอบส่งข้อความได้</p>
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <input type="hidden" name="action" value="wsr_test_telegram">
                <?php wp_nonce_field('wsr_test_tg_nonce'); ?>
                <button type="submit" class="button button-secondary">ส่งข้อความทดสอบ</button>
            </form>
        </div>
        <?php
    }

    public function send_telegram_message($text)
    {
        $token = get_option('wsr_tg_token');
        $chat_id = get_option('wsr_tg_chat_id');

        if (empty($token) || empty($chat_id)) {
            return false;
        }

        $url = "https://api.telegram.org/bot{$token}/sendMessage";
        $response = wp_remote_post($url, array(
            'body' => array(
                'chat_id' => $chat_id,
                'text' => $text,
                'parse_mode' => 'HTML'
            ),
            'timeout' => 10
        ));

        if (is_wp_error($response)) {
            return false;
        }
        return true;
    }

    public function handle_test_telegram()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        check_admin_referer('wsr_test_tg_nonce');

        $sent = $this->send_telegram_message("<b>[ทดสอบระบบสำเร็จ]</b>\nWP Sales Record สามารถส่งข้อความเข้า Telegram ได้อย่างสมบูรณ์แบบครับ");
        
        $redirect_url = add_query_arg(
            array('page' => 'wsr-notifications', 'tg_test' => $sent ? 'success' : 'failed'),
            admin_url('admin.php')
        );
        wp_redirect($redirect_url);
        exit;
    }

    public function trigger_new_order_notification($post_id, $post, $update)
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (get_option('wsr_tg_notify_new') != '1') return;

        // Process only if status is changed to paid or if it's a new post with paid status.
        // Actually, detecting state change in save_post is tricky because it fires after save. 
        // We can just check if status is paid, and we haven't notified for this post.
        
        $status = get_post_meta($post_id, 'wsr_status', true);
        $notified = get_post_meta($post_id, '_wsr_tg_notified', true);

        if ($status === 'paid' && !$notified) {
            $customer_id = (int) get_post_meta($post_id, 'wsr_customer_id', true);
            $customer_name = $customer_id > 0 ? get_the_title($customer_id) : 'ไม่ระบุ';
            $selling_price = (float) get_post_meta($post_id, 'wsr_selling_price', true);
            
            $text = "<b>[แจ้งเตือนออเดอร์ใหม่]</b>\n\n";
            $text .= "<b>ID:</b> #" . $post_id . "\n";
            $text .= "<b>ลูกค้า:</b> " . $customer_name . "\n";
            $text .= "<b>ยอดชำระ:</b> ฿" . number_format($selling_price, 2) . "\n";
            $text .= "<b>สถานะ:</b> ชำระแล้ว\n";

            $sent = $this->send_telegram_message($text);
            if ($sent) {
                update_post_meta($post_id, '_wsr_tg_notified', '1');
            }
        }
    }

    public function send_daily_summary()
    {
        if (get_option('wsr_tg_notify_daily') != '1') return;

        $date_from = wp_date('Y-m-d');
        $date_to = wp_date('Y-m-d');

        $cpt = new WSR_CPT();
        $sales_records = $cpt->wsr_graph_get_sales_record_ids($date_from, $date_to);

        $total_orders = 0;
        $total_revenue = 0;
        $total_profit = 0;

        foreach ($sales_records as $post_id) {
            $status = get_post_meta($post_id, 'wsr_status', true);
            if ($status === 'refunded') continue;

            $total_orders++;
            $selling_price = (float) get_post_meta($post_id, 'wsr_selling_price', true);
            $cost_price = (float) get_post_meta($post_id, 'wsr_cost_price', true);
            $shipping_paid = (float) get_post_meta($post_id, 'wsr_shipping_paid', true);
            $shipping_actual = (float) get_post_meta($post_id, 'wsr_shipping_actual', true);

            $shipping_profit = $shipping_paid - $shipping_actual;
            $order_profit = ($selling_price - $cost_price) + $shipping_profit;

            $total_revenue += $selling_price;
            $total_profit += $order_profit;
        }

        $text = "<b>[สรุปยอดขายประจำวัน]</b> (" . wp_date('d/m/Y') . ")\n\n";
        $text .= "ออเดอร์ทั้งหมด: <b>{$total_orders}</b> รายการ\n";
        $text .= "ยอดขายรวม: <b>฿" . number_format($total_revenue, 2) . "</b>\n";
        $text .= "กำไรสุทธิ: <b>฿" . number_format($total_profit, 2) . "</b>\n";

        $this->send_telegram_message($text);
    }
}
