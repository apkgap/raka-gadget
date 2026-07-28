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
}
add_action( 'rest_api_init', 'preorder_raka_register_rest_routes' );

/**
 * Get Pre-order Status detail.
 */
function preorder_raka_get_status( WP_REST_Request $request ) {
	$post_id    = $request->get_param( 'post_id' );
	$order_no   = sanitize_text_field( (string) $request->get_param( 'order_no' ) );
	$phone      = sanitize_text_field( (string) $request->get_param( 'phone' ) );
	$citizen_id = sanitize_text_field( (string) $request->get_param( 'citizen_id' ) );

	$args = [
		'post_type'   => 'preorder',
		'post_status' => 'publish',
		'numberposts' => 1,
	];

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
	} else {
		return new WP_Error( 'missing_params', 'กรุณาระบุข้อมูลสำหรับค้นหา', [ 'status' => 400 ] );
	}

	$posts = get_posts( $args );

	if ( empty( $posts ) ) {
		return new WP_Error( 'not_found', 'ไม่พบข้อมูลการพรีออเดอร์', [ 'status' => 404 ] );
	}

	$preorder = $posts[0];
	$pid      = $preorder->ID;

	// Security Verification
	$db_phone      = get_post_meta( $pid, '_preorder_phone', true );
	$db_citizen_id = get_post_meta( $pid, '_preorder_citizen_id_last4', true );

	// Clean inputs for comparison
	$phone_clean   = preg_replace( '/[^0-9]/', '', $phone );
	$db_phone_clean = preg_replace( '/[^0-9]/', '', $db_phone );

	$validated = false;

	if ( empty( $db_phone ) && empty( $db_citizen_id ) ) {
		$validated = true;
	} else {
		if ( ! empty( $phone_clean ) && ! empty( $db_phone_clean ) && ( substr( $db_phone_clean, -4 ) === substr( $phone_clean, -4 ) || $db_phone_clean === $phone_clean ) ) {
			$validated = true;
		}
		if ( ! empty( $citizen_id ) && ! empty( $db_citizen_id ) && $db_citizen_id === substr( $citizen_id, -4 ) ) {
			$validated = true;
		}
	}

	if ( ! $validated ) {
		return new WP_Error( 'verification_required', 'กรุณายืนยันเบอร์โทรศัพท์หรือเลขบัตรประชาชน 4 ตัวท้ายให้ถูกต้อง เพื่อดูสถานะ', [ 'status' => 403 ] );
	}
	$steps       = get_post_meta( $pid, '_preorder_steps', true );
	$current_idx = get_post_meta( $pid, '_preorder_current_step_idx', true );

	if ( ! is_array( $steps ) ) {
		$steps = [];
	}
	$product_id         = get_post_meta( $pid, '_preorder_product_id', true );
	$product_name       = get_post_meta( $pid, '_preorder_product', true );
	$price              = get_post_meta( $pid, '_preorder_price', true );
	$featured_image_url = get_the_post_thumbnail_url( $pid, 'large' );

	if ( ! empty( $product_id ) ) {
		$linked_prod = get_post( $product_id );
		if ( $linked_prod ) {
			if ( empty( $product_name ) ) {
				$product_name = $linked_prod->post_title;
			}
			if ( empty( $price ) ) {
				$price = get_post_meta( $product_id, '_preorder_price', true );
			}
			if ( empty( $featured_image_url ) ) {
				$featured_image_url = get_the_post_thumbnail_url( $product_id, 'large' );
			}
		}
	}

	$price    = intval( $price );
	$qty      = intval( get_post_meta( $pid, '_preorder_qty', true ) ) ?: 1;
	$shipping = intval( get_post_meta( $pid, '_preorder_shipping', true ) );
	$total    = ( $price * $qty ) + $shipping;

	return [
		'success' => true,
		'data'    => [
			'id'               => $pid,
			'order_no'         => get_post_meta( $pid, '_preorder_no', true ) ?: 'PRE-' . $pid,
			'product_name'     => $product_name ?: $preorder->post_title,
			'image_url'        => $featured_image_url ? esc_url_raw( $featured_image_url ) : '',
			'price'            => $price,
			'qty'              => $qty,
			'shipping'         => $shipping,
			'total'            => $total,
			'address'          => get_post_meta( $pid, '_preorder_address', true ),
			'courier'          => get_post_meta( $pid, '_preorder_courier', true ) ?: 'kerry',
			'tracking_no'      => get_post_meta( $pid, '_preorder_tracking', true ),
			'tracking_link'    => get_post_meta( $pid, '_preorder_tracking_link', true ),
			'facebook'         => get_post_meta( $pid, '_preorder_facebook', true ),
			'line'             => get_post_meta( $pid, '_preorder_line', true ),
			'steps'            => array_map( function( $s ) {
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
			}, $steps ),
			'current_step_idx' => intval( $current_idx ),
			'phone'            => ! empty( $db_phone ) ? substr( $db_phone, 0, 3 ) . '-xxx-' . substr( $db_phone, -4 ) : '',
		]
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

