<?php
declare(strict_types=1);

namespace MyWcPasskeys;

use WP_Error;
use WP_User;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enterprise CAPTCHA Integration (Cloudflare Turnstile & Google reCAPTCHA).
 * Handles API Keys configuration, conditional frontend enqueue, and backend verification.
 */
class Captcha {

	/**
	 * Provider: 'turnstile' or 'recaptcha'
	 */
	const PROVIDER = 'turnstile';

	/**
	 * Verify API endpoints.
	 */
	const TURNSTILE_VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
	const RECAPTCHA_VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

	/**
	 * Initialize CAPTCHA hooks and actions.
	 */
	public static function init(): void {
		// 1. Enqueue Frontend Scripts conditionally
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue_scripts' ] );
		add_action( 'login_enqueue_scripts', [ __CLASS__, 'enqueue_login_scripts' ] );

		// 2. Render CAPTCHA on standard wp-login.php form
		add_action( 'login_form', [ __CLASS__, 'render_login_form_captcha' ] );

		// 3. Hook Verify on Login (Authenticate filter, Priority 25)
		add_filter( 'authenticate', [ __CLASS__, 'verify_login_captcha' ], 25, 3 );

		// 4. Hook Verify on WooCommerce Native Registration
		add_action( 'woocommerce_register_post', [ __CLASS__, 'verify_wc_register_captcha' ], 10, 3 );

		// 5. Hook Verify on Standard WordPress Registration
		add_filter( 'registration_errors', [ __CLASS__, 'verify_wp_register_captcha' ], 10, 3 );
	}

	/**
	 * Enqueue scripts for standard wp-login.php page.
	 */
	public static function enqueue_login_scripts(): void {
		$secret = self::get_secret_key();
		if ( empty( $secret ) ) {
			return;
		}

		if ( self::PROVIDER === 'turnstile' ) {
			wp_enqueue_script(
				'cloudflare-turnstile',
				'https://challenges.cloudflare.com/turnstile/v0/api.js',
				[],
				null,
				true
			);
		} else {
			wp_enqueue_script(
				'google-recaptcha',
				'https://www.google.com/recaptcha/api.js',
				[],
				null,
				true
			);
		}
	}

	/**
	 * Output CAPTCHA field inside standard wp-login.php.
	 */
	public static function render_login_form_captcha(): void {
		$secret = self::get_secret_key();
		if ( empty( $secret ) ) {
			return;
		}
		echo self::render( 'light' );
	}

	/**
	 * Get Site Key from wp-config constant or options.
	 *
	 * @return string
	 */
	public static function get_site_key(): string {
		if ( defined( 'CAPTCHA_SITE_KEY' ) && ! empty( CAPTCHA_SITE_KEY ) ) {
			return (string) CAPTCHA_SITE_KEY;
		}
		return (string) get_option( 'captcha_site_key', '0x4AAAAAAFNWAI83P9cCw0AU' );
	}

	/**
	 * Get Secret Key from wp-config constant or options.
	 *
	 * @return string
	 */
	public static function get_secret_key(): string {
		if ( defined( 'CAPTCHA_SECRET_KEY' ) && ! empty( CAPTCHA_SECRET_KEY ) ) {
			return (string) CAPTCHA_SECRET_KEY;
		}
		return (string) get_option( 'captcha_secret_key', '' );
	}

	/**
	 * Enqueue CAPTCHA script only on pages with Auth Shortcodes or My Account.
	 */
	public static function enqueue_scripts(): void {
		global $post;

		$has_auth_shortcode = is_a( $post, 'WP_Post' ) && (
			has_shortcode( $post->post_content, 'wc_custom_login' ) ||
			has_shortcode( $post->post_content, 'wc_custom_register' ) ||
			has_shortcode( $post->post_content, 'woocommerce_my_account' ) ||
			has_shortcode( $post->post_content, 'auth_login' ) ||
			has_shortcode( $post->post_content, 'auth_register' )
		);

		if ( ! is_account_page() && ! is_page( [ 'login', 'register' ] ) && ! $has_auth_shortcode ) {
			return;
		}

		if ( self::PROVIDER === 'turnstile' ) {
			wp_enqueue_script(
				'cloudflare-turnstile',
				'https://challenges.cloudflare.com/turnstile/v0/api.js',
				[],
				null,
				true
			);
		} else {
			wp_enqueue_script(
				'google-recaptcha',
				'https://www.google.com/recaptcha/api.js',
				[],
				null,
				true
			);
		}
	}

