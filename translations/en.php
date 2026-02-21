<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

// English translations — Sales Report Pro (emsalesreport)

global $_MODULE;
$_MODULE = array_merge(isset($_MODULE) ? $_MODULE : [], [

    // ── Main module ───────────────────────────────────────────────────────
    '<{emsalesreport}emsalesreport>sales_report_pro'                     => 'Sales Report Pro',
    '<{emsalesreport}emsalesreport>dashboard_ventas_avanzado'            => 'Advanced sales dashboard with KPIs, charts, product/category/customer reports and export. Compatible with PS 8 and 9.',
    '<{emsalesreport}emsalesreport>estas_seguro'                         => 'Are you sure? The module configuration will be deleted.',
    '<{emsalesreport}emsalesreport>configuracion_guardada'               => 'Settings saved successfully.',
    '<{emsalesreport}emsalesreport>configuracion_sales_report'           => 'Sales Report Pro Settings',
    '<{emsalesreport}emsalesreport>periodo_por_defecto'                  => 'Default period',
    '<{emsalesreport}emsalesreport>periodo_descripcion'                  => 'Period shown when opening the dashboard.',
    '<{emsalesreport}emsalesreport>ultimos_7_dias'                       => 'Last 7 days',
    '<{emsalesreport}emsalesreport>ultimos_14_dias'                      => 'Last 14 days',
    '<{emsalesreport}emsalesreport>ultimos_30_dias'                      => 'Last 30 days',
    '<{emsalesreport}emsalesreport>ultimos_90_dias'                      => 'Last 90 days',
    '<{emsalesreport}emsalesreport>ultimo_ano'                           => 'Last year',
    '<{emsalesreport}emsalesreport>estados_pedido_validos'               => 'Valid order states (comma-separated IDs)',
    '<{emsalesreport}emsalesreport>estados_pedido_desc'                  => 'IDs of states considered a "valid sale". Leave empty to use states marked as "paid" in PrestaShop. Example: 2,3,4,5',
    '<{emsalesreport}emsalesreport>mostrar_precios_iva'                  => 'Show prices with VAT',
    '<{emsalesreport}emsalesreport>precios_iva_desc'                     => 'When enabled, amounts include taxes.',
    '<{emsalesreport}emsalesreport>si'                                   => 'Yes',
    '<{emsalesreport}emsalesreport>no'                                   => 'No',
    '<{emsalesreport}emsalesreport>elementos_por_pagina'                 => 'Items per page',
    '<{emsalesreport}emsalesreport>tipo_grafico_defecto'                 => 'Default chart type',
    '<{emsalesreport}emsalesreport>linea'                                => 'Line',
    '<{emsalesreport}emsalesreport>barras'                               => 'Bar',
    '<{emsalesreport}emsalesreport>habilitar_comparativa'                => 'Enable comparison with previous period',
    '<{emsalesreport}emsalesreport>guardar'                              => 'Save',

    // ── Filters ───────────────────────────────────────────────────────────
    '<{emsalesreport}admin_dashboard>filtros'                            => 'Filters',
    '<{emsalesreport}admin_dashboard>desde'                              => 'From',
    '<{emsalesreport}admin_dashboard>hasta'                              => 'To',
    '<{emsalesreport}admin_dashboard>periodo_rapido'                     => 'Quick range',
    '<{emsalesreport}admin_dashboard>7_dias'                             => '7 days',
    '<{emsalesreport}admin_dashboard>14_dias'                            => '14 days',
    '<{emsalesreport}admin_dashboard>30_dias'                            => '30 days',
    '<{emsalesreport}admin_dashboard>90_dias'                            => '90 days',
    '<{emsalesreport}admin_dashboard>1_ano'                              => '1 year',
    '<{emsalesreport}admin_dashboard>este_mes'                           => 'This month',
    '<{emsalesreport}admin_dashboard>este_ano'                           => 'This year',
    '<{emsalesreport}admin_dashboard>aplicar'                            => 'Apply',

    // ── KPIs ──────────────────────────────────────────────────────────────
    '<{emsalesreport}admin_dashboard>ingresos'                           => 'Revenue',
    '<{emsalesreport}admin_dashboard>pedidos'                            => 'Orders',
    '<{emsalesreport}admin_dashboard>ticket_medio'                       => 'Avg. ticket',
    '<{emsalesreport}admin_dashboard>margen_bruto'                       => 'Gross margin',
    '<{emsalesreport}admin_dashboard>margen'                             => 'Margin',
    '<{emsalesreport}admin_dashboard>margen_pct'                         => 'Margin %',
    '<{emsalesreport}admin_dashboard>clientes_unicos'                    => 'Unique customers',
    '<{emsalesreport}admin_dashboard>prod_pedido'                        => 'Items/order',

    // ── Chart ─────────────────────────────────────────────────────────────
    '<{emsalesreport}admin_dashboard>evolucion_ventas'                   => 'Sales trend',
    '<{emsalesreport}admin_dashboard>dia'                                => 'Day',
    '<{emsalesreport}admin_dashboard>semana'                             => 'Week',
    '<{emsalesreport}admin_dashboard>mes'                                => 'Month',

    // ── Tabs ──────────────────────────────────────────────────────────────
    '<{emsalesreport}admin_dashboard>productos'                          => 'Products',
    '<{emsalesreport}admin_dashboard>categorias'                         => 'Categories',
    '<{emsalesreport}admin_dashboard>clientes'                           => 'Customers',
    '<{emsalesreport}admin_dashboard>proveedores'                        => 'Suppliers',

    // ── Tab Products ──────────────────────────────────────────────────────
    '<{emsalesreport}tab_products>todas_las_categorias'                  => 'All categories',
    '<{emsalesreport}tab_products>total'                                 => 'Total:',
    '<{emsalesreport}tab_products>producto'                              => 'Product',
    '<{emsalesreport}tab_products>ref'                                   => 'Ref.',
    '<{emsalesreport}tab_products>uds'                                   => 'Qty.',
    '<{emsalesreport}tab_products>ingresos'                              => 'Revenue',
    '<{emsalesreport}tab_products>coste'                                 => 'Cost',
    '<{emsalesreport}tab_products>margen'                                => 'Margin',
    '<{emsalesreport}tab_products>margen_pct'                            => 'Margin %',
    '<{emsalesreport}tab_products>stock'                                 => 'Stock',
    '<{emsalesreport}tab_products>sin_datos'                             => 'No data for the selected period.',

    // ── Tab Categories ────────────────────────────────────────────────────
    '<{emsalesreport}tab_categories>total_categorias'                    => 'Total categories:',
    '<{emsalesreport}tab_categories>categoria'                           => 'Category',
    '<{emsalesreport}tab_categories>productos'                           => 'Products',
    '<{emsalesreport}tab_categories>uds'                                 => 'Qty.',
    '<{emsalesreport}tab_categories>ingresos'                            => 'Revenue',
    '<{emsalesreport}tab_categories>coste'                               => 'Cost',
    '<{emsalesreport}tab_categories>margen'                              => 'Margin',
    '<{emsalesreport}tab_categories>margen_pct'                          => 'Margin %',
    '<{emsalesreport}tab_categories>distribucion_ingresos'               => 'Revenue distribution',
    '<{emsalesreport}tab_categories>sin_datos_chart'                     => 'No data',
    '<{emsalesreport}tab_categories>sin_datos'                           => 'No data for the selected period.',

    // ── Tab Customers ─────────────────────────────────────────────────────
    '<{emsalesreport}tab_customers>total_clientes'                       => 'Total customers:',
    '<{emsalesreport}tab_customers>cliente'                              => 'Customer',
    '<{emsalesreport}tab_customers>cliente_vip'                          => 'VIP Customer',
    '<{emsalesreport}tab_customers>email'                                => 'Email',
    '<{emsalesreport}tab_customers>empresa'                              => 'Company',
    '<{emsalesreport}tab_customers>pedidos'                              => 'Orders',
    '<{emsalesreport}tab_customers>total'                                => 'Total',
    '<{emsalesreport}tab_customers>ticket_medio'                         => 'Avg. Ticket',
    '<{emsalesreport}tab_customers>ultimo_pedido'                        => 'Last Order',
    '<{emsalesreport}tab_customers>sin_datos'                            => 'No data for the selected period.',

    // ── Tab Suppliers ─────────────────────────────────────────────────────
    '<{emsalesreport}tab_suppliers>total_proveedores'                    => 'Total suppliers:',
    '<{emsalesreport}tab_suppliers>proveedor'                            => 'Supplier',
    '<{emsalesreport}tab_suppliers>productos'                            => 'Products',
    '<{emsalesreport}tab_suppliers>uds_vendidas'                         => 'Units sold',
    '<{emsalesreport}tab_suppliers>ingresos'                             => 'Revenue',
    '<{emsalesreport}tab_suppliers>coste'                                => 'Cost',
    '<{emsalesreport}tab_suppliers>margen'                               => 'Margin',
    '<{emsalesreport}tab_suppliers>margen_pct'                           => 'Margin %',
    '<{emsalesreport}tab_suppliers>fabricante'                           => 'Manufacturer',
    '<{emsalesreport}tab_suppliers>por_fabricante'                       => 'By Manufacturer',
    '<{emsalesreport}tab_suppliers>sin_datos'                            => 'No data for the selected period.',
]);

