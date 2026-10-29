# Graph Analysis Component Documentation

## Overview

The Graph Analysis component is a React-based dashboard for visualizing sales data, product performance, customer analytics, and delivery metrics. It provides interactive charts and statistics through a tabbed interface.

## Folder Structure

```
src/
├── graph-analysis.js          # Main component with tab navigation
├── tabs/                      # Individual tab components
│   ├── OverviewTab.js        # Overview statistics and recent orders
│   ├── MonthlySalesTab.js    # Monthly sales trends with multiple chart types
│   ├── HeatmapTab.js         # Calendar-based heatmap visualization
│   ├── ProfitTab.js          # Profit analysis (cost vs profit)
│   ├── OrderStatusTab.js     # Order status distribution
│   ├── DeliveryMethodsTab.js  # Delivery method statistics
│   ├── TopProductsTab.js     # Top-selling products analysis
│   └── CustomersTab.js       # Customer analytics
└── components/               # Reusable components (if any)
```

## Tab Components

### OverviewTab
- **Purpose**: Display overall sales statistics, order status, and recent orders
- **Features**:
  - Total sales, orders, average order value
  - Shipped vs pending orders
  - Customer and product statistics
  - Recent orders list (last 7 days)
  - Pie chart showing shipping status

### MonthlySalesTab
- **Props**: `query` (string), `group` (string)
- **Features**:
  - Bar, Line, and Area Pieces chart types
  - Support for daily, weekly, and monthly grouping
  - Visual map for performance thresholds (Area Pieces)
- **Chart Types**:
  - Bar: Standard bar chart
  - Line: Smooth line chart
  - Area Pieces: Line chart with color-coded performance zones

### HeatmapTab
- **Props**: `query` (string), `group` (string)
- **Features**:
  - Calendar heatmap visualization
  - Support for day, week, and month grouping
  - Color-coded sales intensity

### ProfitTab
- **Props**: `query` (string), `group` (string)
- **Features**:
  - Total revenue, cost, and profit statistics
  - Stacked Bar chart (cost + profit)
  - Stacked Line chart (cost + profit with area fill)
  - Pie chart (profit distribution)

### OrderStatusTab
- **Props**: `query` (string)
- **Features**:
  - Pending, paid, shipped, refunded order counts
  - Pie chart showing status distribution

### DeliveryMethodsTab
- **Props**: `query` (string)
- **Features**:
  - Pickup, EMS, Messenger statistics
  - Multi-chart support: Pie, Stacked Line (as line chart), Bar Race
  - Stacked Line chart uses smoothing and area fill

### TopProductsTab
- **Props**: `query` (string)
- **Features**:
  - Group by: Product, Category, Brand, Model
  - Multi-chart support: Pie, Stacked Area, Bar Race
  - Displays top 100 products by orders and revenue
  - Ranked list with edit links

### CustomersTab
- **Props**: `query` (string)
- **Features**:
  - Total customers and customers without orders
  - Multi-chart support: Pie, Stacked Line (as line chart), Bar Race
  - Top 100 customers by orders and revenue
  - Customer ranking badges (VIP, Gold, Silver)

## Main Component (GraphAnalysis)

The main component manages:
- Tab navigation
- Date range filters (all, today, last 7 days, this month, this year, custom)
- Grouping filter (day, week, month) for applicable tabs
- CSV export functionality

## Dependencies

- React hooks: `useState`, `useEffect`, `useRef`
- WordPress: `@wordpress/element`, `@wordpress/components`, `@wordpress/api-fetch`
- Charts: `echarts`
- Icons: `@remixicon/react`

## API Endpoints

- `/wsr/v1/graph/overview` - Overview statistics
- `/wsr/v1/graph/monthly-sales` - Monthly sales data
- `/wsr/v1/graph/profit` - Profit data
- `/wsr/v1/graph/status` - Order status data
- `/wsr/v1/graph/delivery` - Delivery methods data
- `/wsr/v1/graph/products` - Products data
- `/wsr/v1/graph/customers` - Customer data
- `/wsr/v1/graph/export-csv` - CSV export

## Development

### Building Assets

```bash
npm run dev
```

### Production Build

```bash
npm run build
```

## Chart Types Reference

| Chart Type | Description | Use Case |
|------------|-------------|----------|
| Bar | Standard bar chart | Comparing values across categories |
| Line | Smooth line chart | Trend analysis over time |
| Area | Line with area fill | Emphasizing volume |
| Stacked Line | Multiple series with area fill | Comparing related metrics (e.g., cost vs profit) |
| Stacked Area | Multiple series with stacked area | Multi-series volume comparison |
| Bar Race | Animated bar chart | Ranking visualization |
| Pie | Circular chart | Part-to-whole relationships |
| Donut | Pie with hollow center | Part-to-whole with emphasis |

## Styling

All components use CSS classes prefixed with `wsr-` (WordPress Sales Record). Styles should be defined in the plugin's CSS files.

## Helper Functions

- `formatWeekLabel(weekLabel)`: Converts ISO week format (e.g., "2023-W01") to Thai date format

## Notes

- All tabs use ECharts for visualization
- Charts automatically resize on window resize
- ECharts instances are properly disposed on unmount to prevent memory leaks
- Loading, error, and empty states are handled for each tab

## POS System (wsr-pos)

The POS admin page (`/wp-admin/admin.php?page=wsr-pos`) supports creating `sales_record` entries with near feature parity to the Sales Record edit screen.

### Supported Sales Record Fields

- `wsr_sale_date` (sale date)
- `wsr_status` (`pending`, `paid`, `shipped`, `refunded`)
- `wsr_delivery_method` (`pickup`, `ems`, `messenger`)
- `wsr_lazada_order`
- `wsr_bargained_price`
- `wsr_notes`
- `wsr_shipping_paid`, `wsr_shipping_actual`
- `wsr_product_ids` (preferred) and `wsr_products` (legacy compatibility)

### Realtime Financial Calculation (Async)

POS performs realtime calculations in the UI (implemented as an async effect) and displays:

- List-price totals
- Bargained totals
- Shipping totals
- Regular profit (list price)
- Actual profit (bargained)

### POS REST API

- `GET /wsr/v1/pos/search-customers`
- `GET /wsr/v1/pos/search-products`
- `POST /wsr/v1/pos/submit-order`

`submit-order` will insert a `sales_record` and save all supported meta fields.

## Facebook / Avatar Scam Tracking

This feature is intended to **track** the buyer profile/avatar information for later manual comparison (no Facebook API integration).

### Customer Meta (manual profile)

Stored on `customer` post type:

- `wsr_social_name`
- `wsr_social_url`
- `wsr_sales_channel`
- `wsr_fb_avatar_url`
- `wsr_fb_avatar_id` (WP attachment ID)
- `wsr_fb_flag` (`''`, `suspicious`, `scammer`, `verified`)
- `wsr_fb_flag_note`

These are editable in the Customer meta box UI (`src/customer-meta.js`).

### Sales Record Observed Snapshot (evidence at time of sale)

Stored on `sales_record`:

- `wsr_observed_social_url`
- `wsr_observed_avatar_url`
- `wsr_observed_avatar_id` (WP attachment ID)

### Comparison UI

- **POS page**: when selecting a customer, the UI loads the customer profile meta and shows a comparison card with quick-copy actions to fill the Observed snapshot fields.
- **Sales Record edit screen**: shows a side-by-side comparison section: "Customer vs Observed" with avatar previews and copy buttons.
