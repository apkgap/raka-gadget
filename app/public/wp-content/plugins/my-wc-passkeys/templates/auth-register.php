<?php
/**
 * Dedicated Custom Register Template (X / Twitter Style - Horizontal 2 Column).
 * Floating active red labels, plump inputs & black pill buttons.
 * Override by copying to yourtheme/wc-passkeys/auth-register.php
 *
 * @package my-wc-passkeys
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$redirect_to      = ! empty( $redirect ) ? $redirect : ( isset( $_GET['redirect_to'] ) ? esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ) : '' );
$login_url        = ! empty( $login_url ) ? $login_url : home_url( '/login/' );
$google_oauth_url = apply_filters( 'my_wc_google_auth_url', home_url( '/wp-login.php?loginSocial=google' ) );
?>

<div id="my-wc-auth-register-container" class="woocommerce my-wc-auth-box w-full max-w-2xl md:max-w-3xl mx-auto">

	<?php
	// Print WooCommerce notices
	if ( function_exists( 'wc_print_notices' ) ) {
		wc_print_notices();
	}
	?>

	<div id="my-wc-register-wrap" class="space-y-6">

		<!-- ── 2-Column Horizontal Grid: Left = Register Form, Right = Google & One-Pass ── -->
		<div id="my-wc-register-grid" class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-10 items-start relative">

			<!-- ── LEFT COLUMN: Standard Registration Form (X Floating Active Red Labels) ── -->
			<div id="my-wc-register-col-left" class="space-y-4">
				<form id="my-wc-custom-register-form" method="post" class="space-y-3.5">

					<!-- Username -->
					<div id="my-wc-reg-field-wrap-username" class="form-row relative w-full rounded-2xl border border-black/15 dark:border-neutral-700 bg-neutral-100/80 dark:bg-neutral-900/60 focus-within:border-red-500 focus-within:ring-2 focus-within:ring-red-500/20 transition-all">
						<input
							type="text"
							id="my_wc_reg_username"
							name="username"
							placeholder=" "
							class="peer w-full h-13 sm:h-14 pt-4 pb-1 px-4 sm:px-5 bg-transparent border-0 text-neutral-900 dark:text-white text-sm sm:text-base font-sans focus:outline-none focus:ring-0 transition-all"
							autocomplete="username"
						/>
						<label
							id="my-wc-reg-lbl-username"
							for="my_wc_reg_username"
							class="absolute left-4 sm:left-5 top-3.5 sm:top-4 text-neutral-500 text-sm sm:text-base font-sans pointer-events-none transition-all duration-200 origin-left transform peer-focus:-translate-y-2.5 peer-focus:text-xs peer-focus:text-red-500 peer-focus:font-medium peer-[:not(:placeholder-shown)]:-translate-y-2.5 peer-[:not(:placeholder-shown)]:text-xs peer-[:not(:placeholder-shown)]:text-neutral-500"
						>
							<?php esc_html_e( 'ชื่อผู้ใช้ (Username)', 'my-wc-passkeys' ); ?>
						</label>
					</div>

					<!-- Email -->
					<div id="my-wc-reg-field-wrap-email" class="form-row relative w-full rounded-2xl border border-black/15 dark:border-neutral-700 bg-neutral-100/80 dark:bg-neutral-900/60 focus-within:border-red-500 focus-within:ring-2 focus-within:ring-red-500/20 transition-all">
						<input
							type="email"
							id="my_wc_reg_email"
							name="email"
							placeholder=" "
							class="peer w-full h-13 sm:h-14 pt-4 pb-1 px-4 sm:px-5 bg-transparent border-0 text-neutral-900 dark:text-white text-sm sm:text-base font-sans focus:outline-none focus:ring-0 transition-all"
							autocomplete="email"
							required
						/>
						<label
							id="my-wc-reg-lbl-email"
							for="my_wc_reg_email"
							class="absolute left-4 sm:left-5 top-3.5 sm:top-4 text-neutral-500 text-sm sm:text-base font-sans pointer-events-none transition-all duration-200 origin-left transform peer-focus:-translate-y-2.5 peer-focus:text-xs peer-focus:text-red-500 peer-focus:font-medium peer-[:not(:placeholder-shown)]:-translate-y-2.5 peer-[:not(:placeholder-shown)]:text-xs peer-[:not(:placeholder-shown)]:text-neutral-500"
						>
							<?php esc_html_e( 'ที่อยู่อีเมล', 'my-wc-passkeys' ); ?>
						</label>
					</div>

					<!-- Password -->
					<div id="my-wc-reg-field-wrap-password" class="form-row relative w-full rounded-2xl border border-black/15 dark:border-neutral-700 bg-neutral-100/80 dark:bg-neutral-900/60 focus-within:border-red-500 focus-within:ring-2 focus-within:ring-red-500/20 transition-all">
						<input
							type="password"
							id="my_wc_reg_password"
							name="password"
							placeholder=" "
							class="peer w-full h-13 sm:h-14 pt-4 pb-1 px-4 sm:px-5 bg-transparent border-0 text-neutral-900 dark:text-white text-sm sm:text-base font-sans focus:outline-none focus:ring-0 transition-all"
							autocomplete="new-password"
							required
						/>
						<label
							id="my-wc-reg-lbl-password"
							for="my_wc_reg_password"
							class="absolute left-4 sm:left-5 top-3.5 sm:top-4 text-neutral-500 text-sm sm:text-base font-sans pointer-events-none transition-all duration-200 origin-left transform peer-focus:-translate-y-2.5 peer-focus:text-xs peer-focus:text-red-500 peer-focus:font-medium peer-[:not(:placeholder-shown)]:-translate-y-2.5 peer-[:not(:placeholder-shown)]:text-xs peer-[:not(:placeholder-shown)]:text-neutral-500"
						>
							<?php esc_html_e( 'รหัสผ่าน', 'my-wc-passkeys' ); ?>
						</label>
					</div>

					<!-- Confirm Password -->
					<div id="my-wc-reg-field-wrap-confirm" class="form-row relative w-full rounded-2xl border border-black/15 dark:border-neutral-700 bg-neutral-100/80 dark:bg-neutral-900/60 focus-within:border-red-500 focus-within:ring-2 focus-within:ring-red-500/20 transition-all">
						<input
							type="password"
							id="my_wc_reg_password_confirm"
							name="password_confirm"
							placeholder=" "
							class="peer w-full h-13 sm:h-14 pt-4 pb-1 px-4 sm:px-5 bg-transparent border-0 text-neutral-900 dark:text-white text-sm sm:text-base font-sans focus:outline-none focus:ring-0 transition-all"
							autocomplete="new-password"
							required
						/>
						<label
							id="my-wc-reg-lbl-confirm"
							for="my_wc_reg_password_confirm"
							class="absolute left-4 sm:left-5 top-3.5 sm:top-4 text-neutral-500 text-sm sm:text-base font-sans pointer-events-none transition-all duration-200 origin-left transform peer-focus:-translate-y-2.5 peer-focus:text-xs peer-focus:text-red-500 peer-focus:font-medium peer-[:not(:placeholder-shown)]:-translate-y-2.5 peer-[:not(:placeholder-shown)]:text-xs peer-[:not(:placeholder-shown)]:text-neutral-500"
						>
							<?php esc_html_e( 'ยืนยันรหัสผ่านอีกครั้ง', 'my-wc-passkeys' ); ?>
						</label>
					</div>

					<!-- 🛡️ CAPTCHA WIDGET CONTAINER (Above Submit Button) -->
					<?php if ( class_exists( '\MyWcPasskeys\Captcha' ) ) : ?>
						<div id="my-wc-reg-captcha-wrap" class="flex items-center justify-center pt-1 overflow-hidden">
							<?php echo \MyWcPasskeys\Captcha::render( 'auto' ); ?>
						</div>
					<?php endif; ?>

					<!-- Submit Button (Black Pill Button, White Text) -->
					<div id="my-wc-reg-field-wrap-submit" class="pt-1.5">
						<?php wp_nonce_field( 'my_wc_custom_register_action', 'my_wc_register_nonce' ); ?>
						<input id="my_wc_reg_redirect" type="hidden" name="redirect" value="<?php echo esc_attr( $redirect_to ); ?>" />
						<button
							id="my_wc_register_submit_btn"
							type="submit"
							name="my_wc_custom_register_submit"
							value="1"
							class="w-full h-12 sm:h-13 rounded-full bg-neutral-950 hover:bg-neutral-800 text-white text-sm sm:text-base font-bold border border-neutral-900 transition-all shadow-sm hover:shadow-md cursor-pointer flex items-center justify-center gap-2"
						>
							<span id="my-wc-reg-submit-text"><?php esc_html_e( 'สร้างบัญชี', 'my-wc-passkeys' ); ?></span>
						</button>
					</div>

					<?php
					do_action( 'woocommerce_register_form_end' );
					do_action( 'register_form' );
					?>

				</form>
			</div>

			<!-- ── RIGHT COLUMN: Quick Social & Passkey Buttons ── -->
			<div id="my-wc-register-col-right" class="space-y-4 md:border-l md:border-black/10 md:dark:border-neutral-800 md:pl-8 lg:pl-10">
				<div id="my-wc-register-quick-auth-wrap" class="space-y-3">
					<!-- Google / Gmail Register Button -->
					<a
						id="my-wc-google-register-btn"
						href="<?php echo esc_url( $google_oauth_url ); ?>"
						class="w-full h-12 sm:h-13 rounded-full bg-white hover:bg-neutral-100 text-neutral-900 text-sm sm:text-base font-semibold border border-neutral-300 dark:border-transparent flex items-center justify-center gap-3 transition-all shadow-xs hover:shadow-sm cursor-pointer select-none"
					>
						<svg id="my-wc-google-register-svg" class="w-5 h-5 shrink-0" viewBox="0 0 24 24">
							<path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.82-2.4 3.68v3.05h3.88c2.27-2.09 3.66-5.17 3.66-9.17z"/>
							<path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.25v3.15C3.26 21.36 7.33 24 12 24z"/>
							<path fill="#FBBC05" d="M5.28 14.27c-.25-.72-.38-1.49-.38-2.27s.13-1.55.38-2.27V6.58H1.25C.45 8.18 0 10.04 0 12s.45 3.82 1.25 5.42l4.03-3.15z"/>
							<path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.33 0 3.26 2.64 1.25 6.58l4.03 3.15c.95-2.83 3.6-4.98 6.72-4.98z"/>
						</svg>
						<span id="my-wc-google-register-text" class="leading-none"><?php esc_html_e( 'สมัครด้วย Google', 'my-wc-passkeys' ); ?></span>
					</a>

					<!-- One-Pass (Passkeys / Biometrics) Register Button -->
					<button
						type="button"
						id="my-wc-passkey-register-auth-btn"
						class="w-full h-12 sm:h-13 rounded-full bg-neutral-950 hover:bg-neutral-800 text-white text-sm sm:text-base font-semibold border border-neutral-900 flex items-center justify-center gap-2.5 transition-all shadow-sm hover:shadow-md cursor-pointer"
					>
						<svg id="my-wc-reg-passkey-icon" class="my-wc-passkey-svg shrink-0 text-white" viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
							<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>
						</svg>
						<span id="my-wc-reg-passkey-btn-text" class="leading-none"><?php esc_html_e( 'สมัครด้วย One-Pass', 'my-wc-passkeys' ); ?></span>
					</button>

					<div id="my-wc-passkey-register-status" class="my-wc-passkey-status-msg text-xs" style="display: none;"></div>
				</div>

				<!-- ── X Terms & Policies Disclaimer ── -->
				<div id="my-wc-reg-disclaimer" class="pt-1">
					<p id="my-wc-reg-disclaimer-text" class="text-[11px] leading-relaxed text-neutral-500 dark:text-neutral-400">
						<?php esc_html_e( 'การสมัครสมาชิก แสดงว่าคุณยอมรับ', 'my-wc-passkeys' ); ?>
						<a id="my-wc-reg-terms-link" href="#" class="text-neutral-800 dark:text-neutral-200 underline hover:text-black dark:hover:text-white"><?php esc_html_e( 'ข้อกำหนดการใช้งาน', 'my-wc-passkeys' ); ?></a>,
						<a id="my-wc-reg-privacy-link" href="#" class="text-neutral-800 dark:text-neutral-200 underline hover:text-black dark:hover:text-white"><?php esc_html_e( 'นโยบายความเป็นส่วนตัว', 'my-wc-passkeys' ); ?></a>
						<?php esc_html_e( 'และ', 'my-wc-passkeys' ); ?>
						<a id="my-wc-reg-cookies-link" href="#" class="text-neutral-800 dark:text-neutral-200 underline hover:text-black dark:hover:text-white"><?php esc_html_e( 'การใช้คุกกี้', 'my-wc-passkeys' ); ?></a> <?php esc_html_e( 'ของเรา', 'my-wc-passkeys' ); ?>
					</p>
				</div>
			</div>

		</div>

		<!-- ── Bottom Switch to Login Link ── -->
		<div id="my-wc-switch-to-login" class="pt-4 border-t border-black/10 dark:border-neutral-800 text-center text-xs sm:text-sm text-neutral-600 dark:text-neutral-400">
			<span id="my-wc-switch-login-desc" class="inline-block"><?php esc_html_e( 'มีบัญชีอยู่แล้วใช่ไหม?', 'my-wc-passkeys' ); ?></span>
			<a id="my-wc-login-link" href="<?php echo esc_url( $login_url ); ?>" class="font-bold text-neutral-900 dark:text-white underline ml-1 hover:text-red-600 dark:hover:text-red-400 transition-colors">
				<?php esc_html_e( 'เข้าสู่ระบบ', 'my-wc-passkeys' ); ?>
			</a>
		</div>

	</div>

</div>
