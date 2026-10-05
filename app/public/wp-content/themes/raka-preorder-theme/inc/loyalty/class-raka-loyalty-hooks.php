<?php
/**
 * WooCommerce Action & Filter Hooks for Loyalty & Rewards Engine.
 *
 * @package Raka_Preorder_Theme
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Raka_Loyalty_Hooks
 */
class Raka_Loyalty_Hooks {

	/**
	 * Register all WooCommerce integration hooks.
	 */
	public static function init(): void {
		// 1. Order Status Lifecycle Hooks
		add_action( 'woocommerce_order_status_completed', [ __CLASS__, 'on_order_completed' ], 10, 2 );
		add_action( 'woocommerce_order_status_processing', [ __CLASS__, 'on_order_processing' ], 10, 2 );
		add_action( 'woocommerce_order_status_cancelled', [ __CLASS__, 'on_order_cancelled' ], 10, 2 );
		add_action( 'woocommerce_order_status_failed', [ __CLASS__, 'on_order_failed' ], 10, 2 );
		add_action( 'woocommerce_order_status_refunded', [ __CLASS__, 'on_order_refunded' ], 10, 2 );
		add_action( 'woocommerce_order_partially_refunded', [ __CLASS__, 'on_order_partially_refunded' ], 10, 2 );

		// 2. Checkout Reservation Hooks
		add_action( 'woocommerce_checkout_create_order', [ __CLASS__, 'on_checkout_create_order' ], 10, 2 );

		// 3. Cart & Price Validation Hooks (Server-Authoritative)
		add_filter( 'woocommerce_add_to_cart_validation', [ __CLASS__, 'validate_add_to_cart' ], 10, 3 );
		add_action( 'woocommerce_check_cart_items', [ __CLASS__, 'validate_cart_items' ] );
		add_action( 'woocommerce_before_calculate_totals', [ __CLASS__, 'enforce_zero_price_reward' ], 10, 1 );

		// 4. Action Scheduler Cron Jobs
		add_action( 'raka_loyalty_daily_expire_lots', [ 'Raka_Loyalty_Service', 'expire_lots' ] );
		add_action( 'raka_loyalty_daily_reconcile_check', [ __CLASS__, 'run_daily_reconcile_check' ] );

		// 5. Cart / Checkout Reward Widget UI
		add_action( 'woocommerce_before_cart_table', [ __CLASS__, 'render_cart_reward_widget' ] );
		add_action( 'woocommerce_before_checkout_form', [ __CLASS__, 'render_cart_reward_widget' ] );
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue_cart_reward_assets' ] );
	}

	/**
	 * Render cart reward widget mountpoint.
	 */
	public static function render_cart_reward_widget(): void {
		if ( ! is_user_logged_in() ) {
			return;
		}
		echo '<div id="raka-cart-loyalty-root" class="w-full"></div>';
	}

	/**
	 * Enqueue assets on cart / checkout if needed.
	 */
	public static function enqueue_cart_reward_assets(): void {
		if ( ! is_user_logged_in() ) {
			return;
		}
		if ( ( function_exists( 'is_cart' ) && is_cart() ) || ( function_exists( 'is_checkout' ) && is_checkout() ) ) {
			$asset_file = get_template_directory() . '/build/index.asset.php';
			$asset      = file_exists( $asset_file ) ? require $asset_file : [ 'dependencies' => [ 'wp-element' ], 'version' => '1.0.0' ];
			wp_enqueue_script(
				'raka-account-react',
				get_template_directory_uri() . '/build/index.js',
				$asset['dependencies'],
				$asset['version'],
				true
			);
			wp_localize_script(
				'raka-account-react',
				'rakaAccountData',
				[
					'apiUrl'      => esc_url_raw( rest_url( 'account/v1' ) ),
					'nonce'       => wp_create_nonce( 'wp_rest' ),
					'currentUser' => get_current_user_id(),
				]
			);
		}
	}

