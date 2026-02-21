/**
 * Sales Report Pro — Dashboard Logic
 * Vanilla JS only — no jQuery dependency
 * Requires: Chart.js 4.4.7 (chart.min.js) + emSalesConfig global object (injected by dashboard.tpl)
 */

/* eslint-disable no-unused-vars */
var EmSalesDashboard = (function () {
  'use strict';

  // ─── State ──────────────────────────────────────────────────────────────────
  var state = {
    dateFrom: '',
    dateTo: '',
    activeTab: 'products',
    activeMetric: 'revenue',
    chartType: 'line',
    granularity: 'day',
    sortBy: 'revenue',
    sortDir: 'DESC',
    currentPage: 1,
    categoryFilter: 0,
    chartInstance: null,
    tabLoaded: { products: true, categories: true, customers: true, suppliers: true }
  };

  // ─── Helpers ─────────────────────────────────────────────────────────────────
  function formatCurrency(value) {
    return parseFloat(value || 0).toLocaleString(undefined, {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    }) + '\u00a0' + (emSalesConfig.currencySign || '');
  }

  function formatNumber(value) {
    return parseFloat(value || 0).toLocaleString(undefined, {
      minimumFractionDigits: 0,
      maximumFractionDigits: 2
    });
  }

  function escapeHtml(str) {
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(str != null ? String(str) : ''));
    return div.innerHTML;
  }

  function showLoading(id) {
    var el = document.getElementById(id);
    if (el) { el.style.display = 'block'; }
  }

  function hideLoading(id) {
    var el = document.getElementById(id);
    if (el) { el.style.display = 'none'; }
  }

  function qs(selector, ctx) {
    return (ctx || document).querySelector(selector);
  }

  function qsa(selector, ctx) {
    return Array.prototype.slice.call((ctx || document).querySelectorAll(selector));
  }

  function buildUrl(base, params) {
    var parts = [];
    Object.keys(params).forEach(function (k) {
      if (params[k] !== undefined && params[k] !== null) {
        parts.push(encodeURIComponent(k) + '=' + encodeURIComponent(params[k]));
      }
    });
    return base + (base.indexOf('?') === -1 ? '?' : '&') + parts.join('&');
  }

  // ─── Chart ────────────────────────────────────────────────────────────────────
  function getChartLabels(seriesData) {
    return seriesData.map(function (d) { return d.period || d.date || d.label || ''; });
  }

  function getChartValues(seriesData, metric) {
    return seriesData.map(function (d) {
      // avg_ticket no viene del servidor, se calcula en cliente
      if (metric === 'avg_ticket') {
        var orders = parseFloat(d.orders || 0);
        return orders > 0 ? parseFloat(d.revenue || 0) / orders : 0;
      }
      return parseFloat(d[metric] || 0);
    });
  }

  function getGradient(ctx, colorRgb) {
    var gradient = ctx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, 'rgba(' + colorRgb + ', 0.35)');
    gradient.addColorStop(1, 'rgba(' + colorRgb + ', 0.02)');
    return gradient;
  }

  function initChart() {
    var canvas = qs('#emsales-main-chart');
    if (!canvas || typeof Chart === 'undefined') { return; }

    var seriesData = emSalesConfig.timeSeries || [];
    var labels = getChartLabels(seriesData);
    var values = getChartValues(seriesData, state.activeMetric);
    var ctx = canvas.getContext('2d');

    var colorRgb = '78, 115, 223'; // blue
    var borderColor = '#4e73df';
    var isBar = state.chartType === 'bar';

    if (state.chartInstance) {
      state.chartInstance.destroy();
      state.chartInstance = null;
    }

    state.chartInstance = new Chart(ctx, {
      type: isBar ? 'bar' : 'line',
      data: {
        labels: labels,
        datasets: [{
          label: getMetricLabel(state.activeMetric),
          data: values,
          borderColor: borderColor,
          backgroundColor: isBar ? 'rgba(78,115,223,0.6)' : getGradient(ctx, colorRgb),
          borderWidth: isBar ? 1 : 2,
          pointRadius: values.length > 60 ? 0 : 3,
          pointHoverRadius: 5,
          fill: !isBar,
          tension: 0.3
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: function (context) {
                var v = context.parsed.y;
                if (state.activeMetric === 'orders') {
                  return ' ' + formatNumber(v);
                }
                return ' ' + formatCurrency(v);
              }
            }
          }
        },
        scales: {
          x: {
            grid: { display: false },
            ticks: { maxTicksLimit: 12, maxRotation: 45 }
          },
          y: {
            beginAtZero: true,
            grid: { color: 'rgba(0,0,0,0.05)' },
            ticks: {
              callback: function (value) {
                if (state.activeMetric === 'orders') { return formatNumber(value); }
                return formatCurrency(value);
              }
            }
          }
        }
      }
    });
  }

  function updateChart(seriesData) {
    if (!state.chartInstance) { initChart(); return; }
    var labels = getChartLabels(seriesData);
    var values = getChartValues(seriesData, state.activeMetric);
    state.chartInstance.data.labels = labels;
    state.chartInstance.data.datasets[0].data = values;
    state.chartInstance.data.datasets[0].label = getMetricLabel(state.activeMetric);
    state.chartInstance.update();
  }

  function getMetricLabel(metric) {
    var labels = {
      revenue: emSalesConfig.i18n ? emSalesConfig.i18n.revenue : 'Ingresos',
      orders: emSalesConfig.i18n ? emSalesConfig.i18n.orders : 'Pedidos',
      avg_ticket: emSalesConfig.i18n ? emSalesConfig.i18n.avg_ticket : 'Ticket Medio'
    };
    return labels[metric] || metric;
  }

  // ─── KPI DOM updates ─────────────────────────────────────────────────────────
  function updateKpiDom(kpis, variations) {
    variations = variations || {};
    var currencyKeys = ['revenue', 'avg_ticket', 'gross_margin', 'margin_pct'];

    // Map server keys → DOM ids  (deben coincidir con kpi_cards.tpl)
    var keyMap = {
      total_revenue:          'revenue',      // #kpi-val-revenue
      total_orders:           'orders',       // #kpi-val-orders
      avg_ticket:             'avg-ticket',   // #kpi-val-avg-ticket
      gross_margin:           'margin',       // #kpi-val-margin
      margin_pct:             'margin-pct',   // #kpi-val-margin-pct
      unique_customers:       'customers',    // #kpi-val-customers
      total_products_sold:    'units',        // #kpi-val-units
      avg_products_per_order: 'avg-products'  // #kpi-val-avg-products
    };

    Object.keys(keyMap).forEach(function (serverKey) {
      var domKey = keyMap[serverKey];
      var valEl  = qs('#kpi-val-' + domKey);
      var varEl  = qs('#kpi-var-' + domKey);
      if (!valEl) { return; }

      var raw = kpis[serverKey];
      var formatted;
      if (serverKey === 'margin_pct') {
        formatted = parseFloat(raw || 0).toFixed(1) + '%';
      } else if (serverKey === 'total_orders' || serverKey === 'total_products_sold' || serverKey === 'unique_customers') {
        formatted = formatNumber(raw);
      } else if (serverKey === 'avg_products_per_order') {
        formatted = parseFloat(raw || 0).toFixed(2);
      } else {
        formatted = formatCurrency(raw);
      }
      valEl.textContent = formatted;

      var vd = variations[serverKey];
      if (varEl && vd) {
        var card = valEl.closest ? valEl.closest('.emsales-kpi-card') : null;
        if (card) {
          card.classList.remove('kpi-up', 'kpi-down', 'kpi-neutral');
          card.classList.add('kpi-' + (vd.direction || 'neutral'));
        }
        varEl.textContent = vd.formatted || '';
        varEl.className = 'kpi-variation kpi-' + (vd.direction || 'neutral');
      }
    });
  }

  // ─── Table rendering ─────────────────────────────────────────────────────────
  function renderProductsRows(items) {
    if (!items || !items.length) {
      return '<tr><td colspan="8" class="text-center text-muted" style="padding:30px;">' +
             '<i class="icon-warning"></i> Sin datos para el período seleccionado.' +
             '</td></tr>';
    }
    return items.map(function (p) {
      var marginClass = p.margin_pct > 30 ? 'badge-success' : (p.margin_pct > 15 ? 'badge-warning' : 'badge-danger');
      var stockClass = p.current_stock <= 0 ? 'text-danger' : (p.current_stock < 5 ? 'text-warning' : '');
      var name = (p.product_name || '').substring(0, 50);
      return '<tr>' +
        '<td><span title="' + escapeHtml(p.product_name) + '">' + escapeHtml(name) + '</span></td>' +
        '<td><code>' + escapeHtml(p.product_reference || '') + '</code></td>' +
        '<td class="text-right">' + parseInt(p.qty_sold || 0) + '</td>' +
        '<td class="text-right">' + formatCurrency(p.revenue) + '</td>' +
        '<td class="text-right">' + formatCurrency(p.cost) + '</td>' +
        '<td class="text-right">' + formatCurrency(p.margin) + '</td>' +
        '<td class="text-right"><span class="badge ' + marginClass + '">' + parseFloat(p.margin_pct || 0).toFixed(1) + '%</span></td>' +
        '<td class="text-right"><span class="' + stockClass + '">' + parseInt(p.current_stock || 0) + '</span></td>' +
        '</tr>';
    }).join('');
  }

  function renderCategoriesRows(items) {
    if (!items || !items.length) {
      return '<tr><td colspan="7" class="text-center text-muted" style="padding:30px;">Sin datos.</td></tr>';
    }
    return items.map(function (c) {
      var marginClass = c.margin_pct > 30 ? 'badge-success' : (c.margin_pct > 15 ? 'badge-warning' : 'badge-danger');
      return '<tr>' +
        '<td>' + escapeHtml(c.category_name || '') + '</td>' +
        '<td class="text-right">' + parseInt(c.num_products || 0) + '</td>' +
        '<td class="text-right">' + parseInt(c.qty_sold || 0) + '</td>' +
        '<td class="text-right">' + formatCurrency(c.revenue) + '</td>' +
        '<td class="text-right">' + formatCurrency(c.cost) + '</td>' +
        '<td class="text-right">' + formatCurrency(c.margin) + '</td>' +
        '<td class="text-right"><span class="badge ' + marginClass + '">' + parseFloat(c.margin_pct || 0).toFixed(1) + '%</span></td>' +
        '</tr>';
    }).join('');
  }

  function renderCustomersRows(items) {
    if (!items || !items.length) {
      return '<tr><td colspan="7" class="text-center text-muted" style="padding:30px;">Sin datos.</td></tr>';
    }
    return items.map(function (c) {
      var isVip = parseFloat(c.total_spent) > parseFloat(c.avg_ticket) * 10;
      var vipBadge = isVip ? ' <span class="badge badge-warning" title="VIP">★ VIP</span>' : '';
      var lastOrder = c.last_order_date ? c.last_order_date.substring(0, 10) : '';
      return '<tr>' +
        '<td>' + escapeHtml((c.firstname || '') + ' ' + (c.lastname || '')) + vipBadge + '</td>' +
        '<td><a href="mailto:' + escapeHtml(c.email || '') + '" style="font-size:.85em;">' + escapeHtml(c.email || '') + '</a></td>' +
        '<td>' + escapeHtml(c.company || '') + '</td>' +
        '<td class="text-right">' + parseInt(c.total_orders || 0) + '</td>' +
        '<td class="text-right"><strong>' + formatCurrency(c.total_spent) + '</strong></td>' +
        '<td class="text-right">' + formatCurrency(c.avg_ticket) + '</td>' +
        '<td><span class="text-muted" style="font-size:.85em;">' + escapeHtml(lastOrder) + '</span></td>' +
        '</tr>';
    }).join('');
  }

  function renderSuppliersRows(items) {
    if (!items || !items.length) {
      return '<tr><td colspan="7" class="text-center text-muted" style="padding:30px;">Sin datos.</td></tr>';
    }
    return items.map(function (s) {
      var marginClass = s.margin_pct > 30 ? 'badge-success' : (s.margin_pct > 15 ? 'badge-warning' : 'badge-danger');
      return '<tr>' +
        '<td><strong>' + escapeHtml(s.supplier_name || '') + '</strong></td>' +
        '<td class="text-right">' + parseInt(s.num_products || 0) + '</td>' +
        '<td class="text-right">' + parseInt(s.qty_sold || 0) + '</td>' +
        '<td class="text-right">' + formatCurrency(s.revenue) + '</td>' +
        '<td class="text-right">' + formatCurrency(s.cost) + '</td>' +
        '<td class="text-right">' + formatCurrency(s.margin) + '</td>' +
        '<td class="text-right"><span class="badge ' + marginClass + '">' + parseFloat(s.margin_pct || 0).toFixed(1) + '%</span></td>' +
        '</tr>';
    }).join('');
  }

  function renderPagination(total, currentPage, perPage, containerId) {
    var container = document.getElementById(containerId);
    if (!container) { return; }

    var totalPages = Math.ceil(total / perPage);
    if (totalPages <= 1) { container.innerHTML = ''; return; }

    var maxVisible = 7;
    var html = '<nav><ul class="pagination pagination-sm" style="margin:10px 0;">';

    // Previous
    html += '<li class="' + (currentPage <= 1 ? 'disabled' : '') + '">';
    html += '<a href="#" data-page="' + Math.max(1, currentPage - 1) + '">&laquo;</a></li>';

    // Page numbers
    var startPage = Math.max(1, currentPage - Math.floor(maxVisible / 2));
    var endPage = Math.min(totalPages, startPage + maxVisible - 1);
    if (endPage - startPage < maxVisible - 1) {
      startPage = Math.max(1, endPage - maxVisible + 1);
    }

    if (startPage > 1) {
      html += '<li><a href="#" data-page="1">1</a></li>';
      if (startPage > 2) { html += '<li class="disabled"><span>&hellip;</span></li>'; }
    }

    for (var p = startPage; p <= endPage; p++) {
      html += '<li class="' + (p === currentPage ? 'active' : '') + '">';
      html += '<a href="#" data-page="' + p + '">' + p + '</a></li>';
    }

    if (endPage < totalPages) {
      if (endPage < totalPages - 1) { html += '<li class="disabled"><span>&hellip;</span></li>'; }
      html += '<li><a href="#" data-page="' + totalPages + '">' + totalPages + '</a></li>';
    }

    // Next
    html += '<li class="' + (currentPage >= totalPages ? 'disabled' : '') + '">';
    html += '<a href="#" data-page="' + Math.min(totalPages, currentPage + 1) + '">&raquo;</a></li>';

    html += '</ul></nav>';
    container.innerHTML = html;

    // Bind pagination clicks
    var tabName = containerId.replace('emsales-', '').replace('-pagination', '');
    qsa('a[data-page]', container).forEach(function (a) {
      a.addEventListener('click', function (e) {
        e.preventDefault();
        if (this.parentElement.classList.contains('disabled') ||
            this.parentElement.classList.contains('active')) { return; }
        handlePageChange(parseInt(this.getAttribute('data-page'), 10));
      });
    });
  }

  // ─── AJAX calls ───────────────────────────────────────────────────────────────
  // ─── Aplicar respuesta de tabla (reutilizado por refresh_all y refresh_table) ───────
  function applyTableResponse(data) {
    var tab = data.tab || state.activeTab;
    var tbody = qs('#emsales-' + tab + '-tbody');
    if (!tbody) { return; }

    var items = data.items || [];

    // Debug: si viene vacío, loguear para diagnostico
    if (!items.length) {
      var sqlErr = data._debug && data._debug.sqlError ? ' SQL_ERROR: ' + data._debug.sqlError : '';
      var states = data._debug && data._debug.states ? ' STATES: ' + JSON.stringify(data._debug.states) : '';
      console.warn('[EmSales] Tab "' + tab + '" vacío. dateFrom=' +
        state.dateFrom + ' dateTo=' + state.dateTo + sqlErr + states);
    }

    var rows = '';
    if (tab === 'products') {
      rows = renderProductsRows(items);
      updateCounter('emsales-products-total', data.total);
      renderPagination(data.total, state.currentPage, emSalesConfig.itemsPerPage || 50, 'emsales-products-pagination');
    } else if (tab === 'categories') {
      rows = renderCategoriesRows(items);
      updateCategoriesPieChart(items);
    } else if (tab === 'customers') {
      rows = renderCustomersRows(items);
      updateCounter('emsales-customers-total', data.total);
      renderPagination(data.total, state.currentPage, emSalesConfig.itemsPerPage || 50, 'emsales-customers-pagination');
    } else if (tab === 'suppliers') {
      rows = renderSuppliersRows(items);
    }
    tbody.innerHTML = rows;
    state.tabLoaded[tab] = true;
  }

  function updateCounter(id, value) {
    var el = document.getElementById(id);
    if (el) { el.textContent = parseInt(value || 0); }
  }

  // ─── applyFilters: UNA sola llamada refresh_all ───────────────────────────────
  function applyFilters() {
    state.currentPage = 1;
    var tab = state.activeTab;

    // Mostrar todos los spinners a la vez
    showLoading('emsales-kpis-loading');
    showLoading('emsales-chart-loading');
    showLoading('emsales-' + tab + '-loading');

    var url = buildUrl(emSalesConfig.ajaxUrl, {
      ajax: 1,
      action: 'refresh_all',
      date_from: state.dateFrom,
      date_to: state.dateTo,
      granularity: state.granularity,
      tab: tab,
      order_by: state.sortBy,
      order_dir: state.sortDir,
      page: 1,
      category_id: state.categoryFilter,
      per_page: emSalesConfig.itemsPerPage || 50
    });

    fetch(url)
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (!data.success) { console.error('[EmSales] refresh_all failed:', data.error); return; }

        // Debug: mostrar states, kpis y errores SQL del servidor
        if (data._debug) { console.log('[EmSales] AJAX _debug:', data._debug); }

        // KPIs
        if (data.kpis) { updateKpiDom(data.kpis, data.variations); }

        // Gráfico
        if (data.timeSeries) {
          emSalesConfig.timeSeries = data.timeSeries; // actualizar caché SSR
          if (data.granularity) { state.granularity = data.granularity; }
          updateChart(data.timeSeries);
        }

        // Tabla
        applyTableResponse(data);
      })
      .catch(function (err) { console.error('[EmSales] refresh_all error:', err); })
      .finally(function () {
        hideLoading('emsales-kpis-loading');
        hideLoading('emsales-chart-loading');
        hideLoading('emsales-' + tab + '-loading');
      });
  }

  // ─── Refresco individual de KPIs (p.ej. sin cambiar fechas) ─────────────────
  function refreshKPIs() {
    showLoading('emsales-kpis-loading');
    var url = buildUrl(emSalesConfig.ajaxUrl, {
      ajax: 1, action: 'refresh_kpis',
      date_from: state.dateFrom, date_to: state.dateTo
    });
    fetch(url)
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (data.success && data.kpis) { updateKpiDom(data.kpis, data.variations); }
      })
      .catch(function (err) { console.error('[EmSales] KPI error:', err); })
      .finally(function () { hideLoading('emsales-kpis-loading'); });
  }

  // ─── Refresco individual del gráfico (p.ej. cambio de granularidad) ─────────
  function refreshChart() {
    showLoading('emsales-chart-loading');
    var url = buildUrl(emSalesConfig.ajaxUrl, {
      ajax: 1, action: 'refresh_chart',
      date_from: state.dateFrom, date_to: state.dateTo,
      granularity: state.granularity
    });
    fetch(url)
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (data.success && data.timeSeries) {
          emSalesConfig.timeSeries = data.timeSeries;
          updateChart(data.timeSeries);
        }
      })
      .catch(function (err) { console.error('[EmSales] Chart error:', err); })
      .finally(function () { hideLoading('emsales-chart-loading'); });
  }

  // ─── Refresco individual de tabla (p.ej. cambio de tab, sort, paginación) ───
  function refreshTable() {
    var tab = state.activeTab;
    var loadingId = 'emsales-' + tab + '-loading';
    showLoading(loadingId);

    var url = buildUrl(emSalesConfig.ajaxUrl, {
      ajax: 1, action: 'refresh_table',
      tab: tab,
      date_from: state.dateFrom, date_to: state.dateTo,
      order_by: state.sortBy, order_dir: state.sortDir,
      page: state.currentPage,
      category_id: state.categoryFilter,
      per_page: emSalesConfig.itemsPerPage || 50
    });

    fetch(url)
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (data.success) { applyTableResponse(data); }
      })
      .catch(function (err) { console.error('[EmSales] Table error:', err); })
      .finally(function () { hideLoading(loadingId); });
  }

  // ─── Categories donut chart ───────────────────────────────────────────────────
  var categoriesChartInstance = null;
  function updateCategoriesPieChart(items) {
    var canvas = qs('#emsales-categories-chart');
    if (!canvas || typeof Chart === 'undefined') { return; }

    var visible = items ? items.slice(0, 8) : [];
    if (!visible.length) {
      var emptyEl = qs('#emsales-categories-chart-empty');
      if (emptyEl) { emptyEl.style.display = ''; }
      canvas.style.display = 'none';
      return;
    }

    canvas.style.display = '';
    var emEl = qs('#emsales-categories-chart-empty');
    if (emEl) { emEl.style.display = 'none'; }

    var palette = ['#4e73df','#1cc88a','#36b9cc','#f6c23e','#e74a3b','#858796','#5a5c69','#6f42c1'];
    var labels = visible.map(function (c) { return c.category_name || ''; });
    var values = visible.map(function (c) { return parseFloat(c.revenue || 0); });

    if (categoriesChartInstance) { categoriesChartInstance.destroy(); }

    categoriesChartInstance = new Chart(canvas.getContext('2d'), {
      type: 'doughnut',
      data: {
        labels: labels,
        datasets: [{ data: values, backgroundColor: palette, borderWidth: 2 }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } },
          tooltip: {
            callbacks: {
              label: function (ctx) {
                return ' ' + escapeHtml(ctx.label) + ': ' + formatCurrency(ctx.parsed);
              }
            }
          }
        }
      }
    });
  }

  // ─── Event handlers ───────────────────────────────────────────────────────────
  function handlePresetClick(e) {
    e.preventDefault();
    var btn = e.currentTarget;
    var days = btn.getAttribute('data-days');
    var period = btn.getAttribute('data-period');
    var today = new Date();
    var fmt = function (d) {
      return d.getFullYear() + '-' +
        String(d.getMonth() + 1).padStart(2, '0') + '-' +
        String(d.getDate()).padStart(2, '0');
    };

    var from, to;
    to = fmt(today);

    if (days) {
      var d = new Date(today);
      d.setDate(d.getDate() - parseInt(days, 10) + 1);
      from = fmt(d);
    } else if (period === 'month') {
      from = fmt(new Date(today.getFullYear(), today.getMonth(), 1));
      to = fmt(new Date(today.getFullYear(), today.getMonth() + 1, 0));
    } else if (period === 'year') {
      from = today.getFullYear() + '-01-01';
      to = today.getFullYear() + '-12-31';
    }

    var fromInput = qs('#emsales-date-from');
    var toInput = qs('#emsales-date-to');
    if (fromInput) { fromInput.value = from; }
    if (toInput) { toInput.value = to; }
    state.dateFrom = from;
    state.dateTo = to;

    // Toggle active state
    qsa('.emsales-preset').forEach(function (b) { b.classList.remove('active', 'btn-primary'); b.classList.add('btn-default'); });
    btn.classList.remove('btn-default');
    btn.classList.add('active', 'btn-primary');

    applyFilters();
  }

  function handleTabClick(e) {
    e.preventDefault();
    var btn = e.currentTarget;
    var tab = btn.getAttribute('data-tab');
    if (!tab || tab === state.activeTab) { return; }

    // Bootstrap nav-tabs: active va en el <li> padre
    qsa('#emsales-tabs li').forEach(function (li) { li.classList.remove('active'); });
    var liParent = btn.parentElement;
    if (liParent) { liParent.classList.add('active'); }

    // Mostrar/ocultar panes con clase Bootstrap (no inline style) — acotado a nuestro contenedor
    var tabContent = qs('#emsales-data-panel .tab-content');
    if (tabContent) {
      qsa('.tab-pane', tabContent).forEach(function (p) {
        p.classList.remove('active', 'in');
        p.style.display = '';  // limpiar cualquier inline style previo
      });
    }
    var pane = qs('#tab-' + tab);
    if (pane) {
      pane.classList.add('active', 'in');
    }

    state.activeTab = tab;
    state.currentPage = 1;
    // Reset sort per tab defaults
    state.sortBy = 'revenue';
    state.sortDir = 'DESC';

    // Siempre recargar datos para la pestaña activa (fechas pueden haber cambiado)
    refreshTable();
  }

  function handleSort(e) {
    var th = e.currentTarget;
    var sortField = th.getAttribute('data-sort');
    if (!sortField) { return; }

    if (state.sortBy === sortField) {
      state.sortDir = state.sortDir === 'DESC' ? 'ASC' : 'DESC';
    } else {
      state.sortBy = sortField;
      state.sortDir = 'DESC';
    }
    state.currentPage = 1;

    // Update sort icons in the active table
    var tableId = '#emsales-' + state.activeTab + '-table';
    qsa(tableId + ' .emsales-sortable').forEach(function (h) {
      h.classList.remove('sort-asc', 'sort-desc');
      var icon = h.querySelector('i');
      if (icon) { icon.className = 'icon-sort'; }
    });
    th.classList.add(state.sortDir === 'ASC' ? 'sort-asc' : 'sort-desc');
    var activeIcon = th.querySelector('i');
    if (activeIcon) {
      activeIcon.className = state.sortDir === 'ASC' ? 'icon-sort-up' : 'icon-sort-down';
    }

    refreshTable();
  }

  function handlePageChange(page) {
    state.currentPage = page;
    refreshTable();
    // Scroll to top of table
    var wrapper = qs('#emsales-' + state.activeTab + '-table-wrapper');
    if (wrapper) { wrapper.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
  }

  // ─── Sort header delegation ───────────────────────────────────────────────────
  function bindSortHeaders() {
    qsa('.emsales-sortable').forEach(function (th) {
      th.style.cursor = 'pointer';
      th.addEventListener('click', handleSort);
    });
  }

  // ─── Init ─────────────────────────────────────────────────────────────────────
  function init() {
    if (typeof emSalesConfig === 'undefined') {
      console.error('[EmSales] emSalesConfig not found.');
      return;
    }

    // Seed state from server-provided config
    state.dateFrom = emSalesConfig.dateFrom || '';
    state.dateTo = emSalesConfig.dateTo || '';
    state.activeTab = emSalesConfig.activeTab || 'products';
    state.chartType = emSalesConfig.chartType || 'line';
    state.granularity = emSalesConfig.granularity || 'day';

    // Debug: mostrar KPIs iniciales del servidor en consola
    if (emSalesConfig.ssrKpis) {
      var kv = emSalesConfig.ssrKpis;
      console.log('[EmSales] SSR KPIs:', kv);
      if (!kv.total_orders || kv.total_orders === 0) {
        console.warn('[EmSales] KPIs SSR = 0. Posibles causas: ' +
          'estados de pedido mal configurados o rango de fechas sin pedidos. ' +
          'Verifica EMSALES_VALID_ORDER_STATES en la configuracioan del módulo.');
      }
    }

    // ── Filter apply button ──
    var applyBtn = qs('#emsales-apply-filters');
    if (applyBtn) {
      applyBtn.addEventListener('click', function (e) {
        e.preventDefault();
        var fromInput = qs('#emsales-date-from');
        var toInput = qs('#emsales-date-to');
        if (fromInput) { state.dateFrom = fromInput.value; }
        if (toInput) { state.dateTo = toInput.value; }
        // Clear preset active states
        qsa('.emsales-preset').forEach(function (b) { b.classList.remove('active', 'btn-primary'); b.classList.add('btn-default'); });
        applyFilters();
      });
    }

    // ── Preset buttons ──
    qsa('.emsales-preset').forEach(function (btn) {
      btn.addEventListener('click', handlePresetClick);
    });

    // ── Tab buttons ──
    // Los <a> dentro de #emsales-tabs tienen data-tab y son los elementos clicables
    qsa('#emsales-tabs a[data-tab]').forEach(function (btn) {
      btn.addEventListener('click', handleTabClick);
    });

    // ── Sort headers ──
    bindSortHeaders();

    // ── Chart metric buttons ──
    qsa('.emsales-chart-metric').forEach(function (btn) {
      btn.addEventListener('click', function () {
        qsa('.emsales-chart-metric').forEach(function (b) {
          b.classList.remove('active', 'btn-primary');
          b.classList.add('btn-default');
        });
        this.classList.remove('btn-default');
        this.classList.add('active', 'btn-primary');
        state.activeMetric = this.getAttribute('data-metric') || 'revenue';
        // Re-render chart con datos en caché (no requiere AJAX)
        // updateChart maneja chartInstance null llamando a initChart()
        if (emSalesConfig.timeSeries) {
          updateChart(emSalesConfig.timeSeries);
        }
      });
    });

    // ── Chart type buttons ──
    qsa('.emsales-chart-type').forEach(function (btn) {
      btn.addEventListener('click', function () {
        state.chartType = this.getAttribute('data-type') || 'line';
        qsa('.emsales-chart-type').forEach(function (b) {
          b.classList.remove('active', 'btn-primary');
          b.classList.add('btn-default');
        });
        this.classList.remove('btn-default');
        this.classList.add('active', 'btn-primary');
        // Rebuild chart from scratch (type change requires new instance)
        // IMPORTANTE: destruir antes de null para liberar el canvas
        if (state.chartInstance) { state.chartInstance.destroy(); }
        state.chartInstance = null;
        initChart();
      });
    });

    // ── Granularity buttons ──
    qsa('.emsales-gran').forEach(function (btn) {
      btn.addEventListener('click', function () {
        state.granularity = this.getAttribute('data-gran') || 'day';
        qsa('.emsales-gran').forEach(function (b) {
          b.classList.remove('active', 'btn-primary');
          b.classList.add('btn-default');
        });
        this.classList.remove('btn-default');
        this.classList.add('active', 'btn-primary');
        refreshChart();
      });
    });

    // ── Category filter ──
    var catFilter = qs('#emsales-category-filter');
    if (catFilter) {
      catFilter.addEventListener('change', function () {
        state.categoryFilter = parseInt(this.value || 0, 10);
        state.currentPage = 1;
        refreshTable();
      });
    }

    // ── Initialize Chart.js with SSR data ──
    initChart();
  }

  // ─── Public API ───────────────────────────────────────────────────────────────
  return {
    init: init,
    state: state,
    applyFilters: applyFilters,
    refreshTable: refreshTable,
    refreshChart: refreshChart,
    refreshKPIs: refreshKPIs
  };

}());

// Bootstrap once DOM ready
document.addEventListener('DOMContentLoaded', function () {
  EmSalesDashboard.init();
});

