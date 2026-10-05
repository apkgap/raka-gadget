<?php
/**
 * Export and Import Handler for Pre-orders and Pre-order Products.
 *
 * @package Preorder_Raka
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Preorder_Raka_Export_Import
 */
class Preorder_Raka_Export_Import {

	/**
	 * Initialize hooks.
	 */
	public static function init(): void {
		// Register Admin Menu
		add_action( 'admin_menu', [ __CLASS__, 'add_admin_menu' ] );

		// Handle Admin POST Export actions
		add_action( 'admin_post_preorder_raka_export', [ __CLASS__, 'handle_export' ] );

		// Handle Admin POST Import actions
		add_action( 'admin_post_preorder_raka_import', [ __CLASS__, 'handle_import' ] );

		// Handle Sample Template download
		add_action( 'admin_post_preorder_raka_sample_template', [ __CLASS__, 'handle_sample_template' ] );

		// Add Export/Import button to CPT list tables
		add_action( 'views_edit-preorder', [ __CLASS__, 'render_list_action_buttons' ] );
		add_action( 'views_edit-preorder_product', [ __CLASS__, 'render_list_action_buttons' ] );
	}

	/**
	 * Register Admin Submenu Page under Pre-order menu.
	 */
	public static function add_admin_menu(): void {
		add_submenu_page(
			'edit.php?post_type=preorder',
			esc_html__( 'นำเข้า / ส่งออกข้อมูล (Import & Export)', 'preorder-raka' ),
			esc_html__( 'นำเข้า / ส่งออก (Import & Export)', 'preorder-raka' ),
			'manage_options',
			'preorder-raka-export-import',
			[ __CLASS__, 'render_admin_page' ]
		);
	}

	/**
	 * Render buttons in the top of CPT list views.
	 *
	 * @param array $views Current views.
	 * @return array
	 */
	public static function render_list_action_buttons( array $views ): array {
		global $typenow;
		if ( ! in_array( $typenow, [ 'preorder', 'preorder_product' ], true ) ) {
			return $views;
		}

		$export_json_url = wp_nonce_url(
			admin_url( 'admin-post.php?action=preorder_raka_export&type=' . $typenow . '&format=json' ),
			'preorder_raka_export_nonce'
		);
		$export_csv_url = wp_nonce_url(
			admin_url( 'admin-post.php?action=preorder_raka_export&type=' . $typenow . '&format=csv' ),
			'preorder_raka_export_nonce'
		);
		$import_url = admin_url( 'edit.php?post_type=preorder&page=preorder-raka-export-import&tab=' . $typenow );

		echo '<div id="preorder-raka-quick-actions" style="margin: 10px 0 14px 0; display: inline-flex; gap: 8px; flex-wrap: wrap;">';
		echo '<a id="preorder-btn-export-json" href="' . esc_url( $export_json_url ) . '" class="button button-secondary" style="display:inline-flex;align-items:center;gap:4px;"><span class="dashicons dashicons-download" style="font-size:16px;line-height:26px;"></span> ' . esc_html__( 'Export JSON', 'preorder-raka' ) . '</a>';
		echo '<a id="preorder-btn-export-csv" href="' . esc_url( $export_csv_url ) . '" class="button button-secondary" style="display:inline-flex;align-items:center;gap:4px;"><span class="dashicons dashicons-media-spreadsheet" style="font-size:16px;line-height:26px;"></span> ' . esc_html__( 'Export CSV', 'preorder-raka' ) . '</a>';
		echo '<a id="preorder-btn-import-page" href="' . esc_url( $import_url ) . '" class="button button-primary" style="display:inline-flex;align-items:center;gap:4px;"><span class="dashicons dashicons-upload" style="font-size:16px;line-height:26px;"></span> ' . esc_html__( 'นำเข้าไฟล์ (Import)', 'preorder-raka' ) . '</a>';
		echo '</div>';

		return $views;
	}

