<?php
/**
 * WooCommerce Blocks & Store API Integration for Loyalty & Rewards Engine.
 *
 * @package Raka_Preorder_Theme
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Raka_Loyalty_Blocks
 */
class Raka_Loyalty_Blocks {

	/**
	 * Initialize Store API and Blocks extensions.
	 */
	public static function init(): void {
		// Hook into Store API Checkout
		add_action( 'woocommerce_store_api_checkout_order_processed', [ __CLASS__, 'on_store_api_checkout' ] );

		// Register Store API Schema Extension if available
		add_action( 'woocommerce_blocks_loaded', [ __CLASS__, 'extend_store_api_schema' ] );
	}

	/**
	 * Handle Store API Checkout order processing (Blocks Checkout).
	 */
	public static function on_store_api_checkout( WC_Order $order ): void {
		if ( ! is_user_logged_in() || ! WC()->cart ) {
			return;
		}

		$user_id           = get_current_user_id();
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
			$order->save();

			// Reserve points
			$res = Raka_Loyalty_Service::reserve( $user_id, $order->get_id(), $reward_cost );
			if ( ! $res->success ) {
				throw new \Exception( $res->message );
			}
		}
	}

	/**
	 * Extend WooCommerce Store API Cart Schema to expose Loyalty state to Checkout Blocks.
	 */
	public static function extend_store_api_schema(): void {
		if ( ! function_exists( 'woocommerce_store_api_register_endpoint_data' ) ) {
			return;
		}

		woocommerce_store_api_register_endpoint_data( [
			'endpoint'        => 'cart',
			'namespace'       => 'raka_loyalty',
			'data_callback'   => [ __CLASS__, 'get_store_api_cart_data' ],
			'schema_callback' => [ __CLASS__, 'get_store_api_cart_schema' ],
		] );
	}

	/**
	 * Callback returning Loyalty data to Store API Cart responses.
	 */
	public static function get_store_api_cart_data(): array {
		if ( ! is_user_logged_in() ) {
			return [
				'is_logged_in'     => false,
				'available_points' => 0.0,
				'has_reward_item'  => false,
				'reward_product'   => null,
			];
		}

		$user_id   = get_current_user_id();
		$available = Raka_Loyalty_Service::get_available_points( $user_id );

		$has_reward = false;
		$reward_info = null;

		if ( WC()->cart ) {
			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
				if ( ! empty( $cart_item['_raka_is_reward'] ) ) {
					$has_reward = true;
					$prod = wc_get_product( $cart_item['product_id'] );
					$reward_info = [
						'cart_key'   => $cart_item_key,
						'product_id' => (int) $cart_item['product_id'],
						'title'      => $prod ? $prod->get_name() : '',
						'cost'       => (float) ( $cart_item['_raka_reward_cost'] ?? 100.0 ),
					];
					break;
				}
			}
		}

		return [
			'is_logged_in'     => true,
			'available_points' => $available,
			'has_reward_item'  => $has_reward,
			'reward_product'   => $reward_info,
		];
	}

	/**
	 * Schema callback for Store API endpoint extension.
	 */
	public static function get_store_api_cart_schema(): array {
		return [
			'is_logged_in'     => [ 'type' => 'boolean', 'context' => [ 'view', 'edit' ] ],
			'available_points' => [ 'type' => 'number', 'context' => [ 'view', 'edit' ] ],
			'has_reward_item'  => [ 'type' => 'boolean', 'context' => [ 'view', 'edit' ] ],
			'reward_product'   => [ 'type' => [ 'object', 'null' ], 'context' => [ 'view', 'edit' ] ],
		];
	}
}
