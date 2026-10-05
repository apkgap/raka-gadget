<?php
declare(strict_types=1);

namespace MyWcPasskeys;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Data Access Layer for Passkey Credentials.
 */
class DB {

	/**
	 * Get table name with WordPress prefix.
	 *
	 * @return string
	 */
	public static function get_table_name(): string {
		global $wpdb;
		return $wpdb->prefix . 'wc_user_passkeys';
	}

	/**
	 * Get all registered passkeys for a specific user.
	 *
	 * @param int $user_id WordPress User ID.
	 * @return array List of passkey records.
	 */
	public static function get_user_passkeys( int $user_id ): array {
		global $wpdb;

		$table = self::get_table_name();
		$sql   = $wpdb->prepare(
			"SELECT id, user_id, credential_id, device_name, sign_count, created_at, last_used_at 
			 FROM {$table} 
			 WHERE user_id = %d 
			 ORDER BY created_at DESC",
			$user_id
		);

		$results = $wpdb->get_results( $sql );
		return is_array( $results ) ? $results : [];
	}

	/**
	 * Get passkey record by unique credential ID.
	 *
	 * @param string $credential_id Base64URL-encoded credential ID.
	 * @return object|null Database row or null if not found.
	 */
	public static function get_passkey_by_credential_id( string $credential_id ): ?object {
		global $wpdb;

		$table = self::get_table_name();
		$sql   = $wpdb->prepare(
			"SELECT * FROM {$table} WHERE credential_id = %s LIMIT 1",
			$credential_id
		);

		$row = $wpdb->get_row( $sql );
		return $row ?: null;
	}

	/**
	 * Get passkey record by primary ID.
	 *
	 * @param int $id Record ID.
	 * @return object|null
	 */
	public static function get_passkey_by_id( int $id ): ?object {
		global $wpdb;

		$table = self::get_table_name();
		$sql   = $wpdb->prepare(
			"SELECT * FROM {$table} WHERE id = %d LIMIT 1",
			$id
		);

		$row = $wpdb->get_row( $sql );
		return $row ?: null;
	}

	/**
	 * Insert a new passkey credential record.
	 *
	 * @param int    $user_id       WordPress User ID.
	 * @param string $credential_id Base64URL-encoded credential ID.
	 * @param string $public_key    JSON/PEM encoded public key.
	 * @param int    $sign_count    Initial signature counter.
	 * @param string $device_name   User-friendly device label.
	 * @return int Inserted ID or 0 on failure.
	 */
	public static function insert_passkey( int $user_id, string $credential_id, string $public_key, int $sign_count = 0, string $device_name = 'อุปกรณ์ไม่ระบุชื่อ' ): int {
		global $wpdb;

		$table = self::get_table_name();
		$data  = [
			'user_id'       => $user_id,
			'credential_id' => sanitize_text_field( $credential_id ),
			'public_key'    => $public_key,
			'sign_count'    => max( 0, $sign_count ),
			'device_name'   => sanitize_text_field( $device_name ?: 'อุปกรณ์ไม่ระบุชื่อ' ),
			'created_at'    => current_time( 'mysql' ),
			'last_used_at'  => null,
		];

		$inserted = $wpdb->insert(
			$table,
			$data,
			[ '%d', '%s', '%s', '%d', '%s', '%s', '%s' ]
		);

		return $inserted ? (int) $wpdb->insert_id : 0;
	}

	/**
	 * Update signature counter and last_used_at timestamp on successful authentication.
	 *
	 * @param string $credential_id Unique credential ID.
	 * @param int    $new_sign_count Signature count from authenticator.
	 * @return bool True on success.
	 */
	public static function update_sign_count( string $credential_id, int $new_sign_count ): bool {
		global $wpdb;

		$table = self::get_table_name();
		$updated = $wpdb->update(
			$table,
			[
				'sign_count'   => $new_sign_count,
				'last_used_at' => current_time( 'mysql' ),
			],
			[ 'credential_id' => $credential_id ],
			[ '%d', '%s' ],
			[ '%s' ]
		);

		return false !== $updated;
	}

	/**
	 * Delete a passkey record by ID, verifying ownership by user_id.
	 *
	 * @param int $id      Record ID.
	 * @param int $user_id WordPress User ID.
	 * @return bool True on successful deletion.
	 */
	public static function delete_passkey( int $id, int $user_id ): bool {
		global $wpdb;

		$table   = self::get_table_name();
		$deleted = $wpdb->delete(
			$table,
			[
				'id'      => $id,
				'user_id' => $user_id,
			],
			[ '%d', '%d' ]
		);

		return (bool) $deleted;
	}
}
