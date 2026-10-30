import { useState, useEffect } from '@wordpress/element';
import { Spinner } from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';
import { 
    RiDashboardLine, 
    RiMoneyDollarCircleLine, 
    RiShoppingBag3Line, 
    RiUser3Line, 
    RiArrowRightUpLine, 
    RiRefreshLine 
} from '@remixicon/react';

const DashboardApp = () => {
    const [ records, setRecords ] = useState( [] );
    const [ customers, setCustomers ] = useState( {} );
    const [ loading, setLoading ] = useState( true );

    const fetchDashboardData = async () => {
        setLoading( true );
        try {
            // Fetch Customers for mapping
            const custRes = await apiFetch( { path: '/wp/v2/customers?per_page=100' } );
            const custMap = {};
            custRes.forEach( c => custMap[ c.id ] = c.title.rendered );
            setCustomers( custMap );

            // Fetch Sales Records
            const salesRes = await apiFetch( { path: '/wp/v2/sales_records?per_page=100' } );
            setRecords( salesRes );
            setLoading( false );
        } catch ( error ) {
            console.error( 'Error fetching data:', error );
            setLoading( false );
        }
    };

    useEffect( () => {
        fetchDashboardData();
    }, [] );

    if ( loading ) {
        return (
            <div className="wsr-state-loading" style={{ padding: '60px 0', textAlign: 'center' }}>
                <Spinner />
                <p style={{ marginTop: '12px', color: '#64748b', fontSize: '13px' }}>กำลังโหลดข้อมูล Sales Dashboard...</p>
            </div>
        );
    }

    // Totals calculation
    let totalSales = 0;
    let totalCost = 0;
    let totalNetProfit = 0;
    let paidOrdersCount = 0;

    records.forEach( record => {
        const meta = record.meta || {};
        const costPrice = Number( meta.wsr_cost_price ) || 0;
        const bargainedPrice = Number( meta.wsr_bargained_price ) || 0;
        const shippingPaid = Number( meta.wsr_shipping_paid ) || 0;
        const shippingActual = Number( meta.wsr_shipping_actual ) || 0;
        const netProfit = ( bargainedPrice - costPrice ) + ( shippingPaid - shippingActual );

        totalSales += bargainedPrice;
        totalCost += costPrice;
        totalNetProfit += netProfit;
        if ( meta.wsr_status === 'paid' ) {
            paidOrdersCount++;
        }
    } );

    const profitMargin = totalSales > 0 ? ( ( totalNetProfit / totalSales ) * 100 ).toFixed( 1 ) : '0.0';

    return (
        <div className="wsr-modern-dashboard" style={{ marginTop: '16px' }}>
            {/* Top Toolbar */}
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '20px' }}>
                <div>
                    <h2 style={{ fontSize: '18px', fontWeight: 700, margin: '0 0 4px 0', color: '#0f172a' }}>ภาพรวมการขายและกำไรสุทธิ</h2>
                    <p style={{ margin: 0, fontSize: '12px', color: '#64748b' }}>สรุปข้อมูลคำสั่งซื้อทั้งหมดที่บันทึกในระบบ Sales Record</p>
                </div>
                <div style={{ display: 'flex', gap: '8px' }}>
                    <button
                        type="button"
                        onClick={ fetchDashboardData }
                        className="button"
                        style={{ display: 'inline-flex', alignItems: 'center', gap: '6px', fontSize: '12px', height: '34px', borderRadius: '8px' }}
                    >
                        <RiRefreshLine size={14} /> รีเฟรช
                    </button>
                    <a
                        href="/wp-admin/admin.php?page=wsr-pos"
                        className="button button-primary"
                        style={{ display: 'inline-flex', alignItems: 'center', gap: '6px', fontSize: '12px', height: '34px', borderRadius: '8px', background: '#4169F7', borderColor: '#4169F7' }}
                    >
                        <RiShoppingBag3Line size={14} /> เพิ่มออเดอร์ (POS)
                    </a>
                </div>
            </div>

            {/* Metric KPI Cards (Clean SaaS Style) */}
            <div className="wsr-stat-grid" style={{ marginBottom: '24px' }}>
                <div className="wsr-stat-card wsr-stat-card--blue">
                    <div className="wsr-stat-label">ยอดขายรวม (Total Revenue)</div>
                    <div className="wsr-stat-value">฿{ totalSales.toLocaleString() }</div>
                    <div className="wsr-stat-sub">จาก { records.length } ออเดอร์ทั้งหมด</div>
                </div>

                <div className="wsr-stat-card wsr-stat-card--green">
                    <div className="wsr-stat-label">กำไรสุทธิ (Net Profit)</div>
                    <div className="wsr-stat-value" style={{ color: totalNetProfit >= 0 ? '#10b981' : '#ef4444' }}>
                        ฿{ totalNetProfit.toLocaleString() }
                    </div>
                    <div className="wsr-stat-sub">Margin: { profitMargin }%</div>
                </div>

                <div className="wsr-stat-card wsr-stat-card--purple">
                    <div className="wsr-stat-label">ต้นทุนรวมสินค้า (Total Cost)</div>
                    <div className="wsr-stat-value">฿{ totalCost.toLocaleString() }</div>
                    <div className="wsr-stat-sub">คิดเป็น { totalSales > 0 ? ( ( totalCost / totalSales ) * 100 ).toFixed( 1 ) : 0 }% ของยอดขาย</div>
                </div>

                <div className="wsr-stat-card wsr-stat-card--orange">
                    <div className="wsr-stat-label">ออเดอร์ชำระแล้ว (Paid Rate)</div>
                    <div className="wsr-stat-value">{ paidOrdersCount } / { records.length }</div>
                    <div className="wsr-stat-sub">ความสำเร็จ { records.length > 0 ? ( ( paidOrdersCount / records.length ) * 100 ).toFixed( 0 ) : 0 }%</div>
                </div>
            </div>

            {/* Recent Orders Table Card */}
            <div className="wsr-chart-card">
                <div className="wsr-chart-card__header" style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                    <h3 className="wsr-chart-card__title">รายการขายล่าสุด (Recent Orders)</h3>
                    <a href="/wp-admin/edit.php?post_type=sales_record" style={{ fontSize: '12px', color: '#4169F7', textDecoration: 'none', fontWeight: 600 }}>
                        ดูออเดอร์ทั้งหมด &rarr;
                    </a>
                </div>
                <div className="wsr-chart-card__body" style={{ padding: 0 }}>
                    <div style={{ overflowX: 'auto' }}>
                        <table style={{ width: '100%', borderCollapse: 'collapse', fontSize: '13px', textAlign: 'left' }}>
                            <thead>
                                <tr style={{ background: '#f8fafc', borderBottom: '1px solid #e2e8f0', color: '#64748b' }}>
                                    <th style={{ padding: '12px 16px', fontWeight: 600 }}>รหัสออเดอร์ (SKU)</th>
                                    <th style={{ padding: '12px 16px', fontWeight: 600 }}>วันที่</th>
                                    <th style={{ padding: '12px 16px', fontWeight: 600 }}>ลูกค้า</th>
                                    <th style={{ padding: '12px 16px', fontWeight: 600, textAlign: 'right' }}>ยอดขาย (฿)</th>
                                    <th style={{ padding: '12px 16px', fontWeight: 600, textAlign: 'right' }}>กำไรสุทธิ (฿)</th>
                                    <th style={{ padding: '12px 16px', fontWeight: 600 }}>สถานะ</th>
                                    <th style={{ padding: '12px 16px', fontWeight: 600, textAlign: 'center' }}>จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                { records.length === 0 ? (
                                    <tr>
                                        <td colSpan="7" style={{ padding: '36px', textAlign: 'center', color: '#94a3b8' }}>
                                            ยังไม่มีรายการขายในระบบ
                                        </td>
                                    </tr>
                                ) : (
                                    records.map( record => {
                                        const meta = record.meta || {};
                                        const customerName = customers[ meta.wsr_customer_id ] || '—';
                                        const costPrice = Number( meta.wsr_cost_price ) || 0;
                                        const bargainedPrice = Number( meta.wsr_bargained_price ) || 0;
                                        const shippingPaid = Number( meta.wsr_shipping_paid ) || 0;
                                        const shippingActual = Number( meta.wsr_shipping_actual ) || 0;
                                        const netProfit = ( bargainedPrice - costPrice ) + ( shippingPaid - shippingActual );

                                        const isPaid = meta.wsr_status === 'paid';
                                        const isPending = meta.wsr_status === 'pending';

                                        return (
                                            <tr key={ record.id } style={{ borderBottom: '1px solid #f1f5f9', transition: 'background 0.1s' }}>
                                                <td style={{ padding: '12px 16px', fontWeight: 600, color: '#0f172a' }}>
                                                    { record.title.rendered || `ORD-${ record.id }` }
                                                </td>
                                                <td style={{ padding: '12px 16px', color: '#64748b' }}>
                                                    { meta.wsr_sale_date || '—' }
                                                </td>
                                                <td style={{ padding: '12px 16px', color: '#334155', fontWeight: 500 }}>
                                                    { customerName }
                                                </td>
                                                <td style={{ padding: '12px 16px', textAlign: 'right', fontWeight: 600, color: '#0f172a' }}>
                                                    ฿{ bargainedPrice.toLocaleString() }
                                                </td>
                                                <td style={{ padding: '12px 16px', textAlign: 'right', fontWeight: 700 }}>
                                                    <span style={{
                                                        display: 'inline-block',
                                                        padding: '3px 8px',
                                                        borderRadius: '6px',
                                                        fontSize: '12px',
                                                        background: netProfit >= 0 ? '#f0fdf4' : '#fef2f2',
                                                        color: netProfit >= 0 ? '#15803d' : '#b91c1c',
                                                        border: `1px solid ${ netProfit >= 0 ? '#bbf7d0' : '#fecaca' }`
                                                    }}>
                                                        ฿{ netProfit.toLocaleString() }
                                                    </span>
                                                </td>
                                                <td style={{ padding: '12px 16px' }}>
                                                    <span style={{
                                                        display: 'inline-block',
                                                        padding: '3px 8px',
                                                        borderRadius: '999px',
                                                        fontSize: '11px',
                                                        fontWeight: 600,
                                                        background: isPaid ? '#eff6ff' : ( isPending ? '#fffbeb' : '#f8fafc' ),
                                                        color: isPaid ? '#1d4ed8' : ( isPending ? '#b45309' : '#64748b' ),
                                                        border: `1px solid ${ isPaid ? '#bfdbfe' : ( isPending ? '#fde68a' : '#e2e8f0' ) }`
                                                    }}>
                                                        { meta.wsr_status || 'draft' }
                                                    </span>
                                                </td>
                                                <td style={{ padding: '12px 16px', textAlign: 'center' }}>
                                                    <a
                                                        href={ `/wp-admin/post.php?post=${ record.id }&action=edit` }
                                                        className="wsr-edit-btn"
                                                    >
                                                        เปิดดู
                                                    </a>
                                                </td>
                                            </tr>
                                        );
                                    } )
                                ) }
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default DashboardApp;
