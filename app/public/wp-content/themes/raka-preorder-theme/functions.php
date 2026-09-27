<?php
/**
 * Theme functions and definitions.
 *
 * @package raka-preorder-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Set up theme defaults and register support for various WordPress features.
 */
function raka_preorder_theme_setup() {
	// Enable support for Post Thumbnails on posts and pages.
	add_theme_support( 'post-thumbnails' );

	// Enable support for Menus.
	add_theme_support( 'menus' );

	// Enable support for WooCommerce.
	add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	// Register Navigation Menu Locations.
	register_nav_menus( [
		'primary-menu' => __( 'Primary Navigation / Mega Menu', 'raka-preorder-theme' ),
		'footer-menu'  => __( 'Footer Menu', 'raka-preorder-theme' ),
	] );

	// Enable support for Custom Logo.
	add_theme_support( 'custom-logo', [
		'height'      => 100,
		'width'       => 300,
		'flex-height' => true,
		'flex-width'  => true,
	] );
}
add_action( 'after_setup_theme', 'raka_preorder_theme_setup' );

/**
 * Enqueue scripts and styles.
 */
function raka_preorder_theme_scripts() {
	// Enqueue main stylesheet (loads Kanit font rules)
	wp_enqueue_style( 'raka-preorder-theme-style', get_stylesheet_uri(), [], '1.0.0' );

	// Enqueue Obsidian theme core styles
	$obsidian_css_path = get_template_directory() . '/assets/css/obsidian.css';
	$obsidian_css_ver  = file_exists( $obsidian_css_path ) ? (string) filemtime( $obsidian_css_path ) : '1.0.0';
	wp_enqueue_style( 'raka-obsidian-style', get_template_directory_uri() . '/assets/css/obsidian.css', [], $obsidian_css_ver );

	// Enqueue Master CSS locally from the theme assets in the header
	wp_enqueue_script(
		'raka-theme-master-css',
		get_template_directory_uri() . '/assets/js/master-css.js',
		[],
		'2.0.0',
		false // Load in header
	);
}
add_action( 'wp_enqueue_scripts', 'raka_preorder_theme_scripts' );

/**
 * Dequeue Gutenberg global inline styles and WooCommerce stylesheets.
 */
function raka_preorder_theme_clean_styles() {
	// Dequeue WordPress global styles (global-styles-inline-css)
	wp_dequeue_style( 'global-styles' );

	// Dequeue WooCommerce front styles
	wp_dequeue_style( 'woocommerce-layout' );
	wp_dequeue_style( 'woocommerce-smallscreen' );
	wp_dequeue_style( 'woocommerce-general' );
	wp_dequeue_style( 'woocommerce_frontend_styles' );
}
add_action( 'wp_enqueue_scripts', 'raka_preorder_theme_clean_styles', 999 );
add_action( 'wp_print_styles', 'raka_preorder_theme_clean_styles', 999 );

// Remove block inline global styles action
remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
remove_action( 'wp_footer', 'wp_enqueue_global_styles', 1 );

/**
 * Include theme helpers, term meta, and theme options admin interface.
 */
require_once get_template_directory() . '/inc/helpers.php';
require_once get_template_directory() . '/inc/term-meta.php';
require_once get_template_directory() . '/inc/theme-options.php';

/**
 * Replace WooCommerce default pagination with OBSIDIAN Ultra-Minimal Pagination
 */
remove_action( 'woocommerce_after_shop_loop', 'woocommerce_pagination', 10 );
add_action( 'woocommerce_after_shop_loop', 'raka_render_woocommerce_pagination', 10 );

