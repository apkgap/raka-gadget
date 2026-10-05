<?php
/**
 * REST API Endpoints for React Account SPA.
 * Namespace: account/v1
 *
 * @package raka-preorder-theme
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Account REST API routes.
 */
function raka_register_account_rest_routes(): void {
	$namespace = 'account/v1';

	// 1. GET /account/v1/overview
	register_rest_route(
		$namespace,
		'/overview',
		[
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'raka_account_api_get_overview',
			'permission_callback' => 'raka_account_api_check_permission',
		]
	);

	// 2. GET /account/v1/profile
	register_rest_route(
		$namespace,
		'/profile',
		[
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'raka_account_api_get_profile',
			'permission_callback' => 'raka_account_api_check_permission',
		]
	);

	// 3. POST /account/v1/profile
	register_rest_route(
		$namespace,
		'/profile',
		[
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => 'raka_account_api_update_profile',
			'permission_callback' => 'raka_account_api_check_permission',
		]
	);

	// 4. GET /account/v1/orders
	register_rest_route(
		$namespace,
		'/orders',
		[
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'raka_account_api_get_orders',
			'permission_callback' => 'raka_account_api_check_permission',
		]
	);

	// 5. GET /account/v1/addresses
	register_rest_route(
		$namespace,
		'/addresses',
		[
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'raka_account_api_get_addresses',
			'permission_callback' => 'raka_account_api_check_permission',
		]
	);

	// 6. POST /account/v1/addresses
	register_rest_route(
		$namespace,
		'/addresses',
		[
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => 'raka_account_api_update_addresses',
			'permission_callback' => 'raka_account_api_check_permission',
		]
	);

	// 7. POST /account/v1/password
	register_rest_route(
		$namespace,
		'/password',
		[
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => 'raka_account_api_change_password',
			'permission_callback' => 'raka_account_api_check_permission',
		]
	);

	// 8. GET /account/v1/passkeys
	register_rest_route(
		$namespace,
		'/passkeys',
		[
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'raka_account_api_get_passkeys',
			'permission_callback' => 'raka_account_api_check_permission',
		]
	);

	// 9. DELETE /account/v1/passkeys/(?P<id>\d+)
	register_rest_route(
		$namespace,
		'/passkeys/(?P<id>\d+)',
		[
			'methods'             => WP_REST_Server::DELETABLE,
			'callback'            => 'raka_account_api_delete_passkey',
			'permission_callback' => 'raka_account_api_check_permission',
		]
	);
}
add_action( 'rest_api_init', 'raka_register_account_rest_routes' );

/**
 * Permission check: User must be logged in.
 */
function raka_account_api_check_permission(): bool {
	return is_user_logged_in();
}

/**
 * Get User Overview Data.
 */