	/**
	 * Render CAPTCHA widget container for template markup.
	 *
	 * @param string $theme 'auto' | 'light' | 'dark'
	 * @return string
	 */
	public static function render( string $theme = 'auto' ): string {
		$site_key = self::get_site_key();
		if ( empty( $site_key ) ) {
			return '';
		}

		ob_start();
		?>
		<div id="raka-captcha-wrap" class="my-wc-captcha-wrapper my-3 flex flex-col items-center justify-center min-h-[65px] rounded-2xl bg-neutral-50/50 dark:bg-neutral-800/40 border border-black/5 dark:border-white/5 p-2 overflow-hidden">
			<?php if ( self::PROVIDER === 'turnstile' ) : ?>
				<div
					id="raka-cf-turnstile-widget"
					class="cf-turnstile"
					data-sitekey="<?php echo esc_attr( $site_key ); ?>"
					data-theme="<?php echo esc_attr( $theme ); ?>"
				></div>
			<?php else : ?>
				<div
					id="raka-g-recaptcha-widget"
					class="g-recaptcha"
					data-sitekey="<?php echo esc_attr( $site_key ); ?>"
					data-theme="<?php echo esc_attr( $theme ); ?>"
				></div>
			<?php endif; ?>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Verify Token against Provider API.
	 *
	 * @param string|null $token
	 * @return bool
	 */
	public static function verify_token( ?string $token = null ): bool {
		if ( null === $token ) {
			if ( isset( $_POST['cf-turnstile-response'] ) ) {
				$token = sanitize_text_field( wp_unslash( $_POST['cf-turnstile-response'] ) );
			} elseif ( isset( $_POST['g-recaptcha-response'] ) ) {
				$token = sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ) );
			} else {
				$token = '';
			}
		}

		$secret_key = self::get_secret_key();
		if ( empty( $secret_key ) ) {
			return true; // Bypass safely if secret key not configured yet
		}

		if ( empty( $token ) ) {
			return false;
		}

		$verify_url = ( self::PROVIDER === 'turnstile' ) ? self::TURNSTILE_VERIFY_URL : self::RECAPTCHA_VERIFY_URL;
		$user_ip    = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

		$response = wp_remote_post(
			$verify_url,
			[
				'timeout'   => 8,
				'sslverify' => true,
				'body'      => [
					'secret'   => $secret_key,
					'response' => $token,
					'remoteip' => $user_ip,
				],
			]
		);

		if ( is_wp_error( $response ) ) {
			self::log_error( 'CAPTCHA connection timeout or error: ' . $response->get_error_message() );
			return false;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( ! is_array( $data ) || empty( $data['success'] ) ) {
			self::log_error( 'CAPTCHA verification failed: ' . $body );
			return false;
		}

		return true;
	}

	/**
	 * Verify on Login Submission.
	 *
	 * @param WP_User|WP_Error|null $user
	 * @param string $username
	 * @param string $password
	 * @return WP_User|WP_Error
	 */
	public static function verify_login_captcha( $user, string $username, string $password ) {
		if ( empty( $username ) && empty( $password ) ) {
			return $user;
		}

		// Skip if REST request (e.g. Passkeys API)
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return $user;
		}

		if ( ! self::verify_token() ) {
			return new WP_Error(
				'captcha_failed',
				__( '<strong>ข้อผิดพลาด:</strong> กรุณายืนยันตัวตนผ่าน CAPTCHA ให้ถูกต้อง', 'my-wc-passkeys' )
			);
		}

		return $user;
	}

	/**
	 * Verify on WooCommerce Registration.
	 *
	 * @param string $username
	 * @param string $email
	 * @param WP_Error $errors
	 */
	public static function verify_wc_register_captcha( string $username, string $email, WP_Error $errors ): void {
		if ( ! self::verify_token() ) {
			$errors->add(
				'captcha_failed',
				__( 'กรุณายืนยันตัวตนผ่าน CAPTCHA ให้ถูกต้อง', 'my-wc-passkeys' )
			);
			if ( function_exists( 'wc_add_notice' ) ) {
				wc_add_notice( __( 'กรุณายืนยันตัวตนผ่าน CAPTCHA ให้ถูกต้อง', 'my-wc-passkeys' ), 'error' );
			}
		}
	}

	/**
	 * Verify on Standard WordPress Registration.
	 *
	 * @param WP_Error $errors
	 * @param string $sanitized_user_login
	 * @param string $user_email
	 * @return WP_Error
	 */
	public static function verify_wp_register_captcha( WP_Error $errors, string $sanitized_user_login, string $user_email ): WP_Error {
		if ( ! self::verify_token() ) {
			$errors->add(
				'captcha_failed',
				__( '<strong>ข้อผิดพลาด:</strong> กรุณายืนยันตัวตนผ่าน CAPTCHA ให้ถูกต้อง', 'my-wc-passkeys' )
			);
		}
		return $errors;
	}

	/**
	 * Log error to WooCommerce Logger.
	 *
	 * @param string $message
	 */
	protected static function log_error( string $message ): void {
		if ( function_exists( 'wc_get_logger' ) ) {
			$logger = wc_get_logger();
			$logger->error( $message, [ 'source' => 'my-wc-captcha' ] );
		} else {
			error_log( '[MY_WC_CAPTCHA] ' . $message );
		}
	}
}
