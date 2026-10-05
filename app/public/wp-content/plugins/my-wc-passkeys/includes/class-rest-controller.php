<?php
declare(strict_types=1);

namespace MyWcPasskeys;

use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Controller for Passkeys endpoints.
 * Namespace: wc-passkeys/v1
 */
class RestController extends WP_REST_Controller {

	/**
	 * Route namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'wc-passkeys/v1';

	/**
	 * Register all REST API routes for Passkeys.
	 */
	public function register_routes(): void {
		// 1. POST /register/options
		register_rest_route(
			$this->namespace,
			'/register/options',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'register_options' ],
				'permission_callback' => [ $this, 'check_logged_in_permission' ],
			]
		);

		// 2. POST /register/verify
		register_rest_route(
			$this->namespace,
			'/register/verify',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'register_verify' ],
				'permission_callback' => [ $this, 'check_logged_in_permission' ],
				'args'                => [
					'response' => [
						'required'          => true,
						'validate_callback' => function( $param ) {
							return is_array( $param );
						},
					],
					'device_name' => [
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
						'default'           => 'อุปกรณ์ของฉัน',
					],
				],
			]
		);

		// 3. POST /login/options
		register_rest_route(
			$this->namespace,
			'/login/options',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'login_options' ],
				'permission_callback' => '__return_true',
			]
		);

		// 4. POST /login/verify
		register_rest_route(
			$this->namespace,
			'/login/verify',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'login_verify' ],
				'permission_callback' => '__return_true',
				'args'                => [
					'response' => [
						'required'          => true,
						'validate_callback' => function( $param ) {
							return is_array( $param );
						},
					],
					'session_hash' => [
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					],
				],
			]
		);

		// 5. DELETE /credentials/(?P<id>[\d]+)
		register_rest_route(
			$this->namespace,
			'/credentials/(?P<id>[\d]+)',
			[
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => [ $this, 'delete_credential' ],
				'permission_callback' => [ $this, 'check_logged_in_permission' ],
			]
		);

		// 6. POST /auth/google - Verify Google GSI Credential
		register_rest_route(
			$this->namespace,
			'/auth/google',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'auth_google' ],
				'permission_callback' => '__return_true',
				'args'                => [
					'credential'  => [
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					],
					'redirect_to' => [
						'required'          => false,
						'type'              => 'string',
						'sanitize_callback' => 'esc_url_raw',
					],
				],
			]
		);

		// 7. POST /auth/magic-link/send - Send Magic Link & OTP Code
		register_rest_route(
			$this->namespace,
			'/auth/magic-link/send',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'auth_magic_send' ],
				'permission_callback' => '__return_true',
				'args'                => [
					'email'       => [
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_email',
					],
					'redirect_to' => [
						'required'          => false,
						'type'              => 'string',
						'sanitize_callback' => 'esc_url_raw',
					],
				],
			]
		);

		// 8. POST /auth/magic-link/verify-otp - Verify 6-digit OTP Code
		register_rest_route(
			$this->namespace,
			'/auth/magic-link/verify-otp',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'auth_magic_verify_otp' ],
				'permission_callback' => '__return_true',
				'args'                => [
					'email'       => [
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_email',
					],
					'otp_code'    => [
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					],
					'redirect_to' => [
						'required'          => false,
						'type'              => 'string',
						'sanitize_callback' => 'esc_url_raw',
					],
				],
			]
		);
	}

	/**
	 * Permission callback for authenticated routes.
	 *
	 * @return bool
	 */
	public function check_logged_in_permission(): bool {
		return is_user_logged_in();
	}

	/**
	 * POST /register/options - Generate registration challenge.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response|WP_Error
	 */
	public function register_options( WP_REST_Request $request ) {
		$user_id = get_current_user_id();
		$options = AuthService::generate_registration_options( $user_id );

		if ( empty( $options ) ) {
			return new WP_Error(
				'user_not_found',
				__( 'ไม่พบข้อมูลผู้ใช้งานสำหรับสร้าง Passkey', 'my-wc-passkeys' ),
				[ 'status' => 404 ]
			);
		}

		return rest_ensure_response( [
			'success' => true,
			'options' => $options,
		] );
	}

	/**
	 * POST /register/verify - Verify attestation and save passkey.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response|WP_Error
	 */
	public function register_verify( WP_REST_Request $request ) {
		$user_id     = get_current_user_id();
		$response    = (array) $request->get_param( 'response' );
		$device_name = sanitize_text_field( (string) $request->get_param( 'device_name' ) );

		$result = AuthService::verify_registration( $user_id, $response, $device_name );

		if ( ! $result['success'] ) {
			return new WP_Error(
				'verification_failed',
				$result['message'],
				[ 'status' => 400 ]
			);
		}

		return rest_ensure_response( [
			'success' => true,
			'message' => $result['message'],
			'id'      => $result['id'],
		] );
	}

	/**
	 * POST /login/options - Generate authentication challenge.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function login_options( WP_REST_Request $request ) {
		// Use client session token or generate unique random hash
		$session_hash = sanitize_text_field( (string) $request->get_param( 'session_hash' ) );
		if ( empty( $session_hash ) ) {
			$session_hash = wp_generate_password( 32, false );
		}

		$options = AuthService::generate_login_options( $session_hash );

		return rest_ensure_response( [
			'success'      => true,
			'session_hash' => $session_hash,
			'options'      => $options,
		] );
	}

	/**
	 * POST /login/verify - Verify assertion and log in user.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response|WP_Error
	 */
	public function login_verify( WP_REST_Request $request ) {
		$response     = (array) $request->get_param( 'response' );
		$session_hash = sanitize_text_field( (string) $request->get_param( 'session_hash' ) );

		$result = AuthService::verify_login( $response, $session_hash );

		if ( ! $result['success'] ) {
			return new WP_Error(
				'login_failed',
				$result['message'],
				[ 'status' => 401 ]
			);
		}

		return rest_ensure_response( $result );
	}

	/**
	 * DELETE /credentials/{id} - Delete a passkey.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_credential( WP_REST_Request $request ) {
		$id      = (int) $request->get_param( 'id' );
		$user_id = get_current_user_id();

		$passkey = DB::get_passkey_by_id( $id );
		if ( ! $passkey ) {
			return new WP_Error(
				'not_found',
				__( 'ไม่พบอุปกรณ์ที่ต้องการลบ', 'my-wc-passkeys' ),
				[ 'status' => 404 ]
			);
		}

		if ( (int) $passkey->user_id !== $user_id && ! current_user_can( 'manage_options' ) ) {
			return new WP_Error(
				'forbidden',
				__( 'คุณไม่มีสิทธิ์ลบอุปกรณ์ของผู้อื่น', 'my-wc-passkeys' ),
				[ 'status' => 403 ]
			);
		}

		$deleted = DB::delete_passkey( $id, (int) $passkey->user_id );

		if ( ! $deleted ) {
			return new WP_Error(
				'delete_failed',
				__( 'เกิดข้อผิดพลาดในการลบอุปกรณ์', 'my-wc-passkeys' ),
				[ 'status' => 500 ]
			);
		}

		return rest_ensure_response( [
			'success' => true,
			'message' => __( 'ลบอุปกรณ์ Passkey สำเร็จเรียบร้อย', 'my-wc-passkeys' ),
		] );
	}

	/**
	 * POST /auth/google - Authenticate user with Google Credential JWT.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response|WP_Error
	 */
	public function auth_google( WP_REST_Request $request ) {
		if ( ! AuthSettings::is_google_enabled() ) {
			return new WP_Error( 'google_disabled', __( 'Google Sign-In ปิดการใช้งานอยู่', 'my-wc-passkeys' ), [ 'status' => 403 ] );
		}

		$credential  = (string) $request->get_param( 'credential' );
		$redirect_to = (string) $request->get_param( 'redirect_to' );
		if ( empty( $redirect_to ) ) {
			$redirect_to = AuthSettings::get_default_redirect_url();
		}

		$payload = SocialAuth::verify_google_jwt( $credential );
		if ( is_wp_error( $payload ) ) {
			return $payload;
		}

		$google_sub   = $payload['sub'] ?? '';
		$email        = sanitize_email( $payload['email'] ?? '' );
		$display_name = sanitize_text_field( $payload['name'] ?? '' );
		$avatar_url   = esc_url_raw( $payload['picture'] ?? '' );

		$user = SocialAuth::login_or_create_user( [
			'provider'     => 'google',
			'provider_id'  => $google_sub,
			'email'        => $email,
			'display_name' => $display_name,
			'avatar_url'   => $avatar_url,
		] );

		if ( is_wp_error( $user ) ) {
			return $user;
		}

		return rest_ensure_response( [
			'success'      => true,
			'redirect_url' => $redirect_to,
			'message'      => __( 'เข้าสู่ระบบด้วย Google สำเร็จเรียบร้อย!', 'my-wc-passkeys' ),
		] );
	}

	/**
	 * POST /auth/magic-link/send - Send Magic Link and OTP.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response|WP_Error
	 */
	public function auth_magic_send( WP_REST_Request $request ) {
		if ( ! AuthSettings::is_magic_link_enabled() ) {
			return new WP_Error( 'magic_link_disabled', __( 'Magic Link & OTP ปิดการใช้งานอยู่', 'my-wc-passkeys' ), [ 'status' => 403 ] );
		}

		$email       = sanitize_email( (string) $request->get_param( 'email' ) );
		$redirect_to = (string) $request->get_param( 'redirect_to' );
		if ( empty( $redirect_to ) ) {
			$redirect_to = AuthSettings::get_default_redirect_url();
		}

		$result = SocialAuth::send_magic_link_otp( $email, $redirect_to );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return rest_ensure_response( [
			'success' => true,
			'message' => __( 'ระบบได้ส่งรหัส OTP 6 หลัก และลิงก์เข้าสู่ระบบไปยังอีเมลของคุณเรียบร้อยแล้ว กรุณาตรวจสอบกล่องข้อความ (Inbox / Spam)', 'my-wc-passkeys' ),
			'ttl'     => AuthSettings::get_magic_link_ttl(),
		] );
	}

	/**
	 * POST /auth/magic-link/verify-otp - Verify 6-digit OTP code and login.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response|WP_Error
	 */
	public function auth_magic_verify_otp( WP_REST_Request $request ) {
		if ( ! AuthSettings::is_magic_link_enabled() ) {
			return new WP_Error( 'magic_link_disabled', __( 'Magic Link & OTP ปิดการใช้งานอยู่', 'my-wc-passkeys' ), [ 'status' => 403 ] );
		}

		$email       = sanitize_email( (string) $request->get_param( 'email' ) );
		$otp_code    = (string) $request->get_param( 'otp_code' );
		$redirect_to = (string) $request->get_param( 'redirect_to' );
		if ( empty( $redirect_to ) ) {
			$redirect_to = AuthSettings::get_default_redirect_url();
		}

		$user = SocialAuth::verify_otp_code( $email, $otp_code );
		if ( is_wp_error( $user ) ) {
			return $user;
		}

		return rest_ensure_response( [
			'success'      => true,
			'redirect_url' => $redirect_to,
			'message'      => __( 'ยืนยันรหัส OTP สำเร็จเรียบร้อย กำลังเข้าสู่ระบบ...', 'my-wc-passkeys' ),
		] );
	}
}
