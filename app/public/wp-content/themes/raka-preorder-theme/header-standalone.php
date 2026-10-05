<?php
/**
 * The Standalone Header template for Pre-Order, Login, and Register pages.
 *
 * @package raka-preorder-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$hero_css_path = get_template_directory() . '/assets/css/standalone-hero.css';
$hero_css_ver  = file_exists( $hero_css_path ) ? (string) filemtime( $hero_css_path ) : '1.0.0';
$hero_js_path  = get_template_directory() . '/assets/js/standalone-hero.js';
$hero_js_ver   = file_exists( $hero_js_path ) ? (string) filemtime( $hero_js_path ) : '1.0.0';
?>
<!DOCTYPE html>
<html id="raka-standalone-html" <?php language_attributes(); ?> class="light">
<head id="raka-standalone-head">
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<title><?php wp_title( '|', true, 'right' ); ?> <?php bloginfo( 'name' ); ?></title>
	
	<!-- RemixIcon -->
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.6.0/fonts/remixicon.css">
	
	<!-- Standalone Hero Dedicated Stylesheet -->
	<link rel="stylesheet" href="<?php echo esc_url( get_template_directory_uri() . '/assets/css/standalone-hero.css?ver=' . $hero_css_ver ); ?>">

	<script id="raka-theme-instant-init">
		(function() {
			try {
				var theme = localStorage.getItem('raka_theme') || localStorage.getItem('obsidian_theme');
				if (theme === 'dark' || (!theme && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
					document.documentElement.classList.add('dark');
					document.documentElement.classList.remove('light');
				} else {
					document.documentElement.classList.add('light');
					document.documentElement.classList.remove('dark');
				}
			} catch(e) {}
		})();
	</script>

	<!-- Standalone Hero Dedicated JS Script -->
	<script src="<?php echo esc_url( get_template_directory_uri() . '/assets/js/standalone-hero.js?ver=' . $hero_js_ver ); ?>" defer></script>

	<?php wp_head(); ?>
</head>
<body id="raka-standalone-body" <?php body_class( 'mindora-preorder-body' ); ?>>
<?php wp_body_open(); ?>
