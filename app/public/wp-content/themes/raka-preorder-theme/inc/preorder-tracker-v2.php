<?php
/**
 * Pre-Order Tracker V2 (Mindora Aesthetic)
 * High-performance, lightweight AJAX pre-order tracking interface tailored for the Mindora layout.
 *
 * @package raka-preorder-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render Pre-Order Tracker V2
 */
function raka_render_preorder_tracker_v2() {
	ob_start();
	?>
	<div id="raka-tracker-v2" class="raka-tracker-v2-container w-full transition-all duration-300">
		
		<!-- ── SEARCH FORM VIEW ── -->
		<div id="raka-tracker-search-view" class="w-full space-y-3.5">
			
			<!-- Input 1: Order Number -->
			<div class="raka-input-group relative text-left">
				<label for="raka-track-order-no" class="block text-[11px] font-semibold tracking-wider uppercase text-neutral-500 dark:text-neutral-400 mb-1.5 font-hn">
					<i class="ri-hashtag text-xs text-red-500 mr-1"></i>
					<?php esc_html_e( 'เลขคำสั่งซื้อ (Order Number)', 'raka-preorder-theme' ); ?>
				</label>
				<div class="relative flex items-center">
					<input
						type="text"
						id="raka-track-order-no"
						name="order_no"
						placeholder="<?php esc_attr_e( 'เช่น RAKA-PRE-7777', 'raka-preorder-theme' ); ?>"
						class="w-full h-11 sm:h-12 px-4 rounded-xl bg-neutral-100/90 dark:bg-neutral-800/80 border border-neutral-200/80 dark:border-neutral-700/60 text-neutral-900 dark:text-white placeholder-neutral-400 dark:placeholder-neutral-500 text-xs sm:text-sm font-medium focus:outline-none focus:ring-2 focus:ring-neutral-900 dark:focus:ring-white focus:bg-white dark:focus:bg-neutral-800 transition-all font-mono"
						autocomplete="off"
						required
					/>
				</div>
			</div>

			<!-- Input 2: Phone Number / Verification -->
			<div class="raka-input-group relative text-left">
				<label for="raka-track-phone" class="block text-[11px] font-semibold tracking-wider uppercase text-neutral-500 dark:text-neutral-400 mb-1.5 font-hn">
					<i class="ri-phone-line text-xs text-neutral-400 mr-1"></i>
					<?php esc_html_e( 'เบอร์โทรศัพท์ (Phone Number)', 'raka-preorder-theme' ); ?>
				</label>
				<div class="relative flex items-center">
					<input
						type="tel"
						id="raka-track-phone"
						name="phone"
						placeholder="<?php esc_attr_e( 'เช่น 0897777777 หรือ 4 ตัวท้าย', 'raka-preorder-theme' ); ?>"
						class="w-full h-11 sm:h-12 px-4 rounded-xl bg-neutral-100/90 dark:bg-neutral-800/80 border border-neutral-200/80 dark:border-neutral-700/60 text-neutral-900 dark:text-white placeholder-neutral-400 dark:placeholder-neutral-500 text-xs sm:text-sm font-medium focus:outline-none focus:ring-2 focus:ring-neutral-900 dark:focus:ring-white focus:bg-white dark:focus:bg-neutral-800 transition-all font-mono"
						autocomplete="off"
					/>
				</div>
			</div>

			<!-- Error Alert Message -->
			<div id="raka-tracker-error-msg" class="hidden p-3 rounded-xl bg-red-500/10 border border-red-500/20 text-red-600 dark:text-red-400 text-xs font-medium text-left flex items-start gap-2 animate-fade-scale">
				<i class="ri-error-warning-line text-base shrink-0 mt-0.5"></i>
				<span id="raka-tracker-error-text"></span>
			</div>

			<!-- Submit Button -->
			<button
				type="button"
				id="raka-tracker-submit-btn"
				class="w-full h-11 sm:h-12 mt-1 inline-flex items-center justify-center gap-2 rounded-xl bg-neutral-950 dark:bg-white text-white dark:text-neutral-950 hover:bg-neutral-800 dark:hover:bg-neutral-100 font-bold text-xs sm:text-sm tracking-wider uppercase transition-all duration-200 shadow-md hover:shadow-lg active:scale-[0.98] cursor-pointer font-hn"
			>
				<i id="raka-tracker-btn-icon" class="ri-search-2-line text-sm"></i>
				<span id="raka-tracker-btn-text"><?php esc_html_e( 'ค้นหารายการพรีออเดอร์', 'raka-preorder-theme' ); ?></span>
			</button>

		</div>

		<!-- ── LIVE RESULT VIEW (Hidden by default) ── -->
		<div id="raka-tracker-result-view" class="hidden text-left space-y-4 animate-fade-scale">
			
			<!-- Result Card Header -->
			<div class="flex items-center justify-between pb-3 border-b border-neutral-200/80 dark:border-neutral-800/80">
				<div>
					<span id="raka-res-order-no" class="text-xs font-mono font-bold text-neutral-900 dark:text-white"></span>
					<h3 id="raka-res-product-title" class="text-sm sm:text-base font-bold text-neutral-950 dark:text-white font-hn mt-0.5 line-clamp-1"></h3>
				</div>
				<button
					type="button"
					id="raka-tracker-reset-btn"
					class="inline-flex items-center gap-1 text-[11px] font-semibold text-neutral-500 hover:text-neutral-950 dark:text-neutral-400 dark:hover:text-white transition-colors cursor-pointer"
				>
					<i class="ri-arrow-left-s-line text-sm"></i>
					<span><?php esc_html_e( 'ค้นหาใหม่', 'raka-preorder-theme' ); ?></span>
				</button>
			</div>

			<!-- Status & Courier Badge Box -->
			<div class="grid grid-cols-2 gap-2 sm:gap-3 p-3 sm:p-3.5 rounded-xl bg-neutral-100 dark:bg-neutral-800/60 border border-neutral-200/70 dark:border-neutral-700/50 text-xs">
				<div>
					<span class="text-[10px] uppercase tracking-wider text-neutral-400 dark:text-neutral-500 block font-hn"><?php esc_html_e( 'สถานะปัจจุบัน', 'raka-preorder-theme' ); ?></span>
					<span id="raka-res-status-badge" class="font-bold text-red-600 dark:text-red-400 mt-0.5 block truncate"></span>
				</div>
				<div>
					<span class="text-[10px] uppercase tracking-wider text-neutral-400 dark:text-neutral-500 block font-hn"><?php esc_html_e( 'พัสดุจัดส่ง', 'raka-preorder-theme' ); ?></span>
					<div class="flex items-center gap-1.5 mt-0.5">
						<span id="raka-res-tracking-no" class="font-mono font-bold text-neutral-900 dark:text-white truncate"></span>
						<a id="raka-res-courier-link" href="#" target="_blank" rel="noopener noreferrer" class="hidden text-neutral-500 hover:text-neutral-900 dark:hover:text-white text-xs">
							<i class="ri-external-link-line"></i>
						</a>
					</div>
				</div>
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
				resOrderNo.textContent = item.order_no || 'RAKA-PREORDER';
				resTitle.textContent = item.product || item.title || 'พรีออเดอร์สินค้า';

				const curIdx = typeof item.current_step_idx === 'number' ? item.current_step_idx : 0;
				const steps = item.steps || [];
				const currentStep = steps[curIdx] || steps[0] || {};
				resStatus.textContent = currentStep.title || 'รับคำสั่งซื้อเรียบร้อย';

				if (item.tracking) {
					resTrackNo.textContent = item.tracking;
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
					const isPending = idx > curIdx;

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
