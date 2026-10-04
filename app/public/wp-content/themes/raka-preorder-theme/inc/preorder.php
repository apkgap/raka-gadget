<?php
/**
 * WooCommerce Pre-Order Management Engine
 * Handles backend stock status, custom product inventory fields,
 * cart bypass for zero stock, and front-end pre-order presentation.
 *
 * @package RakaPreorderTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * -------------------------------------------------------------------------
 * 1. PRE-ORDER DETECTION & UTILITY FUNCTIONS
 * -------------------------------------------------------------------------
 */

/**
 * Check if a product or variation is in Pre-Order state.
 *
 * Criteria:
 * - Explicit backend toggle: `_raka_is_preorder` === 'yes'
 * - Stock status: 'preorder', 'onbackorder', or 'outofstock'
 * - WooCommerce native backorder: is_on_backorder()
 * - Managing stock and quantity <= 0
 *
 * @param WC_Product|int $product
 * @return bool
 */
function raka_is_product_preorder( $product ): bool {
	static $checking = [];

	if ( is_numeric( $product ) ) {
		$product = wc_get_product( $product );
	}

	if ( ! is_a( $product, 'WC_Product' ) ) {
		return false;
	}

	$product_id = $product->get_id();

	// Recursion guard
	if ( isset( $checking[ $product_id ] ) ) {
		return false;
	}
	$checking[ $product_id ] = true;

	$is_pre = false;

	// 1. Explicit Pre-Order toggle in product meta
	$meta_preorder = get_post_meta( $product_id, '_raka_is_preorder', true );
	if ( 'yes' === $meta_preorder ) {
		$is_pre = true;
	}

	// 2. Stock status is 'preorder', 'onbackorder', or 'outofstock'
	if ( ! $is_pre ) {
		$stock_status = $product->get_stock_status();
		if ( 'preorder' === $stock_status || 'onbackorder' === $stock_status || 'outofstock' === $stock_status ) {
			$is_pre = true;
		}
	}

	// 3. Managing stock and stock quantity <= 0
	if ( ! $is_pre && $product->managing_stock() ) {
		$stock_qty = $product->get_stock_quantity();
		if ( null !== $stock_qty && $stock_qty <= 0 ) {
			$is_pre = true;
		}
	}

	// 4. Native WooCommerce on_backorder check
	if ( ! $is_pre && method_exists( $product, 'is_on_backorder' ) && $product->is_on_backorder() ) {
		$is_pre = true;
	}

	unset( $checking[ $product_id ] );
	return $is_pre;
}

/**
 * Get Estimated Time of Arrival (ETA) for a pre-order product.
 *
 * @param WC_Product|int $product
 * @return string
 */
function raka_get_product_preorder_eta( $product ): string {
	if ( is_numeric( $product ) ) {
		$product = wc_get_product( $product );
	}
	if ( ! is_a( $product, 'WC_Product' ) ) {
		return '';
	}

	$product_id = $product->get_id();
	$eta = (string) get_post_meta( $product_id, '_raka_preorder_eta', true );

	// Fallback to parent product if variation has no ETA
	if ( empty( $eta ) && $product->is_type( 'variation' ) ) {
		$parent_id = $product->get_parent_id();
		$eta = (string) get_post_meta( $parent_id, '_raka_preorder_eta', true );
	}

	if ( empty( $eta ) ) {
		$eta = 'รอสินค้าประมาณ 7-14 วันทำการ (Ships in 7-14 business days)';
	}

	return $eta;
}

/**
 * Get pre-order badge text.
 *
 * @param WC_Product|int $product
 * @return string
 */
function raka_get_product_preorder_badge( $product ): string {
	if ( is_numeric( $product ) ) {
		$product = wc_get_product( $product );
	}
	if ( ! is_a( $product, 'WC_Product' ) ) {
		return 'PRE-ORDER';
	}

	$badge = (string) get_post_meta( $product->get_id(), '_raka_preorder_badge', true );
	return ! empty( $badge ) ? $badge : 'PRE-ORDER';
}

/**
 * -------------------------------------------------------------------------
 * 2. BACKEND ADMIN: STOCK STATUS & PRODUCT DATA METABOX
 * -------------------------------------------------------------------------
 */

/**
 * Add 'Pre-Order' option to WooCommerce stock status dropdown.
 */
