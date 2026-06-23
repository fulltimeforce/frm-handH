(function() {
    'use strict';
    
    document.addEventListener("DOMContentLoaded", function() {
        const videoDiv = document.querySelector(".video");

        if (!videoDiv) return;

        videoDiv.addEventListener("click", function() {
            const currentState = videoDiv.getAttribute("data-state");
            const video = videoDiv.querySelector('video');
            
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
    });
})();
(function() {
    'use strict';
    
    document.addEventListener("DOMContentLoaded", function() {
        const videoDiv = document.querySelector(".video");

        if (!videoDiv) return;

        videoDiv.addEventListener("click", function() {
            const currentState = videoDiv.getAttribute("data-state");
            const video = videoDiv.querySelector('video');
            
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
    });
})();
