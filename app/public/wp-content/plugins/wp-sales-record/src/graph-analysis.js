import { useState, useEffect, useRef } from '@wordpress/element';
import { Spinner } from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';
import * as echarts from 'echarts';
import {
    RiDashboard2Line,
    RiLineChartLine,
    RiCalendarEventLine,
    RiMoneyDollarCircleLine,
    RiRefreshLine,
    RiTruckLine,
    RiTrophyLine,
    RiGroupLine,
    RiBarChartBoxLine,
    RiDownload2Line,
} from '@remixicon/react';
import OverviewTab from './tabs/OverviewTab';
import MonthlySalesTab from './tabs/MonthlySalesTab';
import HeatmapTab from './tabs/HeatmapTab';
import ProfitTab from './tabs/ProfitTab';
import OrderStatusTab from './tabs/OrderStatusTab';
import DeliveryMethodsTab from './tabs/DeliveryMethodsTab';
import TopProductsTab from './tabs/TopProductsTab';
import CustomersTab from './tabs/CustomersTab';

const formatWeekLabel = (weekLabel) => {
    if (!weekLabel || typeof weekLabel !== 'string' || !weekLabel.includes('-W')) return weekLabel;
    const MathCeil = Math.ceil;
    const year = parseInt(weekLabel.substring(0, 4), 10);
    const week = parseInt(weekLabel.substring(6), 10);
    const simple = new Date(year, 0, 1 + (week - 1) * 7);
    const monthsThai = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
    const monthName = monthsThai[simple.getMonth()];
    const weekOfMonth = MathCeil(simple.getDate() / 7);
    return `${year} ${monthName} สัปดาห์ที่ ${weekOfMonth}`;
};

const GraphAnalysis = () => {
    const [selectedTab, setSelectedTab] = useState('overview');
    const [preset, setPreset] = useState('all');
    const [from, setFrom] = useState('');
    const [to, setTo] = useState('');
    const [group, setGroup] = useState('month');

    const buildQuery = () => {
        const params = new URLSearchParams();
        if (preset) params.set('preset', preset);
        if (preset === 'custom') {
            if (from) params.set('from', from);
            if (to) params.set('to', to);
        }
        if (group) params.set('group', group);
        params.set('limit', '100');
        const q = params.toString();
        return q ? `?${q}` : '';
    };

    const query = buildQuery();

    const tabs = [
        { name: 'overview',  title: 'ภาพรวม',          icon: <RiDashboard2Line size={18} /> },
        { name: 'monthly',   title: 'ยอดขายรายเดือน',  icon: <RiLineChartLine size={18} /> },
        { name: 'heatmap',   title: 'ปฏิทินยอดขาย',    icon: <RiCalendarEventLine size={18} /> },
        { name: 'profit',    title: 'กำไรสุทธิ',        icon: <RiMoneyDollarCircleLine size={18} /> },
        { name: 'status',    title: 'สถานะออเดอร์',     icon: <RiRefreshLine size={18} /> },
        { name: 'delivery',  title: 'การจัดส่ง',         icon: <RiTruckLine size={18} /> },
        { name: 'products',  title: 'สินค้าขายดี',       icon: <RiTrophyLine size={18} /> },
        { name: 'customers', title: 'ลูกค้า',            icon: <RiGroupLine size={18} /> },
    ];

    const handleExportCSV = async () => {
        try {
            const response = await apiFetch({ path: `/wsr/v1/graph/export-csv${query}`, parse: false });
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `sales-report-${Date.now()}.csv`;
            a.click();
            window.URL.revokeObjectURL(url);
        } catch (err) {
            console.error('Export failed', err);
            alert('Export failed.');
        }
    };

    return (
        <div className="wsr-graph-analysis">
            <div className="wsr-ga-header">
                <div className="wsr-ga-title-wrap" style={{ display: 'flex', justifyContent: 'space-between', width: '100%', alignItems: 'center' }}>
                    <div style={{ display: 'flex', alignItems: 'center' }}>
                        <div className="wsr-ga-icon"><RiBarChartBoxLine size={24} color="#ffffff" /></div>
                        <div style={{ marginLeft: '12px' }}>
                            <h1 className="wsr-ga-title" style={{ margin: 0, lineHeight: 1.2 }}>วิเคราะห์กราฟ</h1>
                            <p className="wsr-ga-subtitle" style={{ margin: 0, marginTop: '2px' }}>ยอดขาย · สินค้า · ลูกค้า</p>
                        </div>
                    </div>
                    <button onClick={handleExportCSV} style={{ padding: '8px 16px', background: '#3b82f6', color: 'white', borderRadius: '6px', border: 'none', cursor: 'pointer', fontWeight: 'bold', display: 'flex', alignItems: 'center', gap: '6px' }}>
                        <RiDownload2Line size={16} /> Export CSV
                    </button>
                </div>
                <div className="wsr-ga-filters">
                    <div className="wsr-ga-filter-group">
                        <label>ช่วงเวลา</label>
                        <select value={preset} onChange={(e) => setPreset(e.target.value)}>
                            <option value="all">ทั้งหมด</option>
                            <option value="today">วันนี้</option>
                            <option value="last_7_days">7 วันล่าสุด</option>
                            <option value="this_month">เดือนนี้</option>
                            <option value="this_year">ปีนี้</option>
                            <option value="custom">กำหนดเอง</option>
                        </select>
                    </div>
                    {preset === 'custom' && (
                        <>
                            <div className="wsr-ga-filter-group">
                                <label>จากวันที่</label>
                                <input type="date" value={from} onChange={(e) => setFrom(e.target.value)} />
                            </div>
                            <div className="wsr-ga-filter-group">
                                <label>ถึงวันที่</label>
                                <input type="date" value={to} onChange={(e) => setTo(e.target.value)} />
                            </div>
                        </>
                    )}
                    {['monthly', 'heatmap', 'profit'].includes(selectedTab) && (
                        <div className="wsr-ga-filter-group">
                            <label>จัดกลุ่ม</label>
                            <select value={group} onChange={(e) => setGroup(e.target.value)}>
                                <option value="day">รายวัน</option>
                                <option value="week">รายสัปดาห์</option>
                                <option value="month">รายเดือน</option>
                            </select>
                        </div>
                    )}
                </div>
            </div>

            <div className="wsr-ga-tab-nav">
                {tabs.map((tab) => (
                    <button
                        key={tab.name}
                        className={`wsr-ga-tab-btn${selectedTab === tab.name ? ' active' : ''}`}
                        onClick={() => setSelectedTab(tab.name)}
                    >
                        <span className="wsr-ga-tab-icon">{tab.icon}</span>
                        {tab.title}
                    </button>
                ))}
            </div>

            <div className="wsr-tab-panel">
                {selectedTab === 'overview'  && <OverviewTab query={query} />}
                {selectedTab === 'monthly'   && <MonthlySalesTab query={query} group={group} />}
                {selectedTab === 'heatmap'   && <HeatmapTab query={query} group={group} />}
                {selectedTab === 'profit'    && <ProfitTab query={query} group={group} />}
                {selectedTab === 'status'    && <OrderStatusTab query={query} />}
                {selectedTab === 'delivery'  && <DeliveryMethodsTab query={query} />}
                {selectedTab === 'products'  && <TopProductsTab query={query} />}
                {selectedTab === 'customers' && <CustomersTab query={query} />}
            </div>
        </div>
    );
};

export default GraphAnalysis;
