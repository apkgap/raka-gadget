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

<main id="raka-hero-main" class="w-full min-h-screen lg:h-screen bg-white dark:bg-neutral-950 flex flex-col lg:flex-row overflow-x-hidden lg:overflow-hidden">
	
	<!-- COLUMN 1 (LEFT): The Content & Pre-Order Tracker Container -->
	<div id="raka-hero-left" class="w-full lg:w-[55%] xl:w-[58%] flex flex-col justify-between p-4 sm:p-6 md:p-8 lg:p-10 xl:p-12 min-h-screen lg:min-h-0 lg:overflow-y-auto">
		
		<!-- Modular Topbar Header -->
		<?php get_template_part( 'template-parts/header/standalone-topbar' ); ?>

		<!-- Stage Center -->
		<div id="raka-hero-stage" class="w-full max-w-2xl mx-auto my-auto py-6 flex flex-col items-center text-center">
			<h1 id="raka-hero-headline" class="text-3xl sm:text-4xl md:text-5xl font-black tracking-wider uppercase text-neutral-950 dark:text-white mb-6 font-hn">TRACKING</h1>
			
			<!-- Pre-Order Form Mount (V2) -->
			<div id="raka-stage-form-mount" class="w-full text-left">
				<?php echo do_shortcode( '[preorder_raka_v2]' ); ?>
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
