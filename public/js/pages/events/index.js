(function () {
  "use strict";

  const slider = document.querySelector(".events_slide");

  if (!slider) {
    return;
  }

  // Prevent the legacy bundle from mounting its one-item Splide instance.
  slider.classList.remove("events_slide");

  function initEventsSlider() {
    slider.classList.add("events_slide");

    const originalTrack = slider.querySelector(".splide__track");
    const originalPrevious = slider.querySelector(".splide__arrow--prev");
    const originalNext = slider.querySelector(".splide__arrow--next");

    if (!originalTrack || !originalPrevious || !originalNext) {
      return;
    }

    // Clone interactive elements to remove listeners and inline state left by legacy Splide.
    const track = originalTrack.cloneNode(true);
    const previous = originalPrevious.cloneNode(true);
    const next = originalNext.cloneNode(true);
    originalTrack.replaceWith(track);
    originalPrevious.replaceWith(previous);
    originalNext.replaceWith(next);

    slider.querySelectorAll(".splide__pagination").forEach(function (element) {
      element.remove();
    });

    const list = track.querySelector(".splide__list");
    const slides = Array.from(track.querySelectorAll(".splide__slide"));

    if (!list || !slides.length) {
      return;
    }

    track.className = "splide__track";
    track.removeAttribute("id");
    track.removeAttribute("aria-live");
    track.removeAttribute("aria-atomic");
    list.removeAttribute("id");
    list.removeAttribute("role");

    slides.forEach(function (slide) {
      slide.className = "splide__slide";
      slide.removeAttribute("id");
      slide.removeAttribute("role");
      slide.removeAttribute("aria-roledescription");
      slide.removeAttribute("aria-label");
      slide.style.marginRight = "0";
    });

    let currentIndex = 0;
    let touchStartX = null;

    const getPerPage = function () {
      if (window.innerWidth <= 640) return 1;
      if (window.innerWidth <= 1024) return 2;
      return 3;
    };

    const getGap = function () {
      return window.innerWidth <= 1024 ? 24 : 36;
    };

    const pagination = document.createElement("ul");
    pagination.className = "splide__pagination";
    slider.appendChild(pagination);

    function render() {
      const perPage = getPerPage();
      const gap = getGap();
      const maximumIndex = Math.max(0, slides.length - perPage);
      currentIndex = Math.max(0, Math.min(currentIndex, maximumIndex));

      slider.classList.toggle("is-overflow", slides.length > perPage);

      list.style.display = "flex";
      list.style.gap = gap + "px";
      list.style.transition = "transform 0.4s ease";

      slides.forEach(function (slide, index) {
        slide.style.width = "calc((100% - " + gap * (perPage - 1) + "px) / " + perPage + ")";
        slide.style.flex = "0 0 auto";
        slide.setAttribute("aria-hidden", index < currentIndex || index >= currentIndex + perPage ? "true" : "false");
      });

      const slideWidth = slides[0].getBoundingClientRect().width;
      list.style.transform = "translate3d(-" + currentIndex * (slideWidth + gap) + "px, 0, 0)";

      previous.disabled = currentIndex === 0;
      next.disabled = currentIndex === maximumIndex;

      pagination.innerHTML = "";
      for (let index = 0; index <= maximumIndex; index += 1) {
        const item = document.createElement("li");
        const button = document.createElement("button");
        button.type = "button";
        button.className = "splide__pagination__page" + (index === currentIndex ? " is-active" : "");
        button.setAttribute("aria-label", "Go to slide " + (index + 1));
        button.addEventListener("click", function () {
          currentIndex = index;
          render();
        });
        item.appendChild(button);
        pagination.appendChild(item);
      }
    }

    previous.addEventListener("click", function () {
      currentIndex = Math.max(0, currentIndex - 1);
      render();
    });

    next.addEventListener("click", function () {
      currentIndex += 1;
      render();
    });

    track.addEventListener("touchstart", function (event) {
      touchStartX = event.touches[0].clientX;
    }, { passive: true });

    track.addEventListener("touchend", function (event) {
      if (touchStartX === null) return;

      const distance = event.changedTouches[0].clientX - touchStartX;
      touchStartX = null;

      if (Math.abs(distance) < 50) return;

      if (distance > 0) {
        currentIndex = Math.max(0, currentIndex - 1);
      } else {
        currentIndex = Math.min(Math.max(0, slides.length - getPerPage()), currentIndex + 1);
      }

      render();
    }, { passive: true });

    let resizeTimer;
    window.addEventListener("resize", function () {
      window.clearTimeout(resizeTimer);
      resizeTimer = window.setTimeout(render, 100);
    });

    track.style.overflow = "hidden";
    slider.classList.add("is-initialized");
    render();
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initEventsSlider);
  } else {
    initEventsSlider();
  }
})();
