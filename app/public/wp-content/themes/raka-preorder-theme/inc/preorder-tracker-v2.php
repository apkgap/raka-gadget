<?php
/**
 * Modern Pre-Order Tracker V2 Component.
 *
 * Lightweight, native vanilla JS AJAX search and timeline renderer.
 * Fully compatible with /wp-json/preorder-raka/v1/status.
 *
 * @package raka-preorder-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function raka_render_preorder_tracker_v2(): string {
	ob_start();
	?>
	<div id="raka-tracker-v2" class="w-full text-left font-sans select-none text-neutral-900 dark:text-white transition-all duration-300">
		
		<!-- ── SEARCH / INPUT VIEW ── -->
		<div id="raka-tracker-search-view" class="w-full space-y-3.5">
			<div class="space-y-1">
				<label for="raka-track-order-no" class="block text-[11px] uppercase tracking-wider font-bold text-neutral-600 dark:text-neutral-400 font-hn">
					<?php esc_html_e( 'Order Number / หมายเลขคำสั่งซื้อ', 'raka-preorder-theme' ); ?>
				</label>
				<div class="relative flex items-center">
					<i class="ri-hashtag text-base text-neutral-400 dark:text-neutral-500 absolute left-3.5 pointer-events-none"></i>
					<input
						type="text"
						id="raka-track-order-no"
						class="w-full pl-10 pr-4 py-2.5 rounded-2xl bg-neutral-100/90 dark:bg-neutral-800/80 border border-black/10 dark:border-white/10 text-xs sm:text-sm font-mono placeholder:text-neutral-400 dark:placeholder:text-neutral-500 focus:outline-none focus:ring-2 focus:ring-red-600 dark:focus:ring-red-500 transition-all"
						placeholder="เช่น RAKA-PRE-312691"
						autocomplete="off"
					/>
				</div>
			</div>

			<div class="space-y-1">
				<div class="flex items-center justify-between">
					<label for="raka-track-phone" class="block text-[11px] uppercase tracking-wider font-bold text-neutral-600 dark:text-neutral-400 font-hn">
						<?php esc_html_e( 'Verification / ยืนยันตัวตน', 'raka-preorder-theme' ); ?>
					</label>
					<span class="text-[11px] text-neutral-400 dark:text-neutral-500 font-normal">
						<?php esc_html_e( 'เบอร์โทร หรือ บัตร ปชช. 4 ตัวท้าย', 'raka-preorder-theme' ); ?>
					</span>
				</div>
				<div class="relative flex items-center">
					<i class="ri-shield-check-line text-base text-neutral-400 dark:text-neutral-500 absolute left-3.5 pointer-events-none"></i>
					<input
						type="text"
						id="raka-track-phone"
						class="w-full pl-10 pr-4 py-2.5 rounded-2xl bg-neutral-100/90 dark:bg-neutral-800/80 border border-black/10 dark:border-white/10 text-xs sm:text-sm font-mono placeholder:text-neutral-400 dark:placeholder:text-neutral-500 focus:outline-none focus:ring-2 focus:ring-red-600 dark:focus:ring-red-500 transition-all"
						placeholder="กรอกเบอร์โทร หรือ เลขท้าย 4 หลัก"
						autocomplete="off"
					/>
				</div>
			</div>

			<!-- Error Alert Message -->
			<div id="raka-tracker-error-msg" class="hidden p-3 rounded-2xl bg-red-500/10 border border-red-500/20 text-red-600 dark:text-red-400 text-xs flex items-center gap-2">
				<i class="ri-error-warning-line text-base shrink-0"></i>
				<span id="raka-tracker-error-text" class="leading-tight"></span>
			</div>

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
			<div class="flex items-center justify-between pb-2 border-b border-black/10 dark:border-white/10">
				<div>
					<span id="raka-res-order-no" class="text-xs font-mono font-bold text-neutral-900 dark:text-white uppercase tracking-wider block"></span>
					<h3 id="raka-res-product-title" class="text-sm font-bold font-hn text-neutral-900 dark:text-white truncate max-w-[200px] sm:max-w-xs"></h3>
				</div>
				<button
					type="button"
					id="raka-tracker-reset-btn"
					class="inline-flex items-center gap-1.5 text-xs text-neutral-500 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white font-medium transition-colors cursor-pointer"
				>
					<i class="ri-arrow-left-s-line text-base"></i>
					<span><?php esc_html_e( 'ค้นหาใหม่', 'raka-preorder-theme' ); ?></span>
				</button>
			</div>

			<!-- Product Details & Summary Card -->
			<div id="raka-res-product-card" class="p-3.5 rounded-2xl bg-neutral-100/90 dark:bg-neutral-800/80 border border-black/5 dark:border-white/10 flex gap-3.5 items-start">
				<div id="raka-res-img-wrap" class="w-14 h-14 sm:w-16 sm:h-16 rounded-xl bg-white dark:bg-neutral-900 overflow-hidden shrink-0 border border-black/5 dark:border-white/10 flex items-center justify-center">
					<img id="raka-res-img" src="" alt="Product" class="w-full h-full object-cover hidden" />
					<i id="raka-res-img-fallback" class="ri-shopping-bag-3-line text-2xl text-neutral-400"></i>
				</div>
				<div class="flex-1 min-w-0 flex flex-col justify-between self-stretch">
					<div class="flex items-start justify-between gap-2">
						<h4 id="raka-res-prod-name" class="text-xs font-bold leading-tight line-clamp-2"></h4>
						<span id="raka-res-total-price" class="text-xs sm:text-sm font-mono font-bold text-neutral-900 dark:text-white shrink-0"></span>
					</div>
					<div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-[11px] text-neutral-500 dark:text-neutral-400 font-mono mt-1">
						<span>จำนวน: <b id="raka-res-qty" class="text-neutral-800 dark:text-neutral-200">1</b></span>
						<span>ค่าส่ง: <b id="raka-res-shipping" class="text-neutral-800 dark:text-neutral-200">฿0</b></span>
						<span id="raka-res-eta-wrap" class="hidden text-amber-600 dark:text-amber-400">
							<i class="ri-time-line text-[10px] mr-0.5"></i>ETA: <b id="raka-res-eta"></b>
						</span>
					</div>
				</div>
			</div>

			<!-- Status & Delivery Badge Card -->
			<div class="grid grid-cols-2 gap-2.5 p-3 rounded-2xl bg-neutral-100/70 dark:bg-neutral-800/60 border border-black/5 dark:border-white/10 text-xs">
				<div>
					<span class="text-[10px] uppercase text-neutral-400 dark:text-neutral-500 font-mono block mb-0.5">
						<?php esc_html_e( 'สถานะปัจจุบัน', 'raka-preorder-theme' ); ?>
					</span>
					<span id="raka-res-status-badge" class="font-bold text-red-600 dark:text-red-400 leading-tight block truncate"></span>
				</div>
				<div>
					<span class="text-[10px] uppercase text-neutral-400 dark:text-neutral-500 font-mono block mb-0.5">
						<?php esc_html_e( 'พัสดุจัดส่ง', 'raka-preorder-theme' ); ?>
					</span>
					<div class="flex items-center gap-1.5">
						<span id="raka-res-tracking-no" class="font-mono font-semibold text-neutral-900 dark:text-white truncate"></span>
						<a id="raka-res-courier-link" href="#" target="_blank" rel="noopener noreferrer" class="hidden text-red-600 dark:text-red-400 hover:opacity-75 text-xs">
							<i class="ri-external-link-line"></i>
						</a>
					</div>
				</div>
			</div>

			<!-- Shipping Address & Contact Info (If available) -->
			<div id="raka-res-address-box" class="hidden p-3 rounded-2xl bg-neutral-100/50 dark:bg-neutral-800/40 border border-black/5 dark:border-white/10 text-xs space-y-1">
				<div class="flex items-center gap-1.5 text-[10px] uppercase font-bold text-neutral-500 dark:text-neutral-400 font-hn">
					<i class="ri-map-pin-line text-xs"></i>
					<span><?php esc_html_e( 'ที่อยู่จัดส่งสินค้า', 'raka-preorder-theme' ); ?></span>
				</div>
				<p id="raka-res-address-text" class="text-[11px] leading-relaxed text-neutral-700 dark:text-neutral-300 font-sans"></p>
			</div>

			<!-- Interactive Step Timeline -->
			<div class="space-y-2 max-h-[220px] overflow-y-auto pr-1">
				<span class="text-[10px] uppercase tracking-wider text-neutral-400 dark:text-neutral-500 font-hn block">
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

			const searchView   = document.getElementById('raka-tracker-search-view');
			const resultView   = document.getElementById('raka-tracker-result-view');
			const orderInput   = document.getElementById('raka-track-order-no');
			const phoneInput   = document.getElementById('raka-track-phone');
			const submitBtn    = document.getElementById('raka-tracker-submit-btn');
			const btnIcon      = document.getElementById('raka-tracker-btn-icon');
			const btnText      = document.getElementById('raka-tracker-btn-text');
			const errorBox     = document.getElementById('raka-tracker-error-msg');
			const errorText    = document.getElementById('raka-tracker-error-text');
			const resetBtn     = document.getElementById('raka-tracker-reset-btn');

			const resOrderNo   = document.getElementById('raka-res-order-no');
			const resTitle     = document.getElementById('raka-res-product-title');
			const resStatus    = document.getElementById('raka-res-status-badge');
			const resTrackNo   = document.getElementById('raka-res-tracking-no');
			const resCourierLnk= document.getElementById('raka-res-courier-link');
			const resTimeline  = document.getElementById('raka-res-timeline');

			// Product details elements
			const resImg       = document.getElementById('raka-res-img');
			const resImgFallback = document.getElementById('raka-res-img-fallback');
			const resProdName  = document.getElementById('raka-res-prod-name');
			const resTotalPrice= document.getElementById('raka-res-total-price');
			const resQty       = document.getElementById('raka-res-qty');
			const resShipping  = document.getElementById('raka-res-shipping');
			const resEtaWrap   = document.getElementById('raka-res-eta-wrap');
			const resEta       = document.getElementById('raka-res-eta');
			const resAddressBox= document.getElementById('raka-res-address-box');
			const resAddressTxt= document.getElementById('raka-res-address-text');

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
					btnText.textContent = 'กำลังค้นหา...';
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
				const orderNo = (orderInput.value || '').trim();
				const phone   = (phoneInput.value || '').trim();

				if (!orderNo) {
					showError('กรุณากรอกเลขคำสั่งซื้อ (Order Number)');
					orderInput.focus();
					return;
				}

				setLoading(true);

				try {
					const params = new URLSearchParams();
					params.append('order_no', orderNo);
					if (phone) {
						params.append('phone', phone);
						params.append('citizen_id', phone);
					}

					const apiUrl = '/wp-json/preorder-raka/v1/status?' + params.toString();
					const res = await fetch(apiUrl, {
						headers: { 'Accept': 'application/json' }
					});

					const data = await res.json();

					if (!res.ok || !data || data.code || !data.success) {
						const errCode = data && data.code ? data.code : '';
						let msg = 'ไม่พบข้อมูลคำสั่งซื้อนี้ กรุณาตรวจสอบเลขคำสั่งซื้ออีกครั้ง';
						if (errCode === 'verification_required') {
							msg = 'กรุณากรอกเบอร์โทรศัพท์ หรือเลขบัตรประชาชน 4 ตัวท้าย เพื่อยืนยันตัวตน';
							phoneInput.focus();
						}
						showError(data && data.message ? data.message : msg);
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
			orderInput.addEventListener('keydown', (e) => {
				if (e.key === 'Enter') doSearch();
			});
			phoneInput.addEventListener('keydown', (e) => {
				if (e.key === 'Enter') doSearch();
			});

			resetBtn.addEventListener('click', () => {
				resultView.classList.add('hidden');
				searchView.classList.remove('hidden');
				orderInput.focus();
			});
		})();
	</script>
	<?php
	return ob_get_clean();
}
add_shortcode( 'preorder_raka_v2', 'raka_render_preorder_tracker_v2' );
