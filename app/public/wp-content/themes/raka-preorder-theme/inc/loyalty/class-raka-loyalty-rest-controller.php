<?php
/**
 * REST API Controller for Loyalty & Rewards Engine.
 * Namespace: account/v1
 *
 * @package Raka_Preorder_Theme
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Raka_Loyalty_REST_Controller
 */
class Raka_Loyalty_REST_Controller {

	/**
	 * Route namespace.
	 */
	public const NAMESPACE = 'account/v1';

	/**
	 * Register all Loyalty REST routes.
	 */
	public static function register_routes(): void {
		// ── 1. Customer Endpoints ──
		// GET /account/v1/rewards/overview
		register_rest_route(
			self::NAMESPACE,
			'/rewards/overview',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ __CLASS__, 'get_rewards_overview' ],
				'permission_callback' => [ __CLASS__, 'check_logged_in_permission' ],
			]
		);

		// POST /account/v1/cart/apply-reward
		register_rest_route(
			self::NAMESPACE,
			'/cart/apply-reward',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ __CLASS__, 'apply_reward_to_cart' ],
				'permission_callback' => [ __CLASS__, 'check_logged_in_permission' ],
				'args'                => [
					'product_id' => [
						'type'              => 'integer',
						'required'          => true,
						'validate_callback' => function ( $param ) {
							return is_numeric( $param ) && $param > 0;
						},
					],
				],
			]
		);

		// POST /account/v1/cart/remove-reward
		register_rest_route(
			self::NAMESPACE,
			'/cart/remove-reward',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ __CLASS__, 'remove_reward_from_cart' ],
				'permission_callback' => [ __CLASS__, 'check_logged_in_permission' ],
			]
		);

		// ── 2. Administrator Endpoints ──
		// GET /account/v1/admin/loyalty/users
		register_rest_route(
			self::NAMESPACE,
			'/admin/loyalty/users',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ __CLASS__, 'admin_get_users' ],
				'permission_callback' => [ __CLASS__, 'check_admin_permission' ],
			]
		);

		// GET /account/v1/admin/loyalty/users/(?P<id>\d+)/ledger
		register_rest_route(
			self::NAMESPACE,
			'/admin/loyalty/users/(?P<id>\d+)/ledger',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ __CLASS__, 'admin_get_user_ledger' ],
				'permission_callback' => [ __CLASS__, 'check_admin_permission' ],
			]
		);

		// POST /account/v1/admin/loyalty/adjust-points
		register_rest_route(
			self::NAMESPACE,
			'/admin/loyalty/adjust-points',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ __CLASS__, 'admin_adjust_points' ],
				'permission_callback' => [ __CLASS__, 'check_admin_permission' ],
				'args'                => [
					'user_id' => [
						'type'     => 'integer',
						'required' => true,
					],
					'amount'  => [
						'type'     => 'number',
						'required' => true,
					],
					'reason'  => [
						'type'              => 'string',
						'required'          => true,
						'sanitize_callback' => 'sanitize_textarea_field',
					],
				],
			]
		);

		// GET /account/v1/admin/loyalty/health
		register_rest_route(
			self::NAMESPACE,
			'/admin/loyalty/health',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ __CLASS__, 'admin_get_health' ],
				'permission_callback' => [ __CLASS__, 'check_admin_permission' ],
			]
		);

		// GET /account/v1/admin/loyalty/logs
		register_rest_route(
			self::NAMESPACE,
			'/admin/loyalty/logs',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ __CLASS__, 'admin_get_logs' ],
				'permission_callback' => [ __CLASS__, 'check_admin_permission' ],
			]
		);

		// GET /account/v1/admin/loyalty/audit
		register_rest_route(
			self::NAMESPACE,
			'/admin/loyalty/audit',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ __CLASS__, 'admin_get_audit_logs' ],
				'permission_callback' => [ __CLASS__, 'check_admin_permission' ],
			]
		);

		// POST /account/v1/admin/loyalty/reconcile
		register_rest_route(
			self::NAMESPACE,
			'/admin/loyalty/reconcile',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ __CLASS__, 'admin_trigger_reconcile' ],
				'permission_callback' => [ __CLASS__, 'check_admin_permission' ],
			]
		);

		// POST /account/v1/admin/loyalty/logs/purge
		register_rest_route(
			self::NAMESPACE,
			'/admin/loyalty/logs/purge',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ __CLASS__, 'admin_purge_logs' ],
				'permission_callback' => [ __CLASS__, 'check_admin_permission' ],
			]
		);
	}

	/**
	 * Permission check: Logged in customer.
	 */
	public static function check_logged_in_permission(): bool {
		return is_user_logged_in();
	}

	/**
	 * Permission check: Admin with manage_options or manage_woocommerce.
	 */
	public static function check_admin_permission(): bool {
		return current_user_can( 'manage_woocommerce' ) || current_user_can( 'manage_options' );
	}

	/**
	 * GET /rewards/overview
	 */
	public static function get_rewards_overview( WP_REST_Request $request ): WP_REST_Response {
		$user_id = get_current_user_id();
		$account = Raka_Loyalty_Service::get_account( $user_id );

		$balance   = (float) $account['balance'];
		$reserved  = (float) $account['reserved'];
		$available = max( 0.0, $balance - $reserved );
		$tier      = (string) $account['tier'];

		$options       = get_option( 'raka_theme_options', [] );
		$loyalty_opts  = $options['loyalty'] ?? [];
		$silver_thresh = (float) ( $loyalty_opts['tier_threshold_silver'] ?? 500.0 );
		$gold_thresh   = (float) ( $loyalty_opts['tier_threshold_gold'] ?? 2000.0 );
		$lifetime      = (float) $account['lifetime_earned'];

		// Calculate tier progress
		$next_tier          = 'silver';
		$next_tier_progress = 0;
		$points_to_next     = 0;

		if ( 'gold' === $tier ) {
			$next_tier          = 'max';
			$next_tier_progress = 100;
			$points_to_next     = 0;
		} elseif ( 'silver' === $tier ) {
			$next_tier          = 'gold';
			$diff               = $gold_thresh - $silver_thresh;
			$progress_val       = $diff > 0 ? ( ( $lifetime - $silver_thresh ) / $diff ) * 100 : 100;
			$next_tier_progress = min( 100, max( 0, (int) round( $progress_val ) ) );
			$points_to_next     = max( 0.0, $gold_thresh - $lifetime );
		} else {
			$next_tier          = 'silver';
			$next_tier_progress = min( 100, max( 0, (int) round( ( $lifetime / max( 1.0, $silver_thresh ) ) * 100 ) ) );
			$points_to_next     = max( 0.0, $silver_thresh - $lifetime );
		}

		global $wpdb;
		$t_lots   = $wpdb->prefix . 'raka_loyalty_lots';
		$t_ledger = $wpdb->prefix . 'raka_loyalty_ledger';

		// Expiring lots within 30 days
		$thirty_days_later = gmdate( 'Y-m-d 23:59:59', time() + ( 30 * DAY_IN_SECONDS ) );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$expiring_rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT remaining_amount, expires_at FROM {$t_lots} WHERE user_id = %d AND remaining_amount > 0 AND expires_at <= %s ORDER BY expires_at ASC",
				$user_id,
				$thirty_days_later
			),
			ARRAY_A
		);

		$expiring_total = 0.0;
		$next_expiry    = null;
		foreach ( $expiring_rows as $row ) {
			$expiring_total += (float) $row['remaining_amount'];
			if ( ! $next_expiry ) {
				$next_expiry = date_i18n( 'd M Y', strtotime( $row['expires_at'] ) );
			}
		}

		// Reward Catalog from Options or Table
		$catalog_items = self::get_user_catalog_items( $user_id, $available, $tier );

		// Cursor / Paged Ledger History
		$filter_type = sanitize_key( $request->get_param( 'type' ) ?? 'all' );
		$cursor      = (int) ( $request->get_param( 'cursor' ) ?? 0 );
		$limit       = min( 20, max( 5, (int) ( $request->get_param( 'limit' ) ?? 10 ) ) );

		$where_clauses = [ $wpdb->prepare( 'user_id = %d', $user_id ) ];
		if ( 'all' !== $filter_type && ! empty( $filter_type ) ) {
			$where_clauses[] = $wpdb->prepare( 'event_type = %s', $filter_type );
		}
		if ( $cursor > 0 ) {
			$where_clauses[] = $wpdb->prepare( 'id < %d', $cursor );
		}
		$where_sql = implode( ' AND ', $where_clauses );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$history_rows = $wpdb->get_results(
			"SELECT id, event_type, amount, balance_after, order_id, created_at FROM {$t_ledger} WHERE {$where_sql} ORDER BY id DESC LIMIT " . ( $limit + 1 ),
			ARRAY_A
		);

		$has_more    = count( $history_rows ) > $limit;
		$history_set = array_slice( $history_rows, 0, $limit );
		$next_cursor = $has_more && ! empty( $history_set ) ? (int) end( $history_set )['id'] : null;

		$formatted_history = array_map( function ( $h ) {
			return [
				'id'            => (int) $h['id'],
				'type'          => $h['event_type'],
				'amount'        => (float) $h['amount'],
				'balance_after' => (float) $h['balance_after'],
				'order_id'      => (int) $h['order_id'],
				'date'          => date_i18n( 'd M Y, H:i น.', strtotime( $h['created_at'] ) ),
			];
		}, $history_set );

		$response_data = [
			'account' => [
				'balance'            => $balance,
				'available'          => $available,
				'reserved'           => $reserved,
				'lifetime_earned'    => $lifetime,
				'lifetime_spent'     => (float) $account['lifetime_spent'],
				'tier'               => $tier,
				'tier_name'          => ucfirst( $tier ),
				'next_tier'          => $next_tier,
				'next_tier_progress' => $next_tier_progress,
				'points_to_next'     => $points_to_next,
				'next_100_progress'  => (int) round( fmod( $available, 100.0 ) ),
			],
			'expiring' => [
				'amount' => $expiring_total,
				'date'   => $next_expiry,
			],
			'catalog' => $catalog_items,
			'history' => [
				'items'       => $formatted_history,
				'has_more'    => $has_more,
				'next_cursor' => $next_cursor,
			],
			'rules_info' => [
				'spend_per_point'     => (float) ( $loyalty_opts['spend_per_point'] ?? 100.0 ),
				'redeem_cost'         => 100.0,
				'preorder_multiplier' => (float) ( $loyalty_opts['preorder_points_multiplier'] ?? 1.0 ),
			],
		];

		$res = new WP_REST_Response( $response_data, 200 );
		$res->header( 'Cache-Control', 'private, no-cache, no-store, must-revalidate' );
		return $res;
	}

	/**
	 * Helper to get available catalog items with eligibility.
	 */
	private static function get_user_catalog_items( int $user_id, float $available, string $tier ): array {
		$options      = get_option( 'raka_theme_options', [] );
		$loyalty_opts = $options['loyalty'] ?? [];
		$product_ids  = (array) ( $loyalty_opts['reward_product_ids'] ?? [] );

		$items = [];
		foreach ( $product_ids as $pid ) {
			$pid = absint( $pid );
			if ( $pid <= 0 ) {
				continue;
			}
			$product = wc_get_product( $pid );
			if ( ! $product || ! $product->is_in_stock() ) {
				continue;
			}

			$cost = 100.0;
			$is_redeemable = $available >= $cost;
			$reason        = $is_redeemable ? '' : sprintf( 'ต้องการอีก %s แต้ม', number_format( $cost - $available, 0 ) );

			$img_url = wp_get_attachment_image_url( $product->get_image_id(), 'medium' );
			if ( ! $img_url ) {
				$img_url = wc_placeholder_img_src( 'medium' );
			}

			$items[] = [
				'id'          => $pid,
				'name'        => $product->get_name(),
				'price_raw'   => $product->get_price(),
				'price_label' => wc_price( $product->get_price() ),
				'cost'        => $cost,
				'image_url'   => $img_url,
				'is_in_stock' => $product->is_in_stock(),
				'redeemable'  => $is_redeemable,
				'reason'      => $reason,
			];
		}

		return $items;
	}

	/**
	 * POST /cart/apply-reward
	 */
	public static function apply_reward_to_cart( WP_REST_Request $request ): WP_REST_Response {
		$user_id    = get_current_user_id();
		$product_id = (int) $request->get_param( 'product_id' );

		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			return new WP_REST_Response( [ 'success' => false, 'message' => 'Cart not initialized' ], 400 );
		}

		$available = Raka_Loyalty_Service::get_available_points( $user_id );
		$cost      = 100.0;

		if ( $available < $cost ) {
			return new WP_REST_Response( [
				'success' => false,
				'message' => 'คะแนนสะสมของคุณไม่เพียงพอสำหรับการแลกของรางวัลนี้ (ต้องการ 100 แต้ม)',
			], 400 );
		}

		// Remove existing reward item first
		foreach ( WC()->cart->get_cart() as $cart_key => $item ) {
			if ( ! empty( $item['_raka_is_reward'] ) ) {
				WC()->cart->remove_cart_item( $cart_key );
			}
		}

		// Generate tamper-proof signature
		$sig = Raka_Loyalty_Hooks::generate_reward_signature( $product_id, $user_id );

		$cart_item_data = [
			'_raka_is_reward'   => true,
			'_raka_reward_cost' => $cost,
			'_raka_reward_sig'  => $sig,
		];

		$added = WC()->cart->add_to_cart( $product_id, 1, 0, [], $cart_item_data );

		if ( ! $added ) {
			return new WP_REST_Response( [
				'success' => false,
				'message' => 'ไม่สามารถเพิ่มของรางวัลลงในตะกร้าสินค้าได้',
			], 400 );
		}

		WC()->cart->calculate_totals();

		return new WP_REST_Response( [
			'success'    => true,
			'message'    => 'แลกของรางวัลลงในตะกร้าสินค้าเรียบร้อยแล้ว (มูลค่า 0 บาท)',
			'cart_total' => WC()->cart->get_cart_total(),
			'cart_count' => WC()->cart->get_cart_contents_count(),
		], 200 );
	}

	/**
	 * POST /cart/remove-reward
	 */
	public static function remove_reward_from_cart( WP_REST_Request $request ): WP_REST_Response {
		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			return new WP_REST_Response( [ 'success' => false, 'message' => 'Cart not available' ], 400 );
		}

		$removed = false;
		foreach ( WC()->cart->get_cart() as $cart_key => $item ) {
			if ( ! empty( $item['_raka_is_reward'] ) ) {
				WC()->cart->remove_cart_item( $cart_key );
				$removed = true;
			}
		}

		WC()->cart->calculate_totals();

		return new WP_REST_Response( [
			'success'    => true,
			'message'    => $removed ? 'นำของรางวัลออกจากตะกร้าสินค้าแล้ว' : 'ไม่พบของรางวัลในตะกร้า',
			'cart_total' => WC()->cart->get_cart_total(),
		], 200 );
	}

	/**
	 * GET /admin/loyalty/users
	 */
	public static function admin_get_users( WP_REST_Request $request ): WP_REST_Response {
		global $wpdb;
		$t_accounts = $wpdb->prefix . 'raka_loyalty_accounts';
		$t_users    = $wpdb->users;

		$search = sanitize_text_field( (string) $request->get_param( 'search' ) ?? '' );
		$page   = max( 1, (int) ( $request->get_param( 'page' ) ?? 1 ) );
		$limit  = min( 50, max( 5, (int) ( $request->get_param( 'limit' ) ?? 15 ) ) );
		$offset = ( $page - 1 ) * $limit;

		$where_sql = '1=1';
		if ( ! empty( $search ) ) {
			$like      = '%' . $wpdb->esc_like( $search ) . '%';
			$where_sql = $wpdb->prepare( '(u.user_login LIKE %s OR u.user_email LIKE %s OR u.display_name LIKE %s OR a.user_id = %d)', $like, $like, $like, (int) $search );
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$total_count = (int) $wpdb->get_var( "SELECT COUNT(u.ID) FROM {$t_users} u LEFT JOIN {$t_accounts} a ON u.ID = a.user_id WHERE {$where_sql}" );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$rows = $wpdb->get_results(
			"SELECT 
				u.ID as user_id, 
				u.user_login, 
				u.user_email, 
				u.display_name, 
				COALESCE(a.balance, 0) as balance, 
				COALESCE(a.reserved, 0) as reserved, 
				COALESCE(a.lifetime_earned, 0) as lifetime_earned, 
				COALESCE(a.tier, 'bronze') as tier,
				a.updated_at
			FROM {$t_users} u
			LEFT JOIN {$t_accounts} a ON u.ID = a.user_id
			WHERE {$where_sql}
			ORDER BY a.balance DESC, u.ID ASC
			LIMIT {$offset}, {$limit}",
			ARRAY_A
		);

		return new WP_REST_Response( [
			'users' => $rows,
			'total' => $total_count,
			'page'  => $page,
			'pages' => ceil( $total_count / $limit ),
		], 200 );
	}

	/**
	 * GET /admin/loyalty/users/{id}/ledger
	 */
	public static function admin_get_user_ledger( WP_REST_Request $request ): WP_REST_Response {
		$user_id = (int) $request->get_param( 'id' );
		global $wpdb;
		$t_ledger = $wpdb->prefix . 'raka_loyalty_ledger';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$entries = $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM {$t_ledger} WHERE user_id = %d ORDER BY id DESC LIMIT 100", $user_id ),
			ARRAY_A
		);

		return new WP_REST_Response( [ 'entries' => $entries ], 200 );
	}

	/**
	 * POST /admin/loyalty/adjust-points
	 */
	public static function admin_adjust_points( WP_REST_Request $request ): WP_REST_Response {
		$user_id = (int) $request->get_param( 'user_id' );
		$amount  = (float) $request->get_param( 'amount' );
		$reason  = (string) $request->get_param( 'reason' );

		$result = Raka_Loyalty_Service::admin_adjust( $user_id, $amount, $reason, get_current_user_id() );

		if ( ! $result->success ) {
			return new WP_REST_Response( [ 'success' => false, 'message' => $result->message ], 400 );
		}

		return new WP_REST_Response( [
			'success' => true,
			'message' => 'ปรับปรุงยอดแต้มสำเร็จ',
			'data'    => $result->data,
		], 200 );
	}

	/**
	 * GET /admin/loyalty/health
	 */
	public static function admin_get_health(): WP_REST_Response {
		global $wpdb;
		$t_system_log = $wpdb->prefix . 'raka_loyalty_system_log';
		$t_accounts   = $wpdb->prefix . 'raka_loyalty_accounts';
		$t_ledger     = $wpdb->prefix . 'raka_loyalty_ledger';

		$twenty_four_hrs_ago = gmdate( 'Y-m-d H:i:s', time() - DAY_IN_SECONDS );

		// 24h Error Count
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$error_count_24h = (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(id) FROM {$t_system_log} WHERE level IN ('error', 'critical') AND created_at >= %s", $twenty_four_hrs_ago )
		);

		// Total Points in Circulation (Liability)
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$total_points_liability = (float) $wpdb->get_var( "SELECT SUM(balance) FROM {$t_accounts}" );

		// Reconcile Check
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$mismatches = (int) $wpdb->get_var(
			"SELECT COUNT(a.user_id) FROM (
				SELECT a.user_id, a.balance, COALESCE(SUM(l.amount), 0) as ledger_sum
				FROM {$t_accounts} a
				LEFT JOIN {$t_ledger} l ON a.user_id = l.user_id
				GROUP BY a.user_id
				HAVING a.balance != ledger_sum
			) mismatch_table"
		);

		return new WP_REST_Response( [
			'errors_24h'       => $error_count_24h,
			'total_liability'  => $total_points_liability,
			'mismatches_count' => $mismatches,
			'reconcile_status' => 0 === $mismatches ? 'PERFECT_MATCH' : 'MISMATCH_DETECTED',
			'checked_at'       => current_time( 'mysql', true ),
		], 200 );
	}

	/**
	 * GET /admin/loyalty/logs
	 */
	public static function admin_get_logs( WP_REST_Request $request ): WP_REST_Response {
		global $wpdb;
		$t_log = $wpdb->prefix . 'raka_loyalty_system_log';

		$level      = sanitize_key( $request->get_param( 'level' ) ?? '' );
		$channel    = sanitize_key( $request->get_param( 'channel' ) ?? '' );
		$event_code = sanitize_text_field( (string) $request->get_param( 'event_code' ) ?? '' );
		$search     = sanitize_text_field( (string) $request->get_param( 'search' ) ?? '' );
		$page       = max( 1, (int) ( $request->get_param( 'page' ) ?? 1 ) );
		$limit      = min( 50, max( 10, (int) ( $request->get_param( 'limit' ) ?? 20 ) ) );
		$offset     = ( $page - 1 ) * $limit;

		$where = [ '1=1' ];
		if ( ! empty( $level ) && 'all' !== $level ) {
			$where[] = $wpdb->prepare( 'level = %s', $level );
		}
		if ( ! empty( $channel ) && 'all' !== $channel ) {
			$where[] = $wpdb->prepare( 'channel = %s', $channel );
		}
		if ( ! empty( $event_code ) ) {
			$where[] = $wpdb->prepare( 'event_code = %s', $event_code );
		}
		if ( ! empty( $search ) ) {
			$like    = '%' . $wpdb->esc_like( $search ) . '%';
			$where[] = $wpdb->prepare( '(message LIKE %s OR request_id LIKE %s OR order_id = %d OR user_id = %d)', $like, $like, (int) $search, (int) $search );
		}

		$where_sql = implode( ' AND ', $where );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$total = (int) $wpdb->get_var( "SELECT COUNT(id) FROM {$t_log} WHERE {$where_sql}" );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$logs = $wpdb->get_results( "SELECT * FROM {$t_log} WHERE {$where_sql} ORDER BY id DESC LIMIT {$offset}, {$limit}", ARRAY_A );

		return new WP_REST_Response( [
			'logs'  => $logs,
			'total' => $total,
			'page'  => $page,
			'pages' => ceil( $total / $limit ),
		], 200 );
	}

	/**
	 * GET /admin/loyalty/audit
	 */
	public static function admin_get_audit_logs( WP_REST_Request $request ): WP_REST_Response {
		global $wpdb;
		$t_audit = $wpdb->prefix . 'raka_loyalty_audit_log';
		$t_users = $wpdb->users;

		$page   = max( 1, (int) ( $request->get_param( 'page' ) ?? 1 ) );
		$limit  = min( 50, max( 10, (int) ( $request->get_param( 'limit' ) ?? 20 ) ) );
		$offset = ( $page - 1 ) * $limit;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$total = (int) $wpdb->get_var( "SELECT COUNT(id) FROM {$t_audit}" );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$rows = $wpdb->get_results(
			"SELECT a.*, u.user_login, u.display_name
			FROM {$t_audit} a
			LEFT JOIN {$t_users} u ON a.actor_id = u.ID
			ORDER BY a.id DESC LIMIT {$offset}, {$limit}",
			ARRAY_A
		);

		return new WP_REST_Response( [
			'audit' => $rows,
			'total' => $total,
			'page'  => $page,
			'pages' => ceil( $total / $limit ),
		], 200 );
	}

	/**
	 * POST /admin/loyalty/reconcile
	 */
	public static function admin_trigger_reconcile(): WP_REST_Response {
		global $wpdb;
		$t_accounts = $wpdb->prefix . 'raka_loyalty_accounts';
		$t_ledger   = $wpdb->prefix . 'raka_loyalty_ledger';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$mismatches = $wpdb->get_results(
			"SELECT a.user_id, a.balance, COALESCE(SUM(l.amount), 0) as ledger_sum
			FROM {$t_accounts} a
			LEFT JOIN {$t_ledger} l ON a.user_id = l.user_id
			GROUP BY a.user_id
			HAVING a.balance != ledger_sum",
			ARRAY_A
		);

		$fixed_count = 0;
		foreach ( $mismatches as $m ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$wpdb->update(
				$t_accounts,
				[
					'balance'    => (float) $m['ledger_sum'],
					'version'    => time(),
					'updated_at' => current_time( 'mysql', true ),
				],
				[ 'user_id' => (int) $m['user_id'] ],
				[ '%f', '%d', '%s' ],
				[ '%d' ]
			);
			$fixed_count++;
		}

		if ( class_exists( 'Raka_Loyalty_Audit' ) ) {
			Raka_Loyalty_Audit::log(
				'manual_reconcile',
				'system',
				'accounts',
				[ 'fixed_count' => $fixed_count, 'mismatches' => $mismatches ],
				'Admin triggered manual ledger reconciliation'
			);
		}

		return new WP_REST_Response( [
			'success'     => true,
			'message'     => sprintf( 'ตรวจสอบเรียบร้อย: ปรับปรุงยอดที่คลาดเคลื่อน %d บัญชี', $fixed_count ),
			'fixed_count' => $fixed_count,
		], 200 );
	}

	/**
	 * POST /admin/loyalty/logs/purge
	 */
	public static function admin_purge_logs( WP_REST_Request $request ): WP_REST_Response {
		$confirm = (bool) $request->get_param( 'confirm' );
		if ( ! $confirm ) {
			return new WP_REST_Response( [ 'success' => false, 'message' => 'Please confirm log purge action.' ], 400 );
		}

		$purged = Raka_Loyalty_Logger::purge_old_logs( 0 ); // Purge all system logs

		if ( class_exists( 'Raka_Loyalty_Audit' ) ) {
			Raka_Loyalty_Audit::log(
				'purge_logs',
				'system',
				'system_logs',
				[ 'purged_rows' => $purged ],
				'Admin purged system logs'
			);
		}

		return new WP_REST_Response( [
			'success' => true,
			'message' => sprintf( 'ล้างประวัติ System Log เก่าเรียบร้อยแล้ว (%d รายการ)', $purged ),
			'purged'  => $purged,
		], 200 );
	}
}
