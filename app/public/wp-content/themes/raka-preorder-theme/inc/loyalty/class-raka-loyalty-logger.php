<?php
/**
 * Central Logger for Loyalty & Rewards Engine (Observability).
 *
 * @package Raka_Preorder_Theme
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Raka_Loyalty_Logger
 */
class Raka_Loyalty_Logger {

	/**
	 * Unique Correlation ID for current HTTP / CLI request.
	 *
	 * @var string|null
	 */
	private static ?string $request_id = null;

	/**
	 * Severity Level Hierarchy.
	 */
	private const LEVELS = [
		'debug'    => 100,
		'info'     => 200,
		'warning'  => 300,
		'error'    => 400,
		'critical' => 500,
	];

	/**
	 * Sensitive keys to redact in context payloads.
	 */
	private const SENSITIVE_KEYS = [
		'password',
		'current_password',
		'new_password',
		'confirm_password',
		'token',
		'nonce',
		'secret',
		'turnstile_secret',
		'card_number',
		'cvv',
		'auth_token',
	];

	/**
	 * Get or generate correlation request ID.
	 *
	 * @return string
	 */
	public static function get_request_id(): string {
		if ( null === self::$request_id ) {
			self::$request_id = wp_generate_uuid4();
		}
		return self::$request_id;
	}

	/**
	 * Log a debug event.
	 */
	public static function debug( string $event_code, string $message, array $context = [], int $order_id = 0, int $user_id = 0, int $actor_id = 0, string $channel = 'service' ): void {
		self::log( 'debug', $channel, $event_code, $message, $context, $order_id, $user_id, $actor_id );
	}

	/**
	 * Log an info event.
	 */
	public static function info( string $event_code, string $message, array $context = [], int $order_id = 0, int $user_id = 0, int $actor_id = 0, string $channel = 'service' ): void {
		self::log( 'info', $channel, $event_code, $message, $context, $order_id, $user_id, $actor_id );
	}

	/**
	 * Log a warning event.
	 */
	public static function warning( string $event_code, string $message, array $context = [], int $order_id = 0, int $user_id = 0, int $actor_id = 0, string $channel = 'service' ): void {
		self::log( 'warning', $channel, $event_code, $message, $context, $order_id, $user_id, $actor_id );
	}

	/**
	 * Log an error event.
	 */
	public static function error( string $event_code, string $message, array $context = [], int $order_id = 0, int $user_id = 0, int $actor_id = 0, string $channel = 'service' ): void {
		self::log( 'error', $channel, $event_code, $message, $context, $order_id, $user_id, $actor_id );
	}

	/**
	 * Log a critical event.
	 */
	public static function critical( string $event_code, string $message, array $context = [], int $order_id = 0, int $user_id = 0, int $actor_id = 0, string $channel = 'service' ): void {
		self::log( 'critical', $channel, $event_code, $message, $context, $order_id, $user_id, $actor_id );
	}

