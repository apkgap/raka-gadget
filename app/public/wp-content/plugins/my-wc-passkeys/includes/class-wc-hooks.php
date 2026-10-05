<?php
declare(strict_types=1);

namespace MyWcPasskeys;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles WooCommerce UI and Native Hooks integration.
 */
class WcHooks {

	/**
	 * Initialize all WooCommerce hooks and endpoints.
	 */
	public static function init(): void {
		// Custom Rewrite Endpoint for My Account
		add_action( 'init', [ __CLASS__, 'add_endpoints' ] );

		// WooCommerce Navigation Menu Filter
		add_filter( 'woocommerce_account_menu_items', [ __CLASS__, 'add_account_menu_item' ], 30 );

		// WooCommerce Endpoint Content
		add_action( 'woocommerce_account_passkeys_endpoint', [ __CLASS__, 'render_passkeys_endpoint' ] );

		// WooCommerce Login Form Button
		add_action( 'woocommerce_login_form_end', [ __CLASS__, 'render_login_passkey_button' ] );

		// Asset Enqueue
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue_assets' ] );
	}

	/**
	 * Register 'passkeys' rewrite endpoint for My Account.
	 */
	public static function add_endpoints(): void {
		add_rewrite_endpoint( 'passkeys', EP_ROOT | EP_PAGES );
	}

	/**
	 * Add 'Passkeys' tab into WooCommerce My Account menu list.
	 *
	 * @param array $items Existing account menu items.
	 * @return array
	 */
	public static function add_account_menu_item( array $items ): array {
		$new_items = [];
		foreach ( $items as $key => $title ) {
			if ( 'customer-logout' === $key ) {
				$new_items['passkeys'] = __( 'ความปลอดภัย / Passkeys', 'my-wc-passkeys' );
			}
			$new_items[ $key ] = $title;
		}

		if ( ! isset( $new_items['passkeys'] ) ) {
			$new_items['passkeys'] = __( 'ความปลอดภัย / Passkeys', 'my-wc-passkeys' );
		}

		return $new_items;
	}

	/**
	 * Enqueue Passkey scripts and styles on login and account pages.
	 */
	public static function enqueue_assets(): void {
		global $post;
		$has_auth_shortcode = is_a( $post, 'WP_Post' ) && (
			has_shortcode( $post->post_content, 'wc_custom_login' ) ||
			has_shortcode( $post->post_content, 'wc_custom_register' ) ||
			has_shortcode( $post->post_content, 'auth_login' ) ||
			has_shortcode( $post->post_content, 'auth_register' )
		);

		// Load only on login / my-account or pages with auth shortcodes
		if ( ! is_account_page() && ! is_checkout() && ! is_page( [ 'login', 'register' ] ) && ! $has_auth_shortcode ) {
			return;
		}

		$plugin_url = plugin_dir_url( dirname( __FILE__ ) );
		$plugin_dir = plugin_dir_path( dirname( __FILE__ ) );

		$js_ver  = file_exists( $plugin_dir . 'assets/js/passkeys.js' ) ? (string) filemtime( $plugin_dir . 'assets/js/passkeys.js' ) : MY_WC_PASSKEYS_VERSION;
		$css_ver = file_exists( $plugin_dir . 'assets/css/passkeys.css' ) ? (string) filemtime( $plugin_dir . 'assets/css/passkeys.css' ) : MY_WC_PASSKEYS_VERSION;

		wp_enqueue_style(
			'my-wc-passkeys-css',
			$plugin_url . 'assets/css/passkeys.css',
			[],
			$css_ver
		);

		wp_enqueue_script(
			'my-wc-passkeys-js',
			$plugin_url . 'assets/js/passkeys.js',
			[],
			$js_ver,
			true
		);

		wp_localize_script(
			'my-wc-passkeys-js',
			'myWcPasskeysData',
			[
				'restUrl'     => esc_url_raw( rest_url( 'wc-passkeys/v1' ) ),
				'nonce'       => wp_create_nonce( 'wp_rest' ),
				'isLoggedIn'  => is_user_logged_in(),
				'i18n'        => [
					'notSupported'   => __( 'เบราว์เซอร์หรืออุปกรณ์นี้ยังไม่รองรับ Passkeys (WebAuthn)', 'my-wc-passkeys' ),
					'regSuccess'     => __( 'ลงทะเบียนอุปกรณ์ Passkey สำเร็จเรียบร้อย!', 'my-wc-passkeys' ),
					'loginSuccess'   => __( 'ยืนยันตัวตนสำเร็จ กำลังเข้าสู่ระบบ...', 'my-wc-passkeys' ),
					'confirmDelete'  => __( 'คุณแน่ใจหรือไม่ว่าต้องการลบอุปกรณ์ Passkey นี้?', 'my-wc-passkeys' ),
					'devicePrompt'   => __( 'กรุณากรอกชื่ออุปกรณ์ เช่น iPhone 15 หรือ MacBook Pro', 'my-wc-passkeys' ),
					'authenticating' => __( 'กำลังรอสแกนลายนิ้วมือ / Face ID...', 'my-wc-passkeys' ),
					'registering'    => __( 'กำลังลงทะเบียนอุปกรณ์...', 'my-wc-passkeys' ),
					'errorOccurred'  => __( 'เกิดข้อผิดพลาดในการทำรายการ กรุณาลองใหม่อีกครั้ง', 'my-wc-passkeys' ),
				],
			]
		);
	}

