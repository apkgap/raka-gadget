import { useState } from '@wordpress/element';
import { useLoyalty } from '../hooks/useLoyalty';

export default function RewardsView( { config, showToast } ) {
	const {
		data,
		loading,
		historyFilter,
		setHistoryFilter,
		historyItems,
		hasMoreHistory,
		historyLoading,
		loadMoreHistory,
		applyReward,
		actionLoading,
	} = useLoyalty( config );

	const [ redeemingId, setRedeemingId ] = useState( null );

	const handleRedeem = async ( productId ) => {
		try {
			setRedeemingId( productId );
			const res = await applyReward( productId );
			if ( res?.success ) {
				showToast( res.message || 'เพิ่มของรางวัลลงในตะกร้าสินค้าเรียบร้อยแล้ว!' );
			}
		} catch ( err ) {
			showToast( err.message || 'ไม่สามารถแลกของรางวัลได้', 'error' );
		} finally {
			setRedeemingId( null );
		}
	};

	if ( loading && ! data ) {
		return (
			<div id="raka-rewards-loading-wrap" className="min-h-[350px] flex flex-col items-center justify-center space-y-4">
				<div id="raka-rewards-spinner" className="w-9 h-9 border-3 border-neutral-300 dark:border-neutral-700 border-t-red-600 rounded-full animate-spin"></div>
				<p id="raka-rewards-loading-text" className="text-xs sm:text-sm text-neutral-500 font-medium">กำลังโหลดข้อมูลแต้มสะสม & ของรางวัล...</p>
			</div>
		);
	}

	const account = data?.account || {
		balance: 0,
		available: 0,
		reserved: 0,
		lifetime_earned: 0,
		lifetime_spent: 0,
		tier: 'bronze',
		tier_name: 'Bronze',
		next_tier: 'silver',
		next_tier_progress: 0,
		points_to_next: 0,
		next_100_progress: 0,
	};

	const expiring = data?.expiring || { amount: 0, date: null };
	const catalog = data?.catalog || [];
	const rulesInfo = data?.rules_info || { spend_per_point: 100, redeem_cost: 100, preorder_multiplier: 1.0 };

	const filterTabs = [
		{ id: 'all', label: 'ทั้งหมด' },
		{ id: 'earn', label: 'ได้รับแต้ม' },
		{ id: 'redeem', label: 'แลกของรางวัล' },
		{ id: 'refund', label: 'คืนแต้ม' },
		{ id: 'expire', label: 'แต้มหมดอายุ' },
	];

	const getTierBadgeColor = ( tier ) => {
		switch ( tier ) {
			case 'gold':
				return 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20';
			case 'silver':
				return 'bg-slate-500/10 text-slate-600 dark:text-slate-300 border-slate-500/20';
			default:
				return 'bg-amber-700/10 text-amber-800 dark:text-amber-600 border-amber-700/20';
		}
	};

	return (
		<div id="raka-rewards-view-root" className="space-y-6 sm:space-y-8 font-hn">

			{ /* ── Top Hero Balance & Tier Banner ── */ }
			<div id="raka-rewards-hero-card" className="p-6 sm:p-8 rounded-3xl bg-neutral-950 text-white relative overflow-hidden border border-black/10 dark:border-white/10 shadow-lg">
				{ /* Subtle Ambient Glow */ }
				<div id="raka-rewards-hero-glow" className="absolute -top-24 -right-24 w-72 h-72 bg-red-600/20 rounded-full blur-3xl pointer-events-none"></div>

				<div id="raka-rewards-hero-content" className="relative z-10 grid grid-cols-1 lg:grid-cols-3 gap-6 items-center">
					{ /* Left: Points Balance */ }
					<div id="raka-rewards-balance-col" className="lg:col-span-2 space-y-3">
						<div id="raka-rewards-tag-row" className="flex items-center gap-2.5">
							<span id="raka-rewards-currency-badge" className="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-red-600/20 text-red-400 border border-red-500/30 flex items-center gap-1.5">
								<i id="raka-coin-icon-top" className="ri-copper-coin-line text-sm"></i>
								Raka Points
							</span>
							<span
								id="raka-rewards-tier-badge"
								className={ `px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border flex items-center gap-1.5 ${ getTierBadgeColor( account.tier ) }` }
							>
								<i id="raka-tier-icon-top" className="ri-vip-crown-line text-sm"></i>
								{ account.tier_name } Member
							</span>
						</div>

						<div id="raka-rewards-main-number-row" className="space-y-1">
							<div id="raka-rewards-points-wrapper" className="flex items-baseline gap-2">
								<span id="raka-rewards-points-val" className="text-4xl sm:text-5xl font-extrabold tracking-tight text-white">
									{ Number( account.available ).toLocaleString() }
								</span>
								<span id="raka-rewards-points-unit" className="text-sm sm:text-base font-semibold text-neutral-400">แต้มพร้อมใช้</span>
							</div>
							{ account.reserved > 0 && (
								<p id="raka-rewards-reserved-note" className="text-xs text-amber-400/90 font-medium">
									(มี { Number( account.reserved ).toLocaleString() } แต้ม กำลังถูกจองในคำสั่งซื้อที่รอชำระ)
								</p>
							) }
						</div>

						{ /* Progress to next 100 points */ }
						<div id="raka-rewards-progress-box" className="space-y-1.5 pt-1 max-w-md">
							<div id="raka-rewards-progress-labels" className="flex justify-between text-xs text-neutral-400">
								<span id="raka-progress-text-left">ความคืบหน้าสู่ 100 แต้มถัดไป</span>
								<span id="raka-progress-text-right" className="font-semibold text-neutral-200">{ account.next_100_progress } / 100</span>
							</div>
							<div id="raka-progress-track" className="w-full h-2 rounded-full bg-neutral-800 overflow-hidden border border-white/5">
								<div
									id="raka-progress-bar"
									className="h-full bg-gradient-to-r from-red-600 to-rose-500 rounded-full transition-all duration-500"
									style={ { width: `${ account.next_100_progress }%` } }
								></div>
							</div>
						</div>
					</div>

					{ /* Right: Tier Status & Lifetime Stats */ }
					<div id="raka-rewards-tier-col" className="p-5 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-xs space-y-4">
						<div id="raka-tier-header-wrap" className="flex items-center justify-between">
							<span id="raka-tier-title" className="text-xs font-bold uppercase tracking-wider text-neutral-400">ระดับสมาชิก</span>
							<span id="raka-tier-curr" className="text-xs font-bold text-neutral-200">{ account.tier_name }</span>
						</div>

						{ account.next_tier !== 'max' ? (
							<div id="raka-tier-prog-wrap" className="space-y-1.5">
								<div id="raka-tier-prog-text" className="flex justify-between text-xs text-neutral-300">
									<span id="raka-tier-next-name">สู่ระดับ { account.next_tier?.toUpperCase() }</span>
									<span id="raka-tier-next-pts" className="font-semibold text-red-400">อีก { Number( account.points_to_next ).toLocaleString() } แต้ม</span>
								</div>
								<div id="raka-tier-track" className="w-full h-1.5 rounded-full bg-neutral-800 overflow-hidden">
									<div
										id="raka-tier-bar"
										className="h-full bg-red-500 rounded-full transition-all duration-500"
										style={ { width: `${ account.next_tier_progress }%` } }
									></div>
								</div>
							</div>
						) : (
							<p id="raka-tier-max-note" className="text-xs text-amber-400 font-semibold flex items-center gap-1.5">
								<i id="raka-max-icon" className="ri-medal-fill"></i>
								คุณอยู่ในระดับสูงสุด (Gold Tier) แล้ว!
							</p>
						) }

						<div id="raka-lifetime-stats-row" className="pt-2 border-t border-white/10 grid grid-cols-2 gap-2 text-xs">
							<div id="raka-stat-earned-wrap" className="space-y-0.5">
								<span id="raka-stat-earned-lbl" className="text-neutral-400">ได้รับสะสม</span>
								<p id="raka-stat-earned-val" className="font-bold text-neutral-200">{ Number( account.lifetime_earned ).toLocaleString() } แต้ม</p>
							</div>
							<div id="raka-stat-spent-wrap" className="space-y-0.5">
								<span id="raka-stat-spent-lbl" className="text-neutral-400">แลกใช้สะสม</span>
								<p id="raka-stat-spent-val" className="font-bold text-neutral-200">{ Number( account.lifetime_spent ).toLocaleString() } แต้ม</p>
							</div>
						</div>
					</div>
				</div>
			</div>

			{ /* ── 30-Day Expiring Alert Banner ── */ }
			{ expiring.amount > 0 && (
				<div
					id="raka-rewards-expiring-alert"
					className="p-4 sm:p-5 rounded-2xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-amber-900 dark:text-amber-200"
				>
					<div id="raka-expiring-left" className="flex items-center gap-3">
						<div id="raka-expiring-icon-wrap" className="w-10 h-10 rounded-xl bg-amber-500/10 dark:bg-amber-500/20 flex items-center justify-center shrink-0">
							<i id="raka-expiring-icon" className="ri-time-line text-xl text-amber-600 dark:text-amber-400"></i>
						</div>
						<div id="raka-expiring-text-wrap" className="space-y-0.5">
							<h4 id="raka-expiring-title" className="text-sm font-bold">
								คุณมีแต้มสะสมที่กำลังจะหมดอายุใน 30 วันนี้
							</h4>
							<p id="raka-expiring-desc" className="text-xs text-amber-700 dark:text-amber-300">
								จำนวน <span className="font-bold text-red-600 dark:text-red-400">{ Number( expiring.amount ).toLocaleString() } แต้ม</span> จะหมดอายุภายในวันที่ { expiring.date }
							</p>
						</div>
					</div>
					<a
						id="raka-expiring-redeem-btn"
						href="#raka-reward-catalog-section"
						className="px-4 py-2 rounded-full bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold transition-all shrink-0 text-center cursor-pointer shadow-xs"
					>
						เลือกแลกของรางวัลเลย
					</a>
				</div>
			) }

			{ /* ── Reward Catalog Section ── */ }
			<div id="raka-reward-catalog-section" className="space-y-4">
				<div id="raka-catalog-header-wrap" className="flex items-center justify-between">
					<div id="raka-catalog-title-group" className="flex items-center gap-2">
						<i id="raka-catalog-icon" className="ri-gift-line text-lg text-red-600"></i>
						<h3 id="raka-catalog-title" className="text-base sm:text-lg font-bold text-neutral-950 dark:text-white">
							แคตตาล็อกของรางวัลพรีเมียม
						</h3>
					</div>
					<span id="raka-catalog-count-badge" className="text-xs font-semibold text-neutral-500">
						มี { catalog.length } รายการ
					</span>
				</div>

				{ catalog.length > 0 ? (
					<div id="raka-catalog-grid" className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
						{ catalog.map( ( item ) => (
							<div
								key={ item.id }
								id={ `raka-reward-item-${ item.id }` }
								className="p-4 rounded-3xl bg-neutral-100/80 dark:bg-neutral-900/80 border border-black/5 dark:border-white/5 hover:border-black/20 dark:hover:border-white/20 transition-all flex flex-col justify-between space-y-4 shadow-xs"
							>
								<div id={ `raka-reward-top-${ item.id }` } className="space-y-3">
									<div id={ `raka-reward-img-wrap-${ item.id }` } className="relative w-full aspect-square rounded-2xl overflow-hidden bg-white dark:bg-neutral-800 border border-black/5 dark:border-white/5">
										<img
											id={ `raka-reward-img-${ item.id }` }
											src={ item.image_url }
											alt={ item.name }
											className="w-full h-full object-cover"
										/>
										<div id={ `raka-reward-cost-tag-${ item.id }` } className="absolute top-2.5 right-2.5 px-3 py-1 rounded-full bg-neutral-950/90 text-white text-xs font-bold backdrop-blur-xs flex items-center gap-1">
											<i id={ `raka-cost-coin-${ item.id }` } className="ri-copper-coin-line text-amber-400"></i>
											<span>{ item.cost } แต้ม</span>
										</div>
									</div>

									<div id={ `raka-reward-info-${ item.id }` } className="space-y-1">
										<h4 id={ `raka-reward-name-${ item.id }` } className="text-sm font-bold text-neutral-900 dark:text-white line-clamp-2">
											{ item.name }
										</h4>
										<div id={ `raka-reward-val-row-${ item.id }` } className="flex items-center gap-2">
											<span id={ `raka-reward-free-${ item.id }` } className="text-xs font-bold text-red-600 dark:text-red-500">ฟรี (0 ฿)</span>
											{ item.price_label && (
												<span id={ `raka-reward-val-orig-${ item.id }` } className="text-[11px] text-neutral-400 line-through">
													{ item.price_label }
												</span>
											) }
										</div>
									</div>
								</div>

								<div id={ `raka-reward-action-wrap-${ item.id }` } className="space-y-1.5 pt-2 border-t border-black/5 dark:border-white/5">
									<button
										type="button"
										id={ `raka-redeem-btn-${ item.id }` }
										disabled={ ! item.redeemable || actionLoading || redeemingId === item.id }
										onClick={ () => handleRedeem( item.id ) }
										className={ `w-full h-10 rounded-full text-xs font-bold transition-all flex items-center justify-center gap-2 cursor-pointer ${
											item.redeemable
												? 'bg-neutral-900 hover:bg-neutral-800 dark:bg-white dark:hover:bg-neutral-200 text-white dark:text-neutral-950 shadow-xs'
												: 'bg-neutral-200 dark:bg-neutral-800 text-neutral-400 dark:text-neutral-600 cursor-not-allowed'
										}` }
									>
										{ redeemingId === item.id ? (
											<>
												<div id={ `raka-spin-btn-${ item.id }` } className="w-4 h-4 border-2 border-current border-t-transparent rounded-full animate-spin"></div>
												<span>กำลังดำเนินการ...</span>
											</>
										) : (
											<>
												<i id={ `raka-gift-btn-icon-${ item.id }` } className="ri-gift-line text-sm"></i>
												<span>{ item.redeemable ? 'แลกของรางวัลนี้' : 'แต้มสะสมไม่เพียงพอ' }</span>
											</>
										)}
									</button>
									{ item.reason && (
										<p id={ `raka-redeem-hint-${ item.id }` } className="text-center text-[11px] text-neutral-400">
											{ item.reason }
										</p>
									) }
								</div>
							</div>
						) ) }
					</div>
				) : (
					<div id="raka-catalog-empty-card" className="p-8 rounded-3xl bg-neutral-100/80 dark:bg-neutral-900/80 border border-black/5 dark:border-white/5 text-center space-y-2">
						<i id="raka-empty-cat-icon" className="ri-gift-line text-3xl text-neutral-400"></i>
						<p id="raka-empty-cat-text" className="text-xs sm:text-sm text-neutral-500 font-medium">ยังไม่มีรายการของรางวัลที่เปิดให้แลกในขณะนี้</p>
					</div>
				) }
			</div>

			{ /* ── Points Ledger History ── */ }
			<div id="raka-rewards-history-section" className="p-5 sm:p-7 rounded-3xl bg-neutral-100/80 dark:bg-neutral-900/80 border border-black/5 dark:border-white/5 space-y-4 shadow-xs">
				<div id="raka-history-header-wrap" className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-black/5 dark:border-white/5 pb-4">
					<div id="raka-history-title-group" className="flex items-center gap-2">
						<i id="raka-history-icon" className="ri-history-line text-lg text-neutral-500"></i>
						<h3 id="raka-history-title" className="text-sm sm:text-base font-bold text-neutral-950 dark:text-white">
							ประวัติการทำรายการแต้ม (Ledger)
						</h3>
					</div>

					{ /* Filter Pills */ }
					<div id="raka-history-filters-wrap" className="flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-none">
						{ filterTabs.map( ( tab ) => (
							<button
								key={ tab.id }
								type="button"
								id={ `raka-hist-tab-${ tab.id }` }
								onClick={ () => setHistoryFilter( tab.id ) }
								className={ `px-3 py-1 rounded-full text-xs font-semibold transition-all shrink-0 cursor-pointer ${
									historyFilter === tab.id
										? 'bg-neutral-950 text-white dark:bg-white dark:text-neutral-950'
										: 'bg-white dark:bg-neutral-800 text-neutral-600 dark:text-neutral-400 hover:bg-neutral-200 dark:hover:bg-neutral-700'
								}` }
							>
								{ tab.label }
							</button>
						) ) }
					</div>
				</div>

				{ historyItems && historyItems.length > 0 ? (
					<div id="raka-history-list" className="space-y-2.5">
						{ historyItems.map( ( entry ) => {
							const isEarn = entry.amount > 0;
							return (
								<div
									key={ entry.id }
									id={ `raka-ledger-row-${ entry.id }` }
									className="p-3.5 sm:p-4 rounded-2xl bg-white dark:bg-neutral-800/80 border border-black/5 dark:border-white/5 flex items-center justify-between gap-3 shadow-xs"
								>
									<div id={ `raka-ledger-left-${ entry.id }` } className="flex items-center gap-3">
										<div
											id={ `raka-ledger-icon-wrap-${ entry.id }` }
											className={ `w-9 h-9 rounded-xl flex items-center justify-center shrink-0 ${
												isEarn ? 'bg-green-500/10 text-green-600 dark:text-green-400' : 'bg-red-500/10 text-red-600 dark:text-red-400'
											}` }
										>
											<i id={ `raka-ledger-icon-${ entry.id }` } className={ isEarn ? 'ri-add-line text-lg font-bold' : 'ri-subtract-line text-lg font-bold' }></i>
										</div>
										<div id={ `raka-ledger-details-${ entry.id }` } className="space-y-0.5">
											<div id={ `raka-ledger-type-row-${ entry.id }` } className="flex items-center gap-2">
												<span id={ `raka-ledger-type-${ entry.id }` } className="text-xs sm:text-sm font-bold text-neutral-900 dark:text-white capitalize">
													{ entry.type === 'earn' && 'ได้รับแต้มสะสม' }
													{ entry.type === 'redeem' && 'แลกของรางวัล' }
													{ entry.type === 'refund' && 'คืนแต้มจากการยกเลิก' }
													{ entry.type === 'expire' && 'แต้มหมดอายุ' }
													{ entry.type === 'admin_adjust' && 'ปรับปรุงยอดแต้มโดยแอดมิน' }
												</span>
												{ entry.order_id > 0 && (
													<span id={ `raka-ledger-order-${ entry.id }` } className="text-[11px] px-2 py-0.2 rounded-full font-medium bg-neutral-100 dark:bg-neutral-700 text-neutral-600 dark:text-neutral-300">
														ออเดอร์ #{ entry.order_id }
													</span>
												) }
											</div>
											<p id={ `raka-ledger-date-${ entry.id }` } className="text-[11px] text-neutral-400">
												{ entry.date }
											</p>
										</div>
									</div>

									<div id={ `raka-ledger-right-${ entry.id }` } className="text-right space-y-0.5 shrink-0">
										<div
											id={ `raka-ledger-amount-${ entry.id }` }
											className={ `text-xs sm:text-sm font-extrabold ${ isEarn ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }` }
										>
											{ isEarn ? `+${ entry.amount }` : `${ entry.amount }` } แต้ม
										</div>
										<p id={ `raka-ledger-bal-after-${ entry.id }` } className="text-[11px] text-neutral-400">
											คงเหลือ { entry.balance_after } แต้ม
										</p>
									</div>
								</div>
							);
						} ) }

						{ hasMoreHistory && (
							<div id="raka-history-load-more-wrap" className="pt-2 text-center">
								<button
									type="button"
									id="raka-load-more-history-btn"
									disabled={ historyLoading }
									onClick={ loadMoreHistory }
									className="px-5 py-2 rounded-full bg-white dark:bg-neutral-800 border border-black/10 dark:border-white/10 hover:bg-neutral-50 dark:hover:bg-neutral-700 text-xs font-semibold text-neutral-800 dark:text-neutral-200 transition-all cursor-pointer shadow-xs inline-flex items-center gap-2"
								>
									{ historyLoading ? (
										<>
											<div id="raka-load-more-spin" className="w-3.5 h-3.5 border-2 border-current border-t-transparent rounded-full animate-spin"></div>
											<span>กำลังโหลด...</span>
										</>
									) : (
										<>
											<i id="raka-load-more-icon" className="ri-arrow-down-line"></i>
											<span>โหลดประวัติเพิ่มเติม</span>
										</>
									)}
								</button>
							</div>
						) }
					</div>
				) : (
					<div id="raka-history-empty" className="py-8 text-center space-y-1.5">
						<i id="raka-empty-hist-icon" className="ri-file-list-3-line text-3xl text-neutral-400"></i>
						<p id="raka-empty-hist-text" className="text-xs text-neutral-500 font-medium">ไม่พบประวัติรายการแต้มในหมวดหมู่นี้</p>
					</div>
				) }
			</div>

			{ /* ── Loyalty Rules & FAQ Guide ── */ }
			<div id="raka-rewards-guide-card" className="p-5 sm:p-7 rounded-3xl bg-neutral-100/80 dark:bg-neutral-900/80 border border-black/5 dark:border-white/5 space-y-4 shadow-xs">
				<div id="raka-guide-header" className="flex items-center gap-2">
					<i id="raka-guide-icon" className="ri-information-line text-lg text-neutral-500"></i>
					<h3 id="raka-guide-title" className="text-sm sm:text-base font-bold text-neutral-950 dark:text-white">
						วิธีการสะสมแต้ม & สิทธิประโยชน์
					</h3>
				</div>

				<div id="raka-guide-grid" className="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
					<div id="raka-guide-col-1" className="p-4 rounded-2xl bg-white dark:bg-neutral-800/80 border border-black/5 dark:border-white/5 space-y-1.5">
						<div id="raka-guide-col1-header" className="flex items-center gap-2 font-bold text-neutral-900 dark:text-white">
							<i id="raka-guide-col1-icon" className="ri-shopping-cart-2-line text-red-600"></i>
							<span>ช้อปทุก { rulesInfo.spend_per_point } บาท = 1 แต้ม</span>
						</div>
						<p id="raka-guide-col1-desc" className="text-neutral-500 dark:text-neutral-400 leading-relaxed">
							คำนวณจากยอดชำระสุทธิ (หักส่วนลด คูปอง และค่าจัดส่ง) แต้มจะเข้าบัญชีทันทีเมื่อออเดอร์เสร็จสมบูรณ์
						</p>
					</div>

					<div id="raka-guide-col-2" className="p-4 rounded-2xl bg-white dark:bg-neutral-800/80 border border-black/5 dark:border-white/5 space-y-1.5">
						<div id="raka-guide-col2-header" className="flex items-center gap-2 font-bold text-neutral-900 dark:text-white">
							<i id="raka-guide-col2-icon" className="ri-flashlight-line text-red-600"></i>
							<span>พรีออเดอร์รับแต้มพิเศษ</span>
						</div>
						<p id="raka-guide-col2-desc" className="text-neutral-500 dark:text-neutral-400 leading-relaxed">
							รับคะแนนพิเศษ { rulesInfo.preorder_multiplier > 1.0 ? `x${ rulesInfo.preorder_multiplier }` : '' } สำหรับสินค้าพรีออเดอร์ที่เข้าร่วมรายการ
						</p>
					</div>

					<div id="raka-guide-col-3" className="p-4 rounded-2xl bg-white dark:bg-neutral-800/80 border border-black/5 dark:border-white/5 space-y-1.5">
						<div id="raka-guide-col3-header" className="flex items-center gap-2 font-bold text-neutral-900 dark:text-white">
							<i id="raka-guide-col3-icon" className="ri-gift-line text-red-600"></i>
							<span>แลกของรางวัลฟรี</span>
						</div>
						<p id="raka-guide-col3-desc" className="text-neutral-500 dark:text-neutral-400 leading-relaxed">
							สะสมครบ { rulesInfo.redeem_cost } แต้ม สามารถเลือกแลกของรางวัลฟรี 1 ชิ้นในแคตตาล็อกต่อ 1 คำสั่งซื้อ
						</p>
					</div>
				</div>
			</div>

		</div>
	);
}
