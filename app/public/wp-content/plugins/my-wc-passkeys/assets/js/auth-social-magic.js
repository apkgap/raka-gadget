/**
 * Frontend JavaScript for Google Sign-In (GSI / One Tap) and Passwordless Magic Link / Email OTP Authentication.
 *
 * @package my-wc-passkeys
 */

(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', initAuth);

	function initAuth() {
		const config = window.myWcPasskeysData || {};
		initGoogleAuth(config);
		initMagicLinkAuth(config);
	}

	/**
	 * ── 1. Google Sign-In & Google One Tap Integration ──
	 */
	function initGoogleAuth(config) {
		const googleClientId = config.googleClientId;
		const isGoogleEnabled = config.isGoogleEnabled;
		const isOneTapEnabled = config.isOneTapEnabled;
		const restUrl = config.restUrl || '/wp-json/wc-passkeys/v1';
		const redirectTo = config.redirectTo || '';

		if (!isGoogleEnabled || !googleClientId) {
			// If google button is clicked without client ID configured, inform admin/user gracefully
			const googleBtn = document.getElementById('my-wc-google-login-btn');
			if (googleBtn && !googleClientId) {
				googleBtn.addEventListener('click', function (e) {
					if (googleBtn.getAttribute('href') === '#google-gsi' || googleBtn.getAttribute('href') === 'javascript:void(0);') {
						e.preventDefault();
						showAuthNotice('กรุณากำหนด Google Client ID ในหน้า Auth Settings ก่อนใช้งาน', 'error');
					}
				});
			}
			return;
		}

		// Load Google GSI Script dynamically if not already loaded
		if (!window.google || !window.google.accounts) {
			const script = document.createElement('script');
			script.src = 'https://accounts.google.com/gsi/client';
			script.async = true;
			script.defer = true;
			script.onload = onGsiLoaded;
			document.head.appendChild(script);
		} else {
			onGsiLoaded();
		}

		function onGsiLoaded() {
			if (!window.google || !window.google.accounts || !window.google.accounts.id) {
				return;
			}

			// Initialize Google Identity Services
			window.google.accounts.id.initialize({
				client_id: googleClientId,
				callback: handleGoogleCredentialResponse,
				auto_select: false,
				cancel_on_tap_outside: true,
			});

			// Show Google One Tap prompt if enabled and user is not logged in
			if (isOneTapEnabled && !config.isLoggedIn) {
				window.google.accounts.id.prompt((notification) => {
					if (notification.isNotDisplayed()) {
						console.log('[GSI] One Tap not displayed:', notification.getNotDisplayedReason());
					} else if (notification.isSkippedMoment()) {
						console.log('[GSI] One Tap skipped:', notification.getSkippedReason());
					} else if (notification.isDismissedMoment()) {
						console.log('[GSI] One Tap dismissed:', notification.getDismissedReason());
					}
				});
			}

			// Bind custom Google button
			const googleBtn = document.getElementById('my-wc-google-login-btn');
			if (googleBtn) {
				googleBtn.addEventListener('click', function (e) {
					e.preventDefault();
					setButtonLoading(googleBtn, true, 'กำลังเชื่อมต่อ Google...');

					// Prompt Google selection
					window.google.accounts.id.prompt((notification) => {
						if (notification.isNotDisplayed() || notification.isDismissedMoment()) {
							setButtonLoading(googleBtn, false);
						}
					});
				});
			}
		}

		// Handle Credential Response from Google
		function handleGoogleCredentialResponse(response) {
			if (!response || !response.credential) {
				showAuthNotice('ไม่สามารถดึงข้อมูลจาก Google ได้', 'error');
				return;
			}

			showAuthNotice('ยืนยันตัวตนสำเร็จ กำลังเข้าสู่ระบบด้วยบัญชี Google...', 'info');

			fetch(restUrl + '/auth/google', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': config.nonce || '',
				},
				body: JSON.stringify({
					credential: response.credential,
					redirect_to: redirectTo,
				}),
			})
				.then((res) => res.json())
				.then((data) => {
					if (data.success) {
						showAuthNotice(data.message || 'เข้าสู่ระบบสำเร็จ กำลังพาคุณไปยังหน้าเป้าหมาย...', 'success');
						setTimeout(() => {
							window.location.href = data.redirect_url || redirectTo || '/my-account/';
						}, 600);
					} else {
						showAuthNotice(data.message || 'เกิดข้อผิดพลาดในการเข้าสู่ระบบด้วย Google', 'error');
						const googleBtn = document.getElementById('my-wc-google-login-btn');
						if (googleBtn) setButtonLoading(googleBtn, false);
					}
				})
				.catch((err) => {
					console.error('[Google Auth Error]', err);
					showAuthNotice('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์', 'error');
					const googleBtn = document.getElementById('my-wc-google-login-btn');
					if (googleBtn) setButtonLoading(googleBtn, false);
				});
		}
	}

	/**
	 * ── 2. Passwordless Magic Link & Email OTP Integration ──
	 */
	function initMagicLinkAuth(config) {
		const isMagicEnabled = config.isMagicEnabled;
		const restUrl = config.restUrl || '/wp-json/wc-passkeys/v1';
		const redirectTo = config.redirectTo || '';

		const magicTabBtn = document.getElementById('my-wc-tab-magic');
		const passwordTabBtn = document.getElementById('my-wc-tab-password');
		const magicSection = document.getElementById('my-wc-magic-auth-section');
		const passwordSection = document.getElementById('my-wc-custom-login-form') || document.getElementById('my-wc-custom-register-form');

		// Tab switching between Password & Magic Link/OTP if both are active
		if (magicTabBtn && passwordTabBtn && magicSection && passwordSection) {
			magicTabBtn.addEventListener('click', function (e) {
				e.preventDefault();
				magicTabBtn.classList.add('active', 'border-black', 'dark:border-white', 'text-neutral-900', 'dark:text-white', 'font-bold');
				magicTabBtn.classList.remove('border-transparent', 'text-neutral-500');
				passwordTabBtn.classList.remove('active', 'border-black', 'dark:border-white', 'text-neutral-900', 'dark:text-white', 'font-bold');
				passwordTabBtn.classList.add('border-transparent', 'text-neutral-500');

				magicSection.style.display = 'block';
				passwordSection.style.display = 'none';
			});

			passwordTabBtn.addEventListener('click', function (e) {
				e.preventDefault();
				passwordTabBtn.classList.add('active', 'border-black', 'dark:border-white', 'text-neutral-900', 'dark:text-white', 'font-bold');
				passwordTabBtn.classList.remove('border-transparent', 'text-neutral-500');
				magicTabBtn.classList.remove('active', 'border-black', 'dark:border-white', 'text-neutral-900', 'dark:text-white', 'font-bold');
				magicTabBtn.classList.add('border-transparent', 'text-neutral-500');

				magicSection.style.display = 'none';
				passwordSection.style.display = 'block';
			});
		}

		if (!isMagicEnabled) {
			return;
		}

		// Send Magic Link / OTP Form
		const sendOtpForm = document.getElementById('my-wc-magic-send-form');
		const otpVerifyForm = document.getElementById('my-wc-otp-verify-form');
		const emailInput = document.getElementById('my_wc_magic_email');
		const sendBtn = document.getElementById('my-wc-magic-send-btn');
		const verifyBtn = document.getElementById('my-wc-otp-verify-btn');
		const resendBtn = document.getElementById('my-wc-otp-resend-btn');
		const otpDisplayEmail = document.getElementById('my-wc-otp-target-email');

		let currentEmail = '';
		let countdownTimer = null;

		if (sendOtpForm && sendBtn && emailInput) {
			sendOtpForm.addEventListener('submit', function (e) {
				e.preventDefault();
				const email = emailInput.value.trim();
				if (!email) {
					showAuthNotice('กรุณากรอกอีเมลของคุณ', 'error');
					return;
				}

				currentEmail = email;
				setButtonLoading(sendBtn, true, 'กำลังส่งรหัส OTP...');

				fetch(restUrl + '/auth/magic-link/send', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-WP-Nonce': config.nonce || '',
					},
					body: JSON.stringify({
						email: email,
						redirect_to: redirectTo,
					}),
				})
					.then((res) => res.json())
					.then((data) => {
						setButtonLoading(sendBtn, false);
						if (data.success) {
							showAuthNotice(data.message, 'success');
							if (otpDisplayEmail) otpDisplayEmail.textContent = email;

							// Switch to OTP verify view
							if (sendOtpForm) sendOtpForm.style.display = 'none';
							if (otpVerifyForm) {
								otpVerifyForm.style.display = 'block';
								const firstOtpInput = document.getElementById('my_wc_otp_1');
								if (firstOtpInput) firstOtpInput.focus();
							}

							startResendCountdown(60);
						} else {
							showAuthNotice(data.message || 'ไม่สามารถส่งรหัส OTP ได้ กรุณาลองใหม่อีกครั้ง', 'error');
						}
					})
					.catch((err) => {
						console.error('[Magic Send Error]', err);
						setButtonLoading(sendBtn, false);
						showAuthNotice('เกิดข้อผิดพลาดในการส่งคำขอ กรุณาลองใหม่อีกครั้ง', 'error');
					});
			});
		}

		// OTP 6-Digit Auto-Tab Navigation & Paste Support
		const otpInputs = document.querySelectorAll('.my-wc-otp-digit-input');
		otpInputs.forEach((input, index) => {
			input.addEventListener('input', function (e) {
				const val = this.value;
				if (val.length >= 1) {
					this.value = val.slice(-1);
					if (index < otpInputs.length - 1) {
						otpInputs[index + 1].focus();
					}
				}
			});

			input.addEventListener('keydown', function (e) {
				if (e.key === 'Backspace' && !this.value && index > 0) {
					otpInputs[index - 1].focus();
				}
			});

			input.addEventListener('paste', function (e) {
				e.preventDefault();
				const pasteData = (e.clipboardData || window.clipboardData).getData('text').trim();
				if (/^\d{6}$/.test(pasteData)) {
					pasteData.split('').forEach((char, i) => {
						if (otpInputs[i]) otpInputs[i].value = char;
					});
					if (otpInputs[otpInputs.length - 1]) {
						otpInputs[otpInputs.length - 1].focus();
					}
					// Auto submit
					if (otpVerifyForm) {
						otpVerifyForm.dispatchEvent(new Event('submit'));
					}
				}
			});
		});

		// Verify OTP Submission
		if (otpVerifyForm && verifyBtn) {
			otpVerifyForm.addEventListener('submit', function (e) {
				e.preventDefault();
				let otpCode = '';
				otpInputs.forEach((input) => {
					otpCode += input.value.trim();
				});

				if (otpCode.length !== 6) {
					showAuthNotice('กรุณากรอกรหัส OTP ให้ครบ 6 หลัก', 'error');
					return;
				}

				setButtonLoading(verifyBtn, true, 'กำลังตรวจสอบ OTP...');

				fetch(restUrl + '/auth/magic-link/verify-otp', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-WP-Nonce': config.nonce || '',
					},
					body: JSON.stringify({
						email: currentEmail || emailInput.value.trim(),
						otp_code: otpCode,
						redirect_to: redirectTo,
					}),
				})
					.then((res) => res.json())
					.then((data) => {
						if (data.success) {
							showAuthNotice(data.message || 'ยืนยันตัวตนสำเร็จ กำลังเข้าสู่ระบบ...', 'success');
							setTimeout(() => {
								window.location.href = data.redirect_url || redirectTo || '/my-account/';
							}, 600);
						} else {
							setButtonLoading(verifyBtn, false);
							showAuthNotice(data.message || 'รหัส OTP ไม่ถูกต้องหรือหมดอายุ', 'error');
						}
					})
					.catch((err) => {
						console.error('[OTP Verify Error]', err);
						setButtonLoading(verifyBtn, false);
						showAuthNotice('เกิดข้อผิดพลาดในการยืนยัน OTP', 'error');
					});
			});
		}

		// Resend OTP handler
		if (resendBtn) {
			resendBtn.addEventListener('click', function (e) {
				e.preventDefault();
				if (resendBtn.disabled) return;
				if (sendOtpForm) {
					sendOtpForm.dispatchEvent(new Event('submit'));
				}
			});
		}

		function startResendCountdown(seconds) {
			if (!resendBtn) return;
			clearInterval(countdownTimer);
			resendBtn.disabled = true;
			resendBtn.classList.add('opacity-50', 'cursor-not-allowed');

			let remaining = seconds;
			resendBtn.textContent = `ขอรหัสใหม่ได้ใน ${remaining}s`;

			countdownTimer = setInterval(() => {
				remaining--;
				if (remaining <= 0) {
					clearInterval(countdownTimer);
					resendBtn.disabled = false;
					resendBtn.classList.remove('opacity-50', 'cursor-not-allowed');
					resendBtn.textContent = 'ส่งรหัส OTP ใหม่อีกครั้ง';
				} else {
					resendBtn.textContent = `ขอรหัสใหม่ได้ใน ${remaining}s`;
				}
			}, 1000);
		}
	}

	/**
	 * Helper: Display inline toast/notice box
	 */
	function showAuthNotice(message, type) {
		let noticeBox = document.getElementById('my-wc-auth-dynamic-notice');
		if (!noticeBox) {
			const container = document.getElementById('my-wc-login-wrap') || document.getElementById('my-wc-register-wrap');
			if (container) {
				noticeBox = document.createElement('div');
				noticeBox.id = 'my-wc-auth-dynamic-notice';
				noticeBox.className = 'w-full p-3.5 rounded-xl text-sm font-medium transition-all mb-4 text-center';
				container.insertBefore(noticeBox, container.firstChild);
			}
		}

		if (!noticeBox) return;

		noticeBox.style.display = 'block';
		if (type === 'error') {
			noticeBox.className = 'w-full p-3.5 rounded-xl text-sm font-medium mb-4 text-center bg-red-50 dark:bg-red-950/50 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-800/60';
		} else if (type === 'success') {
			noticeBox.className = 'w-full p-3.5 rounded-xl text-sm font-medium mb-4 text-center bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60';
		} else {
			noticeBox.className = 'w-full p-3.5 rounded-xl text-sm font-medium mb-4 text-center bg-neutral-100 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-200 border border-neutral-300 dark:border-neutral-700';
		}

		noticeBox.textContent = message;
	}

	/**
	 * Helper: Set Button Loading State
	 */
	function setButtonLoading(btn, isLoading, text) {
		if (!btn) return;
		if (isLoading) {
			btn.disabled = true;
			btn.dataset.originalText = btn.innerHTML;
			btn.innerHTML = `<svg class="animate-spin -ml-1 mr-2 h-4 w-4 inline-block text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg> ${text || 'กำลังดำเนินการ...'}`;
		} else {
			btn.disabled = false;
			if (btn.dataset.originalText) {
				btn.innerHTML = btn.dataset.originalText;
			}
		}
	}
})();
