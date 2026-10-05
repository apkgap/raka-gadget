<?php
declare(strict_types=1);

namespace MyWcPasskeys;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manages Authentication Settings, Admin UI, and Feature Toggles.
 */
class AuthSettings {

	public const OPTION_KEY = 'raka_auth_settings';

	/**
	 * Default settings values.
	 *
	 * @return array<string, mixed>
	 */
	public static function get_defaults(): array {
		return [
			// Password Auth
			'enable_password_auth'    => 'yes',

			// Google Sign-In & One Tap
			'enable_google_auth'      => 'yes',
			'enable_google_onetap'    => 'yes',
			'google_client_id'        => '',
			'google_client_secret'    => '',

			// LINE Login
			'enable_line_auth'        => 'yes',
			'line_channel_id'         => '',
			'line_channel_secret'     => '',

			// Magic Link & Email OTP
			'enable_magic_link'       => 'yes',
			'magic_link_ttl'          => 15, // minutes
			'email_sender_name'       => get_bloginfo( 'name' ),

			// Passkeys (Biometrics / WebAuthn)
			'enable_passkeys'         => 'yes',

			// Redirection
			'login_redirect_type'     => 'myaccount', // 'myaccount', 'home', 'custom'
			'login_redirect_url'      => '',
		];
	}

	/**
	 * Retrieve all options merged with defaults.
	 *
	 * @return array<string, mixed>
	 */
	public static function get_options(): array {
		$saved = get_option( self::OPTION_KEY, [] );
		if ( ! is_array( $saved ) ) {
			$saved = [];
		}
		return array_merge( self::get_defaults(), $saved );
	}

	/**
	 * Get a specific option value.
	 *
	 * @param string $key Option key.
	 * @param mixed  $default Fallback value.
	 * @return mixed
	 */
	public static function get_option( string $key, $default = null ) {
		$options = self::get_options();
		if ( array_key_exists( $key, $options ) ) {
			return $options[ $key ];
		}
		return $default;
	}

	// ── Helper Getters for Method Toggles ──

	public static function is_password_enabled(): bool {
		return 'yes' === self::get_option( 'enable_password_auth', 'yes' );
	}

	public static function is_google_enabled(): bool {
		return 'yes' === self::get_option( 'enable_google_auth', 'yes' );
	}

	public static function is_google_onetap_enabled(): bool {
		return self::is_google_enabled() && 'yes' === self::get_option( 'enable_google_onetap', 'yes' );
	}

	public static function get_google_client_id(): string {
		return trim( (string) self::get_option( 'google_client_id', '' ) );
	}

	public static function get_google_client_secret(): string {
		return trim( (string) self::get_option( 'google_client_secret', '' ) );
	}

	public static function is_line_enabled(): bool {
		return 'yes' === self::get_option( 'enable_line_auth', 'yes' );
	}

	public static function get_line_channel_id(): string {
		return trim( (string) self::get_option( 'line_channel_id', '' ) );
	}

	public static function get_line_channel_secret(): string {
		return trim( (string) self::get_option( 'line_channel_secret', '' ) );
	}

	public static function is_magic_link_enabled(): bool {
		return 'yes' === self::get_option( 'enable_magic_link', 'yes' );
	}

	public static function get_magic_link_ttl(): int {
		$ttl = (int) self::get_option( 'magic_link_ttl', 15 );
		return $ttl > 0 ? $ttl : 15;
	}

	public static function is_passkey_enabled(): bool {
		return 'yes' === self::get_option( 'enable_passkeys', 'yes' );
	}

	public static function get_default_redirect_url(): string {
		$type = self::get_option( 'login_redirect_type', 'myaccount' );
		if ( 'home' === $type ) {
			return home_url( '/' );
		}
		if ( 'custom' === $type ) {
			$custom = (string) self::get_option( 'login_redirect_url', '' );
			if ( ! empty( $custom ) ) {
				return esc_url_raw( $custom );
			}
		}
		if ( function_exists( 'wc_get_page_permalink' ) ) {
			return wc_get_page_permalink( 'myaccount' );
		}
		return home_url( '/account' );
	}

