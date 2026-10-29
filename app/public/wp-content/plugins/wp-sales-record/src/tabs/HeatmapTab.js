import { useState, useEffect, useRef } from '@wordpress/element';
import { Spinner, Modal, Button } from '@wordpress/components';
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

const HeatmapTab = ({ query, group }) => {
    const [data, setData] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const [popupOpen, setPopupOpen] = useState(false);
    const [popupTitle, setPopupTitle] = useState('');
    const [popupLoading, setPopupLoading] = useState(false);
    const [popupError, setPopupError] = useState('');
    const [popupOrders, setPopupOrders] = useState([]);
    const chartRef = useRef(null);
    const chartInstance = useRef(null);

    const openOrdersPopup = async (label) => {
        const safeLabel = String(label || '');
        setPopupTitle(safeLabel);
        setPopupOpen(true);
        setPopupLoading(true);
        setPopupError('');
        setPopupOrders([]);

        try {
            const response = await apiFetch({
                path: `/wsr/v1/graph/orders?group=${encodeURIComponent(group)}&label=${encodeURIComponent(safeLabel)}`,
            });
            const orders = Array.isArray(response?.orders) ? response.orders : [];
            setPopupOrders(orders);
        } catch (e) {
            setPopupError('โหลดรายการซื้อขายไม่สำเร็จ');
        }

        setPopupLoading(false);
    };

    useEffect(() => {
        setLoading(true);
        setError('');

        // Remove overridden tooltipQuery, respect the global `query` which already contains `group`
        const fetchData = async () => {
            try {
                const response = await apiFetch({ path: `/wsr/v1/graph/monthly-sales${query}` });
                setData(response);
                setLoading(false);
            } catch (error) {
                console.error('Error fetching heatmap data:', error);
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

            let option = {};
            const maxValue = Math.max(...data.map(item => item.amount));

            if (group === 'day') {
                const heatmapData = data.map(item => [item.label, item.amount]);
                const years = Array.from(new Set(data.map(item => item.label.substring(0, 4)))).sort((a, b) => b - a);

                option = {
                    tooltip: {
                        position: 'top',
                        formatter: function (p) {
                            const date = p.data[0];
                            const count = p.data[1];
                            return date + ': ฿' + count.toLocaleString();
                        }
                    },
                    visualMap: {
                        min: 0,
                        max: maxValue || 1000,
                        calculable: true,
                        orient: 'horizontal',
                        left: 'center',
                        bottom: 0,
                        inRange: {
                            color: ['#ebedf0', '#9be9a8', '#40c463', '#30a14e', '#216e39']
                        }
                    },
                    calendar: years.map((year, idx) => ({
                        top: 50 + idx * 150,
                        yearLabel: { show: true, margin: 40 },
                        range: year,
                        cellSize: ['auto', 20],
                        right: 20,
                        left: 60,
                        monthLabel: {
                            nameMap: ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.']
                        },
                        dayLabel: {
                            firstDay: 1,
                            nameMap: ['อา.', 'จ.', 'อ.', 'พ.', 'พฤ.', 'ศ.', 'ส.']
                        }
                    })),
                    series: years.map((year, idx) => ({
                        type: 'heatmap',
                        coordinateSystem: 'calendar',
                        calendarIndex: idx,
                        data: heatmapData.filter(d => d[0].startsWith(year))
                    }))
                };

                chartRef.current.style.height = `${150 * years.length + 80}px`;
            } else if (group === 'month') {
                const xData = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
                const years = Array.from(new Set(data.map(item => item.label.substring(0, 4)))).sort((a, b) => b - a);

                const seriesData = [];
                data.forEach(item => {
                    const year = item.label.substring(0, 4);
                    const month = parseInt(item.label.substring(5, 7), 10) - 1; // 0-11
                    const yIndex = years.indexOf(year);
                    seriesData.push([month, yIndex, item.amount]);
                });

                option = {
                    tooltip: {
                        position: 'top',
                        formatter: function (p) {
                            return years[p.value[1]] + ' ' + xData[p.value[0]] + ': ฿' + p.value[2].toLocaleString();
                        }
                    },
                    grid: { height: '60%', top: '10%' },
                    xAxis: { type: 'category', data: xData, splitArea: { show: true } },
                    yAxis: { type: 'category', data: years, splitArea: { show: true } },
                    visualMap: {
                        min: 0,
                        max: maxValue || 1000,
                        calculable: true,
                        orient: 'horizontal',
                        left: 'center',
                        bottom: '5%',
                        inRange: { color: ['#ebedf0', '#9be9a8', '#40c463', '#30a14e', '#216e39'] }
                    },
                    series: [{
                        type: 'heatmap',
                        data: seriesData,
                        label: { show: false },
                        emphasis: { itemStyle: { shadowBlur: 10, shadowColor: 'rgba(0, 0, 0, 0.5)' } }
                    }]
                };
                chartRef.current.style.height = `400px`;
            } else if (group === 'week') {
                const xData = Array.from({length: 53}, (_, i) => `สัปดาห์ ${i + 1}`);
                const years = Array.from(new Set(data.map(item => item.label.substring(0, 4)))).sort((a, b) => b - a);

                const seriesData = [];
                data.forEach(item => {
                    const year = item.label.substring(0, 4);
                    const weekStr = item.label.substring(6); // "01" from "2023-W01"
                    const week = parseInt(weekStr, 10) - 1; // 0-52
                    const yIndex = years.indexOf(year);
                    seriesData.push([week, yIndex, item.amount]);
                });

                option = {
                    tooltip: {
                        position: 'top',
                        formatter: function (p) {
                            return years[p.value[1]] + ' ' + xData[p.value[0]] + ': ฿' + p.value[2].toLocaleString();
                        }
                    },
                    grid: { height: '50%', top: '10%', left: '10%', right: '5%' },
                    xAxis: {
                        type: 'category',
                        data: xData,
                        splitArea: { show: true },
                        axisLabel: { interval: 4, rotate: 45 }
                    },
                    yAxis: { type: 'category', data: years, splitArea: { show: true } },
                    visualMap: {
                        min: 0,
                        max: maxValue || 1000,
                        calculable: true,
                        orient: 'horizontal',
                        left: 'center',
                        bottom: '10%',
                        inRange: { color: ['#ebedf0', '#9be9a8', '#40c463', '#30a14e', '#216e39'] }
                    },
                    series: [{
                        type: 'heatmap',
                        data: seriesData,
                        label: { show: false },
                        emphasis: { itemStyle: { shadowBlur: 10, shadowColor: 'rgba(0, 0, 0, 0.5)' } }
                    }]
                };
                chartRef.current.style.height = `450px`;
            }

            chart.resize();
            chart.setOption(option, true);

            const handleClick = (params) => {
                if (!params || !params.data) return;

                if (group === 'day') {
                    const label = Array.isArray(params.data) ? params.data[0] : '';
                    if (label) openOrdersPopup(label);
                    return;
                }

                const label = typeof params?.name === 'string' ? params.name : '';
                if (label) openOrdersPopup(label);
            };

            chart.on('click', handleClick);

            const handleResize = () => chart.resize();
            window.addEventListener('resize', handleResize);

            return () => {
                chart.off('click', handleClick);
                window.removeEventListener('resize', handleResize);
                chart.dispose();
            };
        }
    }, [data, group]);

    if (loading) return <div className="wsr-state-loading"><Spinner /></div>;
    if (error) return <div className="wsr-state-error">{error}</div>;
    if (!data || data.length === 0) return <div className="wsr-state-empty">ยังไม่มีข้อมูลยอดขายในช่วงนี้</div>;

    const titlePrefix = group === 'day' ? 'ปฏิทินยอดขายรายวัน' : (group === 'month' ? 'ยอดขายรายเดือน' : 'ยอดขายรายสัปดาห์');

    return (
        <div className="wsr-heatmap-tab">
            <div className="wsr-chart-card">
                <div className="wsr-chart-card__header">
                    <h3 className="wsr-chart-card__title">Heatmap {titlePrefix}</h3>
                </div>
                <div className="wsr-chart-card__body">
                    <div ref={chartRef} style={{ width: '100%', height: '230px' }}></div>
                </div>
            </div>

            {popupOpen ? (
                <Modal
                    title={`รายการซื้อขาย: ${popupTitle}`}
                    onRequestClose={() => setPopupOpen(false)}
                    shouldCloseOnClickOutside
                    isDismissible
                >
                    {popupLoading ? (
                        <div className="wsr-state-loading"><Spinner /></div>
                    ) : popupError ? (
                        <div className="wsr-state-error">{popupError}</div>
                    ) : popupOrders.length === 0 ? (
                        <div className="wsr-state-empty">ไม่มีรายการซื้อขายในช่วงนี้</div>
                    ) : (
                        <div style={{ display: 'flex', flexDirection: 'column', gap: '10px' }}>
                            {popupOrders.map((o) => (
                                <div key={o.id} style={{ border: '1px solid #e2e8f0', borderRadius: '10px', padding: '10px' }}>
                                    <div style={{ display: 'flex', justifyContent: 'space-between', gap: '10px' }}>
                                        <div style={{ fontWeight: 700 }}>{o.title || `Order #${o.id}`}</div>
                                        <div style={{ fontWeight: 700 }}>฿{Number(o.amount || 0).toLocaleString()}</div>
                                    </div>
                                    <div style={{ fontSize: '12px', color: '#64748b', marginTop: '6px' }}>
                                        ผู้ซื้อ: {o.customer_name || '-'}
                                    </div>
                                    {Array.isArray(o.products) && o.products.length > 0 ? (
                                        <div style={{ fontSize: '12px', color: '#64748b', marginTop: '6px' }}>
                                            สินค้า: {o.products.filter(Boolean).join(', ')}
                                        </div>
                                    ) : null}
                                    {o.edit_link ? (
                                        <div style={{ marginTop: '8px' }}>
                                            <Button variant="secondary" href={o.edit_link} target="_blank">
                                                เปิด Sales Record
                                            </Button>
                                        </div>
                                    ) : null}
                                </div>
                            ))}
                        </div>
                    )}
                </Modal>
            ) : null}
        </div>
    );
};

export default HeatmapTab;