	/**
	 * Render Passkey Quick Login button in WooCommerce login form.
	 */
	public static function render_login_passkey_button(): void {
		global $post;
		$has_custom_auth = is_a( $post, 'WP_Post' ) && (
			has_shortcode( $post->post_content, 'wc_custom_login' ) ||
			has_shortcode( $post->post_content, 'wc_custom_register' ) ||
			has_shortcode( $post->post_content, 'auth_login' ) ||
			has_shortcode( $post->post_content, 'auth_register' )
		);

		if ( $has_custom_auth || is_page( [ 'login', 'register' ] ) ) {
			return;
		}
		?>
		<div id="my-wc-passkeys-login-wrap" class="my-wc-passkeys-login-box">
			<div id="my-wc-passkeys-divider" class="my-wc-passkeys-divider">
				<span id="my-wc-passkeys-divider-text" class="my-wc-passkeys-divider-text">
					<?php esc_html_e( 'หรือเข้าสู่ระบบแบบปลอดภัย', 'my-wc-passkeys' ); ?>
				</span>
			</div>

			<button
				type="button"
				id="my-wc-passkey-login-btn"
				class="button my-wc-passkey-btn"
			>
				<svg id="my-wc-passkey-icon" class="my-wc-passkey-svg" viewBox="0 0 24 24" width="20" height="20" fill="currentColor">
					<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>
				</svg>
				<span id="my-wc-passkey-btn-text" class="my-wc-passkey-btn-label">
					<?php esc_html_e( 'เข้าสู่ระบบด้วยสแกนนิ้ว / Face ID', 'my-wc-passkeys' ); ?>
				</span>
			</button>

			<p id="my-wc-passkey-login-notice" class="my-wc-passkeys-microcopy">
				<svg id="my-wc-shield-ico" class="my-wc-shield-svg" viewBox="0 0 24 24" width="14" height="14" fill="currentColor">
					<path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 16l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z"/>
				</svg>
				<?php esc_html_e( 'สะดวก ปลอดภัย ข้อมูลสแกนนิ้วจะไม่ถูกส่งมายังเว็บไซต์', 'my-wc-passkeys' ); ?>
			</p>

			<div id="my-wc-passkey-login-status" class="my-wc-passkey-status-msg" style="display: none;"></div>
		</div>
		<?php
	}

