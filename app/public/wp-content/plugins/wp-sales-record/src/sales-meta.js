import { useState, useEffect } from '@wordpress/element';
import { TextControl, SelectControl, TextareaControl, BaseControl, Button } from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';
import Select from 'react-select'; // react-select for nice searchable dropdowns
import AsyncSelect from 'react-select/async';
import { MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';

const SalesRecordMetaFields = ( { postId } ) => {
    const [ meta, setMeta ] = useState( {} );
    const [ postTitle, setPostTitle ] = useState( '' );
    const [ isLoading, setIsLoading ] = useState( true );
    const [ isSaving, setIsSaving ] = useState( false );

    // --- References to options ---
    const [ customerOptions, setCustomerOptions ] = useState( [] );
    const [ lines, setLines ] = useState( [] );

    const [ selectedCustomerProfile, setSelectedCustomerProfile ] = useState( null );
    const [ customerAvatarPreviewUrl, setCustomerAvatarPreviewUrl ] = useState( '' );
    const [ observedAvatarPreviewUrl, setObservedAvatarPreviewUrl ] = useState( '' );

    // Fetch existing data
    useEffect( () => {
        const loadData = async () => {
            try {
                // Load Sales Record Data
                const post = await apiFetch( { path: `/wp/v2/sales_records/${postId}` } );
                setMeta( post.meta || {} );
                
                let currentTitle = post.title.rendered;
                
                // Auto generate SKU if title is empty or "Auto Draft"
                if (!currentTitle || currentTitle === 'Auto Draft') {
                    currentTitle = 'ORD-' + Math.floor(Math.random() * 10000) + '-' + new Date().toISOString().slice(2,10).replace(/-/g, '');
                    
                    // Update input field in classic editor to match
                    const titleInput = document.getElementById('title');
                    if (titleInput) titleInput.value = currentTitle;
                }
                setPostTitle( currentTitle );

                // Load Customers

                const customers = await apiFetch( { path: '/wp/v2/customers?per_page=100' } );
                setCustomerOptions( customers.map( c => ( { value: c.id, label: c.title.rendered } ) ) );

                // Load selected products (grouped by id => qty) with live WooCommerce price/cost
                const ids = Array.isArray( post.meta?.wsr_product_ids ) ? post.meta.wsr_product_ids.map( Number ).filter( Boolean ) : [];
                if ( ids.length ) {
                    const qtyMap = {};
                    ids.forEach( ( id ) => { qtyMap[ id ] = ( qtyMap[ id ] || 0 ) + 1; } );
                    const uniq = Object.keys( qtyMap );
                    const found = await apiFetch( { path: '/wsr/v1/pos/search-products?include=' + uniq.join( ',' ) } );
                    setLines( found.map( ( p ) => ( { ...p, qty: qtyMap[ p.id ] || 1 } ) ) );
                }
                setIsLoading( false );
            } catch (error) {
                console.error("Error loading sales record dependencies:", error);
                setIsLoading( false );
            }
        };

        loadData();
    }, [ postId ] );

    const updateMeta = ( key, value ) => {
        const newMeta = { ...meta, [ key ]: value };
        setMeta( newMeta );
    };

    const updateNumMeta = ( key, value ) => {
        const numValue = value === '' ? '' : Number( value );
        const newMeta = { ...meta, [ key ]: numValue };
        setMeta( newMeta );
    };

    // Calculate Profits
    const costPrice     = Number( meta.wsr_cost_price )     || 0;
    const sellingPrice  = Number( meta.wsr_selling_price )  || 0;
    const shippingPaid   = Number( meta.wsr_shipping_paid )   || 0;
    const shippingActual = Number( meta.wsr_shipping_actual ) || 0;

    // bargainedPrice: ใช้ราคาที่ต่อรองจริง ถ้ามีค่า (> 0)
    // ถ้า field ว่างหรือ 0 ให้ fallback ไปที่ sellingPrice
    const bargainedPrice = Number( meta.wsr_bargained_price ) > 0
        ? Number( meta.wsr_bargained_price )
        : sellingPrice;

    // กำไรปกติ: ราคาเต็ม − ต้นทุน + ค่าส่งที่ได้รับ − ค่าส่งที่จ่ายจริง
    const regularProfit = sellingPrice  - costPrice + shippingPaid - shippingActual;
    // กำไรจริง: ราคาตกลง − ต้นทุน + ค่าส่งที่ได้รับ − ค่าส่งที่จ่ายจริง
    const actualProfit  = bargainedPrice - costPrice + shippingPaid - shippingActual;
    // ส่วนลดจากการต่อราคา (ติดลบ = ลดราคาให้ลูกค้า)
    const discountAmount = bargainedPrice - sellingPrice;
    const hasBargain     = Number( meta.wsr_bargained_price ) > 0
        && Number( meta.wsr_bargained_price ) !== sellingPrice;
    const isProfitNegative = actualProfit < 0;

    const selectedCustomer = customerOptions.find( opt => opt.value === meta.wsr_customer_id ) || null;

    const showPreviousBargainPrice = String(selectedCustomerProfile?.meta?.wsr_fb_flag_note || '').includes('เคยต่อราคาหนัก');

    useEffect(() => {
        let canceled = false;

        const resolveAvatarUrl = (customer) => {
            const urls = Array.isArray(customer?.meta?.wsr_fb_avatar_urls) ? customer.meta.wsr_fb_avatar_urls : [];
            const firstUrl = urls.find((url) => typeof url === 'string' && url.trim() !== '') || '';
            const legacyUrl = customer?.meta?.wsr_fb_avatar_url ? String(customer.meta.wsr_fb_avatar_url) : '';
            return firstUrl || legacyUrl;
        };

        const loadCustomer = async () => {
            if (!meta.wsr_customer_id) {
                setSelectedCustomerProfile(null);
                setCustomerAvatarPreviewUrl('');
                return;
            }

            try {
                const customer = await apiFetch({ path: `/wp/v2/customers/${meta.wsr_customer_id}` });
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
            } catch (e) {
                if (!canceled) {
                    setSelectedCustomerProfile(null);
                    setCustomerAvatarPreviewUrl('');
                }
            }
        };

        loadCustomer();
        return () => {
            canceled = true;
        };
    }, [meta.wsr_customer_id]);

    useEffect(() => {
        let canceled = false;

        const loadObservedAvatar = async () => {
            const observedId = meta?.wsr_observed_avatar_id ? Number(meta.wsr_observed_avatar_id) : 0;
            const observedUrl = meta?.wsr_observed_avatar_url ? String(meta.wsr_observed_avatar_url) : '';

            if (observedId > 0) {
                try {
                    const media = await apiFetch({ path: `/wp/v2/media/${observedId}` });
                    if (!canceled) setObservedAvatarPreviewUrl(media?.source_url || observedUrl);
                } catch (e) {
                    if (!canceled) setObservedAvatarPreviewUrl(observedUrl);
                }
            } else {
                setObservedAvatarPreviewUrl(observedUrl);
            }
        };

        loadObservedAvatar();
        return () => {
            canceled = true;
        };
    }, [meta.wsr_observed_avatar_id, meta.wsr_observed_avatar_url]);
    
    const selectedProductIds = lines.flatMap( ( l ) => Array( Math.max( 1, Number( l.qty ) || 1 ) ).fill( l.id ) );

    const loadProductOptions = ( input ) =>
        apiFetch( { path: '/wsr/v1/pos/search-products?q=' + encodeURIComponent( input || '' ) } )
            .then( ( list ) => list.slice( 0, 50 ).map( ( p ) => ( { value: p.id, label: p.name, product: p } ) ) )
            .catch( () => [] );

    // Recalculate totals from lines and sync into meta
    const applyLines = ( nextLines ) => {
        setLines( nextLines );
        const totalCost = nextLines.reduce( ( s, l ) => s + ( Number( l.cost ) || 0 ) * ( Number( l.qty ) || 0 ), 0 );
        const totalPrice = nextLines.reduce( ( s, l ) => s + ( Number( l.price ) || 0 ) * ( Number( l.qty ) || 0 ), 0 );
        const lazada = Array.from( new Set( nextLines.map( ( l ) => String( l.lazada_order || '' ).trim() ).filter( Boolean ) ) );
        setMeta( ( m ) => ( {
            ...m,
            wsr_cost_price: totalCost,
            wsr_selling_price: totalPrice,
            wsr_bargained_price: totalPrice,
            wsr_lazada_order: lazada.length ? lazada.join( ', ' ) : m.wsr_lazada_order,
        } ) );
    };

    const addProduct = ( option ) => {
        if ( ! option ) return;
        const exists = lines.find( ( l ) => l.id === option.value );
        if ( exists ) {
            applyLines( lines.map( ( l ) => ( l.id === option.value ? { ...l, qty: ( Number( l.qty ) || 0 ) + 1 } : l ) ) );
        } else {
            applyLines( [ ...lines, { ...option.product, qty: 1 } ] );
        }
    };

    const updateLine = ( id, key, value ) =>
        applyLines( lines.map( ( l ) => ( l.id === id ? { ...l, [ key ]: value === '' ? '' : Number( value ) } : l ) ) );

    const removeLine = ( id ) => applyLines( lines.filter( ( l ) => l.id !== id ) );
    if ( isLoading ) {
        return <p>กำลังโหลดข้อมูลรายการขาย...</p>;
    }

    return (
        <div className="wsr-sales-panel component-container" style={{padding: '10px 0'}}>
            
            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '20px', marginBottom: '20px' }}>
                <div className="wsr-meta-field">
                    <TextControl
                        label="วันที่ซื้อขาย"
                        type="date"
                        value={ meta.wsr_sale_date || '' }
                        onChange={ ( value ) => updateMeta( 'wsr_sale_date', value ) }
                    />
                </div>
                <div className="wsr-meta-field">
                    <SelectControl
                        label="สถานะคำสั่งซื้อ"
                        value={ meta.wsr_status || 'pending' }
                        options={ [
                            { label: 'รอโอนเงิน / รอชำระ', value: 'pending' },
                            { label: 'ชำระเงินแล้ว / กำลังเตรียมจัดส่ง', value: 'paid' },
                            { label: 'จัดส่งแล้ว', value: 'shipped' },
                            { label: 'คืนเงิน / ยกเลิก', value: 'refunded' },
                        ] }
                        onChange={ ( value ) => updateMeta( 'wsr_status', value ) }
                    />
                </div>
            </div>

            <div className="wsr-meta-field" style={{marginBottom: '20px'}}>
                <TextControl
                    label="หมายเลขคำสั่งซื้อ Lazada (ถ้ามี)"
                    value={ meta.wsr_lazada_order || '' }
                    onChange={ ( value ) => updateMeta( 'wsr_lazada_order', value ) }
                />
            </div>

            <div className="wsr-meta-field" style={{marginBottom: '20px'}}>
                <SelectControl
                    label="รูปแบบการจัดส่ง"
                    value={ meta.wsr_delivery_method !== undefined ? meta.wsr_delivery_method : '' }
                    options={ [
                        { label: 'โปรดระบุ', value: '' },
                        { label: 'นัดรับ', value: 'pickup' },
                        { label: 'ส่ง EMS', value: 'ems' },
                        { label: 'แมสเซนเจอร์', value: 'messenger' },
                    ] }
                    onChange={ ( value ) => updateMeta( 'wsr_delivery_method', value ) }
                />
            </div>

            <hr style={{margin: '20px 0'}} />
            <h3 style={{marginTop: 0}}>ข้อมูลลูกค้าและสินค้า</h3>

            <div className="wsr-meta-field" style={{marginBottom: '15px'}}>
                <BaseControl label="เลือกลูกค้า">
                    <Select
                        value={selectedCustomer}
                        onChange={(option) => updateMeta('wsr_customer_id', option ? option.value : 0)}
                        options={customerOptions}
                        isClearable
                        placeholder="พิมพ์เพื่อค้นหาลูกค้า..."
                    />
                </BaseControl>
            </div>

            <div className="wsr-meta-field" style={{ marginBottom: '20px' }}>
                <h3 style={{ marginTop: 0 }}>เปรียบเทียบ Facebook (Customer vs Observed)</h3>
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '20px' }}>
                    <div style={{ background: '#fff', border: '1px solid #e2e8f0', borderRadius: '12px', padding: '12px' }}>
                        <div style={{ fontWeight: 700, marginBottom: '10px' }}>ข้อมูลใน Customer</div>
                        <div style={{ display: 'grid', gridTemplateColumns: '72px 1fr', gap: '12px' }}>
                            <div style={{ width: '72px', height: '72px', borderRadius: '12px', overflow: 'hidden', background: '#f1f5f9', border: '1px solid #e2e8f0' }}>
                                {customerAvatarPreviewUrl ? (
                                    <img src={customerAvatarPreviewUrl} alt="Customer Avatar" style={{ width: '100%', height: '100%', objectFit: 'cover' }} />
                                ) : null}
                            </div>
                            <div>
                                <div style={{ fontSize: '12px', color: '#64748b' }}>ลิงก์โปรไฟล์</div>
                                <div style={{ fontSize: '13px', fontWeight: 600, marginBottom: '8px' }}>{selectedCustomerProfile?.meta?.wsr_social_url || '-'}</div>
                                <div style={{ fontSize: '12px', color: '#64748b' }}>Flag</div>
                                <div style={{ fontSize: '13px', fontWeight: 600, marginBottom: '8px' }}>{selectedCustomerProfile?.meta?.wsr_fb_flag || 'ปกติ'}</div>
                                {selectedCustomerProfile?.meta?.wsr_fb_flag_note ? (
                                    <div style={{ fontSize: '12px', color: '#64748b' }}>{selectedCustomerProfile.meta.wsr_fb_flag_note}</div>
                                ) : null}
                            </div>
                        </div>
                    </div>

                    <div style={{ background: '#fff', border: '1px solid #e2e8f0', borderRadius: '12px', padding: '12px' }}>
                        <div style={{ fontWeight: 700, marginBottom: '10px' }}>Observed ตอนขาย (Snapshot)</div>
                        <div style={{ display: 'grid', gridTemplateColumns: '72px 1fr', gap: '12px', marginBottom: '10px' }}>
                            <div style={{ width: '72px', height: '72px', borderRadius: '12px', overflow: 'hidden', background: '#f1f5f9', border: '1px solid #e2e8f0' }}>
                                {observedAvatarPreviewUrl ? (
                                    <img src={observedAvatarPreviewUrl} alt="Observed Avatar" style={{ width: '100%', height: '100%', objectFit: 'cover' }} />
                                ) : null}
                            </div>
                            <div>
                                <TextControl
                                    label="Observed Social URL"
                                    value={ meta.wsr_observed_social_url || '' }
                                    onChange={ ( value ) => updateMeta( 'wsr_observed_social_url', value ) }
                                />
                            </div>
                        </div>

                        <TextControl
                            label="Observed Avatar URL"
                            value={ meta.wsr_observed_avatar_url || '' }
                            onChange={ ( value ) => updateMeta( 'wsr_observed_avatar_url', value ) }
                        />

                        <div style={{ marginTop: '10px' }}>
                            <MediaUploadCheck>
                                <MediaUpload
                                    onSelect={ ( media ) => updateMeta( 'wsr_observed_avatar_id', media?.id || '' ) }
                                    allowedTypes={ [ 'image' ] }
                                    value={ meta.wsr_observed_avatar_id || '' }
                                    render={ ( { open } ) => (
                                        <div style={{ display: 'flex', gap: '8px' }}>
                                            <Button variant="secondary" onClick={ open }>เลือกรูป Observed จาก Media</Button>
                                            { meta.wsr_observed_avatar_id ? (
                                                <Button variant="tertiary" isDestructive onClick={ () => updateMeta( 'wsr_observed_avatar_id', '' ) }>ล้าง</Button>
                                            ) : null }
                                        </div>
                                    ) }
                                />
                            </MediaUploadCheck>
                        </div>

                        <div style={{ display: 'flex', gap: '8px', marginTop: '12px' }}>
                            <Button
                                variant="secondary"
                                onClick={() => {
                                    const url = selectedCustomerProfile?.meta?.wsr_social_url || '';
                                    if (url) updateMeta('wsr_observed_social_url', String(url));
                                }}
                            >
                                คัดลอก Social URL จาก Customer
                            </Button>
                            <Button
                                variant="secondary"
                                onClick={() => {
                                    const url = selectedCustomerProfile?.meta?.wsr_fb_avatar_url || '';
                                    const id = selectedCustomerProfile?.meta?.wsr_fb_avatar_id || '';
                                    if (url) updateMeta('wsr_observed_avatar_url', String(url));
                                    if (id) updateMeta('wsr_observed_avatar_id', id);
                                }}
                            >
                                คัดลอก Avatar จาก Customer
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div className="wsr-meta-field" style={{marginBottom: '20px'}}>
                <BaseControl label="เพิ่มสินค้า (ค้นหาจาก WooCommerce)">
                    <AsyncSelect
                        value={ null }
                        loadOptions={ loadProductOptions }
                        defaultOptions
                        cacheOptions
                        onChange={ addProduct }
                        classNamePrefix="select"
                        placeholder="พิมพ์ชื่อสินค้าเพื่อค้นหา แล้วคลิกเพื่อเพิ่ม..."
                        noOptionsMessage={ () => 'ไม่พบสินค้า' }
                    />
                </BaseControl>
                { lines.length > 0 && (
                    <div style={{ border: '1px solid #e2e8f0', borderRadius: '12px', marginTop: '12px', overflowX: 'auto' }}>
                        <table style={{ width: '100%', borderCollapse: 'collapse', fontSize: '13px' }}>
                            <thead>
                                <tr style={{ background: '#f8fafc', textAlign: 'left' }}>
                                    <th style={{ padding: '8px 12px' }}>สินค้า</th>
                                    <th style={{ padding: '8px', width: '70px' }}>จำนวน</th>
                                    <th style={{ padding: '8px', width: '110px' }}>ต้นทุน/ชิ้น</th>
                                    <th style={{ padding: '8px', width: '110px' }}>ราคา/ชิ้น</th>
                                    <th style={{ padding: '8px', width: '40px' }}></th>
                                </tr>
                            </thead>
                            <tbody>
                                { lines.map( ( l ) => (
                                    <tr key={ l.id } style={{ borderTop: '1px solid #e2e8f0' }}>
                                        <td style={{ padding: '8px 12px' }}>{ l.name }</td>
                                        <td style={{ padding: '4px 8px' }}><input type="number" min="1" style={{ width: '100%' }} value={ l.qty } onChange={ ( e ) => updateLine( l.id, 'qty', e.target.value ) } /></td>
                                        <td style={{ padding: '4px 8px' }}><input type="number" min="0" style={{ width: '100%' }} value={ l.cost } onChange={ ( e ) => updateLine( l.id, 'cost', e.target.value ) } /></td>
                                        <td style={{ padding: '4px 8px' }}><input type="number" min="0" style={{ width: '100%' }} value={ l.price } onChange={ ( e ) => updateLine( l.id, 'price', e.target.value ) } /></td>
                                        <td style={{ padding: '4px 8px' }}><Button isDestructive variant="tertiary" onClick={ () => removeLine( l.id ) } aria-label="ลบ">ลบ</Button></td>
                                    </tr>
                                ) ) }
                            </tbody>
                        </table>
                    </div>
                ) }
                <p style={{fontSize: '11px', color: '#666', marginTop: '5px'}}>ปรับจำนวน/ต้นทุน/ราคาต่อชิ้นได้ ระบบจะรวมยอดต้นทุนและราคาขายให้อัตโนมัติ</p>
            </div>

            <hr style={{margin: '20px 0'}} />
            <h3 style={{marginTop: 0}}>สรุปข้อมูลการเงิน (คำนวณอัตโนมัติ)</h3>

            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '20px', marginBottom: '15px' }}>
                <TextControl
                    label="ต้นทุนสินค้าทั้งหมด"
                    type="number"
                    value={ meta.wsr_cost_price !== undefined ? meta.wsr_cost_price : '' }
                    onChange={ ( value ) => updateNumMeta( 'wsr_cost_price', value ) }
                />
                <TextControl
                    label="ราคาขายรวม"
                    type="number"
                    value={ meta.wsr_selling_price !== undefined ? meta.wsr_selling_price : '' }
                    onChange={ ( value ) => updateNumMeta( 'wsr_selling_price', value ) }
                />
            </div>
            
            <div className="wsr-meta-field" style={{marginBottom: '15px'}}>
                <TextControl
                    label="ราคาที่ตกลงขายจริง (ราคาหลังโดนต่อรอง)"
                    type="number"
                    value={ meta.wsr_bargained_price !== undefined ? meta.wsr_bargained_price : '' }
                    onChange={ ( value ) => updateNumMeta( 'wsr_bargained_price', value ) }
                />
            </div>

            {showPreviousBargainPrice ? (
                <div className="wsr-meta-field" style={{marginBottom: '15px'}}>
                    <TextControl
                        label="ราคาที่เคยต่อ (เคสเคยต่อราคาหนัก)"
                        type="number"
                        value={ meta.wsr_prev_bargain_price !== undefined ? meta.wsr_prev_bargain_price : '' }
                        onChange={ ( value ) => updateNumMeta( 'wsr_prev_bargain_price', value ) }
                    />
                </div>
            ) : null}

            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '20px' }}>
                <TextControl
                    label="ค่าจัดส่ง (ลูกค้าจ่ายให้)"
                    type="number"
                    value={ meta.wsr_shipping_paid !== undefined ? meta.wsr_shipping_paid : '' }
                    onChange={ ( value ) => updateNumMeta( 'wsr_shipping_paid', value ) }
                />
                <TextControl
                    label="ค่าจัดส่งจริง (ร้านจ่ายเอง)"
                    type="number"
                    value={ meta.wsr_shipping_actual !== undefined ? meta.wsr_shipping_actual : '' }
                    onChange={ ( value ) => updateNumMeta( 'wsr_shipping_actual', value ) }
                />
            </div>

            <div className={ `wsr-calculation-result ${ isProfitNegative ? 'wsr-profit-negative' : 'wsr-profit-positive' }` }>
                { hasBargain && (
                    <small style={ { display: 'block', marginBottom: '4px', color: '#64748b' } }>
                        กำไรปกติ (ราคาเต็ม { sellingPrice.toLocaleString() } THB): { regularProfit >= 0 ? '+' : '' }{ regularProfit.toLocaleString() } THB
                        { discountAmount < 0 && (
                            <span style={ { marginLeft: '8px', color: '#ef4444', fontWeight: 600 } }>
                                (ลดราคา { Math.abs( discountAmount ).toLocaleString() } THB)
                            </span>
                        ) }
                    </small>
                ) }
                <strong style={ { fontSize: '1.2em' } }>
                    กำไรสุทธิ{ hasBargain ? ' (หลังต่อราคา)' : '' }:{ ' ' }
                    <span style={ { color: isProfitNegative ? '#dc2626' : '#16a34a' } }>
                        { actualProfit >= 0 ? '+' : '' }{ actualProfit.toLocaleString() } THB
                    </span>
                </strong>
            </div>

            <hr style={{margin: '20px 0'}} />
            <h3 style={{marginTop: 0}}>หมายเหตุและหลักฐานการโอน</h3>

            <div className="wsr-meta-field">
                <TextareaControl
                    label="บันทึกภายใน (Notes)"
                    value={ meta.wsr_notes || '' }
                    onChange={ ( value ) => updateMeta( 'wsr_notes', value ) }
                    rows={ 4 }
                />
            </div>
            <div className="wsr-meta-field">
                <BaseControl label="หลักฐาน / สลิปโอนเงิน">
                    <p style={{fontSize: '12px', color: '#666'}}>ใช้กล่อง "รูปภาพประจำเรื่อง (Featured Image)" หรือ Add Media ด้านบนในการอัปโหลดสลิปใบเสร็จ</p>
                </BaseControl>
            </div>

            {/* Hidden Inputs for WordPress Classic Native Save */}
            <input type="hidden" name="wsr_sale_date" value={ meta.wsr_sale_date || '' } />
            <input type="hidden" name="wsr_status" value={ meta.wsr_status || 'pending' } />
            <input type="hidden" name="wsr_lazada_order" value={ meta.wsr_lazada_order || '' } />
            <input type="hidden" name="wsr_delivery_method" value={ meta.wsr_delivery_method !== undefined ? meta.wsr_delivery_method : '' } />
            <input type="hidden" name="wsr_customer_id" value={ meta.wsr_customer_id || 0 } />
            
            { selectedProductIds.map(id => (
                <input key={`prod-${id}`} type="hidden" name="wsr_product_ids[]" value={id} />
            )) }
            { selectedProductIds.length === 0 && (
                <input type="hidden" name="wsr_product_ids_empty" value="1" />
            )}

            <input type="hidden" name="wsr_cost_price" value={ meta.wsr_cost_price !== undefined ? meta.wsr_cost_price : '' } />
            <input type="hidden" name="wsr_selling_price" value={ meta.wsr_selling_price !== undefined ? meta.wsr_selling_price : '' } />
            <input type="hidden" name="wsr_bargained_price" value={ meta.wsr_bargained_price !== undefined ? meta.wsr_bargained_price : '' } />
            <input type="hidden" name="wsr_prev_bargain_price" value={ meta.wsr_prev_bargain_price !== undefined ? meta.wsr_prev_bargain_price : '' } />
            <input type="hidden" name="wsr_shipping_paid" value={ meta.wsr_shipping_paid !== undefined ? meta.wsr_shipping_paid : '' } />
            <input type="hidden" name="wsr_shipping_actual" value={ meta.wsr_shipping_actual !== undefined ? meta.wsr_shipping_actual : '' } />
            <input type="hidden" name="wsr_observed_social_url" value={ meta.wsr_observed_social_url || '' } />
            <input type="hidden" name="wsr_observed_avatar_url" value={ meta.wsr_observed_avatar_url || '' } />
            <input type="hidden" name="wsr_observed_avatar_id" value={ meta.wsr_observed_avatar_id || '' } />
            <textarea style={{display: 'none'}} name="wsr_notes" value={ meta.wsr_notes || '' } readOnly />
        </div>
    );
};

export default SalesRecordMetaFields;
