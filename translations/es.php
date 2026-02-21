<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

// Traducciones español — Sales Report Pro (emsalesreport)

global $_MODULE;
$_MODULE = array_merge(isset($_MODULE) ? $_MODULE : [], [

    // ── Módulo principal ──────────────────────────────────────────────────
    '<{emsalesreport}emsalesreport>sales_report_pro'                     => 'Sales Report Pro',
    '<{emsalesreport}emsalesreport>dashboard_ventas_avanzado'            => 'Dashboard de ventas avanzado con KPIs, gráficos, informes por producto/categoría/cliente y exportación. Compatible PS 8 y 9.',
    '<{emsalesreport}emsalesreport>estas_seguro'                         => '¿Estás seguro? Se eliminará la configuración del módulo.',
    '<{emsalesreport}emsalesreport>configuracion_guardada'               => 'Configuración guardada correctamente.',
    '<{emsalesreport}emsalesreport>configuracion_sales_report'           => 'Configuración de Sales Report Pro',
    '<{emsalesreport}emsalesreport>periodo_por_defecto'                  => 'Período por defecto',
    '<{emsalesreport}emsalesreport>periodo_descripcion'                  => 'Período que se muestra al abrir el dashboard.',
    '<{emsalesreport}emsalesreport>ultimos_7_dias'                       => 'Últimos 7 días',
    '<{emsalesreport}emsalesreport>ultimos_14_dias'                      => 'Últimos 14 días',
    '<{emsalesreport}emsalesreport>ultimos_30_dias'                      => 'Últimos 30 días',
    '<{emsalesreport}emsalesreport>ultimos_90_dias'                      => 'Últimos 90 días',
    '<{emsalesreport}emsalesreport>ultimo_ano'                           => 'Último año',
    '<{emsalesreport}emsalesreport>estados_pedido_validos'               => 'Estados de pedido válidos (IDs separados por coma)',
    '<{emsalesreport}emsalesreport>estados_pedido_desc'                  => 'IDs de estados que se consideran "venta válida". Dejar vacío para usar los estados marcados como "pagado" en PrestaShop. Ejemplo: 2,3,4,5',
    '<{emsalesreport}emsalesreport>mostrar_precios_iva'                  => 'Mostrar precios con IVA',
    '<{emsalesreport}emsalesreport>precios_iva_desc'                     => 'Si está activo, los importes incluyen impuestos.',
    '<{emsalesreport}emsalesreport>si'                                   => 'Sí',
    '<{emsalesreport}emsalesreport>no'                                   => 'No',
    '<{emsalesreport}emsalesreport>elementos_por_pagina'                 => 'Elementos por página',
    '<{emsalesreport}emsalesreport>tipo_grafico_defecto'                 => 'Tipo de gráfico por defecto',
    '<{emsalesreport}emsalesreport>linea'                                => 'Línea',
    '<{emsalesreport}emsalesreport>barras'                               => 'Barras',
    '<{emsalesreport}emsalesreport>habilitar_comparativa'                => 'Habilitar comparativa con período anterior',
    '<{emsalesreport}emsalesreport>guardar'                              => 'Guardar',

    // ── Filtros ───────────────────────────────────────────────────────────
    '<{emsalesreport}admin_dashboard>filtros'                            => 'Filtros',
    '<{emsalesreport}admin_dashboard>desde'                              => 'Desde',
    '<{emsalesreport}admin_dashboard>hasta'                              => 'Hasta',
    '<{emsalesreport}admin_dashboard>periodo_rapido'                     => 'Período rápido',
    '<{emsalesreport}admin_dashboard>7_dias'                             => '7 días',
    '<{emsalesreport}admin_dashboard>14_dias'                            => '14 días',
    '<{emsalesreport}admin_dashboard>30_dias'                            => '30 días',
    '<{emsalesreport}admin_dashboard>90_dias'                            => '90 días',
    '<{emsalesreport}admin_dashboard>1_ano'                              => '1 año',
    '<{emsalesreport}admin_dashboard>este_mes'                           => 'Este mes',
    '<{emsalesreport}admin_dashboard>este_ano'                           => 'Este año',
    '<{emsalesreport}admin_dashboard>aplicar'                            => 'Aplicar',

    // ── KPIs ──────────────────────────────────────────────────────────────
    '<{emsalesreport}admin_dashboard>ingresos'                           => 'Ingresos',
    '<{emsalesreport}admin_dashboard>pedidos'                            => 'Pedidos',
    '<{emsalesreport}admin_dashboard>ticket_medio'                       => 'Ticket medio',
    '<{emsalesreport}admin_dashboard>margen_bruto'                       => 'Margen bruto',
    '<{emsalesreport}admin_dashboard>margen'                             => 'Margen',
    '<{emsalesreport}admin_dashboard>margen_pct'                         => 'Margen %',
    '<{emsalesreport}admin_dashboard>clientes_unicos'                    => 'Clientes únicos',
    '<{emsalesreport}admin_dashboard>prod_pedido'                        => 'Prod./pedido',

    // ── Gráfico ───────────────────────────────────────────────────────────
    '<{emsalesreport}admin_dashboard>evolucion_ventas'                   => 'Evolución de ventas',
    '<{emsalesreport}admin_dashboard>dia'                                => 'Día',
    '<{emsalesreport}admin_dashboard>semana'                             => 'Semana',
    '<{emsalesreport}admin_dashboard>mes'                                => 'Mes',

    // ── Tabs ──────────────────────────────────────────────────────────────
    '<{emsalesreport}admin_dashboard>productos'                          => 'Productos',
    '<{emsalesreport}admin_dashboard>categorias'                         => 'Categorías',
    '<{emsalesreport}admin_dashboard>clientes'                           => 'Clientes',
    '<{emsalesreport}admin_dashboard>proveedores'                        => 'Proveedores',

    // ── Tab Productos ─────────────────────────────────────────────────────
    '<{emsalesreport}tab_products>todas_las_categorias'                  => 'Todas las categorías',
    '<{emsalesreport}tab_products>total'                                 => 'Total:',
    '<{emsalesreport}tab_products>producto'                              => 'Producto',
    '<{emsalesreport}tab_products>ref'                                   => 'Ref.',
    '<{emsalesreport}tab_products>uds'                                   => 'Uds.',
    '<{emsalesreport}tab_products>ingresos'                              => 'Ingresos',
    '<{emsalesreport}tab_products>coste'                                 => 'Coste',
    '<{emsalesreport}tab_products>margen'                                => 'Margen',
    '<{emsalesreport}tab_products>margen_pct'                            => 'Margen %',
    '<{emsalesreport}tab_products>stock'                                 => 'Stock',
    '<{emsalesreport}tab_products>sin_datos'                             => 'No hay datos para el período seleccionado.',

    // ── Tab Categorías ────────────────────────────────────────────────────
    '<{emsalesreport}tab_categories>total_categorias'                    => 'Total categorías:',
    '<{emsalesreport}tab_categories>categoria'                           => 'Categoría',
    '<{emsalesreport}tab_categories>productos'                           => 'Productos',
    '<{emsalesreport}tab_categories>uds'                                 => 'Uds.',
    '<{emsalesreport}tab_categories>ingresos'                            => 'Ingresos',
    '<{emsalesreport}tab_categories>coste'                               => 'Coste',
    '<{emsalesreport}tab_categories>margen'                              => 'Margen',
    '<{emsalesreport}tab_categories>margen_pct'                          => 'Margen %',
    '<{emsalesreport}tab_categories>distribucion_ingresos'               => 'Distribución de ingresos',
    '<{emsalesreport}tab_categories>sin_datos_chart'                     => 'Sin datos',
    '<{emsalesreport}tab_categories>sin_datos'                           => 'No hay datos para el período seleccionado.',

    // ── Tab Clientes ──────────────────────────────────────────────────────
    '<{emsalesreport}tab_customers>total_clientes'                       => 'Total clientes:',
    '<{emsalesreport}tab_customers>cliente'                              => 'Cliente',
    '<{emsalesreport}tab_customers>cliente_vip'                          => 'Cliente VIP',
    '<{emsalesreport}tab_customers>email'                                => 'Email',
    '<{emsalesreport}tab_customers>empresa'                              => 'Empresa',
    '<{emsalesreport}tab_customers>pedidos'                              => 'Pedidos',
    '<{emsalesreport}tab_customers>total'                                => 'Total',
    '<{emsalesreport}tab_customers>ticket_medio'                         => 'Ticket Medio',
    '<{emsalesreport}tab_customers>ultimo_pedido'                        => 'Último Pedido',
    '<{emsalesreport}tab_customers>sin_datos'                            => 'No hay datos para el período seleccionado.',

    // ── Tab Proveedores ───────────────────────────────────────────────────
    '<{emsalesreport}tab_suppliers>total_proveedores'                    => 'Total proveedores:',
    '<{emsalesreport}tab_suppliers>proveedor'                            => 'Proveedor',
    '<{emsalesreport}tab_suppliers>productos'                            => 'Productos',
    '<{emsalesreport}tab_suppliers>uds_vendidas'                         => 'Uds. Vendidas',
    '<{emsalesreport}tab_suppliers>ingresos'                             => 'Ingresos',
    '<{emsalesreport}tab_suppliers>coste'                                => 'Coste',
    '<{emsalesreport}tab_suppliers>margen'                               => 'Margen',
    '<{emsalesreport}tab_suppliers>margen_pct'                           => 'Margen %',
    '<{emsalesreport}tab_suppliers>fabricante'                           => 'Fabricante',
    '<{emsalesreport}tab_suppliers>por_fabricante'                       => 'Por Fabricante',
    '<{emsalesreport}tab_suppliers>sin_datos'                            => 'No hay datos para el período seleccionado.',
]);

