<?php
/**
 * Helper functions for Raka Preorder Theme
 *
 * @package raka-preorder-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get a theme option value by key.
 *
 * @param string $key Option key.
 * @param mixed  $default Default value if option is empty.
 * @return mixed
 */
function raka_get_option( string $key, $default = null ) {
	$options = get_option( 'raka_theme_options', [] );
	if ( is_array( $options ) && isset( $options[ $key ] ) && '' !== $options[ $key ] ) {
		return $options[ $key ];
	}
	return $default;
}

/**
 * Get all theme options.
 *
 * @return array
 */
function raka_get_all_options(): array {
	return (array) get_option( 'raka_theme_options', [] );
}

/**
 * Get structured hierarchical menu tree by theme location.
 *
 * @param string $theme_location Theme location key (default: 'primary-menu').
 * @return array
 */
function raka_get_nav_menu_tree( string $theme_location = 'primary-menu' ): array {
	$locations = get_nav_menu_locations();
	if ( empty( $locations[ $theme_location ] ) ) {
		return [];
	}

	$menu_items = wp_get_nav_menu_items( $locations[ $theme_location ] );
	if ( empty( $menu_items ) || is_wp_error( $menu_items ) ) {
		return [];
	}

	$tree = [];
	$indexed = [];

	foreach ( $menu_items as $item ) {
		$item->children = [];
		$indexed[ $item->ID ] = $item;
	}

	foreach ( $indexed as $id => $item ) {
		if ( ! empty( $item->menu_item_parent ) && isset( $indexed[ $item->menu_item_parent ] ) ) {
			$indexed[ $item->menu_item_parent ]->children[] = $item;
		} else {
			$tree[] = $item;
		}
	}

	return $tree;
}

/**
 * Get all WooCommerce product categories for select dropdowns.
 *
 * @return array List of [id => name]
 */
function raka_get_wc_categories(): array {
	if ( ! taxonomy_exists( 'product_cat' ) ) {
		return [];
	}

	$terms = get_terms( [
		'taxonomy'   => 'product_cat',
		'hide_empty' => false,
	] );

	$list = [];
	if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
		foreach ( $terms as $term ) {
			$list[ $term->term_id ] = $term->name . ' (' . $term->count . ')';
		}
	}
	return $list;
}

/**
 * Get all brand taxonomies / terms for select dropdowns.
 * Checks for common brand taxonomies: pwb-brand, product_brand, brand, pa_brand
 *
 * @return array List of [id => name]
 */
function raka_get_wc_brands(): array {
	$brand_taxonomies = [ 'pwb-brand', 'product_brand', 'brand', 'yith_product_brand', 'pa_brand' ];
	$list = [];

	foreach ( $brand_taxonomies as $tax ) {
		if ( taxonomy_exists( $tax ) ) {
			$terms = get_terms( [
				'taxonomy'   => $tax,
				'hide_empty' => false,
			] );

			if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
				foreach ( $terms as $term ) {
					$list[ $tax . ':' . $term->term_id ] = '[' . $tax . '] ' . $term->name . ' (' . $term->count . ')';
				}
			}
		}
	}
	return $list;
}

/**
 * Get WooCommerce products for select dropdowns.
 *
 * @param int $limit Number of products to fetch.
 * @return array List of [id => name]
 */
function raka_get_wc_products( int $limit = 100 ): array {
	if ( ! post_type_exists( 'product' ) ) {
		return [];
	}

	$products = get_posts( [
		'post_type'      => 'product',
		'posts_per_page' => $limit,
		'post_status'    => 'publish',
		'orderby'        => 'title',
		'order'          => 'ASC',
	] );

	$list = [];
	if ( ! empty( $products ) ) {
		foreach ( $products as $p ) {
			$price = '';
			if ( function_exists( 'wc_get_product' ) ) {
				$product = wc_get_product( $p->ID );
				if ( $product ) {
					$price = ' - ' . wp_strip_all_tags( wc_price( $product->get_price() ) );
				}
			}
			$list[ $p->ID ] = esc_html( $p->post_title ) . $price;
		}
	}
	return $list;
}

/**
 * Resolve card data dynamically based on slot configuration.
 *
 * @param string $slot_key Slot identifier (e.g. 'sec_2', 'sec_3', 'sec_4_top', 'sec_4_bot', 'sec_5').
 * @param array  $fallback Default static fallback values.
 * @return array
 */
