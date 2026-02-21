{**
 * Sales Report Pro — Pestaña: Productos
 *}

{* Filtros y exportación *}
<div class="row emsales-tab-toolbar" style="margin-bottom:15px; margin-top:10px;">
  <div class="col-md-3">
    <select id="emsales-category-filter" class="form-control">
      <option value="0">{l s='Todas las categorías' mod='emsalesreport'}</option>
      {foreach from=$categories_list item=cat}
        <option value="{$cat.id_category|intval}">{$cat.name|escape:'html'}</option>
      {/foreach}
    </select>
  </div>
  <div class="col-md-6">
    <span class="emsales-table-count text-muted">
      {l s='Total:' mod='emsalesreport'} <strong id="emsales-products-total">{$products_data.total|intval}</strong>
    </span>
  </div>
  <div class="col-md-3 text-right">
    <div class="btn-group">
      <button type="button" class="btn btn-default btn-sm emsales-export-btn"
              data-tab="products" data-format="csv">
        <i class="icon-file-text-o"></i> CSV
      </button>
      <button type="button" class="btn btn-default btn-sm emsales-export-btn"
              data-tab="products" data-format="excel">
        <i class="icon-file-excel-o"></i> Excel
      </button>
    </div>
  </div>
</div>

{* Indicador de carga *}
<div id="emsales-products-loading" class="emsales-loading" style="display:none; text-align:center; padding:20px;">
  <i class="icon-spinner icon-spin icon-2x"></i>
</div>

{* Tabla *}
<div class="table-responsive" id="emsales-products-table-wrapper">
  <table class="table table-striped table-hover emsales-data-table" id="emsales-products-table"
         data-tab="products">
    <thead>
      <tr>
        <th class="emsales-sortable" data-sort="od.product_name">
          {l s='Producto' mod='emsalesreport'} <i class="icon-sort"></i>
        </th>
        <th class="emsales-sortable" data-sort="product_reference">
          {l s='Ref.' mod='emsalesreport'} <i class="icon-sort"></i>
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
        <th class="text-right">{l s='Stock' mod='emsalesreport'}</th>
      </tr>
    </thead>
    <tbody id="emsales-products-tbody">
      {if $products_data.items|count > 0}
        {foreach from=$products_data.items item=product}
          <tr>
            <td>
              <span title="{$product.product_name|escape:'html'}">
                {$product.product_name|escape:'html'|truncate:50:'...':true}
              </span>
            </td>
            <td><code>{$product.product_reference|escape:'html'}</code></td>
            <td class="text-right">{$product.qty_sold|intval}</td>
            <td class="text-right">
              {$product.revenue|string_format:"%.2f"}&nbsp;{$currency_sign}
            </td>
            <td class="text-right">
              {$product.cost|string_format:"%.2f"}&nbsp;{$currency_sign}
            </td>
            <td class="text-right">
              {$product.margin|string_format:"%.2f"}&nbsp;{$currency_sign}
            </td>
            <td class="text-right">
              <span class="badge {if $product.margin_pct > 30}badge-success{elseif $product.margin_pct > 15}badge-warning{else}badge-danger{/if}">
                {$product.margin_pct|string_format:"%.1f"}%
              </span>
            </td>
            <td class="text-right">
              <span class="{if $product.current_stock <= 0}text-danger{elseif $product.current_stock < 5}text-warning{/if}">
                {$product.current_stock|intval}
              </span>
            </td>
          </tr>
        {/foreach}
      {else}
        <tr>
          <td colspan="8" class="text-center text-muted" style="padding:30px;">
            <i class="icon-warning"></i> {l s='No hay datos para el período seleccionado.' mod='emsalesreport'}
          </td>
        </tr>
      {/if}
    </tbody>
  </table>
</div>

{* Paginación (generada por JS) *}
<div class="text-center" id="emsales-products-pagination"></div>
