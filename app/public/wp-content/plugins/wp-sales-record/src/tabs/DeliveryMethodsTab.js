import { useState, useEffect, useRef } from '@wordpress/element';
import { Spinner } from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';
import * as echarts from 'echarts';

const DeliveryMethodsTab = ({ query }) => {
    const [data, setData] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const [chartType, setChartType] = useState('pie'); // 'pie', 'stacked-line', 'bar-race'
    const chartRef = useRef(null);
    const chartInstance = useRef(null);

    useEffect(() => {
        setLoading(true);
        setError('');
        const fetchData = async () => {
            try {
                const response = await apiFetch({ path: `/wsr/v1/graph/delivery${query}` });
                setData(response);
                setLoading(false);
            } catch (error) {
                console.error('Error fetching delivery data:', error);
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

            let option;

            if (chartType === 'pie') {
                option = {
                    tooltip: {
                        trigger: 'item',
                    },
                    legend: {
                        top: '5%',
                        left: 'center',
                    },
                    series: [
                        {
                            name: 'รูปแบบการจัดส่ง',
                            type: 'pie',
                            radius: '70%',
                            data: [
                                { value: data.pickup, name: 'นัดรับ', itemStyle: { color: '#8b5cf6' } },
                                { value: data.ems, name: 'ส่ง EMS', itemStyle: { color: '#06b6d4' } },
                                { value: data.messenger, name: 'แมสเซนเจอร์', itemStyle: { color: '#f97316' } },
                            ],
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
                // For stacked-line and bar-race
                option = {
                    tooltip: {
                        trigger: 'axis',
                        axisPointer: chartType === 'stacked-line' ? { type: 'line' } : { type: 'shadow' },
                    },
                    xAxis: {
                        type: 'category',
                        data: ['นัดรับ', 'ส่ง EMS', 'แมสเซนเจอร์'],
                        axisLabel: { interval: 0, rotate: 0, fontSize: 13 },
                    },
                    yAxis: {
                        type: 'value',
                    },
                    series: [
                        {
                            name: 'จำนวน',
                            type: chartType === 'stacked-line' ? 'line' : 'bar',
                            smooth: chartType === 'stacked-line',
                            data: [data.pickup, data.ems, data.messenger],
                            itemStyle: {
                                color: (params) => {
                                    const colors = ['#8b5cf6', '#06b6d4', '#f97316'];
                                    return colors[params.dataIndex];
                                },
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

    if (loading) return <div className="wsr-state-loading"><Spinner /></div>;
    if (error) return <div className="wsr-state-error">{error}</div>;
    if (!data) return <div className="wsr-state-empty">ไม่มีข้อมูล</div>;

    return (
        <div className="wsr-delivery-methods-tab">
            <div className="wsr-stat-grid">
                <div className="wsr-stat-card wsr-stat-card--purple">
                    <div className="wsr-stat-label">นัดรับ</div>
                    <div className="wsr-stat-value">{data.pickup}</div>
                </div>
                <div className="wsr-stat-card wsr-stat-card--cyan">
                    <div className="wsr-stat-label">ส่ง EMS</div>
                    <div className="wsr-stat-value">{data.ems}</div>
                </div>
                <div className="wsr-stat-card wsr-stat-card--orange">
                    <div className="wsr-stat-label">แมสเซนเจอร์</div>
                    <div className="wsr-stat-value">{data.messenger}</div>
                </div>
            </div>
            <div className="wsr-chart-card">
                <div className="wsr-chart-card__header" style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                    <h3 className="wsr-chart-card__title">สัดส่วนรูปแบบการจัดส่ง</h3>
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
                    <div ref={chartRef} style={{ width: '100%', height: '400px' }}></div>
                </div>
            </div>
        </div>
    );
};

export default DeliveryMethodsTab;
