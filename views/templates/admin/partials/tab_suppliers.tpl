{**
 * Sales Report Pro — Pestaña: Proveedores / Fabricantes
 *}

{* Barra de herramientas *}
<div class="row emsales-tab-toolbar" style="margin-bottom:15px; margin-top:10px;">
  <div class="col-md-9">
    <span class="emsales-table-count text-muted">
      {l s='Total proveedores:' mod='emsalesreport'}
      <strong>{$suppliers_data|count}</strong>
    </span>
  </div>
  <div class="col-md-3 text-right">
    <div class="btn-group">
      <button type="button" class="btn btn-default btn-sm emsales-export-btn"
              data-tab="suppliers" data-format="csv">
        <i class="icon-file-text-o"></i> CSV
      </button>
      <button type="button" class="btn btn-default btn-sm emsales-export-btn"
              data-tab="suppliers" data-format="excel">
        <i class="icon-file-excel-o"></i> Excel
      </button>
    </div>
  </div>
</div>

{* Indicador de carga *}
<div id="emsales-suppliers-loading" class="emsales-loading" style="display:none; text-align:center; padding:20px;">
  <i class="icon-spinner icon-spin icon-2x"></i>
</div>

{* Tabla *}
<div class="table-responsive" id="emsales-suppliers-table-wrapper">
  <table class="table table-striped table-hover emsales-data-table" id="emsales-suppliers-table"
         data-tab="suppliers">
    <thead>
      <tr>
        <th class="emsales-sortable" data-sort="s.name">
          {l s='Proveedor' mod='emsalesreport'} <i class="icon-sort"></i>
        </th>
        <th class="emsales-sortable text-right" data-sort="num_products">
          {l s='Productos' mod='emsalesreport'} <i class="icon-sort"></i>
        </th>
        <th class="emsales-sortable text-right" data-sort="qty_sold">
          {l s='Uds. Vendidas' mod='emsalesreport'} <i class="icon-sort"></i>
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
    <tbody id="emsales-suppliers-tbody">
      {if $suppliers_data|count > 0}
        {foreach from=$suppliers_data item=supplier}
          <tr>
            <td>
              <strong>{$supplier.supplier_name|escape:'html'}</strong>
            </td>
            <td class="text-right">{$supplier.num_products|intval}</td>
            <td class="text-right">{$supplier.qty_sold|intval}</td>
            <td class="text-right">
              {$supplier.revenue|string_format:"%.2f"}&nbsp;{$currency_sign}
            </td>
            <td class="text-right">
              {$supplier.cost|string_format:"%.2f"}&nbsp;{$supplier.currency_sign|default:$currency_sign}
            </td>
            <td class="text-right">
              {$supplier.margin|string_format:"%.2f"}&nbsp;{$currency_sign}
            </td>
            <td class="text-right">
              <span class="badge {if $supplier.margin_pct > 30}badge-success{elseif $supplier.margin_pct > 15}badge-warning{else}badge-danger{/if}">
                {$supplier.margin_pct|string_format:"%.1f"}%
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

{* Fabricantes — sección secundaria *}
{if isset($manufacturers_data) && $manufacturers_data|count > 0}
<hr>
<h4 style="margin-top:20px; margin-bottom:15px;">
  <i class="icon-industry"></i> {l s='Por Fabricante' mod='emsalesreport'}
</h4>
<div class="table-responsive">
  <table class="table table-striped table-hover emsales-data-table" id="emsales-manufacturers-table">
    <thead>
      <tr>
        <th>{l s='Fabricante' mod='emsalesreport'}</th>
        <th class="text-right">{l s='Productos' mod='emsalesreport'}</th>
        <th class="text-right">{l s='Uds. Vendidas' mod='emsalesreport'}</th>
        <th class="text-right">{l s='Ingresos' mod='emsalesreport'}</th>
        <th class="text-right">{l s='Margen %' mod='emsalesreport'}</th>
      </tr>
    </thead>
    <tbody>
      {foreach from=$manufacturers_data item=mfr}
        <tr>
          <td>{$mfr.manufacturer_name|escape:'html'}</td>
          <td class="text-right">{$mfr.num_products|intval}</td>
          <td class="text-right">{$mfr.qty_sold|intval}</td>
          <td class="text-right">
            {$mfr.revenue|string_format:"%.2f"}&nbsp;{$currency_sign}
          </td>
          <td class="text-right">
            <span class="badge {if $mfr.margin_pct > 30}badge-success{elseif $mfr.margin_pct > 15}badge-warning{else}badge-danger{/if}">
              {$mfr.margin_pct|string_format:"%.1f"}%
            </span>
          </td>
        </tr>
      {/foreach}
    </tbody>
  </table>
</div>
{/if}
