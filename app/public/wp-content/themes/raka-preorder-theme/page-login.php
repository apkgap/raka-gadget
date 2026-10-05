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

<section id="hero" class="relative w-full min-h-[100svh] isolate bg-white dark:bg-[#111111] grid grid-cols-1 lg:grid-cols-[minmax(0,1.15fr)_minmax(0,0.85fr)] lg:h-[100svh] lg:overflow-hidden">
	
	<!-- COLUMN 1 (LEFT): The Content & Login Container -->
	<div id="raka-hero-left" class="relative bg-white dark:bg-[#111111] px-3.5 sm:px-6 lg:px-9 pt-2 sm:pt-4 pb-14 sm:pb-8 flex flex-col justify-start h-full min-h-0 overflow-y-auto lg:border-r border-neutral-200 dark:border-neutral-800 overscroll-contain">
		
		<!-- Modular Topbar Header -->
		<?php get_template_part( 'template-parts/header/standalone-topbar' ); ?>

		<!-- Stage Center -->
		<div id="raka-hero-stage" class="flex-1 min-h-0 flex flex-col items-center justify-start text-center relative py-1 sm:py-3 w-full h-full">
			<div id="raka-stage-emblem" class="w-12 h-12 sm:w-16 sm:h-16 rounded-full bg-neutral-100 dark:bg-neutral-800/80 border border-neutral-200 dark:border-neutral-700/80 mb-2 sm:mb-3 shadow-md flex-none inline-flex items-center justify-center">
				<img id="raka-stage-emblem-img" class="w-7 h-7 sm:w-9 sm:h-9 object-contain dark:brightness-0 dark:invert" src="<?php echo esc_url( $logo_icon ); ?>" alt="Raka">
			</div>
			<h1 id="raka-hero-headline" class="mb-2 sm:mb-3 text-2xl sm:text-3xl lg:text-4xl leading-tight font-light text-neutral-900 dark:text-white"><b>Sign In</b> to account<br />with <b>RAKA GADGET</b></h1>
			<svg id="raka-hero-squiggle" class="w-32 sm:w-44 h-auto text-neutral-900 dark:text-neutral-200 mx-auto mb-3 sm:mb-5 block" viewBox="0 0 320 26" aria-hidden="true">
				<path d="M2 20C34 4 60 3 88 12s52 12 80 3 54-12 82-3 46 10 68 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" fill="none"/>
			</svg>

			<!-- Login Form Mount -->
			<div id="raka-stage-form-mount" class="flex-1 flex flex-col w-full max-w-full mx-auto text-left min-h-0 h-full">
				<?php echo do_shortcode( '[wc_custom_login]' ); ?>
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
