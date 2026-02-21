<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'emsalesreport/classes/EmSalesReportQuery.php';
require_once _PS_MODULE_DIR_ . 'emsalesreport/classes/EmSalesReportKPI.php';
require_once _PS_MODULE_DIR_ . 'emsalesreport/classes/EmSalesReportExport.php';

class AdminEmSalesReportController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->display   = 'view';
        // No $this->table ni $this->className — no es un CRUD
        parent::__construct();

        $this->meta_title = 'Sales Report Pro';
    }

    public function initContent()
    {
        // Petición de exportación — enviar archivo y terminar
        if (Tools::getValue('export')) {
            $this->runSalesExport();
            die();
        }

        // Petición AJAX — responder JSON y terminar
        if (Tools::getValue('ajax') == '1') {
            $this->runSalesAjax();
            die();
        }

        parent::initContent();

        $this->content = $this->renderView();
        $this->context->smarty->assign('content', $this->content);
    }

    // ===========================================================
    // RENDER VIEW PRINCIPAL
    // ===========================================================

    public function renderView()
    {
        $defaultDays = (int) Configuration::get('EMSALES_DEFAULT_PERIOD') ?: 30;
        $dateFrom    = Tools::getValue('date_from', date('Y-m-d', strtotime('-' . $defaultDays . ' days')));
        $dateTo      = Tools::getValue('date_to', date('Y-m-d'));
        $activeTab   = Tools::getValue('tab', 'products');

        // Sanitizar entradas
        $dateFrom  = $this->sanitizeDate($dateFrom);
        $dateTo    = $this->sanitizeDate($dateTo);
        $activeTab = in_array($activeTab, ['products', 'categories', 'customers', 'suppliers'], true)
            ? $activeTab : 'products';

        $query = $this->buildQuery();

        // KPIs período actual
        $kpis = $query->getKPIs($dateFrom, $dateTo);

        // KPIs período anterior + variaciones
        $kpisPrevious = [];
        $variations   = [];
        if ((bool) Configuration::get('EMSALES_COMPARE_ENABLED')) {
            $prev         = EmSalesReportKPI::getPreviousPeriod($dateFrom, $dateTo);
            $kpisPrevious = $query->getKPIs($prev['from'], $prev['to']);

            $compareFields = [
                'total_revenue', 'total_orders', 'avg_ticket', 'gross_margin',
                'margin_pct', 'unique_customers', 'total_products_sold',
            ];
            foreach ($compareFields as $field) {
                $variations[$field] = EmSalesReportKPI::calculateVariation(
                    (float) ($kpis[$field] ?? 0),
                    (float) ($kpisPrevious[$field] ?? 0)
                );
            }
        }

        // Serie temporal para el gráfico
        $granularity = EmSalesReportKPI::getOptimalGranularity($dateFrom, $dateTo);
        $timeSeries  = $query->getTimeSeries($dateFrom, $dateTo, $granularity);

        // Datos de la pestaña activa
        $itemsPerPage   = (int) Configuration::get('EMSALES_ITEMS_PER_PAGE') ?: 50;
        $productsData   = ['items' => [], 'total' => 0];
        $categoriesData = [];
        $customersData  = ['items' => [], 'total' => 0];
        $suppliersData  = [];

        if ($activeTab === 'products') {
            $r = $query->getProductsReport($dateFrom, $dateTo, 'revenue', 'DESC', $itemsPerPage, 0);
            $productsData = ['items' => $r['data'], 'total' => $r['total']];
        } elseif ($activeTab === 'categories') {
            $categoriesData = $query->getCategoriesReport($dateFrom, $dateTo);
        } elseif ($activeTab === 'customers') {
            $r = $query->getCustomersReport($dateFrom, $dateTo, 'total_spent', 'DESC', $itemsPerPage, 0);
            $customersData = ['items' => $r['data'], 'total' => $r['total']];
        } elseif ($activeTab === 'suppliers') {
            $suppliersData = $query->getSuppliersReport($dateFrom, $dateTo);
        }

        // Datos auxiliares
        $paymentMethods = $query->getTopPaymentMethods($dateFrom, $dateTo);
        $topCountries   = $query->getTopCountries($dateFrom, $dateTo, 10);
        $categoriesList = $query->getCategoriesList();

        $adminLink = $this->context->link->getAdminLink('AdminEmSalesReport');

        $this->context->smarty->assign([
            'kpis'            => $kpis,
            'kpis_previous'   => $kpisPrevious,
            'variations'      => $variations,
            'time_series'     => json_encode($timeSeries),
            'granularity'     => $granularity,
            'products_data'   => $productsData,
            'categories_data' => $categoriesData,
            'customers_data'  => $customersData,
            'suppliers_data'  => $suppliersData,
            'categories_list' => $categoriesList,
            'date_from'       => $dateFrom,
            'date_to'         => $dateTo,
            'active_tab'      => $activeTab,
            'chart_type'      => Configuration::get('EMSALES_CHART_TYPE') ?: 'line',
            'compare_enabled' => (bool) Configuration::get('EMSALES_COMPARE_ENABLED'),
            'currency_sign'   => $this->context->currency->sign,
            'currency_iso'    => $this->context->currency->iso_code,
            'items_per_page'  => $itemsPerPage,
            'module_url'      => $adminLink,
            'export_url'      => $adminLink . '&export=1',
            'ajax_url'        => $adminLink . '&ajax=1',
            'payment_methods' => $paymentMethods,
            'top_countries'   => $topCountries,
        ]);

        return $this->module->display(
            _PS_MODULE_DIR_ . 'emsalesreport/emsalesreport.php',
            'views/templates/admin/dashboard.tpl'
        );
    }

    // ===========================================================
    // AJAX
    // ===========================================================

    protected function runSalesAjax(): void
    {
        $action   = pSQL(Tools::getValue('action', ''));
        $dateFrom = $this->sanitizeDate(Tools::getValue('date_from', date('Y-m-d', strtotime('-30 days'))));
        $dateTo   = $this->sanitizeDate(Tools::getValue('date_to', date('Y-m-d')));
        $query    = $this->buildQuery();
        $response = ['success' => false, 'error' => ''];

        header('Content-Type: application/json; charset=utf-8');

        try {
            switch ($action) {

                // ── Una sola llamada que reemplaza refresh_kpis + refresh_chart + refresh_table ──
                case 'refresh_all':
                    $tab         = pSQL(Tools::getValue('tab', 'products'));
                    $tab         = in_array($tab, ['products', 'categories', 'customers', 'suppliers'], true)
                                   ? $tab : 'products';
                    $granRaw     = pSQL(Tools::getValue('granularity', 'auto'));
                    $granularity = ($granRaw === 'auto')
                                   ? EmSalesReportKPI::getOptimalGranularity($dateFrom, $dateTo)
                                   : $granRaw;
                    $granularity = in_array($granularity, ['day', 'week', 'month'], true) ? $granularity : 'day';

                    // KPIs
                    $kpis       = $query->getKPIs($dateFrom, $dateTo);
                    $variations = [];
                    if ((bool) Configuration::get('EMSALES_COMPARE_ENABLED')) {
                        $prev     = EmSalesReportKPI::getPreviousPeriod($dateFrom, $dateTo);
                        $kpisPrev = $query->getKPIs($prev['from'], $prev['to']);
                        foreach (['total_revenue','total_orders','avg_ticket','gross_margin',
                                  'margin_pct','unique_customers','total_products_sold'] as $f) {
                            $variations[$f] = EmSalesReportKPI::calculateVariation(
                                (float) ($kpis[$f] ?? 0),
                                (float) ($kpisPrev[$f] ?? 0)
                            );
                        }
                    }

                    // Serie temporal
                    $timeSeries = $query->getTimeSeries($dateFrom, $dateTo, $granularity);

                    // Tabla activa
                    $orderBy  = pSQL(Tools::getValue('order_by', 'revenue'));
                    $orderDir = strtoupper(Tools::getValue('order_dir', 'DESC')) === 'ASC' ? 'ASC' : 'DESC';
                    $page     = max(1, (int) Tools::getValue('page', 1));
                    $limit    = (int) Configuration::get('EMSALES_ITEMS_PER_PAGE') ?: 50;
                    $offset   = ($page - 1) * $limit;
                    $catId    = (int) Tools::getValue('category_id', 0);

                    $items = [];
                    $total = 0;
                    if ($tab === 'products') {
                        $r     = $query->getProductsReport($dateFrom, $dateTo, $orderBy, $orderDir, $limit, $offset, $catId);
                        $items = $r['data'];
                        $total = $r['total'];
                    } elseif ($tab === 'categories') {
                        $items = $query->getCategoriesReport($dateFrom, $dateTo, $orderBy, $orderDir);
                    } elseif ($tab === 'customers') {
                        $r     = $query->getCustomersReport($dateFrom, $dateTo, $orderBy, $orderDir, $limit, $offset);
                        $items = $r['data'];
                        $total = $r['total'];
                    } elseif ($tab === 'suppliers') {
                        $items = $query->getSuppliersReport($dateFrom, $dateTo, $orderBy, $orderDir);
                    }

                    $response = [
                        'success'     => true,
                        'kpis'        => $kpis,
                        'variations'  => $variations,
                        'timeSeries'  => $timeSeries,
                        'granularity' => $granularity,
                        'tab'         => $tab,
                        'items'       => $items,
                        'total'       => $total,
                        // Debug siempre visible — quitar en producción
                        '_debug'      => [
                            'states'   => $this->module->getValidOrderStateIds(),
                            'kpis'     => $kpis,
                            'dateFrom' => $dateFrom,
                            'dateTo'   => $dateTo,
                            'sqlError' => $query->getLastError(),
                        ],
                    ];
                    break;

                // ── Llamadas individuales (usadas al cambiar solo gráfico o solo tabla) ──
                case 'refresh_kpis':
                    $kpis       = $query->getKPIs($dateFrom, $dateTo);
                    $variations = [];
                    if ((bool) Configuration::get('EMSALES_COMPARE_ENABLED')) {
                        $prev     = EmSalesReportKPI::getPreviousPeriod($dateFrom, $dateTo);
                        $kpisPrev = $query->getKPIs($prev['from'], $prev['to']);
                        foreach (['total_revenue','total_orders','avg_ticket','gross_margin',
                                  'margin_pct','unique_customers','total_products_sold'] as $f) {
                            $variations[$f] = EmSalesReportKPI::calculateVariation(
                                (float) ($kpis[$f] ?? 0),
                                (float) ($kpisPrev[$f] ?? 0)
                            );
                        }
                    }
                    $response = ['success' => true, 'kpis' => $kpis, 'variations' => $variations];
                    break;

                case 'refresh_chart':
                    $granRaw     = pSQL(Tools::getValue('granularity', 'auto'));
                    $granularity = ($granRaw === 'auto')
                                   ? EmSalesReportKPI::getOptimalGranularity($dateFrom, $dateTo)
                                   : $granRaw;
                    $granularity = in_array($granularity, ['day', 'week', 'month'], true) ? $granularity : 'day';
                    $timeSeries  = $query->getTimeSeries($dateFrom, $dateTo, $granularity);
                    $response    = ['success' => true, 'timeSeries' => $timeSeries, 'granularity' => $granularity];
                    break;

                case 'refresh_table':
                    $tab      = pSQL(Tools::getValue('tab', 'products'));
                    $tab      = in_array($tab, ['products', 'categories', 'customers', 'suppliers'], true)
                                ? $tab : 'products';
                    $orderBy  = pSQL(Tools::getValue('order_by', 'revenue'));
                    $orderDir = strtoupper(Tools::getValue('order_dir', 'DESC')) === 'ASC' ? 'ASC' : 'DESC';
                    $page     = max(1, (int) Tools::getValue('page', 1));
                    $limit    = (int) Configuration::get('EMSALES_ITEMS_PER_PAGE') ?: 50;
                    $offset   = ($page - 1) * $limit;
                    $catId    = (int) Tools::getValue('category_id', 0);

                    $items = [];
                    $total = 0;
                    if ($tab === 'products') {
                        $r     = $query->getProductsReport($dateFrom, $dateTo, $orderBy, $orderDir, $limit, $offset, $catId);
                        $items = $r['data'];
                        $total = $r['total'];
                    } elseif ($tab === 'categories') {
                        $items = $query->getCategoriesReport($dateFrom, $dateTo, $orderBy, $orderDir);
                    } elseif ($tab === 'customers') {
                        $r     = $query->getCustomersReport($dateFrom, $dateTo, $orderBy, $orderDir, $limit, $offset);
                        $items = $r['data'];
                        $total = $r['total'];
                    } elseif ($tab === 'suppliers') {
                        $items = $query->getSuppliersReport($dateFrom, $dateTo, $orderBy, $orderDir);
                    }
                    $response = ['success' => true, 'tab' => $tab, 'items' => $items, 'total' => $total];
                    $lastErr = $query->getLastError();
                    if ($lastErr !== '') {
                        $response['_debug'] = ['sqlError' => $lastErr, 'states' => $this->module->getValidOrderStateIds()];
                    }
                    break;

                case 'get_product_detail':
                    $productId = (int) Tools::getValue('product_id', 0);
                    if ($productId > 0) {
                        $gran    = EmSalesReportKPI::getOptimalGranularity($dateFrom, $dateTo);
                        $series  = $query->getTimeSeries($dateFrom, $dateTo, $gran);
                        $response = ['success' => true, 'timeSeries' => $series, 'product_id' => $productId];
                    } else {
                        $response = ['success' => false, 'error' => 'Invalid product_id'];
                    }
                    break;

                default:
                    $response = ['success' => false, 'error' => 'Unknown action'];
            }
        } catch (Throwable $e) {
            PrestaShopLogger::addLog('AdminEmSalesReport AJAX: ' . $e->getMessage(), 3, null, 'EmSalesReport');
            $response = ['success' => false, 'error' => 'Internal error'];
        }

        echo json_encode($response);
    }

    // ===========================================================
    // EXPORTACIÓN
    // ===========================================================

    protected function runSalesExport(): void
    {
        $defaultDays = (int) Configuration::get('EMSALES_DEFAULT_PERIOD') ?: 30;
        $dateFrom    = $this->sanitizeDate(Tools::getValue('date_from', date('Y-m-d', strtotime('-' . $defaultDays . ' days'))));
        $dateTo      = $this->sanitizeDate(Tools::getValue('date_to', date('Y-m-d')));
        $tab         = pSQL(Tools::getValue('tab', 'products'));
        $tab         = in_array($tab, ['products', 'categories', 'customers', 'suppliers'], true) ? $tab : 'products';
        $format      = pSQL(Tools::getValue('format', 'csv'));
        $format      = in_array($format, ['csv', 'excel'], true) ? $format : 'csv';

        $query    = $this->buildQuery();
        $filename = 'sales_report_' . $tab;

        $columnsMaps = [
            'products' => [
                'product_name'      => 'Producto',
                'product_reference' => 'Referencia',
                'qty_sold'          => 'Uds. vendidas',
                'revenue'           => 'Ingresos',
                'cost'              => 'Coste',
                'margin'            => 'Margen',
                'margin_pct'        => 'Margen %',
                'current_stock'     => 'Stock actual',
            ],
            'categories' => [
                'category_name' => 'Categoría',
                'num_products'  => 'Productos',
                'qty_sold'      => 'Uds. vendidas',
                'revenue'       => 'Ingresos',
                'cost'          => 'Coste',
                'margin'        => 'Margen',
                'margin_pct'    => 'Margen %',
            ],
            'customers' => [
                'firstname'       => 'Nombre',
                'lastname'        => 'Apellidos',
                'email'           => 'Email',
                'company'         => 'Empresa',
                'total_orders'    => 'Pedidos',
                'total_spent'     => 'Total gastado',
                'avg_ticket'      => 'Ticket medio',
                'last_order_date' => 'Último pedido',
            ],
            'suppliers' => [
                'supplier_name' => 'Proveedor',
                'num_products'  => 'Productos',
                'qty_sold'      => 'Uds. vendidas',
                'revenue'       => 'Ingresos',
                'cost'          => 'Coste',
                'margin'        => 'Margen',
                'margin_pct'    => 'Margen %',
            ],
        ];

        $columns = $columnsMaps[$tab];
        $data    = [];

        if ($tab === 'products') {
            $r    = $query->getProductsReport($dateFrom, $dateTo, 'revenue', 'DESC', 10000, 0);
            $data = $r['data'];
        } elseif ($tab === 'categories') {
            $data = $query->getCategoriesReport($dateFrom, $dateTo);
        } elseif ($tab === 'customers') {
            $r    = $query->getCustomersReport($dateFrom, $dateTo, 'total_spent', 'DESC', 10000, 0);
            $data = $r['data'];
        } elseif ($tab === 'suppliers') {
            $data = $query->getSuppliersReport($dateFrom, $dateTo);
        }

        if ($format === 'excel') {
            EmSalesReportExport::exportExcel($data, $columns, $filename);
        } else {
            EmSalesReportExport::exportCSV($data, $columns, $filename);
        }
    }

    // ===========================================================
    // HELPERS PRIVADOS
    // ===========================================================

    private function buildQuery(): EmSalesReportQuery
    {
        return new EmSalesReportQuery(
            $this->module->getValidOrderStateIds(),
            $this->module->getTotalField(),
            $this->module->getDetailTotalField(),
            (int) $this->context->shop->id,
            (int) $this->context->language->id
        );
    }

    private function sanitizeDate(string $date): string
    {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return ($d && $d->format('Y-m-d') === $date) ? $date : date('Y-m-d');
    }
}
