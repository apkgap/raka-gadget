<?php
/**
 * Earn Rules Calculation Engine for Loyalty & Rewards Engine.
 *
 * @package Raka_Preorder_Theme
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Raka_Loyalty_Earn_Engine
 */
class Raka_Loyalty_Earn_Engine {

	/**
	 * Calculate points earned for a given WooCommerce Order.
	 *
	 * @param WC_Order $order WooCommerce Order object.
	 * @return array Result array with points, base_amount, multipliers, and rule_snapshot.
	 */
	public static function calculate_order_points( WC_Order $order ): array {
		$options = get_option( 'raka_theme_options', [] );
		$loyalty = $options['loyalty'] ?? [];

		// Feature flag check
		if ( isset( $loyalty['enabled'] ) && ! $loyalty['enabled'] ) {
			return [
				'points'          => 0.0,
				'base_amount'     => 0.0,
				'rule_snapshot'   => [ 'reason' => 'loyalty_disabled' ],
			];
		}

		// 1. Determine Base Spend Amount (Exclude tax & shipping if configured, exclude reward item)
		$include_shipping = ! empty( $loyalty['include_shipping_in_earn'] );
		$include_tax      = ! empty( $loyalty['include_tax_in_earn'] );

		$base_amount = 0.0;
		$has_preorder = false;

		foreach ( $order->get_items() as $item ) {
			// Skip reward items (price 0 / reward meta)
			if ( $item->get_meta( '_raka_is_reward_item' ) || $item->get_meta( '_is_loyalty_reward' ) ) {
				continue;
			}

			$line_total = (float) $item->get_total(); // Net after line discounts
			if ( $include_tax ) {
				$line_total += (float) $item->get_total_tax();
			}

			$product = $item->get_product();
			if ( $product ) {
				$is_preorder = 'yes' === get_post_meta( $product->get_id(), '_is_preorder', true )
					|| has_term( 'pre-order', 'product_cat', $product->get_id() );
				if ( $is_preorder ) {
					$has_preorder = true;
				}
			}

			$base_amount += max( 0.0, $line_total );
		}

		if ( $include_shipping ) {
			$base_amount += (float) $order->get_shipping_total();
			if ( $include_tax ) {
				$base_amount += (float) $order->get_shipping_tax();
			}
		}

		// Apply filters on base amount
		$base_amount = (float) apply_filters( 'raka_loyalty_earn_base_amount', $base_amount, $order );

		if ( $base_amount <= 0 ) {
			return [
				'points'        => 0.0,
				'base_amount'   => 0.0,
				'rule_snapshot' => [ 'reason' => 'zero_qualifying_amount' ],
			];
		}

		// 2. Base Conversion Rate (e.g. 100 THB = 1 point -> rate = 0.01)
		$spend_per_point = (float) ( $loyalty['spend_per_point'] ?? 100.0 );
		if ( $spend_per_point <= 0 ) {
			$spend_per_point = 100.0;
		}
		$raw_points = $base_amount / $spend_per_point;

		// 3. User Tier Multiplier
		$user_id         = (int) $order->get_customer_id();
		$user_tier       = self::get_user_tier( $user_id );
		$tier_multiplier = self::get_tier_multiplier( $user_tier, $loyalty );

		// 4. Campaign Multiplier (Active date range promo)
		$campaign_multiplier = self::get_campaign_multiplier( $loyalty );

		// 5. Pre-Order Bonus Multiplier (if applicable)
		$preorder_multiplier = 1.0;
		if ( $has_preorder && ! empty( $loyalty['preorder_points_multiplier'] ) ) {
			$preorder_multiplier = max( 1.0, (float) $loyalty['preorder_points_multiplier'] );
		}

		// Combined Total Multiplier
		$total_multiplier = $tier_multiplier * $campaign_multiplier * $preorder_multiplier;
		$calculated_points = round( $raw_points * $total_multiplier, 2 );

		// Allow extensions to filter final points
		$final_points = (float) apply_filters( 'raka_loyalty_points_earned', $calculated_points, $order, $base_amount );

		$rule_snapshot = [
			'base_amount'         => $base_amount,
			'spend_per_point'     => $spend_per_point,
			'raw_points'          => round( $raw_points, 2 ),
			'tier'                => $user_tier,
			'tier_multiplier'     => $tier_multiplier,
			'campaign_multiplier' => $campaign_multiplier,
			'preorder_multiplier' => $preorder_multiplier,
			'total_multiplier'    => $total_multiplier,
			'calculated_at'       => current_time( 'mysql', true ),
		];

		return [
			'points'        => $final_points,
			'base_amount'   => $base_amount,
			'rule_snapshot' => $rule_snapshot,
		];
	}

	/**
	 * Get current tier of a user.
	 */
	public static function get_user_tier( int $user_id ): string {
		if ( $user_id <= 0 ) {
			return 'bronze';
		}
		global $wpdb;
		$table = $wpdb->prefix . 'raka_loyalty_accounts';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$tier = $wpdb->get_var( $wpdb->prepare( "SELECT tier FROM {$table} WHERE user_id = %d", $user_id ) );
		return $tier ?: 'bronze';
	}

	/**
	 * Get multiplier for a given tier.
	 */
	public static function get_tier_multiplier( string $tier, array $loyalty_options ): float {
		$multipliers = $loyalty_options['tier_multipliers'] ?? [
			'bronze' => 1.0,
			'silver' => 1.25,
			'gold'   => 1.5,
		];
		return isset( $multipliers[ $tier ] ) ? max( 0.1, (float) $multipliers[ $tier ] ) : 1.0;
	}

	/**
	 * Get active campaign multiplier if within promotional date range.
	 */
	public static function get_campaign_multiplier( array $loyalty_options ): float {
		if ( empty( $loyalty_options['campaign_enabled'] ) ) {
			return 1.0;
		}

		$start = ! empty( $loyalty_options['campaign_start'] ) ? strtotime( (string) $loyalty_options['campaign_start'] ) : 0;
		$end   = ! empty( $loyalty_options['campaign_end'] ) ? strtotime( (string) $loyalty_options['campaign_end'] ) : 0;
		$now   = time();

		if ( ( 0 === $start || $now >= $start ) && ( 0 === $end || $now <= $end ) ) {
			return max( 1.0, (float) ( $loyalty_options['campaign_multiplier'] ?? 1.0 ) );
		}

		return 1.0;
	}
}
