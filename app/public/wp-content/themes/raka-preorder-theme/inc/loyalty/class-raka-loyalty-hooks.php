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

		// 6. User Profile & Admin Users Table Integration
		add_action( 'show_user_profile', [ __CLASS__, 'render_user_profile_loyalty_fields' ] );
		add_action( 'edit_user_profile', [ __CLASS__, 'render_user_profile_loyalty_fields' ] );
		add_action( 'personal_options_update', [ __CLASS__, 'save_user_profile_loyalty_fields' ] );
		add_action( 'edit_user_profile_update', [ __CLASS__, 'save_user_profile_loyalty_fields' ] );
		add_filter( 'manage_users_columns', [ __CLASS__, 'add_users_loyalty_columns' ] );
		add_filter( 'manage_users_custom_column', [ __CLASS__, 'render_users_loyalty_column' ], 10, 3 );
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

	/**
	 * Render Loyalty Points & Tier in User Profile.
	 */
	public static function render_user_profile_loyalty_fields( WP_User $user ): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$account  = Raka_Loyalty_Service::get_account( $user->ID );
		$balance  = (float) ( $account['balance'] ?? 0 );
		$tier     = strtoupper( (string) ( $account['tier'] ?? 'BRONZE' ) );
		$lifetime = (float) ( $account['lifetime_earned'] ?? 0 );

		$tier_badge_color = '#d97706'; // Bronze
		if ( 'SILVER' === $tier ) {
			$tier_badge_color = '#64748b';
		} elseif ( 'GOLD' === $tier ) {
			$tier_badge_color = '#eab308';
		}
		?>
		<h2 id="raka-loyalty-heading" style="margin-top: 30px; display: flex; align-items: center; gap: 8px;">
			<span class="dashicons dashicons-awards" style="color: #2563eb; font-size: 24px; width: 24px; height: 24px;"></span>
			<?php esc_html_e( 'ระบบแต้มสะสม & สมาชิก (Raka Loyalty & Rewards)', 'raka-preorder-theme' ); ?>
		</h2>
		<table id="raka-loyalty-table" class="form-table" role="presentation" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 16px; margin-bottom: 20px;">
			<tr id="raka-loyalty-row-balance">
				<th><label><?php esc_html_e( 'ยอดแต้มสะสมปัจจุบัน', 'raka-preorder-theme' ); ?></label></th>
				<td>
					<span style="font-size: 20px; font-weight: 700; color: #0f172a; margin-right: 12px;">
						<?php echo esc_html( number_format( $balance, 2 ) ); ?> แต้ม
					</span>
					<span style="display: inline-block; padding: 2px 10px; border-radius: 9999px; font-size: 11px; font-weight: 700; color: #fff; background: <?php echo esc_attr( $tier_badge_color ); ?>;">
						TIER: <?php echo esc_html( $tier ); ?>
					</span>
					<p class="description" style="margin-top: 6px;"><?php echo esc_html( sprintf( 'แต้มที่เคยสะสมทั้งหมดตลอดชีพ: %s แต้ม', number_format( $lifetime, 2 ) ) ); ?></p>
				</td>
			</tr>
			<tr id="raka-loyalty-row-adjust">
				<th><label for="raka_loyalty_adjust_amount"><?php esc_html_e( 'เพิ่ม / ลด แต้มให้ลูกค้า', 'raka-preorder-theme' ); ?></label></th>
				<td>
					<div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center; margin-bottom: 6px;">
						<input type="number" step="any" name="raka_loyalty_adjust_amount" id="raka_loyalty_adjust_amount" class="regular-text" placeholder="ใส่จำนวนแต้ม เช่น 100 หรือ -50" style="max-width: 220px;" />
						<input type="text" name="raka_loyalty_adjust_reason" id="raka_loyalty_adjust_reason" class="regular-text" placeholder="ระบุเหตุผล (เช่น แต้มพิเศษต้อนรับลูกค้า, ชดเชย)" style="max-width: 320px;" />
					</div>
					<p class="description"><?php esc_html_e( 'ใส่ค่าบวก (เช่น 100) เพื่อเพิ่มแต้ม หรือใส่ค่าลบ (เช่น -50) เพื่อหักแต้ม พร้อมระบุเหตุผล แล้วกดบันทึกโปรไฟล์', 'raka-preorder-theme' ); ?></p>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Save Loyalty Adjustments from User Profile.
	 */
	public static function save_user_profile_loyalty_fields( int $user_id ): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! empty( $_POST['raka_loyalty_adjust_amount'] ) ) {
			$amount = (float) sanitize_text_field( wp_unslash( $_POST['raka_loyalty_adjust_amount'] ) );
			$reason = sanitize_text_field( wp_unslash( $_POST['raka_loyalty_adjust_reason'] ?? 'Admin profile adjustment' ) );

			if ( 0.0 !== $amount && ! empty( $reason ) ) {
				Raka_Loyalty_Service::admin_adjust( $user_id, $amount, $reason, get_current_user_id() );
			}
		}
	}

	/**
	 * Add Loyalty Column in Users List Table.
	 */
	public static function add_users_loyalty_columns( array $columns ): array {
		$columns['raka_points'] = esc_html__( 'แต้มสะสม (Raka Points)', 'raka-preorder-theme' );
		return $columns;
	}

	/**
	 * Render Loyalty Column in Users List Table.
	 */
	public static function render_users_loyalty_column( string $output, string $column_name, int $user_id ): string {
		if ( 'raka_points' === $column_name ) {
			$account = Raka_Loyalty_Service::get_account( $user_id );
			$balance = (float) ( $account['balance'] ?? 0 );
			$tier    = strtoupper( (string) ( $account['tier'] ?? 'BRONZE' ) );

			$color = '#d97706';
			if ( 'SILVER' === $tier ) {
				$color = '#64748b';
			} elseif ( 'GOLD' === $tier ) {
				$color = '#eab308';
			}

			return sprintf(
				'<div style="font-weight:700;color:#0f172a;">%s <span style="font-size:11px;font-weight:600;color:#64748b;">pts</span></div><span style="display:inline-block;padding:1px 6px;border-radius:4px;font-size:10px;font-weight:700;color:#fff;background:%s;">%s</span>',
				number_format( $balance ),
				esc_attr( $color ),
				esc_html( $tier )
			);
		}
		return $output;
	}
}

