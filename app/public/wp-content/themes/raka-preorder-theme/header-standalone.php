<?php
/**
 * The Standalone Header template for Pre-Order, Login, and Register pages.
 *
 * @package raka-preorder-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!DOCTYPE html>
<html id="raka-standalone-html" <?php language_attributes(); ?> class="light">
<head id="raka-standalone-head">
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<title><?php wp_title( '|', true, 'right' ); ?> <?php bloginfo( 'name' ); ?></title>
	
	<!-- RemixIcon -->
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.6.0/fonts/remixicon.css">
	
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

	<?php wp_head(); ?>
</head>
<body id="raka-standalone-body" <?php body_class( 'bg-white dark:bg-neutral-950 text-neutral-900 dark:text-neutral-100 antialiased min-h-screen overflow-x-hidden' ); ?>>
<?php wp_body_open(); ?>
