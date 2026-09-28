<?php
/**
 * The main template file (Fallback Template)
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (along with style.css).
 * It is used to display a page when nothing more specific matches a query.
 *
 * Note: The main homepage of the site is served by front-page.php.
 *
 * @package raka-preorder-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="raka-main-content" class="min-h-screen pt-28 pb-20 px-4 sm:px-6 lg:px-8 bg-neutral-50 dark:bg-neutral-950 text-neutral-900 dark:text-white transition-colors duration-300">
	<div id="raka-index-container" class="max-w-7xl mx-auto">
		<?php if ( have_posts() ) : ?>
			<header id="raka-index-header" class="mb-12 border-b border-neutral-200 dark:border-neutral-800 pb-6">
				<h1 id="raka-index-title" class="text-3xl sm:text-4xl font-black font-hn tracking-tight text-neutral-950 dark:text-white">
					<?php esc_html_e( 'Latest Articles & Updates', 'raka-preorder-theme' ); ?>
				</h1>
			</header>

			<div id="raka-index-posts-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<article id="post-<?php the_ID(); ?>" <?php post_class( 'group bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-2xl p-6 hover:border-red-500/50 dark:hover:border-red-500/50 transition-all duration-300 flex flex-col justify-between' ); ?>>
						<div id="post-body-<?php the_ID(); ?>" class="space-y-3">
							<div id="post-meta-<?php the_ID(); ?>" class="text-[11px] font-mono uppercase tracking-widest text-neutral-400 dark:text-neutral-500">
								<?php echo esc_html( get_the_date() ); ?>
							</div>
							<h2 id="post-title-<?php the_ID(); ?>" class="text-lg font-bold font-hn text-neutral-900 dark:text-white group-hover:text-red-600 dark:group-hover:text-red-400 transition-colors">
								<a id="post-link-<?php the_ID(); ?>" href="<?php the_permalink(); ?>">
									<?php the_title(); ?>
								</a>
							</h2>
							<div id="post-excerpt-<?php the_ID(); ?>" class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 font-light font-hn line-clamp-3">
								<?php the_excerpt(); ?>
							</div>
						</div>
						<div id="post-footer-<?php the_ID(); ?>" class="pt-6 mt-6 border-t border-neutral-100 dark:border-neutral-800 flex items-center justify-between">
							<a id="post-readmore-<?php the_ID(); ?>" href="<?php the_permalink(); ?>" class="inline-flex items-center gap-1.5 text-xs font-mono font-bold uppercase text-red-600 dark:text-red-400 hover:underline">
								<span><?php esc_html_e( 'Read Article', 'raka-preorder-theme' ); ?></span>
								<i id="post-readmore-ico-<?php the_ID(); ?>" class="ri-arrow-right-line text-xs"></i>
							</a>
						</div>
					</article>
				<?php endwhile; ?>
			</div>

			<div id="raka-index-pagination" class="mt-12 flex justify-center">
				<?php the_posts_pagination(); ?>
			</div>
		<?php else : ?>
			<div id="raka-no-posts" class="text-center py-20 bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-3xl p-8 max-w-xl mx-auto space-y-4">
				<div id="raka-no-posts-ico-wrap" class="w-16 h-16 mx-auto rounded-2xl bg-red-500/10 text-red-500 flex items-center justify-center text-2xl">
					<i id="raka-no-posts-ico" class="ri-file-text-line"></i>
				</div>
				<h2 id="raka-no-posts-title" class="text-xl font-bold font-hn text-neutral-900 dark:text-white">
					<?php esc_html_e( 'No Posts Found', 'raka-preorder-theme' ); ?>
				</h2>
				<p id="raka-no-posts-desc" class="text-xs sm:text-sm text-neutral-500 dark:text-neutral-400 font-light max-w-md mx-auto font-hn">
					<?php esc_html_e( 'There are currently no articles or blog posts published in this section.', 'raka-preorder-theme' ); ?>
				</p>
				<a id="raka-back-home-btn" href="<?php echo esc_url( home_url( '/' ) ); ?>" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-neutral-900 dark:bg-white text-white dark:text-neutral-900 font-mono text-xs font-bold uppercase tracking-wider hover:bg-red-600 dark:hover:bg-red-600 dark:hover:text-white transition-colors">
					<i id="raka-back-home-ico" class="ri-home-4-line"></i>
					<span><?php esc_html_e( 'Return to Homepage', 'raka-preorder-theme' ); ?></span>
				</a>
			</div>
		<?php endif; ?>
	</div>
</main>

<?php
get_footer();
