<?php
declare(strict_types=1);

namespace MyWcPasskeys;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles template loading with theme override priority.
 * Checks {child-theme}/wc-passkeys/ -> {parent-theme}/wc-passkeys/ -> plugin/templates/
 */
class TemplateLoader {

	/**
	 * Locate template file with override support.
	 *
	 * @param string $template_name Template file name (e.g. 'auth-login.php')
	 * @param string $template_path Theme sub-folder name (default: 'wc-passkeys/')
	 * @param string $default_path  Plugin fallback path
	 * @return string Absolute path to template
	 */
	public static function locate_template( string $template_name, string $template_path = '', string $default_path = '' ): string {
		if ( empty( $template_path ) ) {
			$template_path = 'wc-passkeys/';
		}

		if ( empty( $default_path ) ) {
			$default_path = MY_WC_PASSKEYS_DIR . 'templates/';
		}

		// 1. Look within child theme / theme directory
		$template = locate_template( [
			trailingslashit( $template_path ) . $template_name,
			$template_name,
		] );

		// 2. Fallback to default plugin templates
		if ( ! $template || ! file_exists( $template ) ) {
			$template = $default_path . $template_name;
		}

		return apply_filters( 'my_wc_passkeys_locate_template', $template, $template_name, $template_path );
	}

	/**
	 * Include a template directly with passed arguments.
	 *
	 * @param string $template_name
	 * @param array<string, mixed> $args
	 */
	public static function get_template( string $template_name, array $args = [] ): void {
		$located = self::locate_template( $template_name );

		if ( ! file_exists( $located ) ) {
			return;
		}

		if ( ! empty( $args ) && is_array( $args ) ) {
			extract( $args ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		}

		do_action( 'my_wc_passkeys_before_template_part', $template_name, $located, $args );

		include $located;

		do_action( 'my_wc_passkeys_after_template_part', $template_name, $located, $args );
	}

	/**
	 * Retrieve template rendered as a string (Output Buffering).
	 *
	 * @param string $template_name
	 * @param array<string, mixed> $args
	 * @return string Rendered HTML output
	 */
	public static function get_template_html( string $template_name, array $args = [] ): string {
		ob_start();
		self::get_template( $template_name, $args );
		return (string) ob_get_clean();
	}
}
