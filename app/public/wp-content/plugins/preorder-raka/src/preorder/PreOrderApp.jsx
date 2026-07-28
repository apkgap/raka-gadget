import React, { useState, useEffect } from 'react';

// ─── Design Tokens — Red × Black ──────────────────────────────────────────────
const T = {
	red:       '#e11d48',
	redDeep:   '#9f1239',
	redGlow:   'rgba(225,29,72,0.22)',
	redSubtle: 'rgba(225,29,72,0.08)',
	redBorder: 'rgba(225,29,72,0.28)',
	// Surfaces dark
	bg0: '#060606',
	bg1: '#0e0e0e',
	bg2: '#161616',
	bg3: '#1e1e1e',
	bg4: '#272727',
	line:      'rgba(255,255,255,0.07)',
	lineStrong:'rgba(255,255,255,0.12)',
	// Text dark
	textPri: '#ffffff',
	textSec: '#888888',
	textMut: '#444444',
	// Status
	green:    '#22c55e',
	greenBg:  'rgba(34,197,94,0.10)',
	greenBord:'rgba(34,197,94,0.28)',
	// Light palette
	lBg0: '#f5f5f5',
	lBg1: '#ffffff',
	lBg2: '#f0f0f0',
	lBg3: '#e8e8e8',
	lLine:'rgba(0,0,0,0.08)',
	lText:'#111111',
	lTextS:'#666666',
};

const ds = (dark, light, isDark) => isDark ? dark : light;

// ─── Step icon types ───────────────────────────────────────────────────────────
const stepIconClass = (type) => ({
	receipt:   'ri-file-list-3-line',
	card:      'ri-bank-card-line',
	shipment:  'ri-flight-takeoff-line',
	warehouse: 'ri-store-2-line',
	delivered: 'ri-checkbox-circle-line',
	default:   'ri-check-line',
}[type] || 'ri-check-line');

const getStepIconType = (title) => {
	const t = title.toLowerCase();
	if (t.includes('รับคำสั่งซื้อ') || t.includes('รับออเดอร์')) return 'receipt';
	if (t.includes('สั่งซื้อสำเร็จ') || t.includes('สั่งสินค้า')) return 'card';
	if (t.includes('ระหว่างขนส่ง') || t.includes('ส่งออกจาก') || t.includes('transit')) return 'shipment';
	if (t.includes('ถึงไทย') || t.includes('โกดัง') || t.includes('arrived')) return 'warehouse';
	if (t.includes('จัดส่งสำเร็จ') || t.includes('สำเร็จ') || t.includes('delivered')) return 'delivered';
	return 'default';
};

// ─── Styles as objects (computed per render) ───────────────────────────────────
const useStyles = (isDark, isMobile) => ({
	root: {
		width: '100%', maxWidth: 1000, margin: '0 auto',
		borderRadius: 24, overflow: 'hidden',
		background: ds(T.bg1, T.lBg1, isDark),
		boxShadow: isDark
			? `0 0 0 1px rgba(225,29,72,0.12), 0 48px 100px -24px rgba(0,0,0,0.95), 0 0 80px -30px ${T.redGlow}`
			: `0 0 0 1px rgba(225,29,72,0.10), 0 32px 80px -16px rgba(0,0,0,0.10), 0 0 40px -10px rgba(225,29,72,0.05)`,
		transition: 'all 0.35s',
	},
	hero: {
		padding: isMobile ? '20px 14px 24px' : '36px 36px 44px',
		background: 'linear-gradient(135deg, rgb(225, 29, 72), rgb(159, 18, 57))',
		borderBottomLeftRadius: isMobile ? '50% 16px' : '50% 28px',
		borderBottomRightRadius: isMobile ? '50% 16px' : '50% 28px',
		position: 'relative', overflow: 'hidden',
		boxShadow: '0 12px 32px rgba(225,29,72,0.3)',
		color: '#ffffff',
	},
	heroGlow: {
		display: 'none',
	},
	body: {
		padding: isMobile ? '14px 10px 20px' : '36px',
		background: ds(T.bg1, T.lBg1, isDark),
	},
	input: (id, focused) => ({
		width: '100%', padding: isMobile ? '10px 16px' : '14px 24px', borderRadius: 9999, fontFamily: 'inherit',
		fontSize: 15, outline: 'none', transition: 'all 0.25s', color: ds('#fff', T.lText, isDark),
		background: ds(T.bg2, T.lBg2, isDark),
		border: `1px solid ${focused === id ? T.redBorder : ds(T.line, T.lLine, isDark)}`,
		boxShadow: focused === id ? `0 0 0 3px ${T.redSubtle}, 0 0 16px ${T.redGlow}` : 'none',
		fontWeight: 400,
	}),
	tabWrap: {
		display: 'flex', gap: 4, padding: 4, borderRadius: 9999,
		background: ds(T.bg2, T.lBg2, isDark),
		border: `1px solid ${ds(T.line, T.lLine, isDark)}`,
	},
	tabBtn: (active) => ({
		flex: 1, padding: isMobile ? '8px 12px' : '12px 20px', borderRadius: 9999, border: 'none',
		fontFamily: 'inherit', fontSize: 13, fontWeight: 500, cursor: 'pointer', transition: 'all 0.25s',
		background: active ? `linear-gradient(135deg, ${T.red}, ${T.redDeep})` : 'transparent',
		color: active ? '#fff' : ds('#777', '#888', isDark),
		boxShadow: active ? `0 4px 20px ${T.redGlow}` : 'none',
		display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 6,
	}),
	primaryBtn: (disabled) => ({
		width: '100%', padding: isMobile ? '12px 16px' : '16px', borderRadius: 9999, border: 'none',
		fontFamily: 'inherit', fontSize: 15, fontWeight: 500, letterSpacing: '0.3px',
		cursor: disabled ? 'not-allowed' : 'pointer', transition: 'all 0.25s',
		background: disabled ? ds(T.bg3, T.lBg3, isDark) : `linear-gradient(135deg, ${T.red}, ${T.redDeep})`,
		color: disabled ? ds('#555', '#aaa', isDark) : '#fff',
		boxShadow: disabled ? 'none' : `0 8px 28px ${T.redGlow}, 0 2px 8px rgba(225,29,72,0.2)`,
		opacity: disabled ? 0.7 : 1,
		display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 8,
	}),
	card: {
		padding: isMobile ? '14px 14px' : '24px', borderRadius: 16, transition: 'all 0.3s',
		background: ds(T.bg2, T.lBg2, isDark),
		border: `1px solid ${ds(T.line, T.lLine, isDark)}`,
	},
	sectionLabel: {
		fontSize: 14, fontWeight: 500, letterSpacing: '1.2px', textTransform: 'uppercase',
		color: ds('#999', '#444', isDark), marginBottom: 12, display: 'block',
	},
	backBtn: {
		display: 'inline-flex', alignItems: 'center', gap: 6, padding: '10px 20px',
		borderRadius: 9999, cursor: 'pointer', transition: 'all 0.2s',
		background: ds(T.bg3, T.lBg3, isDark),
		border: `1px solid ${ds(T.line, T.lLine, isDark)}`,
		color: ds('#aaa', '#555', isDark), fontSize: 14, fontWeight: 500, fontFamily: 'inherit',
	},
	logoBox: {
		width: 44, height: 44, borderRadius: 10, flexShrink: 0,
		background: `linear-gradient(135deg, ${T.red}, ${T.redDeep})`,
		display: 'flex', alignItems: 'center', justifyContent: 'center',
		fontWeight: 500, fontSize: 18, color: '#fff',
		boxShadow: `0 4px 20px ${T.redGlow}`,
	},
	toggleBtn: {
		width: 38, height: 38, borderRadius: 10, cursor: 'pointer',
		border: `1px solid ${ds(T.redBorder, 'rgba(225,29,72,0.2)', isDark)}`,
		background: ds(T.redSubtle, T.redSubtle, isDark),
		color: ds('#f87171', T.red, isDark), transition: 'all 0.25s',
		display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 16,
	},
	chipStyle: (accent) => ({
		display: 'inline-flex', alignItems: 'center', gap: 6,
		padding: '8px 16px', borderRadius: 9999,
		background: accent ? T.redSubtle : ds('rgba(34,197,94,0.08)', 'rgba(34,197,94,0.07)', isDark),
		border: `1px solid ${accent ? T.redBorder : T.greenBord}`,
		fontSize: 14, fontWeight: 500, letterSpacing: '0.6px',
		color: accent ? T.red : T.green,
	}),
});

