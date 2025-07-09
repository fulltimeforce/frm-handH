(function () {

    window.addEventListener('scroll', () => {
        const header = document.querySelector('.header');
        const scrollY = window.scrollY || window.pageYOffset;
        const threshold = window.innerHeight - 50;

        if (header) {
            if (scrollY > threshold) {
                header.classList.add('dark');
            } else {
                header.classList.remove('dark');
            }
        }
    });

})();