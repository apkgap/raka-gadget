import { useState, useEffect, useRef } from '@wordpress/element';
import { Spinner } from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';
import * as echarts from 'echarts';

const formatWeekLabel = (weekLabel) => {
    if (!weekLabel || typeof weekLabel !== 'string' || !weekLabel.includes('-W')) return weekLabel;
    const year = parseInt(weekLabel.substring(0, 4), 10);
    const week = parseInt(weekLabel.substring(6), 10);
    const simple = new Date(year, 0, 1 + (week - 1) * 7);
    const date = simple.getDate();
    const month = simple.toLocaleString('th-TH', { month: 'short' });
    return `${date} ${month} ${year}`;
};

const ProfitTab = ({ query, group }) => {
    const [data, setData] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const [chartType, setChartType] = useState('bar'); // 'bar', 'pie', 'stacked-line'
    const chartRef = useRef(null);
    const chartInstance = useRef(null);

    useEffect(() => {
        setLoading(true);
        setError('');
        const fetchData = async () => {
            try {
                // Ensure group is applied in the fetch
                const fetchQuery = new URLSearchParams(query);
                fetchQuery.set('group', group);
                const qString = fetchQuery.toString();
                const path = `/wsr/v1/graph/profit${qString ? '?' + qString : ''}`;

                const response = await apiFetch({ path });
                setData(response);
                setLoading(false);
            } catch (error) {
                console.error('Error fetching profit data:', error);
                setError('โหลดข้อมูลไม่สำเร็จ');
                setLoading(false);
            }
        };

        fetchData();
    }, [query, group]);

    useEffect(() => {
        if (data && data.items && chartRef.current) {
            if (chartInstance.current) {
                chartInstance.current.dispose();
            }

            const chart = echarts.init(chartRef.current);
            chartInstance.current = chart;

            const labels = data.items.map(item => group === 'week' ? formatWeekLabel(item.label) : item.label);
            const costs = data.items.map(item => item.cost);
            const profits = data.items.map(item => item.profit);

            let option;

            if (chartType === 'pie') {
                // For pie chart, show profit distribution
                const pieData = labels.map((label, index) => ({
                    name: label,
                    value: profits[index],
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
                            name: 'กำไรสุทธิ',
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
            } else if (chartType === 'stacked-line') {
                option = {
                    tooltip: {
                        trigger: 'axis',
                        axisPointer: { type: 'line' },
                        formatter: (params) => {
                            let tooltip = `<div style="font-weight:600">${params[0].name}</div>`;
                            params.forEach(p => {
                                tooltip += `<div>${p.marker} ${p.seriesName}: ฿${p.value.toLocaleString()}</div>`;
                            });
                            return tooltip;
                        }
                    },
                    legend: {
                        data: ['ต้นทุน', 'กำไรสุทธิ']
                    },
                    grid: { left: '3%', right: '4%', bottom: '3%', containLabel: true },
                    xAxis: {
                        type: 'category',
                        boundaryGap: false,
                        data: labels,
                        axisLabel: { interval: 0, rotate: 45, fontSize: 11 }
                    },
                    yAxis: {
                        type: 'value',
                        axisLabel: { formatter: (v) => `฿${v.toLocaleString()}` }
                    },
                    series: [
                        {
                            name: 'ต้นทุน',
                            type: 'line',
                            smooth: true,
                            stack: 'total',
                            data: costs,
                            itemStyle: {
                                color: '#ef4444',
                            },
                            areaStyle: {
                                color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                                    { offset: 0, color: 'rgba(239, 68, 68, 0.5)' },
                                    { offset: 1, color: 'rgba(239, 68, 68, 0.05)' }
                                ]),
                            },
                        },
                        {
                            name: 'กำไรสุทธิ',
                            type: 'line',
                            smooth: true,
                            stack: 'total',
                            data: profits,
                            itemStyle: {
                                color: '#10b981',
                            },
                            areaStyle: {
                                color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                                    { offset: 0, color: 'rgba(16, 185, 129, 0.5)' },
                                    { offset: 1, color: 'rgba(16, 185, 129, 0.05)' }
                                ]),
                            },
                        }
                    ]
                };
            } else {
                option = {
                    tooltip: {
                        trigger: 'axis',
                        axisPointer: { type: 'shadow' },
                        formatter: (params) => {
                            let tooltip = `<div style="font-weight:600">${params[0].name}</div>`;
                            params.forEach(p => {
                                tooltip += `<div>${p.marker} ${p.seriesName}: ฿${p.value.toLocaleString()}</div>`;
                            });
                            return tooltip;
                        }
                    },
                    legend: {
                        data: ['ต้นทุน', 'กำไรสุทธิ']
                    },
                    grid: { left: '3%', right: '4%', bottom: '3%', containLabel: true },
                    xAxis: {
                        type: 'category',
                        boundaryGap: true,
                        data: labels,
                        axisLabel: { interval: 0, rotate: 45, fontSize: 11 }
                    },
                    yAxis: {
                        type: 'value',
                        axisLabel: { formatter: (v) => `฿${v.toLocaleString()}` }
                    },
                    series: [
                        {
                            name: 'ต้นทุน',
                            type: 'bar',
                            stack: 'total',
                            data: costs,
                            itemStyle: {
                                color: '#ef4444',
                                borderRadius: [0, 0, 4, 4]
                            }
                        },
                        {
                            name: 'กำไรสุทธิ',
                            type: 'bar',
                            stack: 'total',
                            data: profits,
                            itemStyle: {
                                color: '#10b981',
                                borderRadius: [4, 4, 0, 0]
                            }
                        }
                    ]
                };
            }

            chart.setOption(option, true);

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
    if (!data || !data.items || data.items.length === 0) return <div className="wsr-state-empty">ไม่มีข้อมูลกำไรในช่วงนี้</div>;

    const profitPercentage = data.total_revenue > 0 ? ((data.total_profit / data.total_revenue) * 100).toFixed(1) : 0;

    return (
        <div className="wsr-profit-tab">
            <div className="wsr-stat-grid">
                <div className="wsr-stat-card wsr-stat-card--blue">
                    <div className="wsr-stat-label">ยอดขาย / รายรับรวม</div>
                    <div className="wsr-stat-value">฿{Number(data.total_revenue).toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</div>
                </div>
                <div className="wsr-stat-card wsr-stat-card--red">
                    <div className="wsr-stat-label">ต้นทุนรวม</div>
                    <div className="wsr-stat-value">฿{Number(data.total_cost).toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</div>
                </div>
                <div className="wsr-stat-card wsr-stat-card--green">
                    <div className="wsr-stat-label">กำไรสุทธิ</div>
                    <div className="wsr-stat-value">฿{Number(data.total_profit).toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</div>
                    <div className="wsr-stat-sub">สัดส่วนกำไร {profitPercentage}%</div>
                </div>
            </div>
            <div className="wsr-chart-card">
                <div className="wsr-chart-card__header" style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                    <h3 className="wsr-chart-card__title" style={{ margin: 0 }}>กราฟกำไรสุทธิ และ ทุน</h3>
                    <div>
                        <select
                            value={chartType}
                            onChange={(e) => setChartType(e.target.value)}
                            style={{ padding: '4px 8px', borderRadius: '4px', border: '1px solid #ccc', background: '#fff', fontSize: '13px' }}
                        >
                            <option value="bar">แบบแท่งซ้อน (Stacked Bar)</option>
                            <option value="stacked-line">แบบเส้นซ้อน (Stacked Line)</option>
                            <option value="pie">Pie Chart</option>
                        </select>
                    </div>
                </div>
                <div className="wsr-chart-card__body">
                    <div ref={chartRef} style={{ width: '100%', height: '420px' }}></div>
                </div>
            </div>
        </div>
    );
};

export default ProfitTab;