// ─── Component ─────────────────────────────────────────────────────────────────
const PreOrderApp = ({ postId }) => {
	const settings = window.preorderRakaSettings || { root: '/wp-json/', nonce: '' };

	const [isDark, setIsDark]     = useState(() => localStorage.getItem('preorder_raka_theme') !== 'light');
	const [screen, setScreen]     = useState('search');
	const [initialVerifying, setInitialVerifying] = useState(true);
	const [orderNo, setOrderNo]   = useState('');
	const [phone, setPhone]       = useState('');
	const [citizenId, setCitizenId] = useState('');
	const [verificationType, setVerificationType] = useState('phone');
	const [loading, setLoading]   = useState(false);
	const [error, setError]       = useState(null);
	const [preorderData, setPreorderData] = useState(null);
	const [copied, setCopied]     = useState(false);
	const [focused, setFocused]   = useState(null);
	const [width, setWidth]       = useState(window.innerWidth);

	// Handle resize for custom responsive styling in JS
	useEffect(() => {
		const handleResize = () => setWidth(window.innerWidth);
		window.addEventListener('resize', handleResize);
		return () => window.removeEventListener('resize', handleResize);
	}, []);

	const isMobile = width < 768;
	const S = useStyles(isDark, isMobile);

	// Sync dark mode
	useEffect(() => {
		const root = document.documentElement;
		root.classList.toggle('dark', isDark);
		root.classList.toggle('light', !isDark);
		localStorage.setItem('preorder_raka_theme', isDark ? 'dark' : 'light');
	}, [isDark]);

	useEffect(() => {
		const obs = new MutationObserver(() => setIsDark(document.documentElement.classList.contains('dark')));
		obs.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
		const onStorage = (e) => { if (e.key === 'preorder_raka_theme') setIsDark(e.newValue !== 'light'); };
		window.addEventListener('storage', onStorage);
		return () => { obs.disconnect(); window.removeEventListener('storage', onStorage); };
	}, []);

	const TWO_DAYS_MS = 2 * 24 * 60 * 60 * 1000;

	// Load cached verification session (valid for 2 days)
	const getCachedAuth = () => {
		try {
			const saved = localStorage.getItem('preorder_raka_auth');
			if (!saved) return null;
			const parsed = JSON.parse(saved);
			if (Date.now() - parsed.timestamp < TWO_DAYS_MS) {
				return parsed;
			} else {
				localStorage.removeItem('preorder_raka_auth');
			}
		} catch (e) {
			console.error('Failed to parse auth cache', e);
		}
		return null;
	};

	const saveAuthCache = (savedPhone, savedCitizen, savedType) => {
		try {
			localStorage.setItem('preorder_raka_auth', JSON.stringify({
				phone: savedPhone || '',
				citizenId: savedCitizen || '',
				verificationType: savedType || 'phone',
				timestamp: Date.now(),
			}));
		} catch (e) {
			console.error('Failed to save auth cache', e);
		}
	};

	useEffect(() => {
		let isSubscribed = true;
		const searchParams = new URLSearchParams(window.location.search);
		const urlOrderNo   = searchParams.get('order_no') || searchParams.get('order');
		let urlPhone       = searchParams.get('phone');
		let urlCitizen     = searchParams.get('citizen_id') || searchParams.get('citizen');

		// Fallback to 2-day session cache if URL has no phone/citizen
		const cached = getCachedAuth();
		if (!urlPhone && !urlCitizen && cached) {
			if (cached.phone) urlPhone = cached.phone;
			if (cached.citizenId) urlCitizen = cached.citizenId;
		}

		if (urlOrderNo) setOrderNo(urlOrderNo);
		if (urlPhone) { setPhone(urlPhone); setVerificationType('phone'); }
		if (urlCitizen) { setCitizenId(urlCitizen); setVerificationType('citizen'); }

		const initFetch = async () => {
			if (postId) {
				setScreen('verify-direct');
				if (urlPhone || urlCitizen) {
					await fetchStatus({ pId: postId, pPhone: urlPhone, pCitizen: urlCitizen, pType: urlCitizen ? 'citizen' : 'phone' });
				}
			} else if (urlOrderNo && (urlPhone || urlCitizen)) {
				await fetchStatus({ pOrderNo: urlOrderNo, pPhone: urlPhone, pCitizen: urlCitizen, pType: urlCitizen ? 'citizen' : 'phone' });
			}
			if (isSubscribed) setInitialVerifying(false);
		};

		initFetch();
		return () => { isSubscribed = false; };
	}, [postId]);

	const fetchStatus = async ({ pId, pOrderNo, pPhone, pCitizen, pType }) => {
		const targetPhone = pPhone !== undefined ? pPhone : phone;
		const targetCitizen = pCitizen !== undefined ? pCitizen : citizenId;
		const targetType = pType || verificationType;
		const targetOrderNo = pOrderNo || orderNo;

		setLoading(true); setError(null);
		try {
			let url = '';
			const rootUrl = settings.root;
			if (rootUrl.includes('rest_route=')) {
				// Plain Permalinks e.g. https://site.com/index.php?rest_route=/preorder-raka/v1
				const urlObj = new URL(rootUrl, window.location.origin);
				const currentRestRoute = (urlObj.searchParams.get('rest_route') || '/preorder-raka/v1').replace(/\/$/, '');
				urlObj.searchParams.set('rest_route', `${currentRestRoute}/status`);
				if (pId || (postId && screen === 'verify-direct')) urlObj.searchParams.set('post_id', pId || postId);
				else urlObj.searchParams.set('order_no', targetOrderNo);

				if (targetType === 'phone') urlObj.searchParams.set('phone', targetPhone);
				else urlObj.searchParams.set('citizen_id', targetCitizen);
				url = urlObj.toString();
			} else {
				// Pretty Permalinks e.g. https://site.com/wp-json/preorder-raka/v1
				const baseUrl = `${rootUrl.replace(/\/$/, '')}/status`;
				const params = new URLSearchParams();
				if (pId || (postId && screen === 'verify-direct')) params.append('post_id', pId || postId);
				else params.append('order_no', targetOrderNo);

				if (targetType === 'phone') params.append('phone', targetPhone);
				else params.append('citizen_id', targetCitizen);

				url = `${baseUrl}?${params.toString()}`;
			}

			const res  = await fetch(url, { headers: { 'X-WP-Nonce': settings.nonce } });
			const data = await res.json();
			if (!res.ok) throw new Error(data.message || 'ไม่สามารถดึงข้อมูลได้');
			
			// Save successful verification into 2-day session cache
			saveAuthCache(targetPhone, targetCitizen, targetType);

			setPreorderData(data.data); setScreen('result');
		} catch (err) { setError(err.message); }
		finally { setLoading(false); }
	};

	const handleSearch = async (e) => {
		if (e) e.preventDefault();
		await fetchStatus({});
	};

	const handleReset = () => {
		setPreorderData(null); setError(null);
		// Keep cached phone & citizen for fast re-check, just return to search view
		setScreen(postId ? 'verify-direct' : 'search');
	};

	const handleCopy = (text) => {
		if (navigator.clipboard && window.isSecureContext && typeof navigator.clipboard.writeText === 'function') {
			navigator.clipboard.writeText(text).then(() => {
				setCopied(true);
				setTimeout(() => setCopied(false), 2200);
			}).catch(() => {
				fallbackCopyTextToClipboard(text);
			});
		} else {
			fallbackCopyTextToClipboard(text);
		}
	};

	const fallbackCopyTextToClipboard = (text) => {
		try {
			const textArea = document.createElement('textarea');
			textArea.value = text;
			textArea.style.position = 'fixed';
			textArea.style.top = '-9999px';
			textArea.style.left = '-9999px';
			document.body.appendChild(textArea);
			textArea.focus();
			textArea.select();
			document.execCommand('copy');
			document.body.removeChild(textArea);
			setCopied(true);
			setTimeout(() => setCopied(false), 2200);
		} catch (err) {
			console.error('Fallback copy failed', err);
		}
	};

	// ── Shared form renderer ───────────────────────────────────────────────────
	const renderForm = (formId, submitLabel, submitIcon) => (
		<form id={formId} onSubmit={handleSearch} style={{ display: 'flex', flexDirection: 'column', gap: 20 }}>

			{/* Order number — search screen only */}
			{screen === 'search' && (
				<div id="preorder-raka-input-order-group">
					<label id="preorder-raka-order-label" htmlFor="order_no" style={S.sectionLabel}>
						<i className="ri-hashtag" style={{ marginRight: 6 }}/>เลขคำสั่งซื้อ
					</label>
					<input id="order_no" style={S.input('order_no', focused)} type="text"
						placeholder="เช่น RAKA-PRE-7777" value={orderNo}
						onChange={e => setOrderNo(e.target.value)}
						onFocus={() => setFocused('order_no')} onBlur={() => setFocused(null)} required/>
				</div>
			)}

			{/* Verify type tabs */}
			<div id="preorder-raka-tab-row" style={S.tabWrap}>
				<button id="preorder-raka-verify-phone-btn" style={S.tabBtn(verificationType === 'phone')} type="button" onClick={() => setVerificationType('phone')}>
					<i className="ri-smartphone-line" style={{ fontSize: 16 }}/>เบอร์โทรศัพท์
				</button>
				<button id="preorder-raka-verify-citizen-btn" style={S.tabBtn(verificationType === 'citizen')} type="button" onClick={() => setVerificationType('citizen')}>
					<i className="ri-id-card-line" style={{ fontSize: 16 }}/>บัตรประชาชน
				</button>
			</div>

			{/* Verify input */}
			{verificationType === 'phone' ? (
				<div id="preorder-raka-input-phone-group">
					<label id="preorder-raka-phone-label" htmlFor="phone" style={S.sectionLabel}>
						<i className="ri-phone-line" style={{ marginRight: 6 }}/>เบอร์โทรศัพท์
					</label>
					<input id="phone" style={S.input('phone', focused)} type="tel" placeholder="0897777777"
						value={phone} onChange={e => setPhone(e.target.value)}
						onFocus={() => setFocused('phone')} onBlur={() => setFocused(null)} required/>
				</div>
			) : (
				<div id="preorder-raka-input-citizen-group">
					<label id="preorder-raka-citizen-label" htmlFor="citizen_id" style={S.sectionLabel}>
						<i className="ri-fingerprint-line" style={{ marginRight: 6 }}/>เลขบัตร 4 ตัวท้าย
					</label>
					<input id="citizen_id" style={S.input('citizen_id', focused)} type="password" maxLength={4}
						placeholder="xxxx" value={citizenId} onChange={e => setCitizenId(e.target.value)}
						onFocus={() => setFocused('citizen_id')} onBlur={() => setFocused(null)} required/>
				</div>
			)}

			{/* Error box */}
			{error && (
				<div id="preorder-raka-error-box" style={{
					padding: '13px 16px', borderRadius: 12, display: 'flex', alignItems: 'center', gap: 9,
					background: 'rgba(225,29,72,0.07)', border: '1px solid rgba(225,29,72,0.22)',
					color: '#f87171', fontSize: 14, fontWeight: 500,
				}}>
					<i id="preorder-raka-error-icon" className="ri-error-warning-line" style={{ fontSize: 18, flexShrink: 0 }}/>
					<span id="preorder-raka-error-span">{error}</span>
				</div>
			)}

			{/* Submit */}
			<button id={`${formId}-submit`} style={S.primaryBtn(loading)} type="submit" disabled={loading}>
				<i className={loading ? 'ri-loader-4-line' : submitIcon} style={{ fontSize: 18 }}/>
				{loading ? 'กำลังตรวจสอบ...' : submitLabel}
			</button>
		</form>
	);

	// ── RENDER ─────────────────────────────────────────────────────────────────
	return (
		<div id="preorder-raka-app-root" style={{ ...S.root, position: 'relative' }}>

			{/* Initial Verification Overlay Popup */}
			{initialVerifying && (
				<div id="preorder-raka-initial-loading-overlay" style={{
					position: 'absolute', inset: 0, zIndex: 50,
					background: ds('rgba(14, 14, 14, 0.92)', 'rgba(255, 255, 255, 0.92)', isDark),
					backdropFilter: 'blur(12px)',
					display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center',
					borderRadius: 24, padding: 32, gap: 16,
					animation: 'fadeIn 0.3s ease',
				}}>
					<div style={{
						width: 56, height: 56, borderRadius: 16,
						background: 'linear-gradient(135deg, rgb(225, 29, 72), rgb(159, 18, 57))',
						display: 'flex', alignItems: 'center', justifyContent: 'center',
						color: '#ffffff', boxShadow: '0 8px 24px rgba(225, 29, 72, 0.3)',
						position: 'relative'
					}}>
						<i className="ri-shield-keyhole-line" style={{ fontSize: 26 }}/>
					</div>
					<div style={{ textAlign: 'center' }}>
						<h3 style={{ margin: '0 0 6px', fontSize: 18, fontWeight: 500, color: ds('#ffffff', '#111827', isDark) }}>
							กำลังตรวจสอบข้อมูลสมาชิก...
						</h3>
						<p style={{ margin: 0, fontSize: 13, color: ds('#888888', '#6b7280', isDark) }}>
							ระบบกำลังเชื่อมต่อและยืนยันตัวตนอัตโนมัติ
						</p>
					</div>
					<div style={{ display: 'flex', alignItems: 'center', gap: 8, color: T.red, fontSize: 14, fontWeight: 500 }}>
						<i className="ri-loader-4-line" style={{ fontSize: 20, animation: 'spin 1s linear infinite' }}/>
						<span>โปรดรอสักครู่</span>
					</div>
				</div>
			)}

			{/* ── HERO ──────────────────────────────────────────────────────── */}
			<div id="preorder-raka-hero" style={S.hero}>
				<div id="preorder-raka-hero-glow" style={S.heroGlow}/>

				{/* Nav row */}
				<div id="preorder-raka-nav-row" style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 28, position: 'relative' }}>
					{/* Logo */}
					<div id="preorder-raka-logo-group" style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
						<div id="preorder-raka-logo-badge" style={{
							width: 44, height: 44, borderRadius: 10, flexShrink: 0,
							background: '#ffffff', display: 'flex', alignItems: 'center', justifyContent: 'center',
							fontWeight: 500, fontSize: 20, color: '#dc2626',
							boxShadow: '0 4px 16px rgba(0,0,0,0.15)'
						}}>R</div>
						<div id="preorder-raka-logo-text-block">
							<div id="preorder-raka-logo-name" style={{ fontSize: 18, fontWeight: 500, letterSpacing: '-0.3px', color: '#ffffff', lineHeight: 1.15 }}>
								RAKA GADGET
							</div>
							<div id="preorder-raka-logo-sub" style={{ fontSize: 11, fontWeight: 400, letterSpacing: '1.1px', color: 'rgba(255,255,255,0.75)', textTransform: 'uppercase' }}>
								Pre-Order Tracker
							</div>
						</div>
					</div>

					{/* Controls */}
					<div id="preorder-raka-nav-controls" style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
						{/* Live badge */}
						<div id="preorder-raka-live-badge" style={{
							display: 'flex', alignItems: 'center', gap: 5, padding: '5px 12px', borderRadius: 9999,
							background: 'rgba(255,255,255,0.2)', border: '1px solid rgba(255,255,255,0.3)',
							fontSize: 11, fontWeight: 500, color: '#ffffff', letterSpacing: '0.4px',
						}}>
							<i className="ri-radio-button-line" style={{ fontSize: 12 }}/>LIVE
						</div>

						{/* Theme toggle */}
						<button id="preorder-raka-theme-toggle" onClick={() => setIsDark(!isDark)} aria-label="Toggle theme" style={{
							width: 38, height: 38, borderRadius: 10, cursor: 'pointer',
							border: '1px solid rgba(255,255,255,0.3)',
							background: 'rgba(255,255,255,0.15)',
							color: '#ffffff', transition: 'all 0.25s',
							display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 16,
						}}>
							<i id="preorder-raka-theme-icon" className={isDark ? 'ri-sun-line' : 'ri-moon-line'}/>
						</button>
					</div>
				</div>

				{/* Hero title — search / verify */}
				{screen !== 'result' && (
					<div id="preorder-raka-hero-content" style={{ position: 'relative' }}>
						<div id="preorder-raka-hero-chip" style={{
							display: 'inline-flex', alignItems: 'center', gap: 6,
							padding: '8px 16px', borderRadius: 9999,
							background: 'rgba(0, 0, 0, 0.2)', border: '1px solid rgba(255, 255, 255, 0.25)',
							fontSize: 14, fontWeight: 500, letterSpacing: '0.6px', color: '#ffffff',
						}} className="mb-3">
							<i className={screen === 'verify-direct' ? 'ri-shield-keyhole-line' : 'ri-search-eye-line'} style={{ fontSize: 14 }}/>
							{screen === 'verify-direct' ? 'ยืนยันตัวตน' : 'ค้นหาสถานะ'}
						</div>
						<h2 id="preorder-raka-hero-title" style={{ margin: '12px 0 6px', fontSize: 28, fontWeight: 500, letterSpacing: '-0.6px', color: '#ffffff', lineHeight: 1.15 }}>
							{screen === 'verify-direct' ? 'ยืนยันตัวตนเพื่อดูรายละเอียด' : 'ตรวจสอบสถานะพรีออเดอร์'}
						</h2>
						<p id="preorder-raka-hero-desc" style={{ margin: 0, fontSize: 15, color: 'rgba(255, 255, 255, 0.85)', lineHeight: 1.65 }}>
							{screen === 'verify-direct'
								? 'กรอกข้อมูลที่ใช้ลงทะเบียนเพื่อเข้าถึงรายละเอียดสินค้าของคุณอย่างปลอดภัย'
								: 'ติดตามความคืบหน้าการนำเข้าสินค้าพรีออเดอร์จากต่างประเทศแบบเรียลไทม์'}
						</p>
					</div>
				)}

				{/* Hero title — result */}
				{screen === 'result' && preorderData && (
					<div id="preorder-raka-result-hero" style={{ position: 'relative', display: 'flex', flexDirection: isMobile ? 'column' : 'row', alignItems: isMobile ? 'flex-start' : 'flex-end', justifyContent: 'space-between', gap: 16 }}>
						<div>
							<div id="preorder-raka-result-status-chip" style={{
								display: 'inline-flex', alignItems: 'center', gap: 6,
								padding: '8px 16px', borderRadius: 9999,
								background: 'rgba(0,0,0,0.2)', border: '1px solid rgba(255,255,255,0.25)',
								fontSize: 14, fontWeight: 500, letterSpacing: '0.6px', color: '#ffffff',
								marginBottom: 10,
							}}>
								<i className="ri-truck-line" style={{ fontSize: 14 }}/>
								{preorderData.steps[preorderData.current_step_idx]?.title || 'กำลังประมวลผล'}
							</div>
							<h2 id="preorder-raka-result-title" style={{ margin: '0 0 6px', fontSize: 24, fontWeight: 500, letterSpacing: '-0.5px', color: '#ffffff' }}>
								{preorderData.product_name}
							</h2>
							<p id="preorder-raka-result-order-no" style={{ margin: 0, fontSize: 14, color: 'rgba(255,255,255,0.85)' }}>
								<i className="ri-barcode-line" style={{ marginRight: 4 }}/>
								รหัส <span style={{ color: '#ffffff', fontWeight: 500 }}>{preorderData.order_no}</span>
							</p>
						</div>
						<div id="preorder-raka-result-total-block" style={{ textAlign: isMobile ? 'left' : 'right', marginTop: isMobile ? 8 : 0 }}>
							<div style={{ fontSize: 11, fontWeight: 500, color: 'rgba(255,255,255,0.8)', letterSpacing: '0.8px', textTransform: 'uppercase', marginBottom: 2 }}>ยอดรวม</div>
							<div style={{ fontSize: 32, fontWeight: 500, color: '#ffffff', letterSpacing: '-0.5px', lineHeight: 1 }}>
								{preorderData.total.toLocaleString()} บาท
							</div>
						</div>
					</div>
				)}
			</div>

			{/* ── BODY ──────────────────────────────────────────────────────── */}
			<div id="preorder-raka-body" style={S.body}>

				{/* SEARCH */}
				{screen === 'search' && (
					<div id="preorder-raka-search-container" style={{ maxWidth: 480, margin: '0 auto' }}>
						{renderForm('preorder-raka-search-form', 'ค้นหารายการพรีออเดอร์', 'ri-search-line')}
					</div>
				)}

				{/* VERIFY DIRECT */}
				{screen === 'verify-direct' && (
					<div id="preorder-raka-direct-verify-container" style={{ maxWidth: 480, margin: '0 auto' }}>
						{renderForm('preorder-raka-direct-form', 'เข้าสู่รายละเอียดสินค้า', 'ri-lock-unlock-line')}
					</div>
				)}

				{/* RESULT */}
				{screen === 'result' && preorderData && (
					<div id="preorder-raka-result-container" style={{ display: 'flex', flexDirection: 'column', gap: 24 }}>

						{/* Back */}
						<div>
							<button id="preorder-raka-back-btn" style={S.backBtn} onClick={handleReset}>
								<i className="ri-arrow-left-s-line" style={{ fontSize: 18 }}/>กลับหน้าหลัก
							</button>
						</div>

						{/* Stats strip */}
						<div id="preorder-raka-stats-strip" style={{ display: 'flex', gap: 12, flexWrap: 'wrap' }}>
							{[
								{ id: 'qty',   icon: 'ri-box-3-line',        label: 'จำนวน',      val: `${preorderData.qty} ชิ้น`,                       accent: false },
								{ id: 'price', icon: 'ri-price-tag-3-line',  label: 'ราคาสินค้า', val: `${preorderData.price.toLocaleString()} บาท`,       accent: false },
								{ id: 'ship',  icon: 'ri-truck-line',         label: 'ค่าส่ง',    val: `${preorderData.shipping.toLocaleString()} บาท`,    accent: false },
								{ id: 'step',  icon: 'ri-route-line',         label: 'ขั้นตอน',   val: `${preorderData.current_step_idx + 1} / ${preorderData.steps.length}`, accent: true },
							].map(s => (
								<div id={`preorder-raka-stat-${s.id}`} key={s.id} style={{
									flex: '1 1 120px', padding: '16px 18px', borderRadius: 14,
									background: s.accent ? T.redSubtle : ds(T.bg2, T.lBg2, isDark),
									border: `1px solid ${s.accent ? T.redBorder : ds(T.line, T.lLine, isDark)}`,
								}}>
									<div style={{ display: 'flex', alignItems: 'center', gap: 5, marginBottom: 4 }}>
										<i className={s.icon} style={{ fontSize: 14, color: s.accent ? T.red : ds('#666', '#aaa', isDark) }}/>
										<span style={{ fontSize: 12, fontWeight: 500, color: ds('#555', '#aaa', isDark) }}>{s.label}</span>
									</div>
									<div style={{ fontSize: 18, fontWeight: 500, color: s.accent ? T.red : ds('#fff', '#111', isDark) }}>{s.val}</div>
								</div>
							))}
						</div>

						{/* Grid: stepper + details */}
						<div id="preorder-raka-result-grid" style={{ display: 'flex', flexDirection: isMobile ? 'column' : 'row', gap: 20 }}>

							{/* Stepper */}
							<div id="preorder-raka-stepper-panel" style={{ ...S.card, flex: '1 1 360px', padding: isMobile ? '20px' : '24px 28px' }}>
								<span style={S.sectionLabel}><i className="ri-git-commit-line" style={{ marginRight: 4 }}/>ความคืบหน้า</span>

								<div style={{ display: 'flex', flexDirection: 'column', gap: 28 }}>
									{preorderData.steps.map((step, idx) => {
										const done   = idx <= preorderData.current_step_idx;
										const active = idx === preorderData.current_step_idx;
										const last   = idx === preorderData.steps.length - 1;
										const icoClass = stepIconClass(getStepIconType(step.title));

										return (
											<div id={`preorder-raka-step-item-${idx}`} key={idx} style={{ display: 'flex', gap: 14, position: 'relative' }}>
												{/* Connector line */}
												{!last && (
													<div id={`preorder-raka-step-line-${idx}`} style={{
														position: 'absolute', left: 21, top: 44, width: 2, height: 'calc(100% + 28px)',
														background: done
															? `linear-gradient(to bottom, ${T.red}, ${T.redSubtle})`
															: ds(T.line, T.lLine, isDark),
														transition: 'background 0.5s',
													}}/>
												)}

												{/* Node circle */}
												<div id={`preorder-raka-step-node-${idx}`} style={{ position: 'relative', zIndex: 1, flexShrink: 0 }}>
													<div style={{
														width: 44, height: 44, borderRadius: '50%',
														display: 'flex', alignItems: 'center', justifyContent: 'center',
														background: active ? T.redSubtle : done ? 'rgba(225,29,72,0.05)' : ds(T.bg3, T.lBg3, isDark),
														border: `2px solid ${active ? T.red : done ? T.redDeep : ds(T.line, T.lLine, isDark)}`,
														boxShadow: active ? `0 0 16px ${T.redGlow}` : 'none',
														transform: active ? 'scale(1.12)' : 'scale(1)',
														transition: 'all 0.4s',
													}}>
														<i id={`preorder-raka-step-icon-${idx}`} className={icoClass} style={{
															fontSize: 20,
															color: active ? T.red : done ? T.redDeep : ds('#444', '#ccc', isDark),
															transition: 'color 0.3s',
														}}/>
													</div>
												</div>

												{/* Text */}
												<div id={`preorder-raka-step-details-${idx}`} style={{ paddingTop: 5, flex: 1 }}>
													<div id={`preorder-raka-step-heading-${idx}`} style={{
														fontSize: 15, fontWeight: active ? 500 : 400, marginBottom: 3, transition: 'color 0.3s',
														color: active ? ds('#fff', '#111', isDark) : done ? ds('#ccc', '#444', isDark) : ds(T.textMut, '#ccc', isDark),
													}}>{step.title}</div>
													<div id={`preorder-raka-step-desc-${idx}`} style={{ fontSize: 13, lineHeight: 1.6, color: ds('#888', '#666', isDark) }}>{step.desc}</div>
													{step.date && (
														<div id={`preorder-raka-step-date-${idx}`} style={{
															marginTop: 6, fontSize: 12, fontWeight: 400,
															color: active ? T.red : ds('#666', '#888', isDark),
															display: 'flex', alignItems: 'center', gap: 4,
														}}>
															<i className="ri-time-line" style={{ fontSize: 12 }}/>{step.date}
														</div>
													)}
												</div>
											</div>
										);
									})}
								</div>
							</div>

							{/* Right details column */}
							<div id="preorder-raka-details-col" style={{ flex: '1 1 280px', display: 'flex', flexDirection: 'column', gap: 16 }}>

								{/* Product image */}
								{preorderData.image_url && (
									<div id="preorder-raka-product-img-box" style={{
										width: '100%', height: 210, borderRadius: 16, overflow: 'hidden',
										border: `1px solid ${ds(T.line, T.lLine, isDark)}`,
										boxShadow: '0 8px 32px rgba(0,0,0,0.3)',
									}}>
										<img id="preorder-raka-product-img" src={preorderData.image_url} alt={preorderData.product_name}
											style={{ width: '100%', height: '100%', objectFit: 'cover', display: 'block' }}/>
									</div>
								)}

								{/* Address */}
								<div id="preorder-raka-addr-card" style={S.card}>
									<span style={S.sectionLabel}><i className="ri-map-pin-line" style={{ marginRight: 4 }}/>ที่อยู่จัดส่ง</span>
									<p id="preorder-raka-addr-val" style={{ margin: 0, fontSize: 14, fontWeight: 400, color: ds('#ccc', '#333', isDark), lineHeight: 1.65 }}>
										{preorderData.address || '–'}
									</p>
								</div>

								{/* Tracking */}
								{preorderData.tracking_no && (
									<div id="preorder-raka-tracking-card" style={{
										...S.card,
										background: T.redSubtle,
										border: `1px solid ${T.redBorder}`,
									}}>
										<div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 8 }}>
											<span style={{ ...S.sectionLabel, color: T.redDeep, marginBottom: 0 }}>
												<i className="ri-scan-line" style={{ marginRight: 4 }}/>หมายเลขพัสดุ
											</span>
											{/* Courier badge */}
											{(() => {
												const courierMap = {
													kerry: { name: 'Kerry Express', icon: 'ri-truck-fill' },
													flash: { name: 'Flash Express', icon: 'ri-flashlight-fill' },
													jt:    { name: 'J&T Express', icon: 'ri-send-plane-fill' },
													thp:   { name: 'ไปรษณีย์ไทย', icon: 'ri-mail-fill' },
													spx:   { name: 'Shopee Xpress', icon: 'ri-shopping-bag-fill' },
													ninja: { name: 'Ninja Van', icon: 'ri-user-star-fill' },
													other: { name: 'ขนส่งเอกชน', icon: 'ri-truck-line' },
												};
												const info = courierMap[preorderData.courier] || courierMap.kerry;
												return (
													<span id="preorder-raka-courier-badge" style={{
														display: 'inline-flex', alignItems: 'center', gap: 4,
														padding: '3px 10px', borderRadius: 9999,
														background: 'rgba(225,29,72,0.12)', border: '1px solid rgba(225,29,72,0.25)',
														color: T.red, fontSize: 11, fontWeight: 500,
													}}>
														<i className={info.icon} style={{ fontSize: 12 }}/>
														{info.name}
													</span>
												);
											})()}
										</div>

										<div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: 8, marginTop: 4 }}>
											<span id="preorder-raka-tracking-val" style={{
												fontSize: 18, fontWeight: 500, color: T.red,
												letterSpacing: '0.5px', fontVariantNumeric: 'tabular-nums',
											}}>{preorderData.tracking_no}</span>
											<button id="preorder-raka-copy-btn" onClick={() => handleCopy(preorderData.tracking_no)} style={{
												padding: '6px 16px', borderRadius: 9999, fontFamily: 'inherit',
												border: `1px solid ${copied ? 'rgba(34,197,94,0.4)' : T.redBorder}`,
												background: copied
													? 'linear-gradient(135deg, rgba(34,197,94,0.2), rgba(34,197,94,0.1))'
													: `linear-gradient(135deg, ${T.redSubtle}, rgba(225,29,72,0.15))`,
												color: copied ? T.green : T.red,
												fontSize: 12, fontWeight: 500, cursor: 'pointer',
												transition: 'all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1)',
												display: 'flex', alignItems: 'center', gap: 6,
												transform: copied ? 'scale(1.08)' : 'scale(1)',
												boxShadow: copied
													? '0 0 16px rgba(34,197,94,0.35), 0 2px 8px rgba(34,197,94,0.2)'
													: `0 2px 8px ${T.redGlow}`,
												outline: 'none', position: 'relative', overflow: 'hidden',
											}}>
												<i className={copied ? 'ri-checkbox-circle-fill' : 'ri-file-copy-2-line'} style={{
													fontSize: 14,
													transition: 'transform 0.3s ease',
													transform: copied ? 'rotate(360deg) scale(1.2)' : 'rotate(0deg) scale(1)',
												}}/>
												<span>{copied ? 'คัดลอกเรียบร้อย!' : 'คัดลอก'}</span>
											</button>
										</div>

										{/* External Tracking Link Button */}
										{preorderData.tracking_link && (
											<div style={{ marginTop: 12, paddingTop: 10, borderTop: `1px solid rgba(225,29,72,0.15)` }}>
												<a
													id="preorder-raka-external-track-btn"
													href={preorderData.tracking_link}
													target="_blank"
													rel="noopener noreferrer"
													style={{
														display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 6,
														padding: '9px 16px', borderRadius: 9999,
														background: `linear-gradient(135deg, ${T.red}, ${T.redDeep})`,
														color: '#ffffff', fontSize: 13, fontWeight: 500, textDecoration: 'none',
														boxShadow: `0 4px 14px ${T.redGlow}`, transition: 'all 0.25s',
													}}
												>
													<i className="ri-external-link-line" style={{ fontSize: 14 }}/>
													<span>ติดตามพัสดุผ่านเว็บไซต์ขนส่ง</span>
												</a>
											</div>
										)}
									</div>
								)}

								{/* Social Contact Card (Facebook & LINE) */}
								{(preorderData.facebook || preorderData.line) && (
									<div id="preorder-raka-social-card" style={S.card}>
										<span style={S.sectionLabel}>
											<i className="ri-customer-service-2-line" style={{ marginRight: 4 }}/>สอบถามและช่วยเหลือ
										</span>
										<div style={{ display: 'flex', flexDirection: 'column', gap: 10, marginTop: 4 }}>
											{preorderData.facebook && (
												<a
													id="preorder-raka-facebook-btn"
													href={preorderData.facebook}
													target="_blank"
													rel="noopener noreferrer"
													style={{
														display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 8,
														padding: '10px 16px', borderRadius: 9999,
														background: 'linear-gradient(135deg, #1877f2, #0056b3)',
														color: '#ffffff', fontSize: 13, fontWeight: 500, textDecoration: 'none',
														boxShadow: '0 4px 14px rgba(24, 119, 242, 0.3)', transition: 'all 0.25s',
													}}
												>
													<i className="ri-facebook-circle-fill" style={{ fontSize: 18 }}/>
													<span>ติดต่อผ่าน Facebook / Messenger</span>
												</a>
											)}
											{preorderData.line && (
												<a
													id="preorder-raka-line-btn"
													href={preorderData.line}
													target="_blank"
													rel="noopener noreferrer"
													style={{
														display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 8,
														padding: '10px 16px', borderRadius: 9999,
														background: 'linear-gradient(135deg, #06c755, #05a044)',
														color: '#ffffff', fontSize: 13, fontWeight: 500, textDecoration: 'none',
														boxShadow: '0 4px 14px rgba(6, 199, 85, 0.3)', transition: 'all 0.25s',
													}}
												>
													<i className="ri-line-fill" style={{ fontSize: 18 }}/>
													<span>สอบถามแอดมินทาง LINE Official</span>
												</a>
											)}
										</div>
									</div>
								)}

								{/* Summary totals */}
								<div id="preorder-raka-summary-card" style={S.card}>
									<span style={S.sectionLabel}><i className="ri-receipt-line" style={{ marginRight: 4 }}/>สรุปค่าใช้จ่าย</span>
									{[
										{ id: 'prod',  label: 'สินค้า',   val: `${preorderData.price.toLocaleString()} บาท` },
										{ id: 'shipc', label: 'ค่าจัดส่ง', val: `${preorderData.shipping.toLocaleString()} บาท` },
									].map(r => (
										<div id={`preorder-raka-sum-row-${r.id}`} key={r.id} style={{
											display: 'flex', justifyContent: 'space-between', padding: '8px 0',
											borderBottom: `1px solid ${ds(T.line, T.lLine, isDark)}`,
										}}>
											<span style={{ fontSize: 14, color: ds('#888', '#777', isDark) }}>{r.label}</span>
											<span style={{ fontSize: 14, fontWeight: 500, color: ds('#ddd', '#333', isDark) }}>{r.val}</span>
										</div>
									))}
									<div id="preorder-raka-sum-total-row" style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', paddingTop: 12 }}>
										<span style={{ fontSize: 14, fontWeight: 500, color: ds('#888', '#888', isDark) }}>รวมทั้งสิ้น</span>
										<span style={{ fontSize: 24, fontWeight: 500, color: T.red }}>{preorderData.total.toLocaleString()} บาท</span>
									</div>
								</div>
							</div>
						</div>
					</div>
				)}
			</div>
		</div>
	);
};

export default PreOrderApp;
