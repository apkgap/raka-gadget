export default function LogoutModal( { logoutUrl, onClose } ) {
	return (
		<div id="raka-logout-modal-backdrop" className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade-in font-hn">
			<div
				id="raka-logout-modal-content"
				className="w-full max-w-sm rounded-3xl bg-white dark:bg-neutral-900 border border-black/10 dark:border-white/10 shadow-2xl p-6 sm:p-7 space-y-5 text-center text-neutral-900 dark:text-white"
			>
				<div id="raka-logout-icon-wrap" className="w-14 h-14 mx-auto rounded-2xl bg-red-500/10 text-red-600 dark:text-red-400 flex items-center justify-center">
					<i id="raka-modal-logout-icon" className="ri-logout-box-r-line text-2xl"></i>
				</div>

				<div id="raka-logout-text-wrap" className="space-y-1.5">
					<h3 id="raka-logout-modal-title" className="text-base font-bold">
						ยืนยันการออกจากระบบ?
					</h3>
					<p id="raka-logout-modal-desc" className="text-xs text-neutral-500 leading-relaxed">
						คุณต้องการออกจากระบบบัญชีสมาชิก Raka Gadget ใช่หรือไม่
					</p>
				</div>

				<div id="raka-logout-actions" className="flex items-center gap-3 pt-2">
					<button
						type="button"
						id="raka-cancel-logout-btn"
						onClick={ onClose }
						className="w-1/2 h-11 rounded-full bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 text-xs font-semibold transition-all cursor-pointer"
					>
						ยกเลิก
					</button>
					<a
						id="raka-confirm-logout-btn"
						href={ logoutUrl }
						className="w-1/2 h-11 rounded-full bg-red-600 hover:bg-red-700 text-white text-xs font-bold transition-all shadow-sm flex items-center justify-center cursor-pointer"
					>
						ออกจากระบบ
					</a>
				</div>
			</div>
		</div>
	);
}
