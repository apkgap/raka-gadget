import { useState, useEffect } from '@wordpress/element';
import { Spinner } from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';
import { MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { 
    RiUserSearchLine, 
    RiShoppingBag3Line, 
    RiAddCircleFill, 
    RiCloseCircleFill,
    RiPrinterLine,
    RiCheckDoubleLine
} from '@remixicon/react';

const POSSystem = () => {
    // Customer State
    const [custSearch, setCustSearch] = useState('');
    const [custResults, setCustResults] = useState([]);
    const [selectedCustomer, setSelectedCustomer] = useState(null);
    const [selectedCustomerProfile, setSelectedCustomerProfile] = useState(null);
    const [customerAvatarPreviewUrl, setCustomerAvatarPreviewUrl] = useState('');
    const [isNewCustomer, setIsNewCustomer] = useState(false);
    const [newCustName, setNewCustName] = useState('');
    const [newCustPhone, setNewCustPhone] = useState('');
    const [custSearching, setCustSearching] = useState(false);

    // Product State
    const [prodSearch, setProdSearch] = useState('');
    const [prodResults, setProdResults] = useState([]);
    const [prodSearching, setProdSearching] = useState(false);
    const [prodError, setProdError] = useState('');
    const [custError, setCustError] = useState('');
    const [custFocused, setCustFocused] = useState(false);
    const [prodFocused, setProdFocused] = useState(false);

    // Cart State
    const [cart, setCart] = useState([]);
    const [shippingPaid, setShippingPaid] = useState('');
    const [shippingActual, setShippingActual] = useState('');
    const [status, setStatus] = useState('paid');

    // Sales Record Fields (match sales_record edit screen)
    const [saleDate, setSaleDate] = useState(() => new Date().toISOString().slice(0, 10));
    const [deliveryMethod, setDeliveryMethod] = useState('');
    const [lazadaOrder, setLazadaOrder] = useState('');
    const [bargainedPrice, setBargainedPrice] = useState('');
    const [bargainedPriceManuallyEdited, setBargainedPriceManuallyEdited] = useState(false);
    const [previousBargainedPrice, setPreviousBargainedPrice] = useState('');
    const [notes, setNotes] = useState('');

    const [observedSocialUrl, setObservedSocialUrl] = useState('');
    const [observedAvatarUrl, setObservedAvatarUrl] = useState('');
    const [observedAvatarId, setObservedAvatarId] = useState('');

    const [isCalculating, setIsCalculating] = useState(false);
    const [calc, setCalc] = useState({
        totalCost: 0,
        totalSelling: 0,
        bargained: 0,
        shippingPaid: 0,
        shippingActual: 0,
        regularProfit: 0,
        actualProfit: 0,
        netTotal: 0,
    });
    
    // UI State
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [orderSuccess, setOrderSuccess] = useState(null);

    // Data Store (Local Cache)
    const [allCustomers, setAllCustomers] = useState([]);
    const [allProducts, setAllProducts] = useState([]);

    useEffect(() => {
        let canceled = false;

        const resolveAvatarUrl = (customer) => {
            const urls = Array.isArray(customer?.meta?.wsr_fb_avatar_urls) ? customer.meta.wsr_fb_avatar_urls : [];
            const firstUrl = urls.find((url) => typeof url === 'string' && url.trim() !== '') || '';
            const legacyUrl = customer?.meta?.wsr_fb_avatar_url ? String(customer.meta.wsr_fb_avatar_url) : '';
            return firstUrl || legacyUrl;
        };

        const load = async () => {
            if (!selectedCustomer?.id) {
                setSelectedCustomerProfile(null);
                setCustomerAvatarPreviewUrl('');
                return;
            }

            try {
                const customer = await apiFetch({ path: `/wp/v2/customers/${selectedCustomer.id}` });
                if (canceled) return;
                setSelectedCustomerProfile(customer);

                const avatarId = customer?.meta?.wsr_fb_avatar_id ? Number(customer.meta.wsr_fb_avatar_id) : 0;
                const avatarUrl = resolveAvatarUrl(customer);

                if (avatarId > 0) {
                    const media = await apiFetch({ path: `/wp/v2/media/${avatarId}` });
                    if (canceled) return;
                    setCustomerAvatarPreviewUrl(media?.source_url || avatarUrl);
                } else {
                    setCustomerAvatarPreviewUrl(avatarUrl);
                }

                // Auto-fill observed fields from customer data if still empty
                const customerSocialUrl = customer?.meta?.wsr_social_url ? String(customer.meta.wsr_social_url) : '';
                setObservedSocialUrl(customerSocialUrl);
                setObservedAvatarUrl(avatarUrl);
                setObservedAvatarId(avatarId > 0 ? String(avatarId) : '');
            } catch (e) {
                if (!canceled) {
                    setSelectedCustomerProfile(null);
                    setCustomerAvatarPreviewUrl('');
                }
            }
        };

        load();
        return () => {
            canceled = true;
        };
    }, [selectedCustomer]);

    // Preload initial customers & products for instant 0ms search + focus list
    useEffect(() => {
        let canceled = false;
        const preload = async () => {
            try {
                const [cRes, pRes] = await Promise.all([
                    apiFetch({ path: '/wsr/v1/pos/search-customers' }),
                    apiFetch({ path: '/wsr/v1/pos/search-products' }),
                ]);
                if (canceled) return;
                if (Array.isArray(cRes)) setAllCustomers(cRes);
                if (Array.isArray(pRes)) setAllProducts(pRes);
            } catch (e) {
                console.error('POS preload error:', e);
            }
        };
        preload();
        return () => {
            canceled = true;
        };
    }, []);

    // Customer Search (instant local filter + debounced server search, works from 1 char or on focus)
    useEffect(() => {
        const term = custSearch.trim();
        if (term.length === 0) {
            setCustResults(custFocused ? allCustomers.slice(0, 15) : []);
            setCustError('');
            setCustSearching(false);
            return;
        }

        const lower = term.toLowerCase();
        const localMatches = allCustomers.filter(c =>
            (c.name && c.name.toLowerCase().includes(lower)) ||
            (c.phone && String(c.phone).toLowerCase().includes(lower)) ||
            (c.social && String(c.social).toLowerCase().includes(lower))
        ).slice(0, 30);
        setCustResults(localMatches);

        let canceled = false;
        setCustSearching(true);
        const timer = setTimeout(async () => {
            try {
                const res = await apiFetch({ path: `/wsr/v1/pos/search-customers?q=${encodeURIComponent(term)}` });
                if (canceled) return;
                if (Array.isArray(res)) {
                    const mergedMap = new Map();
                    localMatches.forEach(item => mergedMap.set(item.id, item));
                    res.forEach(item => mergedMap.set(item.id, item));
                    setCustResults(Array.from(mergedMap.values()).slice(0, 30));
                }
                setCustError('');
            } catch (err) {
                if (canceled) return;
                if (localMatches.length === 0) {
                    setCustError((err && err.message) || 'ค้นหาลูกค้าไม่สำเร็จ');
                }
            }
            if (!canceled) setCustSearching(false);
        }, 200);
        return () => {
            canceled = true;
            clearTimeout(timer);
        };
    }, [custSearch, custFocused, allCustomers]);

    // Product Search (instant local filter + debounced server search, works from 1 char or on focus)
    useEffect(() => {
        const term = prodSearch.trim();
        if (term.length === 0) {
            setProdResults(prodFocused ? allProducts.slice(0, 20) : []);
            setProdError('');
            setProdSearching(false);
            return;
        }

        const lower = term.toLowerCase();
        const localMatches = allProducts.filter(p =>
            p.name && p.name.toLowerCase().includes(lower)
        ).slice(0, 30);
        setProdResults(localMatches);

        let canceled = false;
        setProdSearching(true);
        const timer = setTimeout(async () => {
            try {
                const res = await apiFetch({ path: `/wsr/v1/pos/search-products?q=${encodeURIComponent(term)}` });
                if (canceled) return;
                if (Array.isArray(res)) {
                    const mergedMap = new Map();
                    localMatches.forEach(item => mergedMap.set(item.id, item));
                    res.forEach(item => mergedMap.set(item.id, item));
                    setProdResults(Array.from(mergedMap.values()).slice(0, 40));
                }
                setProdError('');
            } catch (err) {
                if (canceled) return;
                if (localMatches.length === 0) {
                    setProdError((err && err.message) || 'ค้นหาสินค้าไม่สำเร็จ');
                }
            }
            if (!canceled) setProdSearching(false);
        }, 200);
        return () => {
            canceled = true;
            clearTimeout(timer);
        };
    }, [prodSearch, prodFocused, allProducts]);

    const addToCart = (product) => {
        setCart(prev => {
            const exists = prev.find(p => p.id === product.id);
            if (exists) {
                return prev.map(p => p.id === product.id ? { ...p, qty: p.qty + 1 } : p);
            }
            return [...prev, { ...product, qty: 1 }];
        });
        setProdSearch('');
        setProdResults([]);
    };

    const clampQty = (item, qty) => {
        let q = Math.max(1, parseInt(qty, 10) || 1);
        if (item.stock !== null && item.stock !== undefined && item.stock > 0) {
            q = Math.min(q, item.stock);
        }
        return q;
    };

    const updateQty = (id, delta) => {
        setCart(prev => prev.map(p => (p.id === id ? { ...p, qty: clampQty(p, p.qty + delta) } : p)));
    };

    const setQty = (id, value) => {
        setCart(prev => prev.map(p => (p.id === id ? { ...p, qty: value === '' ? '' : clampQty(p, value) } : p)));
    };

    const updateItemCost = (id, newCost) => {
        setCart(prev => prev.map(p => (p.id === id ? { ...p, cost: newCost === '' ? '' : parseFloat(newCost) || 0 } : p)));
    };

    const updateItemPrice = (id, newPrice) => {
        setCart(prev => prev.map(p => (p.id === id ? { ...p, price: newPrice === '' ? '' : parseFloat(newPrice) || 0 } : p)));
    };
    const removeFromCart = (id) => {
        setCart(prev => prev.filter(p => p.id !== id));
    };

    useEffect(() => {
        if (lazadaOrder !== '') return;

        const orders = Array.from(
            new Set(
                cart
                    .map((item) => (item && item.lazada_order ? String(item.lazada_order).trim() : ''))
                    .filter((v) => v !== '')
            )
        );

        if (orders.length > 0) {
            setLazadaOrder(orders.join(', '));
        }
    }, [cart, lazadaOrder]);

    const submitOrder = async () => {
        if (cart.length === 0) return alert('กรุณาเลือกสินค้าอย่างน้อย 1 รายการ');
        if (!selectedCustomer && (!isNewCustomer || !newCustName)) return alert('กรุณาเลือกลูกค้า หรือกรอกชื่อลูกค้าใหม่');

        setIsSubmitting(true);
        try {
            const payload = {
                customer_id: selectedCustomer ? selectedCustomer.id : 0,
                customer_name: isNewCustomer ? newCustName : (selectedCustomer ? selectedCustomer.name : ''),
                customer_phone: isNewCustomer ? newCustPhone : '',
                products: cart.map(i => ({ ...i, qty: Math.max(1, Number(i.qty) || 1), price: Number(i.price) || 0, cost: Number(i.cost) || 0 })),
                shipping_paid: parseFloat(shippingPaid) || 0,
                shipping_actual: parseFloat(shippingActual) || 0,
                status: status,
                sale_date: saleDate,
                delivery_method: deliveryMethod,
                lazada_order: lazadaOrder,
                bargained_price: bargainedPrice === '' ? null : Number(bargainedPrice),
                previous_bargained_price: previousBargainedPrice === '' ? null : Number(previousBargainedPrice),
                notes: notes,

                observed_social_url: observedSocialUrl,
                observed_avatar_url: observedAvatarUrl,
                observed_avatar_id: observedAvatarId ? Number(observedAvatarId) : 0,
            };

            const res = await apiFetch({ 
                path: `/wsr/v1/pos/submit-order`,
                method: 'POST',
                data: payload
            });

            if (res.success) {
                setOrderSuccess(res.order_id);
            } else {
                alert('เกิดข้อผิดพลาด: ' + (res.message || 'Unknown error'));
            }
        } catch (err) {
            console.error(err);
            alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
        }
        setIsSubmitting(false);
    };

    const resetPOS = () => {
        setSelectedCustomer(null);
        setIsNewCustomer(false);
        setNewCustName('');
        setNewCustPhone('');
        setCustSearch('');
        setCart([]);
        setShippingPaid('');
        setShippingActual('');
        setStatus('paid');
        setSaleDate(new Date().toISOString().slice(0, 10));
        setDeliveryMethod('');
        setLazadaOrder('');
        setBargainedPrice('');
        setBargainedPriceManuallyEdited(false);
        setPreviousBargainedPrice('');
        setNotes('');

        setObservedSocialUrl('');
        setObservedAvatarUrl('');
        setObservedAvatarId('');
        setSelectedCustomerProfile(null);
        setCustomerAvatarPreviewUrl('');
        setOrderSuccess(null);
    };

    // Auto-sync bargained price with cart total whenever cart changes,
    // unless user has manually edited the bargained price input.
    useEffect(() => {
        if (bargainedPriceManuallyEdited) return;
        const totalSelling = cart.reduce((sum, item) => sum + ((Number(item.price) || 0) * (Number(item.qty) || 0)), 0);
        setBargainedPrice(totalSelling > 0 ? String(totalSelling) : '');
    }, [cart, bargainedPriceManuallyEdited]);

    const printReceipt = () => {
        const printWindow = window.open('', '', 'width=350,height=600');
        
        let itemsHtml = '';
        let total = 0;
        cart.forEach(item => {
            const lineTotal = item.price * item.qty;
            total += lineTotal;
            itemsHtml += `
                <div style="display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 13px;">
                    <div style="flex: 2;">${item.name} <br><small>x${item.qty}</small></div>
                    <div style="flex: 1; text-align: right;">฿${lineTotal.toLocaleString()}</div>
                </div>
            `;
        });

        const sPaid = parseFloat(shippingPaid) || 0;
        const netTotal = total + sPaid;
        const customerDisplay = selectedCustomer ? selectedCustomer.name : newCustName;
        const dateStr = new Date().toLocaleString('th-TH');

        const html = `
            <html>
            <head>
                <style>
                    body { font-family: 'Courier New', Courier, monospace; padding: 20px; color: #000; }
                    .center { text-align: center; }
                    .divider { border-bottom: 1px dashed #000; margin: 15px 0; }
                    .bold { font-weight: bold; }
                </style>
            </head>
            <body>
                <h2 class="center" style="margin:0 0 5px 0;">ใบเสร็จรับเงิน</h2>
                <div class="center" style="font-size: 12px; margin-bottom: 15px;">วันที่: ${dateStr}</div>
                <div style="font-size: 13px;">ลูกค้า: ${customerDisplay}</div>
                <div class="divider"></div>
                ${itemsHtml}
                <div class="divider"></div>
                <div style="display: flex; justify-content: space-between; font-size: 13px;">
                    <div>ค่าจัดส่ง</div>
                    <div>฿${sPaid.toLocaleString()}</div>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 16px; margin-top: 10px;" class="bold">
                    <div>ยอดสุทธิ</div>
                    <div>฿${netTotal.toLocaleString()}</div>
                </div>
                <div class="divider"></div>
                <div class="center" style="font-size: 12px; margin-top: 20px;">ขอบคุณที่ใช้บริการ</div>
                
                <script>
                    window.onload = function() { window.print(); window.close(); }
                </script>
            </body>
            </html>
        `;
        printWindow.document.write(html);
        printWindow.document.close();
    };

    useEffect(() => {
        let canceled = false;
        setIsCalculating(true);

        const run = async () => {
            await Promise.resolve();
            if (canceled) return;

            const totalCost = cart.reduce((sum, item) => sum + ((Number(item.cost) || 0) * (Number(item.qty) || 0)), 0);
            const totalSelling = cart.reduce((sum, item) => sum + ((Number(item.price) || 0) * (Number(item.qty) || 0)), 0);
            const sPaid = parseFloat(shippingPaid) || 0;
            const sActual = parseFloat(shippingActual) || 0;

            const bargained = bargainedPrice === '' ? totalSelling : (Number(bargainedPrice) || 0);

            const regularProfit = totalSelling - totalCost + sPaid - sActual;
            const actualProfit = bargained - totalCost + sPaid - sActual;
            const netTotal = bargained + sPaid;

            if (canceled) return;
            setCalc({
                totalCost,
                totalSelling,
                bargained,
                shippingPaid: sPaid,
                shippingActual: sActual,
                regularProfit,
                actualProfit,
                netTotal,
            });
            setIsCalculating(false);
        };

        run();
        return () => {
            canceled = true;
        };
    }, [cart, shippingPaid, shippingActual, bargainedPrice]);

    const subtotal = calc.totalSelling;
    const sPaid = calc.shippingPaid;
    const shippingPaidNum = parseFloat(shippingPaid) || 0;
    const netTotal = calc.netTotal;

    if (orderSuccess) {
        return (
            <div className="wsr-pos">
                <div className="wsr-pos-success">
                    <div className="wsr-pos-success__icon">
                        <RiCheckDoubleLine size={40} />
                    </div>
                    <h2 className="wsr-pos-success__title">บันทึกออเดอร์สำเร็จ</h2>
                    <p className="wsr-pos-success__meta">รหัสออเดอร์ <strong>#{orderSuccess}</strong></p>
                    <div className="wsr-pos-success__actions">
                        <button type="button" className="wsr-btn wsr-btn--ghost" onClick={printReceipt}>
                            <RiPrinterLine size={18} /> พิมพ์ใบเสร็จ
                        </button>
                        <button type="button" className="wsr-btn wsr-btn--primary" onClick={resetPOS}>
                            รับออเดอร์ต่อไป
                        </button>
                    </div>
                </div>
            </div>
        );
    }

    const hasHeavyBargainFlag = String(selectedCustomerProfile?.meta?.wsr_fb_flag_note || '').includes('เคยต่อราคาหนัก');
    const customerDisplayName = selectedCustomer?.name || selectedCustomerProfile?.title?.rendered || '';
    const isDirty = cart.length > 0 || !!selectedCustomer || isNewCustomer;

    return (
        <div className="wsr-pos">
            {/* Top bar */}
            <header className="wsr-pos__topbar">
                <div className="wsr-pos__brand">
                    <span className="wsr-pos__brand-dot"><RiShoppingBag3Line size={18} /></span>
                    <div>
                        <div className="wsr-pos__brand-title">Mini POS</div>
                        <div className="wsr-pos__brand-sub">บันทึกออเดอร์ขายหน้าร้าน</div>
                    </div>
                </div>
                <div className="wsr-pos__topmeta">
                    <span className="wsr-pos__chip">
                        <RiShoppingBag3Line size={14} /> {cart.length} รายการ
                    </span>
                    {isDirty && (
                        <button type="button" className="wsr-btn wsr-btn--ghost wsr-btn--sm" onClick={resetPOS}>
                            <RiCloseCircleFill size={16} /> ล้างทั้งหมด
                        </button>
                    )}
                </div>
            </header>

            <div className="wsr-pos__grid">
                {/* LEFT: main */}
                <div className="wsr-pos__main">
                    {/* Customer */}
                    <section className="wsr-card">
                        <header className="wsr-card__header">
                            <span className="wsr-card__icon wsr-card__icon--blue"><RiUserSearchLine size={16} /></span>
                            <h3 className="wsr-card__title">ลูกค้า</h3>
                            {selectedCustomer && (
                                <button type="button" className="wsr-card__action" onClick={() => setSelectedCustomer(null)}>เปลี่ยนลูกค้า</button>
                            )}
                        </header>
                        <div className="wsr-card__body">
                            {!selectedCustomer && !isNewCustomer && (
                                <>
                                    <div className="wsr-search">
                                        <input
                                            type="text"
                                            placeholder="คลิกเพื่อเลือกลูกค้า หรือพิมพ์ชื่อ / เบอร์โทรเพื่อค้นหา…"
                                            value={custSearch}
                                            onFocus={() => setCustFocused(true)}
                                            onBlur={() => setTimeout(() => setCustFocused(false), 180)}
                                            onChange={e => {
                                                setCustSearch(e.target.value);
                                                setCustFocused(true);
                                            }}
                                            className="wsr-pos-input"
                                        />
                                        {custResults.length > 0 && (
                                            <div className="wsr-search__results">
                                                {custResults.map(c => (
                                                    <div
                                                        key={c.id}
                                                        className="wsr-search__item"
                                                        onMouseDown={(e) => {
                                                            e.preventDefault();
                                                            setSelectedCustomer(c);
                                                            setCustFocused(false);
                                                        }}
                                                        onClick={() => {
                                                            setSelectedCustomer(c);
                                                            setCustFocused(false);
                                                        }}
                                                    >
                                                        <div className="wsr-search__item-main">{c.name}</div>
                                                        <div className="wsr-search__item-sub">{c.phone && c.phone !== '-' ? c.phone : 'ไม่ระบุเบอร์'}{c.social ? ` · ${c.social}` : ''}</div>
                                                    </div>
                                                ))}
                                            </div>
                                        )}
                                        {custSearching && custResults.length === 0 && (
                                            <div className="wsr-search__item-sub" style={{ padding: '8px 4px' }}>กำลังค้นหา…</div>
                                        )}
                                        {custError && (
                                            <div className="wsr-search__item-sub" style={{ padding: '8px 4px', color: '#ef4444' }}>{custError}</div>
                                        )}
                                        {!custSearching && !custError && custSearch.trim().length >= 1 && custResults.length === 0 && (
                                            <div className="wsr-search__item-sub" style={{ padding: '8px 4px' }}>
                                                ไม่พบลูกค้า "{custSearch.trim()}" — กด "เพิ่มลูกค้าใหม่" ด้านล่างได้เลย
                                            </div>
                                        )}
                                    </div>
                                    <div className="wsr-card__divider">
                                        <span>หรือ</span>
                                    </div>
                                    <button type="button" className="wsr-btn wsr-btn--outline wsr-btn--block" onClick={() => setIsNewCustomer(true)}>
                                        <RiAddCircleFill size={18} /> เพิ่มลูกค้าใหม่
                                    </button>
                                </>
                            )}

                            {selectedCustomer && (
                                <div className="wsr-selected">
                                    <div className="wsr-selected__avatar">
                                        {customerAvatarPreviewUrl ? <img src={customerAvatarPreviewUrl} alt="avatar" /> : <RiUserSearchLine size={22} />}
                                    </div>
                                    <div className="wsr-selected__info">
                                        <div className="wsr-selected__name">{selectedCustomer.name}</div>
                                        <div className="wsr-selected__meta">{selectedCustomer.phone || '—'}</div>
                                        {selectedCustomerProfile?.meta?.wsr_fb_flag && !['normal', 'ปกติ', ''].includes(String(selectedCustomerProfile.meta.wsr_fb_flag)) ? (
                                            <span className="wsr-flag wsr-flag--danger">{selectedCustomerProfile.meta.wsr_fb_flag}</span>
                                        ) : (
                                            <span className="wsr-flag">ลูกค้าปกติ</span>
                                        )}
                                    </div>
                                    {selectedCustomerProfile?.id && (
                                        <a className="wsr-selected__link" href={`/wp-admin/post.php?post=${selectedCustomerProfile.id}&action=edit`} target="_blank" rel="noreferrer">เปิดหน้าลูกค้า ↗</a>
                                    )}
                                </div>
                            )}

                            {isNewCustomer && (
                                <div className="wsr-newcust">
                                    <div className="wsr-newcust__head">
                                        <h4>เพิ่มลูกค้าใหม่</h4>
                                        <button type="button" className="wsr-btn-link" onClick={() => setIsNewCustomer(false)}>ยกเลิก</button>
                                    </div>
                                    <div className="wsr-grid-2">
                                        <input type="text" placeholder="ชื่ออ้างอิงลูกค้า *" value={newCustName} onChange={e => setNewCustName(e.target.value)} className="wsr-pos-input" />
                                        <input type="text" placeholder="เบอร์โทรศัพท์" value={newCustPhone} onChange={e => setNewCustPhone(e.target.value)} className="wsr-pos-input" />
                                    </div>
                                </div>
                            )}
                        </div>
                    </section>

                    {/* Products & Cart */}
                    <section className="wsr-card">
                        <header className="wsr-card__header">
                            <span className="wsr-card__icon wsr-card__icon--green"><RiShoppingBag3Line size={16} /></span>
                            <h3 className="wsr-card__title">สินค้า</h3>
                            <span className="wsr-card__hint">{cart.length} รายการในตะกร้า</span>
                        </header>
                        <div className="wsr-card__body">
                            <div className="wsr-search wsr-search--accent">
                                <input
                                    type="text"
                                    placeholder="คลิกเพื่อเลือกสินค้า หรือพิมพ์ชื่อสินค้าเพื่อค้นหา…"
                                    value={prodSearch}
                                    onFocus={() => setProdFocused(true)}
                                    onBlur={() => setTimeout(() => setProdFocused(false), 180)}
                                    onChange={e => {
                                        setProdSearch(e.target.value);
                                        setProdFocused(true);
                                    }}
                                    className="wsr-pos-input wsr-pos-input--accent"
                                />
                                {prodResults.length > 0 && (
                                    <div className="wsr-search__results">
                                        {prodResults.map(p => (
                                            <div
                                                key={p.id}
                                                className="wsr-search__item wsr-search__item--product"
                                                onMouseDown={(e) => {
                                                    e.preventDefault();
                                                    addToCart(p);
                                                    setProdFocused(false);
                                                }}
                                                onClick={() => {
                                                    addToCart(p);
                                                    setProdFocused(false);
                                                }}
                                            >
                                                <div className="wsr-search__item-body">
                                                    <div className="wsr-search__item-main">{p.name}</div>
                                                    <div className="wsr-search__item-sub">{p.stock === null ? 'มีของ: ไม่จำกัด' : (p.stock > 0 ? `มีของ: ${p.stock}` : 'สินค้าหมด')} · ทุน ฿{(p.cost || 0).toLocaleString()}</div>
                                                </div>
                                                <div className="wsr-search__item-right">
                                                    <span className="wsr-price">฿{(p.price || 0).toLocaleString()}</span>
                                                    <RiAddCircleFill size={22} />
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                )}
                            </div>

                            {prodSearching && <div className="wsr-search__item-sub" style={{ padding: '8px 4px' }}>กำลังค้นหา…</div>}
                            {prodError && <div className="wsr-search__item-sub" style={{ padding: '8px 4px', color: '#ef4444' }}>{prodError}</div>}
                            {!prodSearching && !prodError && prodSearch.trim() !== '' && prodResults.length === 0 && (
                                <div className="wsr-search__item-sub" style={{ padding: '8px 4px' }}>ไม่พบสินค้า "{prodSearch.trim()}"</div>
                            )}

                            {/* Cart list */}
                            <div className="wsr-cart">
                                {cart.length === 0 ? (
                                    <div className="wsr-pos-cart-empty">
                                        <RiShoppingBag3Line size={48} />
                                        <div className="wsr-pos-cart-empty__title">ยังไม่มีสินค้าในตะกร้า</div>
                                        <div className="wsr-pos-cart-empty__hint">ค้นหาด้านบนเพื่อเพิ่มสินค้า</div>
                                    </div>
                                ) : (
                                    <ul className="wsr-cart__list">
                                        {cart.map(item => (
                                            <li key={item.id} className="wsr-cart__item">
                                                <div className="wsr-cart__info">
                                                    <div className="wsr-cart__name">{item.name}</div>
                                                    {item.stock !== null && item.stock !== undefined && item.qty >= item.stock && <div className="wsr-search__item-sub" style={{ color: '#ef4444' }}>ถึงจำนวนสต็อกสูงสุด ({item.stock})</div>}
                                                    <div className="wsr-cart__price-meta">
                                                        <div className="wsr-cart__cost-field">
                                                            <label>ราคา/ชิ้น:</label>
                                                            <div className="wsr-cart__cost-input-wrap">
                                                                <span>฿</span>
                                                                <input
                                                                    type="number"
                                                                    min="0"
                                                                    step="any"
                                                                    value={item.price !== undefined ? item.price : ''}
                                                                    onChange={e => updateItemPrice(item.id, e.target.value)}
                                                                    className="wsr-cart__cost-input"
                                                                    title="ราคาขายต่อชิ้น (แก้ไขได้)"
                                                                />
                                                            </div>
                                                        </div>
                                                        <div className="wsr-cart__cost-field">
                                                            <label>ทุน/ชิ้น:</label>
                                                            <div className="wsr-cart__cost-input-wrap">
                                                                <span>฿</span>
                                                                <input
                                                                    type="number"
                                                                    min="0"
                                                                    step="any"
                                                                    placeholder="0"
                                                                    value={item.cost !== undefined ? item.cost : ''}
                                                                    onChange={e => updateItemCost(item.id, e.target.value)}
                                                                    className="wsr-cart__cost-input"
                                                                    title="ระบุต้นทุนเฉพาะของชิ้นนี้"
                                                                />
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div className="wsr-qty">
                                                    <button type="button" className="wsr-qty__btn" onClick={() => updateQty(item.id, -1)}>−</button>
                                                    <input type="number" min="1" className="wsr-qty__val" style={{ width: '48px', textAlign: 'center' }} value={item.qty} onChange={e => setQty(item.id, e.target.value)} onBlur={() => setQty(item.id, item.qty)} />
                                                    <button type="button" className="wsr-qty__btn" onClick={() => updateQty(item.id, 1)}>+</button>
                                                </div>
                                                <div className="wsr-cart__subtotal">฿{((item.price || 0) * (item.qty || 0)).toLocaleString()}</div>
                                                <button type="button" className="wsr-cart__remove" onClick={() => removeFromCart(item.id)} aria-label="ลบ">
                                                    <RiCloseCircleFill size={18} />
                                                </button>
                                            </li>
                                        ))}
                                    </ul>
                                )}
                            </div>
                        </div>
                    </section>
                </div>

                {/* RIGHT: checkout */}
                <aside className="wsr-pos__side">
                    <div className="wsr-card wsr-checkout">
                        <header className="wsr-card__header">
                            <h3 className="wsr-card__title">สรุปคำสั่งซื้อ</h3>
                            {customerDisplayName && <span className="wsr-card__hint">{customerDisplayName}</span>}
                        </header>
                        <div className="wsr-card__body">
                            {/* Customer profile mini */}
                            {selectedCustomerProfile?.id && (
                                <div className="wsr-profile">
                                    <div className="wsr-profile__avatar">
                                        {customerAvatarPreviewUrl ? <img src={customerAvatarPreviewUrl} alt="avatar" /> : <RiUserSearchLine size={22} />}
                                    </div>
                                    <div className="wsr-profile__info">
                                        <div className="wsr-profile__name">{selectedCustomerProfile.title?.rendered || '—'}</div>
                                        {selectedCustomerProfile.meta?.wsr_social_url && (
                                            <a className="wsr-profile__link" href={selectedCustomerProfile.meta.wsr_social_url} target="_blank" rel="noreferrer">
                                                Facebook ↗
                                            </a>
                                        )}
                                        {selectedCustomerProfile.meta?.wsr_fb_flag_note && (
                                            <div className="wsr-profile__note">{selectedCustomerProfile.meta.wsr_fb_flag_note}</div>
                                        )}
                                    </div>
                                    <div className="wsr-profile__actions">
                                        <button type="button" className="wsr-btn wsr-btn--ghost wsr-btn--xs" onClick={() => {
                                            const url = selectedCustomerProfile.meta?.wsr_social_url || '';
                                            if (url) setObservedSocialUrl(String(url));
                                        }}>ใช้ลิงก์</button>
                                        <button type="button" className="wsr-btn wsr-btn--ghost wsr-btn--xs" onClick={() => {
                                            const urls = Array.isArray(selectedCustomerProfile.meta?.wsr_fb_avatar_urls) ? selectedCustomerProfile.meta.wsr_fb_avatar_urls : [];
                                            const firstUrl = urls.find((u) => typeof u === 'string' && u.trim() !== '') || '';
                                            const url = firstUrl || selectedCustomerProfile.meta?.wsr_fb_avatar_url || '';
                                            const id = selectedCustomerProfile.meta?.wsr_fb_avatar_id || '';
                                            if (url) setObservedAvatarUrl(String(url));
                                            if (id) setObservedAvatarId(String(id));
                                        }}>ใช้ Avatar</button>
                                    </div>
                                </div>
                            )}

                            {/* Section: Order Info */}
                            <div className="wsr-section">
                                <div className="wsr-section__title">ข้อมูลออเดอร์</div>
                                <div className="wsr-grid-2">
                                    <div className="wsr-field">
                                        <label>วันที่ซื้อขาย</label>
                                        <input type="date" value={saleDate} onChange={e => setSaleDate(e.target.value)} className="wsr-pos-input" />
                                    </div>
                                    <div className="wsr-field">
                                        <label>รูปแบบการจัดส่ง</label>
                                        <select value={deliveryMethod} onChange={e => setDeliveryMethod(e.target.value)} className="wsr-pos-input">
                                            <option value="">โปรดระบุ</option>
                                            <option value="pickup">นัดรับ</option>
                                            <option value="ems">ส่ง EMS</option>
                                            <option value="messenger">แมสเซนเจอร์</option>
                                        </select>
                                    </div>
                                    <div className="wsr-field">
                                        <label>สถานะออเดอร์</label>
                                        <select value={status} onChange={e => setStatus(e.target.value)} className="wsr-pos-input">
                                            <option value="pending">รอโอนเงิน / รอชำระ</option>
                                            <option value="paid">ชำระเงินแล้ว / กำลังเตรียมจัดส่ง</option>
                                            <option value="shipped">จัดส่งแล้ว</option>
                                            <option value="refunded">คืนเงิน / ยกเลิก</option>
                                        </select>
                                    </div>
                                    <div className="wsr-field">
                                        <label>ราคาที่ตกลงขายจริง (฿) {bargainedPriceManuallyEdited && <button type="button" className="wsr-btn-link" onClick={() => setBargainedPriceManuallyEdited(false)}>ใช้ยอดรวมสินค้า</button>}</label>
                                        <input type="number" min="0" value={bargainedPrice} onChange={e => { setBargainedPrice(e.target.value); setBargainedPriceManuallyEdited(true); }} className="wsr-pos-input" />
                                    </div>
                                </div>
                                {hasHeavyBargainFlag && (
                                    <div className="wsr-field wsr-field--danger">
                                        <label>ราคาที่เคยต่อ (เคสเคยต่อราคาหนัก)</label>
                                        <input type="number" min="0" value={previousBargainedPrice} onChange={e => setPreviousBargainedPrice(e.target.value)} className="wsr-pos-input" />
                                    </div>
                                )}
                            </div>

                            {/* Section: Social */}
                            <div className="wsr-section">
                                <div className="wsr-section__title">ข้อมูลโซเชียล (ที่เห็นตอนคุย)</div>
                                <div className="wsr-field">
                                    <label>หมายเลขคำสั่งซื้อ Lazada (ถ้ามี)</label>
                                    <input type="text" value={lazadaOrder} onChange={e => setLazadaOrder(e.target.value)} className="wsr-pos-input" />
                                </div>
                                <div className="wsr-field">
                                    <label>ลิงก์โปรไฟล์ Facebook</label>
                                    <input type="text" value={observedSocialUrl} onChange={e => setObservedSocialUrl(e.target.value)} className="wsr-pos-input" />
                                </div>
                                <div className="wsr-grid-2">
                                    <div className="wsr-field">
                                        <label>Avatar Facebook (URL)</label>
                                        <input type="text" value={observedAvatarUrl} onChange={e => setObservedAvatarUrl(e.target.value)} className="wsr-pos-input" />
                                    </div>
                                    <div className="wsr-field">
                                        <label>Avatar จาก Media</label>
                                        <MediaUploadCheck>
                                            <MediaUpload
                                                onSelect={(media) => setObservedAvatarId(media?.id ? String(media.id) : '')}
                                                allowedTypes={[ 'image' ]}
                                                value={observedAvatarId || ''}
                                                render={({ open }) => (
                                                    <div className="wsr-media">
                                                        <button type="button" className="wsr-btn wsr-btn--outline wsr-btn--sm" onClick={open}>
                                                            {observedAvatarId ? 'เปลี่ยนรูป' : 'เลือกรูป'}
                                                        </button>
                                                        {observedAvatarId && (
                                                            <button type="button" className="wsr-btn wsr-btn--danger-ghost wsr-btn--sm" onClick={() => setObservedAvatarId('')}>
                                                                ล้าง
                                                            </button>
                                                        )}
                                                    </div>
                                                )}
                                            />
                                        </MediaUploadCheck>
                                    </div>
                                </div>
                            </div>

                            {/* Section: Shipping */}
                            <div className="wsr-section">
                                <div className="wsr-section__title">ค่าจัดส่ง</div>
                                <div className="wsr-grid-2">
                                    <div className="wsr-field">
                                        <label>ค่าส่งที่เรียกเก็บลูกค้า (฿)</label>
                                        <input type="number" min="0" value={shippingPaid} onChange={e => setShippingPaid(e.target.value)} className="wsr-pos-input" />
                                    </div>
                                    <div className="wsr-field">
                                        <label>ค่าส่งที่ร้านจ่ายจริง (฿)</label>
                                        <input type="number" min="0" value={shippingActual} onChange={e => setShippingActual(e.target.value)} className="wsr-pos-input" />
                                    </div>
                                </div>
                            </div>

                            {/* Section: Notes */}
                            <div className="wsr-section">
                                <div className="wsr-field">
                                    <label>บันทึกภายใน (Notes)</label>
                                    <textarea value={notes} onChange={e => setNotes(e.target.value)} rows={2} className="wsr-pos-input" />
                                </div>
                            </div>

                            {/* Total */}
                            <div className="wsr-pos-total">
                                <div className="wsr-pos-total__rows">
                                    <div className="wsr-pos-total__row">
                                        <span>ยอดรวมสินค้า</span>
                                        <span>{isCalculating ? '…' : `฿${subtotal.toLocaleString()}`}</span>
                                    </div>
                                    <div className="wsr-pos-total__row">
                                        <span>ยอดหลังต่อรอง</span>
                                        <span>{isCalculating ? '…' : `฿${calc.bargained.toLocaleString()}`}</span>
                                    </div>
                                    {calc.bargained !== calc.totalSelling && (
                                        <div className="wsr-pos-total__row">
                                            <span>ส่วนต่างจากการต่อรอง</span>
                                            <span>{`฿${(calc.bargained - calc.totalSelling).toLocaleString()}`}</span>
                                        </div>
                                    )}
                                    <div className="wsr-pos-total__row">
                                        <span>ต้นทุนรวม</span>
                                        <span>{isCalculating ? '…' : `฿${calc.totalCost.toLocaleString()}`}</span>
                                    </div>
                                    <div className="wsr-pos-total__row">
                                        <span>ค่าจัดส่ง</span>
                                        <span>{isCalculating ? '…' : `฿${shippingPaidNum.toLocaleString()}`}</span>
                                    </div>
                                </div>
                                <div className="wsr-pos-total__grand">
                                    <div className="wsr-pos-total__label">ยอดสุทธิที่ต้องชำระ</div>
                                    <div className="wsr-pos-total__amount">{isCalculating ? '…' : `฿${netTotal.toLocaleString()}`}</div>
                                </div>
                            </div>

                            {/* Profit pills */}
                            <div className="wsr-pos-profit-grid">
                                <div className="wsr-pos-profit">
                                    <div className="wsr-pos-profit__label">กำไรปกติ (ราคาเต็ม)</div>
                                    <div className={`wsr-pos-profit__value ${calc.regularProfit < 0 ? 'is-negative' : ''}`}>
                                        {isCalculating ? '…' : `฿${calc.regularProfit.toLocaleString()}`}
                                    </div>
                                </div>
                                <div className="wsr-pos-profit">
                                    <div className="wsr-pos-profit__label">กำไรสุทธิ (หลังต่อรอง)</div>
                                    <div className={`wsr-pos-profit__value ${calc.actualProfit < 0 ? 'is-negative' : ''}`}>
                                        {isCalculating ? '…' : `฿${calc.actualProfit.toLocaleString()}`}
                                    </div>
                                </div>
                            </div>

                            <button
                                onClick={submitOrder}
                                disabled={isSubmitting || cart.length === 0}
                                className="wsr-pos-submit"
                            >
                                {isSubmitting ? <Spinner /> : <>ยืนยันและบันทึกออเดอร์ <RiCheckDoubleLine size={20} /></>}
                            </button>
                        </div>
                    </div>
                </aside>
            </div>

            {/* Mobile Fixed Bottom Summary & Action Bar */}
            <div className="wsr-pos-mobile-bar">
                <div className="wsr-pos-mobile-bar__info">
                    <span className="wsr-pos-mobile-bar__label">{cart.length} รายการ · สุทธิ:</span>
                    <span className="wsr-pos-mobile-bar__amount">฿{netTotal.toLocaleString()}</span>
                </div>
                <button
                    type="button"
                    onClick={() => {
                        const side = document.querySelector('.wsr-pos__side');
                        if (side) {
                            side.scrollIntoView({ behavior: 'smooth' });
                        }
                    }}
                    className="wsr-pos-mobile-bar__btn"
                >
                    ดูยอดสรุป / บันทึก
                </button>
            </div>
        </div>
    );
};

export default POSSystem;
