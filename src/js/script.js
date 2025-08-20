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
            type      : 'fade',
            rewind    : true,
            autoplay  : true,
            interval  : 5000,
            speed     : 1000,
            arrows    : false,
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

  const searchInput    = document.getElementById("blog-search");
  const categorySelect = document.getElementById("blog-category");
  const perPageSelect  = document.getElementById("blog-perpage");

  if (searchInput || categorySelect || perPageSelect) {
    if (searchInput) {
      searchInput.addEventListener("keypress", function(e) {
        if (e.key === "Enter") {
          e.preventDefault();
          updateFilters();
        }
      });
    }

    if (categorySelect) {
      categorySelect.addEventListener("change", updateFilters);
    }

    if (perPageSelect) {
      perPageSelect.addEventListener("change", updateFilters);
    }

    function updateFilters() {
      let url = new URL(window.location.href);

      if (searchInput && searchInput.value) {
        url.searchParams.set("s", searchInput.value);
      } else {
        url.searchParams.delete("s");
      }

      if (categorySelect && categorySelect.value) {
        url.searchParams.set("category_name", categorySelect.value);
      } else {
        url.searchParams.delete("category_name");
      }

      if (perPageSelect && perPageSelect.value) {
        url.searchParams.set("posts_per_page", perPageSelect.value);
      } else {
        url.searchParams.delete("posts_per_page");
      }

      url.searchParams.delete("paged");
      window.location.href = url.toString();
    }
  }
});
