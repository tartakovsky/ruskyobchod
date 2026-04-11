<?php
/**
 * Plugin Name: Rusky Language Switcher Lite
 * Description: Emergency storefront shell for language switcher while the main language plugin is bypassed.
 */

if (!defined('ABSPATH')) {
    exit;
}

function rsll_current_lang(): string {
    if (function_exists('rslc_current_lang')) {
        return rslc_current_lang();
    }

    $query_lang = isset($_GET['lang']) ? sanitize_key(wp_unslash($_GET['lang'])) : '';
    if ($query_lang === 'ru' || $query_lang === 'sk') {
        return $query_lang;
    }

    $cookie_lang = isset($_COOKIE['gastronom_lang']) ? sanitize_key(wp_unslash($_COOKIE['gastronom_lang'])) : '';
    return $cookie_lang === 'ru' ? 'ru' : 'sk';
}

function rsll_switcher_url(string $lang): string {
    if (function_exists('rslc_switcher_url')) {
        return rslc_switcher_url($lang);
    }

    $current_url = home_url('/');

    if (!empty($_SERVER['HTTP_HOST']) && !empty($_SERVER['REQUEST_URI'])) {
        $scheme = is_ssl() ? 'https://' : 'http://';
        $current_url = $scheme . wp_unslash($_SERVER['HTTP_HOST']) . wp_unslash($_SERVER['REQUEST_URI']);
    }

    return add_query_arg('lang', $lang, $current_url);
}

