<?php
/**
 * Plugin Name: Rusky Front Page Language
 * Description: Normalizes small front-page shell strings per storefront language.
 */

if (!defined('ABSPATH')) {
    exit;
}

function rfpl_runtime_should_stand_down(): bool {
    return function_exists('rslc_main_runtime_is_available')
        ? rslc_main_runtime_is_available()
        : (function_exists('gls_server_lang') || function_exists('gls_add_switcher'));
}

function rfpl_current_lang(): string {
    if (function_exists('gls_server_lang')) {
        return gls_server_lang() === 'ru' ? 'ru' : 'sk';
    }

    if (isset($_GET['lang'])) {
        $lang = sanitize_key(wp_unslash($_GET['lang']));
        if ($lang === 'ru' || $lang === 'sk') {
            return $lang;
        }
    }

    if (isset($_COOKIE['gastronom_lang'])) {
        $lang = sanitize_key(wp_unslash($_COOKIE['gastronom_lang']));
        if ($lang === 'ru' || $lang === 'sk') {
            return $lang;
        }
    }

    return 'sk';
}

function rfpl_normalize_front_page_block(string $html): string {
    if (!function_exists('is_front_page') || !is_front_page()) {
        return $html;
    }

    $lang = rfpl_current_lang();

    if ($lang === 'ru') {
        return strtr($html, [
            'Братислава • Palisády' => 'Братислава • Палисады',
            'Bratislava • Palisády' => 'Братислава • Палисады',
            'aria-label="Top Menu"' => 'aria-label="Верхнее меню"',
            'aria-label="Footer"' => 'aria-label="Подвал"',
            '>Scroll Up<' => '>Прокрутить вверх<',
        ]);
    }

    return strtr($html, [
        'Братислава • Палисады' => 'Bratislava • Palisády',
        'aria-label="Top Menu"' => 'aria-label="Horné menu"',
        'aria-label="Footer"' => 'aria-label="Päta stránky"',
        '>Scroll Up<' => '>Späť hore<',
        '>Прокрутить вверх<' => '>Späť hore<',
    ]);
}

function rfpl_filter_render_block($block_content, $block = []) {
    if (!is_string($block_content) || $block_content === '' || is_admin() || rfpl_runtime_should_stand_down()) {
        return $block_content;
    }

    return rfpl_normalize_front_page_block($block_content);
}

function rfpl_filter_template_output_html($html) {
    if (!is_string($html) || $html === '') {
        return $html;
    }

    return rfpl_normalize_front_page_block($html);
}

function rfpl_start_template_output_buffer() {
    if (is_admin() || rfpl_runtime_should_stand_down() || !function_exists('is_front_page') || !is_front_page()) {
        return;
    }

    ob_start('rfpl_filter_template_output_html');
}

add_filter('render_block', 'rfpl_filter_render_block', 130, 2);
add_action('template_redirect', 'rfpl_start_template_output_buffer', 130);

add_action('plugins_loaded', function() {
    if (!rfpl_runtime_should_stand_down()) {
        return;
    }

    remove_filter('render_block', 'rfpl_filter_render_block', 130);
    remove_action('template_redirect', 'rfpl_start_template_output_buffer', 130);
}, 30);
