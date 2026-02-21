{**
 * Sales Report Pro — Tarjetas de KPIs
 * Fila 1: KPIs principales (revenue, orders, avg_ticket, gross_margin)
 * Fila 2: KPIs secundarios (margin_pct, unique_customers, products_sold, avg_products_per_order)
 *}

{* Helper local: clase CSS según dirección de variación *}
{* up=verde, down=rojo, neutral=gris *}

<div id="emsales-kpis">

  {* === FILA 1: KPIs principales === *}
  <div class="row emsales-kpi-row" id="emsales-kpi-main">

    {* ---- Ingresos totales ---- *}
    <div class="col-md-3 col-sm-6">
      <div class="emsales-kpi-card{if $compare_enabled && isset($variations.total_revenue)} kpi-{$variations.total_revenue.direction}{/if}"
           id="kpi-card-revenue">
        <div class="kpi-icon">
          <i class="material-icons">monetization_on</i>
        </div>
        <div class="kpi-content">
          <span class="kpi-label">{l s='Ingresos' mod='emsalesreport'}</span>
          <span class="kpi-value" id="kpi-val-revenue">
            {$kpis.total_revenue|string_format:"%.2f"}&nbsp;{$currency_sign}
          </span>
          {if $compare_enabled && isset($variations.total_revenue)}
            <span class="kpi-variation kpi-{$variations.total_revenue.direction}" id="kpi-var-revenue">
              {if $variations.total_revenue.direction == 'up'}&#8593;{elseif $variations.total_revenue.direction == 'down'}&#8595;{/if}
              {$variations.total_revenue.formatted}
            </span>
          {/if}
        </div>
      </div>
    </div>

    {* ---- Pedidos ---- *}
    <div class="col-md-3 col-sm-6">
      <div class="emsales-kpi-card{if $compare_enabled && isset($variations.total_orders)} kpi-{$variations.total_orders.direction}{/if}"
           id="kpi-card-orders">
        <div class="kpi-icon">
          <i class="material-icons">shopping_cart</i>
        </div>
        <div class="kpi-content">
          <span class="kpi-label">{l s='Pedidos' mod='emsalesreport'}</span>
          <span class="kpi-value" id="kpi-val-orders">
            {$kpis.total_orders}
          </span>
          {if $compare_enabled && isset($variations.total_orders)}
            <span class="kpi-variation kpi-{$variations.total_orders.direction}" id="kpi-var-orders">
              {if $variations.total_orders.direction == 'up'}&#8593;{elseif $variations.total_orders.direction == 'down'}&#8595;{/if}
              {$variations.total_orders.formatted}
            </span>
          {/if}
        </div>
      </div>
    </div>

    {* ---- Ticket medio ---- *}
    <div class="col-md-3 col-sm-6">
      <div class="emsales-kpi-card{if $compare_enabled && isset($variations.avg_ticket)} kpi-{$variations.avg_ticket.direction}{/if}"
           id="kpi-card-avg-ticket">
        <div class="kpi-icon">
          <i class="material-icons">receipt</i>
        </div>
        <div class="kpi-content">
          <span class="kpi-label">{l s='Ticket medio' mod='emsalesreport'}</span>
          <span class="kpi-value" id="kpi-val-avg-ticket">
            {$kpis.avg_ticket|string_format:"%.2f"}&nbsp;{$currency_sign}
          </span>
          {if $compare_enabled && isset($variations.avg_ticket)}
            <span class="kpi-variation kpi-{$variations.avg_ticket.direction}" id="kpi-var-avg-ticket">
              {if $variations.avg_ticket.direction == 'up'}&#8593;{elseif $variations.avg_ticket.direction == 'down'}&#8595;{/if}
              {$variations.avg_ticket.formatted}
            </span>
          {/if}
        </div>
      </div>
    </div>

    {* ---- Margen bruto ---- *}
    <div class="col-md-3 col-sm-6">
      <div class="emsales-kpi-card{if $compare_enabled && isset($variations.gross_margin)} kpi-{$variations.gross_margin.direction}{/if}"
           id="kpi-card-margin">
        <div class="kpi-icon">
          <i class="material-icons">trending_up</i>
        </div>
        <div class="kpi-content">
          <span class="kpi-label">{l s='Margen bruto' mod='emsalesreport'}</span>
          <span class="kpi-value" id="kpi-val-margin">
            {$kpis.gross_margin|string_format:"%.2f"}&nbsp;{$currency_sign}
          </span>
          {if $compare_enabled && isset($variations.gross_margin)}
            <span class="kpi-variation kpi-{$variations.gross_margin.direction}" id="kpi-var-margin">
              {if $variations.gross_margin.direction == 'up'}&#8593;{elseif $variations.gross_margin.direction == 'down'}&#8595;{/if}
              {$variations.gross_margin.formatted}
            </span>
          {/if}
        </div>
      </div>
    </div>

  </div>{* /row fila 1 *}

  {* === FILA 2: KPIs secundarios === *}
  <div class="row emsales-kpi-row emsales-kpi-secondary" id="emsales-kpi-secondary">

    {* ---- Margen % ---- *}
    <div class="col-md-3 col-sm-6">
      <div class="emsales-kpi-card emsales-kpi-sm{if $compare_enabled && isset($variations.margin_pct)} kpi-{$variations.margin_pct.direction}{/if}"
           id="kpi-card-margin-pct">
        <div class="kpi-icon"><i class="material-icons">percent</i></div>
        <div class="kpi-content">
          <span class="kpi-label">{l s='Margen %' mod='emsalesreport'}</span>
          <span class="kpi-value" id="kpi-val-margin-pct">
            {$kpis.margin_pct|string_format:"%.1f"}%
          </span>
          {if $compare_enabled && isset($variations.margin_pct)}
            <span class="kpi-variation kpi-{$variations.margin_pct.direction}" id="kpi-var-margin-pct">
              {if $variations.margin_pct.direction == 'up'}&#8593;{elseif $variations.margin_pct.direction == 'down'}&#8595;{/if}
              {$variations.margin_pct.formatted}
            </span>
          {/if}
        </div>
      </div>
    </div>

    {* ---- Clientes únicos ---- *}
    <div class="col-md-3 col-sm-6">
      <div class="emsales-kpi-card emsales-kpi-sm{if $compare_enabled && isset($variations.unique_customers)} kpi-{$variations.unique_customers.direction}{/if}"
           id="kpi-card-customers">
        <div class="kpi-icon"><i class="material-icons">person</i></div>
        <div class="kpi-content">
          <span class="kpi-label">{l s='Clientes únicos' mod='emsalesreport'}</span>
          <span class="kpi-value" id="kpi-val-customers">
            {$kpis.unique_customers}
          </span>
          {if $compare_enabled && isset($variations.unique_customers)}
            <span class="kpi-variation kpi-{$variations.unique_customers.direction}" id="kpi-var-customers">
              {if $variations.unique_customers.direction == 'up'}&#8593;{elseif $variations.unique_customers.direction == 'down'}&#8595;{/if}
              {$variations.unique_customers.formatted}
            </span>
          {/if}
        </div>
      </div>
    </div>

    {* ---- Unidades vendidas ---- *}
    <div class="col-md-3 col-sm-6">
      <div class="emsales-kpi-card emsales-kpi-sm{if $compare_enabled && isset($variations.total_products_sold)} kpi-{$variations.total_products_sold.direction}{/if}"
           id="kpi-card-units">
        <div class="kpi-icon"><i class="material-icons">inventory_2</i></div>
        <div class="kpi-content">
          <span class="kpi-label">{l s='Uds. vendidas' mod='emsalesreport'}</span>
          <span class="kpi-value" id="kpi-val-units">
            {$kpis.total_products_sold}
          </span>
          {if $compare_enabled && isset($variations.total_products_sold)}
            <span class="kpi-variation kpi-{$variations.total_products_sold.direction}" id="kpi-var-units">
              {if $variations.total_products_sold.direction == 'up'}&#8593;{elseif $variations.total_products_sold.direction == 'down'}&#8595;{/if}
              {$variations.total_products_sold.formatted}
            </span>
          {/if}
        </div>
      </div>
    </div>

    {* ---- Media productos por pedido ---- *}
    <div class="col-md-3 col-sm-6">
      <div class="emsales-kpi-card emsales-kpi-sm" id="kpi-card-avg-products">
        <div class="kpi-icon"><i class="material-icons">layers</i></div>
        <div class="kpi-content">
          <span class="kpi-label">{l s='Prod./pedido' mod='emsalesreport'}</span>
          <span class="kpi-value" id="kpi-val-avg-products">
            {$kpis.avg_products_per_order|string_format:"%.1f"}
          </span>
        </div>
      </div>
    </div>

  </div>{* /row fila 2 *}

</div>{* /emsales-kpis *}
