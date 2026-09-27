<?php
/**
 * The template for displaying all single pages.
 *
 * @package raka-preorder-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main id="raka-site-main" class="min-h-screen pt-6 md:pt-10 pb-16 px-6 md:px-12 lg:px-16 text-inherit font-hn">
	<div id="raka-page-container" class="max-w-5xl mx-auto">
		<?php
		if ( have_posts() ) :
			while ( have_posts() ) :
				the_post();
				?>
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<header class="entry-header mb-8">
						<h1 class="text-3xl md:text-5xl font-black uppercase tracking-tight text-black dark:text-white"><?php the_title(); ?></h1>
					</header>
					<div class="entry-content leading-relaxed text-base md:text-lg text-black/85 dark:text-white/85">
						<?php the_content(); ?>
					</div>
				</article>
				<?php
			endwhile;
		endif;
		?>
	</div>
</main>
<?php
get_footer();
