<?php
/**
 * REST API Endpoints for Pre-orders.
 *
 * @package preorder-raka
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Pre-order REST Routes.
 */
function preorder_raka_register_rest_routes() {
	register_rest_route( 'preorder-raka/v1', '/status', [
		[
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'preorder_raka_get_status',
			'permission_callback' => '__return_true', // Publicly accessible to allow checking status
		]
	] );

	register_rest_route( 'preorder-raka/v1', '/create', [
		[
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => 'preorder_raka_create_preorder',
			'permission_callback' => function () {
				return current_user_can( 'edit_posts' );
			},
		]
	] );

	register_rest_route( 'preorder-raka/v1', '/products', [
		[
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'preorder_raka_get_products',
			'permission_callback' => function () {
				return current_user_can( 'edit_posts' );
			},
		]
	] );
}
add_action( 'rest_api_init', 'preorder_raka_register_rest_routes' );

/**
 * Format a single preorder item response array.
 *
 * @param int|WP_Post $post Preorder post or ID.
 * @return array
 */
function preorder_raka_format_single_item( $post ) {
	$preorder = is_object( $post ) ? $post : get_post( $post );
	if ( ! $preorder ) {
		return [];
	}
	$pid = $preorder->ID;

	$all_meta = get_post_meta( $pid );
	$get_meta = function( $key, $default = '' ) use ( $all_meta ) {
		return isset( $all_meta[ $key ][0] ) ? maybe_unserialize( $all_meta[ $key ][0] ) : $default;
	};

	$steps       = $get_meta( '_preorder_steps', [] );
	$current_idx = $get_meta( '_preorder_current_step_idx', 0 );

	if ( ! is_array( $steps ) ) {
		$steps = [];
	}
	$product_id         = $get_meta( '_preorder_product_id' );
	$product_name       = $get_meta( '_preorder_product' );
	$price              = $get_meta( '_preorder_price' );
	$featured_image_url = get_the_post_thumbnail_url( $pid, 'large' );

	if ( ! empty( $product_id ) ) {
		$linked_prod = get_post( $product_id );
		if ( $linked_prod ) {
			if ( empty( $product_name ) ) {
				$product_name = $linked_prod->post_title;
			}
			if ( empty( $price ) ) {
				$price = get_post_meta( $product_id, '_price', true ) ?: get_post_meta( $product_id, '_preorder_price', true );
			}
			if ( empty( $featured_image_url ) ) {
				$featured_image_url = get_the_post_thumbnail_url( $product_id, 'large' );
			}
		}
	}

	$price    = intval( $price );
	$qty      = intval( $get_meta( '_preorder_qty', 1 ) ) ?: 1;
	$shipping = intval( $get_meta( '_preorder_shipping', 0 ) );
	$total    = ( $price * $qty ) + $shipping;
	$db_phone = (string) $get_meta( '_preorder_phone' );

	$formatted_steps = array_map( function( $s ) {
		if ( ! empty( $s['date'] ) && strpos( $s['date'], 'T' ) !== false ) {
			$time = strtotime( $s['date'] );
			if ( $time ) {
				$months = [ '', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.' ];
				$m = intval( date( 'n', $time ) );
				$y = intval( date( 'Y', $time ) ) + 543;
				$s['date'] = date( 'j', $time ) . ' ' . $months[$m] . ' ' . $y . ' ' . date( 'H:i', $time );
			}
		}
		return $s;
	}, $steps );

	$cur_idx_int = intval( $current_idx );
	$cur_status_title = ! empty( $formatted_steps[ $cur_idx_int ]['title'] ) ? $formatted_steps[ $cur_idx_int ]['title'] : ( ! empty( $formatted_steps[0]['title'] ) ? $formatted_steps[0]['title'] : 'รับคำสั่งซื้อเรียบร้อย' );

	return [
		'id'               => $pid,
		'order_no'         => $get_meta( '_preorder_no' ) ?: 'PRE-' . $pid,
		'product_name'     => $product_name ?: $preorder->post_title,
		'image_url'        => $featured_image_url ? esc_url_raw( $featured_image_url ) : '',
		'price'            => $price,
		'qty'              => $qty,
		'shipping'         => $shipping,
		'total'            => $total,
		'estimate_days'    => $get_meta( '_preorder_estimate_days' ) ?: '7-14 วัน',
		'address'          => $get_meta( '_preorder_address' ),
		'courier'          => $get_meta( '_preorder_courier' ) ?: 'kerry',
		'tracking_no'      => $get_meta( '_preorder_tracking' ),
		'tracking_link'    => $get_meta( '_preorder_tracking_link' ),
		'facebook'         => $get_meta( '_preorder_facebook' ),
		'line'             => $get_meta( '_preorder_line' ),
		'steps'            => $formatted_steps,
		'current_step_idx' => $cur_idx_int,
		'current_status'   => $cur_status_title,
		'phone'            => ! empty( $db_phone ) ? substr( $db_phone, 0, 3 ) . '-xxx-' . substr( $db_phone, -4 ) : '',
		'created_at'       => get_the_date( 'j M Y', $pid ),
	];
}

/**
 * Get Pre-order Status detail.
 */
function preorder_raka_get_status( WP_REST_Request $request ) {
	$post_id    = $request->get_param( 'post_id' );
	$order_no   = sanitize_text_field( (string) $request->get_param( 'order_no' ) );
	$phone      = sanitize_text_field( (string) $request->get_param( 'phone' ) );
	$citizen_id = sanitize_text_field( (string) $request->get_param( 'citizen_id' ) );

	$args = [
		'post_type'              => 'preorder',
		'post_status'            => 'publish',
		'numberposts'            => 1,
		'no_found_rows'          => true,
		'update_post_term_cache' => false,
	];

	$is_phone_query = false;

	if ( ! empty( $post_id ) ) {
		$args['include'] = [ intval( $post_id ) ];
	} elseif ( ! empty( $order_no ) ) {
		$args['meta_query'] = [
			[
				'key'     => '_preorder_no',
				'value'   => $order_no,
				'compare' => '=',
			]
		];
	} elseif ( ! empty( $phone ) ) {
		$is_phone_query = true;
		$phone_digits   = preg_replace( '/[^0-9]/', '', $phone );
		$args['numberposts'] = 50;
		$args['meta_query']  = [
			'relation' => 'OR',
			[
				'key'     => '_preorder_phone',
				'value'   => $phone,
				'compare' => '=',
			],
			[
				'key'     => '_preorder_phone',
				'value'   => $phone_digits,
				'compare' => 'LIKE',
			],
		];
		$args['orderby'] = 'date';
		$args['order']   = 'DESC';
	} else {
		return new WP_Error( 'missing_params', 'กรุณาระบุหมายเลขคำสั่งซื้อหรือเบอร์โทรศัพท์สำหรับค้นหา', [ 'status' => 400 ] );
	}

	$posts = get_posts( $args );

	if ( empty( $posts ) ) {
		return new WP_Error( 'not_found', 'ไม่พบข้อมูลการพรีออเดอร์จากข้อมูลที่ระบุ', [ 'status' => 404 ] );
	}

	// Security Verification (Turnstile CAPTCHA & Phone/Citizen Fallback)
	$turnstile_token   = sanitize_text_field( (string) ( $request->get_param( 'cf_turnstile_response' ) ?: $request->get_param( 'turnstile_token' ) ) );
	$turnstile_enabled = function_exists( 'raka_is_turnstile_enabled' ) ? raka_is_turnstile_enabled() : true;

	if ( $turnstile_enabled && empty( $turnstile_token ) ) {
		return new WP_Error( 'turnstile_required', 'กรุณากดติ๊กถูกในช่อง "Verify you are human" ก่อนค้นหา', [ 'status' => 403 ] );
	}

	$validated = false;

	// 1. Check Cloudflare Turnstile Verification
	if ( ! empty( $turnstile_token ) ) {
		$turnstile_secret = function_exists( 'raka_get_turnstile_secret' ) ? raka_get_turnstile_secret() : '0x4AAAAAAFNWAKM9TKecHcitT5juwPWd4gM';
		$remote_ip        = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		
		$verify_res = wp_remote_post( 'https://challenges.cloudflare.com/turnstile/v0/siteverify', [
			'body'    => [
				'secret'   => $turnstile_secret,
				'response' => $turnstile_token,
				'remoteip' => $remote_ip,
			],
			'timeout' => 5,
		] );

		if ( ! is_wp_error( $verify_res ) ) {
			$verify_body = json_decode( wp_remote_retrieve_body( $verify_res ), true );
			if ( ! empty( $verify_body['success'] ) ) {
				$validated = true;
			}
		}
	}

	// 2. Direct Validation Fallback
	if ( ! $validated ) {
		$first_meta     = get_post_meta( $posts[0]->ID );
		$db_phone       = isset( $first_meta['_preorder_phone'][0] ) ? $first_meta['_preorder_phone'][0] : '';
		$db_citizen_id  = isset( $first_meta['_preorder_citizen_id_last4'][0] ) ? $first_meta['_preorder_citizen_id_last4'][0] : '';
		$db_order_no    = isset( $first_meta['_preorder_no'][0] ) ? $first_meta['_preorder_no'][0] : '';
		$phone_clean    = preg_replace( '/[^0-9]/', '', $phone );
		$db_phone_clean = preg_replace( '/[^0-9]/', '', $db_phone );

		if ( empty( $db_phone ) && empty( $db_citizen_id ) ) {
			$validated = true;
		} else {
			if ( ! empty( $phone_clean ) && ! empty( $db_phone_clean ) && ( substr( $db_phone_clean, -4 ) === substr( $phone_clean, -4 ) || $db_phone_clean === $phone_clean ) ) {
				$validated = true;
			}
			if ( ! empty( $citizen_id ) && ! empty( $db_citizen_id ) && $db_citizen_id === substr( $citizen_id, -4 ) ) {
				$validated = true;
			}
			if ( ! empty( $order_no ) && ! empty( $db_order_no ) && strtoupper( trim( (string) $db_order_no ) ) === strtoupper( trim( (string) $order_no ) ) ) {
				$validated = true;
			}
		}
	}

	if ( ! $validated ) {
		return new WP_Error( 'verification_required', 'กรุณายืนยันความปลอดภัยผ่าน CAPTCHA หรือกรอกเบอร์โทรศัพท์เพื่อดูสถานะ', [ 'status' => 403 ] );
	}

	// If phone search, return full list
	if ( $is_phone_query ) {
		$items = [];
		foreach ( $posts as $p ) {
			$items[] = preorder_raka_format_single_item( $p );
		}
		return [
			'success' => true,
			'mode'    => 'list',
			'phone'   => $phone,
			'count'   => count( $items ),
			'items'   => $items,
			'data'    => $items[0],
		];
	}

	// Single order search
	$single_item = preorder_raka_format_single_item( $posts[0] );
	return [
		'success' => true,
		'mode'    => 'single',
		'data'    => $single_item,
	];
}

/**
 * Create Pre-order Post via REST API.
 */
function preorder_raka_create_preorder( WP_REST_Request $request ) {
	$customer_name = sanitize_text_field( (string) $request->get_param( 'customer_name' ) );
	$order_no      = sanitize_text_field( (string) $request->get_param( 'order_no' ) );
	$phone         = sanitize_text_field( (string) $request->get_param( 'phone' ) );
	$citizen_id    = sanitize_text_field( (string) $request->get_param( 'citizen_id' ) );
	$product_id    = intval( $request->get_param( 'product_id' ) );
	$product_name  = sanitize_text_field( (string) $request->get_param( 'product_name' ) );
	$price         = intval( $request->get_param( 'price' ) );
	$qty           = intval( $request->get_param( 'qty' ) ) ?: 1;
	$shipping      = intval( $request->get_param( 'shipping' ) );
	$estimate_days = sanitize_text_field( (string) $request->get_param( 'estimate_days' ) ) ?: '7-14 วัน';
	$address       = sanitize_textarea_field( (string) $request->get_param( 'address' ) );
	$courier       = sanitize_text_field( (string) $request->get_param( 'courier' ) ) ?: 'kerry';
	$tracking_no   = sanitize_text_field( (string) $request->get_param( 'tracking_no' ) );
	$tracking_link = esc_url_raw( (string) $request->get_param( 'tracking_link' ) );
	$facebook      = esc_url_raw( (string) $request->get_param( 'facebook' ) );
	$line          = esc_url_raw( (string) $request->get_param( 'line' ) );

	if ( empty( $order_no ) ) {
		$order_no = 'RAKA-PRE-' . wp_rand( 100000, 999999 );
	}

	$post_title = 'พรีออเดอร์ ' . $order_no;
	if ( ! empty( $customer_name ) ) {
		$post_title .= ' - ' . $customer_name;
	} elseif ( ! empty( $product_name ) ) {
		$post_title .= ' (' . $product_name . ')';
	}

	$post_name = sanitize_title( strtolower( $order_no ) );

	$post_id = wp_insert_post([
		'post_title'  => $post_title,
		'post_name'   => $post_name,
		'post_type'   => 'preorder',
		'post_status' => 'publish',
	]);

	if ( is_wp_error( $post_id ) || ! $post_id ) {
		return new WP_Error( 'create_failed', 'ไม่สามารถบันทึกข้อมูลพรีออเดอร์ได้', [ 'status' => 500 ] );
	}

	// Update Meta
	update_post_meta( $post_id, '_preorder_no', $order_no );
	update_post_meta( $post_id, '_preorder_phone', $phone );
	update_post_meta( $post_id, '_preorder_citizen_id_last4', substr( preg_replace( '/[^0-9]/', '', $citizen_id ), -4 ) );
	update_post_meta( $post_id, '_preorder_product_id', $product_id );
	update_post_meta( $post_id, '_preorder_product', $product_name );
	update_post_meta( $post_id, '_preorder_price', $price );
	update_post_meta( $post_id, '_preorder_qty', $qty );
	update_post_meta( $post_id, '_preorder_shipping', $shipping );
	update_post_meta( $post_id, '_preorder_estimate_days', $estimate_days );
	update_post_meta( $post_id, '_preorder_address', $address );
	update_post_meta( $post_id, '_preorder_courier', $courier );
	update_post_meta( $post_id, '_preorder_tracking', $tracking_no );
	update_post_meta( $post_id, '_preorder_tracking_link', $tracking_link );
	update_post_meta( $post_id, '_preorder_facebook', $facebook );
	update_post_meta( $post_id, '_preorder_line', $line );
	update_post_meta( $post_id, '_preorder_current_step_idx', 0 );

	// Default steps setup
	$default_steps = [
		[ 'title' => 'รับคำสั่งซื้อเรียบร้อย (Order Received)', 'desc' => 'ระบบได้รับการพรีออเดอร์ของท่านแล้ว', 'date' => date( 'Y-m-d\TH:i' ) ],
		[ 'title' => 'สั่งซื้อสินค้าสำเร็จ (Purchased at Origin Country)', 'desc' => 'ทำรายการสั่งซื้อสินค้าจากต่างประเทศเรียบร้อยแล้ว', 'date' => '' ],
		[ 'title' => 'ถึงคลังสินค้าต่างประเทศ (Arrived at Overseas Warehouse)', 'desc' => 'สินค้าเดินทางถึงคลังจัดเก็บในต่างประเทศแล้ว เตรียมพร้อมส่งกลับไทย', 'date' => '' ],
		[ 'title' => 'ระหว่างขนส่งระหว่างประเทศ (In Transit to Thailand)', 'desc' => 'สินค้าอยู่ระหว่างการจัดส่งมายังประเทศไทย', 'date' => '' ],
		[ 'title' => 'ผ่านพิธีการศุลกากร (Customs Cleared)', 'desc' => 'สินค้าผ่านการตรวจและเสียภาษีนำเข้าเรียบร้อยแล้ว', 'date' => '' ],
		[ 'title' => 'ถึงศูนย์กระจายสินค้าในไทย (Arrived at Local Sorting Center)', 'desc' => 'สินค้าถึงศูนย์จัดส่งในประเทศไทยและกำลังจัดเตรียมเพื่อนำจ่าย', 'date' => '' ],
		[ 'title' => 'อยู่ระหว่างนำจ่าย (Out for Delivery)', 'desc' => 'บริษัทขนส่งในประเทศกำลังนำจ่ายสินค้าไปยังที่อยู่ของท่าน', 'date' => '' ],
		[ 'title' => 'จัดส่งสำเร็จ (Delivered)', 'desc' => 'ท่านได้รับสินค้าเรียบร้อยแล้ว', 'date' => '' ],
	];
	update_post_meta( $post_id, '_preorder_steps', $default_steps );

	return [
		'success' => true,
		'data'    => [
			'id'       => $post_id,
			'order_no' => $order_no,
			'edit_url' => get_edit_post_link( $post_id, 'raw' ),
			'view_url' => get_permalink( $post_id ),
		]
	];
}

/**
 * Get List of Pre-order Products for Admin dropdown.
 */
function preorder_raka_get_products() {
	$data = [];

	// 1. Fetch WooCommerce Products if WooCommerce is active
	if ( function_exists( 'wc_get_products' ) ) {
		$wc_products = wc_get_products([
			'status'  => 'publish',
			'limit'   => -1,
			'orderby' => 'name',
			'order'   => 'ASC',
		]);

		foreach ( $wc_products as $prod ) {
			$is_preorder = get_post_meta( $prod->get_id(), '_raka_is_preorder', true ) === 'yes' || 'preorder' === $prod->get_stock_status();
			$title = $prod->get_name();
			if ( $is_preorder ) {
				$title = '[PRE-ORDER] ' . $title;
			}
			$data[] = [
				'id'          => $prod->get_id(),
				'title'       => $title,
				'price'       => (int) $prod->get_price(),
				'is_preorder' => $is_preorder,
			];
		}
	}

	// 2. Also include legacy preorder_product CPT
	$posts = get_posts([
		'post_type'   => 'preorder_product',
		'post_status' => 'publish',
		'numberposts' => -1,
		'orderby'     => 'title',
		'order'       => 'ASC',
	]);

	foreach ( $posts as $p ) {
		$data[] = [
			'id'    => $p->ID,
			'title' => $p->post_title,
			'price' => (int) get_post_meta( $p->ID, '_preorder_price', true ),
		];
	}

	return [
		'success' => true,
		'data'    => $data,
	];
}