	/**
	 * Render Passkeys Management Dashboard in My Account endpoint.
	 */
	public static function render_passkeys_endpoint(): void {
		$user_id  = get_current_user_id();
		$passkeys = DB::get_user_passkeys( $user_id );
		?>
		<div id="my-wc-passkeys-manager" class="my-wc-passkeys-account-wrap">
			<div id="my-wc-passkeys-header" class="my-wc-passkeys-header">
				<h2 id="my-wc-passkeys-title" class="my-wc-passkeys-heading">
					<?php esc_html_e( 'จัดการ Passkeys & สแกนใบหน้า/ลายนิ้วมือ', 'my-wc-passkeys' ); ?>
				</h2>
				<p id="my-wc-passkeys-desc" class="my-wc-passkeys-subheading">
					<?php esc_html_e( 'Passkeys ให้คุณเข้าสู่ระบบได้อย่างปลอดภัยและรวดเร็ว โดยใช้ Touch ID, Face ID, Windows Hello หรือ PIN ของอุปกรณ์ โดยไม่ต้องจำรหัสผ่าน', 'my-wc-passkeys' ); ?>
				</p>
			</div>

			<!-- Add Device Card -->
			<div id="my-wc-passkey-add-box" class="my-wc-passkey-card my-wc-passkey-add-card">
				<h3 id="my-wc-add-card-title" class="my-wc-card-title">
					<?php esc_html_e( 'เพิ่มอุปกรณ์ใหม่ (Register New Passkey)', 'my-wc-passkeys' ); ?>
				</h3>
				<form id="my-wc-passkey-register-form" class="my-wc-passkey-inline-form" onsubmit="return false;">
					<div id="my-wc-input-wrap" class="my-wc-form-group">
						<label id="my-wc-dev-label" for="my-wc-device-name-input" class="screen-reader-text">
							<?php esc_html_e( 'ชื่ออุปกรณ์', 'my-wc-passkeys' ); ?>
						</label>
						<input
							type="text"
							id="my-wc-device-name-input"
							class="input-text my-wc-input"
							placeholder="<?php esc_attr_e( 'เช่น iPhone ของฉัน, MacBook ทำงาน, คอมพิวเตอร์บ้าน', 'my-wc-passkeys' ); ?>"
							maxlength="100"
						/>
					</div>
					<button
						type="button"
						id="my-wc-passkey-reg-btn"
						class="button my-wc-btn-primary"
					>
						<svg id="my-wc-finger-icon" class="my-wc-btn-ico" viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
							<path d="M17.81 4.47c-.08 0-.16-.02-.23-.06C15.66 3.42 14 3 12.01 3c-1.98 0-3.86.47-5.57 1.41-.24.13-.54.04-.68-.2-.13-.24-.04-.55.2-.68C7.82 2.52 9.86 2 12.01 2c2.13 0 3.99.47 5.93 1.51.24.13.33.43.2.67-.09.18-.21.29-.33.29zm3.03 3.03c-.09 0-.18-.02-.27-.08-1.2-.73-2.67-1.22-4.22-1.43-.27-.04-.46-.29-.42-.56.04-.27.29-.46.56-.42 1.73.23 3.38.78 4.73 1.6.23.14.3.45.16.68-.11.14-.34.21-.54.21zm-15.6 0c-.17 0-.34-.09-.44-.24-.13-.23-.05-.53.18-.66 1.48-.86 3.19-1.39 5.09-1.57.27-.02.51.17.54.44.03.27-.17.51-.44.54-1.72.16-3.26.64-4.61 1.42-.1.05-.21.07-.32.07zm14.4 3.7c-.06 0-.13-.01-.19-.04-2.14-.94-4.65-1.42-7.44-1.42-2.8 0-5.32.48-7.46 1.42-.25.11-.54-.01-.65-.26-.11-.25.01-.54.26-.65C7.07 9.17 9.82 8.65 12 8.65c2.18 0 4.93.52 7.27 1.55.25.11.37.4.26.65-.08.17-.25.26-.41.26zM12 22c-1.48 0-2.85-.38-4.05-1.05-.24-.13-.32-.44-.19-.68.13-.24.44-.32.68-.19 1.05.58 2.24.91 3.56.91 3.73 0 6.84-2.55 7.67-6.07.06-.27.33-.43.6-.37.27.06.43.33.37.6-.96 4.09-4.57 7.05-8.91 7.05zm-3.56-4.51c-.13 0-.26-.05-.35-.15-1.28-1.3-1.99-3.03-1.99-4.84 0-3.9 3.13-7 7.03-7 3.9 0 7.03 3.1 7.03 7 0 .28-.22.5-.5.5s-.5-.22-.5-.5c0-3.35-2.7-6-6.03-6-3.33 0-6.03 2.65-6.03 6 0 1.55.61 3.03 1.7 4.15.2.2.19.51-.01.71-.11.09-.23.13-.35.13zm8.39-1.38c-.24 0-.46-.17-.49-.42-.28-2.07-1.35-3.79-3.09-4.99-.23-.16-.28-.47-.13-.7.16-.23.47-.28.7-.13 1.98 1.36 3.2 3.32 3.52 5.67.04.27-.15.53-.42.57h-.09z"/>
						</svg>
						<span id="my-wc-reg-btn-lbl" class="my-wc-btn-text">
							<?php esc_html_e( 'เพิ่มอุปกรณ์นี้ (Touch ID / Face ID / PIN)', 'my-wc-passkeys' ); ?>
						</span>
					</button>
				</form>
				<div id="my-wc-reg-status" class="my-wc-passkey-status-msg" style="display: none;"></div>
			</div>

			<!-- Registered Devices Table -->
			<div id="my-wc-passkeys-list-wrap" class="my-wc-passkey-card">
				<h3 id="my-wc-list-title" class="my-wc-card-title">
					<?php esc_html_e( 'อุปกรณ์ที่ลงทะเบียนไว้แล้ว', 'my-wc-passkeys' ); ?>
					<span id="my-wc-key-count" class="my-wc-badge"><?php echo esc_html( (string) count( $passkeys ) ); ?></span>
				</h3>

				<?php if ( empty( $passkeys ) ) : ?>
					<div id="my-wc-empty-keys" class="my-wc-empty-notice">
						<p id="my-wc-empty-msg" class="my-wc-empty-text">
							<?php esc_html_e( 'คุณยังไม่มีอุปกรณ์ Passkeys ที่ลงทะเบียนไว้ คลิกปุ่ม "เพิ่มอุปกรณ์นี้" ด้านบนเพื่อเริ่มใช้งาน', 'my-wc-passkeys' ); ?>
						</p>
					</div>
				<?php else : ?>
					<div id="my-wc-table-responsive" class="my-wc-table-responsive">
						<table id="my-wc-passkeys-table" class="woocommerce-orders-table my-wc-table shop_table shop_table_responsive">
							<thead>
								<tr>
									<th id="my-wc-th-device" class="woocommerce-orders-table__header"><?php esc_html_e( 'ชื่ออุปกรณ์', 'my-wc-passkeys' ); ?></th>
									<th id="my-wc-th-created" class="woocommerce-orders-table__header"><?php esc_html_e( 'วันที่ลงทะเบียน', 'my-wc-passkeys' ); ?></th>
									<th id="my-wc-th-used" class="woocommerce-orders-table__header"><?php esc_html_e( 'ใช้งานล่าสุด', 'my-wc-passkeys' ); ?></th>
									<th id="my-wc-th-actions" class="woocommerce-orders-table__header"><?php esc_html_e( 'จัดการ', 'my-wc-passkeys' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( $passkeys as $pk ) : ?>
									<tr id="my-wc-pk-row-<?php echo esc_attr( (string) $pk->id ); ?>" class="woocommerce-orders-table__row">
										<td id="my-wc-pk-dev-<?php echo esc_attr( (string) $pk->id ); ?>" class="woocommerce-orders-table__cell" data-title="<?php esc_attr_e( 'ชื่ออุปกรณ์', 'my-wc-passkeys' ); ?>">
											<div id="my-wc-dev-name-wrap-<?php echo esc_attr( (string) $pk->id ); ?>" class="my-wc-device-label-wrap">
												<span id="my-wc-dev-icon-<?php echo esc_attr( (string) $pk->id ); ?>" class="my-wc-device-icon">🔑</span>
												<strong id="my-wc-dev-txt-<?php echo esc_attr( (string) $pk->id ); ?>" class="my-wc-device-name"><?php echo esc_html( $pk->device_name ); ?></strong>
											</div>
										</td>
										<td id="my-wc-pk-created-<?php echo esc_attr( (string) $pk->id ); ?>" class="woocommerce-orders-table__cell" data-title="<?php esc_attr_e( 'วันที่ลงทะเบียน', 'my-wc-passkeys' ); ?>">
											<time datetime="<?php echo esc_attr( $pk->created_at ); ?>">
												<?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $pk->created_at ) ) ); ?>
											</time>
										</td>
										<td id="my-wc-pk-used-<?php echo esc_attr( (string) $pk->id ); ?>" class="woocommerce-orders-table__cell" data-title="<?php esc_attr_e( 'ใช้งานล่าสุด', 'my-wc-passkeys' ); ?>">
											<?php if ( $pk->last_used_at ) : ?>
												<time datetime="<?php echo esc_attr( $pk->last_used_at ); ?>">
													<?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $pk->last_used_at ) ) ); ?>
												</time>
											<?php else : ?>
												<span id="my-wc-pk-never-<?php echo esc_attr( (string) $pk->id ); ?>" class="my-wc-text-muted"><?php esc_html_e( 'ยังไม่เคยใช้งาน', 'my-wc-passkeys' ); ?></span>
											<?php endif; ?>
										</td>
										<td id="my-wc-pk-act-<?php echo esc_attr( (string) $pk->id ); ?>" class="woocommerce-orders-table__cell" data-title="<?php esc_attr_e( 'จัดการ', 'my-wc-passkeys' ); ?>">
											<button
												type="button"
												id="my-wc-del-btn-<?php echo esc_attr( (string) $pk->id ); ?>"
												class="button my-wc-btn-danger my-wc-delete-passkey-btn"
												data-id="<?php echo esc_attr( (string) $pk->id ); ?>"
											>
												<?php esc_html_e( 'ลบอุปกรณ์', 'my-wc-passkeys' ); ?>
											</button>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				<?php endif; ?>
			</div>

			<!-- Security Explanation Notice -->
			<div id="my-wc-passkeys-info" class="my-wc-passkey-notice-card">
				<h4 id="my-wc-info-title" class="my-wc-notice-title">
					🛡️ <?php esc_html_e( 'Passkeys ทำงานอย่างไรและปลอดภัยแค่ไหน?', 'my-wc-passkeys' ); ?>
				</h4>
				<ul id="my-wc-info-list" class="my-wc-notice-list">
					<li id="my-wc-info-item-1"><?php esc_html_e( 'ข้อมูลสแกนลายนิ้วมือหรือใบหน้าจะถูกเก็บรักษาอย่างปลอดภัยในชิปของอุปกรณ์คุณเท่านั้น และไม่มีวันถูกส่งมายังเซิร์ฟเวอร์', 'my-wc-passkeys' ); ?></li>
					<li id="my-wc-info-item-2"><?php esc_html_e( 'ป้องกันการถูก Phishing หรือดักขโมยรหัสผ่าน 100% เพราะ Passkey ผูกเฉพาะกับโดเมนของร้านนี้เท่านั้น', 'my-wc-passkeys' ); ?></li>
					<li id="my-wc-info-item-3"><?php esc_html_e( 'คุณยังสามารถใช้รหัสผ่านเดิมเข้าสู่ระบบได้ตามปกติเมื่อใช้งานบนอุปกรณ์อื่น', 'my-wc-passkeys' ); ?></li>
				</ul>
			</div>
		</div>
		<?php
	}
}
