<?php
/**
 * React-Powered Theme Options Page for Raka Preorder Theme
 * Styled using @wordpress/components and @wordpress/scripts design system.
 *
 * @package raka-preorder-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the Theme Options admin menu and submenu.
 */
function raka_register_theme_options_menu() {
	// Top-level menu in Admin sidebar: "Theme Settings"
	add_menu_page(
		__( 'Theme Settings', 'raka-preorder-theme' ),
		__( 'Theme Settings', 'raka-preorder-theme' ),
		'manage_options',
		'raka-theme-settings',
		'raka_render_theme_options_page',
		'dashicons-art',
		58
	);

	// Submenu 1: Product Cards (Repeater)
	add_submenu_page(
		'raka-theme-settings',
		__( 'Product Cards (Repeater)', 'raka-preorder-theme' ),
		__( 'Product Cards (Repeater)', 'raka-preorder-theme' ),
		'manage_options',
		'raka-theme-settings',
		'raka_render_theme_options_page'
	);

	// Submenu 2: Hero Media & Title
	add_submenu_page(
		'raka-theme-settings',
		__( 'Hero Media & Title', 'raka-preorder-theme' ),
		__( 'Hero Media & Title', 'raka-preorder-theme' ),
		'manage_options',
		'raka-theme-settings-media',
		'raka_render_theme_options_page'
	);

	// Submenu 3: Contact & Store Info
	add_submenu_page(
		'raka-theme-settings',
		__( 'Contact & Store Info', 'raka-preorder-theme' ),
		__( 'Contact & Store Info', 'raka-preorder-theme' ),
		'manage_options',
		'raka-theme-settings-contact',
		'raka_render_theme_options_page'
	);

	// Submenu 4: Flash Sale & Our Products
	add_submenu_page(
		'raka-theme-settings',
		__( 'Flash Sale & Our Products', 'raka-preorder-theme' ),
		__( 'Products & Deals', 'raka-preorder-theme' ),
		'manage_options',
		'raka-theme-settings-products',
		'raka_render_theme_options_page'
	);

	// Submenu 5: Pre-Order & Turnstile Security
	add_submenu_page(
		'raka-theme-settings',
		__( 'Pre-Order & Security', 'raka-preorder-theme' ),
		__( 'Pre-Order & Security', 'raka-preorder-theme' ),
		'manage_options',
		'raka-theme-settings-security',
		'raka_render_theme_options_page'
	);

	// Submenu 6: Loyalty & Rewards Engine
	add_submenu_page(
		'raka-theme-settings',
		__( 'Loyalty & Rewards', 'raka-preorder-theme' ),
		__( 'Loyalty & Rewards', 'raka-preorder-theme' ),
		'manage_options',
		'raka-theme-settings-loyalty',
		'raka_render_theme_options_page'
	);
}
add_action( 'admin_menu', 'raka_register_theme_options_menu' );

/**
 * Enqueue scripts and styles for the Theme Settings admin page.
 *
 * @param string $hook The current admin page hook.
 */
