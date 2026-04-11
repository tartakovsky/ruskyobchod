<?php
/**
 * Plugin Name: Rusky Product Sorting
 * Description: Isolates product sorting meta generation and alphabetical catalog ordering.
 */

if (!defined('ABSPATH')) {
    exit;
}

function rps_strip_diacritics($text) {
    $map = [
        'á' => 'a', 'ä' => 'a', 'č' => 'c', 'ď' => 'd', 'é' => 'e', 'í' => 'i', 'ĺ' => 'l', 'ľ' => 'l',
        'ň' => 'n', 'ó' => 'o', 'ô' => 'o', 'ŕ' => 'r', 'š' => 's', 'ť' => 't', 'ú' => 'u', 'ý' => 'y', 'ž' => 'z',
        'Á' => 'A', 'Ä' => 'A', 'Č' => 'C', 'Ď' => 'D', 'É' => 'E', 'Í' => 'I', 'Ĺ' => 'L', 'Ľ' => 'L',
        'Ň' => 'N', 'Ó' => 'O', 'Ô' => 'O', 'Ŕ' => 'R', 'Š' => 'S', 'Ť' => 'T', 'Ú' => 'U', 'Ý' => 'Y', 'Ž' => 'Z',
    ];

    return strtr($text, $map);
}

function rps_parse_title($title) {
    if (strpos($title, ' / ') !== false) {
        $parts = explode(' / ', $title, 2);
        return [trim($parts[0]), trim($parts[1])];
    }
    if (strpos($title, '/ ') !== false) {
        $parts = explode('/ ', $title, 2);
        return [trim($parts[0]), trim($parts[1])];
    }
    if (strpos($title, ' /') !== false) {
        $parts = explode(' /', $title, 2);
        return [trim($parts[0]), trim($parts[1])];
    }

    return [trim($title), trim($title)];
}

function rps_update_sort_meta($post_id, $post, $update) {
    if ($post->post_type !== 'product') {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    [$sk_part, $ru_part] = rps_parse_title($post->post_title);

    update_post_meta($post_id, '_sort_sk', mb_strtolower(rps_strip_diacritics($sk_part), 'UTF-8'));
    update_post_meta($post_id, '_sort_ru', mb_strtolower($ru_part, 'UTF-8'));
}
add_action('save_post', 'rps_update_sort_meta', 20, 3);

function rps_update_sort_meta_wc($product_id) {
    $post = get_post($product_id);
    if (!$post) {
        return;
    }

    [$sk_part, $ru_part] = rps_parse_title($post->post_title);

    update_post_meta($product_id, '_sort_sk', mb_strtolower(rps_strip_diacritics($sk_part), 'UTF-8'));
    update_post_meta($product_id, '_sort_ru', mb_strtolower($ru_part, 'UTF-8'));
}
add_action('woocommerce_update_product', 'rps_update_sort_meta_wc', 20);
add_action('woocommerce_new_product', 'rps_update_sort_meta_wc', 20);

function rps_catalog_orderby($options) {
    unset($options['menu_order']);

    $lang = isset($_GET['lang']) ? sanitize_key(wp_unslash($_GET['lang'])) : '';
    if ($lang !== 'ru' && $lang !== 'sk') {
        $lang = function_exists('gastronom_current_lang')
            ? gastronom_current_lang()
            : (isset($_COOKIE['gastronom_lang']) ? sanitize_key(wp_unslash($_COOKIE['gastronom_lang'])) : 'sk');
    }

    $options['alphabetical'] = $lang === 'ru' ? 'По алфавиту' : 'Podľa abecedy';

    return $options;
}
add_filter('woocommerce_catalog_orderby', 'rps_catalog_orderby');

function rps_default_sorting() {
    return 'popularity';
}
add_filter('woocommerce_default_catalog_orderby', 'rps_default_sorting');

function rps_ordering_args($args) {
    if (isset($_GET['orderby']) && $_GET['orderby'] === 'alphabetical') {
        $lang = isset($_COOKIE['gastronom_lang']) ? $_COOKIE['gastronom_lang'] : 'sk';
        $meta_key = ($lang === 'ru') ? '_sort_ru' : '_sort_sk';
        $args['orderby'] = 'meta_value';
        $args['order'] = 'ASC';
        $args['meta_key'] = $meta_key;
    }

    return $args;
}
add_filter('woocommerce_get_catalog_ordering_args', 'rps_ordering_args');
