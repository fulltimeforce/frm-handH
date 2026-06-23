(() => {
  const cfg = window.hnhGalleryReplace || {};
  const FIELD_NAME = cfg.fieldName || "gallery_vehicle";

  const state = {
    active: false,
    postId: 0,
    startedAt: 0, // unix seconds
    selectedIds: [], // IDs de las imágenes seleccionadas a reemplazar
    selectedIndexes: [], // índices de las seleccionadas
    minIndex: 0, // índice menor de las seleccionadas
    preOpenIds: [], // IDs en galería antes de abrir modal (para detectar nuevas)
    uploadedIds: [], // ids recién subidos (capturados cuando WP los marca selected)
    saving: false, // flag para prevenir race conditions en guardado AJAX
    autoSelectTimer: null, // timer para auto-select del botón Select
    autoSelectDone: false, // flag para prevenir múltiples auto-selects
    autoSelectRetries: 0, // contador de reintentos
    autoSelectMaxRetries: 30, // 30 * 200ms = 6s max
  };

  const STYLE_ID = "hnh-gallery-replace-style";

  let originalMediaTitle = null;
  function setMediaModalTitle(title) {
    const titleEl =
      document.querySelector(".media-frame-title h1") ||
      document.querySelector("#media-frame-title"); // fallback raro

    if (!titleEl) return;

    // Guardamos el original solo 1 vez por sesión de modal
    if (originalMediaTitle === null) {
      originalMediaTitle = titleEl.textContent;
    }

    titleEl.textContent = title;
  }
  function restoreMediaModalTitle() {
    if (originalMediaTitle === null) return;

    const titleEl =
      document.querySelector(".media-frame-title h1") ||
      document.querySelector("#media-frame-title");

    if (!titleEl) return;

    titleEl.textContent = originalMediaTitle;
    originalMediaTitle = null;
  }

  /**
   * Atajo para querySelector
   * @param {string} sel - Selector CSS
   * @param {Document|Element} root - Elemento raíz donde buscar (por defecto document)
   * @returns {Element|null} El primer elemento que coincida o null
   */
  function $(sel, root = document) {
    return root.querySelector(sel);
  }

  /**
   * Atajo para querySelectorAll que retorna un array
   * @param {string} sel - Selector CSS
   * @param {Document|Element} root - Elemento raíz donde buscar (por defecto document)
   * @returns {Element[]} Array de elementos que coinciden con el selector
   */
  function $all(sel, root = document) {
    return Array.from(root.querySelectorAll(sel));
  }

  function injectStyles() {
    if (document.getElementById(STYLE_ID)) return;

    const style = document.createElement("style");
    style.id = STYLE_ID;
    style.textContent = `
      /* Replace mode: ocultar pestaña Media Library (UI). Igual nosotros filtramos por backend */
      body.hnh-replace-mode .media-router .media-menu-item,
      body.hnh-replace-mode .media-menu .media-menu-item {
        display: none !important;
      }

      /* Mostrar solo "Upload files" si WP lo marca con data-mode */
      body.hnh-replace-mode .media-router .media-menu-item[data-mode="upload"],
      body.hnh-replace-mode .media-menu .media-menu-item[data-mode="upload"]{
        display: inline-block !important;
      }

      /* Deshabilitar botón Add cuando hay items seleccionados */
      .acf-gallery-add[disabled] {
        pointer-events: none !important;
        opacity: 0.5 !important;
        cursor: not-allowed !important;
      }
      .media-frame-toolbar #hnh-vehicle-gallery-mode{
        display: none !important;
      }
    `;
    document.head.appendChild(style);
  }

  /**
   * Obtiene el elemento del campo ACF gallery_vehicle
   * @returns {HTMLElement|null} El elemento del campo o null si no existe
   */
  function getFieldEl() {
    return $(`.acf-field[data-name="${FIELD_NAME}"]`);
  }

  /**
   * Obtiene el ID del post actual del vehículo
   *
   * Busca el ID en dos lugares (en orden de prioridad):
   * 1. Input hidden #post_ID en el DOM (valor en tiempo real de WordPress)
   * 2. cfg.postId pasado desde PHP via wp_localize_script (fallback)
   *
   * @returns {number} El ID del post, o 0 si es un post nuevo sin guardar
   *
   * @example
   * const id = getPostId();
   * if (!id) {
   *   alert("Guardá el vehículo antes de continuar");
   * }
   */
  function getPostId() {
    // preferimos el real del input (si existe)
    const el = document.getElementById("post_ID");
    const id = el ? parseInt(el.value || "0", 10) : 0;
    return id || (cfg.postId ? parseInt(cfg.postId, 10) : 0);
  }

  function getCurrentGalleryIds(fieldEl) {
    return $all(".acf-gallery-attachment[data-id]", fieldEl)
      .map((it) => parseInt(it.getAttribute("data-id") || "0", 10))
      .filter(Boolean);
  }

  function addReplaceButton(fieldEl) {
    // ya existe?
    if ($(".hnh-gallery-replace-btn", fieldEl)) return;

    const addBtn = $(".acf-gallery-add", fieldEl);
    if (!addBtn) return;

    const deleteBtn = $(".acf-bulk-remove-selected", fieldEl); // tu botón ya existe si tu otro JS corrió
    const anchorLi = deleteBtn ? deleteBtn.closest("li") : addBtn.closest("li");
    if (!anchorLi) return;

    const li = document.createElement("li");
    const btn = document.createElement("button");
    btn.type = "button";
    btn.className = "button button-secondary hnh-gallery-replace-btn";
    btn.textContent = "Replace";
    btn.style.marginLeft = "8px";
    btn.setAttribute("disabled", ""); // Disabled por defecto

    li.appendChild(btn);
    anchorLi.insertAdjacentElement("afterend", li);

    btn.addEventListener("click", (e) => {
      e.preventDefault();

      const postId = getPostId();
      if (!postId) {
        alert("Guardá el vehículo como borrador antes de reemplazar imágenes.");
        return;
      }

      // Obtener items actuales en orden
      const attachmentsEl = $(".acf-gallery-attachments", fieldEl);
      if (!attachmentsEl) return;

      const items = Array.from(
        attachmentsEl.querySelectorAll(".acf-gallery-attachment[data-id]"),
      );

      // Obtener seleccionados
      const selected = items.filter((it) =>
        it.classList.contains("is-bulk-selected"),
      );

      if (!selected.length) {
        // No debería pasar si el botón está disabled correctamente
        return;
      }

      // Calcular estado para la sesión Replace
      state.selectedIds = selected.map((it) =>
        parseInt(it.getAttribute("data-id") || "0", 10),
      );
      state.selectedIndexes = selected.map((it) => items.indexOf(it));
      state.minIndex = Math.min(...state.selectedIndexes);

      // Guardar foto del estado ANTES de abrir modal
      state.preOpenIds = items.map((it) =>
        parseInt(it.getAttribute("data-id") || "0", 10),
      );

      state.active = true;
      state.postId = postId;
      state.startedAt = Math.floor(Date.now() / 1000);
      state.uploadedIds = [];

      // Activamos replace mode visual + dejamos el frame en "lot" para que el upload quede adjunto al post
      injectStyles();
      document.body.classList.add("hnh-replace-mode");

      // Marcamos un flag global para nuestros intercepts
      window.__hnhVehicleGalleryReplaceActive = true;

      // Abrimos modal usando el Add de ACF (así no rompemos el workflow de ACF)
      addBtn.click();

      // best effort: forzar tab upload (si existe el botón)
      setTimeout(forceUploadTab, 50);
    });
  }

  function forceUploadTab() {
    if (!state.active) return;

    const modal =
      document.getElementById("wp-media-modal") || $(".media-modal");
    if (!modal) return;

    // Si existe link con data-mode upload
    const uploadTab =
      modal.querySelector(
        '.media-router .media-menu-item[data-mode="upload"]',
      ) ||
      modal.querySelector('.media-menu .media-menu-item[data-mode="upload"]');

    if (uploadTab) {
      uploadTab.click();
      return;
    }

    // Fallback por texto (según idioma puede variar)
    const items = $all(
      ".media-router .media-menu-item, .media-menu .media-menu-item",
      modal,
    );
    const byText = items.find(
      (a) => (a.textContent || "").trim().toLowerCase() === "upload files",
    );
    if (byText) byText.click();
  }

  /**
   * Captura IDs recién subidos cuando WP los marca selected en la grilla
   */
  function captureUploadedIdsOnce() {
    if (!state.active) return;

    const modal = $(".media-modal");
    if (!modal) return;

    const selected = $all(".attachments .attachment.selected[data-id]", modal);
    const ids = selected
      .map((el) => parseInt(el.getAttribute("data-id") || "0", 10))
      .filter(Boolean);

    if (ids.length) {
      // merge sin duplicados
      const merged = new Set([...(state.uploadedIds || []), ...ids]);
      state.uploadedIds = Array.from(merged);
    }
  }

  /**
   * Auto-select: presiona automáticamente el botón "Select" tras upload exitoso
   */
  function scheduleAutoSelect() {
    if (!state.active) return;
    if (state.autoSelectDone) return;

    // debounce: si llegan varios loadend, dejamos solo 1 intento
    if (state.autoSelectTimer) clearTimeout(state.autoSelectTimer);

    // reset retries cada vez que "arranca" el ciclo
    state.autoSelectRetries = 0;

    const tick = () => {
      if (!state.active) return;
      if (state.autoSelectDone) return;

      state.autoSelectRetries++;
      if (state.autoSelectRetries > state.autoSelectMaxRetries) {
        console.warn("[HNH Replace] Auto-select abortado (timeout).");
        return;
      }

      // intentar capturar (NO dependas de que WP marque selected una sola vez)
      captureUploadedIdsOnce();

      const modal =
        document.getElementById("wp-media-modal") || $(".media-modal");
      if (!modal) {
        state.autoSelectTimer = setTimeout(tick, 200);
        return;
      }

      const selectBtn = $(".media-button-select", modal);
      if (!selectBtn) {
        state.autoSelectTimer = setTimeout(tick, 200);
        return;
      }

      const isDisabled =
        selectBtn.disabled ||
        selectBtn.getAttribute("aria-disabled") === "true" ||
        selectBtn.classList.contains("disabled");

      // si sigue disabled o no tenemos ids, reintentar pero con límite
      if (isDisabled || state.uploadedIds.length === 0) {
        state.autoSelectTimer = setTimeout(tick, 200);
        return;
      }

      state.autoSelectDone = true;
      console.log(
        `[HNH Replace] Auto-select ejecutado (${state.uploadedIds.length} uploads)`,
      );
      selectBtn.click();
    };

    state.autoSelectTimer = setTimeout(tick, 500);
  }

  /**
   * Actualiza el estado de los botones Replace y Add según la selección actual
   */
  function updateReplaceButton() {
    const fieldEl = getFieldEl();
    if (!fieldEl) return;

    const replaceBtn = $(".hnh-gallery-replace-btn", fieldEl);
    const addBtn = $(".acf-gallery-add", fieldEl);

    const attachmentsEl = $(".acf-gallery-attachments", fieldEl);
    if (!attachmentsEl) {
      if (replaceBtn) replaceBtn.setAttribute("disabled", "");
      if (addBtn) addBtn.removeAttribute("disabled");
      return;
    }

    const selectedItems = $all(
      ".acf-gallery-attachment.is-bulk-selected",
      attachmentsEl,
    );

    // Si hay items seleccionados: habilitar Replace, deshabilitar Add
    // Si no hay selección: deshabilitar Replace, habilitar Add
    if (selectedItems.length > 0) {
      if (replaceBtn) replaceBtn.removeAttribute("disabled");
      if (addBtn) addBtn.setAttribute("disabled", "");
    } else {
      if (replaceBtn) replaceBtn.setAttribute("disabled", "");
      if (addBtn) addBtn.removeAttribute("disabled");
    }
  }

  /**
   * Guarda el orden final de la galería en BD por AJAX (Modo B)
   */
  async function saveGalleryOrderAjax() {
    // Prevenir race conditions (si se dispara múltiples veces)
    if (state.saving) {
      console.warn("[HNH Replace] Ya hay un guardado en proceso, ignorando...");
      return;
    }

    try {
      state.saving = true;

      const postId = state.postId || getPostId();
      if (!postId) {
        console.warn("[HNH Replace] No post_id disponible para guardar orden");
        return;
      }

      const fieldEl = getFieldEl();
      if (!fieldEl) {
        console.warn("[HNH Replace] No se encontró el field element");
        return;
      }

      const fieldName = fieldEl.dataset.name || FIELD_NAME;
      const fieldKey = fieldEl.dataset.key;

      if (!fieldKey) {
        console.warn("[HNH Replace] No se encontró field_key");
        return;
      }

      const attachmentsEl = $(".acf-gallery-attachments", fieldEl);
      if (!attachmentsEl) {
        console.warn("[HNH Replace] No se encontró attachmentsEl");
        return;
      }

      // Obtener orden final del DOM
      const ids = $all(".acf-gallery-attachment[data-id]", attachmentsEl)
        .map((it) => parseInt(it.getAttribute("data-id") || "0", 10))
        .filter(Boolean);

      if (!ids.length) {
        console.warn("[HNH Replace] No hay IDs para guardar");
        return;
      }

      // Preparar FormData
      const formData = new FormData();
      formData.append("action", "hnh_vehicle_gallery_save_order");
      formData.append("post_id", String(postId));
      formData.append("field_name", fieldName);
      formData.append("field_key", fieldKey);
      formData.append("_wpnonce", cfg.nonce);
      ids.forEach((id) => {
        formData.append("ids[]", String(id));
      });

      // Hacer fetch
      const response = await fetch(cfg.ajaxUrl, {
        method: "POST",
        body: formData,
        credentials: "same-origin", // Asegurar que se envíen cookies/sesión WP
      });

      const result = await response.json();

      if (result.success) {
        console.log(
          `[HNH Replace] Orden guardado automáticamente (${result.data.count} imágenes)`,
        );
      } else {
        console.warn(
          "[HNH Replace] Error al guardar orden:",
          result.data?.message || "Unknown error",
        );
      }
    } catch (err) {
      console.warn("[HNH Replace] Error en saveGalleryOrderAjax:", err);
    } finally {
      state.saving = false;
    }
  }

  /**
   * C) Al apretar "Select": reemplazar SOLO seleccionados
   */
  function handleSelectClick(e) {
    if (!state.active) return;

    // Asegurar que el click venga del botón "Select" del modal
    const selectBtn = e.target.closest(".media-button-select");
    if (!selectBtn) return;

    // Capturar copias INMUTABLES antes de que el modal se cierre o el state se resetee
    const idsToDelete = state.selectedIds.slice();
    const postId = state.postId || getPostId();

    // Nota: WP cierra el modal automáticamente, usamos copias inmutables para sobrevivir al reset
    const fieldEl = getFieldEl();
    if (!fieldEl) return;

    const attachmentsEl = $(".acf-gallery-attachments", fieldEl);
    if (!attachmentsEl) return;

    // Pequeño delay para que ACF termine de insertar las nuevas imágenes
    setTimeout(() => {
      try {
        // 1) Detectar IDs actuales después de que ACF insertó
        const currentItems = Array.from(
          attachmentsEl.querySelectorAll(".acf-gallery-attachment[data-id]"),
        );
        const postOpenIds = currentItems.map((it) =>
          parseInt(it.getAttribute("data-id") || "0", 10),
        );

        // 2) Detectar cuáles son nuevas (comparar con preOpenIds)
        const newIds = postOpenIds.filter(
          (id) => !state.preOpenIds.includes(id),
        );

        // 3) Obtener nodos DOM de las nuevas (en orden)
        const newNodes = newIds
          .map((id) =>
            attachmentsEl.querySelector(
              `.acf-gallery-attachment[data-id="${id}"]`,
            ),
          )
          .filter(Boolean);

        // 4) Eliminar SOLO los seleccionados (en orden descendente por índice)
        const itemsBeforeRemoval = Array.from(
          attachmentsEl.querySelectorAll(".acf-gallery-attachment[data-id]"),
        );

        // Obtener los items seleccionados con sus índices actuales
        const toRemove = itemsBeforeRemoval
          .map((item, idx) => ({ item, idx }))
          .filter((obj) => {
            const id = parseInt(obj.item.getAttribute("data-id") || "0", 10);
            return idsToDelete.includes(id);
          })
          .sort((a, b) => b.idx - a.idx); // orden descendente

        toRemove.forEach(({ item }) => {
          const removeBtn = item.querySelector(".acf-gallery-remove");
          if (removeBtn) removeBtn.click();
        });

        // 5) Calcular ancla para inserción
        // Necesitamos los items originales antes de abrir modal
        const originalItems = state.preOpenIds.map((id) => ({
          id,
          idx: state.preOpenIds.indexOf(id),
        }));

        // Buscar primer elemento con índice > minIndex que NO era seleccionado
        let anchorId = null;
        for (let i = state.minIndex + 1; i < originalItems.length; i++) {
          const candidate = originalItems[i];
          if (!idsToDelete.includes(candidate.id)) {
            anchorId = candidate.id;
            break;
          }
        }

        // Esperar un tick para que el DOM se actualice después de los removes
        setTimeout(() => {
          const anchorNode = anchorId
            ? attachmentsEl.querySelector(
                `.acf-gallery-attachment[data-id="${anchorId}"]`,
              )
            : null;

          // 6) Insertar nuevas en la posición correcta
          if (anchorNode) {
            // Insertar antes del ancla
            newNodes.forEach((node) => {
              anchorNode.insertAdjacentElement("beforebegin", node);
            });
          } else {
            // Insertar al final
            newNodes.forEach((node) => {
              attachmentsEl.appendChild(node);
            });
          }

          // 7) Limpiar selección
          $all(".acf-gallery-attachment", attachmentsEl).forEach((item) =>
            item.classList.remove("is-bulk-selected"),
          );

          updateReplaceButton();

          // 8) Guardar automáticamente el orden final (Modo B) + borrado permanente condicionado
          (async () => {
            try {
              // Primero guardar el orden
              await saveGalleryOrderAjax();

              // Luego borrar permanentemente las viejas (solo si post_parent === vehicle_id)
              if (idsToDelete.length && postId) {
                const result = await deleteOldAttachmentsAjax(
                  idsToDelete,
                  postId,
                );
                if (result.success) {
                  console.log(
                    `[HNH Replace] Borradas permanentemente: ${result.deleted}, Saltadas: ${result.skipped}`,
                  );
                }
              } else if (idsToDelete.length && !postId) {
                console.warn(
                  "[HNH Replace] postId=0, no se puede borrar del Media Library.",
                );
              }
            } catch (err) {
              console.warn(
                "[HNH Replace] Error en guardado/borrado final:",
                err,
              );
            }
          })();
        }, 50);
      } catch (err) {
        console.error("Error en handleSelectClick:", err);
      }
    }, 100);
  }

  /**
   * Detecta si el body de una petición XHR es una subida de archivos de WordPress
   *
   * Verifica si el body contiene alguna de las acciones de upload que usa WordPress:
   * - `upload-attachment`: acción estándar de subida de media
   * - `async-upload`: acción de subida asíncrona
   *
   * Soporta dos formatos de body:
   * 1. String (application/x-www-form-urlencoded): busca el texto de la acción
   * 2. FormData: extrae y verifica el campo "action"
   *
   * @param {string|FormData} body - El body de la petición XHR a analizar
   * @returns {boolean} true si es una petición de upload de WordPress, false en caso contrario
   *
   * @example
   * // String URL-encoded
   * isUploadRequest("action=upload-attachment&file=..."); // true
   * isUploadRequest("action=query-attachments"); // false
   *
   * // FormData
   * const fd = new FormData();
   * fd.append("action", "async-upload");
   * isUploadRequest(fd); // true
   */
  function isUploadRequest(body) {
    if (typeof body === "string") {
      return (
        body.indexOf("action=upload-attachment") !== -1 ||
        body.indexOf("action=async-upload") !== -1
      );
    }
    if (body instanceof FormData) {
      const action = body.get("action");
      return action === "upload-attachment" || action === "async-upload";
    }
    return false;
  }

  async function deleteOldAttachmentsAjax(ids, postIdOverride) {
    try {
      const postId = postIdOverride || state.postId || getPostId();
      if (!postId) return { success: false, deleted: 0, skipped: ids.length };

      const cleanIds = Array.from(
        new Set((ids || []).map((n) => parseInt(n, 10)).filter(Boolean)),
      );
      if (!cleanIds.length) return { success: true, deleted: 0, skipped: 0 };

      const formData = new FormData();
      formData.append("action", "hnh_vehicle_gallery_delete_old");
      formData.append("post_id", String(postId));
      formData.append("_wpnonce", cfg.nonce);
      cleanIds.forEach((id) => formData.append("ids[]", String(id)));

      const res = await fetch(cfg.ajaxUrl, {
        method: "POST",
        body: formData,
        credentials: "same-origin",
      });

      const json = await res.json();

      if (!json || !json.success) {
        console.warn(
          "[HNH Replace] Delete old failed:",
          json?.data?.message || "Unknown error",
        );
        return { success: false, deleted: 0, skipped: cleanIds.length };
      }

      return {
        success: true,
        deleted: json.data?.deleted || 0,
        skipped: json.data?.skipped || 0,
      };
    } catch (err) {
      console.warn("[HNH Replace] deleteOldAttachmentsAjax error:", err);
      return { success: false, deleted: 0, skipped: (ids || []).length };
    }
  }

  /**
   * Intercept XHR: inyecta scope replace + startedAt + uploadedIds
   * Importante: este patch corre DESPUÉS del patch que ya tenés en admin-gallery.php
   * Así que agarramos el send actual y lo envolvemos.
   */
  function patchXHR() {
    const prevSend = XMLHttpRequest.prototype.send;

    XMLHttpRequest.prototype.send = function (body) {
      /* El body es un string con formato query (application/x-www-form-urlencoded) 
      en las queries de attachments de WP, así que podemos manipularlo como texto */
      try {
        /* Interceptamos solo las queries de attachments (que es donde ACF pide las 
        imágenes para mostrar en la grilla) */
        if (
          state.active &&
          typeof body === "string" &&
          body.includes("action=query-attachments")
        ) {
          // scope replace (si ya viene gallery_vehicle por el otro script, lo cambiamos)
          if (body.includes("query[vehicle_scope]=gallery_vehicle")) {
            body = body.replace(
              "query[vehicle_scope]=gallery_vehicle",
              "query[vehicle_scope]=gallery_vehicle_replace",
            );
          } else if (!body.includes("vehicle_scope")) {
            body += "&query[vehicle_scope]=gallery_vehicle_replace";
          } else if (
            body.includes("vehicle_scope") &&
            !body.includes("gallery_vehicle_replace")
          ) {
            // si viene algo distinto, lo sobreescribimos
            body = body.replace(
              /query\[vehicle_scope\]=[^&]+/g,
              "query[vehicle_scope]=gallery_vehicle_replace",
            );
          }

          // startedAt
          if (!body.includes("hnh_replace_started_at")) {
            body += `&query[hnh_replace_started_at]=${encodeURIComponent(String(state.startedAt))}`;
          }

          // uploadedIds (cuando ya los tenemos)
          if (state.uploadedIds.length && !body.includes("hnh_uploaded_ids")) {
            body += `&query[hnh_uploaded_ids]=${encodeURIComponent(state.uploadedIds.join(","))}`;
          }
        }

        // Detectar uploads de imágenes (cuando el usuario sube archivos en replace mode)
        if (state.active && isUploadRequest(body)) {
          console.log("📤 [HNH Replace] Upload detectado");
          const xhr = this;

          xhr.addEventListener(
            "loadend",
            function () {
              // Upload completado exitosamente
              if (xhr.status >= 200 && xhr.status < 300) {
                console.log(
                  "✅✅ [HNH Replace] Upload completado exitosamente",
                );

                // Capturar ID directamente del response (más confiable)
                try {
                  const response = JSON.parse(xhr.responseText);
                  const uploadedId =
                    response?.data?.id ? parseInt(response.data.id, 10) : 0;

                  if (uploadedId) {
                    const merged = new Set([...(state.uploadedIds || []), uploadedId]);
                    state.uploadedIds = Array.from(merged);
                    console.log(
                      `[HNH Replace] ID capturado desde XHR: ${uploadedId} (Total: ${state.uploadedIds.length})`,
                    );
                  }
                } catch (e) {
                  console.warn("[HNH Replace] No se pudo parsear response:", e);
                }

                // Disparar auto-select del botón Select
                scheduleAutoSelect();
              } else {
                console.warn(
                  "⚠️ [HNH Replace] Upload falló con status:",
                  xhr.status,
                );
              }
            },
            { once: true },
          );
        }

        return prevSend.call(this, body);
      } catch (err) {
        return prevSend.call(this, body);
      }
    };
  }

  /**
   * Reset replace al cerrar modal
   */
  function bindCloseReset() {
    document.addEventListener(
      "click",
      (e) => {
        if (
          e.target.closest(".media-modal-close") ||
          e.target.classList.contains("media-modal-backdrop")
        ) {
          resetReplaceState();
        }

        // cuando apretás Select, WP suele cerrar luego; igual reseteamos después de un toque
        if (e.target.closest(".media-button-select")) {
          setTimeout(resetReplaceState, 250);
        }
      },
      true,
    );
  }

  function resetReplaceState() {
    // Limpiar timer de auto-select
    if (state.autoSelectTimer) {
      clearTimeout(state.autoSelectTimer);
    }

    state.active = false;
    state.postId = 0;
    state.startedAt = 0;
    state.selectedIds = [];
    state.selectedIndexes = [];
    state.minIndex = 0;
    state.preOpenIds = [];
    state.uploadedIds = [];
    state.saving = false;
    state.autoSelectTimer = null;
    state.autoSelectDone = false;
    state.autoSelectRetries = 0;
    window.__hnhVehicleGalleryReplaceActive = false;
    document.body.classList.remove("hnh-replace-mode");
    // restoreMediaModalTitle();
  }

  /**
   * Init
   */
  function init() {
    patchXHR();
    bindCloseReset();
    injectStyles(); // Inyectar estilos CSS desde el inicio

    // Insertar botón Replace cuando el field ya esté listo (y ya exista Delete selected photos)
    const boot = () => {
      const fieldEl = getFieldEl();
      if (!fieldEl) return;

      addReplaceButton(fieldEl);
      updateReplaceButton(); // Estado inicial

      // Observer para actualizar botón Replace cuando cambie la selección o el contenido
      const galleryEl = $(".acf-gallery-attachments", fieldEl);
      if (galleryEl) {
        const selectionObserver = new MutationObserver((mutations) => {
          // Detectar cambios en clases (is-bulk-selected)
          const hasClassChanges = mutations.some(
            (m) => m.type === "attributes" && m.attributeName === "class",
          );
          // Detectar cambios en la estructura (add/remove items)
          const hasStructureChanges = mutations.some(
            (m) => m.type === "childList",
          );

          if (hasClassChanges || hasStructureChanges) {
            updateReplaceButton();
          }
        });

        selectionObserver.observe(galleryEl, {
          attributes: true,
          attributeFilter: ["class"],
          childList: true,
          subtree: true,
        });
      }

      // observer para capturar uploadedIds cuando aparezca el modal/selección
      const obs = new MutationObserver(() => {
        if (!state.active) return;
        forceUploadTab();
        captureUploadedIdsOnce();
        // setMediaModalTitle("Replace Images in Gallery");
      });
      obs.observe(document.body, { childList: true, subtree: true });
    };

    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", boot);
    } else {
      boot();
    }

    // intercept Select
    document.addEventListener("click", handleSelectClick, true);
  }

  init();
})();
