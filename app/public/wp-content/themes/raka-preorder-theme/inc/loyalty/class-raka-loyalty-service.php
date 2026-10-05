<?php
/**
 * Core Transactional Service Layer for Loyalty & Rewards Engine.
 *
 * @package Raka_Preorder_Theme
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Standard Result Value Object.
 */
class Raka_Loyalty_Result {
	public bool $success;
	public string $code;
	public string $message;
	public array $data;

	public function __construct( bool $success, string $code, string $message, array $data = [] ) {
		$this->success = $success;
		$this->code    = $code;
		$this->message = $message;
		$this->data    = $data;
	}

	public static function ok( string $code = 'SUCCESS', string $message = 'Operation successful', array $data = [] ): self {
		return new self( true, $code, $message, $data );
	}

	public static function fail( string $code, string $message, array $data = [] ): self {
		return new self( false, $code, $message, $data );
	}
}

/**
 * Class Raka_Loyalty_Service
 */
class Raka_Loyalty_Service {

	/**
	 * Get or initialize user loyalty account.
	 *
	 * @param int $user_id User ID.
	 * @return array Account data row.
	 */
	public static function get_account( int $user_id ): array {
		if ( $user_id <= 0 ) {
			return self::empty_account_data( 0 );
		}

		global $wpdb;
		$table = $wpdb->prefix . 'raka_loyalty_accounts';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE user_id = %d", $user_id ), ARRAY_A );

		if ( ! $row ) {
			// Initialize row
			$default_data = [
				'user_id'         => $user_id,
				'balance'         => 0.00,
				'reserved'        => 0.00,
				'lifetime_earned' => 0.00,
				'lifetime_spent'  => 0.00,
				'tier'            => 'bronze',
				'version'         => 1,
				'created_at'      => current_time( 'mysql', true ),
				'updated_at'      => current_time( 'mysql', true ),
			];
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$wpdb->insert( $table, $default_data );
			return $default_data;
		}

		return $row;
	}

	/**
	 * Get spendable points (balance - reserved).
	 */
	public static function get_available_points( int $user_id ): float {
		$account = self::get_account( $user_id );
		$balance  = (float) ( $account['balance'] ?? 0.0 );
		$reserved = (float) ( $account['reserved'] ?? 0.0 );
		return max( 0.0, $balance - $reserved );
	}

