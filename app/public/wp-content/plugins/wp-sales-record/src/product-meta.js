import { useState, useEffect } from '@wordpress/element';
import { TextControl } from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';

const ProductMetaFields = ( { postId } ) => {
    const [ meta, setMeta ] = useState( {} );
    const [ isLoading, setIsLoading ] = useState( true );

    // Fetch existing meta
    useEffect( () => {
        apiFetch( { path: `/wp/v2/products/${postId}` } ).then( ( post ) => {
            setMeta( post.meta || {} );
            setIsLoading( false );
        } );
    }, [ postId ] );

    const updateMeta = ( key, value ) => {
        const newMeta = { ...meta, [ key ]: value };
        setMeta( newMeta );
    };

    const updateNumMeta = ( key, value ) => {
        const numValue = value === '' ? '' : Number( value );
        updateMeta( key, numValue );
    };

    // --- Profit preview calculation ---
    const defaultCost  = Number( meta.wsr_default_cost )  || 0;
    const defaultPrice = Number( meta.wsr_default_price ) || 0;
    const estimatedProfit = defaultPrice - defaultCost;
    const profitMargin   = defaultPrice > 0
        ? ( ( estimatedProfit / defaultPrice ) * 100 ).toFixed( 1 )
        : '0.0';
    const isProfitNegative = estimatedProfit < 0;

    if ( isLoading ) {
        return <p>กำลังโหลดข้อมูลสินค้า...</p>;
    }

    return (
        <div className="wsr-product-panel">
            <div className="wsr-meta-field" style={ { marginBottom: '20px' } }>
                <TextControl
                    label="วันที่ซื้อสินค้า (เข้าสต็อก)"
                    type="date"
                    value={ meta.wsr_purchase_date || '' }
                    onChange={ ( value ) => updateMeta( 'wsr_purchase_date', value ) }
                    help="วันที่ทำการสั่งซื้อหรือนำสินค้านี้เข้ามา"
                />
            </div>
            <div className="wsr-meta-field">
                <TextControl
                    label="หมายเลขคำสั่งซื้อ Lazada"
                    value={ meta.wsr_lazada_order || '' }
                    onChange={ ( value ) => updateMeta( 'wsr_lazada_order', value ) }
                />
            </div>

            <hr style={ { margin: '20px 0' } } />
            <h3 style={ { marginTop: 0 } }>ราคาอ้างอิง &amp; กำไรประมาณการ</h3>

            <div style={ { display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '20px', marginBottom: '15px' } }>
                <TextControl
                    label="ราคาทุนอ้างอิง (ต้นทุน)"
                    type="number"
                    value={ meta.wsr_default_cost !== undefined ? meta.wsr_default_cost : '' }
                    onChange={ ( value ) => updateNumMeta( 'wsr_default_cost', value ) }
                    help="ราคาทุนตั้งต้นสำหรับสินค้านี้"
                />
                <TextControl
                    label="ราคาขายอ้างอิง"
                    type="number"
                    value={ meta.wsr_default_price !== undefined ? meta.wsr_default_price : '' }
                    onChange={ ( value ) => updateNumMeta( 'wsr_default_price', value ) }
                    help="ราคาขายตั้งต้นสำหรับสินค้านี้"
                />
            </div>

            { ( defaultCost > 0 || defaultPrice > 0 ) && (
                <div
                    className={ `wsr-calculation-result ${ isProfitNegative ? 'wsr-profit-negative' : 'wsr-profit-positive' }` }
                    style={ {
                        padding: '12px 16px',
                        borderRadius: '8px',
                        marginBottom: '20px',
                        background: isProfitNegative ? '#fef2f2' : '#f0fdf4',
                        border: `1px solid ${ isProfitNegative ? '#fca5a5' : '#86efac' }`,
                    } }
                >
                    <div style={ { display: 'flex', justifyContent: 'space-between', alignItems: 'center', flexWrap: 'wrap', gap: '8px' } }>
                        <div>
                            <small style={ { display: 'block', color: '#64748b', marginBottom: '2px' } }>
                                กำไรประมาณการ (ราคาขาย − ราคาทุน)
                            </small>
                            <strong style={ { fontSize: '1.3em', color: isProfitNegative ? '#dc2626' : '#16a34a' } }>
                                { estimatedProfit >= 0 ? '+' : '' }{ estimatedProfit.toLocaleString() } THB
                            </strong>
                        </div>
                        <div
                            style={ {
                                padding: '4px 12px',
                                borderRadius: '20px',
                                background: isProfitNegative ? '#dc2626' : '#16a34a',
                                color: '#fff',
                                fontWeight: 700,
                                fontSize: '0.9em',
                            } }
                        >
                            Margin { profitMargin }%
                        </div>
                    </div>
                </div>
            ) }

            {/* Hidden Inputs for WordPress Classic Native Save */}
            <input type="hidden" name="wsr_purchase_date" value={ meta.wsr_purchase_date || '' } />
            <input type="hidden" name="wsr_lazada_order" value={ meta.wsr_lazada_order || '' } />
            <input type="hidden" name="wsr_default_cost" value={ meta.wsr_default_cost !== undefined ? meta.wsr_default_cost : '' } />
            <input type="hidden" name="wsr_default_price" value={ meta.wsr_default_price !== undefined ? meta.wsr_default_price : '' } />
        </div>
    );
};

export default ProductMetaFields;
