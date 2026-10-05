import { useState, useEffect } from '@wordpress/element';

export default function OrdersView( { orders, loading, onRefresh, onViewOrder } ) {
	const [ filterStatus, setFilterStatus ] = useState( 'all' );
	const [ searchQuery, setSearchQuery ] = useState( '' );
	const [ currentPage, setCurrentPage ] = useState( 1 );
	const [ pageSize, setPageSize ] = useState( 5 );

	const filterTabs = [
		{ id: 'all', label: 'ทั้งหมด' },
		{ id: 'processing', label: 'กำลังเตรียมจัดส่ง' },
		{ id: 'on-hold', label: 'รอชำระ / รอตรวจสอบ' },
		{ id: 'completed', label: 'จัดส่งสำเร็จ' },
		{ id: 'cancelled', label: 'ยกเลิก' },
	];

	const handleFilterChange = ( status ) => {
		setFilterStatus( status );
		setCurrentPage( 1 );
		if ( onRefresh ) {
			onRefresh( status );
		}
	};

	const handleSearchChange = ( e ) => {
		setSearchQuery( e.target.value );
		setCurrentPage( 1 );
	};

	const handlePageSizeChange = ( size ) => {
		setPageSize( size );
		setCurrentPage( 1 );
	};

	const handlePageChange = ( page ) => {
		setCurrentPage( page );
		const el = document.getElementById( 'raka-orders-view' );
		if ( el ) {
			el.scrollIntoView( { behavior: 'smooth', block: 'start' } );
		}
	};

	const filteredOrders = orders.filter( ( order ) => {
		if ( ! searchQuery ) return true;
		const query = searchQuery.toLowerCase();
		const matchNumber = order.number?.toLowerCase().includes( query );
		const matchItem = order.items?.some( ( it ) => it.name?.toLowerCase().includes( query ) );
		const matchTracking = order.tracking_code?.toLowerCase().includes( query );
		return matchNumber || matchItem || matchTracking;
	} );

	// Pagination Calculations
	const totalOrders = filteredOrders.length;
	const totalPages = Math.max( 1, Math.ceil( totalOrders / pageSize ) );
	const validCurrentPage = Math.min( currentPage, totalPages );
	const startIndex = ( validCurrentPage - 1 ) * pageSize;
	const endIndex = Math.min( startIndex + pageSize, totalOrders );
	const paginatedOrders = filteredOrders.slice( startIndex, endIndex );

	// Generate pagination item list with ellipsis
	const getPaginationItems = () => {
		const items = [];
		if ( totalPages <= 7 ) {
			for ( let i = 1; i <= totalPages; i++ ) {
				items.push( i );
			}
		} else {
			items.push( 1 );
			if ( validCurrentPage > 3 ) {
				items.push( '...' );
			}
			const start = Math.max( 2, validCurrentPage - 1 );
			const end = Math.min( totalPages - 1, validCurrentPage + 1 );
			for ( let i = start; i <= end; i++ ) {
				if ( ! items.includes( i ) ) {
					items.push( i );
				}
			}
			if ( validCurrentPage < totalPages - 2 ) {
				items.push( '...' );
			}
			if ( ! items.includes( totalPages ) ) {
				items.push( totalPages );
			}
		}
		return items;
	};

	return (
		<div id="raka-orders-view" className="space-y-6">

			{ /* ── Top Toolbar: Filters & Search ── */ }
			<div id="raka-orders-toolbar" className="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
				{ /* Filter Tabs */ }
				<div id="raka-orders-filter-tabs" className="flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-none">
					{ filterTabs.map( ( tab ) => (
						<button
							key={ tab.id }
							type="button"
							id={ `raka-order-filter-${ tab.id }` }
							onClick={ () => handleFilterChange( tab.id ) }
							className={ `h-9 px-3.5 rounded-full text-xs font-semibold transition-all shrink-0 cursor-pointer ${
								filterStatus === tab.id
									? 'bg-neutral-950 dark:bg-white text-white dark:text-neutral-950 shadow-xs'
									: 'bg-neutral-100/80 hover:bg-neutral-200/70 dark:bg-neutral-900/60 dark:hover:bg-neutral-800 text-neutral-600 dark:text-neutral-400'
							}` }
						>
							<span id={ `raka-order-filter-text-${ tab.id }` } className="inline-block">
								{ tab.label }
							</span>
						</button>
					) ) }
				</div>

				{ /* Search Input */ }
				<div id="raka-orders-search-wrap" className="relative w-full sm:w-64 shrink-0">
					<i id="raka-search-icon" className="ri-search-line absolute left-3.5 top-2.5 text-neutral-400 text-sm"></i>
					<input
						type="text"
						id="raka-orders-search-input"
						placeholder="ค้นหาเลขออเดอร์, สินค้า..."
						value={ searchQuery }
						onChange={ handleSearchChange }
						className="w-full h-9 pl-9 pr-3 rounded-full bg-neutral-100/80 dark:bg-neutral-900/60 border border-black/5 dark:border-white/5 text-xs text-neutral-900 dark:text-white placeholder-neutral-500 focus:outline-none focus:ring-1 focus:ring-black/20 dark:focus:ring-white/20 transition-all"
					/>
				</div>
			</div>

			{ /* ── Orders List ── */ }
			{ loading ? (
				<div id="raka-orders-loading" className="py-20 text-center space-y-3">
					<div id="raka-orders-spinner" className="w-8 h-8 mx-auto border-2 border-neutral-300 dark:border-neutral-700 border-t-red-600 rounded-full animate-spin"></div>
					<p id="raka-orders-loading-txt" className="text-xs text-neutral-500">กำลังโหลดรายการคำสั่งซื้อ...</p>
				</div>
			) : paginatedOrders.length > 0 ? (
				<div id="raka-orders-container" className="space-y-4">
					<div id="raka-orders-list-grid" className="space-y-4">
						{ paginatedOrders.map( ( order ) => (
							<div
								key={ order.id }
								id={ `raka-order-card-${ order.id }` }
								className="p-5 sm:p-6 rounded-3xl bg-neutral-100/80 dark:bg-neutral-900/80 border border-black/5 dark:border-white/5 space-y-4 shadow-xs hover:border-black/15 dark:hover:border-white/15 transition-all"
							>
								{ /* Order Top Header */ }
								<div id={ `raka-order-card-header-${ order.id }` } className="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-black/5 dark:border-white/5 gap-2">
									<div id={ `raka-order-header-left-${ order.id }` } className="flex items-center gap-2.5 flex-wrap">
										<span id={ `raka-order-num-${ order.id }` } className="text-sm sm:text-base font-black text-neutral-950 dark:text-white">
											คำสั่งซื้อ #{ order.number }
										</span>
										{ order.is_preorder && (
											<span id={ `raka-order-pre-badge-${ order.id }` } className="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-red-600 text-white tracking-wider flex items-center gap-1">
												<i id={ `raka-order-pre-icon-${ order.id }` } className="ri-flashlight-line"></i>
												<span id={ `raka-order-pre-txt-${ order.id }` } className="inline-block">PRE-ORDER</span>
											</span>
										)}
										<span id={ `raka-order-status-badge-${ order.id }` } className={ `px-2.5 py-0.5 rounded-full text-xs font-semibold ${
											order.status === 'completed' ? 'bg-green-500/15 text-green-700 dark:text-green-400' :
											order.status === 'processing' ? 'bg-blue-500/15 text-blue-700 dark:text-blue-400' :
											order.status === 'cancelled' ? 'bg-neutral-500/15 text-neutral-600 dark:text-neutral-400' :
											'bg-amber-500/15 text-amber-700 dark:text-amber-400'
										}` }>
											{ order.status_label }
										</span>
									</div>
									<span id={ `raka-order-date-${ order.id }` } className="text-xs text-neutral-500 dark:text-neutral-400">
										{ order.date }
									</span>
								</div>

								{ /* Items List */ }
								<div id={ `raka-order-items-wrap-${ order.id }` } className="space-y-3">
									{ order.items?.map( ( it, idx ) => (
										<div key={ idx } id={ `raka-order-item-${ order.id }-${ idx }` } className="flex items-center justify-between gap-4">
											<div id={ `raka-order-item-left-${ order.id }-${ idx }` } className="flex items-center gap-3">
												<img
													id={ `raka-order-item-img-${ order.id }-${ idx }` }
													src={ it.image_url }
													alt={ it.name }
													className="w-12 h-12 rounded-xl object-cover border border-black/5 dark:border-white/5 bg-white shrink-0"
												/>
												<div id={ `raka-order-item-meta-${ order.id }-${ idx }` } className="space-y-0.5">
													<h4 id={ `raka-order-item-title-${ order.id }-${ idx }` } className="text-xs sm:text-sm font-semibold text-neutral-900 dark:text-white line-clamp-1">
														{ it.name }
													</h4>
													<p id={ `raka-order-item-qty-${ order.id }-${ idx }` } className="text-xs text-neutral-500">
														จำนวน: <span id={ `raka-order-item-qty-val-${ order.id }-${ idx }` } className="font-semibold text-neutral-800 dark:text-neutral-200">{ it.quantity }</span>
													</p>
												</div>
											</div>
											<span id={ `raka-order-item-total-${ order.id }-${ idx }` } className="text-xs sm:text-sm font-bold text-neutral-900 dark:text-white shrink-0">
												{ it.total }
											</span>
										</div>
									) ) }
								</div>

								{ /* Tracking & Total Footer */ }
								<div id={ `raka-order-footer-${ order.id }` } className="pt-3 border-t border-black/5 dark:border-white/5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
									<div id={ `raka-order-tracking-info-${ order.id }` } className="text-xs text-neutral-500">
										{ order.tracking_code ? (
											<span id={ `raka-order-tracking-badge-${ order.id }` } className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-500/10 text-blue-600 dark:text-blue-400 font-medium">
												<i id={ `raka-order-truck-icon-${ order.id }` } className="ri-truck-line"></i>
												<span id={ `raka-order-truck-txt-${ order.id }` } className="inline-block">พัสดุ: </span>
												<span id={ `raka-order-tracking-code-${ order.id }` } className="font-bold">{ order.tracking_code }</span>
											</span>
										) : (
											<span id={ `raka-order-shipping-method-${ order.id }` } className="inline-block">
												การจัดส่ง: { order.shipping_method }
											</span>
										) }
									</div>

									<div id={ `raka-order-footer-actions-${ order.id }` } className="flex items-center justify-between sm:justify-end gap-4">
										<div id={ `raka-order-total-block-${ order.id }` } className="text-right">
											<span id={ `raka-order-total-label-${ order.id }` } className="text-[11px] text-neutral-500 block">
												ยอดรวมทั้งสิ้น
											</span>
											<span id={ `raka-order-total-val-${ order.id }` } className="text-base sm:text-lg font-black text-neutral-950 dark:text-white">
												{ order.total }
											</span>
										</div>
										<button
											type="button"
											id={ `raka-order-detail-btn-${ order.id }` }
											onClick={ () => onViewOrder( order ) }
											className="h-9 px-4 rounded-full bg-neutral-900 hover:bg-neutral-800 dark:bg-white dark:hover:bg-neutral-200 text-white dark:text-neutral-900 text-xs font-bold transition-all shadow-xs cursor-pointer"
										>
											<span id={ `raka-order-detail-btn-txt-${ order.id }` } className="inline-block">
												ดูรายละเอียด
											</span>
										</button>
									</div>
								</div>
							</div>
						) ) }
					</div>

					{ /* ── Modern Minimalist Pagination Controls ── */ }
					<div id="raka-orders-pagination-bar" className="pt-4 pb-2 flex flex-col md:flex-row items-center justify-between gap-4 border-t border-black/5 dark:border-white/5">
						
						{ /* Items Range & Total Info */ }
						<div id="raka-pagination-info" className="flex items-center gap-3 text-xs text-neutral-500 dark:text-neutral-400">
							<span id="raka-pagination-count-text" className="inline-block font-medium">
								แสดง { totalOrders > 0 ? startIndex + 1 : 0 } - { endIndex } จากทั้งหมด { totalOrders } รายการ
							</span>

							{ /* Page Size Selector */ }
							<div id="raka-page-size-selector" className="flex items-center gap-1 pl-3 border-l border-black/10 dark:border-white/10">
								<span id="raka-page-size-label" className="text-[11px] text-neutral-400">ต่อหน้า:</span>
								{ [ 5, 10, 20 ].map( ( size ) => (
									<button
										key={ size }
										type="button"
										id={ `raka-page-size-btn-${ size }` }
										onClick={ () => handlePageSizeChange( size ) }
										className={ `w-6 h-6 rounded-md text-[11px] font-semibold transition-all cursor-pointer flex items-center justify-center ${
											pageSize === size
												? 'bg-neutral-950 dark:bg-white text-white dark:text-neutral-950 shadow-xs'
												: 'text-neutral-500 hover:bg-neutral-200/60 dark:hover:bg-neutral-800'
										}` }
									>
										<span id={ `raka-page-size-val-${ size }` } className="inline-block">{ size }</span>
									</button>
								) ) }
							</div>
						</div>

						{ /* Page Navigation Pills */ }
						{ totalPages > 1 && (
							<div id="raka-pagination-nav" className="flex items-center gap-1.5">
								{ /* Previous Page Button */ }
								<button
									type="button"
									id="raka-pagination-prev-btn"
									disabled={ validCurrentPage <= 1 }
									onClick={ () => handlePageChange( validCurrentPage - 1 ) }
									className="w-8 h-8 rounded-full bg-neutral-100/80 hover:bg-neutral-200/80 dark:bg-neutral-900/80 dark:hover:bg-neutral-800 text-neutral-700 dark:text-neutral-300 disabled:opacity-30 disabled:cursor-not-allowed transition-all flex items-center justify-center cursor-pointer shadow-xs border border-black/5 dark:border-white/5"
									aria-label="Previous Page"
								>
									<i id="raka-pagination-prev-icon" className="ri-arrow-left-s-line text-base"></i>
								</button>

								{ /* Page Numbers */ }
								<div id="raka-pagination-pages-wrap" className="flex items-center gap-1">
									{ getPaginationItems().map( ( item, idx ) => {
										if ( item === '...' ) {
											return (
												<span
													key={ `ellipsis-${ idx }` }
													id={ `raka-pagination-ellipsis-${ idx }` }
													className="w-8 h-8 flex items-center justify-center text-xs text-neutral-400 font-bold"
												>
													...
												</span>
											);
										}

										const isCurrent = validCurrentPage === item;
										return (
											<button
												key={ item }
												type="button"
												id={ `raka-pagination-page-${ item }` }
												onClick={ () => handlePageChange( item ) }
												className={ `w-8 h-8 rounded-full text-xs font-bold transition-all flex items-center justify-center cursor-pointer ${
													isCurrent
														? 'bg-neutral-950 dark:bg-white text-white dark:text-neutral-950 shadow-xs'
														: 'bg-neutral-100/80 hover:bg-neutral-200/80 dark:bg-neutral-900/80 dark:hover:bg-neutral-800 text-neutral-700 dark:text-neutral-300 border border-black/5 dark:border-white/5'
												}` }
											>
												<span id={ `raka-pagination-page-num-${ item }` } className="inline-block">
													{ item }
												</span>
											</button>
										);
									} ) }
								</div>

								{ /* Next Page Button */ }
								<button
									type="button"
									id="raka-pagination-next-btn"
									disabled={ validCurrentPage >= totalPages }
									onClick={ () => handlePageChange( validCurrentPage + 1 ) }
									className="w-8 h-8 rounded-full bg-neutral-100/80 hover:bg-neutral-200/80 dark:bg-neutral-900/80 dark:hover:bg-neutral-800 text-neutral-700 dark:text-neutral-300 disabled:opacity-30 disabled:cursor-not-allowed transition-all flex items-center justify-center cursor-pointer shadow-xs border border-black/5 dark:border-white/5"
									aria-label="Next Page"
								>
									<i id="raka-pagination-next-icon" className="ri-arrow-right-s-line text-base"></i>
								</button>
							</div>
						) }
					</div>
				</div>
			) : (
				<div id="raka-orders-empty-state" className="py-16 text-center space-y-3 bg-neutral-100/50 dark:bg-neutral-900/50 rounded-3xl border border-black/5 dark:border-white/5">
					<i id="raka-empty-icon" className="ri-shopping-bag-line text-4xl text-neutral-400"></i>
					<p id="raka-empty-title" className="text-sm font-bold text-neutral-700 dark:text-neutral-300">ไม่พบคำสั่งซื้อในสถานะนี้</p>
					<p id="raka-empty-subtitle" className="text-xs text-neutral-500">เลือกสถานะอื่น หรือเริ่มสั่งซื้อสินค้าพรีออเดอร์ใหม่</p>
					<a
						id="raka-empty-action-link"
						href="/pre-order/"
						className="inline-block mt-2 px-5 py-2.5 rounded-full text-xs font-bold bg-red-600 hover:bg-red-700 text-white transition-all shadow-sm"
					>
						<span id="raka-empty-action-txt" className="inline-block">เลือกดูสินค้าพรีออเดอร์</span>
					</a>
				</div>
			) }

		</div>
	);
}
