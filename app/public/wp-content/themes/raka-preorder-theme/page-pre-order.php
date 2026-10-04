<?php
/**
 * Template Name: Pre-Order Hero & Tracking Center
 * The template for displaying the Pre-Order page with central tracking form.
 *
 * @package raka-preorder-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<style>
	@keyframes fade-scale {
		from { opacity: 0; transform: scale(0.96); }
		to   { opacity: 1; transform: scale(1); }
	}

	.animate-fade-scale { animation: fade-scale 0.7s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
</style>

<!-- HERO BANNER SECTION (CINEMATIC VIDEO BACKGROUND) -->
<section id="raka-preorder-banner-section" class="relative w-full min-h-[calc(100vh-70px)] flex items-center justify-center overflow-hidden bg-[#080A19] py-8 sm:py-12 md:py-16 px-4 sm:px-6 md:px-8">

	<!-- 1. Background Video Layer -->
	<video
		id="raka-preorder-banner-video"
		class="absolute inset-0 w-full h-full object-cover"
		src="https://d8j0ntlcm91z4.cloudfront.net/user_38xzZboKViGWJOttwIXH07lWA1P/hf_20260813_092641_de52eb87-daf2-41db-92cb-7a56eae012a5.mp4"
		autoplay
		loop
		muted
		playsinline
	></video>

	<!-- 2. Dark Atmospheric Glass Overlay -->
	<div id="raka-preorder-banner-overlay" class="absolute inset-0 bg-gradient-to-b from-black/70 via-black/50 to-black/85 pointer-events-none"></div>

	<!-- 3. Center Form Wrapper -->
	<div id="raka-preorder-center-wrapper" class="relative z-10 w-full max-w-2xl mx-auto flex flex-col items-center justify-center">

		<!-- Center Pre-Order Form Mount -->
		<div id="raka-preorder-form-mount" class="opacity-0 animate-fade-scale w-full text-left" style="animation-delay: 150ms;">
			<div id="raka-preorder-form-card" class="rounded-3xl shadow-2xl shadow-black/80 overflow-hidden backdrop-blur-2xl">
				<?php echo do_shortcode( '[preorder_raka]' ); ?>
			</div>
		</div>

	</div>

</section>

<?php
get_footer();
