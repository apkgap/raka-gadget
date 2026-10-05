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
<!-- COLUMN 2 (RIGHT): Dedicated Video Column (Desktop Only) -->
<div id="raka-hero-right" class="relative hidden lg:block lg:w-[45%] xl:w-[42%] h-full shrink-0 overflow-hidden bg-neutral-900 select-none">
	<video id="raka-hero-video" class="absolute inset-0 w-full h-full object-cover" autoplay muted loop playsinline preload="auto" poster="https://images.higgs.ai/?default=1&output=webp&url=https%3A%2F%2Fd8j0ntlcm91z4.cloudfront.net%2Fuser_38xzZboKViGWJOttwIXH07lWA1P%2Fhf_20260831_232824_93a0252b-e9f2-432f-9961-555a2e458100.png&w=1920&q=85">
		<source src="<?php echo esc_url( $video_url ); ?>" type="video/mp4">
	</video>

	<!-- Floating Controls on Top Right of Video -->
	<div id="raka-side-controls" class="absolute top-6 right-6 z-20 flex items-center gap-3">
		<a id="raka-explore-shop-btn" class="w-11 h-11 rounded-full bg-black/40 hover:bg-black/60 backdrop-blur-md border border-white/20 text-white flex items-center justify-center transition-all duration-300 hover:scale-105" href="<?php echo esc_url( $shop_url ); ?>" aria-label="Explore Shop">
			<svg id="raka-explore-shop-svg" class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
				<path d="M12 4v11M7 11l5 5 5-5M5 20h14"/>
			</svg>
		</a>
		<a id="raka-side-account-btn" class="flex items-center gap-2.5 py-1.5 pl-3.5 pr-1.5 rounded-full bg-black/40 hover:bg-black/60 backdrop-blur-md border border-white/20 text-white transition-all duration-300 text-xs font-semibold hover:scale-105" href="<?php echo esc_url( $account_url ); ?>" aria-label="<?php echo esc_attr( $account_label ); ?>">
			<?php if ( $is_logged_in ) : ?>
				<svg id="raka-side-account-svg" class="w-3.5 h-3.5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
					<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
					<circle cx="12" cy="7" r="4"/>
				</svg>
			<?php else : ?>
				<svg id="raka-side-account-svg" class="w-3.5 h-3.5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
					<path d="M12 5v14M5 12h14"/>
				</svg>
			<?php endif; ?>
			<span id="raka-account-pill-label" class="font-medium tracking-wide"><?php echo esc_html( $account_label ); ?></span>
			<div id="raka-account-pill-avatar" class="w-8 h-8 rounded-full bg-white text-black flex items-center justify-center overflow-hidden shrink-0 font-bold text-xs uppercase">
				<?php if ( $is_logged_in && ! empty( $current_user->display_name ) ) : ?>
					<span><?php echo esc_html( strtoupper( substr( $current_user->display_name, 0, 1 ) ) ); ?></span>
				<?php else : ?>
					<img id="raka-account-avatar-img" class="w-full h-full object-cover" src="<?php echo esc_url( $logo_icon ); ?>" alt="Account">
				<?php endif; ?>
			</div>
		</a>
	</div>

	<!-- Bottom-Right FAB -->
	<a id="raka-side-cart-fab" class="absolute bottom-6 right-6 z-20 w-12 h-12 rounded-full bg-white dark:bg-neutral-900 text-neutral-950 dark:text-white shadow-2xl flex items-center justify-center border border-black/10 dark:border-white/20 transition-all duration-300 hover:scale-110" href="<?php echo esc_url( $shop_url ); ?>" aria-label="Shop Products">
		<i id="raka-side-cart-fab-icon" class="ri-shopping-bag-3-line text-xl"></i>
	</a>
</div>
