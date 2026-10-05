import { useState } from '@wordpress/element';

export default function SecurityView( { config, passkeys, onRefreshPasskeys, showToast } ) {
	// Password state
	const [ currentPassword, setCurrentPassword ] = useState( '' );
	const [ newPassword, setNewPassword ] = useState( '' );
	const [ confirmPassword, setConfirmPassword ] = useState( '' );
	const [ passwordLoading, setPasswordLoading ] = useState( false );

	// Passkey state
	const [ passkeyRegistering, setPasskeyRegistering ] = useState( false );
	const [ deletingId, setDeletingId ] = useState( null );

	const handlePasswordChange = async ( e ) => {
		e.preventDefault();
		if ( newPassword !== confirmPassword ) {
			showToast( 'รหัสผ่านใหม่และการยืนยันไม่ตรงกัน', 'error' );
			return;
		}

		try {
			setPasswordLoading( true );
			const res = await fetch( `${ config.apiUrl }/password`, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': config.nonce,
				},
				body: JSON.stringify( {
					current_password: currentPassword,
					new_password: newPassword,
					confirm_password: confirmPassword,
				} ),
			} );

			const data = await res.json();
			if ( res.ok && data.success ) {
				showToast( data.message || 'เปลี่ยนรหัสผ่านสำเร็จ' );
				setCurrentPassword( '' );
				setNewPassword( '' );
				setConfirmPassword( '' );
			} else {
				showToast( data.message || 'เกิดข้อผิดพลาดในการเปลี่ยนรหัสผ่าน', 'error' );
			}
		} catch ( err ) {
			showToast( 'เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error' );
		} finally {
			setPasswordLoading( false );
		}
	};

	const handleDeletePasskey = async ( id ) => {
		if ( ! confirm( 'คุณแน่ใจหรือไม่ว่าต้องการลบอุปกรณ์ Passkey นี้?' ) ) {
			return;
		}

		try {
			setDeletingId( id );
			const res = await fetch( `${ config.apiUrl }/passkeys/${ id }`, {
				method: 'DELETE',
				headers: { 'X-WP-Nonce': config.nonce },
			} );

			const data = await res.json();
			if ( res.ok && data.success ) {
				showToast( 'ลบอุปกรณ์สำเร็จ' );
				if ( onRefreshPasskeys ) onRefreshPasskeys();
			} else {
				showToast( data.message || 'เกิดข้อผิดพลาดในการลบอุปกรณ์', 'error' );
			}
		} catch ( err ) {
			showToast( 'เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error' );
		} finally {
			setDeletingId( null );
		}
	};

	const handleRegisterPasskey = async () => {
		if ( ! window.PublicKeyCredential ) {
			showToast( 'เบราว์เซอร์หรืออุปกรณ์ของคุณยังไม่รองรับ WebAuthn / Passkeys', 'error' );
			return;
		}

		const deviceName = prompt( 'กรุณาตั้งชื่ออุปกรณ์ของคุณ (เช่น iPhone 15, MacBook Pro):', 'อุปกรณ์ของฉัน' );
		if ( ! deviceName ) return;

		try {
			setPasskeyRegistering( true );

			// Step 1: Get Registration Options from wc-passkeys/v1
			const optRes = await fetch( `${ config.passkeysApiUrl }/register/options`, {
				method: 'POST',
				headers: { 'X-WP-Nonce': config.nonce },
			} );

			if ( ! optRes.ok ) {
				throw new Error( 'Failed to get registration options' );
			}

			const options = await optRes.json();

			// Transform base64 strings to Uint8Array for WebAuthn API
			const challenge = Uint8Array.from( atob( options.challenge.replace( /_/g, '/' ).replace( /-/g, '+' ) ), ( c ) => c.charCodeAt( 0 ) );
			const userId = Uint8Array.from( atob( options.user.id.replace( /_/g, '/' ).replace( /-/g, '+' ) ), ( c ) => c.charCodeAt( 0 ) );

			const credential = await navigator.credentials.create( {
				publicKey: {
					...options,
					challenge,
					user: { ...options.user, id: userId },
				},
			} );

			if ( ! credential ) {
				throw new Error( 'No credential generated' );
			}

			// Format response to send back
			const clientDataJSON = btoa( String.fromCharCode( ...new Uint8Array( credential.response.clientDataJSON ) ) );
			const attestationObject = btoa( String.fromCharCode( ...new Uint8Array( credential.response.attestationObject ) ) );

			const verifyRes = await fetch( `${ config.passkeysApiUrl }/register/verify`, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': config.nonce,
				},
				body: JSON.stringify( {
					device_name: deviceName,
					response: {
						id: credential.id,
						rawId: btoa( String.fromCharCode( ...new Uint8Array( credential.rawId ) ) ),
						type: credential.type,
						response: {
							clientDataJSON,
							attestationObject,
						},
					},
				} ),
			} );

			const verifyData = await verifyRes.json();
			if ( verifyRes.ok && verifyData.success ) {
				showToast( 'ลงทะเบียนอุปกรณ์ Passkey สำเร็จเรียบร้อย!' );
				if ( onRefreshPasskeys ) onRefreshPasskeys();
			} else {
				showToast( verifyData.message || 'การตรวจสอบอุปกรณ์ไม่สำเร็จ', 'error' );
			}
		} catch ( err ) {
			console.error( 'Passkey error:', err );
			showToast( err.message || 'ยกเลิกการลงทะเบียนหรืออุปกรณ์ไม่พร้อมใช้งาน', 'error' );
		} finally {
			setPasskeyRegistering( false );
		}
	};

	return (
		<div id="raka-security-view" className="space-y-8">

			{ /* ── 1. Passkeys / Biometric Devices Section ── */ }
			<div id="raka-passkeys-management-card" className="p-6 sm:p-7 rounded-3xl bg-neutral-100/80 dark:bg-neutral-900/80 border border-black/5 dark:border-white/5 space-y-5 shadow-xs">
				<div id="raka-passkeys-section-header" className="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-black/5 dark:border-white/5 gap-3">
					<div className="space-y-1">
						<div className="flex items-center gap-2">
							<i id="raka-passkey-sec-icon" className="ri-fingerprint-line text-lg text-red-600 dark:text-red-500"></i>
							<h3 id="raka-passkeys-title" className="text-base font-bold text-neutral-950 dark:text-white">
								อุปกรณ์ Passkeys (WebAuthn / Biometrics)
							</h3>
						</div>
						<p className="text-xs text-neutral-500">
							เข้าสู่ระบบอย่างปลอดภัยโดยใช้ Touch ID, Face ID, หรือ Windows Hello โดยไม่ต้องพึ่งรหัสผ่าน
						</p>
					</div>

					<button
						type="button"
						id="raka-add-passkey-btn"
						disabled={ passkeyRegistering }
						onClick={ handleRegisterPasskey }
						className="h-10 px-4 rounded-full bg-neutral-900 hover:bg-neutral-800 dark:bg-white dark:hover:bg-neutral-200 text-white dark:text-neutral-900 text-xs font-bold transition-all shadow-xs cursor-pointer shrink-0 flex items-center justify-center gap-2 disabled:opacity-50"
					>
						<i className={ passkeyRegistering ? 'ri-loader-4-line animate-spin' : 'ri-add-line' }></i>
						<span>{ passkeyRegistering ? 'กำลังลงทะเบียน...' : 'เพิ่มอุปกรณ์นี้' }</span>
					</button>
				</div>

				{ passkeys && passkeys.length > 0 ? (
					<div id="raka-passkeys-devices-list" className="space-y-3">
						{ passkeys.map( ( dev ) => (
							<div
								key={ dev.id }
								id={ `raka-device-item-${ dev.id }` }
								className="p-4 rounded-2xl bg-white dark:bg-neutral-800/80 border border-black/5 dark:border-white/5 flex items-center justify-between gap-4"
							>
								<div className="flex items-center gap-3">
									<div className="w-10 h-10 rounded-xl bg-red-600/10 text-red-600 dark:text-red-400 flex items-center justify-center shrink-0">
										<i className="ri-shield-keyhole-line text-xl"></i>
									</div>
									<div className="space-y-0.5">
										<h4 className="text-xs sm:text-sm font-bold text-neutral-900 dark:text-white">
											{ dev.device_name }
										</h4>
										<p className="text-[11px] text-neutral-500">
											เพิ่มเมื่อ: { dev.created_at } • ใช้งานล่าสุด: { dev.last_used_at }
										</p>
									</div>
								</div>

								<button
									type="button"
									id={ `raka-delete-device-btn-${ dev.id }` }
									disabled={ deletingId === dev.id }
									onClick={ () => handleDeletePasskey( dev.id ) }
									className="px-3 py-1.5 rounded-full text-xs font-semibold text-red-600 dark:text-red-400 bg-red-500/10 hover:bg-red-500/20 transition-all cursor-pointer disabled:opacity-50"
								>
									{ deletingId === dev.id ? 'กำลังลบ...' : 'ลบอุปกรณ์' }
								</button>
							</div>
						) ) }
					</div>
				) : (
					<div id="raka-passkeys-empty-box" className="py-8 text-center space-y-2 bg-white/50 dark:bg-neutral-800/40 rounded-2xl border border-dashed border-black/10 dark:border-white/10">
						<i className="ri-fingerprint-line text-3xl text-neutral-400"></i>
						<p className="text-xs text-neutral-500">ยังไม่มีอุปกรณ์ Passkey ที่ลงทะเบียนไว้</p>
					</div>
				)}
			</div>

			{ /* ── 2. Change Password Form ── */ }
			<div id="raka-password-change-card" className="p-6 sm:p-7 rounded-3xl bg-neutral-100/80 dark:bg-neutral-900/80 border border-black/5 dark:border-white/5 space-y-5 shadow-xs">
				<div id="raka-password-header" className="pb-3 border-b border-black/5 dark:border-white/5 flex items-center gap-2">
					<i id="raka-password-icon" className="ri-lock-password-line text-lg text-amber-500"></i>
					<h3 id="raka-password-title" className="text-base font-bold text-neutral-950 dark:text-white">
						เปลี่ยนรหัสผ่าน
					</h3>
				</div>

				<form id="raka-change-password-form" onSubmit={ handlePasswordChange } className="space-y-4 max-w-md">
					<div id="raka-field-wrap-current-pwd" className="space-y-1.5">
						<label id="raka-lbl-current-pwd" className="block text-xs font-bold text-neutral-700 dark:text-neutral-300">
							รหัสผ่านปัจจุบัน *
						</label>
						<input
							type="password"
							id="raka_current_password"
							value={ currentPassword }
							onChange={ ( e ) => setCurrentPassword( e.target.value ) }
							required
							className="w-full h-11 px-4 rounded-xl bg-white dark:bg-neutral-800 border border-black/10 dark:border-white/10 text-sm focus:outline-none focus:ring-1 focus:ring-black/20 dark:focus:ring-white/20 transition-all"
						/>
					</div>

					<div id="raka-field-wrap-new-pwd" className="space-y-1.5">
						<label id="raka-lbl-new-pwd" className="block text-xs font-bold text-neutral-700 dark:text-neutral-300">
							รหัสผ่านใหม่ (อย่างน้อย 6 ตัวอักษร) *
						</label>
						<input
							type="password"
							id="raka_new_password"
							value={ newPassword }
							onChange={ ( e ) => setNewPassword( e.target.value ) }
							required
							className="w-full h-11 px-4 rounded-xl bg-white dark:bg-neutral-800 border border-black/10 dark:border-white/10 text-sm focus:outline-none focus:ring-1 focus:ring-black/20 dark:focus:ring-white/20 transition-all"
						/>
					</div>

					<div id="raka-field-wrap-confirm-pwd" className="space-y-1.5">
						<label id="raka-lbl-confirm-pwd" className="block text-xs font-bold text-neutral-700 dark:text-neutral-300">
							ยืนยันรหัสผ่านใหม่อีกครั้ง *
						</label>
						<input
							type="password"
							id="raka_confirm_password"
							value={ confirmPassword }
							onChange={ ( e ) => setConfirmPassword( e.target.value ) }
							required
							className="w-full h-11 px-4 rounded-xl bg-white dark:bg-neutral-800 border border-black/10 dark:border-white/10 text-sm focus:outline-none focus:ring-1 focus:ring-black/20 dark:focus:ring-white/20 transition-all"
						/>
					</div>

					<button
						type="submit"
						id="raka-save-password-btn"
						disabled={ passwordLoading }
						className="h-10 px-6 rounded-full bg-neutral-950 hover:bg-neutral-800 dark:bg-white dark:hover:bg-neutral-200 text-white dark:text-neutral-900 text-xs font-bold transition-all shadow-xs cursor-pointer disabled:opacity-50"
					>
						{ passwordLoading ? 'กำลังบันทึก...' : 'อัปเดตรหัสผ่านใหม่' }
					</button>
				</form>
			</div>

		</div>
	);
}
