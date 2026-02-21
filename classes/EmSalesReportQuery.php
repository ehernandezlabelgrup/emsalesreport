<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class EmSalesReportQuery
{
    /** @var array IDs de estados válidos */
    private array $validStates;

    /** @var string Campo total en ps_orders: total_paid_tax_incl o total_paid_tax_excl */
    private string $totalField;

    /** @var string Campo total en ps_order_detail: total_price_tax_incl o total_price_tax_excl */
    private string $detailTotalField;

    /** @var int ID de la tienda actual */
    private int $idShop;

    /** @var int ID del idioma activo */
    private int $idLang;

    /**
     * Expresión SQL para el coste unitario, detectada en el constructor según columnas existentes:
     *   - original_wholesale_price  (PS 8.1+)
     *   - product_wholesale_price   (PS clásico)
     *   - 0.00                      (ninguna existe → coste = 0)
     */
    private string $wholesaleCostExpr;

    /** @var string Último error SQL capturado (para debug AJAX) */
    private string $lastError = '';

    /**
     * Devuelve el último error SQL capturado por un catch interno, o cadena vacía si no hubo error.
     */
    public function getLastError(): string
    {
        return $this->lastError;
    }

    /**
     * @param array  $validStates       IDs de order states válidos
     * @param string $totalField        Campo de total en ps_orders
     * @param string $detailTotalField  Campo de total en ps_order_detail
     * @param int    $idShop            ID de tienda
     * @param int    $idLang            ID de idioma
     */
    public function __construct(
        array $validStates,
        string $totalField,
        string $detailTotalField,
        int $idShop,
        int $idLang = 1
    ) {
        $this->validStates   = $validStates;
        $this->totalField    = $totalField;
        $this->detailTotalField = $detailTotalField;
        $this->idShop        = $idShop;
        $this->idLang        = $idLang;
        $this->wholesaleCostExpr = $this->detectWholesaleExpr();
    }

    // ===========================================================
    // HELPERS PRIVADOS
    // ===========================================================

    /**
     * Genera la cláusula IN de estados válidos, ya sanitizada con intval.
     */
    private function stateIn(): string
    {
        if (empty($this->validStates)) {
            return '0'; // Nunca devolvería resultados, evita SQL inválido
        }
        return implode(',', array_map('intval', $this->validStates));
    }

    /**
     * Devuelve la expresión SQL para el precio de coste unitario.
     * Delega directamente en el valor cacheado en el constructor.
     */
    private function wholesaleExpr(): string
    {
        return $this->wholesaleCostExpr;
    }

    /**
     * Detecta qué columna de coste existe en ps_order_detail y devuelve la expresión SQL correcta.
     * Prueba en orden: original_wholesale_price → product_wholesale_price → 0.00
     */
    private function detectWholesaleExpr(): string
    {
        $table = _DB_PREFIX_ . 'order_detail';

        // Opción 1: PS 8.1+ — columna preferida
        try {
            Db::getInstance()->executeS('SELECT original_wholesale_price FROM ' . $table . ' LIMIT 1');
            return 'IFNULL(od.original_wholesale_price, 0)';
        } catch (Throwable $e) {}

        // Opción 2: PS clásico — columna heredada
        try {
            Db::getInstance()->executeS('SELECT product_wholesale_price FROM ' . $table . ' LIMIT 1');
            return 'IFNULL(od.product_wholesale_price, 0)';
        } catch (Throwable $e) {}

        // Opción 3: Ninguna columna de coste existe → coste = 0, márgen = ingresos
        return '0.00';
    }

    /**
     * Whitelist de columnas permitidas para ORDER BY (previene SQL injection).
     */
    private function sanitizeOrderBy(string $field, array $allowed, string $default): string
    {
        return in_array($field, $allowed, true) ? $field : $default;
    }

    /**
     * Sanitize ORDER direction.
     */
    private function sanitizeOrderDir(string $dir): string
    {
        return strtoupper($dir) === 'ASC' ? 'ASC' : 'DESC';
    }

    // ===========================================================
    // MÉTODO 1: getKPIs
    // ===========================================================

    /**
     * KPIs globales del período seleccionado.
     *
     * @param string $dateFrom  Y-m-d
     * @param string $dateTo    Y-m-d
     * @return array{
     *   total_revenue: float,
     *   total_orders: int,
     *   avg_ticket: float,
     *   unique_customers: int,
     *   total_products_sold: int,
     *   total_cost: float,
     *   gross_margin: float,
     *   margin_pct: float,
     *   avg_products_per_order: float
     * }
     */
    public function getKPIs(string $dateFrom, string $dateTo): array
    {
        $empty = [
            'total_revenue'          => 0.0,
            'total_orders'           => 0,
            'avg_ticket'             => 0.0,
            'unique_customers'       => 0,
            'total_products_sold'    => 0,
            'total_cost'             => 0.0,
            'gross_margin'           => 0.0,
            'margin_pct'             => 0.0,
            'avg_products_per_order' => 0.0,
        ];

        try {
            $dfrom = pSQL($dateFrom);
            $dto   = pSQL($dateTo);
            $shop  = (int) $this->idShop;
            $tf    = pSQL($this->totalField);
            $states = $this->stateIn();

            // Query principal: pedidos + líneas
            $sql = 'SELECT
                COUNT(DISTINCT o.id_order) as total_orders,
                IFNULL(SUM(o.' . $tf . '), 0) as total_revenue,
                IFNULL(AVG(o.' . $tf . '), 0) as avg_ticket,
                COUNT(DISTINCT o.id_customer) as unique_customers,
                IFNULL(SUM(od.product_quantity), 0) as total_products_sold
            FROM ' . _DB_PREFIX_ . 'orders o
            LEFT JOIN ' . _DB_PREFIX_ . 'order_detail od ON o.id_order = od.id_order
            WHERE o.current_state IN (' . $states . ')
              AND o.id_shop = ' . $shop . '
              AND o.date_add BETWEEN \'' . $dfrom . ' 00:00:00\' AND \'' . $dto . ' 23:59:59\'';

            $row = Db::getInstance()->getRow($sql);

            if (!$row) {
                return $empty;
            }

            // Query de coste por separado (puede lanzar excepción si no existe la columna)
            $wExpr = $this->wholesaleExpr();
            $costSql = 'SELECT IFNULL(SUM(' . $wExpr . ' * od.product_quantity), 0) as total_cost
            FROM ' . _DB_PREFIX_ . 'order_detail od
            INNER JOIN ' . _DB_PREFIX_ . 'orders o ON o.id_order = od.id_order
            WHERE o.current_state IN (' . $states . ')
              AND o.id_shop = ' . $shop . '
              AND o.date_add BETWEEN \'' . $dfrom . ' 00:00:00\' AND \'' . $dto . ' 23:59:59\'';

            $costRow  = Db::getInstance()->getRow($costSql);
            $totalCost = $costRow ? (float) $costRow['total_cost'] : 0.0;

            $totalRevenue  = (float) $row['total_revenue'];
            $totalOrders   = (int)   $row['total_orders'];
            $grossMargin   = $totalRevenue - $totalCost;
            $marginPct     = $totalRevenue > 0
                ? round(($grossMargin / $totalRevenue) * 100, 2)
                : 0.0;
            $avgProducts   = $totalOrders > 0
                ? round((float) $row['total_products_sold'] / $totalOrders, 1)
                : 0.0;

            return [
                'total_revenue'          => round($totalRevenue, 2),
                'total_orders'           => $totalOrders,
                'avg_ticket'             => round((float) $row['avg_ticket'], 2),
                'unique_customers'       => (int) $row['unique_customers'],
                'total_products_sold'    => (int) $row['total_products_sold'],
                'total_cost'             => round($totalCost, 2),
                'gross_margin'           => round($grossMargin, 2),
                'margin_pct'             => $marginPct,
                'avg_products_per_order' => $avgProducts,
            ];
        } catch (Throwable $e) {
            $this->lastError = 'getKPIs: ' . $e->getMessage();
            PrestaShopLogger::addLog(
                'EmSalesReportQuery::getKPIs - ' . $e->getMessage(),
                3,
                null,
                'EmSalesReport'
            );
            return $empty;
        }
    }

    // ===========================================================
    // MÉTODO 2: getTimeSeries
    // ===========================================================

    /**
     * Serie temporal de pedidos y ventas, sin huecos (días/semanas/meses con 0).
     *
     * @param string $dateFrom
     * @param string $dateTo
     * @param string $granularity  'day' | 'week' | 'month'
     * @return array<int, array{date: string, orders: int, revenue: float}>
     */
    public function getTimeSeries(string $dateFrom, string $dateTo, string $granularity = 'day'): array
    {
        $granularity = in_array($granularity, ['day', 'week', 'month'], true) ? $granularity : 'day';

        try {
            $dfrom  = pSQL($dateFrom);
            $dto    = pSQL($dateTo);
            $shop   = (int) $this->idShop;
            $tf     = pSQL($this->totalField);
            $states = $this->stateIn();

            // Construir SELECT según granularidad
            if ($granularity === 'day') {
                $selectDate  = 'DATE(o.date_add)';
                $groupBy     = 'DATE(o.date_add)';
                $periodAlias = 'period_date';
            } elseif ($granularity === 'week') {
                // Calcular siempre el LUNES exacto de la semana
                // WEEKDAY() = 0=Lun, 1=Mar, ..., 6=Dom
                $selectDate  = 'DATE_SUB(DATE(o.date_add), INTERVAL WEEKDAY(o.date_add) DAY)';
                $groupBy     = 'YEARWEEK(o.date_add, 1)';
                $periodAlias = 'period_date';
            } else {
                // month
                $selectDate  = 'DATE_FORMAT(o.date_add, \'%Y-%m-01\')';
                $groupBy     = 'DATE_FORMAT(o.date_add, \'%Y-%m\')';
                $periodAlias = 'period_date';
            }

            $sql = 'SELECT
                ' . $selectDate . ' as ' . $periodAlias . ',
                COUNT(DISTINCT o.id_order) as orders,
                IFNULL(SUM(o.' . $tf . '), 0) as revenue
            FROM ' . _DB_PREFIX_ . 'orders o
            WHERE o.current_state IN (' . $states . ')
              AND o.id_shop = ' . $shop . '
              AND o.date_add BETWEEN \'' . $dfrom . ' 00:00:00\' AND \'' . $dto . ' 23:59:59\'
            GROUP BY ' . $groupBy . '
            ORDER BY ' . $periodAlias . ' ASC';

            $sqlResults = Db::getInstance()->executeS($sql);

            // Construir mapa de resultados SQL indexado por fecha
            $resultMap = [];
            if ($sqlResults) {
                foreach ($sqlResults as $r) {
                    $resultMap[$r['period_date']] = [
                        'date'    => $r['period_date'],
                        'orders'  => (int) $r['orders'],
                        'revenue' => round((float) $r['revenue'], 2),
                    ];
                }
            }

            // Rellenar TODOS los períodos del rango con 0 donde no hay datos
            $allPeriods = [];
            $current    = new DateTime($dateFrom);
            $end        = new DateTime($dateTo);

            if ($granularity === 'day') {
                while ($current <= $end) {
                    $key = $current->format('Y-m-d');
                    $allPeriods[$key] = $resultMap[$key] ?? ['date' => $key, 'orders' => 0, 'revenue' => 0.0];
                    $current->modify('+1 day');
                }
            } elseif ($granularity === 'week') {
                // Ir al primer lunes en o antes de $dateFrom
                $current = new DateTime($dateFrom);
                $dow = (int) $current->format('N'); // 1=Mon, 7=Sun
                if ($dow > 1) {
                    $current->modify('-' . ($dow - 1) . ' days');
                }
                while ($current <= $end) {
                    $key = $current->format('Y-m-d');
                    $allPeriods[$key] = $resultMap[$key] ?? ['date' => $key, 'orders' => 0, 'revenue' => 0.0];
                    $current->modify('+7 days');
                }
            } else {
                // month: ir mes a mes
                $current = new DateTime($dateFrom . '-01'); // primer día del mes
                $current = new DateTime($dateFrom);
                $current->setDate((int)$current->format('Y'), (int)$current->format('m'), 1);
                $endMonth = clone $end;
                $endMonth->setDate((int)$endMonth->format('Y'), (int)$endMonth->format('m'), 1);

                while ($current <= $endMonth) {
                    $key = $current->format('Y-m-d'); // Y-m-01
                    $allPeriods[$key] = $resultMap[$key] ?? ['date' => $key, 'orders' => 0, 'revenue' => 0.0];
                    $current->modify('+1 month');
                }
            }

            return array_values($allPeriods);
        } catch (Throwable $e) {
            PrestaShopLogger::addLog(
                'EmSalesReportQuery::getTimeSeries - ' . $e->getMessage(),
                3,
                null,
                'EmSalesReport'
            );
            return [];
        }
    }

    // ===========================================================
    // MÉTODO 3: getProductsReport
    // ===========================================================

    /**
     * Informe por producto con stock, paginación y filtro por categoría.
     *
     * @param string $dateFrom
     * @param string $dateTo
     * @param string $orderBy     Columna de ordenación
     * @param string $orderDir    ASC | DESC
     * @param int    $limit
     * @param int    $offset
     * @param int    $categoryId  0 = todas las categorías
     * @return array{data: array, total: int}
     */
    public function getProductsReport(
        string $dateFrom,
        string $dateTo,
        string $orderBy   = 'revenue',
        string $orderDir  = 'DESC',
        int $limit        = 50,
        int $offset       = 0,
        int $categoryId   = 0
    ): array {
        $allowedOrder = ['revenue', 'qty_sold', 'cost', 'margin', 'margin_pct', 'od.product_name'];
        $orderBy  = $this->sanitizeOrderBy($orderBy, $allowedOrder, 'revenue');
        $orderDir = $this->sanitizeOrderDir($orderDir);
        $limit    = max(1, (int) $limit);
        $offset   = max(0, (int) $offset);
        $catId    = (int) $categoryId;

        try {
            $dfrom  = pSQL($dateFrom);
            $dto    = pSQL($dateTo);
            $shop   = (int) $this->idShop;
            $dtf    = pSQL($this->detailTotalField);
            $wExpr  = $this->wholesaleExpr();
            $states = $this->stateIn();

            $joinCategory = '';
            $whereCategory = '';
            if ($catId > 0) {
                $joinCategory  = ' INNER JOIN ' . _DB_PREFIX_ . 'category_product cp ON od.product_id = cp.id_product';
                $whereCategory = ' AND cp.id_category = ' . $catId;
            }

            $baseFrom = _DB_PREFIX_ . 'order_detail od
            INNER JOIN ' . _DB_PREFIX_ . 'orders o ON o.id_order = od.id_order'
                . $joinCategory;

            $baseWhere = 'o.current_state IN (' . $states . ')
              AND o.id_shop = ' . $shop . '
              AND o.date_add BETWEEN \'' . $dfrom . ' 00:00:00\' AND \'' . $dto . ' 23:59:59\''
                . $whereCategory;

            // Query de totales para paginación
            $countSql = 'SELECT COUNT(DISTINCT od.product_id) as total
            FROM ' . $baseFrom . '
            WHERE ' . $baseWhere;

            $countRow = Db::getInstance()->getRow($countSql);
            $total = $countRow ? (int) $countRow['total'] : 0;

            if ($total === 0) {
                return ['data' => [], 'total' => 0];
            }

            // Query principal
            $sql = 'SELECT
                od.product_id,
                od.product_name,
                od.product_reference,
                SUM(od.product_quantity) as qty_sold,
                SUM(od.' . $dtf . ') as revenue,
                SUM(' . $wExpr . ' * od.product_quantity) as cost,
                (SUM(od.' . $dtf . ') - SUM(' . $wExpr . ' * od.product_quantity)) as margin,
                CASE
                    WHEN SUM(od.' . $dtf . ') > 0
                    THEN ROUND(
                        (SUM(od.' . $dtf . ') - SUM(' . $wExpr . ' * od.product_quantity))
                        / SUM(od.' . $dtf . ') * 100, 2
                    )
                    ELSE 0
                END as margin_pct
            FROM ' . $baseFrom . '
            WHERE ' . $baseWhere . '
            GROUP BY od.product_id, od.product_name, od.product_reference
            ORDER BY ' . $orderBy . ' ' . $orderDir . '
            LIMIT ' . $limit . ' OFFSET ' . $offset;

            $rows = Db::getInstance()->executeS($sql);

            if (!$rows) {
                return ['data' => [], 'total' => $total];
            }

            // Obtener stock actual de ps_stock_available
            $productIds = array_map(static fn($r) => (int) $r['product_id'], $rows);
            $idsStr     = implode(',', $productIds);

            $stockSql = 'SELECT id_product, SUM(quantity) as stock
            FROM ' . _DB_PREFIX_ . 'stock_available
            WHERE id_product IN (' . $idsStr . ')
              AND id_product_attribute = 0
            GROUP BY id_product';

            $stockResults = Db::getInstance()->executeS($stockSql);
            $stockMap     = [];
            if ($stockResults) {
                foreach ($stockResults as $sr) {
                    $stockMap[(int) $sr['id_product']] = (int) $sr['stock'];
                }
            }

            // Construir resultado final
            $data = [];
            foreach ($rows as $r) {
                $pid     = (int) $r['product_id'];
                $revenue = (float) $r['revenue'];
                $cost    = (float) $r['cost'];
                $margin  = $revenue - $cost;

                $data[] = [
                    'product_id'        => $pid,
                    'product_name'      => $r['product_name'],
                    'product_reference' => $r['product_reference'],
                    'qty_sold'          => (int)   $r['qty_sold'],
                    'revenue'           => round($revenue, 2),
                    'cost'              => round($cost, 2),
                    'margin'            => round($margin, 2),
                    'margin_pct'        => (float) $r['margin_pct'],
                    'current_stock'     => $stockMap[$pid] ?? 0,
                ];
            }

            return ['data' => $data, 'total' => $total];
        } catch (Throwable $e) {
            $this->lastError = 'getProductsReport: ' . $e->getMessage();
            PrestaShopLogger::addLog(
                'EmSalesReportQuery::getProductsReport - ' . $e->getMessage(),
                3,
                null,
                'EmSalesReport'
            );
            return ['data' => [], 'total' => 0];
        }
    }

    // ===========================================================
    // MÉTODO 4: getCategoriesReport
    // ===========================================================

    /**
     * Informe agrupado por categoría principal del producto (id_category_default).
     *
     * @return array<int, array{id_category: int, category_name: string, num_products: int,
     *                          qty_sold: int, revenue: float, cost: float,
     *                          margin: float, margin_pct: float}>
     */
    public function getCategoriesReport(
        string $dateFrom,
        string $dateTo,
        string $orderBy  = 'revenue',
        string $orderDir = 'DESC'
    ): array {
        $allowedOrder = ['revenue', 'qty_sold', 'cost', 'margin', 'margin_pct', 'num_products', 'cl.name'];
        $orderBy  = $this->sanitizeOrderBy($orderBy, $allowedOrder, 'revenue');
        $orderDir = $this->sanitizeOrderDir($orderDir);

        try {
            $dfrom  = pSQL($dateFrom);
            $dto    = pSQL($dateTo);
            $shop   = (int) $this->idShop;
            $lang   = (int) $this->idLang;
            $dtf    = pSQL($this->detailTotalField);
            $wExpr  = $this->wholesaleExpr();
            $states = $this->stateIn();

            $sql = 'SELECT
                cl.id_category,
                cl.name as category_name,
                COUNT(DISTINCT od.product_id) as num_products,
                SUM(od.product_quantity) as qty_sold,
                SUM(od.' . $dtf . ') as revenue,
                SUM(' . $wExpr . ' * od.product_quantity) as cost,
                (SUM(od.' . $dtf . ') - SUM(' . $wExpr . ' * od.product_quantity)) as margin,
                CASE
                    WHEN SUM(od.' . $dtf . ') > 0
                    THEN ROUND(
                        (SUM(od.' . $dtf . ') - SUM(' . $wExpr . ' * od.product_quantity))
                        / SUM(od.' . $dtf . ') * 100, 2
                    )
                    ELSE 0
                END as margin_pct
            FROM ' . _DB_PREFIX_ . 'order_detail od
            INNER JOIN ' . _DB_PREFIX_ . 'orders o ON o.id_order = od.id_order
            INNER JOIN ' . _DB_PREFIX_ . 'product p ON od.product_id = p.id_product
            INNER JOIN ' . _DB_PREFIX_ . 'category_product cp
                ON od.product_id = cp.id_product AND cp.id_category = p.id_category_default
            INNER JOIN ' . _DB_PREFIX_ . 'category_lang cl
                ON cp.id_category = cl.id_category
                AND cl.id_lang = ' . $lang . '
                AND cl.id_shop = ' . $shop . '
            WHERE o.current_state IN (' . $states . ')
              AND o.id_shop = ' . $shop . '
              AND o.date_add BETWEEN \'' . $dfrom . ' 00:00:00\' AND \'' . $dto . ' 23:59:59\'
            GROUP BY cl.id_category, cl.name
            ORDER BY ' . $orderBy . ' ' . $orderDir;

            $rows = Db::getInstance()->executeS($sql);

            if (!$rows) {
                return [];
            }

            $result = [];
            foreach ($rows as $r) {
                $revenue = (float) $r['revenue'];
                $cost    = (float) $r['cost'];
                $margin  = $revenue - $cost;

                $result[] = [
                    'id_category'   => (int)   $r['id_category'],
                    'category_name' => $r['category_name'],
                    'num_products'  => (int)   $r['num_products'],
                    'qty_sold'      => (int)   $r['qty_sold'],
                    'revenue'       => round($revenue, 2),
                    'cost'          => round($cost, 2),
                    'margin'        => round($margin, 2),
                    'margin_pct'    => (float) $r['margin_pct'],
                ];
            }

            return $result;
        } catch (Throwable $e) {
            $this->lastError = 'getCategoriesReport: ' . $e->getMessage();
            PrestaShopLogger::addLog(
                'EmSalesReportQuery::getCategoriesReport - ' . $e->getMessage(),
                3,
                null,
                'EmSalesReport'
            );
            return [];
        }
    }

    // ===========================================================
    // MÉTODO 5: getCustomersReport
    // ===========================================================

    /**
     * Informe por cliente con paginación.
     *
     * @return array{data: array, total: int}
     */
    public function getCustomersReport(
        string $dateFrom,
        string $dateTo,
        string $orderBy  = 'total_spent',
        string $orderDir = 'DESC',
        int $limit       = 50,
        int $offset      = 0
    ): array {
        $allowedOrder = ['total_spent', 'total_orders', 'avg_ticket', 'last_order_date', 'first_order_date', 'c.lastname'];
        $orderBy  = $this->sanitizeOrderBy($orderBy, $allowedOrder, 'total_spent');
        $orderDir = $this->sanitizeOrderDir($orderDir);
        $limit    = max(1, (int) $limit);
        $offset   = max(0, (int) $offset);

        try {
            $dfrom  = pSQL($dateFrom);
            $dto    = pSQL($dateTo);
            $shop   = (int) $this->idShop;
            $tf     = pSQL($this->totalField);
            $states = $this->stateIn();

            $baseFrom  = _DB_PREFIX_ . 'orders o
            INNER JOIN ' . _DB_PREFIX_ . 'customer c ON o.id_customer = c.id_customer';
            $baseWhere = 'o.current_state IN (' . $states . ')
              AND o.id_shop = ' . $shop . '
              AND o.date_add BETWEEN \'' . $dfrom . ' 00:00:00\' AND \'' . $dto . ' 23:59:59\'';

            // Total para paginación
            $countSql = 'SELECT COUNT(DISTINCT c.id_customer) as total
            FROM ' . $baseFrom . '
            WHERE ' . $baseWhere;

            $countRow = Db::getInstance()->getRow($countSql);
            $total    = $countRow ? (int) $countRow['total'] : 0;

            if ($total === 0) {
                return ['data' => [], 'total' => 0];
            }

            $sql = 'SELECT
                c.id_customer,
                c.firstname,
                c.lastname,
                c.email,
                c.company,
                COUNT(DISTINCT o.id_order) as total_orders,
                SUM(o.' . $tf . ') as total_spent,
                AVG(o.' . $tf . ') as avg_ticket,
                MIN(o.date_add) as first_order_date,
                MAX(o.date_add) as last_order_date
            FROM ' . $baseFrom . '
            WHERE ' . $baseWhere . '
            GROUP BY c.id_customer, c.firstname, c.lastname, c.email, c.company
            ORDER BY ' . $orderBy . ' ' . $orderDir . '
            LIMIT ' . $limit . ' OFFSET ' . $offset;

            $rows = Db::getInstance()->executeS($sql);

            if (!$rows) {
                return ['data' => [], 'total' => $total];
            }

            $data = [];
            foreach ($rows as $r) {
                $data[] = [
                    'id_customer'      => (int)   $r['id_customer'],
                    'firstname'        => $r['firstname'],
                    'lastname'         => $r['lastname'],
                    'email'            => $r['email'],
                    'company'          => $r['company'] ?? '',
                    'total_orders'     => (int)   $r['total_orders'],
                    'total_spent'      => round((float) $r['total_spent'], 2),
                    'avg_ticket'       => round((float) $r['avg_ticket'], 2),
                    'first_order_date' => $r['first_order_date'],
                    'last_order_date'  => $r['last_order_date'],
                ];
            }

            return ['data' => $data, 'total' => $total];
        } catch (Throwable $e) {
            $this->lastError = 'getCustomersReport: ' . $e->getMessage();
            PrestaShopLogger::addLog(
                'EmSalesReportQuery::getCustomersReport - ' . $e->getMessage(),
                3,
                null,
                'EmSalesReport'
            );
            return ['data' => [], 'total' => 0];
        }
    }

    // ===========================================================
    // MÉTODO 6: getSuppliersReport
    // ===========================================================

    /**
     * Informe agrupado por proveedor.
     *
     * @return array<int, array{id_supplier: int, supplier_name: string, num_products: int,
     *                          qty_sold: int, revenue: float, cost: float,
     *                          margin: float, margin_pct: float}>
     */
    public function getSuppliersReport(
        string $dateFrom,
        string $dateTo,
        string $orderBy  = 'revenue',
        string $orderDir = 'DESC'
    ): array {
        $allowedOrder = ['revenue', 'qty_sold', 'cost', 'margin', 'margin_pct', 'num_products', 's.name'];
        $orderBy  = $this->sanitizeOrderBy($orderBy, $allowedOrder, 'revenue');
        $orderDir = $this->sanitizeOrderDir($orderDir);

        try {
            $dfrom  = pSQL($dateFrom);
            $dto    = pSQL($dateTo);
            $shop   = (int) $this->idShop;
            $dtf    = pSQL($this->detailTotalField);
            $wExpr  = $this->wholesaleExpr();
            $states = $this->stateIn();

            $sql = 'SELECT
                s.id_supplier,
                s.name as supplier_name,
                COUNT(DISTINCT od.product_id) as num_products,
                SUM(od.product_quantity) as qty_sold,
                SUM(od.' . $dtf . ') as revenue,
                SUM(' . $wExpr . ' * od.product_quantity) as cost,
                (SUM(od.' . $dtf . ') - SUM(' . $wExpr . ' * od.product_quantity)) as margin,
                CASE
                    WHEN SUM(od.' . $dtf . ') > 0
                    THEN ROUND(
                        (SUM(od.' . $dtf . ') - SUM(' . $wExpr . ' * od.product_quantity))
                        / SUM(od.' . $dtf . ') * 100, 2
                    )
                    ELSE 0
                END as margin_pct
            FROM ' . _DB_PREFIX_ . 'order_detail od
            INNER JOIN ' . _DB_PREFIX_ . 'orders o ON o.id_order = od.id_order
            INNER JOIN ' . _DB_PREFIX_ . 'product p ON od.product_id = p.id_product
            INNER JOIN ' . _DB_PREFIX_ . 'supplier s ON p.id_supplier = s.id_supplier
            WHERE o.current_state IN (' . $states . ')
              AND o.id_shop = ' . $shop . '
              AND o.date_add BETWEEN \'' . $dfrom . ' 00:00:00\' AND \'' . $dto . ' 23:59:59\'
              AND p.id_supplier > 0
            GROUP BY s.id_supplier, s.name
            ORDER BY ' . $orderBy . ' ' . $orderDir;

            $rows = Db::getInstance()->executeS($sql);

            if (!$rows) {
                return [];
            }

            $result = [];
            foreach ($rows as $r) {
                $revenue = (float) $r['revenue'];
                $cost    = (float) $r['cost'];

                $result[] = [
                    'id_supplier'   => (int)   $r['id_supplier'],
                    'supplier_name' => $r['supplier_name'],
                    'num_products'  => (int)   $r['num_products'],
                    'qty_sold'      => (int)   $r['qty_sold'],
                    'revenue'       => round($revenue, 2),
                    'cost'          => round($cost, 2),
                    'margin'        => round($revenue - $cost, 2),
                    'margin_pct'    => (float) $r['margin_pct'],
                ];
            }

            return $result;
        } catch (Throwable $e) {
            $this->lastError = 'getSuppliersReport: ' . $e->getMessage();
            PrestaShopLogger::addLog(
                'EmSalesReportQuery::getSuppliersReport - ' . $e->getMessage(),
                3,
                null,
                'EmSalesReport'
            );
            return [];
        }
    }

    // ===========================================================
    // MÉTODO 7: getManufacturersReport
    // ===========================================================

    /**
     * Informe agrupado por fabricante/marca.
     *
     * @return array<int, array{id_manufacturer: int, manufacturer_name: string, num_products: int,
     *                          qty_sold: int, revenue: float, cost: float,
     *                          margin: float, margin_pct: float}>
     */
    public function getManufacturersReport(
        string $dateFrom,
        string $dateTo,
        string $orderBy  = 'revenue',
        string $orderDir = 'DESC'
    ): array {
        $allowedOrder = ['revenue', 'qty_sold', 'cost', 'margin', 'margin_pct', 'num_products', 'm.name'];
        $orderBy  = $this->sanitizeOrderBy($orderBy, $allowedOrder, 'revenue');
        $orderDir = $this->sanitizeOrderDir($orderDir);

        try {
            $dfrom  = pSQL($dateFrom);
            $dto    = pSQL($dateTo);
            $shop   = (int) $this->idShop;
            $dtf    = pSQL($this->detailTotalField);
            $wExpr  = $this->wholesaleExpr();
            $states = $this->stateIn();

            $sql = 'SELECT
                m.id_manufacturer,
                m.name as manufacturer_name,
                COUNT(DISTINCT od.product_id) as num_products,
                SUM(od.product_quantity) as qty_sold,
                SUM(od.' . $dtf . ') as revenue,
                SUM(' . $wExpr . ' * od.product_quantity) as cost,
                (SUM(od.' . $dtf . ') - SUM(' . $wExpr . ' * od.product_quantity)) as margin,
                CASE
                    WHEN SUM(od.' . $dtf . ') > 0
                    THEN ROUND(
                        (SUM(od.' . $dtf . ') - SUM(' . $wExpr . ' * od.product_quantity))
                        / SUM(od.' . $dtf . ') * 100, 2
                    )
                    ELSE 0
                END as margin_pct
            FROM ' . _DB_PREFIX_ . 'order_detail od
            INNER JOIN ' . _DB_PREFIX_ . 'orders o ON o.id_order = od.id_order
            INNER JOIN ' . _DB_PREFIX_ . 'product p ON od.product_id = p.id_product
            INNER JOIN ' . _DB_PREFIX_ . 'manufacturer m ON p.id_manufacturer = m.id_manufacturer
            WHERE o.current_state IN (' . $states . ')
              AND o.id_shop = ' . $shop . '
              AND o.date_add BETWEEN \'' . $dfrom . ' 00:00:00\' AND \'' . $dto . ' 23:59:59\'
              AND p.id_manufacturer > 0
            GROUP BY m.id_manufacturer, m.name
            ORDER BY ' . $orderBy . ' ' . $orderDir;

            $rows = Db::getInstance()->executeS($sql);

            if (!$rows) {
                return [];
            }

            $result = [];
            foreach ($rows as $r) {
                $revenue = (float) $r['revenue'];
                $cost    = (float) $r['cost'];

                $result[] = [
                    'id_manufacturer'   => (int)   $r['id_manufacturer'],
                    'manufacturer_name' => $r['manufacturer_name'],
                    'num_products'      => (int)   $r['num_products'],
                    'qty_sold'          => (int)   $r['qty_sold'],
                    'revenue'           => round($revenue, 2),
                    'cost'              => round($cost, 2),
                    'margin'            => round($revenue - $cost, 2),
                    'margin_pct'        => (float) $r['margin_pct'],
                ];
            }

            return $result;
        } catch (Throwable $e) {
            PrestaShopLogger::addLog(
                'EmSalesReportQuery::getManufacturersReport - ' . $e->getMessage(),
                3,
                null,
                'EmSalesReport'
            );
            return [];
        }
    }

    // ===========================================================
    // MÉTODO 8: getTopPaymentMethods
    // ===========================================================

    /**
     * Desglose de ventas por método de pago.
     *
     * @return array<int, array{payment_method: string, total_orders: int, revenue: float}>
     */
    public function getTopPaymentMethods(string $dateFrom, string $dateTo): array
    {
        try {
            $dfrom  = pSQL($dateFrom);
            $dto    = pSQL($dateTo);
            $shop   = (int) $this->idShop;
            $tf     = pSQL($this->totalField);
            $states = $this->stateIn();

            $sql = 'SELECT
                o.payment as payment_method,
                COUNT(DISTINCT o.id_order) as total_orders,
                IFNULL(SUM(o.' . $tf . '), 0) as revenue
            FROM ' . _DB_PREFIX_ . 'orders o
            WHERE o.current_state IN (' . $states . ')
              AND o.id_shop = ' . $shop . '
              AND o.date_add BETWEEN \'' . $dfrom . ' 00:00:00\' AND \'' . $dto . ' 23:59:59\'
            GROUP BY o.payment
            ORDER BY revenue DESC';

            $rows = Db::getInstance()->executeS($sql);

            if (!$rows) {
                return [];
            }

            $result = [];
            foreach ($rows as $r) {
                $result[] = [
                    'payment_method' => $r['payment_method'],
                    'total_orders'   => (int)   $r['total_orders'],
                    'revenue'        => round((float) $r['revenue'], 2),
                ];
            }

            return $result;
        } catch (Throwable $e) {
            PrestaShopLogger::addLog(
                'EmSalesReportQuery::getTopPaymentMethods - ' . $e->getMessage(),
                3,
                null,
                'EmSalesReport'
            );
            return [];
        }
    }

    // ===========================================================
    // MÉTODO 9: getTopCountries
    // ===========================================================

    /**
     * Top países por volumen de ventas (usa dirección de entrega).
     *
     * @param int $limit  Máximo de países a devolver
     * @return array<int, array{id_country: int, country_name: string, total_orders: int, revenue: float}>
     */
    public function getTopCountries(string $dateFrom, string $dateTo, int $limit = 10): array
    {
        $limit = max(1, (int) $limit);

        try {
            $dfrom  = pSQL($dateFrom);
            $dto    = pSQL($dateTo);
            $shop   = (int) $this->idShop;
            $lang   = (int) $this->idLang;
            $tf     = pSQL($this->totalField);
            $states = $this->stateIn();

            $sql = 'SELECT
                a.id_country,
                cl.name as country_name,
                COUNT(DISTINCT o.id_order) as total_orders,
                IFNULL(SUM(o.' . $tf . '), 0) as revenue
            FROM ' . _DB_PREFIX_ . 'orders o
            INNER JOIN ' . _DB_PREFIX_ . 'address a ON o.id_address_delivery = a.id_address
            INNER JOIN ' . _DB_PREFIX_ . 'country_lang cl
                ON a.id_country = cl.id_country AND cl.id_lang = ' . $lang . '
            WHERE o.current_state IN (' . $states . ')
              AND o.id_shop = ' . $shop . '
              AND o.date_add BETWEEN \'' . $dfrom . ' 00:00:00\' AND \'' . $dto . ' 23:59:59\'
            GROUP BY a.id_country, cl.name
            ORDER BY revenue DESC
            LIMIT ' . $limit;

            $rows = Db::getInstance()->executeS($sql);

            if (!$rows) {
                return [];
            }

            $result = [];
            foreach ($rows as $r) {
                $result[] = [
                    'id_country'   => (int)   $r['id_country'],
                    'country_name' => $r['country_name'],
                    'total_orders' => (int)   $r['total_orders'],
                    'revenue'      => round((float) $r['revenue'], 2),
                ];
            }

            return $result;
        } catch (Throwable $e) {
            PrestaShopLogger::addLog(
                'EmSalesReportQuery::getTopCountries - ' . $e->getMessage(),
                3,
                null,
                'EmSalesReport'
            );
            return [];
        }
    }

    // ===========================================================
    // MÉTODO 10: getCategoriesList
    // ===========================================================

    /**
     * Lista de categorías activas para el dropdown de filtro.
     *
     * @return array<int, array{id_category: int, name: string}>
     */
    public function getCategoriesList(): array
    {
        try {
            $shop = (int) $this->idShop;
            $lang = (int) $this->idLang;

            $sql = 'SELECT c.id_category, cl.name
            FROM ' . _DB_PREFIX_ . 'category c
            INNER JOIN ' . _DB_PREFIX_ . 'category_lang cl
                ON c.id_category = cl.id_category
                AND cl.id_lang = ' . $lang . '
                AND cl.id_shop = ' . $shop . '
            WHERE c.active = 1
              AND c.id_parent > 0
            ORDER BY cl.name ASC';

            $rows = Db::getInstance()->executeS($sql);

            if (!$rows) {
                return [];
            }

            $result = [];
            foreach ($rows as $r) {
                $result[] = [
                    'id_category' => (int) $r['id_category'],
                    'name'        => $r['name'],
                ];
            }

            return $result;
        } catch (Throwable $e) {
            PrestaShopLogger::addLog(
                'EmSalesReportQuery::getCategoriesList - ' . $e->getMessage(),
                3,
                null,
                'EmSalesReport'
            );
            return [];
        }
    }
}
