import { useState, useEffect, useRef } from '@wordpress/element';
import { Spinner } from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';
import * as echarts from 'echarts';

const OverviewTab = ({ query }) => {
    const [data, setData] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const chartRef = useRef(null);
    const chartInstance = useRef(null);

    useEffect(() => {
        setLoading(true);
        setError('');

        const fetchData = async () => {
            try {
                const response = await apiFetch({ path: `/wsr/v1/graph/overview${query}` });
                setData(response);
                setLoading(false);
            } catch (e) {
                console.error('Error fetching overview data:', e);
                setError('โหลดข้อมูลไม่สำเร็จ');
                setLoading(false);
            }
        };

        fetchData();
    }, [query]);

    useEffect(() => {
        if (data && chartRef.current) {
            if (chartInstance.current) {
                chartInstance.current.dispose();
            }

            const chart = echarts.init(chartRef.current);
            chartInstance.current = chart;

            const option = {
                tooltip: {
                    trigger: 'item',
                },
                legend: {
                    top: '5%',
                    left: 'center',
                },
                series: [
                    {
                        name: 'สถิติ',
                        type: 'pie',
                        radius: ['40%', '70%'],
                        avoidLabelOverlap: false,
                        itemStyle: {
                            borderRadius: 10,
                            borderColor: '#fff',
                            borderWidth: 2,
                        },
                        label: {
                            show: false,
                            position: 'center',
                        },
                        emphasis: {
                            label: {
                                show: true,
                                fontSize: 20,
                                fontWeight: 'bold',
                            },
                        },
                        labelLine: {
                            show: false,
                        },
                        data: [
                            { value: data.total_orders - data.shipped_count, name: 'รอจัดส่ง' },
                            { value: data.shipped_count, name: 'จัดส่งแล้ว' },
                        ],
                    },
                ],
            };

            chart.setOption(option);

            const handleResize = () => chart.resize();
            window.addEventListener('resize', handleResize);

            return () => {
                window.removeEventListener('resize', handleResize);
                chart.dispose();
            };
        }
    }, [data]);

    if (loading) return <div className="wsr-state-loading"><Spinner /></div>;
    if (error) return <div className="wsr-state-error">{error}</div>;
    if (!data) return <div className="wsr-state-empty">ไม่มีข้อมูล</div>;

    return (
        <div className="wsr-overview-tab">
            <div className="wsr-stat-grid">
                <div className="wsr-stat-card wsr-stat-card--blue">
                    <div className="wsr-stat-label">ยอดขายรวม</div>
                    <div className="wsr-stat-value">฿{Number(data.total_sales).toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</div>
                </div>
                <div className="wsr-stat-card wsr-stat-card--green">
                    <div className="wsr-stat-label">ออเดอร์ทั้งหมด</div>
                    <div className="wsr-stat-value">{data.total_orders.toLocaleString()}</div>
                </div>
                <div className="wsr-stat-card wsr-stat-card--purple">
                    <div className="wsr-stat-label">ค่าเฉลี่ยต่อออเดอร์</div>
                    <div className="wsr-stat-value">฿{Number(data.average_order).toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</div>
                </div>
                <div className="wsr-stat-card wsr-stat-card--orange">
                    <div className="wsr-stat-label">จัดส่งแล้ว</div>
                    <div className="wsr-stat-value">{data.shipped_count.toLocaleString()}</div>
                    <div className="wsr-stat-sub">จาก {data.total_orders} ออเดอร์</div>
                </div>
                <div className="wsr-stat-card wsr-stat-card--cyan">
                    <div className="wsr-stat-label">ลูกค้าทั้งหมด</div>
                    <div className="wsr-stat-value">{data.total_customers.toLocaleString()}</div>
                    <div className="wsr-stat-sub">ซื้อแล้ว {data.active_customers} คน</div>
                </div>
                <div className="wsr-stat-card wsr-stat-card--green">
                    <div className="wsr-stat-label">สินค้าทั้งหมด</div>
                    <div className="wsr-stat-value">{data.total_products.toLocaleString()}</div>
                    <div className="wsr-stat-sub">มีขาย {data.active_products} รายการ</div>
                </div>
            </div>
            <div className="wsr-chart-card">
                <div className="wsr-chart-card__header">
                    <h3 className="wsr-chart-card__title">ภาพรวมสถานะการจัดส่ง</h3>
                </div>
                <div className="wsr-chart-card__body">
                    <div ref={chartRef} style={{ width: '100%', height: '360px' }}></div>
                </div>
            </div>
            {data.recent_orders && data.recent_orders.length > 0 && (
                <div className="wsr-chart-card">
                    <div className="wsr-chart-card__header">
                        <h3 className="wsr-chart-card__title">รายการสั่งซื้อล่าสุดไม่เกิน 7 วัน ({data.recent_orders.length} รายการ)</h3>
                    </div>
                    <div className="wsr-list">
                        {data.recent_orders.map((item) => {
                            const statusColors = {
                                'pending': '#f59e0b',
                                'paid': '#10b981',
                                'shipped': '#3b82f6',
                                'refunded': '#ef4444'
                            };
                            return (
                                <div key={item.id} className="wsr-list-item">
                                    <span className="wsr-list-name" style={{ minWidth: '150px' }}>{item.title}</span>
                                    <div className="wsr-list-stats">
                                        <div className="wsr-list-stat" style={{ width: '120px', textAlign: 'left', flex: 'none' }}>
                                            <div className="wsr-list-stat__value" style={{ fontSize: '13px' }}>{item.customer}</div>
                                            <div className="wsr-list-stat__label">ลูกค้า</div>
                                        </div>
                                        <div className="wsr-list-stat" style={{ width: '100px', flex: 'none' }}>
                                            <div className="wsr-list-stat__value" style={{ fontSize: '13px' }}>{item.date}</div>
                                            <div className="wsr-list-stat__label">วันที่</div>
                                        </div>
                                        <div className="wsr-list-stat" style={{ width: '80px', flex: 'none' }}>
                                            <span style={{
                                                backgroundColor: statusColors[item.status] || '#666',
                                                color: 'white',
                                                padding: '3px 8px',
                                                borderRadius: '4px',
                                                fontSize: '11px',
                                                fontWeight: '600',
                                                display: 'inline-block'
                                            }}>
                                                {item.status_label}
                                            </span>
                                        </div>
                                        <div className="wsr-list-stat" style={{ width: '100px', flex: 'none' }}>
                                            <div className="wsr-list-stat__value">฿{item.price.toLocaleString('th-TH', { maximumFractionDigits: 0 })}</div>
                                            <div className="wsr-list-stat__label">ยอดขาย</div>
                                        </div>
                                        <a href={item.edit_link} className="wsr-edit-btn" style={{ marginLeft: 'auto' }} target="_blank" rel="noreferrer">ตรวจสอบ</a>
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                </div>
            )}
        </div>
    );
};

export default OverviewTab;
