export default function OrderDetailModal( { order, onClose } ) {
	if ( ! order ) return null;

	return (
		<div id="raka-order-modal-backdrop" className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade-in font-hn">
			<div
				id="raka-order-modal-content"
				className="w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-3xl bg-white dark:bg-neutral-900 border border-black/10 dark:border-white/10 shadow-2xl p-6 sm:p-8 space-y-6 text-neutral-900 dark:text-white"
			>
				{ /* Top Modal Header */ }
				<div id="raka-order-modal-header" className="flex items-center justify-between pb-4 border-b border-black/10 dark:border-white/10">
					<div className="space-y-0.5">
						<div className="flex items-center gap-2 flex-wrap">
							<h3 id="raka-order-modal-title" className="text-base sm:text-lg font-black">
								รายละเอียดคำสั่งซื้อ #{ order.number }
							</h3>
							{ order.is_preorder && (
								<span className="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-red-600 text-white tracking-wider">
									PRE-ORDER
								</span>
							) }
						</div>
						<p className="text-xs text-neutral-500">{ order.date }</p>
					</div>

					<button
						type="button"
						id="raka-close-order-modal-btn"
						onClick={ onClose }
						className="w-8 h-8 rounded-full bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 flex items-center justify-center text-neutral-500 hover:text-neutral-900 dark:hover:text-white transition-all cursor-pointer"
					>
						<i className="ri-close-line text-lg"></i>
					</button>
				</div>

				{ /* Tracking info box */ }
				{ order.tracking_code && (
					<div id="raka-modal-tracking-card" className="p-4 rounded-2xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-between gap-4">
						<div className="flex items-center gap-3">
							<i className="ri-truck-line text-2xl text-blue-600 dark:text-blue-400"></i>
							<div>
								<span className="text-[11px] text-blue-600 dark:text-blue-400 block font-semibold">หมายเลขพัสดุ / Tracking No.</span>
								<span className="text-sm sm:text-base font-mono font-bold text-neutral-900 dark:text-white">{ order.tracking_code }</span>
							</div>
						</div>
						<span className="text-xs px-2.5 py-1 rounded-full bg-blue-600 text-white font-medium">กำลังจัดส่ง</span>
					</div>
				) }

				{ /* Items Table */ }
				<div id="raka-modal-items-section" className="space-y-3">
					<h4 className="text-xs font-bold uppercase tracking-wider text-neutral-500">รายการสินค้า</h4>
					<div className="space-y-3 divide-y divide-black/5 dark:divide-white/5">
						{ order.items?.map( ( it, idx ) => (
							<div key={ idx } id={ `raka-modal-item-${ idx }` } className="pt-3 first:pt-0 flex items-center justify-between gap-4">
								<div className="flex items-center gap-3">
									<img
										src={ it.image_url }
										alt={ it.name }
										className="w-12 h-12 rounded-xl object-cover border border-black/10 dark:border-white/10 bg-neutral-100 shrink-0"
									/>
									<div className="space-y-0.5">
										<div className="text-xs sm:text-sm font-semibold">{ it.name }</div>
										<p className="text-xs text-neutral-500">จำนวน: { it.quantity } x { it.subtotal }</p>
									</div>
								</div>
								<span className="text-xs sm:text-sm font-bold shrink-0">{ it.total }</span>
							</div>
						) ) }
					</div>
				</div>

				{ /* Cost Breakdown */ }
				<div id="raka-modal-cost-breakdown" className="p-4 rounded-2xl bg-neutral-50 dark:bg-neutral-800/50 space-y-2 text-xs sm:text-sm">
					<div className="flex justify-between text-neutral-600 dark:text-neutral-400">
						<span>ยอดรวมสินค้า:</span>
						<span>{ order.subtotal }</span>
					</div>
					<div className="flex justify-between text-neutral-600 dark:text-neutral-400">
						<span>ค่าจัดส่ง ({ order.shipping_method }):</span>
						<span>{ order.shipping_total || '0 ฿' }</span>
					</div>
					<div className="pt-2 border-t border-black/10 dark:border-white/10 flex justify-between text-sm sm:text-base font-bold text-neutral-950 dark:text-white">
						<span>ยอดรวมสุทธิ:</span>
						<span className="text-red-600 dark:text-red-400 font-black">{ order.total }</span>
					</div>
				</div>

				{ /* Shipping Address info */ }
				{ order.shipping_address && (
					<div id="raka-modal-address-info" className="p-4 rounded-2xl border border-black/5 dark:border-white/5 space-y-1.5 text-xs text-neutral-600 dark:text-neutral-400">
						<span className="font-bold text-neutral-900 dark:text-white block text-xs">ที่อยู่จัดส่งสินค้า:</span>
						<p>{ order.shipping_address.name }</p>
						<p>{ order.shipping_address.address } { order.shipping_address.city } { order.shipping_address.state } { order.shipping_address.postcode }</p>
						{ order.shipping_address.phone && <p>เบอร์โทร: { order.shipping_address.phone }</p> }
					</div>
				) }

				{ /* Footer Close */ }
				<div id="raka-modal-footer" className="flex justify-end pt-2">
					<button
						type="button"
						id="raka-modal-close-action-btn"
						onClick={ onClose }
						className="h-10 px-6 rounded-full bg-neutral-900 hover:bg-neutral-800 dark:bg-white dark:hover:bg-neutral-200 text-white dark:text-neutral-900 text-xs font-bold transition-all cursor-pointer"
					>
						ปิดหน้าต่าง
					</button>
				</div>
			</div>
		</div>
	);
}