global $_MODULE;
$_MODULE = [];

// emsalesreport.php - English translations
$_MODULE['<{emsalesreport}prestashop>emsalesreport_dc2fa9d6e236ddb23024e89ae1af3f06'] = 'Sales Report Pro';
$_MODULE['<{emsalesreport}prestashop>emsalesreport_6aa5a4e3b5d2cde08f0beab9ba89b4f6'] = 'Advanced sales dashboard with KPIs, charts, reports by product/category/customer and export. Compatible PS 8 and 9.';
$_MODULE['<{emsalesreport}prestashop>emsalesreport_configuracion_guardada'] = 'Configuration saved successfully.';
$_MODULE['<{emsalesreport}prestashop>emsalesreport_confirm_uninstall'] = 'Are you sure? The module configuration will be deleted.';
$_MODULE['<{emsalesreport}prestashop>emsalesreport_dashboard_en_construccion'] = 'Dashboard under construction. The module has been installed successfully.';
$_MODULE['<{emsalesreport}prestashop>emsalesreport_periodo_defecto'] = 'Default period';
$_MODULE['<{emsalesreport}prestashop>emsalesreport_7_dias'] = 'Last 7 days';
$_MODULE['<{emsalesreport}prestashop>emsalesreport_14_dias'] = 'Last 14 days';
$_MODULE['<{emsalesreport}prestashop>emsalesreport_30_dias'] = 'Last 30 days';
$_MODULE['<{emsalesreport}prestashop>emsalesreport_90_dias'] = 'Last 90 days';
$_MODULE['<{emsalesreport}prestashop>emsalesreport_1_anio'] = 'Last year';
$_MODULE['<{emsalesreport}prestashop>emsalesreport_estados_validos'] = 'Valid order states (IDs separated by comma)';
$_MODULE['<{emsalesreport}prestashop>emsalesreport_precios_con_iva'] = 'Show prices with tax';
$_MODULE['<{emsalesreport}prestashop>emsalesreport_elementos_pagina'] = 'Items per page';
$_MODULE['<{emsalesreport}prestashop>emsalesreport_tipo_grafico'] = 'Default chart type';
$_MODULE['<{emsalesreport}prestashop>emsalesreport_grafico_linea'] = 'Line';
$_MODULE['<{emsalesreport}prestashop>emsalesreport_grafico_barras'] = 'Bar';
$_MODULE['<{emsalesreport}prestashop>emsalesreport_comparativa'] = 'Enable comparison with previous period';
$_MODULE['<{emsalesreport}prestashop>emsalesreport_guardar'] = 'Save';
