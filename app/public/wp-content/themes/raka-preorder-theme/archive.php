<?php
/**
 * The template for displaying archive pages (Blog, Categories, Tags).
 *
 * @package raka-preorder-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$archive_title = get_the_archive_title();
$archive_desc  = get_the_archive_description();
?>

<main id="obsidian-archive-main" class="min-h-screen pt-28 md:pt-36 pb-24 bg-cement dark:bg-obsidian text-black dark:text-white transition-colors duration-300">
	<div id="obsidian-archive-container" class="container">

		<!-- ── Archive Header ── -->
		<div id="blog-archive-header" class="mb-12 md:mb-16 space-y-4 border-b border-black/10 dark:border-white/10 pb-8">
			<?php raka_render_breadcrumbs( 'blog-breadcrumbs', 'mb-2' ); ?>
			<h1 id="blog-title-heading" class="text-3xl sm:text-4xl md:text-5xl font-black uppercase tracking-tight text-neutral-900 dark:text-white font-hn">
				<?php echo wp_kses_post( $archive_title ); ?>
			</h1>
			<?php if ( $archive_desc ) : ?>
				<div id="blog-desc-text" class="text-sm text-neutral-500 dark:text-neutral-400 font-light max-w-xl font-hn leading-relaxed">
					<?php echo wp_kses_post( $archive_desc ); ?>
				</div>
			<?php endif; ?>
		</div>

		<!-- ── Posts Loop ── -->
		<?php if ( have_posts() ) : ?>
			<div id="blog-posts-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
				<?php
				while ( have_posts() ) :
					the_post();
					$p_id   = get_the_ID();
					$p_date = get_the_date( 'M d, Y' );
				?>
					<article id="post-card-<?php echo esc_attr( (string) $p_id ); ?>" class="group flex flex-col justify-between rounded-2xl bg-neutral-100 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800/80 p-6 transition-all duration-300 hover:scale-[1.02] hover:border-black dark:hover:border-white">
						<?php if ( has_post_thumbnail() ) : ?>
							<div id="post-thumb-wrap-<?php echo esc_attr( (string) $p_id ); ?>" class="w-full h-48 rounded-xl overflow-hidden mb-5">
								<?php the_post_thumbnail( 'medium_large', [ 'id' => 'post-thumb-' . $p_id, 'class' => 'w-full h-full object-cover transition-transform duration-500 group-hover:scale-105' ] ); ?>
							</div>
						<?php endif; ?>

						<div id="post-info-<?php echo esc_attr( (string) $p_id ); ?>" class="space-y-3">
							<span id="post-date-<?php echo esc_attr( (string) $p_id ); ?>" class="text-[11px] font-mono tracking-widest uppercase text-neutral-400 dark:text-neutral-500"><?php echo esc_html( $p_date ); ?></span>
							<h2 id="post-title-<?php echo esc_attr( (string) $p_id ); ?>" class="text-lg sm:text-xl font-bold uppercase tracking-tight text-neutral-900 dark:text-white leading-snug font-hn">
								<a id="post-link-<?php echo esc_attr( (string) $p_id ); ?>" href="<?php echo esc_url( get_permalink() ); ?>" class="hover:opacity-60 transition-opacity">
									<?php the_title(); ?>
								</a>
							</h2>
							<p id="post-excerpt-<?php echo esc_attr( (string) $p_id ); ?>" class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 font-light font-hn line-clamp-3 leading-relaxed">
								<?php echo wp_trim_words( get_the_excerpt(), 18 ); ?>
							</p>
						</div>

						<div id="post-footer-<?php echo esc_attr( (string) $p_id ); ?>" class="pt-4 mt-4 border-t border-black/5 dark:border-white/5 flex items-center justify-between text-xs font-mono">
							<a id="post-read-<?php echo esc_attr( (string) $p_id ); ?>" href="<?php echo esc_url( get_permalink() ); ?>" class="inline-flex items-center gap-2 uppercase tracking-widest text-neutral-900 dark:text-white font-semibold group-hover:opacity-60">
								<span>READ ARTICLE</span>
								<i id="post-icon-<?php echo esc_attr( (string) $p_id ); ?>" class="ri-arrow-right-line text-sm transition-transform group-hover:translate-x-1"></i>
							</a>
						</div>
					</article>
				<?php endwhile; ?>
			</div>

			<!-- Minimal Pagination -->
			<div id="blog-pagination-container" class="mt-12">
				<?php raka_render_pagination(); ?>
			</div>
		<?php else : ?>
			<div id="blog-no-posts" class="py-24 text-center space-y-4">
				<i id="icon-empty-blog" class="ri-article-line text-4xl text-neutral-400"></i>
				<h3 id="no-posts-title" class="text-xl font-bold uppercase tracking-wider font-hn">No posts found</h3>
				<p id="no-posts-desc" class="text-xs text-neutral-400 font-mono">Articles and releases will be published soon.</p>
			</div>
		<?php endif; ?>

	</div>
</main>

<?php
get_footer();
