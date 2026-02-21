{**
 * Sales Report Pro — Vista principal del dashboard
 * Módulo: emsalesreport
 *}

<div id="emsales-dashboard">

  {* Barra de filtros superior *}
  {include file="./partials/filters_bar.tpl"}

  {* Tarjetas de KPIs *}
  {include file="./partials/kpi_cards.tpl"}

  {* Gráfico principal *}
  {include file="./partials/chart_area.tpl"}

  {* Panel de pestañas con datos detallados *}
  <div class="panel" id="emsales-data-panel">
    <div class="panel-heading">
      <ul class="nav nav-tabs" id="emsales-tabs" role="tablist">
        <li role="presentation" class="{if $active_tab == 'products'}active{/if}">
          <a href="#tab-products" role="tab" data-tab="products" id="emsales-tab-products">
            <i class="icon-tag"></i> {l s='Productos' mod='emsalesreport'}
          </a>
        </li>
        <li role="presentation" class="{if $active_tab == 'categories'}active{/if}">
          <a href="#tab-categories" role="tab" data-tab="categories" id="emsales-tab-categories">
            <i class="icon-sitemap"></i> {l s='Categorías' mod='emsalesreport'}
          </a>
        </li>
        <li role="presentation" class="{if $active_tab == 'customers'}active{/if}">
          <a href="#tab-customers" role="tab" data-tab="customers" id="emsales-tab-customers">
            <i class="icon-users"></i> {l s='Clientes' mod='emsalesreport'}
          </a>
        </li>
        <li role="presentation" class="{if $active_tab == 'suppliers'}active{/if}">
          <a href="#tab-suppliers" role="tab" data-tab="suppliers" id="emsales-tab-suppliers">
            <i class="icon-truck"></i> {l s='Proveedores' mod='emsalesreport'}
          </a>
        </li>
      </ul>
    </div>

    <div class="tab-content">
      <div id="tab-products" class="tab-pane{if $active_tab == 'products'} active{/if}">
        {include file="./partials/tab_products.tpl"}
      </div>
      <div id="tab-categories" class="tab-pane{if $active_tab == 'categories'} active{/if}">
        {include file="./partials/tab_categories.tpl"}
      </div>
      <div id="tab-customers" class="tab-pane{if $active_tab == 'customers'} active{/if}">
        {include file="./partials/tab_customers.tpl"}
      </div>
      <div id="tab-suppliers" class="tab-pane{if $active_tab == 'suppliers'} active{/if}">
        {include file="./partials/tab_suppliers.tpl"}
      </div>
    </div>
  </div>

</div>{* /emsales-dashboard *}

{* Variables de configuración para JavaScript — deben ir ANTES de que se cargue dashboard.js *}
<script>
var emSalesConfig = {
  ajaxUrl:        '{$ajax_url|escape:'javascript'}',
  exportUrl:      '{$export_url|escape:'javascript'}',
  moduleUrl:      '{$module_url|escape:'javascript'}',
  dateFrom:       '{$date_from|escape:'javascript'}',
  dateTo:         '{$date_to|escape:'javascript'}',
  chartType:      '{$chart_type|escape:'javascript'}',
  activeTab:      '{$active_tab|escape:'javascript'}',
  compareEnabled: {if $compare_enabled}true{else}false{/if},
  currencySign:   '{$currency_sign|escape:'javascript'}',
  currencyIso:    '{$currency_iso|escape:'javascript'}',
  itemsPerPage:   {$items_per_page|intval},
  timeSeries:     {$time_series nofilter},
  granularity:    '{$granularity|escape:'javascript'}',
  ssrKpis:        {$kpis|json_encode nofilter}
};
</script>
