import React, { useState, useEffect, useRef } from 'react';


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

	const [isDark, setIsDark]           = useState(() => localStorage.getItem('preorder_raka_theme') !== 'light');
	const [screen, setScreen]           = useState('search');
	const [initialVerifying, setInitialVerifying] = useState(true);
	const [orderNo, setOrderNo]         = useState('');
	const [phone, setPhone]             = useState('');
	const [citizenId, setCitizenId]     = useState('');
	const [verificationType, setVerificationType] = useState('phone');
	const [loading, setLoading]         = useState(false);
	const [error, setError]             = useState(null);
	const [preorderData, setPreorderData] = useState(null);
	const [copied, setCopied]           = useState(false);
	const [focused, setFocused]         = useState(null);
	const [width, setWidth]             = useState(window.innerWidth);

	// Pre-order products catalog state
	const [products, setProducts]               = useState([]);
	const [productsLoading, setProductsLoading] = useState(false);
	const [productSearch, setProductSearch]     = useState('');

	// Cloudflare Turnstile CAPTCHA state
	const [turnstileToken, setTurnstileToken]   = useState('');
	const turnstileContainerRef                 = useRef(null);
	const turnstileWidgetIdRef                  = useRef(null);

	// My Pre-orders state
	const [myOrders, setMyOrders]               = useState([]);
	const [myOrdersLoading, setMyOrdersLoading] = useState(false);
	const [myOrdersError, setMyOrdersError]     = useState(null);


	// Handle resize for custom responsive styling in JS
	useEffect(() => {
		const handleResize = () => setWidth(window.innerWidth);
		window.addEventListener('resize', handleResize);
		return () => window.removeEventListener('resize', handleResize);
	}, []);

	const isMobile = width < 768;
	const S = useStyles(isDark, isMobile);

	// Fetch My Pre-orders (by logged-in user or user_id)
	const fetchMyOrders = async (targetUserId) => {
		setScreen('my-orders');
		setMyOrdersLoading(true);
		setMyOrdersError(null);
		try {
			let endpoint = '';
			const rootUrl = settings.root || '/wp-json/preorder-raka/v1';
			const params = new URLSearchParams();
			if (targetUserId) {
				params.append('user_id', targetUserId);
			} else {
				params.append('my_orders', '1');
			}

			if (rootUrl.includes('rest_route=')) {
				const [basePath] = rootUrl.split('?');
				endpoint = `${basePath}?rest_route=/preorder-raka/v1/status&${params.toString()}`;
			} else {
				endpoint = `${rootUrl.replace(/\/$/, '')}/status?${params.toString()}`;
			}

			const res = await fetch(endpoint, {
				headers: { 'X-WP-Nonce': settings.nonce || '' },
				credentials: 'same-origin',
			});
			const json = await res.json();
			if (!res.ok) {
				throw new Error(json.message || 'ไม่พบรายการพรีออเดอร์ในบัญชีของคุณ');
			}
			if (json.success) {
				const items = Array.isArray(json.items) ? json.items : (json.data ? [json.data] : []);
				setMyOrders(items);
			}
		} catch (e) {
			setMyOrdersError(e.message || 'เกิดข้อผิดพลาดในการโหลดข้อมูล');
		} finally {
			setMyOrdersLoading(false);
		}
	};

	// Fetch Pre-order Products catalog
	const fetchPreorderProducts = async () => {
		if (products.length > 0) return;
		setProductsLoading(true);
		try {
			let endpoint = '';
			const rootUrl = settings.root || '/wp-json/preorder-raka/v1';
			if (rootUrl.includes('rest_route=')) {
				const [basePath] = rootUrl.split('?');
				endpoint = `${basePath}?rest_route=/preorder-raka/v1/products`;
			} else {
				endpoint = `${rootUrl.replace(/\/$/, '')}/products`;
			}
			const res = await fetch(endpoint, { headers: { 'X-WP-Nonce': settings.nonce || '' } });
			if (res.ok) {
				const json = await res.json();
				if (json.success && Array.isArray(json.data)) {
					setProducts(json.data);
				}
			}
		} catch (e) {
			console.error('Failed to load preorder products', e);
		} finally {
			setProductsLoading(false);
		}
	};

	const filteredProducts = products.filter((p) => {
		if (!productSearch) return true;
		const query = productSearch.toLowerCase();
		return (
			p.title?.toLowerCase().includes(query) ||
			(p.description && p.description.toLowerCase().includes(query))
		);
	});

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

	// Cloudflare Turnstile widget initialization effect
	useEffect(() => {
		if (!settings.turnstileEnabled || !settings.turnstileSitekey) return;

		let intervalId = null;

		const renderTurnstileWidget = () => {
			if (!window.turnstile || !turnstileContainerRef.current) return;
			if (turnstileContainerRef.current.hasChildNodes() && turnstileWidgetIdRef.current !== null) return;

			try {
				const widgetId = window.turnstile.render(turnstileContainerRef.current, {
					sitekey: settings.turnstileSitekey,
					theme: settings.turnstileTheme || 'auto',
					callback: (token) => {
						setTurnstileToken(token);
						setError(null);
					},
					'expired-callback': () => {
						setTurnstileToken('');
					},
					'error-callback': () => {
						setTurnstileToken('');
					},
				});
				turnstileWidgetIdRef.current = widgetId;
			} catch (e) {
				console.log('Turnstile render init:', e);
			}
		};

		if (window.turnstile) {
			renderTurnstileWidget();
		} else {
			intervalId = setInterval(() => {
				if (window.turnstile) {
					clearInterval(intervalId);
					renderTurnstileWidget();
				}
			}, 250);
		}

		return () => {
			if (intervalId) clearInterval(intervalId);
		};
	}, [screen, settings.turnstileEnabled, settings.turnstileSitekey, settings.turnstileTheme]);

	const fetchStatus = async ({ pId, pOrderNo, pPhone, pCitizen, pType, pTurnstileToken }) => {
		const targetPhone = pPhone !== undefined ? pPhone : phone;
		const targetCitizen = pCitizen !== undefined ? pCitizen : citizenId;
		const targetType = pType || verificationType;
		const targetOrderNo = pOrderNo || orderNo;
		const activeTurnstileToken = pTurnstileToken !== undefined ? pTurnstileToken : (turnstileToken || (window.turnstile && turnstileWidgetIdRef.current !== null ? window.turnstile.getResponse(turnstileWidgetIdRef.current) : ''));

		setLoading(true); setError(null);
		try {
			let url = '';
			const rootUrl = settings.root || '/wp-json/preorder-raka/v1';

			if (rootUrl.includes('rest_route=')) {
				const [basePath] = rootUrl.split('?');
				const params = new URLSearchParams();
				params.set('rest_route', '/preorder-raka/v1/status');
				
				if (pId || (postId && screen === 'verify-direct')) params.set('post_id', pId || postId);
				else params.set('order_no', targetOrderNo);

				if (targetType === 'phone') params.set('phone', targetPhone);
				else params.set('citizen_id', targetCitizen);

				if (activeTurnstileToken) {
					params.set('cf_turnstile_response', activeTurnstileToken);
				}

				url = `${basePath}?${params.toString()}`;
			} else {
				const baseUrl = `${rootUrl.replace(/\/$/, '')}/status`;
				const params = new URLSearchParams();
				if (pId || (postId && screen === 'verify-direct')) params.append('post_id', pId || postId);
				else params.append('order_no', targetOrderNo);

				if (targetType === 'phone') params.append('phone', targetPhone);
				else params.append('citizen_id', targetCitizen);

				if (activeTurnstileToken) {
					params.append('cf_turnstile_response', activeTurnstileToken);
				}

				url = `${baseUrl}?${params.toString()}`;
			}

			const res  = await fetch(url, { headers: { 'X-WP-Nonce': settings.nonce } });
			const data = await res.json();
			if (!res.ok) throw new Error(data.message || 'ไม่สามารถดึงข้อมูลได้');
			
			saveAuthCache(targetPhone, targetCitizen, targetType);

			setPreorderData(data.data); setScreen('result');
		} catch (err) {
			setError(err.message);
			if (window.turnstile && turnstileWidgetIdRef.current !== null) {
				try {
					window.turnstile.reset(turnstileWidgetIdRef.current);
					setTurnstileToken('');
				} catch (e) {}
			}
		}
		finally { setLoading(false); }
	};


	const handleSearch = async (e) => {
		if (e) e.preventDefault();
		await fetchStatus({});
	};

	const handleReset = () => {
		setPreorderData(null); setError(null);
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

			{/* Phone number verification input */}
			<div id="preorder-raka-input-phone-group">
				<label id="preorder-raka-phone-label" htmlFor="phone" style={S.sectionLabel}>
					<i className="ri-phone-line" style={{ marginRight: 6 }}/>เบอร์โทรศัพท์
				</label>
				<input id="phone" style={S.input('phone', focused)} type="tel" placeholder="0897777777"
					value={phone} onChange={e => setPhone(e.target.value)}
					onFocus={() => setFocused('phone')} onBlur={() => setFocused(null)} required/>
			</div>

			{/* Cloudflare Turnstile CAPTCHA Widget */}
			{settings.turnstileEnabled && (
				<div
					id="preorder-raka-turnstile-container"
					style={{
						display: 'flex',
						flexDirection: 'column',
						alignItems: 'center',
						justifyContent: 'center',
						minHeight: 65,
						borderRadius: 14,
						background: isDark ? 'rgba(255,255,255,0.02)' : 'rgba(0,0,0,0.02)',
						border: isDark ? '1px solid rgba(255,255,255,0.08)' : '1px solid rgba(0,0,0,0.08)',
						padding: '8px 12px',
						overflow: 'hidden',
					}}
				>
					<div ref={turnstileContainerRef} id="preorder-raka-turnstile-widget" />
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

			{/* Quick Catalog Link on Search Screen */}
			{screen === 'search' && (
				<div style={{ textAlign: 'center', paddingTop: 8 }}>
					<button
						type="button"
						id="preorder-jump-catalog-btn"
						onClick={() => {
							setScreen('products');
							fetchPreorderProducts();
						}}
						style={{
							background: 'transparent',
							border: 'none',
							color: T.red,
							fontSize: 14,
							fontWeight: 600,
							cursor: 'pointer',
							display: 'inline-flex',
							alignItems: 'center',
							gap: 6,
							padding: '6px 12px',
							borderRadius: 8,
							transition: 'all 0.2s ease',
						}}
					>
						<i className="ri-shopping-bag-3-line" style={{ fontSize: 16 }} />
						<span>ยังไม่มีเลขคำสั่งซื้อ? ดูสินค้าพรีออเดอร์ทั้งหมด</span>
						<i className="ri-arrow-right-s-line" style={{ fontSize: 16 }} />
					</button>
				</div>
			)}
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
				<div id="preorder-raka-nav-row" style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 20, position: 'relative' }}>
					{/* Logo */}
					<div id="preorder-raka-logo-group" style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
						{settings.logoUrl ? (
							<img
								id="preorder-raka-site-logo-img"
								src={settings.logoUrl}
								alt={settings.siteName || 'Logo'}
								style={{ height: 44, width: 'auto', maxHeight: 44, objectFit: 'contain', borderRadius: 8 }}
							/>
						) : (
							<div id="preorder-raka-logo-badge" style={{
								width: 44, height: 44, borderRadius: 10, flexShrink: 0,
								background: '#ffffff', display: 'flex', alignItems: 'center', justifyContent: 'center',
								fontWeight: 500, fontSize: 20, color: '#dc2626',
								boxShadow: '0 4px 16px rgba(0,0,0,0.15)'
							}}>{(settings.siteName || 'R')[0]}</div>
						)}
						<div id="preorder-raka-logo-text-block">
							<div id="preorder-raka-logo-name" style={{ fontSize: 18, fontWeight: 500, letterSpacing: '-0.3px', color: '#ffffff', lineHeight: 1.15 }}>
								{settings.siteName || 'RAKA GADGET'}
							</div>
							<div id="preorder-raka-logo-sub" style={{ fontSize: 11, fontWeight: 400, letterSpacing: '1.1px', color: 'rgba(255,255,255,0.75)', textTransform: 'uppercase' }}>
								Pre-Order Hub
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

				{/* ── Mode Switcher Pills (Tracker vs My Orders vs Preorder Catalog) ── */}
				<div id="preorder-raka-mode-switcher-wrap" style={{ marginBottom: 24 }}>
					<div id="preorder-raka-mode-pills" style={{
						display: 'inline-flex', flexWrap: 'wrap', gap: 6, padding: 4, borderRadius: 9999,
						background: 'rgba(0, 0, 0, 0.22)', border: '1px solid rgba(255, 255, 255, 0.25)',
						backdropFilter: 'blur(8px)'
					}}>
						<button
							id="preorder-tab-btn-tracker"
							type="button"
							onClick={() => setScreen(postId ? 'verify-direct' : 'search')}
							style={{
								padding: '7px 16px', borderRadius: 9999, border: 'none',
								fontFamily: 'inherit', fontSize: 13, fontWeight: 600, cursor: 'pointer', transition: 'all 0.25s',
								background: screen !== 'products' && screen !== 'my-orders' ? '#ffffff' : 'transparent',
								color: screen !== 'products' && screen !== 'my-orders' ? '#dc2626' : '#ffffff',
								boxShadow: screen !== 'products' && screen !== 'my-orders' ? '0 2px 10px rgba(0,0,0,0.2)' : 'none',
								display: 'inline-flex', alignItems: 'center', gap: 6,
							}}
						>
							<i className="ri-radar-line" />
							<span>เช็คสถานะ</span>
						</button>
						<button
							id="preorder-tab-btn-my-orders"
							type="button"
							onClick={() => {
								if (settings.isLoggedIn) {
									fetchMyOrders();
								} else {
									window.location.href = settings.loginUrl || '/my-account/';
								}
							}}
							style={{
								padding: '7px 16px', borderRadius: 9999, border: 'none',
								fontFamily: 'inherit', fontSize: 13, fontWeight: 600, cursor: 'pointer', transition: 'all 0.25s',
								background: screen === 'my-orders' ? '#ffffff' : 'transparent',
								color: screen === 'my-orders' ? '#dc2626' : '#ffffff',
								boxShadow: screen === 'my-orders' ? '0 2px 10px rgba(0,0,0,0.2)' : 'none',
								display: 'inline-flex', alignItems: 'center', gap: 6,
							}}
						>
							<i className={settings.isLoggedIn ? "ri-user-star-line" : "ri-lock-line"} />
							<span>{settings.isLoggedIn ? 'พรีออเดอร์ของฉัน' : 'เข้าสู่ระบบ'}</span>
						</button>
						<button
							id="preorder-tab-btn-catalog"
							type="button"
							onClick={() => {
								setScreen('products');
								fetchPreorderProducts();
							}}
							style={{
								padding: '7px 16px', borderRadius: 9999, border: 'none',
								fontFamily: 'inherit', fontSize: 13, fontWeight: 600, cursor: 'pointer', transition: 'all 0.25s',
								background: screen === 'products' ? '#ffffff' : 'transparent',
								color: screen === 'products' ? '#dc2626' : '#ffffff',
								boxShadow: screen === 'products' ? '0 2px 10px rgba(0,0,0,0.2)' : 'none',
								display: 'inline-flex', alignItems: 'center', gap: 6,
							}}
						>
							<i className="ri-shopping-bag-3-line" />
							<span>ดูสินค้าพรีออเดอร์</span>
							{products.length > 0 && (
								<span style={{
									background: screen === 'products' ? '#dc2626' : 'rgba(255,255,255,0.3)',
									color: '#ffffff', fontSize: 11, padding: '1px 6px', borderRadius: 9999, fontWeight: 700
								}}>
									{products.length}
								</span>
							)}
						</button>
					</div>
				</div>

				{/* Hero title — My Orders */}
				{screen === 'my-orders' && (
					<div id="preorder-raka-hero-content-my-orders" style={{ position: 'relative' }}>
						<div id="preorder-raka-hero-chip-my-orders" style={{
							display: 'inline-flex', alignItems: 'center', gap: 6,
							padding: '8px 16px', borderRadius: 9999,
							background: 'rgba(0, 0, 0, 0.2)', border: '1px solid rgba(255, 255, 255, 0.25)',
							fontSize: 14, fontWeight: 500, letterSpacing: '0.6px', color: '#ffffff',
						}} className="mb-3">
							<i className="ri-user-smile-line" style={{ fontSize: 14 }} />
							<span>บัญชีสมาชิก {settings.userName ? `(${settings.userName})` : ''}</span>
						</div>
						<h2 id="preorder-raka-hero-title-my-orders" style={{ margin: '12px 0 6px', fontSize: 28, fontWeight: 500, letterSpacing: '-0.6px', color: '#ffffff', lineHeight: 1.15 }}>
							รายการพรีออเดอร์ของคุณ
						</h2>
						<p id="preorder-raka-hero-desc-my-orders" style={{ margin: 0, fontSize: 15, color: 'rgba(255, 255, 255, 0.85)', lineHeight: 1.65 }}>
							ประวัติและสถานะการสั่งซื้อสินค้าพรีออเดอร์ทั้งหมดที่ผูกกับบัญชีสมาชิกของคุณ
						</p>
					</div>
				)}

				{/* Hero title — Products Catalog */}
				{screen === 'products' && (
					<div id="preorder-raka-hero-content-products" style={{ position: 'relative' }}>
						<div id="preorder-raka-hero-chip-products" style={{
							display: 'inline-flex', alignItems: 'center', gap: 6,
							padding: '8px 16px', borderRadius: 9999,
							background: 'rgba(0, 0, 0, 0.2)', border: '1px solid rgba(255, 255, 255, 0.25)',
							fontSize: 14, fontWeight: 500, letterSpacing: '0.6px', color: '#ffffff',
						}} className="mb-3">
							<i className="ri-sparkling-fill" style={{ fontSize: 14 }} />
							<span>แคตตาล็อกสินค้าพรีออเดอร์</span>
						</div>
						<h2 id="preorder-raka-hero-title-products" style={{ margin: '12px 0 6px', fontSize: 28, fontWeight: 500, letterSpacing: '-0.6px', color: '#ffffff', lineHeight: 1.15 }}>
							สินค้าเปิดรับพรีออเดอร์ (Pre-Orders)
						</h2>
						<p id="preorder-raka-hero-desc-products" style={{ margin: 0, fontSize: 15, color: 'rgba(255, 255, 255, 0.85)', lineHeight: 1.65 }}>
							รวมสินค้าเกมมิ่งเกียร์ คีย์บอร์ด และอุปกรณ์คัสตอมนำเข้าจากต่างประเทศ สั่งจองได้ก่อนใคร
						</p>
					</div>
				)}

				{/* Hero title — search / verify */}
				{screen !== 'result' && screen !== 'products' && screen !== 'my-orders' && (
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

				{/* MY PRE-ORDERS LIST VIEW */}
				{screen === 'my-orders' && (
					<div id="preorder-raka-my-orders-view" style={{ display: 'flex', flexDirection: 'column', gap: 20 }}>
						<div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', borderBottom: `1px solid ${ds(T.line, T.lLine, isDark)}`, paddingBottom: 16 }}>
							<div>
								<div style={{ fontSize: 12, color: T.red, fontWeight: 700, letterSpacing: '0.5px', textTransform: 'uppercase' }}>
									บัญชีสมาชิก: {settings.userName || 'คุณ'}
								</div>
								<h3 style={{ margin: '4px 0 0', fontSize: 20, fontWeight: 700, color: ds('#ffffff', '#111827', isDark) }}>
									รายการพรีออเดอร์ของคุณ ({myOrders.length} รายการ)
								</h3>
							</div>
							<button
								type="button"
								onClick={() => setScreen(postId ? 'verify-direct' : 'search')}
								style={S.backBtn}
							>
								<i className="ri-arrow-left-s-line" />
								<span>ค้นหาด้วยเลขออเดอร์</span>
							</button>
						</div>

						{myOrdersLoading && (
							<div style={{ padding: 48, textAlign: 'center', color: T.red }}>
								<i className="ri-loader-4-line" style={{ fontSize: 32, animation: 'spin 1s linear infinite' }} />
								<p style={{ margin: '12px 0 0', fontSize: 14, color: ds('#888', '#666', isDark) }}>กำลังโหลดรายการพรีออเดอร์...</p>
							</div>
						)}

						{myOrdersError && !myOrdersLoading && (
							<div style={{ padding: 24, borderRadius: 16, background: 'rgba(225,29,72,0.1)', border: `1px solid ${T.redBorder}`, textAlign: 'center', color: T.red }}>
								<i className="ri-error-warning-line" style={{ fontSize: 28 }} />
								<p style={{ margin: '8px 0 0', fontSize: 14, fontWeight: 600 }}>{myOrdersError}</p>
							</div>
						)}

						{!myOrdersLoading && !myOrdersError && myOrders.length === 0 && (
							<div style={{ padding: 48, textAlign: 'center', borderRadius: 16, background: ds(T.bg2, T.lBg2, isDark), border: `1px solid ${ds(T.line, T.lLine, isDark)}` }}>
								<i className="ri-inbox-line" style={{ fontSize: 40, color: ds('#555', '#aaa', isDark) }} />
								<p style={{ margin: '12px 0 4px', fontSize: 16, fontWeight: 600, color: ds('#fff', '#111', isDark) }}>ยังไม่มีรายการพรีออเดอร์ในบัญชีของคุณ</p>
								<p style={{ margin: 0, fontSize: 13, color: ds('#888', '#666', isDark) }}>เมื่อคุณสั่งซื้อสินค้าพรีออเดอร์ รายการจะปรากฏที่นี่โดยอัตโนมัติ</p>
							</div>
						)}

						{!myOrdersLoading && myOrders.length > 0 && (
							<div style={{ display: 'flex', flexDirection: 'column', gap: 14 }}>
								{myOrders.map((item, idx) => (
									<div
										key={item.id || idx}
										onClick={() => {
											setPreorderData(item);
											setScreen('result');
										}}
										style={{
											...S.card,
											cursor: 'pointer',
											display: 'flex',
											flexDirection: isMobile ? 'column' : 'row',
											alignItems: isMobile ? 'flex-start' : 'center',
											justifyContent: 'space-between',
											gap: 16,
											boxShadow: isDark ? '0 4px 16px rgba(0,0,0,0.3)' : '0 2px 10px rgba(0,0,0,0.04)',
											transition: 'all 0.2s ease',
										}}
									>
										<div style={{ display: 'flex', alignItems: 'center', gap: 14, minWidth: 0, flex: 1 }}>
											<div style={{ width: 56, height: 56, borderRadius: 12, overflow: 'hidden', background: ds(T.bg3, T.lBg3, isDark), flexShrink: 0, border: `1px solid ${ds(T.line, T.lLine, isDark)}`, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
												{item.image_url ? (
													<img src={item.image_url} alt={item.product_name} style={{ width: '100%', height: '100%', objectFit: 'cover' }} />
												) : (
													<i className="ri-shopping-bag-3-line" style={{ fontSize: 22, color: ds('#666', '#aaa', isDark) }} />
												)}
											</div>
											<div style={{ minWidth: 0, flex: 1 }}>
												<div style={{ display: 'flex', alignItems: 'center', gap: 8, marginBottom: 3 }}>
													<span style={{ fontSize: 12, fontFamily: 'monospace', fontWeight: 700, color: ds('#ffffff', '#111827', isDark) }}>
														{item.order_no}
													</span>
													<span style={{ fontSize: 11, color: ds('#777', '#888', isDark) }}>
														{item.created_at}
													</span>
												</div>
												<h4 style={{ margin: 0, fontSize: 15, fontWeight: 600, color: ds('#ffffff', '#111827', isDark), whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }}>
													{item.product_name}
												</h4>
											</div>
										</div>

										<div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', width: isMobile ? '100%' : 'auto', gap: 20 }}>
											<div style={{ textAlign: isMobile ? 'left' : 'right' }}>
												<div style={{ fontSize: 16, fontWeight: 700, color: T.red, fontFamily: 'monospace' }}>
													฿{(item.total || item.price || 0).toLocaleString()}
												</div>
												<div style={{ fontSize: 12, color: ds('#888', '#777', isDark) }}>
													{item.qty || 1} ชิ้น
												</div>
											</div>

											<div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
												<span style={{
													padding: '4px 10px', borderRadius: 9999,
													background: T.redSubtle, border: `1px solid ${T.redBorder}`,
													color: T.red, fontSize: 12, fontWeight: 600,
													display: 'inline-flex', alignItems: 'center', gap: 5,
												}}>
													<span style={{ width: 6, height: 6, borderRadius: '50%', background: T.red }} />
													{item.current_status}
												</span>
												<i className="ri-arrow-right-s-line" style={{ fontSize: 20, color: ds('#666', '#aaa', isDark) }} />
											</div>
										</div>
									</div>
								))}
							</div>
						)}
					</div>
				)}

				{/* PRE-ORDER PRODUCTS CATALOG VIEW */}
				{screen === 'products' && (
					<div id="preorder-raka-products-catalog" style={{ display: 'flex', flexDirection: 'column', gap: 24 }}>
						{/* Search Bar & Product Count */}
						<div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: 12, flexWrap: 'wrap' }}>
							<div style={{ position: 'relative', flex: '1 1 260px', maxWidth: 420 }}>
								<i className="ri-search-line" style={{ position: 'absolute', left: 16, top: '50%', transform: 'translateY(-50%)', color: ds('#888', '#aaa', isDark), fontSize: 16 }} />
								<input
									type="text"
									placeholder="ค้นหาชื่อสินค้าพรีออเดอร์..."
									value={productSearch}
									onChange={(e) => setProductSearch(e.target.value)}
									style={{
										...S.input('prod_search', focused),
										paddingLeft: 42,
										paddingRight: 16,
									}}
									onFocus={() => setFocused('prod_search')}
									onBlur={() => setFocused(null)}
								/>
							</div>
							<div style={{ fontSize: 13, color: ds('#888', '#666', isDark), fontWeight: 600 }}>
								พบทั้งหมด {filteredProducts.length} รายการ
							</div>
						</div>

						{/* Loading state */}
						{productsLoading ? (
							<div style={{ padding: '60px 0', textAlign: 'center', color: ds('#888', '#666', isDark) }}>
								<i className="ri-loader-4-line" style={{ fontSize: 32, animation: 'spin 1s linear infinite', color: T.red, display: 'inline-block', marginBottom: 12 }} />
								<div>กำลังโหลดรายการสินค้าพรีออเดอร์...</div>
							</div>
						) : filteredProducts.length === 0 ? (
							<div style={{ padding: '60px 20px', textAlign: 'center', background: ds(T.bg2, T.lBg2, isDark), borderRadius: 16, border: `1px solid ${ds(T.line, T.lLine, isDark)}` }}>
								<i className="ri-inbox-line" style={{ fontSize: 40, color: ds('#666', '#bbb', isDark), marginBottom: 12, display: 'inline-block' }} />
								<h4 style={{ margin: '0 0 6px', fontSize: 16, color: ds('#fff', '#111', isDark) }}>ไม่พบสินค้าที่ค้นหา</h4>
								<p style={{ margin: 0, fontSize: 13, color: ds('#888', '#666', isDark) }}>ลองค้นหาด้วยคำค้นอื่น หรือล้างคำค้นหา</p>
							</div>
						) : (
							/* Grid of products */
							<div id="preorder-raka-products-grid" style={{
								display: 'grid',
								gridTemplateColumns: isMobile ? '1fr' : 'repeat(auto-fill, minmax(280px, 1fr))',
								gap: 20
							}}>
								{filteredProducts.map((p) => (
									<div key={p.id} id={`preorder-prod-card-${p.id}`} style={{
										...S.card,
										display: 'flex', flexDirection: 'column', overflow: 'hidden', padding: 0,
										border: `1px solid ${ds(T.line, T.lLine, isDark)}`,
										boxShadow: isDark ? '0 4px 20px rgba(0,0,0,0.4)' : '0 4px 16px rgba(0,0,0,0.05)',
										transition: 'all 0.3s ease',
									}}>
										{/* Product Image */}
										<div style={{ width: '100%', height: 210, position: 'relative', background: ds(T.bg3, T.lBg3, isDark), overflow: 'hidden' }}>
											{p.image_url ? (
												<img
													src={p.image_url}
													alt={p.title}
													style={{ width: '100%', height: '100%', objectFit: 'cover', transition: 'transform 0.5s' }}
												/>
											) : (
												<div style={{ width: '100%', height: '100%', display: 'flex', alignItems: 'center', justifyContent: 'center', color: ds('#555', '#bbb', isDark) }}>
													<i className="ri-image-line" style={{ fontSize: 36 }} />
												</div>
											)}
											<span style={{
												position: 'absolute', top: 12, left: 12,
												padding: '4px 10px', borderRadius: 9999,
												background: 'rgba(225,29,72,0.92)', color: '#ffffff',
												fontSize: 10, fontWeight: 700, letterSpacing: '0.6px',
												boxShadow: '0 2px 8px rgba(225,29,72,0.4)',
												backdropFilter: 'blur(4px)',
											}}>
												PRE-ORDER
											</span>
										</div>

										{/* Content */}
										<div style={{ padding: '18px 20px', display: 'flex', flexDirection: 'column', flex: 1, justifyContent: 'space-between' }}>
											<div>
												<h3 style={{ margin: '0 0 8px', fontSize: 16, fontWeight: 600, color: ds('#fff', '#111', isDark), lineHeight: 1.35 }}>
													{p.title}
												</h3>
												{p.description && (
													<p style={{
														margin: '0 0 14px', fontSize: 13, color: ds('#888', '#666', isDark), lineHeight: 1.5,
														display: '-webkit-box', WebkitLineClamp: 2, WebkitBoxOrient: 'vertical', overflow: 'hidden'
													}}>
														{p.description}
													</p>
												)}
											</div>

											<div style={{ paddingTop: 14, borderTop: `1px solid ${ds(T.line, T.lLine, isDark)}`, display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: 10 }}>
												<div>
													<div style={{ fontSize: 10, color: ds('#888', '#999', isDark), textTransform: 'uppercase', fontWeight: 600, letterSpacing: '0.5px' }}>ราคาพรีออเดอร์</div>
													<div style={{ fontSize: 20, fontWeight: 700, color: T.red }}>
														{p.price > 0 ? `฿${p.price.toLocaleString()}` : 'ติดต่อสั่งจอง'}
													</div>
												</div>
												<button
													type="button"
													onClick={() => {
														setOrderNo('');
														setScreen('search');
													}}
													style={{
														padding: '8px 16px', borderRadius: 9999,
														background: `linear-gradient(135deg, ${T.red}, ${T.redDeep})`,
														color: '#fff', border: 'none', fontSize: 12, fontWeight: 600,
														cursor: 'pointer', display: 'inline-flex', alignItems: 'center', gap: 5,
														boxShadow: `0 4px 12px ${T.redGlow}`,
														transition: 'all 0.2s',
													}}
												>
													<span>เช็คสถานะ</span>
													<i className="ri-arrow-right-line" />
												</button>
											</div>
										</div>
									</div>
								))}
							</div>
						)}
					</div>
				)}

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
								{ id: 'est',   icon: 'ri-time-line',          label: 'ระยะเวลาจัดส่งโดยประมาณ', val: preorderData.estimate_days || '7-14 วัน', accent: false },
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

								{/* Product image (if single or primary) */}
								{preorderData.image_url && (!preorderData.items || preorderData.items.length <= 1) && (
									<div id="preorder-raka-product-img-box" style={{
										width: '100%', height: 210, borderRadius: 16, overflow: 'hidden',
										border: `1px solid ${ds(T.line, T.lLine, isDark)}`,
										boxShadow: '0 8px 32px rgba(0,0,0,0.3)',
									}}>
										<img id="preorder-raka-product-img" src={preorderData.image_url} alt={preorderData.product_name}
											style={{ width: '100%', height: '100%', objectFit: 'cover', display: 'block' }}/>
									</div>
								)}

								{/* Multi-Item Product List Card */}
								{preorderData.items && preorderData.items.length > 0 && (
									<div id="preorder-raka-items-card" style={S.card}>
										<div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 12 }}>
											<span style={{ ...S.sectionLabel, marginBottom: 0 }}>
												<i className="ri-shopping-bag-3-line" style={{ marginRight: 4 }}/>รายการสินค้า ({preorderData.items.length})
											</span>
											<span style={{ fontSize: 12, color: T.red, fontWeight: 600 }}>
												{preorderData.items.reduce((acc, it) => acc + (it.qty || 1), 0)} ชิ้น
											</span>
										</div>

										<div style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>
											{preorderData.items.map((it, i) => (
												<div key={i} style={{
													display: 'flex', alignItems: 'center', gap: 10,
													padding: '8px 10px', borderRadius: 10,
													background: ds('rgba(255,255,255,0.03)', 'rgba(0,0,0,0.02)', isDark),
													border: `1px solid ${ds('rgba(255,255,255,0.06)', 'rgba(0,0,0,0.06)', isDark)}`
												}}>
													{it.image_url ? (
														<img src={it.image_url} alt={it.product_name} style={{ width: 42, height: 42, objectFit: 'cover', borderRadius: 8, flexShrink: 0, border: `1px solid ${ds(T.line, T.lLine, isDark)}` }} />
													) : (
														<div style={{ width: 42, height: 42, borderRadius: 8, background: ds(T.bg3, T.lBg3, isDark), display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0, color: ds('#666', '#aaa', isDark) }}>
															<i className="ri-box-3-line" style={{ fontSize: 18 }} />
														</div>
													)}
													<div style={{ flex: 1, minWidth: 0 }}>
														<div style={{ fontSize: 13, fontWeight: 500, color: ds('#eee', '#222', isDark), whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }}>
															{it.product_name}
														</div>
														<div style={{ fontSize: 11, color: ds('#888', '#666', isDark), marginTop: 2, display: 'flex', gap: 6 }}>
															<span>฿{(it.price || 0).toLocaleString()}</span>
															<span>× {it.qty || 1}</span>
														</div>
													</div>
													<div style={{ fontSize: 13, fontWeight: 600, color: ds('#fff', '#111', isDark), flexShrink: 0 }}>
														฿{((it.price || 0) * (it.qty || 1)).toLocaleString()}
													</div>
												</div>
											))}
										</div>
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