global $_MODULE;
$_MODULE = [];

// emsalesreport.php
$_MODULE['<{emsalesreport}prestashop>emsalesreport_'] = [];
$_MODULE['<{emsalesreport}prestashop>emsalesreport_dc2fa9d6e236ddb23024e89ae1af3f06'] = 'Sales Report Pro';
$_MODULE['<{emsalesreport}prestashop>emsalesreport_6aa5a4e3b5d2cde08f0beab9ba89b4f6'] = 'Dashboard de ventas avanzado con KPIs, gráficos, informes por producto/categoría/cliente y exportación. Compatible PS 8 y 9.';
$_MODULE['<{emsalesreport}prestashop>emsalesreport_configuracion_guardada'] = 'Configuración guardada correctamente.';
$_MODULE['<{emsalesreport}prestashop>emsalesreport_confirm_uninstall'] = '¿Estás seguro? Se eliminará la configuración del módulo.';
$_MODULE['<{emsalesreport}prestashop>emsalesreport_dashboard_en_construccion'] = 'Dashboard en construcción. El módulo se ha instalado correctamente.';
$_MODULE['<{emsalesreport}prestashop>emsalesreport_periodo_defecto'] = 'Período por defecto';
$_MODULE['<{emsalesreport}prestashop>emsalesreport_7_dias'] = 'Últimos 7 días';
$_MODULE['<{emsalesreport}prestashop>emsalesreport_14_dias'] = 'Últimos 14 días';
$_MODULE['<{emsalesreport}prestashop>emsalesreport_30_dias'] = 'Últimos 30 días';
$_MODULE['<{emsalesreport}prestashop>emsalesreport_90_dias'] = 'Últimos 90 días';
$_MODULE['<{emsalesreport}prestashop>emsalesreport_1_anio'] = 'Último año';
$_MODULE['<{emsalesreport}prestashop>emsalesreport_estados_validos'] = 'Estados de pedido válidos (IDs separados por coma)';
$_MODULE['<{emsalesreport}prestashop>emsalesreport_precios_con_iva'] = 'Mostrar precios con IVA';
$_MODULE['<{emsalesreport}prestashop>emsalesreport_elementos_pagina'] = 'Elementos por página';
$_MODULE['<{emsalesreport}prestashop>emsalesreport_tipo_grafico'] = 'Tipo de gráfico por defecto';
$_MODULE['<{emsalesreport}prestashop>emsalesreport_grafico_linea'] = 'Línea';
$_MODULE['<{emsalesreport}prestashop>emsalesreport_grafico_barras'] = 'Barras';
$_MODULE['<{emsalesreport}prestashop>emsalesreport_comparativa'] = 'Habilitar comparativa con período anterior';
$_MODULE['<{emsalesreport}prestashop>emsalesreport_guardar'] = 'Guardar';
