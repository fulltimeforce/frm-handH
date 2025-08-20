import { Splide } from '@splidejs/splide';
import { AutoScroll } from '@splidejs/splide-extension-auto-scroll';
import "@splidejs/splide/css";

(function () {

    if(document.querySelector('#images')){
        new Splide('#images', {
            type: 'fade',
            arrows: false,
            pagination: true,
            perPage: 1,
            perMove: 1,
            rewind: true,
            breakpoints: {
            }
        }).mount();
    }

    if(document.querySelector('#timeline')){
        new Splide('#timeline', {
            arrows: true,
            drag: 'free',
            pagination: false,
            autoWidth: true,
            gap: '1.406vw',
            padding: {
                right: '21.979vw'
            },
        }).mount();
    }

    if(document.querySelector('#gallery')){
        new Splide('#gallery', {
            type: 'splide',
            arrows: false,
            pagination: true,
            perPage: 1,
            perMove: 1,
            breakpoints: {
            }
        }).mount();
    }

    if (document.querySelector('#cars1')) {
        new Splide('#cars1', {
            type: 'loop',
            arrows: false,
            pagination: false,
            focus: 'center',
            autoWidth: true,
            gap: '1.042vw',
            autoScroll: {
                speed: 0.4,
                pauseOnFocus: false,
                pauseOnHover: false,
            },
            breakpoints: {
                1540: {
                    gap: '16px'
                }
            }
        }).mount({ AutoScroll });
    }

    if (document.querySelector('#cars2')) {
        new Splide('#cars2', {
            type: 'loop',
            arrows: false,
            pagination: false,
            focus: 'center',
            autoWidth: true,
            gap: '1.042vw',
            autoScroll: {
                speed: -0.4,
                pauseOnFocus: false,
                pauseOnHover: false,
            },
            breakpoints: {
                1540: {
                    gap: '16px'
                }
            }
        }).mount({ AutoScroll });
    }

    if (document.querySelector('#text1')) {
        new Splide('#text1', {
            type: 'loop',
            arrows: false,
            drag: false,
            pagination: false,
            focus: 'center',
            autoWidth: true,
            gap: '2.5vw',
            autoScroll: {
                speed: 0.3,
                pauseOnFocus: false,
                pauseOnHover: false,
            },
            breakpoints: {
                1540: {
                    gap: '36px'
                }
            }
        }).mount({ AutoScroll });
    }

    if (document.querySelector('#text2')) {
        new Splide('#text2', {
            type: 'loop',
            arrows: false,
            drag: false,
            pagination: false,
            focus: 'center',
            autoWidth: true,
            gap: '2.5vw',
            autoScroll: {
                speed: -0.3,
                pauseOnFocus: false,
                pauseOnHover: false,
            },
            breakpoints: {
                1540: {
                    gap: '36px'
                }
            }
        }).mount({ AutoScroll });
    }

    let vehiclesThumbs = document.querySelectorAll('.vehicle_card-thumbs')
    if (vehiclesThumbs) {
        vehiclesThumbs.forEach(vehicle => {
            new Splide(vehicle, {
                type: 'loop',
                arrows: true,
                pagination: true,
                perPage: 1,
                perMove: 1,
                breakpoints: {
                }
            }).mount();
        })
    }

    let specialist = document.querySelector('#specialist')
    if (specialist) {
        new Splide('#specialist', {
            type: 'slider',
            arrows: true,
            pagination: false,
            autoWidth: true,
            gap: '1.094vw',
            breakpoints: {
                1540: {
                    gap: '17px'
                },
                768: {
                    perPage: 1,
                }
            }
        }).mount();

        let card_toggles = specialist.querySelectorAll('.card_toggle');
        if(card_toggles){
            Array.from(card_toggles).forEach(toggle=>{
                toggle.addEventListener('click', (e)=>{
                    e.preventDefault();
                    e.currentTarget.closest('.specialist_card').classList.toggle('active');
                })
            })
        }
    }

    if (document.querySelector('#clients')) {
        new Splide('#clients', {
            type: 'loop',
            arrows: true,
            pagination: false,
            autoWidth: true,
            gap: '1.094vw',
            breakpoints: {
                1540: {
                    gap: '17px'
                },
                768: {
                    perPage: 1,
                }
            }
        }).mount();
    }

    if (document.querySelector('#upcoming')) {
        let splide = new Splide('#upcoming', {
            focus: 0,
            start: 0,
            arrows: true,
            pagination: false,
            autoWidth: true,
            gap: '1.042vw',
            padding: {
                right: '9.375vw'
            },
            breakpoints: {
                1540: {
                    gap: '16px'
                },
                768: {
                    perPage: 1
                }
            }
        })

        splide.on("move", function () {
            let index = splide.index,
                slides = splide.Components.Elements.slides,
                el = slides[index];

            Array.from(slides).forEach(slide => {
                let vehicle = slide.querySelector('.vehicle');
                if (vehicle && vehicle.classList.contains('active')) {
                    vehicle.classList.remove('active')
                }
            })

            let v = el.querySelector('.vehicle')
            if (v && !v.classList.contains('active')) {
                v.classList.add('active');
            }
        });

        splide.mount();
    }

})();