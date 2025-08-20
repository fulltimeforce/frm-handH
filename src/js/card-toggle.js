document.addEventListener("DOMContentLoaded", function () {
  const openButtons = document.querySelectorAll(".specialist_toggle.open");
  const closeButtons = document.querySelectorAll(".specialist_toggle.close");

  openButtons.forEach((btn) => {
    btn.addEventListener("click", function (e) {
      e.stopPropagation();
      const wrapper = this.closest(".specialist_item_wrapper");

      document.querySelectorAll(".specialist_item_wrapper").forEach((w) => {
        w.classList.remove("active");
        w.querySelectorAll(".specialist_toggle").forEach((t) =>
          t.setAttribute("aria-expanded", "false")
        );
      });

      wrapper.classList.add("active");
      this.setAttribute("aria-expanded", "true");
    });
  });

  closeButtons.forEach((btn) => {
    btn.addEventListener("click", function (e) {
      e.stopPropagation();
      const wrapper = this.closest(".specialist_item_wrapper");

      wrapper.classList.remove("active");
      wrapper.querySelectorAll(".specialist_toggle").forEach((t) =>
        t.setAttribute("aria-expanded", "false")
      );
    });
  });
});
