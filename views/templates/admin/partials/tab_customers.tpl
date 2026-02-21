{**
 * Sales Report Pro — Pestaña: Clientes
 *}

{* Barra de herramientas *}
<div class="row emsales-tab-toolbar" style="margin-bottom:15px; margin-top:10px;">
  <div class="col-md-9">
    <span class="emsales-table-count text-muted">
      {l s='Total clientes:' mod='emsalesreport'}
      <strong id="emsales-customers-total">{$customers_data.total|intval}</strong>
    </span>
  </div>
  <div class="col-md-3 text-right">
    <div class="btn-group">
      <button type="button" class="btn btn-default btn-sm emsales-export-btn"
              data-tab="customers" data-format="csv">
        <i class="icon-file-text-o"></i> CSV
      </button>
      <button type="button" class="btn btn-default btn-sm emsales-export-btn"
              data-tab="customers" data-format="excel">
        <i class="icon-file-excel-o"></i> Excel
      </button>
    </div>
  </div>
</div>

{* Indicador de carga *}
<div id="emsales-customers-loading" class="emsales-loading" style="display:none; text-align:center; padding:20px;">
  <i class="icon-spinner icon-spin icon-2x"></i>
</div>

{* Tabla *}
<div class="table-responsive" id="emsales-customers-table-wrapper">
  <table class="table table-striped table-hover emsales-data-table" id="emsales-customers-table"
         data-tab="customers">
    <thead>
      <tr>
        <th class="emsales-sortable" data-sort="c.lastname">
          {l s='Cliente' mod='emsalesreport'} <i class="icon-sort"></i>
        </th>
        <th class="emsales-sortable" data-sort="c.email">
          {l s='Email' mod='emsalesreport'} <i class="icon-sort"></i>
        </th>
        <th class="emsales-sortable" data-sort="c.company">
          {l s='Empresa' mod='emsalesreport'} <i class="icon-sort"></i>
        </th>
        <th class="emsales-sortable text-right" data-sort="total_orders">
          {l s='Pedidos' mod='emsalesreport'} <i class="icon-sort"></i>
        </th>
        <th class="emsales-sortable text-right" data-sort="total_spent">
          {l s='Total' mod='emsalesreport'} <i class="icon-sort-down"></i>
        </th>
        <th class="emsales-sortable text-right" data-sort="avg_ticket">
          {l s='Ticket Medio' mod='emsalesreport'} <i class="icon-sort"></i>
        </th>
        <th class="emsales-sortable" data-sort="last_order_date">
          {l s='Último Pedido' mod='emsalesreport'} <i class="icon-sort"></i>
        </th>
      </tr>
    </thead>
    <tbody id="emsales-customers-tbody">
      {if $customers_data.items|count > 0}
        {foreach from=$customers_data.items item=customer}
          {assign var="is_vip" value=($customer.total_spent > $customer.avg_ticket * 10)}
          <tr>
            <td>
              {$customer.firstname|escape:'html'} {$customer.lastname|escape:'html'}
              {if $is_vip}
                <span class="badge badge-warning" title="{l s='Cliente VIP' mod='emsalesreport'}">
                  ★ VIP
                </span>
              {/if}
            </td>
            <td>
              <a href="mailto:{$customer.email|escape:'html'}" style="font-size:.85em;">
                {$customer.email|escape:'html'}
              </a>
            </td>
            <td>{$customer.company|escape:'html'}</td>
            <td class="text-right">{$customer.total_orders|intval}</td>
            <td class="text-right">
              <strong>{$customer.total_spent|string_format:"%.2f"}&nbsp;{$currency_sign}</strong>
            </td>
            <td class="text-right">
              {$customer.avg_ticket|string_format:"%.2f"}&nbsp;{$currency_sign}
            </td>
            <td>
              <span class="text-muted" style="font-size:.85em;">
                {$customer.last_order_date|date_format:"%d/%m/%Y"}
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

{* Paginación *}
<div class="text-center" id="emsales-customers-pagination"></div>
