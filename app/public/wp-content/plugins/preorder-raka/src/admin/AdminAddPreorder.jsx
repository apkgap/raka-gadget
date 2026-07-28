import React, { useState, useEffect } from 'react';

const generateRandomOrderNo = () => {
	const randNum = Math.floor(100000 + Math.random() * 900000);
	return `RAKA-PRE-${randNum}`;
};

const AdminAddPreorder = () => {
	const settings = window.preorderRakaAdminSettings || {
		root: '/wp-json/',
		nonce: '',
		adminUrl: '/wp-admin/edit.php?post_type=preorder'
	};

	const [formData, setFormData] = useState({
		order_no: generateRandomOrderNo(),
		customer_name: '',
		phone: '',
		citizen_id: '',
		product_id: '',
		product_name: '',
		price: '',
		qty: 1,
		shipping: 0,
		estimate_days: '7-14 วัน',
		address: '',
		courier: 'kerry',
		tracking_no: '',
		tracking_link: '',
		facebook: '',
		line: '',
	});

	const [products, setProducts] = useState([]);
	const [loadingProds, setLoadingProds] = useState(false);
	const [submitting, setSubmitting] = useState(false);
	const [resultMsg, setResultMsg] = useState(null);
	const [createdData, setCreatedData] = useState(null);

	// Fetch available Pre-order Products for select dropdown
	useEffect(() => {
		const fetchProducts = async () => {
			setLoadingProds(true);
			try {
				const baseUrl = `${settings.root.replace(/\/$/, '')}/products`;
				const res = await fetch(baseUrl, {
					headers: { 'X-WP-Nonce': settings.nonce }
				});
				if (res.ok) {
					const json = await res.json();
					if (json.success && Array.isArray(json.data)) {
						setProducts(json.data);
					}
				}
			} catch (e) {
				console.error('Failed to load products', e);
			} finally {
				setLoadingProds(false);
			}
		};
		fetchProducts();
	}, []);

	// Handle product change
	const handleProductChange = (e) => {
		const selectedId = e.target.value;
		if (!selectedId) {
			setFormData(prev => ({ ...prev, product_id: '', product_name: '', price: '' }));
			return;
		}
		const prod = products.find(p => p.id === parseInt(selectedId, 10));
		if (prod) {
			setFormData(prev => ({
				...prev,
				product_id: prod.id,
				product_name: prod.title || '',
				price: prod.price ? parseInt(prod.price, 10) : prev.price,
			}));
		}
	};

	const handleChange = (field, value) => {
		setFormData(prev => ({ ...prev, [field]: value }));
	};

	const handleRegenerateOrderNo = () => {
		setFormData(prev => ({ ...prev, order_no: generateRandomOrderNo() }));
	};

	const handleSubmit = async (e) => {
		e.preventDefault();
		setSubmitting(true);
		setResultMsg(null);
		setCreatedData(null);

		try {
			const baseUrl = `${settings.root.replace(/\/$/, '')}/create`;
			const res = await fetch(baseUrl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': settings.nonce,
				},
				body: JSON.stringify(formData),
			});
			const data = await res.json();

			if (!res.ok || !data.success) {
				throw new Error(data.message || 'เกิดข้อผิดพลาดในการบันทึกรายการ');
			}

			setCreatedData(data.data);
			setResultMsg({ type: 'success', text: `บันทึกรายการพรีออเดอร์ ${data.data.order_no} สำเร็จเรียบร้อย!` });

			// Reset form for next entry with new random order_no
			setFormData({
				order_no: generateRandomOrderNo(),
				customer_name: '',
				phone: '',
				citizen_id: '',
				product_id: '',
				product_name: '',
				price: '',
				qty: 1,
				shipping: 0,
				estimate_days: '7-14 วัน',
				address: '',
				courier: 'kerry',
				tracking_no: '',
				tracking_link: '',
				facebook: '',
				line: '',
			});
		} catch (err) {
			setResultMsg({ type: 'error', text: err.message });
		} finally {
			setSubmitting(false);
		}
	};

	return (
		<div style={{ maxWidth: 960, margin: '20px 0', fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif' }}>
			{/* Admin Header Banner */}
			<div style={{
				padding: '24px 30px', borderRadius: 16, marginBottom: 24,
				background: 'linear-gradient(135deg, rgb(225, 29, 72), rgb(159, 18, 57))',
				color: '#ffffff', boxShadow: '0 8px 24px rgba(225, 29, 72, 0.25)',
				display: 'flex', alignItems: 'center', justifyContent: 'space-between', flexWrap: 'wrap', gap: 16
			}}>
				<div>
					<h1 style={{ margin: '0 0 6px', fontSize: 24, fontWeight: 500, color: '#ffffff', display: 'flex', alignItems: 'center', gap: 10 }}>
						<i className="ri-add-circle-fill" style={{ fontSize: 26 }}/>
						เพิ่มรายการพรีออเดอร์ใหม่
					</h1>
					<p style={{ margin: 0, fontSize: 14, color: 'rgba(255, 255, 255, 0.85)', fontWeight: 400 }}>
						ระบบออกเลขพรีออเดอร์อัตโนมัติ สะดวก รวดเร็ว เป็นทางการ
					</p>
				</div>
				<a href={settings.adminUrl} className="button button-secondary" style={{
					padding: '6px 16px', borderRadius: 9999, height: 'auto',
					background: 'rgba(255, 255, 255, 0.2)', color: '#ffffff',
					border: '1px solid rgba(255, 255, 255, 0.3)', fontWeight: 500, textDecoration: 'none'
				}}>
					<i className="ri-arrow-left-line" style={{ marginRight: 4 }}/> กลับหน้ารายการทั้งหมด
				</a>
			</div>

			{/* Success Alert Banner */}
			{resultMsg && (
				<div style={{
					padding: '16px 20px', borderRadius: 12, marginBottom: 24, fontSize: 14, fontWeight: 500,
					background: resultMsg.type === 'success' ? '#f0fdf4' : '#fef2f2',
					border: `1px solid ${resultMsg.type === 'success' ? '#bbf7d0' : '#fecaca'}`,
					color: resultMsg.type === 'success' ? '#15803d' : '#dc2626',
					display: 'flex', alignItems: 'center', justifyContent: 'space-between'
				}}>
					<div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
						<i className={resultMsg.type === 'success' ? 'ri-checkbox-circle-fill' : 'ri-error-warning-fill'} style={{ fontSize: 18 }}/>
						<span>{resultMsg.text}</span>
					</div>
					{createdData && (
						<div style={{ display: 'flex', gap: 8 }}>
							<a href={createdData.edit_url} target="_blank" rel="noreferrer" className="button button-small">
								แก้ไขในระบบ WP
							</a>
							<a href={createdData.view_url} target="_blank" rel="noreferrer" className="button button-small button-primary">
								ดูหน้าติดตามลูกค้า
							</a>
						</div>
					)}
				</div>
			)}

			{/* Main Form */}
			<form onSubmit={handleSubmit} style={{ background: '#ffffff', padding: 30, borderRadius: 16, border: '1px solid #e5e7eb', boxShadow: '0 4px 12px rgba(0,0,0,0.03)' }}>

				{/* SECTION 1: Order Meta & Customer Info */}
				<div style={{ marginBottom: 28 }}>
					<h2 style={{ fontSize: 16, fontWeight: 500, margin: '0 0 16px', color: '#111827', borderBottom: '2px solid #f3f4f6', paddingBottom: 8, display: 'flex', alignItems: 'center', gap: 8 }}>
						<i className="ri-user-star-line" style={{ color: '#e11d48' }}/> 1. ข้อมูลคำสั่งซื้อและลูกค้า
					</h2>

					<div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(280px, 1fr))', gap: 16 }}>
						{/* Auto Order No */}
						<div>
							<label style={{ display: 'block', fontSize: 13, fontWeight: 500, color: '#374151', marginBottom: 6 }}>
								เลขพรีออเดอร์ (ระบบสร้างให้อัตโนมัติ)
							</label>
							<div style={{ display: 'flex', gap: 8 }}>
								<input
									type="text"
									value={formData.order_no}
									onChange={(e) => handleChange('order_no', e.target.value)}
									style={{ flex: 1, padding: '8px 12px', borderRadius: 8, border: '1px solid #d1d5db', fontWeight: 500, background: '#f9fafb' }}
									required
								/>
								<button
									type="button"
									onClick={handleRegenerateOrderNo}
									title="สุ่มเลขใหม่"
									style={{ padding: '8px 12px', borderRadius: 8, border: '1px solid #d1d5db', background: '#ffffff', cursor: 'pointer' }}
								>
									<i className="ri-refresh-line"/>
								</button>
							</div>
						</div>

						{/* Customer Name */}
						<div>
							<label style={{ display: 'block', fontSize: 13, fontWeight: 500, color: '#374151', marginBottom: 6 }}>
								ชื่อลูกค้า (ระบุเพื่อจัดเก็บอ้างอิง)
							</label>
							<input
								type="text"
								placeholder="เช่น คุณสมชาย ใจดี"
								value={formData.customer_name}
								onChange={(e) => handleChange('customer_name', e.target.value)}
								style={{ width: '100%', padding: '8px 12px', borderRadius: 8, border: '1px solid #d1d5db' }}
							/>
						</div>

						{/* Phone */}
						<div>
							<label style={{ display: 'block', fontSize: 13, fontWeight: 500, color: '#374151', marginBottom: 6 }}>
								เบอร์โทรศัพท์ (ใช้ยืนยันตัวตน)
							</label>
							<input
								type="tel"
								placeholder="0897777777"
								value={formData.phone}
								onChange={(e) => handleChange('phone', e.target.value)}
								style={{ width: '100%', padding: '8px 12px', borderRadius: 8, border: '1px solid #d1d5db' }}
								required
							/>
						</div>

						{/* Citizen ID */}
						<div>
							<label style={{ display: 'block', fontSize: 13, fontWeight: 500, color: '#374151', marginBottom: 6 }}>
								เลขบัตรประชาชน 4 ตัวท้าย (ถ้ามี)
							</label>
							<input
								type="text"
								maxLength={4}
								placeholder="7777"
								value={formData.citizen_id}
								onChange={(e) => handleChange('citizen_id', e.target.value)}
								style={{ width: '100%', padding: '8px 12px', borderRadius: 8, border: '1px solid #d1d5db' }}
							/>
						</div>
					</div>
				</div>

				{/* SECTION 2: Product & Pricing Info */}
				<div style={{ marginBottom: 28 }}>
					<h2 style={{ fontSize: 16, fontWeight: 500, margin: '0 0 16px', color: '#111827', borderBottom: '2px solid #f3f4f6', paddingBottom: 8, display: 'flex', alignItems: 'center', gap: 8 }}>
						<i className="ri-shopping-bag-3-line" style={{ color: '#e11d48' }}/> 2. รายละเอียดสินค้าและราคา
					</h2>

					<div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(280px, 1fr))', gap: 16 }}>
						{/* Product Select */}
						<div>
							<label style={{ display: 'block', fontSize: 13, fontWeight: 500, color: '#374151', marginBottom: 6 }}>
								เลือกสินค้าพรีออเดอร์ (จากระบบ)
							</label>
							<select
								value={formData.product_id}
								onChange={handleProductChange}
								style={{ width: '100%', padding: '8px 12px', borderRadius: 8, border: '1px solid #d1d5db', background: '#ffffff' }}
							>
								<option value="">-- กำหนดชื่อสินค้าเอง / ไม่เลือกจากระบบ --</option>
								{products.map(p => (
									<option key={p.id} value={p.id}>{p.title}</option>
								))}
							</select>
						</div>

						{/* Product Name */}
						<div>
							<label style={{ display: 'block', fontSize: 13, fontWeight: 500, color: '#374151', marginBottom: 6 }}>
								ชื่อสินค้า / แคมเปญ
							</label>
							<input
								type="text"
								placeholder="ระบุชื่อสินค้าพรีออเดอร์"
								value={formData.product_name}
								onChange={(e) => handleChange('product_name', e.target.value)}
								style={{ width: '100%', padding: '8px 12px', borderRadius: 8, border: '1px solid #d1d5db' }}
								required
							/>
						</div>

						{/* Price */}
						<div>
							<label style={{ display: 'block', fontSize: 13, fontWeight: 500, color: '#374151', marginBottom: 6 }}>
								ราคาสินค้าต่อชิ้น (บาท)
							</label>
							<input
								type="number"
								placeholder="55900"
								value={formData.price}
								onChange={(e) => handleChange('price', e.target.value)}
								style={{ width: '100%', padding: '8px 12px', borderRadius: 8, border: '1px solid #d1d5db' }}
								required
							/>
						</div>

						{/* Qty */}
						<div>
							<label style={{ display: 'block', fontSize: 13, fontWeight: 500, color: '#374151', marginBottom: 6 }}>
								จำนวน (ชิ้น)
							</label>
							<input
								type="number"
								min={1}
								value={formData.qty}
								onChange={(e) => handleChange('qty', e.target.value)}
								style={{ width: '100%', padding: '8px 12px', borderRadius: 8, border: '1px solid #d1d5db' }}
								required
							/>
						</div>

						{/* Shipping Fee */}
						<div>
							<label style={{ display: 'block', fontSize: 13, fontWeight: 500, color: '#374151', marginBottom: 6 }}>
								ค่าจัดส่งในไทย (บาท)
							</label>
							<input
								type="number"
								min={0}
								value={formData.shipping}
								onChange={(e) => handleChange('shipping', e.target.value)}
								style={{ width: '100%', padding: '8px 12px', borderRadius: 8, border: '1px solid #d1d5db' }}
							/>
						</div>

						{/* Estimate Days */}
						<div>
							<label style={{ display: 'block', fontSize: 13, fontWeight: 500, color: '#374151', marginBottom: 6 }}>
								ระยะเวลาจัดส่งโดยประมาณ
							</label>
							<select
								value={formData.estimate_days}
								onChange={(e) => handleChange('estimate_days', e.target.value)}
								style={{ width: '100%', padding: '8px 12px', borderRadius: 8, border: '1px solid #d1d5db', background: '#ffffff' }}
							>
								<option value="3-5 วัน">3-5 วัน</option>
								<option value="7-14 วัน">7-14 วัน (มาตรฐาน)</option>
								<option value="15-20 วัน">15-20 วัน</option>
								<option value="20-30 วัน">20-30 วัน</option>
								<option value="30-45 วัน">30-45 วัน</option>
							</select>
						</div>

						{/* Total Calculated Preview */}
						<div style={{ background: '#fcf0f2', padding: 12, borderRadius: 8, border: '1px solid #fecaca', display: 'flex', flexDirection: 'column', justifyContent: 'center' }}>
							<span style={{ fontSize: 12, color: '#9f1239', fontWeight: 500 }}>ราคารวมคำนวณสุทธิ</span>
							<span style={{ fontSize: 20, color: '#e11d48', fontWeight: 500 }}>
								{((parseInt(formData.price || 0, 10) * parseInt(formData.qty || 1, 10)) + parseInt(formData.shipping || 0, 10)).toLocaleString()} บาท
							</span>
						</div>
					</div>
				</div>

				{/* SECTION 3: Shipping & Tracking Info */}
				<div style={{ marginBottom: 28 }}>
					<h2 style={{ fontSize: 16, fontWeight: 500, margin: '0 0 16px', color: '#111827', borderBottom: '2px solid #f3f4f6', paddingBottom: 8, display: 'flex', alignItems: 'center', gap: 8 }}>
						<i className="ri-truck-line" style={{ color: '#e11d48' }}/> 3. การจัดส่งและพัสดุ
					</h2>

					<div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(280px, 1fr))', gap: 16, marginBottom: 16 }}>
						{/* Courier */}
						<div>
							<label style={{ display: 'block', fontSize: 13, fontWeight: 500, color: '#374151', marginBottom: 6 }}>
								บริษัทขนส่งในไทย
							</label>
							<select
								value={formData.courier}
								onChange={(e) => handleChange('courier', e.target.value)}
								style={{ width: '100%', padding: '8px 12px', borderRadius: 8, border: '1px solid #d1d5db', background: '#ffffff' }}
							>
								<option value="kerry">Kerry Express (เคอรี่ เอ็กซ์เพรส)</option>
								<option value="flash">Flash Express (แฟลช เอ็กซ์เพรส)</option>
								<option value="jt">J&T Express (เจแอนด์ที เอ็กซ์เพรส)</option>
								<option value="thp">ไปรษณีย์ไทย (EMS / Thailand Post)</option>
								<option value="spx">Shopee Xpress (SPX)</option>
								<option value="ninja">Ninja Van (นินจาแวน)</option>
								<option value="other">ขนส่งเอกชนอื่น ๆ</option>
							</select>
						</div>

						{/* Tracking No */}
						<div>
							<label style={{ display: 'block', fontSize: 13, fontWeight: 500, color: '#374151', marginBottom: 6 }}>
								หมายเลขพัสดุ (Tracking No.)
							</label>
							<input
								type="text"
								placeholder="เช่น TH8829481947"
								value={formData.tracking_no}
								onChange={(e) => handleChange('tracking_no', e.target.value)}
								style={{ width: '100%', padding: '8px 12px', borderRadius: 8, border: '1px solid #d1d5db' }}
							/>
						</div>

						{/* Tracking Link */}
						<div>
							<label style={{ display: 'block', fontSize: 13, fontWeight: 500, color: '#374151', marginBottom: 6 }}>
								ลิงก์เช็คพัสดุ (Tracking URL)
							</label>
							<input
								type="url"
								placeholder="https://th.kerryexpress.com/th/track/?track=..."
								value={formData.tracking_link}
								onChange={(e) => handleChange('tracking_link', e.target.value)}
								style={{ width: '100%', padding: '8px 12px', borderRadius: 8, border: '1px solid #d1d5db' }}
							/>
						</div>

						{/* Facebook Link */}
						<div>
							<label style={{ display: 'block', fontSize: 13, fontWeight: 500, color: '#374151', marginBottom: 6 }}>
								ลิงก์ Facebook / Messenger
							</label>
							<input
								type="url"
								placeholder="https://m.me/yourfanpage"
								value={formData.facebook}
								onChange={(e) => handleChange('facebook', e.target.value)}
								style={{ width: '100%', padding: '8px 12px', borderRadius: 8, border: '1px solid #d1d5db' }}
							/>
						</div>

						{/* LINE Link */}
						<div>
							<label style={{ display: 'block', fontSize: 13, fontWeight: 500, color: '#374151', marginBottom: 6 }}>
								ลิงก์ LINE Official / LINE ID
							</label>
							<input
								type="url"
								placeholder="https://line.me/ti/p/@yourlineid"
								value={formData.line}
								onChange={(e) => handleChange('line', e.target.value)}
								style={{ width: '100%', padding: '8px 12px', borderRadius: 8, border: '1px solid #d1d5db' }}
							/>
						</div>
					</div>

					{/* Address */}
					<div>
						<label style={{ display: 'block', fontSize: 13, fontWeight: 500, color: '#374151', marginBottom: 6 }}>
							ที่อยู่จัดส่งสินค้า
						</label>
						<textarea
							rows={3}
							placeholder="ระบุที่อยู่จัดส่งของลูกค้า"
							value={formData.address}
							onChange={(e) => handleChange('address', e.target.value)}
							style={{ width: '100%', padding: '8px 12px', borderRadius: 8, border: '1px solid #d1d5db' }}
						/>
					</div>
				</div>

				{/* Submit Button */}
				<div style={{ textAlign: 'right', borderTop: '1px solid #f3f4f6', paddingTop: 20 }}>
					<button
						type="submit"
						disabled={submitting}
						style={{
							padding: '12px 32px', borderRadius: 9999, border: 'none',
							background: submitting ? '#9ca3af' : 'linear-gradient(135deg, rgb(225, 29, 72), rgb(159, 18, 57))',
							color: '#ffffff', fontSize: 15, fontWeight: 500, cursor: submitting ? 'not-allowed' : 'pointer',
							boxShadow: '0 4px 14px rgba(225, 29, 72, 0.3)', display: 'inline-flex', alignItems: 'center', gap: 8
						}}
					>
						<i className={submitting ? 'ri-loader-4-line' : 'ri-save-line'} style={{ fontSize: 18 }}/>
						{submitting ? 'กำลังบันทึกพรีออเดอร์...' : 'บันทึกสร้างรายการพรีออเดอร์'}
					</button>
				</div>
			</form>
		</div>
	);
};

export default AdminAddPreorder;