function raka_register_preorder_stock_status( array $statuses ): array {
	$new_statuses = [];
	foreach ( $statuses as $key => $label ) {
		$new_statuses[ $key ] = $label;
		if ( 'onbackorder' === $key ) {
			$new_statuses['preorder'] = __( 'Pre-Order (สั่งจองล่วงหน้า)', 'raka-preorder-theme' );
		}
	}
	if ( ! isset( $new_statuses['preorder'] ) ) {
		$new_statuses['preorder'] = __( 'Pre-Order (สั่งจองล่วงหน้า)', 'raka-preorder-theme' );
	}
	return $new_statuses;
}
add_filter( 'woocommerce_product_stock_status_options', 'raka_register_preorder_stock_status' );

/**
 * Add custom Pre-order settings fields in Product Data > Inventory tab.
 */
function raka_add_preorder_product_options(): void {
	global $post;
	if ( ! $post ) {
		return;
	}

	echo '<div id="raka-preorder-options-group" class="options_group show_if_simple show_if_variable border-t border-neutral-200 mt-3 pt-3">';
	echo '<h4 style="margin: 10px 12px 14px; font-size: 13px; font-weight: 700; color: #d97706; text-transform: uppercase; letter-spacing: 0.05em;">' . esc_html__( 'การตั้งค่าสินค้าพรีออเดอร์ (Pre-Order Settings)', 'raka-preorder-theme' ) . '</h4>';

	// Toggle Pre-Order
	woocommerce_wp_checkbox( [
		'id'          => '_raka_is_preorder',
		'label'       => __( 'เปิดใช้งานสถานะ Pre-Order', 'raka-preorder-theme' ),
		'description' => __( 'บังคับเปิดโหมดพรีออเดอร์ทันที (ปุ่มสั่งซื้อจะเปลี่ยนเป็น PRE-ORDER NOW และอนุญาตให้สั่งซื้อได้ตลอดเวลา แม้สต๊อกจะเป็น 0)', 'raka-preorder-theme' ),
		'desc_tip'    => false,
	] );

	// ETA field
	woocommerce_wp_text_input( [
		'id'          => '_raka_preorder_eta',
		'label'       => __( 'กำหนดการจัดส่ง / ETA', 'raka-preorder-theme' ),
		'placeholder' => __( 'เช่น รอสินค้า 7-14 วัน หรือ ปลายเดือนพฤศจิกายน 2569', 'raka-preorder-theme' ),
		'desc_tip'    => true,
		'description' => __( 'ข้อความแสดงระยะเวลารอสินค้าให้ลูกค้าเห็นบนหน้าร้าน', 'raka-preorder-theme' ),
	] );

	// Badge field
	woocommerce_wp_text_input( [
		'id'          => '_raka_preorder_badge',
		'label'       => __( 'ข้อความป้ายกำกับ (Badge)', 'raka-preorder-theme' ),
		'placeholder' => 'PRE-ORDER',
		'desc_tip'    => true,
		'description' => __( 'ข้อความบนป้ายสินค้า เช่น PRE-ORDER หรือ สินค้าสั่งจอง', 'raka-preorder-theme' ),
	] );

	// Notice / Terms field
	woocommerce_wp_textarea_input( [
		'id'          => '_raka_preorder_note',
		'label'       => __( 'หมายเหตุการสั่งจอง', 'raka-preorder-theme' ),
		'placeholder' => __( 'ระบุเงื่อนไขการจอง เช่น ชำระเต็มจำนวน, รวมภาษีแล้ว, ประกันศูนย์ไทย', 'raka-preorder-theme' ),
		'desc_tip'    => true,
		'description' => __( 'รายละเอียดเงื่อนไขที่จะแสดงในหน้าสินค้า', 'raka-preorder-theme' ),
	] );

	echo '</div>';
}
add_action( 'woocommerce_product_options_inventory_product_data', 'raka_add_preorder_product_options', 30 );

/**
 * Save custom Pre-order settings fields on product save.
 */
