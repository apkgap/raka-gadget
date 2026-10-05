<?php
/**
 * Template Name: My Account (React SPA)
 * Template for displaying the user account dashboard in full React SPA.
 *
 * @package raka-preorder-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Redirect logged-out users to /login/
if ( ! is_user_logged_in() ) {
	wp_safe_redirect( home_url( '/login/?redirect_to=' . urlencode( home_url( '/my-account/' ) ) ) );
	exit;
}

/**
 * Enqueue React Account App assets
 */
function raka_enqueue_account_spa_assets(): void {
	$asset_file_path = get_template_directory() . '/build/index.asset.php';
	$asset_file      = file_exists( $asset_file_path )
		? require $asset_file_path
		: [ 'dependencies' => [ 'wp-element', 'wp-i18n' ], 'version' => '1.0.0' ];

	$js_path = get_template_directory() . '/build/index.js';
	$js_ver  = file_exists( $js_path ) ? (string) filemtime( $js_path ) : ( $asset_file['version'] ?? '1.0.0' );

	wp_enqueue_script(
		'raka-account-spa-js',
		get_template_directory_uri() . '/build/index.js',
		$asset_file['dependencies'] ?? [ 'wp-element' ],
		$js_ver,
		true
	);

	wp_localize_script(
		'raka-account-spa-js',
		'rakaAccountData',
		[
			'apiUrl'         => esc_url_raw( rest_url( 'account/v1' ) ),
			'passkeysApiUrl' => esc_url_raw( rest_url( 'wc-passkeys/v1' ) ),
			'nonce'          => wp_create_nonce( 'wp_rest' ),
			'logoutUrl'      => esc_url( wp_logout_url( home_url( '/login/' ) ) ),
			'homeUrl'        => esc_url( home_url( '/' ) ),
			'currentUser'    => [
				'id'          => get_current_user_id(),
				'displayName' => wp_get_current_user()->display_name,
				'userEmail'   => wp_get_current_user()->user_email,
			],
		]
	);
}
add_action( 'wp_enqueue_scripts', 'raka_enqueue_account_spa_assets' );

get_header();
?>

<main id="raka-account-main-page" class="min-h-screen pt-4 sm:pt-8 md:pt-10 pb-20 px-4 sm:px-6 md:px-10 lg:px-16 text-inherit font-hn">
	<div id="raka-account-wrapper" class="w-full max-w-6xl mx-auto">
		<!-- ── React SPA Mount Point ── -->
		<div id="raka-account-root" class="w-full min-h-[500px]"></div>
	</div>
</main>

<?php
get_footer();
