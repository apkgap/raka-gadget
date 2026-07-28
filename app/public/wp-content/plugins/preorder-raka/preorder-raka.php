<?php
/**
 * Plugin Name: Pre-order Raka Status Page
 * Description: Core functionality and CPT registration for Pre-order Raka tracking system with React.
 * Version: 1.0.0
 * Author: raka gadget
 * Text Domain: preorder-raka
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Require REST API logic
require_once plugin_dir_path( __FILE__ ) . 'inc/preorder-api.php';

/**
 * Register Pre-order Custom Post Type.
 */
function preorder_raka_register_post_type() {
	$labels = [
		'name'               => esc_html__( 'รายการพรีออเดอร์', 'preorder-raka' ),
		'singular_name'      => esc_html__( 'รายการพรีออเดอร์', 'preorder-raka' ),
		'menu_name'          => esc_html__( 'พรีออเดอร์ Raka', 'preorder-raka' ),
		'name_admin_bar'     => esc_html__( 'พรีออเดอร์', 'preorder-raka' ),
		'add_new'            => esc_html__( 'เพิ่มรายการใหม่', 'preorder-raka' ),
		'add_new_item'       => esc_html__( 'เพิ่มรายการพรีออเดอร์ใหม่', 'preorder-raka' ),
		'new_item'           => esc_html__( 'รายการใหม่', 'preorder-raka' ),
		'edit_item'          => esc_html__( 'แก้ไขข้อมูลพรีออเดอร์', 'preorder-raka' ),
		'view_item'          => esc_html__( 'ดูรายละเอียด', 'preorder-raka' ),
		'all_items'          => esc_html__( 'รายการพรีออเดอร์ทั้งหมด', 'preorder-raka' ),
		'search_items'       => esc_html__( 'ค้นหารายการพรีออเดอร์', 'preorder-raka' ),
		'not_found'          => esc_html__( 'ไม่พบรายการพรีออเดอร์', 'preorder-raka' ),
		'not_found_in_trash' => esc_html__( 'ไม่พบรายการพรีออเดอร์ในถังขยะ', 'preorder-raka' ),
	];

	$args = [
		'labels'             => $labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => [ 'slug' => 'preorder' ],
		'capability_type'    => 'post',
		'has_archive'        => false, // Disables archive index page
		'hierarchical'       => false,
		'menu_position'      => 26,
		'menu_icon'          => 'dashicons-store',
		'supports'           => [ 'title', 'thumbnail', 'custom-fields' ],
		'show_in_rest'       => true,
	];

	register_post_type( 'preorder', $args );

	// Register Pre-order Product CPT
	$prod_labels = [
		'name'               => esc_html__( 'สินค้าพรีออเดอร์', 'preorder-raka' ),
		'singular_name'      => esc_html__( 'สินค้าพรีออเดอร์', 'preorder-raka' ),
		'menu_name'          => esc_html__( 'สินค้าพรีออเดอร์', 'preorder-raka' ),
		'add_new'            => esc_html__( 'เพิ่มสินค้าใหม่', 'preorder-raka' ),
		'add_new_item'       => esc_html__( 'เพิ่มสินค้าพรีออเดอร์ใหม่', 'preorder-raka' ),
		'edit_item'          => esc_html__( 'แก้ไขสินค้า', 'preorder-raka' ),
		'all_items'          => esc_html__( 'สินค้าทั้งหมด', 'preorder-raka' ),
		'view_item'          => esc_html__( 'ดูสินค้า', 'preorder-raka' ),
	];

	$prod_args = [
		'labels'             => $prod_labels,
		'public'             => true,
		'publicly_queryable' => false,
		'show_ui'            => true,
		'show_in_menu'       => 'edit.php?post_type=preorder', // Nest under Pre-order menu
		'query_var'          => true,
		'rewrite'            => [ 'slug' => 'preorder-product' ],
		'capability_type'    => 'post',
		'has_archive'        => false,
		'hierarchical'       => false,
		'supports'           => [ 'title', 'thumbnail', 'custom-fields' ],
		'show_in_rest'       => true,
	];

	register_post_type( 'preorder_product', $prod_args );
}
add_action( 'init', 'preorder_raka_register_post_type' );

/**
 * Register Admin Submenu Page for React Add Preorder form.
 */
function preorder_raka_add_admin_menu() {
	add_submenu_page(
		'edit.php?post_type=preorder',
		esc_html__( 'สร้างพรีออเดอร์ใหม่', 'preorder-raka' ),
		esc_html__( 'สร้างพรีออเดอร์ใหม่', 'preorder-raka' ),
		'edit_posts',
		'preorder-raka-add-new',
		'preorder_raka_render_admin_page'
	);
}
add_action( 'admin_menu', 'preorder_raka_add_admin_menu' );

