<?php
/**
 * Template Name: Pre-Order Standalone Hero
 *
 * @package raka-preorder-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header( 'standalone' );
?>

<section id="hero" class="relative w-full min-h-[100svh] isolate bg-white dark:bg-[#111111] grid grid-cols-1 lg:grid-cols-[minmax(0,1.15fr)_minmax(0,0.85fr)] lg:h-[100svh] lg:overflow-hidden">
	
	<!-- COLUMN 1 (LEFT): The Content & Pre-Order Tracker Container -->
	<div id="raka-hero-left" class="relative bg-white dark:bg-[#111111] px-3.5 sm:px-6 lg:px-9 pt-2 sm:pt-4 pb-14 sm:pb-8 flex flex-col justify-start h-full min-h-0 overflow-y-auto lg:border-r border-neutral-200 dark:border-neutral-800 overscroll-contain">
		
		<!-- Modular Topbar Header -->
		<?php get_template_part( 'template-parts/header/standalone-topbar' ); ?>

		<!-- Stage Center -->
		<div id="raka-hero-stage" class="flex-1 min-h-0 flex flex-col items-center justify-start text-center relative py-1 sm:py-3 w-full h-full">
			<h1 id="raka-hero-headline" class="mb-2 sm:mb-3 text-2xl sm:text-3xl lg:text-4xl leading-tight font-extrabold tracking-wider uppercase text-neutral-950 dark:text-white">TRACKING</h1>
			
			<!-- Pre-Order Form Mount (V2) -->
			<div id="raka-stage-form-mount" class="flex-1 flex flex-col w-full max-w-full mx-auto text-left min-h-0 h-full">
				<?php echo do_shortcode( '[preorder_raka_v2]' ); ?>
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
