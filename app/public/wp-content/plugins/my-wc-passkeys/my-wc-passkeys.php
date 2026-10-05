<?php
/**
 * Plugin Name:       My WooCommerce Passkeys (WebAuthn / FIDO2)
 * Plugin URI:        https://raka-gadget.com/passkeys
 * Description:       Enterprise-grade Passkeys authentication for WordPress and WooCommerce. Enables seamless biometric login (Touch ID, Face ID, Windows Hello) without modifying legacy password or social login flows.
 * Version:           1.0.0
 * Author:            Senior Core Developer
 * Author URI:        https://raka-gadget.com
 * Text Domain:       my-wc-passkeys
 * Domain Path:       /languages
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * WC requires at least: 5.0
 * WC tested up to:   9.2
 * License:           GPL-2.0-or-later
 *
 * @package           my-wc-passkeys
 */

declare(strict_types=1);

namespace MyWcPasskeys;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define Plugin Constants
define( 'MY_WC_PASSKEYS_VERSION', '1.0.0' );
define( 'MY_WC_PASSKEYS_FILE', __FILE__ );
define( 'MY_WC_PASSKEYS_DIR', plugin_dir_path( __FILE__ ) );
define( 'MY_WC_PASSKEYS_URL', plugin_dir_url( __FILE__ ) );

/**
 * Autoload Plugin Classes.
 */
spl_autoload_register( function ( string $class_name ): void {
	$prefix = 'MyWcPasskeys\\';
	if ( 0 !== strpos( $class_name, $prefix ) ) {
		return;
	}

	$relative_class = substr( $class_name, strlen( $prefix ) );
	$map            = [
		'DB'             => 'class-db.php',
		'WcHooks'        => 'class-wc-hooks.php',
		'Activator'      => 'class-activator.php',
		'AuthService'    => 'class-auth-service.php',
		'RestController' => 'class-rest-controller.php',
		'TemplateLoader' => 'class-template-loader.php',
		'AuthShortcodes' => 'class-auth-shortcodes.php',
		'Captcha'        => 'class-captcha.php',
	];

	if ( isset( $map[ $relative_class ] ) ) {
		$file_path = MY_WC_PASSKEYS_DIR . 'includes/' . $map[ $relative_class ];
	} else {
		$kebab     = strtolower( (string) preg_replace( '/([a-z0-9])([A-Z])/', '$1-$2', $relative_class ) );
		$file_path = MY_WC_PASSKEYS_DIR . 'includes/class-' . str_replace( '_', '-', $kebab ) . '.php';
	}

	if ( file_exists( $file_path ) ) {
		require_once $file_path;
	}
} );

// Include Composer autoloader if present
if ( file_exists( MY_WC_PASSKEYS_DIR . 'vendor/autoload.php' ) ) {
	require_once MY_WC_PASSKEYS_DIR . 'vendor/autoload.php';
}

/**
 * Activation Hook.
 */
register_activation_hook( __FILE__, [ Activator::class, 'activate' ] );

/**
 * Deactivation Hook.
 */
register_deactivation_hook( __FILE__, [ Activator::class, 'deactivate' ] );

/**
 * Declare WooCommerce HPOS Compatibility.
 */
add_action( 'before_woocommerce_init', function (): void {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
			'custom_order_tables',
			__FILE__,
			true
		);
	}
} );

/**
 * Main Plugin Initialization.
 */
function init(): void {
	// Initialize WooCommerce UI Hooks
	WcHooks::init();

	// Initialize Custom Auth Shortcodes
	AuthShortcodes::init();

	// Initialize CAPTCHA Integration
	Captcha::init();

	// Initialize REST API Routes
	add_action( 'rest_api_init', function (): void {
		$controller = new RestController();
		$controller->register_routes();
	} );

	// Load Text Domain
	add_action( 'init', function (): void {
		load_plugin_textdomain(
			'my-wc-passkeys',
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/languages'
		);
	} );
}
add_action( 'plugins_loaded', __NAMESPACE__ . '\\init' );