function raka_enqueue_theme_settings_assets( $hook ) {
	$is_settings_page = ( isset( $_GET['page'] ) && 0 === strpos( (string) $_GET['page'], 'raka-theme-settings' ) )
		|| false !== strpos( $hook, 'raka-theme-settings' );

	if ( ! $is_settings_page ) {
		return;
	}

	// Enqueue WordPress media library modal scripts & styles
	wp_enqueue_media();

	// Enqueue WordPress components stylesheet
	wp_enqueue_style( 'wp-components' );

	// Enqueue RemixIcon (No emojis in the UI)
	wp_enqueue_style(
		'raka-remixicon',
		'https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css',
		[],
		'4.5.0'
	);

	// Enqueue Select2 stylesheet & script
	wp_enqueue_style(
		'raka-select2-css',
		'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css',
		[],
		'4.1.0'
	);
	wp_enqueue_script(
		'raka-select2-js',
		'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js',
		[ 'jquery' ],
		'4.1.0',
		true
	);

	$asset_file = get_template_directory() . '/build/index.asset.php';
	$asset      = file_exists( $asset_file )
		? require $asset_file
		: [ 'dependencies' => [ 'wp-element', 'wp-components', 'wp-i18n', 'wp-api-fetch', 'jquery' ], 'version' => '1.0.0' ];

	$dependencies = array_unique( array_merge( $asset['dependencies'], [ 'jquery', 'raka-select2-js' ] ) );

	wp_enqueue_script(
		'raka-theme-settings-react',
		get_template_directory_uri() . '/build/index.js',
		$dependencies,
		file_exists( get_template_directory() . '/build/index.js' ) ? filemtime( get_template_directory() . '/build/index.js' ) : '1.0.0',
		true
	);

	// Localize REST API base and nonce
	wp_localize_script(
		'raka-theme-settings-react',
		'rakaSettingsData',
		[
			'apiUrl' => esc_url_raw( rest_url( 'raka/v1' ) ),
			'nonce'  => wp_create_nonce( 'wp_rest' ),
		]
	);

	// Inject Select2 custom skin styling for WordPress admin
	$custom_select2_css = '
		.select2-container .select2-selection--single {
			height: 36px !important;
			border: 1px solid #8c8f94 !important;
			border-radius: 4px !important;
			padding: 4px 8px !important;
			display: flex !important;
			align-items: center !important;
			background-color: #fff !important;
		}
		.select2-container--default .select2-selection--single .select2-selection__rendered {
			color: #2c3338 !important;
			line-height: 28px !important;
			padding-left: 0 !important;
			font-size: 13px !important;
		}
		.select2-container--default .select2-selection--single .select2-selection__arrow {
			height: 34px !important;
			right: 6px !important;
		}
		.select2-container--default.select2-container--focus .select2-selection--single,
		.select2-container--default.select2-container--open .select2-selection--single {
			border-color: #2271b1 !important;
			box-shadow: 0 0 0 1px #2271b1 !important;
			outline: 2px solid transparent !important;
		}
		.select2-dropdown {
			border-color: #2271b1 !important;
			border-radius: 4px !important;
			box-shadow: 0 3px 10px rgba(0,0,0,0.15) !important;
			font-size: 13px !important;
			z-index: 999999 !important;
		}
		.select2-container--default .select2-results__option--highlighted[aria-selected] {
			background-color: #2271b1 !important;
		}
		.select2-search--dropdown .select2-search__field {
			padding: 6px 8px !important;
			border: 1px solid #8c8f94 !important;
			border-radius: 3px !important;
		}
	';
	wp_add_inline_style( 'raka-select2-css', $custom_select2_css );
}
add_action( 'admin_enqueue_scripts', 'raka_enqueue_theme_settings_assets' );

/**
 * Register REST API Endpoints for React Settings.
 */
function raka_register_settings_rest_routes() {
	register_rest_route(
		'raka/v1',
		'/settings',
		[
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => 'raka_rest_get_settings',
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			],
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => 'raka_rest_save_settings',
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			],
		]
	);
}
add_action( 'rest_api_init', 'raka_register_settings_rest_routes' );

/**
 * REST Callback: Get Settings + WooCommerce Data
 *
 * @return WP_REST_Response
 */
