<?php
/**
 * WooCommerce Comprehensive Sidebar Filter Engine
 *
 * @package raka-preorder-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Filter WooCommerce Product Queries via URL parameters
 */
function raka_filter_woocommerce_product_query( $query, $query_vars = [] ): void {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( ! ( is_shop() || is_product_taxonomy() || is_product_category() || is_product_tag() ) ) {
		return;
	}

	// 1. Keyword Search
	if ( ! empty( $_GET['filter_q'] ) ) {
		$search_keyword = sanitize_text_field( wp_unslash( $_GET['filter_q'] ) );
		$query->set( 's', $search_keyword );
	}

	$tax_query  = (array) $query->get( 'tax_query' );
	$meta_query = (array) $query->get( 'meta_query' );

	// 2. Brand Filter
	if ( ! empty( $_GET['filter_brand'] ) ) {
		$brands = array_filter( array_map( 'sanitize_title', explode( ',', wp_unslash( $_GET['filter_brand'] ) ) ) );
		if ( ! empty( $brands ) ) {
			$tax_query[] = [
				'taxonomy' => 'product_brand',
				'field'    => 'slug',
				'terms'    => $brands,
				'operator' => 'IN',
			];
		}
	}

	// 3. Category Filter (when not already on category page or for multi-cat filtering)
	if ( ! empty( $_GET['filter_cat'] ) ) {
		$cats = array_filter( array_map( 'sanitize_title', explode( ',', wp_unslash( $_GET['filter_cat'] ) ) ) );
		if ( ! empty( $cats ) ) {
			$tax_query[] = [
				'taxonomy' => 'product_cat',
				'field'    => 'slug',
				'terms'    => $cats,
				'operator' => 'IN',
			];
		}
	}

	// 4. Stock & Pre-Order Status Filter
	if ( ! empty( $_GET['filter_stock'] ) ) {
		$stock_status = sanitize_key( wp_unslash( $_GET['filter_stock'] ) );
		if ( $stock_status === 'instock' ) {
			$meta_query[] = [
				'key'     => '_stock_status',
				'value'   => 'instock',
				'compare' => '=',
			];
		} elseif ( $stock_status === 'preorder' ) {
			$meta_query[] = [
				'relation' => 'OR',
				[
					'key'     => '_raka_is_preorder',
					'value'   => 'yes',
					'compare' => '=',
				],
				[
					'key'     => '_stock_status',
					'value'   => 'onbackorder',
					'compare' => '=',
				],
			];
		} elseif ( $stock_status === 'outofstock' ) {
			$meta_query[] = [
				'key'     => '_stock_status',
				'value'   => 'outofstock',
				'compare' => '=',
			];
		}
	}

	// 5. Price Range Filter
	$min_price = isset( $_GET['min_price'] ) ? floatval( $_GET['min_price'] ) : 0;
	$max_price = isset( $_GET['max_price'] ) ? floatval( $_GET['max_price'] ) : 0;

	if ( $min_price > 0 || $max_price > 0 ) {
		$price_query = [ 'key' => '_price', 'type' => 'NUMERIC' ];
		if ( $min_price > 0 && $max_price > 0 ) {
			$price_query['value']   = [ $min_price, $max_price ];
			$price_query['compare'] = 'BETWEEN';
		} elseif ( $min_price > 0 ) {
			$price_query['value']   = $min_price;
			$price_query['compare'] = '>=';
		} elseif ( $max_price > 0 ) {
			$price_query['value']   = $max_price;
			$price_query['compare'] = '<=';
		}
		$meta_query[] = $price_query;
	}

	// Set combined queries
	if ( ! empty( $tax_query ) ) {
		$query->set( 'tax_query', $tax_query );
	}
	if ( ! empty( $meta_query ) ) {
		$query->set( 'meta_query', $meta_query );
	}
}
add_action( 'woocommerce_product_query', 'raka_filter_woocommerce_product_query', 20 );

/**
 * Get current filter state from request
 */