function raka_account_api_get_overview( WP_REST_Request $request ): WP_REST_Response {
	$user_id = get_current_user_id();
	$user    = get_userdata( $user_id );

	if ( ! $user ) {
		return new WP_REST_Response( [ 'message' => 'User not found' ], 404 );
	}

	// Order stats
	$orders_count     = 0;
	$active_preorders = 0;
	$total_spent      = 0.0;
	$recent_orders    = [];

	if ( function_exists( 'wc_get_orders' ) ) {
		$customer_orders = wc_get_orders( [
			'customer_id' => $user_id,
			'limit'       => 20,
			'orderby'     => 'date',
			'order'       => 'DESC',
		] );

		$orders_count = count( $customer_orders );

		foreach ( $customer_orders as $order ) {
			$total_spent += (float) $order->get_total();
			$status       = $order->get_status();

			$has_preorder = false;
			foreach ( $order->get_items() as $item ) {
				$product = $item->get_product();
				if ( $product && ( 'yes' === get_post_meta( $product->get_id(), '_is_preorder', true ) || has_term( 'pre-order', 'product_cat', $product->get_id() ) ) ) {
					$has_preorder = true;
					break;
				}
			}

			if ( $has_preorder && ! in_array( $status, [ 'completed', 'cancelled', 'refunded' ], true ) ) {
				$active_preorders++;
			}

			if ( count( $recent_orders ) < 3 ) {
				$recent_orders[] = raka_account_format_order_summary( $order );
			}
		}
	}

	// Passkeys count
	global $wpdb;
	$passkeys_count = 0;
	$table_passkeys = $wpdb->prefix . 'wc_user_passkeys';
	if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_passkeys ) ) === $table_passkeys ) {
		$passkeys_count = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table_passkeys} WHERE user_id = %d", $user_id ) );
	}

	$profile = [
		'id'           => $user_id,
		'username'     => $user->user_login,
		'email'        => $user->user_email,
		'first_name'   => get_user_meta( $user_id, 'first_name', true ) ?: $user->display_name,
		'last_name'    => get_user_meta( $user_id, 'last_name', true ) ?: '',
		'display_name' => $user->display_name,
		'phone'        => get_user_meta( $user_id, 'billing_phone', true ) ?: get_user_meta( $user_id, 'phone', true ),
		'line_id'      => get_user_meta( $user_id, 'billing_line_id', true ) ?: get_user_meta( $user_id, 'line_id', true ),
		'avatar_url'   => get_avatar_url( $user_id, [ 'size' => 120 ] ),
		'member_since' => date_i18n( 'd M Y', strtotime( $user->user_registered ) ),
	];

	$points_balance = class_exists( 'Raka_Loyalty_Service' ) ? Raka_Loyalty_Service::get_available_points( $user_id ) : 0.0;
	$tier           = class_exists( 'Raka_Loyalty_Earn_Engine' ) ? Raka_Loyalty_Earn_Engine::get_user_tier( $user_id ) : 'bronze';

	$data = [
		'profile' => $profile,
		'stats'   => [
			'total_orders'     => $orders_count,
			'active_preorders' => $active_preorders,
			'total_spent'      => function_exists( 'wc_price' ) ? html_entity_decode( wp_strip_all_tags( wc_price( $total_spent ) ) ) : number_format( $total_spent, 2 ) . ' ฿',
			'passkeys_count'   => $passkeys_count,
			'points_balance'   => $points_balance,
			'tier'             => $tier,
		],
		'recent_orders' => $recent_orders,
	];

	return new WP_REST_Response( $data, 200 );
}

/**
 * Get User Profile Data.
 */
function raka_account_api_get_profile( WP_REST_Request $request ): WP_REST_Response {
	$user_id = get_current_user_id();
	$user    = get_userdata( $user_id );

	if ( ! $user ) {
		return new WP_REST_Response( [ 'message' => 'User not found' ], 404 );
	}

	$data = [
		'id'           => $user_id,
		'username'     => $user->user_login,
		'email'        => $user->user_email,
		'first_name'   => get_user_meta( $user_id, 'first_name', true ),
		'last_name'    => get_user_meta( $user_id, 'last_name', true ),
		'display_name' => $user->display_name,
		'phone'        => get_user_meta( $user_id, 'billing_phone', true ) ?: get_user_meta( $user_id, 'phone', true ),
		'line_id'      => get_user_meta( $user_id, 'billing_line_id', true ) ?: get_user_meta( $user_id, 'line_id', true ),
		'avatar_url'   => get_avatar_url( $user_id, [ 'size' => 120 ] ),
		'member_since' => date_i18n( 'd M Y', strtotime( $user->user_registered ) ),
	];

	return new WP_REST_Response( $data, 200 );
}

/**
 * Update User Profile Data.
 */
