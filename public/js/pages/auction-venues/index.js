const initVideoPlayer = () => {
  const videoDiv = document.querySelector(".video");

  if (!videoDiv) return;

  videoDiv.addEventListener("click", function () {
    const currentState = videoDiv.getAttribute("data-state");
    const video = videoDiv.querySelector("video");

    if (!video) return;

    if (currentState === "1") {
      videoDiv.setAttribute("data-state", "2");
      video.play();
    } else {
      videoDiv.setAttribute("data-state", "1");
      video.pause();
      video.muted = false;
    }
  });
};

const initInteractiveMap = () => {
  const pins = document.querySelectorAll(".pin");
  const interactiveMap = document.querySelector(".interactive_map");

  if (!pins.length || !interactiveMap) return;

  pins.forEach(function (pin) {
    pin.addEventListener("click", function (e) {
      e.preventDefault();
      const pinId = this.getAttribute("data-pin-id");

      if (pinId) {
        interactiveMap.setAttribute("data-state", pinId);
      }
    });
  });
};

export const initAuctionVenuesPage = () => {
  initVideoPlayer();
  initInteractiveMap();
};