/**
 * Render Admin Page HTML container for React mounting.
 */
function preorder_raka_render_admin_page() {
	?>
	<div class="wrap" id="preorder-raka-admin-wrap">
		<div id="preorder-raka-admin-app-root"></div>
	</div>
	<?php
}

/**
 * Enqueue React App assets for Admin Page.
 */
function preorder_raka_enqueue_admin_assets( $hook ) {
	if ( 'preorder_page_preorder-raka-add-new' !== $hook ) {
		return;
	}

	$asset_file   = plugin_dir_path( __FILE__ ) . 'build/index.asset.php';
	$script_file  = plugin_dir_path( __FILE__ ) . 'build/index.js';
	$dependencies = [ 'wp-element', 'wp-components', 'wp-i18n' ];
	$version      = '1.0.0';

	if ( file_exists( $asset_file ) ) {
		$assets_info  = require $asset_file;
		$dependencies = isset( $assets_info['dependencies'] ) ? $assets_info['dependencies'] : $dependencies;
		$version      = isset( $assets_info['version'] ) ? $assets_info['version'] : $version;
	}

	if ( file_exists( $script_file ) ) {
		$version .= '-' . filemtime( $script_file );
	}

	wp_enqueue_style(
		'remixicon',
		'https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css',
		[],
		'4.5.0'
	);

	wp_enqueue_script(
		'preorder-raka-admin-app',
		plugin_dir_url( __FILE__ ) . 'build/index.js',
		$dependencies,
		$version,
		true
	);

	wp_localize_script(
		'preorder-raka-admin-app',
		'preorderRakaAdminSettings',
		[
			'root'     => esc_url_raw( rest_url( 'preorder-raka/v1/' ) ),
			'nonce'    => wp_create_nonce( 'wp_rest' ),
			'adminUrl' => esc_url( admin_url( 'edit.php?post_type=preorder' ) ),
		]
	);
}
add_action( 'admin_enqueue_scripts', 'preorder_raka_enqueue_admin_assets' );

/**
 * Redirect archive requests to home page.
 */
function preorder_raka_redirect_archive() {
	if ( is_post_type_archive( 'preorder' ) ) {
		wp_safe_redirect( home_url(), 301 );
		exit;
	}
}
add_action( 'template_redirect', 'preorder_raka_redirect_archive' );
/**
 * Flush rewrite rules on activation.
 */
function preorder_raka_activate() {
	preorder_raka_register_post_type();
	preorder_raka_create_dummy_data();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'preorder_raka_activate' );
add_action( 'admin_init', 'preorder_raka_create_dummy_data' );
/**
 * Seeding dummy pre-order and product data for testing.
 */
