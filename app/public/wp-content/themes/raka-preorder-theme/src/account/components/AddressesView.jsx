import { useState, useEffect } from '@wordpress/element';

export default function AddressesView( { config, addresses, onEditAddress, onRefreshAddresses, showToast } ) {
	const shipping = addresses?.shipping || {};
	const billing = addresses?.billing || {};

	const [ etaxEnabled, setEtaxEnabled ] = useState( billing.etax_enabled ?? false );
	const [ toggling, setToggling ] = useState( false );

	useEffect( () => {
		if ( billing.etax_enabled !== undefined ) {
			setEtaxEnabled( Boolean( billing.etax_enabled ) );
		}
	}, [ billing.etax_enabled ] );

	const handleToggleEtax = async () => {
		if ( toggling ) return;
		const nextState = ! etaxEnabled;
		setEtaxEnabled( nextState );

		try {
			setToggling( true );
			if ( config?.apiUrl ) {
				const res = await fetch( `${ config.apiUrl }/addresses`, {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-WP-Nonce': config.nonce,
					},
					body: JSON.stringify( {
						type: 'toggle_etax',
						etax_enabled: nextState,
					} ),
				} );

				const data = await res.json();
				if ( res.ok && data.success ) {
					if ( showToast ) {
						showToast( data.message || ( nextState ? 'เปิดใช้งานการขอใบกำกับภาษีเรียบร้อยแล้ว' : 'ปิดการขอใบกำกับภาษีเรียบร้อยแล้ว' ) );
					}
					if ( onRefreshAddresses ) {
						onRefreshAddresses();
					}
				} else {
					setEtaxEnabled( ! nextState );
					if ( showToast ) {
						showToast( data.message || 'เกิดข้อผิดพลาดในการบันทึกการตั้งค่า', 'error' );
					}
				}
			}
		} catch ( err ) {
			setEtaxEnabled( ! nextState );
			if ( showToast ) {
				showToast( 'เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error' );
			}
		} finally {
			setToggling( false );
		}
	};

	const formatAddressText = ( addr ) => {
		const parts = [
			addr.address_1,
			addr.address_2,
			addr.subdistrict && `ต./แขวง ${ addr.subdistrict }`,
			addr.district && `อ./เขต ${ addr.district }`,
			addr.province && `จ. ${ addr.province }`,
			addr.postcode,
		].filter( Boolean );

		return parts.length > 0 ? parts.join( ' ' ) : 'ยังไม่ได้ระบุที่อยู่';
	};

	return (
		<div id="raka-addresses-view" className="space-y-6">

			<div id="raka-addresses-header" className="space-y-1">
				<h2 id="raka-addresses-main-title" className="text-base sm:text-lg font-bold text-neutral-950 dark:text-white">
					สมุดที่อยู่ & ข้อมูลการออกใบกำกับภาษี
				</h2>
				<p id="raka-addresses-sub-title" className="text-xs text-neutral-500">
					จัดการที่อยู่สำหรับจัดส่งสินค้าและข้อมูลสำหรับออกใบเสร็จ / ใบกำกับภาษีเต็มรูปแบบ
				</p>
			</div>

			<div id="raka-addresses-grid" className="grid grid-cols-1 md:grid-cols-2 gap-6">

				{ /* ── 1. Shipping Address Card ── */ }
				<div id="raka-shipping-address-card" className="p-6 rounded-3xl bg-neutral-100/80 dark:bg-neutral-900/80 border border-black/5 dark:border-white/5 space-y-4 shadow-xs flex flex-col justify-between">
					<div id="raka-shipping-card-top" className="space-y-3">
						<div id="raka-shipping-title-row" className="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/5">
							<div id="raka-shipping-icon-wrap" className="flex items-center gap-2">
								<i id="raka-shipping-icon" className="ri-truck-line text-lg text-red-600 dark:text-red-500"></i>
								<h3 id="raka-shipping-card-title" className="text-sm font-bold text-neutral-950 dark:text-white">
									ที่อยู่จัดส่งสินค้า
								</h3>
							</div>
							<span id="raka-shipping-default-badge" className="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-red-600/10 text-red-600 dark:text-red-400">
								ค่าเริ่มต้น
							</span>
						</div>

						<div id="raka-shipping-details" className="space-y-2 text-xs sm:text-sm">
							<div id="raka-shipping-recipient-name" className="font-bold text-neutral-900 dark:text-white">
								{ shipping.first_name || shipping.last_name
									? `${ shipping.first_name || '' } ${ shipping.last_name || '' }`
									: 'ยังไม่ได้ระบุชื่อผู้รับ' }
							</div>
							<p id="raka-shipping-address-text" className="text-neutral-600 dark:text-neutral-300 leading-relaxed">
								{ formatAddressText( shipping ) }
							</p>
							<div id="raka-shipping-phone-row" className="text-neutral-500 pt-1">
								<i id="raka-shipping-phone-icon" className="ri-phone-line mr-1"></i>
								<span id="raka-shipping-phone-val">{ shipping.phone || 'ยังไม่ได้ระบุเบอร์โทร' }</span>
							</div>
						</div>
					</div>

					<button
						type="button"
						id="raka-edit-shipping-btn"
						onClick={ () => onEditAddress( 'shipping' ) }
						className="w-full mt-4 h-10 rounded-full bg-white dark:bg-neutral-800 hover:bg-neutral-50 dark:hover:bg-neutral-700 text-neutral-900 dark:text-white text-xs font-bold border border-black/10 dark:border-white/10 transition-all shadow-xs cursor-pointer flex items-center justify-center gap-2"
					>
						<i id="raka-edit-shipping-icon" className="ri-edit-line"></i>
						<span id="raka-edit-shipping-txt">แก้ไขที่อยู่จัดส่ง</span>
					</button>
				</div>

				{ /* ── 2. Billing & Tax Invoice Address Card ── */ }
				<div id="raka-billing-address-card" className="p-6 rounded-3xl bg-neutral-100/80 dark:bg-neutral-900/80 border border-black/5 dark:border-white/5 space-y-4 shadow-xs flex flex-col justify-between">
					<div id="raka-billing-card-top" className="space-y-3">
						<div id="raka-billing-title-row" className="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/5">
							<div id="raka-billing-icon-wrap" className="flex items-center gap-2">
								<i id="raka-billing-icon" className="ri-file-list-3-line text-lg text-red-600 dark:text-red-500"></i>
								<h3 id="raka-billing-card-title" className="text-sm font-bold text-neutral-950 dark:text-white">
									ที่อยู่ออกใบเสร็จ / ใบกำกับภาษี
								</h3>
							</div>

							{ /* ON/OFF Toggle Switch & Badge */ }
							<div id="raka-etax-toggle-wrapper" className="flex items-center gap-2.5">
								<span
									id="raka-billing-badge"
									className={ `text-[10px] font-semibold px-2 py-0.5 rounded-full transition-all ${
										addresses?.etax_system_enabled === false
											? 'bg-neutral-200 dark:bg-neutral-800 text-neutral-500 dark:text-neutral-400'
											: etaxEnabled
												? 'bg-red-600/10 text-red-600 dark:text-red-400'
												: 'bg-neutral-200 dark:bg-neutral-800 text-neutral-500 dark:text-neutral-400'
									}` }
								>
									{ addresses?.etax_system_enabled === false ? 'ปิดบริการ' : etaxEnabled ? 'e-Tax Invoice' : 'ปิดใช้งาน' }
								</span>

								{ /* Toggle Switch Button */ }
								<button
									type="button"
									role="switch"
									aria-checked={ etaxEnabled && addresses?.etax_system_enabled !== false }
									id="raka-etax-toggle-btn"
									onClick={ handleToggleEtax }
									disabled={ toggling || addresses?.etax_system_enabled === false }
									className={ `relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none ${
										addresses?.etax_system_enabled === false
											? 'bg-neutral-300 dark:bg-neutral-700 opacity-50 cursor-not-allowed'
											: etaxEnabled
												? 'bg-red-600'
												: 'bg-neutral-300 dark:bg-neutral-700'
									} ${ toggling ? 'opacity-60 cursor-not-allowed' : '' }` }
									title={ addresses?.etax_system_enabled === false ? 'ระบบปิดให้บริการโดยผู้ดูแลระบบ' : etaxEnabled ? 'คลิกเพื่อปิดใช้งาน' : 'คลิกเพื่อเปิดใช้งาน' }
								>
									<span id="raka-etax-toggle-sr" className="sr-only">เปิด/ปิด ใบกำกับภาษี</span>
									<span
										id="raka-etax-toggle-knob"
										className={ `pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-md ring-0 transition duration-200 ease-in-out ${
											etaxEnabled && addresses?.etax_system_enabled !== false ? 'translate-x-5' : 'translate-x-0'
										}` }
									/>
								</button>
							</div>
						</div>

						{ addresses?.etax_system_enabled === false ? (
							<div id="raka-billing-admin-disabled-state" className="py-4 space-y-1.5 text-xs text-neutral-500">
								<div id="raka-billing-admin-disabled-row" className="flex items-center gap-1.5 text-amber-600 dark:text-amber-400 font-medium">
									<i id="raka-billing-admin-disabled-icon" className="ri-error-warning-line text-sm"></i>
									<span id="raka-billing-admin-disabled-title">ระบบขอใบกำกับภาษีปิดให้บริการชั่วคราว</span>
								</div>
								<p id="raka-billing-admin-disabled-desc" className="text-[11px] leading-relaxed">
									ผู้ดูแลระบบได้ปิดการให้บริการขอใบกำกับภาษี e-Tax Invoice ไว้ชั่วคราว หากต้องการสอบถามเพิ่มเติมสามารถติดต่อฝ่ายบริการลูกค้า
								</p>
							</div>
						) : etaxEnabled ? (
							<div id="raka-billing-details" className="space-y-2 text-xs sm:text-sm">
								<div id="raka-billing-recipient-name" className="font-bold text-neutral-900 dark:text-white">
									{ billing.company ? (
										<span id="raka-billing-company-span">{ billing.company } ({ billing.branch || 'สำนักงานใหญ่' })</span>
									) : (
										<span id="raka-billing-person-span">{ billing.first_name || billing.last_name ? `${ billing.first_name || '' } ${ billing.last_name || '' }` : 'ยังไม่ได้ระบุชื่อ' }</span>
									) }
								</div>
								{ billing.tax_id && (
									<p id="raka-billing-taxid-row" className="text-xs text-neutral-500">
										เลขประจำตัวผู้เสียภาษี: <span id="raka-billing-taxid-val" className="font-mono font-semibold text-neutral-800 dark:text-neutral-200">{ billing.tax_id }</span>
									</p>
								) }
								<p id="raka-billing-address-text" className="text-neutral-600 dark:text-neutral-300 leading-relaxed">
									{ formatAddressText( billing ) }
								</p>
								<div id="raka-billing-contact-row" className="text-neutral-500 pt-1 flex flex-wrap gap-x-4 gap-y-1">
									<span id="raka-billing-phone-span"><i id="raka-billing-phone-icon" className="ri-phone-line mr-1"></i>{ billing.phone || '-' }</span>
									<span id="raka-billing-email-span"><i id="raka-billing-email-icon" className="ri-mail-line mr-1"></i>{ billing.email || '-' }</span>
								</div>
							</div>
						) : (
							<div id="raka-billing-disabled-state" className="py-4 space-y-1.5 text-xs text-neutral-500">
								<div id="raka-billing-disabled-badge-row" className="flex items-center gap-1.5 text-neutral-600 dark:text-neutral-400 font-medium">
									<i id="raka-billing-disabled-icon" className="ri-information-line text-sm"></i>
									<span id="raka-billing-disabled-title">ปิดการขอใบกำกับภาษี e-Tax</span>
								</div>
								<p id="raka-billing-disabled-desc" className="text-[11px] leading-relaxed">
									หากคุณต้องการขอใบกำกับภาษีเต็มรูปแบบ สามารถกดเปิดสวิตช์ด้านบนเพื่อเปิดใช้งานข้อมูลใบกำกับภาษี
								</p>
							</div>
						) }
					</div>

					<button
						type="button"
						id="raka-edit-billing-btn"
						onClick={ () => onEditAddress( 'billing' ) }
						className="w-full mt-4 h-10 rounded-full bg-white dark:bg-neutral-800 hover:bg-neutral-50 dark:hover:bg-neutral-700 text-neutral-900 dark:text-white text-xs font-bold border border-black/10 dark:border-white/10 transition-all shadow-xs cursor-pointer flex items-center justify-center gap-2"
					>
						<i id="raka-edit-billing-icon" className="ri-edit-line"></i>
						<span id="raka-edit-billing-txt">แก้ไขข้อมูลใบกำกับภาษี</span>
					</button>
				</div>

			</div>

		</div>
	);
}