	/**
	 * Initialize Admin Settings Hooks.
	 */
	public static function init(): void {
		add_action( 'admin_menu', [ __CLASS__, 'add_admin_menu' ] );
		add_action( 'admin_init', [ __CLASS__, 'handle_save_settings' ] );
	}

	/**
	 * Register Admin Menu under WooCommerce / RAKA Settings.
	 */
	public static function add_admin_menu(): void {
		add_menu_page(
			__( 'การตั้งค่าระบบสมาชิก & การล็อกอิน', 'my-wc-passkeys' ),
			__( 'Auth Settings', 'my-wc-passkeys' ),
			'manage_options',
			'raka-auth-settings',
			[ __CLASS__, 'render_settings_page' ],
			'dashicons-shield-alt',
			56
		);
	}

	/**
	 * Handle Admin Settings Save.
	 */
	public static function handle_save_settings(): void {
		if ( ! isset( $_POST['raka_save_auth_settings'] ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'คุณไม่มีสิทธิ์ในการแก้ไขการตั้งค่านี้', 'my-wc-passkeys' ) );
		}

		check_admin_referer( 'raka_auth_settings_action', 'raka_auth_settings_nonce' );

		$options = [
			'enable_password_auth'    => isset( $_POST['enable_password_auth'] ) ? 'yes' : 'no',
			'enable_google_auth'      => isset( $_POST['enable_google_auth'] ) ? 'yes' : 'no',
			'enable_google_onetap'    => isset( $_POST['enable_google_onetap'] ) ? 'yes' : 'no',
			'google_client_id'        => sanitize_text_field( wp_unslash( $_POST['google_client_id'] ?? '' ) ),
			'google_client_secret'    => sanitize_text_field( wp_unslash( $_POST['google_client_secret'] ?? '' ) ),
			'enable_line_auth'        => isset( $_POST['enable_line_auth'] ) ? 'yes' : 'no',
			'line_channel_id'         => sanitize_text_field( wp_unslash( $_POST['line_channel_id'] ?? '' ) ),
			'line_channel_secret'     => sanitize_text_field( wp_unslash( $_POST['line_channel_secret'] ?? '' ) ),
			'enable_magic_link'       => isset( $_POST['enable_magic_link'] ) ? 'yes' : 'no',
			'magic_link_ttl'          => max( 1, (int) ( $_POST['magic_link_ttl'] ?? 15 ) ),
			'email_sender_name'       => sanitize_text_field( wp_unslash( $_POST['email_sender_name'] ?? get_bloginfo( 'name' ) ) ),
			'enable_passkeys'         => isset( $_POST['enable_passkeys'] ) ? 'yes' : 'no',
			'login_redirect_type'     => sanitize_key( wp_unslash( $_POST['login_redirect_type'] ?? 'myaccount' ) ),
			'login_redirect_url'      => esc_url_raw( wp_unslash( $_POST['login_redirect_url'] ?? '' ) ),
		];

		update_option( self::OPTION_KEY, $options );

