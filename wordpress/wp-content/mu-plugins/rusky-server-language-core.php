<?php
/**
 * Plugin Name: Rusky Server Language Core
 * Description: Shared server-side language context and switcher rendering helpers.
 */

if (!defined('ABSPATH')) {
    exit;
}

function rslc_current_lang(): string {
    $query_lang = isset($_GET['lang']) ? sanitize_key(wp_unslash($_GET['lang'])) : '';
    if ($query_lang === 'ru' || $query_lang === 'sk') {
        return $query_lang;
    }

    $cookie_lang = isset($_COOKIE['gastronom_lang']) ? sanitize_key(wp_unslash($_COOKIE['gastronom_lang'])) : '';
    return $cookie_lang === 'ru' ? 'ru' : 'sk';
}

function rslc_switcher_url(string $lang): string {
    $current_url = home_url('/');

    if (!empty($_SERVER['HTTP_HOST']) && !empty($_SERVER['REQUEST_URI'])) {
        $scheme = is_ssl() ? 'https://' : 'http://';
        $current_url = $scheme . wp_unslash($_SERVER['HTTP_HOST']) . wp_unslash($_SERVER['REQUEST_URI']);
    }

    return add_query_arg('lang', $lang, $current_url);
}

function rslc_main_runtime_is_available(): bool {
    return function_exists('gls_server_lang') || function_exists('gls_add_switcher');
}

function rslc_lite_runtime_should_stand_down(): bool {
    return rslc_main_runtime_is_available();
}

function rslc_render_switcher(?string $lang = null, bool $in_header = false): string {
    $lang = $lang === 'ru' || $lang === 'sk' ? $lang : rslc_current_lang();
    $ru_class = $lang === 'ru' ? 'gls-btn gls-btn-ru active' : 'gls-btn gls-btn-ru';
    $sk_class = $lang === 'sk' ? 'gls-btn gls-btn-sk active' : 'gls-btn gls-btn-sk';
    $container_class = $in_header ? 'gls-switcher gls-in-header' : 'gls-switcher gls-floating';

    return '<div id="gls-switcher" class="' . esc_attr($container_class) . '">'
        . '<a class="' . esc_attr($ru_class) . '" data-lang="ru" href="' . esc_url(rslc_switcher_url('ru')) . '" title="Русский">RU</a>'
        . '<a class="' . esc_attr($sk_class) . '" data-lang="sk" href="' . esc_url(rslc_switcher_url('sk')) . '" title="Slovenčina">SK</a>'
        . '</div>';
}
