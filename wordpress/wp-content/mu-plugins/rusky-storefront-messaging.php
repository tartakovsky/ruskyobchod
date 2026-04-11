<?php
/**
 * Plugin Name: Rusky Storefront Messaging
 * Description: Storefront-only merchandising messages extracted from the language layer.
 */

if (!defined('ABSPATH')) {
    exit;
}

function rsm_current_lang(): string {
    if (function_exists('gls_current_lang_code')) {
        $lang = gls_current_lang_code();
        if ($lang === 'ru' || $lang === 'sk') {
            return $lang;
        }
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

function rsm_free_shipping_banner() {
    $message = rsm_current_lang() === 'ru'
        ? '🚚 Бесплатная доставка при заказе от 100 €!'
        : '🚚 Doprava zadarmo pri objednávke nad 100 €!';

    echo '<div class="gls-free-shipping-banner" style="background:#2d5f2d;color:#fff;text-align:center;padding:12px 20px;border-radius:8px;margin-bottom:20px;font-size:15px;font-weight:500;">'
        . '<span>' . esc_html($message) . '</span>'
        . '</div>';
}
add_action('woocommerce_before_shop_loop', 'rsm_free_shipping_banner', 5);
add_action('woocommerce_before_cart', 'rsm_free_shipping_banner', 5);

function rsm_frozen_delivery_notice() {
    if (!function_exists('is_product_category') || !is_product_category('zamorozhennye-produkty-mrazene-potraviny')) {
        return;
    }

    echo '<div class="gls-frozen-notice" style="background:#fff6df;border:1px solid #e5c168;border-left:5px solid #c7921b;border-radius:12px;padding:18px 20px;margin:0 0 22px;color:#6d520d;">'
        . '<div class="gls-content-sk">'
        . '<strong style="display:block;font-size:18px;margin-bottom:8px;">⚠️ Dôležité! Doručenie mrazených produktov</strong>'
        . '<p style="margin:0;line-height:1.65;">Doručenie mrazených výrobkov z cesta (pelmene, vareniky, chinkali a pod.) a zmrzliny sa nerealizuje pri teplote okolitého prostredia nad 0°C. Prepravné spoločnosti nezaručujú dodržanie teplotného režimu pri doručení. Objednávka mrazených produktov cez stránku je možná len pri osobnom odbere v našej predajni.</p>'
        . '</div>'
        . '<div class="gls-content-ru">'
        . '<strong style="display:block;font-size:18px;margin-bottom:8px;">⚠️ Важно! Доставка замороженных продуктов</strong>'
        . '<p style="margin:0;line-height:1.65;">Доставка замороженных изделий из теста (пельмени, вареники, хинкали и др.) и мороженого не осуществляется при температуре окружающей среды выше 0°C. Транспортные компании не гарантируют соблюдение температурного режима при доставке. Заказ замороженных продуктов через сайт возможен только при самовывозе из нашего магазина.</p>'
        . '</div>'
        . '</div>';
}
add_action('woocommerce_before_shop_loop', 'rsm_frozen_delivery_notice', 6);

function rsm_footer_credit() {
    echo '<div class="gls-footer-credit">'
        . '<span class="gls-content-ru">&copy; ' . date('Y') . ' Гастроном — русский магазин продуктов</span>'
        . '<span class="gls-content-sk">&copy; ' . date('Y') . ' Gastronom — Ruský obchod s potravinami</span>'
        . '</div>';
}
add_action('wp_footer', 'rsm_footer_credit');
