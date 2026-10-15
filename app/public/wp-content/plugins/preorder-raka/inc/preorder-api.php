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
			'permission_callback' => '__return_true', // Publicly accessible to display preorder catalog
		]
	] );

	register_rest_route( 'preorder-raka/v1', '/wc-orders', [
		[
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'preorder_raka_get_wc_orders',
			'permission_callback' => function () {
				return current_user_can( 'edit_posts' );
			},
		]
	] );

	register_rest_route( 'preorder-raka/v1', '/users', [
		[
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'preorder_raka_get_users',
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

	// Auto-lookup matching WooCommerce / Pre-order product image if image is still empty
	if ( empty( $featured_image_url ) && ! empty( $product_name ) ) {
		$search_query = trim( preg_replace( '/\s*\(.*?\)\s*/', ' ', $product_name ) );
		$matched_prods = get_posts( [
			'post_type'      => [ 'product', 'preorder_product' ],
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			's'              => $search_query ?: $product_name,
		] );
		if ( ! empty( $matched_prods ) ) {
			$m_thumb = get_the_post_thumbnail_url( $matched_prods[0]->ID, 'large' );
			if ( $m_thumb ) {
				$featured_image_url = $m_thumb;
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

	$items_meta = $get_meta( '_preorder_items', [] );
	$items_list = [];

	if ( is_array( $items_meta ) && ! empty( $items_meta ) ) {
		foreach ( $items_meta as $it ) {
			$it_p_id   = isset( $it['product_id'] ) ? intval( $it['product_id'] ) : 0;
			$it_name   = isset( $it['product_name'] ) ? sanitize_text_field( $it['product_name'] ) : '';
			$it_price  = isset( $it['price'] ) ? intval( $it['price'] ) : 0;
			$it_qty    = isset( $it['qty'] ) ? max( 1, intval( $it['qty'] ) ) : 1;
			$it_img    = isset( $it['image_url'] ) ? esc_url_raw( $it['image_url'] ) : '';
			$it_eta    = isset( $it['estimate_days'] ) ? sanitize_text_field( $it['estimate_days'] ) : '';

			if ( empty( $it_img ) && $it_p_id > 0 ) {
				$it_thumb = get_the_post_thumbnail_url( $it_p_id, 'large' );
				if ( $it_thumb ) {
					$it_img = $it_thumb;
				}
			}

			$items_list[] = [
				'product_id'    => $it_p_id,
				'product_name'  => $it_name,
				'price'         => $it_price,
				'qty'           => $it_qty,
				'subtotal'      => $it_price * $it_qty,
				'image_url'     => $it_img,
				'estimate_days' => $it_eta,
			];
		}
	}

	// Fallback to legacy single product fields if items list is empty
	if ( empty( $items_list ) ) {
		$items_list[] = [
			'product_id'    => intval( $product_id ),
			'product_name'  => $product_name ?: $preorder->post_title,
			'price'         => $price,
			'qty'           => $qty,
			'subtotal'      => $price * $qty,
			'image_url'     => $featured_image_url ? esc_url_raw( $featured_image_url ) : '',
			'estimate_days' => $get_meta( '_preorder_estimate_days' ) ?: '7-14 วัน',
		];
	}

	$calc_items_total = 0;
	foreach ( $items_list as $it ) {
		$calc_items_total += $it['subtotal'];
	}

	$total = $calc_items_total + $shipping;

	return [
		'id'               => $pid,
		'order_no'         => $get_meta( '_preorder_no' ) ?: 'PRE-' . $pid,
		'product_name'     => $product_name ?: ( ! empty( $items_list[0]['product_name'] ) ? $items_list[0]['product_name'] : $preorder->post_title ),
		'image_url'        => $featured_image_url ? esc_url_raw( $featured_image_url ) : ( ! empty( $items_list[0]['image_url'] ) ? $items_list[0]['image_url'] : '' ),
		'price'            => $price ?: ( ! empty( $items_list[0]['price'] ) ? $items_list[0]['price'] : 0 ),
		'qty'              => $qty ?: ( ! empty( $items_list[0]['qty'] ) ? $items_list[0]['qty'] : 1 ),
		'shipping'         => $shipping,
		'items'            => $items_list,
		'items_count'      => count( $items_list ),
		'items_subtotal'   => $calc_items_total,
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
 * Identify if a preorder post is associated with a registered WordPress member.
 *
 * @param int $post_id Preorder post ID.
 * @return int User ID if member, 0 if guest.
 */
function preorder_raka_get_post_member_id( int $post_id ): int {
	// 1. Check direct user ID meta
	$raw_user_id = get_post_meta( $post_id, '_preorder_user_id', true );
	if ( $raw_user_id === '0' || $raw_user_id === 0 ) {
		// Explicitly selected as guest / general customer (-- ลูกค้าทั่วไป / ไม่ระบุ --)
		return 0;
	}

	$user_id = (int) $raw_user_id;
	if ( ! $user_id ) {
		$raw_cust = get_post_meta( $post_id, '_customer_user', true );
		if ( $raw_cust === '0' || $raw_cust === 0 ) {
			return 0;
		}
		$user_id = (int) $raw_cust;
	}

	if ( $user_id > 0 && get_userdata( $user_id ) ) {
		return $user_id;
	}

	// If explicit user ID was already evaluated and is 0/empty, do not guess phone/email for guest orders
	if ( metadata_exists( 'post', $post_id, '_preorder_user_id' ) ) {
		return 0;
	}

	// 2. Check author if non-admin member
	$post = get_post( $post_id );
	if ( $post && ! empty( $post->post_author ) && (int) $post->post_author > 0 ) {
		$author_id = (int) $post->post_author;
		$user = get_userdata( $author_id );
		if ( $user && ! in_array( 'administrator', (array) $user->roles, true ) ) {
			return $author_id;
		}
	}

	// 3. Match phone number with registered users in WP (only for legacy orders without _preorder_user_id meta)
	$phone = get_post_meta( $post_id, '_preorder_phone', true );
	if ( ! empty( $phone ) ) {
		$clean_phone = preg_replace( '/[^0-9]/', '', (string) $phone );
		if ( strlen( $clean_phone ) >= 9 ) {

			$matched_users = get_users( [
				'meta_query' => [
					'relation' => 'OR',
					[
						'key'     => 'billing_phone',
						'value'   => $clean_phone,
						'compare' => 'LIKE',
					],
					[
						'key'     => 'phone',
						'value'   => $clean_phone,
						'compare' => 'LIKE',
					],
					[
						'key'     => 'user_phone',
						'value'   => $clean_phone,
						'compare' => 'LIKE',
					],
				],
				'number' => 1,
				'fields' => 'ID',
			] );

			if ( ! empty( $matched_users ) ) {
				return (int) $matched_users[0];
			}
		}
	}

	// 4. Match email with registered users in WP
	$email = get_post_meta( $post_id, '_preorder_email', true );
	if ( ! empty( $email ) && is_email( $email ) ) {
		$user = get_user_by( 'email', $email );
		if ( $user ) {
			return (int) $user->ID;
		}
	}

	return 0;
}

/**
 * Get Pre-order Status detail.
 */
function preorder_raka_get_status( WP_REST_Request $request ) {
	$post_id    = $request->get_param( 'post_id' );
	$order_no   = sanitize_text_field( (string) $request->get_param( 'order_no' ) );
	$phone      = sanitize_text_field( (string) $request->get_param( 'phone' ) );
	$citizen_id = sanitize_text_field( (string) $request->get_param( 'citizen_id' ) );
	$my_orders  = $request->get_param( 'my_orders' );
	$user_id    = intval( $request->get_param( 'user_id' ) );

	// ── 0. Handle Current Member / User ID Preorder List Query (Only when explicit my_orders=1 or user_id without specific phone/order search) ──
	$is_my_orders_query = ! empty( $my_orders ) || ( ! empty( $user_id ) && empty( $order_no ) && empty( $phone ) && empty( $post_id ) );
	if ( $is_my_orders_query ) {
		$target_user_id = $user_id > 0 ? $user_id : get_current_user_id();
		if ( $target_user_id <= 0 && ! is_user_logged_in() ) {
			return new WP_Error( 'login_required', 'กรุณาเข้าสู่ระบบเพื่อดูรายการพรีออเดอร์ของคุณ', [
				'status'        => 401,
				'require_login' => true,
				'login_url'     => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : wp_login_url( home_url( '/pre-order/?my_orders=1' ) ),
			] );
		}

		$user_obj = get_userdata( $target_user_id );
		$user_email = $user_obj ? $user_obj->user_email : '';
		$user_phones = [];
		$b_phone = get_user_meta( $target_user_id, 'billing_phone', true );
		$u_phone = get_user_meta( $target_user_id, 'phone', true );
		if ( ! empty( $b_phone ) ) {
			$user_phones[] = (string) $b_phone;
			$user_phones[] = preg_replace( '/[^0-9]/', '', (string) $b_phone );
		}
		if ( ! empty( $u_phone ) ) {
			$user_phones[] = (string) $u_phone;
			$user_phones[] = preg_replace( '/[^0-9]/', '', (string) $u_phone );
		}
		$user_phones = array_unique( array_filter( $user_phones ) );

		$meta_or_query = [
			'relation' => 'OR',
			[
				'key'     => '_preorder_user_id',
				'value'   => $target_user_id,
				'compare' => '=',
			],
			[
				'key'     => '_customer_user',
				'value'   => $target_user_id,
				'compare' => '=',
			],
		];
		if ( ! empty( $user_email ) ) {
			$meta_or_query[] = [
				'key'     => '_preorder_email',
				'value'   => $user_email,
				'compare' => '=',
			];
		}
		foreach ( $user_phones as $p_val ) {
			$meta_or_query[] = [
				'key'     => '_preorder_phone',
				'value'   => $p_val,
				'compare' => '=',
			];
			$meta_or_query[] = [
				'key'     => '_preorder_phone',
				'value'   => $p_val,
				'compare' => 'LIKE',
			];
		}

		$user_preorders = get_posts( [
			'post_type'      => 'preorder',
			'post_status'    => 'publish',
			'numberposts'    => 100,
			'meta_query'     => $meta_or_query,
			'orderby'        => 'date',
			'order'          => 'DESC',
		] );

		$author_preorders = get_posts( [
			'post_type'      => 'preorder',
			'post_status'    => 'publish',
			'numberposts'    => 100,
			'author'         => $target_user_id,
			'orderby'        => 'date',
			'order'          => 'DESC',
		] );

		$all_pids = [];
		$merged_posts = [];
		foreach ( array_merge( $user_preorders, $author_preorders ) as $p ) {
			if ( ! in_array( $p->ID, $all_pids, true ) ) {
				$all_pids[] = $p->ID;
				$merged_posts[] = $p;
			}
		}

		if ( empty( $merged_posts ) ) {
			return [
				'success'   => true,
				'mode'      => 'list',
				'user_id'   => $target_user_id,
				'user_name' => $user_obj ? ( $user_obj->display_name ?: $user_obj->user_login ) : '',
				'count'     => 0,
				'items'     => [],
				'data'      => null,
			];
		}

		$items = [];
		foreach ( $merged_posts as $p ) {
			$items[] = preorder_raka_format_single_item( $p );
		}

		return [
			'success'   => true,
			'mode'      => 'list',
			'user_id'   => $target_user_id,
			'user_name' => $user_obj ? ( $user_obj->display_name ?: $user_obj->user_login ) : '',
			'count'     => count( $items ),
			'items'     => $items,
			'data'      => $items[0],
		];
	}

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
		
		$phone_vars = array_unique( array_filter( [
			$phone,
			$phone_digits,
			preg_replace( '/(\d{3})(\d{3})(\d{4})/', '$1-$2-$3', $phone_digits ),
			preg_replace( '/(\d{2})(\d{4})(\d{4})/', '$1-$2-$3', $phone_digits ),
			preg_replace( '/(\d{3})(\d{3})(\d{4})/', '$1 $2 $3', $phone_digits ),
			ltrim( $phone_digits, '0' ),
		] ) );

		$phone_meta_queries = [ 'relation' => 'OR' ];
		foreach ( $phone_vars as $pv ) {
			$phone_meta_queries[] = [
				'key'     => '_preorder_phone',
				'value'   => $pv,
				'compare' => '=',
			];
			$phone_meta_queries[] = [
				'key'     => '_preorder_phone',
				'value'   => $pv,
				'compare' => 'LIKE',
			];
		}

		$args['numberposts'] = 100;
		$args['meta_query']  = $phone_meta_queries;
		$args['orderby']     = 'date';
		$args['order']       = 'DESC';
	} else {
		return new WP_Error( 'missing_params', 'กรุณาระบุหมายเลขคำสั่งซื้อหรือเบอร์โทรศัพท์สำหรับค้นหา', [ 'status' => 400 ] );
	}

	$posts = get_posts( $args );

	// For phone queries: post-filter strictly to ensure exact phone digits match
	if ( $is_phone_query && ! empty( $posts ) && ! empty( $phone_digits ) ) {
		$strictly_matched = [];
		foreach ( $posts as $p ) {
			$p_phone = (string) get_post_meta( $p->ID, '_preorder_phone', true );
			$p_digits = preg_replace( '/[^0-9]/', '', $p_phone );
			if ( $p_digits === $phone_digits || ( strlen( $phone_digits ) >= 8 && ( substr( $p_digits, -8 ) === substr( $phone_digits, -8 ) || strpos( $p_digits, $phone_digits ) !== false ) ) ) {
				$strictly_matched[] = $p;
			}
		}
		$posts = $strictly_matched;
	}

	if ( empty( $posts ) ) {
		return new WP_Error( 'not_found', 'ไม่พบข้อมูลการพรีออเดอร์จากข้อมูลที่ระบุ', [ 'status' => 404 ] );
	}

	// ── Security Verification (Turnstile CAPTCHA & Direct Lookup Fallback) ──
	$turnstile_token   = sanitize_text_field( (string) ( $request->get_param( 'cf_turnstile_response' ) ?: $request->get_param( 'turnstile_token' ) ) );
	$turnstile_enabled = function_exists( 'raka_is_turnstile_enabled' ) ? raka_is_turnstile_enabled() : true;

	$validated = false;

	// 1. Check Cloudflare Turnstile Verification if token provided
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

	// 2. Direct Validation Fallback (Valid matching Order No / Phone)
	if ( ! $validated ) {
		$first_meta     = get_post_meta( $posts[0]->ID );
		$db_phone       = isset( $first_meta['_preorder_phone'][0] ) ? $first_meta['_preorder_phone'][0] : '';
		$db_citizen_id  = isset( $first_meta['_preorder_citizen_id_last4'][0] ) ? $first_meta['_preorder_citizen_id_last4'][0] : '';
		$db_order_no    = isset( $first_meta['_preorder_no'][0] ) ? $first_meta['_preorder_no'][0] : '';
		$phone_clean    = preg_replace( '/[^0-9]/', '', $phone );
		$db_phone_clean = preg_replace( '/[^0-9]/', '', $db_phone );

		if ( ! empty( $order_no ) && ! empty( $db_order_no ) && strtoupper( trim( (string) $db_order_no ) ) === strtoupper( trim( (string) $order_no ) ) ) {
			$validated = true;
		} elseif ( ! empty( $phone_clean ) && ! empty( $db_phone_clean ) && ( substr( $db_phone_clean, -4 ) === substr( $phone_clean, -4 ) || $db_phone_clean === $phone_clean ) ) {
			$validated = true;
		} elseif ( empty( $db_phone ) && empty( $db_citizen_id ) ) {
			$validated = true;
		} elseif ( ! empty( $citizen_id ) && ! empty( $db_citizen_id ) && $db_citizen_id === substr( $citizen_id, -4 ) ) {
			$validated = true;
		}
	}

	if ( ! $validated && $turnstile_enabled ) {
		return new WP_Error( 'turnstile_required', 'กรุณากดติ๊กถูกในช่อง "Verify you are human" ก่อนค้นหา', [ 'status' => 403 ] );
	}

	if ( ! $validated ) {
		return new WP_Error( 'verification_required', 'กรุณายืนยันความปลอดภัยผ่าน CAPTCHA หรือกรอกเบอร์โทรศัพท์เพื่อดูสถานะ', [ 'status' => 403 ] );
	}

	// ── 3. Member Privacy & Access Control Enforcement ──
	$require_member_login = function_exists( 'raka_is_member_login_required' ) 
		? raka_is_member_login_required() 
		: (bool) get_option( 'preorder_require_member_login', false );

	if ( $require_member_login ) {
		$member_user_id = preorder_raka_get_post_member_id( $posts[0]->ID );

		if ( $member_user_id > 0 ) {
			// Preorder belongs to a registered member!
			$target_param = ! empty( $order_no ) ? ( 'order_no=' . rawurlencode( $order_no ) ) : ( 'phone=' . rawurlencode( $phone ) );
			$target_url   = home_url( '/pre-order/?' . $target_param );

			$login_url = function_exists( 'wc_get_page_permalink' ) && wc_get_page_id( 'myaccount' ) > 0
				? add_query_arg( 'redirect_to', urlencode( $target_url ), wc_get_page_permalink( 'myaccount' ) )
				: wp_login_url( $target_url );

			$effective_uid = $user_id > 0 ? $user_id : get_current_user_id();
			$is_logged_in_check = $effective_uid > 0 || is_user_logged_in();

			if ( ! $is_logged_in_check ) {
				return new WP_Error(
					'member_login_required',
					'คำสั่งซื้อนี้เป็นของสมาชิกเว็บไซต์ เพื่อความปลอดภัยกรุณาเข้าสู่ระบบก่อนตรวจสอบสถานะ',
					[
						'status'        => 401,
						'require_login' => true,
						'login_url'     => $login_url,
						'order_no'      => $order_no ?: get_post_meta( $posts[0]->ID, '_preorder_no', true ),
					]
				);
			}

			$is_admin_or_staff = user_can( $effective_uid, 'edit_posts' ) || user_can( $effective_uid, 'manage_options' );

			if ( ! $is_admin_or_staff && $effective_uid !== $member_user_id ) {
				return new WP_Error(
					'member_forbidden',
					'คุณไม่มีสิทธิ์เข้าถึงคำสั่งซื้อนี้ กรุณาเข้าสู่ระบบด้วยบัญชีสมาชิกที่เป็นเจ้าของคำสั่งซื้อ',
					[
						'status'        => 403,
						'require_login' => true,
						'login_url'     => $login_url,
					]
				);
			}
		}
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
	$wc_order_id   = intval( $request->get_param( 'wc_order_id' ) );
	$user_id       = intval( $request->get_param( 'user_id' ) );
	$email         = sanitize_email( (string) $request->get_param( 'email' ) );

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
	
	if ( $product_id ) {
		$p_thumb = get_post_thumbnail_id( $product_id );
		if ( $p_thumb ) {
			set_post_thumbnail( $post_id, $p_thumb );
		}
	}
	
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

	if ( $wc_order_id > 0 ) {
		update_post_meta( $post_id, '_preorder_wc_order_id', $wc_order_id );
	}

	if ( $user_id > 0 ) {
		update_post_meta( $post_id, '_preorder_user_id', $user_id );
		update_post_meta( $post_id, '_customer_user', $user_id );
	} else {
		update_post_meta( $post_id, '_preorder_user_id', 0 );
		update_post_meta( $post_id, '_customer_user', 0 );
	}

	$raw_items = $request->get_param( 'items' );
	$items     = [];

	if ( is_array( $raw_items ) && ! empty( $raw_items ) ) {
		foreach ( $raw_items as $it ) {
			$it_name = isset( $it['product_name'] ) ? sanitize_text_field( $it['product_name'] ) : '';
			if ( empty( $it_name ) && ! empty( $it['name'] ) ) {
				$it_name = sanitize_text_field( $it['name'] );
			}
			$it_p_id  = isset( $it['product_id'] ) ? intval( $it['product_id'] ) : 0;
			$it_price = isset( $it['price'] ) ? intval( $it['price'] ) : 0;
			$it_qty   = isset( $it['qty'] ) ? max( 1, intval( $it['qty'] ) ) : 1;
			$it_img   = isset( $it['image_url'] ) ? esc_url_raw( $it['image_url'] ) : '';
			$it_eta   = isset( $it['estimate_days'] ) ? sanitize_text_field( $it['estimate_days'] ) : '';

			if ( empty( $it_img ) && $it_p_id > 0 ) {
				$it_thumb = get_the_post_thumbnail_url( $it_p_id, 'large' );
				if ( $it_thumb ) {
					$it_img = $it_thumb;
				}
			}

			if ( ! empty( $it_name ) || $it_p_id > 0 ) {
				$items[] = [
					'product_id'    => $it_p_id,
					'product_name'  => $it_name,
					'price'         => $it_price,
					'qty'           => $it_qty,
					'subtotal'      => $it_price * $it_qty,
					'image_url'     => $it_img,
					'estimate_days' => $it_eta ?: $estimate_days,
				];
			}
		}
	}

	// Fallback to single product fields if items is not passed
	if ( empty( $items ) && ( ! empty( $product_name ) || $product_id > 0 ) ) {
		$items[] = [
			'product_id'    => $product_id,
			'product_name'  => $product_name,
			'price'         => $price,
			'qty'           => $qty,
			'subtotal'      => $price * $qty,
			'image_url'     => $product_id ? ( get_the_post_thumbnail_url( $product_id, 'large' ) ?: '' ) : '',
			'estimate_days' => $estimate_days,
		];
	}

	if ( ! empty( $items ) ) {
		update_post_meta( $post_id, '_preorder_items', $items );
		// Ensure primary product fields reflect first item or summary
		if ( empty( $product_name ) && ! empty( $items[0]['product_name'] ) ) {
			$product_name = count( $items ) > 1 
				? $items[0]['product_name'] . ' (และอีก ' . ( count( $items ) - 1 ) . ' รายการ)'
				: $items[0]['product_name'];
			update_post_meta( $post_id, '_preorder_product', $product_name );
		}
		if ( ! $product_id && ! empty( $items[0]['product_id'] ) ) {
			$product_id = $items[0]['product_id'];
			update_post_meta( $post_id, '_preorder_product_id', $product_id );
			$first_thumb = get_post_thumbnail_id( $product_id );
			if ( $first_thumb && ! has_post_thumbnail( $post_id ) ) {
				set_post_thumbnail( $post_id, $first_thumb );
			}
		}
	}

	if ( ! empty( $email ) ) {
		update_post_meta( $post_id, '_preorder_email', $email );
	}

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
 * Get List of Products (WooCommerce Products, Variations & Custom Preorders) for Admin.
 */
function preorder_raka_get_products() {
	$data = [];

	// 1. Fetch WooCommerce Products if WooCommerce is active
	if ( function_exists( 'wc_get_products' ) ) {
		$wc_products = wc_get_products([
			'status'  => 'publish',
			'limit'   => -1,
			'orderby' => 'title',
			'order'   => 'ASC',
		]);


		foreach ( $wc_products as $prod ) {
			$p_id        = $prod->get_id();
			$p_name      = $prod->get_name();
			$p_type      = $prod->get_type();
			$p_price     = (float) $prod->get_price();
			$img_id      = $prod->get_image_id();
			$img_url     = $img_id ? wp_get_attachment_image_url( $img_id, 'medium' ) : '';
			$is_preorder = function_exists( 'raka_is_product_preorder' ) ? raka_is_product_preorder( $prod ) : ( get_post_meta( $p_id, '_raka_is_preorder', true ) === 'yes' );
			$eta         = function_exists( 'raka_get_product_preorder_eta' ) ? raka_get_product_preorder_eta( $prod ) : '7-14 วัน';

			if ( 'variable' === $p_type && method_exists( $prod, 'get_available_variations' ) ) {
				$variations = $prod->get_available_variations();
				foreach ( $variations as $var_data ) {
					$var_id    = $var_data['variation_id'];
					$var_obj   = wc_get_product( $var_id );
					$var_attrs = [];
					if ( ! empty( $var_data['attributes'] ) ) {
						foreach ( $var_data['attributes'] as $attr_k => $attr_v ) {
							$attr_label = wc_attribute_label( str_replace( 'attribute_', '', $attr_k ) );
							$var_attrs[] = $attr_v;
						}
					}
					$var_suffix    = ! empty( $var_attrs ) ? ' (' . implode( ' / ', $var_attrs ) . ')' : ' #' . $var_id;
					$var_thumb_id  = get_post_thumbnail_id( $var_id );
					if ( $var_thumb_id ) {
						$var_img_url = wp_get_attachment_image_url( $var_thumb_id, 'medium' ) ?: wp_get_attachment_url( $var_thumb_id );
					} else {
						$var_img_url = ! empty( $var_data['image']['src'] ) ? $var_data['image']['src'] : $img_url;
					}
					$var_price     = ! empty( $var_data['display_price'] ) ? (float) $var_data['display_price'] : $p_price;
					$var_is_pre    = function_exists( 'raka_is_product_preorder' ) ? raka_is_product_preorder( $var_obj ) : $is_preorder;
					$var_eta       = function_exists( 'raka_get_product_preorder_eta' ) ? raka_get_product_preorder_eta( $var_obj ) : $eta;

					$data[] = [
						'id'          => $var_id,
						'parent_id'   => $p_id,
						'title'       => $p_name . $var_suffix,
						'group'       => $p_name,
						'sku'         => $var_data['sku'] ?? '',
						'price'       => $var_price,
						'image_url'   => $var_img_url,
						'is_preorder' => $var_is_pre,
						'eta'         => $var_eta,
						'type'        => 'variation',
						'permalink'   => $prod->get_permalink(),
					];
				}
			} else {
				$data[] = [
					'id'          => $p_id,
					'parent_id'   => 0,
					'title'       => $p_name,
					'group'       => __( 'สินค้าทั่วไป (Simple)', 'preorder-raka' ),
					'sku'         => $prod->get_sku() ?: '',
					'price'       => $p_price,
					'image_url'   => $img_url,
					'is_preorder' => $is_preorder,
					'eta'         => $eta,
					'type'        => 'simple',
					'permalink'   => $prod->get_permalink(),
				];
			}
		}
	}

	// 2. Also include custom preorder_product CPT
	$posts = get_posts([
		'post_type'   => 'preorder_product',
		'post_status' => 'publish',
		'numberposts' => 100,
		'orderby'     => 'title',
		'order'       => 'ASC',
	]);

	foreach ( $posts as $p ) {
		$thumb_url = get_the_post_thumbnail_url( $p->ID, 'medium' ) ?: '';
		$data[] = [
			'id'          => $p->ID,
			'parent_id'   => 0,
			'title'       => '[Pre-Order เฉพาะกิจ] ' . $p->post_title,
			'group'       => __( 'สินค้าเฉพาะพรีออเดอร์', 'preorder-raka' ),
			'sku'         => '',
			'price'       => (float) get_post_meta( $p->ID, '_preorder_price', true ),
			'image_url'   => $thumb_url,
			'is_preorder' => true,
			'eta'         => get_post_meta( $p->ID, '_preorder_estimate_days', true ) ?: '7-14 วัน',
			'type'        => 'custom_preorder',
			'permalink'   => get_permalink( $p->ID ),
		];
	}

	return [
		'success' => true,
		'data'    => $data,
	];
}

/**
 * Get Recent WooCommerce Orders for Quick Import / Preorder Link.
 */
function preorder_raka_get_wc_orders( WP_REST_Request $request ) {
	if ( ! function_exists( 'wc_get_orders' ) ) {
		return [ 'success' => true, 'data' => [] ];
	}

	$search = sanitize_text_field( (string) $request->get_param( 's' ) );
	$limit  = intval( $request->get_param( 'limit' ) ) ?: 50;

	$args = [
		'limit'   => $limit,
		'orderby' => 'date',
		'order'   => 'DESC',
		'status'  => [ 'processing', 'on-hold', 'completed', 'pending' ],
	];

	if ( ! empty( $search ) ) {
		$args['s'] = $search;
	}

	$orders = wc_get_orders( $args );
	$data   = [];

	foreach ( $orders as $order ) {
		$order_id      = $order->get_id();
		$order_no      = $order->get_order_number();
		$customer_name = trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() );
		$phone         = preg_replace( '/[^0-9]/', '', (string) $order->get_billing_phone() );
		$email         = $order->get_billing_email();
		$address       = $order->get_formatted_shipping_address() ?: $order->get_formatted_billing_address();
		$user_id       = (int) $order->get_user_id();
		$status        = $order->get_status();
		$date_created  = $order->get_date_created() ? $order->get_date_created()->date_i18n( 'd/m/Y H:i' ) : '';
		$total         = (float) $order->get_total();
		$shipping      = (float) $order->get_shipping_total();

		$items = [];
		$has_preorder = false;
		foreach ( $order->get_items() as $item ) {
			$product      = $item->get_product();
			$item_p_id    = $item->get_product_id();
			$item_v_id    = $item->get_variation_id();
			$target_prod  = $item_v_id ? wc_get_product( $item_v_id ) : $product;
			$is_item_pre  = $target_prod && function_exists( 'raka_is_product_preorder' ) ? raka_is_product_preorder( $target_prod ) : false;
			if ( $is_item_pre ) {
				$has_preorder = true;
			}
			$thumb_id     = $target_prod ? $target_prod->get_image_id() : ( $product ? $product->get_image_id() : 0 );
			$thumb_url    = $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'thumbnail' ) : '';
			$eta          = $target_prod && function_exists( 'raka_get_product_preorder_eta' ) ? raka_get_product_preorder_eta( $target_prod ) : '7-14 วัน';

			$items[] = [
				'product_id'   => $item_v_id ?: $item_p_id,
				'parent_id'    => $item_p_id,
				'name'         => $item->get_name(),
				'price'        => (float) ( $item->get_total() / max( 1, $item->get_quantity() ) ),
				'qty'          => (int) $item->get_quantity(),
				'subtotal'     => (float) $item->get_total(),
				'thumb_url'    => $thumb_url,
				'is_preorder'  => $is_item_pre,
				'eta'          => $eta,
			];
		}

		// Check if already linked to a preorder post
		$existing_pre = get_posts([
			'post_type'   => 'preorder',
			'meta_key'    => '_preorder_wc_order_id',
			'meta_value'  => $order_id,
			'post_status' => 'any',
			'numberposts' => 1,
			'fields'      => 'ids',
		]);

		$data[] = [
			'id'              => $order_id,
			'order_no'        => $order_no,
			'customer_name'   => $customer_name ?: __( 'ลูกค้าทั่วไป', 'preorder-raka' ),
			'phone'           => $phone,
			'email'           => $email,
			'address'         => wp_strip_all_tags( $address ),
			'user_id'         => $user_id,
			'status'          => $status,
			'status_label'    => wc_get_order_status_name( $status ),
			'date'            => $date_created,
			'total'           => $total,
			'shipping'        => $shipping,
			'has_preorder'    => $has_preorder,
			'is_already_synced' => ! empty( $existing_pre ),
			'synced_post_id'  => ! empty( $existing_pre ) ? $existing_pre[0] : 0,
			'items'           => $items,
		];
	}

	return [
		'success' => true,
		'data'    => $data,
	];
}

/**
 * Get List of WordPress / WooCommerce Users for Member Linking.
 */
function preorder_raka_get_users( WP_REST_Request $request ) {
	$search = sanitize_text_field( (string) $request->get_param( 's' ) );
	$args   = [
		'number'  => 100,
		'orderby' => 'display_name',
		'order'   => 'ASC',
	];

	if ( ! empty( $search ) ) {
		$args['search']         = '*' . $search . '*';
		$args['search_columns'] = [ 'user_login', 'user_email', 'user_nicename', 'display_name' ];
	}

	$users = get_users( $args );
	$data  = [];

	foreach ( $users as $u ) {
		$phone = get_user_meta( $u->ID, 'billing_phone', true ) ?: get_user_meta( $u->ID, 'phone', true );
		$name  = $u->display_name ?: $u->user_login;
		$label = sprintf( '#%d: %s (%s)%s', $u->ID, $name, $u->user_email, $phone ? ' - ' . $phone : '' );

		$data[] = [
			'id'           => $u->ID,
			'name'         => $name,
			'email'        => $u->user_email,
			'phone'        => $phone ? (string) $phone : '',
			'label'        => $label,
		];
	}

	return [
		'success' => true,
		'data'    => $data,
	];
}