function raka_save_preorder_product_options( int $post_id ): void {
	// Pre-order toggle
	$is_preorder = isset( $_POST['_raka_is_preorder'] ) ? 'yes' : 'no';
	update_post_meta( $post_id, '_raka_is_preorder', $is_preorder );

	// Pre-order ETA
	if ( isset( $_POST['_raka_preorder_eta'] ) ) {
		update_post_meta( $post_id, '_raka_preorder_eta', sanitize_text_field( wp_unslash( $_POST['_raka_preorder_eta'] ) ) );
	}

	// Pre-order Badge
	if ( isset( $_POST['_raka_preorder_badge'] ) ) {
		update_post_meta( $post_id, '_raka_preorder_badge', sanitize_text_field( wp_unslash( $_POST['_raka_preorder_badge'] ) ) );
	}

	// Pre-order Note
	if ( isset( $_POST['_raka_preorder_note'] ) ) {
		update_post_meta( $post_id, '_raka_preorder_note', sanitize_textarea_field( wp_unslash( $_POST['_raka_preorder_note'] ) ) );
	}
}
add_action( 'woocommerce_process_product_meta', 'raka_save_preorder_product_options', 20 );

/**
 * Display Pre-order status in admin product list columns.
 */
function raka_admin_product_stock_column_badge( $status_html, $product = null ): string {
	$status_str = is_string( $status_html ) ? $status_html : '';
	if ( $product && raka_is_product_preorder( $product ) ) {
		$badge_text = raka_get_product_preorder_badge( $product );
		$eta        = raka_get_product_preorder_eta( $product );
		return sprintf(
			'<mark class="preorder" style="background:#fef3c7;color:#b45309;padding:4px 8px;border-radius:4px;font-weight:700;display:inline-block;font-size:11px;letter-spacing:0.05em;border:1px solid #fde68a;">%s</mark><br><small style="color:#78716c;font-size:11px;">%s</small>',
			esc_html( $badge_text ),
			esc_html( $eta )
		);
	}
	return $status_str;
}
add_filter( 'woocommerce_admin_stock_html', 'raka_admin_product_stock_column_badge', 20, 2 );

/**
 * -------------------------------------------------------------------------
 * 3. PURCHASABILITY & CART ENGINE BYPASS
 * -------------------------------------------------------------------------
 */

/**
 * Ensure pre-order items are always purchasable, even if stock quantity is 0.
 */
function raka_preorder_is_purchasable( $purchasable, $product = null ): bool {
	if ( $product && raka_is_product_preorder( $product ) ) {
		return true;
	}
	return (bool) $purchasable;
}
add_filter( 'woocommerce_is_purchasable', 'raka_preorder_is_purchasable', 20, 2 );

/**
 * Bypass out-of-stock restriction for pre-order products.
 */
function raka_preorder_is_in_stock( $is_in_stock, $product = null ): bool {
	if ( $product && raka_is_product_preorder( $product ) ) {
		return true;
	}
	return (bool) $is_in_stock;
}
add_filter( 'woocommerce_product_is_in_stock', 'raka_preorder_is_in_stock', 20, 2 );

/**
 * Allow backorders for pre-order products automatically.
 */
function raka_preorder_backorders_allowed( $allowed, $product_or_id = null, $product = null ): bool {
	$target = ( $product instanceof WC_Product ) ? $product : ( ( $product_or_id instanceof WC_Product ) ? $product_or_id : ( is_numeric( $product_or_id ) ? wc_get_product( (int) $product_or_id ) : null ) );
	if ( $target && raka_is_product_preorder( $target ) ) {
		return true;
	}
	return (bool) $allowed;
}
add_filter( 'woocommerce_product_backorders_allowed', 'raka_preorder_backorders_allowed', 20, 3 );

/**
 * Silence standard backorder notice to replace with our custom UI.
 * WooCommerce filter signature: apply_filters( 'woocommerce_product_backorders_require_notification', $notify, $product );
 */
function raka_preorder_backorders_require_notification( $notify, $product_or_id = null, $product = null ): bool {
	$target = ( $product instanceof WC_Product ) ? $product : ( ( $product_or_id instanceof WC_Product ) ? $product_or_id : ( is_numeric( $product_or_id ) ? wc_get_product( (int) $product_or_id ) : null ) );
	if ( $target && raka_is_product_preorder( $target ) ) {
		return false;
	}
	return (bool) $notify;
}
add_filter( 'woocommerce_product_backorders_require_notification', 'raka_preorder_backorders_require_notification', 20, 3 );

/**
 * Inject pre-order data into variable product JS array.
 */
