<?php
/**
 * Single Pre-order React Mounting Template.
 *
 * @package preorder-raka
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$post_id = get_the_ID();
?>

<!-- HERO BANNER SECTION (CINEMATIC VIDEO BACKGROUND) -->
<section id="raka-preorder-single-banner" class="relative w-full min-h-[calc(100vh-70px)] flex items-center justify-center overflow-hidden bg-[#080A19] py-12 sm:py-16 md:py-20 px-4 sm:px-6 md:px-8">

	<!-- 1. Background Video Layer -->
	<video
		id="raka-preorder-single-video"
		class="absolute inset-0 w-full h-full object-cover"
		src="https://d8j0ntlcm91z4.cloudfront.net/user_38xzZboKViGWJOttwIXH07lWA1P/hf_20260813_092641_de52eb87-daf2-41db-92cb-7a56eae012a5.mp4"
		autoplay
		loop
		muted
		playsinline
	></video>

	<!-- 2. Dark Atmospheric Glass Overlay -->
	<div id="raka-preorder-single-overlay" class="absolute inset-0 bg-gradient-to-b from-black/70 via-black/50 to-black/85 pointer-events-none"></div>

	<!-- 3. Center Form & Content Wrapper -->
	<div id="raka-preorder-single-wrapper" class="relative z-10 w-full max-w-4xl mx-auto flex flex-col items-center text-center">

		<!-- Mount PreOrderApp with data-post-id -->
		<div id="preorder-raka-single-app" class="w-full max-w-2xl mx-auto text-left rounded-3xl shadow-2xl shadow-black/80 overflow-hidden backdrop-blur-2xl" data-post-id="<?php echo esc_attr( (string) $post_id ); ?>">
			<div id="preorder-raka-loading-box" class="flex flex-col items-center justify-center py-24 text-slate-400">
				<div id="preorder-raka-loading-spinner" class="relative w-12 h-12 animate-spin mb-4" aria-hidden="true">
					<i class="ri-loader-4-line text-4xl text-rose-500"></i>
				</div>
				<span id="preorder-raka-loading-text" class="text-sm font-medium tracking-wider">กำลังโหลดข้อมูลสถานะพรีออเดอร์...</span>
			</div>
		</div>

	</div>

</section>

<?php
get_footer();
