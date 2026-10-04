<?php
/**
 * Modern Pre-Order Tracker V2 Component with Cloudflare Turnstile CAPTCHA.
 *
 * Lightweight, native vanilla JS AJAX search, Tab Switcher, Phone List Mode, and Turnstile integration.
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

	<style>
		#raka-tracker-v2,
		#raka-tracker-v2 button,
		#raka-tracker-v2 input,
		#raka-tracker-v2 label,
		#raka-tracker-v2 span,
		#raka-tracker-v2 h3,
		#raka-tracker-v2 h4,
		#raka-tracker-v2 p {
			font-family: 'Helvetica Neue ME', 'Helvetica Neue', 'Prompt', 'Kanit', -apple-system, BlinkMacSystemFont, sans-serif;
		}
		#raka-tracker-v2 .font-mono {
			font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace !important;
		}
		#raka-track-primary-input {
			padding-left: 46px !important;
			padding-right: 16px !important;
		}
		.custom-tracker-scroll::-webkit-scrollbar {
			width: 5px;
		}
		.custom-tracker-scroll::-webkit-scrollbar-track {
			background: transparent;
		}
		.custom-tracker-scroll::-webkit-scrollbar-thumb {
			background: rgba(150, 150, 150, 0.35);
			border-radius: 9999px;
		}
		.custom-tracker-scroll::-webkit-scrollbar-thumb:hover {
			background: rgba(150, 150, 150, 0.65);
		}
	</style>

	<div id="raka-tracker-v2" class="w-full text-left font-hn select-none text-neutral-900 dark:text-white transition-all duration-300">
		
		<!-- ── 1. SEARCH / INPUT VIEW ── -->
		<div id="raka-tracker-search-view" class="w-full space-y-3.5">
			
			<!-- Mode Selector Tab Switcher -->
			<div id="raka-track-tab-wrap" class="grid grid-cols-2 p-1 rounded-2xl bg-neutral-100/90 dark:bg-neutral-800/80 border border-black/10 dark:border-white/10">
				<button
					type="button"
					id="raka-tab-btn-order"
					class="h-10 px-3 rounded-xl text-xs sm:text-sm font-semibold transition-all duration-200 flex items-center justify-center gap-1.5 cursor-pointer bg-white dark:bg-neutral-900 text-neutral-950 dark:text-white shadow-sm border border-black/5 dark:border-white/10"
					data-tab="order"
				>
					<i id="raka-tab-icon-order" class="ri-hashtag text-sm sm:text-base"></i>
					<span id="raka-tab-text-order"><?php esc_html_e( 'ค้นหาด้วยเลขออเดอร์', 'raka-preorder-theme' ); ?></span>
				</button>
				<button
					type="button"
					id="raka-tab-btn-phone"
					class="h-10 px-3 rounded-xl text-xs sm:text-sm font-semibold transition-all duration-200 flex items-center justify-center gap-1.5 cursor-pointer text-neutral-500 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white"
					data-tab="phone"
				>
					<i id="raka-tab-icon-phone" class="ri-phone-line text-sm sm:text-base"></i>
					<span id="raka-tab-text-phone"><?php esc_html_e( 'ค้นหาด้วยเบอร์โทร', 'raka-preorder-theme' ); ?></span>
				</button>
			</div>

			<!-- Primary Input Group -->
			<div id="raka-track-primary-group" class="space-y-1.5">
				<label id="raka-track-primary-label" for="raka-track-primary-input" class="block text-xs font-bold text-neutral-700 dark:text-neutral-300">
					<?php esc_html_e( 'ORDER NUMBER / หมายเลขคำสั่งซื้อ', 'raka-preorder-theme' ); ?>
				</label>
				<div id="raka-track-primary-wrap" class="relative flex items-center w-full">
					<i id="raka-track-primary-icon" class="ri-hashtag text-lg text-neutral-400 dark:text-neutral-500 absolute left-4 pointer-events-none z-10"></i>
					<input
						type="text"
						id="raka-track-primary-input"
						class="w-full h-12 pl-12 pr-4 rounded-2xl bg-neutral-100/90 dark:bg-neutral-800/80 border border-black/10 dark:border-white/10 text-sm sm:text-base font-mono placeholder:text-neutral-400 dark:placeholder:text-neutral-500 focus:outline-none focus:ring-2 focus:ring-red-600/50 dark:focus:ring-red-500/50 transition-all"
						style="padding-left: 46px !important; padding-right: 16px !important;"
						placeholder="เช่น RAKA-PRE-312691"
						autocomplete="off"
					/>
				</div>
			</div>

			<!-- Cloudflare Turnstile CAPTCHA Widget -->
			<div id="raka-turnstile-wrapper" class="flex flex-col items-center justify-center min-h-[65px] rounded-2xl bg-neutral-50/50 dark:bg-neutral-800/40 border border-black/5 dark:border-white/5 p-2 overflow-hidden">
				<div
					id="raka-turnstile-widget"
					class="cf-turnstile"
					data-sitekey="<?php echo esc_attr( $turnstile_sitekey ); ?>"
					data-theme="auto"
				></div>
			</div>

			<!-- Error Alert Message -->
			<div id="raka-tracker-error-msg" class="hidden p-3 rounded-2xl bg-red-500/10 border border-red-500/20 text-red-600 dark:text-red-400 text-xs sm:text-sm flex items-center gap-2.5">
				<i id="raka-tracker-error-icon" class="ri-error-warning-line text-lg shrink-0"></i>
				<span id="raka-tracker-error-text" class="leading-tight"></span>
			</div>

			<!-- Search Submit Button -->
			<button
				type="button"
				id="raka-tracker-submit-btn"
				class="w-full h-12 px-6 rounded-2xl bg-neutral-950 hover:bg-neutral-800 dark:bg-white dark:hover:bg-neutral-200 text-white dark:text-neutral-950 text-sm sm:text-base font-semibold flex items-center justify-center gap-2 shadow-md transition-all duration-200 hover:scale-[1.005] active:scale-[0.99] cursor-pointer"
			>
				<i id="raka-tracker-btn-icon" class="ri-search-2-line text-base sm:text-lg"></i>
				<span id="raka-tracker-btn-text"><?php esc_html_e( 'ค้นหารายการพรีออเดอร์', 'raka-preorder-theme' ); ?></span>
			</button>
		</div>

		<!-- ── 2. PHONE SEARCH MULTI-ITEM LIST VIEW ── -->
		<div id="raka-tracker-list-view" class="hidden w-full space-y-3">
			
			<!-- List Header -->
			<div id="raka-list-header-bar" class="flex items-center justify-between pb-2 border-b border-black/10 dark:border-white/10">
				<div id="raka-list-header-info" class="space-y-0.5">
					<span id="raka-list-phone-badge" class="text-xs font-mono font-bold text-red-600 dark:text-red-400 tracking-wide block"></span>
					<h3 id="raka-list-heading" class="text-base sm:text-lg font-bold text-neutral-900 dark:text-white">
						<?php esc_html_e( 'รายการพรีออเดอร์ของคุณ', 'raka-preorder-theme' ); ?>
					</h3>
				</div>
				<button
					type="button"
					id="raka-list-back-btn"
					class="inline-flex items-center gap-1 text-xs sm:text-sm text-neutral-500 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white font-medium transition-colors cursor-pointer py-1 px-2.5 rounded-xl hover:bg-black/5 dark:hover:bg-white/5"
				>
					<i id="raka-list-back-ico" class="ri-arrow-left-s-line text-base"></i>
					<span id="raka-list-back-txt"><?php esc_html_e( 'ค้นหาใหม่', 'raka-preorder-theme' ); ?></span>
				</button>
			</div>

			<!-- Dynamic List Container with custom scroll -->
			<div id="raka-tracker-list-container" class="space-y-3 max-h-[58vh] sm:max-h-[64vh] overflow-y-auto pr-1.5 custom-tracker-scroll">
				<!-- Dynamically populated via JS -->
			</div>

		</div>

		<!-- ── 3. SINGLE RESULT / DETAIL VIEW ── -->
		<div id="raka-tracker-result-view" class="hidden w-full space-y-3">
			
			<!-- Top Header: Order ID & Back Button -->
			<div id="raka-res-header-bar" class="flex items-center justify-between pb-2 border-b border-black/10 dark:border-white/10">
				<div id="raka-res-header-info">
					<span id="raka-res-order-no" class="text-xs sm:text-sm font-mono font-bold text-neutral-900 dark:text-white uppercase tracking-wider block"></span>
					<h3 id="raka-res-product-title" class="text-base sm:text-lg font-bold text-neutral-900 dark:text-white truncate max-w-[220px] sm:max-w-sm"></h3>
				</div>
				<button
					type="button"
					id="raka-tracker-reset-btn"
					class="inline-flex items-center gap-1 text-xs sm:text-sm text-neutral-500 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white font-medium transition-colors cursor-pointer py-1 px-2.5 rounded-xl hover:bg-black/5 dark:hover:bg-white/5"
				>
					<i id="raka-res-back-ico" class="ri-arrow-left-s-line text-base"></i>
					<span id="raka-res-back-txt"><?php esc_html_e( 'ค้นหาใหม่', 'raka-preorder-theme' ); ?></span>
				</button>
			</div>

			<!-- Scrollable Container for Result Body -->
			<div id="raka-res-scroll-body" class="space-y-3.5 max-h-[58vh] sm:max-h-[64vh] overflow-y-auto pr-1.5 custom-tracker-scroll">
				
				<!-- Product Details & Summary Card -->
				<div id="raka-res-product-card" class="p-4 rounded-2xl bg-neutral-100/90 dark:bg-neutral-800/80 border border-black/5 dark:border-white/10 flex gap-4 items-start">
					<div id="raka-res-img-wrap" class="w-16 h-16 sm:w-20 sm:h-20 rounded-xl bg-white dark:bg-neutral-900 overflow-hidden shrink-0 border border-black/5 dark:border-white/10 flex items-center justify-center">
						<img id="raka-res-img" src="" alt="Product" class="w-full h-full object-cover hidden" />
						<i id="raka-res-img-fallback" class="ri-shopping-bag-3-line text-2xl sm:text-3xl text-neutral-400"></i>
					</div>
					<div id="raka-res-details-col" class="flex-1 min-w-0 flex flex-col justify-between self-stretch">
						<div id="raka-res-title-row" class="flex items-start justify-between gap-2">
							<h4 id="raka-res-prod-name" class="text-sm sm:text-base font-bold leading-tight line-clamp-2"></h4>
							<span id="raka-res-total-price" class="text-base sm:text-lg font-mono font-bold text-neutral-900 dark:text-white shrink-0"></span>
						</div>
						<div id="raka-res-meta-row" class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 font-mono mt-1.5">
							<span id="raka-res-qty-wrap">จำนวน: <b id="raka-res-qty" class="text-neutral-900 dark:text-neutral-100">1</b></span>
							<span id="raka-res-ship-wrap">ค่าส่ง: <b id="raka-res-shipping" class="text-neutral-900 dark:text-neutral-100">฿0</b></span>
							<span id="raka-res-eta-wrap" class="hidden text-amber-600 dark:text-amber-400 font-semibold">
								<i id="raka-res-eta-ico" class="ri-time-line text-xs mr-0.5"></i>ETA: <b id="raka-res-eta"></b>
							</span>
						</div>
					</div>
				</div>

				<!-- Status & Delivery Badge Card -->
				<div id="raka-res-status-grid" class="grid grid-cols-2 gap-3 p-3.5 rounded-2xl bg-neutral-100/70 dark:bg-neutral-800/60 border border-black/5 dark:border-white/10 text-xs sm:text-sm">
					<div id="raka-res-cur-status-wrap">
						<span id="raka-res-cur-status-lbl" class="text-xs uppercase text-neutral-500 dark:text-neutral-400 font-mono block mb-1">
							<?php esc_html_e( 'สถานะปัจจุบัน', 'raka-preorder-theme' ); ?>
						</span>
						<span id="raka-res-status-badge" class="font-bold text-sm sm:text-base text-red-600 dark:text-red-400 leading-tight block"></span>
					</div>
					<div id="raka-res-shipment-wrap">
						<span id="raka-res-shipment-lbl" class="text-xs uppercase text-neutral-500 dark:text-neutral-400 font-mono block mb-1">
							<?php esc_html_e( 'พัสดุจัดส่ง', 'raka-preorder-theme' ); ?>
						</span>
						<div id="raka-res-tracking-row" class="flex items-center gap-1.5">
							<span id="raka-res-tracking-no" class="font-mono font-bold text-sm sm:text-base text-neutral-900 dark:text-white truncate"></span>
							<a id="raka-res-courier-link" href="#" target="_blank" rel="noopener noreferrer" class="hidden text-red-600 dark:text-red-400 hover:opacity-75 text-sm">
								<i id="raka-res-courier-ico" class="ri-external-link-line"></i>
							</a>
						</div>
					</div>
				</div>

				<!-- Shipping Address (If available) -->
				<div id="raka-res-address-box" class="hidden p-3.5 rounded-2xl bg-neutral-100/50 dark:bg-neutral-800/40 border border-black/5 dark:border-white/10 space-y-1">
					<div id="raka-res-addr-header" class="flex items-center gap-1.5 text-xs uppercase font-bold text-neutral-600 dark:text-neutral-400">
						<i id="raka-res-addr-ico" class="ri-map-pin-line text-sm"></i>
						<span id="raka-res-addr-lbl"><?php esc_html_e( 'ที่อยู่จัดส่งสินค้า', 'raka-preorder-theme' ); ?></span>
					</div>
					<p id="raka-res-address-text" class="text-xs sm:text-sm leading-relaxed text-neutral-800 dark:text-neutral-200 font-sans"></p>
				</div>

				<!-- Interactive Step Timeline -->
				<div id="raka-res-timeline-wrap" class="space-y-2.5 pt-1">
					<span id="raka-res-timeline-lbl" class="text-xs uppercase font-bold tracking-wider text-neutral-500 dark:text-neutral-400 block">
						<?php esc_html_e( 'ไทม์ไลน์ความคืบหน้า (Timeline)', 'raka-preorder-theme' ); ?>
					</span>
					<div id="raka-res-timeline" class="space-y-3.5 border-l-2 border-neutral-200 dark:border-neutral-700 ml-2.5 pl-4 py-1">
						<!-- Dynamically injected via JS -->
					</div>
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
			let currentPhoneItems = [];
			let navigatedFromList = false;

			const tabBtnOrder   = document.getElementById('raka-tab-btn-order');
			const tabBtnPhone   = document.getElementById('raka-tab-btn-phone');

			const searchView    = document.getElementById('raka-tracker-search-view');
			const listView      = document.getElementById('raka-tracker-list-view');
			const listContainer = document.getElementById('raka-tracker-list-container');
			const listPhoneBadge= document.getElementById('raka-list-phone-badge');
			const listBackBtn   = document.getElementById('raka-list-back-btn');

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
			const resetBtnText  = document.getElementById('raka-res-back-txt');

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

			const isTurnstileActive = <?php echo $turnstile_enabled ? 'true' : 'false'; ?>;

			// Initialize Turnstile Explicitly
			function renderTurnstile() {
				if (!isTurnstileActive) return;
				if (window.turnstile && document.getElementById('raka-turnstile-widget')) {
					try {
						if (turnstileWidgetId !== null) {
							window.turnstile.reset(turnstileWidgetId);
						} else {
							turnstileWidgetId = window.turnstile.render('#raka-turnstile-widget', {
								sitekey: '<?php echo esc_js( $turnstile_sitekey ); ?>',
								theme: '<?php echo esc_js( $turnstile_theme ); ?>',
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

			if (isTurnstileActive) {
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
			}

			function switchMode(mode) {
				currentMode = mode;
				hideError();

				if (mode === 'order') {
					tabBtnOrder.className = 'h-10 px-3 rounded-xl text-xs sm:text-sm font-semibold transition-all duration-200 flex items-center justify-center gap-1.5 cursor-pointer ' + activeTabClass;
					tabBtnPhone.className = 'h-10 px-3 rounded-xl text-xs sm:text-sm font-semibold transition-all duration-200 flex items-center justify-center gap-1.5 cursor-pointer ' + inactiveTabClass;

					primaryLabel.textContent = 'ORDER NUMBER / หมายเลขคำสั่งซื้อ';
					primaryIcon.className = 'ri-hashtag text-lg text-neutral-400 dark:text-neutral-500 absolute left-4 pointer-events-none';
					primaryInput.placeholder = 'เช่น RAKA-PRE-312691';
					primaryInput.type = 'text';
				} else {
					tabBtnOrder.className = 'h-10 px-3 rounded-xl text-xs sm:text-sm font-semibold transition-all duration-200 flex items-center justify-center gap-1.5 cursor-pointer ' + inactiveTabClass;
					tabBtnPhone.className = 'h-10 px-3 rounded-xl text-xs sm:text-sm font-semibold transition-all duration-200 flex items-center justify-center gap-1.5 cursor-pointer ' + activeTabClass;

					primaryLabel.textContent = 'PHONE NUMBER / เบอร์โทรศัพท์';
					primaryIcon.className = 'ri-phone-line text-lg text-neutral-400 dark:text-neutral-500 absolute left-4 pointer-events-none';
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
					btnIcon.className = 'ri-loader-4-line text-base sm:text-lg animate-spin';
					btnText.textContent = 'กำลังตรวจสอบ...';
				} else {
					submitBtn.disabled = false;
					btnIcon.className = 'ri-search-2-line text-base sm:text-lg';
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

				if (isTurnstileActive && !activeToken) {
					showError('กรุณากดติ๊กถูกในช่อง "Verify you are human" ก่อนค้นหาครับ');
					return;
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

					// Route based on mode and result structure
					if (currentMode === 'phone' && data.mode === 'list' && Array.isArray(data.items)) {
						currentPhoneItems = data.items;
						renderListView(data.items, data.phone || primaryVal);
					} else {
						navigatedFromList = false;
						renderResult(data.data || (data.items && data.items[0]));
					}
				} catch (err) {
					showError('เกิดข้อผิดพลาดในการเชื่อมต่อ กรุณาลองใหม่อีกครั้ง');
				} finally {
					setLoading(false);
				}
			}

			// Render Phone Multi-Item List View
			function renderListView(items, phoneStr) {
				listPhoneBadge.textContent = 'เบอร์โทรศัพท์: ' + phoneStr;
				listContainer.innerHTML = '';

				if (!items || items.length === 0) {
					showError('ไม่พบรายการพรีออเดอร์สำหรับเบอร์โทรนี้');
					return;
				}

				items.forEach((item, index) => {
					const card = document.createElement('div');
					card.id = 'raka-list-item-' + index;
					card.className = 'group p-4 rounded-2xl bg-neutral-100/90 dark:bg-neutral-800/80 hover:bg-neutral-200/90 dark:hover:bg-neutral-700/80 border border-black/5 dark:border-white/10 transition-all duration-200 cursor-pointer space-y-2.5 shadow-sm hover:shadow-md';
					
					const totalVal = item.total ? item.total : (Number(item.price || 0) * Number(item.qty || 1) + Number(item.shipping || 0));
					const orderNumber = item.order_no || ('RAKA-PRE-' + item.id);
					const productName = item.product_name || 'พรีออเดอร์สินค้า';
					const statusText = item.current_status || 'รับคำสั่งซื้อเรียบร้อย';

					card.innerHTML = `
						<div id="raka-list-card-top-${index}" class="flex items-center justify-between gap-2 border-b border-black/5 dark:border-white/5 pb-2">
							<span id="raka-list-card-orderno-${index}" class="text-xs sm:text-sm font-mono font-bold text-neutral-900 dark:text-white uppercase tracking-wider">
								${orderNumber}
							</span>
							<span id="raka-list-card-date-${index}" class="text-xs font-mono text-neutral-500 dark:text-neutral-400">
								${item.created_at || ''}
							</span>
						</div>
						<div id="raka-list-card-body-${index}" class="flex items-center gap-3.5">
							<div id="raka-list-card-imgwrap-${index}" class="w-14 h-14 rounded-xl bg-white dark:bg-neutral-900 overflow-hidden shrink-0 border border-black/5 dark:border-white/10 flex items-center justify-center">
								${item.image_url ? `<img id="raka-list-card-img-${index}" src="${item.image_url}" alt="Product" class="w-full h-full object-cover" />` : `<i id="raka-list-card-fallback-${index}" class="ri-shopping-bag-3-line text-2xl text-neutral-400"></i>`}
							</div>
							<div id="raka-list-card-info-${index}" class="flex-1 min-w-0">
								<h4 id="raka-list-card-name-${index}" class="text-sm sm:text-base font-bold text-neutral-900 dark:text-white line-clamp-1 leading-tight group-hover:text-red-600 dark:group-hover:text-red-400 transition-colors">
									${productName}
								</h4>
								<div id="raka-list-card-meta-${index}" class="flex items-center gap-2 mt-1">
									<span id="raka-list-card-price-${index}" class="text-sm sm:text-base font-mono font-bold text-neutral-900 dark:text-white">
										${formatPrice(totalVal)}
									</span>
									<span id="raka-list-card-qty-${index}" class="text-xs font-mono text-neutral-500 dark:text-neutral-400">
										(${item.qty || 1} ชิ้น)
									</span>
								</div>
							</div>
							<i id="raka-list-card-arrow-${index}" class="ri-arrow-right-s-line text-2xl text-neutral-400 group-hover:text-neutral-900 dark:group-hover:text-white group-hover:translate-x-0.5 transition-all"></i>
						</div>
						<div id="raka-list-card-footer-${index}" class="pt-1 flex items-center justify-between text-xs sm:text-sm">
							<span id="raka-list-card-status-${index}" class="inline-flex items-center gap-1.5 font-semibold text-red-600 dark:text-red-400">
								<span id="raka-list-dot-${index}" class="w-2 h-2 rounded-full bg-red-600 animate-pulse"></span>
								${statusText}
							</span>
							<span id="raka-list-card-action-${index}" class="text-xs font-medium text-neutral-500 dark:text-neutral-400 group-hover:underline">
								ดูสถานะ & ไทม์ไลน์
							</span>
						</div>
					`;

					card.addEventListener('click', () => {
						navigatedFromList = true;
						renderResult(item);
					});

					listContainer.appendChild(card);
				});

				searchView.classList.add('hidden');
				resultView.classList.add('hidden');
				listView.classList.remove('hidden');
			}

			// Render Single Detail View
			function renderResult(item) {
				const orderNumber = item.order_no || 'RAKA-PREORDER';
				const productName = item.product_name || item.product || item.title || 'พรีออเดอร์สินค้า';

				resOrderNo.textContent = orderNumber;
				resTitle.textContent = productName;
				resProdName.textContent = productName;

				// Back button text adapts whether navigated from list or direct search
				if (navigatedFromList) {
					resetBtnText.textContent = 'กลับหน้ารายการ';
				} else {
					resetBtnText.textContent = 'ค้นหาใหม่';
				}

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
				resStatus.textContent = currentStep.title || item.current_status || 'รับคำสั่งซื้อเรียบร้อย';

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

				// Render Timeline
				resTimeline.innerHTML = '';
				steps.forEach((step, idx) => {
					const isPassed = idx < curIdx;
					const isCurrent = idx === curIdx;

					const stepEl = document.createElement('div');
					stepEl.id = 'raka-step-' + idx;
					stepEl.className = 'relative flex items-start gap-3 text-xs sm:text-sm';

					// Dot
					const dot = document.createElement('div');
					dot.id = 'raka-step-dot-' + idx;
					dot.className = 'w-5 h-5 rounded-full flex items-center justify-center -ml-[23px] shrink-0 border-2 ' +
						(isPassed ? 'bg-emerald-600 border-emerald-600 text-white' :
						(isCurrent ? 'bg-red-600 border-red-600 text-white ring-4 ring-red-500/20' :
						'bg-white dark:bg-neutral-800 border-neutral-300 dark:border-neutral-600 text-transparent'));

					if (isPassed) {
						dot.innerHTML = '<i class="ri-check-line text-xs font-bold"></i>';
					} else if (isCurrent) {
						dot.innerHTML = '<div class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></div>';
					}

					// Content
					const content = document.createElement('div');
					content.id = 'raka-step-content-' + idx;
					content.className = 'flex-1 min-w-0';

					const title = document.createElement('div');
					title.id = 'raka-step-title-' + idx;
					title.className = isCurrent ? 'font-bold text-neutral-900 dark:text-white text-sm sm:text-base' :
						(isPassed ? 'font-semibold text-neutral-700 dark:text-neutral-300' : 'text-neutral-400 dark:text-neutral-500');
					title.textContent = step.title;

					if (step.date) {
						const dateSpan = document.createElement('span');
						dateSpan.id = 'raka-step-date-' + idx;
						dateSpan.className = 'block text-xs font-mono text-neutral-400 dark:text-neutral-500 mt-0.5';
						dateSpan.textContent = step.date;
						content.appendChild(dateSpan);
					}

					content.insertBefore(title, content.firstChild);
					stepEl.appendChild(dot);
					stepEl.appendChild(content);
					resTimeline.appendChild(stepEl);
				});

				searchView.classList.add('hidden');
				listView.classList.add('hidden');
				resultView.classList.remove('hidden');
			}

			submitBtn.addEventListener('click', doSearch);
			primaryInput.addEventListener('keydown', (e) => {
				if (e.key === 'Enter') doSearch();
			});

			// Back button in list view returns to search
			listBackBtn.addEventListener('click', () => {
				listView.classList.add('hidden');
				searchView.classList.remove('hidden');
				primaryInput.focus();
				if (window.turnstile && turnstileWidgetId !== null) {
					window.turnstile.reset(turnstileWidgetId);
					turnstileToken = '';
				}
			});

			// Back button in detail view returns to list (if from list) or to search
			resetBtn.addEventListener('click', () => {
				if (navigatedFromList && currentPhoneItems.length > 0) {
					resultView.classList.add('hidden');
					listView.classList.remove('hidden');
				} else {
					resultView.classList.add('hidden');
					searchView.classList.remove('hidden');
					primaryInput.focus();
					if (window.turnstile && turnstileWidgetId !== null) {
						window.turnstile.reset(turnstileWidgetId);
						turnstileToken = '';
					}
				}
			});
		})();
	</script>
	<?php
	return ob_get_clean();
}
add_shortcode( 'preorder_raka_v2', 'raka_render_preorder_tracker_v2' );
