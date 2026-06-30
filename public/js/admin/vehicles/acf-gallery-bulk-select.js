(() => {
  // Ajustá esto si querés limitarlo a un field específico:
  const ONLY_FIELD_NAME = "gallery_vehicle";
  // const ONLY_FIELD_NAME = null;

  const STYLE_ID = "acf-gallery-bulk-select-style";

  /**
   * Inyecta los estilos CSS necesarios para los checkboxes y elementos seleccionados.
   * Solo lo hace una vez, verificando que no exista ya el elemento style en el documento.
   */
  function injectStyles() {
    if (document.getElementById(STYLE_ID)) return;

    const checkIconUrl =
      "https://www.handh.co.uk/wp-content/uploads/2026/02/icon-check-admin.png";

    const style = document.createElement("style");
    style.id = STYLE_ID;
    style.textContent = `
      /* BEGIN - Ocultar barra lateral de la galería */
      .acf-field[data-type='gallery'] .acf-gallery-side{
        display: none !important;        
      }
      .acf-field[data-type='gallery'] .acf-gallery .acf-gallery-attachment.active .margin{
        box-shadow: none !important;
      }
       .acf-field[data-type='gallery'] .acf-gallery .acf-gallery-main{
          right: 0 !important;
       }
      /* END - Ocultar barra lateral de la galería */
      .acf-gallery-attachment.is-bulk-selected .margin{
        outline: 4px solid #2271b1;
        outline-offset: -2px;
      }
      .acf-bulk-cb{
        position:absolute;
        top:6px;
        left:6px;
        z-index:50;
        width:22px;
        height:22px;
        border:0;
        padding:0;
        background:transparent;
        cursor:pointer;
      }
      .acf-bulk-cb__box{
        width:20px;
        height:20px;
        border-radius:4px;
        background: rgba(255,255,255,.95);
        border: 1px solid rgba(0,0,0,.25);
        box-shadow: 0 1px 2px rgba(0,0,0,.2);
        display:block;
        margin: 3.5px;
      }
      .acf-gallery-attachment.is-bulk-selected .acf-bulk-cb__box{
        background:#2271b1;
        border-color:#2271b1;        
        position: relative;
      }
      .acf-gallery-attachment.is-bulk-selected .acf-bulk-cb__box::after{
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: url('${checkIconUrl}') center/12px no-repeat;
        filter: brightness(0) invert(1);
      }
      .acf-gallery-attachment.is-bulk-selected .acf-bulk-cb__box:focus-visible {
        outline: none !important;
      }        

      button.acf-bulk-cb:focus-visible {
        outline: none !important;
      }

    `;
    document.head.appendChild(style);
  }

  /**
   * Busca todos los elementos que coincidan con un selector dentro de un elemento raíz.
   * @param {HTMLElement|Document} root - Elemento raíz donde buscar
   * @param {string} selector - Selector CSS
   * @returns {HTMLElement[]} Array de elementos encontrados
   */
  function findAll(root, selector) {
    return Array.from(root.querySelectorAll(selector));
  }

  /**
   * Busca el ancestro más cercano que coincida con un selector.
   * @param {HTMLElement} element - Elemento desde donde empezar la búsqueda
   * @param {string} selector - Selector CSS
   * @returns {HTMLElement|null} Elemento encontrado o null
   */
  function closest(element, selector) {
    return element && element.closest ? element.closest(selector) : null;
  }

  /**
   * Mejora un campo de galería de ACF añadiendo funcionalidad de selección múltiple.
   * Agrega checkboxes a cada imagen, botón para eliminar seleccionados y maneja
   * la lógica de selección (simple, múltiple con Ctrl, rango con Shift).
   * @param {HTMLElement} fieldEl - Elemento del campo de galería ACF
   */
  function enhanceGalleryField(fieldEl) {
    if (fieldEl.dataset.bulkSelectReady === "1") return;
    fieldEl.dataset.bulkSelectReady = "1";

    // Limitar a un field name si querés
    const fieldName = fieldEl.getAttribute("data-name");
    if (ONLY_FIELD_NAME && fieldName !== ONLY_FIELD_NAME) return;

    const gallery = fieldEl.querySelector(".acf-gallery");

    /**
     * Contenedor donde ACF renderiza todas las miniaturas de las imágenes de la galería.
     * Aquí se agregan los checkboxes y se detectan los clicks de selección.
     * @type {HTMLElement|null}
     */
    const attachments = fieldEl.querySelector(".acf-gallery-attachments");

    if (!gallery || !attachments) return;

    injectStyles();

    let lastIndex = null;
    let anchorIndex = null; // Índice ancla para selección con Shift

    // Agregar botón "Remove selected" al lado del Add to gallery
    const addButton = fieldEl.querySelector(".acf-gallery-add");
    if (addButton) {
      const listItem = addButton.closest("li");
      if (listItem && !fieldEl.querySelector(".acf-bulk-remove-selected")) {
        const deleteButtonContainer = document.createElement("li");
        const deleteButton = document.createElement("button");
        deleteButton.type = "button";
        deleteButton.className =
          "button button-secondary acf-bulk-remove-selected";
        deleteButton.textContent = "Delete";
        deleteButton.style.marginLeft = "8px";
        deleteButton.setAttribute("disabled", "");
        deleteButtonContainer.appendChild(deleteButton);
        listItem.insertAdjacentElement("afterend", deleteButtonContainer);

        deleteButton.addEventListener("click", () => {
          // Obtenemos todos los items que están seleccionados
          const selectedItems = getItems().filter((galleryItem) =>
            galleryItem.classList.contains("is-bulk-selected"),
          );

          if (!selectedItems.length) return;

          // Simulamos click en el botón de eliminar de cada item seleccionado
          selectedItems.forEach((item) => {
            const removeButton = item.querySelector(".acf-gallery-remove");
            if (removeButton) removeButton.click(); // ACF actualiza los hidden inputs
          });

          clearAllSelections();
          lastIndex = null;
        });
      }
    }

    /**
     * Obtenemos todos los items de la galería
     * @returns {HTMLElement[]} Array de elementos attachment de la galería
     */
    function getItems() {
      return findAll(attachments, ".acf-gallery-attachment");
    }

    /**
     * Asegura que cada item de la galería tenga su checkbox de selección.
     * Si ya existe el checkbox, no hace nada. Si no existe, lo crea.
     * El checkbox es un botón con un span visual dentro.
     */
    function ensureCheckboxes() {
      getItems().forEach((item) => {
        if (item.querySelector(".acf-bulk-cb")) return;
        item.style.position = "relative";

        const checkbox = document.createElement("button");
        checkbox.type = "button";
        checkbox.className = "acf-bulk-cb";
        checkbox.setAttribute("aria-label", "Select image");

        const checkboxBox = document.createElement("span");
        checkboxBox.className = "acf-bulk-cb__box";
        checkbox.appendChild(checkboxBox);

        item.appendChild(checkbox);
      });
    }

    function updateDeleteButton() {
      const deleteButton = fieldEl.querySelector(".acf-bulk-remove-selected");
      if (!deleteButton) return;

      const hasSelectedItems = getItems().some((galleryItem) =>
        galleryItem.classList.contains("is-bulk-selected"),
      );

      if (hasSelectedItems) {
        deleteButton.removeAttribute("disabled");
      } else {
        deleteButton.setAttribute("disabled", "");
      }
    }

    function clearAllSelections() {
      getItems().forEach((galleryItem) =>
        galleryItem.classList.remove("is-bulk-selected"),
      );
      updateDeleteButton();
    }

    function toggleItem(item) {
      item.classList.toggle("is-bulk-selected");
      updateDeleteButton();
    }

    function selectRange(from, to) {
      const items = getItems();
      const start = Math.min(from, to);
      const end = Math.max(from, to);
      for (let i = start; i <= end; i++) {
        items[i]?.classList.add("is-bulk-selected");
      }
      updateDeleteButton();
    }

    // Inicial + observer para cuando agregan imágenes
    ensureCheckboxes();
    const mutationObserver = new MutationObserver(() => ensureCheckboxes());
    mutationObserver.observe(attachments, { childList: true, subtree: true });

    // Click handler en todo el item (no solo el checkbox)
    attachments.addEventListener("click", (event) => {
      // Ignorar clicks en botones de acción o elementos interactivos
      if (closest(event.target, ".acf-gallery-remove")) return;
      if (closest(event.target, ".actions")) return;
      
      const item = closest(event.target, ".acf-gallery-attachment");
      if (!item) return;

      // Si clickeamos en un área muy específica que no queremos, podemos salir
      // (Por ahora solo evitamos los botones de acción)

      const items = getItems();
      const index = items.indexOf(item);

      const isShiftPressed = event.shiftKey && anchorIndex !== null;
      const isMultiSelect = event.ctrlKey || event.metaKey;
      const isRangeAddition = isShiftPressed && isMultiSelect; // Ctrl+Shift como Windows

      if (isRangeAddition) {
        // Ctrl+Shift: Agregar nuevo rango SIN limpiar selección anterior (como Windows)
        selectRange(anchorIndex, index);
      } else if (!isShiftPressed && !isMultiSelect) {
        // Si el item ya está seleccionado y es el único, deseleccionarlo
        const isAlreadySelected = item.classList.contains("is-bulk-selected");
        const selectedCount = getItems().filter((galleryItem) =>
          galleryItem.classList.contains("is-bulk-selected"),
        ).length;

        if (isAlreadySelected && selectedCount === 1) {
          item.classList.remove("is-bulk-selected");
          anchorIndex = null;
        } else {
          clearAllSelections();
          item.classList.add("is-bulk-selected");
          anchorIndex = index; // Establecer nuevo ancla
        }
      } else if (isShiftPressed) {
        // Shift solo: Limpiar todo y seleccionar rango desde ancla
        clearAllSelections();
        selectRange(anchorIndex, index);
      } else {
        // Ctrl solo: Toggle individual
        toggleItem(item);
        anchorIndex = index; // Actualizar ancla en selección múltiple
      }

      lastIndex = index;
      updateDeleteButton();

      // Si querés “saber IDs”:
      // const ids = getItems()
      //   .filter(it => it.classList.contains("is-bulk-selected"))
      //   .map(it => it.getAttribute("data-id"));
      // console.log(ids);
    });
  }

  /**
   * Busca todos los campos de tipo gallery de ACF en el documento
   * y les aplica las mejoras de selección múltiple (checkboxes y botón de eliminar)
   */
  function enhanceAll() {
    // Box Gallery de ACF
    const fields = findAll(document, ".acf-field[data-type='gallery']");
    fields.forEach(enhanceGalleryField);
  }

  // ACF suele disparar eventos al cargar/append. Si no querés depender:
  // - corremos al DOMContentLoaded
  // - y también cada vez que haya cambios grandes en el body (fallback)
  document.addEventListener("DOMContentLoaded", () => {
    enhanceAll();

    // Fallback: si ACF inserta campos por AJAX, los detectamos
    const bodyObserver = new MutationObserver(() => enhanceAll());
    bodyObserver.observe(document.body, { childList: true, subtree: true });
  });
})();
