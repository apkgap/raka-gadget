<?php

/**
 * Graph Analysis Page Class
 */

if (! defined('ABSPATH')) {
    exit;
}

class WSR_Graph_Analysis
{

    public function init()
    {
        add_action('admin_menu', array($this, 'register_submenu_page'));
    }

    public function register_submenu_page()
    {
        add_submenu_page(
            'wsr-dashboard', // Parent slug
            'วิเคราะห์กราฟ', // Page title
            'วิเคราะห์กราฟ', // Menu title
            'edit_posts',
            'wsr-graph-analysis', // Slug
            array($this, 'render_graph_analysis_page'),
            30 // Position
        );
    }

    public function render_graph_analysis_page()
    {
?>
        <div class="wrap wsr-graph-analysis-wrap">
            <h1>วิเคราะห์กราฟ</h1>
            <div id="wsr-graph-analysis-root">
                <!-- React Graph Analysis will mount here -->
                <p>Loading Graph Analysis...</p>
            </div>
        </div>
<?php
    }
}
