<?php
declare(strict_types=1);

namespace MyWcPasskeys;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Authentication and Cryptographic Service for WebAuthn / FIDO2 Passkeys.
 */
class AuthService {

	/**
	 * Transient expiry time in seconds for challenges.
	 */
	public const CHALLENGE_TTL = 120;

	/**
	 * Convert binary data to Base64URL string (RFC 4648 §5).
	 *
	 * @param string $data Raw binary data.
	 * @return string Base64URL string.
	 */
	public static function base64url_encode( string $data ): string {
		return rtrim( strtr( base64_encode( $data ), '+/', '-_' ), '=' );
	}

	/**
	 * Convert Base64URL string to binary data.
	 *
	 * @param string $data Base64URL string.
	 * @return string Raw binary data.
	 */
	public static function base64url_decode( string $data ): string {
		return base64_decode( strtr( $data, '-_', '+/' ) . str_repeat( '=', 3 - ( 3 + strlen( $data ) ) % 4 ) );
	}

	/**
	 * Get Relying Party ID (Domain without port/protocol).
	 *
	 * @return string RP ID host.
	 */
	public static function get_rp_id(): string {
		$host = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : 'localhost';
		// Remove port if present
		$parts = explode( ':', $host );
		return $parts[0];
	}

	/**
	 * Get Relying Party Name.
	 *
	 * @return string Site name.
	 */
	public static function get_rp_name(): string {
		return get_bloginfo( 'name' ) ?: 'WooCommerce Store';
	}

	/**
	 * Generate Registration Challenge and CredentialCreationOptions.
	 *
	 * @param int $user_id WordPress User ID.
	 * @return array Creation options for frontend navigator.credentials.create().
	 */
	public static function generate_registration_options( int $user_id ): array {
		$user = get_userdata( $user_id );
		if ( ! $user ) {
			return [];
		}

		// Generate 32 bytes cryptographically secure challenge
		$raw_challenge = random_bytes( 32 );
		$challenge_b64 = self::base64url_encode( $raw_challenge );

		// Store challenge in transient
		set_transient( 'wc_pk_reg_' . $user_id, $challenge_b64, self::CHALLENGE_TTL );

		// Existing user credentials to exclude from re-registration
		$existing_keys = DB::get_user_passkeys( $user_id );
		$exclude_credentials = [];
		foreach ( $existing_keys as $key ) {
			$exclude_credentials[] = [
				'id'         => $key->credential_id,
				'type'       => 'public-key',
				'transports' => [ 'internal', 'hybrid', 'usb', 'nfc', 'ble' ],
			];
		}

		// User handle (immutable ID)
		$user_handle = self::base64url_encode( (string) $user->ID );

		return [
			'rp' => [
				'name' => self::get_rp_name(),
				'id'   => self::get_rp_id(),
			],
			'user' => [
				'id'          => $user_handle,
				'name'        => $user->user_email,
				'displayName' => $user->display_name ?: $user->user_login,
			],
			'challenge' => $challenge_b64,
			'pubKeyCredParams' => [
				[ 'type' => 'public-key', 'alg' => -7 ],   // ES256 (ECDSA w/ SHA-256)
				[ 'type' => 'public-key', 'alg' => -257 ], // RS256 (RSASSA-PKCS1-v1_5 w/ SHA-256)
			],
			'timeout' => 60000,
			'excludeCredentials' => $exclude_credentials,
			'authenticatorSelection' => [
				'authenticatorAttachment' => 'platform',
				'residentKey'             => 'preferred',
				'requireResidentKey'      => false,
				'userVerification'        => 'preferred',
			],
			'attestation' => 'none',
		];
	}

