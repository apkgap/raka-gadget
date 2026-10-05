<?php
declare(strict_types=1);

namespace MyWcPasskeys;

use WP_Error;
use WP_User;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles Modern Social Logins (Google GSI, LINE OAuth) and Passwordless Magic Link / OTP Authentication.
 */
class SocialAuth {

	/**
	 * Initialize Social & Magic Link Authentication Hooks.
	 */
	public static function init(): void {
		// Handle LINE Login OAuth Callback & Magic Link Token Click
		add_action( 'init', [ __CLASS__, 'handle_line_callback' ] );
		add_action( 'init', [ __CLASS__, 'handle_magic_link_token_login' ] );

		// Register custom auth filters
		add_filter( 'my_wc_google_auth_url', [ __CLASS__, 'filter_google_auth_url' ] );
		add_filter( 'my_wc_line_auth_url', [ __CLASS__, 'get_line_auth_url' ] );
	}

	/**
	 * Filter Google Auth URL or return empty if GSI client-side modal is used.
	 */
	public static function filter_google_auth_url( string $url ): string {
		if ( ! empty( $url ) ) {
			return $url;
		}
		// When GSI is active, standard button triggers client JS modal/popup
		return '#google-gsi';
	}

	/**
	 * Generate LINE OAuth 2.0 Authorize URL.
	 *
	 * @param string $redirect_to Intended redirection after login.
	 * @return string
	 */
	public static function get_line_auth_url( string $redirect_to = '' ): string {
		if ( ! AuthSettings::is_line_enabled() ) {
			return '';
		}

		$channel_id = AuthSettings::get_line_channel_id();
		if ( empty( $channel_id ) ) {
			return '';
		}

		if ( empty( $redirect_to ) ) {
			$redirect_to = isset( $_GET['redirect_to'] ) ? esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ) : AuthSettings::get_default_redirect_url();
		}

		$state_data = [
			'nonce'       => wp_create_nonce( 'raka_line_oauth_state' ),
			'redirect_to' => $redirect_to,
		];
		$state = base64_encode( (string) wp_json_encode( $state_data ) );

		$callback_url = home_url( '/?raka_social_auth=line' );

		$query_params = [
			'response_type' => 'code',
			'client_id'     => $channel_id,
			'redirect_uri'  => $callback_url,
			'state'         => $state,
			'scope'         => 'profile openid email',
			'prompt'        => 'consent',
		];

