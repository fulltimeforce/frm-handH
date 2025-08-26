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

    if (document.querySelector('#logos1')) {
        new Splide('#logos1', {
            type: 'loop',
            arrows: false,
            pagination: false,
            rewind: true,
            autoWidth: true,
            gap: '5vw',
            autoScroll: {
                pauseOnFocus: false,
                pauseOnHover: false,
            },
            breakpoints: {
                1420: {
                    gap: '52px'
                },
                768: {
                    gap: '36px'
                }
            }
        }).mount({ AutoScroll });
    }

    if (document.querySelector('#logos2')) {
        new Splide('#logos2', {
            type: 'loop',
            arrows: false,
            pagination: false,
            rewind: true,
            autoWidth: true,
            gap: '7.5vw',
            autoScroll: {
                pauseOnFocus: false,
                pauseOnHover: false,
            },
            breakpoints: {
                1420: {
                    gap: '64px'
                },
                768: {
                    gap: '44px'
                }
            }
        }).mount({ AutoScroll });
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
                1420: {
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
                1420: {
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
                1420: {
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
                1420: {
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
                1420: {
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
                1420: {
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
                1420: {
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
        const interval = 6000;
        const progress = document.querySelector('.progress');
        const chooseus = new Splide('#whychooseSplide', {
            type: 'fade',
            rewind: true,
            autoplay: true,
            interval: 6000,
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
                1420: {
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

    if (document.querySelector('#heritage')) {
        const interval = 6000;
        const progress = document.querySelector('.progress');
        const heritageMain = document.querySelector('.heritage_images-main');
        const heritage = new Splide('#heritage', {
            type: 'fade',
            rewind: true,
            autoplay: true,
            interval: interval,
            speed: 1000,
            arrows: false,
            pagination: false
        });

        heritage.on('mounted move', () => {
            progress.style.transition = 'none';
            progress.style.width = '0%';
            void progress.offsetWidth;
            progress.style.transition = `width ${interval}ms linear`;
            progress.style.width = '100%';
        });

        heritage.on('move', (newIndex, oldIndex) => {
            const oldSlide = heritage.Components.Slides.getAt(oldIndex).slide;
            const oldImg = oldSlide.querySelector('img');

            if (oldImg) {
                heritageMain.src = oldImg.src;
            }
        });

        heritage.mount();
    }


    // scrolled section - about
    if (document.querySelector('.imagelider')) {
        const wrapper = document.querySelector(".imagelider-wrapper");
        const viewportMask = document.querySelector(".imagelider");
        const slides = document.querySelectorAll(".imagelider .slide");
        if (!wrapper || !viewportMask || !slides.length) return;

        const SLIDE_H = 466;
        const N = slides.length;

        const setWrapperHeight = () => {
            const wrapperHeight = (SLIDE_H * N) + 250;
            wrapper.style.height = wrapperHeight + "px";
            const info = document.querySelector(".tailored_info-box");
            if (info) {
                console.log('entro')
                info.style.height = wrapperHeight + "px";
            }
        };

        const setZIndex = () => {
            slides.forEach((s, i) => (s.style.zIndex = i + 1));
        };

        const update = () => {
            const rect = wrapper.getBoundingClientRect();
            const totalScrollable = wrapper.offsetHeight - SLIDE_H;
            if (totalScrollable <= 0) return;

            const scrolledInside = Math.min(Math.max(-rect.top, 0), totalScrollable);
            const t = scrolledInside / totalScrollable;
            const phase = t * (N - 1);
            const i = Math.floor(phase);
            const p = phase - i;

            slides.forEach((s, k) => {
            if (k < i) {
                s.style.transform = "translateY(0%)";
            } else if (k === i) {
                s.style.transform = "translateY(0%)";
            } else if (k === i + 1) {
                s.style.transform = `translateY(${(1 - p) * 100}%)`;
            } else {
                s.style.transform = "translateY(100%)";
            }
            });
        };

        const onResize = () => {
            setWrapperHeight();
            update();
        };

        setWrapperHeight();
        setZIndex();
        update();

        window.addEventListener("scroll", update, { passive: true });
        window.addEventListener("resize", onResize);
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
