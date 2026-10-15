import React, { useState, useEffect } from 'react';
import SearchableSelect from './SearchableSelect';

const generateRandomOrderNo = () => {
	const randNum = Math.floor(100000 + Math.random() * 900000);
	return `RAKA-PRE-${randNum}`;
};

const AdminAddPreorder = () => {
	const settings = window.preorderRakaAdminSettings || {
		root: '/wp-json/preorder-raka/v1',
		nonce: '',
		adminUrl: '/wp-admin/edit.php?post_type=preorder'
	};

	const [formData, setFormData] = useState({
		order_no: generateRandomOrderNo(),
		customer_name: '',
		phone: '',
		email: '',
		citizen_id: '',
		wc_order_id: '',
		user_id: '',
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

	const [items, setItems] = useState([]);
	const [selectedImage, setSelectedImage] = useState('');
	const [products, setProducts] = useState([]);
	const [wcOrders, setWcOrders] = useState([]);
	const [users, setUsers] = useState([]);
	const [loadingProds, setLoadingProds] = useState(false);
	const [loadingOrders, setLoadingOrders] = useState(false);
	const [loadingUsers, setLoadingUsers] = useState(false);
	const [submitting, setSubmitting] = useState(false);
	const [resultMsg, setResultMsg] = useState(null);
	const [createdData, setCreatedData] = useState(null);
	const [importMode, setImportMode] = useState('wc_order'); // 'wc_order' | 'wc_product' | 'custom'

	// 1. Fetch Products & Variations
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

	// 2. Fetch WooCommerce Recent Orders
	const fetchWcOrders = async () => {
		setLoadingOrders(true);
		try {
			const baseUrl = `${settings.root.replace(/\/$/, '')}/wc-orders`;
			const res = await fetch(baseUrl, {
				headers: { 'X-WP-Nonce': settings.nonce }
			});
			if (res.ok) {
				const json = await res.json();
				if (json.success && Array.isArray(json.data)) {
					setWcOrders(json.data);
				}
			}
		} catch (e) {
			console.error('Failed to load WooCommerce orders', e);
		} finally {
			setLoadingOrders(false);
		}
	};

	// 3. Fetch Registered Members
	const fetchUsers = async () => {
		setLoadingUsers(true);
		try {
			const baseUrl = `${settings.root.replace(/\/$/, '')}/users`;
			const res = await fetch(baseUrl, {
				headers: { 'X-WP-Nonce': settings.nonce }
			});
			if (res.ok) {
				const json = await res.json();
				if (json.success && Array.isArray(json.data)) {
					setUsers(json.data);
				}
			}
		} catch (e) {
			console.error('Failed to load users', e);
		} finally {
			setLoadingUsers(false);
		}
	};

	useEffect(() => {
		fetchProducts();
		fetchWcOrders();
		fetchUsers();
	}, []);

	// Handle selection from WooCommerce Order
	const handleSelectWcOrder = (orderId) => {
		if (!orderId) {
			setFormData(prev => ({
				...prev,
				wc_order_id: '',
			}));
			setItems([]);
			return;
		}

		const order = wcOrders.find(o => o.id === parseInt(orderId, 10));
		if (order) {
			// Extract all items from WC order
			const orderItems = (order.items && order.items.length > 0)
				? order.items.map(it => ({
						product_id: it.product_id,
						product_name: it.name,
						price: Math.round(it.price || 0),
						qty: it.qty || 1,
						image_url: it.thumb_url || '',
						estimate_days: it.eta || '7-14 วัน',
				  }))
				: [];

			setItems(orderItems);

			const firstItem = orderItems[0] || null;
			const pName = orderItems.length > 1
				? `${firstItem.product_name} (และสินค้าอื่นอีก ${orderItems.length - 1} รายการ)`
				: (firstItem ? firstItem.product_name : `คำสั่งซื้อ #${order.order_no}`);

			setFormData(prev => ({
				...prev,
				wc_order_id: order.id,
				order_no: order.order_no ? `RAKA-PRE-${order.order_no}` : prev.order_no,
				customer_name: order.customer_name || prev.customer_name,
				phone: order.phone || prev.phone,
				email: order.email || prev.email,
				address: order.address || prev.address,
				user_id: order.user_id ? String(order.user_id) : prev.user_id,
				product_id: firstItem ? String(firstItem.product_id) : prev.product_id,
				product_name: pName,
				price: firstItem ? firstItem.price : Math.round(order.total),
				qty: firstItem ? firstItem.qty : 1,
				shipping: Math.round(order.shipping || 0),
				estimate_days: firstItem && firstItem.estimate_days ? firstItem.estimate_days : '7-14 วัน',
			}));

			if (firstItem && firstItem.image_url) {
				setSelectedImage(firstItem.image_url);
			}
		}
	};

	// Add item to multi-product list from product dropdown
	const handleAddProductItem = (selectedId) => {
		if (!selectedId) return;
		const prod = products.find(p => p.id === parseInt(selectedId, 10));
		if (!prod) return;

		const newItem = {
			product_id: prod.id,
			product_name: prod.title || '',
			price: prod.price ? Math.round(prod.price) : 0,
			qty: 1,
			image_url: prod.image_url || '',
			estimate_days: prod.eta || '7-14 วัน',
		};

		setItems(prev => {
			const updated = [...prev, newItem];
			// Sync primary form field with first item
			if (updated.length === 1) {
				setFormData(f => ({
					...f,
					product_id: newItem.product_id,
					product_name: newItem.product_name,
					price: newItem.price,
					qty: newItem.qty,
					estimate_days: newItem.estimate_days,
				}));
				setSelectedImage(newItem.image_url);
			} else {
				setFormData(f => ({
					...f,
					product_name: `${updated[0].product_name} (และอีก ${updated.length - 1} รายการ)`,
				}));
			}
			return updated;
		});
	};

	// Add manual custom item
	const handleAddCustomItem = () => {
		const newItem = {
			product_id: 0,
			product_name: 'สินค้าพรีออเดอร์ใหม่',
			price: 0,
			qty: 1,
			image_url: '',
			estimate_days: '7-14 วัน',
		};
		setItems(prev => [...prev, newItem]);
	};

	// Remove item from multi-product list
	const handleRemoveItem = (index) => {
		setItems(prev => {
			const updated = prev.filter((_, idx) => idx !== index);
			if (updated.length > 0) {
				setFormData(f => ({
					...f,
					product_id: updated[0].product_id,
					product_name: updated.length > 1
						? `${updated[0].product_name} (และอีก ${updated.length - 1} รายการ)`
						: updated[0].product_name,
					price: updated[0].price,
					qty: updated[0].qty,
				}));
				setSelectedImage(updated[0].image_url || '');
			} else {
				setFormData(f => ({
					...f,
					product_id: '',
					product_name: '',
					price: '',
					qty: 1,
				}));
				setSelectedImage('');
			}
			return updated;
		});
	};

	// Update specific item in items list
	const handleUpdateItem = (index, field, val) => {
		setItems(prev => {
			const updated = [...prev];
			updated[index] = { ...updated[index], [field]: val };
			// keep formData sync
			if (index === 0 && (field === 'price' || field === 'qty' || field === 'product_name')) {
				setFormData(f => ({
					...f,
					[field === 'product_name' ? 'product_name' : field]: val,
				}));
			}
			return updated;
		});
	};

	// Handle user member change
	const handleUserChange = (val) => {
		const uid = (val && typeof val === 'object' && val.target) ? val.target.value : val;
		if (!uid) {
			setFormData(prev => ({ ...prev, user_id: '' }));
			return;
		}
		const u = users.find(usr => usr.id === parseInt(uid, 10));
		if (u) {
			setFormData(prev => ({
				...prev,
				user_id: u.id,
				customer_name: prev.customer_name || u.name,
				phone: prev.phone || u.phone,
				email: prev.email || u.email,
			}));
		}
	};

	const handleChange = (field, value) => {
		setFormData(prev => ({ ...prev, [field]: value }));
	};

	const handleRegenerateOrderNo = () => {
		setFormData(prev => ({ ...prev, order_no: generateRandomOrderNo() }));
	};

	// Calculate subtotal of items
	const itemsSubtotal = items.reduce((sum, it) => sum + (parseInt(it.price || 0, 10) * parseInt(it.qty || 1, 10)), 0);
	const orderTotal = (items.length > 0 ? itemsSubtotal : (parseInt(formData.price || 0, 10) * parseInt(formData.qty || 1, 10))) + parseInt(formData.shipping || 0, 10);

	const handleSubmit = async (e) => {
		e.preventDefault();
		setSubmitting(true);
		setResultMsg(null);
		setCreatedData(null);

		try {
			const payload = {
				...formData,
				items: items.length > 0 ? items : [
					{
						product_id: parseInt(formData.product_id || 0, 10),
						product_name: formData.product_name,
						price: parseInt(formData.price || 0, 10),
						qty: parseInt(formData.qty || 1, 10),
						estimate_days: formData.estimate_days,
						image_url: selectedImage || '',
					}
				],
			};

			const baseUrl = `${settings.root.replace(/\/$/, '')}/create`;
			const res = await fetch(baseUrl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': settings.nonce,
				},
				body: JSON.stringify(payload),
			});
			const data = await res.json();

			if (!res.ok || !data.success) {
				throw new Error(data.message || 'เกิดข้อผิดพลาดในการบันทึกรายการ');
			}

			setCreatedData(data.data);
			setResultMsg({ type: 'success', text: `บันทึกรายการพรีออเดอร์ ${data.data.order_no} สำเร็จเรียบร้อย! (${payload.items.length} รายการสินค้า)` });

			// Reset form for next entry with new random order_no
			setFormData({
				order_no: generateRandomOrderNo(),
				customer_name: '',
				phone: '',
				email: '',
				citizen_id: '',
				wc_order_id: '',
				user_id: '',
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
			setItems([]);
			setSelectedImage('');
		} catch (err) {
			setResultMsg({ type: 'error', text: err.message });
		} finally {
			setSubmitting(false);
		}
	};

	// Format options for Select2 SearchableSelect
	const productOptions = products.map(p => ({
		value: p.id,
		label: p.title + (p.sku ? ` (SKU: ${p.sku})` : ''),
		sub: `฿${(p.price || 0).toLocaleString()} ${p.is_preorder ? '⚡[Pre-Order]' : ''} ${p.eta ? '• ' + p.eta : ''}`,
		image: p.image_url || '',
		group: p.group || 'อื่น ๆ',
		raw: p
	}));

	const orderOptions = wcOrders.map(ord => ({
		value: ord.id,
		label: `#${ord.order_no} | ${ord.customer_name} (${ord.phone || 'ไม่มีเบอร์'})`,
		sub: `฿${(ord.total || 0).toLocaleString()} [${ord.status_label}] (${ord.date}) ${ord.has_preorder ? '⚡ มีสินค้าพรีออเดอร์' : ''}`,
		group: ord.status_label || 'คำสั่งซื้อ',
		raw: ord
	}));

	const userOptions = users.map(u => ({
		value: u.id,
		label: `#${u.id}: ${u.name}`,
		sub: `${u.email}${u.phone ? ` • ${u.phone}` : ''}`,
		raw: u
	}));

	return (
		<div style={{ maxWidth: 1080, margin: '20px auto 40px', fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif' }}>
			{/* Admin Header Banner */}
			<div style={{
				padding: '24px 30px', borderRadius: 16, marginBottom: 24,
				background: 'linear-gradient(135deg, #0f172a, #1e293b)',
				color: '#ffffff', boxShadow: '0 8px 24px rgba(0, 0, 0, 0.2)',
				display: 'flex', alignItems: 'center', justifyContent: 'space-between', flexWrap: 'wrap', gap: 16,
				border: '1px solid rgba(255, 255, 255, 0.1)'
			}}>
				<div>
					<h1 style={{ margin: '0 0 6px', fontSize: 24, fontWeight: 700, color: '#ffffff', display: 'flex', alignItems: 'center', gap: 10 }}>
						<i className="ri-shopping-cart-2-fill" style={{ color: '#38bdf8', fontSize: 26 }}/>
						สร้างรายการพรีออเดอร์ใหม่ (เชื่อมต่อ WooCommerce)
					</h1>
					<p style={{ margin: 0, fontSize: 14, color: 'rgba(255, 255, 255, 0.75)', fontWeight: 400 }}>
						ดึงข้อมูลจาก WooCommerce Orders, สินค้าและตัวเลือกย่อย (Variations), และผูกกับสมาชิกเว็บไซต์ได้ทันที
					</p>
				</div>
				<a href={settings.adminUrl} className="button button-secondary" style={{
					padding: '8px 18px', borderRadius: 9999, height: 'auto',
					background: 'rgba(255, 255, 255, 0.15)', color: '#ffffff',
					border: '1px solid rgba(255, 255, 255, 0.25)', fontWeight: 600, textDecoration: 'none'
				}}>
					<i className="ri-arrow-left-line" style={{ marginRight: 6 }}/> กลับหน้ารายการพรีออเดอร์
				</a>
			</div>

			{/* Success / Error Alert Banner */}
			{resultMsg && (
				<div style={{
					padding: '16px 20px', borderRadius: 12, marginBottom: 24, fontSize: 14, fontWeight: 500,
					background: resultMsg.type === 'success' ? '#f0fdf4' : '#fef2f2',
					border: `1px solid ${resultMsg.type === 'success' ? '#bbf7d0' : '#fecaca'}`,
					color: resultMsg.type === 'success' ? '#15803d' : '#dc2626',
					display: 'flex', alignItems: 'center', justifyContent: 'space-between', flexWrap: 'wrap', gap: 12
				}}>
					<div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
						<i className={resultMsg.type === 'success' ? 'ri-checkbox-circle-fill' : 'ri-error-warning-fill'} style={{ fontSize: 20 }}/>
						<span>{resultMsg.text}</span>
					</div>
					{createdData && (
						<div style={{ display: 'flex', gap: 8 }}>
							<a href={createdData.edit_url} target="_blank" rel="noreferrer" className="button button-small" style={{ borderRadius: 6 }}>
								แก้ไขในระบบ WP
							</a>
							<a href={createdData.view_url} target="_blank" rel="noreferrer" className="button button-small button-primary" style={{ borderRadius: 6 }}>
								ดูหน้าติดตามลูกค้า
							</a>
						</div>
					)}
				</div>
			)}

			{/* Quick Import Switcher Cards */}
			<div style={{
				display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(280px, 1fr))', gap: 16, marginBottom: 24
			}}>
				<div
					onClick={() => setImportMode('wc_order')}
					style={{
						padding: '18px 20px', borderRadius: 14, cursor: 'pointer', transition: 'all 0.2s',
						background: importMode === 'wc_order' ? '#f0fdf4' : '#ffffff',
						border: `2px solid ${importMode === 'wc_order' ? '#22c55e' : '#e2e8f0'}`,
						boxShadow: importMode === 'wc_order' ? '0 4px 12px rgba(34, 197, 94, 0.15)' : 'none'
					}}
				>
					<div style={{ display: 'flex', alignItems: 'center', gap: 10, marginBottom: 6 }}>
						<i className="ri-file-list-3-line" style={{ fontSize: 20, color: '#16a34a' }}/>
						<strong style={{ fontSize: 14, color: '#1e293b' }}>1. ดึงจากออเดอร์ WooCommerce</strong>
					</div>
					<p style={{ margin: 0, fontSize: 12, color: '#64748b' }}>
						เลือกคำสั่งซื้อจากร้านค้า ระบบจะกรอกชื่อ, ที่อยู่, เบอร์โทร, สินค้า และราคาทั้งหมดให้อัตโนมัติ
					</p>
				</div>

				<div
					onClick={() => setImportMode('wc_product')}
					style={{
						padding: '18px 20px', borderRadius: 14, cursor: 'pointer', transition: 'all 0.2s',
						background: importMode === 'wc_product' ? '#eff6ff' : '#ffffff',
						border: `2px solid ${importMode === 'wc_product' ? '#3b82f6' : '#e2e8f0'}`,
						boxShadow: importMode === 'wc_product' ? '0 4px 12px rgba(59, 130, 246, 0.15)' : 'none'
					}}
				>
					<div style={{ display: 'flex', alignItems: 'center', gap: 10, marginBottom: 6 }}>
						<i className="ri-archive-line" style={{ fontSize: 20, color: '#2563eb' }}/>
						<strong style={{ fontSize: 14, color: '#1e293b' }}>2. เลือกจากแคตตาล็อกสินค้า</strong>
					</div>
					<p style={{ margin: 0, fontSize: 12, color: '#64748b' }}>
						เลือกสินค้า / Variation ของร้านค้าเพื่อดึงราคา, รูปภาพ, และกำหนดส่ง (ETA) ให้อัตโนมัติ
					</p>
				</div>

				<div
					onClick={() => setImportMode('custom')}
					style={{
						padding: '18px 20px', borderRadius: 14, cursor: 'pointer', transition: 'all 0.2s',
						background: importMode === 'custom' ? '#faf5ff' : '#ffffff',
						border: `2px solid ${importMode === 'custom' ? '#a855f7' : '#e2e8f0'}`,
						boxShadow: importMode === 'custom' ? '0 4px 12px rgba(168, 85, 247, 0.15)' : 'none'
					}}
				>
					<div style={{ display: 'flex', alignItems: 'center', gap: 10, marginBottom: 6 }}>
						<i className="ri-edit-2-line" style={{ fontSize: 20, color: '#9333ea' }}/>
						<strong style={{ fontSize: 14, color: '#1e293b' }}>3. กรอกข้อมูลกำหนดเอง</strong>
					</div>
					<p style={{ margin: 0, fontSize: 12, color: '#64748b' }}>
						ระบุข้อมูลลูกค้า, สินค้าสั่งทำพิเศษ, และราคาด้วยตนเองอย่างอิสระ
					</p>
				</div>
			</div>

			{/* Main Form */}
			<form onSubmit={handleSubmit} style={{ background: '#ffffff', padding: 32, borderRadius: 16, border: '1px solid #e2e8f0', boxShadow: '0 4px 20px rgba(0,0,0,0.04)' }}>

				{/* ── FAST PICK: WOOCOMMERCE ORDER SELECTOR ── */}
				{importMode === 'wc_order' && (
					<div style={{
						background: '#f8fafc', padding: 20, borderRadius: 12, border: '1px solid #cbd5e1', marginBottom: 28
					}}>
						<label style={{ display: 'block', fontSize: 13, fontWeight: 700, color: '#0f172a', marginBottom: 8 }}>
							<i className="ri-search-eye-line" style={{ color: '#16a34a', marginRight: 6 }}/>
							เลือกคำสั่งซื้อ WooCommerce ที่ต้องการออกสถานะพรีออเดอร์:
						</label>
						<SearchableSelect
							options={orderOptions}
							value={formData.wc_order_id}
							onChange={handleSelectWcOrder}
							placeholder={`-- พิมพ์ค้นหาเลขที่ออเดอร์ หรือชื่อลูกค้า (${wcOrders.length} รายการ) --`}
							groupBy={false}
						/>
						{formData.wc_order_id && (
							<div style={{ marginTop: 10, fontSize: 12, color: '#16a34a', display: 'flex', alignItems: 'center', gap: 6 }}>
								<i className="ri-check-double-line"/> ดึงข้อมูลจาก WooCommerce Order #{formData.wc_order_id} สำเร็จ! ข้อมูลด้านล่างถูกกรอกให้อัตโนมัติแล้ว
							</div>
						)}
					</div>
				)}

				{/* ── SECTION 1: Product & Variation Selection (Multiple Items Supported) ── */}
				<div style={{ marginBottom: 28 }}>
					<div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', borderBottom: '2px solid #f1f5f9', paddingBottom: 10, marginBottom: 16 }}>
						<h2 style={{ fontSize: 16, fontWeight: 700, margin: 0, color: '#0f172a', display: 'flex', alignItems: 'center', gap: 8 }}>
							<i className="ri-shopping-bag-3-fill" style={{ color: '#2563eb' }}/> 1. รายการสินค้าพรีออเดอร์ (รองรับหลายสินค้าใน 1 ออเดอร์)
						</h2>
						<span style={{ fontSize: 13, fontWeight: 600, color: '#2563eb', background: '#eff6ff', padding: '4px 12px', borderRadius: 9999 }}>
							รวม {items.length} รายการ
						</span>
					</div>

					{/* Product Add Bar */}
					<div style={{ background: '#f8fafc', padding: 18, borderRadius: 12, border: '1px solid #e2e8f0', marginBottom: 20 }}>
						<label style={{ display: 'block', fontSize: 13, fontWeight: 600, color: '#334155', marginBottom: 6 }}>
							ค้นหาและเพิ่มสินค้าเข้าไปในออเดอร์ (เลือกจากสินค้า / ตัวเลือกย่อย):
						</label>
						<div style={{ display: 'flex', gap: 12, alignItems: 'center', flexWrap: 'wrap' }}>
							<div style={{ flex: '1 1 320px' }}>
								<SearchableSelect
									options={productOptions}
									value=""
									onChange={(val) => {
										handleAddProductItem(val);
									}}
									placeholder="-- พิมพ์ค้นหาชื่อสินค้า, รหัส SKU เพื่อกดเพิ่มในรายการ --"
									groupBy={true}
								/>
							</div>
							<button
								type="button"
								onClick={handleAddCustomItem}
								className="button"
								style={{ height: 40, padding: '0 16px', borderRadius: 8, display: 'inline-flex', alignItems: 'center', gap: 6, fontWeight: 600 }}
							>
								<i className="ri-add-line" style={{ fontSize: 16 }}/> เพิ่มรายการกำหนดเอง
							</button>
						</div>
					</div>

					{/* Items Table / Card Repeater */}
					{items.length === 0 ? (
						<div style={{ padding: '24px 20px', textAlign: 'center', border: '2px dashed #cbd5e1', borderRadius: 12, color: '#64748b', marginBottom: 16 }}>
							<i className="ri-inbox-archive-line" style={{ fontSize: 32, color: '#94a3b8', display: 'block', marginBottom: 8 }}/>
							<strong style={{ display: 'block', color: '#334155', marginBottom: 4 }}>ยังไม่มีสินค้าในพรีออเดอร์นี้</strong>
							<span style={{ fontSize: 13 }}>เลือกสินค้าจากช่องค้นหาด้านบน หรือเลือกจากคำสั่งซื้อ WooCommerce เพื่อใส่สินค้าอัตโนมัติ</span>
						</div>
					) : (
						<div style={{ display: 'flex', flexDirection: 'column', gap: 12, marginBottom: 20 }}>
							{items.map((item, idx) => (
								<div key={idx} style={{
									display: 'flex', alignItems: 'center', gap: 12, padding: '14px 16px', borderRadius: 12,
									background: '#ffffff', border: '1px solid #e2e8f0', boxShadow: '0 2px 6px rgba(0,0,0,0.02)',
									flexWrap: 'wrap'
								}}>
									{/* Index badge */}
									<span style={{ width: 24, height: 24, borderRadius: '50%', background: '#0f172a', color: '#fff', fontSize: 12, fontWeight: 700, display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
										{idx + 1}
									</span>

									{/* Thumbnail preview */}
									<div style={{ width: 48, height: 48, borderRadius: 8, border: '1px solid #cbd5e1', overflow: 'hidden', flexShrink: 0, background: '#f8fafc', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
										{item.image_url ? (
											<img src={item.image_url} alt={item.product_name} style={{ width: '100%', height: '100%', objectFit: 'contain' }} />
										) : (
											<i className="ri-image-line" style={{ color: '#94a3b8', fontSize: 20 }}/>
										)}
									</div>

									{/* Product Name input */}
									<div style={{ flex: '2 1 200px' }}>
										<label style={{ display: 'block', fontSize: 11, fontWeight: 600, color: '#64748b', marginBottom: 4 }}>ชื่อสินค้า / รุ่น</label>
										<input
											type="text"
											value={item.product_name}
											onChange={(e) => handleUpdateItem(idx, 'product_name', e.target.value)}
											style={{ width: '100%', padding: '8px 10px', borderRadius: 6, border: '1px solid #cbd5e1', fontSize: 13, fontWeight: 500 }}
											required
										/>
									</div>

									{/* Price */}
									<div style={{ flex: '1 1 100px' }}>
										<label style={{ display: 'block', fontSize: 11, fontWeight: 600, color: '#64748b', marginBottom: 4 }}>ราคา/ชิ้น (฿)</label>
										<input
											type="number"
											min={0}
											value={item.price}
											onChange={(e) => handleUpdateItem(idx, 'price', e.target.value)}
											style={{ width: '100%', padding: '8px 10px', borderRadius: 6, border: '1px solid #cbd5e1', fontSize: 13, fontWeight: 600 }}
											required
										/>
									</div>

									{/* Qty */}
									<div style={{ flex: '0 1 70px' }}>
										<label style={{ display: 'block', fontSize: 11, fontWeight: 600, color: '#64748b', marginBottom: 4 }}>จำนวน</label>
										<input
											type="number"
											min={1}
											value={item.qty}
											onChange={(e) => handleUpdateItem(idx, 'qty', e.target.value)}
											style={{ width: '100%', padding: '8px 10px', borderRadius: 6, border: '1px solid #cbd5e1', fontSize: 13, textAlign: 'center' }}
											required
										/>
									</div>

									{/* Subtotal */}
									<div style={{ flex: '1 1 110px', textAlign: 'right' }}>
										<label style={{ display: 'block', fontSize: 11, fontWeight: 600, color: '#64748b', marginBottom: 4 }}>รวม (฿)</label>
										<span style={{ fontSize: 15, fontWeight: 700, color: '#0f172a' }}>
											฿{((parseInt(item.price || 0, 10)) * (parseInt(item.qty || 1, 10))).toLocaleString()}
										</span>
									</div>

									{/* Delete button */}
									<button
										type="button"
										onClick={() => handleRemoveItem(idx)}
										title="ลบรายการนี้"
										style={{ background: '#fee2e2', border: '1px solid #fecaca', color: '#dc2626', width: 34, height: 34, borderRadius: 8, cursor: 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'center' }}
									>
										<i className="ri-delete-bin-line" style={{ fontSize: 16 }}/>
									</button>
								</div>
							))}
						</div>
					)}

					{/* Summary bar for products */}
					<div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(220px, 1fr))', gap: 16, background: '#f8fafc', padding: '16px 20px', borderRadius: 12, border: '1px solid #e2e8f0', alignItems: 'center' }}>
						<div>
							<label style={{ display: 'block', fontSize: 12, fontWeight: 600, color: '#475569', marginBottom: 4 }}>ค่าจัดส่ง (บาท)</label>
							<input
								type="number"
								min={0}
								value={formData.shipping}
								onChange={(e) => handleChange('shipping', e.target.value)}
								style={{ width: '100%', padding: '8px 12px', borderRadius: 8, border: '1px solid #cbd5e1', fontSize: 13 }}
							/>
						</div>

						<div>
							<label style={{ display: 'block', fontSize: 12, fontWeight: 600, color: '#475569', marginBottom: 4 }}>ระยะเวลารอสินค้าเฉลี่ย (ETA)</label>
							<input
								type="text"
								placeholder="เช่น 7-14 วัน หรือ ปลายเดือนนี้"
								value={formData.estimate_days}
								onChange={(e) => handleChange('estimate_days', e.target.value)}
								style={{ width: '100%', padding: '8px 12px', borderRadius: 8, border: '1px solid #cbd5e1', fontSize: 13 }}
							/>
						</div>

						<div style={{ textAlign: 'right', display: 'flex', flexDirection: 'column', justifyContent: 'center' }}>
							<span style={{ fontSize: 12, color: '#64748b', fontWeight: 600 }}>ยอดรวมทั้งออเดอร์สุทธิ</span>
							<span style={{ fontSize: 24, color: '#0f172a', fontWeight: 800 }}>
								฿{orderTotal.toLocaleString()}
							</span>
						</div>
					</div>
				</div>

				{/* ── SECTION 2: Customer & Member Linking ── */}
				<div style={{ marginBottom: 28 }}>
					<h2 style={{ fontSize: 16, fontWeight: 700, margin: '0 0 16px', color: '#0f172a', borderBottom: '2px solid #f1f5f9', paddingBottom: 10, display: 'flex', alignItems: 'center', gap: 8 }}>
						<i className="ri-user-settings-fill" style={{ color: '#059669' }}/> 2. ข้อมูลลูกค้าและการผูกสมาชิก (Customer & Member)
					</h2>

					<div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(280px, 1fr))', gap: 16 }}>
						{/* Auto Order No */}
						<div>
							<label style={{ display: 'block', fontSize: 13, fontWeight: 600, color: '#334155', marginBottom: 6 }}>
								เลขพรีออเดอร์ (Tracking Code)
							</label>
							<div style={{ display: 'flex', gap: 8 }}>
								<input
									type="text"
									value={formData.order_no}
									onChange={(e) => handleChange('order_no', e.target.value)}
									style={{ flex: 1, padding: '10px 14px', borderRadius: 8, border: '1px solid #cbd5e1', fontWeight: 600, background: '#f8fafc', fontSize: 13 }}
									required
								/>
								<button
									type="button"
									onClick={handleRegenerateOrderNo}
									title="สุ่มเลขใหม่"
									style={{ padding: '0 14px', borderRadius: 8, border: '1px solid #cbd5e1', background: '#ffffff', cursor: 'pointer' }}
								>
									<i className="ri-refresh-line"/>
								</button>
							</div>
						</div>

						{/* Member Link */}
						<div>
							<label style={{ display: 'block', fontSize: 13, fontWeight: 600, color: '#334155', marginBottom: 6 }}>
								ผูกกับสมาชิกเว็บไซต์ (Member Account)
							</label>
							<SearchableSelect
								options={userOptions}
								value={formData.user_id}
								onChange={handleUserChange}
								placeholder="-- ค้นหาชื่อสมาชิก, อีเมล, เบอร์โทร หรือปล่อยว่างสำหรับลูกค้าทั่วไป --"
								groupBy={false}
							/>
						</div>

						{/* Customer Name */}
						<div>
							<label style={{ display: 'block', fontSize: 13, fontWeight: 600, color: '#334155', marginBottom: 6 }}>
								ชื่อลูกค้า
							</label>
							<input
								type="text"
								placeholder="เช่น คุณสมชาย ใจดี"
								value={formData.customer_name}
								onChange={(e) => handleChange('customer_name', e.target.value)}
								style={{ width: '100%', padding: '10px 14px', borderRadius: 8, border: '1px solid #cbd5e1', fontSize: 13 }}
							/>
						</div>

						{/* Phone */}
						<div>
							<label style={{ display: 'block', fontSize: 13, fontWeight: 600, color: '#334155', marginBottom: 6 }}>
								เบอร์โทรศัพท์ (ใช้ค้นหา / ยืนยันสิทธิ์)
							</label>
							<input
								type="tel"
								placeholder="0897777777"
								value={formData.phone}
								onChange={(e) => handleChange('phone', e.target.value)}
								style={{ width: '100%', padding: '10px 14px', borderRadius: 8, border: '1px solid #cbd5e1', fontSize: 13 }}
								required
							/>
						</div>

						{/* Email */}
						<div>
							<label style={{ display: 'block', fontSize: 13, fontWeight: 600, color: '#334155', marginBottom: 6 }}>
								อีเมลลูกค้า (ถ้ามี)
							</label>
							<input
								type="email"
								placeholder="customer@email.com"
								value={formData.email}
								onChange={(e) => handleChange('email', e.target.value)}
								style={{ width: '100%', padding: '10px 14px', borderRadius: 8, border: '1px solid #cbd5e1', fontSize: 13 }}
							/>
						</div>

						{/* Citizen ID */}
						<div>
							<label style={{ display: 'block', fontSize: 13, fontWeight: 600, color: '#334155', marginBottom: 6 }}>
								เลขบัตรประชาชน 4 ตัวท้าย (เสริมความปลอดภัย)
							</label>
							<input
								type="text"
								maxLength={4}
								placeholder="7777"
								value={formData.citizen_id}
								onChange={(e) => handleChange('citizen_id', e.target.value)}
								style={{ width: '100%', padding: '10px 14px', borderRadius: 8, border: '1px solid #cbd5e1', fontSize: 13 }}
							/>
						</div>
					</div>
				</div>

				{/* ── SECTION 3: Shipping & Tracking ── */}
				<div style={{ marginBottom: 28 }}>
					<h2 style={{ fontSize: 16, fontWeight: 700, margin: '0 0 16px', color: '#0f172a', borderBottom: '2px solid #f1f5f9', paddingBottom: 10, display: 'flex', alignItems: 'center', gap: 8 }}>
						<i className="ri-truck-fill" style={{ color: '#d97706' }}/> 3. ข้อมูลการจัดส่งและพัสดุ (Shipping & Courier)
					</h2>

					<div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(280px, 1fr))', gap: 16, marginBottom: 16 }}>
						{/* Courier */}
						<div>
							<label style={{ display: 'block', fontSize: 13, fontWeight: 600, color: '#334155', marginBottom: 6 }}>
								บริษัทขนส่งในไทย
							</label>
							<select
								value={formData.courier}
								onChange={(e) => handleChange('courier', e.target.value)}
								style={{ width: '100%', padding: '10px 14px', borderRadius: 8, border: '1px solid #cbd5e1', background: '#ffffff', fontSize: 13 }}
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
							<label style={{ display: 'block', fontSize: 13, fontWeight: 600, color: '#334155', marginBottom: 6 }}>
								หมายเลขพัสดุ (Tracking No.)
							</label>
							<input
								type="text"
								placeholder="เช่น TH8829481947"
								value={formData.tracking_no}
								onChange={(e) => handleChange('tracking_no', e.target.value)}
								style={{ width: '100%', padding: '10px 14px', borderRadius: 8, border: '1px solid #cbd5e1', fontSize: 13 }}
							/>
						</div>

						{/* Tracking Link */}
						<div>
							<label style={{ display: 'block', fontSize: 13, fontWeight: 600, color: '#334155', marginBottom: 6 }}>
								ลิงก์เช็คพัสดุ (Tracking URL)
							</label>
							<input
								type="url"
								placeholder="https://track.thailandpost.co.th/..."
								value={formData.tracking_link}
								onChange={(e) => handleChange('tracking_link', e.target.value)}
								style={{ width: '100%', padding: '10px 14px', borderRadius: 8, border: '1px solid #cbd5e1', fontSize: 13 }}
							/>
						</div>

						{/* LINE Link */}
						<div>
							<label style={{ display: 'block', fontSize: 13, fontWeight: 600, color: '#334155', marginBottom: 6 }}>
								ลิงก์ LINE Official / LINE ID
							</label>
							<input
								type="url"
								placeholder="https://line.me/ti/p/@rakagadget"
								value={formData.line}
								onChange={(e) => handleChange('line', e.target.value)}
								style={{ width: '100%', padding: '10px 14px', borderRadius: 8, border: '1px solid #cbd5e1', fontSize: 13 }}
							/>
						</div>
					</div>

					{/* Address */}
					<div>
						<label style={{ display: 'block', fontSize: 13, fontWeight: 600, color: '#334155', marginBottom: 6 }}>
							ที่อยู่จัดส่งสินค้า
						</label>
						<textarea
							rows={3}
							placeholder="ระบุที่อยู่จัดส่งของลูกค้า"
							value={formData.address}
							onChange={(e) => handleChange('address', e.target.value)}
							style={{ width: '100%', padding: '10px 14px', borderRadius: 8, border: '1px solid #cbd5e1', fontSize: 13 }}
						/>
					</div>
				</div>

				{/* Submit Button */}
				<div style={{ textAlign: 'right', borderTop: '1px solid #f1f5f9', paddingTop: 24, display: 'flex', justifyContent: 'flex-end', gap: 12 }}>
					<button
						type="submit"
						disabled={submitting}
						style={{
							padding: '12px 36px', borderRadius: 9999, border: 'none',
							background: submitting ? '#94a3b8' : 'linear-gradient(135deg, #0f172a, #334155)',
							color: '#ffffff', fontSize: 15, fontWeight: 700, cursor: submitting ? 'not-allowed' : 'pointer',
							boxShadow: '0 4px 14px rgba(15, 23, 42, 0.25)', display: 'inline-flex', alignItems: 'center', gap: 8
						}}
					>
						<i className={submitting ? 'ri-loader-4-line' : 'ri-save-fill'} style={{ fontSize: 18 }}/>
						{submitting ? 'กำลังบันทึกพรีออเดอร์...' : 'บันทึกสร้างรายการพรีออเดอร์'}
					</button>
				</div>
			</form>
		</div>
	);
};

export default AdminAddPreorder;