	/**
	 * Core logging method.
	 */
	public static function log(
		string $level,
		string $channel,
		string $event_code,
		string $message,
		array $context = [],
		int $order_id = 0,
		int $user_id = 0,
		int $actor_id = 0
	): void {
		$level = strtolower( $level );
		if ( ! isset( self::LEVELS[ $level ] ) ) {
			$level = 'info';
		}

		// Check if level meets configured threshold
		if ( ! self::should_log( $level ) ) {
			return;
		}

		// Fallback user/actor if empty
		if ( 0 === $user_id && is_user_logged_in() ) {
			$user_id = get_current_user_id();
		}
		if ( 0 === $actor_id && is_user_logged_in() ) {
			$actor_id = get_current_user_id();
		}

		$clean_context = self::sanitize_context( $context );
		$ip_hash       = self::get_anonymized_ip_hash();
		$request_id    = self::get_request_id();

		// Write to DB with isolated error handling
		try {
			global $wpdb;
			$table_name = $wpdb->prefix . 'raka_loyalty_system_log';

			// Check table exists to prevent errors during early setup
			$wpdb->insert(
				$table_name,
				[
					'level'      => $level,
					'channel'    => sanitize_key( $channel ),
					'event_code' => sanitize_text_field( $event_code ),
					'message'    => sanitize_text_field( $message ),
					'context'    => ! empty( $clean_context ) ? wp_json_encode( $clean_context, JSON_UNESCAPED_UNICODE ) : null,
					'request_id' => $request_id,
					'order_id'   => $order_id,
					'user_id'    => $user_id,
					'actor_id'   => $actor_id,
					'ip_hash'    => $ip_hash,
					'created_at' => current_time( 'mysql', true ),
				],
				[ '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%s', '%s' ]
			);
		} catch ( \Throwable $e ) {
			// Fail-safe: write to standard PHP error log
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( sprintf( '[Raka Loyalty Logger DB Error] %s: %s', $event_code, $e->getMessage() ) );
		}

		// For error and critical, also write to standard WC logger & error_log
		if ( in_array( $level, [ 'error', 'critical' ], true ) ) {
			$wc_msg = sprintf( '[%s] [%s] %s | Context: %s', strtoupper( $level ), $event_code, $message, wp_json_encode( $clean_context ) );
			if ( function_exists( 'wc_get_logger' ) ) {
				$logger = wc_get_logger();
				$logger->log( $level, $wc_msg, [ 'source' => 'raka-loyalty' ] );
			}
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( '[Raka Loyalty] ' . $wc_msg );
		}
	}

	/**
	 * Check if level satisfies minimum log level threshold.
	 */
	private static function should_log( string $level ): bool {
		// Temporary 24h debug mode override
		if ( get_transient( 'raka_loyalty_debug_mode_expires' ) ) {
			return true;
		}

		$options       = get_option( 'raka_theme_options', [] );
		$min_level_str = $options['loyalty']['min_log_level'] ?? 'warning';
		$min_level_val = self::LEVELS[ strtolower( (string) $min_level_str ) ] ?? self::LEVELS['warning'];
		$curr_val      = self::LEVELS[ $level ] ?? self::LEVELS['info'];

		return $curr_val >= $min_level_val;
	}

	/**
	 * Recursively sanitize and mask sensitive data.
	 */
	public static function sanitize_context( array $data ): array {
		$sanitized = [];
		foreach ( $data as $k => $v ) {
			$lower_key = strtolower( (string) $k );
			if ( in_array( $lower_key, self::SENSITIVE_KEYS, true ) ) {
				$sanitized[ $k ] = '***REDACTED***';
				continue;
			}
			if ( is_array( $v ) ) {
				$sanitized[ $k ] = self::sanitize_context( $v );
			} elseif ( is_scalar( $v ) || null === $v ) {
				$sanitized[ $k ] = $v;
			} elseif ( is_object( $v ) ) {
				$sanitized[ $k ] = method_exists( $v, '__toString' ) ? (string) $v : get_class( $v );
			}
		}
		return $sanitized;
	}

	/**
	 * Get SHA-256 hashed IP for privacy compliance.
	 */
	private static function get_anonymized_ip_hash(): string {
		$ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
		$salt = defined( 'AUTH_SALT' ) ? AUTH_SALT : 'raka_salt_fallback';
		return hash( 'sha256', $ip . $salt );
	}

	/**
	 * Scheduled Log Retention Cleanup (Batch delete).
	 *
	 * @param int $retention_days Days to keep logs.
	 * @return int Deleted rows count.
	 */
	public static function purge_old_logs( int $retention_days = 90 ): int {
		global $wpdb;
		$table_name = $wpdb->prefix . 'raka_loyalty_system_log';
		$cutoff     = gmdate( 'Y-m-d H:i:s', time() - ( $retention_days * DAY_IN_SECONDS ) );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$deleted = $wpdb->query(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"DELETE FROM {$table_name} WHERE created_at < %s LIMIT 10000",
				$cutoff
			)
		);

		return (int) $deleted;
	}
}
