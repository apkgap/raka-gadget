<?php
/**
 * The template for displaying product archives (Shop, Categories, Brands) with Comprehensive Sidebar Filter.
 *
 * @package raka-preorder-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$current_term  = get_queried_object();
$archive_title = is_a( $current_term, 'WP_Term' ) ? $current_term->name : 'All Products';
$archive_desc  = is_a( $current_term, 'WP_Term' ) ? $current_term->description : 'Discover official esports hardware, wireless mice, magnetic keyboards, and archival collectibles.';

$all_cats = get_terms( [
	'taxonomy'   => 'product_cat',
	'hide_empty' => true,
] );
?>

<main id="archive-product-main" class="min-h-screen pt-20 md:pt-24 pb-12 md:pb-16 bg-white dark:bg-obsidian text-black dark:text-white transition-colors duration-300">
	<div id="archive-product-container" class="container">

		<!-- ── Archive Header ── -->
		<div id="archive-header-block" class="mb-8 md:mb-12 space-y-4 border-b border-black/10 dark:border-white/10 pb-6 md:pb-8">
			<?php raka_render_breadcrumbs( 'archive-breadcrumbs', 'mb-2' ); ?>
			<div id="archive-title-wrap" class="flex flex-col md:flex-row md:items-end justify-between gap-6">
				<h1 id="archive-title-heading" class="text-3xl sm:text-4xl md:text-5xl font-black uppercase tracking-tight text-neutral-900 dark:text-white font-hn">
					<?php echo esc_html( $archive_title ); ?>
				</h1>
				<p id="archive-desc-text" class="text-sm sm:text-base text-neutral-500 dark:text-neutral-400 font-light max-w-md font-hn leading-relaxed">
					<?php echo esc_html( $archive_desc ?: 'Esports tournament gear, Hall Effect magnetic keyboards, and ultra-lightweight wireless mice.' ); ?>
				</p>
			</div>
		</div>

		<!-- ── Main Shop Layout: Sidebar + Product Grid ── -->
		<div id="shop-layout-wrap" class="flex flex-col lg:flex-row items-start gap-8 xl:gap-12">
			
			<!-- 1. Left Sticky Sidebar Filter (Desktop) -->
			<div id="shop-desktop-sidebar-wrap" class="hidden lg:block w-64 xl:w-72 flex-shrink-0 sticky top-28 self-start">
				<?php raka_render_shop_sidebar_filters( 'desktop' ); ?>
			</div>

			<!-- 2. Right Products Content Area -->
			<div id="shop-products-content-wrap" class="flex-1 min-w-0 w-full">
				
				<!-- Top Bar: Result Count, Mobile Filter Trigger, and Sort Dropdown -->
				<div id="shop-toolbar-bar" class="flex items-center justify-between gap-4 pb-4 border-b border-black/10 dark:border-white/10 mb-4">
					<!-- Result Count -->
					<div id="shop-result-count" class="text-xs sm:text-sm font-mono uppercase tracking-wider text-neutral-500 dark:text-neutral-400">
						<?php
						global $wp_query;
						$total_products = (int) $wp_query->found_posts;
						printf( esc_html( _n( '%d Product Found', '%d Products Found', $total_products, 'raka-preorder-theme' ) ), $total_products );
						?>
					</div>

					<!-- Right Controls: Mobile Filter Button & Sort Dropdown -->
					<div id="shop-toolbar-controls" class="flex items-center gap-2.5">
						<!-- Mobile Filter Trigger Button -->
						<button
							id="btn-mobile-filter-open"
							type="button"
							onclick="window.rakaOpenMobileFilters()"
							class="lg:hidden inline-flex items-center gap-1.5 h-10 px-4 rounded-full bg-neutral-900 text-white dark:bg-white dark:text-neutral-900 text-xs sm:text-sm font-mono font-semibold uppercase tracking-wider shadow-sm active:scale-95 transition-transform cursor-pointer"
						>
							<i id="btn-mobile-filter-ico" class="ri-filter-3-line text-sm"></i>
							<span id="btn-mobile-filter-txt">FILTERS</span>
							<?php
							$f_state = raka_get_current_filter_state();
							if ( $f_state['active_count'] > 0 ) :
							?>
								<span id="btn-mobile-filter-num" class="w-4 h-4 rounded-full bg-white text-black dark:bg-black dark:text-white text-[10px] font-mono font-bold flex items-center justify-center">
									<?php echo (int) $f_state['active_count']; ?>
								</span>
							<?php endif; ?>
						</button>

						<!-- Sort Dropdown -->
						<?php raka_render_shop_sort_dropdown(); ?>
					</div>
				</div>

				<!-- Active Filter Pills Bar -->
				<?php raka_render_shop_active_filter_pills(); ?>

				<!-- Products Grid (2 Columns on Mobile, 2 on Tablet, 3 on Desktop) -->
				<?php if ( have_posts() ) : ?>
					<div id="archive-products-grid" class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-3 gap-2.5 sm:gap-6">
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
						<h3 id="no-products-title" class="text-xl font-bold uppercase tracking-wider font-hn">No products match your filter</h3>
						<p id="no-products-desc" class="text-sm text-neutral-400 font-mono">Try clearing active filters or explore other categories.</p>
						<a
							id="btn-no-products-reset"
							href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop' ) ); ?>"
							class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-full bg-black text-white dark:bg-white dark:text-black text-xs sm:text-sm font-mono uppercase tracking-wider font-bold hover:scale-105 transition-transform"
						>
							<i id="btn-no-products-ico" class="ri-refresh-line text-sm"></i>
							<span id="btn-no-products-txt">RESET ALL FILTERS</span>
						</a>
					</div>
				<?php endif; ?>

			</div>

		</div>

	</div>
</main>

<!-- ── Mobile Filter Slide-over Drawer ── -->
<div
	id="mobile-filters-drawer"
	class="fixed inset-0 z-[100] pointer-events-none opacity-0 transition-opacity duration-300 flex justify-end"
>
	<!-- Backdrop -->
	<div
		id="mobile-filters-backdrop"
		onclick="window.rakaCloseMobileFilters()"
		class="absolute inset-0 bg-black/60 backdrop-blur-xs transition-opacity cursor-pointer"
	></div>

	<!-- Drawer Panel -->
	<div
		id="mobile-filters-panel"
		class="relative w-full max-w-md h-full bg-white dark:bg-obsidian text-black dark:text-white p-6 shadow-2xl flex flex-col justify-between transform translate-x-full transition-transform duration-300 ease-out overflow-hidden"
	>
		<!-- Top Bar -->
		<div id="mobile-filters-top" class="flex items-center justify-between pb-4 border-b border-black/10 dark:border-white/10 flex-shrink-0">
			<div id="mobile-filters-heading" class="flex items-center gap-2">
				<i id="mobile-filters-heading-ico" class="ri-filter-3-line text-base text-neutral-900 dark:text-white"></i>
				<span id="mobile-filters-heading-txt" class="text-sm font-mono font-bold uppercase tracking-[0.15em] text-neutral-900 dark:text-white">FILTER CATALOGUE</span>
			</div>
			<button
				id="btn-mobile-filter-close"
				type="button"
				onclick="window.rakaCloseMobileFilters()"
				class="w-8 h-8 rounded-full bg-black/5 dark:bg-white/5 flex items-center justify-center text-base hover:bg-black/10 dark:hover:bg-white/10 transition-colors cursor-pointer"
			>
				<i id="btn-mobile-filter-close-ico" class="ri-close-line"></i>
			</button>
		</div>

		<!-- Scrollable Body -->
		<div id="mobile-filters-body" class="flex-1 overflow-y-auto py-4 pr-1">
			<?php raka_render_shop_sidebar_filters( 'mobile' ); ?>
		</div>

		<!-- Bottom Sticky Action -->
		<div id="mobile-filters-foot" class="pt-4 border-t border-black/10 dark:border-white/10 flex items-center gap-3 flex-shrink-0">
			<button
				id="btn-mobile-filter-apply"
				type="button"
				onclick="window.rakaCloseMobileFilters()"
				class="w-full h-11 rounded-full bg-neutral-900 text-white dark:bg-white dark:text-neutral-900 text-sm font-mono font-bold uppercase tracking-wider shadow-md active:scale-95 transition-transform cursor-pointer"
			>
				APPLY FILTERS
			</button>
		</div>
	</div>
</div>

<script id="raka-mobile-filter-js">
	window.rakaOpenMobileFilters = function() {
		var d = document.getElementById('mobile-filters-drawer');
		var p = document.getElementById('mobile-filters-panel');
		if (d && p) {
			d.classList.remove('pointer-events-none', 'opacity-0');
			d.classList.add('pointer-events-auto', 'opacity-100');
			p.classList.remove('translate-x-full');
			p.classList.add('translate-x-0');
			document.body.style.overflow = 'hidden';
		}
	};
	window.rakaCloseMobileFilters = function() {
		var d = document.getElementById('mobile-filters-drawer');
		var p = document.getElementById('mobile-filters-panel');
		if (d && p) {
			d.classList.add('pointer-events-none', 'opacity-0');
			d.classList.remove('pointer-events-auto', 'opacity-100');
			p.classList.add('translate-x-full');
			p.classList.remove('translate-x-0');
			document.body.style.overflow = '';
		}
	};
</script>

<?php
get_footer();