	/**
	 * Verify Registration Attestation Response.
	 *
	 * @param int    $user_id      WordPress User ID.
	 * @param array  $response     WebAuthn attestation response from browser.
	 * @param string $device_name  User given device name.
	 * @return array Verification result [ 'success' => bool, 'message' => string, 'id' => int ].
	 */
	public static function verify_registration( int $user_id, array $response, string $device_name ): array {
		$stored_challenge = get_transient( 'wc_pk_reg_' . $user_id );
		delete_transient( 'wc_pk_reg_' . $user_id );

		if ( empty( $stored_challenge ) ) {
			return [
				'success' => false,
				'message' => __( 'คำขอลงทะเบียนหมดอายุ กรุณาลองใหม่อีกครั้ง', 'my-wc-passkeys' ),
			];
		}

		$client_data_json = isset( $response['response']['clientDataJSON'] ) ? self::base64url_decode( (string) $response['response']['clientDataJSON'] ) : '';
		$client_data      = json_decode( $client_data_json, true );

		if ( ! is_array( $client_data ) || empty( $client_data['challenge'] ) || empty( $client_data['type'] ) ) {
			return [
				'success' => false,
				'message' => __( 'โครงสร้างข้อมูล ClientData ไม่ถูกต้อง', 'my-wc-passkeys' ),
			];
		}

		if ( 'webauthn.create' !== $client_data['type'] ) {
			return [
				'success' => false,
				'message' => __( 'ประเภทคำขอไม่ตรงกับการลงทะเบียน (Expected webauthn.create)', 'my-wc-passkeys' ),
			];
		}

		if ( ! hash_equals( $stored_challenge, (string) $client_data['challenge'] ) ) {
			return [
				'success' => false,
				'message' => __( 'Challenge ไม่ตรงกับที่ระบบสร้างไว้', 'my-wc-passkeys' ),
			];
		}

		$credential_id = sanitize_text_field( (string) ( $response['id'] ?? '' ) );
		if ( empty( $credential_id ) ) {
			return [
				'success' => false,
				'message' => __( 'ไม่พบ Credential ID จากอุปกรณ์', 'my-wc-passkeys' ),
			];
		}

		// Check if credential ID already exists in DB
		$existing = DB::get_passkey_by_credential_id( $credential_id );
		if ( $existing ) {
			return [
				'success' => false,
				'message' => __( 'อุปกรณ์หรือ Passkey นี้ได้ลงทะเบียนไว้ในระบบแล้ว', 'my-wc-passkeys' ),
			];
		}

		// Store Attestation Object / Public Key payload
		$public_key_payload = wp_json_encode( [
			'attestationObject' => $response['response']['attestationObject'] ?? '',
			'publicKeyAlgorithm'=> $response['response']['publicKeyAlgorithm'] ?? -7,
			'transports'        => $response['response']['transports'] ?? [ 'internal' ],
		] );

		$inserted_id = DB::insert_passkey(
			$user_id,
			$credential_id,
			$public_key_payload,
			0,
			$device_name
		);

		if ( ! $inserted_id ) {
			return [
				'success' => false,
				'message' => __( 'เกิดข้อผิดพลาดในการบันทึกข้อมูลลงฐานข้อมูล', 'my-wc-passkeys' ),
			];
		}

		return [
			'success' => true,
			'message' => __( 'ลงทะเบียน Passkey สำเร็จเรียบร้อย', 'my-wc-passkeys' ),
			'id'      => $inserted_id,
		];
	}

	/**
	 * Generate Login Authentication Challenge and CredentialRequestOptions.
	 *
	 * @param string $session_hash Client session identifier.
	 * @return array Request options for frontend navigator.credentials.get().
	 */
	public static function generate_login_options( string $session_hash ): array {
		$raw_challenge = random_bytes( 32 );
		$challenge_b64 = self::base64url_encode( $raw_challenge );

		// Store challenge in transient with session hash
		set_transient( 'wc_pk_login_' . $session_hash, $challenge_b64, self::CHALLENGE_TTL );

		return [
			'challenge'        => $challenge_b64,
			'rpId'             => self::get_rp_id(),
			'timeout'          => 60000,
			'userVerification' => 'preferred',
		];
	}

