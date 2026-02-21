/**
 * Sales Report Pro — Export Handlers
 * Builds download URLs from current dashboard state and triggers file download.
 */

document.addEventListener('DOMContentLoaded', function () {
  'use strict';

  if (typeof emSalesConfig === 'undefined') { return; }

  /**
   * Build export URL from current dashboard state + button attributes.
   */
  function buildExportUrl(tab, format) {
    var s = EmSalesDashboard.state;
    var base = emSalesConfig.exportUrl;
    var params = [
      'export=1',
      'tab='         + encodeURIComponent(tab),
      'format='      + encodeURIComponent(format),
      'date_from='   + encodeURIComponent(s.dateFrom || emSalesConfig.dateFrom || ''),
      'date_to='     + encodeURIComponent(s.dateTo   || emSalesConfig.dateTo   || ''),
      'order_by='    + encodeURIComponent(s.sortBy   || 'revenue'),
      'order_dir='   + encodeURIComponent(s.sortDir  || 'DESC'),
      'category_id=' + encodeURIComponent(s.categoryFilter || 0)
    ];
    return base + (base.indexOf('?') === -1 ? '?' : '&') + params.join('&');
  }

  /**
   * Trigger a file download without navigating away from the page.
   */
  function triggerDownload(url, filename) {
    var a = document.createElement('a');
    a.href = url;
    a.download = filename || 'sales-report';
    a.style.display = 'none';
    document.body.appendChild(a);
    a.click();
    setTimeout(function () { document.body.removeChild(a); }, 200);
  }

  /**
   * Build a human-readable filename for the export.
   */
  function buildFilename(tab, format) {
    var s = EmSalesDashboard.state;
    var from = (s.dateFrom || '').replace(/-/g, '');
    var to   = (s.dateTo   || '').replace(/-/g, '');
    var ext  = format === 'excel' ? 'xlsx' : 'csv';
    return 'sales-report-' + tab + '-' + from + '-' + to + '.' + ext;
  }

  // ── Bind click handlers on ALL export buttons ──
  document.querySelectorAll('.emsales-export-btn').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      e.preventDefault();

      // Resolve tab: button's own data-tab OR current active tab from dashboard state
      var tab = this.getAttribute('data-tab') ||
                (typeof EmSalesDashboard !== 'undefined' && EmSalesDashboard.state.activeTab) ||
                'products';

      var format = this.getAttribute('data-format') || 'csv';

      var url = buildExportUrl(tab, format);
      triggerDownload(url, buildFilename(tab, format));
    });
  });
});

