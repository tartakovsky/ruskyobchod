<?php
/**
 * Plugin Name: Rusky Order Language Helpers
 * Description: Extraction-ready helper surface for preorder/order language and localized order messaging.
 *
 * Scaffold status:
 * - safe to keep in the codebase locally
 * - not wired into the current preorder flow yet
 * - uses `rslh_*` names to avoid collisions with the existing `gastronom_*` functions
 */

if (!defined('ABSPATH')) {
    exit;
}

function rslh_current_lang(): string {
    $lang = isset($_COOKIE['gastronom_lang']) ? sanitize_key(wp_unslash($_COOKIE['gastronom_lang'])) : 'sk';

    return $lang === 'ru' ? 'ru' : 'sk';
}

function rslh_order_lang($order = null): string {
    if (is_numeric($order)) {
        $order = wc_get_order($order);
    }

    if ($order instanceof WC_Order) {
        $stored = sanitize_key((string) $order->get_meta('_gastronom_lang', true));
        if ($stored === 'ru' || $stored === 'sk') {
            return $stored;
        }
    }

    return rslh_current_lang();
}

function rslh_tt($order, string $ru, string $sk): string {
    return rslh_order_lang($order) === 'ru' ? $ru : $sk;
}

function rslh_t(string $ru, string $sk): string {
    return rslh_current_lang() === 'ru' ? $ru : $sk;
}

function rslh_locale_for_order($order = null): string {
    return rslh_order_lang($order) === 'ru' ? 'ru_RU' : 'sk_SK';
}

function rslh_with_order_locale($order, callable $callback) {
    $switched = false;
    if (function_exists('switch_to_locale')) {
        $switched = switch_to_locale(rslh_locale_for_order($order));
    }

    try {
        return $callback();
    } finally {
        if ($switched && function_exists('restore_previous_locale')) {
            restore_previous_locale();
        }
    }
}

function rslh_split_bilingual_title(string $title): array {
    foreach ([' / ', '/ ', ' /'] as $separator) {
        if (strpos($title, $separator) === false) {
            continue;
        }

        $parts = explode($separator, $title, 2);
        $left = trim((string) ($parts[0] ?? ''));
        $right = trim((string) ($parts[1] ?? ''));
        if ($left === '' || $right === '') {
            continue;
        }

        $left_cyr = preg_match('/[А-Яа-яЁё]/u', $left) === 1;
        $right_cyr = preg_match('/[А-Яа-яЁё]/u', $right) === 1;
        if ($left_cyr !== $right_cyr) {
            return [
                'sk' => $left_cyr ? $right : $left,
                'ru' => $left_cyr ? $left : $right,
            ];
        }

        return [
            'sk' => $left,
            'ru' => $right,
        ];
    }

    return [
        'sk' => trim($title),
        'ru' => trim($title),
    ];
}

function rslh_localize_title(string $title, string $lang): string {
    $parts = rslh_split_bilingual_title($title);

    return $parts[$lang === 'ru' ? 'ru' : 'sk'] ?? trim($title);
}

function rslh_localize_order_label(string $value, string $lang): string {
    $value = trim(html_entity_decode(wp_strip_all_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    if ($value === '') {
        return '';
    }

    $map = [
        'Osobne vyzdvihnutie' => ['ru' => 'Самовывоз', 'sk' => 'Osobne vyzdvihnutie'],
        'Platba pri doručení' => ['ru' => 'Оплата при получении', 'sk' => 'Platba pri doručení'],
        'Bankový prevod' => ['ru' => 'Банковский перевод', 'sk' => 'Bankový prevod'],
        'Platba kartou' => ['ru' => 'Оплата картой', 'sk' => 'Platba kartou'],
        'Platba po potvrdení hmotnosti' => ['ru' => 'Оплата после подтверждения веса', 'sk' => 'Platba po potvrdení hmotnosti'],
        'GLS доставка на адрес' => ['ru' => 'GLS доставка на адрес', 'sk' => 'GLS dorucenie na adresu'],
        'GLS dorucenie na adresu' => ['ru' => 'GLS доставка на адрес', 'sk' => 'GLS dorucenie na adresu'],
        'Packeta' => ['ru' => 'Packeta', 'sk' => 'Packeta'],
        'Osobný odber' => ['ru' => 'Самовывоз', 'sk' => 'Osobný odber'],
        'Самовывоз' => ['ru' => 'Самовывоз', 'sk' => 'Osobne vyzdvihnutie'],
        'Оплата при получении' => ['ru' => 'Оплата при получении', 'sk' => 'Platba pri doručení'],
        'Банковский перевод' => ['ru' => 'Банковский перевод', 'sk' => 'Bankový prevod'],
        'Оплата картой' => ['ru' => 'Оплата картой', 'sk' => 'Platba kartou'],
        'Оплата после подтверждения веса' => ['ru' => 'Оплата после подтверждения веса', 'sk' => 'Platba po potvrdení hmotnosti'],
    ];

    if (isset($map[$value])) {
        return $map[$value][$lang === 'ru' ? 'ru' : 'sk'];
    }

    return rslh_localize_title($value, $lang);
}

if (!function_exists('gastronom_current_lang')) {
    function gastronom_current_lang(): string {
        return rslh_current_lang();
    }
}

if (!function_exists('gastronom_order_lang')) {
    function gastronom_order_lang($order = null): string {
        return rslh_order_lang($order);
    }
}

if (!function_exists('gastronom_tt')) {
    function gastronom_tt($order, string $ru, string $sk): string {
        return rslh_tt($order, $ru, $sk);
    }
}

if (!function_exists('gastronom_t')) {
    function gastronom_t(string $ru, string $sk): string {
        return rslh_t($ru, $sk);
    }
}

if (!function_exists('gastronom_locale_for_order')) {
    function gastronom_locale_for_order($order = null): string {
        return rslh_locale_for_order($order);
    }
}

if (!function_exists('gastronom_with_order_locale')) {
    function gastronom_with_order_locale($order, callable $callback) {
        return rslh_with_order_locale($order, $callback);
    }
}

if (!function_exists('gastronom_split_bilingual_title')) {
    function gastronom_split_bilingual_title(string $title): array {
        return rslh_split_bilingual_title($title);
    }
}

if (!function_exists('gastronom_localize_title')) {
    function gastronom_localize_title(string $title, string $lang): string {
        return rslh_localize_title($title, $lang);
    }
}

if (!function_exists('gastronom_localize_order_label')) {
    function gastronom_localize_order_label(string $value, string $lang): string {
        return rslh_localize_order_label($value, $lang);
    }
}
