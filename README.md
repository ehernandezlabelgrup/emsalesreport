# Sales Report Pro â€” PrestaShop Module

**Module name:** `emsalesreport`  
**Version:** 1.0.0  
**Author:** EM Modules  
**Compatibility:** PrestaShop 8.0 â€“ 9.x  
**Minimum PHP:** 8.1  
**License:** Commercial

---

## Overview

Sales Report Pro is an advanced sales analytics dashboard for PrestaShop 8 and 9. It provides store owners with real-time KPIs, interactive charts, and detailed reports broken down by product, category, customer, and supplier â€” all accessible directly from the Back Office without any external services or third-party dependencies.

---

## Features

### KPI Dashboard
Eight key performance indicators displayed as summary cards at the top of the dashboard:

| KPI | Description |
|---|---|
| Total Revenue | Gross revenue from completed orders (tax-incl. or tax-excl., configurable) |
| Total Orders | Number of orders matching the selected states and date range |
| Average Ticket | Average revenue per order |
| Gross Margin | Revenue minus wholesale cost |
| Margin % | Gross margin as a percentage of revenue |
| Unique Customers | Number of distinct customers who ordered |
| Units Sold | Total product units sold |
| Avg. Products/Order | Average number of line items per order |

Each card shows the current period value alongside a **variance indicator** (% change vs. previous equivalent period).

### Interactive Chart
- Line chart or bar chart (toggleable via UI buttons)
- Three selectable metrics: **Revenue**, **Orders**, **Average Ticket**
- Three time granularities: **Daily**, **Weekly** (aligned to Monday), **Monthly**
- Rendered client-side with **Chart.js 4.4.7** (bundled â€” no CDN required)

### Data Tables (tabbed)

Four tabs, each with server-side pagination, sortable columns, and download:

**Products**
- Product name, reference, units sold, revenue, cost, gross margin, margin %

**Categories**
- Category name, units sold, revenue, cost, gross margin, margin %

**Customers**
- Customer name, number of orders, total spent, average ticket, first/last order date

**Suppliers / Brands**
- Supplier name, units sold, revenue, cost, gross margin

### Date Range Filter
- Predefined quick ranges: Today, Yesterday, Last 7 days, Last 30 days, This month, Last month, This year
- Custom date picker (from / to)
- All data reloads via a **single AJAX call** (`refresh_all` action) that returns KPIs, chart data, and the active tab table in one round-trip

### Export
- Download any table as **CSV** (semicolon-delimited, UTF-8 BOM for Excel compatibility)
- Filename includes the report type and a timestamp, e.g. `sales_report_products_2026-02-21_14-30-00.csv`
- European decimal format (comma separator) applied automatically

### Configuration Page
- **Visual order state selector**: checkbox grid showing each order state with its color badge and a "Paid" indicator â€” no need to manually type state IDs
- Toggle tax-inclusive / tax-exclusive totals
- Set default date range (in days)
- Set items per page (pagination)
- Enable/disable period comparison

---

## Technical Specifications

### Architecture

```
emsalesreport/
â”œâ”€â”€ emsalesreport.php                         # Module main class
â”œâ”€â”€ controllers/admin/
â”‚   â””â”€â”€ AdminEmSalesReportController.php      # AJAX + page controller
â”œâ”€â”€ classes/
â”‚   â”œâ”€â”€ EmSalesReportQuery.php                # All SQL queries
â”‚   â”œâ”€â”€ EmSalesReportKPI.php                  # KPI calculation helpers
â”‚   â””â”€â”€ EmSalesReportExport.php               # CSV export
â”œâ”€â”€ views/
â”‚   â”œâ”€â”€ templates/admin/
â”‚   â”‚   â”œâ”€â”€ dashboard.tpl                     # Main dashboard layout
â”‚   â”‚   â””â”€â”€ partials/
â”‚   â”‚       â”œâ”€â”€ kpi_cards.tpl
â”‚   â”‚       â”œâ”€â”€ chart_area.tpl
â”‚   â”‚       â”œâ”€â”€ filters_bar.tpl
â”‚   â”‚       â”œâ”€â”€ tab_products.tpl
â”‚   â”‚       â”œâ”€â”€ tab_categories.tpl
â”‚   â”‚       â”œâ”€â”€ tab_customers.tpl
â”‚   â”‚       â””â”€â”€ tab_suppliers.tpl
â”‚   â”œâ”€â”€ css/admin/dashboard.css
â”‚   â””â”€â”€ js/admin/
â”‚       â”œâ”€â”€ chart.min.js                      # Chart.js 4.4.7 (bundled)
â”‚       â”œâ”€â”€ dashboard.js                      # All dashboard interactivity
â”‚       â”œâ”€â”€ export.js                         # Export trigger logic
â”‚       â””â”€â”€ config.js                         # Config page checkbox toggle
â”œâ”€â”€ translations/
â”‚   â”œâ”€â”€ es.php
â”‚   â”œâ”€â”€ en.php
â”‚   â””â”€â”€ fr.php
â””â”€â”€ sql/                                      # Install/uninstall SQL (if any)
```

