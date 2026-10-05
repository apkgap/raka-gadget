<?php
/**
 * Database Schema and Migration Manager for Loyalty & Rewards Engine.
 *
 * @package Raka_Preorder_Theme
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Raka_Loyalty_DB
 */
class Raka_Loyalty_DB {

	/**
	 * Database schema version.
	 */
	public const DB_VERSION = '1.0.0';

	/**
	 * Option key for stored DB version.
	 */
	public const VERSION_OPTION = 'raka_loyalty_db_version';

	/**
	 * Initialize DB hooks and migrations.
	 */
	public static function init(): void {
		add_action( 'admin_init', [ __CLASS__, 'check_migration' ] );
	}

	/**
	 * Check and run DB migration if version differs.
	 */
	public static function check_migration(): void {
		$installed_version = get_option( self::VERSION_OPTION, '0.0.0' );
		if ( version_compare( (string) $installed_version, self::DB_VERSION, '<' ) ) {
			self::migrate();
		}
	}

	/**
	 * Run DB schema migration using dbDelta().
	 */
	public static function migrate(): void {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();

		// 1. Table: Accounts (Materialized Cache)
		$table_accounts = $wpdb->prefix . 'raka_loyalty_accounts';
		$sql_accounts   = "CREATE TABLE {$table_accounts} (
			user_id bigint(20) unsigned NOT NULL,
			balance decimal(12,2) NOT NULL DEFAULT 0.00,
			reserved decimal(12,2) NOT NULL DEFAULT 0.00,
			lifetime_earned decimal(14,2) NOT NULL DEFAULT 0.00,
			lifetime_spent decimal(14,2) NOT NULL DEFAULT 0.00,
			tier varchar(32) NOT NULL DEFAULT 'bronze',
			version bigint(20) unsigned NOT NULL DEFAULT 1,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (user_id),
			KEY tier (tier),
			KEY updated_at (updated_at)
		) {$charset_collate} ENGINE=InnoDB;";
		dbDelta( $sql_accounts );

		// 2. Table: Ledger (Append-Only)
		$table_ledger = $wpdb->prefix . 'raka_loyalty_ledger';
		$sql_ledger   = "CREATE TABLE {$table_ledger} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			user_id bigint(20) unsigned NOT NULL,
			event_type varchar(32) NOT NULL,
			amount decimal(12,2) NOT NULL,
			balance_after decimal(12,2) NOT NULL,
			order_id bigint(20) unsigned DEFAULT 0,
			idempotency_key varchar(128) NOT NULL,
			rule_snapshot longtext DEFAULT NULL,
			actor_id bigint(20) unsigned DEFAULT 0,
			request_id varchar(64) DEFAULT '',
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY idempotency_key (idempotency_key),
			KEY user_created (user_id,created_at),
			KEY order_id (order_id),
			KEY event_type (event_type),
			KEY request_id (request_id)
		) {$charset_collate} ENGINE=InnoDB;";
		dbDelta( $sql_ledger );

		// 3. Table: Lots (FIFO Expiry Management)
		$table_lots = $wpdb->prefix . 'raka_loyalty_lots';
		$sql_lots   = "CREATE TABLE {$table_lots} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			user_id bigint(20) unsigned NOT NULL,
			ledger_id bigint(20) unsigned NOT NULL,
			earned_amount decimal(12,2) NOT NULL,
			remaining_amount decimal(12,2) NOT NULL,
			expires_at datetime NOT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY user_remaining_expires (user_id,remaining_amount,expires_at),
			KEY expires_at (expires_at),
			KEY ledger_id (ledger_id)
		) {$charset_collate} ENGINE=InnoDB;";
		dbDelta( $sql_lots );

		// 4. Table: Reward Catalog
		$table_catalog = $wpdb->prefix . 'raka_loyalty_reward_catalog';
		$sql_catalog   = "CREATE TABLE {$table_catalog} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			product_id bigint(20) unsigned NOT NULL,
			points_cost decimal(10,2) NOT NULL DEFAULT 100.00,
			monthly_quota int(11) NOT NULL DEFAULT -1,
			stock_limit int(11) NOT NULL DEFAULT -1,
			eligible_tier varchar(32) NOT NULL DEFAULT 'all',
			is_active tinyint(1) NOT NULL DEFAULT 1,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY product_id (product_id),
			KEY is_active (is_active),
			KEY eligible_tier (eligible_tier)
		) {$charset_collate} ENGINE=InnoDB;";
		dbDelta( $sql_catalog );

		// 5. Table: System Log (Observability)
		$table_system_log = $wpdb->prefix . 'raka_loyalty_system_log';
		$sql_system_log   = "CREATE TABLE {$table_system_log} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			level varchar(16) NOT NULL,
			channel varchar(32) NOT NULL,
			event_code varchar(64) NOT NULL,
			message text NOT NULL,
			context longtext DEFAULT NULL,
			request_id varchar(64) DEFAULT '',
			order_id bigint(20) unsigned DEFAULT 0,
			user_id bigint(20) unsigned DEFAULT 0,
			actor_id bigint(20) unsigned DEFAULT 0,
			ip_hash varchar(64) DEFAULT '',
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY level_created (level,created_at),
			KEY channel_created (channel,created_at),
			KEY event_code (event_code),
			KEY order_id (order_id),
			KEY user_id (user_id),
			KEY request_id (request_id)
		) {$charset_collate} ENGINE=InnoDB;";
		dbDelta( $sql_system_log );

		// 6. Table: Audit Log (Admin Actions - Immutable)
		$table_audit_log = $wpdb->prefix . 'raka_loyalty_audit_log';
		$sql_audit_log   = "CREATE TABLE {$table_audit_log} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			actor_id bigint(20) unsigned NOT NULL,
			action varchar(64) NOT NULL,
			target_type varchar(32) NOT NULL,
			target_id varchar(64) NOT NULL,
			diff longtext DEFAULT NULL,
			reason text NOT NULL,
			ip_hash varchar(64) DEFAULT '',
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY actor_created (actor_id,created_at),
			KEY action (action),
			KEY target (target_type,target_id)
		) {$charset_collate} ENGINE=InnoDB;";
		dbDelta( $sql_audit_log );

		// Ensure InnoDB Engine on all tables
		$tables = [ $table_accounts, $table_ledger, $table_lots, $table_catalog, $table_system_log, $table_audit_log ];
		foreach ( $tables as $tbl ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->query( "ALTER TABLE {$tbl} ENGINE = InnoDB;" );
		}

		update_option( self::VERSION_OPTION, self::DB_VERSION );

		// Log Migration
		if ( class_exists( 'Raka_Loyalty_Logger' ) ) {
			Raka_Loyalty_Logger::info(
				'MIGRATION_COMPLETED',
				'Loyalty DB schema migrated successfully to version ' . self::DB_VERSION,
				[ 'version' => self::DB_VERSION ],
				0,
				0,
				get_current_user_id()
			);
		}
	}

	/**
	 * Get table name with prefix.
	 *
	 * @param string $table_key Short name (accounts, ledger, lots, reward_catalog, system_log, audit_log).
	 * @return string
	 */
	public static function table( string $table_key ): string {
		global $wpdb;
		return $wpdb->prefix . 'raka_loyalty_' . $table_key;
	}
}
