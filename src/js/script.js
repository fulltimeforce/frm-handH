import { Splide } from '@splidejs/splide';
import { AutoScroll } from '@splidejs/splide-extension-auto-scroll';
import "@splidejs/splide/css";

(function () {

    if (document.querySelector('#images')) {
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

    if (document.querySelector('#timeline')) {
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

    if (document.querySelector('#gallery')) {
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
        if (card_toggles) {
            Array.from(card_toggles).forEach(toggle => {
                toggle.addEventListener('click', (e) => {
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
                    gap: '16px',
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

    if (document.querySelector('#whychooseSplide')) {
        const interval = 5000;
        const progress = document.querySelector('.progress');
        const chooseus = new Splide('#whychooseSplide', {
            type: 'fade',
            rewind: true,
            autoplay: true,
            interval: 5000,
            speed: 1000,
            arrows: false,
            pagination: false
        });
        chooseus.on('mounted move', () => {
            progress.style.transition = 'none';
            progress.style.width = '0%';
            void progress.offsetWidth;
            progress.style.transition = `width ${interval}ms linear`;
            progress.style.width = '100%';

        });
        chooseus.mount();
    }

    if (document.querySelector('#pavilionGardens')) {
        const pavilionSplide = new Splide('#pavilionSlider', {
            focus: 0,
            start: 0,
            type: 'loop',
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
            },
        });

        pavilionSplide.mount();
    }

    if(document.querySelector('.trustpilot_reviews')) {
        const trustpilot = new Splide('.trustpilot_reviews', {
            type: 'loop',
            perPage: 1,
            perMove: 1,
            pagination: false,
            arrows: true,
            gap: '1rem',
        });

        trustpilot.mount();
        
        const prev = trustpilot.root.querySelector('.splide__arrow--prev');
        const next = trustpilot.root.querySelector('.splide__arrow--next');

        if (prev) {
            prev.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="15" height="26" viewBox="0 0 15 26" fill="none">
            <path d="M1 1L13 13L1 25" stroke="#8C6E47" stroke-width="2"/>
            </svg>`;
        }

        if (next) {
            next.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="15" height="26" viewBox="0 0 15 26" fill="none">
            <path d="M1 1L13 13L1 25" stroke="#8C6E47" stroke-width="2"/>
            </svg>`;
        }
    }

    if (document.querySelector('#imagelider')) {
    const imagelid = new Splide('#imagelider', {
        type: 'slide',
        direction: 'ttb',
        height: '466px',     
        perPage: 1, 
        perMove: 1,
        pagination: false,
        arrows: false,
        wheel: true,
        speed: 0,
    });

    const slides = document.querySelectorAll('#imagelider .splide__slide');

    slides.forEach(slide => {
        slide.style.position = 'absolute';
        slide.style.top = '0';
        slide.style.left = '0';
        slide.style.width = '100%';
        slide.style.height = '100%';
    });

    imagelid.on('move', (newIndex) => {
        slides.forEach((slide, index) => {
        slide.style.zIndex = index <= newIndex ? index + 1 : 0;
        });
    });

    imagelid.mount();
    }

    })();

document.addEventListener("DOMContentLoaded", () => {
    const sections = document.querySelectorAll(".title_watermark");

    if (sections.length > 0) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add("in-view");
                }
            });
        }, { threshold: 0.3 });

        sections.forEach(section => observer.observe(section));
    }

    if(document.getElementById('blog-perpage')) {
        document.getElementById('blog-perpage').addEventListener('change', function() {
            const url = new URL(window.location.href);
            url.searchParams.set('posts_per_page', this.value);
            window.location.href = url.toString();
        });
    }
});
