/**
 * ACF Gallery Bulk Actions - Empty Gallery
 *
 * Adds "Empty Gallery" option to the ACF Gallery field bulk actions dropdown
 * for the vehicles CPT. Allows removing all gallery images with one click.
 *
 * @package HandH
 * @since 1.0.0
 */

(() => {
  const GALLERY_FIELD_NAME = "gallery_vehicle";
  const ACTION_VALUE = "empty_gallery";

  function addOption(select) {
    if (select.querySelector(`option[value="${ACTION_VALUE}"]`)) return;

    const opt = document.createElement("option");
    opt.value = ACTION_VALUE;
    opt.textContent = "Empty Gallery";
    select.appendChild(opt);
  }
  
  function emptyGalleryByClickingRemoveButtons(galleryRoot) {
    // Busca todas las X
    const removeButtons = galleryRoot.querySelectorAll("a.acf-gallery-remove");

    // Si no hay nada, no hagas nada
    if (!removeButtons.length) return;

    // Click a todas
    removeButtons.forEach((btn) => {
      btn.dispatchEvent(
        new MouseEvent("click", {
          bubbles: true,
          cancelable: true,
          view: window,
        })
      );
    });
  }

  function wireSelect(select, galleryRoot) {
    if (select.dataset.emptyGalleryWired === "1") return;
    select.dataset.emptyGalleryWired = "1";

    select.addEventListener("change", () => {
      if (select.value !== ACTION_VALUE) return;

      // const ok = window.confirm(
      //   "Are you sure you want to empty the entire gallery?"
      // );      
      // if (!ok) {
      //   select.value = "";
      //   return;
      // }      
      emptyGalleryByClickingRemoveButtons(galleryRoot);

      // reset
      select.value = "";
    });
  }

  function initForField(field) {
    
    const el = field.$el && field.$el[0] ? field.$el[0] : null;
    if (!el) return;
    
    const toolbar = el.querySelector(".acf-gallery-toolbar");
    if (!toolbar) return;

    const select = toolbar.querySelector("select.acf-gallery-sort");
    if (!select) return;

    addOption(select);

    
    const galleryRoot = el.querySelector(".acf-gallery");
    if (!galleryRoot) return;

    wireSelect(select, galleryRoot);
  }

  if (!window.acf) return;

  // ACF events
  acf.addAction("ready_field/type=gallery", (field) => {
    if (field.get("name") !== GALLERY_FIELD_NAME) return;
    initForField(field);
  });

  acf.addAction("append_field/type=gallery", (field) => {
    if (field.get("name") !== GALLERY_FIELD_NAME) return;
    initForField(field);
  });
})();