function raka_preorder_available_variation( $data, $product = null, $variation = null ): array {
	$res = is_array( $data ) ? $data : [];
	$target_variation = ( $variation instanceof WC_Product_Variation ) ? $variation : ( ( $product instanceof WC_Product_Variation ) ? $product : null );
	if ( $target_variation ) {
		$res['is_preorder']    = raka_is_product_preorder( $target_variation );
		$res['preorder_eta']   = raka_get_product_preorder_eta( $target_variation );
		$res['preorder_badge'] = raka_get_product_preorder_badge( $target_variation );
	}
	return $res;
}
add_filter( 'woocommerce_available_variation', 'raka_preorder_available_variation', 20, 3 );

/**
 * -------------------------------------------------------------------------
 * 4. CART & ORDER BADGES
 * -------------------------------------------------------------------------
 */

/**
 * Add [PRE-ORDER] tag and ETA to cart items.
 */
function raka_preorder_cart_item_name( $name, $cart_item = [], $cart_item_key = '' ): string {
	$name_str = is_string( $name ) ? $name : '';
	$product  = is_array( $cart_item ) ? ( $cart_item['data'] ?? null ) : null;
	if ( $product && raka_is_product_preorder( $product ) ) {
		$badge = raka_get_product_preorder_badge( $product );
		$eta   = raka_get_product_preorder_eta( $product );
		$name_str = '<span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-mono font-bold bg-amber-500/10 text-amber-600 border border-amber-500/20 mr-1.5 align-middle">[' . esc_html( $badge ) . ']</span>' . $name_str;
		if ( ! empty( $eta ) ) {
			$name_str .= '<div class="text-xs text-amber-600/90 font-mono mt-0.5 font-normal"><i class="ri-time-line text-[11px] mr-1"></i>ETA: ' . esc_html( $eta ) . '</div>';
		}
	}
	return $name_str;
}
add_filter( 'woocommerce_cart_item_name', 'raka_preorder_cart_item_name', 20, 3 );

/**
 * Save pre-order metadata into Order Line Items.
 */
function raka_preorder_add_order_item_meta( $item, $cart_item_key = '', $values = [], $order = null ): void {
	if ( ! ( $item instanceof WC_Order_Item_Product ) ) {
		return;
	}
	$product = is_array( $values ) ? ( $values['data'] ?? null ) : null;
	if ( $product && raka_is_product_preorder( $product ) ) {
		$badge = raka_get_product_preorder_badge( $product );
		$eta   = raka_get_product_preorder_eta( $product );
		$item->add_meta_data( __( 'Order Type', 'raka-preorder-theme' ), $badge, true );
		if ( ! empty( $eta ) ) {
			$item->add_meta_data( __( 'Estimated Delivery', 'raka-preorder-theme' ), $eta, true );
		}
	}
}
add_action( 'woocommerce_checkout_create_order_line_item', 'raka_preorder_add_order_item_meta', 20, 4 );

/**
 * -------------------------------------------------------------------------
 * 5. SYNC WOOCOMMERCE ORDERS WITH PRE-ORDER PLUGIN (preorder-raka)
 * -------------------------------------------------------------------------
 */

/**
 * Automatically create or update a Pre-order tracking post when a WooCommerce
 * order with pre-order item(s) reaches processing or on-hold status.
 */