		add_settings_error(
			'raka_auth_settings',
			'settings_updated',
			__( 'บันทึกการตั้งค่าระบบล็อกอินและลงทะเบียนเรียบร้อยแล้ว', 'my-wc-passkeys' ),
			'updated'
		);
	}

	/**
	 * Render Admin Settings Page HTML.
	 */
	public static function render_settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$options   = self::get_options();
		$line_cb   = home_url( '/?raka_social_auth=line' );
		?>
		<div id="raka-auth-settings-page" class="wrap" style="max-width: 1000px; margin-top: 20px;">
			<h1 id="raka-auth-settings-title" style="font-size: 24px; font-weight: 700; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
				<span class="dashicons dashicons-shield" style="font-size: 28px; width: 28px; height: 28px; color: #dc2626;"></span>
				<?php esc_html_e( 'การตั้งค่าระบบสมาชิก & การยืนยันตัวตน (Authentication Settings)', 'my-wc-passkeys' ); ?>
			</h1>

			<?php settings_errors( 'raka_auth_settings' ); ?>

			<form id="raka-auth-settings-form" method="post" action="">
				<?php wp_nonce_field( 'raka_auth_settings_action', 'raka_auth_settings_nonce' ); ?>

				<style>
					.raka-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
					.raka-card-title { font-size: 17px; font-weight: 600; margin-top: 0; margin-bottom: 8px; color: #0f172a; display: flex; align-items: center; gap: 8px; }
					.raka-card-desc { font-size: 13px; color: #64748b; margin-bottom: 20px; }
					.raka-toggle-row { display: flex; align-items: center; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f1f5f9; }
					.raka-toggle-row:last-child { border-bottom: none; }
					.raka-toggle-label { font-weight: 500; color: #1e293b; font-size: 14px; }
					.raka-toggle-sub { font-size: 12px; color: #64748b; display: block; font-weight: normal; margin-top: 2px; }
					.raka-input-row { margin-top: 14px; }
					.raka-input-row label { display: block; font-size: 13px; font-weight: 500; color: #334155; margin-bottom: 6px; }
					.raka-input-row input[type="text"], .raka-input-row input[type="password"], .raka-input-row input[type="number"], .raka-input-row select { width: 100%; max-width: 500px; padding: 8px 12px; border-radius: 8px; border: 1px solid #cbd5e1; }
					.raka-badge { display: inline-block; padding: 2px 8px; border-radius: 9999px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
					.raka-badge-green { background: #dcfce7; color: #166534; }
					.raka-badge-blue { background: #e0f2fe; color: #0369a1; }
					.raka-badge-purple { background: #f3e8ff; color: #6b21a8; }
				</style>

				<!-- 1. General & Password Settings -->
				<div id="raka-card-password" class="raka-card">
					<h2 id="raka-card-pwd-title" class="raka-card-title">
						<span class="dashicons dashicons-lock" style="color: #0284c7;"></span>
						<?php esc_html_e( '1. รหัสผ่านแบบดั้งเดิม (Username / Password Auth)', 'my-wc-passkeys' ); ?>
					</h2>
					<p id="raka-card-pwd-desc" class="raka-card-desc"><?php esc_html_e( 'เปิด/ปิด ฟอร์มกรอกอีเมลและรหัสผ่านแบบดั้งเดิม', 'my-wc-passkeys' ); ?></p>
					
					<div id="raka-pwd-toggle-row" class="raka-toggle-row">
						<div>
							<span id="raka-pwd-label" class="raka-toggle-label"><?php esc_html_e( 'เปิดใช้งานรหัสผ่าน (Password Login & Register)', 'my-wc-passkeys' ); ?></span>
							<span id="raka-pwd-sub" class="raka-toggle-sub"><?php esc_html_e( 'หากปิด ผู้ใช้จะเข้าสู่ระบบผ่าน Google, LINE, Magic Link หรือ Passkeys เท่านั้น', 'my-wc-passkeys' ); ?></span>
						</div>
						<label id="raka-pwd-switch-wrap" class="switch">
							<input type="checkbox" id="enable_password_auth" name="enable_password_auth" value="yes" <?php checked( $options['enable_password_auth'], 'yes' ); ?>>
							<span class="slider round"></span>
						</label>
					</div>
				</div>

				<!-- 2. Google Sign-In & One Tap -->
				<div id="raka-card-google" class="raka-card">
					<h2 id="raka-card-google-title" class="raka-card-title">
						<span class="dashicons dashicons-google" style="color: #ea4335;"></span>
						<?php esc_html_e( '2. Google Sign-In & Google One Tap', 'my-wc-passkeys' ); ?>
						<span id="raka-badge-gsi" class="raka-badge raka-badge-blue"><?php esc_html_e( 'Google Identity Services (GSI)', 'my-wc-passkeys' ); ?></span>
					</h2>
					<p id="raka-card-google-desc" class="raka-card-desc"><?php esc_html_e( 'เข้าสู่ระบบด้วยบัญชี Google ทันที และแสดงกล่อง One Tap เมื่อเปิดหน้าเว็บ', 'my-wc-passkeys' ); ?></p>

					<div id="raka-google-toggle-row" class="raka-toggle-row">
						<div>
							<span id="raka-google-label" class="raka-toggle-label"><?php esc_html_e( 'เปิดใช้งาน Google Sign-In Button', 'my-wc-passkeys' ); ?></span>
							<span id="raka-google-sub" class="raka-toggle-sub"><?php esc_html_e( 'แสดงปุ่ม "ดำเนินการต่อด้วย Google" ในหน้าเข้าสู่ระบบและสมัครสมาชิก', 'my-wc-passkeys' ); ?></span>
						</div>
						<label id="raka-google-switch-wrap" class="switch">
							<input type="checkbox" id="enable_google_auth" name="enable_google_auth" value="yes" <?php checked( $options['enable_google_auth'], 'yes' ); ?>>
							<span class="slider round"></span>
						</label>
					</div>

					<div id="raka-onetap-toggle-row" class="raka-toggle-row">
						<div>
							<span id="raka-onetap-label" class="raka-toggle-label"><?php esc_html_e( 'เปิดใช้งาน Google One Tap Prompt', 'my-wc-passkeys' ); ?></span>
							<span id="raka-onetap-sub" class="raka-toggle-sub"><?php esc_html_e( 'แสดงป๊อปอัพ One Tap ที่มุมขวาบนให้อนุมัติล็อกอินได้ด้วยคลิกเดียว', 'my-wc-passkeys' ); ?></span>
						</div>
						<label id="raka-onetap-switch-wrap" class="switch">
							<input type="checkbox" id="enable_google_onetap" name="enable_google_onetap" value="yes" <?php checked( $options['enable_google_onetap'], 'yes' ); ?>>
							<span class="slider round"></span>
						</label>
					</div>

					<div id="raka-google-clientid-row" class="raka-input-row">
						<label id="raka-lbl-g-clientid" for="google_client_id"><?php esc_html_e( 'Google Client ID (*.apps.googleusercontent.com)', 'my-wc-passkeys' ); ?></label>
						<input type="text" id="google_client_id" name="google_client_id" value="<?php echo esc_attr( $options['google_client_id'] ); ?>" placeholder="เช่น 1234567890-abcdefg.apps.googleusercontent.com">
						<p id="raka-hint-g-clientid" class="description" style="font-size: 12px; color: #64748b; margin-top: 4px;">
							<?php esc_html_e( 'รับ Client ID ได้ที่ Google Cloud Console > APIs & Services > Credentials (OAuth 2.0 Client IDs for Web Application)', 'my-wc-passkeys' ); ?>
						</p>
					</div>
				</div>

				<!-- 3. LINE Login -->
				<div id="raka-card-line" class="raka-card">
					<h2 id="raka-card-line-title" class="raka-card-title">
						<span class="dashicons dashicons-format-chat" style="color: #06c755;"></span>
						<?php esc_html_e( '3. LINE Login (OAuth 2.0)', 'my-wc-passkeys' ); ?>
						<span id="raka-badge-line" class="raka-badge raka-badge-green"><?php esc_html_e( 'LINE Developer Console', 'my-wc-passkeys' ); ?></span>
					</h2>
					<p id="raka-card-line-desc" class="raka-card-desc"><?php esc_html_e( 'เข้าสู่ระบบด้วยบัญชี LINE ลูกค้าบนมือถือกดอนุญาตครั้งเดียวจบ ไม่ต้องพิมพ์รหัสผ่าน', 'my-wc-passkeys' ); ?></p>

					<div id="raka-line-toggle-row" class="raka-toggle-row">
						<div>
							<span id="raka-line-label" class="raka-toggle-label"><?php esc_html_e( 'เปิดใช้งาน LINE Login', 'my-wc-passkeys' ); ?></span>
							<span id="raka-line-sub" class="raka-toggle-sub"><?php esc_html_e( 'แสดงปุ่ม "ดำเนินการต่อด้วย LINE" ในหน้าเข้าสู่ระบบและสมัครสมาชิก', 'my-wc-passkeys' ); ?></span>
						</div>
						<label id="raka-line-switch-wrap" class="switch">
							<input type="checkbox" id="enable_line_auth" name="enable_line_auth" value="yes" <?php checked( $options['enable_line_auth'], 'yes' ); ?>>
							<span class="slider round"></span>
						</label>
					</div>

					<div id="raka-line-cid-row" class="raka-input-row">
						<label id="raka-lbl-line-cid" for="line_channel_id"><?php esc_html_e( 'LINE Channel ID', 'my-wc-passkeys' ); ?></label>
						<input type="text" id="line_channel_id" name="line_channel_id" value="<?php echo esc_attr( $options['line_channel_id'] ); ?>" placeholder="เช่น 2001234567">
					</div>

					<div id="raka-line-secret-row" class="raka-input-row">
						<label id="raka-lbl-line-secret" for="line_channel_secret"><?php esc_html_e( 'LINE Channel Secret', 'my-wc-passkeys' ); ?></label>
						<input type="password" id="line_channel_secret" name="line_channel_secret" value="<?php echo esc_attr( $options['line_channel_secret'] ); ?>" placeholder="••••••••••••••••••••••••••••••••">
					</div>

					<div id="raka-line-cb-row" class="raka-input-row" style="background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px dashed #cbd5e1;">
						<label id="raka-lbl-line-cb" style="font-weight: 600; color: #0f172a; margin-bottom: 4px;"><?php esc_html_e( 'Callback URL (นำไปใส่ที่ LINE Developers Console > LINE Login > Callback URL):', 'my-wc-passkeys' ); ?></label>
						<code id="raka-code-line-cb" style="background: #fff; padding: 6px 10px; border: 1px solid #e2e8f0; border-radius: 4px; font-size: 13px; display: inline-block; user-select: all;"><?php echo esc_html( $line_cb ); ?></code>
					</div>
				</div>

				<!-- 4. Magic Link & Email OTP -->
				<div id="raka-card-magic" class="raka-card">
					<h2 id="raka-card-magic-title" class="raka-card-title">
						<span class="dashicons dashicons-email-alt2" style="color: #9333ea;"></span>
						<?php esc_html_e( '4. Magic Link & Email OTP (ไม่ต้องจำรหัสผ่าน)', 'my-wc-passkeys' ); ?>
						<span id="raka-badge-magic" class="raka-badge raka-badge-purple"><?php esc_html_e( 'Passwordless Auth', 'my-wc-passkeys' ); ?></span>
					</h2>
					<p id="raka-card-magic-desc" class="raka-card-desc"><?php esc_html_e( 'ผู้ใช้กรอกแค่อีเมล -> ระบบส่งลิงก์คลิกเดียวเข้าสู่ระบบ + รหัส OTP 6 หลักเข้าอีเมล', 'my-wc-passkeys' ); ?></p>

					<div id="raka-magic-toggle-row" class="raka-toggle-row">
						<div>
							<span id="raka-magic-label" class="raka-toggle-label"><?php esc_html_e( 'เปิดใช้งาน Magic Link & OTP ทางอีเมล', 'my-wc-passkeys' ); ?></span>
							<span id="raka-magic-sub" class="raka-toggle-sub"><?php esc_html_e( 'อนุญาตให้ผู้ใช้เข้าสู่ระบบ/สมัครสมาชิกผ่านอีเมลโดยไม่ต้องกรอกรหัสผ่าน', 'my-wc-passkeys' ); ?></span>
						</div>
						<label id="raka-magic-switch-wrap" class="switch">
							<input type="checkbox" id="enable_magic_link" name="enable_magic_link" value="yes" <?php checked( $options['enable_magic_link'], 'yes' ); ?>>
							<span class="slider round"></span>
						</label>
					</div>

					<div id="raka-magic-ttl-row" class="raka-input-row">
						<label id="raka-lbl-magic-ttl" for="magic_link_ttl"><?php esc_html_e( 'อายุของ Token & รหัส OTP (นาที)', 'my-wc-passkeys' ); ?></label>
						<input type="number" id="magic_link_ttl" name="magic_link_ttl" value="<?php echo esc_attr( (string) $options['magic_link_ttl'] ); ?>" min="5" max="60" style="max-width: 120px;">
						<span id="raka-magic-ttl-unit" style="font-size: 13px; color: #64748b; margin-left: 6px;"><?php esc_html_e( 'นาที (แนะนำ: 15 นาที)', 'my-wc-passkeys' ); ?></span>
					</div>

					<div id="raka-magic-sender-row" class="raka-input-row">
						<label id="raka-lbl-magic-sender" for="email_sender_name"><?php esc_html_e( 'ชื่อผู้ส่งอีเมล (Sender Name)', 'my-wc-passkeys' ); ?></label>
						<input type="text" id="email_sender_name" name="email_sender_name" value="<?php echo esc_attr( $options['email_sender_name'] ); ?>">
					</div>
				</div>

				<!-- 5. Passkeys (WebAuthn / Biometrics) -->
				<div id="raka-card-passkeys" class="raka-card">
					<h2 id="raka-card-passkeys-title" class="raka-card-title">
						<span class="dashicons dashicons-admin-network" style="color: #059669;"></span>
						<?php esc_html_e( '5. Passkeys / WebAuthn (One-Pass Biometrics)', 'my-wc-passkeys' ); ?>
					</h2>
					<p id="raka-card-passkeys-desc" class="raka-card-desc"><?php esc_html_e( 'สแกนใบหน้า Face ID / สแกนลายนิ้วมือ Touch ID / Windows Hello', 'my-wc-passkeys' ); ?></p>

					<div id="raka-pk-toggle-row" class="raka-toggle-row">
						<div>
							<span id="raka-pk-label" class="raka-toggle-label"><?php esc_html_e( 'เปิดใช้งาน One-Pass (Passkeys)', 'my-wc-passkeys' ); ?></span>
							<span id="raka-pk-sub" class="raka-toggle-sub"><?php esc_html_e( 'แสดงปุ่ม "ดำเนินการต่อด้วย One-Pass" และระบบจัดการ Passkeys ใน My Account', 'my-wc-passkeys' ); ?></span>
						</div>
						<label id="raka-pk-switch-wrap" class="switch">
							<input type="checkbox" id="enable_passkeys" name="enable_passkeys" value="yes" <?php checked( $options['enable_passkeys'], 'yes' ); ?>>
							<span class="slider round"></span>
						</label>
					</div>
				</div>

				<!-- 6. Redirect Settings -->
				<div id="raka-card-redirect" class="raka-card">
					<h2 id="raka-card-redirect-title" class="raka-card-title">
						<span class="dashicons dashicons-admin-links" style="color: #475569;"></span>
						<?php esc_html_e( '6. ปลายทางหลังเข้าสู่ระบบสำเร็จ (Login Redirection)', 'my-wc-passkeys' ); ?>
					</h2>

					<div id="raka-redirect-type-row" class="raka-input-row">
						<label id="raka-lbl-redirect-type" for="login_redirect_type"><?php esc_html_e( 'ปลายทางเริ่มต้น:', 'my-wc-passkeys' ); ?></label>
						<select id="login_redirect_type" name="login_redirect_type">
							<option value="myaccount" <?php selected( $options['login_redirect_type'], 'myaccount' ); ?>><?php esc_html_e( 'หน้าบัญชีของฉัน (My Account)', 'my-wc-passkeys' ); ?></option>
							<option value="home" <?php selected( $options['login_redirect_type'], 'home' ); ?>><?php esc_html_e( 'หน้าแรกของเว็บไซต์ (Home Page)', 'my-wc-passkeys' ); ?></option>
							<option value="custom" <?php selected( $options['login_redirect_type'], 'custom' ); ?>><?php esc_html_e( 'กำหนด URL เอง (Custom URL)', 'my-wc-passkeys' ); ?></option>
						</select>
					</div>

					<div id="raka-redirect-url-row" class="raka-input-row">
						<label id="raka-lbl-redirect-url" for="login_redirect_url"><?php esc_html_e( 'Custom Redirect URL (ถ้าเลือกกำหนด URL เอง):', 'my-wc-passkeys' ); ?></label>
						<input type="text" id="login_redirect_url" name="login_redirect_url" value="<?php echo esc_attr( $options['login_redirect_url'] ); ?>" placeholder="https://example.com/pre-order">
					</div>
				</div>

				<!-- Save Button -->
				<div id="raka-save-wrap" style="margin-top: 20px;">
					<button type="submit" id="raka_save_auth_settings" name="raka_save_auth_settings" value="1" class="button button-primary button-hero" style="font-weight: 600; padding: 6px 24px;">
						<?php esc_html_e( 'บันทึกการตั้งค่าทั้งหมด', 'my-wc-passkeys' ); ?>
					</button>
				</div>

			</form>
		</div>
		<?php
	}
}
