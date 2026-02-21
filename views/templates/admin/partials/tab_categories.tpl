{**
 * Sales Report Pro — Pestaña: Categorías
 *}

{* Barra de herramientas *}
<div class="row emsales-tab-toolbar" style="margin-bottom:15px; margin-top:10px;">
  <div class="col-md-9">
    <span class="emsales-table-count text-muted">
      {l s='Total categorías:' mod='emsalesreport'}
      <strong>{$categories_data|count}</strong>
    </span>
  </div>
  <div class="col-md-3 text-right">
    <div class="btn-group">
      <button type="button" class="btn btn-default btn-sm emsales-export-btn"
              data-tab="categories" data-format="csv">
        <i class="icon-file-text-o"></i> CSV
      </button>
      <button type="button" class="btn btn-default btn-sm emsales-export-btn"
              data-tab="categories" data-format="excel">
        <i class="icon-file-excel-o"></i> Excel
      </button>
    </div>
  </div>
</div>

<div class="row">
  {* Tabla izquierda *}
  <div class="col-md-8">
    <div id="emsales-categories-loading" class="emsales-loading" style="display:none; text-align:center; padding:20px;">
      <i class="icon-spinner icon-spin icon-2x"></i>
    </div>

    <div class="table-responsive">
      <table class="table table-striped table-hover emsales-data-table" id="emsales-categories-table"
             data-tab="categories">
        <thead>
          <tr>
            <th class="emsales-sortable" data-sort="cl.name">
              {l s='Categoría' mod='emsalesreport'} <i class="icon-sort"></i>
            </th>
            <th class="emsales-sortable text-right" data-sort="num_products">
              {l s='Productos' mod='emsalesreport'} <i class="icon-sort"></i>
            </th>
            <th class="emsales-sortable text-right" data-sort="qty_sold">
              {l s='Uds.' mod='emsalesreport'} <i class="icon-sort"></i>
            </th>
            <th class="emsales-sortable text-right" data-sort="revenue">
              {l s='Ingresos' mod='emsalesreport'} <i class="icon-sort-down"></i>
            </th>
            <th class="emsales-sortable text-right" data-sort="cost">
              {l s='Coste' mod='emsalesreport'} <i class="icon-sort"></i>
            </th>
            <th class="emsales-sortable text-right" data-sort="margin">
              {l s='Margen' mod='emsalesreport'} <i class="icon-sort"></i>
            </th>
            <th class="emsales-sortable text-right" data-sort="margin_pct">
              {l s='Margen %' mod='emsalesreport'} <i class="icon-sort"></i>
            </th>
          </tr>
        </thead>
        <tbody id="emsales-categories-tbody">
          {if $categories_data|count > 0}
            {foreach from=$categories_data item=cat}
              <tr>
                <td>{$cat.category_name|escape:'html'}</td>
                <td class="text-right">{$cat.num_products|intval}</td>
                <td class="text-right">{$cat.qty_sold|intval}</td>
                <td class="text-right">
                  {$cat.revenue|string_format:"%.2f"}&nbsp;{$currency_sign}
                </td>
                <td class="text-right">
                  {$cat.cost|string_format:"%.2f"}&nbsp;{$currency_sign}
                </td>
                <td class="text-right">
                  {$cat.margin|string_format:"%.2f"}&nbsp;{$currency_sign}
                </td>
                <td class="text-right">
                  <span class="badge {if $cat.margin_pct > 30}badge-success{elseif $cat.margin_pct > 15}badge-warning{else}badge-danger{/if}">
                    {$cat.margin_pct|string_format:"%.1f"}%
                  </span>
                </td>
              </tr>
            {/foreach}
          {else}
            <tr>
              <td colspan="7" class="text-center text-muted" style="padding:30px;">
                <i class="icon-warning"></i> {l s='No hay datos para el período seleccionado.' mod='emsalesreport'}
              </td>
            </tr>
          {/if}
        </tbody>
      </table>
    </div>
  </div>

  {* Mini gráfico de distribución *}
  <div class="col-md-4">
    <div class="panel emsales-panel-sm">
      <div class="panel-heading">
        <i class="icon-pie-chart"></i>
        {l s='Distribución de ingresos' mod='emsalesreport'}
      </div>
      <div class="panel-body" style="position:relative; min-height:220px;">
        <canvas id="emsales-categories-chart" height="220"></canvas>
        <div id="emsales-categories-chart-empty" class="text-center text-muted" style="display:none; padding-top:80px;">
          {l s='Sin datos' mod='emsalesreport'}
        </div>
      </div>
    </div>
  </div>
</div>
