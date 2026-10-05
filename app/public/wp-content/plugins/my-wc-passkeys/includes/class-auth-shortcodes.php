<?php
declare(strict_types=1);

namespace MyWcPasskeys;

use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles custom [wc_custom_login] and [wc_custom_register] Shortcodes & Form Handlers.
 */
class AuthShortcodes {

	/**
	 * Initialize shortcodes and form submission actions.
	 */
	public static function init(): void {
		// Register Shortcodes
		add_shortcode( 'wc_custom_login', [ __CLASS__, 'render_login_shortcode' ] );
		add_shortcode( 'wc_custom_register', [ __CLASS__, 'render_register_shortcode' ] );

		// Process Form Submissions before headers sent
		add_action( 'template_redirect', [ __CLASS__, 'process_custom_registration' ] );
		add_action( 'template_redirect', [ __CLASS__, 'process_custom_login' ] );
		add_action( 'template_redirect', [ __CLASS__, 'redirect_logged_in_users' ] );
	}

	/**
	 * Redirect already logged-in users visiting dedicated login/register pages.
	 */
	public static function redirect_logged_in_users(): void {
		if ( ! is_user_logged_in() ) {
			return;
		}

		global $post;
		$is_auth_page = is_a( $post, 'WP_Post' ) && (
			has_shortcode( $post->post_content, 'wc_custom_login' ) ||
			has_shortcode( $post->post_content, 'wc_custom_register' )
		);

		if ( ! $is_auth_page ) {
			return;
		}

		$redirect_url = isset( $_GET['redirect_to'] )
			? esc_url_raw( wp_unslash( $_GET['redirect_to'] ) )
			: ( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/my-account/' ) );

		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Render Custom Login Form [wc_custom_login].
	 *
	 * @param array<string, mixed> $atts
	 * @return string
	 */
	public static function render_login_shortcode( $atts = [] ): string {
		if ( is_user_logged_in() ) {
			return '';
		}

		$redirect     = isset( $_GET['redirect_to'] ) ? esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ) : '';
		$register_url = ! empty( $redirect )
			? add_query_arg( 'redirect_to', rawurlencode( $redirect ), home_url( '/register/' ) )
			: home_url( '/register/' );

		return TemplateLoader::get_template_html( 'auth-login.php', [
			'redirect'     => $redirect,
			'register_url' => $register_url,
		] );
	}

	/**
	 * Render Custom Register Form [wc_custom_register].
	 *
	 * @param array<string, mixed> $atts
	 * @return string
	 */
	public static function render_register_shortcode( $atts = [] ): string {
		if ( is_user_logged_in() ) {
			return '';
		}

		$redirect  = isset( $_GET['redirect_to'] ) ? esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ) : '';
		$login_url = ! empty( $redirect )
			? add_query_arg( 'redirect_to', rawurlencode( $redirect ), home_url( '/login/' ) )
			: home_url( '/login/' );

		return TemplateLoader::get_template_html( 'auth-register.php', [
			'redirect'  => $redirect,
			'login_url' => $login_url,
		] );
	}

