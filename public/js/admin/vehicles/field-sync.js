/**
 * Sincronización de campos ACF en tiempo real
 * Registration No → VRN
 * Chassis No → VIN
 */
(function () {
  'use strict';

  // IDs de los campos fuente
  const SOURCE_FIELDS = {
    registration: '#gallery_registration_no input[type="text"]',
    chassis: '#gallery_chassis_no input[type="text"]'
  };

  // IDs de los campos destino (serán readonly)
  const TARGET_FIELDS = {
    vrn: '#cc_vrn input[type="text"]',
    vin: '#cc_vin input[type="text"]'
  };

  /**
   * Inicializar sincronización cuando ACF esté listo
   */
  function init() {
    // Esperar a que ACF cargue los campos
    if (typeof acf === 'undefined') {
      setTimeout(init, 100);
      return;
    }

    // Dar tiempo a que ACF renderice completamente
    setTimeout(setupFieldSync, 300);
  }

  /**
   * Configurar la sincronización de campos
   */
  function setupFieldSync() {
    const sourceRegInput = document.querySelector(SOURCE_FIELDS.registration);
    const sourceChassisInput = document.querySelector(SOURCE_FIELDS.chassis);
    const targetVrnInput = document.querySelector(TARGET_FIELDS.vrn);
    const targetVinInput = document.querySelector(TARGET_FIELDS.vin);

    // Validar que todos los campos existen
    if (!sourceRegInput || !sourceChassisInput || !targetVrnInput || !targetVinInput) {
      console.warn('Field sync: No se encontraron todos los campos necesarios');
      return;
    }

    // Deshabilitar edición de campos destino
    targetVrnInput.setAttribute('readonly', 'readonly');
    targetVinInput.setAttribute('readonly', 'readonly');
    
    // Añadir estilos visuales para indicar que son readonly
    targetVrnInput.style.backgroundColor = '#f5f5f5';
    targetVrnInput.style.cursor = 'default';
    targetVinInput.style.backgroundColor = '#f5f5f5';
    targetVinInput.style.cursor = 'default';

    // Sincronizar valor inicial
    syncField(sourceRegInput, targetVrnInput);
    syncField(sourceChassisInput, targetVinInput);

    // Escuchar cambios en tiempo real (input detecta typing, paste, etc.)
    sourceRegInput.addEventListener('input', function () {
      syncField(sourceRegInput, targetVrnInput);
    });

    sourceChassisInput.addEventListener('input', function () {
      syncField(sourceChassisInput, targetVinInput);
    });

    // También escuchar eventos de cambio (por si ACF hace actualizaciones)
    sourceRegInput.addEventListener('change', function () {
      syncField(sourceRegInput, targetVrnInput);
    });

    sourceChassisInput.addEventListener('change', function () {
      syncField(sourceChassisInput, targetVinInput);
    });

    console.log('✓ Field sync inicializado correctamente');
  }

  /**
   * Sincronizar valor de un campo a otro
   */
  function syncField(sourceInput, targetInput) {
    const value = sourceInput.value || '';
    targetInput.value = value;

    // Disparar evento change para que ACF detecte el cambio
    const event = new Event('change', { bubbles: true });
    targetInput.dispatchEvent(event);
  }

  // Iniciar cuando el DOM esté listo
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
