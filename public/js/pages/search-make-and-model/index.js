/**
 * Search Make and Model Page - Dropdown Handler
 */

/**
 * Handle dropdown menu toggle and click outside
 */
function handleDropdownMenu() {
  // Toggle dropdown cuando se hace clic en el botón
  document.querySelectorAll(".submenu_dropdown-item > button").forEach((button) => {
    button.addEventListener("click", function () {
      const parentItem = this.closest(".submenu_dropdown-item");

      // Cerrar otros activos
      document.querySelectorAll(".submenu_dropdown-item.active").forEach((item) => {
        if (item !== parentItem) {
          item.classList.remove("active");
        }
      });

      // Toggle del actual
      parentItem.classList.toggle("active");
    });
  });

  // Cerrar dropdown al hacer clic fuera
  document.addEventListener("click", function (e) {
    const isDropdown = e.target.closest(".submenu_dropdown-item");

    if (!isDropdown) {
      document.querySelectorAll(".submenu_dropdown-item.active").forEach((item) => {
        item.classList.remove("active");
      });
    }
  });
}

/**
 * Initialize all search make and model page functionality
 */
export function initSearchMakeAndModelPage() {
  handleDropdownMenu();
}
