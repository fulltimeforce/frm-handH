<?php
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Vehicle Gallery - Media Modal Filter
 * 
 * Filtrado de imágenes en el Media Modal del admin para vehículos.
 * Solo muestra imágenes adjuntas al vehículo actual o ya guardadas en la galería.
 * 
 * @package HandH
 * @subpackage Vehicles
 */

// Variable global para guardar IDs históricos de la galería
$GLOBALS['hnh_vehicle_gallery_keep_ids'] = [];

/**
 * Filtrar argumentos de la consulta de adjuntos en el Media Modal
 * Solo se activa cuando se abre la galería del campo ACF 'gallery_vehicle'
 */
add_filter('ajax_query_attachments_args', function ($args) {
  $post_id = isset($_REQUEST['post_id']) ? (int) $_REQUEST['post_id'] : 0;
  if (!$post_id)
    return $args;

  if (get_post_type($post_id) !== 'vehicles') {
    return $args;
  }

  // ✅ SOLO si viene nuestro flag (inyectado por JS)
  $scope = '';
  if (isset($_REQUEST['query']['vehicle_scope'])) {
    $scope = (string) $_REQUEST['query']['vehicle_scope'];
  } elseif (isset($_REQUEST['vehicle_scope'])) {
    $scope = (string) $_REQUEST['vehicle_scope'];
  }

  if ($scope !== 'gallery_vehicle') {
    return $args;
  }

  // ✅ IDs históricos ya guardados en el field gallery_vehicle
  // BEGIN
  $current_ids = get_field('gallery_vehicle', $post_id, false); // false = IDs
  if (!is_array($current_ids))
    $current_ids = [];
  $GLOBALS['hnh_vehicle_gallery_keep_ids'] = array_values(
    array_unique(
      array_map(
        'intval',
        $current_ids
      )
    )
  );
  // END

  // 🎯 objetivo
  // $args['post_parent'] = $post_id;
  $args['post_mime_type'] = 'image';

  /* No ponemos post_parent acá, porque vamos a hacer OR vía SQL en posts_clauses
  pero sí dejamos el post_id disponible:*/
  // BEGIN
  $args['hnh_vehicle_post_id'] = $post_id; // custom flag (no afecta WP)
  // END

  return $args;
});

/**
 * Modificar cláusulas SQL para filtrar imágenes
 * Muestra solo imágenes adjuntas al vehículo O ya guardadas en la galería
 */
add_filter('posts_clauses', function ($clauses, $query) {
  // ✅ Solo admin + AJAX
  if (!is_admin() || !wp_doing_ajax()) {
    return $clauses;
  }

  // ✅ Solo el endpoint real del media modal
  if (empty($_REQUEST['action']) || $_REQUEST['action'] !== 'query-attachments') {
    return $clauses;
  }

  // ✅ SOLO si viene nuestro scope (igual que en ajax_query_attachments_args)
  $scope = '';
  if (isset($_REQUEST['query']['vehicle_scope'])) {
    $scope = (string) $_REQUEST['query']['vehicle_scope'];
  } elseif (isset($_REQUEST['vehicle_scope'])) {
    $scope = (string) $_REQUEST['vehicle_scope'];
  }
  if ($scope !== 'gallery_vehicle') {
    return $clauses;
  }

  // ✅ Solo attachments
  if ($query->get('post_type') !== 'attachment') {
    return $clauses;
  }

  $post_id = (int) $query->get('hnh_vehicle_post_id');
  if (!$post_id) {
    return $clauses;
  }

  $keep_ids = $GLOBALS['hnh_vehicle_gallery_keep_ids'] ?? [];
  $keep_ids = array_filter(array_map('intval', (array) $keep_ids));

  global $wpdb;

  // WHERE original
  $where = $clauses['where'];

  // Condición 1: adjuntas al vehículo
  $cond_parent = $wpdb->prepare("{$wpdb->posts}.post_parent = %d", $post_id);

  // Condición 2: ya estaban en la galería (histórico)
  $cond_in = '';
  if (!empty($keep_ids)) {
    $ids_sql = implode(',', $keep_ids); // ints
    $cond_in = "{$wpdb->posts}.ID IN ($ids_sql)";
  }

  // Construimos OR seguro
  if ($cond_in) {
    $extra = " AND ( ($cond_parent) OR ($cond_in) ) ";
  } else {
    $extra = " AND ( ($cond_parent) ) ";
  }

  $clauses['where'] = $where . $extra;

  return $clauses;

}, 10, 2);

/**
 * Inyectar JavaScript para controlar el Media Modal
 * Intercepta la apertura del modal de galería y añade el flag vehicle_scope
 */
