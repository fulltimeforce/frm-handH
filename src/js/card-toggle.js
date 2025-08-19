document.addEventListener("DOMContentLoaded", function () {
  const toggles = document.querySelectorAll(".specialist_toggle");

  toggles.forEach((btn) => {
    btn.addEventListener("click", function (e) {
      e.stopPropagation();
      const wrapper = this.closest(".specialist_item_wrapper");
      const expanded = this.getAttribute("aria-expanded") === "true";

      document.querySelectorAll(".specialist_item_wrapper").forEach((w) => {
        w.classList.remove("active");
        w.querySelector(".specialist_toggle").setAttribute("aria-expanded", "false");
      });

      if (!expanded) {
        wrapper.classList.add("active");
        this.setAttribute("aria-expanded", "true");
      }
    });
  });
});
