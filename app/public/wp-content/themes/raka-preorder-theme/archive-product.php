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
	<div id="archive-product-container" class="max-w-7xl mx-auto px-6 sm:px-8 md:px-12 lg:px-16">

		<!-- ── Archive Header ── -->
		<div id="archive-header-block" class="mb-12 md:mb-16 space-y-4 border-b border-black/10 dark:border-white/10 pb-8 md:pb-12">
			<span id="archive-sub-tag" class="text-xs uppercase tracking-[0.3em] font-mono text-neutral-400 dark:text-neutral-500">// CATALOGUE</span>
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
							class="px-4 py-2 rounded-full text-xs uppercase tracking-wider font-mono transition-colors <?php echo esc_attr( $pill_cls ); ?>"
						>
							<?php echo esc_html( $cat_term->name ); ?> (<?php echo (int) $cat_term->count; ?>)
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>

		<!-- ── Products Grid ── -->
		<?php if ( have_posts() ) : ?>
			<div id="archive-products-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 sm:gap-8">
				<?php
				while ( have_posts() ) :
					the_post();
					global $product;
					if ( ! is_a( $product, 'WC_Product' ) ) {
						$product = wc_get_product( get_the_ID() );
					}
					$p_id      = $product->get_id();
					$p_title   = $product->get_name();
					$p_price   = $product->get_price_html();
					$p_img_id  = $product->get_image_id();
					$p_img_url = $p_img_id ? wp_get_attachment_image_url( $p_img_id, 'medium_large' ) : wc_placeholder_img_src( 'medium_large' );
					$p_link    = get_permalink( $p_id );

					$p_brands = wp_get_post_terms( $p_id, 'product_brand' );
					$b_name   = ( ! is_wp_error( $p_brands ) && ! empty( $p_brands ) ) ? $p_brands[0]->name : 'ATK';
				?>
					<a
						id="product-card-<?php echo esc_attr( (string) $p_id ); ?>"
						href="<?php echo esc_url( $p_link ); ?>"
						class="group flex flex-col justify-between rounded-2xl bg-neutral-100 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800/80 p-6 transition-all duration-300 hover:scale-[1.02] hover:border-black dark:hover:border-white shadow-sm hover:shadow-lg"
					>
						<!-- Card Media -->
						<div id="p-img-wrap-<?php echo esc_attr( (string) $p_id ); ?>" class="relative w-full h-56 flex items-center justify-center overflow-hidden mb-6">
							<span id="p-brand-badge-<?php echo esc_attr( (string) $p_id ); ?>" class="absolute top-0 left-0 text-[10px] font-mono tracking-widest uppercase text-neutral-400 dark:text-neutral-500">
								<?php echo esc_html( $b_name ); ?>
							</span>
							<img
								id="p-img-<?php echo esc_attr( (string) $p_id ); ?>"
								src="<?php echo esc_url( $p_img_url ); ?>"
								alt="<?php echo esc_attr( $p_title ); ?>"
								class="max-h-full object-contain transition-transform duration-500 group-hover:scale-105"
								loading="lazy"
							/>
						</div>

						<!-- Card Details -->
						<div id="p-details-<?php echo esc_attr( (string) $p_id ); ?>" class="space-y-3 pt-4 border-t border-black/5 dark:border-white/5">
							<h3 id="p-title-<?php echo esc_attr( (string) $p_id ); ?>" class="text-sm sm:text-base font-bold uppercase tracking-tight text-neutral-900 dark:text-white line-clamp-2 leading-snug font-hn">
								<?php echo esc_html( $p_title ); ?>
							</h3>
							<div id="p-price-row-<?php echo esc_attr( (string) $p_id ); ?>" class="flex items-center justify-between text-xs sm:text-sm font-mono text-neutral-800 dark:text-neutral-200">
								<span id="p-price-val-<?php echo esc_attr( (string) $p_id ); ?>"><?php echo wp_kses_post( $p_price ); ?></span>
								<span id="p-view-arrow-<?php echo esc_attr( (string) $p_id ); ?>" class="text-neutral-400 group-hover:text-black dark:group-hover:text-white transition-colors">
									<i id="p-icon-<?php echo esc_attr( (string) $p_id ); ?>" class="ri-arrow-right-line text-base transition-transform group-hover:translate-x-1"></i>
								</span>
							</div>
						</div>
					</a>
				<?php endwhile; ?>
			</div>

			<!-- Pagination -->
			<div id="archive-pagination" class="mt-16 flex items-center justify-center gap-2">
				<?php
				echo paginate_links( [
					'prev_text' => '<i class="ri-arrow-left-line"></i>',
					'next_text' => '<i class="ri-arrow-right-line"></i>',
					'type'      => 'list',
				] );
				?>
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
