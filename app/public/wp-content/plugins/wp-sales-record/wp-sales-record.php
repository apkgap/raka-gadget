<?php

/**
 * Plugin Name:       WP Sales Record
 * Description:       A modern sales record and order management plugin built with React and Gutenberg Components. Includes 3 custom post types: Customers, Products, and Sales Records.
 * Version:           1.0.0
 * Author:            Your Name
 * License:           GPL-2.0+
 * Text Domain:       wp-sales-record
 */

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Define Plugin Constants
define('WSR_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WSR_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WSR_PLUGIN_VERSION', '1.0.0');

// Include Core Classes
require_once WSR_PLUGIN_DIR . 'includes/class-wsr-cpt.php';
require_once WSR_PLUGIN_DIR . 'includes/class-wsr-dashboard.php';
require_once WSR_PLUGIN_DIR . 'includes/class-wsr-graph-analysis.php';
require_once WSR_PLUGIN_DIR . 'includes/class-wsr-metaboxes.php';
require_once WSR_PLUGIN_DIR . 'includes/class-wsr-pos.php';
require_once WSR_PLUGIN_DIR . 'includes/class-wsr-notifications.php';
require_once WSR_PLUGIN_DIR . 'includes/class-wsr-tools.php';
require_once WSR_PLUGIN_DIR . 'includes/class-wsr-wc-cost-profit.php';

// Initialize Plugin
function wsr_init()
{
    $cpt = new WSR_CPT();
    $cpt->init();

    $dashboard = new WSR_Dashboard();
    $dashboard->init();

    $graph_analysis = new WSR_Graph_Analysis();
    $graph_analysis->init();

    $metaboxes = new WSR_MetaBoxes();
    $metaboxes->init();

    $notifications = new WSR_Notifications();
    $notifications->init();

    $pos = new WSR_POS();
    $pos->init();

    $tools = new WSR_Tools();
    $tools->init();

    if (class_exists('WooCommerce')) {
        $wc_cogs = new WSR_WooCommerce_Cost_Profit();
        $wc_cogs->init();
    }
}
add_action('plugins_loaded', 'wsr_init');

// Enqueue Admin Scripts
function wsr_enqueue_admin_scripts($hook_suffix)
{
    // Only load on our specific post types and dashboard page
    $allowed_screens = array(
        'customer',
        'product',
        'sales_record',
        'toplevel_page_wsr-dashboard',
        'wsr-dashboard_page_wsr-graph-analysis',
        'wsr-dashboard_page_wsr-pos'
    );

    $current_screen = get_current_screen();

    $page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
    $allowed_pages = array('wsr-dashboard', 'wsr-graph-analysis', 'wsr-pos', 'wsr-tools');

    if (
        (! $current_screen || ! in_array($current_screen->id, $allowed_screens, true))
        && ! in_array($page, $allowed_pages, true)
    ) {
        return;
    }

    $asset_file = WSR_PLUGIN_DIR . 'build/index.asset.php';
    if (! file_exists($asset_file)) {
        return;
    }

    $asset = require $asset_file;

    wp_enqueue_style(
        'wsr-fonts',
        WSR_PLUGIN_URL . 'assets/css/wsr-fonts.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_style(
        'wsr-admin-style',
        WSR_PLUGIN_URL . 'build/style-index.css',
        array('wp-components', 'wsr-fonts'),
        $asset['version'],
        'all'
    );

    wp_enqueue_script(
        'wsr-admin-script',
        WSR_PLUGIN_URL . 'build/index.js',
        array_merge($asset['dependencies'], array('wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data', 'wp-core-data')),
        $asset['version'],
        true
    );

    // Enforce single-term selection for wsr_brand and wsr_model on Product edit screens.
    if (
        $current_screen
        && $current_screen->id === 'product'
        && in_array($current_screen->base, array('post', 'post-new'), true)
    ) {
        $inline = <<<'JS'
(function () {
    function enforceSingle(containerId) {
        var box = document.getElementById(containerId);
        if (!box) return;
        box.addEventListener('change', function (e) {
            var target = e.target;
            if (!target || target.type !== 'checkbox') return;
            if (!target.checked) return;
            var checkboxes = box.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach(function (cb) {
                if (cb !== target) cb.checked = false;
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        enforceSingle('wsr_branddiv');
        enforceSingle('wsr_modeldiv');
    });
})();
JS;
        wp_add_inline_script('wsr-admin-script', $inline, 'after');
    }

    wp_localize_script(
        'wsr-admin-script',
        'wsrSettings',
        array(
            'root'  => esc_url_raw(rest_url()),
            'nonce' => wp_create_nonce('wp_rest'),
        )
    );
}
add_action('admin_enqueue_scripts', 'wsr_enqueue_admin_scripts', 999);
