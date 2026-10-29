import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

const DashboardApp = () => {
    const [ records, setRecords ] = useState( [] );
    const [ customers, setCustomers ] = useState( {} );
    const [ loading, setLoading ] = useState( true );

    useEffect( () => {
        const fetchDashboardData = async () => {
             try {
                // Fetch Customers for mapping
                const custRes = await apiFetch({ path: '/wp/v2/customers?per_page=100' });
                const custMap = {};
                custRes.forEach(c => custMap[c.id] = c.title.rendered);
                setCustomers(custMap);

                // Fetch Sales Records
                const salesRes = await apiFetch({ path: '/wp/v2/sales_records?per_page=50' });
                setRecords(salesRes);
                setLoading(false);
             } catch (error) {
                 console.error("Error fetching data:", error);
                 setLoading(false);
             }
        };

        fetchDashboardData();
    }, [] );

    if ( loading ) {
        return <p>Loading Data...</p>;
    }

    // Totals calculation
    let totalSales = 0;
    let totalNetProfit = 0;

    const tableRows = records.map( record => {
        const meta = record.meta || {};
        const customerName = customers[meta.wsr_customer_id] || 'Unknown';
        
        const costPrice = Number(meta.wsr_cost_price) || 0;
        const bargainedPrice = Number(meta.wsr_bargained_price) || 0;
        const shippingPaid = Number(meta.wsr_shipping_paid) || 0;
        const shippingActual = Number(meta.wsr_shipping_actual) || 0;
        
        const netProfit = bargainedPrice - costPrice + shippingPaid - shippingActual;
        
        totalSales += bargainedPrice;
        totalNetProfit += netProfit;

        return (
            <tr key={record.id}>
                <td>{record.title.rendered}</td>
                <td>{meta.wsr_sale_date}</td>
                <td>{customerName}</td>
                <td>{bargainedPrice}</td>
                <td>
                    <span className={netProfit < 0 ? 'wsr-profit-negative' : 'wsr-profit-positive'} style={{padding: '2px 6px', borderRadius: '3px'}}>
                        {netProfit}
                    </span>
                </td>
                <td>{meta.wsr_status}</td>
            </tr>
        );
    });

    return (
        <div>
            <div style={{ display: 'flex', gap: '20px', marginBottom: '20px' }}>
                <div style={{ background: '#fff', padding: '15px', border: '1px solid #ccd0d4', borderRadius: '4px', flex: 1 }}>
                    <h3 style={{ margin: '0 0 10px 0' }}>Total Sales (Displayed)</h3>
                    <h2 style={{ margin: 0, color: '#2271b1' }}>{totalSales} THB</h2>
                </div>
                <div style={{ background: '#fff', padding: '15px', border: '1px solid #ccd0d4', borderRadius: '4px', flex: 1 }}>
                    <h3 style={{ margin: '0 0 10px 0' }}>Total Net Profit</h3>
                    <h2 style={{ margin: 0, color: totalNetProfit >= 0 ? '#155724' : '#721c24' }}>{totalNetProfit} THB</h2>
                </div>
            </div>

            <table className="wp-list-table widefat fixed striped table-view-list">
                <thead>
                    <tr>
                        <th style={{width: '15%'}}>Order SKU</th>
                        <th style={{width: '15%'}}>Date</th>
                        <th style={{width: '20%'}}>Customer</th>
                        <th style={{width: '15%'}}>Final Deal (THB)</th>
                        <th style={{width: '15%'}}>Net Profit</th>
                        <th style={{width: '20%'}}>Status</th>
                    </tr>
                </thead>
                <tbody>
                    { records.length > 0 ? tableRows : <tr><td colSpan="6">No records found.</td></tr> }
                </tbody>
            </table>
        </div>
    );
};

export default DashboardApp;