function preorder_raka_create_dummy_data() {
	// 1. Create a dummy product if not exists
	$dummy_prods = get_posts([
		'post_type'   => 'preorder_product',
		'title'       => 'iPhone 18 Pro Max Space Black (Pre-order 1TB)',
		'post_status' => 'any',
	]);

	if ( empty( $dummy_prods ) ) {
		$prod_id = wp_insert_post([
			'post_title'   => 'iPhone 18 Pro Max Space Black (Pre-order 1TB)',
			'post_type'    => 'preorder_product',
			'post_status'  => 'publish',
		]);
		if ( $prod_id && ! is_wp_error( $prod_id ) ) {
			update_post_meta( $prod_id, '_preorder_price', 55900 );
		}
	} else {
		$prod_id = $dummy_prods[0]->ID;
	}

	// 2. Create a dummy preorder if not exists
	$dummy_preorders = get_posts([
		'post_type'   => 'preorder',
		'meta_key'    => '_preorder_no',
		'meta_value'  => 'RAKA-PRE-7777',
		'post_status' => 'any',
	]);

	if ( empty( $dummy_preorders ) ) {
		$preorder_id = wp_insert_post([
			'post_title'   => 'พรีออเดอร์ RAKA-PRE-7777 ของ คุณสมชาย ใจดี',
			'post_type'    => 'preorder',
			'post_status'  => 'publish',
		]);

		if ( $preorder_id && ! is_wp_error( $preorder_id ) ) {
			update_post_meta( $preorder_id, '_preorder_no', 'RAKA-PRE-7777' );
			update_post_meta( $preorder_id, '_preorder_phone', '0897777777' );
			update_post_meta( $preorder_id, '_preorder_citizen_id_last4', '7777' );
			update_post_meta( $preorder_id, '_preorder_qty', 1 );
			update_post_meta( $preorder_id, '_preorder_shipping', 100 );
			update_post_meta( $preorder_id, '_preorder_address', "123/45 ถนนวิภาวดีรังสิต แขวงดินแดง เขตดินแดง กรุงเทพฯ 10400" );
			update_post_meta( $preorder_id, '_preorder_tracking', 'TH8829481947' );
			update_post_meta( $preorder_id, '_preorder_current_step_idx', 3 ); // In Transit to Thailand
			update_post_meta( $preorder_id, '_preorder_product_id', $prod_id );

			$dummy_steps = [
				[ 'title' => 'รับคำสั่งซื้อเรียบร้อย (Order Received)', 'desc' => 'ระบบได้รับการพรีออเดอร์ของท่านแล้ว', 'date' => '20 ก.ค. 2569 09:00' ],
				[ 'title' => 'สั่งซื้อสินค้าสำเร็จ (Purchased at Origin Country)', 'desc' => 'ทำรายการสั่งซื้อสินค้าจากต่างประเทศเรียบร้อยแล้ว', 'date' => '22 ก.ค. 2569 14:30' ],
				[ 'title' => 'ถึงคลังสินค้าต่างประเทศ (Arrived at Overseas Warehouse)', 'desc' => 'สินค้าเดินทางถึงคลังจัดเก็บในต่างประเทศแล้ว เตรียมพร้อมส่งกลับไทย', 'date' => '25 ก.ค. 2569 11:15' ],
				[ 'title' => 'ระหว่างขนส่งระหว่างประเทศ (In Transit to Thailand)', 'desc' => 'สินค้าอยู่ระหว่างการจัดส่งมายังประเทศไทย', 'date' => '27 ก.ค. 2569 08:00' ],
				[ 'title' => 'ผ่านพิธีการศุลกากร (Customs Cleared)', 'desc' => 'สินค้าผ่านการตรวจและเสียภาษีนำเข้าเรียบร้อยแล้ว', 'date' => '' ],
				[ 'title' => 'ถึงศูนย์กระจายสินค้าในไทย (Arrived at Local Sorting Center)', 'desc' => 'สินค้าถึงศูนย์จัดส่งในประเทศไทยและกำลังจัดเตรียมเพื่อนำจ่าย', 'date' => '' ],
				[ 'title' => 'อยู่ระหว่างนำจ่าย (Out for Delivery)', 'desc' => 'บริษัทขนส่งในประเทศกำลังนำจ่ายสินค้าไปยังที่อยู่ของท่าน', 'date' => '' ],
				[ 'title' => 'จัดส่งสำเร็จ (Delivered)', 'desc' => 'ท่านได้รับสินค้าเรียบร้อยแล้ว', 'date' => '' ],
			];
			update_post_meta( $preorder_id, '_preorder_steps', $dummy_steps );
		}
	}
}

/**
 * Flush rewrite rules on deactivation.
 */
function preorder_raka_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'preorder_raka_deactivate' );

/**
 * Register Meta Boxes for Pre-order settings and dynamic steps.
 */
