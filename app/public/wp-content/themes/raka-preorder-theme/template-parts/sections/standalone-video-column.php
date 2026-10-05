<?php
/**
 * Standalone Video Column Component (Desktop).
 *
 * @package raka-preorder-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$video_url       = get_template_directory_uri() . '/assets/images/a9-air.mp4';
$logo_icon       = get_template_directory_uri() . '/assets/images/logo-raka-icon.png';
$shop_url        = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop' );
$current_user    = wp_get_current_user();
$is_logged_in    = is_user_logged_in();

if ( $is_logged_in ) {
	$myaccount_page_id = get_option( 'woocommerce_myaccount_page_id' );
	$account_url       = $myaccount_page_id ? get_permalink( $myaccount_page_id ) : home_url( '/my-account/' );
	$account_label     = ! empty( $current_user->display_name ) ? esc_html( $current_user->display_name ) : esc_html__( 'My Account', 'raka-preorder-theme' );
} else {
	$account_url   = home_url( '/login/' );
	$account_label = esc_html__( 'Login', 'raka-preorder-theme' );
}
?>
<!-- COLUMN 2 (RIGHT): Dedicated Video Column (No Overlap) -->
<div id="raka-hero-right" class="relative bg-black h-full w-full overflow-hidden hidden lg:flex items-center justify-center select-none">
	<video id="raka-hero-video" class="w-full h-full object-cover object-center opacity-90" autoplay muted loop playsinline preload="auto" poster="https://images.higgs.ai/?default=1&output=webp&url=https%3A%2F%2Fd8j0ntlcm91z4.cloudfront.net%2Fuser_38xzZboKViGWJOttwIXH07lWA1P%2Fhf_20260831_232824_93a0252b-e9f2-432f-9961-555a2e458100.png&w=1920&q=85">
		<source src="<?php echo esc_url( $video_url ); ?>" type="video/mp4">
	</video>

	<!-- Floating Controls on Top Right of Video -->
	<div id="raka-side-controls" class="absolute top-4 sm:top-5 right-4 sm:right-5 flex items-center gap-2.5 z-20">
		<a id="raka-explore-shop-btn" class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-white/90 dark:bg-neutral-900/90 backdrop-blur-md flex items-center justify-center text-neutral-900 dark:text-white shadow-lg hover:scale-105 transition-all cursor-pointer" href="<?php echo esc_url( $shop_url ); ?>" aria-label="Explore Shop">
			<svg class="w-5 h-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
				<path d="M12 4v11M7 11l5 5 5-5M5 20h14"/>
			</svg>
		</a>
		<a id="raka-side-account-btn" class="flex items-center gap-2.5 bg-white/90 dark:bg-neutral-900/90 backdrop-blur-md rounded-full py-1.5 pl-3.5 pr-1.5 shadow-lg hover:scale-105 transition-all cursor-pointer text-neutral-800 dark:text-neutral-200" href="<?php echo esc_url( $account_url ); ?>" aria-label="<?php echo esc_attr( $account_label ); ?>">
			<?php if ( $is_logged_in ) : ?>
				<svg class="w-3.5 h-3.5 shrink-0 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round">
					<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
					<circle cx="12" cy="7" r="4"/>
				</svg>
			<?php else : ?>
				<svg class="w-3.5 h-3.5 shrink-0 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round">
					<path d="M12 5v14M5 12h14"/>
				</svg>
			<?php endif; ?>
			<span id="raka-account-pill-label" class="text-xs sm:text-sm font-semibold text-neutral-600 dark:text-neutral-300"><?php echo esc_html( $account_label ); ?></span>
			<div id="raka-account-pill-avatar" class="w-7 h-7 sm:w-8 sm:h-8 rounded-full overflow-hidden bg-neutral-200 dark:bg-neutral-800 shrink-0 flex items-center justify-center">
				<?php if ( $is_logged_in && ! empty( $current_user->display_name ) ) : ?>
					<span class="text-[11px] font-bold text-neutral-900 dark:text-white uppercase"><?php echo esc_html( strtoupper( substr( $current_user->display_name, 0, 1 ) ) ); ?></span>
				<?php else : ?>
					<img id="raka-account-avatar-img" class="w-5 h-5 object-contain" src="<?php echo esc_url( $logo_icon ); ?>" alt="Account">
				<?php endif; ?>
			</div>
		</a>
	</div>

	<!-- Floating Bottom Cart FAB -->
	<a id="raka-side-cart-fab" class="absolute right-6 lg:right-10 bottom-6 lg:bottom-10 w-16 h-16 lg:w-20 lg:h-20 rounded-full bg-white dark:bg-neutral-900 text-neutral-950 dark:text-white flex items-center justify-center shadow-2xl hover:scale-110 transition-all cursor-pointer z-20" href="<?php echo esc_url( $shop_url ); ?>" aria-label="Shop Products">
		<i id="raka-side-cart-fab-icon" class="ri-shopping-bag-3-line text-2xl lg:text-3xl"></i>
	</a>
</div>
