import { useState, useEffect, useRef } from '@wordpress/element';
import { Spinner } from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';
import * as echarts from 'echarts';
import { RiVipCrownFill, RiMedalFill, RiStarFill } from '@remixicon/react';

const CustomersTab = ({ query }) => {
    const [data, setData] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const [chartType, setChartType] = useState('pie'); // 'pie', 'stacked-line', 'bar-race'
    const chartRef = useRef(null);
    const chartInstance = useRef(null);
    const revenueChartRef = useRef(null);
    const revenueChartInstance = useRef(null);

    useEffect(() => {
        const fetchData = async () => {
            try {
                const response = await apiFetch({ path: `/wsr/v1/graph/customers${query}` });
                setData(response);
                setLoading(false);
            } catch (error) {
                console.error('Error fetching customers data:', error);
                setError('โหลดข้อมูลไม่สำเร็จ');
                setLoading(false);
            }
        };

        fetchData();
    }, [query]);

    useEffect(() => {
        if (data && data.items && data.items.length > 0 && chartRef.current) {
            if (chartInstance.current) {
                chartInstance.current.dispose();
            }

            const chart = echarts.init(chartRef.current);
            chartInstance.current = chart;

            const names = data.items.map(item => item.name);
            const orders = data.items.map(item => item.orders);

            let option;

            if (chartType === 'pie') {
                const pieData = names.map((name, index) => ({
                    name: name,
                    value: orders[index],
                }));

                option = {
                    tooltip: {
                        trigger: 'item',
                        formatter: '{b}: {c} ({d}%)',
                    },
                    legend: {
                        type: 'scroll',
                        orient: 'vertical',
                        right: 10,
                        top: 20,
                        bottom: 20,
                    },
                    series: [
                        {
                            name: 'จำนวนออเดอร์',
                            type: 'pie',
                            radius: '70%',
                            data: pieData,
                            emphasis: {
                                itemStyle: {
                                    shadowBlur: 10,
                                    shadowOffsetX: 0,
                                    shadowColor: 'rgba(0, 0, 0, 0.5)',
                                },
                            },
                            label: {
                                formatter: '{b}: {c} ({d}%)',
                            },
                        },
                    ],
                };
            } else {
                option = {
                    tooltip: {
                        trigger: 'axis',
                        axisPointer: chartType === 'stacked-line' ? { type: 'line' } : { type: 'shadow' },
                    },
                    xAxis: {
                        type: 'category',
                        data: names,
                        axisLabel: {
                            rotate: 45,
                        },
                    },
                    yAxis: {
                        type: 'value',
                    },
                    series: [
                        {
                            name: 'จำนวนออเดอร์',
                            type: chartType === 'stacked-line' ? 'line' : 'bar',
                            smooth: chartType === 'stacked-line',
                            data: orders,
                            itemStyle: {
                                color: '#06b6d4',
                            },
                            animationDuration: chartType === 'bar-race' ? 1000 : undefined,
                            animationEasing: chartType === 'bar-race' ? 'cubicOut' : undefined,
                            areaStyle: chartType === 'stacked-line' ? {
                                color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                                    { offset: 0, color: 'rgba(6, 182, 212, 0.5)' },
                                    { offset: 1, color: 'rgba(6, 182, 212, 0.1)' }
                                ]),
                            } : undefined,
                        },
                    ],
                };
            }

            chart.setOption(option);

            const handleResize = () => chart.resize();
            window.addEventListener('resize', handleResize);

            return () => {
                window.removeEventListener('resize', handleResize);
                chart.dispose();
            };
        }
    }, [data, chartType]);

    useEffect(() => {
        if (data && data.items && data.items.length > 0 && revenueChartRef.current) {
            if (revenueChartInstance.current) {
                revenueChartInstance.current.dispose();
            }
            const chart = echarts.init(revenueChartRef.current);
            revenueChartInstance.current = chart;
            const names = data.items.map(item => item.name);
            const revenues = data.items.map(item => Number(item.revenue || 0));

            let option;

            if (chartType === 'pie') {
                const pieData = names.map((name, index) => ({
                    name: name,
                    value: revenues[index],
                }));

                option = {
                    tooltip: {
                        trigger: 'item',
                        formatter: '{b}: ฿{c} ({d}%)',
                    },
                    legend: {
                        type: 'scroll',
                        orient: 'vertical',
                        right: 10,
                        top: 20,
                        bottom: 20,
                    },
                    series: [
                        {
                            name: 'ยอดขาย (฿)',
                            type: 'pie',
                            radius: '70%',
                            data: pieData,
                            emphasis: {
                                itemStyle: {
                                    shadowBlur: 10,
                                    shadowOffsetX: 0,
                                    shadowColor: 'rgba(0, 0, 0, 0.5)',
                                },
                            },
                            label: {
                                formatter: '{b}: ฿{c} ({d}%)',
                            },
                        },
                    ],
                };
            } else {
                option = {
                    tooltip: { trigger: 'axis', axisPointer: chartType === 'stacked-line' ? { type: 'line' } : { type: 'shadow' } },
                    grid: { left: '3%', right: '4%', bottom: '3%', containLabel: true },
                    xAxis: { type: 'value', axisLabel: { formatter: (v) => `฿${v.toLocaleString()}` } },
                    yAxis: { type: 'category', data: names },
                    series: [{ name: 'ยอดขาย (฿)', type: chartType === 'stacked-line' ? 'line' : 'bar', smooth: chartType === 'stacked-line', data: revenues, itemStyle: { color: '#06b6d4' }, animationDuration: chartType === 'bar-race' ? 1000 : undefined, animationEasing: chartType === 'bar-race' ? 'cubicOut' : undefined, areaStyle: chartType === 'stacked-line' ? {
                        color: new echarts.graphic.LinearGradient(0, 0, 1, 0, [
                            { offset: 0, color: 'rgba(6, 182, 212, 0.5)' },
                            { offset: 1, color: 'rgba(6, 182, 212, 0.1)' }
                        ]),
                    } : undefined }],
                };
            }
            chart.setOption(option);
            const handleResize = () => chart.resize();
            window.addEventListener('resize', handleResize);
            return () => {
                window.removeEventListener('resize', handleResize);
                chart.dispose();
            };
        }
    }, [data, chartType]);

    if (loading) return <div className="wsr-state-loading"><Spinner /></div>;
    if (error) return <div className="wsr-state-error">{error}</div>;
    if (!data || !data.items) return <div className="wsr-state-empty">ไม่มีข้อมูลลูกค้า</div>;

    return (
        <div className="wsr-customers-tab">
            <div className="wsr-stat-grid">
                <div className="wsr-stat-card wsr-stat-card--cyan">
                    <div className="wsr-stat-label">ลูกค้าทั้งหมด</div>
                    <div className="wsr-stat-value">{data.total_customers.toLocaleString()}</div>
                </div>
                <div className="wsr-stat-card wsr-stat-card--orange">
                    <div className="wsr-stat-label">ยังไม่มีออเดอร์</div>
                    <div className="wsr-stat-value">{data.customers_without_orders.toLocaleString()}</div>
                    <div className="wsr-stat-sub">คน ({data.total_customers > 0 ? Math.round(data.customers_without_orders / data.total_customers * 100) : 0}%)</div>
                </div>
            </div>
            <div className="wsr-chart-card">
                <div className="wsr-chart-card__header" style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                    <h3 className="wsr-chart-card__title">จำนวนออเดอร์ต่อลูกค้า (Top 100)</h3>
                    <div style={{ display: 'flex', gap: '10px', flexWrap: 'wrap' }}>
                        <button
                            onClick={() => setChartType('pie')}
                            style={{ padding: '6px 12px', borderRadius: '4px', border: '1px solid #cbd5e1', background: chartType === 'pie' ? '#f1f5f9' : '#fff', fontWeight: chartType === 'pie' ? 'bold' : 'normal', cursor: 'pointer', fontSize: '12px' }}
                        >Pie</button>
                        <button
                            onClick={() => setChartType('stacked-line')}
                            style={{ padding: '6px 12px', borderRadius: '4px', border: '1px solid #cbd5e1', background: chartType === 'stacked-line' ? '#f1f5f9' : '#fff', fontWeight: chartType === 'stacked-line' ? 'bold' : 'normal', cursor: 'pointer', fontSize: '12px' }}
                        >Stacked Line</button>
                        <button
                            onClick={() => setChartType('bar-race')}
                            style={{ padding: '6px 12px', borderRadius: '4px', border: '1px solid #cbd5e1', background: chartType === 'bar-race' ? '#f1f5f9' : '#fff', fontWeight: chartType === 'bar-race' ? 'bold' : 'normal', cursor: 'pointer', fontSize: '12px' }}
                        >Bar Race</button>
                    </div>
                </div>
                <div className="wsr-chart-card__body">
                    <div ref={chartRef} style={{ width: '100%', height: '420px' }}></div>
                </div>
            </div>
            <div className="wsr-chart-card">
                <div className="wsr-chart-card__header">
                    <h3 className="wsr-chart-card__title">ยอดขายต่อลูกค้า (Top 100)</h3>
                </div>
                <div className="wsr-chart-card__body">
                    <div ref={revenueChartRef} style={{ width: '100%', height: '420px' }}></div>
                </div>
            </div>
            <div className="wsr-chart-card">
                <div className="wsr-chart-card__header">
                    <h3 className="wsr-chart-card__title">รายการลูกค้าทั้งหมด ({data.total_customers} คน)</h3>
                </div>
                <div className="wsr-list">
                    {data.items.map((item, i) => (
                        <div key={item.id} className="wsr-list-item">
                            <span className={`wsr-list-rank${i === 0 ? ' wsr-list-rank--gold' : i === 1 ? ' wsr-list-rank--silver' : i === 2 ? ' wsr-list-rank--bronze' : ''}`}>{i + 1}</span>
                            <span className="wsr-list-name">
                                {item.name || `#${item.id}`}
                                {item.rank && (
                                    <span style={{
                                        display: 'inline-flex',
                                        alignItems: 'center',
                                        gap: '4px',
                                        marginLeft: '8px',
                                        backgroundColor: item.rankColor || '#64748b',
                                        color: '#fff',
                                        fontSize: '11px',
                                        padding: '2px 8px',
                                        borderRadius: '12px',
                                        fontWeight: 'bold',
                                        verticalAlign: 'middle'
                                    }}>
                                        {item.rank === 'VIP' && <RiVipCrownFill size={12} />}
                                        {item.rank === 'Gold' && <RiMedalFill size={12} />}
                                        {item.rank === 'Silver' && <RiStarFill size={12} />}
                                        {item.rank}
                                    </span>
                                )}
                            </span>
                            <div className="wsr-list-stats">
                                <div className="wsr-list-stat">
                                    <div className="wsr-list-stat__value">{item.orders}</div>
                                    <div className="wsr-list-stat__label">ออเดอร์</div>
                                </div>
                                <div className="wsr-list-stat">
                                    <div className="wsr-list-stat__value">฿{Number(item.revenue || 0).toLocaleString('th-TH', { maximumFractionDigits: 0 })}</div>
                                    <div className="wsr-list-stat__label">ยอดขาย</div>
                                </div>
                                <a href={item.edit_link} className="wsr-edit-btn" target="_blank" rel="noreferrer">แก้ไข</a>
                            </div>
                        </div>
                    ))}
                </div>
            </div>
        </div>
    );
};

export default CustomersTab;