### JavaScript
- **Vanilla JS only** â€” no jQuery dependency (PS9-forward compatible)
- `fetch()` API for all AJAX calls
- Chart.js loaded from the module bundle (no external CDN)
- Content Security Policy (CSP) compliant â€” no inline `<script>` blocks

### SQL & Database
- All queries built with `Db::getInstance()` and `pSQL()` / `intval()` sanitisation
- No custom tables required â€” reads from core PS tables only:
  - `ps_orders`, `ps_order_detail`, `ps_order_state`
  - `ps_product_lang`, `ps_category_lang`
  - `ps_customer`, `ps_supplier`, `ps_manufacturer`
- Wholesale cost detection adapts automatically to the PS installation:
  1. `original_wholesale_price` (PS 8.1+)
  2. `product_wholesale_price` (classic PS)
  3. Falls back to `0.00` if neither column exists (margin = revenue)
- Weekly granularity uses `DATE_SUB(DATE, INTERVAL WEEKDAY(date) DAY)` to guarantee Monday-aligned keys

### Hooks Used
| Hook | Purpose |
|---|---|
| `displayBackOfficeHeader` | Loads CSS, JS, and config.js on the relevant admin pages |

### Configuration Keys
| Key | Default | Description |
|---|---|---|
| `EMSALES_DEFAULT_PERIOD` | `30` | Default date range in days |
| `EMSALES_VALID_ORDER_STATES` | *(auto)* | Comma-separated paid order state IDs |
| `EMSALES_INCLUDE_TAX` | `1` | 1 = tax-inclusive totals, 0 = tax-exclusive |
| `EMSALES_ITEMS_PER_PAGE` | `50` | Rows per page in data tables |
| `EMSALES_CHART_TYPE` | `line` | Default chart type: `line` or `bar` |
| `EMSALES_COMPARE_ENABLED` | `1` | Enable period-over-period variance |
| `EMSALES_CURRENCY_DEFAULT` | `0` | 0 = shop default currency |

---

## Installation

1. Download or clone the `emsalesreport` folder.
2. Compress it into `emsalesreport.zip` (the folder must be at the root of the ZIP).
3. In PrestaShop Back Office go to **Modules â†’ Module Manager â†’ Upload a module**.
4. Upload the ZIP and click **Install**.
5. The module adds a **Sales Report Pro** entry under the **Stats** section of the left navigation.

### Manual upload (FTP)
Copy the entire `emsalesreport/` folder to `/modules/` on your server, then install from the Module Manager.

---

## Requirements

- PrestaShop **8.0** or **9.x**
- PHP **8.1+**
- MySQL / MariaDB (standard PS requirement)
- No additional PHP extensions required

---

## Translations

Translations included out of the box:

| Locale | Status |
|---|---|
| Spanish (`es`) | âœ… Complete |
| English (`en`) | âœ… Complete |
| French (`fr`) | âœ… Complete |

Additional locales can be added under `translations/` following the standard PrestaShop translation file format.

---

## Uninstall

Uninstalling the module removes:
- The Back Office tab
- All `EMSALES_*` configuration keys

No database tables are created by this module, so no table cleanup is needed.

---

## Changelog

### 1.0.0 — Initial release
- Full KPI dashboard with 8 metrics and period comparison
- Interactive Chart.js chart (line/bar, 3 metrics, 3 granularities)
- Four data tables: Products, Categories, Customers, Suppliers
- CSV export for all tables
- Visual order state selector in configuration
- Single AJAX endpoint (`refresh_all`) for optimised performance
- PS8 and PS9 compatibility (CSP-safe, jQuery-free)
- Wholesale cost auto-detection (PS 8.1+ / classic PS / fallback)
- Translations: ES, EN, FR

---

## Support

For bug reports or feature requests, please contact EM Modules.

---

## License

This module is commercial software. All rights reserved.
