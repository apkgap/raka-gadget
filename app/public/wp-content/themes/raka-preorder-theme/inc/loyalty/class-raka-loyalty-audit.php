<?php
/**
 * Admin Audit Trail Logger for Loyalty & Rewards Engine (Immutable).
 *
 * @package Raka_Preorder_Theme
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Raka_Loyalty_Audit
 */
class Raka_Loyalty_Audit {

	/**
	 * Log an administrative action to the immutable audit trail.
	 *
	 * @param string      $action      Action identifier (e.g. 'adjust_points', 'update_settings', 'reconcile', 'export_csv', 'purge_logs').
	 * @param string      $target_type Target type (e.g. 'user', 'settings', 'system', 'catalog').
	 * @param string|int  $target_id   Target identifier (e.g. User ID, Option Key).
	 * @param array|null  $diff        JSON-serializable array of before/after state diff.
	 * @param string      $reason      Mandatory human-readable reason for the action.
	 * @param int         $actor_id    Admin User ID (defaults to current user).
	 * @return bool
	 */
	public static function log(
		string $action,
		string $target_type,
		$target_id,
		?array $diff,
		string $reason,
		int $actor_id = 0
	): bool {
		global $wpdb;

		if ( 0 === $actor_id ) {
			$actor_id = get_current_user_id();
		}

		$table_name = $wpdb->prefix . 'raka_loyalty_audit_log';
		$ip_hash    = self::get_anonymized_ip_hash();

		// Clean and mask sensitive keys in diff
		$clean_diff = $diff ? Raka_Loyalty_Logger::sanitize_context( $diff ) : null;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$result = $wpdb->insert(
			$table_name,
			[
				'actor_id'    => $actor_id,
				'action'      => sanitize_key( $action ),
				'target_type' => sanitize_key( $target_type ),
				'target_id'   => sanitize_text_field( (string) $target_id ),
				'diff'        => ! empty( $clean_diff ) ? wp_json_encode( $clean_diff, JSON_UNESCAPED_UNICODE ) : null,
				'reason'      => sanitize_textarea_field( $reason ),
				'ip_hash'     => $ip_hash,
				'created_at'  => current_time( 'mysql', true ),
			],
			[ '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ]
		);

		return false !== $result;
	}

	/**
	 * Anonymized IP hash.
	 */
	private static function get_anonymized_ip_hash(): string {
		$ip   = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
		$salt = defined( 'AUTH_SALT' ) ? AUTH_SALT : 'raka_audit_salt';
		return hash( 'sha256', $ip . $salt );
	}
}
