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
 * Get all product categories with images and links for hero modal popup.
 *
 * @return array
 */
function raka_get_featured_categories(): array {
	if ( ! taxonomy_exists( 'product_cat' ) ) {
		return [];
	}

	$terms = get_terms( [
		'taxonomy'   => 'product_cat',
		'hide_empty' => false,
		'orderby'    => 'count',
		'order'      => 'DESC',
	] );

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return [];
	}

	$list = [];
	foreach ( $terms as $term ) {
		// Skip 'Uncategorized'
		if ( 'uncategorized' === $term->slug ) {
			continue;
		}

		$image_url = '';
		$thumb_id  = get_term_meta( $term->term_id, 'thumbnail_id', true );
		if ( $thumb_id ) {
			$img = wp_get_attachment_image_src( (int) $thumb_id, 'medium' );
			if ( $img ) {
				$image_url = $img[0];
			}
		}

		// Fallback: If no category thumbnail, grab first product image in category
		if ( empty( $image_url ) ) {
			$prods = get_posts( [
				'post_type'      => 'product',
				'posts_per_page' => 1,
				'tax_query'      => [
					[
						'taxonomy' => 'product_cat',
						'field'    => 'term_id',
						'terms'    => $term->term_id,
					],
				],
			] );
			if ( ! empty( $prods ) ) {
				$image_url = get_the_post_thumbnail_url( $prods[0]->ID, 'medium' ) ?: '';
			}
		}

		$term_link = get_term_link( $term );
		$icon_data = function_exists( 'raka_get_term_icon_data' ) ? raka_get_term_icon_data( $term->term_id ) : [ 'type' => 'none', 'remixicon' => '', 'media_id' => 0, 'media_url' => '' ];

		$list[] = [
			'id'          => $term->term_id,
			'name'        => $term->name,
			'slug'        => $term->slug,
			'count'       => $term->count,
			'description' => $term->description,
			'url'         => ! is_wp_error( $term_link ) ? $term_link : '#',
			'image_url'   => $image_url,
			'icon_type'   => $icon_data['type'],
			'icon_class'  => ! empty( $icon_data['remixicon'] ) ? $icon_data['remixicon'] : '',
			'media_icon'  => ! empty( $icon_data['media_url'] ) ? $icon_data['media_url'] : '',
		];
	}

	return $list;
}

/**
 * Get all product brands with images and links for hero modal popup.
 *
 * @return array
 */
function raka_get_featured_brands(): array {
	$brand_taxonomies = [ 'product_brand', 'pwb-brand', 'brand', 'pa_brand' ];
	$list = [];

	foreach ( $brand_taxonomies as $tax ) {
		if ( ! taxonomy_exists( $tax ) ) {
			continue;
		}

		$terms = get_terms( [
			'taxonomy'   => $tax,
			'hide_empty' => false,
			'orderby'    => 'count',
			'order'      => 'DESC',
		] );

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			continue;
		}

		foreach ( $terms as $term ) {
			$image_url = '';
			$thumb_id  = get_term_meta( $term->term_id, 'thumbnail_id', true )
				?: ( get_term_meta( $term->term_id, 'pwb_brand_image', true )
				?: get_term_meta( $term->term_id, 'brand_image_id', true ) );

			if ( $thumb_id ) {
				if ( is_numeric( $thumb_id ) ) {
					$img = wp_get_attachment_image_src( (int) $thumb_id, 'medium' );
					if ( $img ) {
						$image_url = $img[0];
					}
				} elseif ( filter_var( $thumb_id, FILTER_VALIDATE_URL ) ) {
					$image_url = $thumb_id;
				}
			}

			// Fallback: If no brand thumbnail, grab first product image with this brand
			if ( empty( $image_url ) ) {
				$prods = get_posts( [
					'post_type'      => 'product',
					'posts_per_page' => 1,
					'tax_query'      => [
						[
							'taxonomy' => $tax,
							'field'    => 'term_id',
							'terms'    => $term->term_id,
						],
					],
				] );
				if ( ! empty( $prods ) ) {
					$image_url = get_the_post_thumbnail_url( $prods[0]->ID, 'medium' ) ?: '';
				}
			}

			$term_link = get_term_link( $term );

			$list[] = [
				'id'          => $term->term_id,
				'taxonomy'    => $tax,
				'name'        => $term->name,
				'slug'        => $term->slug,
				'count'       => $term->count,
				'description' => $term->description,
				'url'         => ! is_wp_error( $term_link ) ? $term_link : '#',
				'image_url'   => $image_url,
			];
		}

		if ( ! empty( $list ) ) {
			break; // Found primary brand taxonomy
		}
	}

	return $list;
}

/**
 * Get contact settings with safe defaults.
 *
 * @return array
 */
