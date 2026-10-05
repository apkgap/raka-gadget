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
}
