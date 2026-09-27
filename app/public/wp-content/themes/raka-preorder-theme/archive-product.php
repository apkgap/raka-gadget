<?php
/**
 * The template for displaying product archives (Shop, Categories, Brands).
 *
 * @package raka-preorder-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$current_term = get_queried_object();
$archive_title = is_a( $current_term, 'WP_Term' ) ? $current_term->name : 'All Products';
$archive_desc  = is_a( $current_term, 'WP_Term' ) ? $current_term->description : 'Discover official esports hardware, wireless mice, magnetic keyboards, and archival collectibles.';

$all_cats = get_terms( [
	'taxonomy'   => 'product_cat',
	'hide_empty' => true,
] );
?>

<main id="archive-product-main" class="min-h-screen pt-28 md:pt-36 pb-24 bg-cement dark:bg-obsidian text-black dark:text-white transition-colors duration-300">
	<div id="archive-product-container" class="max-w-7xl mx-auto px-3.5 sm:px-8 md:px-12 lg:px-16">

		<!-- ── Archive Header ── -->
		<div id="archive-header-block" class="mb-12 md:mb-16 space-y-4 border-b border-black/10 dark:border-white/10 pb-8 md:pb-12">
			<?php raka_render_breadcrumbs( 'archive-breadcrumbs', 'mb-2' ); ?>
			<div id="archive-title-wrap" class="flex flex-col md:flex-row md:items-end justify-between gap-6">
				<h1 id="archive-title-heading" class="text-4xl sm:text-5xl md:text-6xl font-black uppercase tracking-tight text-neutral-900 dark:text-white font-hn">
					<?php echo esc_html( $archive_title ); ?>
				</h1>
				<p id="archive-desc-text" class="text-xs sm:text-sm text-neutral-500 dark:text-neutral-400 font-light max-w-md font-hn leading-relaxed">
					<?php echo esc_html( $archive_desc ?: 'Esports tournament gear, Hall Effect magnetic keyboards, and ultra-lightweight wireless mice.' ); ?>
				</p>
			</div>

			<!-- Category Filter Pills -->
			<?php if ( ! empty( $all_cats ) && ! is_wp_error( $all_cats ) ) : ?>
				<div id="archive-filter-pills" class="flex flex-wrap gap-2.5 pt-4">
					<a
						id="filter-pill-all"
						href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop' ) ); ?>"
						class="px-4 py-2 rounded-full text-xs uppercase tracking-wider font-mono transition-colors <?php echo ! is_tax( 'product_cat' ) ? 'bg-black text-white dark:bg-white dark:text-black font-semibold' : 'bg-neutral-100 dark:bg-neutral-900 text-neutral-700 dark:text-neutral-300 hover:opacity-70'; ?>"
					>
						ALL
					</a>
					<?php foreach ( $all_cats as $cat_term ) :
						$is_active_cat = ( is_tax( 'product_cat', $cat_term->term_id ) );
						$pill_cls = $is_active_cat
							? 'bg-black text-white dark:bg-white dark:text-black font-semibold'
							: 'bg-neutral-100 dark:bg-neutral-900 text-neutral-700 dark:text-neutral-300 hover:opacity-70';
					?>
						<a
							id="filter-pill-<?php echo esc_attr( $cat_term->slug ); ?>"
							href="<?php echo esc_url( get_term_link( $cat_term ) ); ?>"
							class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-xs uppercase tracking-wider font-mono transition-colors <?php echo esc_attr( $pill_cls ); ?>"
						>
							<?php
							if ( function_exists( 'raka_get_term_icon_html' ) ) {
								echo raka_get_term_icon_html( $cat_term->term_id, 'text-xs' );
							}
							?>
							<span><?php echo esc_html( $cat_term->name ); ?></span>
							<span class="opacity-50 text-[10px]">(<?php echo (int) $cat_term->count; ?>)</span>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>

		<!-- ── Products Grid (2 Columns on Mobile, 3 on Tablet, 4 on Desktop) ── -->
		<?php if ( have_posts() ) : ?>
			<div id="archive-products-grid" class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-2.5 sm:gap-6 lg:gap-8">
				<?php
				while ( have_posts() ) :
					the_post();
					global $product;
					if ( ! is_a( $product, 'WC_Product' ) ) {
						$product = wc_get_product( get_the_ID() );
					}
					raka_render_product_card( $product );
				endwhile;
				?>
			</div>

			<!-- Minimal Pagination -->
			<div id="archive-pagination-container" class="mt-12">
				<?php raka_render_pagination(); ?>
			</div>
		<?php else : ?>
			<div id="archive-no-products" class="py-24 text-center space-y-4">
				<i id="icon-empty-box" class="ri-inbox-line text-4xl text-neutral-400"></i>
				<h3 id="no-products-title" class="text-xl font-bold uppercase tracking-wider font-hn">No products found</h3>
				<p id="no-products-desc" class="text-xs text-neutral-400 font-mono">Check back soon or explore other categories.</p>
			</div>
		<?php endif; ?>

	</div>
</main>

<?php
get_footer();