function raka_account_api_update_profile( WP_REST_Request $request ): WP_REST_Response {
	$user_id = get_current_user_id();
	$params  = $request->get_json_params();

	$first_name   = sanitize_text_field( $params['first_name'] ?? '' );
	$last_name    = sanitize_text_field( $params['last_name'] ?? '' );
	$display_name = sanitize_text_field( $params['display_name'] ?? '' );
	$phone        = sanitize_text_field( $params['phone'] ?? '' );
	$line_id      = sanitize_text_field( $params['line_id'] ?? '' );

	// Update user record
	wp_update_user( [
		'ID'           => $user_id,
		'display_name' => $display_name ?: ( $first_name . ' ' . $last_name ),
		'first_name'   => $first_name,
		'last_name'    => $last_name,
	] );

	// Update meta
	update_user_meta( $user_id, 'first_name', $first_name );
	update_user_meta( $user_id, 'last_name', $last_name );
	update_user_meta( $user_id, 'billing_phone', $phone );
	update_user_meta( $user_id, 'phone', $phone );
	update_user_meta( $user_id, 'billing_line_id', $line_id );
	update_user_meta( $user_id, 'line_id', $line_id );

	return new WP_REST_Response( [
		'success' => true,
		'message' => 'บันทึกข้อมูลส่วนตัวเรียบร้อยแล้ว',
	], 200 );
}

/**
 * Get Orders List for Current User.
 */
function raka_account_api_get_orders( WP_REST_Request $request ): WP_REST_Response {
	$user_id = get_current_user_id();
	$status  = sanitize_key( $request->get_param( 'status' ) ?? 'all' );

	if ( ! function_exists( 'wc_get_orders' ) ) {
		return new WP_REST_Response( [ 'orders' => [] ], 200 );
	}

	$args = [
		'customer_id' => $user_id,
		'limit'       => -1,
		'orderby'     => 'date',
		'order'       => 'DESC',
	];

	if ( 'all' !== $status && ! empty( $status ) ) {
		$args['status'] = [ 'wc-' . $status, $status ];
	}

	$customer_orders = wc_get_orders( $args );
	$formatted       = [];

	foreach ( $customer_orders as $order ) {
		$formatted[] = raka_account_format_order_full( $order );
	}

	return new WP_REST_Response( [
		'orders' => $formatted,
		'total'  => count( $formatted ),
	], 200 );
}

/**
 * Format Order Summary Helper.
 */
function raka_account_format_order_summary( $order ): array {
	$status_key   = $order->get_status();
	$status_label = wc_get_order_status_name( $status_key );

	$items = [];
	foreach ( $order->get_items() as $item ) {
		$product = $item->get_product();
		$img_url = $product ? wp_get_attachment_image_url( $product->get_image_id(), 'thumbnail' ) : '';
		if ( ! $img_url ) {
			$img_url = wc_placeholder_img_src( 'thumbnail' );
		}

		$items[] = [
			'name'      => $item->get_name(),
			'quantity'  => $item->get_quantity(),
			'image_url' => $img_url,
		];
	}

	return [
		'id'           => $order->get_id(),
		'number'       => $order->get_order_number(),
		'date'         => date_i18n( 'd M Y, H:i', strtotime( (string) $order->get_date_created() ) ),
		'status'       => $status_key,
		'status_label' => $status_label,
		'total'        => html_entity_decode( wp_strip_all_tags( wc_price( $order->get_total() ) ) ),
		'item_count'   => $order->get_item_count(),
		'items'        => array_slice( $items, 0, 3 ),
	];
}

/**
 * Format Full Order Helper.
 */
