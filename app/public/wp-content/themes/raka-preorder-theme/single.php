<?php
/**
 * The template for displaying all single posts (Blog).
 *
 * @package raka-preorder-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	$p_id   = get_the_ID();
	$p_date = get_the_date( 'M d, Y' );
?>

<main id="single-post-main" class="min-h-screen pt-28 md:pt-36 pb-24 bg-cement dark:bg-obsidian text-black dark:text-white transition-colors duration-300 font-hn">
	<div id="single-post-container" class="max-w-4xl mx-auto px-6 sm:px-8">

		<!-- ── Breadcrumbs ── -->
		<nav id="post-breadcrumbs-nav" class="flex items-center gap-2 text-xs uppercase tracking-[0.2em] text-neutral-400 dark:text-neutral-500 mb-8 font-mono">
			<a id="crumb-blog-home" href="<?php echo esc_url( home_url( '/' ) ); ?>" class="hover:text-black dark:hover:text-white transition-colors">HOME</a>
			<span id="crumb-blog-sep-1" class="opacity-40">/</span>
			<a id="crumb-blog-archive" href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ?: home_url( '/blog' ) ); ?>" class="hover:text-black dark:hover:text-white transition-colors">JOURNAL</a>
			<span id="crumb-blog-sep-2" class="opacity-40">/</span>
			<span id="crumb-blog-current" class="text-neutral-900 dark:text-neutral-200 truncate max-w-xs"><?php the_title(); ?></span>
		</nav>

		<article id="post-article-<?php echo esc_attr( (string) $p_id ); ?>" class="space-y-8">
			<!-- Header -->
			<header id="post-article-header-<?php echo esc_attr( (string) $p_id ); ?>" class="space-y-4 border-b border-black/10 dark:border-white/10 pb-8">
				<div id="post-meta-tag-<?php echo esc_attr( (string) $p_id ); ?>" class="flex items-center gap-3 text-xs font-mono text-neutral-400 dark:text-neutral-500">
					<span><?php echo esc_html( $p_date ); ?></span>
					<span>//</span>
					<span><?php echo esc_html( get_the_author() ); ?></span>
				</div>
				<h1 id="post-title-<?php echo esc_attr( (string) $p_id ); ?>" class="text-3xl sm:text-4xl md:text-5xl font-black uppercase tracking-tight text-neutral-900 dark:text-white leading-tight">
					<?php the_title(); ?>
				</h1>
			</header>

			<!-- Featured Image -->
			<?php if ( has_post_thumbnail() ) : ?>
				<div id="post-featured-wrap-<?php echo esc_attr( (string) $p_id ); ?>" class="w-full rounded-2xl overflow-hidden border border-neutral-200 dark:border-neutral-800">
					<?php the_post_thumbnail( 'full', [ 'id' => 'post-feat-img-' . $p_id, 'class' => 'w-full max-h-[500px] object-cover' ] ); ?>
				</div>
			<?php endif; ?>

			<!-- Content -->
			<div id="post-article-content-<?php echo esc_attr( (string) $p_id ); ?>" class="entry-content text-base sm:text-lg text-neutral-800 dark:text-neutral-200 font-light leading-relaxed space-y-6">
				<?php the_content(); ?>
			</div>
		</article>

	</div>
</main>

<?php
endwhile;

get_footer();
