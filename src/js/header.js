(function () {

    const header = document.querySelector('.header');

    window.addEventListener('scroll', () => {
        const scrollY = window.scrollY || window.pageYOffset;
        const threshold = window.innerHeight - 50;

        if (header && !header.classList.contains('white_bg')) {
            if (scrollY > threshold) {
                header.classList.add('dark');
            } else {
                header.classList.remove('dark');
            }
        }
    });

    document.addEventListener("DOMContentLoaded", () => {
        const lis = document.querySelectorAll("ul.ul_menu > li");

        lis.forEach(li => {
            const button = li.querySelector("button");
            const submenu = li.querySelector(".submenu");

            // Si este <li> no tiene <button> o .submenu, no hacemos nada
            if (!button || !submenu) return;

            let timeout;

            const removeActive = () => {
                li.classList.remove("active");
            };

            const scheduleRemove = () => {
                clearTimeout(timeout);
                timeout = setTimeout(removeActive, 1500);
            };

            const cancelRemove = () => {
                clearTimeout(timeout);
            };

            button.addEventListener("click", () => {
                if (li.classList.contains("active")) {
                    // Si ya está activo, lo cerramos
                    li.classList.remove("active");
                    cancelRemove();
                    return;
                }

                // Desactivar otros li si quieres uno abierto a la vez
                lis.forEach(otherLi => {
                    if (otherLi !== li) {
                        otherLi.classList.remove("active");
                    }
                });

                li.classList.add("active");

                if (!li.matches(':hover') && !submenu.matches(':hover')) {
                    scheduleRemove();
                }
            });

            li.addEventListener("mouseenter", cancelRemove);
            submenu.addEventListener("mouseenter", cancelRemove);

            li.addEventListener("mouseleave", scheduleRemove);
            submenu.addEventListener("mouseleave", scheduleRemove);
        });
    });


    document.addEventListener("DOMContentLoaded", () => {
        const dropdown = document.querySelector(".submenu_dropdown");
        const buttons = dropdown.querySelectorAll(".submenu_dropdown-item button");

        buttons.forEach(button => {
            button.addEventListener("click", () => {
                const currentState = dropdown.getAttribute("data-state");
                const buttonId = button.getAttribute("data-id");

                if (currentState === buttonId) {
                    // Si ya estaba activo, lo reseteamos
                    dropdown.setAttribute("data-state", "0");
                } else {
                    // Si es otro, lo activamos
                    dropdown.setAttribute("data-state", buttonId);
                }
            });
        });
    });

    let header_toggle = document.querySelectorAll('.header_toggle');
    if(header_toggle && header){
        Array.from(header_toggle).forEach(header_toggle=>{
            header_toggle.addEventListener('click', (e)=>{
                e.preventDefault();
                header.classList.toggle('active');
            })
        })
    }


})();