function raka_account_format_order_full( $order ): array {
	$status_key   = $order->get_status();
	$status_label = wc_get_order_status_name( $status_key );

	// Status color map
	$color_map = [
		'completed'  => 'green',
		'processing' => 'blue',
		'on-hold'    => 'amber',
		'pending'    => 'amber',
		'cancelled'  => 'neutral',
		'failed'     => 'red',
		'refunded'   => 'neutral',
	];
	$status_color = $color_map[ $status_key ] ?? 'neutral';

	$items        = [];
	$has_preorder = false;

	foreach ( $order->get_items() as $item_id => $item ) {
		$product = $item->get_product();
		$img_url = $product ? wp_get_attachment_image_url( $product->get_image_id(), 'medium' ) : '';
		if ( ! $img_url ) {
			$img_url = wc_placeholder_img_src( 'medium' );
		}

		$is_item_preorder = $product && ( 'yes' === get_post_meta( $product->get_id(), '_is_preorder', true ) || has_term( 'pre-order', 'product_cat', $product->get_id() ) );
		if ( $is_item_preorder ) {
			$has_preorder = true;
		}

		$items[] = [
			'id'          => $item_id,
			'product_id'  => $item->get_product_id(),
			'name'        => $item->get_name(),
			'quantity'    => $item->get_quantity(),
			'subtotal'    => html_entity_decode( wp_strip_all_tags( wc_price( $item->get_subtotal() ) ) ),
			'total'       => html_entity_decode( wp_strip_all_tags( wc_price( $item->get_total() ) ) ),
			'image_url'   => $img_url,
			'is_preorder' => $is_item_preorder,
		];
	}

	$tracking_code = $order->get_meta( '_tracking_number' ) ?: $order->get_meta( 'tracking_code' ) ?: $order->get_meta( '_raka_tracking_no' ) ?: '';

	return [
		'id'              => $order->get_id(),
		'number'          => $order->get_order_number(),
		'date'            => date_i18n( 'd M Y, H:i น.', strtotime( (string) $order->get_date_created() ) ),
		'status'          => $status_key,
		'status_label'    => $status_label,
		'status_color'    => $status_color,
		'is_preorder'     => $has_preorder,
		'currency'        => $order->get_currency(),
		'total'           => html_entity_decode( wp_strip_all_tags( wc_price( $order->get_total() ) ) ),
		'subtotal'        => html_entity_decode( wp_strip_all_tags( wc_price( $order->get_subtotal() ) ) ),
		'shipping_total'  => html_entity_decode( wp_strip_all_tags( wc_price( $order->get_shipping_total() ) ) ),
		'shipping_method' => $order->get_shipping_method() ?: 'Standard Delivery',
		'payment_method'  => $order->get_payment_method_title(),
		'tracking_code'   => $tracking_code,
		'item_count'      => $order->get_item_count(),
		'items'           => $items,
		'billing_address' => [
			'name'     => $order->get_formatted_billing_full_name(),
			'address'  => $order->get_billing_address_1() . ' ' . $order->get_billing_address_2(),
			'city'     => $order->get_billing_city(),
			'state'    => $order->get_billing_state(),
			'postcode' => $order->get_billing_postcode(),
			'phone'    => $order->get_billing_phone(),
			'email'    => $order->get_billing_email(),
		],
		'shipping_address' => [
			'name'     => $order->get_formatted_shipping_full_name(),
			'address'  => $order->get_shipping_address_1() . ' ' . $order->get_shipping_address_2(),
			'city'     => $order->get_shipping_city(),
			'state'    => $order->get_shipping_state(),
			'postcode' => $order->get_shipping_postcode(),
			'phone'    => $order->get_shipping_phone() ?: $order->get_billing_phone(),
		],
	];
}

/**
 * Get User Addresses (Shipping & Billing).
 */