function rsll_localize_bilingual_text(string $text, ?string $lang = null): string {
    $text = trim(html_entity_decode(wp_strip_all_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    if ($text === '') {
        return '';
    }

    $lang = $lang ?: rsll_current_lang();

    foreach ([' / ', '/ ', ' /', '/'] as $separator) {
        if (strpos($text, $separator) !== false) {
            $parts = array_map('trim', explode($separator, $text, 2));
            if (count($parts) === 2) {
                return $lang === 'ru' ? $parts[0] : $parts[1];
            }
        }
    }

    return $text;
}

function rsll_strip_inactive_language_blocks(string $html, string $lang): string {
    if (strpos($html, 'gls-content-') === false || !class_exists('DOMDocument')) {
        return $html;
    }

    $inactive = $lang === 'ru' ? 'gls-content-sk' : 'gls-content-ru';
    $internal_errors = libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $loaded = $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_NOWARNING | LIBXML_NOERROR);

    if (!$loaded) {
        libxml_clear_errors();
        libxml_use_internal_errors($internal_errors);
        return $html;
    }

    $xpath = new DOMXPath($dom);
    $nodes = $xpath->query(sprintf('//*[contains(concat(" ", normalize-space(@class), " "), " %s ")]', $inactive));
    if ($nodes instanceof DOMNodeList) {
        $to_remove = [];
        foreach ($nodes as $node) {
            $to_remove[] = $node;
        }
        foreach ($to_remove as $node) {
            if ($node->parentNode) {
                $node->parentNode->removeChild($node);
            }
        }
    }

    $result = $dom->saveHTML();
    $result = preg_replace('/^<\?xml.+?\?>/u', '', (string) $result);
    $result = str_replace('<p></p>', '', (string) $result);

    libxml_clear_errors();
    libxml_use_internal_errors($internal_errors);

    return (string) $result;
}

function rsll_normalize_front_page_html(string $html, string $lang): string {
    $html = rsll_strip_inactive_language_blocks($html, $lang);

    // Theme-level duplicate page chrome on the front page only.
    $html = preg_replace('~<div class="bradcrumbs">.*?</div>\s*~su', '', $html, 1);
    $html = preg_replace('~<h1 class="vw-page-title">.*?</h1>\s*~su', '', $html, 1);

    $html = preg_replace_callback(
        '~(<div class="gc-card-name">)(.*?)(</div>)~su',
        static function($matches) use ($lang) {
            $text = html_entity_decode(wp_strip_all_tags((string) ($matches[2] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            return (string) ($matches[1] ?? '') . esc_html(rsll_localize_bilingual_text($text, $lang)) . (string) ($matches[3] ?? '');
        },
        $html
    );

    $html = preg_replace_callback(
        '~(<li class="drp_dwn_menu[^"]*"[^>]*>\s*<a [^>]*>\s*)([^<]+)(\s*</a>)~su',
        static function($matches) use ($lang) {
            $text = html_entity_decode(trim((string) ($matches[2] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            return (string) ($matches[1] ?? '') . esc_html(rsll_localize_bilingual_text($text, $lang)) . (string) ($matches[3] ?? '');
        },
        $html
    );

    $menu_labels = $lang === 'ru'
        ? [
            '8567' => 'Главная',
            '10861' => 'Доставка',
            '189' => 'Контакты',
            '8568' => 'Мой аккаунт',
        ]
        : [
            '8567' => 'Domov',
            '10861' => 'Doprava',
            '189' => 'Kontakt',
            '8568' => 'Môj účet',
        ];

    foreach ($menu_labels as $item_id => $label) {
        $html = preg_replace(
            '~(<li id="menu-item-' . preg_quote($item_id, '~') . '".*?<a [^>]*>)(.*?)(</a>)~su',
            '$1' . esc_html($label) . '$3',
            $html,
            1
        );
    }

    $html = preg_replace(
        '~<script>\s*\(function\(\)\{\s*function patchCards\(\).*?\}\)\(\);\s*</script>~su',
        '',
        $html
    );

    if ($lang === 'ru') {
        $html = str_replace('>Контакт<', '>Контакты<', $html);
        $html = str_replace('>Мой Аккаунт<', '>Мой аккаунт<', $html);
        $html = str_replace('>Моя учётная запись<', '>Мой аккаунт<', $html);
        $html = str_replace('>Все рубрики<', '>Категории<', $html);
    } else {
        $map = [
            '>Главная<' => '>Domov<',
            '>Мой Аккаунт<' => '>Môj účet<',
            '>Мой аккаунт<' => '>Môj účet<',
            '>Моя учётная запись<' => '>Môj účet<',
            '>Искать:<' => '>Hľadať:<',
            '>Поиск<' => '>Vyhľadávanie<',
            '>Все рубрики<' => '>Všetky kategórie<',
            '>Кнопка Открыть<' => '>Tlačidlo Otvoriť<',
            '>Кнопка Закрыть<' => '>Tlačidlo Zavrieť<',
            '>Перейти к содержимому<' => '>Preskočiť na obsah<',
            'aria-label="Верхнее меню"' => 'aria-label="Horné menu"',
            'title="корзина"' => 'title="košík"',
            '>корзина<' => '>košík<',
            'title="Вход / Регистрация"' => 'title="Prihlásenie / Registrácia"',
            '>Моя учётная запись<' => '>Môj účet<',
        ];

        $html = strtr($html, $map);
    }

    $entity_map = $lang === 'ru'
        ? [
            '&#1052;&#1086;&#1081; &#1072;&#1082;&#1082;&#1072;&#1091;&#1085;&#1090;' => 'Мой аккаунт',
            '&#1050;&#1085;&#1086;&#1087;&#1082;&#1072; &#1054;&#1090;&#1082;&#1088;&#1099;&#1090;&#1100;' => 'Кнопка Открыть',
            '&#1050;&#1085;&#1086;&#1087;&#1082;&#1072; &#1047;&#1072;&#1082;&#1088;&#1099;&#1090;&#1100;' => 'Кнопка Закрыть',
            '&#1042;&#1077;&#1088;&#1093;&#1085;&#1077;&#1077; &#1084;&#1077;&#1085;&#1102;' => 'Верхнее меню',
        ]
        : [
            '&#1043;&#1083;&#1072;&#1074;&#1085;&#1072;&#1103;' => 'Domov',
            '&#1052;&#1086;&#1081; &#1072;&#1082;&#1082;&#1072;&#1091;&#1085;&#1090;' => 'Môj účet',
            '&#1050;&#1085;&#1086;&#1087;&#1082;&#1072; &#1054;&#1090;&#1082;&#1088;&#1099;&#1090;&#1100;' => 'Tlačidlo Otvoriť',
            '&#1050;&#1085;&#1086;&#1087;&#1082;&#1072; &#1047;&#1072;&#1082;&#1088;&#1099;&#1090;&#1100;' => 'Tlačidlo Zavrieť',
            '&#1042;&#1077;&#1088;&#1093;&#1085;&#1077;&#1077; &#1084;&#1077;&#1085;&#1102;' => 'Horné menu',
        ];

    $html = strtr($html, $entity_map);

    return $html;
}

add_action('init', function() {
    if (function_exists('rslc_lite_runtime_should_stand_down') && rslc_lite_runtime_should_stand_down()) {
        return;
    }

    $query_lang = isset($_GET['lang']) ? sanitize_key(wp_unslash($_GET['lang'])) : '';
    if ($query_lang !== 'ru' && $query_lang !== 'sk') {
        return;
    }

    setcookie('gastronom_lang', $query_lang, time() + YEAR_IN_SECONDS, COOKIEPATH ?: '/', COOKIE_DOMAIN, is_ssl(), false);
    $_COOKIE['gastronom_lang'] = $query_lang;
}, 1);

add_action('wp_enqueue_scripts', function() {
    if (function_exists('rslc_lite_runtime_should_stand_down') && rslc_lite_runtime_should_stand_down()) {
        return;
    }

    $style_url = content_url('plugins/gastronom-lang-switcher/gls-style.css');
    wp_enqueue_style('rsll-gls-style', $style_url, [], '6.24-lite');

    $active_lang = rsll_current_lang();
    $inline_css = '
        .gls-switcher{display:flex !important;align-items:center !important;flex-wrap:nowrap !important}
        .gls-switcher .gls-btn{display:inline-flex !important;align-items:center !important;justify-content:center !important;text-decoration:none !important}
        .gls-switcher .gls-btn.active{background:#1a3a5c !important;color:#fff !important}
    ';

    if ($active_lang === 'ru') {
        $inline_css .= '
            html{lang:ru-RU}
        ';
    } else {
        $inline_css .= '
            html{lang:sk-SK}
        ';
    }

    wp_add_inline_style('rsll-gls-style', $inline_css);
}, 20);

add_action('wp_body_open', function() {
    if (function_exists('rslc_lite_runtime_should_stand_down') && rslc_lite_runtime_should_stand_down()) {
        return;
    }

    if (function_exists('rslc_render_switcher')) {
        echo rslc_render_switcher(rsll_current_lang());
        return;
    }

    $lang = rsll_current_lang();
    $ru_class = $lang === 'ru' ? ' active' : '';
    $sk_class = $lang === 'sk' ? ' active' : '';

    echo '<div id="gls-switcher" class="gls-switcher">'
        . '<a class="gls-btn gls-btn-ru' . esc_attr($ru_class) . '" data-lang="ru" href="' . esc_url(rsll_switcher_url('ru')) . '" title="Русский">RU</a>'
        . '<a class="gls-btn gls-btn-sk' . esc_attr($sk_class) . '" data-lang="sk" href="' . esc_url(rsll_switcher_url('sk')) . '" title="Slovenčina">SK</a>'
        . '</div>';
}, 20);

add_action('template_redirect', function() {
    if (function_exists('rslc_lite_runtime_should_stand_down') && rslc_lite_runtime_should_stand_down()) {
        return;
    }

    if (is_admin() || wp_doing_ajax() || !is_front_page()) {
        return;
    }

    ob_start(static function($html) {
        if (!is_string($html) || $html === '') {
            return $html;
        }

        return rsll_normalize_front_page_html($html, rsll_current_lang());
    });
}, 1);
