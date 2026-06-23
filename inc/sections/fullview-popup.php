<?php
$gallery = get_field('gallery_vehicle');

if ($gallery && is_array($gallery)):
    // Normalizar a un arreglo con url y alt
    $imgs = [];
    foreach ($gallery as $item) {
        $url = $alt = '';

        if (is_array($item)) {               // ACF como array
            $id  = $item['ID'] ?? 0;
            $url = $item['url'] ?? ($id ? wp_get_attachment_image_url($id, 'full') : '');
            $alt = $item['alt'] ?? ($id ? get_post_meta($id, '_wp_attachment_image_alt', true) : ($item['title'] ?? ''));
        } elseif (is_numeric($item)) {       // ACF como ID
            $id  = (int) $item;
            $url = wp_get_attachment_image_url($id, 'full');
            $alt = get_post_meta($id, '_wp_attachment_image_alt', true);
        } else {                              // ACF como URL
            $url = $item;
            $alt = '';
        }

        if ($url) $imgs[] = ['url' => $url, 'alt' => $alt];
    }
?>

    <?php if (!empty($imgs)) : ?>

        <div style="display: none">
            <?php foreach ($imgs as $n => $img): ?>
                <input type="hidden" class="hidden_image_<?php echo intval($n) + 1; ?>" value="<?php echo esc_url($img['url']); ?>">
            <?php endforeach; ?>
        </div>

        <div class="listing_fullview">
            <div class="listing_fullview-close">
                <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 60 60" fill="none">
                    <rect width="60" height="60" rx="30" fill="#EEE9E2" fill-opacity="0.8" />
                    <path d="M42 42L18 18L30 30L18 42L42 18" stroke="#8C6E47" stroke-width="2" />
                </svg>
            </div>
            <div id="openGridView" class="listing_fullview-button">
                <img src="<?php echo IMG; ?>/grid-icon.svg" alt="icon">
            </div>

            <div class="listing_fullview-content">
                <div class="listing_fullview-slide splide">
                    <div class="splide__track">
                        <ul class="splide__list">
                            <?php foreach ($imgs as $img): ?>
                                <li class="splide__slide listing_fullview-item">
                                    <img class="wh-100" src="<?php echo esc_url($img['url']); ?>" alt="<?php echo esc_attr($img['alt'] ?: 'Vehicle Image'); ?>">
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
<script>
document.addEventListener("DOMContentLoaded", () => {

    const track = document.querySelector("#splide02 .splide__track");

    let isDragging = false;
    let startX = 0;
    let startY = 0;
    let currentX = 0;
    let currentY = 0;
    let scale = 1;

    const getActiveImg = () =>
        document.querySelector("#splide02 .splide__slide.is-active img");

    const reset = () => {
        const img = getActiveImg();
        if (!img) return;

        scale = 1;
        currentX = 0;
        currentY = 0;

        img.dataset.scale = 1;
        img.style.transform = "scale(1) translate(0px, 0px)";
    };

    document.querySelector("#splide02").addEventListener("wheel", (e) => {
        const img = getActiveImg();
        if (!img) return;

        e.preventDefault();
        e.stopPropagation();

        scale += e.deltaY < 0 ? 0.1 : -0.1;
        scale = Math.min(Math.max(scale, 1), 3);

        img.dataset.scale = scale;

        apply(img);
    }, { passive: false });

    track.addEventListener("mousedown", (e) => {
        const img = getActiveImg();
        if (!img) return;

        if (scale <= 1) return;

        isDragging = true;

        startX = e.clientX - currentX;
        startY = e.clientY - currentY;
    });

    document.addEventListener("mousemove", (e) => {
        const img = getActiveImg();
        if (!img || !isDragging) return;

        currentX = e.clientX - startX;
        currentY = e.clientY - startY;

        apply(img);
    });

    document.addEventListener("mouseup", () => {
        isDragging = false;
    });

    function apply(img) {
        img.style.transform = `translate(${currentX}px, ${currentY}px) scale(${scale})`;
        img.style.transformOrigin = "center center";
        img.style.cursor = scale > 1 ? "grab" : "zoom-in";
    }

    document.querySelector("#splide02").addEventListener("click", () => {
        reset();
    });

});
</script>
<script>
document.addEventListener("DOMContentLoaded", () => {
    const gridItems = document.querySelectorAll(".listing_grid-item");
    const closeBtn = document.querySelector(".listing_grid-close");
    const thumbnailPosts = document.querySelectorAll(".thumbnail-post");

    gridItems.forEach((item) => {
        item.addEventListener("click", () => {
            const idx = Number(item.dataset.fullviewIndex);
            if (Number.isNaN(idx)) return;

            if (closeBtn) closeBtn.click();

            if (thumbnailPosts && thumbnailPosts[idx]) {
                thumbnailPosts[idx].click();
            } else {
                const viewer = document.querySelector(".listing_fullview");
                viewer?.classList.add("active");
                document.body.style.overflow = "hidden";
            }

            setTimeout(() => {
                if (window.splide02) {
                    window.splide02.go(idx);
                } else {
                    console.error("Error: window.splide02 no está disponible. Asegúrate de haber guardado los cambios en el min.js");
                }
            }, 100);
        });
    });
});
</script>

<?php endif; ?>