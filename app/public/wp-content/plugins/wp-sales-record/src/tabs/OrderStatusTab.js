import { useState, useEffect, useRef } from '@wordpress/element';
import { Spinner } from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';
import * as echarts from 'echarts';

const OrderStatusTab = ({ query }) => {
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
                const response = await apiFetch({ path: `/wsr/v1/graph/status${query}` });
                setData(response);
                setLoading(false);
            } catch (error) {
                console.error('Error fetching status data:', error);
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
                        name: 'สถานะ',
                        type: 'pie',
                        radius: '50%',
                        data: [
                            { value: data.pending, name: 'รอโอนเงิน', itemStyle: { color: '#f59e0b' } },
                            { value: data.paid, name: 'ชำระแล้ว', itemStyle: { color: '#10b981' } },
                            { value: data.shipped, name: 'จัดส่งแล้ว', itemStyle: { color: '#3b82f6' } },
                            { value: data.refunded, name: 'คืนเงิน', itemStyle: { color: '#ef4444' } },
                        ],
                        emphasis: {
                            itemStyle: {
                                shadowBlur: 10,
                                shadowOffsetX: 0,
                                shadowColor: 'rgba(0, 0, 0, 0.5)',
                            },
                        },
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
        <div className="wsr-order-status-tab">
            <div className="wsr-stat-grid">
                <div className="wsr-stat-card wsr-stat-card--yellow">
                    <div className="wsr-stat-label">รอโอนเงิน</div>
                    <div className="wsr-stat-value">{data.pending}</div>
                </div>
                <div className="wsr-stat-card wsr-stat-card--green">
                    <div className="wsr-stat-label">ชำระแล้ว</div>
                    <div className="wsr-stat-value">{data.paid}</div>
                </div>
                <div className="wsr-stat-card wsr-stat-card--blue">
                    <div className="wsr-stat-label">จัดส่งแล้ว</div>
                    <div className="wsr-stat-value">{data.shipped}</div>
                </div>
                <div className="wsr-stat-card wsr-stat-card--red">
                    <div className="wsr-stat-label">คืนเงิน</div>
                    <div className="wsr-stat-value">{data.refunded}</div>
                </div>
            </div>
            <div className="wsr-chart-card">
                <div className="wsr-chart-card__header">
                    <h3 className="wsr-chart-card__title">สัดส่วนสถานะคำสั่งซื้อ</h3>
                </div>
                <div className="wsr-chart-card__body">
                    <div ref={chartRef} style={{ width: '100%', height: '400px' }}></div>
                </div>
            </div>
        </div>
    );
};

export default OrderStatusTab;
