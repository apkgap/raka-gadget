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

<!-- HERO BANNER SECTION (CLEAN AMBIENT BACKGROUND — NO BG VIDEO) -->
<section id="raka-preorder-banner-section" class="relative w-full min-h-[calc(100vh-70px)] flex items-center justify-center overflow-hidden bg-neutral-950 dark:bg-black py-8 sm:py-12 md:py-16 px-4 sm:px-6 md:px-8">

	<!-- Subtle Ambient Glow -->
	<div id="raka-preorder-ambient-glow" class="absolute -top-40 left-1/2 -translate-x-1/2 w-[700px] h-[500px] bg-red-600/10 dark:bg-red-600/15 rounded-full blur-[140px] pointer-events-none"></div>

	<!-- Center Form Wrapper -->
	<div id="raka-preorder-center-wrapper" class="relative z-10 w-full max-w-3xl mx-auto flex flex-col items-center justify-center">

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
