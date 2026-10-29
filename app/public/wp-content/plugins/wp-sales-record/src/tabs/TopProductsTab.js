import { useState, useEffect, useRef } from '@wordpress/element';
import { Spinner } from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';
import * as echarts from 'echarts';

const TopProductsTab = ({ query }) => {
    const [data, setData] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const [groupBy, setGroupBy] = useState('wsr_model'); // 'product', 'wsr_product_cat', 'wsr_brand', 'wsr_model'
    const [chartType, setChartType] = useState('pie'); // 'stacked-area', 'bar-race', 'pie'
    const chartRef = useRef(null);
    const chartInstance = useRef(null);
    const revenueChartRef = useRef(null);
    const revenueChartInstance = useRef(null);

    useEffect(() => {
        setLoading(true);
        setError('');
        const fetchData = async () => {
            try {
                const fetchQuery = new URLSearchParams(query);
                fetchQuery.set('group_by', groupBy);
                const qString = fetchQuery.toString();
                const path = `/wsr/v1/graph/products${qString ? '?' + qString : ''}`;

                const response = await apiFetch({ path });
                setData(response);
                setLoading(false);
            } catch (error) {
                console.error('Error fetching products data:', error);
                setError('โหลดข้อมูลไม่สำเร็จ');
                setLoading(false);
            }
        };

        fetchData();
    }, [query, groupBy]);

    useEffect(() => {
        if (data && data.items && data.items.length > 0 && chartRef.current) {
            if (chartInstance.current) {
                chartInstance.current.dispose();
            }

            const chart = echarts.init(chartRef.current);
            chartInstance.current = chart;

            const names = data.items.map(item => item.name);
            const values = data.items.map(item => item.orders);

            let seriesConfig;
            let option;

            if (chartType === 'pie') {
                const pieData = names.map((name, index) => ({
                    name: name,
                    value: values[index],
                }));

                seriesConfig = {
                    name: 'จำนวนขาย',
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
                };

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
                    series: [seriesConfig],
                };
            } else {
                seriesConfig = {
                    name: 'จำนวนขาย',
                    type: chartType === 'stacked-area' ? 'line' : (chartType === 'bar-race' ? 'bar' : chartType),
                    data: values,
                    itemStyle: {
                        color: '#8b5cf6',
                    },
                };

                if (chartType === 'line' || chartType === 'area' || chartType === 'stacked-area') {
                    seriesConfig.smooth = true;
                    seriesConfig.symbol = 'circle';
                    seriesConfig.symbolSize = 6;
                }

                if (chartType === 'area' || chartType === 'stacked-area') {
                    seriesConfig.areaStyle = {
                        color: new echarts.graphic.LinearGradient(0, 0, 1, 0, [
                            { offset: 0, color: 'rgba(139, 92, 246, 0.5)' },
                            { offset: 1, color: 'rgba(139, 92, 246, 0.1)' },
                        ]),
                    };
                    if (chartType === 'stacked-area') {
                        seriesConfig.stack = 'total';
                    }
                }

                if (chartType === 'bar-race') {
                    seriesConfig.animationDuration = 1000;
                    seriesConfig.animationEasing = 'cubicOut';
                }

                option = {
                    tooltip: {
                        trigger: 'axis',
                        axisPointer: {
                            type: (chartType === 'bar' || chartType === 'bar-race') ? 'shadow' : 'line',
                        },
                    },
                    grid: {
                        left: '3%',
                        right: '4%',
                        bottom: '3%',
                        containLabel: true,
                    },
                    xAxis: {
                        type: 'category',
                        data: names,
                        axisLabel: {
                            interval: 0,
                            rotate: 45,
                            fontSize: 11,
                        },
                    },
                    yAxis: {
                        type: 'value',
                    },
                    series: [seriesConfig],
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

            let seriesConfig;
            let option;

            if (chartType === 'pie') {
                const pieData = names.map((name, index) => ({
                    name: name,
                    value: revenues[index],
                }));

                seriesConfig = {
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
                };

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
                    series: [seriesConfig],
                };
            } else {
                seriesConfig = {
                    name: 'ยอดขาย (฿)',
                    type: chartType === 'stacked-area' ? 'line' : (chartType === 'bar-race' ? 'bar' : chartType),
                    data: revenues,
                    itemStyle: {
                        color: '#f59e0b',
                    },
                };

                if (chartType === 'line' || chartType === 'area' || chartType === 'stacked-area') {
                    seriesConfig.smooth = true;
                    seriesConfig.symbol = 'circle';
                    seriesConfig.symbolSize = 6;
                }

                if (chartType === 'area' || chartType === 'stacked-area') {
                    seriesConfig.areaStyle = {
                        color: new echarts.graphic.LinearGradient(0, 0, 1, 0, [
                            { offset: 0, color: 'rgba(245, 158, 11, 0.5)' },
                            { offset: 1, color: 'rgba(245, 158, 11, 0.1)' },
                        ]),
                    };
                    if (chartType === 'stacked-area') {
                        seriesConfig.stack = 'total';
                    }
                }

                if (chartType === 'bar-race') {
                    seriesConfig.animationDuration = 1000;
                    seriesConfig.animationEasing = 'cubicOut';
                }

                option = {
                    tooltip: { trigger: 'axis', axisPointer: { type: (chartType === 'bar' || chartType === 'bar-race') ? 'shadow' : 'line' } },
                    grid: { left: '3%', right: '4%', bottom: '3%', containLabel: true },
                    xAxis: {
                        type: 'category',
                        data: names,
                        axisLabel: {
                            interval: 0,
                            rotate: 45,
                            fontSize: 11,
                        },
                    },
                    yAxis: {
                        type: 'value',
                        axisLabel: { formatter: (v) => `฿${v.toLocaleString()}` }
                    },
                    series: [seriesConfig],
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
    if (!data || !data.items) return <div className="wsr-state-empty">ไม่มีข้อมูลสินค้า</div>;

    const getLabelName = () => {
        if (groupBy === 'wsr_product_cat') return 'หมวดหมู่';
        if (groupBy === 'wsr_brand') return 'แบรนด์';
        if (groupBy === 'wsr_model') return 'รุ่น';
        return 'สินค้า';
    };

    return (
        <div className="wsr-top-products-tab">
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '15px', flexWrap: 'wrap', gap: '10px' }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: '10px' }}>
                    <label style={{ fontWeight: '600' }}>จัดกลุ่มตาม:</label>
                    <select
                        value={groupBy}
                        onChange={(e) => setGroupBy(e.target.value)}
                        style={{ padding: '6px 12px', borderRadius: '4px', border: '1px solid #cbd5e1', fontSize: '14px' }}
                    >
                        <option value="product">สินค้ารายชิ้น (Product)</option>
                        <option value="wsr_product_cat">หมวดหมู่ (Category)</option>
                        <option value="wsr_brand">แบรนด์/ยี่ห้อ (Brand)</option>
                        <option value="wsr_model">รุ่น (Model)</option>
                    </select>
                </div>
                <div style={{ display: 'flex', gap: '10px', flexWrap: 'wrap' }}>
                    <button
                        onClick={() => setChartType('pie')}
                        style={{ padding: '6px 12px', borderRadius: '4px', border: '1px solid #cbd5e1', background: chartType === 'pie' ? '#f1f5f9' : '#fff', fontWeight: chartType === 'pie' ? 'bold' : 'normal', cursor: 'pointer', fontSize: '12px' }}
                    >Pie</button>
                    <button
                        onClick={() => setChartType('stacked-area')}
                        style={{ padding: '6px 12px', borderRadius: '4px', border: '1px solid #cbd5e1', background: chartType === 'stacked-area' ? '#f1f5f9' : '#fff', fontWeight: chartType === 'stacked-area' ? 'bold' : 'normal', cursor: 'pointer', fontSize: '12px' }}
                    >Stacked Area</button>
                    <button
                        onClick={() => setChartType('bar-race')}
                        style={{ padding: '6px 12px', borderRadius: '4px', border: '1px solid #cbd5e1', background: chartType === 'bar-race' ? '#f1f5f9' : '#fff', fontWeight: chartType === 'bar-race' ? 'bold' : 'normal', cursor: 'pointer', fontSize: '12px' }}
                    >Bar Race</button>
                </div>
            </div>

            <div className="wsr-chart-card">
                <div className="wsr-chart-card__header">
                    <h3 className="wsr-chart-card__title">{getLabelName()}ขายดี — จำนวนชิ้น (Top 100)</h3>
                </div>
                <div className="wsr-chart-card__body">
                    <div ref={chartRef} style={{ width: '100%', height: '420px' }}></div>
                </div>
            </div>
            <div className="wsr-chart-card">
                <div className="wsr-chart-card__header">
                    <h3 className="wsr-chart-card__title">{getLabelName()}ขายดี — ยอดขาย (Top 100)</h3>
                </div>
                <div className="wsr-chart-card__body">
                    <div ref={revenueChartRef} style={{ width: '100%', height: '420px' }}></div>
                </div>
            </div>
            <div className="wsr-chart-card">
                <div className="wsr-chart-card__header">
                    <h3 className="wsr-chart-card__title">รายการ{getLabelName()}ทั้งหมด ({data.total_items} รายการ)</h3>
                </div>
                <div className="wsr-list">
                    {data.items.map((item, i) => (
                        <div key={item.id} className="wsr-list-item">
                            <span className={`wsr-list-rank${i === 0 ? ' wsr-list-rank--gold' : i === 1 ? ' wsr-list-rank--silver' : i === 2 ? ' wsr-list-rank--bronze' : ''}`}>{i + 1}</span>
                            <span className="wsr-list-name">{item.name || `#${item.id}`}</span>
                            <div className="wsr-list-stats">
                                <div className="wsr-list-stat">
                                    <div className="wsr-list-stat__value">{item.orders}</div>
                                    <div className="wsr-list-stat__label">ชิ้น</div>
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

export default TopProductsTab;