	/**
	 * Trigger point earning and commitment on order completion.
	 */
	public static function on_order_completed( int $order_id, $order = null ): void {
		if ( ! $order instanceof WC_Order ) {
			$order = wc_get_order( $order_id );
		}
		if ( ! $order ) {
			return;
		}

		$user_id = (int) $order->get_customer_id();
		if ( $user_id <= 0 ) {
			return;
		}

		// 1. Commit any reserved points
		if ( $order->get_meta( '_raka_points_reserved' ) ) {
			Raka_Loyalty_Service::commit( $order_id );
		}

		// 2. Earn points for completed order
		Raka_Loyalty_Service::earn( $order_id, $user_id, 'hook_order_completed' );
	}

	/**
	 * Commit reserved points when order becomes processing.
	 */
	public static function on_order_processing( int $order_id, $order = null ): void {
		if ( ! $order instanceof WC_Order ) {
			$order = wc_get_order( $order_id );
		}
		if ( ! $order ) {
			return;
		}

		// Commit reserved points during processing
		if ( $order->get_meta( '_raka_points_reserved' ) ) {
			Raka_Loyalty_Service::commit( $order_id );
		}
	}

	/**
	 * Release reserved points on cancelled order.
	 */
	public static function on_order_cancelled( int $order_id, $order = null ): void {
		Raka_Loyalty_Service::release( $order_id, 'order_cancelled' );
	}

	/**
	 * Release reserved points on failed order.
	 */
	public static function on_order_failed( int $order_id, $order = null ): void {
		Raka_Loyalty_Service::release( $order_id, 'order_failed' );
	}

	/**
	 * Handle full order refund and clawback.
	 */
	public static function on_order_refunded( int $order_id, $order = null ): void {
		if ( ! $order instanceof WC_Order ) {
			$order = wc_get_order( $order_id );
		}
		if ( ! $order ) {
			return;
		}
		Raka_Loyalty_Service::handle_refund( $order_id, (float) $order->get_total(), 0 );
	}

	/**
	 * Handle partial refund and proportional clawback.
	 */
	public static function on_order_partially_refunded( int $order_id, int $refund_id ): void {
		$refund = wc_get_order( $refund_id );
		if ( ! $refund ) {
			return;
		}
		$refund_amount = abs( (float) $refund->get_amount() );
		Raka_Loyalty_Service::handle_refund( $order_id, $refund_amount, $refund_id );
	}

	/**
	 * Checkout Create Order: Reserve points for reward item if present.
	 */
	public static function on_checkout_create_order( WC_Order $order, array $data ): void {
		if ( ! is_user_logged_in() || ! WC()->cart ) {
			return;
		}

		$user_id = get_current_user_id();
		$reward_product_id = 0;
		$reward_cost       = 100.0;

		foreach ( WC()->cart->get_cart() as $cart_item ) {
			if ( ! empty( $cart_item['_raka_is_reward'] ) ) {
				$reward_product_id = (int) $cart_item['product_id'];
				$reward_cost       = (float) ( $cart_item['_raka_reward_cost'] ?? 100.0 );
				break;
			}
		}

		if ( $reward_product_id > 0 ) {
			$order->update_meta_data( '_raka_reward_product_id', $reward_product_id );
			$order->update_meta_data( '_raka_points_reserved', $reward_cost );

			// Execute reservation in DB
			$res = Raka_Loyalty_Service::reserve( $user_id, $order->get_id(), $reward_cost );
			if ( ! $res->success ) {
				throw new \Exception( $res->message );
			}
		}
	}

	/**
	 * Prevent adding more than 1 reward item to cart or direct tampering.
	 */
	public static function validate_add_to_cart( bool $passed, int $product_id, int $quantity ): bool {
		if ( ! WC()->cart ) {
			return $passed;
		}

		// Check if attempting to add another reward item when one already exists
		$has_reward = false;
		foreach ( WC()->cart->get_cart() as $cart_item ) {
			if ( ! empty( $cart_item['_raka_is_reward'] ) ) {
				$has_reward = true;
				break;
			}
		}

		// If current item is being added as reward
		if ( isset( $_POST['_raka_is_reward'] ) && $has_reward ) {
			wc_add_notice( 'คุณสามารถเลือกรับของรางวัลได้ 1 ชิ้น ต่อ 1 คำสั่งซื้อเท่านั้น', 'error' );
			return false;
		}

		return $passed;
	}

