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

	// Sub-menu for Hero Banner Settings
	add_submenu_page(
		'raka-theme-settings',
		__( 'Hero Banner & Product Cards', 'raka-preorder-theme' ),
		__( 'Hero Banner', 'raka-preorder-theme' ),
		'manage_options',
		'raka-theme-settings',
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
	$is_settings_page = ( isset( $_GET['page'] ) && 'raka-theme-settings' === $_GET['page'] )
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
