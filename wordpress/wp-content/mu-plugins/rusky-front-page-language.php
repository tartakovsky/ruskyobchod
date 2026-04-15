<?php
/**
 * Plugin Name: Rusky Front Page Language
 * Description: Normalizes small front-page shell strings per storefront language.
 */

if (!defined('ABSPATH')) {
    exit;
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
        ]);
    }

    return strtr($html, [
        'Братислава • Палисады' => 'Bratislava • Palisády',
    ]);
}

add_filter('render_block', function($block_content, $block = []) {
    if (!is_string($block_content) || $block_content === '' || is_admin()) {
        return $block_content;
    }

    return rfpl_normalize_front_page_block($block_content);
}, 130, 2);

add_action('template_redirect', function() {
    if (is_admin() || !function_exists('is_front_page') || !is_front_page()) {
        return;
    }

    ob_start(static function($html) {
        if (!is_string($html) || $html === '') {
            return $html;
        }

        return rfpl_normalize_front_page_block($html);
    });
}, 130);