function raka_sync_wc_order_to_preorder_plugin( int $order_id ): void {
	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		return;
	}

	// 1. Prevent duplicate creation
	$existing = get_posts( [
		'post_type'   => 'preorder',
		'meta_key'    => '_preorder_wc_order_id',
		'meta_value'  => $order_id,
		'post_status' => 'any',
		'numberposts' => 1,
	] );

	if ( ! empty( $existing ) ) {
		return;
	}

	// 2. Identify pre-order items in the order
	$preorder_items = [];
	foreach ( $order->get_items() as $item ) {
		$product = $item->get_product();
		if ( $product && raka_is_product_preorder( $product ) ) {
			$preorder_items[] = [
				'product'    => $product,
				'name'       => $item->get_name(),
				'product_id' => $item->get_product_id(),
				'qty'        => $item->get_quantity(),
				'total'      => $item->get_total(),
			];
		}
	}

	if ( empty( $preorder_items ) ) {
		return;
	}

	// 3. Customer & Order Info
	$order_no      = $order->get_order_number();
	$customer_name = trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() );
	$phone         = preg_replace( '/[^0-9]/', '', (string) $order->get_billing_phone() );
	$address       = $order->get_formatted_shipping_address() ?: $order->get_formatted_billing_address();

	$first_item   = $preorder_items[0];
	$product_name = count( $preorder_items ) === 1 
		? $first_item['name'] 
		: $first_item['name'] . ' และสินค้าอื่นอีก ' . ( count( $preorder_items ) - 1 ) . ' รายการ';

	$post_title = 'พรีออเดอร์ #' . $order_no . ' - ' . ( $customer_name ?: 'ลูกค้า' );
	$post_name  = sanitize_title( 'raka-order-' . $order_no );

	// 4. Create Post in Post Type 'preorder'
	$preorder_post_id = wp_insert_post( [
		'post_title'  => $post_title,
		'post_name'   => $post_name,
		'post_type'   => 'preorder',
		'post_status' => 'publish',
	] );

	if ( ! $preorder_post_id || is_wp_error( $preorder_post_id ) ) {
		return;
	}

	// 5. Store Metadata
	$item_price = (int) round( $first_item['total'] / max( 1, $first_item['qty'] ) );
	update_post_meta( $preorder_post_id, '_preorder_wc_order_id', $order_id );
	update_post_meta( $preorder_post_id, '_preorder_no', $order_no );
	update_post_meta( $preorder_post_id, '_preorder_customer_name', $customer_name );
	update_post_meta( $preorder_post_id, '_preorder_phone', $phone );
	update_post_meta( $preorder_post_id, '_preorder_product_id', $first_item['product_id'] );
	update_post_meta( $preorder_post_id, '_preorder_product', $product_name );
	update_post_meta( $preorder_post_id, '_preorder_price', $item_price );
	update_post_meta( $preorder_post_id, '_preorder_qty', $first_item['qty'] );
	update_post_meta( $preorder_post_id, '_preorder_shipping', (int) round( $order->get_shipping_total() ) );
	update_post_meta( $preorder_post_id, '_preorder_address', wp_strip_all_tags( $address ) );
	$eta = raka_get_product_preorder_eta( $first_item['product'] );
	update_post_meta( $preorder_post_id, '_preorder_estimate_days', $eta ?: '7-14 วัน' );
	update_post_meta( $preorder_post_id, '_preorder_current_step_idx', 0 );

	// Set featured image
	$thumb_id = get_post_thumbnail_id( $first_item['product_id'] );
	if ( $thumb_id ) {
		set_post_thumbnail( $preorder_post_id, $thumb_id );
	}

	// Default tracking steps
	$default_steps = [
		[ 'title' => 'รับคำสั่งซื้อเรียบร้อย (Order Received)', 'desc' => 'ระบบได้รับการชำระเงินและบันทึกรายการพรีออเดอร์แล้ว', 'date' => current_time( 'Y-m-d\TH:i' ) ],
		[ 'title' => 'สั่งซื้อสินค้าสำเร็จ (Purchased at Origin Country)', 'desc' => 'ทำรายการสั่งซื้อสินค้าจากต่างประเทศเรียบร้อย', 'date' => '' ],
		[ 'title' => 'ถึงคลังสินค้าต่างประเทศ (Arrived at Overseas Warehouse)', 'desc' => 'สินค้าเดินทางถึงคลังจัดเก็บในต่างประเทศแล้ว เตรียมพร้อมส่งกลับไทย', 'date' => '' ],
		[ 'title' => 'ระหว่างขนส่งระหว่างประเทศ (In Transit to Thailand)', 'desc' => 'สินค้าอยู่ระหว่างการจัดส่งมายังประเทศไทย', 'date' => '' ],
		[ 'title' => 'ผ่านพิธีการศุลกากร (Customs Cleared)', 'desc' => 'สินค้าผ่านการตรวจและเสียภาษีนำเข้าเรียบร้อยแล้ว', 'date' => '' ],
		[ 'title' => 'ถึงศูนย์กระจายสินค้าในไทย (Arrived at Local Sorting Center)', 'desc' => 'สินค้าถึงศูนย์จัดส่งในประเทศไทยและกำลังจัดเตรียมเพื่อนำจ่าย', 'date' => '' ],
		[ 'title' => 'อยู่ระหว่างนำจ่าย (Out for Delivery)', 'desc' => 'บริษัทขนส่งในประเทศกำลังนำจ่ายสินค้าไปยังที่อยู่ของท่าน', 'date' => '' ],
		[ 'title' => 'จัดส่งสำเร็จ (Delivered)', 'desc' => 'ท่านได้รับสินค้าเรียบร้อยแล้ว', 'date' => '' ],
	];
	update_post_meta( $preorder_post_id, '_preorder_steps', $default_steps );
}
add_action( 'woocommerce_order_status_processing', 'raka_sync_wc_order_to_preorder_plugin', 20, 1 );
add_action( 'woocommerce_order_status_on-hold', 'raka_sync_wc_order_to_preorder_plugin', 20, 1 );