function raka_get_resolved_card_data( string $slot_key, array $fallback = [] ): array {
	$options = raka_get_all_options();
	$prefix  = 'card_' . $slot_key . '_';

	$source_type = $options[ $prefix . 'source' ] ?? ( $fallback['source'] ?? 'custom' );

	$data = [
		'tag'         => $fallback['tag'] ?? '',
		'title'       => $fallback['title'] ?? '',
		'description' => $fallback['description'] ?? '',
		'url'         => $fallback['url'] ?? '#',
		'image_url'   => $fallback['image_url'] ?? '',
		'price_html'  => '',
		'source_type' => $source_type,
	];

	if ( 'product_cat' === $source_type ) {
		$cat_id = absint( $options[ $prefix . 'cat_id' ] ?? 0 );
		if ( $cat_id > 0 && taxonomy_exists( 'product_cat' ) ) {
			$term = get_term( $cat_id, 'product_cat' );
			if ( $term && ! is_wp_error( $term ) ) {
				$data['tag']         = ! empty( $options[ $prefix . 'tag' ] ) ? $options[ $prefix . 'tag' ] : 'CATEGORY';
				$data['title']       = $term->name;
				$data['description'] = ! empty( $options[ $prefix . 'description' ] ) ? $options[ $prefix . 'description' ] : ( $term->description ?: sprintf( __( 'Explore %d items in %s collection.', 'raka-preorder-theme' ), $term->count, $term->name ) );
				$data['url']         = get_term_link( $term );

				// Get category thumbnail
				if ( function_exists( 'get_term_meta' ) ) {
					$thumb_id = get_term_meta( $term->term_id, 'thumbnail_id', true );
					if ( $thumb_id ) {
						$img = wp_get_attachment_image_src( $thumb_id, 'medium_large' );
						if ( $img ) {
							$data['image_url'] = $img[0];
						}
					}
				}
				return $data;
			}
		}
	} elseif ( 'brand' === $source_type ) {
		$brand_val = $options[ $prefix . 'brand_id' ] ?? '';
		if ( ! empty( $brand_val ) && false !== strpos( $brand_val, ':' ) ) {
			list( $tax, $term_id ) = explode( ':', $brand_val, 2 );
			$term_id = absint( $term_id );
			if ( taxonomy_exists( $tax ) && $term_id > 0 ) {
				$term = get_term( $term_id, $tax );
				if ( $term && ! is_wp_error( $term ) ) {
					$data['tag']         = ! empty( $options[ $prefix . 'tag' ] ) ? $options[ $prefix . 'tag' ] : 'BRAND';
					$data['title']       = $term->name;
					$data['description'] = ! empty( $options[ $prefix . 'description' ] ) ? $options[ $prefix . 'description' ] : ( $term->description ?: sprintf( __( 'Discover curated items by %s.', 'raka-preorder-theme' ), $term->name ) );
					$data['url']         = get_term_link( $term );

					// Get brand image/logo if meta exists
					$thumb_id = get_term_meta( $term->term_id, 'pwb_brand_image', true ) ?: get_term_meta( $term->term_id, 'thumbnail_id', true );
					if ( $thumb_id ) {
						$img = wp_get_attachment_image_src( absint( $thumb_id ), 'medium_large' );
						if ( $img ) {
							$data['image_url'] = $img[0];
						}
					}
					return $data;
				}
			}
		}
	} elseif ( 'product' === $source_type ) {
		$product_id = absint( $options[ $prefix . 'product_id' ] ?? 0 );
		if ( $product_id > 0 && function_exists( 'wc_get_product' ) ) {
			$product = wc_get_product( $product_id );
			if ( $product ) {
				$data['tag']         = ! empty( $options[ $prefix . 'tag' ] ) ? $options[ $prefix . 'tag' ] : 'FEATURED PRODUCT';
				$data['title']       = $product->get_name();
				$data['description'] = ! empty( $options[ $prefix . 'description' ] ) ? $options[ $prefix . 'description' ] : ( $product->get_short_description() ?: wp_trim_words( $product->get_description(), 20 ) );
				$data['url']         = $product->get_permalink();
				$data['price_html']  = $product->get_price_html();

				$image_id = $product->get_image_id();
				if ( $image_id ) {
					$img = wp_get_attachment_image_src( $image_id, 'medium_large' );
					if ( $img ) {
						$data['image_url'] = $img[0];
					}
				}
				return $data;
			}
		}
	}

	// Custom Source Type
	if ( isset( $options[ $prefix . 'tag' ] ) && '' !== $options[ $prefix . 'tag' ] ) {
		$data['tag'] = $options[ $prefix . 'tag' ];
	}
	if ( isset( $options[ $prefix . 'title' ] ) && '' !== $options[ $prefix . 'title' ] ) {
		$data['title'] = $options[ $prefix . 'title' ];
	}
	if ( isset( $options[ $prefix . 'description' ] ) && '' !== $options[ $prefix . 'description' ] ) {
		$data['description'] = $options[ $prefix . 'description' ];
	}
	if ( isset( $options[ $prefix . 'url' ] ) && '' !== $options[ $prefix . 'url' ] ) {
		$data['url'] = $options[ $prefix . 'url' ];
	}
	if ( isset( $options[ $prefix . 'image_url' ] ) && '' !== $options[ $prefix . 'image_url' ] ) {
		$data['image_url'] = $options[ $prefix . 'image_url' ];
	}

	return $data;
}

