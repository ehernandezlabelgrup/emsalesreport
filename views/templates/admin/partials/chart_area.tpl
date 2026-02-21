{**
 * Sales Report Pro — Área del gráfico principal (Chart.js)
 *}
<div class="panel" id="emsales-chart-panel">
  <div class="panel-heading">
    <i class="icon-bar-chart"></i> {l s='Evolución de ventas' mod='emsalesreport'}

    <div class="pull-right" id="emsales-chart-controls">

      {* Selector de métrica *}
      <div class="btn-group btn-group-sm" id="emsales-chart-metrics">
        <button type="button" class="btn btn-default emsales-chart-metric active" data-metric="revenue"
                title="{l s='Ingresos' mod='emsalesreport'}">
          {l s='Ingresos' mod='emsalesreport'}
        </button>
        <button type="button" class="btn btn-default emsales-chart-metric" data-metric="orders"
                title="{l s='Pedidos' mod='emsalesreport'}">
          {l s='Pedidos' mod='emsalesreport'}
        </button>
        <button type="button" class="btn btn-default emsales-chart-metric" data-metric="avg_ticket"
                title="{l s='Ticket medio' mod='emsalesreport'}">
          {l s='Ticket medio' mod='emsalesreport'}
        </button>
      </div>

      {* Selector de tipo de gráfico *}
      <div class="btn-group btn-group-sm" id="emsales-chart-types" style="margin-left:8px;">
        <button type="button" class="btn btn-default emsales-chart-type{if $chart_type == 'line'} active{/if}"
                data-type="line" title="{l s='Línea' mod='emsalesreport'}">
          <i class="icon-line-chart"></i>
        </button>
        <button type="button" class="btn btn-default emsales-chart-type{if $chart_type == 'bar'} active{/if}"
                data-type="bar" title="{l s='Barras' mod='emsalesreport'}">
          <i class="icon-bar-chart"></i>
        </button>
      </div>

      {* Selector de granularidad *}
      <div class="btn-group btn-group-sm" id="emsales-granularity" style="margin-left:8px;">
        <button type="button" class="btn btn-default emsales-gran{if $granularity == 'day'} active{/if}"
                data-gran="day">{l s='Día' mod='emsalesreport'}</button>
        <button type="button" class="btn btn-default emsales-gran{if $granularity == 'week'} active{/if}"
                data-gran="week">{l s='Semana' mod='emsalesreport'}</button>
        <button type="button" class="btn btn-default emsales-gran{if $granularity == 'month'} active{/if}"
                data-gran="month">{l s='Mes' mod='emsalesreport'}</button>
      </div>

    </div>{* /pull-right *}
  </div>

  <div class="panel-body" id="emsales-chart-body">
    <div id="emsales-chart-loading" style="display:none; text-align:center; padding:20px;">
      <i class="icon-spinner icon-spin icon-2x"></i>
    </div>
    <canvas id="emsales-main-chart" style="max-height:320px;"></canvas>
  </div>
</div>