function raka_rest_get_settings() {
	$options = raka_get_all_options();

	// Format categories for React SelectControl
	$categories = [];
	if ( taxonomy_exists( 'product_cat' ) ) {
		$terms = get_terms( [
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
		] );
		if ( ! is_wp_error( $terms ) ) {
			foreach ( $terms as $t ) {
				$categories[] = [
					'id'    => $t->term_id,
					'name'  => $t->name,
					'count' => $t->count,
				];
			}
		}
	}

	// Format brands for React SelectControl
	$brands = [];
	$brand_taxonomies = [ 'pwb-brand', 'product_brand', 'brand', 'yith_product_brand', 'pa_brand' ];
	foreach ( $brand_taxonomies as $tax ) {
		if ( taxonomy_exists( $tax ) ) {
			$terms = get_terms( [
				'taxonomy'   => $tax,
				'hide_empty' => false,
			] );
			if ( ! is_wp_error( $terms ) ) {
				foreach ( $terms as $t ) {
					$brands[] = [
						'id'       => $t->term_id,
						'taxonomy' => $tax,
						'name'     => $t->name,
						'count'    => $t->count,
					];
				}
			}
		}
	}

	// Format products for React SelectControl with rich metadata
	$products = [];
	if ( post_type_exists( 'product' ) ) {
		$prods = get_posts( [
			'post_type'      => 'product',
			'posts_per_page' => 150,
			'post_status'    => 'publish',
			'orderby'        => 'title',
			'order'          => 'ASC',
		] );
		foreach ( $prods as $p ) {
			$price = '';
			$image_url = '';
			$short_desc = '';
			$permalink = get_permalink( $p->ID );
			if ( function_exists( 'wc_get_product' ) ) {
				$product = wc_get_product( $p->ID );
				if ( $product ) {
					$raw_price  = wc_price( $product->get_price() );
					$price      = html_entity_decode( wp_strip_all_tags( $raw_price ), ENT_QUOTES, 'UTF-8' );
					$raw_desc   = $product->get_short_description() ?: $product->get_description();
					$short_desc = html_entity_decode( wp_strip_all_tags( $raw_desc ), ENT_QUOTES, 'UTF-8' );
					$short_desc = wp_trim_words( $short_desc, 22 );
					$img_id     = $product->get_image_id();
					if ( $img_id ) {
						$img = wp_get_attachment_image_src( $img_id, 'medium' );
						if ( $img ) {
							$image_url = $img[0];
						}
					}
				}
			}
			$products[] = [
				'id'          => $p->ID,
				'title'       => html_entity_decode( $p->post_title, ENT_QUOTES, 'UTF-8' ),
				'price'       => $price,
				'image_url'   => $image_url,
				'permalink'   => $permalink,
				'description' => $short_desc,
			];
		}
	}

	// Ensure hero_cards exists
	if ( ! isset( $options['hero_cards'] ) || ! is_array( $options['hero_cards'] ) ) {
		$options['hero_cards'] = raka_get_hero_cards();
	}

	return rest_ensure_response( [
		'options'    => $options,
		'categories' => $categories,
		'brands'     => $brands,
		'products'   => $products,
	] );
}

/**
 * REST Callback: Save Settings
 *
 * @param WP_REST_Request $request
 * @return WP_REST_Response
 */