	/**
	 * Check cart items: Ensure reward quantity is 1 and points are sufficient.
	 */
	public static function validate_cart_items(): void {
		if ( ! WC()->cart ) {
			return;
		}

		$reward_count = 0;
		$user_id      = get_current_user_id();
		$available    = is_user_logged_in() ? Raka_Loyalty_Service::get_available_points( $user_id ) : 0.0;

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			if ( ! empty( $cart_item['_raka_is_reward'] ) ) {
				$reward_count++;
				$cost = (float) ( $cart_item['_raka_reward_cost'] ?? 100.0 );

				// Enforce quantity = 1
				if ( (int) $cart_item['quantity'] > 1 ) {
					WC()->cart->set_quantity( $cart_item_key, 1 );
				}

				// If user logged out or points insufficient, remove reward
				if ( ! is_user_logged_in() || $available < $cost ) {
					WC()->cart->remove_cart_item( $cart_item_key );
					wc_add_notice( 'ของรางวัลถูกนำออกจากตะกร้า เนื่องจากคะแนนสะสมคงเหลือไม่เพียงพอ', 'notice' );
				}
			}
		}

		if ( $reward_count > 1 ) {
			// Remove duplicate rewards
			$first = true;
			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
				if ( ! empty( $cart_item['_raka_is_reward'] ) ) {
					if ( $first ) {
						$first = false;
					} else {
						WC()->cart->remove_cart_item( $cart_item_key );
					}
				}
			}
			wc_add_notice( 'จำกัดการแลกของรางวัล 1 ชิ้น ต่อ 1 คำสั่งซื้อ', 'notice' );
		}
	}

	/**
	 * Server-Authoritative Price Enforcement: Set price = 0 for reward item with HMAC verification.
	 */
	public static function enforce_zero_price_reward( $cart ): void {
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			return;
		}

		foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
			if ( ! empty( $cart_item['_raka_is_reward'] ) ) {
				$product_id = (int) $cart_item['product_id'];
				$sig        = (string) ( $cart_item['_raka_reward_sig'] ?? '' );
				$expected   = self::generate_reward_signature( $product_id, get_current_user_id() );

				if ( ! hash_equals( $expected, $sig ) ) {
					// Price tamper attempt detected!
					Raka_Loyalty_Logger::critical(
						'PRICE_TAMPER_BLOCKED',
						"Reward signature mismatch on product #{$product_id}",
						[ 'product_id' => $product_id, 'received_sig' => $sig ],
						0,
						get_current_user_id()
					);
					$cart->remove_cart_item( $cart_item_key );
					wc_add_notice( 'เกิดข้อผิดพลาดในการตรวจสอบสิทธิ์ของรางวัล', 'error' );
					continue;
				}

				// Enforce 0.00 THB price
				$cart_item['data']->set_price( 0.00 );
			}
		}
	}

	/**
	 * Generate HMAC signature for a reward product in cart.
	 */
	public static function generate_reward_signature( int $product_id, int $user_id ): string {
		$secret = defined( 'NONCE_KEY' ) ? NONCE_KEY : 'raka_reward_secret_key';
		return hash_hmac( 'sha256', "reward:{$product_id}:{$user_id}", $secret );
	}

	/**
	 * Scheduled daily reconciliation check.
	 */
	public static function run_daily_reconcile_check(): void {
		global $wpdb;
		$t_accounts = $wpdb->prefix . 'raka_loyalty_accounts';
		$t_ledger   = $wpdb->prefix . 'raka_loyalty_ledger';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$mismatches = $wpdb->get_results(
			"SELECT a.user_id, a.balance, COALESCE(SUM(l.amount), 0) as ledger_sum
			FROM {$t_accounts} a
			LEFT JOIN {$t_ledger} l ON a.user_id = l.user_id
			GROUP BY a.user_id
			HAVING a.balance != ledger_sum"
		);

		if ( ! empty( $mismatches ) ) {
			Raka_Loyalty_Logger::error(
				'RECONCILE_MISMATCH',
				sprintf( 'Daily reconciliation check found %d account mismatch(es)', count( $mismatches ) ),
				[ 'mismatches' => $mismatches ],
				0,
				0,
				0,
				'reconcile'
			);
		}
	}
}
