<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class EmSalesReport extends Module
{
    public function __construct()
    {
        $this->name = 'emsalesreport';
        $this->tab = 'analytics_stats';
        $this->version = '1.0.0';
        $this->author = 'EM Modules';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '8.0.0', 'max' => '9.99.99'];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Sales Report Pro');
        $this->description = $this->l('Dashboard de ventas avanzado con KPIs, gráficos, informes por producto/categoría/cliente y exportación. Compatible PS 8 y 9.');
        $this->confirmUninstall = $this->l('¿Estás seguro? Se eliminará la configuración del módulo.');
    }

    public function install()
    {
        return parent::install()
            && $this->registerHook('displayBackOfficeHeader')
            && $this->installTab()
            && $this->installConfig();
    }

    public function uninstall()
    {
        return parent::uninstall()
            && $this->uninstallTab()
            && $this->uninstallConfig();
    }

    // --- Tab Installation ---
    private function installTab()
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'AdminEmSalesReport';
        $tab->module = $this->name;
        $tab->id_parent = (int) Tab::getIdFromClassName('AdminStats');
        // Si AdminStats no existe, usar AdminParentModules como fallback
        if (!$tab->id_parent) {
            $tab->id_parent = (int) Tab::getIdFromClassName('AdminParentModules');
        }
        $tab->icon = 'assessment'; // Material icon para PS 8+
        $languages = Language::getLanguages(false);
        foreach ($languages as $lang) {
            $tab->name[$lang['id_lang']] = 'Sales Report Pro';
        }
        return $tab->add();
    }

    private function uninstallTab()
    {
        $id_tab = (int) Tab::getIdFromClassName('AdminEmSalesReport');
        if ($id_tab) {
            $tab = new Tab($id_tab);
            return $tab->delete();
        }
        return true;
    }

    // --- Configuration ---
    private function installConfig()
    {
        Configuration::updateValue('EMSALES_DEFAULT_PERIOD', '30'); // últimos 30 días
        Configuration::updateValue('EMSALES_VALID_ORDER_STATES', ''); // vacío = usar ps_order_state.paid
        Configuration::updateValue('EMSALES_INCLUDE_TAX', '1'); // 1=con IVA, 0=sin IVA
        Configuration::updateValue('EMSALES_ITEMS_PER_PAGE', '50');
        Configuration::updateValue('EMSALES_CHART_TYPE', 'line'); // line, bar, area
        Configuration::updateValue('EMSALES_COMPARE_ENABLED', '1'); // comparativa con período anterior
        Configuration::updateValue('EMSALES_CURRENCY_DEFAULT', '0'); // 0 = moneda por defecto de la tienda
        return true;
    }

    private function uninstallConfig()
    {
        $keys = [
            'EMSALES_DEFAULT_PERIOD',
            'EMSALES_VALID_ORDER_STATES',
            'EMSALES_INCLUDE_TAX',
            'EMSALES_ITEMS_PER_PAGE',
            'EMSALES_CHART_TYPE',
            'EMSALES_COMPARE_ENABLED',
            'EMSALES_CURRENCY_DEFAULT',
        ];
        foreach ($keys as $key) {
            Configuration::deleteByName($key);
        }
        return true;
    }

    // --- Hook: Cargar CSS/JS en Back Office ---
    public function hookDisplayBackOfficeHeader()
    {
        // Cargar assets del dashboard en nuestro controller
        if ($this->context->controller instanceof AdminEmSalesReportController
            || Tools::getValue('controller') === 'AdminEmSalesReport') {
            $this->context->controller->addCSS($this->_path . 'views/css/admin/dashboard.css');
            $this->context->controller->addJS($this->_path . 'views/js/admin/chart.min.js');
            $this->context->controller->addJS($this->_path . 'views/js/admin/dashboard.js');
            $this->context->controller->addJS($this->_path . 'views/js/admin/export.js');
        }

        // Cargar JS de configuración (selector de estados) sólo en página de configurar módulo
        if (Tools::getValue('configure') === $this->name) {
            $this->context->controller->addJS($this->_path . 'views/js/admin/config.js');
        }
    }

    // --- Página de configuración del módulo ---
    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submitEmSalesReport')) {
            // Recoger checkboxes de estados de pedido
            $allStates = Db::getInstance()->executeS(
                'SELECT id_order_state FROM ' . _DB_PREFIX_ . 'order_state WHERE deleted = 0'
            );
            $selectedStates = [];
            if ($allStates) {
                foreach ($allStates as $st) {
                    $sid = (int) $st['id_order_state'];
                    if (Tools::getValue('EMSALES_STATE_' . $sid)) {
                        $selectedStates[] = $sid;
                    }
                }
            }
            Configuration::updateValue('EMSALES_VALID_ORDER_STATES', implode(',', $selectedStates));

            // Resto de campos
            Configuration::updateValue('EMSALES_DEFAULT_PERIOD',  (int) Tools::getValue('EMSALES_DEFAULT_PERIOD'));
            Configuration::updateValue('EMSALES_INCLUDE_TAX',     (int) Tools::getValue('EMSALES_INCLUDE_TAX'));
            Configuration::updateValue('EMSALES_ITEMS_PER_PAGE',  (int) Tools::getValue('EMSALES_ITEMS_PER_PAGE'));
            Configuration::updateValue('EMSALES_CHART_TYPE',      pSQL(Tools::getValue('EMSALES_CHART_TYPE')));
            Configuration::updateValue('EMSALES_COMPARE_ENABLED', (int) Tools::getValue('EMSALES_COMPARE_ENABLED'));
            $output .= $this->displayConfirmation($this->l('Configuración guardada correctamente.'));
        }

        return $output . $this->renderForm();
    }

    private function renderForm()
    {
        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG')
            ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG')
            : 0;
        $helper->submit_action = 'submitEmSalesReport';

        $helper->fields_value = [
            'EMSALES_DEFAULT_PERIOD'  => Configuration::get('EMSALES_DEFAULT_PERIOD'),
            'EMSALES_INCLUDE_TAX'     => Configuration::get('EMSALES_INCLUDE_TAX'),
            'EMSALES_ITEMS_PER_PAGE'  => Configuration::get('EMSALES_ITEMS_PER_PAGE'),
            'EMSALES_CHART_TYPE'      => Configuration::get('EMSALES_CHART_TYPE'),
            'EMSALES_COMPARE_ENABLED' => Configuration::get('EMSALES_COMPARE_ENABLED'),
        ];

        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Configuración de Sales Report Pro'),
                    'icon'  => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type'    => 'select',
                        'label'   => $this->l('Período por defecto'),
                        'name'    => 'EMSALES_DEFAULT_PERIOD',
                        'desc'    => $this->l('Período que se muestra al abrir el dashboard.'),
                        'options' => [
                            'query' => [
                                ['id' => '7',   'name' => $this->l('Últimos 7 días')],
                                ['id' => '14',  'name' => $this->l('Últimos 14 días')],
                                ['id' => '30',  'name' => $this->l('Últimos 30 días')],
                                ['id' => '90',  'name' => $this->l('Últimos 90 días')],
                                ['id' => '365', 'name' => $this->l('Último año')],
                            ],
                            'id'   => 'id',
                            'name' => 'name',
                        ],
                    ],
                    [
                        'type'    => 'html',
                        'label'   => $this->l('Estados de pedido válidos'),
                        'name'    => 'EMSALES_ORDER_STATES_SELECTOR',
                        'html_content' => $this->renderOrderStatesSelector(),
                    ],
                    [
                        'type'    => 'switch',
                        'label'   => $this->l('Mostrar precios con IVA'),
                        'name'    => 'EMSALES_INCLUDE_TAX',
                        'desc'    => $this->l('Si está activo, los importes incluyen impuestos.'),
                        'is_bool' => true,
                        'values'  => [
                            ['id' => 'active_on',  'value' => 1, 'label' => $this->l('Sí')],
                            ['id' => 'active_off', 'value' => 0, 'label' => $this->l('No')],
                        ],
                    ],
                    [
                        'type'    => 'select',
                        'label'   => $this->l('Elementos por página'),
                        'name'    => 'EMSALES_ITEMS_PER_PAGE',
                        'options' => [
                            'query' => [
                                ['id' => '25',  'name' => '25'],
                                ['id' => '50',  'name' => '50'],
                                ['id' => '100', 'name' => '100'],
                                ['id' => '200', 'name' => '200'],
                            ],
                            'id'   => 'id',
                            'name' => 'name',
                        ],
                    ],
                    [
                        'type'    => 'select',
                        'label'   => $this->l('Tipo de gráfico por defecto'),
                        'name'    => 'EMSALES_CHART_TYPE',
                        'options' => [
                            'query' => [
                                ['id' => 'line', 'name' => $this->l('Línea')],
                                ['id' => 'bar',  'name' => $this->l('Barras')],
                            ],
                            'id'   => 'id',
                            'name' => 'name',
                        ],
                    ],
                    [
                        'type'    => 'switch',
                        'label'   => $this->l('Habilitar comparativa con período anterior'),
                        'name'    => 'EMSALES_COMPARE_ENABLED',
                        'is_bool' => true,
                        'values'  => [
                            ['id' => 'compare_on',  'value' => 1, 'label' => $this->l('Sí')],
                            ['id' => 'compare_off', 'value' => 0, 'label' => $this->l('No')],
                        ],
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Guardar'),
                ],
            ],
        ];

        return $helper->generateForm([$fields_form]);
    }

    /**
     * Genera el selector visual de estados de pedido con checkboxes.
     * Muestra nombre, color del estado y badge "Pagado" si paid=1.
     */
    private function renderOrderStatesSelector(): string
    {
        $idLang = (int) Configuration::get('PS_LANG_DEFAULT');

        $states = Db::getInstance()->executeS(
            'SELECT os.id_order_state, os.color, os.paid,
                    IFNULL(osl.name, os.id_order_state) as name
             FROM ' . _DB_PREFIX_ . 'order_state os
             LEFT JOIN ' . _DB_PREFIX_ . 'order_state_lang osl
               ON os.id_order_state = osl.id_order_state AND osl.id_lang = ' . $idLang . '
             WHERE os.deleted = 0
             ORDER BY os.id_order_state ASC'
        );

        if (!$states) {
            return '<p class="alert alert-warning">' . $this->l('No se encontraron estados de pedido.') . '</p>';
        }

        // IDs actualmente seleccionados
        $configured = Configuration::get('EMSALES_VALID_ORDER_STATES');
        if (!empty($configured)) {
            $selectedIds = array_map('intval', explode(',', $configured));
        } else {
            // Fallback: los marcados como paid
            $selectedIds = [];
            foreach ($states as $s) {
                if ((int) $s['paid'] === 1) {
                    $selectedIds[] = (int) $s['id_order_state'];
                }
            }
        }

        $html  = '<div class="emsales-state-selector">';
        $html .= '<p class="help-block" style="margin-bottom:12px;">';
        $html .= $this->l('Selecciona los estados que se consideran una venta válida en los informes. ');
        $html .= $this->l('Los marcados con ✓ Pagado son los recomendados.');
        $html .= '</p>';
        $html .= '<div style="display:flex;flex-wrap:wrap;gap:8px;">';

        foreach ($states as $s) {
            $sid      = (int) $s['id_order_state'];
            $name     = htmlspecialchars($s['name'], ENT_QUOTES);
            $color    = htmlspecialchars($s['color'] ?? '#aaaaaa', ENT_QUOTES);
            $isPaid   = (int) $s['paid'] === 1;
            $checked  = in_array($sid, $selectedIds, true) ? ' checked' : '';
            $fieldId  = 'emsales_state_' . $sid;

            // Calcular si el color es claro u oscuro para el texto
            $hex = ltrim($color, '#');
            if (strlen($hex) === 6) {
                $r = hexdec(substr($hex, 0, 2));
                $g = hexdec(substr($hex, 2, 2));
                $b = hexdec(substr($hex, 4, 2));
                $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;
                $textColor = $luminance > 0.6 ? '#333333' : '#ffffff';
            } else {
                $textColor = '#333333';
            }

            $html .= '<label for="' . $fieldId . '" style="'
                . 'display:inline-flex;align-items:center;gap:6px;'
                . 'border:2px solid ' . ($checked ? $color : '#ddd') . ';'
                . 'border-radius:6px;padding:7px 12px;cursor:pointer;'
                . 'background:' . ($checked ? $color : '#fff') . ';'
                . 'color:' . ($checked ? $textColor : '#555') . ';'
                . 'font-size:13px;transition:all .15s;white-space:nowrap;'
                . 'min-width:160px;'
                . '" class="emsales-state-label" data-color="' . $color . '" data-textcolor="' . $textColor . '">';
            $html .= '<input type="checkbox" id="' . $fieldId . '" name="EMSALES_STATE_' . $sid . '" value="1"'
                . $checked . ' style="margin:0;">';
            $html .= '<span>' . $name . '</span>';
            if ($isPaid) {
                $html .= '<span style="font-size:11px;opacity:.85;">&#10003; ' . $this->l('Pagado') . '</span>';
            }
            $html .= '</label>';
        }

        $html .= '</div>'; // flex
        $html .= '</div>'; // emsales-state-selector

        return $html;
    }

    // --- Helpers públicos que usa el AdminController ---

    /**
     * Obtiene los IDs de estados de pedido válidos para los informes.
     * Si el usuario configuró IDs específicos, los usa.
     * Si no, busca los estados marcados como "paid" en PrestaShop.
     */
    public function getValidOrderStateIds(): array
    {
        $configured = Configuration::get('EMSALES_VALID_ORDER_STATES');
        if (!empty($configured)) {
            return array_map('intval', explode(',', $configured));
        }

        // Fallback: estados marcados como "paid" en PrestaShop
        $sql = 'SELECT id_order_state FROM ' . _DB_PREFIX_ . 'order_state WHERE paid = 1';
        $results = Db::getInstance()->executeS($sql);
        $ids = [];
        if ($results) {
            foreach ($results as $row) {
                $ids[] = (int) $row['id_order_state'];
            }
        }

        // Si no hay ninguno marcado como paid, usar estados comunes
        if (empty($ids)) {
            $ids = [2, 3, 4, 5]; // Payment accepted, Processing, Shipped, Delivered
        }

        return $ids;
    }

    /**
     * Devuelve el campo SQL correcto según si se quiere con o sin IVA (ps_orders)
     */
    public function getTotalField(): string
    {
        return (int) Configuration::get('EMSALES_INCLUDE_TAX')
            ? 'total_paid_tax_incl'
            : 'total_paid_tax_excl';
    }

    /**
     * Devuelve el campo SQL correcto según si se quiere con o sin IVA (ps_order_detail)
     */
    public function getDetailTotalField(): string
    {
        return (int) Configuration::get('EMSALES_INCLUDE_TAX')
            ? 'total_price_tax_incl'
            : 'total_price_tax_excl';
    }
}