function raka_get_current_filter_state(): array {
	$brands       = ! empty( $_GET['filter_brand'] ) ? array_filter( array_map( 'sanitize_title', explode( ',', wp_unslash( $_GET['filter_brand'] ) ) ) ) : [];
	$cats         = ! empty( $_GET['filter_cat'] ) ? array_filter( array_map( 'sanitize_title', explode( ',', wp_unslash( $_GET['filter_cat'] ) ) ) ) : [];
	$stock        = ! empty( $_GET['filter_stock'] ) ? sanitize_key( wp_unslash( $_GET['filter_stock'] ) ) : '';
	$min_price    = isset( $_GET['min_price'] ) && $_GET['min_price'] !== '' ? floatval( $_GET['min_price'] ) : null;
	$max_price    = isset( $_GET['max_price'] ) && $_GET['max_price'] !== '' ? floatval( $_GET['max_price'] ) : null;
	$search       = ! empty( $_GET['filter_q'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_q'] ) ) : '';
	$orderby      = ! empty( $_GET['orderby'] ) ? sanitize_key( wp_unslash( $_GET['orderby'] ) ) : 'menu_order';

	$active_count = count( $brands ) + count( $cats ) + ( $stock ? 1 : 0 ) + ( $min_price !== null || $max_price !== null ? 1 : 0 ) + ( $search ? 1 : 0 );

	return [
		'brands'       => $brands,
		'cats'         => $cats,
		'stock'        => $stock,
		'min_price'    => $min_price,
		'max_price'    => $max_price,
		'search'       => $search,
		'orderby'      => $orderby,
		'active_count' => $active_count,
	];
}

/**
 * Render Comprehensive Sidebar Filters Component
 */
function raka_render_shop_sidebar_filters( string $id_prefix = 'shop' ): void {
	$state     = raka_get_current_filter_state();
	$shop_link = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop' );

	// Fetch all categories
	$categories = get_terms( [
		'taxonomy'   => 'product_cat',
		'hide_empty' => true,
		'orderby'    => 'count',
		'order'      => 'DESC',
	] );

	// Fetch all brands
	$brands = get_terms( [
		'taxonomy'   => 'product_brand',
		'hide_empty' => false,
		'orderby'    => 'name',
		'order'      => 'ASC',
	] );

	// Current queried taxonomy
	$queried_obj = get_queried_object();
	$current_cat_slug   = is_a( $queried_obj, 'WP_Term' ) && $queried_obj->taxonomy === 'product_cat' ? $queried_obj->slug : '';
	$current_brand_slug = is_a( $queried_obj, 'WP_Term' ) && $queried_obj->taxonomy === 'product_brand' ? $queried_obj->slug : '';
	?>
	<aside id="<?php echo esc_attr( $id_prefix ); ?>-filters-sidebar" class="w-full space-y-6">
		
		<!-- ── Filter Top Header: Title & Clear All ── -->
		<div id="<?php echo esc_attr( $id_prefix ); ?>-filter-head" class="flex items-center justify-between pb-4 border-b border-black/10 dark:border-white/10">
			<div id="<?php echo esc_attr( $id_prefix ); ?>-filter-title-wrap" class="flex items-center gap-2">
				<i id="<?php echo esc_attr( $id_prefix ); ?>-filter-icon" class="ri-filter-3-line text-base text-neutral-900 dark:text-white"></i>
				<h2 id="<?php echo esc_attr( $id_prefix ); ?>-filter-title" class="text-sm font-mono font-bold uppercase tracking-[0.15em] text-neutral-900 dark:text-white">
					FILTERS
				</h2>
				<?php if ( $state['active_count'] > 0 ) : ?>
					<span id="<?php echo esc_attr( $id_prefix ); ?>-filter-badge" class="w-5 h-5 rounded-full bg-black text-white dark:bg-white dark:text-black text-xs font-mono font-bold flex items-center justify-center">
						<?php echo (int) $state['active_count']; ?>
					</span>
				<?php endif; ?>
			</div>

			<?php if ( $state['active_count'] > 0 || $current_cat_slug || $current_brand_slug ) : ?>
				<a
					id="<?php echo esc_attr( $id_prefix ); ?>-filter-reset-btn"
					href="<?php echo esc_url( $shop_link ); ?>"
					class="text-xs font-mono font-semibold uppercase tracking-wider text-red-500 hover:text-red-700 dark:hover:text-red-400 transition-colors flex items-center gap-1"
				>
					<i id="<?php echo esc_attr( $id_prefix ); ?>-filter-reset-ico" class="ri-refresh-line text-xs"></i>
					<span>RESET</span>
				</a>
			<?php endif; ?>
		</div>

		<!-- ── 1. Search Within Shop Filter ── -->
		<div id="<?php echo esc_attr( $id_prefix ); ?>-search-block" class="space-y-2">
			<form id="<?php echo esc_attr( $id_prefix ); ?>-search-form" method="get" action="<?php echo esc_url( $shop_link ); ?>" class="relative">
				<input
					id="<?php echo esc_attr( $id_prefix ); ?>-search-input"
					type="text"
					name="filter_q"
					value="<?php echo esc_attr( $state['search'] ); ?>"
					placeholder="Search catalogue..."
					class="w-full h-10 pl-9 pr-3 rounded-full bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-sm font-sans text-neutral-900 dark:text-white placeholder:text-neutral-400 focus:outline-none focus:border-black dark:focus:border-white transition-colors"
				/>
				<i id="<?php echo esc_attr( $id_prefix ); ?>-search-ico" class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-sm text-neutral-400 pointer-events-none"></i>
				<?php
				// Preserve existing active filters in search form
				if ( ! empty( $state['brands'] ) ) {
					echo '<input type="hidden" name="filter_brand" value="' . esc_attr( implode( ',', $state['brands'] ) ) . '">';
				}
				if ( ! empty( $state['cats'] ) ) {
					echo '<input type="hidden" name="filter_cat" value="' . esc_attr( implode( ',', $state['cats'] ) ) . '">';
				}
				if ( ! empty( $state['stock'] ) ) {
					echo '<input type="hidden" name="filter_stock" value="' . esc_attr( $state['stock'] ) . '">';
				}
				?>
			</form>
		</div>

		<!-- ── 2. Categories Accordion ── -->
		<?php if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) : ?>
			<div id="<?php echo esc_attr( $id_prefix ); ?>-cat-section" class="border-b border-black/5 dark:border-white/5 pb-5 space-y-3">
				<div id="<?php echo esc_attr( $id_prefix ); ?>-cat-title" class="text-xs font-mono font-bold uppercase tracking-wider text-neutral-400 dark:text-neutral-500 flex items-center justify-between">
					<span>CATEGORIES</span>
					<span class="text-xs opacity-60"><?php echo count( $categories ); ?></span>
				</div>

				<div id="<?php echo esc_attr( $id_prefix ); ?>-cat-list" class="space-y-1 max-h-56 overflow-y-auto pr-1">
					<?php foreach ( $categories as $cat ) :
						$is_checked = in_array( $cat->slug, $state['cats'], true ) || $current_cat_slug === $cat->slug;
						$cat_toggle_url = raka_get_filter_toggle_url( 'filter_cat', $cat->slug );
					?>
						<a
							id="<?php echo esc_attr( $id_prefix ); ?>-cat-item-<?php echo esc_attr( $cat->slug ); ?>"
							href="<?php echo esc_url( $cat_toggle_url ); ?>"
							class="group flex items-center justify-between py-2 px-2.5 rounded-lg text-sm transition-colors <?php echo $is_checked ? 'bg-black text-white dark:bg-white dark:text-black font-semibold' : 'text-neutral-700 dark:text-neutral-300 hover:bg-black/5 dark:hover:bg-white/5'; ?>"
						>
							<div id="<?php echo esc_attr( $id_prefix ); ?>-cat-info-<?php echo esc_attr( $cat->slug ); ?>" class="flex items-center gap-2 truncate">
								<?php
								if ( function_exists( 'raka_get_term_icon_html' ) ) {
									echo raka_get_term_icon_html( $cat->term_id, 'text-sm flex-shrink-0' );
								}
								?>
								<span class="truncate"><?php echo esc_html( $cat->name ); ?></span>
							</div>
							<span id="<?php echo esc_attr( $id_prefix ); ?>-cat-count-<?php echo esc_attr( $cat->slug ); ?>" class="text-xs opacity-60 font-mono flex-shrink-0 pl-2">
								<?php echo (int) $cat->count; ?>
							</span>
						</a>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>

		<!-- ── 3. Brands Accordion ── -->
		<?php if ( ! empty( $brands ) && ! is_wp_error( $brands ) ) : ?>
			<div id="<?php echo esc_attr( $id_prefix ); ?>-brand-section" class="border-b border-black/5 dark:border-white/5 pb-5 space-y-3">
				<div id="<?php echo esc_attr( $id_prefix ); ?>-brand-title" class="text-xs font-mono font-bold uppercase tracking-wider text-neutral-400 dark:text-neutral-500 flex items-center justify-between">
					<span>BRANDS</span>
					<span class="text-xs opacity-60"><?php echo count( $brands ); ?></span>
				</div>

				<div id="<?php echo esc_attr( $id_prefix ); ?>-brand-list" class="space-y-1 max-h-56 overflow-y-auto pr-1">
					<?php foreach ( $brands as $brand ) :
						$is_checked = in_array( $brand->slug, $state['brands'], true ) || $current_brand_slug === $brand->slug;
						$brand_toggle_url = raka_get_filter_toggle_url( 'filter_brand', $brand->slug );
					?>
						<a
							id="<?php echo esc_attr( $id_prefix ); ?>-brand-item-<?php echo esc_attr( $brand->slug ); ?>"
							href="<?php echo esc_url( $brand_toggle_url ); ?>"
							class="group flex items-center justify-between py-2 px-2.5 rounded-lg text-sm transition-colors <?php echo $is_checked ? 'bg-black text-white dark:bg-white dark:text-black font-semibold' : 'text-neutral-700 dark:text-neutral-300 hover:bg-black/5 dark:hover:bg-white/5'; ?>"
						>
							<div id="<?php echo esc_attr( $id_prefix ); ?>-brand-info-<?php echo esc_attr( $brand->slug ); ?>" class="flex items-center gap-2 truncate">
								<span id="<?php echo esc_attr( $id_prefix ); ?>-brand-box-<?php echo esc_attr( $brand->slug ); ?>" class="w-4 h-4 rounded border <?php echo $is_checked ? 'bg-white text-black dark:bg-black dark:text-white border-transparent' : 'border-neutral-400 dark:border-neutral-600'; ?> flex items-center justify-center text-xs">
									<?php if ( $is_checked ) : ?>
										<i class="ri-check-line"></i>
									<?php endif; ?>
								</span>
								<span class="truncate"><?php echo esc_html( $brand->name ); ?></span>
							</div>
							<span id="<?php echo esc_attr( $id_prefix ); ?>-brand-count-<?php echo esc_attr( $brand->slug ); ?>" class="text-xs opacity-60 font-mono flex-shrink-0 pl-2">
								<?php echo (int) $brand->count; ?>
							</span>
						</a>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>

		<!-- ── 4. Price Range Filter ── -->
		<div id="<?php echo esc_attr( $id_prefix ); ?>-price-section" class="border-b border-black/5 dark:border-white/5 pb-5 space-y-3">
			<div id="<?php echo esc_attr( $id_prefix ); ?>-price-title" class="text-xs font-mono font-bold uppercase tracking-wider text-neutral-400 dark:text-neutral-500">
				<span>PRICE RANGE (THB)</span>
			</div>

			<form id="<?php echo esc_attr( $id_prefix ); ?>-price-form" method="get" action="<?php echo esc_url( $shop_link ); ?>" class="space-y-3">
				<div id="<?php echo esc_attr( $id_prefix ); ?>-price-inputs" class="flex items-center gap-2">
					<div id="<?php echo esc_attr( $id_prefix ); ?>-min-wrap" class="relative flex-1">
						<span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-xs font-mono text-neutral-400">฿</span>
						<input
							id="<?php echo esc_attr( $id_prefix ); ?>-min-price-input"
							type="number"
							name="min_price"
							value="<?php echo $state['min_price'] !== null ? esc_attr( (string) $state['min_price'] ) : ''; ?>"
							placeholder="Min"
							class="w-full h-9 pl-6 pr-2 rounded-lg bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-sm font-mono text-neutral-900 dark:text-white placeholder:text-neutral-400 focus:outline-none focus:border-black dark:focus:border-white"
						/>
					</div>
					<span class="text-neutral-400 text-sm">&ndash;</span>
					<div id="<?php echo esc_attr( $id_prefix ); ?>-max-wrap" class="relative flex-1">
						<span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-xs font-mono text-neutral-400">฿</span>
						<input
							id="<?php echo esc_attr( $id_prefix ); ?>-max-price-input"
							type="number"
							name="max_price"
							value="<?php echo $state['max_price'] !== null ? esc_attr( (string) $state['max_price'] ) : ''; ?>"
							placeholder="Max"
							class="w-full h-9 pl-6 pr-2 rounded-lg bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-sm font-mono text-neutral-900 dark:text-white placeholder:text-neutral-400 focus:outline-none focus:border-black dark:focus:border-white"
						/>
					</div>
					<button
						id="<?php echo esc_attr( $id_prefix ); ?>-price-apply-btn"
						type="submit"
						class="h-9 px-3.5 rounded-lg bg-black text-white dark:bg-white dark:text-black text-xs font-mono font-bold uppercase hover:opacity-80 active:scale-95 transition-all"
					>
						GO
					</button>
				</div>

				<!-- Preserve other filters -->
				<?php
				if ( ! empty( $state['brands'] ) ) {
					echo '<input type="hidden" name="filter_brand" value="' . esc_attr( implode( ',', $state['brands'] ) ) . '">';
				}
				if ( ! empty( $state['cats'] ) ) {
					echo '<input type="hidden" name="filter_cat" value="' . esc_attr( implode( ',', $state['cats'] ) ) . '">';
				}
				if ( ! empty( $state['stock'] ) ) {
					echo '<input type="hidden" name="filter_stock" value="' . esc_attr( $state['stock'] ) . '">';
				}
				if ( ! empty( $state['search'] ) ) {
					echo '<input type="hidden" name="filter_q" value="' . esc_attr( $state['search'] ) . '">';
				}
				?>
			</form>

			<!-- Quick Budget Presets -->
			<div id="<?php echo esc_attr( $id_prefix ); ?>-price-presets" class="flex flex-wrap gap-1.5 pt-1">
				<?php
				$price_tiers = [
					[ 'label' => '< ฿1,000', 'min' => 0, 'max' => 1000 ],
					[ 'label' => '฿1K - ฿3K', 'min' => 1000, 'max' => 3000 ],
					[ 'label' => '฿3K - ฿6K', 'min' => 3000, 'max' => 6000 ],
					[ 'label' => '฿6,000+', 'min' => 6000, 'max' => '' ],
				];
				foreach ( $price_tiers as $idx => $tier ) :
					$tier_url = add_query_arg( [
						'min_price' => $tier['min'],
						'max_price' => $tier['max'],
					] );
					$is_tier_active = ( $state['min_price'] == $tier['min'] && ( $tier['max'] === '' ? $state['max_price'] === null : $state['max_price'] == $tier['max'] ) );
				?>
					<a
						id="<?php echo esc_attr( $id_prefix ); ?>-tier-<?php echo (int) $idx; ?>"
						href="<?php echo esc_url( $tier_url ); ?>"
						class="px-2.5 py-1 rounded text-xs font-mono font-medium uppercase tracking-wider transition-colors <?php echo $is_tier_active ? 'bg-black text-white dark:bg-white dark:text-black font-semibold' : 'bg-black/5 dark:bg-white/5 text-neutral-600 dark:text-neutral-400 hover:opacity-75'; ?>"
					>
						<?php echo esc_html( $tier['label'] ); ?>
					</a>
				<?php endforeach; ?>
			</div>
		</div>

		<!-- ── 5. Stock & Availability Filter ── -->
		<div id="<?php echo esc_attr( $id_prefix ); ?>-stock-section" class="space-y-3">
			<div id="<?php echo esc_attr( $id_prefix ); ?>-stock-title" class="text-xs font-mono font-bold uppercase tracking-wider text-neutral-400 dark:text-neutral-500">
				<span>AVAILABILITY</span>
			</div>

			<div id="<?php echo esc_attr( $id_prefix ); ?>-stock-options" class="space-y-1">
				<?php
				$stock_options = [
					''           => [ 'label' => 'All Items', 'ico' => 'ri-apps-line' ],
					'instock'    => [ 'label' => 'In Stock', 'ico' => 'ri-checkbox-circle-line' ],
					'preorder'   => [ 'label' => 'Pre-Order', 'ico' => 'ri-time-line' ],
					'outofstock' => [ 'label' => 'Sold Out', 'ico' => 'ri-close-circle-line' ],
				];
				foreach ( $stock_options as $key => $opt ) :
					$is_stock_active = ( $state['stock'] === $key );
					$stock_url       = $key ? add_query_arg( 'filter_stock', $key ) : remove_query_arg( 'filter_stock' );
				?>
					<a
						id="<?php echo esc_attr( $id_prefix ); ?>-stock-opt-<?php echo esc_attr( $key ?: 'all' ); ?>"
						href="<?php echo esc_url( $stock_url ); ?>"
						class="flex items-center gap-2 py-2 px-2.5 rounded-lg text-sm font-medium transition-colors <?php echo $is_stock_active ? 'bg-black text-white dark:bg-white dark:text-black font-semibold' : 'text-neutral-700 dark:text-neutral-300 hover:bg-black/5 dark:hover:bg-white/5'; ?>"
					>
						<i class="<?php echo esc_attr( $opt['ico'] ); ?> text-base"></i>
						<span><?php echo esc_html( $opt['label'] ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>

	</aside>
	<?php
}

/**
 * Generate toggle URL for multi-select taxonomy filters
 */
function raka_get_filter_toggle_url( string $param, string $slug ): string {
	$current_val = ! empty( $_GET[ $param ] ) ? sanitize_text_field( wp_unslash( $_GET[ $param ] ) ) : '';
	$items       = $current_val ? explode( ',', $current_val ) : [];

	if ( in_array( $slug, $items, true ) ) {
		$items = array_diff( $items, [ $slug ] );
	} else {
		$items[] = $slug;
	}

	if ( empty( $items ) ) {
		return remove_query_arg( $param );
	}
	return add_query_arg( $param, implode( ',', $items ) );
}

/**
 * Render Active Filter Pills Bar
 */
function raka_render_shop_active_filter_pills(): void {
	$state     = raka_get_current_filter_state();
	$shop_link = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop' );

	if ( $state['active_count'] === 0 ) {
		return;
	}
	?>
	<div id="shop-active-filter-pills-bar" class="flex items-center flex-wrap gap-2 pb-6 pt-2">
		<span id="shop-active-filter-label" class="text-xs font-mono uppercase text-neutral-400 dark:text-neutral-500 mr-1">Active:</span>

		<!-- Search Pill -->
		<?php if ( ! empty( $state['search'] ) ) : ?>
			<a
				id="pill-active-search"
				href="<?php echo esc_url( remove_query_arg( 'filter_q' ) ); ?>"
				class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-black/10 dark:bg-white/10 text-neutral-900 dark:text-white text-xs sm:text-sm font-mono uppercase tracking-wider hover:bg-red-500 hover:text-white transition-colors group"
			>
				<span>Keyword: <?php echo esc_html( $state['search'] ); ?></span>
				<i class="ri-close-line text-sm opacity-60 group-hover:opacity-100"></i>
			</a>
		<?php endif; ?>

		<!-- Category Pills -->
		<?php foreach ( $state['cats'] as $cat_slug ) :
			$cat_term = get_term_by( 'slug', $cat_slug, 'product_cat' );
			$cat_name = $cat_term ? $cat_term->name : $cat_slug;
		?>
			<a
				id="pill-active-cat-<?php echo esc_attr( $cat_slug ); ?>"
				href="<?php echo esc_url( raka_get_filter_toggle_url( 'filter_cat', $cat_slug ) ); ?>"
				class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-black/10 dark:bg-white/10 text-neutral-900 dark:text-white text-xs sm:text-sm font-mono uppercase tracking-wider hover:bg-red-500 hover:text-white transition-colors group"
			>
				<span>Cat: <?php echo esc_html( $cat_name ); ?></span>
				<i class="ri-close-line text-sm opacity-60 group-hover:opacity-100"></i>
			</a>
		<?php endforeach; ?>

		<!-- Brand Pills -->
		<?php foreach ( $state['brands'] as $brand_slug ) :
			$brand_term = get_term_by( 'slug', $brand_slug, 'product_brand' );
			$brand_name = $brand_term ? $brand_term->name : $brand_slug;
		?>
			<a
				id="pill-active-brand-<?php echo esc_attr( $brand_slug ); ?>"
				href="<?php echo esc_url( raka_get_filter_toggle_url( 'filter_brand', $brand_slug ) ); ?>"
				class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-black/10 dark:bg-white/10 text-neutral-900 dark:text-white text-xs sm:text-sm font-mono uppercase tracking-wider hover:bg-red-500 hover:text-white transition-colors group"
			>
				<span>Brand: <?php echo esc_html( $brand_name ); ?></span>
				<i class="ri-close-line text-sm opacity-60 group-hover:opacity-100"></i>
			</a>
		<?php endforeach; ?>

		<!-- Price Range Pill -->
		<?php if ( $state['min_price'] !== null || $state['max_price'] !== null ) : ?>
			<a
				id="pill-active-price"
				href="<?php echo esc_url( remove_query_arg( [ 'min_price', 'max_price' ] ) ); ?>"
				class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-black/10 dark:bg-white/10 text-neutral-900 dark:text-white text-xs sm:text-sm font-mono uppercase tracking-wider hover:bg-red-500 hover:text-white transition-colors group"
			>
				<span>Price: ฿<?php echo esc_html( (string) ( $state['min_price'] ?? 0 ) ); ?> - ฿<?php echo esc_html( $state['max_price'] ? (string) $state['max_price'] : 'Max' ); ?></span>
				<i class="ri-close-line text-sm opacity-60 group-hover:opacity-100"></i>
			</a>
		<?php endif; ?>

		<!-- Stock Pill -->
		<?php if ( ! empty( $state['stock'] ) ) : ?>
			<a
				id="pill-active-stock"
				href="<?php echo esc_url( remove_query_arg( 'filter_stock' ) ); ?>"
				class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-black/10 dark:bg-white/10 text-neutral-900 dark:text-white text-xs sm:text-sm font-mono uppercase tracking-wider hover:bg-red-500 hover:text-white transition-colors group"
			>
				<span>Status: <?php echo esc_html( ucfirst( $state['stock'] ) ); ?></span>
				<i class="ri-close-line text-sm opacity-60 group-hover:opacity-100"></i>
			</a>
		<?php endif; ?>

		<!-- Clear All Button -->
		<a
			id="pill-active-clear-all"
			href="<?php echo esc_url( $shop_link ); ?>"
			class="text-xs font-mono font-semibold uppercase text-red-500 hover:underline pl-2"
		>
			Clear All
		</a>
	</div>
	<?php
}

/**
 * Render Modern Sort Dropdown
 */
function raka_render_shop_sort_dropdown(): void {
	$orderby_options = [
		'menu_order' => 'Featured / Default',
		'date'       => 'Newest Arrivals',
		'price'      => 'Price: Low to High',
		'price-desc' => 'Price: High to Low',
		'popularity' => 'Most Popular',
		'rating'     => 'Top Rated',
	];
	$current_orderby = ! empty( $_GET['orderby'] ) ? sanitize_key( wp_unslash( $_GET['orderby'] ) ) : 'menu_order';
	?>
	<div id="shop-sort-wrap" class="relative inline-flex items-center">
		<select
			id="shop-sort-select"
			onchange="window.location.href=this.value"
			class="h-10 pl-3.5 pr-8 rounded-full bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-xs sm:text-sm font-mono uppercase tracking-wider text-neutral-900 dark:text-white cursor-pointer focus:outline-none appearance-none transition-colors"
		>
			<?php foreach ( $orderby_options as $key => $label ) :
				$sort_url = add_query_arg( 'orderby', $key );
			?>
				<option value="<?php echo esc_url( $sort_url ); ?>" <?php selected( $current_orderby, $key ); ?>>
					<?php echo esc_html( $label ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<i id="shop-sort-arrow" class="ri-arrow-down-s-line absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-xs text-neutral-400"></i>
	</div>
	<?php
}