function raka_get_contact_options(): array {
	return [
		'title'        => raka_get_option( 'contact_title', __( 'Contact Raka Gadget', 'raka-preorder-theme' ) ),
		'subtitle'     => raka_get_option( 'contact_subtitle', __( 'Pre-order inquiry, order tracking & support', 'raka-preorder-theme' ) ),
		'line_id'      => raka_get_option( 'contact_line_id', '@rakagadget' ),
		'line_url'     => raka_get_option( 'contact_line_url', 'https://line.me/R/ti/p/@rakagadget' ),
		'facebook_url' => raka_get_option( 'contact_facebook_url', 'https://facebook.com/rakagadget' ),
		'phone'        => raka_get_option( 'contact_phone', '081-234-5678' ),
		'email'        => raka_get_option( 'contact_email', 'support@raka-gadget.com' ),
		'hours'        => raka_get_option( 'contact_hours', 'Mon - Sun: 09:00 - 22:00 (GMT+7)' ),
		'address'      => raka_get_option( 'contact_address', 'Bangkok, Thailand' ),
		'note'         => raka_get_option( 'contact_note', 'ยินดีให้คำปรึกษาและสอบถามข้อมูลสินค้าพรีออเดอร์ทุกรุ่นได้ตลอด 24 ชั่วโมง' ),
	];
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
 * Get resolved hero banner product cards for front-page scroll timeline.
 * Supports dynamic repeater cards with WooCommerce product resolution and fallback items.
 *
 * @return array List of resolved card items.
 */
function raka_get_hero_cards(): array {
	$options    = raka_get_all_options();
	$hero_cards = $options['hero_cards'] ?? null;

	// Fetch top Keyboards for the hero banner stream
	$keyboard_products = [];
	if ( function_exists( 'wc_get_products' ) ) {
		$keyboard_products = wc_get_products( [
			'category' => [ 'keyboards', 'keyboard' ],
			'limit'    => 12,
			'status'   => 'publish',
			'orderby'  => 'menu_order date',
			'order'    => 'DESC',
		] );
	}

	// Populate from Keyboards category
	if ( ! empty( $keyboard_products ) ) {
		$sample_cards = [];
		$positions    = [ 'left', 'right' ];
		$tags         = [ 'HALL EFFECT 8K', 'PRE-ORDER', 'ESPORTS GRADE', 'ALUMINUM CNC', 'MAGNETIC SWITCH', 'LIMITED EDITION' ];

		foreach ( $keyboard_products as $idx => $prod ) {
			$sample_cards[] = [
				'product_id'  => $prod->get_id(),
				'tag'         => $tags[ $idx % count( $tags ) ],
				'title'       => '',
				'description' => '',
				'price'       => '',
				'url'         => '',
				'image_url'   => '',
				'button_text' => 'Pre-Order Now',
				'position'    => $positions[ $idx % 2 ],
			];
		}
		$hero_cards = $sample_cards;
		if ( empty( $sample_cards ) ) {
			$sample_cards = [
				[
					'product_id'  => 0,
					'tag'         => 'FLAGSHIP PRE-ORDER',
					'title'       => 'Vortex Pro 8K Wireless Keyboard',
					'description' => 'Ultra-low latency 8,000Hz polling rate with Hall Effect magnetic switches and aerospace CNC aluminum body.',
					'price'       => '฿4,990',
					'url'         => '#',
					'image_url'   => '',
					'button_text' => 'Pre-Order Now',
					'position'    => 'left',
				],
				[
					'product_id'  => 0,
					'tag'         => 'LIMITED EDITION',
					'title'       => 'CyberPunk Glass Mousepad',
					'description' => 'Micro-etched tempered glass surface engineered for pixel-perfect tracking and effortless glide.',
					'price'       => '฿2,290',
					'url'         => '#',
					'image_url'   => '',
					'button_text' => 'Pre-Order Now',
					'position'    => 'right',
				],
				[
					'product_id'  => 0,
					'tag'         => 'CUSTOM SWITCHES',
					'title'       => 'Aero Tactile Magnetic Switches',
					'description' => 'Factory lubed linear switches with 0.1mm to 4.0mm adjustable actuation distance for esports dominance.',
					'price'       => '฿1,150',
					'url'         => '#',
					'image_url'   => '',
					'button_text' => 'Explore Specs',
					'position'    => 'left',
				],
			];
			$hero_cards = $sample_cards;
		}
	}

	$resolved = [];
	$default_positions = [ 'left', 'right', 'left', 'right' ];

	foreach ( $hero_cards as $index => $raw_card ) {
		if ( ! is_array( $raw_card ) ) {
			continue;
		}

		$show_custom_text = ! empty( $raw_card['show_custom_text'] );
		$product_id       = absint( $raw_card['product_id'] ?? 0 );
		$product          = ( $product_id > 0 && function_exists( 'wc_get_product' ) ) ? wc_get_product( $product_id ) : null;
		$position         = ! empty( $raw_card['position'] ) ? sanitize_key( $raw_card['position'] ) : $default_positions[ $index % 4 ];
		$button_text      = ! empty( $raw_card['button_text'] ) ? sanitize_text_field( $raw_card['button_text'] ) : __( 'Pre-Order Now', 'raka-preorder-theme' );

		$tag         = ! empty( $raw_card['tag'] ) ? sanitize_text_field( $raw_card['tag'] ) : '';
		$title       = ! empty( $raw_card['title'] ) ? sanitize_text_field( $raw_card['title'] ) : '';
		$description = ! empty( $raw_card['description'] ) ? wp_strip_all_tags( (string) $raw_card['description'] ) : '';
		$price_html  = ! empty( $raw_card['price'] ) ? esc_html( $raw_card['price'] ) : '';
		$url         = ! empty( $raw_card['url'] ) ? esc_url_raw( $raw_card['url'] ) : '';
		$image_url   = ! empty( $raw_card['image_url'] ) ? esc_url_raw( $raw_card['image_url'] ) : '';
		$stock_status = '';

		if ( $product ) {
			if ( empty( $title ) ) {
				$title = html_entity_decode( $product->get_name(), ENT_QUOTES, 'UTF-8' );
			}
			if ( empty( $description ) ) {
				$raw_desc    = $product->get_short_description() ?: $product->get_description();
				$description = html_entity_decode( wp_strip_all_tags( $raw_desc ), ENT_QUOTES, 'UTF-8' );
				$description = wp_trim_words( $description, 24 );
			}
			if ( empty( $price_html ) ) {
				$price_html = html_entity_decode( wp_strip_all_tags( wc_price( $product->get_price() ) ), ENT_QUOTES, 'UTF-8' );
			}
			if ( empty( $url ) ) {
				$url = $product->get_permalink();
			}
			if ( empty( $image_url ) ) {
				$image_id = $product->get_image_id();
				if ( $image_id ) {
					$img = wp_get_attachment_image_src( $image_id, 'medium_large' );
					if ( $img ) {
						$image_url = $img[0];
					}
				}
			}
			if ( empty( $tag ) ) {
				$cats = wp_get_post_terms( $product_id, 'product_cat' );
				if ( ! empty( $cats ) && ! is_wp_error( $cats ) ) {
					$tag = $cats[0]->name;
				} else {
					$tag = __( 'PRE-ORDER', 'raka-preorder-theme' );
				}
			}
			$stock_status = $product->get_stock_status();
		}

		if ( empty( $tag ) ) {
			$tag = __( 'PRE-ORDER', 'raka-preorder-theme' );
		}
		if ( empty( $url ) ) {
			$url = '#';
		}

		$resolved[] = [
			'index'            => $index,
			'product_id'       => $product_id,
			'show_custom_text' => $show_custom_text,
			'tag'              => $tag,
			'title'            => $title,
			'description'      => $description,
			'price_html'       => $price_html,
			'url'              => $url,
			'image_url'        => $image_url,
			'button_text'      => $button_text,
			'position'         => $position,
			'stock_status'     => $stock_status,
		];
	}

	return $resolved;
}

/**
 * Retrieve Flash Sale campaign metadata, status and live countdown details.
 *
 * @return array
 */
function raka_get_flash_sale_campaign_data(): array {
	$options = raka_get_all_options();

	$enabled       = isset( $options['flash_sale_enabled'] ) ? (bool) $options['flash_sale_enabled'] : true;
	$title         = ! empty( $options['flash_sale_title'] ) ? $options['flash_sale_title'] : __( 'FLASH SALE', 'raka-preorder-theme' );
	$tag           = ! empty( $options['flash_sale_tag'] ) ? $options['flash_sale_tag'] : __( 'LIMITED TIME DROPS', 'raka-preorder-theme' );
	$desc          = ! empty( $options['flash_sale_desc'] ) ? $options['flash_sale_desc'] : __( 'Exclusive pre-order allocations at special introductory prices. Once stock is claimed, regular rates apply.', 'raka-preorder-theme' );
	$schedule_mode = ! empty( $options['flash_sale_schedule_mode'] ) ? $options['flash_sale_schedule_mode'] : 'fixed';

	$current_time = current_time( 'timestamp' ); // Respect WordPress site timezone

	$timer_label       = __( 'ENDS IN', 'raka-preorder-theme' );
	$remaining_seconds = 0;
	$status            = 'active';
	$target_timestamp  = 0;

	if ( ! $enabled ) {
		$status = 'disabled';
	} elseif ( 'daily' === $schedule_mode ) {
		// Daily Recurring Mode
		$daily_start = ! empty( $options['flash_sale_daily_start'] ) ? $options['flash_sale_daily_start'] : '18:00';
		$daily_end   = ! empty( $options['flash_sale_daily_end'] ) ? $options['flash_sale_daily_end'] : '23:59';
		$camp_start  = ! empty( $options['flash_sale_campaign_start_date'] ) ? $options['flash_sale_campaign_start_date'] : '';
		$camp_end    = ! empty( $options['flash_sale_campaign_end_date'] ) ? $options['flash_sale_campaign_end_date'] : '';

		$camp_start_ts = ! empty( $camp_start ) ? strtotime( $camp_start . ' 00:00:00' ) : 0;
		$camp_end_ts   = ! empty( $camp_end ) ? strtotime( $camp_end . ' 23:59:59' ) : 0;

		$today_ymd      = wp_date( 'Y-m-d', $current_time );
		$today_start_ts = strtotime( $today_ymd . ' ' . $daily_start . ':00' );
		$today_end_ts   = strtotime( $today_ymd . ' ' . $daily_end . ':00' );

		// Overnight support (e.g. 20:00 to 02:00 next day)
		if ( $today_end_ts <= $today_start_ts ) {
			$today_end_ts += 86400;
		}

		if ( $camp_start_ts > 0 && $current_time < $camp_start_ts ) {
			$status            = 'upcoming';
			$target_timestamp  = $camp_start_ts;
			$remaining_seconds = max( 0, $camp_start_ts - $current_time );
			$timer_label       = __( 'STARTS IN', 'raka-preorder-theme' );
		} elseif ( $camp_end_ts > 0 && $current_time > $camp_end_ts ) {
			$status            = 'ended';
			$remaining_seconds = 0;
			$timer_label       = __( 'ENDED', 'raka-preorder-theme' );
		} elseif ( $current_time < $today_start_ts ) {
			// Today's daily flash sale hasn't started yet
			$status            = 'upcoming_today';
			$target_timestamp  = $today_start_ts;
			$remaining_seconds = max( 0, $today_start_ts - $current_time );
			$timer_label       = __( 'STARTS IN', 'raka-preorder-theme' );
		} elseif ( $current_time >= $today_start_ts && $current_time < $today_end_ts ) {
			// Daily flash sale is active now!
			$status            = 'active';
			$target_timestamp  = $today_end_ts;
			$remaining_seconds = max( 0, $today_end_ts - $current_time );
			$timer_label       = __( 'ENDS IN', 'raka-preorder-theme' );
		} else {
			// Today's flash sale finished, counts down to tomorrow's slot
			$tomorrow_start_ts = $today_start_ts + 86400;
			if ( $camp_end_ts > 0 && $tomorrow_start_ts > $camp_end_ts ) {
				$status            = 'ended';
				$remaining_seconds = 0;
				$timer_label       = __( 'ENDED', 'raka-preorder-theme' );
			} else {
				$status            = 'next_drop';
				$target_timestamp  = $tomorrow_start_ts;
				$remaining_seconds = max( 0, $tomorrow_start_ts - $current_time );
				$timer_label       = __( 'NEXT DROP IN', 'raka-preorder-theme' );
			}
		}
	} else {
		// Fixed Date & Time Mode
		$start_str = ! empty( $options['flash_sale_start_time'] ) ? $options['flash_sale_start_time'] : '';
		$end_str   = ! empty( $options['flash_sale_end_time'] ) ? $options['flash_sale_end_time'] : '';

		$start_ts = ! empty( $start_str ) ? strtotime( $start_str ) : 0;
		$end_ts   = ! empty( $end_str ) ? strtotime( $end_str ) : 0;

		if ( $end_ts <= 0 ) {
			$end_ts = strtotime( 'tomorrow midnight' ) + ( 18 * 3600 );
		}

		if ( $start_ts > 0 && $current_time < $start_ts ) {
			$status            = 'upcoming';
			$target_timestamp  = $start_ts;
			$remaining_seconds = max( 0, $start_ts - $current_time );
			$timer_label       = __( 'STARTS IN', 'raka-preorder-theme' );
		} elseif ( $end_ts > 0 && $current_time >= $end_ts ) {
			$status            = 'ended';
			$target_timestamp  = $end_ts;
			$remaining_seconds = 0;
			$timer_label       = __( 'ENDED', 'raka-preorder-theme' );
		} else {
			$status            = 'active';
			$target_timestamp  = $end_ts;
			$remaining_seconds = max( 0, $end_ts - $current_time );
			$timer_label       = __( 'ENDS IN', 'raka-preorder-theme' );
		}
	}

	$is_active = ( 'active' === $status );

	return [
		'enabled'           => $enabled,
		'is_active'         => $is_active,
		'status'            => $status,
		'schedule_mode'     => $schedule_mode,
		'title'             => $title,
		'tag'               => $tag,
		'desc'              => $desc,
		'timer_label'       => $timer_label,
		'target_timestamp'  => $target_timestamp,
		'current_timestamp' => $current_time,
		'remaining_seconds' => $remaining_seconds,
	];
}

/**
 * Retrieve products for the homepage Flash Sale section.
 *
 * @param int $limit Number of products to fetch.
 * @return array
 */
function raka_get_flash_sale_products( int $limit = 6 ): array {
	if ( ! function_exists( 'wc_get_products' ) ) {
		return [];
	}

	// 1. Try fetching on-sale products
	$sale_products = wc_get_products( [
		'limit'   => $limit,
		'status'  => 'publish',
		'on_sale' => true,
		'orderby' => 'date',
		'order'   => 'DESC',
	] );

	// Fallback to recent products if not enough sale items
	if ( count( $sale_products ) < $limit ) {
		$exclude_ids = wp_list_pluck( $sale_products, 'id' );
		$fallback = wc_get_products( [
			'limit'   => $limit - count( $sale_products ),
			'status'  => 'publish',
			'exclude' => $exclude_ids,
			'orderby' => 'popularity',
			'order'   => 'DESC',
		] );
		$sale_products = array_merge( $sale_products, $fallback );
	}

	$results = [];
	foreach ( $sale_products as $prod ) {
		if ( ! $prod instanceof WC_Product ) {
			continue;
		}

		$pid           = $prod->get_id();
		$regular_price = (float) $prod->get_regular_price();
		$sale_price    = (float) $prod->get_sale_price();
		$current_price = (float) $prod->get_price();
		$discount_pct  = 0;

		if ( $regular_price > 0 && $sale_price > 0 && $sale_price < $regular_price ) {
			$discount_pct = round( ( ( $regular_price - $sale_price ) / $regular_price ) * 100 );
		} elseif ( $regular_price > 0 && $current_price < $regular_price ) {
			$discount_pct = round( ( ( $regular_price - $current_price ) / $regular_price ) * 100 );
		} else {
			$discount_pct  = 15 + ( ( $pid % 4 ) * 5 );
			$regular_price = $current_price > 0 ? ( $current_price * 1.25 ) : 2990;
		}

		$img_url = '';
		if ( $prod->get_image_id() ) {
			$img = wp_get_attachment_image_src( $prod->get_image_id(), 'medium_large' );
			if ( $img ) {
				$img_url = $img[0];
			}
		}

		$cats     = wp_get_post_terms( $pid, 'product_cat' );
		$cat_name = ( ! empty( $cats ) && ! is_wp_error( $cats ) ) ? $cats[0]->name : __( 'Gadget', 'raka-preorder-theme' );

		$claimed_pct = 60 + ( ( $pid * 7 ) % 35 );
		$left_count  = max( 2, 18 - ( ( $pid * 3 ) % 15 ) );

		$results[] = [
			'id'            => $pid,
			'name'          => html_entity_decode( $prod->get_name(), ENT_QUOTES, 'UTF-8' ),
			'url'           => $prod->get_permalink(),
			'image_url'     => $img_url,
			'category'      => $cat_name,
			'regular_price' => wc_price( $regular_price ),
			'sale_price'    => wc_price( $current_price > 0 ? $current_price : 2490 ),
			'discount_pct'  => $discount_pct,
			'claimed_pct'   => $claimed_pct,
			'left_count'    => $left_count,
			'is_on_sale'    => $prod->is_on_sale() || $discount_pct > 0,
		];
	}

	return $results;
}

/**
 * Retrieve products and category tabs for the homepage Our Products section.
 *
 * @param int $limit Total limit of products.
 * @return array
 */
function raka_get_homepage_catalog_products( int $limit = 12 ): array {
	if ( ! function_exists( 'wc_get_products' ) ) {
		return [ 'products' => [], 'categories' => [] ];
	}

	$products = wc_get_products( [
		'limit'   => $limit,
		'status'  => 'publish',
		'orderby' => 'date',
		'order'   => 'DESC',
	] );

	$items      = [];
	$categories = [];

	foreach ( $products as $prod ) {
		if ( ! $prod instanceof WC_Product ) {
			continue;
		}

		$pid     = $prod->get_id();
		$img_url = '';
		if ( $prod->get_image_id() ) {
			$img = wp_get_attachment_image_src( $prod->get_image_id(), 'medium_large' );
			if ( $img ) {
				$img_url = $img[0];
			}
		}

		$cats      = wp_get_post_terms( $pid, 'product_cat' );
		$cat_slugs = [];
		$cat_name  = __( 'Gear', 'raka-preorder-theme' );
		if ( ! empty( $cats ) && ! is_wp_error( $cats ) ) {
			$cat_name = $cats[0]->name;
			foreach ( $cats as $c ) {
				$cat_slugs[]            = $c->slug;
				$categories[ $c->slug ] = $c->name;
			}
		}

		$brands = wp_get_post_terms( $pid, 'pwb-brand' );
		if ( empty( $brands ) || is_wp_error( $brands ) ) {
			$brands = wp_get_post_terms( $pid, 'product_brand' );
		}
		$brand_name = ( ! empty( $brands ) && ! is_wp_error( $brands ) ) ? $brands[0]->name : '';

		$items[] = [
			'id'         => $pid,
			'name'       => html_entity_decode( $prod->get_name(), ENT_QUOTES, 'UTF-8' ),
			'url'        => $prod->get_permalink(),
			'image_url'  => $img_url,
			'category'   => $cat_name,
			'cat_slugs'  => $cat_slugs,
			'brand'      => $brand_name,
			'price_html' => $prod->get_price_html() ?: wc_price( $prod->get_price() ),
			'stock'      => $prod->get_stock_status(),
			'is_on_sale' => $prod->is_on_sale(),
			'rating'     => (float) $prod->get_average_rating(),
		];
	}

	return [
		'products'   => $items,
		'categories' => $categories,
	];
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
 * @param array          $args    Optional styling and behavior args.
 */
function raka_render_product_card( $product, array $args = [] ): void {
	if ( is_numeric( $product ) ) {
		$product = wc_get_product( $product );
	}

	if ( ! is_a( $product, 'WC_Product' ) ) {
		return;
	}

	$p_id      = (string) $product->get_id();
	$id_prefix = ! empty( $args['id_prefix'] ) ? (string) $args['id_prefix'] . '-' : '';
	$card_id   = $id_prefix . $p_id;
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
	$p_brands = wp_get_post_terms( (int) $p_id, 'pwb-brand' );
	if ( empty( $p_brands ) || is_wp_error( $p_brands ) ) {
		$p_brands = wp_get_post_terms( (int) $p_id, 'product_brand' );
	}
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

	// Flash Sale Args
	$is_flash_sale = ! empty( $args['is_flash_sale'] );
	$discount_pct  = $args['discount_pct'] ?? 0;
	$claimed_pct   = $args['claimed_pct'] ?? 0;
	$left_count    = $args['left_count'] ?? 0;
	if ( $is_flash_sale && ! $discount_pct ) {
		$regular_price = (float) $product->get_regular_price();
		$current_price = (float) $product->get_price();
		if ( $regular_price > 0 && $current_price < $regular_price ) {
			$discount_pct = round( ( ( $regular_price - $current_price ) / $regular_price ) * 100 );
		} else {
			$discount_pct = 15 + ( ( (int) $p_id % 4 ) * 5 );
		}
	}
	if ( $is_flash_sale && ! $claimed_pct ) {
		$claimed_pct = 60 + ( ( (int) $p_id * 7 ) % 35 );
		$left_count  = max( 2, 18 - ( ( (int) $p_id * 3 ) % 15 ) );
	}

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

	// Custom classes & Data Category attribute
	$extra_classes = ! empty( $args['extra_classes'] ) ? ' ' . trim( $args['extra_classes'] ) : '';
	$data_cat_attr = ! empty( $args['data_category'] ) ? ' data-category="' . esc_attr( $args['data_category'] ) . '"' : '';
	$border_hover  = $is_flash_sale ? 'hover:border-red-500/50 dark:hover:border-red-500/50' : 'hover:border-black/20 dark:hover:border-white/25';
?>
	<div
		id="product-card-<?php echo esc_attr( $card_id ); ?>"
		<?php echo $data_cat_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		class="group relative flex flex-col justify-between rounded-2xl sm:rounded-3xl border border-neutral-200 dark:border-neutral-800/80 <?php echo esc_attr( $border_hover ); ?> bg-white dark:bg-neutral-900/70 p-3 sm:p-5 transition-all duration-300 backdrop-blur-sm<?php echo esc_attr( $extra_classes ); ?>"
	>
		<!-- ── Top Header: Brand Logo & Status / Flash Tag ── -->
		<div id="p-header-<?php echo esc_attr( $card_id ); ?>" class="flex items-center justify-between gap-2 mb-2.5 sm:mb-4 min-h-[20px] sm:min-h-[24px]">
			<div id="p-brand-badge-<?php echo esc_attr( $card_id ); ?>" class="flex items-center">
				<?php if ( $b_logo ) : ?>
					<img
						id="p-brand-img-<?php echo esc_attr( $card_id ); ?>"
						src="<?php echo esc_url( $b_logo ); ?>"
						alt="<?php echo esc_attr( $b_name ); ?>"
						class="h-3.5 sm:h-4.5 w-auto max-w-[65px] sm:max-w-[95px] object-contain opacity-75 group-hover:opacity-100 transition-opacity filter dark:invert"
						loading="lazy"
					/>
				<?php else : ?>
					<span id="p-brand-txt-<?php echo esc_attr( $card_id ); ?>" class="text-xs sm:text-sm font-mono font-semibold tracking-wider uppercase text-neutral-500 dark:text-neutral-400 truncate max-w-[80px] sm:max-w-none">
						<?php echo esc_html( $b_name ); ?>
					</span>
				<?php endif; ?>
			</div>

			<div id="p-status-badge-<?php echo esc_attr( $card_id ); ?>" class="flex items-center">
				<?php if ( $is_flash_sale && $discount_pct > 0 ) : ?>
					<span id="p-badge-flash-<?php echo esc_attr( $card_id ); ?>" class="px-2.5 py-0.5 rounded-full text-[10px] sm:text-xs font-mono uppercase tracking-wider font-bold bg-red-500/10 text-red-600 dark:text-red-400 border border-red-500/20 dark:border-red-500/30">
						-<?php echo (int) $discount_pct; ?>% OFF
					</span>
				<?php elseif ( ! $is_in_stock ) : ?>
					<span id="p-badge-oos-<?php echo esc_attr( $card_id ); ?>" class="px-2 py-0.5 rounded-full text-[10px] sm:text-xs font-mono uppercase tracking-wider font-semibold bg-red-500/10 text-red-600 dark:text-red-400 border border-red-500/20">
						SOLD OUT
					</span>
				<?php elseif ( $is_preorder ) : ?>
					<span id="p-badge-pre-<?php echo esc_attr( $card_id ); ?>" class="flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] sm:text-xs font-mono uppercase tracking-wider font-semibold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
						<span id="p-badge-dot-<?php echo esc_attr( $card_id ); ?>" class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
						PRE-ORDER
					</span>
				<?php elseif ( ! empty( $cat_name ) ) : ?>
					<span id="p-badge-cat-<?php echo esc_attr( $card_id ); ?>" class="px-2 py-0.5 rounded-full text-[10px] sm:text-xs font-mono uppercase tracking-wider text-neutral-500 dark:text-neutral-400 bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700/50">
						<?php echo esc_html( $cat_name ); ?>
					</span>
				<?php endif; ?>
			</div>
		</div>

		<!-- ── Visual Stage: Clickable Image with Dual Hover Crossfade & Floating Action ── -->
		<a
			id="p-stage-link-<?php echo esc_attr( $card_id ); ?>"
			href="<?php echo esc_url( $p_link ); ?>"
			class="relative w-full aspect-square rounded-xl sm:rounded-2xl bg-neutral-100 dark:bg-black/40 border border-neutral-200 dark:border-neutral-800 flex items-center justify-center overflow-hidden mb-3 sm:mb-4 group/stage"
		>
			<img
				id="p-img-main-<?php echo esc_attr( $card_id ); ?>"
				src="<?php echo esc_url( $p_img_url ); ?>"
				alt="<?php echo esc_attr( $p_title ); ?>"
				class="w-full h-full object-cover rounded-xl sm:rounded-2xl transition-all duration-700 ease-out <?php echo $secondary_img_url ? 'group-hover/stage:opacity-0 group-hover/stage:scale-95' : 'group-hover/stage:scale-105'; ?>"
				loading="lazy"
			/>

			<?php if ( $secondary_img_url ) : ?>
				<img
					id="p-img-hover-<?php echo esc_attr( $card_id ); ?>"
					src="<?php echo esc_url( $secondary_img_url ); ?>"
					alt="<?php echo esc_attr( $p_title ); ?>"
					class="absolute inset-0 w-full h-full object-cover rounded-xl sm:rounded-2xl opacity-0 group-hover/stage:opacity-100 group-hover/stage:scale-105 transition-all duration-700 ease-out"
					loading="lazy"
				/>
			<?php endif; ?>

			<div id="p-stage-pill-<?php echo esc_attr( $card_id ); ?>" class="absolute bottom-2.5 sm:bottom-3 left-1/2 -translate-x-1/2 opacity-0 translate-y-2 group-hover/stage:opacity-100 group-hover/stage:translate-y-0 transition-all duration-300 pointer-events-none hidden sm:flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-black/85 dark:bg-white/95 text-white dark:text-black text-xs font-mono uppercase tracking-wider backdrop-blur select-none">
				<span id="p-stage-pill-txt-<?php echo esc_attr( $card_id ); ?>">VIEW GEAR</span>
				<i id="p-stage-pill-ico-<?php echo esc_attr( $card_id ); ?>" class="ri-arrow-right-up-line text-xs"></i>
			</div>
		</a>

		<!-- ── Lower Meta: Color Swatches, Title, Stock Urgency (if Flash Sale), Price & CTA ── -->
		<div id="p-info-wrap-<?php echo esc_attr( $card_id ); ?>" class="flex flex-col justify-between flex-grow space-y-2.5 sm:space-y-3">
			
			<?php if ( ! empty( $color_swatches ) ) : ?>
				<div id="p-swatches-row-<?php echo esc_attr( $card_id ); ?>" class="flex items-center gap-1.5 pt-0.5">
					<?php foreach ( $color_swatches as $idx => $c_name ) :
						$swatch_style = raka_get_swatch_css( $c_name );
					?>
						<span
							id="p-swatch-<?php echo esc_attr( $card_id . '-' . $idx ); ?>"
							title="<?php echo esc_attr( (string) $c_name ); ?>"
							class="w-3 h-3 sm:w-3.5 sm:h-3.5 rounded-full border border-neutral-300 dark:border-neutral-600 shadow-xs flex-shrink-0 transition-transform group-hover:scale-110"
							style="<?php echo esc_attr( $swatch_style ); ?>"
						></span>
					<?php endforeach; ?>
					<?php if ( $total_colors > count( $color_swatches ) ) : ?>
						<span id="p-swatch-more-<?php echo esc_attr( $card_id ); ?>" class="text-[10px] sm:text-xs font-mono text-neutral-400 dark:text-neutral-500 pl-0.5">
							+<?php echo (int) ( $total_colors - count( $color_swatches ) ); ?>
						</span>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<a id="p-title-link-<?php echo esc_attr( $card_id ); ?>" href="<?php echo esc_url( $p_link ); ?>" class="block group/title">
				<h3 id="p-title-<?php echo esc_attr( $card_id ); ?>" class="text-sm sm:text-base font-bold uppercase tracking-tight text-neutral-900 dark:text-neutral-100 line-clamp-2 leading-snug font-hn group-hover/title:text-red-600 dark:group-hover/title:text-red-400 transition-colors">
					<?php echo esc_html( $p_title ); ?>
				</h3>
			</a>

			<?php if ( $is_flash_sale ) : ?>
				<!-- Flash Sale Urgency Progress Bar -->
				<div id="p-flash-progress-wrap-<?php echo esc_attr( $card_id ); ?>" class="space-y-1.5 pt-1">
					<div id="p-flash-progress-meta-<?php echo esc_attr( $card_id ); ?>" class="flex items-center justify-between text-[10px] font-mono text-neutral-500 dark:text-neutral-400">
						<span id="p-flash-claimed-lbl-<?php echo esc_attr( $card_id ); ?>">Claimed: <?php echo esc_html( (string) $claimed_pct ); ?>%</span>
						<span id="p-flash-left-lbl-<?php echo esc_attr( $card_id ); ?>" class="text-red-600 dark:text-red-400 font-bold"><?php echo esc_html( (string) $left_count ); ?> left</span>
					</div>
					<div id="p-flash-progress-track-<?php echo esc_attr( $card_id ); ?>" class="w-full h-1.5 rounded-full bg-neutral-200 dark:bg-neutral-800 overflow-hidden">
						<div id="p-flash-progress-bar-<?php echo esc_attr( $card_id ); ?>" class="h-full bg-gradient-to-r from-red-600 to-red-400 rounded-full transition-all duration-500" style="width: <?php echo esc_attr( (string) $claimed_pct ); ?>%"></div>
					</div>
				</div>
			<?php endif; ?>

			<!-- Price & Add To Cart Action Row -->
			<div id="p-price-row-<?php echo esc_attr( $card_id ); ?>" class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-3 pt-2.5 sm:pt-3 border-t border-neutral-200 dark:border-neutral-800">
				<div id="p-price-val-<?php echo esc_attr( $card_id ); ?>" class="text-sm sm:text-base font-bold font-hn text-neutral-900 dark:text-neutral-100 whitespace-nowrap flex-shrink min-w-0 truncate">
					<?php echo wp_kses_post( $p_price ); ?>
				</div>
				
				<div id="p-action-wrap-<?php echo esc_attr( $card_id ); ?>" class="w-full sm:w-auto flex items-center justify-start sm:justify-end flex-shrink-0">
					<?php if ( ! $is_in_stock ) : ?>
						<span id="p-btn-cart-<?php echo esc_attr( $card_id ); ?>" class="inline-flex items-center justify-center h-8 sm:h-9 px-3.5 sm:px-4 w-full sm:w-auto rounded-xl sm:rounded-full bg-neutral-200/70 dark:bg-neutral-800/70 text-neutral-400 dark:text-neutral-500 text-xs font-mono uppercase tracking-wider cursor-not-allowed select-none whitespace-nowrap">
							SOLD OUT
						</span>
					<?php elseif ( $product->is_type( 'variable' ) ) : ?>
						<a
							id="p-btn-cart-<?php echo esc_attr( $card_id ); ?>"
							href="<?php echo esc_url( $p_link ); ?>"
							class="inline-flex items-center justify-center gap-1.5 h-8 sm:h-9 px-3.5 sm:px-4 w-full sm:w-auto rounded-xl sm:rounded-full bg-neutral-900 text-white dark:bg-white dark:text-neutral-900 hover:bg-neutral-800 dark:hover:bg-neutral-100 text-xs font-mono uppercase tracking-wider font-semibold active:scale-95 transition-all whitespace-nowrap group/btn"
						>
							<i id="p-btn-cart-ico-<?php echo esc_attr( $card_id ); ?>" class="ri-arrow-right-line text-xs flex-shrink-0"></i>
							<span id="p-btn-cart-txt-<?php echo esc_attr( $card_id ); ?>" class="whitespace-nowrap">READ MORE</span>
						</a>
					<?php else : ?>
						<button
							id="p-btn-cart-<?php echo esc_attr( $card_id ); ?>"
							type="button"
							onclick="window.rakaCardAddToCart(<?php echo (int) $p_id; ?>, this)"
							class="inline-flex items-center justify-center gap-1.5 h-8 sm:h-9 px-3.5 sm:px-4 w-full sm:w-auto rounded-xl sm:rounded-full bg-neutral-900 text-white dark:bg-white dark:text-neutral-900 hover:bg-neutral-800 dark:hover:bg-neutral-100 text-xs font-mono uppercase tracking-wider font-semibold active:scale-95 transition-all group/btn cursor-pointer whitespace-nowrap"
						>
							<i id="p-btn-cart-ico-<?php echo esc_attr( $card_id ); ?>" class="ri-shopping-bag-3-line text-xs flex-shrink-0"></i>
							<span id="p-btn-cart-txt-<?php echo esc_attr( $card_id ); ?>" class="whitespace-nowrap">ADD TO CART</span>
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
 * Add WooCommerce Cart fragments to update cart badge count and drawer HTML via AJAX.
 *
 * @param array $fragments Existing fragments.
 * @return array
 */
function raka_add_to_cart_fragments( array $fragments ): array {
	$cart_count = ( function_exists( 'WC' ) && WC()->cart ) ? WC()->cart->get_cart_contents_count() : 0;
	$scale_class = ( $cart_count > 0 ) ? 'scale-100' : 'scale-0';
	
	$html = '<span id="raka-cart-count" class="raka-cart-count-badge absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-black text-white dark:bg-white dark:text-black text-[10px] font-bold font-mono flex items-center justify-center border border-white dark:border-black transition-transform duration-300 ' . esc_attr( $scale_class ) . '">' . esc_html( (string) $cart_count ) . '</span>';
	
	$fragments['#raka-cart-count'] = $html;
	$fragments['#overlay-cart-count'] = '<span id="overlay-cart-count" class="raka-cart-count-badge absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-black text-white dark:bg-white dark:text-black text-[10px] font-bold font-mono flex items-center justify-center border border-white dark:border-black transition-transform duration-300 ' . esc_attr( $scale_class ) . '">' . esc_html( (string) $cart_count ) . '</span>';
	$fragments['#raka-cart-drawer-count'] = '<span id="raka-cart-drawer-count">' . esc_html( (string) $cart_count ) . '</span>';
	$fragments['#raka-cart-drawer-content'] = raka_get_cart_drawer_html();

	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'raka_add_to_cart_fragments' );

/**
 * Render inner content of Cart Drawer (items list, empty state, subtotal, actions).
 *
 * @return string
 */
function raka_get_cart_drawer_html(): string {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return '<div id="raka-cart-drawer-content" class="p-6 text-center text-sm text-neutral-400">WooCommerce is inactive.</div>';
	}

	$cart         = WC()->cart;
	$cart_items   = $cart->get_cart();
	$subtotal     = $cart->get_cart_subtotal();
	$checkout_url = wc_get_checkout_url();
	$cart_url     = wc_get_cart_url();

	ob_start();
	?>
	<div id="raka-cart-drawer-content" class="flex flex-col h-full justify-between">
		<!-- ── Items List ── -->
		<div id="raka-cart-drawer-items" class="flex-1 overflow-y-auto px-6 py-4 divide-y divide-black/5 dark:divide-white/5 space-y-4">
			<?php if ( empty( $cart_items ) ) : ?>
				<div id="raka-cart-empty-state" class="flex flex-col items-center justify-center h-full min-h-[260px] py-16 text-center space-y-4">
					<div id="raka-cart-empty-icon-wrap" class="w-16 h-16 rounded-full bg-black/5 dark:bg-white/5 flex items-center justify-center text-neutral-400">
						<i id="raka-cart-empty-icon" class="ri-shopping-bag-3-line text-3xl"></i>
					</div>
					<div id="raka-cart-empty-text-wrap" class="space-y-1">
						<h4 id="raka-cart-empty-title" class="text-base font-bold uppercase tracking-wider text-neutral-900 dark:text-white font-hn">YOUR BAG IS EMPTY</h4>
						<p id="raka-cart-empty-desc" class="text-xs text-neutral-500 dark:text-neutral-400 font-light font-hn max-w-xs">You have no items in your shopping bag yet.</p>
					</div>
					<div id="raka-cart-empty-action-wrap" class="pt-2">
						<a id="raka-cart-empty-shop-btn" href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop' ) ); ?>" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full bg-neutral-900 dark:bg-white text-white dark:text-neutral-900 text-xs font-bold uppercase tracking-widest hover:opacity-80 transition-opacity shadow-sm">
							<span id="raka-cart-empty-shop-txt">START SHOPPING</span>
							<i id="raka-cart-empty-shop-ico" class="ri-arrow-right-line text-sm"></i>
						</a>
					</div>
				</div>
			<?php else : ?>
				<?php
				foreach ( $cart_items as $cart_item_key => $cart_item ) :
					$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
					$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

					if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) :
						$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
						$thumbnail         = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image( 'woocommerce_thumbnail', [ 'class' => 'w-full h-full object-contain p-1' ] ), $cart_item, $cart_item_key );
						$product_price     = apply_filters( 'woocommerce_cart_item_price', $cart->get_product_price( $_product ), $cart_item, $cart_item_key );
				?>
					<div id="cart-item-<?php echo esc_attr( $cart_item_key ); ?>" class="flex items-center gap-4 pt-4 first:pt-0 group">
						<!-- Product Thumbnail -->
						<div id="cart-thumb-<?php echo esc_attr( $cart_item_key ); ?>" class="w-16 h-16 sm:w-20 sm:h-20 rounded-xl bg-neutral-100 dark:bg-neutral-900 border border-black/5 dark:border-white/5 flex items-center justify-center shrink-0 overflow-hidden">
							<?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</div>

						<!-- Details -->
						<div id="cart-details-<?php echo esc_attr( $cart_item_key ); ?>" class="flex-1 min-w-0 flex flex-col justify-between py-0.5">
							<div id="cart-info-<?php echo esc_attr( $cart_item_key ); ?>" class="space-y-0.5">
								<h5 id="cart-title-<?php echo esc_attr( $cart_item_key ); ?>" class="text-xs sm:text-sm font-bold uppercase tracking-tight text-neutral-900 dark:text-white truncate font-hn">
									<?php if ( $product_permalink ) : ?>
										<a id="cart-link-<?php echo esc_attr( $cart_item_key ); ?>" href="<?php echo esc_url( $product_permalink ); ?>" class="hover:opacity-60 transition-opacity">
											<?php echo esc_html( $_product->get_name() ); ?>
										</a>
									<?php else : ?>
										<?php echo esc_html( $_product->get_name() ); ?>
									<?php endif; ?>
								</h5>

								<?php if ( ! empty( $cart_item['variation'] ) ) : ?>
									<div id="cart-var-<?php echo esc_attr( $cart_item_key ); ?>" class="text-[10px] sm:text-xs text-neutral-400 font-mono">
										<?php echo esc_html( wc_get_formatted_cart_item_data( $cart_item, true ) ); ?>
									</div>
								<?php endif; ?>
							</div>

							<div id="cart-meta-<?php echo esc_attr( $cart_item_key ); ?>" class="flex items-center justify-between pt-2">
								<span id="cart-qty-<?php echo esc_attr( $cart_item_key ); ?>" class="text-[11px] sm:text-xs font-mono text-neutral-500 dark:text-neutral-400">
									QTY: <?php echo esc_html( (string) $cart_item['quantity'] ); ?> × <?php echo wp_kses_post( $product_price ); ?>
								</span>

								<!-- Remove Action -->
								<button
									id="cart-remove-<?php echo esc_attr( $cart_item_key ); ?>"
									type="button"
									onclick="window.rakaRemoveCartItem('<?php echo esc_attr( $cart_item_key ); ?>', this)"
									class="text-xs text-neutral-400 hover:text-red-500 transition-colors p-1 cursor-pointer focus:outline-none"
									aria-label="Remove item"
									title="Remove item"
								>
									<i id="cart-del-icon-<?php echo esc_attr( $cart_item_key ); ?>" class="ri-delete-bin-line text-sm"></i>
								</button>
							</div>
						</div>
					</div>
				<?php
					endif;
				endforeach;
				?>
			<?php endif; ?>
		</div>

		<!-- ── Footer Summary & Checkout ── -->
		<?php if ( ! empty( $cart_items ) ) : ?>
			<div id="raka-cart-drawer-footer" class="p-6 border-t border-black/10 dark:border-white/10 bg-neutral-50/50 dark:bg-neutral-900/50 space-y-4 shrink-0">
				<div id="raka-cart-subtotal-row" class="flex items-center justify-between">
					<span id="raka-cart-subtotal-lbl" class="text-xs font-mono uppercase tracking-widest text-neutral-500 dark:text-neutral-400">SUBTOTAL</span>
					<span id="raka-cart-subtotal-val" class="text-base sm:text-lg font-bold text-neutral-900 dark:text-white font-mono"><?php echo wp_kses_post( $subtotal ); ?></span>
				</div>

				<p id="raka-cart-tax-note" class="text-[10px] text-neutral-400 dark:text-neutral-500 font-light font-hn text-center">
					Shipping, taxes, and promotional discounts calculated at checkout.
				</p>

				<div id="raka-cart-actions-wrap" class="space-y-2.5">
					<a id="raka-cart-checkout-btn" href="<?php echo esc_url( $checkout_url ); ?>" class="w-full flex items-center justify-center gap-2 py-3 sm:py-3.5 px-6 rounded-full bg-neutral-900 dark:bg-white text-white dark:text-neutral-900 font-bold text-xs sm:text-sm uppercase tracking-widest hover:opacity-90 transition-opacity shadow-sm">
						<span id="raka-cart-checkout-txt">CHECKOUT NOW</span>
						<i id="raka-cart-checkout-icon" class="ri-arrow-right-line text-base"></i>
					</a>

					<a id="raka-cart-view-btn" href="<?php echo esc_url( $cart_url ); ?>" class="w-full flex items-center justify-center py-2 px-6 rounded-full border border-black/15 dark:border-white/20 text-neutral-800 dark:text-neutral-200 font-medium text-xs uppercase tracking-widest hover:bg-black/5 dark:hover:bg-white/10 transition-colors">
						<span id="raka-cart-view-txt">VIEW FULL BAG</span>
					</a>
				</div>
			</div>
		<?php endif; ?>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * Render Complete Cart Drawer Wrapper Modal in theme footer.
 */
function raka_render_cart_drawer(): void {
	if ( ! function_exists( 'WC' ) ) {
		return;
	}
	$cart_count = ( WC()->cart ) ? WC()->cart->get_cart_contents_count() : 0;
	?>
	<!-- ── Raka Luxury Cart Drawer Modal (Slide-Over) ── -->
	<div id="raka-cart-drawer" class="fixed inset-0 z-[100] pointer-events-none transition-all duration-500" aria-hidden="true">
		<!-- Backdrop -->
		<div id="raka-cart-backdrop" onclick="window.closeRakaCart()" class="absolute inset-0 bg-black/60 dark:bg-black/80 backdrop-blur-xs opacity-0 transition-opacity duration-500 cursor-pointer"></div>

		<!-- Slide-Over Panel -->
		<div id="raka-cart-panel" class="absolute top-0 right-0 bottom-0 w-full max-w-md sm:max-w-lg bg-white dark:bg-neutral-950 shadow-2xl flex flex-col justify-between translate-x-full transition-transform duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] z-10 border-l border-black/10 dark:border-white/10">
			<!-- Header -->
			<div id="raka-cart-drawer-header" class="flex items-center justify-between px-6 py-5 border-b border-black/10 dark:border-white/10 shrink-0">
				<div id="raka-cart-drawer-title-wrap" class="flex items-center gap-3">
					<h3 id="raka-cart-drawer-heading" class="text-sm sm:text-base font-extrabold uppercase tracking-[0.2em] text-neutral-900 dark:text-white font-hn">SHOPPING BAG</h3>
					<span id="raka-cart-drawer-badge" class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-black/5 dark:bg-white/10 text-neutral-700 dark:text-neutral-300">
						<span id="raka-cart-drawer-count"><?php echo esc_html( (string) $cart_count ); ?></span> ITEMS
					</span>
				</div>

				<!-- Close Button -->
				<button
					id="raka-cart-close-btn"
					type="button"
					onclick="window.closeRakaCart()"
					class="w-9 h-9 sm:w-10 sm:h-10 flex items-center justify-center rounded-full border border-black/10 dark:border-white/15 bg-black/5 dark:bg-white/10 hover:bg-black/10 dark:hover:bg-white/20 text-neutral-900 dark:text-white transition-all duration-300 hover:scale-105 cursor-pointer focus:outline-none"
					aria-label="Close cart"
					title="Close cart"
				>
					<i id="raka-cart-close-icon" class="ri-close-line text-xl transition-transform duration-300 hover:rotate-90"></i>
				</button>
			</div>

			<!-- Dynamic Content Container -->
			<div id="raka-cart-drawer-body" class="flex-1 overflow-hidden relative">
				<?php echo raka_get_cart_drawer_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		</div>
	</div>
	<?php
}

/**
 * AJAX handler for removing item from cart drawer.
 */
function raka_ajax_remove_cart_item(): void {
	$cart_item_key = isset( $_POST['cart_item_key'] ) ? sanitize_text_field( wp_unslash( $_POST['cart_item_key'] ) ) : '';

	if ( $cart_item_key && function_exists( 'WC' ) && WC()->cart ) {
		WC()->cart->remove_cart_item( $cart_item_key );
		if ( class_exists( 'WC_AJAX' ) ) {
			WC_AJAX::get_refreshed_fragments();
		}
	}
	wp_die();
}
add_action( 'wp_ajax_raka_remove_cart_item', 'raka_ajax_remove_cart_item' );
add_action( 'wp_ajax_nopriv_raka_remove_cart_item', 'raka_ajax_remove_cart_item' );

/**
 * Render the unified Luxury Gadget & Pre-Order Site Footer.
 */
function raka_render_site_footer(): void {
	$contact_options = function_exists( 'raka_get_contact_options' ) ? raka_get_contact_options() : [
		'line_id'      => '@rakagadget',
		'line_url'     => 'https://line.me/ti/p/@rakagadget',
		'facebook_url' => 'https://facebook.com/rakagadget',
		'address'      => '123/45 Gadget District, Bangkok 10110',
		'hours'        => 'Mon - Sun: 10:00 - 20:00',
	];
	?>
	<footer id="raka-site-footer" class="relative z-20 bg-white dark:bg-neutral-950 text-neutral-900 dark:text-white border-t border-neutral-200 dark:border-neutral-800/80 transition-colors duration-300 overflow-hidden">
		<!-- Ambient Background Glow Accents -->
		<div id="raka-footer-glow-bg" class="absolute top-0 left-1/2 -translate-x-1/2 w-3/4 h-32 bg-gradient-to-b from-red-500/5 dark:from-red-500/10 to-transparent pointer-events-none blur-3xl"></div>

		<!-- Main Multi-Column Footer Grid -->
		<div id="raka-footer-main-grid-wrap" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
			<div id="raka-footer-main-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-10 lg:gap-8">
				
				<!-- Col 1: Brand & Mission Statement -->
				<div id="raka-footer-col-brand" class="lg:col-span-2 space-y-5">
					<div id="raka-footer-brand-title-wrap" class="flex items-center gap-3">
						<div id="raka-footer-brand-logo-badge" class="w-9 h-9 rounded-xl bg-red-600 text-white flex items-center justify-center font-mono font-black text-base shadow-md shadow-red-600/20">
							R
						</div>
						<div>
							<h4 id="raka-footer-brand-name" class="text-lg font-black font-hn tracking-tight text-neutral-950 dark:text-white leading-none">
								RAKA GADGET
							</h4>
							<span id="raka-footer-brand-sub" class="text-[10px] font-mono tracking-widest text-neutral-500 dark:text-neutral-400 uppercase">
								PRE-ORDER & BESPOKE GEAR
							</span>
						</div>
					</div>

					<p id="raka-footer-brand-bio" class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 font-light font-hn leading-relaxed max-w-sm">
						<?php esc_html_e( 'Curator and authorized distributor of premium custom mechanical keyboards, magnetic switches, esports gaming mice, and audiophile gear in Thailand.', 'raka-preorder-theme' ); ?>
					</p>

					<!-- Trust Value Points -->
					<div id="raka-footer-trust-pills" class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-2">
						<div id="raka-footer-trust-1" class="flex items-center gap-2 text-xs font-mono text-neutral-700 dark:text-neutral-300">
							<i id="raka-footer-trust-ico-1" class="ri-checkbox-circle-fill text-red-500 text-sm shrink-0"></i>
							<span>100% Genuine Authorized</span>
						</div>
						<div id="raka-footer-trust-2" class="flex items-center gap-2 text-xs font-mono text-neutral-700 dark:text-neutral-300">
							<i id="raka-footer-trust-ico-2" class="ri-shield-check-fill text-red-500 text-sm shrink-0"></i>
							<span>QC Inspected & Verified</span>
						</div>
						<div id="raka-footer-trust-3" class="flex items-center gap-2 text-xs font-mono text-neutral-700 dark:text-neutral-300">
							<i id="raka-footer-trust-ico-3" class="ri-truck-fill text-red-500 text-sm shrink-0"></i>
							<span>Express Insured Shipping</span>
						</div>
						<div id="raka-footer-trust-4" class="flex items-center gap-2 text-xs font-mono text-neutral-700 dark:text-neutral-300">
							<i id="raka-footer-trust-ico-4" class="ri-customer-service-2-fill text-red-500 text-sm shrink-0"></i>
							<span>Dedicated Specialist Help</span>
						</div>
					</div>
				</div>

				<!-- Col 2: Shop & Categories -->
				<div id="raka-footer-col-shop" class="space-y-4">
					<h5 id="raka-footer-shop-heading" class="text-xs font-mono uppercase tracking-[0.15em] font-bold text-neutral-950 dark:text-white pb-1 border-b border-neutral-200 dark:border-neutral-800">
						<?php esc_html_e( 'SHOP & CATALOG', 'raka-preorder-theme' ); ?>
					</h5>
					<ul id="raka-footer-shop-links" class="space-y-2.5 text-xs sm:text-sm font-hn font-light text-neutral-600 dark:text-neutral-400">
						<li id="raka-footer-link-shop-1">
							<a id="raka-footer-a-shop-1" href="<?php echo esc_url( home_url( '/shop/?filter_cat=keyboards' ) ); ?>" class="hover:text-red-600 dark:hover:text-white transition-colors duration-200 flex items-center justify-between">
								<span><?php esc_html_e( 'Custom Keyboards', 'raka-preorder-theme' ); ?></span>
							</a>
						</li>
						<li id="raka-footer-link-shop-2">
							<a id="raka-footer-a-shop-2" href="<?php echo esc_url( home_url( '/shop/?filter_cat=switches' ) ); ?>" class="hover:text-red-600 dark:hover:text-white transition-colors duration-200 flex items-center justify-between">
								<span><?php esc_html_e( 'Switches & Keycaps', 'raka-preorder-theme' ); ?></span>
							</a>
						</li>
						<li id="raka-footer-link-shop-3">
							<a id="raka-footer-a-shop-3" href="<?php echo esc_url( home_url( '/shop/?filter_cat=mice' ) ); ?>" class="hover:text-red-600 dark:hover:text-white transition-colors duration-200 flex items-center justify-between">
								<span><?php esc_html_e( 'Esports Mice', 'raka-preorder-theme' ); ?></span>
							</a>
						</li>
						<li id="raka-footer-link-shop-4">
							<a id="raka-footer-a-shop-4" href="<?php echo esc_url( home_url( '/shop/?filter_cat=audio' ) ); ?>" class="hover:text-red-600 dark:hover:text-white transition-colors duration-200 flex items-center justify-between">
								<span><?php esc_html_e( 'Studio Audio & IEMs', 'raka-preorder-theme' ); ?></span>
							</a>
						</li>
						<li id="raka-footer-link-shop-5">
							<a id="raka-footer-a-shop-5" href="<?php echo esc_url( home_url( '/#raka-flashsale-section' ) ); ?>" class="inline-flex items-center gap-1.5 text-red-600 dark:text-red-400 font-bold hover:underline">
								<i id="raka-footer-link-flash-ico" class="ri-flashlight-fill text-xs"></i>
								<span><?php esc_html_e( 'Flash Sale Deals', 'raka-preorder-theme' ); ?></span>
							</a>
						</li>
					</ul>
				</div>

				<!-- Col 3: Customer Care & Policy -->
				<div id="raka-footer-col-help" class="space-y-4">
					<h5 id="raka-footer-help-heading" class="text-xs font-mono uppercase tracking-[0.15em] font-bold text-neutral-950 dark:text-white pb-1 border-b border-neutral-200 dark:border-neutral-800">
						<?php esc_html_e( 'CUSTOMER CARE', 'raka-preorder-theme' ); ?>
					</h5>
					<ul id="raka-footer-help-links" class="space-y-2.5 text-xs sm:text-sm font-hn font-light text-neutral-600 dark:text-neutral-400">
						<li id="raka-footer-link-help-1">
							<a id="raka-footer-a-help-1" href="<?php echo esc_url( home_url( '/pre-order-policy' ) ); ?>" class="hover:text-red-600 dark:hover:text-white transition-colors duration-200">
								<?php esc_html_e( 'Pre-Order Guidelines', 'raka-preorder-theme' ); ?>
							</a>
						</li>
						<li id="raka-footer-link-help-2">
							<a id="raka-footer-a-help-2" href="<?php echo esc_url( home_url( '/return-policy' ) ); ?>" class="hover:text-red-600 dark:hover:text-white transition-colors duration-200">
								<?php esc_html_e( 'Return & Defect Policy', 'raka-preorder-theme' ); ?>
							</a>
						</li>
						<li id="raka-footer-link-help-3">
							<a id="raka-footer-a-help-3" href="<?php echo esc_url( home_url( '/shipping-tracking' ) ); ?>" class="hover:text-red-600 dark:hover:text-white transition-colors duration-200">
								<?php esc_html_e( 'Track Shipment', 'raka-preorder-theme' ); ?>
							</a>
						</li>
						<li id="raka-footer-link-help-4">
							<a id="raka-footer-a-help-4" href="<?php echo esc_url( home_url( '/my-account' ) ); ?>" class="hover:text-red-600 dark:hover:text-white transition-colors duration-200">
								<?php esc_html_e( 'My Orders & Account', 'raka-preorder-theme' ); ?>
							</a>
						</li>
						<li id="raka-footer-link-help-5">
							<a id="raka-footer-a-help-5" href="<?php echo esc_url( home_url( '/payment-methods' ) ); ?>" class="hover:text-red-600 dark:hover:text-white transition-colors duration-200">
								<?php esc_html_e( 'Payment & 0% Installments', 'raka-preorder-theme' ); ?>
							</a>
						</li>
					</ul>
				</div>

				<!-- Col 4: Official Showroom & Direct Contact -->
				<div id="raka-footer-col-contact" class="space-y-4">
					<h5 id="raka-footer-contact-heading" class="text-xs font-mono uppercase tracking-[0.15em] font-bold text-neutral-950 dark:text-white pb-1 border-b border-neutral-200 dark:border-neutral-800">
						<?php esc_html_e( 'SHOWROOM & SUPPORT', 'raka-preorder-theme' ); ?>
					</h5>

					<div id="raka-footer-contact-details" class="space-y-3 text-xs sm:text-sm font-hn font-light text-neutral-600 dark:text-neutral-400">
						<!-- Active Hours Badge -->
						<div id="raka-footer-hours-pill" class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-[11px] font-mono font-medium">
							<span id="raka-footer-green-dot" class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
							<span><?php echo esc_html( $contact_options['hours'] ?: 'Mon - Sun: 10:00 - 20:00' ); ?></span>
						</div>

						<p id="raka-footer-addr" class="text-xs leading-relaxed text-neutral-700 dark:text-neutral-300">
							<?php echo esc_html( $contact_options['address'] ?: '123/45 Gadget District, Bangkok 10110' ); ?>
						</p>

						<!-- Quick Contact Buttons -->
						<div id="raka-footer-contact-actions" class="flex flex-col gap-2 pt-1">
							<?php if ( ! empty( $contact_options['line_url'] ) ) : ?>
								<a id="raka-footer-btn-line" href="<?php echo esc_url( $contact_options['line_url'] ); ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center gap-2 px-3.5 py-2 rounded-xl bg-[#06C755] hover:bg-[#05b34c] text-white font-mono text-xs font-bold transition-colors shadow-sm">
									<i id="raka-footer-line-ico" class="ri-line-fill text-sm"></i>
									<span>LINE: <?php echo esc_html( $contact_options['line_id'] ?: '@rakagadget' ); ?></span>
								</a>
							<?php endif; ?>

							<?php if ( ! empty( $contact_options['facebook_url'] ) ) : ?>
								<a id="raka-footer-btn-fb" href="<?php echo esc_url( $contact_options['facebook_url'] ); ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center gap-2 px-3.5 py-2 rounded-xl bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 text-neutral-800 dark:text-neutral-200 font-mono text-xs font-medium transition-colors">
									<i id="raka-footer-fb-ico" class="ri-facebook-circle-fill text-sm text-blue-600"></i>
									<span>Facebook Official</span>
								</a>
							<?php endif; ?>
						</div>
					</div>
				</div>

			</div>
		</div>

		<!-- Bottom Copyright & Payment Methods Bar -->
		<div id="raka-footer-bottom-bar" class="border-t border-neutral-200 dark:border-neutral-800/80 py-6 px-4 sm:px-6 lg:px-8 bg-neutral-50 dark:bg-black/60">
			<div id="raka-footer-bottom-container" class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-4 text-xs font-mono text-neutral-500 dark:text-neutral-400">
				<!-- Copyright & Security -->
				<div id="raka-footer-copy-wrap" class="flex flex-col sm:flex-row items-center gap-2 sm:gap-4 text-center sm:text-left">
					<span id="raka-footer-copyright-text">
						&copy; <?php echo esc_html( date( 'Y' ) ); ?> RAKA GADGET. ALL RIGHTS RESERVED.
					</span>
					<span id="raka-footer-copy-sep" class="hidden sm:inline text-neutral-300 dark:text-neutral-700">|</span>
					<span id="raka-footer-security-text" class="text-[11px] text-neutral-400 dark:text-neutral-500">
						256-Bit SSL Encrypted Pre-Order Checkout
					</span>
				</div>

				<!-- Back to Top Button -->
				<button id="raka-footer-back-to-top" type="button" onclick="window.scrollTo({ top: 0, behavior: 'smooth' });" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-neutral-200 dark:border-neutral-800 hover:border-red-500 dark:hover:border-red-500 hover:text-red-600 dark:hover:text-red-400 transition-colors text-[11px] uppercase tracking-wider cursor-pointer">
					<span id="raka-footer-top-txt"><?php esc_html_e( 'BACK TO TOP', 'raka-preorder-theme' ); ?></span>
					<i id="raka-footer-top-ico" class="ri-arrow-up-line"></i>
				</button>
			</div>
		</div>
	</footer>
	<?php
}