/**
 * Render Ultra-Minimal Pagination (Left: « ‹ [ 1 ] จาก 499 › » | Right: แสดง 1-12 จาก 5985 รายการ)
 *
 * @param WP_Query|null $query Custom query or defaults to global $wp_query.
 * @param array         $args  Customization arguments.
 */
function raka_render_pagination( $query = null, array $args = [] ): void {
	if ( null === $query ) {
		global $wp_query;
		$query = $wp_query;
	}

	if ( ! is_a( $query, 'WP_Query' ) ) {
		return;
	}

	$total_pages = (int) $query->max_num_pages;
	$total_items = (int) $query->found_posts;

	if ( $total_items <= 0 ) {
		return;
	}

	$current_page = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
	$per_page     = (int) $query->get( 'posts_per_page' ) ?: 12;
	if ( $per_page < 1 ) {
		$per_page = 12;
	}

	$start_item = ( ( $current_page - 1 ) * $per_page ) + 1;
	$end_item   = min( $total_items, $current_page * $per_page );

	// Calculate pagination links
	$link_pattern = esc_url( get_pagenum_link( 999999999 ) );
	$link_pattern = str_replace( '999999999', '__PAGE__', $link_pattern );

	$first_url = ( $current_page > 1 ) ? esc_url( get_pagenum_link( 1 ) ) : '';
	$prev_url  = ( $current_page > 1 ) ? esc_url( get_pagenum_link( $current_page - 1 ) ) : '';
	$next_url  = ( $current_page < $total_pages ) ? esc_url( get_pagenum_link( $current_page + 1 ) ) : '';
	$last_url  = ( $current_page < $total_pages ) ? esc_url( get_pagenum_link( $total_pages ) ) : '';

	$unique_id = wp_unique_id( 'raka-page-' );
?>
	<div id="raka-pagination-wrapper-<?php echo esc_attr( $unique_id ); ?>" class="raka-minimal-pagination flex flex-col sm:flex-row items-center justify-between gap-5 pt-12 pb-6 border-t border-black/10 dark:border-white/10 text-xs sm:text-sm font-mono text-neutral-500 dark:text-neutral-400">
		
		<!-- ── Left Controls: « ‹ [ 1 ] จาก Total › » ── -->
		<div id="pagination-controls-<?php echo esc_attr( $unique_id ); ?>" class="flex items-center gap-2 sm:gap-3">
			
			<!-- First Page («) -->
			<?php if ( $first_url ) : ?>
				<a id="btn-page-first-<?php echo esc_attr( $unique_id ); ?>" href="<?php echo esc_url( $first_url ); ?>" class="p-1 text-neutral-400 hover:text-black dark:hover:text-white transition-colors flex items-center justify-center" aria-label="First page">
					<i id="icon-page-first-<?php echo esc_attr( $unique_id ); ?>" class="ri-arrow-left-double-line text-base"></i>
				</a>
			<?php else : ?>
				<span id="btn-page-first-disabled-<?php echo esc_attr( $unique_id ); ?>" class="p-1 opacity-25 cursor-not-allowed flex items-center justify-center">
					<i id="icon-page-first-dis-<?php echo esc_attr( $unique_id ); ?>" class="ri-arrow-left-double-line text-base"></i>
				</span>
			<?php endif; ?>

			<!-- Prev Page (‹) -->
			<?php if ( $prev_url ) : ?>
				<a id="btn-page-prev-<?php echo esc_attr( $unique_id ); ?>" href="<?php echo esc_url( $prev_url ); ?>" class="p-1 text-neutral-400 hover:text-black dark:hover:text-white transition-colors flex items-center justify-center" aria-label="Previous page">
					<i id="icon-page-prev-<?php echo esc_attr( $unique_id ); ?>" class="ri-arrow-left-s-line text-lg"></i>
				</a>
			<?php else : ?>
				<span id="btn-page-prev-disabled-<?php echo esc_attr( $unique_id ); ?>" class="p-1 opacity-25 cursor-not-allowed flex items-center justify-center">
					<i id="icon-page-prev-dis-<?php echo esc_attr( $unique_id ); ?>" class="ri-arrow-left-s-line text-lg"></i>
				</span>
			<?php endif; ?>

			<!-- Page Input Box: [ 1 ] -->
			<div id="page-input-wrap-<?php echo esc_attr( $unique_id ); ?>" class="flex items-center">
				<input
					id="page-input-<?php echo esc_attr( $unique_id ); ?>"
					type="number"
					min="1"
					max="<?php echo (int) $total_pages; ?>"
					value="<?php echo (int) $current_page; ?>"
					onkeydown="if(event.key==='Enter'){event.preventDefault();let p=Math.max(1,Math.min(<?php echo (int) $total_pages; ?>,parseInt(this.value,10)||1));window.location.href='<?php echo esc_url( $link_pattern ); ?>'.replace('__PAGE__',p);}"
					onchange="let p=Math.max(1,Math.min(<?php echo (int) $total_pages; ?>,parseInt(this.value,10)||1));window.location.href='<?php echo esc_url( $link_pattern ); ?>'.replace('__PAGE__',p);"
					class="w-12 h-9 text-center font-mono text-xs sm:text-sm font-semibold rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-white focus:outline-none focus:border-black dark:focus:border-white shadow-sm [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
					aria-label="Current page number"
				/>
			</div>

			<!-- Total pages text: จาก 499 -->
			<span id="page-total-label-<?php echo esc_attr( $unique_id ); ?>" class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 select-none">
				จาก <?php echo esc_html( (string) $total_pages ); ?>
			</span>

			<!-- Next Page (›) -->
			<?php if ( $next_url ) : ?>
				<a id="btn-page-next-<?php echo esc_attr( $unique_id ); ?>" href="<?php echo esc_url( $next_url ); ?>" class="p-1 text-neutral-400 hover:text-black dark:hover:text-white transition-colors flex items-center justify-center" aria-label="Next page">
					<i id="icon-page-next-<?php echo esc_attr( $unique_id ); ?>" class="ri-arrow-right-s-line text-lg"></i>
				</a>
			<?php else : ?>
				<span id="btn-page-next-disabled-<?php echo esc_attr( $unique_id ); ?>" class="p-1 opacity-25 cursor-not-allowed flex items-center justify-center">
					<i id="icon-page-next-dis-<?php echo esc_attr( $unique_id ); ?>" class="ri-arrow-right-s-line text-lg"></i>
				</span>
			<?php endif; ?>

			<!-- Last Page (») -->
			<?php if ( $last_url ) : ?>
				<a id="btn-page-last-<?php echo esc_attr( $unique_id ); ?>" href="<?php echo esc_url( $last_url ); ?>" class="p-1 text-neutral-400 hover:text-black dark:hover:text-white transition-colors flex items-center justify-center" aria-label="Last page">
					<i id="icon-page-last-<?php echo esc_attr( $unique_id ); ?>" class="ri-arrow-right-double-line text-base"></i>
				</a>
			<?php else : ?>
				<span id="btn-page-last-disabled-<?php echo esc_attr( $unique_id ); ?>" class="p-1 opacity-25 cursor-not-allowed flex items-center justify-center">
					<i id="icon-page-last-dis-<?php echo esc_attr( $unique_id ); ?>" class="ri-arrow-right-double-line text-base"></i>
				</span>
			<?php endif; ?>

		</div>

		<!-- ── Right Status: แสดง 1-12 จาก 5985 รายการ ── -->
		<div id="pagination-status-<?php echo esc_attr( $unique_id ); ?>" class="text-xs sm:text-sm text-neutral-500 dark:text-neutral-400 font-light font-hn select-none">
			แสดง <?php echo esc_html( (string) $start_item ); ?>-<?php echo esc_html( (string) $end_item ); ?> จาก <?php echo esc_html( number_format_i18n( $total_items ) ); ?> รายการ
		</div>

	</div>
<?php
}

/**
 * WooCommerce hook replacement for default pagination
 */
function raka_render_woocommerce_pagination(): void {
	raka_render_pagination();
}