function preorder_raka_add_meta_boxes() {
	add_meta_box(
		'preorder_raka_details_metabox',
		esc_html__( 'ข้อมูลพรีออเดอร์ (Pre-order Details)', 'preorder-raka' ),
		'preorder_raka_render_details_metabox',
		'preorder',
		'normal',
		'high'
	);

	add_meta_box(
		'preorder_raka_steps_metabox',
		esc_html__( 'สถานะขั้นตอนความคืบหน้า (Dynamic Progress Steps)', 'preorder-raka' ),
		'preorder_raka_render_steps_metabox',
		'preorder',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'preorder_raka_add_meta_boxes' );

/**
 * Render Details Meta Box.
 */
function preorder_raka_render_details_metabox( $post ) {
	wp_nonce_field( 'preorder_raka_save_details', 'preorder_raka_details_nonce' );

	$order_no           = get_post_meta( $post->ID, '_preorder_no', true );
	$phone              = get_post_meta( $post->ID, '_preorder_phone', true );
	$citizen_id         = get_post_meta( $post->ID, '_preorder_citizen_id_last4', true );
	$selected_prod_id   = get_post_meta( $post->ID, '_preorder_product_id', true );
	$product            = get_post_meta( $post->ID, '_preorder_product', true );
	$price              = get_post_meta( $post->ID, '_preorder_price', true );
	$qty                = get_post_meta( $post->ID, '_preorder_qty', true ) ?: 1;
	$shipping           = get_post_meta( $post->ID, '_preorder_shipping', true ) ?: 0;
	$address            = get_post_meta( $post->ID, '_preorder_address', true );
	$tracking           = get_post_meta( $post->ID, '_preorder_tracking', true );

	$products = get_posts([
		'post_type'   => 'preorder_product',
		'numberposts' => -1,
	]);
	?>
	<table id="preorder-raka-details-table" class="form-table" style="margin: 10px 0;">
		<tbody>
			<tr>
				<th scope="row"><label for="preorder_raka_product_id"><?php esc_html_e( 'ผูกกับสินค้าพรีออเดอร์', 'preorder-raka' ); ?></label></th>
				<td>
					<select id="preorder_raka_product_id" name="preorder_raka_product_id" style="width: 25em;">
						<option value=""><?php esc_html_e( '-- ระบุสินค้าพรีออเดอร์ --', 'preorder-raka' ); ?></option>
						<?php foreach ( $products as $prod ) : ?>
							<option value="<?php echo esc_attr( $prod->ID ); ?>" <?php selected( $selected_prod_id, $prod->ID ); ?>>
								<?php echo esc_html( $prod->post_title ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<p class="description" style="margin-top: 5px;"><?php esc_html_e( 'เลือกสินค้าพรีออเดอร์เพื่อดึงชื่อสินค้า ราคา และรูปภาพหลักมาใช้งานโดยอัตโนมัติ', 'preorder-raka' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="preorder_raka_no"><?php esc_html_e( 'เลขที่พรีออเดอร์', 'preorder-raka' ); ?></label></th>
				<td>
					<div style="display: flex; align-items: center; gap: 8px;">
						<input id="preorder_raka_no" class="regular-text" type="text" name="preorder_raka_no" value="<?php echo esc_attr( $order_no ); ?>" placeholder="e.g. RAKA-PRE-9999">
						<button type="button" class="button" onclick="document.getElementById('preorder_raka_no').value = 'RAKA-PRE-' + Math.floor(100000 + Math.random() * 900000);">
							<span class="dashicons dashicons-randomize" style="vertical-align: middle; font-size: 16px; width: 16px; height: 16px;"></span> <?php esc_html_e( 'สุ่มเลขพรีออเดอร์ (Random SKU)', 'preorder-raka' ); ?>
						</button>
					</div>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="preorder_raka_phone"><?php esc_html_e( 'เบอร์โทรศัพท์', 'preorder-raka' ); ?></label></th>
				<td><input id="preorder_raka_phone" class="regular-text" type="text" name="preorder_raka_phone" value="<?php echo esc_attr( $phone ); ?>"></td>
			</tr>
			<tr>
				<th scope="row"><label for="preorder_raka_citizen"><?php esc_html_e( 'เลขบัตรประชาชน (4 ตัวท้าย)', 'preorder-raka' ); ?></label></th>
				<td><input id="preorder_raka_citizen" class="small-text" type="text" name="preorder_raka_citizen" value="<?php echo esc_attr( $citizen_id ); ?>" maxlength="4"></td>
			</tr>
			<tr>
				<th scope="row"><label for="preorder_raka_product"><?php esc_html_e( 'ชื่อสินค้า/แคมเปญ', 'preorder-raka' ); ?></label></th>
				<td><input id="preorder_raka_product" class="regular-text" type="text" name="preorder_raka_product" value="<?php echo esc_attr( $product ); ?>"></td>
			</tr>
			<tr>
				<th scope="row"><label for="preorder_raka_price"><?php esc_html_e( 'ราคาสินค้า (บาท)', 'preorder-raka' ); ?></label></th>
				<td><input id="preorder_raka_price" class="small-text" type="number" name="preorder_raka_price" value="<?php echo esc_attr( $price ); ?>"></td>
			</tr>
			<tr>
				<th scope="row"><label for="preorder_raka_qty"><?php esc_html_e( 'จำนวน', 'preorder-raka' ); ?></label></th>
				<td><input id="preorder_raka_qty" class="small-text" type="number" name="preorder_raka_qty" value="<?php echo esc_attr( $qty ); ?>"></td>
			</tr>
			<tr>
				<th scope="row"><label for="preorder_raka_shipping"><?php esc_html_e( 'ค่าจัดส่ง (บาท)', 'preorder-raka' ); ?></label></th>
				<td><input id="preorder_raka_shipping" class="small-text" type="number" name="preorder_raka_shipping" value="<?php echo esc_attr( $shipping ); ?>"></td>
			</tr>
			<tr>
				<th scope="row"><label for="preorder_raka_address"><?php esc_html_e( 'ที่อยู่จัดส่ง', 'preorder-raka' ); ?></label></th>
				<td><textarea id="preorder_raka_address" class="large-text" name="preorder_raka_address" rows="3"><?php echo esc_textarea( $address ); ?></textarea></td>
			</tr>
			<tr>
				<th scope="row"><label for="preorder_raka_courier"><?php esc_html_e( 'ผู้ให้บริการขนส่ง (Courier)', 'preorder-raka' ); ?></label></th>
				<td>
					<?php $courier = get_post_meta( $post->ID, '_preorder_courier', true ) ?: 'kerry'; ?>
					<select id="preorder_raka_courier" name="preorder_raka_courier" style="width: 25em;">
						<option value="kerry" <?php selected( $courier, 'kerry' ); ?>>Kerry Express (เคอรี่ เอ็กซ์เพรส)</option>
						<option value="flash" <?php selected( $courier, 'flash' ); ?>>Flash Express (แฟลช เอ็กซ์เพรส)</option>
						<option value="jt" <?php selected( $courier, 'jt' ); ?>>J&T Express (เจแอนด์ที เอ็กซ์เพรส)</option>
						<option value="thp" <?php selected( $courier, 'thp' ); ?>>ไปรษณีย์ไทย (EMS / Thailand Post)</option>
						<option value="spx" <?php selected( $courier, 'spx' ); ?>>Shopee Xpress (SPX)</option>
						<option value="ninja" <?php selected( $courier, 'ninja' ); ?>>Ninja Van (นินจาแวน)</option>
						<option value="other" <?php selected( $courier, 'other' ); ?>>ขนส่งอื่น ๆ (Other)</option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="preorder_raka_tracking"><?php esc_html_e( 'เลขพัสดุ (Tracking No.)', 'preorder-raka' ); ?></label></th>
				<td><input id="preorder_raka_tracking" class="regular-text" type="text" name="preorder_raka_tracking" value="<?php echo esc_attr( $tracking ); ?>"></td>
			</tr>
			<tr>
				<th scope="row"><label for="preorder_raka_tracking_link"><?php esc_html_e( 'ลิงก์เช็คพัสดุ (Tracking Link)', 'preorder-raka' ); ?></label></th>
				<td>
					<?php $tracking_link = get_post_meta( $post->ID, '_preorder_tracking_link', true ); ?>
					<input id="preorder_raka_tracking_link" class="large-text" type="url" name="preorder_raka_tracking_link" value="<?php echo esc_attr( $tracking_link ); ?>" placeholder="e.g. https://th.kerryexpress.com/th/track/?track=...">
					<p class="description" style="margin-top: 5px;"><?php esc_html_e( 'หากระบุลิงก์ ปุ่มเช็คพัสดุจะเปิดลิงก์ไปยังเว็บขนส่งโดยตรง', 'preorder-raka' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="preorder_raka_facebook"><?php esc_html_e( 'ลิงก์ Facebook / Messenger', 'preorder-raka' ); ?></label></th>
				<td>
					<?php $facebook = get_post_meta( $post->ID, '_preorder_facebook', true ); ?>
					<input id="preorder_raka_facebook" class="large-text" type="url" name="preorder_raka_facebook" value="<?php echo esc_attr( $facebook ); ?>" placeholder="https://m.me/yourfanpage หรือ https://facebook.com/yourpage">
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="preorder_raka_line"><?php esc_html_e( 'ลิงก์ LINE Official / LINE ID', 'preorder-raka' ); ?></label></th>
				<td>
					<?php $line = get_post_meta( $post->ID, '_preorder_line', true ); ?>
					<input id="preorder_raka_line" class="large-text" type="url" name="preorder_raka_line" value="<?php echo esc_attr( $line ); ?>" placeholder="https://line.me/ti/p/@yourlineid หรือ https://lin.ee/xxxx">
				</td>
			</tr>
		</tbody>
	</table>
	<?php
}

/**
 * Render Steps Meta Box.
 */
function preorder_raka_render_steps_metabox( $post ) {
	$steps       = get_post_meta( $post->ID, '_preorder_steps', true );
	$current_idx = get_post_meta( $post->ID, '_preorder_current_step_idx', true );

	if ( ! is_array( $steps ) ) {
		// Set default steps
		$steps = [
			[ 'title' => 'รับคำสั่งซื้อเรียบร้อย (Order Received)', 'desc' => 'ระบบได้รับการพรีออเดอร์ของท่านแล้ว', 'date' => '' ],
			[ 'title' => 'สั่งซื้อสินค้าสำเร็จ (Purchased at Origin Country)', 'desc' => 'ทำรายการสั่งซื้อสินค้าจากต่างประเทศเรียบร้อยแล้ว', 'date' => '' ],
			[ 'title' => 'ถึงคลังสินค้าต่างประเทศ (Arrived at Overseas Warehouse)', 'desc' => 'สินค้าเดินทางถึงคลังจัดเก็บในต่างประเทศแล้ว เตรียมพร้อมส่งกลับไทย', 'date' => '' ],
			[ 'title' => 'ระหว่างขนส่งระหว่างประเทศ (In Transit to Thailand)', 'desc' => 'สินค้าอยู่ระหว่างการจัดส่งมายังประเทศไทย', 'date' => '' ],
			[ 'title' => 'ผ่านพิธีการศุลกากร (Customs Cleared)', 'desc' => 'สินค้าผ่านการตรวจและเสียภาษีนำเข้าเรียบร้อยแล้ว', 'date' => '' ],
			[ 'title' => 'ถึงศูนย์กระจายสินค้าในไทย (Arrived at Local Sorting Center)', 'desc' => 'สินค้าถึงศูนย์จัดส่งในประเทศไทยและกำลังจัดเตรียมเพื่อนำจ่าย', 'date' => '' ],
			[ 'title' => 'อยู่ระหว่างนำจ่าย (Out for Delivery)', 'desc' => 'บริษัทขนส่งในประเทศกำลังนำจ่ายสินค้าไปยังที่อยู่ของท่าน', 'date' => '' ],
			[ 'title' => 'จัดส่งสำเร็จ (Delivered)', 'desc' => 'ท่านได้รับสินค้าเรียบร้อยแล้ว', 'date' => '' ],
		];
	}

	if ( $current_idx === '' ) {
		$current_idx = 0;
	}
	?>
	<div id="preorder-raka-steps-container" class="inside" style="margin: 0; padding: 10px 0;">
		<p class="description" style="margin-bottom: 15px;">
			<?php esc_html_e( 'กำหนดรายการขั้นตอนความคืบหน้า และระบุว่าปัจจุบันคำสั่งซื้อนี้อยู่ในขั้นตอนใด', 'preorder-raka' ); ?>
		</p>
		
		<div style="margin-bottom: 20px;">
			<label for="preorder_raka_current_step_idx" style="font-weight: 600; margin-right: 10px;"><?php esc_html_e( 'ขั้นตอนปัจจุบัน:', 'preorder-raka' ); ?></label>
			<select id="preorder_raka_current_step_idx" name="preorder_raka_current_step_idx">
				<?php foreach ( $steps as $idx => $step ) : ?>
					<option value="<?php echo esc_attr( $idx ); ?>" <?php selected( $current_idx, $idx ); ?>>
						<?php echo esc_html( ($idx + 1) . '. ' . $step['title'] ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>

		<table id="preorder-raka-steps-list-table" class="widefat" style="margin-bottom: 15px;">
			<thead>
				<tr>
					<th style="width: 5%;"><?php esc_html_e( 'ลำดับ', 'preorder-raka' ); ?></th>
					<th style="width: 30%;"><?php esc_html_e( 'หัวข้อขั้นตอน', 'preorder-raka' ); ?></th>
					<th style="width: 45%;"><?php esc_html_e( 'รายละเอียดขั้นตอน', 'preorder-raka' ); ?></th>
					<th style="width: 15%;"><?php esc_html_e( 'วันที่/เวลา (ระบุหรือไม่ก็ได้)', 'preorder-raka' ); ?></th>
					<th style="width: 5%;"><?php esc_html_e( 'ลบ', 'preorder-raka' ); ?></th>
				</tr>
			</thead>
			<tbody id="preorder-raka-steps-rows">
				<?php foreach ( $steps as $idx => $step ) : ?>
					<tr id="preorder-raka-step-row-<?php echo esc_attr($idx); ?>" class="preorder-raka-step-row">
						<td class="row-number-cell" style="vertical-align: middle; font-weight: 600;"><?php echo esc_html( $idx + 1 ); ?></td>
						<td>
							<input id="preorder_raka_step_title_<?php echo esc_attr($idx); ?>" class="large-text" type="text" name="preorder_raka_steps[<?php echo esc_attr( $idx ); ?>][title]" value="<?php echo esc_attr( $step['title'] ); ?>" required>
						</td>
						<td>
							<input id="preorder_raka_step_desc_<?php echo esc_attr($idx); ?>" class="large-text" type="text" name="preorder_raka_steps[<?php echo esc_attr( $idx ); ?>][desc]" value="<?php echo esc_attr( $step['desc'] ); ?>">
						</td>
						<td>
							<input id="preorder_raka_step_date_<?php echo esc_attr($idx); ?>" class="large-text" type="datetime-local" name="preorder_raka_steps[<?php echo esc_attr( $idx ); ?>][date]" value="<?php echo esc_attr( $step['date'] ); ?>">
						</td>
						<td style="vertical-align: middle; text-align: center;">
							<button id="preorder_raka_delete_btn_<?php echo esc_attr($idx); ?>" class="button button-link-delete delete-step-row" type="button" style="color: #a00; font-size: 16px; text-decoration: none;">&times;</button>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<button id="preorder-raka-add-step-btn" class="button button-secondary" type="button"><?php esc_html_e( 'เพิ่มขั้นตอน +', 'preorder-raka' ); ?></button>
	</div>

	<script>
		document.addEventListener('DOMContentLoaded', function() {
			const container = document.getElementById('preorder-raka-steps-rows');
			const addButton = document.getElementById('preorder-raka-add-step-btn');
			const selector = document.getElementById('preorder_raka_current_step_idx');

			function updateRowNumbersAndSelector() {
				const rows = container.querySelectorAll('.preorder-raka-step-row');
				const currentValue = selector.value;
				selector.innerHTML = '';
				
				rows.forEach((row, idx) => {
					row.querySelector('.row-number-cell').textContent = idx + 1;
					
					// Update input names
					row.querySelector('input[name*="[title]"]').name = `preorder_raka_steps[${idx}][title]`;
					row.querySelector('input[name*="[desc]"]').name = `preorder_raka_steps[${idx}][desc]`;
					row.querySelector('input[name*="[date]"]').name = `preorder_raka_steps[${idx}][date]`;

					const titleVal = row.querySelector('input[name*="[title]"]').value || `ขั้นตอนที่ ${idx + 1}`;
					const opt = document.createElement('option');
					opt.value = idx;
					opt.textContent = `${idx + 1}. ${titleVal}`;
					if (currentValue == idx) {
						opt.selected = true;
					}
					selector.appendChild(opt);
				});
			}

			addButton.addEventListener('click', function() {
				const idx = container.querySelectorAll('.preorder-raka-step-row').length;
				const row = document.createElement('tr');
				row.id = `preorder-raka-step-row-${idx}`;
				row.className = 'preorder-raka-step-row';
				row.innerHTML = `
					<td class="row-number-cell" style="vertical-align: middle; font-weight: 600;">\${idx + 1}</td>
					<td>
						<input id="preorder_raka_step_title_\${idx}" class="large-text" type="text" name="preorder_raka_steps[\${idx}][title]" value="" required>
					</td>
					<td>
						<input id="preorder_raka_step_desc_\${idx}" class="large-text" type="text" name="preorder_raka_steps[\${idx}][desc]" value="">
					</td>
					<td>
						<input id="preorder_raka_step_date_\${idx}" class="large-text" type="text" name="preorder_raka_steps[\${idx}][date]" value="" placeholder="e.g. 28 ก.ค. 2569 08:00">
					</td>
					<td style="vertical-align: middle; text-align: center;">
						<button id="preorder_raka_delete_btn_\${idx}" class="button button-link-delete delete-step-row" type="button" style="color: #a00; font-size: 16px; text-decoration: none;">&times;</button>
					</td>
				`;
				container.appendChild(row);
				updateRowNumbersAndSelector();

				row.querySelector('input[name*="[title]"]').addEventListener('input', updateRowNumbersAndSelector);
			});

			container.addEventListener('click', function(e) {
				if (e.target.classList.contains('delete-step-row')) {
					e.target.closest('tr').remove();
					updateRowNumbersAndSelector();
				}
			});

			container.querySelectorAll('input[name*="[title]"]').forEach(input => {
				input.addEventListener('input', updateRowNumbersAndSelector);
			});
		});
	</script>
	<?php
}

/**
 * Save Pre-order Metadata.
 */
function preorder_raka_save_post( $post_id ) {
	if ( ! isset( $_POST['preorder_raka_details_nonce'] ) || ! wp_verify_nonce( $_POST['preorder_raka_details_nonce'], 'preorder_raka_save_details' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// Save fields
	$fields = [
		'preorder_raka_no'              => '_preorder_no',
		'preorder_raka_phone'           => '_preorder_phone',
		'preorder_raka_citizen'         => '_preorder_citizen_id_last4',
		'preorder_raka_product'         => '_preorder_product',
		'preorder_raka_price'           => '_preorder_price',
		'preorder_raka_qty'             => '_preorder_qty',
		'preorder_raka_shipping'        => '_preorder_shipping',
		'preorder_raka_address'         => '_preorder_address',
		'preorder_raka_courier'         => '_preorder_courier',
		'preorder_raka_tracking'        => '_preorder_tracking',
		'preorder_raka_tracking_link'   => '_preorder_tracking_link',
		'preorder_raka_facebook'        => '_preorder_facebook',
		'preorder_raka_line'            => '_preorder_line',
		'preorder_raka_current_step_idx' => '_preorder_current_step_idx',
		'preorder_raka_product_id'      => '_preorder_product_id',
	];

	foreach ( $fields as $post_key => $meta_key ) {
		if ( isset( $_POST[ $post_key ] ) ) {
			update_post_meta( $post_id, $meta_key, sanitize_text_field( $_POST[ $post_key ] ) );
		}
	}

	// Save Steps Array
	if ( isset( $_POST['preorder_raka_steps'] ) && is_array( $_POST['preorder_raka_steps'] ) ) {
		$sanitized_steps = [];
		foreach ( $_POST['preorder_raka_steps'] as $step ) {
			if ( ! empty( $step['title'] ) ) {
				$sanitized_steps[] = [
					'title' => sanitize_text_field( $step['title'] ),
					'desc'  => sanitize_text_field( $step['desc'] ),
					'date'  => sanitize_text_field( $step['date'] ),
				];
			}
		}
		update_post_meta( $post_id, '_preorder_steps', $sanitized_steps );
	} else {
		delete_post_meta( $post_id, '_preorder_steps' );
	}
}
add_action( 'save_post_preorder', 'preorder_raka_save_post' );

/**
 * Filter single page template for Pre-orders to render React App.
 */
function preorder_raka_single_template( $single_template ) {
	global $post;

	if ( 'preorder' === $post->post_type ) {
		$custom_template = plugin_dir_path( __FILE__ ) . 'templates/single-preorder.php';
		if ( file_exists( $custom_template ) ) {
			return $custom_template;
		}
	}

	return $single_template;
}
add_filter( 'single_template', 'preorder_raka_single_template' );

/**
 * Enqueue assets for single-preorder template page.
 */
function preorder_raka_enqueue_assets() {
	if ( is_singular( 'preorder' ) || is_front_page() || is_home() ) {
		$asset_file   = plugin_dir_path( __FILE__ ) . 'build/index.asset.php';
		$script_file  = plugin_dir_path( __FILE__ ) . 'build/index.js';
		$dependencies = [ 'wp-element' ];
		$version      = '1.0.0';

		if ( file_exists( $asset_file ) ) {
			$assets_info  = require $asset_file;
			$dependencies = isset( $assets_info['dependencies'] ) ? $assets_info['dependencies'] : $dependencies;
			$version      = isset( $assets_info['version'] ) ? $assets_info['version'] : $version;
		}

		if ( file_exists( $script_file ) ) {
			$version .= '-' . filemtime( $script_file );
		}
		// Hide Header & Footer CSS
		wp_enqueue_style(
			'preorder-raka-hide-header-footer',
			plugin_dir_url( __FILE__ ) . 'assets/css/hide-header-footer.css',
			[],
			'1.0.0'
		);

		// RemixIcon v4 — icon font for React UI
		wp_enqueue_style(
			'remixicon',
			'https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css',
			[],
			'4.5.0'
		);

		wp_enqueue_script(
			'master-css',
			plugin_dir_url( __FILE__ ) . 'assets/js/master-css.js',
			[],
			'2.0.0',
			false
		);

		wp_enqueue_script(
			'preorder-raka-app',
			plugin_dir_url( __FILE__ ) . 'build/index.js',
			$dependencies,
			$version,
			true
		);
		wp_localize_script(
			'preorder-raka-app',
			'preorderRakaSettings',
			[
				'root'  => esc_url_raw( rest_url( 'preorder-raka/v1/' ) ),
				'nonce' => wp_create_nonce( 'wp_rest' ),
				'home'  => esc_url( home_url() ),
			]
		);

	}
}
add_action( 'wp_enqueue_scripts', 'preorder_raka_enqueue_assets' );