	/**
	 * Render the Export/Import Admin UI Page.
	 */
	public static function render_admin_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้', 'preorder-raka' ) );
		}

		$active_tab = isset( $_GET['tab'] ) && 'preorder_product' === $_GET['tab'] ? 'preorder_product' : 'preorder';
		$result_msg = get_transient( 'preorder_raka_import_result' );
		if ( $result_msg ) {
			delete_transient( 'preorder_raka_import_result' );
		}
		?>
		<div id="preorder-export-import-wrap" class="wrap" style="max-width: 1200px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
			<h1 id="preorder-exp-imp-heading" style="display: flex; align-items: center; gap: 10px; margin-bottom: 18px;">
				<span id="preorder-exp-icon" class="dashicons dashicons-database" style="font-size: 30px; width: 30px; height: 30px;"></span>
				<?php esc_html_e( 'จัดการนำเข้า & ส่งออกข้อมูล (Pre-order Export & Import)', 'preorder-raka' ); ?>
			</h1>

			<?php if ( ! empty( $result_msg ) ) : ?>
				<div id="preorder-import-notice" class="notice notice-<?php echo esc_attr( $result_msg['type'] ?? 'info' ); ?> is-dismissible" style="padding: 12px 16px; border-radius: 8px; margin-bottom: 20px;">
					<h3 style="margin: 0 0 6px 0; font-size: 15px;"><?php echo esc_html( $result_msg['title'] ?? 'ผลการดำเนินการ' ); ?></h3>
					<p style="margin: 0; font-size: 13px;"><?php echo esc_html( $result_msg['message'] ); ?></p>
					<?php if ( ! empty( $result_msg['details'] ) ) : ?>
						<ul style="margin: 8px 0 0 16px; list-style-type: disc; font-size: 12px; color: #475569;">
							<?php foreach ( $result_msg['details'] as $detail ) : ?>
								<li><?php echo esc_html( $detail ); ?></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<nav id="preorder-exp-tabs" class="nav-tab-wrapper" style="margin-bottom: 24px;">
				<a id="tab-preorder" href="<?php echo esc_url( admin_url( 'edit.php?post_type=preorder&page=preorder-raka-export-import&tab=preorder' ) ); ?>" class="nav-tab <?php echo 'preorder' === $active_tab ? 'nav-tab-active' : ''; ?>" style="font-weight: 600; font-size: 14px;">
					<span class="dashicons dashicons-cart" style="margin-right: 4px;"></span> <?php esc_html_e( '1. รายการพรีออเดอร์ (Pre-orders)', 'preorder-raka' ); ?>
				</a>
				<a id="tab-preorder-product" href="<?php echo esc_url( admin_url( 'edit.php?post_type=preorder&page=preorder-raka-export-import&tab=preorder_product' ) ); ?>" class="nav-tab <?php echo 'preorder_product' === $active_tab ? 'nav-tab-active' : ''; ?>" style="font-weight: 600; font-size: 14px;">
					<span class="dashicons dashicons-products" style="margin-right: 4px;"></span> <?php esc_html_e( '2. สินค้าพรีออเดอร์ (Pre-order Products)', 'preorder-raka' ); ?>
				</a>
			</nav>

			<?php if ( 'preorder' === $active_tab ) : ?>
				<!-- ── PREORDER TAB ── -->
				<div id="preorder-tab-content" style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
					<!-- Export Card -->
					<div id="card-export-preorder" class="postbox" style="padding: 20px; border-radius: 12px; border: 1px solid #cbd5e1; box-shadow: 0 1px 3px rgba(0,0,0,0.05); background: #fff;">
						<div class="postbox-header" style="border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 16px;">
							<h2 style="font-size: 16px; font-weight: 700; margin: 0; color: #0f172a; display: flex; align-items: center; gap: 8px;">
								<span class="dashicons dashicons-download" style="color: #2563eb;"></span>
								<?php esc_html_e( 'ส่งออกข้อมูลพรีออเดอร์ (Export Pre-orders)', 'preorder-raka' ); ?>
							</h2>
						</div>
						<p style="font-size: 13px; color: #64748b; margin-bottom: 18px;">
							<?php esc_html_e( 'ดาวน์โหลดรายการพรีออเดอร์ทั้งหมด พร้อมข้อมูลลูกค้า เบอร์โทร สถานะขั้นตอนการจัดส่ง และ Tracking Number', 'preorder-raka' ); ?>
						</p>

						<form id="form-export-preorder" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
							<?php wp_nonce_field( 'preorder_raka_export_nonce', 'preorder_raka_nonce' ); ?>
							<input type="hidden" name="action" value="preorder_raka_export" />
							<input type="hidden" name="type" value="preorder" />

							<div style="margin-bottom: 16px;">
								<label style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 6px;"><?php esc_html_e( 'รูปแบบไฟล์ (Format):', 'preorder-raka' ); ?></label>
								<label style="margin-right: 16px; font-size: 13px;">
									<input type="radio" name="format" value="json" checked="checked" /> <strong>JSON</strong> (แนะนำ - บันทึกขั้นตอน Steps ครบถ้วน 100%)
								</label>
								<label style="font-size: 13px;">
									<input type="radio" name="format" value="csv" /> <strong>CSV</strong> (Excel / Spreadsheet UTF-8)
								</label>
							</div>

							<div style="margin-bottom: 20px;">
								<label style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 6px;"><?php esc_html_e( 'สถานะคำสั่งซื้อ:', 'preorder-raka' ); ?></label>
								<select name="status_filter" style="width: 100%; max-width: 320px;">
									<option value="all"><?php esc_html_e( 'ทั้งหมด (ทุกสถานะ)', 'preorder-raka' ); ?></option>
									<option value="publish"><?php esc_html_e( 'เฉพาะรายการที่เผยแพร่ (Publish)', 'preorder-raka' ); ?></option>
									<option value="draft"><?php esc_html_e( 'เฉพาะฉบับร่าง (Draft)', 'preorder-raka' ); ?></option>
								</select>
							</div>

							<button id="btn-submit-export-preorder" type="submit" class="button button-primary button-hero" style="font-size: 14px; display: inline-flex; align-items: center; gap: 6px;">
								<span class="dashicons dashicons-download" style="line-height: 28px;"></span>
								<?php esc_html_e( 'ดาวน์โหลดไฟล์ Export', 'preorder-raka' ); ?>
							</button>
						</form>
					</div>

					<!-- Import Card -->
					<div id="card-import-preorder" class="postbox" style="padding: 20px; border-radius: 12px; border: 1px solid #cbd5e1; box-shadow: 0 1px 3px rgba(0,0,0,0.05); background: #fff;">
						<div class="postbox-header" style="border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 16px;">
							<h2 style="font-size: 16px; font-weight: 700; margin: 0; color: #0f172a; display: flex; align-items: center; gap: 8px;">
								<span class="dashicons dashicons-upload" style="color: #16a34a;"></span>
								<?php esc_html_e( 'นำเข้าข้อมูลพรีออเดอร์ (Import Pre-orders)', 'preorder-raka' ); ?>
							</h2>
						</div>
						<p style="font-size: 13px; color: #64748b; margin-bottom: 16px;">
							<?php esc_html_e( 'อัปโหลดไฟล์ JSON หรือ CSV เพื่อเพิ่มหรืออัปเดตข้อมูลพรีออเดอร์ในระบบ', 'preorder-raka' ); ?>
						</p>

						<form id="form-import-preorder" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
							<?php wp_nonce_field( 'preorder_raka_import_nonce', 'preorder_raka_nonce' ); ?>
							<input type="hidden" name="action" value="preorder_raka_import" />
							<input type="hidden" name="type" value="preorder" />

							<div style="margin-bottom: 16px;">
								<label for="import_file_preorder" style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 6px;">
									<?php esc_html_e( 'เลือกไฟล์ (.json หรือ .csv):', 'preorder-raka' ); ?>
								</label>
								<input id="import_file_preorder" type="file" name="import_file" accept=".json,.csv" required="required" style="padding: 6px; border: 1px solid #cbd5e1; border-radius: 6px; width: 100%;" />
							</div>

							<div style="margin-bottom: 16px;">
								<label style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600;">
									<input type="checkbox" name="update_existing" value="1" checked="checked" />
									<?php esc_html_e( 'อัปเดตข้อมูลเดิมหากพบเลขที่พรีออเดอร์ (_preorder_no) ซ้ำในระบบ', 'preorder-raka' ); ?>
								</label>
							</div>

							<div style="margin-bottom: 20px; padding: 10px 14px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
								<span style="font-size: 12px; color: #64748b;"><?php esc_html_e( 'ต้องการไฟล์ตัวอย่าง?', 'preorder-raka' ); ?></span>
								<div style="display: flex; gap: 8px;">
									<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=preorder_raka_sample_template&type=preorder&format=json' ), 'preorder_sample' ) ); ?>" class="button button-small">ดาวน์โหลดตัวอย่าง JSON</a>
									<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=preorder_raka_sample_template&type=preorder&format=csv' ), 'preorder_sample' ) ); ?>" class="button button-small">ดาวน์โหลดตัวอย่าง CSV</a>
								</div>
							</div>

							<button id="btn-submit-import-preorder" type="submit" class="button button-primary button-hero" style="font-size: 14px; display: inline-flex; align-items: center; gap: 6px;">
								<span class="dashicons dashicons-upload" style="line-height: 28px;"></span>
								<?php esc_html_e( 'เริ่มนำเข้าข้อมูล (Import Now)', 'preorder-raka' ); ?>
							</button>
						</form>
					</div>
				</div>

			<?php else : ?>
				<!-- ── PREORDER PRODUCT TAB ── -->
				<div id="preorder-product-tab-content" style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
					<!-- Export Products Card -->
					<div id="card-export-products" class="postbox" style="padding: 20px; border-radius: 12px; border: 1px solid #cbd5e1; box-shadow: 0 1px 3px rgba(0,0,0,0.05); background: #fff;">
						<div class="postbox-header" style="border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 16px;">
							<h2 style="font-size: 16px; font-weight: 700; margin: 0; color: #0f172a; display: flex; align-items: center; gap: 8px;">
								<span class="dashicons dashicons-download" style="color: #2563eb;"></span>
								<?php esc_html_e( 'ส่งออกรายการสินค้าพรีออเดอร์ (Export Pre-order Products)', 'preorder-raka' ); ?>
							</h2>
						</div>
						<p style="font-size: 13px; color: #64748b; margin-bottom: 18px;">
							<?php esc_html_e( 'ดาวน์โหลดรายการสินค้าเฉพาะพรีออเดอร์ทั้งหมด พร้อมราคา รูปภาพปก และรายละเอียด', 'preorder-raka' ); ?>
						</p>

						<form id="form-export-products" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
							<?php wp_nonce_field( 'preorder_raka_export_nonce', 'preorder_raka_nonce' ); ?>
							<input type="hidden" name="action" value="preorder_raka_export" />
							<input type="hidden" name="type" value="preorder_product" />

							<div style="margin-bottom: 16px;">
								<label style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 6px;"><?php esc_html_e( 'รูปแบบไฟล์ (Format):', 'preorder-raka' ); ?></label>
								<label style="margin-right: 16px; font-size: 13px;">
									<input type="radio" name="format" value="json" checked="checked" /> <strong>JSON</strong>
								</label>
								<label style="font-size: 13px;">
									<input type="radio" name="format" value="csv" /> <strong>CSV</strong> (Excel)
								</label>
							</div>

							<button id="btn-submit-export-products" type="submit" class="button button-primary button-hero" style="font-size: 14px; display: inline-flex; align-items: center; gap: 6px;">
								<span class="dashicons dashicons-download" style="line-height: 28px;"></span>
								<?php esc_html_e( 'ดาวน์โหลดรายการสินค้า', 'preorder-raka' ); ?>
							</button>
						</form>
					</div>

					<!-- Import Products Card -->
					<div id="card-import-products" class="postbox" style="padding: 20px; border-radius: 12px; border: 1px solid #cbd5e1; box-shadow: 0 1px 3px rgba(0,0,0,0.05); background: #fff;">
						<div class="postbox-header" style="border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 16px;">
							<h2 style="font-size: 16px; font-weight: 700; margin: 0; color: #0f172a; display: flex; align-items: center; gap: 8px;">
								<span class="dashicons dashicons-upload" style="color: #16a34a;"></span>
								<?php esc_html_e( 'นำเข้ารายการสินค้าพรีออเดอร์ (Import Pre-order Products)', 'preorder-raka' ); ?>
							</h2>
						</div>
						<p style="font-size: 13px; color: #64748b; margin-bottom: 16px;">
							<?php esc_html_e( 'อัปโหลดไฟล์ JSON หรือ CSV เพื่อเพิ่มรายการสินค้าพรีออเดอร์ใหม่ หรืออัปเดตราคา/รายละเอียด', 'preorder-raka' ); ?>
						</p>

						<form id="form-import-products" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
							<?php wp_nonce_field( 'preorder_raka_import_nonce', 'preorder_raka_nonce' ); ?>
							<input type="hidden" name="action" value="preorder_raka_import" />
							<input type="hidden" name="type" value="preorder_product" />

							<div style="margin-bottom: 16px;">
								<label for="import_file_products" style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 6px;">
									<?php esc_html_e( 'เลือกไฟล์ (.json หรือ .csv):', 'preorder-raka' ); ?>
								</label>
								<input id="import_file_products" type="file" name="import_file" accept=".json,.csv" required="required" style="padding: 6px; border: 1px solid #cbd5e1; border-radius: 6px; width: 100%;" />
							</div>

							<div style="margin-bottom: 16px;">
								<label style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600;">
									<input type="checkbox" name="update_existing" value="1" checked="checked" />
									<?php esc_html_e( 'อัปเดตข้อมูลสินค้าเดิมหากพบชื่อสินค้า (Product Title) ซ้ำกัน', 'preorder-raka' ); ?>
								</label>
							</div>

							<div style="margin-bottom: 20px; padding: 10px 14px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
								<span style="font-size: 12px; color: #64748b;"><?php esc_html_e( 'ต้องการไฟล์ตัวอย่าง?', 'preorder-raka' ); ?></span>
								<div style="display: flex; gap: 8px;">
									<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=preorder_raka_sample_template&type=preorder_product&format=json' ), 'preorder_sample' ) ); ?>" class="button button-small">ดาวน์โหลดตัวอย่าง JSON</a>
									<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=preorder_raka_sample_template&type=preorder_product&format=csv' ), 'preorder_sample' ) ); ?>" class="button button-small">ดาวน์โหลดตัวอย่าง CSV</a>
								</div>
							</div>

							<button id="btn-submit-import-products" type="submit" class="button button-primary button-hero" style="font-size: 14px; display: inline-flex; align-items: center; gap: 6px;">
								<span class="dashicons dashicons-upload" style="line-height: 28px;"></span>
								<?php esc_html_e( 'เริ่มนำเข้าสินค้า (Import Now)', 'preorder-raka' ); ?>
							</button>
						</form>
					</div>
				</div>
			<?php endif; ?>

		</div>
		<?php
	}

	/**
	 * Handle Export Request.
	 */
	public static function handle_export(): void {
		check_admin_referer( 'preorder_raka_export_nonce', 'preorder_raka_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'preorder-raka' ) );
		}

		$type   = sanitize_key( $_REQUEST['type'] ?? 'preorder' );
		$format = sanitize_key( $_REQUEST['format'] ?? 'json' );
		$status = sanitize_key( $_REQUEST['status_filter'] ?? 'all' );

		if ( 'preorder_product' === $type ) {
			self::export_products( $format );
		} else {
			self::export_preorders( $format, $status );
		}
		exit;
	}

	/**
	 * Export Preorders.
	 */
	private static function export_preorders( string $format, string $status ): void {
		$args = [
			'post_type'      => 'preorder',
			'post_status'    => 'all' === $status ? 'any' : $status,
			'posts_per_page' => -1,
			'orderby'        => 'ID',
			'order'          => 'DESC',
		];

		$posts = get_posts( $args );
		$data  = [];

		foreach ( $posts as $p ) {
			$steps = get_post_meta( $p->ID, '_preorder_steps', true );
			if ( ! is_array( $steps ) ) {
				$steps = [];
			}

			$data[] = [
				'id'                  => $p->ID,
				'title'               => $p->post_title,
				'status'              => $p->post_status,
				'date'                => $p->post_date,
				'preorder_no'         => (string) get_post_meta( $p->ID, '_preorder_no', true ),
				'phone'               => (string) get_post_meta( $p->ID, '_preorder_phone', true ),
				'citizen_id_last4'    => (string) get_post_meta( $p->ID, '_preorder_citizen_id_last4', true ),
				'product_id'          => (int) get_post_meta( $p->ID, '_preorder_product_id', true ),
				'product_name'        => (string) get_post_meta( $p->ID, '_preorder_product', true ),
				'price'               => (float) get_post_meta( $p->ID, '_preorder_price', true ),
				'qty'                 => (int) ( get_post_meta( $p->ID, '_preorder_qty', true ) ?: 1 ),
				'shipping'            => (float) ( get_post_meta( $p->ID, '_preorder_shipping', true ) ?: 0 ),
				'user_id'             => (int) get_post_meta( $p->ID, '_preorder_user_id', true ),
				'address'             => (string) get_post_meta( $p->ID, '_preorder_address', true ),
				'tracking'            => (string) get_post_meta( $p->ID, '_preorder_tracking', true ),
				'current_step_idx'    => (int) get_post_meta( $p->ID, '_preorder_current_step_idx', true ),
				'steps'               => $steps,
			];
		}

		$filename = 'raka_preorders_export_' . gmdate( 'Y-m-d_His' );

		if ( 'csv' === $format ) {
			header( 'Content-Type: text/csv; charset=UTF-8' );
			header( 'Content-Disposition: attachment; filename="' . $filename . '.csv"' );
			header( 'Pragma: no-cache' );
			header( 'Expires: 0' );

			$output = fopen( 'php://output', 'w' );
			// Add UTF-8 BOM for Excel compatibility
			fprintf( $output, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) );

			// CSV Header
			fputcsv( $output, [
				'preorder_no',
				'title',
				'phone',
				'citizen_id_last4',
				'product_name',
				'price',
				'qty',
				'shipping',
				'tracking',
				'address',
				'current_step_idx',
				'steps_json',
				'status',
			] );

			foreach ( $data as $row ) {
				fputcsv( $output, [
					$row['preorder_no'],
					$row['title'],
					$row['phone'],
					$row['citizen_id_last4'],
					$row['product_name'],
					$row['price'],
					$row['qty'],
					$row['shipping'],
					$row['tracking'],
					$row['address'],
					$row['current_step_idx'],
					wp_json_encode( $row['steps'], JSON_UNESCAPED_UNICODE ),
					$row['status'],
				] );
			}
			fclose( $output );
		} else {
			header( 'Content-Type: application/json; charset=UTF-8' );
			header( 'Content-Disposition: attachment; filename="' . $filename . '.json"' );
			header( 'Pragma: no-cache' );
			header( 'Expires: 0' );

			echo wp_json_encode( [
				'version'     => '1.0.0',
				'exported_at' => gmdate( 'c' ),
				'type'        => 'preorder',
				'total'       => count( $data ),
				'data'        => $data,
			], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
		}
	}

	/**
	 * Export Preorder Products.
	 */
	private static function export_products( string $format ): void {
		$args = [
			'post_type'      => 'preorder_product',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		];

		$posts = get_posts( $args );
		$data  = [];

		foreach ( $posts as $p ) {
			$thumb_url = get_the_post_thumbnail_url( $p->ID, 'full' ) ?: '';
			$price     = (float) get_post_meta( $p->ID, '_preorder_price', true );

			$data[] = [
				'id'          => $p->ID,
				'title'       => $p->post_title,
				'slug'        => $p->post_name,
				'status'      => $p->post_status,
				'price'       => $price,
				'image_url'   => $thumb_url,
				'description' => $p->post_content,
			];
		}

		$filename = 'raka_preorder_products_export_' . gmdate( 'Y-m-d_His' );

		if ( 'csv' === $format ) {
			header( 'Content-Type: text/csv; charset=UTF-8' );
			header( 'Content-Disposition: attachment; filename="' . $filename . '.csv"' );
			header( 'Pragma: no-cache' );
			header( 'Expires: 0' );

			$output = fopen( 'php://output', 'w' );
			fprintf( $output, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) );

			fputcsv( $output, [
				'title',
				'price',
				'image_url',
				'description',
				'status',
			] );

			foreach ( $data as $row ) {
				fputcsv( $output, [
					$row['title'],
					$row['price'],
					$row['image_url'],
					$row['description'],
					$row['status'],
				] );
			}
			fclose( $output );
		} else {
			header( 'Content-Type: application/json; charset=UTF-8' );
			header( 'Content-Disposition: attachment; filename="' . $filename . '.json"' );
			header( 'Pragma: no-cache' );
			header( 'Expires: 0' );

			echo wp_json_encode( [
				'version'     => '1.0.0',
				'exported_at' => gmdate( 'c' ),
				'type'        => 'preorder_product',
				'total'       => count( $data ),
				'data'        => $data,
			], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
		}
	}

	/**
	 * Handle Import Request.
	 */
	public static function handle_import(): void {
		check_admin_referer( 'preorder_raka_import_nonce', 'preorder_raka_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'preorder-raka' ) );
		}

		$type            = sanitize_key( $_REQUEST['type'] ?? 'preorder' );
		$update_existing = ! empty( $_REQUEST['update_existing'] );

		if ( empty( $_FILES['import_file']['tmp_name'] ) || ! is_uploaded_file( $_FILES['import_file']['tmp_name'] ) ) {
			set_transient( 'preorder_raka_import_result', [
				'type'    => 'error',
				'title'   => 'เกิดข้อผิดพลาด',
				'message' => 'ไม่พบไฟล์ที่อัปโหลด กรุณาเลือกไฟล์ .json หรือ .csv ที่ถูกต้อง',
			], 60 );
			wp_safe_redirect( admin_url( 'edit.php?post_type=preorder&page=preorder-raka-export-import&tab=' . $type ) );
			exit;
		}

		$file_path = $_FILES['import_file']['tmp_name'];
		$file_name = sanitize_file_name( $_FILES['import_file']['name'] );
		$file_ext  = strtolower( pathinfo( $file_name, PATHINFO_EXTENSION ) );

		if ( ! in_array( $file_ext, [ 'json', 'csv' ], true ) ) {
			set_transient( 'preorder_raka_import_result', [
				'type'    => 'error',
				'title'   => 'รูปแบบไฟล์ไม่รองรับ',
				'message' => 'รองรับเฉพาะไฟล์นามสกุล .json และ .csv เท่านั้น',
			], 60 );
			wp_safe_redirect( admin_url( 'edit.php?post_type=preorder&page=preorder-raka-export-import&tab=' . $type ) );
			exit;
		}

		if ( 'preorder_product' === $type ) {
			$result = self::import_products( $file_path, $file_ext, $update_existing );
		} else {
			$result = self::import_preorders( $file_path, $file_ext, $update_existing );
		}

		set_transient( 'preorder_raka_import_result', $result, 60 );
		wp_safe_redirect( admin_url( 'edit.php?post_type=preorder&page=preorder-raka-export-import&tab=' . $type ) );
		exit;
	}

	/**
	 * Import Preorders.
	 */
	private static function import_preorders( string $file_path, string $ext, bool $update_existing ): array {
		$records = [];

		if ( 'json' === $ext ) {
			$content = file_get_contents( $file_path );
			$json    = json_decode( (string) $content, true );
			if ( ! is_array( $json ) ) {
				return [ 'type' => 'error', 'title' => 'JSON Error', 'message' => 'ไฟล์ JSON ไม่ถูกต้องตามรูปแบบ' ];
			}
			$records = isset( $json['data'] ) && is_array( $json['data'] ) ? $json['data'] : $json;
		} else {
			$handle = fopen( $file_path, 'r' );
			if ( ! $handle ) {
				return [ 'type' => 'error', 'title' => 'CSV Error', 'message' => 'ไม่สามารถอ่านไฟล์ CSV ได้' ];
			}

			// Check BOM
			$bom = fread( $handle, 3 );
			if ( $bom !== chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) ) {
				rewind( $handle );
			}

			$headers = fgetcsv( $handle );
			if ( ! $headers ) {
				fclose( $handle );
				return [ 'type' => 'error', 'title' => 'CSV Error', 'message' => 'ไฟล์ CSV ไม่มีหัวคอลัมน์' ];
			}

			$clean_headers = array_map( function ( $h ) {
				return trim( strtolower( (string) $h ) );
			}, $headers );

			while ( ( $row = fgetcsv( $handle ) ) !== false ) {
				if ( count( $row ) === count( $clean_headers ) ) {
					$records[] = array_combine( $clean_headers, $row );
				}
			}
			fclose( $handle );
		}

		$created = 0;
		$updated = 0;
		$skipped = 0;
		$details = [];

		foreach ( $records as $rec ) {
			$order_no = sanitize_text_field( (string) ( $rec['preorder_no'] ?? '' ) );
			if ( empty( $order_no ) ) {
				$skipped++;
				continue;
			}

			$title       = sanitize_text_field( (string) ( $rec['title'] ?? ( 'พรีออเดอร์ ' . $order_no ) ) );
			$phone       = sanitize_text_field( (string) ( $rec['phone'] ?? '' ) );
			$citizen_id  = sanitize_text_field( (string) ( $rec['citizen_id_last4'] ?? '' ) );
			$product_name = sanitize_text_field( (string) ( $rec['product_name'] ?? '' ) );
			$price       = (float) ( $rec['price'] ?? 0 );
			$qty         = max( 1, (int) ( $rec['qty'] ?? 1 ) );
			$shipping    = (float) ( $rec['shipping'] ?? 0 );
			$tracking    = sanitize_text_field( (string) ( $rec['tracking'] ?? '' ) );
			$address     = sanitize_textarea_field( (string) ( $rec['address'] ?? '' ) );
			$step_idx    = max( 0, (int) ( $rec['current_step_idx'] ?? 0 ) );
			$status      = in_array( $rec['status'] ?? 'publish', [ 'publish', 'draft', 'pending' ], true ) ? $rec['status'] : 'publish';

			// Parse steps
			$steps = [];
			if ( isset( $rec['steps'] ) && is_array( $rec['steps'] ) ) {
				$steps = $rec['steps'];
			} elseif ( isset( $rec['steps_json'] ) ) {
				$decoded_steps = json_decode( (string) $rec['steps_json'], true );
				if ( is_array( $decoded_steps ) ) {
					$steps = $decoded_steps;
				}
			}

			// Check if preorder exists
			$existing_posts = get_posts( [
				'post_type'   => 'preorder',
				'meta_key'    => '_preorder_no',
				'meta_value'  => $order_no,
				'post_status' => 'any',
				'numberposts' => 1,
			] );

			if ( ! empty( $existing_posts ) ) {
				if ( $update_existing ) {
					$post_id = $existing_posts[0]->ID;
					wp_update_post( [
						'ID'          => $post_id,
						'post_title'  => $title,
						'post_status' => $status,
					] );

					update_post_meta( $post_id, '_preorder_phone', $phone );
					update_post_meta( $post_id, '_preorder_citizen_id_last4', $citizen_id );
					update_post_meta( $post_id, '_preorder_product', $product_name );
					update_post_meta( $post_id, '_preorder_price', $price );
					update_post_meta( $post_id, '_preorder_qty', $qty );
					update_post_meta( $post_id, '_preorder_shipping', $shipping );
					update_post_meta( $post_id, '_preorder_tracking', $tracking );
					update_post_meta( $post_id, '_preorder_address', $address );
					update_post_meta( $post_id, '_preorder_current_step_idx', $step_idx );
					if ( ! empty( $steps ) ) {
						update_post_meta( $post_id, '_preorder_steps', $steps );
					}

					$updated++;
					$details[] = sprintf( 'อัปเดต #%s: %s', $order_no, $title );
				} else {
					$skipped++;
				}
			} else {
				$post_id = wp_insert_post( [
					'post_title'  => $title,
					'post_type'   => 'preorder',
					'post_status' => $status,
				] );

				if ( $post_id && ! is_wp_error( $post_id ) ) {
					update_post_meta( $post_id, '_preorder_no', $order_no );
					update_post_meta( $post_id, '_preorder_phone', $phone );
					update_post_meta( $post_id, '_preorder_citizen_id_last4', $citizen_id );
					update_post_meta( $post_id, '_preorder_product', $product_name );
					update_post_meta( $post_id, '_preorder_price', $price );
					update_post_meta( $post_id, '_preorder_qty', $qty );
					update_post_meta( $post_id, '_preorder_shipping', $shipping );
					update_post_meta( $post_id, '_preorder_tracking', $tracking );
					update_post_meta( $post_id, '_preorder_address', $address );
					update_post_meta( $post_id, '_preorder_current_step_idx', $step_idx );
					if ( ! empty( $steps ) ) {
						update_post_meta( $post_id, '_preorder_steps', $steps );
					}

					$created++;
					$details[] = sprintf( 'เพิ่มใหม่ #%s: %s', $order_no, $title );
				} else {
					$skipped++;
				}
			}
		}

		return [
			'type'    => 'success',
			'title'   => 'นำเข้าข้อมูลพรีออเดอร์สำเร็จ',
			'message' => sprintf( 'ประมวลผลทั้งหมด %d รายการ (สร้างใหม่: %d, อัปเดต: %d, ข้าม: %d)', count( $records ), $created, $updated, $skipped ),
			'details' => array_slice( $details, 0, 15 ),
		];
	}

	/**
	 * Import Products.
	 */
	private static function import_products( string $file_path, string $ext, bool $update_existing ): array {
		$records = [];

		if ( 'json' === $ext ) {
			$content = file_get_contents( $file_path );
			$json    = json_decode( (string) $content, true );
			if ( ! is_array( $json ) ) {
				return [ 'type' => 'error', 'title' => 'JSON Error', 'message' => 'ไฟล์ JSON ไม่ถูกต้อง' ];
			}
			$records = isset( $json['data'] ) && is_array( $json['data'] ) ? $json['data'] : $json;
		} else {
			$handle = fopen( $file_path, 'r' );
			if ( ! $handle ) {
				return [ 'type' => 'error', 'title' => 'CSV Error', 'message' => 'ไม่สามารถอ่านไฟล์ CSV ได้' ];
			}
			$bom = fread( $handle, 3 );
			if ( $bom !== chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) ) {
				rewind( $handle );
			}
			$headers = fgetcsv( $handle );
			$clean_headers = array_map( function ( $h ) {
				return trim( strtolower( (string) $h ) );
			}, (array) $headers );

			while ( ( $row = fgetcsv( $handle ) ) !== false ) {
				if ( count( $row ) === count( $clean_headers ) ) {
					$records[] = array_combine( $clean_headers, $row );
				}
			}
			fclose( $handle );
		}

		$created = 0;
		$updated = 0;
		$skipped = 0;
		$details = [];

		foreach ( $records as $rec ) {
			$title = sanitize_text_field( (string) ( $rec['title'] ?? '' ) );
			if ( empty( $title ) ) {
				$skipped++;
				continue;
			}

			$price = (float) ( $rec['price'] ?? 0 );
			$desc  = sanitize_textarea_field( (string) ( $rec['description'] ?? '' ) );

			// Check if product exists by title
			$existing = get_page_by_title( $title, OBJECT, 'preorder_product' );

			if ( $existing && $update_existing ) {
				wp_update_post( [
					'ID'           => $existing->ID,
					'post_content' => $desc,
				] );
				update_post_meta( $existing->ID, '_preorder_price', $price );
				$updated++;
				$details[] = sprintf( 'อัปเดตสินค้า: %s (฿%s)', $title, number_format( $price ) );
			} elseif ( ! $existing ) {
				$pid = wp_insert_post( [
					'post_title'   => $title,
					'post_type'    => 'preorder_product',
					'post_status'  => 'publish',
					'post_content' => $desc,
				] );
				if ( $pid && ! is_wp_error( $pid ) ) {
					update_post_meta( $pid, '_preorder_price', $price );
					$created++;
					$details[] = sprintf( 'เพิ่มสินค้าใหม่: %s (฿%s)', $title, number_format( $price ) );
				}
			} else {
				$skipped++;
			}
		}

		return [
			'type'    => 'success',
			'title'   => 'นำเข้าสินค้าพรีออเดอร์สำเร็จ',
			'message' => sprintf( 'ประมวลผลทั้งหมด %d รายการ (สร้างใหม่: %d, อัปเดต: %d, ข้าม: %d)', count( $records ), $created, $updated, $skipped ),
			'details' => array_slice( $details, 0, 15 ),
		];
	}

	/**
	 * Download Sample Templates.
	 */
	public static function handle_sample_template(): void {
		check_admin_referer( 'preorder_sample' );

		$type   = sanitize_key( $_GET['type'] ?? 'preorder' );
		$format = sanitize_key( $_GET['format'] ?? 'json' );

		if ( 'preorder_product' === $type ) {
			if ( 'csv' === $format ) {
				header( 'Content-Type: text/csv; charset=UTF-8' );
				header( 'Content-Disposition: attachment; filename="sample_preorder_products.csv"' );
				$out = fopen( 'php://output', 'w' );
				fprintf( $out, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) );
				fputcsv( $out, [ 'title', 'price', 'image_url', 'description', 'status' ] );
				fputcsv( $out, [ 'Wooting 60HE+ Custom Keyboard (Pre-order)', '8900', 'https://example.com/keyboard.jpg', 'สวิตช์ Lekker Hall Effect ปรับแต่งได้ตามต้องการ', 'publish' ] );
				fputcsv( $out, [ 'Finalmouse UltralightX Lion (Pre-order)', '7900', 'https://example.com/mouse.jpg', 'เมาส์เกมมิ่งคาร์บอนไฟเบอร์ไร้สายน้ำหนัก 31 กรัม', 'publish' ] );
				fclose( $out );
			} else {
				header( 'Content-Type: application/json; charset=UTF-8' );
				header( 'Content-Disposition: attachment; filename="sample_preorder_products.json"' );
				echo wp_json_encode( [
					'type' => 'preorder_product',
					'data' => [
						[
							'title'       => 'Wooting 60HE+ Custom Keyboard (Pre-order)',
							'price'       => 8900,
							'image_url'   => 'https://example.com/keyboard.jpg',
							'description' => 'สวิตช์ Lekker Hall Effect ปรับแต่งได้ตามต้องการ',
							'status'      => 'publish',
						],
					],
				], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
			}
		} else {
			if ( 'csv' === $format ) {
				header( 'Content-Type: text/csv; charset=UTF-8' );
				header( 'Content-Disposition: attachment; filename="sample_preorders.csv"' );
				$out = fopen( 'php://output', 'w' );
				fprintf( $out, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) );
				fputcsv( $out, [
					'preorder_no',
					'title',
					'phone',
					'citizen_id_last4',
					'product_name',
					'price',
					'qty',
					'shipping',
					'tracking',
					'address',
					'current_step_idx',
					'steps_json',
					'status',
				] );
				$sample_steps = [
					[ 'title' => 'รับคำสั่งซื้อเรียบร้อย', 'desc' => 'ระบบได้รับการพรีออเดอร์ของท่านแล้ว', 'date' => '05 ต.ค. 2569 09:00' ],
					[ 'title' => 'สั่งซื้อสินค้าสำเร็จ', 'desc' => 'สั่งซื้อสินค้าจากผู้ผลิตต่างประเทศเรียบร้อยแล้ว', 'date' => '05 ต.ค. 2569 14:00' ],
					[ 'title' => 'ถึงคลังสินค้าต่างประเทศ', 'desc' => 'สินค้าถึงคลังแล้ว', 'date' => '' ],
					[ 'title' => 'ระหว่างขนส่งมาไทย', 'desc' => 'สินค้ากำลังเดินทางมาไทย', 'date' => '' ],
					[ 'title' => 'ผ่านพิธีการศุลกากร', 'desc' => 'ผ่านการตรวจและชำระภาษีนำเข้าแล้ว', 'date' => '' ],
					[ 'title' => 'ถึงศูนย์กระจายสินค้า', 'desc' => 'ถึงศูนย์กระจายสินค้าในไทย', 'date' => '' ],
					[ 'title' => 'อยู่ระหว่างนำจ่าย', 'desc' => 'ขนส่งกำลังนำส่งสินค้า', 'date' => '' ],
					[ 'title' => 'จัดส่งสำเร็จ', 'desc' => 'ส่งมอบสินค้าเรียบร้อยแล้ว', 'date' => '' ],
				];
				fputcsv( $out, [
					'RAKA-PRE-9999',
					'พรีออเดอร์ RAKA-PRE-9999 ของ คุณสมศักดิ์ มั่งมี',
					'0812345678',
					'5678',
					'Wooting 60HE+ Custom Keyboard (Pre-order)',
					'8900',
					'1',
					'100',
					'TH9988776655',
					'99/9 อาคารเอ็กแซท ชั้น 12 ถนนวิภาวดีรังสิต กรุงเทพฯ 10900',
					'1',
					wp_json_encode( $sample_steps, JSON_UNESCAPED_UNICODE ),
					'publish',
				] );
				fclose( $out );
			} else {
				header( 'Content-Type: application/json; charset=UTF-8' );
				header( 'Content-Disposition: attachment; filename="sample_preorders.json"' );
				echo wp_json_encode( [
					'type' => 'preorder',
					'data' => [
						[
							'preorder_no'      => 'RAKA-PRE-9999',
							'title'            => 'พรีออเดอร์ RAKA-PRE-9999 ของ คุณสมศักดิ์ มั่งมี',
							'phone'            => '0812345678',
							'citizen_id_last4' => '5678',
							'product_name'     => 'Wooting 60HE+ Custom Keyboard (Pre-order)',
							'price'            => 8900,
							'qty'              => 1,
							'shipping'         => 100,
							'tracking'         => 'TH9988776655',
							'address'          => '99/9 อาคารเอ็กแซท ชั้น 12 ถนนวิภาวดีรังสิต กรุงเทพฯ 10900',
							'current_step_idx' => 1,
							'status'           => 'publish',
							'steps'            => [
								[ 'title' => 'รับคำสั่งซื้อเรียบร้อย', 'desc' => 'ระบบได้รับการพรีออเดอร์ของท่านแล้ว', 'date' => '05 ต.ค. 2569 09:00' ],
								[ 'title' => 'สั่งซื้อสินค้าสำเร็จ', 'desc' => 'สั่งซื้อสินค้าจากผู้ผลิตต่างประเทศเรียบร้อยแล้ว', 'date' => '05 ต.ค. 2569 14:00' ],
								[ 'title' => 'ถึงคลังสินค้าต่างประเทศ', 'desc' => 'สินค้าถึงคลังแล้ว', 'date' => '' ],
								[ 'title' => 'ระหว่างขนส่งมาไทย', 'desc' => 'สินค้ากำลังเดินทางมาไทย', 'date' => '' ],
								[ 'title' => 'ผ่านพิธีการศุลกากร', 'desc' => 'ผ่านการตรวจและชำระภาษีนำเข้าแล้ว', 'date' => '' ],
								[ 'title' => 'ถึงศูนย์กระจายสินค้า', 'desc' => 'ถึงศูนย์กระจายสินค้าในไทย', 'date' => '' ],
								[ 'title' => 'อยู่ระหว่างนำจ่าย', 'desc' => 'ขนส่งกำลังนำส่งสินค้า', 'date' => '' ],
								[ 'title' => 'จัดส่งสำเร็จ', 'desc' => 'ส่งมอบสินค้าเรียบร้อยแล้ว', 'date' => '' ],
							],
						],
					],
				], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
			}
		}
		exit;
	}
}
