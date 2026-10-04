<?php
/**
 * Modern Pre-Order Tracker V2 Component with Cloudflare Turnstile CAPTCHA.
 *
 * Lightweight, native vanilla JS AJAX search, Tab Switcher, and Turnstile integration.
 * Fully compatible with /wp-json/preorder-raka/v1/status.
 *
 * @package raka-preorder-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render Modern Pre-Order Tracker Component with Cloudflare Turnstile.
 *
 * @return string
 */
function raka_render_preorder_tracker_v2(): string {
	ob_start();
	$turnstile_enabled = function_exists( 'raka_is_turnstile_enabled' ) ? raka_is_turnstile_enabled() : true;
	$turnstile_sitekey = function_exists( 'raka_get_turnstile_sitekey' ) ? raka_get_turnstile_sitekey() : '0x4AAAAAAFNWAI83P9cCw0AU';
	$turnstile_theme   = function_exists( 'raka_get_turnstile_theme' ) ? raka_get_turnstile_theme() : 'auto';
	?>
	<?php if ( $turnstile_enabled ) : ?>
		<!-- Cloudflare Turnstile API -->
		<script src="https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit" async defer></script>
	<?php endif; ?>

	<div id="raka-tracker-v2" class="w-full text-left font-sans select-none text-neutral-900 dark:text-white transition-all duration-300">
		
		<!-- ── SEARCH / INPUT VIEW ── -->
		<div id="raka-tracker-search-view" class="w-full space-y-3.5">
			
			<!-- Mode Selector Tab Switcher -->
			<div id="raka-track-tab-wrap" class="grid grid-cols-2 p-1 rounded-2xl bg-neutral-100/90 dark:bg-neutral-800/80 border border-black/10 dark:border-white/10">
				<button
					type="button"
					id="raka-tab-btn-order"
					class="py-2.5 px-3 rounded-xl text-xs font-bold font-hn transition-all duration-200 flex items-center justify-center gap-1.5 cursor-pointer bg-white dark:bg-neutral-900 text-neutral-950 dark:text-white shadow-sm border border-black/5 dark:border-white/10"
					data-tab="order"
				>
					<i id="raka-tab-icon-order" class="ri-hashtag text-sm"></i>
					<span id="raka-tab-text-order"><?php esc_html_e( 'ค้นหาด้วยเลขออเดอร์', 'raka-preorder-theme' ); ?></span>
				</button>
				<button
					type="button"
					id="raka-tab-btn-phone"
					class="py-2.5 px-3 rounded-xl text-xs font-bold font-hn transition-all duration-200 flex items-center justify-center gap-1.5 cursor-pointer text-neutral-500 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white"
					data-tab="phone"
				>
					<i id="raka-tab-icon-phone" class="ri-phone-line text-sm"></i>
					<span id="raka-tab-text-phone"><?php esc_html_e( 'ค้นหาด้วยเบอร์โทร', 'raka-preorder-theme' ); ?></span>
				</button>
			</div>

			<!-- Primary Input Group -->
			<div id="raka-track-primary-group" class="space-y-1">
				<label id="raka-track-primary-label" for="raka-track-primary-input" class="block text-[11px] uppercase tracking-wider font-bold text-neutral-600 dark:text-neutral-400 font-hn">
					<?php esc_html_e( 'ORDER NUMBER / หมายเลขคำสั่งซื้อ', 'raka-preorder-theme' ); ?>
				</label>
				<div id="raka-track-primary-wrap" class="relative flex items-center">
					<i id="raka-track-primary-icon" class="ri-hashtag text-base text-neutral-400 dark:text-neutral-500 absolute left-3.5 pointer-events-none"></i>
					<input
						type="text"
						id="raka-track-primary-input"
						class="w-full pl-10 pr-4 py-2.5 rounded-2xl bg-neutral-100/90 dark:bg-neutral-800/80 border border-black/10 dark:border-white/10 text-xs sm:text-sm font-mono placeholder:text-neutral-400 dark:placeholder:text-neutral-500 focus:outline-none focus:ring-2 focus:ring-red-600 dark:focus:ring-red-500 transition-all"
						placeholder="เช่น RAKA-PRE-312691"
						autocomplete="off"
					/>
				</div>
			</div>

			<!-- Cloudflare Turnstile CAPTCHA Widget -->
			<div id="raka-turnstile-wrapper" class="pt-1 flex flex-col items-center justify-center min-h-[65px] rounded-2xl bg-neutral-50/50 dark:bg-neutral-800/40 border border-black/5 dark:border-white/5 p-2 overflow-hidden">
				<div
					id="raka-turnstile-widget"
					class="cf-turnstile"
					data-sitekey="<?php echo esc_attr( $turnstile_sitekey ); ?>"
					data-theme="auto"
				></div>
			</div>

			<!-- Error Alert Message -->
			<div id="raka-tracker-error-msg" class="hidden p-3 rounded-2xl bg-red-500/10 border border-red-500/20 text-red-600 dark:text-red-400 text-xs flex items-center gap-2">
				<i id="raka-tracker-error-icon" class="ri-error-warning-line text-base shrink-0"></i>
				<span id="raka-tracker-error-text" class="leading-tight"></span>
			</div>

			<!-- Search Submit Button -->
			<button
				type="button"
				id="raka-tracker-submit-btn"
				class="w-full py-3 px-5 rounded-2xl bg-neutral-950 hover:bg-neutral-800 dark:bg-white dark:hover:bg-neutral-200 text-white dark:text-neutral-950 text-xs sm:text-sm font-bold font-hn tracking-wider uppercase flex items-center justify-center gap-2 shadow-lg transition-all duration-200 hover:scale-[1.01] active:scale-[0.99] cursor-pointer"
			>
				<i id="raka-tracker-btn-icon" class="ri-search-2-line text-sm"></i>
				<span id="raka-tracker-btn-text"><?php esc_html_e( 'ค้นหารายการพรีออเดอร์', 'raka-preorder-theme' ); ?></span>
			</button>
		</div>

		<!-- ── RESULT / DETAIL VIEW ── -->
		<div id="raka-tracker-result-view" class="hidden w-full space-y-4">
			
			<!-- Top Header: Order ID & Back Button -->
			<div id="raka-res-header-bar" class="flex items-center justify-between pb-2 border-b border-black/10 dark:border-white/10">
				<div id="raka-res-header-info">
					<span id="raka-res-order-no" class="text-xs font-mono font-bold text-neutral-900 dark:text-white uppercase tracking-wider block"></span>
					<h3 id="raka-res-product-title" class="text-sm font-bold font-hn text-neutral-900 dark:text-white truncate max-w-[200px] sm:max-w-xs"></h3>
				</div>
				<button
					type="button"
					id="raka-tracker-reset-btn"
					class="inline-flex items-center gap-1.5 text-xs text-neutral-500 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white font-medium transition-colors cursor-pointer"
				>
					<i id="raka-res-back-ico" class="ri-arrow-left-s-line text-base"></i>
					<span id="raka-res-back-txt"><?php esc_html_e( 'ค้นหาใหม่', 'raka-preorder-theme' ); ?></span>
				</button>
			</div>

			<!-- Product Details & Summary Card -->
			<div id="raka-res-product-card" class="p-3.5 rounded-2xl bg-neutral-100/90 dark:bg-neutral-800/80 border border-black/5 dark:border-white/10 flex gap-3.5 items-start">
				<div id="raka-res-img-wrap" class="w-14 h-14 sm:w-16 sm:h-16 rounded-xl bg-white dark:bg-neutral-900 overflow-hidden shrink-0 border border-black/5 dark:border-white/10 flex items-center justify-center">
					<img id="raka-res-img" src="" alt="Product" class="w-full h-full object-cover hidden" />
					<i id="raka-res-img-fallback" class="ri-shopping-bag-3-line text-2xl text-neutral-400"></i>
				</div>
				<div id="raka-res-details-col" class="flex-1 min-w-0 flex flex-col justify-between self-stretch">
					<div id="raka-res-title-row" class="flex items-start justify-between gap-2">
						<h4 id="raka-res-prod-name" class="text-xs font-bold leading-tight line-clamp-2"></h4>
						<span id="raka-res-total-price" class="text-xs sm:text-sm font-mono font-bold text-neutral-900 dark:text-white shrink-0"></span>
					</div>
					<div id="raka-res-meta-row" class="flex flex-wrap items-center gap-x-3 gap-y-1 text-[11px] text-neutral-500 dark:text-neutral-400 font-mono mt-1">
						<span id="raka-res-qty-wrap">จำนวน: <b id="raka-res-qty" class="text-neutral-800 dark:text-neutral-200">1</b></span>
						<span id="raka-res-ship-wrap">ค่าส่ง: <b id="raka-res-shipping" class="text-neutral-800 dark:text-neutral-200">฿0</b></span>
						<span id="raka-res-eta-wrap" class="hidden text-amber-600 dark:text-amber-400">
							<i id="raka-res-eta-ico" class="ri-time-line text-[10px] mr-0.5"></i>ETA: <b id="raka-res-eta"></b>
						</span>
					</div>
				</div>
			</div>

			<!-- Status & Delivery Badge Card -->
			<div id="raka-res-status-grid" class="grid grid-cols-2 gap-2.5 p-3 rounded-2xl bg-neutral-100/70 dark:bg-neutral-800/60 border border-black/5 dark:border-white/10 text-xs">
				<div id="raka-res-cur-status-wrap">
					<span id="raka-res-cur-status-lbl" class="text-[10px] uppercase text-neutral-400 dark:text-neutral-500 font-mono block mb-0.5">
						<?php esc_html_e( 'สถานะปัจจุบัน', 'raka-preorder-theme' ); ?>
					</span>
					<span id="raka-res-status-badge" class="font-bold text-red-600 dark:text-red-400 leading-tight block truncate"></span>
				</div>
				<div id="raka-res-shipment-wrap">
					<span id="raka-res-shipment-lbl" class="text-[10px] uppercase text-neutral-400 dark:text-neutral-500 font-mono block mb-0.5">
						<?php esc_html_e( 'พัสดุจัดส่ง', 'raka-preorder-theme' ); ?>
					</span>
					<div id="raka-res-tracking-row" class="flex items-center gap-1.5">
						<span id="raka-res-tracking-no" class="font-mono font-semibold text-neutral-900 dark:text-white truncate"></span>
						<a id="raka-res-courier-link" href="#" target="_blank" rel="noopener noreferrer" class="hidden text-red-600 dark:text-red-400 hover:opacity-75 text-xs">
							<i id="raka-res-courier-ico" class="ri-external-link-line"></i>
						</a>
					</div>
				</div>
			</div>

			<!-- Shipping Address (If available) -->
			<div id="raka-res-address-box" class="hidden p-3 rounded-2xl bg-neutral-100/50 dark:bg-neutral-800/40 border border-black/5 dark:border-white/10 text-xs space-y-1">
				<div id="raka-res-addr-header" class="flex items-center gap-1.5 text-[10px] uppercase font-bold text-neutral-500 dark:text-neutral-400 font-hn">
					<i id="raka-res-addr-ico" class="ri-map-pin-line text-xs"></i>
					<span id="raka-res-addr-lbl"><?php esc_html_e( 'ที่อยู่จัดส่งสินค้า', 'raka-preorder-theme' ); ?></span>
				</div>
				<p id="raka-res-address-text" class="text-[11px] leading-relaxed text-neutral-700 dark:text-neutral-300 font-sans"></p>
			</div>

			<!-- Interactive Step Timeline -->
			<div id="raka-res-timeline-wrap" class="space-y-2 max-h-[220px] overflow-y-auto pr-1">
				<span id="raka-res-timeline-lbl" class="text-[10px] uppercase tracking-wider text-neutral-400 dark:text-neutral-500 font-hn block">
					<?php esc_html_e( 'ไทม์ไลน์ความคืบหน้า (Timeline)', 'raka-preorder-theme' ); ?>
				</span>
				<div id="raka-res-timeline" class="space-y-2.5 border-l-2 border-neutral-200 dark:border-neutral-700/70 ml-2 pl-3 py-1">
					<!-- Dynamically injected via JS -->
				</div>
			</div>

		</div>

	</div>

	<script>
		(function initPreorderTrackerV2() {
			const container = document.getElementById('raka-tracker-v2');
			if (!container) return;

			let currentMode = 'order'; // 'order' or 'phone'
			let turnstileWidgetId = null;
			let turnstileToken = '';

			const tabBtnOrder   = document.getElementById('raka-tab-btn-order');
			const tabBtnPhone   = document.getElementById('raka-tab-btn-phone');

			const searchView    = document.getElementById('raka-tracker-search-view');
			const resultView    = document.getElementById('raka-tracker-result-view');
			
			const primaryLabel  = document.getElementById('raka-track-primary-label');
			const primaryIcon   = document.getElementById('raka-track-primary-icon');
			const primaryInput  = document.getElementById('raka-track-primary-input');

			const submitBtn     = document.getElementById('raka-tracker-submit-btn');
			const btnIcon       = document.getElementById('raka-tracker-btn-icon');
			const btnText       = document.getElementById('raka-tracker-btn-text');
			const errorBox      = document.getElementById('raka-tracker-error-msg');
			const errorText     = document.getElementById('raka-tracker-error-text');
			const resetBtn      = document.getElementById('raka-tracker-reset-btn');

			const resOrderNo    = document.getElementById('raka-res-order-no');
			const resTitle      = document.getElementById('raka-res-product-title');
			const resStatus     = document.getElementById('raka-res-status-badge');
			const resTrackNo    = document.getElementById('raka-res-tracking-no');
			const resCourierLnk = document.getElementById('raka-res-courier-link');
			const resTimeline   = document.getElementById('raka-res-timeline');

			// Product details elements
			const resImg        = document.getElementById('raka-res-img');
			const resImgFallback= document.getElementById('raka-res-img-fallback');
			const resProdName   = document.getElementById('raka-res-prod-name');
			const resTotalPrice = document.getElementById('raka-res-total-price');
			const resQty        = document.getElementById('raka-res-qty');
			const resShipping   = document.getElementById('raka-res-shipping');
			const resEtaWrap    = document.getElementById('raka-res-eta-wrap');
			const resEta        = document.getElementById('raka-res-eta');
			const resAddressBox = document.getElementById('raka-res-address-box');
			const resAddressTxt = document.getElementById('raka-res-address-text');

			const activeTabClass = 'bg-white dark:bg-neutral-900 text-neutral-950 dark:text-white shadow-sm border border-black/5 dark:border-white/10';
			const inactiveTabClass = 'text-neutral-500 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white';

			// Initialize Turnstile Explicitly
			function renderTurnstile() {
				if (window.turnstile && document.getElementById('raka-turnstile-widget')) {
					try {
						if (turnstileWidgetId !== null) {
							window.turnstile.reset(turnstileWidgetId);
						} else {
							turnstileWidgetId = window.turnstile.render('#raka-turnstile-widget', {
								sitekey: '<?php echo esc_js( $turnstile_sitekey ); ?>',
								theme: 'auto',
								callback: function(token) {
									turnstileToken = token;
									hideError();
								},
								'expired-callback': function() {
									turnstileToken = '';
								},
								'error-callback': function() {
									turnstileToken = '';
								}
							});
						}
					} catch (e) {
						console.log('Turnstile load init:', e);
					}
				}
			}

			if (window.turnstile) {
				renderTurnstile();
			} else {
				const checkTurnstile = setInterval(() => {
					if (window.turnstile) {
						clearInterval(checkTurnstile);
						renderTurnstile();
					}
				}, 200);
			}

			function switchMode(mode) {
				currentMode = mode;
				hideError();

				if (mode === 'order') {
					tabBtnOrder.className = 'py-2.5 px-3 rounded-xl text-xs font-bold font-hn transition-all duration-200 flex items-center justify-center gap-1.5 cursor-pointer ' + activeTabClass;
					tabBtnPhone.className = 'py-2.5 px-3 rounded-xl text-xs font-bold font-hn transition-all duration-200 flex items-center justify-center gap-1.5 cursor-pointer ' + inactiveTabClass;

					primaryLabel.textContent = 'ORDER NUMBER / หมายเลขคำสั่งซื้อ';
					primaryIcon.className = 'ri-hashtag text-base text-neutral-400 dark:text-neutral-500 absolute left-3.5 pointer-events-none';
					primaryInput.placeholder = 'เช่น RAKA-PRE-312691';
					primaryInput.type = 'text';
				} else {
					tabBtnOrder.className = 'py-2.5 px-3 rounded-xl text-xs font-bold font-hn transition-all duration-200 flex items-center justify-center gap-1.5 cursor-pointer ' + inactiveTabClass;
					tabBtnPhone.className = 'py-2.5 px-3 rounded-xl text-xs font-bold font-hn transition-all duration-200 flex items-center justify-center gap-1.5 cursor-pointer ' + activeTabClass;

					primaryLabel.textContent = 'PHONE NUMBER / เบอร์โทรศัพท์';
					primaryIcon.className = 'ri-phone-line text-base text-neutral-400 dark:text-neutral-500 absolute left-3.5 pointer-events-none';
					primaryInput.placeholder = 'เช่น 081-234-5678';
					primaryInput.type = 'tel';
				}

				primaryInput.value = '';
				primaryInput.focus();
			}

			tabBtnOrder.addEventListener('click', () => switchMode('order'));
			tabBtnPhone.addEventListener('click', () => switchMode('phone'));

			function showError(msg) {
				errorText.textContent = msg || 'ไม่พบข้อมูลการพรีออเดอร์ กรุณาตรวจสอบข้อมูลอีกครั้ง';
				errorBox.classList.remove('hidden');
			}

			function hideError() {
				errorBox.classList.add('hidden');
			}

			function setLoading(loading) {
				if (loading) {
					submitBtn.disabled = true;
					btnIcon.className = 'ri-loader-4-line text-sm animate-spin';
					btnText.textContent = 'กำลังตรวจสอบ...';
				} else {
					submitBtn.disabled = false;
					btnIcon.className = 'ri-search-2-line text-sm';
					btnText.textContent = 'ค้นหารายการพรีออเดอร์';
				}
			}

			function formatPrice(num) {
				const n = Number(num) || 0;
				return '฿' + n.toLocaleString('th-TH');
			}

			async function doSearch() {
				hideError();
				const primaryVal = (primaryInput.value || '').trim();

				if (!primaryVal) {
					if (currentMode === 'order') {
						showError('กรุณากรอกเลขคำสั่งซื้อ (Order Number)');
					} else {
						showError('กรุณากรอกเบอร์โทรศัพท์ (Phone Number)');
					}
					primaryInput.focus();
					return;
				}

				// Get Turnstile token from widget if available
				let activeToken = turnstileToken;
				if (!activeToken && window.turnstile && turnstileWidgetId !== null) {
					activeToken = window.turnstile.getResponse(turnstileWidgetId) || '';
				}

				setLoading(true);

				try {
					const params = new URLSearchParams();

					if (currentMode === 'order') {
						params.append('order_no', primaryVal);
					} else {
						params.append('phone', primaryVal);
					}

					if (activeToken) {
						params.append('cf_turnstile_response', activeToken);
					}

					const apiUrl = '/wp-json/preorder-raka/v1/status?' + params.toString();
					const res = await fetch(apiUrl, {
						headers: { 'Accept': 'application/json' }
					});

					const data = await res.json();

					if (!res.ok || !data || data.code || !data.success) {
						const errMsg = data && data.message ? data.message : 'ไม่พบข้อมูลคำสั่งซื้อนี้ กรุณาตรวจสอบข้อมูลอีกครั้ง';
						showError(errMsg);
						if (window.turnstile && turnstileWidgetId !== null) {
							window.turnstile.reset(turnstileWidgetId);
							turnstileToken = '';
						}
						setLoading(false);
						return;
					}

					renderResult(data.data);
				} catch (err) {
					showError('เกิดข้อผิดพลาดในการเชื่อมต่อ กรุณาลองใหม่อีกครั้ง');
				} finally {
					setLoading(false);
				}
			}

			function renderResult(item) {
				const orderNumber = item.order_no || 'RAKA-PREORDER';
				const productName = item.product_name || item.product || item.title || 'พรีออเดอร์สินค้า';

				resOrderNo.textContent = orderNumber;
				resTitle.textContent = productName;
				resProdName.textContent = productName;

				// Price & Qty
				const totalVal = item.total ? item.total : (Number(item.price || 0) * Number(item.qty || 1) + Number(item.shipping || 0));
				resTotalPrice.textContent = formatPrice(totalVal);
				resQty.textContent = item.qty || 1;
				resShipping.textContent = item.shipping ? formatPrice(item.shipping) : '฿0';

				// Thumbnail
				if (item.image_url) {
					resImg.src = item.image_url;
					resImg.classList.remove('hidden');
					resImgFallback.classList.add('hidden');
				} else {
					resImg.classList.add('hidden');
					resImgFallback.classList.remove('hidden');
				}

				// ETA
				if (item.estimate_days) {
					resEta.textContent = item.estimate_days;
					resEtaWrap.classList.remove('hidden');
				} else {
					resEtaWrap.classList.add('hidden');
				}

				// Address
				if (item.address) {
					resAddressTxt.textContent = item.address;
					resAddressBox.classList.remove('hidden');
				} else {
					resAddressBox.classList.add('hidden');
				}

				// Current Status & Tracking
				const curIdx = typeof item.current_step_idx === 'number' ? item.current_step_idx : 0;
				const steps = item.steps || [];
				const currentStep = steps[curIdx] || steps[0] || {};
				resStatus.textContent = currentStep.title || 'รับคำสั่งซื้อเรียบร้อย';

				const trackingNumber = item.tracking_no || item.tracking;
				if (trackingNumber) {
					resTrackNo.textContent = trackingNumber;
					if (item.tracking_link) {
						resCourierLnk.href = item.tracking_link;
						resCourierLnk.classList.remove('hidden');
					} else {
						resCourierLnk.classList.add('hidden');
					}
				} else {
					resTrackNo.textContent = 'เตรียมจัดส่ง';
					resCourierLnk.classList.add('hidden');
				}

				// Build Timeline
				resTimeline.innerHTML = '';
				steps.forEach((step, idx) => {
					const isDone = idx < curIdx;
					const isCurrent = idx === curIdx;

					const stepEl = document.createElement('div');
					stepEl.className = 'relative flex items-start gap-2.5 text-xs';

					const dot = document.createElement('div');
					dot.className = isDone 
						? 'w-3.5 h-3.5 rounded-full bg-emerald-500 text-white flex items-center justify-center shrink-0 -ml-[19px] mt-0.5 text-[9px]'
						: (isCurrent 
							? 'w-3.5 h-3.5 rounded-full bg-red-600 text-white ring-4 ring-red-500/20 flex items-center justify-center shrink-0 -ml-[19px] mt-0.5 text-[9px]' 
							: 'w-3 h-3 rounded-full bg-neutral-300 dark:bg-neutral-700 shrink-0 -ml-[18px] mt-1');
					
					if (isDone) dot.innerHTML = '<i class="ri-check-line"></i>';
					else if (isCurrent) dot.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-white animate-ping"></span>';

					const content = document.createElement('div');
					content.className = 'flex-1 min-w-0';

					const title = document.createElement('div');
					title.className = isCurrent 
						? 'font-bold text-neutral-950 dark:text-white leading-tight'
						: (isDone ? 'font-medium text-neutral-800 dark:text-neutral-200' : 'text-neutral-400 dark:text-neutral-600');
					title.textContent = step.title;

					if (step.date) {
						const dateSpan = document.createElement('span');
						dateSpan.className = 'block text-[10px] font-mono text-neutral-400 dark:text-neutral-500 mt-0.5';
						dateSpan.textContent = step.date;
						content.appendChild(dateSpan);
					}

					content.insertBefore(title, content.firstChild);
					stepEl.appendChild(dot);
					stepEl.appendChild(content);
					resTimeline.appendChild(stepEl);
				});

				searchView.classList.add('hidden');
				resultView.classList.remove('hidden');
			}

			submitBtn.addEventListener('click', doSearch);
			primaryInput.addEventListener('keydown', (e) => {
				if (e.key === 'Enter') doSearch();
			});

			resetBtn.addEventListener('click', () => {
				resultView.classList.add('hidden');
				searchView.classList.remove('hidden');
				primaryInput.focus();
				if (window.turnstile && turnstileWidgetId !== null) {
					window.turnstile.reset(turnstileWidgetId);
					turnstileToken = '';
				}
			});
		})();
	</script>
	<?php
	return ob_get_clean();
}
add_shortcode( 'preorder_raka_v2', 'raka_render_preorder_tracker_v2' );
