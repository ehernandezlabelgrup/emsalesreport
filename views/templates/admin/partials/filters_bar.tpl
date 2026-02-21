{**
 * Sales Report Pro — Barra de filtros de fechas
 *}
<div class="panel" id="emsales-filters">
  <div class="panel-heading">
    <i class="icon-filter"></i> {l s='Filtros' mod='emsalesreport'}
  </div>
  <div class="panel-body">
    <div class="row">

      {* Fecha desde *}
      <div class="col-md-2">
        <label for="emsales-date-from">{l s='Desde' mod='emsalesreport'}</label>
        <input type="date"
               id="emsales-date-from"
               value="{$date_from|escape:'html'}"
               class="form-control">
      </div>

      {* Fecha hasta *}
      <div class="col-md-2">
        <label for="emsales-date-to">{l s='Hasta' mod='emsalesreport'}</label>
        <input type="date"
               id="emsales-date-to"
               value="{$date_to|escape:'html'}"
               class="form-control">
      </div>

      {* Períodos rápidos *}
      <div class="col-md-6">
        <label>{l s='Período rápido' mod='emsalesreport'}</label>
        <div class="btn-group" id="emsales-presets" style="display:block;">
          <button type="button" class="btn btn-default emsales-preset" data-days="7">
            {l s='7 días' mod='emsalesreport'}
          </button>
          <button type="button" class="btn btn-default emsales-preset" data-days="14">
            {l s='14 días' mod='emsalesreport'}
          </button>
          <button type="button" class="btn btn-default emsales-preset active" data-days="30">
            {l s='30 días' mod='emsalesreport'}
          </button>
          <button type="button" class="btn btn-default emsales-preset" data-days="90">
            {l s='90 días' mod='emsalesreport'}
          </button>
          <button type="button" class="btn btn-default emsales-preset" data-days="365">
            {l s='1 año' mod='emsalesreport'}
          </button>
          <button type="button" class="btn btn-default emsales-preset" data-period="month">
            {l s='Este mes' mod='emsalesreport'}
          </button>
          <button type="button" class="btn btn-default emsales-preset" data-period="year">
            {l s='Este año' mod='emsalesreport'}
          </button>
        </div>
      </div>

      {* Botón aplicar *}
      <div class="col-md-2">
        <label>&nbsp;</label>
        <button type="button" class="btn btn-primary btn-block" id="emsales-apply-filters">
          <i class="icon-refresh"></i> {l s='Aplicar' mod='emsalesreport'}
        </button>
      </div>

    </div>
  </div>
</div>
