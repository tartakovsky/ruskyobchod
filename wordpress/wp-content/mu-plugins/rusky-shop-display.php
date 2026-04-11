<?php
/**
 * Plugin Name: Rusky Shop Display
 * Description: Isolates shop layout and cart-count display tweaks from the language layer.
 */

if (!defined('ABSPATH')) {
    exit;
}

function rsd_shop_columns() {
    return 4;
}
add_filter('loop_shop_columns', 'rsd_shop_columns', 999);

function rsd_products_per_page() {
    return 12;
}
add_filter('loop_shop_per_page', 'rsd_products_per_page', 999);

add_filter('woocommerce_cart_contents_count', function($count) {
    if (function_exists('WC') && WC()->cart) {
        return count(WC()->cart->get_cart());
    }

    return $count;
});
