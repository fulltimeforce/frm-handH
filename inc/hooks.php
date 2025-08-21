<?php

// Hook solo en la página de carrito
add_action('woocommerce_before_cart', 'mi_contenido_personalizado_cart');
function mi_contenido_personalizado_cart() {
    if (is_cart()) {
        echo '<div class="cart_page-container">';
    }
}

// Hook solo en la página de carrito (después del carrito)
add_action('woocommerce_after_cart', 'mi_contenido_despues_cart');
function mi_contenido_despues_cart() {
    if (is_cart()) {
        echo '</div>';
    }
}
