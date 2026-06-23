(function() {
    'use strict';
    
    document.addEventListener("DOMContentLoaded", function() {
        const pins = document.querySelectorAll('.pin');
        const interactiveMap = document.querySelector('.interactive_map');

        if (!pins.length || !interactiveMap) return;

        pins.forEach(function(pin) {
            pin.addEventListener('click', function(e) {
                e.preventDefault();
                const pinId = this.getAttribute('data-pin-id');
                
                if (pinId) {
                    interactiveMap.setAttribute('data-state', pinId);
                }
            });
        });
    });
})();
