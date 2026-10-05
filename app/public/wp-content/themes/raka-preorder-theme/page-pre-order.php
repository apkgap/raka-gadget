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

<section id="hero" class="hero">
	
	<!-- COLUMN 1 (LEFT): The Content & Pre-Order Tracker Container -->
	<div id="raka-hero-left" class="hero__left panel">
		
		<!-- Modular Topbar Header -->
		<?php get_template_part( 'template-parts/header/standalone-topbar' ); ?>

		<!-- Stage Center -->
		<div id="raka-hero-stage" class="stage">
			<h1 id="raka-hero-headline" class="headline font-extrabold tracking-wide uppercase">TRACKING</h1>
			
			<!-- Pre-Order Form Mount (V2) -->
			<div id="raka-stage-form-mount" class="stage__form-mount">
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