	/**
	 * Verify Login Assertion Response and Authenticate User.
	 *
	 * @param array  $response     WebAuthn assertion response from browser.
	 * @param string $session_hash Client session identifier.
	 * @return array Verification and login result.
	 */
	public static function verify_login( array $response, string $session_hash ): array {
		$stored_challenge = get_transient( 'wc_pk_login_' . $session_hash );
		delete_transient( 'wc_pk_login_' . $session_hash );

		if ( empty( $stored_challenge ) ) {
			return [
				'success' => false,
				'message' => __( 'เซสชันการเข้าสู่ระบบหมดอายุ กรุณาลองใหม่อีกครั้ง', 'my-wc-passkeys' ),
			];
		}

		$client_data_json = isset( $response['response']['clientDataJSON'] ) ? self::base64url_decode( (string) $response['response']['clientDataJSON'] ) : '';
		$client_data      = json_decode( $client_data_json, true );

		if ( ! is_array( $client_data ) || empty( $client_data['challenge'] ) || empty( $client_data['type'] ) ) {
			return [
				'success' => false,
				'message' => __( 'โครงสร้างข้อมูล ClientData ไม่ถูกต้อง', 'my-wc-passkeys' ),
			];
		}

		if ( 'webauthn.get' !== $client_data['type'] ) {
			return [
				'success' => false,
				'message' => __( 'ประเภทคำขอไม่ถูกต้อง (Expected webauthn.get)', 'my-wc-passkeys' ),
			];
		}

		if ( ! hash_equals( $stored_challenge, (string) $client_data['challenge'] ) ) {
			return [
				'success' => false,
				'message' => __( 'Challenge ไม่ถูกต้องหรือหมดอายุ', 'my-wc-passkeys' ),
			];
		}

		$credential_id = sanitize_text_field( (string) ( $response['id'] ?? '' ) );
		$passkey_row   = DB::get_passkey_by_credential_id( $credential_id );

		if ( ! $passkey_row ) {
			return [
				'success' => false,
				'message' => __( 'ไม่พบข้อมูล Passkey ในระบบ หรืออุปกรณ์นี้อาจถูกลบไปแล้ว', 'my-wc-passkeys' ),
			];
		}

		$user = get_userdata( (int) $passkey_row->user_id );
		if ( ! $user ) {
			return [
				'success' => false,
				'message' => __( 'ไม่พบผู้ใช้งานที่ผูกกับ Passkey นี้', 'my-wc-passkeys' ),
			];
		}

		// Replay Attack Protection: Parse Authenticator Data Sign Counter
		$auth_data_raw = isset( $response['response']['authenticatorData'] ) ? self::base64url_decode( (string) $response['response']['authenticatorData'] ) : '';
		$new_sign_count = 0;
		if ( strlen( $auth_data_raw ) >= 37 ) {
			// Bytes 33-36 contains big-endian 32-bit integer sign count
			$sign_count_bytes = substr( $auth_data_raw, 33, 4 );
			$unpacked         = unpack( 'NsignCount', $sign_count_bytes );
			$new_sign_count   = isset( $unpacked['signCount'] ) ? (int) $unpacked['signCount'] : 0;
		}

		$prev_sign_count = (int) $passkey_row->sign_count;

		// If both counts > 0, ensure new counter is higher than previous to prevent cloned keys
		if ( $prev_sign_count > 0 && $new_sign_count > 0 && $new_sign_count <= $prev_sign_count ) {
			return [
				'success' => false,
				'message' => __( 'ตรวจพบความผิดปกติของ Signature Counter (อาจมีการ Clone อุปกรณ์)', 'my-wc-passkeys' ),
			];
		}

		// Update sign counter & last_used_at in DB
		DB::update_sign_count( $credential_id, $new_sign_count );

		// Authenticate WordPress User session
		wp_clear_auth_cookie();
		wp_set_current_user( $user->ID );
		wp_set_auth_cookie( $user->ID, true );
		do_action( 'wp_login', $user->user_login, $user );

		// Determine redirect URL (WooCommerce My Account or Referrer)
		$redirect_url = function_exists( 'wc_get_page_permalink' ) && wc_get_page_id( 'myaccount' ) > 0
			? wc_get_page_permalink( 'myaccount' )
			: home_url( '/my-account/' );

		return [
			'success'      => true,
			'message'      => __( 'เข้าสู่ระบบสำเร็จ กำลังนำท่านเข้าสู่ระบบ...', 'my-wc-passkeys' ),
			'redirect_url' => $redirect_url,
			'user'         => [
				'id'          => $user->ID,
				'display_name'=> $user->display_name,
				'email'       => $user->user_email,
			],
		];
	}
}