/**
 * When WooCommerce order is marked Completed, advance Pre-order tracking to Delivered.
 */
function raka_sync_wc_order_completed_to_preorder( int $order_id ): void {
	$posts = get_posts( [
		'post_type'   => 'preorder',
		'meta_key'    => '_preorder_wc_order_id',
		'meta_value'  => $order_id,
		'post_status' => 'any',
		'numberposts' => 1,
	] );

	if ( empty( $posts ) ) {
		return;
	}

	$preorder_id = $posts[0]->ID;
	update_post_meta( $preorder_id, '_preorder_current_step_idx', 7 );

	$steps = get_post_meta( $preorder_id, '_preorder_steps', true );
	if ( is_array( $steps ) && isset( $steps[7] ) ) {
		$steps[7]['date'] = current_time( 'Y-m-d\TH:i' );
		update_post_meta( $preorder_id, '_preorder_steps', $steps );
	}
}
add_action( 'woocommerce_order_status_completed', 'raka_sync_wc_order_completed_to_preorder', 20, 1 );

/**
 * Display Pre-order tracking link on WooCommerce Order Received / Thank You page.
 */
function raka_display_preorder_tracking_on_thankyou( int $order_id ): void {
	$posts = get_posts( [
		'post_type'   => 'preorder',
		'meta_key'    => '_preorder_wc_order_id',
		'meta_value'  => $order_id,
		'post_status' => 'any',
		'numberposts' => 1,
	] );

	if ( empty( $posts ) ) {
		return;
	}

	$preorder_post = $posts[0];
	$order         = wc_get_order( $order_id );
	$order_no      = $order ? $order->get_order_number() : get_post_meta( $preorder_post->ID, '_preorder_no', true );
	$phone         = $order ? preg_replace( '/[^0-9]/', '', (string) $order->get_billing_phone() ) : '';
	$tracking_url  = add_query_arg( [
		'order_no' => $order_no,
		'phone'    => ! empty( $phone ) ? substr( $phone, -4 ) : '',
	], get_permalink( $preorder_post->ID ) );
	?>
	<div id="raka-preorder-thankyou-notice" class="mt-8 p-6 rounded-2xl bg-neutral-900 text-white border border-neutral-800 shadow-xl">
		<div id="raka-preorder-thankyou-header" class="flex items-center gap-3 mb-3">
			<span id="raka-preorder-thankyou-badge" class="px-2.5 py-1 text-xs font-mono font-bold uppercase tracking-wider bg-white text-black rounded-md">
				PRE-ORDER TRACKING
			</span>
			<h3 id="raka-preorder-thankyou-title" class="text-lg font-bold">
				คำสั่งซื้อนี้มีรายการสินค้าพรีออเดอร์
			</h3>
		</div>
		<p id="raka-preorder-thankyou-desc" class="text-sm text-neutral-300 mb-5 leading-relaxed">
			สินค้าของคุณถูกบันทึกเข้าสู่ระบบจัดการและติดตามพรีออเดอร์แล้ว สามารถติดตามไทม์ไลน์สถานะการผลิตและการจัดส่งได้ตลอดเวลา
		</p>
		<a id="raka-preorder-thankyou-btn" href="<?php echo esc_url( $tracking_url ); ?>" class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-white text-black font-bold text-sm tracking-wider uppercase hover:bg-neutral-200 transition-colors">
			<i id="raka-preorder-thankyou-icon" class="ri-radar-line text-lg"></i>
			<span>เปิดหน้าระบบติดตามพรีออเดอร์</span>
		</a>
	</div>
	<?php
}
add_action( 'woocommerce_thankyou', 'raka_display_preorder_tracking_on_thankyou', 15, 1 );