		return 'https://access.line.me/oauth2/v2.1/authorize?' . http_build_query( $query_params );
	}

	/**
	 * Handle LINE OAuth Callback (/?raka_social_auth=line).
	 */
	public static function handle_line_callback(): void {
		if ( ! isset( $_GET['raka_social_auth'] ) || 'line' !== $_GET['raka_social_auth'] ) {
			return;
		}

		if ( ! AuthSettings::is_line_enabled() ) {
			wp_die( esc_html__( 'LINE Login ปิดการใช้งานอยู่', 'my-wc-passkeys' ) );
		}

		// Check for errors returned by LINE
		if ( isset( $_GET['error'] ) ) {
			$error_desc = isset( $_GET['error_description'] ) ? sanitize_text_field( wp_unslash( $_GET['error_description'] ) ) : 'LINE login cancelled';
			if ( function_exists( 'wc_add_notice' ) ) {
				wc_add_notice( sprintf( __( 'เข้าสู่ระบบด้วย LINE ไม่สำเร็จ: %s', 'my-wc-passkeys' ), $error_desc ), 'error' );
			}
			wp_safe_redirect( home_url( '/login/' ) );
			exit;
		}

		$code  = isset( $_GET['code'] ) ? sanitize_text_field( wp_unslash( $_GET['code'] ) ) : '';
		$state = isset( $_GET['state'] ) ? sanitize_text_field( wp_unslash( $_GET['state'] ) ) : '';

		if ( empty( $code ) || empty( $state ) ) {
			wp_safe_redirect( home_url( '/login/' ) );
			exit;
		}

		// Decode State
		$state_decoded = json_decode( (string) base64_decode( $state ), true );
		$redirect_to   = ! empty( $state_decoded['redirect_to'] ) ? esc_url_raw( $state_decoded['redirect_to'] ) : AuthSettings::get_default_redirect_url();

		$channel_id     = AuthSettings::get_line_channel_id();
		$channel_secret = AuthSettings::get_line_channel_secret();
		$callback_url   = home_url( '/?raka_social_auth=line' );

		// 1. Exchange authorization code for access_token & id_token
		$token_response = wp_remote_post( 'https://api.line.me/oauth2/v2.1/token', [
			'timeout' => 20,
			'headers' => [
				'Content-Type' => 'application/x-www-form-urlencoded',
			],
			'body'    => [
				'grant_type'    => 'authorization_code',
				'code'          => $code,
				'redirect_uri'  => $callback_url,
				'client_id'     => $channel_id,
				'client_secret' => $channel_secret,
			],
		] );

		if ( is_wp_error( $token_response ) ) {
			if ( function_exists( 'wc_add_notice' ) ) {
				wc_add_notice( __( 'ไม่สามารถเชื่อมต่อกับ LINE Server ได้ กรุณาลองใหม่อีกครั้ง', 'my-wc-passkeys' ), 'error' );
			}
			wp_safe_redirect( home_url( '/login/' ) );
			exit;
		}

		$token_body = json_decode( wp_remote_retrieve_body( $token_response ), true );
		if ( empty( $token_body['access_token'] ) ) {
			$err_msg = $token_body['error_description'] ?? __( 'Token exchange failed', 'my-wc-passkeys' );
			if ( function_exists( 'wc_add_notice' ) ) {
				wc_add_notice( sprintf( __( 'LINE Login ผิดพลาด: %s', 'my-wc-passkeys' ), $err_msg ), 'error' );
			}
			wp_safe_redirect( home_url( '/login/' ) );
			exit;
		}

		$access_token = $token_body['access_token'];
		$id_token     = $token_body['id_token'] ?? '';

		// 2. Fetch User Profile
		$profile_response = wp_remote_get( 'https://api.line.me/v2/profile', [
			'timeout' => 15,
			'headers' => [
				'Authorization' => 'Bearer ' . $access_token,
			],
		] );

		if ( is_wp_error( $profile_response ) ) {
			if ( function_exists( 'wc_add_notice' ) ) {
				wc_add_notice( __( 'ไม่สามารถดึงข้อมูลโปรไฟล์ LINE ได้', 'my-wc-passkeys' ), 'error' );
			}
			wp_safe_redirect( home_url( '/login/' ) );
			exit;
		}

		$profile = json_decode( wp_remote_retrieve_body( $profile_response ), true );
		if ( empty( $profile['userId'] ) ) {
			wp_safe_redirect( home_url( '/login/' ) );
			exit;
		}

		$line_user_id = $profile['userId'];
		$display_name = $profile['displayName'] ?? 'LINE User';
		$picture_url  = $profile['pictureUrl'] ?? '';
		$email        = '';

		// Try extracting email from id_token if available
		if ( ! empty( $id_token ) ) {
			$jwt_parts = explode( '.', $id_token );
			if ( isset( $jwt_parts[1] ) ) {
				$payload = json_decode( (string) self::base64url_decode( $jwt_parts[1] ), true );
				if ( ! empty( $payload['email'] ) ) {
					$email = sanitize_email( $payload['email'] );
				}
			}
		}

		// 3. Login or Create User
		$user = self::login_or_create_user( [
			'provider'     => 'line',
			'provider_id'  => $line_user_id,
			'email'        => $email,
			'display_name' => $display_name,
			'avatar_url'   => $picture_url,
		] );

		if ( is_wp_error( $user ) ) {
			if ( function_exists( 'wc_add_notice' ) ) {
				wc_add_notice( $user->get_error_message(), 'error' );
			}
			wp_safe_redirect( home_url( '/login/' ) );
			exit;
		}

		wp_safe_redirect( $redirect_to );
		exit;
	}

	/**
	 * Verify Google Credential (JWT) sent via Google Identity Services (GSI).
	 *
	 * @param string $id_token
	 * @return array<string, mixed>|WP_Error
	 */
	public static function verify_google_jwt( string $id_token ) {
		if ( empty( $id_token ) ) {
			return new WP_Error( 'invalid_token', __( 'Google Token ว่างเปล่า', 'my-wc-passkeys' ) );
		}

		// 1. Verify token with Google Tokeninfo endpoint
		$verify_url = 'https://oauth2.googleapis.com/tokeninfo?id_token=' . rawurlencode( $id_token );
		$response   = wp_remote_get( $verify_url, [ 'timeout' => 15 ] );

		if ( is_wp_error( $response ) ) {
			// Fallback: Decode payload directly if network call fails
			$jwt_parts = explode( '.', $id_token );
			if ( 3 === count( $jwt_parts ) ) {
				$payload = json_decode( (string) self::base64url_decode( $jwt_parts[1] ), true );
				if ( ! empty( $payload['sub'] ) && ! empty( $payload['email'] ) ) {
					$client_id = AuthSettings::get_google_client_id();
					if ( ! empty( $client_id ) && ( $payload['aud'] ?? '' ) !== $client_id ) {
						return new WP_Error( 'invalid_aud', __( 'Google Client ID ไม่ถูกต้อง', 'my-wc-passkeys' ) );
					}
					return $payload;
				}
			}
			return new WP_Error( 'google_verify_failed', __( 'ไม่สามารถยืนยันตัวตนกับ Google ได้', 'my-wc-passkeys' ) );
		}

		$payload = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( empty( $payload['sub'] ) || empty( $payload['email'] ) ) {
			return new WP_Error( 'invalid_payload', __( 'ข้อมูลจาก Google ไม่ถูกต้องหรือหมดอายุ', 'my-wc-passkeys' ) );
		}

		$client_id = AuthSettings::get_google_client_id();
		if ( ! empty( $client_id ) && ( $payload['aud'] ?? '' ) !== $client_id ) {
			return new WP_Error( 'invalid_aud', __( 'Google Client ID ไม่ตรงกับการตั้งค่าระบบ', 'my-wc-passkeys' ) );
		}

		return $payload;
	}

	/**
	 * Send Magic Link & 6-digit OTP Code to Email.
	 *
	 * @param string $email User email.
	 * @param string $redirect_to Redirection URL.
	 * @return true|WP_Error
	 */
	public static function send_magic_link_otp( string $email, string $redirect_to = '' ) {
		if ( ! AuthSettings::is_magic_link_enabled() ) {
			return new WP_Error( 'magic_link_disabled', __( 'ระบบ Magic Link & OTP ปิดการใช้งานอยู่', 'my-wc-passkeys' ) );
		}

		$email = sanitize_email( $email );
		if ( empty( $email ) || ! is_email( $email ) ) {
			return new WP_Error( 'invalid_email', __( 'กรุณากรอกอีเมลที่ถูกต้อง', 'my-wc-passkeys' ) );
		}

		if ( empty( $redirect_to ) ) {
			$redirect_to = AuthSettings::get_default_redirect_url();
		}

		$ttl_minutes = AuthSettings::get_magic_link_ttl();
		$ttl_seconds = $ttl_minutes * MINUTE_IN_SECONDS;

		// Generate 32-char crypto token & 6-digit OTP code
		$magic_token = bin2hex( random_bytes( 16 ) );
		$otp_code    = sprintf( '%06d', wp_rand( 100000, 999999 ) );

		// Store in Transients
		set_transient( 'raka_magic_' . md5( $magic_token ), [
			'email'       => $email,
			'redirect_to' => $redirect_to,
		], $ttl_seconds );

		set_transient( 'raka_otp_' . md5( $email ), [
			'otp'         => $otp_code,
			'redirect_to' => $redirect_to,
			'attempts'    => 0,
		], $ttl_seconds );

		// Generate 1-Click Login URL
		$magic_link_url = add_query_arg( [
			'raka_magic_token' => $magic_token,
			'redirect_to'      => rawurlencode( $redirect_to ),
		], home_url( '/' ) );

		// Send Email
		$site_name   = AuthSettings::get_option( 'email_sender_name', get_bloginfo( 'name' ) );
		$subject     = sprintf( '[%s] รหัสยืนยันตัวตน OTP และลิงก์เข้าสู่ระบบของคุณ', $site_name );
		$html_body   = self::build_magic_email_html( $otp_code, $magic_link_url, $ttl_minutes, $site_name );

		$headers = [
			'Content-Type: text/html; charset=UTF-8',
			'From: ' . $site_name . ' <' . get_option( 'admin_email' ) . '>',
		];

		$sent = wp_mail( $email, $subject, $html_body, $headers );

		if ( ! $sent ) {
			return new WP_Error( 'mail_send_failed', __( 'ไม่สามารถส่งอีเมลได้ กรุณาลองใหม่อีกครั้ง', 'my-wc-passkeys' ) );
		}

		return true;
	}

	/**
	 * Verify 6-digit OTP code submitted by user.
	 *
	 * @param string $email
	 * @param string $otp_code
	 * @return WP_User|WP_Error
	 */
	public static function verify_otp_code( string $email, string $otp_code ) {
		$email    = sanitize_email( $email );
		$otp_code = trim( sanitize_text_field( $otp_code ) );

		if ( empty( $email ) || empty( $otp_code ) ) {
			return new WP_Error( 'empty_fields', __( 'กรุณากรอกอีเมลและรหัส OTP ให้ครบถ้วน', 'my-wc-passkeys' ) );
		}

		$transient_key = 'raka_otp_' . md5( $email );
		$data          = get_transient( $transient_key );

		if ( empty( $data ) || ! is_array( $data ) ) {
			return new WP_Error( 'otp_expired', __( 'รหัส OTP หมดอายุหรือไม่มีอยู่ในระบบ กรุณากดขอรหัสใหม่อีกครั้ง', 'my-wc-passkeys' ) );
		}

		// Rate limiting / brute-force protection
		if ( ( $data['attempts'] ?? 0 ) >= 5 ) {
			delete_transient( $transient_key );
			return new WP_Error( 'otp_locked', __( 'คุณกรอกรหัสผิดเกินจำนวนที่กำหนด กรุณากดขอรหัส OTP ใหม่', 'my-wc-passkeys' ) );
		}

		if ( (string) $data['otp'] !== $otp_code ) {
			$data['attempts'] = ( $data['attempts'] ?? 0 ) + 1;
			set_transient( $transient_key, $data, 15 * MINUTE_IN_SECONDS );
			return new WP_Error( 'otp_invalid', __( 'รหัส OTP ไม่ถูกต้อง กรุณาตรวจสอบอีกครั้ง', 'my-wc-passkeys' ) );
		}

		// Success! Clean up transient
		delete_transient( $transient_key );

		// Login or create user
		return self::login_or_create_user( [
			'provider'     => 'magic_otp',
			'email'        => $email,
			'display_name' => explode( '@', $email )[0],
		] );
	}

	/**
	 * Handle 1-Click Magic Link Token Login (/?raka_magic_token=...).
	 */
	public static function handle_magic_link_token_login(): void {
		if ( ! isset( $_GET['raka_magic_token'] ) ) {
			return;
		}

		$token = sanitize_text_field( wp_unslash( $_GET['raka_magic_token'] ) );
		if ( empty( $token ) ) {
			return;
		}

		$transient_key = 'raka_magic_' . md5( $token );
		$data          = get_transient( $transient_key );

		if ( empty( $data ) || ! is_array( $data ) || empty( $data['email'] ) ) {
			if ( function_exists( 'wc_add_notice' ) ) {
				wc_add_notice( __( 'ลิงก์ Magic Link นี้หมดอายุหรือถูกใช้งานไปแล้ว กรุณาขอลิงก์ใหม่อีกครั้ง', 'my-wc-passkeys' ), 'error' );
			}
			wp_safe_redirect( home_url( '/login/' ) );
			exit;
		}

		// Delete token after 1 use
		delete_transient( $transient_key );

		$email       = $data['email'];
		$redirect_to = ! empty( $data['redirect_to'] ) ? esc_url_raw( $data['redirect_to'] ) : AuthSettings::get_default_redirect_url();

		$user = self::login_or_create_user( [
			'provider'     => 'magic_link',
			'email'        => $email,
			'display_name' => explode( '@', $email )[0],
		] );

		if ( is_wp_error( $user ) ) {
			if ( function_exists( 'wc_add_notice' ) ) {
				wc_add_notice( $user->get_error_message(), 'error' );
			}
			wp_safe_redirect( home_url( '/login/' ) );
			exit;
		}

		wp_safe_redirect( $redirect_to );
		exit;
	}

	/**
	 * Safe User Provisioning and Login Helper.
	 *
	 * @param array<string, mixed> $data
	 * @return WP_User|WP_Error
	 */
	public static function login_or_create_user( array $data ) {
		$email        = ! empty( $data['email'] ) ? sanitize_email( $data['email'] ) : '';
		$provider     = sanitize_key( $data['provider'] ?? 'social' );
		$provider_id  = sanitize_text_field( $data['provider_id'] ?? '' );
		$display_name = sanitize_text_field( $data['display_name'] ?? '' );
		$avatar_url   = esc_url_raw( $data['avatar_url'] ?? '' );

		$user = null;

		// 1. Search by Provider User ID Meta if available
		if ( ! empty( $provider_id ) ) {
			$meta_key = '_' . $provider . '_user_id';
			$users    = get_users( [
				'meta_key'   => $meta_key,
				'meta_value' => $provider_id,
				'number'     => 1,
			] );
			if ( ! empty( $users ) ) {
				$user = $users[0];
			}
		}

		// 2. Search by Email if not found
		if ( ! $user && ! empty( $email ) ) {
			$user = get_user_by( 'email', $email );
		}

		// 3. Create New User if still not found
		if ( ! $user ) {
			$username = '';
			if ( ! empty( $email ) ) {
				$username = sanitize_user( explode( '@', $email )[0], true );
			}
			if ( empty( $username ) && ! empty( $provider_id ) ) {
				$username = $provider . '_' . substr( md5( $provider_id ), 0, 8 );
			}
			if ( empty( $username ) ) {
				$username = 'user_' . wp_rand( 10000, 99999 );
			}

			// Ensure unique username
			$base_username = $username;
			$suffix        = 1;
			while ( username_exists( $username ) ) {
				$username = $base_username . $suffix;
				$suffix++;
			}

			$random_password = wp_generate_password( 24, true, true );

			$user_args = [
				'user_login'   => $username,
				'user_pass'    => $random_password,
				'user_email'   => ! empty( $email ) ? $email : $username . '@' . ( $_SERVER['HTTP_HOST'] ?? 'raka-gadget.com' ),
				'display_name' => ! empty( $display_name ) ? $display_name : $username,
				'role'         => 'customer',
			];

			$user_id = wp_insert_user( $user_args );

			if ( is_wp_error( $user_id ) ) {
				return $user_id;
			}

			$user = get_user_by( 'id', $user_id );
		}

		if ( ! $user instanceof WP_User ) {
			return new WP_Error( 'user_create_failed', __( 'ไม่สามารถสร้างบัญชีผู้ใช้ได้', 'my-wc-passkeys' ) );
		}

		// 4. Save Provider Meta
		if ( ! empty( $provider_id ) ) {
			update_user_meta( $user->ID, '_' . $provider . '_user_id', $provider_id );
		}
		if ( ! empty( $avatar_url ) ) {
			update_user_meta( $user->ID, '_' . $provider . '_avatar_url', $avatar_url );
		}

		// 5. Authenticate and set session cookies
		wp_clear_auth_cookie();
		wp_set_current_user( $user->ID, $user->user_login );
		wp_set_auth_cookie( $user->ID, true, is_ssl() );

		if ( function_exists( 'wc_set_customer_auth_cookie' ) ) {
			wc_set_customer_auth_cookie( $user->ID );
		}

		do_action( 'wp_login', $user->user_login, $user );

		return $user;
	}

	/**
	 * Build Premium HTML Email Template for Magic Link & OTP.
	 */
	private static function build_magic_email_html( string $otp, string $magic_url, int $ttl, string $site_name ): string {
		$bg_color    = '#09090b';
		$card_bg     = '#18181b';
		$accent_red  = '#ef4444';
		$text_white  = '#f4f4f5';
		$text_muted  = '#a1a1aa';

		ob_start();
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<meta charset="UTF-8">
			<title><?php echo esc_html( $site_name ); ?></title>
		</head>
		<body style="margin: 0; padding: 0; background-color: #f4f4f5; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
			<table border="0" cellpadding="0" cellspacing="0" width="100%" style="table-layout: fixed; padding: 40px 10px;">
				<tr>
					<td align="center">
						<table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 520px; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border: 1px solid #e4e4e7;">
							<!-- Header -->
							<tr>
								<td style="padding: 32px 32px 20px 32px; text-align: center; background-color: <?php echo esc_attr( $bg_color ); ?>;">
									<h1 style="color: <?php echo esc_attr( $text_white ); ?>; font-size: 22px; font-weight: 800; margin: 0; letter-spacing: 0.5px;">
										<?php echo esc_html( $site_name ); ?>
									</h1>
									<p style="color: <?php echo esc_attr( $text_muted ); ?>; font-size: 13px; margin: 6px 0 0 0;">
										ยืนยันตัวตนเข้าสู่ระบบแบบไม่ต้องใช้รหัสผ่าน
									</p>
								</td>
							</tr>
							<!-- Body -->
							<tr>
								<td style="padding: 32px;">
									<p style="font-size: 15px; color: #27272a; line-height: 1.6; margin: 0 0 20px 0;">
										สวัสดีครับ/ค่ะ,<br>
										คุณได้ร้องขอการเข้าสู่ระบบผ่านอีเมล กรุณาใช้รหัส OTP ด้านล่างนี้ หรือคลิกที่ปุ่มเพื่อเข้าสู่ระบบทันที:
									</p>

									<!-- OTP Code Box -->
									<div style="background-color: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 12px; padding: 18px; text-align: center; margin-bottom: 24px;">
										<span style="display: block; font-size: 12px; color: #64748b; text-transform: uppercase; font-weight: 600; letter-spacing: 1px; margin-bottom: 6px;">รหัส OTP ยืนยันตัวตน (6 หลัก)</span>
										<span style="font-size: 32px; font-weight: 800; letter-spacing: 6px; color: <?php echo esc_attr( $accent_red ); ?>; font-family: monospace;"><?php echo esc_html( $otp ); ?></span>
										<span style="display: block; font-size: 11px; color: #94a3b8; margin-top: 6px;">(รหัสหมดอายุภายใน <?php echo esc_html( (string) $ttl ); ?> นาที)</span>
									</div>

									<!-- 1-Click Magic Button -->
									<div style="text-align: center; margin-bottom: 28px;">
										<a href="<?php echo esc_url( $magic_url ); ?>" style="display: inline-block; background-color: #09090b; color: #ffffff; text-decoration: none; font-size: 15px; font-weight: 700; padding: 14px 32px; border-radius: 9999px; box-shadow: 0 2px 10px rgba(0,0,0,0.15);">
											คลิกที่นี่เพื่อเข้าสู่ระบบด้วย 1-Click Link
										</a>
									</div>

									<p style="font-size: 12px; color: #71717a; line-height: 1.5; margin: 0; border-top: 1px solid #f4f4f5; padding-top: 16px;">
										หากคุณไม่ได้เป็นผู้ส่งคำขอนี้ สามารถเพิกเฉยต่ออีเมลฉบับนี้ได้อย่างปลอดภัย บัญชีของคุณยังคงปลอดภัยตามปกติ
									</p>
								</td>
							</tr>
							<!-- Footer -->
							<tr>
								<td style="padding: 16px 32px; background-color: #fafafa; border-top: 1px solid #f4f4f5; text-align: center;">
									<p style="font-size: 11px; color: #a1a1aa; margin: 0;">
										&copy; <?php echo esc_html( (string) date( 'Y' ) ); ?> <?php echo esc_html( $site_name ); ?>. All rights reserved.
									</p>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</body>
		</html>
		<?php
		return ob_get_clean();
	}

	/**
	 * Helper Base64Url decode.
	 */
	private static function base64url_decode( string $data ): string {
		return base64_decode( str_pad( strtr( $data, '-_', '+/' ), strlen( $data ) % 4, '=', STR_PAD_RIGHT ) );
	}
}
