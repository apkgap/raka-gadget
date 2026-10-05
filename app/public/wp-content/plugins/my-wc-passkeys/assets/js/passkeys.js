/**
 * Vanilla JavaScript WebAuthn / FIDO2 Passkeys Client for WooCommerce.
 * Compatible with modern browsers supporting Web Authentication API.
 *
 * @package my-wc-passkeys
 */

(function () {
	'use strict';

	// Config passed from WordPress
	const config = window.myWcPasskeysData || {
		restUrl: '/wp-json/wc-passkeys/v1',
		nonce: '',
		isLoggedIn: false,
		i18n: {}
	};

	/**
	 * Helper: Convert Base64URL string to Uint8Array / ArrayBuffer
	 *
	 * @param {string} base64url Base64URL encoded string
	 * @return {Uint8Array} Binary array
	 */
	function base64urlToUint8Array(base64url) {
		const padding = '='.repeat((4 - (base64url.length % 4)) % 4);
		const base64 = (base64url + padding).replace(/-/g, '+').replace(/_/g, '/');
		const rawData = window.atob(base64);
		const outputArray = new Uint8Array(rawData.length);
		for (let i = 0; i < rawData.length; ++i) {
			outputArray[i] = rawData.charCodeAt(i);
		}
		return outputArray;
	}

	/**
	 * Helper: Convert ArrayBuffer to Base64URL string
	 *
	 * @param {ArrayBuffer} buffer Binary buffer
	 * @return {string} Base64URL encoded string
	 */
	function arrayBufferToBase64url(buffer) {
		let binary = '';
		const bytes = new Uint8Array(buffer);
		const len = bytes.byteLength;
		for (let i = 0; i < len; i++) {
			binary += String.fromCharCode(bytes[i]);
		}
		const base64 = window.btoa(binary);
		return base64.replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
	}

	/**
	 * Check WebAuthn browser compatibility
	 *
	 * @return {boolean}
	 */
	function isWebAuthnSupported() {
		return !!(
			window.PublicKeyCredential &&
			navigator.credentials &&
			navigator.credentials.create &&
			navigator.credentials.get
		);
	}

	/**
	 * Update status message banner in UI
	 *
	 * @param {HTMLElement} el Status container element
	 * @param {string} msg Message text
	 * @param {string} type Status type ('info', 'success', 'error')
	 */
	function showStatus(el, msg, type = 'info') {
		if (!el) return;
		el.textContent = msg;
		el.className = `my-wc-passkey-status-msg my-wc-status-${type}`;
		el.style.display = 'block';
	}

	/**
	 * Hide status message banner
	 *
	 * @param {HTMLElement} el Status container element
	 */
	function hideStatus(el) {
		if (!el) return;
		el.style.display = 'none';
		el.textContent = '';
	}

	// ─── 1. REGISTRATION FLOW (My Account) ───

	async function handleRegistration() {
		const regBtn = document.getElementById('my-wc-passkey-reg-btn');
		const statusEl = document.getElementById('my-wc-reg-status');
		const devInput = document.getElementById('my-wc-device-name-input');

		if (!isWebAuthnSupported()) {
			showStatus(statusEl, config.i18n.notSupported || 'เบราว์เซอร์ไม่รองรับ Passkeys', 'error');
			return;
		}

		const deviceName = (devInput ? devInput.value : '').trim() || 'อุปกรณ์ของฉัน';

		try {
			if (regBtn) regBtn.disabled = true;
			showStatus(statusEl, config.i18n.registering || 'กำลังเริ่มลงทะเบียน...', 'info');

			// 1. Fetch Registration Options from Server
			const optRes = await fetch(`${config.restUrl}/register/options`, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': config.nonce
				}
			});

			const optData = await optRes.json();
			if (!optRes.ok || !optData.success || !optData.options) {
				throw new Error(optData.message || config.i18n.errorOccurred);
			}

			const options = optData.options;

			// Format options for navigator.credentials.create()
			options.challenge = base64urlToUint8Array(options.challenge);
			options.user.id = base64urlToUint8Array(options.user.id);

			if (options.excludeCredentials && Array.isArray(options.excludeCredentials)) {
				options.excludeCredentials = options.excludeCredentials.map((cred) => ({
					...cred,
					id: base64urlToUint8Array(cred.id)
				}));
			}

			showStatus(statusEl, config.i18n.authenticating || 'กำลังรอสแกนลายนิ้วมือ / Face ID...', 'info');

			// 2. Trigger Platform Authenticator (Touch ID, Face ID, Windows Hello)
			const credential = await navigator.credentials.create({ publicKey: options });
			if (!credential) {
				throw new Error('No credential created');
			}

			// 3. Serialize Attestation Response to Base64URL
			const attestationResponse = {
				id: credential.id,
				rawId: arrayBufferToBase64url(credential.rawId),
				type: credential.type,
				response: {
					attestationObject: arrayBufferToBase64url(credential.response.attestationObject),
					clientDataJSON: arrayBufferToBase64url(credential.response.clientDataJSON),
					transports: credential.response.getTransports ? credential.response.getTransports() : ['internal']
				}
			};

			// 4. Send to Server for Verification & Storage
			const verifyRes = await fetch(`${config.restUrl}/register/verify`, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': config.nonce
				},
				body: JSON.stringify({
					response: attestationResponse,
					device_name: deviceName
				})
			});

			const verifyData = await verifyRes.json();
			if (!verifyRes.ok || !verifyData.success) {
				throw new Error(verifyData.message || config.i18n.errorOccurred);
			}

			showStatus(statusEl, config.i18n.regSuccess || 'ลงทะเบียน Passkey สำเร็จเรียบร้อย!', 'success');

			// Refresh page after 1.2s to show in table
			setTimeout(() => {
				window.location.reload();
			}, 1200);

		} catch (err) {
			if (err.name === 'NotAllowedError') {
				// User explicitly cancelled the biometric prompt - silently reset
				hideStatus(statusEl);
			} else {
				showStatus(statusEl, err.message || config.i18n.errorOccurred, 'error');
			}
		} finally {
			if (regBtn) regBtn.disabled = false;
		}
	}

	// ─── 2. LOGIN FLOW (WooCommerce Login Form) ───

	// ─── 2. LOGIN / QUICK AUTH FLOW (WooCommerce Login & Register Forms) ───

	async function handleLogin(customBtn, customStatusEl) {
		const loginBtn = customBtn || document.getElementById('my-wc-passkey-login-btn');
		const statusEl = customStatusEl || document.getElementById('my-wc-passkey-login-status');

		if (!isWebAuthnSupported()) {
			showStatus(statusEl, config.i18n.notSupported || 'เบราว์เซอร์ไม่รองรับ Passkeys', 'error');
			return;
		}

		try {
			if (loginBtn) loginBtn.disabled = true;
			showStatus(statusEl, config.i18n.authenticating || 'กำลังรอสแกนลายนิ้วมือ / Face ID...', 'info');

			// 1. Fetch Login Challenge & Options
			const optRes = await fetch(`${config.restUrl}/login/options`, {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' }
			});

			const optData = await optRes.json();
			if (!optRes.ok || !optData.success || !optData.options) {
				throw new Error(optData.message || config.i18n.errorOccurred);
			}

			const options = optData.options;
			const sessionHash = optData.session_hash;

			// Format options for navigator.credentials.get()
			options.challenge = base64urlToUint8Array(options.challenge);

			// 2. Trigger Platform Authenticator
			const assertion = await navigator.credentials.get({ publicKey: options });
			if (!assertion) {
				throw new Error('No assertion returned');
			}

			// 3. Serialize Assertion Response to Base64URL
			const assertionResponse = {
				id: assertion.id,
				rawId: arrayBufferToBase64url(assertion.rawId),
				type: assertion.type,
				response: {
					authenticatorData: arrayBufferToBase64url(assertion.response.authenticatorData),
					clientDataJSON: arrayBufferToBase64url(assertion.response.clientDataJSON),
					signature: arrayBufferToBase64url(assertion.response.signature),
					userHandle: assertion.response.userHandle ? arrayBufferToBase64url(assertion.response.userHandle) : null
				}
			};

			// 4. Send to Server for Verification and Auto-login
			const verifyRes = await fetch(`${config.restUrl}/login/verify`, {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify({
					response: assertionResponse,
					session_hash: sessionHash
				})
			});

			const verifyData = await verifyRes.json();
			if (!verifyRes.ok || !verifyData.success) {
				throw new Error(verifyData.message || config.i18n.errorOccurred);
			}

			showStatus(statusEl, config.i18n.loginSuccess || 'ยืนยันตัวตนสำเร็จ กำลังเข้าสู่ระบบ...', 'success');

			// Redirect to target URL
			const redirectUrl = verifyData.redirect_url || '/my-account/';
			setTimeout(() => {
				window.location.href = redirectUrl;
			}, 800);

		} catch (err) {
			if (err.name === 'NotAllowedError') {
				// User cancelled prompt - dismiss cleanly
				hideStatus(statusEl);
			} else {
				showStatus(statusEl, err.message || config.i18n.errorOccurred, 'error');
			}
		} finally {
			if (loginBtn) loginBtn.disabled = false;
		}
	}

	// ─── 3. DELETION FLOW (My Account Table) ───

	async function handleDelete(btn) {
		const keyId = btn.getAttribute('data-id');
		if (!keyId) return;

		const confirmMsg = config.i18n.confirmDelete || 'คุณแน่ใจหรือไม่ว่าต้องการลบอุปกรณ์ Passkey นี้?';
		if (!window.confirm(confirmMsg)) {
			return;
		}

		try {
			btn.disabled = true;
			const res = await fetch(`${config.restUrl}/credentials/${keyId}`, {
				method: 'DELETE',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': config.nonce
				}
			});

			const data = await res.json();
			if (!res.ok || !data.success) {
				throw new Error(data.message || config.i18n.errorOccurred);
			}

			// Remove table row with smooth animation
			const row = document.getElementById(`my-wc-pk-row-${keyId}`);
			if (row) {
				row.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
				row.style.opacity = '0';
				row.style.transform = 'scale(0.95)';
				setTimeout(() => {
					row.remove();
					// Reload if no keys left to update empty state
					const tableBody = document.querySelector('#my-wc-passkeys-table tbody');
					if (!tableBody || tableBody.children.length === 0) {
						window.location.reload();
					}
				}, 300);
			}
		} catch (err) {
			alert(err.message || config.i18n.errorOccurred);
			btn.disabled = false;
		}
	}

	// ─── 4. DOM EVENT LISTENERS INITIALIZATION ───

	document.addEventListener('DOMContentLoaded', () => {
		const loginBtn = document.getElementById('my-wc-passkey-login-btn');
		const regAuthBtn = document.getElementById('my-wc-passkey-register-auth-btn');
		const regBtn = document.getElementById('my-wc-passkey-reg-btn');
		const container = document.getElementById('my-wc-passkeys-manager');

		// Handle Login Page Passkey Button Click
		if (loginBtn) {
			loginBtn.addEventListener('click', (e) => {
				e.preventDefault();
				if (!window.isSecureContext && window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1') {
					const statusEl = document.getElementById('my-wc-passkey-login-status');
					showStatus(
						statusEl,
						'ระบบ Passkeys (สแกนนิ้ว/Face ID) จำเป็นต้องใช้งานผ่าน HTTPS หรือ localhost กรุณาเข้าผ่าน https://' + window.location.host,
						'error'
					);
					return;
				}
				handleLogin(loginBtn, document.getElementById('my-wc-passkey-login-status'));
			});
		}

		// Handle Register Page Passkey Button Click
		if (regAuthBtn) {
			regAuthBtn.addEventListener('click', (e) => {
				e.preventDefault();
				if (!window.isSecureContext && window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1') {
					const statusEl = document.getElementById('my-wc-passkey-register-status');
					showStatus(
						statusEl,
						'ระบบ Passkeys จำเป็นต้องใช้งานผ่าน HTTPS หรือ localhost กรุณาเข้าผ่าน https://' + window.location.host,
						'error'
					);
					return;
				}
				handleLogin(regAuthBtn, document.getElementById('my-wc-passkey-register-status'));
			});
		}

		// Handle Register Button Click in My Account
		if (regBtn) {
			regBtn.addEventListener('click', (e) => {
				e.preventDefault();
				if (!window.isSecureContext && window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1') {
					const statusEl = document.getElementById('my-wc-reg-status');
					showStatus(
						statusEl,
						'ระบบ Passkeys จำเป็นต้องใช้งานผ่าน HTTPS หรือ localhost กรุณาเข้าผ่าน https://' + window.location.host,
						'error'
					);
					return;
				}
				handleRegistration();
			});
		}

		// Delete buttons delegation in My Account
		if (container) {
			container.addEventListener('click', (e) => {
				const delBtn = e.target.closest('.my-wc-delete-passkey-btn');
				if (delBtn) {
					e.preventDefault();
					handleDelete(delBtn);
				}
			});
		}
	});

})();
