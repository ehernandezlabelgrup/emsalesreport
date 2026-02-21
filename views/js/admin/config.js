/**
 * Sales Report Pro — Página de configuración del módulo
 * Toggle visual de los checkboxes de estados de pedido.
 * Cargado via hookDisplayBackOfficeHeader solo cuando configure=emsalesreport
 */
(function () {
  'use strict';

  function applyStateLabel(checkbox) {
    var label = checkbox.closest ? checkbox.closest('label.emsales-state-label')
                                 : null;
    if (!label) { return; }

    var color     = label.getAttribute('data-color')     || '#4e73df';
    var textColor = label.getAttribute('data-textcolor') || '#ffffff';

    if (checkbox.checked) {
      label.style.background   = color;
      label.style.borderColor  = color;
      label.style.color        = textColor;
    } else {
      label.style.background   = '#ffffff';
      label.style.borderColor  = '#dddddd';
      label.style.color        = '#555555';
    }
  }

  function init() {
    var container = document.querySelector('.emsales-state-selector');
    if (!container) { return; }

    // Aplicar estilos iniciales (por si el PHP no los puso bien)
    container.querySelectorAll('input[type="checkbox"]').forEach(function (cb) {
      applyStateLabel(cb);
    });

    // Event delegation — un solo listener para todos los checkboxes
    container.addEventListener('change', function (e) {
      if (e.target && e.target.type === 'checkbox') {
        applyStateLabel(e.target);
      }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
}());
