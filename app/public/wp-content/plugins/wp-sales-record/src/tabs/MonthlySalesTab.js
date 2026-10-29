import { useState, useEffect, useRef } from '@wordpress/element';
import { Spinner } from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';
import * as echarts from 'echarts';

const formatWeekLabel = (weekLabel) => {
    if (!weekLabel || typeof weekLabel !== 'string' || !weekLabel.includes('-W')) return weekLabel;
    const MathCeil = Math.ceil;
    const year = parseInt(weekLabel.substring(0, 4), 10);
    const week = parseInt(weekLabel.substring(6), 10);
    const simple = new Date(year, 0, 1 + (week - 1) * 7);
    const date = simple.getDate();
    const month = simple.toLocaleString('th-TH', { month: 'short' });
    return `${date} ${month} ${year}`;
};

const MonthlySalesTab = ({ query, group }) => {
    const [data, setData] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const [chartType, setChartType] = useState('bar'); // 'bar' | 'line' | 'area-pieces'
    const chartRef = useRef(null);
    const chartInstance = useRef(null);

    useEffect(() => {
        setLoading(true);
        setError('');
        const fetchData = async () => {
            try {
                const response = await apiFetch({ path: `/wsr/v1/graph/monthly-sales${query}` });
                setData(response);
                setLoading(false);
            } catch (error) {
                console.error('Error fetching monthly sales data:', error);
                setError('โหลดข้อมูลไม่สำเร็จ');
                setLoading(false);
            }
        };

        fetchData();
    }, [query]);

    useEffect(() => {
        if (data.length > 0 && chartRef.current) {
            if (chartInstance.current) {
                chartInstance.current.dispose();
            }

            const chart = echarts.init(chartRef.current);
            chartInstance.current = chart;

            // Check if we have weekly or daily breakdown data
            const hasWeeklyData = data.some(item => item.weeks && item.weeks.length > 0);
            const hasDailyData = data.some(item => item.days && item.days.length > 0);

            let option;

            if (chartType === 'area-pieces' || chartType === 'line') {
                // Flatten data for line/area
                const labels = [];
                const amounts = [];
                data.forEach(monthItem => {
                    if (hasDailyData && monthItem.days && monthItem.days.length > 0) {
                        monthItem.days.forEach(dayItem => {
                            labels.push(`${monthItem.label} - ${dayItem.label}`);
                            amounts.push(dayItem.amount);
                        });
                    } else if (hasWeeklyData && monthItem.weeks && monthItem.weeks.length > 0) {
                        monthItem.weeks.forEach(weekItem => {
                            labels.push(`${monthItem.label} - ${weekItem.label}`);
                            amounts.push(weekItem.amount);
                        });
                    } else {
                        labels.push(group === 'week' ? formatWeekLabel(monthItem.label) : monthItem.label);
                        amounts.push(monthItem.amount);
                    }
                });

                const maxAmount = Math.max(...amounts, 1);

                option = {
                    tooltip: {
                        trigger: 'axis',
                        formatter: (params) => {
                            const item = params[0];
                            return `<div style="font-weight:600">${item.name}</div><div>ยอดขาย: ฿${item.value.toLocaleString()}</div>`;
                        },
                    },
                    grid: { left: '3%', right: '4%', bottom: '3%', containLabel: true },
                    xAxis: {
                        type: 'category',
                        data: labels,
                        axisLabel: { interval: 0, rotate: 45, fontSize: 11 },
                    },
                    yAxis: {
                        type: 'value',
                        axisLabel: { formatter: (v) => `฿${v.toLocaleString()}` },
                    },
                    visualMap: chartType === 'area-pieces' ? {
                        type: 'piecewise',
                        show: true,
                        dimension: 1, // Split by Y value
                        seriesIndex: 0,
                        top: 10,
                        right: 10,
                        pieces: [
                            { gt: maxAmount * 0.7, color: 'rgba(16, 185, 129, 0.8)', label: 'ดีเยี่ยม' }, // Green
                            { gt: maxAmount * 0.3, lte: maxAmount * 0.7, color: 'rgba(59, 130, 246, 0.8)', label: 'ปานกลาง' }, // Blue
                            { lte: maxAmount * 0.3, color: 'rgba(239, 68, 68, 0.8)', label: 'ต่ำ' } // Red
                        ],
                        outOfRange: { color: '#999' }
                    } : undefined,
                    series: [
                        {
                            data: amounts,
                            type: 'line',
                            smooth: true,
                            areaStyle: chartType === 'area-pieces' ? { opacity: 0.8 } : (chartType === 'line' ? undefined : {}),
                            itemStyle: chartType === 'area-pieces' ? undefined : { color: '#3b82f6' },
                            markLine: chartType === 'area-pieces' ? {
                                silent: true,
                                lineStyle: { color: '#333' },
                                data: [
                                    { yAxis: maxAmount * 0.3 },
                                    { yAxis: maxAmount * 0.7 }
                                ]
                            } : undefined
                        },
                    ],
                };
            } else {
                // Original bar chart logic
                if (hasDailyData) {
                    // Grouped bar chart showing days within each week
                    const dayLabels = [];
                    const seriesData = [];

                    data.forEach((weekItem) => {
                        if (weekItem.days && weekItem.days.length > 0) {
                            weekItem.days.forEach((dayItem) => {
                                dayLabels.push(`${weekItem.label} - ${dayItem.label}`);
                                seriesData.push(dayItem.amount);
                            });
                        } else {
                            dayLabels.push(weekItem.label);
                            seriesData.push(weekItem.amount);
                        }
                    });

                    option = {
                        tooltip: {
                            trigger: 'axis',
                            axisPointer: { type: 'shadow' },
                            formatter: (params) => {
                                const item = params[0];
                                return `<div style="font-weight:600">${item.name}</div><div>ยอดขาย: ฿${item.value.toLocaleString()}</div>`;
                            },
                        },
                        grid: { left: '3%', right: '4%', bottom: '3%', containLabel: true },
                        xAxis: {
                            type: 'category',
                            data: dayLabels,
                            axisLabel: { interval: 0, rotate: 45, fontSize: 11 },
                        },
                        yAxis: { type: 'value', axisLabel: { formatter: (v) => `฿${v.toLocaleString()}` } },
                        series: [
                            {
                                name: 'ยอดขาย',
                                type: 'bar',
                                data: seriesData,
                                itemStyle: { color: '#10b981', borderRadius: [4, 4, 0, 0] },
                                barWidth: '60%',
                            },
                        ],
                    };
                } else if (hasWeeklyData) {
                    const weekLabels = [];
                    const seriesData = [];

                    data.forEach((monthItem) => {
                        if (monthItem.weeks && monthItem.weeks.length > 0) {
                            monthItem.weeks.forEach((weekItem) => {
                                weekLabels.push(`${monthItem.label} - ${weekItem.label}`);
                                seriesData.push(weekItem.amount);
                            });
                        } else {
                            weekLabels.push(monthItem.label);
                            seriesData.push(monthItem.amount);
                        }
                    });

                    option = {
                        tooltip: {
                            trigger: 'axis',
                            axisPointer: { type: 'shadow' },
                            formatter: (params) => {
                                const item = params[0];
                                return `<div style="font-weight:600">${item.name}</div><div>ยอดขาย: ฿${item.value.toLocaleString()}</div>`;
                            },
                        },
                        grid: { left: '3%', right: '4%', bottom: '3%', containLabel: true },
                        xAxis: { type: 'category', data: weekLabels, axisLabel: { interval: 0, rotate: 45, fontSize: 11 } },
                        yAxis: { type: 'value', axisLabel: { formatter: (v) => `฿${v.toLocaleString()}` } },
                        series: [
                            {
                                name: 'ยอดขาย',
                                type: 'bar',
                                data: seriesData,
                                itemStyle: { color: '#3b82f6', borderRadius: [4, 4, 0, 0] },
                                barWidth: '60%',
                            },
                        ],
                    };
                } else {
                    const months = data.map(item => group === 'week' ? formatWeekLabel(item.label) : item.label);
                    const amounts = data.map(item => item.amount);

                    option = {
                        tooltip: {
                            trigger: 'axis',
                            formatter: (params) => {
                                const item = params[0];
                                return `<div style="font-weight:600">${item.name}</div><div>ยอดขาย: ฿${item.value.toLocaleString()}</div>`;
                            },
                        },
                        xAxis: { type: 'category', data: months, axisLabel: { interval: 0, rotate: 45, fontSize: 11 } },
                        yAxis: { type: 'value', axisLabel: { formatter: (v) => `฿${v.toLocaleString()}` } },
                        series: [
                            {
                                data: amounts,
                                type: 'bar',
                                itemStyle: { color: '#3b82f6', borderRadius: [4, 4, 0, 0] },
                            },
                        ],
                    };
                }
            }

            chart.setOption(option, true);

            const handleResize = () => chart.resize();
            window.addEventListener('resize', handleResize);

            return () => {
                window.removeEventListener('resize', handleResize);
                chart.dispose();
            };
        }
    }, [data, group, chartType]);

    if (loading) return <div className="wsr-state-loading"><Spinner /></div>;
    if (error) return <div className="wsr-state-error">{error}</div>;
    if (!data || data.length === 0) return <div className="wsr-state-empty">ยังไม่มีข้อมูลยอดขายในช่วงนี้</div>;

    return (
        <div className="wsr-monthly-sales-tab">
            <div className="wsr-chart-card">
                <div className="wsr-chart-card__header" style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                    <h3 className="wsr-chart-card__title">ยอดขายตามช่วงเวลา</h3>
                    <div style={{ display: 'flex', gap: '10px' }}>
                        <button
                            onClick={() => setChartType('bar')}
                            style={{ padding: '6px 12px', borderRadius: '4px', border: '1px solid #cbd5e1', background: chartType === 'bar' ? '#f1f5f9' : '#fff', fontWeight: chartType === 'bar' ? 'bold' : 'normal', cursor: 'pointer', fontSize: '12px' }}
                        >แท่งกราฟ (Bar)</button>
                        <button
                            onClick={() => setChartType('line')}
                            style={{ padding: '6px 12px', borderRadius: '4px', border: '1px solid #cbd5e1', background: chartType === 'line' ? '#f1f5f9' : '#fff', fontWeight: chartType === 'line' ? 'bold' : 'normal', cursor: 'pointer', fontSize: '12px' }}
                        >เส้นกราฟ (Line)</button>
                        <button
                            onClick={() => setChartType('area-pieces')}
                            style={{ padding: '6px 12px', borderRadius: '4px', border: '1px solid #cbd5e1', background: chartType === 'area-pieces' ? '#f1f5f9' : '#fff', fontWeight: chartType === 'area-pieces' ? 'bold' : 'normal', cursor: 'pointer', fontSize: '12px' }}
                        >ช่วงสี (Area Pieces)</button>
                    </div>
                </div>
                <div className="wsr-chart-card__body">
                    <div ref={chartRef} style={{ width: '100%', height: '420px' }}></div>
                </div>
            </div>
        </div>
    );
};

export default MonthlySalesTab;
