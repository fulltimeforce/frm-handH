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

    if (document.querySelector('#brandsCar')) {
        document.addEventListener('DOMContentLoaded', function () {
            new Splide('#splide-models', {
                perPage: 4,
                gap: '20px',
                rewind: false,
                arrows: true,
                pagination: false,
                breakpoints: {
                    1024: { perPage: 3 },
                    768: { perPage: 2 },
                    480: { perPage: 1 },
                },
            }).mount();
        });
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

    if(document.querySelector('.listing_images-slider')) {
        const listing = new Splide( '.listing_images-slider', {
            type: 'loop',
            direction: 'ttb',
            height: '31.25vw',
            perPage: 2,
            perMove: 1,
            gap: '8px',
            pagination: false,
            arrows: true,
            wheel: true,
            breakpoints: {
                768: {
                    height: '400px',
                },
                1420: {
                    height: '450px',
                }
            }
        });
        listing.mount();
        listing.go(1);

        const prev = listing.root.querySelector('.splide__arrow--prev');
        const next = listing.root.querySelector('.splide__arrow--next');

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
        const mainImage = document.querySelector('.listing_images-main img');
        const counter   = document.getElementById('openFullView');
        const slides    = document.querySelectorAll('.listing_images-slider .splide__slide img');
        const total     = counter ? counter.dataset.total : slides.length;  

        if (counter) {
            counter.textContent = `1/${total}`;
        }
        const firstActive = document.querySelector('.listing_images-slider .splide__slide.is-active img');
        if (firstActive && mainImage) {
            mainImage.src = firstActive.src;
        }

        listing.on('move', function (newIndex) {
            const activeSlide = document.querySelector('.listing_images-slider .splide__slide.is-active img');
            if (activeSlide && mainImage) {
                mainImage.src = activeSlide.src;
            }
            if (counter) {
                counter.textContent = `${newIndex + 1}/${total}`;
            }
        });

    }

    if(document.querySelector(".listing_grid")) {
        const grid = document.querySelector(".listing_grid");
        const openBtn  = document.getElementById("openGrid");
        const closeBtn = document.querySelector(".listing_grid-close");

         openBtn.addEventListener("click", () => {
            grid.classList.add("active");
            document.body.style.overflow = "hidden";
            document.body.style.height = "100vh";
        });

        closeBtn.addEventListener("click", () => {
            grid.classList.remove("active");
            document.body.style.overflow = "";
            document.body.style.height = "auto";
        });
    }

    if(document.querySelector(".listing_fullview")) {
        const fullView = document.querySelector(".listing_fullview");
        const openBtn  = document.getElementById("openFullView");
        const openBtn2  = document.getElementById("openGridView");
        const closeBtn = document.querySelector(".listing_fullview-close");

        const fullViewSlide = new Splide( '.listing_fullview-slide', {
            type      : 'loop',
            perPage   : 1,
            perMove   : 1,
            arrows    : true,
            pagination: false,
            heightRatio: 0.75,
        });
        fullViewSlide.mount();

        const prev = fullViewSlide.root.querySelector('.splide__arrow--prev');
        const next = fullViewSlide.root.querySelector('.splide__arrow--next');

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

        openBtn.addEventListener("click", () => {
            fullView.classList.add("active");
            document.body.style.overflow = "hidden";
            document.body.style.height = "100vh";
        });

        openBtn2.addEventListener("click", () => {
            fullView.classList.remove("active");
            document.querySelector(".listing_grid").classList.add("active");
            document.body.style.overflow = "hidden";
            document.body.style.height = "100vh";
        });

        closeBtn.addEventListener("click", () => {
            fullView.classList.remove("active");
            document.body.style.overflow = "";
            document.body.style.height = "auto";
        });
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

document.addEventListener("DOMContentLoaded", () => {
  const container = document.querySelector(".animated_text");
  const textElement = document.querySelector(".animated_text-item");

  if (container && textElement) {
    const observer = new IntersectionObserver((entries, observer) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          animateText(textElement);
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.3 });

    observer.observe(container);
  }

  function animateText(textElement) {
    const text = textElement.textContent.trim();
    textElement.textContent = "";

    const words = text.split(" ");
    words.forEach((word, wIndex) => {
      const wordSpan = document.createElement("span");
      wordSpan.classList.add("word");

      word.split("").forEach(char => {
        const charSpan = document.createElement("span");
        charSpan.textContent = char;
        charSpan.classList.add("char");
        wordSpan.appendChild(charSpan);
      });

      textElement.appendChild(wordSpan);

      if (wIndex < words.length - 1) {
        textElement.appendChild(document.createTextNode(" "));
      }
    });

    const spans = textElement.querySelectorAll(".char");

    spans.forEach((span, i) => {
      setTimeout(() => {
        span.classList.add("visible");
      }, i * 80);
    });
  }
});

