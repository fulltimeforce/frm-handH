<?php

// Hook solo en la página de carrito
add_action('woocommerce_before_cart', 'mi_contenido_personalizado_cart');
function mi_contenido_personalizado_cart()
{
    if (is_cart()) {
        echo '<div class="cart_page-pt w-100">';
    }
}

// Hook solo en la página de carrito (después del carrito)
add_action('woocommerce_after_cart', 'mi_contenido_despues_cart');
function mi_contenido_despues_cart()
{
    if (is_cart()) {
        echo '</div>';
    }
}

// Hook solo en la página de checkout (antes del heading de "Tu pedido")
add_action('woocommerce_checkout_before_order_review_heading', 'mi_contenido_checkout_before_order_review_heading');
function mi_contenido_checkout_before_order_review_heading()
{
    if (is_checkout() && !is_order_received_page()) {
        // Aquí tu código personalizado
        echo '<div class="checkout_page-order">';
    }
}

// Hook solo en la página de checkout (después del bloque de order review)
add_action('woocommerce_checkout_after_order_review', 'mi_contenido_checkout_after_order_review');
function mi_contenido_checkout_after_order_review()
{
    if (is_checkout() && !is_order_received_page()) {
        // Aquí tu código personalizado
        echo '</div>';
    }
}

// Hook solo en checkout, antes de la sección de métodos de pago
add_action('woocommerce_review_order_before_payment', 'mi_contenido_before_payment');
function mi_contenido_before_payment()
{
    if (is_checkout() && !is_order_received_page()) {
        echo '</div><div class="checkout_page-payments">';
    }
}

// Redirigir "Mi cuenta" al editar cuenta
add_filter('woocommerce_login_redirect', function ($redirect, $user) {
    return wc_get_account_endpoint_url('edit-account');
}, 10, 2);

add_filter( 'woocommerce_form_field', function( $field, $key, $args, $value ) {
    // Quita el &nbsp; de los labels requeridos
    $field = str_replace('&nbsp;<span class="required"', '<span class="required"', $field);
    return $field;
}, 10, 4 );


/**
 * Limita la búsqueda a post_title cuando la query tenga el flag 'hnh_title_only'.
 */
if (!function_exists('hnh_search_only_in_title')) {
    function hnh_search_only_in_title(
        $search,
        \WP_Query $q
    ) {
        global $wpdb;

        // Solo actuar si nos pasan el flag y hay término de búsqueda
        if (!$q->get('hnh_title_only') || !$q->is_search() && $q->get('s') === '') {
            return $search;
        }

        $s = trim($q->get('s'));
        if ($s === '') {
            return $search;
        }

        // Divide en palabras y exige que TODAS estén en el título (AND)
        $terms = preg_split('/\s+/', $s);
        $pieces = [];
        foreach ($terms as $term) {
            $like = '%' . $wpdb->esc_like($term) . '%';
            $pieces[] = $wpdb->prepare("{$wpdb->posts}.post_title LIKE %s", $like);
        }

        if ($pieces) {
            // Reemplaza la búsqueda por defecto
            $search = ' AND (' . implode(' AND ', $pieces) . ') ';
        }

        return $search;
    }
}