add_action('admin_enqueue_scripts', function ($hook) {
  if ($hook !== 'post.php' && $hook !== 'post-new.php')
    return;

  $screen = function_exists('get_current_screen') ? get_current_screen() : null;
  if (!$screen || $screen->post_type !== 'vehicles')
    return;

  // Asegura que el media modal y wp.media estén disponibles
  wp_enqueue_media();

  // Creamos un handle vacío para poder inyectar JS sin jQuery
  wp_register_script('vehicle-gallery-scope', false, [], '1.0.0', true);
  wp_enqueue_script('vehicle-gallery-scope');

  wp_add_inline_script(
    'vehicle-gallery-scope',
    <<<JS
(function () {
  // Flag global: indica que el modal fue abierto desde gallery_vehicle
  window.__vehicleGalleryScopeActive = false;

  // Modo del dropdown
  window.__vehicleGalleryMode = 'lot'; // 'lot' | 'global'

  var SELECT_ID = 'hnh-vehicle-gallery-mode';

  // Cambiar el texto del botón "Add to gallery" a "Add"
  function changeButtonText() {
    var btn = document.querySelector('.acf-field[data-name="gallery_vehicle"] .acf-gallery-add');
    if (btn && btn.textContent.trim() === 'Add to gallery') {
      btn.textContent = 'Add';
    }
  }

  // Inserta dropdown en el toolbar del modal (si no existe)
  function ensureModeDropdown() {
    
    var modal = document.getElementById('wp-media-modal');
    if (!modal) {
      console.warn('⚠️ Modal no encontrado en ensureModeDropdown');
      return;
    }

    var toolbar = modal.querySelector('.media-toolbar-secondary');
    if (!toolbar) {
      console.warn('⚠️ Toolbar no encontrado en modal');
      return;
    }

    // ya existe
    if (toolbar.querySelector('#' + SELECT_ID)) {
      console.log('ℹ️ Select ya existe en toolbar');
      return;
    }

    console.log('➕ Creando select en toolbar');

    var wrap = document.createElement('span');
    wrap.style.marginLeft = '8px';
    wrap.style.position = 'absolute';
    wrap.style.width = '400px';

    var label = document.createElement('label');
    label.setAttribute('for', SELECT_ID);
    label.className = 'screen-reader-text';
    label.textContent = 'Gallery mode';
    wrap.appendChild(label);

    var select = document.createElement('select');
    select.id = SELECT_ID;
    select.className = 'attachment-filters';
    select.innerHTML = ''
      + '<option value="lot">Lot Gallery</option>'
      + '<option value="global">Global Gallery</option>';

    // default Lot
    select.value = (window.__vehicleGalleryMode === 'global') ? 'global' : 'lot';
    console.log('✅ Select creado con valor:', select.value);

    select.addEventListener('change', function () {
      var newMode = select.value;
      console.log('🔀 Cambiando modo de', window.__vehicleGalleryMode, 'a', newMode);
      
      window.__vehicleGalleryMode = newMode;

      // Marcar que el select fue cambiado (agregar clase al modal)
      var modal = document.getElementById('wp-media-modal');
      if (modal) {
        modal.classList.add('hnh-select-changed');
        console.log('✅ Select cambiado - Clase agregada al modal');
      } else {
        console.warn('⚠️ Modal no encontrado al cambiar select');
      }

      // Verificar que wp.media esté disponible antes de refrescar
      if (!window.wp || !wp.media) {
        console.error('❌ wp.media no disponible - no se puede refrescar');
        alert('Error: El sistema de medios no está disponible. Por favor, cierra y vuelve a abrir el modal.');
        return;
      }

      // refrescar grilla
      refreshMediaLibrary();
    });

    wrap.appendChild(select);

    // lo metemos al final del toolbar secondary
    toolbar.appendChild(wrap);
    console.log('✅ Select insertado en toolbar correctamente');
  }

  // Intenta refrescar la grilla del Media Library sin recargar página
  function refreshMediaLibrary() {
    var refreshed = false;
    
    try {
      console.log('🔄 Intentando refrescar Media Library - Modo:', window.__vehicleGalleryMode);
      
      // Asegurar que estamos en la pestaña Media Library
      var browseTab = document.getElementById('menu-item-browse');
      if (browseTab) {
        browseTab.click();
        console.log('✅ Click en pestaña Browse');
      }

      // Requery del frame (cuando existe)
      if (window.wp && wp.media && wp.media.frame && wp.media.frame.content) {
        var content = wp.media.frame.content.get();
        if (content && content.collection) {
          console.log('📊 Collection encontrada');
          
          // truco: tocar props para forzar re-query
          content.collection.props.set({ _hnhRefresh: Date.now() });

          if (typeof content.collection._requery === 'function') {
            console.log('✅ Usando _requery()');
            content.collection._requery(true);
            refreshed = true;
          } else if (typeof content.collection.fetch === 'function') {
            console.log('✅ Usando fetch()');
            content.collection.fetch();
            refreshed = true;
          }
          
          if (refreshed) return;
        } else {
          console.warn('⚠️ Collection no disponible');
        }
      } else {
        console.warn('⚠️ wp.media.frame no disponible');
      }

      // fallback 1: disparar un input event en el search
      var search = document.getElementById('media-search-input');
      if (search) {
        console.log('🔍 Usando fallback: search input event');
        var ev = new Event('input', { bubbles: true });
        search.dispatchEvent(ev);
        
        // Pequeño hack: cambiar y restaurar valor para forzar requery
        var oldVal = search.value;
        search.value = oldVal + ' ';
        search.dispatchEvent(new Event('input', { bubbles: true }));
        setTimeout(function() {
          search.value = oldVal;
          search.dispatchEvent(new Event('input', { bubbles: true }));
        }, 50);
        refreshed = true;
        return;
      }

      // fallback 2 (último recurso): cerrar y reabrir modal
      if (!refreshed) {
        console.warn('⚠️ Fallbacks normales fallaron. Intentando reabrir modal...');
        reopenModal();
      }
      
    } catch (e) {
      console.error('❌ Error en refreshMediaLibrary:', e);
      // Último intento: reabrir modal
      reopenModal();
    }
  }

  // Cierra y reabre el modal para refrescar completamente
  function reopenModal() {
    try {
      console.log('🔄 Reabriendo modal...');
      
      var closeBtn = document.querySelector('.media-modal-close');
      if (closeBtn) {
        closeBtn.click();
        
        // Reabrir después de un delay
        setTimeout(function() {
          var addBtn = document.querySelector('.acf-field[data-name="gallery_vehicle"] .acf-gallery-add');
          if (addBtn) {
            console.log('✅ Reabriendo modal automáticamente');
            addBtn.click();
            
            // Restaurar el modo seleccionado
            setTimeout(function() {
              var select = document.getElementById(SELECT_ID);
              if (select && window.__vehicleGalleryMode) {
                select.value = window.__vehicleGalleryMode;
                console.log('✅ Modo restaurado:', window.__vehicleGalleryMode);
              }
            }, 100);
          }
        }, 300);
      }
    } catch (e) {
      console.error('❌ Error al reabrir modal:', e);
    }
  }

  // Observer para cuando apareceél modal (ACF lo crea dinámico)
  var modalObserver = new MutationObserver(function () {
    ensureModeDropdown();
  });

  function startObservingModal() {
    modalObserver.observe(document.body, { childList: true, subtree: true });
  }

  // Watchdog: verifica periódicamente que el modal esté en buen estado
  var watchdogInterval = null;
  function startWatchdog() {
    if (watchdogInterval) clearInterval(watchdogInterval);
    
    watchdogInterval = setInterval(function() {
      // Solo si el scope está activo
      if (!window.__vehicleGalleryScopeActive) return;
      
      var modal = document.getElementById('wp-media-modal');
      if (!modal) {
        // Modal cerrado - limpiar watchdog
        clearInterval(watchdogInterval);
        watchdogInterval = null;
        return;
      }
      
      // Verificar que el select exista
      var select = document.getElementById(SELECT_ID);
      if (!select) {
        console.warn('⚠️ Watchdog: Select desapareció - recreando');
        ensureModeDropdown();
      } else {
        // Verificar que el valor del select coincida con el modo actual
        if (select.value !== window.__vehicleGalleryMode) {
          console.warn('⚠️ Watchdog: Select desincronizado - corrigiendo');
          select.value = window.__vehicleGalleryMode;
        }
      }
    }, 2000); // Cada 2 segundos
  }

  function stopWatchdog() {
    if (watchdogInterval) {
      clearInterval(watchdogInterval);
      watchdogInterval = null;
      console.log('🛑 Watchdog detenido');
    }
  }

  // 1) Activar SOLO cuando el click viene del botón Add del field gallery_vehicle
  document.addEventListener('click', function (e) {
    var btn = e.target.closest('.acf-field[data-name="gallery_vehicle"] .acf-gallery-add');
    if (!btn) return;

    // Post ID existe?
    var postIdEl = document.getElementById('post_ID');
    var postId = postIdEl ? parseInt(postIdEl.value || '0', 10) : 0;

    if (!postId) {
      e.preventDefault();
      alert('Guardá el vehículo como borrador antes de subir imágenes a la galería.');
      window.__vehicleGalleryScopeActive = false;
      return;
    }

    // default cada vez que abre: Lot Gallery
    window.__vehicleGalleryMode = 'lot';
    window.__vehicleGalleryScopeActive = true;
    
    console.log('🚀 Abriendo modal - Modo inicial: Lot Gallery');
    
    // Iniciar watchdog
    startWatchdog();

    // el modal aparece después: lo inyectamos apenas exista
    // Intentar varias veces para asegurar que se agrega
    var attempts = 0;
    var maxAttempts = 10;
    var retryInterval = setInterval(function() {
      attempts++;
      var modalExists = document.getElementById('wp-media-modal');
      
      if (modalExists) {
        console.log('✅ Modal detectado en intento', attempts);
        ensureModeDropdown();
        
        // RESETEAR: Quitar clase al abrir el modal
        modalExists.classList.remove('hnh-select-changed');
        console.log('🔄 Modal abierto - Clase reseteada');
        
        clearInterval(retryInterval);
      } else if (attempts >= maxAttempts) {
        console.warn('⚠️ Modal no detectado después de', maxAttempts, 'intentos');
        clearInterval(retryInterval);
      }
    }, 50);
  }, true);

  // 2) Apagar al cerrar modal (cualquier cierre)
  document.addEventListener('click', function (e) {
    if (
      e.target.closest('.media-modal-close') ||
      e.target.classList.contains('media-modal-backdrop') ||
      e.target.closest('.media-button')
    ) {
      console.log('❌ Cerrando modal');
      window.__vehicleGalleryScopeActive = false;
      stopWatchdog();
    }
  }, true);

  // 3) Interceptar requests (query-attachments y uploads)
  var originalSend = XMLHttpRequest.prototype.send;

  function isUploadRequest(body) {
    if (typeof body === 'string') {
      return body.indexOf('action=upload-attachment') !== -1 || body.indexOf('action=async-upload') !== -1;
    }
    if (body instanceof FormData) {
      var action = body.get('action');
      return action === 'upload-attachment' || action === 'async-upload';
    }
    return false;
  }

  XMLHttpRequest.prototype.send = function (body) {
    try {
      if (!window.__vehicleGalleryScopeActive) {
        return originalSend.call(this, body);
      }

      // Query attachments
      if (typeof body === 'string' && body.indexOf('action=query-attachments') !== -1) {
        console.log('🔍 Query attachments detectado - Modo actual:', window.__vehicleGalleryMode);
        
        // LOT => inyecta scope y aplica filtro server-side
        if (window.__vehicleGalleryMode === 'lot') {
          if (body.indexOf('vehicle_scope') === -1) {
            body += '&query[vehicle_scope]=gallery_vehicle';
            console.log('✅ Scope inyectado para filtrado Lot');
          } else {
            console.log('ℹ️ Scope ya presente en query');
          }
        } else {
          console.log('ℹ️ Modo Global - sin filtrado');
        }
        // GLOBAL => NO inyectamos nada => comportamiento normal (sin tu filtro)
      }

      // Detectar uploads de imágenes
      if (isUploadRequest(body)) {
        console.log('📤 Upload detectado');
        var xhr = this;
        xhr.addEventListener('loadend', function () {
          // Upload completado exitosamente
          if (xhr.status >= 200 && xhr.status < 300) {
            console.log('✅ Upload completado exitosamente');
            
            // SOLO simular evento change si el modal tiene la clase 'hnh-select-changed'
            var modal = document.getElementById('wp-media-modal');
            if (modal && modal.classList.contains('hnh-select-changed')) {
              console.log('🔄 Modal tiene clase hnh-select-changed - Disparando refresh');
              
              setTimeout(function () {
                var select = document.getElementById(SELECT_ID);
                if (select) {
                  // Verificar que el select siga existiendo y esté visible
                  if (select.offsetParent !== null) {
                    var event = new Event('change', { bubbles: true });
                    select.dispatchEvent(event);
                    console.log('✅ Evento change disparado en select');
                  } else {
                    console.warn('⚠️ Select no visible - no se dispara evento');
                  }
                } else {
                  console.warn('⚠️ Select no encontrado después de upload');
                }
              }, 500);
            } else {
              console.log('ℹ️ Modal NO tiene clase hnh-select-changed - No se dispara refresh');
            }
          } else {
            console.warn('⚠️ Upload falló con status:', xhr.status);
          }
        }, { once: true });
      }

      return originalSend.call(this, body);
    } catch (err) {
      return originalSend.call(this, body);
    }
  };

  // Init
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
      changeButtonText();
      startObservingModal();
    });
  } else {
    changeButtonText();
    startObservingModal();
  }

  // Si ACF vuelve a pintar el field, seguimos cambiando texto
  var fieldObserver = new MutationObserver(function () {
    changeButtonText();
  });

  setTimeout(function () {
    var acfField = document.querySelector('.acf-field[data-name="gallery_vehicle"]');
    if (acfField) {
      fieldObserver.observe(acfField, { childList: true, subtree: true });
    }
  }, 100);

})();
JS
    ,
    'after'
  );
});