function raka_account_api_get_addresses( WP_REST_Request $request ): WP_REST_Response {
	$user_id = get_current_user_id();

	$data = [
		'billing' => [
			'first_name'   => get_user_meta( $user_id, 'billing_first_name', true ) ?: get_user_meta( $user_id, 'first_name', true ),
			'last_name'    => get_user_meta( $user_id, 'billing_last_name', true ) ?: get_user_meta( $user_id, 'last_name', true ),
			'company'      => get_user_meta( $user_id, 'billing_company', true ),
			'tax_id'       => get_user_meta( $user_id, 'billing_tax_id', true ) ?: get_user_meta( $user_id, 'tax_id', true ),
			'branch'       => get_user_meta( $user_id, 'billing_branch', true ) ?: 'สำนักงานใหญ่',
			'address_1'    => get_user_meta( $user_id, 'billing_address_1', true ),
			'address_2'    => get_user_meta( $user_id, 'billing_address_2', true ),
			'subdistrict'  => get_user_meta( $user_id, 'billing_subdistrict', true ),
			'district'     => get_user_meta( $user_id, 'billing_district', true ) ?: get_user_meta( $user_id, 'billing_city', true ),
			'province'     => get_user_meta( $user_id, 'billing_province', true ) ?: get_user_meta( $user_id, 'billing_state', true ),
			'postcode'     => get_user_meta( $user_id, 'billing_postcode', true ),
			'phone'        => get_user_meta( $user_id, 'billing_phone', true ),
			'email'        => get_user_meta( $user_id, 'billing_email', true ) ?: wp_get_current_user()->user_email,
			'etax_enabled' => '1' === (string) get_user_meta( $user_id, 'billing_etax_enabled', true ),
		],
		'shipping' => [
			'first_name'   => get_user_meta( $user_id, 'shipping_first_name', true ) ?: get_user_meta( $user_id, 'first_name', true ),
			'last_name'    => get_user_meta( $user_id, 'shipping_last_name', true ) ?: get_user_meta( $user_id, 'last_name', true ),
			'company'      => get_user_meta( $user_id, 'shipping_company', true ),
			'address_1'    => get_user_meta( $user_id, 'shipping_address_1', true ),
			'address_2'    => get_user_meta( $user_id, 'shipping_address_2', true ),
			'subdistrict'  => get_user_meta( $user_id, 'shipping_subdistrict', true ),
			'district'     => get_user_meta( $user_id, 'shipping_district', true ) ?: get_user_meta( $user_id, 'shipping_city', true ),
			'province'     => get_user_meta( $user_id, 'shipping_province', true ) ?: get_user_meta( $user_id, 'shipping_state', true ),
			'postcode'     => get_user_meta( $user_id, 'shipping_postcode', true ),
			'phone'        => get_user_meta( $user_id, 'shipping_phone', true ) ?: get_user_meta( $user_id, 'billing_phone', true ),
		],
		'etax_system_enabled' => function_exists( 'raka_is_etax_invoice_enabled' ) ? raka_is_etax_invoice_enabled() : true,
	];

	return new WP_REST_Response( $data, 200 );
}

/**
 * Update User Addresses.
 */
function raka_account_api_update_addresses( WP_REST_Request $request ): WP_REST_Response {
	$user_id = get_current_user_id();
	$params  = $request->get_json_params();
	$type    = sanitize_key( $params['type'] ?? 'shipping' );
	$address = $params['address'] ?? [];

	if ( isset( $params['etax_enabled'] ) ) {
		update_user_meta( $user_id, 'billing_etax_enabled', ! empty( $params['etax_enabled'] ) ? '1' : '0' );
	}

	if ( 'toggle_etax' === $type ) {
		return new WP_REST_Response( [
			'success'      => true,
			'etax_enabled' => ! empty( $params['etax_enabled'] ),
			'message'      => ! empty( $params['etax_enabled'] ) ? 'เปิดใช้งานการขอใบกำกับภาษีเรียบร้อยแล้ว' : 'ปิดการขอใบกำกับภาษีเรียบร้อยแล้ว',
		], 200 );
	}

	if ( ! in_array( $type, [ 'shipping', 'billing' ], true ) ) {
		return new WP_REST_Response( [ 'message' => 'Invalid address type' ], 400 );
	}

	$fields = [ 'first_name', 'last_name', 'company', 'address_1', 'address_2', 'subdistrict', 'district', 'province', 'postcode', 'phone', 'email', 'tax_id', 'branch' ];

	foreach ( $fields as $field ) {
		if ( isset( $address[ $field ] ) ) {
			$val = sanitize_text_field( (string) $address[ $field ] );
			update_user_meta( $user_id, $type . '_' . $field, $val );

			// Sync with core WooCommerce state / city
			if ( 'district' === $field ) {
				update_user_meta( $user_id, $type . '_city', $val );
			}
			if ( 'province' === $field ) {
				update_user_meta( $user_id, $type . '_state', $val );
			}
		}
	}

	return new WP_REST_Response( [
		'success' => true,
		'message' => 'บันทึกที่อยู่เรียบร้อยแล้ว',
	], 200 );
}

