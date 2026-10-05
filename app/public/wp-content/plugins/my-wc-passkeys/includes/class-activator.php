<?php
declare(strict_types=1);

namespace MyWcPasskeys;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles plugin activation, database migrations, and deactivation tasks.
 */
class Activator {

	/**
	 * Run activation tasks (create database table, register endpoint, flush rewrite rules).
	 */
	public static function activate(): void {
		self::create_tables();
		self::add_endpoints();
		flush_rewrite_rules();
	}

	/**
	 * Run deactivation tasks.
	 */
	public static function deactivate(): void {
		flush_rewrite_rules();
	}

	/**
	 * Create or update custom database tables using dbDelta.
	 */
	public static function create_tables(): void {
		global $wpdb;

		$table_name      = $wpdb->prefix . 'wc_user_passkeys';
		$charset_collate = $wpdb->get_charset_collate();

		// dbDelta requires two spaces after PRIMARY KEY and strict syntax formatting
		$sql = "CREATE TABLE {$table_name} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			user_id bigint(20) unsigned NOT NULL,
			credential_id varchar(255) NOT NULL,
			public_key text NOT NULL,
			sign_count int(11) unsigned NOT NULL DEFAULT 0,
			device_name varchar(150) DEFAULT 'อุปกรณ์ไม่ระบุชื่อ',
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			last_used_at datetime DEFAULT NULL,
			PRIMARY KEY  (id),
			KEY user_id (user_id),
			UNIQUE KEY credential_id (credential_id)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		// Record DB version in options
		update_option( 'my_wc_passkeys_db_version', MY_WC_PASSKEYS_VERSION );
	}

	/**
	 * Add WooCommerce custom endpoint to rewrite rules before flushing.
	 */
	private static function add_endpoints(): void {
		add_rewrite_endpoint( 'passkeys', EP_ROOT | EP_PAGES );
	}
}
