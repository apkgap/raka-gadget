<?php
/**
 * Dedicated Custom Login Template (X / Twitter Style - Horizontal 2 Column).
 * Floating active red labels, plump inputs & black pill buttons.
 * Supports: Google Sign-In / One Tap, LINE OAuth, Magic Link & Email OTP, Password, Passkeys.
 *
 * @package my-wc-passkeys
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use MyWcPasskeys\AuthSettings;
use MyWcPasskeys\SocialAuth;

$redirect_to         = ! empty( $redirect ) ? $redirect : ( isset( $_GET['redirect_to'] ) ? esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ) : AuthSettings::get_default_redirect_url() );
$register_url        = ! empty( $register_url ) ? $register_url : home_url( '/register/' );

$is_password_enabled = AuthSettings::is_password_enabled();
$is_google_enabled   = AuthSettings::is_google_enabled();
$is_line_enabled     = AuthSettings::is_line_enabled();
$is_magic_enabled    = AuthSettings::is_magic_link_enabled();
$is_passkey_enabled  = AuthSettings::is_passkey_enabled();

$line_auth_url       = $is_line_enabled ? SocialAuth::get_line_auth_url( $redirect_to ) : '';
$has_right_col       = $is_google_enabled || $is_line_enabled || $is_passkey_enabled;
?>

<div id="my-wc-auth-login-container" class="woocommerce my-wc-auth-box w-full max-w-2xl md:max-w-3xl mx-auto">

	<?php
	// Print WooCommerce error / success notices
	if ( function_exists( 'wc_print_notices' ) ) {
		wc_print_notices();
	}
	?>

	<div id="my-wc-login-wrap" class="space-y-6">

		<!-- Dynamic Notice Container for AJAX -->
		<div id="my-wc-auth-dynamic-notice" class="hidden w-full p-3.5 rounded-xl text-sm font-medium text-center"></div>

		<!-- ── 2-Column Horizontal Grid: Left = Form / OTP, Right = Social & Passkeys ── -->
		<div id="my-wc-login-grid" class="grid grid-cols-1 <?php echo $has_right_col ? 'md:grid-cols-2 gap-6 md:gap-10' : 'max-w-md mx-auto'; ?> items-start relative">

			<!-- ── LEFT COLUMN: Password or Magic Link & OTP ── -->
			<div id="my-wc-login-col-left" class="space-y-4">

				<?php if ( $is_password_enabled && $is_magic_enabled ) : ?>
					<!-- Auth Mode Switcher Tabs -->
					<div id="my-wc-auth-tabs" class="flex border-b border-black/10 dark:border-neutral-800 mb-2">
						<button
							type="button"
							id="my-wc-tab-password"
							class="active pb-2.5 px-3 text-sm sm:text-base font-bold text-neutral-900 dark:text-white border-b-2 border-black dark:border-white transition-all cursor-pointer"
						>
							<?php esc_html_e( 'ใช้รหัสผ่าน', 'my-wc-passkeys' ); ?>
						</button>
						<button
							type="button"
							id="my-wc-tab-magic"
							class="pb-2.5 px-3 text-sm sm:text-base font-medium text-neutral-500 border-b-2 border-transparent hover:text-neutral-900 dark:hover:text-white transition-all cursor-pointer"
						>
							<?php esc_html_e( 'Magic Link / OTP', 'my-wc-passkeys' ); ?>
						</button>
					</div>
				<?php endif; ?>

				<!-- 1. Password Form -->
				<?php if ( $is_password_enabled ) : ?>
					<form id="my-wc-custom-login-form" method="post" class="space-y-3.5">

						<!-- Username / Email (X Floating Label) -->
						<div id="my-wc-field-wrap-login" class="form-row relative w-full rounded-2xl border border-black/15 dark:border-neutral-700 bg-neutral-100/80 dark:bg-neutral-900/60 focus-within:border-red-500 focus-within:ring-2 focus-within:ring-red-500/20 transition-all">
							<input
								type="text"
								id="my_wc_login_username"
								name="username"
								placeholder=" "
								class="peer w-full h-13 sm:h-14 pt-4 pb-1 px-4 sm:px-5 bg-transparent border-0 text-neutral-900 dark:text-white text-sm sm:text-base font-sans focus:outline-none focus:ring-0 transition-all"
								autocomplete="username"
								required
							/>
							<label
								id="my-wc-lbl-username"
								for="my_wc_login_username"
								class="absolute left-4 sm:left-5 top-3.5 sm:top-4 text-neutral-500 text-sm sm:text-base font-sans pointer-events-none transition-all duration-200 origin-left transform peer-focus:-translate-y-2.5 peer-focus:text-xs peer-focus:text-red-500 peer-focus:font-medium peer-[:not(:placeholder-shown)]:-translate-y-2.5 peer-[:not(:placeholder-shown)]:text-xs peer-[:not(:placeholder-shown)]:text-neutral-500"
							>
								<?php esc_html_e( 'อีเมลหรือชื่อผู้ใช้', 'my-wc-passkeys' ); ?>
							</label>
						</div>

						<!-- Password (X Floating Label) -->
						<div id="my-wc-field-wrap-password" class="form-row relative w-full rounded-2xl border border-black/15 dark:border-neutral-700 bg-neutral-100/80 dark:bg-neutral-900/60 focus-within:border-red-500 focus-within:ring-2 focus-within:ring-red-500/20 transition-all">
							<input
								type="password"
								id="my_wc_login_password"
								name="password"
								placeholder=" "
								class="peer w-full h-13 sm:h-14 pt-4 pb-1 px-4 sm:px-5 bg-transparent border-0 text-neutral-900 dark:text-white text-sm sm:text-base font-sans focus:outline-none focus:ring-0 transition-all"
								autocomplete="current-password"
								required
							/>
							<label
								id="my-wc-lbl-password"
								for="my_wc_login_password"
								class="absolute left-4 sm:left-5 top-3.5 sm:top-4 text-neutral-500 text-sm sm:text-base font-sans pointer-events-none transition-all duration-200 origin-left transform peer-focus:-translate-y-2.5 peer-focus:text-xs peer-focus:text-red-500 peer-focus:font-medium peer-[:not(:placeholder-shown)]:-translate-y-2.5 peer-[:not(:placeholder-shown)]:text-xs peer-[:not(:placeholder-shown)]:text-neutral-500"
							>
								<?php esc_html_e( 'รหัสผ่าน', 'my-wc-passkeys' ); ?>
							</label>
						</div>

						<!-- Remember Me & Lost Password -->
						<div id="my-wc-auth-options-wrap" class="flex items-center justify-between text-xs sm:text-sm pt-0.5">
							<div id="my-wc-field-wrap-remember" class="flex items-center gap-2">
								<input type="checkbox" id="my_wc_rememberme" name="rememberme" value="forever" class="w-4 h-4 rounded accent-black cursor-pointer" />
								<label id="my-wc-lbl-remember" for="my_wc_rememberme" class="text-neutral-600 dark:text-neutral-400 cursor-pointer select-none">
									<?php esc_html_e( 'จดจำฉัน', 'my-wc-passkeys' ); ?>
								</label>
							</div>
							<a id="my-wc-link-lost-password" href="<?php echo esc_url( wp_lostpassword_url() ); ?>" class="text-neutral-500 hover:text-neutral-900 dark:hover:text-white underline">
								<?php esc_html_e( 'ลืมรหัสผ่าน?', 'my-wc-passkeys' ); ?>
							</a>
						</div>

						<!-- CAPTCHA CONTAINER -->
						<?php if ( class_exists( '\MyWcPasskeys\Captcha' ) ) : ?>
							<div id="my-wc-captcha-wrap" class="flex items-center justify-center pt-1 overflow-hidden">
								<?php echo \MyWcPasskeys\Captcha::render( 'auto' ); ?>
							</div>
						<?php endif; ?>

						<!-- Submit Button -->
						<div id="my-wc-field-wrap-submit" class="pt-1.5">
							<?php wp_nonce_field( 'my_wc_custom_login_action', 'my_wc_login_nonce' ); ?>
							<input id="my_wc_login_redirect" type="hidden" name="redirect" value="<?php echo esc_attr( $redirect_to ); ?>" />
							<button
								id="my_wc_login_submit_btn"
								type="submit"
								name="my_wc_custom_login_submit"
								value="1"
								class="w-full h-12 sm:h-13 rounded-full bg-neutral-950 hover:bg-neutral-800 text-white text-sm sm:text-base font-bold border border-neutral-900 transition-all shadow-sm hover:shadow-md cursor-pointer flex items-center justify-center gap-2"
							>
								<span id="my-wc-login-submit-text"><?php esc_html_e( 'เข้าสู่ระบบ', 'my-wc-passkeys' ); ?></span>
							</button>
						</div>

						<?php
						do_action( 'woocommerce_login_form_end' );
						do_action( 'login_form' );
						?>

					</form>
				<?php endif; ?>

				<!-- 2. Magic Link & Email OTP Section -->
				<?php if ( $is_magic_enabled ) : ?>
					<div id="my-wc-magic-auth-section" class="space-y-4" style="<?php echo ( $is_password_enabled ) ? 'display: none;' : ''; ?>">
						<!-- Step 1: Email Form -->
						<form id="my-wc-magic-send-form" class="space-y-3.5">
							<p id="my-wc-magic-hint" class="text-xs text-neutral-500 leading-relaxed">
								<?php esc_html_e( 'กรอกอีเมลของคุณเพื่อรับรหัส OTP 6 หลัก และลิงก์เข้าสู่ระบบได้ทันทีโดยไม่ต้องจำรหัสผ่าน', 'my-wc-passkeys' ); ?>
							</p>

							<div id="my-wc-field-wrap-magic-email" class="form-row relative w-full rounded-2xl border border-black/15 dark:border-neutral-700 bg-neutral-100/80 dark:bg-neutral-900/60 focus-within:border-red-500 focus-within:ring-2 focus-within:ring-red-500/20 transition-all">
								<input
									type="email"
									id="my_wc_magic_email"
									name="email"
									placeholder=" "
									class="peer w-full h-13 sm:h-14 pt-4 pb-1 px-4 sm:px-5 bg-transparent border-0 text-neutral-900 dark:text-white text-sm sm:text-base font-sans focus:outline-none focus:ring-0 transition-all"
									autocomplete="email"
									required
								/>
								<label
									id="my-wc-lbl-magic-email"
									for="my_wc_magic_email"
									class="absolute left-4 sm:left-5 top-3.5 sm:top-4 text-neutral-500 text-sm sm:text-base font-sans pointer-events-none transition-all duration-200 origin-left transform peer-focus:-translate-y-2.5 peer-focus:text-xs peer-focus:text-red-500 peer-focus:font-medium peer-[:not(:placeholder-shown)]:-translate-y-2.5 peer-[:not(:placeholder-shown)]:text-xs peer-[:not(:placeholder-shown)]:text-neutral-500"
								>
									<?php esc_html_e( 'ที่อยู่อีเมลของคุณ', 'my-wc-passkeys' ); ?>
								</label>
							</div>

							<div id="my-wc-field-wrap-magic-submit" class="pt-1.5">
								<button
									id="my-wc-magic-send-btn"
									type="submit"
									class="w-full h-12 sm:h-13 rounded-full bg-neutral-950 hover:bg-neutral-800 text-white text-sm sm:text-base font-bold border border-neutral-900 transition-all shadow-sm hover:shadow-md cursor-pointer flex items-center justify-center gap-2"
								>
									<span id="my-wc-magic-send-text"><?php esc_html_e( 'ส่งรหัส OTP / ลิงก์เข้าสู่ระบบ', 'my-wc-passkeys' ); ?></span>
								</button>
							</div>
						</form>

						<!-- Step 2: OTP Verification Form (Hidden until OTP sent) -->
						<form id="my-wc-otp-verify-form" class="space-y-4" style="display: none;">
							<div id="my-wc-otp-info" class="text-center space-y-1">
								<span id="my-wc-otp-sent-label" class="text-xs text-neutral-500"><?php esc_html_e( 'รหัส OTP ถูกส่งไปยังอีเมล:', 'my-wc-passkeys' ); ?></span>
								<div id="my-wc-otp-target-email" class="text-sm font-bold text-neutral-900 dark:text-white"></div>
							</div>

							<!-- 6-Digit OTP Inputs -->
							<div id="my-wc-otp-boxes" class="flex justify-center items-center gap-2 sm:gap-2.5">
								<input type="text" id="my_wc_otp_1" class="my-wc-otp-digit-input w-11 h-13 sm:w-12 sm:h-14 text-center text-xl font-bold rounded-xl border border-black/20 dark:border-neutral-700 bg-neutral-100/80 dark:bg-neutral-900 text-neutral-900 dark:text-white focus:outline-none focus:border-red-500 focus:ring-2 focus:ring-red-500/20" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="one-time-code" required />
								<input type="text" id="my_wc_otp_2" class="my-wc-otp-digit-input w-11 h-13 sm:w-12 sm:h-14 text-center text-xl font-bold rounded-xl border border-black/20 dark:border-neutral-700 bg-neutral-100/80 dark:bg-neutral-900 text-neutral-900 dark:text-white focus:outline-none focus:border-red-500 focus:ring-2 focus:ring-red-500/20" maxlength="1" pattern="[0-9]" inputmode="numeric" required />
								<input type="text" id="my_wc_otp_3" class="my-wc-otp-digit-input w-11 h-13 sm:w-12 sm:h-14 text-center text-xl font-bold rounded-xl border border-black/20 dark:border-neutral-700 bg-neutral-100/80 dark:bg-neutral-900 text-neutral-900 dark:text-white focus:outline-none focus:border-red-500 focus:ring-2 focus:ring-red-500/20" maxlength="1" pattern="[0-9]" inputmode="numeric" required />
								<input type="text" id="my_wc_otp_4" class="my-wc-otp-digit-input w-11 h-13 sm:w-12 sm:h-14 text-center text-xl font-bold rounded-xl border border-black/20 dark:border-neutral-700 bg-neutral-100/80 dark:bg-neutral-900 text-neutral-900 dark:text-white focus:outline-none focus:border-red-500 focus:ring-2 focus:ring-red-500/20" maxlength="1" pattern="[0-9]" inputmode="numeric" required />
								<input type="text" id="my_wc_otp_5" class="my-wc-otp-digit-input w-11 h-13 sm:w-12 sm:h-14 text-center text-xl font-bold rounded-xl border border-black/20 dark:border-neutral-700 bg-neutral-100/80 dark:bg-neutral-900 text-neutral-900 dark:text-white focus:outline-none focus:border-red-500 focus:ring-2 focus:ring-red-500/20" maxlength="1" pattern="[0-9]" inputmode="numeric" required />
								<input type="text" id="my_wc_otp_6" class="my-wc-otp-digit-input w-11 h-13 sm:w-12 sm:h-14 text-center text-xl font-bold rounded-xl border border-black/20 dark:border-neutral-700 bg-neutral-100/80 dark:bg-neutral-900 text-neutral-900 dark:text-white focus:outline-none focus:border-red-500 focus:ring-2 focus:ring-red-500/20" maxlength="1" pattern="[0-9]" inputmode="numeric" required />
							</div>

							<!-- Verify Button -->
							<div id="my-wc-otp-actions" class="space-y-2 pt-1">
								<button
									id="my-wc-otp-verify-btn"
									type="submit"
									class="w-full h-12 sm:h-13 rounded-full bg-neutral-950 hover:bg-neutral-800 text-white text-sm sm:text-base font-bold border border-neutral-900 transition-all shadow-sm hover:shadow-md cursor-pointer flex items-center justify-center gap-2"
								>
									<span id="my-wc-otp-verify-text"><?php esc_html_e( 'ยืนยันรหัส OTP และเข้าสู่ระบบ', 'my-wc-passkeys' ); ?></span>
								</button>

								<div id="my-wc-otp-resend-wrap" class="text-center">
									<button
										type="button"
										id="my-wc-otp-resend-btn"
										class="text-xs sm:text-sm text-neutral-500 hover:text-neutral-900 dark:hover:text-white underline cursor-pointer"
									>
										<?php esc_html_e( 'ส่งรหัส OTP ใหม่อีกครั้ง', 'my-wc-passkeys' ); ?>
									</button>
								</div>
							</div>
						</form>
					</div>
				<?php endif; ?>

			</div>

			<!-- ── RIGHT COLUMN: Quick Social & Passkey Buttons ── -->
			<?php if ( $has_right_col ) : ?>
				<div id="my-wc-login-col-right" class="space-y-4 md:border-l md:border-black/10 md:dark:border-neutral-800 md:pl-8 lg:pl-10">
					<div id="my-wc-login-quick-auth-wrap" class="space-y-3">

						<!-- 1. Google Login Button -->
						<?php if ( $is_google_enabled ) : ?>
							<a
								id="my-wc-google-login-btn"
								href="#google-gsi"
								class="w-full h-12 sm:h-13 rounded-full bg-white hover:bg-neutral-100 text-neutral-900 text-sm sm:text-base font-semibold border border-neutral-300 dark:border-transparent flex items-center justify-center gap-3 transition-all shadow-xs hover:shadow-sm cursor-pointer select-none"
							>
								<svg id="my-wc-google-login-svg" class="w-5 h-5 shrink-0" viewBox="0 0 24 24">
									<path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.82-2.4 3.68v3.05h3.88c2.27-2.09 3.66-5.17 3.66-9.17z"/>
									<path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.25v3.15C3.26 21.36 7.33 24 12 24z"/>
									<path fill="#FBBC05" d="M5.28 14.27c-.25-.72-.38-1.49-.38-2.27s.13-1.55.38-2.27V6.58H1.25C.45 8.18 0 10.04 0 12s.45 3.82 1.25 5.42l4.03-3.15z"/>
									<path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.33 0 3.26 2.64 1.25 6.58l4.03 3.15c.95-2.83 3.6-4.98 6.72-4.98z"/>
								</svg>
								<span id="my-wc-google-login-text" class="leading-none"><?php esc_html_e( 'ดำเนินการต่อด้วย Google', 'my-wc-passkeys' ); ?></span>
							</a>
						<?php endif; ?>

						<!-- 2. LINE Login Button -->
						<?php if ( $is_line_enabled ) : ?>
							<a
								id="my-wc-line-login-btn"
								href="<?php echo ! empty( $line_auth_url ) ? esc_url( $line_auth_url ) : 'javascript:alert(\'กรุณากำหนด LINE Channel ID ในหน้าการตั้งค่าก่อน\');'; ?>"
								class="w-full h-12 sm:h-13 rounded-full bg-[#06C755] hover:bg-[#05b34c] text-white text-sm sm:text-base font-semibold border border-[#06C755] flex items-center justify-center gap-3 transition-all shadow-xs hover:shadow-sm cursor-pointer select-none"
							>
								<svg id="my-wc-line-login-svg" class="w-5 h-5 shrink-0 fill-current" viewBox="0 0 24 24">
									<path d="M24 10.304c0-5.369-5.383-9.738-12-9.738-6.616 0-12 4.369-12 9.738 0 4.814 4.269 8.846 10.039 9.608.391.084.922.258 1.057.592.121.303.079.777.039 1.085l-.171 1.027c-.053.303-.242 1.186 1.039.647 1.281-.54 6.911-4.069 9.428-6.967 1.739-1.907 2.569-3.864 2.569-5.392zm-14.896 2.378h-2.186a.557.557 0 0 1-.557-.557v-4.05a.557.557 0 0 1 .557-.557h2.186a.557.557 0 1 1 0 1.114h-1.629v.911h1.629a.557.557 0 1 1 0 1.114h-1.629v.925h1.629a.557.557 0 1 1 0 1.114zm3.03 0a.557.557 0 0 1-.557-.557v-4.05a.557.557 0 1 1 1.114 0v4.05a.557.557 0 0 1-.557.557zm4.27 0h-2.186a.557.557 0 0 1-.557-.557v-4.05a.557.557 0 0 1 .557-.557.557.557 0 0 1 .557.557v3.493h1.629a.557.557 0 1 1 0 1.114zm3.629-1.393a.557.557 0 0 1-.163.394l-2.072 2.071a.557.557 0 0 1-.788-.788l1.678-1.677-1.678-1.678a.557.557 0 1 1 .788-.788l2.072 2.071a.557.557 0 0 1 .163.395z"/>
								</svg>
								<span id="my-wc-line-login-text" class="leading-none"><?php esc_html_e( 'ดำเนินการต่อด้วย LINE', 'my-wc-passkeys' ); ?></span>
							</a>
						<?php endif; ?>

						<!-- 3. One-Pass (Passkeys / Biometrics) Login Button -->
						<?php if ( $is_passkey_enabled ) : ?>
							<button
								type="button"
								id="my-wc-passkey-login-btn"
								class="w-full h-12 sm:h-13 rounded-full bg-neutral-950 hover:bg-neutral-800 text-white text-sm sm:text-base font-semibold border border-neutral-900 flex items-center justify-center gap-2.5 transition-all shadow-sm hover:shadow-md cursor-pointer"
							>
								<svg id="my-wc-passkey-icon" class="my-wc-passkey-svg shrink-0 text-white" viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
									<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>
								</svg>
								<span id="my-wc-passkey-btn-text" class="leading-none"><?php esc_html_e( 'ดำเนินการต่อด้วย One-Pass', 'my-wc-passkeys' ); ?></span>
							</button>

							<div id="my-wc-passkey-login-status" class="my-wc-passkey-status-msg text-xs" style="display: none;"></div>
						<?php endif; ?>

					</div>

					<!-- ── Terms & Policies Disclaimer ── -->
					<div id="my-wc-auth-disclaimer" class="pt-1">
						<p id="my-wc-auth-disclaimer-text" class="text-[11px] leading-relaxed text-neutral-500 dark:text-neutral-400">
							<?php esc_html_e( 'การดำเนินการต่อ แสดงว่าคุณยอมรับ', 'my-wc-passkeys' ); ?>
							<a id="my-wc-terms-link" href="#" class="text-neutral-800 dark:text-neutral-200 underline hover:text-black dark:hover:text-white"><?php esc_html_e( 'ข้อกำหนดการใช้งาน', 'my-wc-passkeys' ); ?></a>,
							<a id="my-wc-privacy-link" href="#" class="text-neutral-800 dark:text-neutral-200 underline hover:text-black dark:hover:text-white"><?php esc_html_e( 'นโยบายความเป็นส่วนตัว', 'my-wc-passkeys' ); ?></a>
							<?php esc_html_e( 'และ', 'my-wc-passkeys' ); ?>
							<a id="my-wc-cookies-link" href="#" class="text-neutral-800 dark:text-neutral-200 underline hover:text-black dark:hover:text-white"><?php esc_html_e( 'การใช้คุกกี้', 'my-wc-passkeys' ); ?></a> <?php esc_html_e( 'ของเรา', 'my-wc-passkeys' ); ?>
						</p>
					</div>
				</div>
			<?php endif; ?>

		</div>

		<!-- ── Bottom Switch to Register Link ── -->
		<div id="my-wc-switch-to-register" class="pt-4 border-t border-black/10 dark:border-neutral-800 text-center text-xs sm:text-sm text-neutral-600 dark:text-neutral-400">
			<span id="my-wc-switch-register-desc" class="inline-block"><?php esc_html_e( 'ยังไม่มีบัญชีใช่ไหม?', 'my-wc-passkeys' ); ?></span>
			<a id="my-wc-register-link" href="<?php echo esc_url( $register_url ); ?>" class="font-bold text-neutral-900 dark:text-white underline ml-1 hover:text-red-600 dark:hover:text-red-400 transition-colors">
				<?php esc_html_e( 'สมัครสมาชิก', 'my-wc-passkeys' ); ?>
			</a>
		</div>

	</div>

</div>