/**
 * Change Password Handler.
 */
function raka_account_api_change_password( WP_REST_Request $request ): WP_REST_Response {
	$user_id = get_current_user_id();
	$user    = get_userdata( $user_id );
	$params  = $request->get_json_params();

	$current_password = $params['current_password'] ?? '';
	$new_password     = $params['new_password'] ?? '';
	$confirm_password = $params['confirm_password'] ?? '';

	if ( empty( $current_password ) || empty( $new_password ) ) {
		return new WP_REST_Response( [ 'message' => 'กรุณากรอกรหัสผ่านให้ครบถ้วน' ], 400 );
	}

	if ( $new_password !== $confirm_password ) {
		return new WP_REST_Response( [ 'message' => 'รหัสผ่านใหม่และการยืนยันไม่ตรงกัน' ], 400 );
	}

	if ( strlen( $new_password ) < 6 ) {
		return new WP_REST_Response( [ 'message' => 'รหัสผ่านใหม่ต้องมีความยาวอย่างน้อย 6 ตัวอักษร' ], 400 );
	}

	// Verify current password
	if ( ! wp_check_password( $current_password, $user->user_pass, $user_id ) ) {
		return new WP_REST_Response( [ 'message' => 'รหัสผ่านเดิมไม่ถูกต้อง' ], 400 );
	}

	// Update password
	wp_set_password( $new_password, $user_id );

	// Re-authenticate session cookie so user stays logged in
	wp_set_current_user( $user_id );
	wp_set_auth_cookie( $user_id );

	return new WP_REST_Response( [
		'success' => true,
		'message' => 'เปลี่ยนรหัสผ่านสำเร็จเรียบร้อยแล้ว',
	], 200 );
}

/**
 * Get User Passkeys List.
 */
function raka_account_api_get_passkeys( WP_REST_Request $request ): WP_REST_Response {
	global $wpdb;
	$user_id = get_current_user_id();
	$table   = $wpdb->prefix . 'wc_user_passkeys';

	if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table ) ) !== $table ) {
		return new WP_REST_Response( [ 'passkeys' => [] ], 200 );
	}

	$results = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT id, device_name, created_at, last_used_at FROM {$table} WHERE user_id = %d ORDER BY created_at DESC",
			$user_id
		),
		ARRAY_A
	);

	$passkeys = [];
	foreach ( $results as $row ) {
		$passkeys[] = [
			'id'           => (int) $row['id'],
			'device_name'  => $row['device_name'] ?: 'อุปกรณ์ไม่ระบุชื่อ',
			'created_at'   => date_i18n( 'd M Y', strtotime( $row['created_at'] ) ),
			'last_used_at' => ! empty( $row['last_used_at'] ) ? date_i18n( 'd M Y, H:i น.', strtotime( $row['last_used_at'] ) ) : 'ยังไม่เคยใช้งาน',
		];
	}

	return new WP_REST_Response( [ 'passkeys' => $passkeys ], 200 );
}

/**
 * Delete User Passkey Device.
 */
function raka_account_api_delete_passkey( WP_REST_Request $request ): WP_REST_Response {
	global $wpdb;
	$user_id = get_current_user_id();
	$id      = (int) $request->get_param( 'id' );
	$table   = $wpdb->prefix . 'wc_user_passkeys';

	if ( ! $id ) {
		return new WP_REST_Response( [ 'message' => 'Invalid passkey ID' ], 400 );
	}

	$deleted = $wpdb->delete(
		$table,
		[
			'id'      => $id,
			'user_id' => $user_id,
		],
		[ '%d', '%d' ]
	);

	if ( false === $deleted || 0 === $deleted ) {
		return new WP_REST_Response( [ 'message' => 'ไม่พบอุปกรณ์หรือเกิดข้อผิดพลาดในการลบ' ], 400 );
	}

	return new WP_REST_Response( [
		'success' => true,
		'message' => 'ลบอุปกรณ์ Passkey สำเร็จเรียบร้อยแล้ว',
	], 200 );
}
