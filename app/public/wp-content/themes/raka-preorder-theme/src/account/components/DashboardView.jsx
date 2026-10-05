export default function DashboardView( { user, stats, recentOrders, onSelectTab, onViewOrder } ) {
	const statCards = [
		{
			id: 'stat-orders',
			label: 'คำสั่งซื้อทั้งหมด',
			value: stats.total_orders ?? 0,
			unit: 'รายการ',
			icon: 'ri-shopping-bag-3-line',
			color: 'text-red-600 dark:text-red-500',
			bgColor: 'bg-red-600/10 dark:bg-red-500/10',
			tab: 'orders',
		},
		{
			id: 'stat-preorders',
			label: 'พรีออเดอร์ที่กำลังผลิต/รอส่ง',
			value: stats.active_preorders ?? 0,
			unit: 'รายการ',
			icon: 'ri-flashlight-line',
			color: 'text-red-600 dark:text-red-500',
			bgColor: 'bg-red-600/10 dark:bg-red-500/10',
			tab: 'orders',
		},
		{
			id: 'stat-spent',
			label: 'ยอดสั่งซื้อสะสม',
			value: stats.total_spent ?? '0 ฿',
			unit: '',
			icon: 'ri-wallet-3-line',
			color: 'text-red-600 dark:text-red-500',
			bgColor: 'bg-red-600/10 dark:bg-red-500/10',
			tab: 'orders',
		},
		{
			id: 'stat-points',
			label: 'แต้มสะสมพร้อมใช้',
			value: Number( stats.points_balance ?? 0 ).toLocaleString(),
			unit: 'แต้ม',
			icon: 'ri-copper-coin-line',
			color: 'text-red-600 dark:text-red-500',
			bgColor: 'bg-red-600/10 dark:bg-red-500/10',
			tab: 'rewards',
		},
		{
			id: 'stat-passkeys',
			label: 'อุปกรณ์ Passkeys',
			value: stats.passkeys_count ?? 0,
			unit: 'อุปกรณ์',
			icon: 'ri-fingerprint-line',
			color: 'text-red-600 dark:text-red-500',
			bgColor: 'bg-red-600/10 dark:bg-red-500/10',
			tab: 'security',
		},
	];

	return (
		<div id="raka-dashboard-view" className="space-y-6 sm:space-y-8">

			{ /* Stat Metrics Grid */ }
			<div id="raka-dashboard-stats-grid" className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
				{ statCards.map( ( item ) => (
					<div
						key={ item.id }
						id={ `raka-dashboard-${ item.id }` }
						onClick={ () => onSelectTab && onSelectTab( item.tab ) }
						className="p-4 sm:p-5 rounded-3xl bg-neutral-100/80 dark:bg-neutral-900/80 border border-black/5 dark:border-white/5 hover:border-black/20 dark:hover:border-white/20 transition-all cursor-pointer shadow-xs flex flex-col justify-between space-y-3"
					>
						<div id={ `raka-stat-top-${ item.id }` } className="flex items-center justify-between">
							<div id={ `raka-stat-icon-wrap-${ item.id }` } className={ `w-10 h-10 rounded-2xl ${ item.bgColor } flex items-center justify-center` }>
								<i id={ `raka-stat-icon-${ item.id }` } className={ `${ item.icon } ${ item.color } text-xl` }></i>
							</div>
							<i id={ `raka-stat-arrow-${ item.id }` } className="ri-arrow-right-up-line text-neutral-400 text-sm"></i>
						</div>
						<div id={ `raka-stat-body-${ item.id }` } className="space-y-0.5">
							<div id={ `raka-stat-value-${ item.id }` } className="text-xl sm:text-2xl font-bold tracking-tight text-neutral-900 dark:text-white">
								{ item.value } { item.unit && <span className="text-xs font-normal text-neutral-500">{ item.unit }</span> }
							</div>
							<p id={ `raka-stat-label-${ item.id }` } className="text-xs text-neutral-500 dark:text-neutral-400 font-medium line-clamp-1">
								{ item.label }
							</p>
						</div>
					</div>
				) ) }
			</div>

			{ /* Main Dashboard 2-Column Section */ }
			<div id="raka-dashboard-main-grid" className="grid grid-cols-1 lg:grid-cols-3 gap-6">

				{ /* Recent Orders (2 Cols) */ }
				<div id="raka-dashboard-recent-orders-card" className="lg:col-span-2 p-5 sm:p-7 rounded-3xl bg-neutral-100/80 dark:bg-neutral-900/80 border border-black/5 dark:border-white/5 space-y-4 shadow-xs">
					<div id="raka-recent-orders-header" className="flex items-center justify-between border-b border-black/5 dark:border-white/5 pb-3">
						<div id="raka-recent-orders-title-wrap" className="flex items-center gap-2">
							<i id="raka-recent-orders-icon" className="ri-time-line text-neutral-500"></i>
							<h2 id="raka-recent-orders-title" className="text-sm sm:text-base font-bold text-neutral-950 dark:text-white">
								คำสั่งซื้อล่าสุด
							</h2>
						</div>
						<button
							type="button"
							id="raka-view-all-orders-btn"
							onClick={ () => onSelectTab( 'orders' ) }
							className="text-xs font-semibold text-neutral-600 hover:text-neutral-950 dark:text-neutral-400 dark:hover:text-white underline cursor-pointer"
						>
							ดูทั้งหมด ({ stats.total_orders ?? 0 })
						</button>
					</div>

					{ recentOrders && recentOrders.length > 0 ? (
						<div id="raka-recent-orders-list" className="space-y-3">
							{ recentOrders.map( ( order ) => (
								<div
									key={ order.id }
									id={ `raka-recent-order-${ order.id }` }
									onClick={ () => onViewOrder && onViewOrder( order ) }
									className="p-3.5 sm:p-4 rounded-2xl bg-white dark:bg-neutral-800/80 border border-black/5 dark:border-white/5 hover:border-black/20 dark:hover:border-white/20 transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-3 cursor-pointer shadow-xs"
								>
									<div id={ `raka-order-left-${ order.id }` } className="flex items-center gap-3">
										<div id={ `raka-order-thumbs-${ order.id }` } className="flex -space-x-2 shrink-0">
											{ order.items?.slice( 0, 2 ).map( ( it, idx ) => (
												<img
													key={ idx }
													id={ `raka-order-img-${ order.id }-${ idx }` }
													src={ it.image_url }
													alt={ it.name }
													className="w-10 h-10 rounded-xl object-cover border border-white dark:border-neutral-700 bg-neutral-100"
												/>
											) ) }
										</div>
										<div id={ `raka-order-meta-${ order.id }` } className="space-y-0.5">
											<div id={ `raka-order-num-row-${ order.id }` } className="flex items-center gap-2">
												<span className="text-xs sm:text-sm font-bold text-neutral-900 dark:text-white">#{ order.number }</span>
												<span className="text-[11px] px-2 py-0.5 rounded-full font-medium bg-neutral-100 dark:bg-neutral-700 text-neutral-600 dark:text-neutral-300">
													{ order.status_label }
												</span>
											</div>
											<p className="text-[11px] text-neutral-500 dark:text-neutral-400">{ order.date } • { order.item_count } ชิ้น</p>
										</div>
									</div>

									<div id={ `raka-order-right-${ order.id }` } className="flex items-center justify-between sm:justify-end gap-3 shrink-0">
										<span className="text-sm sm:text-base font-bold text-neutral-950 dark:text-white">{ order.total }</span>
										<i className="ri-arrow-right-s-line text-neutral-400 text-lg"></i>
									</div>
								</div>
							) ) }
						</div>
					) : (
						<div id="raka-recent-orders-empty" className="py-10 text-center space-y-2">
							<i id="raka-empty-orders-icon" className="ri-inbox-line text-4xl text-neutral-400"></i>
							<p id="raka-empty-orders-text" className="text-xs sm:text-sm text-neutral-500 font-medium">ยังไม่มีประวัติคำสั่งซื้อ</p>
							<a
								id="raka-empty-shop-link"
								href="/pre-order/"
								className="inline-block mt-2 px-4 py-2 rounded-full text-xs font-semibold bg-neutral-900 dark:bg-white text-white dark:text-neutral-900"
							>
								เริ่มช้อปพรีออเดอร์
							</a>
						</div>
					) }
				</div>

				{ /* Quick Links & Security Card (1 Col) */ }
				<div id="raka-dashboard-side-card" className="space-y-4">
					<div id="raka-profile-summary-box" className="p-5 rounded-3xl bg-neutral-100/80 dark:bg-neutral-900/80 border border-black/5 dark:border-white/5 space-y-3 shadow-xs">
						<h3 id="raka-profile-summary-title" className="text-xs sm:text-sm font-bold uppercase tracking-wider text-neutral-500">
							ข้อมูลการติดต่อ
						</h3>
						<div id="raka-profile-summary-details" className="space-y-2 text-xs sm:text-sm">
							<div className="flex justify-between">
								<span className="text-neutral-500">เบอร์โทรศัพท์:</span>
								<span className="font-semibold text-neutral-900 dark:text-white">{ user.phone || '-' }</span>
							</div>
							<div className="flex justify-between">
								<span className="text-neutral-500">LINE ID:</span>
								<span className="font-semibold text-neutral-900 dark:text-white">{ user.line_id || '-' }</span>
							</div>
						</div>
						<button
							type="button"
							id="raka-edit-profile-btn"
							onClick={ () => onSelectTab( 'addresses' ) }
							className="w-full mt-2 h-9 rounded-full bg-white dark:bg-neutral-800 border border-black/10 dark:border-white/10 text-xs font-semibold text-neutral-800 dark:text-neutral-200 hover:bg-neutral-50 dark:hover:bg-neutral-700 transition-all cursor-pointer"
						>
							แก้ไขที่อยู่ / ข้อมูลติดต่อ
						</button>
					</div>

					<div id="raka-passkey-promo-box" className="p-5 rounded-3xl bg-neutral-950 text-white space-y-3 shadow-xs relative overflow-hidden">
						<div id="raka-passkey-promo-header" className="flex items-center gap-2">
							<i id="raka-promo-icon" className="ri-fingerprint-line text-lg text-red-500"></i>
							<span id="raka-promo-title" className="text-xs sm:text-sm font-bold">One-Pass Security</span>
						</div>
						<p id="raka-promo-desc" className="text-xs text-neutral-400 leading-relaxed">
							เข้าสู่ระบบรวดเร็ว ปลอดภัยด้วย Touch ID หรือ Face ID โดยไม่ต้องใช้รหัสผ่าน
						</p>
						<button
							type="button"
							id="raka-promo-manage-btn"
							onClick={ () => onSelectTab( 'security' ) }
							className="h-9 px-4 rounded-full bg-white text-neutral-950 text-xs font-bold hover:bg-neutral-200 transition-all cursor-pointer"
						>
							จัดการอุปกรณ์ ({ stats.passkeys_count ?? 0 })
						</button>
					</div>
				</div>

			</div>

		</div>
	);
}
