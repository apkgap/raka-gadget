<?php
/**
 * Template Name: Login Standalone Hero
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

<main id="raka-hero-main" class="w-full min-h-screen lg:h-screen bg-white dark:bg-neutral-950 flex flex-col lg:flex-row overflow-x-hidden lg:overflow-hidden">
	
	<!-- COLUMN 1 (LEFT): The Content & Login Container -->
	<div id="raka-hero-left" class="w-full lg:w-[55%] xl:w-[58%] flex flex-col justify-between p-4 sm:p-6 md:p-8 lg:p-10 xl:p-12 min-h-screen lg:min-h-0 lg:overflow-y-auto">
		
		<!-- Modular Topbar Header -->
		<?php get_template_part( 'template-parts/header/standalone-topbar' ); ?>

		<!-- Stage Center -->
		<div id="raka-hero-stage" class="w-full max-w-md mx-auto my-auto py-6 flex flex-col items-center text-center">
			<div id="raka-stage-emblem" class="w-14 h-14 sm:w-16 sm:h-16 rounded-3xl bg-neutral-950 dark:bg-white p-3.5 mb-4 flex items-center justify-center shadow-lg">
				<img id="raka-stage-emblem-img" class="w-full h-full object-contain filter invert dark:invert-0" src="<?php echo esc_url( $logo_icon ); ?>" alt="Raka">
			</div>
			<h1 id="raka-hero-headline" class="text-2xl sm:text-3xl md:text-4xl font-light tracking-tight text-neutral-950 dark:text-white mb-2 leading-tight font-hn"><b>Sign In</b> to account<br />with <b>RAKA GADGET</b></h1>
			<svg id="raka-hero-squiggle" class="w-32 sm:w-44 h-auto text-neutral-950 dark:text-white mx-auto mb-6" viewBox="0 0 320 26" aria-hidden="true">
				<path d="M2 20C34 4 60 3 88 12s52 12 80 3 54-12 82-3 46 10 68 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" fill="none"/>
			</svg>

			<!-- Login Form Mount -->
			<div id="raka-stage-form-mount" class="w-full text-left">
				<?php echo do_shortcode( '[wc_custom_login]' ); ?>
			</div>
		</div>

		<!-- Spacer to keep layout balanced -->
		<div id="raka-stage-bottom-spacer" class="h-2 shrink-0"></div>
	</div>

	<!-- COLUMN 2 (RIGHT): Dedicated Video Column (Desktop Only) -->
	<?php get_template_part( 'template-parts/sections/standalone-video-column' ); ?>

</main>

<?php
get_footer( 'standalone' );