	/**
	 * Process Custom Registration Form Submission.
	 */
	public static function process_custom_registration(): void {
		if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) || ! isset( $_POST['my_wc_custom_register_submit'] ) ) {
			return;
		}

		// Verify Nonce
		if ( ! isset( $_POST['my_wc_register_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['my_wc_register_nonce'] ) ), 'my_wc_custom_register_action' ) ) {
			if ( function_exists( 'wc_add_notice' ) ) {
				wc_add_notice( __( 'การเชื่อมต่อหมดอายุ กรุณาลองใหม่อีกครั้ง', 'my-wc-passkeys' ), 'error' );
			}
			return;
		}

		$username         = isset( $_POST['username'] ) ? sanitize_user( wp_unslash( $_POST['username'] ) ) : '';
		$email            = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$password         = isset( $_POST['password'] ) ? (string) $_POST['password'] : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$password_confirm = isset( $_POST['password_confirm'] ) ? (string) $_POST['password_confirm'] : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		// 1. Validate Required Fields
		if ( empty( $email ) || ! is_email( $email ) ) {
			if ( function_exists( 'wc_add_notice' ) ) {
				wc_add_notice( __( 'กรุณากรอกที่อยู่อีเมลที่ถูกต้อง', 'my-wc-passkeys' ), 'error' );
			}
			return;
		}

		if ( empty( $password ) ) {
			if ( function_exists( 'wc_add_notice' ) ) {
				wc_add_notice( __( 'กรุณากรอกรหัสผ่าน', 'my-wc-passkeys' ), 'error' );
			}
			return;
		}

		// 2. Validate Password Confirmation Match
		if ( $password !== $password_confirm ) {
			if ( function_exists( 'wc_add_notice' ) ) {
				wc_add_notice( __( 'รหัสผ่านและการยืนยันรหัสผ่านไม่ตรงกัน', 'my-wc-passkeys' ), 'error' );
			}
			return;
		}

		// 3. Check Duplicate Email / Username
		if ( email_exists( $email ) ) {
			if ( function_exists( 'wc_add_notice' ) ) {
				wc_add_notice( __( 'อีเมลนี้ถูกใช้งานในระบบแล้ว กรุณาเข้าสู่ระบบหรือใช้อีเมลอื่น', 'my-wc-passkeys' ), 'error' );
			}
			return;
		}

		if ( empty( $username ) ) {
			$username = sanitize_user( current( explode( '@', $email ) ), true );
		}

		if ( username_exists( $username ) ) {
			$username = $username . '_' . wp_rand( 100, 999 );
		}

		// 4. Create Customer using WooCommerce Core Helper
		try {
			$new_customer_id = wc_create_new_customer( $email, $username, $password );

			if ( is_wp_error( $new_customer_id ) ) {
				if ( function_exists( 'wc_add_notice' ) ) {
					wc_add_notice( $new_customer_id->get_error_message(), 'error' );
				}
				return;
			}

			// 5. Auto Login
			wp_clear_auth_cookie();
			wp_set_current_user( $new_customer_id );
			wp_set_auth_cookie( $new_customer_id, true );

			$user = get_user_by( 'id', $new_customer_id );
			if ( $user ) {
				do_action( 'wp_login', $user->user_login, $user );
			}

			// 6. Redirect to My Account or Redirect param
			$redirect_url = isset( $_POST['redirect'] ) && ! empty( $_POST['redirect'] )
				? esc_url_raw( wp_unslash( $_POST['redirect'] ) )
				: ( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/my-account/' ) );

			wp_safe_redirect( $redirect_url );
			exit;

		} catch ( \Exception $e ) {
			if ( function_exists( 'wc_add_notice' ) ) {
				wc_add_notice( $e->getMessage(), 'error' );
			}
		}
	}

	/**
	 * Process Custom Login Form Submission.
	 */
	public static function process_custom_login(): void {
		if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) || ! isset( $_POST['my_wc_custom_login_submit'] ) ) {
			return;
		}

		// Verify Nonce
		if ( ! isset( $_POST['my_wc_login_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['my_wc_login_nonce'] ) ), 'my_wc_custom_login_action' ) ) {
			if ( function_exists( 'wc_add_notice' ) ) {
				wc_add_notice( __( 'การเชื่อมต่อหมดอายุ กรุณาลองใหม่อีกครั้ง', 'my-wc-passkeys' ), 'error' );
			}
			return;
		}

		$username   = isset( $_POST['username'] ) ? sanitize_user( wp_unslash( $_POST['username'] ) ) : '';
		$password   = isset( $_POST['password'] ) ? (string) $_POST['password'] : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$rememberme = ! empty( $_POST['rememberme'] );

		if ( empty( $username ) || empty( $password ) ) {
			if ( function_exists( 'wc_add_notice' ) ) {
				wc_add_notice( __( 'กรุณากรอกชื่อผู้ใช้และรหัสผ่าน', 'my-wc-passkeys' ), 'error' );
			}
			return;
		}

		// Authenticate with WordPress
		$creds = [
			'user_login'    => $username,
			'user_password' => $password,
			'remember'      => $rememberme,
		];

		$user = wp_signon( $creds, is_ssl() );

		if ( is_wp_error( $user ) ) {
			if ( function_exists( 'wc_add_notice' ) ) {
				wc_add_notice( $user->get_error_message(), 'error' );
			}
			return;
		}

		// Redirect
		$redirect_url = isset( $_POST['redirect'] ) && ! empty( $_POST['redirect'] )
			? esc_url_raw( wp_unslash( $_POST['redirect'] ) )
			: ( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/my-account/' ) );

		wp_safe_redirect( $redirect_url );
		exit;
	}
}
