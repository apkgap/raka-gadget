import { useState } from '@wordpress/element';

export default function AddressEditModal( { type, address, config, onClose, onSuccess } ) {
	const [ formData, setFormData ] = useState( {
		first_name: address.first_name || '',
		last_name: address.last_name || '',
		company: address.company || '',
		tax_id: address.tax_id || '',
		branch: address.branch || 'สำนักงานใหญ่',
		phone: address.phone || '',
		email: address.email || '',
		address_1: address.address_1 || '',
		address_2: address.address_2 || '',
		subdistrict: address.subdistrict || '',
		district: address.district || '',
		province: address.province || '',
		postcode: address.postcode || '',
	} );

	const [ saving, setSaving ] = useState( false );
	const [ error, setError ] = useState( '' );

	const handleChange = ( e ) => {
		const { name, value } = e.target;
		setFormData( ( prev ) => ( { ...prev, [ name ]: value } ) );
	};

	const handleSubmit = async ( e ) => {
		e.preventDefault();
		try {
			setSaving( true );
			setError( '' );

			const res = await fetch( `${ config.apiUrl }/addresses`, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': config.nonce,
				},
				body: JSON.stringify( {
					type,
					address: formData,
				} ),
			} );

			const data = await res.json();
			if ( res.ok && data.success ) {
				if ( onSuccess ) onSuccess();
			} else {
				setError( data.message || 'เกิดข้อผิดพลาดในการบันทึกที่อยู่' );
			}
		} catch ( err ) {
			setError( 'เกิดข้อผิดพลาดในการเชื่อมต่อ' );
		} finally {
			setSaving( false );
		}
	};

	const title = type === 'shipping' ? 'แก้ไขที่อยู่จัดส่งสินค้า' : 'แก้ไขข้อมูลที่อยู่ออกใบกำกับภาษี';

	return (
		<div id="raka-address-modal-backdrop" className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade-in font-hn">
			<div
				id="raka-address-modal-content"
				className="w-full max-w-xl max-h-[90vh] overflow-y-auto rounded-3xl bg-white dark:bg-neutral-900 border border-black/10 dark:border-white/10 shadow-2xl p-6 sm:p-8 space-y-5 text-neutral-900 dark:text-white"
			>
				<div id="raka-address-modal-header" className="flex items-center justify-between pb-3 border-b border-black/10 dark:border-white/10">
					<h3 id="raka-address-modal-title" className="text-base sm:text-lg font-bold">
						{ title }
					</h3>
					<button
						type="button"
						id="raka-close-address-modal-btn"
						onClick={ onClose }
						className="w-8 h-8 rounded-full bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 flex items-center justify-center text-neutral-500 hover:text-neutral-900 dark:hover:text-white transition-all cursor-pointer"
					>
						<i className="ri-close-line text-lg"></i>
					</button>
				</div>

				{ error && (
					<div id="raka-address-error-box" className="p-3 rounded-xl bg-red-500/10 border border-red-500/20 text-red-600 dark:text-red-400 text-xs">
						{ error }
					</div>
				) }

				<form id="raka-address-edit-form" onSubmit={ handleSubmit } className="space-y-4 text-xs sm:text-sm">
					<div className="grid grid-cols-2 gap-3">
						<div className="space-y-1">
							<label className="block text-xs font-semibold text-neutral-600 dark:text-neutral-400">ชื่อผู้รับ *</label>
							<input
								type="text"
								name="first_name"
								value={ formData.first_name }
								onChange={ handleChange }
								required
								className="w-full h-10 px-3.5 rounded-xl bg-neutral-100/80 dark:bg-neutral-800 border border-black/10 dark:border-white/10 text-xs sm:text-sm focus:outline-none focus:ring-1 focus:ring-black/20 dark:focus:ring-white/20 transition-all"
							/>
						</div>
						<div className="space-y-1">
							<label className="block text-xs font-semibold text-neutral-600 dark:text-neutral-400">นามสกุล *</label>
							<input
								type="text"
								name="last_name"
								value={ formData.last_name }
								onChange={ handleChange }
								required
								className="w-full h-10 px-3.5 rounded-xl bg-neutral-100/80 dark:bg-neutral-800 border border-black/10 dark:border-white/10 text-xs sm:text-sm focus:outline-none focus:ring-1 focus:ring-black/20 dark:focus:ring-white/20 transition-all"
							/>
						</div>
					</div>

					{ type === 'billing' && (
						<div className="grid grid-cols-2 gap-3">
							<div className="space-y-1">
								<label className="block text-xs font-semibold text-neutral-600 dark:text-neutral-400">ชื่อบริษัท / นิติบุคคล (ถ้ามี)</label>
								<input
									type="text"
									name="company"
									value={ formData.company }
									onChange={ handleChange }
									className="w-full h-10 px-3.5 rounded-xl bg-neutral-100/80 dark:bg-neutral-800 border border-black/10 dark:border-white/10 text-xs sm:text-sm focus:outline-none focus:ring-1 focus:ring-black/20 dark:focus:ring-white/20 transition-all"
								/>
							</div>
							<div className="space-y-1">
								<label className="block text-xs font-semibold text-neutral-600 dark:text-neutral-400">เลขประจำตัวผู้เสียภาษี (Tax ID)</label>
								<input
									type="text"
									name="tax_id"
									value={ formData.tax_id }
									onChange={ handleChange }
									placeholder="เลข 13 หลัก"
									className="w-full h-10 px-3.5 rounded-xl bg-neutral-100/80 dark:bg-neutral-800 border border-black/10 dark:border-white/10 text-xs sm:text-sm focus:outline-none focus:ring-1 focus:ring-black/20 dark:focus:ring-white/20 transition-all"
								/>
							</div>
						</div>
					) }

					<div className="space-y-1">
						<label className="block text-xs font-semibold text-neutral-600 dark:text-neutral-400">ที่อยู่ (เลขที่, อาคาร, หมู่บ้าน, ถนน) *</label>
						<input
							type="text"
							name="address_1"
							value={ formData.address_1 }
							onChange={ handleChange }
							required
							className="w-full h-10 px-3.5 rounded-xl bg-neutral-100/80 dark:bg-neutral-800 border border-black/10 dark:border-white/10 text-xs sm:text-sm focus:outline-none focus:ring-1 focus:ring-black/20 dark:focus:ring-white/20 transition-all"
						/>
					</div>

					<div className="grid grid-cols-2 sm:grid-cols-4 gap-3">
						<div className="space-y-1">
							<label className="block text-xs font-semibold text-neutral-600 dark:text-neutral-400">ตำบล / แขวง</label>
							<input
								type="text"
								name="subdistrict"
								value={ formData.subdistrict }
								onChange={ handleChange }
								className="w-full h-10 px-3 rounded-xl bg-neutral-100/80 dark:bg-neutral-800 border border-black/10 dark:border-white/10 text-xs sm:text-sm focus:outline-none focus:ring-1 focus:ring-black/20 dark:focus:ring-white/20 transition-all"
							/>
						</div>
						<div className="space-y-1">
							<label className="block text-xs font-semibold text-neutral-600 dark:text-neutral-400">อำเภอ / เขต</label>
							<input
								type="text"
								name="district"
								value={ formData.district }
								onChange={ handleChange }
								className="w-full h-10 px-3 rounded-xl bg-neutral-100/80 dark:bg-neutral-800 border border-black/10 dark:border-white/10 text-xs sm:text-sm focus:outline-none focus:ring-1 focus:ring-black/20 dark:focus:ring-white/20 transition-all"
							/>
						</div>
						<div className="space-y-1">
							<label className="block text-xs font-semibold text-neutral-600 dark:text-neutral-400">จังหวัด *</label>
							<input
								type="text"
								name="province"
								value={ formData.province }
								onChange={ handleChange }
								required
								className="w-full h-10 px-3 rounded-xl bg-neutral-100/80 dark:bg-neutral-800 border border-black/10 dark:border-white/10 text-xs sm:text-sm focus:outline-none focus:ring-1 focus:ring-black/20 dark:focus:ring-white/20 transition-all"
							/>
						</div>
						<div className="space-y-1">
							<label className="block text-xs font-semibold text-neutral-600 dark:text-neutral-400">รหัสไปรษณีย์ *</label>
							<input
								type="text"
								name="postcode"
								value={ formData.postcode }
								onChange={ handleChange }
								required
								maxLength={ 5 }
								className="w-full h-10 px-3 rounded-xl bg-neutral-100/80 dark:bg-neutral-800 border border-black/10 dark:border-white/10 text-xs sm:text-sm focus:outline-none focus:ring-1 focus:ring-black/20 dark:focus:ring-white/20 transition-all"
							/>
						</div>
					</div>

					<div className="grid grid-cols-2 gap-3">
						<div className="space-y-1">
							<label className="block text-xs font-semibold text-neutral-600 dark:text-neutral-400">เบอร์โทรศัพท์ติดต่อ *</label>
							<input
								type="tel"
								name="phone"
								value={ formData.phone }
								onChange={ handleChange }
								required
								className="w-full h-10 px-3.5 rounded-xl bg-neutral-100/80 dark:bg-neutral-800 border border-black/10 dark:border-white/10 text-xs sm:text-sm focus:outline-none focus:ring-1 focus:ring-black/20 dark:focus:ring-white/20 transition-all"
							/>
						</div>
						<div className="space-y-1">
							<label className="block text-xs font-semibold text-neutral-600 dark:text-neutral-400">อีเมล</label>
							<input
								type="email"
								name="email"
								value={ formData.email }
								onChange={ handleChange }
								className="w-full h-10 px-3.5 rounded-xl bg-neutral-100/80 dark:bg-neutral-800 border border-black/10 dark:border-white/10 text-xs sm:text-sm focus:outline-none focus:ring-1 focus:ring-black/20 dark:focus:ring-white/20 transition-all"
							/>
						</div>
					</div>

					<div className="flex items-center justify-end gap-3 pt-3 border-t border-black/10 dark:border-white/10">
						<button
							type="button"
							id="raka-cancel-address-btn"
							onClick={ onClose }
							className="h-10 px-5 rounded-full bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 text-xs font-semibold transition-all cursor-pointer"
						>
							ยกเลิก
						</button>
						<button
							type="submit"
							id="raka-save-address-btn"
							disabled={ saving }
							className="h-10 px-6 rounded-full bg-neutral-950 hover:bg-neutral-800 dark:bg-white dark:hover:bg-neutral-200 text-white dark:text-neutral-950 text-xs font-bold transition-all shadow-xs cursor-pointer disabled:opacity-50"
						>
							{ saving ? 'กำลังบันทึก...' : 'บันทึกที่อยู่' }
						</button>
					</div>
				</form>
			</div>
		</div>
	);
}
