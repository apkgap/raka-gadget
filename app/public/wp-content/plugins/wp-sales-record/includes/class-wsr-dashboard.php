<?php

/**
 * Dashboard & Summary Page Class
 */

if (! defined('ABSPATH')) {
    exit;
}

class WSR_Dashboard
{

    public function init()
    {
        add_action('admin_menu', array($this, 'register_menu_page'));
    }

    public function register_menu_page()
    {
        add_menu_page(
            'Sales Dashboard',
            'Sales Record', // Menu Title
            'manage_options',
            'wsr-dashboard', // Slug
            array($this, 'render_dashboard_page'),
            'dashicons-chart-area', // Icon
            30 // Position
        );
    }

    public function render_dashboard_page()
    {
        // Output a simple container that React will attach to, or simple HTML for MVP
?>
        <div class="wrap wsr-dashboard-wrap">
            <h1>Sales Dashboard</h1>
            <div id="wsr-dashboard-root">
                <!-- React Dashboard will mount here -->
                <p>Loading Dashboard...</p>
            </div>
        </div>
<?php
    }
}
