<?php
/**
 * Loyalty & Rewards Engine Bootstrap Loader.
 *
 * @package Raka_Preorder_Theme
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// 1. Load Core Infrastructure Classes
require_once __DIR__ . '/class-raka-loyalty-db.php';
require_once __DIR__ . '/class-raka-loyalty-logger.php';
require_once __DIR__ . '/class-raka-loyalty-audit.php';
require_once __DIR__ . '/class-raka-loyalty-cli.php';

// 2. Load Core Service & Engine Classes
require_once __DIR__ . '/class-raka-loyalty-earn-engine.php';
require_once __DIR__ . '/class-raka-loyalty-service.php';
require_once __DIR__ . '/class-raka-loyalty-hooks.php';
require_once __DIR__ . '/class-raka-loyalty-blocks.php';

// Initialize Infrastructure & Hooks
Raka_Loyalty_DB::init();
Raka_Loyalty_CLI::init();
Raka_Loyalty_Hooks::init();
Raka_Loyalty_Blocks::init();

// Register Action Scheduler Scheduled Tasks
add_action( 'init', 'raka_loyalty_register_scheduled_actions' );

/**
 * Register Action Scheduler recurring jobs.
 */
function raka_loyalty_register_scheduled_actions(): void {
	if ( ! function_exists( 'as_has_scheduled_action' ) || ! function_exists( 'as_schedule_recurring_action' ) ) {
		return;
	}

	// 1. Daily Lot Expiration and Notification Check (Everyday at 03:00 UTC)
	if ( ! as_has_scheduled_action( 'raka_loyalty_daily_expire_lots' ) ) {
		as_schedule_recurring_action( strtotime( 'tomorrow 03:00:00' ), DAY_IN_SECONDS, 'raka_loyalty_daily_expire_lots', [], 'raka-loyalty' );
	}

	// 2. Daily Ledger Reconciliation Health Check (Everyday at 04:00 UTC)
	if ( ! as_has_scheduled_action( 'raka_loyalty_daily_reconcile_check' ) ) {
		as_schedule_recurring_action( strtotime( 'tomorrow 04:00:00' ), DAY_IN_SECONDS, 'raka_loyalty_daily_reconcile_check', [], 'raka-loyalty' );
	}

	// 3. Weekly Log Retention Cleanup (Every Sunday at 02:00 UTC)
	if ( ! as_has_scheduled_action( 'raka_loyalty_weekly_purge_logs' ) ) {
		as_schedule_recurring_action( strtotime( 'next sunday 02:00:00' ), WEEK_IN_SECONDS, 'raka_loyalty_weekly_purge_logs', [], 'raka-loyalty' );
	}
}

// Attach Action Scheduler hook handlers
add_action( 'raka_loyalty_weekly_purge_logs', function () {
	$options        = get_option( 'raka_theme_options', [] );
	$retention_days = (int) ( $options['loyalty']['log_retention_days'] ?? 90 );
	$purged         = Raka_Loyalty_Logger::purge_old_logs( $retention_days );
	Raka_Loyalty_Logger::info( 'CRON_PURGE_LOGS', "Purged {$purged} old system logs (retention: {$retention_days} days)", [ 'purged' => $purged ] );
} );
