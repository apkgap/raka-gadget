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
 * Get Brand Logo image URL from term meta
 *
 * @param int|WP_Term $term_id_or_object Term ID or WP_Term object.
 * @return string Brand logo image URL or empty string.
 */
function raka_get_brand_logo_url( $term_id_or_object ): string {
	$term_id = is_object( $term_id_or_object ) ? (int) $term_id_or_object->term_id : (int) $term_id_or_object;
	if ( $term_id <= 0 ) {
		return '';
	}

	$thumb_id = get_term_meta( $term_id, 'thumbnail_id', true )
		?: get_term_meta( $term_id, 'pwb_brand_image', true )
		?: get_term_meta( $term_id, 'brand_image_id', true );

	if ( $thumb_id ) {
		$url = wp_get_attachment_image_url( (int) $thumb_id, 'full' );
		if ( $url ) {
			return $url;
		}
		$post = get_post( (int) $thumb_id );
		if ( $post && ! empty( $post->guid ) ) {
			return $post->guid;
		}
	}

	return '';
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
 * Helper to resolve realistic CSS hex or gradient styles from color names
 *
 * @param string $raw_name Color attribute or variation name.
 * @return string CSS style string.
 */
if ( ! function_exists( 'raka_get_swatch_css' ) ) {
	function raka_get_swatch_css( $raw_name ): string {
		$c = strtolower( trim( html_entity_decode( (string) $raw_name, ENT_QUOTES, 'UTF-8' ) ) );
		$c = preg_replace( '/[^\p{L}\p{N}\s\-]/u', '', $c );

		if ( strpos( $c, 'black' ) !== false || strpos( $c, 'obsidian' ) !== false || strpos( $c, 'eon' ) !== false || strpos( $c, 'blackberry' ) !== false || strpos( $c, 'gunmetal' ) !== false ) {
			return 'background-color: #121212; border-color: #383838;';
		}
		if ( strpos( $c, 'white' ) !== false || strpos( $c, 'polar' ) !== false || strpos( $c, 'ice' ) !== false || strpos( $c, 'prime' ) !== false || strpos( $c, 'arctic' ) !== false || strpos( $c, 'pearl' ) !== false ) {
			return 'background-color: #F8F9FA; border: 1.5px solid #D1D5DB;';
		}
		if ( strpos( $c, 'yellow' ) !== false || strpos( $c, 'lemon' ) !== false ) {
			return 'background-color: #FACC15; border-color: #EAB308;';
		}
		if ( strpos( $c, 'pink' ) !== false || strpos( $c, 'sakura' ) !== false || strpos( $c, 'blush' ) !== false ) {
			return 'background-color: #F472B6; border-color: #EC4899;';
		}
		if ( strpos( $c, 'purple' ) !== false || strpos( $c, 'lilac' ) !== false || strpos( $c, 'violet' ) !== false ) {
			return 'background-color: #A855F7; border-color: #9333EA;';
		}
		if ( strpos( $c, 'orange' ) !== false || strpos( $c, 'sunset' ) !== false ) {
			return 'background-color: #FB923C; border-color: #EA580C;';
		}
		if ( strpos( $c, 'red' ) !== false || strpos( $c, 'crimson' ) !== false ) {
			return 'background-color: #EF4444; border-color: #DC2626;';
		}
		if ( strpos( $c, 'blue' ) !== false || strpos( $c, 'verge' ) !== false || strpos( $c, 'cyan' ) !== false || strpos( $c, 'sky' ) !== false || strpos( $c, 'periwinkle' ) !== false ) {
			return 'background-color: #38BDF8; border-color: #0284C7;';
		}
		if ( strpos( $c, 'silver' ) !== false || strpos( $c, 'metal' ) !== false || strpos( $c, 'gray' ) !== false || strpos( $c, 'grey' ) !== false ) {
			return 'background-color: #94A3B8; border-color: #64748B;';
		}
		if ( strpos( $c, 'sand' ) !== false || strpos( $c, 'dune' ) !== false || strpos( $c, 'beige' ) !== false ) {
			return 'background-color: #D4B996; border-color: #BCA380;';
		}
		if ( strpos( $c, 'green' ) !== false || strpos( $c, 'mint' ) !== false ) {
			return 'background-color: #10B981; border-color: #059669;';
		}

		return 'background: linear-gradient(135deg, #4A5568 0%, #2D3748 100%); border-color: #4A5568;';
	}
}

/**
 * Render Ultra-Luxury Editorial Product Card
 *
 * @param WC_Product|int $product Product instance or Product ID.
 * @param array          $args    Optional styling args.
 */
function raka_render_product_card( $product, array $args = [] ): void {
	if ( is_numeric( $product ) ) {
		$product = wc_get_product( $product );
	}

	if ( ! is_a( $product, 'WC_Product' ) ) {
		return;
	}

	$p_id      = (string) $product->get_id();
	$p_title   = $product->get_name();
	$p_price   = $product->get_price_html();
	$p_link    = get_permalink( (int) $p_id );

	// Images: Main featured + first gallery image for hover crossfade
	$p_img_id  = $product->get_image_id();
	$p_img_url = $p_img_id ? wp_get_attachment_image_url( $p_img_id, 'medium_large' ) : wc_placeholder_img_src( 'medium_large' );

	$gallery_ids       = $product->get_gallery_image_ids();
	$secondary_img_url = '';
	if ( ! empty( $gallery_ids ) ) {
		$secondary_img_url = wp_get_attachment_image_url( $gallery_ids[0], 'medium_large' );
	}

	// Brand information
	$p_brands = wp_get_post_terms( (int) $p_id, 'product_brand' );
	$b_name   = ( ! is_wp_error( $p_brands ) && ! empty( $p_brands ) ) ? $p_brands[0]->name : 'ATK';
	$b_logo   = ( ! is_wp_error( $p_brands ) && ! empty( $p_brands ) ) ? raka_get_brand_logo_url( $p_brands[0] ) : '';

	// Categories
	$p_cats   = wp_get_post_terms( (int) $p_id, 'product_cat' );
	$cat_name = '';
	if ( ! is_wp_error( $p_cats ) && ! empty( $p_cats ) ) {
		foreach ( $p_cats as $c ) {
			if ( 'uncategorized' !== $c->slug ) {
				$cat_name = $c->name;
				break;
			}
		}
	}

	// Stock & Preorder Status
	$is_in_stock     = $product->is_in_stock();
	$is_on_backorder = $product->is_on_backorder();
	$is_preorder     = ( false !== stripos( $p_title, 'preorder' ) || false !== stripos( $p_title, 'pre-order' ) || $is_on_backorder );

	// Color Variations preview
	$color_swatches = [];
	$total_colors   = 0;
	if ( $product->is_type( 'variable' ) ) {
		$attributes = $product->get_variation_attributes();
		foreach ( $attributes as $attr_name => $options ) {
			if ( false !== stripos( $attr_name, 'color' ) || false !== stripos( $attr_name, 'colour' ) || false !== stripos( $attr_name, 'สี' ) ) {
				$color_swatches = array_slice( (array) $options, 0, 5 );
				$total_colors   = count( (array) $options );
				break;
			}
		}
	}
	if ( empty( $color_swatches ) ) {
		$attrs = $product->get_attributes();
		foreach ( $attrs as $attr_name => $attr_obj ) {
			if ( false !== stripos( $attr_name, 'color' ) || false !== stripos( $attr_name, 'colour' ) || false !== stripos( $attr_name, 'สี' ) ) {
				if ( is_object( $attr_obj ) && method_exists( $attr_obj, 'get_options' ) ) {
					$opts           = (array) $attr_obj->get_options();
					$color_swatches = array_slice( $opts, 0, 5 );
					$total_colors   = count( $opts );
				}
				break;
			}
		}
	}
?>
	<div
		id="product-card-<?php echo esc_attr( $p_id ); ?>"
		class="group relative flex flex-col justify-between rounded-xl sm:rounded-2xl border border-black/10 dark:border-white/10 hover:border-black/20 dark:hover:border-white/25 bg-white dark:bg-neutral-900/70 p-2.5 sm:p-5 transition-all duration-500 hover:shadow-[0_20px_40px_-15px_rgba(0,0,0,0.08)] dark:hover:shadow-[0_20px_40px_-15px_rgba(0,0,0,0.7)] hover:-translate-y-1 backdrop-blur-sm"
	>
		<!-- ── Top Header: Brand Logo & Status Tag ── -->
		<div id="p-header-<?php echo esc_attr( $p_id ); ?>" class="flex items-center justify-between gap-2 mb-2.5 sm:mb-4 min-h-[20px] sm:min-h-[24px]">
			<div id="p-brand-badge-<?php echo esc_attr( $p_id ); ?>" class="flex items-center">
				<?php if ( $b_logo ) : ?>
					<img
						id="p-brand-img-<?php echo esc_attr( $p_id ); ?>"
						src="<?php echo esc_url( $b_logo ); ?>"
						alt="<?php echo esc_attr( $b_name ); ?>"
						class="h-3.5 sm:h-4.5 w-auto max-w-[65px] sm:max-w-[95px] object-contain opacity-75 group-hover:opacity-100 transition-opacity filter dark:invert"
						loading="lazy"
					/>
				<?php else : ?>
					<span id="p-brand-txt-<?php echo esc_attr( $p_id ); ?>" class="text-xs sm:text-sm font-mono font-semibold tracking-wider uppercase text-neutral-500 dark:text-neutral-400 truncate max-w-[80px] sm:max-w-none">
						<?php echo esc_html( $b_name ); ?>
					</span>
				<?php endif; ?>
			</div>

			<div id="p-status-badge-<?php echo esc_attr( $p_id ); ?>" class="flex items-center">
				<?php if ( ! $is_in_stock ) : ?>
					<span id="p-badge-oos-<?php echo esc_attr( $p_id ); ?>" class="px-2 py-0.5 rounded-full text-[10px] sm:text-xs font-mono uppercase tracking-wider font-semibold bg-red-500/10 text-red-600 dark:text-red-400 border border-red-500/20">
						SOLD OUT
					</span>
				<?php elseif ( $is_preorder ) : ?>
					<span id="p-badge-pre-<?php echo esc_attr( $p_id ); ?>" class="flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] sm:text-xs font-mono uppercase tracking-wider font-semibold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
						<span id="p-badge-dot-<?php echo esc_attr( $p_id ); ?>" class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
						PRE-ORDER
					</span>
				<?php elseif ( ! empty( $cat_name ) ) : ?>
					<span id="p-badge-cat-<?php echo esc_attr( $p_id ); ?>" class="px-2 py-0.5 rounded-full text-[10px] sm:text-xs font-mono uppercase tracking-wider text-neutral-500 dark:text-neutral-400 bg-black/5 dark:bg-white/5 border border-black/5 dark:border-white/5">
						<?php echo esc_html( $cat_name ); ?>
					</span>
				<?php endif; ?>
			</div>
		</div>

		<!-- ── Visual Stage: Clickable Image with Dual Hover Crossfade & Floating Action ── -->
		<a
			id="p-stage-link-<?php echo esc_attr( $p_id ); ?>"
			href="<?php echo esc_url( $p_link ); ?>"
			class="relative w-full aspect-square rounded-lg sm:rounded-2xl bg-white/70 dark:bg-black/40 border border-black/5 dark:border-white/5 flex items-center justify-center overflow-hidden mb-3 sm:mb-4 group/stage"
		>
			<img
				id="p-img-main-<?php echo esc_attr( $p_id ); ?>"
				src="<?php echo esc_url( $p_img_url ); ?>"
				alt="<?php echo esc_attr( $p_title ); ?>"
				class="w-full h-full object-cover rounded-lg sm:rounded-2xl transition-all duration-700 ease-out <?php echo $secondary_img_url ? 'group-hover/stage:opacity-0 group-hover/stage:scale-95' : 'group-hover/stage:scale-105'; ?>"
				loading="lazy"
			/>

			<?php if ( $secondary_img_url ) : ?>
				<img
					id="p-img-hover-<?php echo esc_attr( $p_id ); ?>"
					src="<?php echo esc_url( $secondary_img_url ); ?>"
					alt="<?php echo esc_attr( $p_title ); ?>"
					class="absolute inset-0 w-full h-full object-cover rounded-lg sm:rounded-2xl opacity-0 group-hover/stage:opacity-100 group-hover/stage:scale-105 transition-all duration-700 ease-out"
					loading="lazy"
				/>
			<?php endif; ?>

			<div id="p-stage-pill-<?php echo esc_attr( $p_id ); ?>" class="absolute bottom-2.5 sm:bottom-3 left-1/2 -translate-x-1/2 opacity-0 translate-y-2 group-hover/stage:opacity-100 group-hover/stage:translate-y-0 transition-all duration-300 pointer-events-none hidden sm:flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-black/85 dark:bg-white/95 text-white dark:text-black text-xs font-mono uppercase tracking-wider backdrop-blur shadow-md select-none">
				<span id="p-stage-pill-txt-<?php echo esc_attr( $p_id ); ?>">VIEW GEAR</span>
				<i id="p-stage-pill-ico-<?php echo esc_attr( $p_id ); ?>" class="ri-arrow-right-up-line text-xs"></i>
			</div>
		</a>

		<!-- ── Lower Meta: Color Swatches, Title, and Price Row ── -->
		<div id="p-info-wrap-<?php echo esc_attr( $p_id ); ?>" class="flex flex-col justify-between flex-grow space-y-2.5 sm:space-y-3">
			
			<?php if ( ! empty( $color_swatches ) ) : ?>
				<div id="p-swatches-row-<?php echo esc_attr( $p_id ); ?>" class="flex items-center gap-1.5 pt-0.5">
					<?php foreach ( $color_swatches as $idx => $c_name ) :
						$swatch_style = raka_get_swatch_css( $c_name );
					?>
						<span
							id="p-swatch-<?php echo esc_attr( $p_id . '-' . $idx ); ?>"
							title="<?php echo esc_attr( (string) $c_name ); ?>"
							class="w-3 h-3 sm:w-3.5 sm:h-3.5 rounded-full border border-black/15 dark:border-white/20 shadow-xs flex-shrink-0 transition-transform group-hover:scale-110"
							style="<?php echo esc_attr( $swatch_style ); ?>"
						></span>
					<?php endforeach; ?>
					<?php if ( $total_colors > count( $color_swatches ) ) : ?>
						<span id="p-swatch-more-<?php echo esc_attr( $p_id ); ?>" class="text-[10px] sm:text-xs font-mono text-neutral-400 dark:text-neutral-500 pl-0.5">
							+<?php echo (int) ( $total_colors - count( $color_swatches ) ); ?>
						</span>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<a id="p-title-link-<?php echo esc_attr( $p_id ); ?>" href="<?php echo esc_url( $p_link ); ?>" class="block group/title">
				<h3 id="p-title-<?php echo esc_attr( $p_id ); ?>" class="text-sm sm:text-base font-bold uppercase tracking-tight text-neutral-900 dark:text-neutral-100 line-clamp-2 leading-snug font-hn group-hover/title:text-black dark:group-hover/title:text-white transition-colors">
					<?php echo esc_html( $p_title ); ?>
				</h3>
			</a>

			<!-- Price & Add To Cart Action Row (Single-line on desktop, clean stacked on 2-col mobile) -->
			<div id="p-price-row-<?php echo esc_attr( $p_id ); ?>" class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-3 pt-2.5 sm:pt-3 border-t border-black/5 dark:border-white/5">
				<div id="p-price-val-<?php echo esc_attr( $p_id ); ?>" class="text-sm sm:text-lg font-bold font-hn text-neutral-900 dark:text-neutral-100 whitespace-nowrap flex-shrink min-w-0 truncate">
					<?php echo wp_kses_post( $p_price ); ?>
				</div>
				
				<div id="p-action-wrap-<?php echo esc_attr( $p_id ); ?>" class="w-full sm:w-auto flex items-center justify-start sm:justify-end flex-shrink-0">
					<?php if ( ! $is_in_stock ) : ?>
						<span id="p-btn-cart-<?php echo esc_attr( $p_id ); ?>" class="inline-flex items-center justify-center h-8 sm:h-9 px-3.5 sm:px-4 w-full sm:w-auto rounded-full bg-neutral-200/70 dark:bg-neutral-800/70 text-neutral-400 dark:text-neutral-500 text-xs font-mono uppercase tracking-wider cursor-not-allowed select-none whitespace-nowrap">
							SOLD OUT
						</span>
					<?php elseif ( $product->is_type( 'variable' ) ) : ?>
						<a
							id="p-btn-cart-<?php echo esc_attr( $p_id ); ?>"
							href="<?php echo esc_url( $p_link ); ?>"
							class="inline-flex items-center justify-center gap-1.5 h-8 sm:h-9 px-3.5 sm:px-4 w-full sm:w-auto rounded-full bg-neutral-900 text-white dark:bg-white dark:text-neutral-900 hover:bg-black dark:hover:bg-neutral-100 text-xs font-mono uppercase tracking-wider font-semibold hover:scale-[1.02] sm:hover:scale-105 active:scale-95 transition-all shadow-xs whitespace-nowrap group/btn"
						>
							<i id="p-btn-cart-ico-<?php echo esc_attr( $p_id ); ?>" class="ri-arrow-right-line text-xs flex-shrink-0"></i>
							<span id="p-btn-cart-txt-<?php echo esc_attr( $p_id ); ?>" class="whitespace-nowrap">READ MORE</span>
						</a>
					<?php else : ?>
						<button
							id="p-btn-cart-<?php echo esc_attr( $p_id ); ?>"
							type="button"
							onclick="window.rakaCardAddToCart(<?php echo (int) $p_id; ?>, this)"
							class="inline-flex items-center justify-center gap-1.5 h-8 sm:h-9 px-3.5 sm:px-4 w-full sm:w-auto rounded-full bg-neutral-900 text-white dark:bg-white dark:text-neutral-900 hover:bg-black dark:hover:bg-neutral-100 text-xs font-mono uppercase tracking-wider font-semibold hover:scale-[1.02] sm:hover:scale-105 active:scale-95 transition-all shadow-xs group/btn cursor-pointer whitespace-nowrap"
						>
							<i id="p-btn-cart-ico-<?php echo esc_attr( $p_id ); ?>" class="ri-shopping-bag-3-line text-xs flex-shrink-0"></i>
							<span id="p-btn-cart-txt-<?php echo esc_attr( $p_id ); ?>" class="whitespace-nowrap">ADD TO CART</span>
						</button>
					<?php endif; ?>
				</div>
			</div>

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

/**
 * Custom Chevron Separator for Yoast SEO Breadcrumbs
 */
add_filter( 'wpseo_breadcrumb_separator', function( $separator ) {
	return '<i class="ri-arrow-right-s-line text-sm opacity-50 mx-1 align-middle inline-block"></i>';
} );

/**
 * Render Breadcrumbs using Yoast SEO if active, with clean Obsidian editorial fallback
 */
function raka_render_breadcrumbs( string $wrapper_id = 'obsidian-breadcrumbs', string $extra_classes = '' ): void {
	if ( function_exists( 'yoast_breadcrumb' ) ) {
		yoast_breadcrumb(
			'<nav id="' . esc_attr( $wrapper_id ) . '" class="yoast-breadcrumbs flex items-center flex-wrap gap-2 text-xs uppercase tracking-[0.2em] font-mono text-neutral-400 dark:text-neutral-500 ' . esc_attr( $extra_classes ) . '">',
			'</nav>'
		);
		return;
	}

	// Fallback if Yoast not active
	echo '<nav id="' . esc_attr( $wrapper_id ) . '" class="yoast-breadcrumbs flex items-center flex-wrap gap-2 text-xs uppercase tracking-[0.2em] font-mono text-neutral-400 dark:text-neutral-500 ' . esc_attr( $extra_classes ) . '">';
	echo '<a id="' . esc_attr( $wrapper_id ) . '-home" href="' . esc_url( home_url( '/' ) ) . '" class="hover:text-black dark:hover:text-white transition-colors">HOME</a>';
	if ( is_singular( 'product' ) ) {
		echo '<i id="' . esc_attr( $wrapper_id ) . '-sep-1" class="ri-arrow-right-s-line text-sm opacity-50"></i>';
		echo '<a id="' . esc_attr( $wrapper_id ) . '-shop" href="' . esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ) . '" class="hover:text-black dark:hover:text-white transition-colors">SHOP</a>';
		echo '<i id="' . esc_attr( $wrapper_id ) . '-sep-2" class="ri-arrow-right-s-line text-sm opacity-50"></i>';
		echo '<span id="' . esc_attr( $wrapper_id ) . '-current" class="text-neutral-900 dark:text-neutral-200 font-semibold truncate max-w-[200px]">' . esc_html( get_the_title() ) . '</span>';
	} elseif ( is_shop() ) {
		echo '<i id="' . esc_attr( $wrapper_id ) . '-sep-1" class="ri-arrow-right-s-line text-sm opacity-50"></i>';
		echo '<span id="' . esc_attr( $wrapper_id ) . '-current" class="text-neutral-900 dark:text-neutral-200 font-semibold">SHOP</span>';
	} elseif ( is_tax() || is_category() || is_tag() ) {
		echo '<i id="' . esc_attr( $wrapper_id ) . '-sep-1" class="ri-arrow-right-s-line text-sm opacity-50"></i>';
		echo '<a id="' . esc_attr( $wrapper_id ) . '-shop" href="' . esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ) . '" class="hover:text-black dark:hover:text-white transition-colors">SHOP</a>';
		echo '<i id="' . esc_attr( $wrapper_id ) . '-sep-2" class="ri-arrow-right-s-line text-sm opacity-50"></i>';
		echo '<span id="' . esc_attr( $wrapper_id ) . '-current" class="text-neutral-900 dark:text-neutral-200 font-semibold">' . esc_html( single_term_title( '', false ) ) . '</span>';
	}
	echo '</nav>';
}

/**
 * Add WooCommerce Cart fragments to update cart badge count via AJAX.
 *
 * @param array $fragments Existing fragments.
 * @return array
 */
function raka_add_to_cart_fragments( array $fragments ): array {
	$cart_count = ( function_exists( 'WC' ) && WC()->cart ) ? WC()->cart->get_cart_contents_count() : 0;
	$scale_class = ( $cart_count > 0 ) ? 'scale-100' : 'scale-0';
	
	$html = '<span id="raka-cart-count" class="raka-cart-count-badge absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-black text-white dark:bg-white dark:text-black text-[10px] font-bold font-mono flex items-center justify-center border border-white dark:border-black transition-transform duration-300 ' . esc_attr( $scale_class ) . '">' . esc_html( (string) $cart_count ) . '</span>';
	
	$fragments['#raka-cart-count'] = $html;
	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'raka_add_to_cart_fragments' );



