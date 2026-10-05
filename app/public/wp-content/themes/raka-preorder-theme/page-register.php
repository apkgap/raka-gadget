<?php
/**
 * Template Name: Register Standalone Hero
 *
 * @package raka-preorder-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( is_user_logged_in() ) {
	$myaccount_page_id = get_option( 'woocommerce_myaccount_page_id' );
	$myaccount_url     = $myaccount_page_id ? get_permalink( $myaccount_page_id ) : home_url( '/account' );
	wp_safe_redirect( $myaccount_url );
	exit;
}

$logo_icon = get_template_directory_uri() . '/assets/images/logo-raka-icon.png';

get_header( 'standalone' );
?>

<section id="hero" class="hero">
	
	<!-- COLUMN 1 (LEFT): The Content & Register Container -->
	<div id="raka-hero-left" class="hero__left panel">
		
		<!-- Modular Topbar Header -->
		<?php get_template_part( 'template-parts/header/standalone-topbar' ); ?>

		<!-- Stage Center -->
		<div id="raka-hero-stage" class="stage">
			<div id="raka-stage-emblem" class="stage__emblem">
				<img id="raka-stage-emblem-img" src="<?php echo esc_url( $logo_icon ); ?>" alt="Raka">
			</div>
			<h1 id="raka-hero-headline" class="headline"><b>Join Us</b> create account<br />with <b>RAKA GADGET</b></h1>
			<svg id="raka-hero-squiggle" class="squiggle" viewBox="0 0 320 26" aria-hidden="true">
				<path d="M2 20C34 4 60 3 88 12s52 12 80 3 54-12 82-3 46 10 68 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" fill="none"/>
			</svg>

			<!-- Register Form Mount -->
			<div id="raka-stage-form-mount" class="stage__form-mount">
				<?php echo do_shortcode( '[wc_custom_register]' ); ?>
			</div>
		</div>

		<!-- Spacer -->
		<div id="raka-stage-bottom-spacer" class="h-2 shrink-0"></div>
	</div>

	<!-- COLUMN 2 (RIGHT): Dedicated Video Column (Desktop Only) -->
	<?php get_template_part( 'template-parts/sections/standalone-video-column' ); ?>

</section>

<?php
get_footer( 'standalone' );