function raka_rest_save_settings( WP_REST_Request $request ) {
	$params   = $request->get_json_params();
	$raw_data = $params['options'] ?? [];

	if ( ! is_array( $raw_data ) ) {
		return new WP_Error( 'invalid_data', __( 'Invalid options format.', 'raka-preorder-theme' ), [ 'status' => 400 ] );
	}

	$clean_options = [];

	// General & Hero texts
	$text_fields = [
		'hero_main_title',
		'hero_caption',
		'hero_video_url',
		'hero_overlay_url',
	];
	foreach ( $text_fields as $field ) {
		if ( isset( $raw_data[ $field ] ) ) {
			if ( strpos( $field, '_url' ) !== false ) {
				$clean_options[ $field ] = esc_url_raw( trim( (string) $raw_data[ $field ] ) );
			} elseif ( $field === 'hero_caption' ) {
				$clean_options[ $field ] = sanitize_textarea_field( (string) $raw_data[ $field ] );
			} else {
				$clean_options[ $field ] = sanitize_text_field( (string) $raw_data[ $field ] );
			}
		}
	}

	// Contact & Store Info fields
	$contact_fields = [
		'contact_title',
		'contact_subtitle',
		'contact_line_id',
		'contact_line_url',
		'contact_facebook_url',
		'contact_phone',
		'contact_email',
		'contact_hours',
		'contact_address',
		'contact_note',
	];
	foreach ( $contact_fields as $c_field ) {
		if ( isset( $raw_data[ $c_field ] ) ) {
			if ( strpos( $c_field, '_url' ) !== false ) {
				$clean_options[ $c_field ] = esc_url_raw( trim( (string) $raw_data[ $c_field ] ) );
			} elseif ( in_array( $c_field, [ 'contact_address', 'contact_note' ], true ) ) {
				$clean_options[ $c_field ] = sanitize_textarea_field( (string) $raw_data[ $c_field ] );
			} elseif ( $c_field === 'contact_email' ) {
				$clean_options[ $c_field ] = sanitize_email( (string) $raw_data[ $c_field ] );
			} else {
				$clean_options[ $c_field ] = sanitize_text_field( (string) $raw_data[ $c_field ] );
			}
		}
	}

	// Section display counts and settings
	if ( isset( $raw_data['flash_sale_enabled'] ) ) {
		$clean_options['flash_sale_enabled'] = ! empty( $raw_data['flash_sale_enabled'] );
	}
	if ( isset( $raw_data['flash_sale_limit'] ) ) {
		$clean_options['flash_sale_limit'] = max( 1, min( 24, (int) $raw_data['flash_sale_limit'] ) );
	}
	if ( isset( $raw_data['our_products_limit'] ) ) {
		$clean_options['our_products_limit'] = max( 1, min( 48, (int) $raw_data['our_products_limit'] ) );
	}
	if ( isset( $raw_data['flash_sale_title'] ) ) {
		$clean_options['flash_sale_title'] = sanitize_text_field( (string) $raw_data['flash_sale_title'] );
	}
	if ( isset( $raw_data['flash_sale_tag'] ) ) {
		$clean_options['flash_sale_tag'] = sanitize_text_field( (string) $raw_data['flash_sale_tag'] );
	}
	if ( isset( $raw_data['flash_sale_desc'] ) ) {
		$clean_options['flash_sale_desc'] = sanitize_textarea_field( (string) $raw_data['flash_sale_desc'] );
	}
	if ( isset( $raw_data['flash_sale_schedule_mode'] ) ) {
		$clean_options['flash_sale_schedule_mode'] = in_array( $raw_data['flash_sale_schedule_mode'], [ 'fixed', 'daily' ], true ) ? $raw_data['flash_sale_schedule_mode'] : 'fixed';
	}
	if ( isset( $raw_data['flash_sale_end_time'] ) ) {
		$clean_options['flash_sale_end_time'] = sanitize_text_field( (string) $raw_data['flash_sale_end_time'] );
	}
	if ( isset( $raw_data['flash_sale_start_time'] ) ) {
		$clean_options['flash_sale_start_time'] = sanitize_text_field( (string) $raw_data['flash_sale_start_time'] );
	}
	if ( isset( $raw_data['flash_sale_daily_start'] ) ) {
		$clean_options['flash_sale_daily_start'] = sanitize_text_field( (string) $raw_data['flash_sale_daily_start'] );
	}
	if ( isset( $raw_data['flash_sale_daily_end'] ) ) {
		$clean_options['flash_sale_daily_end'] = sanitize_text_field( (string) $raw_data['flash_sale_daily_end'] );
	}
	if ( isset( $raw_data['flash_sale_campaign_start_date'] ) ) {
		$clean_options['flash_sale_campaign_start_date'] = sanitize_text_field( (string) $raw_data['flash_sale_campaign_start_date'] );
	}
	if ( isset( $raw_data['flash_sale_campaign_end_date'] ) ) {
		$clean_options['flash_sale_campaign_end_date'] = sanitize_text_field( (string) $raw_data['flash_sale_campaign_end_date'] );
	}
	if ( isset( $raw_data['our_products_title'] ) ) {
		$clean_options['our_products_title'] = sanitize_text_field( (string) $raw_data['our_products_title'] );
	}
	if ( isset( $raw_data['our_products_desc'] ) ) {
		$clean_options['our_products_desc'] = sanitize_textarea_field( (string) $raw_data['our_products_desc'] );
	}

	// Cloudflare Turnstile Security Settings
	if ( isset( $raw_data['turnstile_enabled'] ) ) {
		$clean_options['turnstile_enabled'] = ! empty( $raw_data['turnstile_enabled'] );
	}
	if ( isset( $raw_data['turnstile_sitekey'] ) ) {
		$clean_options['turnstile_sitekey'] = sanitize_text_field( (string) $raw_data['turnstile_sitekey'] );
	}
	if ( isset( $raw_data['turnstile_secret'] ) ) {
		$clean_options['turnstile_secret'] = sanitize_text_field( (string) $raw_data['turnstile_secret'] );
	}
	if ( isset( $raw_data['turnstile_theme'] ) ) {
		$theme_val = sanitize_key( (string) $raw_data['turnstile_theme'] );
		$clean_options['turnstile_theme'] = in_array( $theme_val, [ 'auto', 'light', 'dark' ], true ) ? $theme_val : 'auto';
	}

	// Member Privacy & Access Control
	if ( isset( $raw_data['preorder_require_member_login'] ) ) {
		$clean_options['preorder_require_member_login'] = ! empty( $raw_data['preorder_require_member_login'] );
	}

	// e-Tax Invoice System Global Toggle
	if ( isset( $raw_data['etax_invoice_enabled'] ) ) {
		$clean_options['etax_invoice_enabled'] = ! empty( $raw_data['etax_invoice_enabled'] );
	}

	// Loyalty & Rewards Engine Settings
	if ( isset( $raw_data['loyalty'] ) && is_array( $raw_data['loyalty'] ) ) {
		$raw_loyalty = $raw_data['loyalty'];
		$clean_product_ids = array_values( array_filter( array_map( 'absint', (array) ( $raw_loyalty['reward_product_ids'] ?? [] ) ) ) );
		
		$clean_product_points = [];
		if ( isset( $raw_loyalty['reward_product_points'] ) && is_array( $raw_loyalty['reward_product_points'] ) ) {
			foreach ( $raw_loyalty['reward_product_points'] as $pid => $pts ) {
				$pid = absint( $pid );
				$pts = max( 1.0, (float) $pts );
				if ( $pid > 0 ) {
					$clean_product_points[ $pid ] = $pts;
				}
			}
		}

		$clean_options['loyalty'] = [
			'enabled'                    => ! empty( $raw_loyalty['enabled'] ),
			'spend_per_point'            => max( 1.0, (float) ( $raw_loyalty['spend_per_point'] ?? 100.0 ) ),
			'tier_threshold_silver'      => max( 1.0, (float) ( $raw_loyalty['tier_threshold_silver'] ?? 500.0 ) ),
			'tier_threshold_gold'        => max( 1.0, (float) ( $raw_loyalty['tier_threshold_gold'] ?? 2000.0 ) ),
			'preorder_points_multiplier' => max( 0.1, (float) ( $raw_loyalty['preorder_points_multiplier'] ?? 1.0 ) ),
			'lot_expiry_days'            => max( 1, (int) ( $raw_loyalty['lot_expiry_days'] ?? 365 ) ),
			'reward_product_ids'         => $clean_product_ids,
			'reward_product_points'      => $clean_product_points,
		];
	} else {
		$clean_options['loyalty'] = [
			'enabled'                    => true,
			'spend_per_point'            => 100.0,
			'tier_threshold_silver'      => 500.0,
			'tier_threshold_gold'        => 2000.0,
			'preorder_points_multiplier' => 1.0,
			'lot_expiry_days'            => 365,
			'reward_product_ids'         => [],
			'reward_product_points'      => [],
		];
	}

	// Dynamic Repeater: hero_cards
	$allowed_positions = [ 'left', 'right', 'center', 'bottom_left', 'bottom_right' ];
	$clean_hero_cards  = [];

	if ( isset( $raw_data['hero_cards'] ) && is_array( $raw_data['hero_cards'] ) ) {
		foreach ( $raw_data['hero_cards'] as $card ) {
			if ( ! is_array( $card ) ) {
				continue;
			}
			$pos = sanitize_key( $card['position'] ?? 'left' );
			$desc_clean = sanitize_textarea_field( (string) ( $card['description'] ?? '' ) );
			$desc_clean = wp_strip_all_tags( $desc_clean );
			$desc_clean = html_entity_decode( $desc_clean, ENT_QUOTES, 'UTF-8' );

			$price_clean = sanitize_text_field( (string) ( $card['price'] ?? '' ) );
			$price_clean = html_entity_decode( $price_clean, ENT_QUOTES, 'UTF-8' );

			$clean_hero_cards[] = [
				'product_id'        => absint( $card['product_id'] ?? 0 ),
				'show_custom_text'  => ! empty( $card['show_custom_text'] ),
				'tag'               => sanitize_text_field( (string) ( $card['tag'] ?? '' ) ),
				'title'             => sanitize_text_field( (string) ( $card['title'] ?? '' ) ),
				'description'       => $desc_clean,
				'price'             => $price_clean,
				'url'               => esc_url_raw( trim( (string) ( $card['url'] ?? '' ) ) ),
				'image_url'         => esc_url_raw( trim( (string) ( $card['image_url'] ?? '' ) ) ),
				'button_text'       => sanitize_text_field( (string) ( $card['button_text'] ?? __( 'Pre-Order Now', 'raka-preorder-theme' ) ) ),
				'position'          => in_array( $pos, $allowed_positions, true ) ? $pos : 'left',
			];
		}
	}
	$clean_options['hero_cards'] = $clean_hero_cards;

	// Backward-compatible card slots (sec_2, sec_3, sec_4_top, sec_4_bot, sec_5)
	$slots = [ 'sec_2', 'sec_3', 'sec_4_top', 'sec_4_bot', 'sec_5' ];
	$allowed_sources = [ 'custom', 'product_cat', 'brand', 'product' ];

	foreach ( $slots as $slot ) {
		$p = 'card_' . $slot . '_';

		$source = sanitize_key( $raw_data[ $p . 'source' ] ?? 'custom' );
		$clean_options[ $p . 'source' ] = in_array( $source, $allowed_sources, true ) ? $source : 'custom';

		$clean_options[ $p . 'cat_id' ]     = absint( $raw_data[ $p . 'cat_id' ] ?? 0 );
		$clean_options[ $p . 'brand_id' ]   = sanitize_text_field( (string) ( $raw_data[ $p . 'brand_id' ] ?? '' ) );
		$clean_options[ $p . 'product_id' ] = absint( $raw_data[ $p . 'product_id' ] ?? 0 );

		$clean_options[ $p . 'tag' ]         = sanitize_text_field( (string) ( $raw_data[ $p . 'tag' ] ?? '' ) );
		$clean_options[ $p . 'title' ]       = sanitize_text_field( (string) ( $raw_data[ $p . 'title' ] ?? '' ) );
		$clean_options[ $p . 'description' ] = sanitize_textarea_field( (string) ( $raw_data[ $p . 'description' ] ?? '' ) );
		$clean_options[ $p . 'url' ]         = esc_url_raw( trim( (string) ( $raw_data[ $p . 'url' ] ?? '' ) ) );
		$clean_options[ $p . 'image_url' ]   = esc_url_raw( trim( (string) ( $raw_data[ $p . 'image_url' ] ?? '' ) ) );
	}

	update_option( 'raka_theme_options', $clean_options );

	return rest_ensure_response( [
		'success' => true,
		'message' => __( 'Hero Banner settings saved successfully!', 'raka-preorder-theme' ),
		'options' => $clean_options,
	] );
}

/**
 * Render Theme Options Admin Mountpoint.
 */
function raka_render_theme_options_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<div id="raka-theme-settings-app"></div>
	</div>
	<?php
}
