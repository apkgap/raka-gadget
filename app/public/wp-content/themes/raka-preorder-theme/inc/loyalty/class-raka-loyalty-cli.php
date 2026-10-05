<?php
/**
 * WP-CLI Commands for Loyalty & Rewards Engine.
 *
 * @package Raka_Preorder_Theme
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Raka_Loyalty_CLI
 */
class Raka_Loyalty_CLI {

	/**
	 * Register CLI commands if WP-CLI is available.
	 */
	public static function init(): void {
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			WP_CLI::add_command( 'raka loyalty backfill', [ __CLASS__, 'backfill' ] );
			WP_CLI::add_command( 'raka loyalty reconcile', [ __CLASS__, 'reconcile' ] );
		}
	}

	/**
	 * Backfill loyalty points for existing completed orders.
	 *
	 * ## OPTIONS
	 *
	 * [--from=<date>]
	 * : Start date for orders in YYYY-MM-DD format. Default is 90 days ago.
	 *
	 * [--dry-run]
	 * : Simulate calculation without writing to database.
	 *
	 * [--batch-size=<int>]
	 * : Number of orders per batch. Default: 100.
	 *
	 * ## EXAMPLES
	 *
	 *     wp raka loyalty backfill --from=2026-01-01 --dry-run
	 *     wp raka loyalty backfill --from=2026-06-01
	 *
	 * @param array $args       Positional args.
	 * @param array $assoc_args Associative flags.
	 */
	public static function backfill( array $args, array $assoc_args ): void {
		$from_date  = $assoc_args['from'] ?? gmdate( 'Y-m-d', time() - ( 90 * DAY_IN_SECONDS ) );
		$is_dry_run = isset( $assoc_args['dry-run'] );
		$batch_size = (int) ( $assoc_args['batch-size'] ?? 100 );

		WP_CLI::log( sprintf( '==> Starting Loyalty Backfill from date: %s (Dry run: %s)', $from_date, $is_dry_run ? 'YES' : 'NO' ) );

		if ( ! function_exists( 'wc_get_orders' ) ) {
			WP_CLI::error( 'WooCommerce is not active.' );
			return;
		}

		$orders = wc_get_orders( [
			'status'        => 'completed',
			'date_created'  => '>=' . $from_date,
			'limit'         => $batch_size,
			'return'        => 'ids',
			'orderby'       => 'date',
			'order'         => 'ASC',
		] );

		if ( empty( $orders ) ) {
			WP_CLI::success( 'No completed orders found for backfill criteria.' );
			return;
		}

		$processed = 0;
		$skipped   = 0;
		$total_pts = 0.0;

		$progress = \WP_CLI\Utils\make_progress_bar( 'Processing Orders', count( $orders ) );

		foreach ( $orders as $order_id ) {
			$order   = wc_get_order( $order_id );
			$user_id = $order ? (int) $order->get_customer_id() : 0;

			if ( ! $order || 0 === $user_id ) {
				$skipped++;
				$progress->tick();
				continue;
			}

			// Check if already processed
			$idem_key = "earn:{$order_id}";
			global $wpdb;
			$table_ledger = $wpdb->prefix . 'raka_loyalty_ledger';
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table_ledger} WHERE idempotency_key = %s", $idem_key ) );

			if ( $exists ) {
				$skipped++;
				$progress->tick();
				continue;
			}

			if ( class_exists( 'Raka_Loyalty_Earn_Engine' ) && class_exists( 'Raka_Loyalty_Service' ) ) {
				$earn_result = Raka_Loyalty_Earn_Engine::calculate_order_points( $order );
				$points      = $earn_result['points'] ?? 0.0;

				if ( $points > 0 ) {
					if ( ! $is_dry_run ) {
						Raka_Loyalty_Service::earn( $order_id, $user_id, 'cli_backfill' );
					}
					$processed++;
					$total_pts += $points;
				} else {
					$skipped++;
				}
			}

			$progress->tick();
		}

		$progress->finish();

		WP_CLI::success( sprintf( 'Backfill Complete. Processed: %d orders (+%s pts), Skipped: %d', $processed, number_format( $total_pts, 2 ), $skipped ) );

		if ( ! $is_dry_run && class_exists( 'Raka_Loyalty_Audit' ) ) {
			Raka_Loyalty_Audit::log(
				'cli_backfill',
				'system',
				'all',
				[
					'from_date' => $from_date,
					'processed' => $processed,
					'total_pts' => $total_pts,
				],
				'Executed WP-CLI Backfill'
			);
		}
	}

	/**
	 * Reconcile Accounts materialized balance with Ledger aggregate totals.
	 *
	 * ## OPTIONS
	 *
	 * [--user_id=<int>]
	 * : Reconcile specific user ID only.
	 *
	 * [--fix]
	 * : Automatically fix and rebuild mismatched accounts balance from ledger.
	 *
	 * ## EXAMPLES
	 *
	 *     wp raka loyalty reconcile
	 *     wp raka loyalty reconcile --fix
	 *
	 * @param array $args       Positional args.
	 * @param array $assoc_args Associative flags.
	 */
	public static function reconcile( array $args, array $assoc_args ): void {
		$target_user = isset( $assoc_args['user_id'] ) ? (int) $assoc_args['user_id'] : 0;
		$fix_mode    = isset( $assoc_args['fix'] );

		WP_CLI::log( sprintf( '==> Running Loyalty Ledger Reconcile Check (Auto-Fix: %s)', $fix_mode ? 'YES' : 'NO' ) );

		global $wpdb;
		$table_accounts = $wpdb->prefix . 'raka_loyalty_accounts';
		$table_ledger   = $wpdb->prefix . 'raka_loyalty_ledger';

		$where = $target_user > 0 ? $wpdb->prepare( 'WHERE a.user_id = %d', $target_user ) : '';

		// Query comparing ledger sums vs accounts balance
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$results = $wpdb->get_results(
			"SELECT 
				a.user_id,
				a.balance AS cached_balance,
				COALESCE(SUM(l.amount), 0) AS ledger_sum,
				(a.balance - COALESCE(SUM(l.amount), 0)) AS delta
			FROM {$table_accounts} a
			LEFT JOIN {$table_ledger} l ON a.user_id = l.user_id
			{$where}
			GROUP BY a.user_id
			HAVING delta != 0"
		);

		if ( empty( $results ) ) {
			WP_CLI::success( 'All Loyalty Accounts match Ledger entries perfectly. (0 mismatches)' );
			return;
		}

		WP_CLI::warning( sprintf( 'Detected %d account(s) with Ledger mismatches!', count( $results ) ) );

		$table_format = [];
		foreach ( $results as $row ) {
			$table_format[] = [
				'User ID'        => $row->user_id,
				'Cached Balance' => $row->cached_balance,
				'Ledger Sum'     => $row->ledger_sum,
				'Discrepancy'    => $row->delta,
			];

			if ( $fix_mode ) {
				// Rebuild account row
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				$wpdb->update(
					$table_accounts,
					[
						'balance'    => (float) $row->ledger_sum,
						'version'    => time(),
						'updated_at' => current_time( 'mysql', true ),
					],
					[ 'user_id' => (int) $row->user_id ],
					[ '%f', '%d', '%s' ],
					[ '%d' ]
				);
			}
		}

		\WP_CLI\Utils\format_items( 'table', $table_format, [ 'User ID', 'Cached Balance', 'Ledger Sum', 'Discrepancy' ] );

		if ( $fix_mode ) {
			WP_CLI::success( sprintf( 'Successfully reconciled and fixed %d account(s).', count( $results ) ) );
			if ( class_exists( 'Raka_Loyalty_Audit' ) ) {
				Raka_Loyalty_Audit::log(
					'cli_reconcile_fix',
					'system',
					'accounts',
					[ 'fixed_count' => count( $results ) ],
					'Executed WP-CLI Reconcile with --fix'
				);
			}
		} else {
			WP_CLI::log( 'Run with `wp raka loyalty reconcile --fix` to update accounts balance cache from ledger.' );
		}
	}
}