	/**
	 * EARN: Calculate and credit points for an order (Idempotent & Safe).
	 */
	public static function earn( int $order_id, int $user_id, string $channel = 'order_completed' ): Raka_Loyalty_Result {
		if ( $order_id <= 0 || $user_id <= 0 ) {
			return Raka_Loyalty_Result::fail( 'INVALID_PARAMS', 'Invalid order or user ID.' );
		}

		$idem_key = "earn:{$order_id}";
		global $wpdb;
		$t_ledger   = $wpdb->prefix . 'raka_loyalty_ledger';
		$t_accounts = $wpdb->prefix . 'raka_loyalty_accounts';
		$t_lots     = $wpdb->prefix . 'raka_loyalty_lots';

		// 1. Check Idempotency Key
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$existing = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$t_ledger} WHERE idempotency_key = %s", $idem_key ) );
		if ( $existing ) {
			Raka_Loyalty_Logger::warning( 'EARN_SKIPPED_DUPLICATE', "Skipped earn for order #{$order_id} (already processed)", [], $order_id, $user_id, 0, $channel );
			return Raka_Loyalty_Result::ok( 'EARN_SKIPPED_DUPLICATE', 'Points already awarded for this order.' );
		}

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return Raka_Loyalty_Result::fail( 'ORDER_NOT_FOUND', "Order #{$order_id} not found." );
		}

		$calculation = Raka_Loyalty_Earn_Engine::calculate_order_points( $order );
		$points      = (float) $calculation['points'];

		if ( $points <= 0 ) {
			Raka_Loyalty_Logger::info( 'EARN_ZERO_POINTS', "Order #{$order_id} yielded 0 qualifying points", $calculation['rule_snapshot'], $order_id, $user_id, 0, $channel );
			return Raka_Loyalty_Result::ok( 'EARN_ZERO_POINTS', 'No qualifying points for this order.' );
		}

		// 2. Transaction with Row Locking
		$wpdb->query( 'START TRANSACTION;' );

		try {
			// Lock account row
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$account = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$t_accounts} WHERE user_id = %d FOR UPDATE", $user_id ), ARRAY_A );
			if ( ! $account ) {
				self::get_account( $user_id );
				$account = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$t_accounts} WHERE user_id = %d FOR UPDATE", $user_id ), ARRAY_A );
			}

			$old_balance     = (float) $account['balance'];
			$new_balance     = $old_balance + $points;
			$lifetime_earned = (float) $account['lifetime_earned'] + $points;
			$request_id      = Raka_Loyalty_Logger::get_request_id();

			// Append Ledger
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$wpdb->insert(
				$t_ledger,
				[
					'user_id'         => $user_id,
					'event_type'      => 'earn',
					'amount'          => $points,
					'balance_after'   => $new_balance,
					'order_id'        => $order_id,
					'idempotency_key' => $idem_key,
					'rule_snapshot'   => wp_json_encode( $calculation['rule_snapshot'], JSON_UNESCAPED_UNICODE ),
					'actor_id'        => 0,
					'request_id'      => $request_id,
					'created_at'      => current_time( 'mysql', true ),
				],
				[ '%d', '%s', '%f', '%f', '%d', '%s', '%s', '%d', '%s', '%s' ]
			);
			$ledger_id = $wpdb->insert_id;

			// Append FIFO Lot
			$options       = get_option( 'raka_theme_options', [] );
			$expiry_months = (int) ( $options['loyalty']['expiration_months'] ?? 12 );
			$expires_at    = $expiry_months > 0
				? gmdate( 'Y-m-d 23:59:59', strtotime( "+{$expiry_months} months" ) )
				: '2099-12-31 23:59:59';

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$wpdb->insert(
				$t_lots,
				[
					'user_id'          => $user_id,
					'ledger_id'        => $ledger_id,
					'earned_amount'    => $points,
					'remaining_amount' => $points,
					'expires_at'       => $expires_at,
					'created_at'       => current_time( 'mysql', true ),
				],
				[ '%d', '%d', '%f', '%f', '%s', '%s' ]
			);

			// Recalculate Tier
			$new_tier = self::calculate_tier( $lifetime_earned, $options['loyalty'] ?? [] );

			// Update Accounts Cache
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$wpdb->update(
				$t_accounts,
				[
					'balance'         => $new_balance,
					'lifetime_earned' => $lifetime_earned,
					'tier'            => $new_tier,
					'version'         => (int) $account['version'] + 1,
					'updated_at'      => current_time( 'mysql', true ),
				],
				[ 'user_id' => $user_id ],
				[ '%f', '%f', '%s', '%d', '%s' ],
				[ '%d' ]
			);

			$wpdb->query( 'COMMIT;' );

			// Update Order Meta & Private Order Note
			$order->update_meta_data( '_raka_points_earned', $points );
			$order->add_order_note( sprintf( 'Loyalty: Earned %s points (Total balance: %s pts)', number_format( $points, 2 ), number_format( $new_balance, 2 ) ), false );
			$order->save();

			Raka_Loyalty_Logger::info( 'POINTS_EARNED', "Earned {$points} pts on Order #{$order_id}", $calculation['rule_snapshot'], $order_id, $user_id, 0, $channel );

			return Raka_Loyalty_Result::ok( 'POINTS_EARNED', "Successfully earned {$points} points.", [ 'points' => $points, 'balance' => $new_balance, 'tier' => $new_tier ] );
		} catch ( \Throwable $e ) {
			$wpdb->query( 'ROLLBACK;' );
			Raka_Loyalty_Logger::error( 'EARN_FAILED', "Failed to earn points for order #{$order_id}: " . $e->getMessage(), [ 'error' => $e->getMessage() ], $order_id, $user_id, 0, $channel );
			return Raka_Loyalty_Result::fail( 'EARN_FAILED', 'Database error during point earning.' );
		}
	}

	/**
	 * RESERVE: Lock points during checkout session (Idempotent).
	 */
	public static function reserve( int $user_id, int $order_id, float $cost = 100.0 ): Raka_Loyalty_Result {
		if ( $user_id <= 0 || $order_id <= 0 || $cost <= 0 ) {
			return Raka_Loyalty_Result::fail( 'INVALID_PARAMS', 'Invalid parameters for reservation.' );
		}

		$idem_key = "reserve:{$order_id}";
		global $wpdb;
		$t_ledger   = $wpdb->prefix . 'raka_loyalty_ledger';
		$t_accounts = $wpdb->prefix . 'raka_loyalty_accounts';

		// Idempotency check
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$existing = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$t_ledger} WHERE idempotency_key = %s", $idem_key ) );
		if ( $existing ) {
			return Raka_Loyalty_Result::ok( 'RESERVE_EXISTS', 'Points already reserved for this order.' );
		}

		$wpdb->query( 'START TRANSACTION;' );

		try {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$account = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$t_accounts} WHERE user_id = %d FOR UPDATE", $user_id ), ARRAY_A );
			if ( ! $account ) {
				$account = self::get_account( $user_id );
			}

			$available = (float) $account['balance'] - (float) $account['reserved'];
			if ( $available < $cost ) {
				$wpdb->query( 'ROLLBACK;' );
				Raka_Loyalty_Logger::warning( 'RESERVE_INSUFFICIENT_POINTS', "User #{$user_id} tried to reserve {$cost} pts with only {$available} available", [], $order_id, $user_id );
				return Raka_Loyalty_Result::fail( 'INSUFFICIENT_POINTS', 'คะแนนสะสมคงเหลือไม่เพียงพอสำหรับการแลก' );
			}

			$new_reserved = (float) $account['reserved'] + $cost;
			$request_id   = Raka_Loyalty_Logger::get_request_id();

			// Append Ledger
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$wpdb->insert(
				$t_ledger,
				[
					'user_id'         => $user_id,
					'event_type'      => 'reserve',
					'amount'          => -$cost,
					'balance_after'   => (float) $account['balance'],
					'order_id'        => $order_id,
					'idempotency_key' => $idem_key,
					'rule_snapshot'   => wp_json_encode( [ 'action' => 'reserve', 'cost' => $cost ] ),
					'actor_id'        => $user_id,
					'request_id'      => $request_id,
					'created_at'      => current_time( 'mysql', true ),
				],
				[ '%d', '%s', '%f', '%f', '%d', '%s', '%s', '%d', '%s', '%s' ]
			);

			// Update Accounts
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$wpdb->update(
				$t_accounts,
				[
					'reserved'   => $new_reserved,
					'version'    => (int) $account['version'] + 1,
					'updated_at' => current_time( 'mysql', true ),
				],
				[ 'user_id' => $user_id ],
				[ '%f', '%d', '%s' ],
				[ '%d' ]
			);

			$wpdb->query( 'COMMIT;' );

			$order = wc_get_order( $order_id );
			if ( $order ) {
				$order->update_meta_data( '_raka_points_reserved', $cost );
				$order->add_order_note( sprintf( 'Loyalty: Reserved %s points for reward item', number_format( $cost, 2 ) ), false );
				$order->save();
			}

			Raka_Loyalty_Logger::info( 'POINTS_RESERVED', "Reserved {$cost} pts on Order #{$order_id}", [ 'cost' => $cost ], $order_id, $user_id );

			return Raka_Loyalty_Result::ok( 'POINTS_RESERVED', 'Points reserved successfully.' );
		} catch ( \Throwable $e ) {
			$wpdb->query( 'ROLLBACK;' );
			Raka_Loyalty_Logger::error( 'RESERVE_FAILED', "Failed to reserve points on order #{$order_id}: " . $e->getMessage(), [], $order_id, $user_id );
			return Raka_Loyalty_Result::fail( 'RESERVE_FAILED', 'Error reserving points.' );
		}
	}

	/**
	 * COMMIT: Deduct reserved points permanently via FIFO Lots (Idempotent).
	 */
	public static function commit( int $order_id ): Raka_Loyalty_Result {
		$idem_key = "commit:{$order_id}";
		global $wpdb;
		$t_ledger   = $wpdb->prefix . 'raka_loyalty_ledger';
		$t_accounts = $wpdb->prefix . 'raka_loyalty_accounts';
		$t_lots     = $wpdb->prefix . 'raka_loyalty_lots';

		// Idempotency check
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$existing = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$t_ledger} WHERE idempotency_key = %s", $idem_key ) );
		if ( $existing ) {
			return Raka_Loyalty_Result::ok( 'COMMIT_EXISTS', 'Points already committed for this order.' );
		}

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return Raka_Loyalty_Result::fail( 'ORDER_NOT_FOUND', "Order #{$order_id} not found." );
		}

		$cost    = (float) $order->get_meta( '_raka_points_reserved' );
		$user_id = (int) $order->get_customer_id();

		if ( $cost <= 0 || $user_id <= 0 ) {
			return Raka_Loyalty_Result::ok( 'NO_RESERVE', 'No points reserved on this order.' );
		}

		$wpdb->query( 'START TRANSACTION;' );

		try {
			// Lock account
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$account = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$t_accounts} WHERE user_id = %d FOR UPDATE", $user_id ), ARRAY_A );
			if ( ! $account ) {
				$wpdb->query( 'ROLLBACK;' );
				return Raka_Loyalty_Result::fail( 'ACCOUNT_NOT_FOUND', 'Loyalty account not found.' );
			}

			// Deduct FIFO Lots
			$to_deduct = $cost;
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$lots = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$t_lots} WHERE user_id = %d AND remaining_amount > 0 ORDER BY expires_at ASC, id ASC FOR UPDATE", $user_id ), ARRAY_A );

			foreach ( $lots as $lot ) {
				if ( $to_deduct <= 0 ) {
					break;
				}
				$rem = (float) $lot['remaining_amount'];
				if ( $rem <= $to_deduct ) {
					$deduct_this = $rem;
					$new_rem     = 0.0;
				} else {
					$deduct_this = $to_deduct;
					$new_rem     = $rem - $to_deduct;
				}

				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				$wpdb->update( $t_lots, [ 'remaining_amount' => $new_rem ], [ 'id' => $lot['id'] ], [ '%f' ], [ '%d' ] );
				$to_deduct -= $deduct_this;
			}

			$new_balance  = max( 0.0, (float) $account['balance'] - $cost );
			$new_reserved = max( 0.0, (float) $account['reserved'] - $cost );
			$spent_total  = (float) $account['lifetime_spent'] + $cost;
			$request_id   = Raka_Loyalty_Logger::get_request_id();

			// Append Ledger
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$wpdb->insert(
				$t_ledger,
				[
					'user_id'         => $user_id,
					'event_type'      => 'commit',
					'amount'          => -$cost,
					'balance_after'   => $new_balance,
					'order_id'        => $order_id,
					'idempotency_key' => $idem_key,
					'rule_snapshot'   => wp_json_encode( [ 'action' => 'commit', 'cost' => $cost ] ),
					'actor_id'        => 0,
					'request_id'      => $request_id,
					'created_at'      => current_time( 'mysql', true ),
				],
				[ '%d', '%s', '%f', '%f', '%d', '%s', '%s', '%d', '%s', '%s' ]
			);

			// Update Accounts
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$wpdb->update(
				$t_accounts,
				[
					'balance'        => $new_balance,
					'reserved'       => $new_reserved,
					'lifetime_spent' => $spent_total,
					'version'        => (int) $account['version'] + 1,
					'updated_at'     => current_time( 'mysql', true ),
				],
				[ 'user_id' => $user_id ],
				[ '%f', '%f', '%f', '%d', '%s' ],
				[ '%d' ]
			);

			$wpdb->query( 'COMMIT;' );

			$order->update_meta_data( '_raka_points_committed', $cost );
			$order->delete_meta_data( '_raka_points_reserved' );
			$order->add_order_note( sprintf( 'Loyalty: Committed and deducted %s points for reward', number_format( $cost, 2 ) ), false );
			$order->save();

			Raka_Loyalty_Logger::info( 'POINTS_COMMITTED', "Committed {$cost} pts on Order #{$order_id}", [ 'cost' => $cost, 'balance' => $new_balance ], $order_id, $user_id );

			return Raka_Loyalty_Result::ok( 'POINTS_COMMITTED', 'Points committed successfully.' );
		} catch ( \Throwable $e ) {
			$wpdb->query( 'ROLLBACK;' );
			Raka_Loyalty_Logger::error( 'COMMIT_FAILED', "Failed to commit points on order #{$order_id}: " . $e->getMessage(), [], $order_id, $user_id );
			return Raka_Loyalty_Result::fail( 'COMMIT_FAILED', 'Error committing points.' );
		}
	}

	/**
	 * RELEASE: Unlock reserved points on failed / cancelled order (Idempotent).
	 */
	public static function release( int $order_id, string $reason = 'order_cancelled' ): Raka_Loyalty_Result {
		$idem_key = "release:{$order_id}";
		global $wpdb;
		$t_ledger   = $wpdb->prefix . 'raka_loyalty_ledger';
		$t_accounts = $wpdb->prefix . 'raka_loyalty_accounts';

		// Idempotency check
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$existing = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$t_ledger} WHERE idempotency_key = %s", $idem_key ) );
		if ( $existing ) {
			return Raka_Loyalty_Result::ok( 'RELEASE_EXISTS', 'Reserved points already released.' );
		}

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return Raka_Loyalty_Result::fail( 'ORDER_NOT_FOUND', "Order #{$order_id} not found." );
		}

		$cost    = (float) $order->get_meta( '_raka_points_reserved' );
		$user_id = (int) $order->get_customer_id();

		if ( $cost <= 0 || $user_id <= 0 ) {
			return Raka_Loyalty_Result::ok( 'NO_RESERVE', 'No points reserved to release.' );
		}

		$wpdb->query( 'START TRANSACTION;' );

		try {
			// Lock account
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$account = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$t_accounts} WHERE user_id = %d FOR UPDATE", $user_id ), ARRAY_A );
			if ( ! $account ) {
				$wpdb->query( 'ROLLBACK;' );
				return Raka_Loyalty_Result::fail( 'ACCOUNT_NOT_FOUND', 'Account not found.' );
			}

			$new_reserved = max( 0.0, (float) $account['reserved'] - $cost );
			$request_id   = Raka_Loyalty_Logger::get_request_id();

			// Append Ledger
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$wpdb->insert(
				$t_ledger,
				[
					'user_id'         => $user_id,
					'event_type'      => 'release',
					'amount'          => $cost,
					'balance_after'   => (float) $account['balance'],
					'order_id'        => $order_id,
					'idempotency_key' => $idem_key,
					'rule_snapshot'   => wp_json_encode( [ 'action' => 'release', 'reason' => $reason, 'cost' => $cost ] ),
					'actor_id'        => 0,
					'request_id'      => $request_id,
					'created_at'      => current_time( 'mysql', true ),
				],
				[ '%d', '%s', '%f', '%f', '%d', '%s', '%s', '%d', '%s', '%s' ]
			);

			// Update Accounts
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$wpdb->update(
				$t_accounts,
				[
					'reserved'   => $new_reserved,
					'version'    => (int) $account['version'] + 1,
					'updated_at' => current_time( 'mysql', true ),
				],
				[ 'user_id' => $user_id ],
				[ '%f', '%d', '%s' ],
				[ '%d' ]
			);

			$wpdb->query( 'COMMIT;' );

			$order->delete_meta_data( '_raka_points_reserved' );
			$order->add_order_note( sprintf( 'Loyalty: Released %s reserved points (%s)', number_format( $cost, 2 ), $reason ), false );
			$order->save();

			Raka_Loyalty_Logger::info( 'POINTS_RELEASED', "Released {$cost} reserved pts on Order #{$order_id}", [ 'reason' => $reason ], $order_id, $user_id );

			return Raka_Loyalty_Result::ok( 'POINTS_RELEASED', 'Reserved points released.' );
		} catch ( \Throwable $e ) {
			$wpdb->query( 'ROLLBACK;' );
			Raka_Loyalty_Logger::error( 'RELEASE_FAILED', "Failed to release points on order #{$order_id}: " . $e->getMessage(), [], $order_id, $user_id );
			return Raka_Loyalty_Result::fail( 'RELEASE_FAILED', 'Error releasing reserved points.' );
		}
	}

	/**
	 * REFUND / CLAWBACK: Reclaim earned points on partial or full order refund (Idempotent).
	 */
	public static function handle_refund( int $order_id, float $refund_amount, int $refund_id = 0 ): Raka_Loyalty_Result {
		$idem_key = "clawback:{$order_id}:{$refund_id}";
		global $wpdb;
		$t_ledger   = $wpdb->prefix . 'raka_loyalty_ledger';
		$t_accounts = $wpdb->prefix . 'raka_loyalty_accounts';

		// Idempotency check
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$existing = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$t_ledger} WHERE idempotency_key = %s", $idem_key ) );
		if ( $existing ) {
			return Raka_Loyalty_Result::ok( 'CLAWBACK_EXISTS', 'Clawback already handled for this refund event.' );
		}

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return Raka_Loyalty_Result::fail( 'ORDER_NOT_FOUND', "Order #{$order_id} not found." );
		}

		$earned_points = (float) $order->get_meta( '_raka_points_earned' );
		$user_id       = (int) $order->get_customer_id();

		if ( $earned_points <= 0 || $user_id <= 0 ) {
			return Raka_Loyalty_Result::ok( 'NO_POINTS_TO_CLAWBACK', 'No earned points to clawback on this order.' );
		}

		// Calculate proportional clawback
		$order_total = (float) $order->get_total();
		$ratio       = $order_total > 0 ? min( 1.0, $refund_amount / $order_total ) : 1.0;
		$claw_points = round( $earned_points * $ratio, 2 );

		if ( $claw_points <= 0 ) {
			return Raka_Loyalty_Result::ok( 'ZERO_CLAWBACK', 'Zero clawback calculated.' );
		}

		$options             = get_option( 'raka_theme_options', [] );
		$allow_negative_debt = ! empty( $options['loyalty']['allow_negative_balance'] );

		$wpdb->query( 'START TRANSACTION;' );

		try {
			// Lock account
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$account = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$t_accounts} WHERE user_id = %d FOR UPDATE", $user_id ), ARRAY_A );
			if ( ! $account ) {
				$account = self::get_account( $user_id );
			}

			$curr_balance = (float) $account['balance'];
			$new_balance  = $curr_balance - $claw_points;

			if ( ! $allow_negative_debt && $new_balance < 0 ) {
				$unrecovered_debt = abs( $new_balance );
				$new_balance      = 0.00;
				Raka_Loyalty_Logger::warning( 'UNRECOVERED_DEBT_FLOORED', "User #{$user_id} has unrecovered point debt: {$unrecovered_debt} pts (Floored at 0)", [ 'debt' => $unrecovered_debt ], $order_id, $user_id );
			}

			$lifetime_earned = max( 0.0, (float) $account['lifetime_earned'] - $claw_points );
			$request_id      = Raka_Loyalty_Logger::get_request_id();

			// Append Ledger
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$wpdb->insert(
				$t_ledger,
				[
					'user_id'         => $user_id,
					'event_type'      => 'clawback',
					'amount'          => -$claw_points,
					'balance_after'   => $new_balance,
					'order_id'        => $order_id,
					'idempotency_key' => $idem_key,
					'rule_snapshot'   => wp_json_encode( [ 'action' => 'clawback', 'refund_amount' => $refund_amount, 'claw_points' => $claw_points ] ),
					'actor_id'        => get_current_user_id(),
					'request_id'      => $request_id,
					'created_at'      => current_time( 'mysql', true ),
				],
				[ '%d', '%s', '%f', '%f', '%d', '%s', '%s', '%d', '%s', '%s' ]
			);

			// Recalculate Tier
			$new_tier = self::calculate_tier( $lifetime_earned, $options['loyalty'] ?? [] );

			// Update Accounts
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$wpdb->update(
				$t_accounts,
				[
					'balance'         => $new_balance,
					'lifetime_earned' => $lifetime_earned,
					'tier'            => $new_tier,
					'version'         => (int) $account['version'] + 1,
					'updated_at'      => current_time( 'mysql', true ),
				],
				[ 'user_id' => $user_id ],
				[ '%f', '%f', '%s', '%d', '%s' ],
				[ '%d' ]
			);

			$wpdb->query( 'COMMIT;' );

			$order->add_order_note( sprintf( 'Loyalty: Clawback -%s points due to refund (Balance: %s pts)', number_format( $claw_points, 2 ), number_format( $new_balance, 2 ) ), false );
			$order->save();

			Raka_Loyalty_Logger::info( 'POINTS_CLAWBACK', "Clawback {$claw_points} pts on Order #{$order_id} refund", [ 'refund_amount' => $refund_amount, 'claw_points' => $claw_points ], $order_id, $user_id );

			return Raka_Loyalty_Result::ok( 'POINTS_CLAWBACK', "Clawback {$claw_points} points." );
		} catch ( \Throwable $e ) {
			$wpdb->query( 'ROLLBACK;' );
			Raka_Loyalty_Logger::error( 'CLAWBACK_FAILED', "Failed clawback on order #{$order_id}: " . $e->getMessage(), [], $order_id, $user_id );
			return Raka_Loyalty_Result::fail( 'CLAWBACK_FAILED', 'Error during refund clawback.' );
		}
	}

	/**
	 * ADMIN ADJUST: Manually adjust points with audit trail.
	 */
	public static function admin_adjust( int $user_id, float $amount, string $reason, int $actor_id = 0 ): Raka_Loyalty_Result {
		if ( $user_id <= 0 || 0.0 === $amount || empty( trim( $reason ) ) ) {
			return Raka_Loyalty_Result::fail( 'INVALID_PARAMS', 'Invalid user, zero amount, or missing mandatory reason.' );
		}

		if ( 0 === $actor_id ) {
			$actor_id = get_current_user_id();
		}

		$idem_key = 'admin_adjust:' . $user_id . ':' . microtime( true );
		global $wpdb;
		$t_ledger   = $wpdb->prefix . 'raka_loyalty_ledger';
		$t_accounts = $wpdb->prefix . 'raka_loyalty_accounts';
		$t_lots     = $wpdb->prefix . 'raka_loyalty_lots';

		$wpdb->query( 'START TRANSACTION;' );

		try {
			// Lock account
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$account = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$t_accounts} WHERE user_id = %d FOR UPDATE", $user_id ), ARRAY_A );
			if ( ! $account ) {
				$account = self::get_account( $user_id );
			}

			$old_balance = (float) $account['balance'];
			$new_balance = max( 0.0, $old_balance + $amount );
			$request_id  = Raka_Loyalty_Logger::get_request_id();

			// Append Ledger
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$wpdb->insert(
				$t_ledger,
				[
					'user_id'         => $user_id,
					'event_type'      => 'admin_adjust',
					'amount'          => $amount,
					'balance_after'   => $new_balance,
					'order_id'        => 0,
					'idempotency_key' => $idem_key,
					'rule_snapshot'   => wp_json_encode( [ 'action' => 'admin_adjust', 'reason' => $reason, 'delta' => $amount ] ),
					'actor_id'        => $actor_id,
					'request_id'      => $request_id,
					'created_at'      => current_time( 'mysql', true ),
				],
				[ '%d', '%s', '%f', '%f', '%d', '%s', '%s', '%d', '%s', '%s' ]
			);
			$ledger_id = $wpdb->insert_id;

			// If adding points, create a new lot
			if ( $amount > 0 ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				$wpdb->insert(
					$t_lots,
					[
						'user_id'          => $user_id,
						'ledger_id'        => $ledger_id,
						'earned_amount'    => $amount,
						'remaining_amount' => $amount,
						'expires_at'       => gmdate( 'Y-m-d 23:59:59', strtotime( '+12 months' ) ),
						'created_at'       => current_time( 'mysql', true ),
					],
					[ '%d', '%d', '%f', '%f', '%s', '%s' ]
				);
			}

			$lifetime_earned = (float) $account['lifetime_earned'] + ( $amount > 0 ? $amount : 0 );

			// Update Accounts
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$wpdb->update(
				$t_accounts,
				[
					'balance'         => $new_balance,
					'lifetime_earned' => $lifetime_earned,
					'version'         => (int) $account['version'] + 1,
					'updated_at'      => current_time( 'mysql', true ),
				],
				[ 'user_id' => $user_id ],
				[ '%f', '%f', '%d', '%s' ],
				[ '%d' ]
			);

			$wpdb->query( 'COMMIT;' );

			// Write to Admin Audit Log
			if ( class_exists( 'Raka_Loyalty_Audit' ) ) {
				Raka_Loyalty_Audit::log(
					'adjust_points',
					'user',
					(string) $user_id,
					[
						'before' => $old_balance,
						'after'  => $new_balance,
						'delta'  => $amount,
					],
					$reason,
					$actor_id
				);
			}

			Raka_Loyalty_Logger::info( 'ADMIN_ADJUST', "Admin #{$actor_id} adjusted user #{$user_id} points by {$amount} pts", [ 'reason' => $reason, 'delta' => $amount ], 0, $user_id, $actor_id, 'admin' );

			return Raka_Loyalty_Result::ok( 'ADMIN_ADJUST', 'Points adjusted successfully.', [ 'balance' => $new_balance ] );
		} catch ( \Throwable $e ) {
			$wpdb->query( 'ROLLBACK;' );
			Raka_Loyalty_Logger::error( 'ADMIN_ADJUST_FAILED', "Failed admin adjust: " . $e->getMessage(), [], 0, $user_id, $actor_id, 'admin' );
			return Raka_Loyalty_Result::fail( 'ADMIN_ADJUST_FAILED', 'Error adjusting points.' );
		}
	}

	/**
	 * EXPIRE LOTS: Deduct expired points daily via cron.
	 */
	public static function expire_lots(): int {
		global $wpdb;
		$t_lots     = $wpdb->prefix . 'raka_loyalty_lots';
		$t_ledger   = $wpdb->prefix . 'raka_loyalty_ledger';
		$t_accounts = $wpdb->prefix . 'raka_loyalty_accounts';

		$now = current_time( 'mysql', true );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$expired_lots = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$t_lots} WHERE remaining_amount > 0 AND expires_at < %s LIMIT 500", $now ), ARRAY_A );

		if ( empty( $expired_lots ) ) {
			return 0;
		}

		$expired_count = 0;

		foreach ( $expired_lots as $lot ) {
			$user_id = (int) $lot['user_id'];
			$rem     = (float) $lot['remaining_amount'];
			$lot_id  = (int) $lot['id'];

			$wpdb->query( 'START TRANSACTION;' );

			try {
				// Lock account
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$account = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$t_accounts} WHERE user_id = %d FOR UPDATE", $user_id ), ARRAY_A );
				if ( ! $account ) {
					$wpdb->query( 'ROLLBACK;' );
					continue;
				}

				$new_balance = max( 0.0, (float) $account['balance'] - $rem );
				$idem_key    = "expire:lot:{$lot_id}";
				$request_id  = Raka_Loyalty_Logger::get_request_id();

				// Append Ledger
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				$wpdb->insert(
					$t_ledger,
					[
						'user_id'         => $user_id,
						'event_type'      => 'expire',
						'amount'          => -$rem,
						'balance_after'   => $new_balance,
						'order_id'        => 0,
						'idempotency_key' => $idem_key,
						'rule_snapshot'   => wp_json_encode( [ 'action' => 'expire', 'lot_id' => $lot_id, 'expired_points' => $rem ] ),
						'actor_id'        => 0,
						'request_id'      => $request_id,
						'created_at'      => current_time( 'mysql', true ),
					],
					[ '%d', '%s', '%f', '%f', '%d', '%s', '%s', '%d', '%s', '%s' ]
				);

				// Update Lot to 0
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				$wpdb->update( $t_lots, [ 'remaining_amount' => 0 ], [ 'id' => $lot_id ], [ '%f' ], [ '%d' ] );

				// Update Account
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				$wpdb->update(
					$t_accounts,
					[
						'balance'    => $new_balance,
						'version'    => (int) $account['version'] + 1,
						'updated_at' => current_time( 'mysql', true ),
					],
					[ 'user_id' => $user_id ],
					[ '%f', '%d', '%s' ],
					[ '%d' ]
				);

				$wpdb->query( 'COMMIT;' );
				$expired_count++;
			} catch ( \Throwable $e ) {
				$wpdb->query( 'ROLLBACK;' );
				Raka_Loyalty_Logger::error( 'EXPIRE_LOT_FAILED', "Failed to expire lot #{$lot_id}: " . $e->getMessage(), [], 0, $user_id );
			}
		}

		Raka_Loyalty_Logger::info( 'CRON_EXPIRE_LOTS', "Expired {$expired_count} point lots", [ 'expired_count' => $expired_count ], 0, 0, 0, 'cron' );

		return $expired_count;
	}

	/**
	 * Calculate Tier based on lifetime earned points.
	 */
	public static function calculate_tier( float $lifetime_earned, array $loyalty_options ): string {
		$silver_thresh = (float) ( $loyalty_options['tier_threshold_silver'] ?? 500.0 );
		$gold_thresh   = (float) ( $loyalty_options['tier_threshold_gold'] ?? 2000.0 );

		if ( $lifetime_earned >= $gold_thresh ) {
			return 'gold';
		}
		if ( $lifetime_earned >= $silver_thresh ) {
			return 'silver';
		}
		return 'bronze';
	}

	/**
	 * Fallback default empty account array.
	 */
	private static function empty_account_data( int $user_id ): array {
		return [
			'user_id'         => $user_id,
			'balance'         => 0.00,
			'reserved'        => 0.00,
			'lifetime_earned' => 0.00,
			'lifetime_spent'  => 0.00,
			'tier'            => 'bronze',
			'version'         => 1,
		];
	}
}
