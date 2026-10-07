<?php
/**
 * The Standalone Footer template for Pre-Order, Login, and Register pages.
 *
 * @package raka-preorder-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ── Render Global Luxury Site Footer ──
if ( function_exists( 'raka_render_site_footer' ) ) {
	raka_render_site_footer();
}

// ── Render Global Slide-Over Cart Drawer ──
if ( function_exists( 'raka_render_cart_drawer' ) ) {
	raka_render_cart_drawer();
}

// Render Fullscreen Menu Overlay
get_template_part( 'template-parts/header/overlay-menu' );

wp_footer();
?>
</body>
</html>
