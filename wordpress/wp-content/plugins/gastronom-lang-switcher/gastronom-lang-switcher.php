<?php
/**
 * Plugin Name: Gastronom Language Switcher
 * Description: Простой переключатель RU/SK для двуязычных названий товаров + стили сайта
 * Version: 6.18
 * Author: Gastronom
 */

if (!defined('ABSPATH')) exit;

function gls_current_lang_code() {
    if (is_admin() && !wp_doing_ajax()) {
        return 'sk';
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

function gls_brand_name() {
    return gls_current_lang_code() === 'ru' ? 'Гастроном' : 'Gastronom';
}

function gls_brand_description() {
    return gls_current_lang_code() === 'ru'
        ? 'русский магазин продуктов в Братиславе'
        : 'obchod s ruskými potravinami v Bratislave';
}

function gls_translate_static_title($title) {
    $lang = gls_current_lang_code();

    $map = [
        'Dostavka a platba' => [
            'ru' => 'Доставка и оплата',
            'sk' => 'Doprava a platba',
        ],
        'Doprava a platba' => [
            'ru' => 'Доставка и оплата',
            'sk' => 'Doprava a platba',
        ],
        'Kontakty' => [
            'ru' => 'Контакты',
            'sk' => 'Kontakt',
        ],
        'Kontakt' => [
            'ru' => 'Контакты',
            'sk' => 'Kontakt',
        ],
    ];

    if (isset($map[$title][$lang])) {
        return $map[$title][$lang];
    }

    return $title;
}

function gls_translate_menu_label(string $title, string $lang): string {
    $map = [
        'ru' => [
            'Domov' => 'Главная',
            'Doprava' => 'Доставка',
            'Kontakt' => 'Контакты',
            'Kontakty' => 'Контакты',
            'Môj účet' => 'Мой аккаунт',
            'Môj Účet' => 'Мой аккаунт',
            'Мой Аккаунт' => 'Мой аккаунт',
            'Моя учётная запись' => 'Мой аккаунт',
        ],
        'sk' => [
            'Главная' => 'Domov',
            'Доставка' => 'Doprava',
            'Контакты' => 'Kontakt',
            'Мой аккаунт' => 'Môj účet',
            'Мой Аккаунт' => 'Môj účet',
            'Моя учётная запись' => 'Môj účet',
        ],
    ];

    return $map[$lang][$title] ?? $title;
}

function gls_translate_account_checkout_phrase(string $value, string $lang): string {
    $map = [
        'ru' => [
            'Please log in to your account below to continue to the payment form.' => 'Пожалуйста, войдите в свою учётную запись, чтобы перейти к оплате.',
            'Пожалуйста войдите в вашу учетную запись ниже чтобы продожить к платежной форме.' => 'Пожалуйста, войдите в свою учётную запись, чтобы перейти к оплате.',
            'Username or email' => 'Имя пользователя или Email',
            'Password' => 'Пароль',
            'Remember me' => 'Запомнить меня',
            'Log in' => 'Войти',
            'Lost your password?' => 'Забыли пароль?',
            'Billing address' => 'Платёжный адрес',
            'My account' => 'Мой аккаунт',
            'Order' => 'Заказ',
        ],
        'sk' => [
            'Пожалуйста войдите в вашу учетную запись ниже чтобы продожить к платежной форме.' => 'Prihláste sa do svojho účtu, aby ste mohli pokračovať na platbu.',
            'Пожалуйста, войдите в свою учётную запись, чтобы перейти к оплате.' => 'Prihláste sa do svojho účtu, aby ste mohli pokračovať na platbu.',
            'Имя пользователя или Email' => 'Používateľské meno alebo e-mail',
            'Пароль' => 'Heslo',
            'Запомнить меня' => 'Zapamätať si ma',
            'Войти' => 'Prihlásiť sa',
            'Забыли пароль?' => 'Zabudli ste heslo?',
            'Платёжный адрес' => 'Fakturačná adresa',
            'Мой аккаунт' => 'Môj účet',
            'Заказ' => 'Objednávka',
            'Обязательно' => 'Povinné',
        ],
    ];

    return $map[$lang][$value] ?? $value;
}

add_filter('option_blogname', function($value) {
    if (is_admin() && !wp_doing_ajax()) {
        return $value;
    }

    return gls_brand_name();
}, 20);

add_filter('option_blogdescription', function($value) {
    if (is_admin() && !wp_doing_ajax()) {
        return $value;
    }

    return gls_brand_description();
}, 20);

add_filter('the_title', function($title, $post_id) {
    if (is_admin() || !$post_id) {
        return $title;
    }

    $front_id = (int) get_option('page_on_front');
    if ($front_id > 0 && (int) $post_id === $front_id) {
        return gls_brand_name();
    }

    return gls_translate_static_title($title);
}, 20, 2);

add_filter('document_title_parts', function($parts) {
    if (!is_front_page()) {
        return $parts;
    }

    $parts['title'] = gls_brand_name();
    $parts['tagline'] = gls_brand_description();

    return $parts;
}, 20);

add_filter('wpseo_title', function($title) {
    if (!is_front_page()) {
        return $title;
    }

    return gls_brand_name() . ' — ' . gls_brand_description();
}, 20);

// Force Stripe/WooPayments to use Slovak locale for card form
add_filter('wcpay_elements_options', function($options) {
    $options['locale'] = 'sk';
    return $options;
});
add_filter('wc_stripe_elements_options', function($options) {
    $options['locale'] = 'sk';
    return $options;
});

function gls_server_lang(): string {
    $query_lang = isset($_GET['lang']) ? sanitize_key(wp_unslash($_GET['lang'])) : '';
    if ($query_lang === 'ru' || $query_lang === 'sk') {
        return $query_lang;
    }

    $cookie_lang = isset($_COOKIE['gastronom_lang']) ? sanitize_key(wp_unslash($_COOKIE['gastronom_lang'])) : '';
    return $cookie_lang === 'ru' ? 'ru' : 'sk';
}

add_filter('gettext', function($translated, $text, $domain) {
    if (is_admin()) {
        return $translated;
    }

    $lang = gls_server_lang();

    if ((function_exists('is_account_page') && is_account_page()) || (function_exists('is_checkout_pay_page') && is_checkout_pay_page())) {
        $translated = gls_translate_account_checkout_phrase($translated, $lang);
        $translated = gls_translate_account_checkout_phrase($text, $lang) === $text ? $translated : gls_translate_account_checkout_phrase($text, $lang);
    }

    if ($lang === 'ru') {
        if ($translated === 'Моя учётная запись' || $translated === 'Мой Аккаунт') {
            return 'Мой аккаунт';
        }
    } else {
        if ($translated === 'Môj Účet') {
            return 'Môj účet';
        }
    }

    return $translated;
}, 20, 3);

add_filter('nav_menu_item_title', function($title) {
    $lang = gls_server_lang();

    return gls_translate_menu_label($title, $lang);
}, 20);

add_filter('the_title', function($title, $post_id = 0) {
    if (is_admin()) {
        return $title;
    }

    $lang = gls_server_lang();

    if (function_exists('is_checkout_pay_page') && is_checkout_pay_page()) {
        if ($lang === 'sk' && ($title === 'Оплатить заказ' || $title === 'Objednávka')) {
            return 'Zaplatiť objednávku';
        }
        if ($lang === 'ru' && ($title === 'Zaplatiť objednávku' || $title === 'Objednávka')) {
            return 'Оплатить заказ';
        }
    }

    if (function_exists('is_account_page') && is_account_page()) {
        if ($lang === 'ru') {
            if ($title === 'Môj účet' || $title === 'Môj Účet' || $title === 'Моя учётная запись' || $title === 'Мой Аккаунт') {
                return 'Мой аккаунт';
            }
        } else {
            if ($title === 'Мой аккаунт' || $title === 'Мой Аккаунт' || $title === 'Моя учётная запись') {
                return 'Môj účet';
            }
            if ($title === 'Môj Účet') {
                return 'Môj účet';
            }
        }
    }

    return $title;
}, 20, 2);

add_filter('pre_get_document_title', function($title) {
    $lang = gls_server_lang();

    if ($lang === 'ru') {
        $title = str_replace('Môj účet', 'Мой аккаунт', $title);
        $title = str_replace('Môj Účet', 'Мой аккаунт', $title);
        $title = str_replace('Моя учётная запись', 'Мой аккаунт', $title);
    } else {
        $title = str_replace('Мой аккаунт', 'Môj účet', $title);
        $title = str_replace('Мой Аккаунт', 'Môj účet', $title);
        $title = str_replace('Моя учётная запись', 'Môj účet', $title);
        $title = str_replace('Оплатить заказ', 'Zaplatiť objednávku', $title);
    }

    return $title;
}, 20);

function gls_normalize_public_title(string $title): string {
    $lang = gls_server_lang();

    if ($lang === 'ru') {
        $title = str_replace('Môj účet', 'Мой аккаунт', $title);
        $title = str_replace('Môj Účet', 'Мой аккаунт', $title);
        $title = str_replace('Моя учётная запись', 'Мой аккаунт', $title);
        $title = str_replace('Objednávka', 'Оплатить заказ', $title);
    } else {
        $title = str_replace('Мой аккаунт', 'Môj účet', $title);
        $title = str_replace('Мой Аккаунт', 'Môj účet', $title);
        $title = str_replace('Моя учётная запись', 'Môj účet', $title);
        $title = str_replace('Оплатить заказ', 'Zaplatiť objednávku', $title);
        $title = str_replace('Заказ', 'Objednávka', $title);
    }

    return $title;
}

add_filter('wpseo_title', 'gls_normalize_public_title', 20);
add_filter('wpseo_opengraph_title', 'gls_normalize_public_title', 20);
add_filter('wpseo_twitter_title', 'gls_normalize_public_title', 20);
add_filter('rank_math/frontend/title', 'gls_normalize_public_title', 20);
add_filter('rank_math/opengraph/facebook/title', 'gls_normalize_public_title', 20);
add_filter('rank_math/opengraph/twitter/title', 'gls_normalize_public_title', 20);

// Override locale for WooPayments scripts according to the current page language.
add_filter('wcpay_locale', function() {
    return gls_server_lang() === 'ru' ? 'ru_RU' : 'sk_SK';
});

// Override locale in ALL WooPayments script tags (before Stripe init)
add_filter('script_loader_tag', function($tag, $handle) {
    if (strpos($handle, 'wcpay') !== false || strpos($handle, 'WCPAY') !== false) {
        if (gls_server_lang() === 'ru') {
            $tag = str_replace('"locale":"sk"', '"locale":"ru"', $tag);
        } else {
            $tag = str_replace('"locale":"ru"', '"locale":"sk"', $tag);
        }
    }
    return $tag;
}, 10, 2);

// Override locale in wp_localize_script data before it's printed
add_action('wp_enqueue_scripts', function() {
    add_action('wp_print_footer_scripts', function() {
        if (!is_checkout()) return;
        global $wp_scripts;
        $target_locale = gls_server_lang() === 'ru' ? 'ru' : 'sk';
        $source_locale = $target_locale === 'ru' ? 'sk' : 'ru';
        // Replace locale in ALL registered scripts containing wcpay
        foreach ($wp_scripts->registered as $handle => $script) {
            if (strpos($handle, 'wcpay') === false) continue;
            // Replace in localized data (wp_localize_script)
            if (!empty($script->extra['data'])) {
                $wp_scripts->registered[$handle]->extra['data'] = str_replace(
                    ['"locale":"' . $source_locale . '"', '%22locale%22%3A%22' . $source_locale . '%22'],
                    ['"locale":"' . $target_locale . '"', '%22locale%22%3A%22' . $target_locale . '%22'],
                    $script->extra['data']
                );
            }
            // Replace in inline scripts (wp_add_inline_script)
            if (!empty($script->extra['before'])) {
                foreach ($script->extra['before'] as $i => $code) {
                    $wp_scripts->registered[$handle]->extra['before'][$i] = str_replace(
                        ['"locale":"' . $source_locale . '"', '%22locale%22%3A%22' . $source_locale . '%22'],
                        ['"locale":"' . $target_locale . '"', '%22locale%22%3A%22' . $target_locale . '%22'],
                        $code
                    );
                }
            }
            if (!empty($script->extra['after'])) {
                foreach ($script->extra['after'] as $i => $code) {
                    $wp_scripts->registered[$handle]->extra['after'][$i] = str_replace(
                        ['"locale":"' . $source_locale . '"', '%22locale%22%3A%22' . $source_locale . '%22'],
                        ['"locale":"' . $target_locale . '"', '%22locale%22%3A%22' . $target_locale . '%22'],
                        $code
                    );
                }
            }
        }
    }, 1);
}, 999);

// Keep WooPayments field labels aligned with the current page language.
add_action('wp_footer', function() {
    if (!is_checkout()) return;
    $lang = gls_server_lang();
    ?>
    <script>
    // Translate WooPayments card form labels and save checkbox
    (function() {
        const targetLang = <?php echo wp_json_encode($lang); ?>;
        const wcpayTranslations = targetLang === 'ru'
            ? {
                'Číslo karty': 'Номер карты',
                'Dátum expirácie': 'Окончание срока действия',
                'Bezpečnostný kód': 'Код безопасности',
                'Krajina': 'Страна',
                'Save payment information to my account for future purchases.': 'Сохранить платёжные данные для будущих покупок.',
                'Uložiť platobné údaje pre budúce nákupy.': 'Сохранить платёжные данные для будущих покупок.',
              }
            : {
                'Номер карты': 'Číslo karty',
                'Окончание срока действия': 'Dátum expirácie',
                'Код безопасности': 'Bezpečnostný kód',
                'Страна': 'Krajina',
                'Save payment information to my account for future purchases.': 'Uložiť platobné údaje pre budúce nákupy.',
              };
        function translateWcPay() {
            document.querySelectorAll('.wcpay-checkout label, .payment_method_woocommerce_payments label, #payment label').forEach(function(el) {
                el.childNodes.forEach(function(child) {
                    if (child.nodeType === 3) {
                        var txt = child.textContent.trim();
                        if (wcpayTranslations[txt]) {
                            child.textContent = child.textContent.replace(txt, wcpayTranslations[txt]);
                        }
                    }
                });
            });
        }
        // Run after WooPayments renders
        setTimeout(translateWcPay, 1000);
        setTimeout(translateWcPay, 3000);
        jQuery(document.body).on('updated_checkout', function() {
            setTimeout(translateWcPay, 500);
        });
    })();
    </script>
    <?php
});

// Force EUR symbol to always be € regardless of WP locale (ru_RU shows ₽ by default)
add_filter('woocommerce_currency_symbol', function($symbol, $currency) {
    if ($currency === 'EUR') {
        return '€';
    }
    return $symbol;
}, 999, 2);

function gls_enqueue_scripts() {
    wp_enqueue_style('gls-style', plugin_dir_url(__FILE__) . 'gls-style.css', [], '6.18');
}
add_action('wp_enqueue_scripts', 'gls_enqueue_scripts');

function gls_add_switcher() {
    echo '<div id="gls-switcher" class="gls-switcher">
        <button class="gls-btn gls-btn-ru" data-lang="ru" title="Русский">RU</button>
        <button class="gls-btn gls-btn-sk" data-lang="sk" title="Slovenčina">SK</button>
    </div>';
}
add_action('wp_body_open', 'gls_add_switcher');

// Free shipping banner on shop and cart pages
add_action('woocommerce_before_shop_loop', 'gls_free_shipping_banner', 5);
add_action('woocommerce_before_cart', 'gls_free_shipping_banner', 5);
function gls_free_shipping_banner() {
    echo '<div class="gls-free-shipping-banner" style="background:#2d5f2d;color:#fff;text-align:center;padding:12px 20px;border-radius:8px;margin-bottom:20px;font-size:15px;font-weight:500;">
        <span class="gls-banner-sk">🚚 Doprava zadarmo pri objednávke nad 100 €!</span>
        <span class="gls-banner-ru" style="display:none;">🚚 Бесплатная доставка при заказе от 100 €!</span>
    </div>';
}

add_action('woocommerce_before_shop_loop', 'gls_frozen_delivery_notice', 6);
function gls_frozen_delivery_notice() {
    if (!is_product_category('zamorozhennye-produkty-mrazene-potraviny')) {
        return;
    }

    echo '<div class="gls-frozen-notice" style="background:#fff6df;border:1px solid #e5c168;border-left:5px solid #c7921b;border-radius:12px;padding:18px 20px;margin:0 0 22px;color:#6d520d;">
        <div class="gls-content-sk">
            <strong style="display:block;font-size:18px;margin-bottom:8px;">⚠️ Dôležité! Doručenie mrazených produktov</strong>
            <p style="margin:0;line-height:1.65;">Doručenie mrazených výrobkov z cesta (pelmene, vareniky, chinkali a pod.) a zmrzliny sa nerealizuje pri teplote okolitého prostredia nad 0°C. Prepravné spoločnosti nezaručujú dodržanie teplotného režimu pri doručení. Objednávka mrazených produktov cez stránku je možná len pri osobnom odbere v našej predajni.</p>
        </div>
        <div class="gls-content-ru">
            <strong style="display:block;font-size:18px;margin-bottom:8px;">⚠️ Важно! Доставка замороженных продуктов</strong>
            <p style="margin:0;line-height:1.65;">Доставка замороженных изделий из теста (пельмени, вареники, хинкали и др.) и мороженого не осуществляется при температуре окружающей среды выше 0°C. Транспортные компании не гарантируют соблюдение температурного режима при доставке. Заказ замороженных продуктов через сайт возможен только при самовывозе из нашего магазина.</p>
        </div>
    </div>';
}

// Add custom footer credit
function gls_footer_credit() {
    echo '<div class="gls-footer-credit">'
        . '<span class="gls-content-ru">&copy; ' . date('Y') . ' Гастроном — русский магазин продуктов</span>'
        . '<span class="gls-content-sk">&copy; ' . date('Y') . ' Gastronom — Ruský obchod s potravinami</span>'
        . '</div>';
}
add_action('wp_footer', 'gls_footer_credit');

// --- Alphabetical sorting (auto sort keys + catalog ordering) ---

// Strip Slovak diacritics: á→a, č→c, š→s, etc.
function gls_strip_diacritics($text) {
    $map = [
        'á'=>'a','ä'=>'a','č'=>'c','ď'=>'d','é'=>'e','í'=>'i','ĺ'=>'l','ľ'=>'l',
        'ň'=>'n','ó'=>'o','ô'=>'o','ŕ'=>'r','š'=>'s','ť'=>'t','ú'=>'u','ý'=>'y','ž'=>'z',
        'Á'=>'A','Ä'=>'A','Č'=>'C','Ď'=>'D','É'=>'E','Í'=>'I','Ĺ'=>'L','Ľ'=>'L',
        'Ň'=>'N','Ó'=>'O','Ô'=>'O','Ŕ'=>'R','Š'=>'S','Ť'=>'T','Ú'=>'U','Ý'=>'Y','Ž'=>'Z',
    ];
    return strtr($text, $map);
}

// Parse bilingual title "Slovak / Russian" into parts
function gls_parse_title($title) {
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

// Auto-compute _sort_sk and _sort_ru on every product save
function gls_update_sort_meta($post_id, $post, $update) {
    if ($post->post_type !== 'product') return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    $title = $post->post_title;
    list($sk_part, $ru_part) = gls_parse_title($title);

    $sort_sk = mb_strtolower(gls_strip_diacritics($sk_part), 'UTF-8');
    $sort_ru = mb_strtolower($ru_part, 'UTF-8');

    update_post_meta($post_id, '_sort_sk', $sort_sk);
    update_post_meta($post_id, '_sort_ru', $sort_ru);
}
add_action('save_post', 'gls_update_sort_meta', 20, 3);

// Also hook into WC product save (covers REST API and Dotypos sync)
function gls_update_sort_meta_wc($product_id) {
    $post = get_post($product_id);
    if (!$post) return;

    $title = $post->post_title;
    list($sk_part, $ru_part) = gls_parse_title($title);

    $sort_sk = mb_strtolower(gls_strip_diacritics($sk_part), 'UTF-8');
    $sort_ru = mb_strtolower($ru_part, 'UTF-8');

    update_post_meta($product_id, '_sort_sk', $sort_sk);
    update_post_meta($product_id, '_sort_ru', $sort_ru);
}
add_action('woocommerce_update_product', 'gls_update_sort_meta_wc', 20);
add_action('woocommerce_new_product', 'gls_update_sort_meta_wc', 20);

// Custom sorting options: remove "Исходная сортировка", add alphabetical
function gls_catalog_orderby($options) {
    unset($options['menu_order']);
    $options['alphabetical'] = 'Podľa abecedy';
    return $options;
}
add_filter('woocommerce_catalog_orderby', 'gls_catalog_orderby');

function gls_default_sorting() {
    return 'popularity';
}
add_filter('woocommerce_default_catalog_orderby', 'gls_default_sorting');

// Handle alphabetical ordering args
function gls_ordering_args($args) {
    if (isset($_GET['orderby']) && $_GET['orderby'] === 'alphabetical') {
        $lang = isset($_COOKIE['gastronom_lang']) ? $_COOKIE['gastronom_lang'] : 'sk';
        $meta_key = ($lang === 'ru') ? '_sort_ru' : '_sort_sk';
        $args['orderby'] = 'meta_value';
        $args['order'] = 'ASC';
        $args['meta_key'] = $meta_key;
    }
    return $args;
}
add_filter('woocommerce_get_catalog_ordering_args', 'gls_ordering_args');

// Set product grid to 4 columns
function gls_shop_columns() {
    return 4;
}
add_filter('loop_shop_columns', 'gls_shop_columns', 999);

// 12 products per page (fills 4-column grid evenly: 3 rows x 4)
function gls_products_per_page() {
    return 12;
}
add_filter('loop_shop_per_page', 'gls_products_per_page', 999);

// Cart item count: count line items, not sum of quantities (weighted products have qty like 0.8)
add_filter('woocommerce_cart_contents_count', function($count) {
    if (WC()->cart) {
        return count(WC()->cart->get_cart());
    }
    return $count;
});

// --- Weighted products: fractional qty (0.01 kg step) ---

// Fix: WooCommerce uses intval() inside wc_stock_amount() BEFORE the filter,
// so 0.5 becomes 0. We re-read the raw quantity from the request for weighted products.
function gls_fix_add_to_cart_qty($quantity, $product_id) {
    if (get_post_meta($product_id, '_gls_weighted', true) === 'yes' && isset($_REQUEST['quantity'])) {
        return floatval(wp_unslash($_REQUEST['quantity']));
    }
    return $quantity;
}
add_filter('woocommerce_add_to_cart_quantity', 'gls_fix_add_to_cart_qty', 10, 2);

// Also fix stock amount filter for cart updates
add_filter('woocommerce_stock_amount', 'floatval');

// Fix: cart update for weighted products — WC calls intval() on qty which turns 0.8 into 0,
// then set_quantity($key, 0) removes the item. We intercept and set the correct decimal qty.
add_filter('woocommerce_update_cart_validation', function($passed, $cart_item_key, $values, $quantity) {
    if ($quantity > 0) return $passed; // Normal qty, no fix needed

    // Check if raw POST value was actually > 0 (decimal truncated by intval)
    if (!isset($_POST['cart'][$cart_item_key]['qty'])) return $passed;

    $raw_qty = floatval(str_replace(',', '.', wp_unslash($_POST['cart'][$cart_item_key]['qty'])));
    if ($raw_qty <= 0) return $passed; // User actually wants to remove

    $product_id = $values['product_id'];
    if (get_post_meta($product_id, '_gls_weighted', true) !== 'yes') return $passed;

    // Set the correct decimal quantity ourselves, skip WC's set_quantity(key, 0)
    WC()->cart->set_quantity($cart_item_key, $raw_qty, false);
    return false;
}, 10, 4);

// Override WC REST API schema to accept decimal stock_quantity
add_filter('woocommerce_rest_product_schema', function($schema) {
    if (isset($schema['properties']['stock_quantity'])) {
        $schema['properties']['stock_quantity']['type'] = 'number';
    }
    return $schema;
});

// Intercept product save to preserve decimal stock from any source
add_action('woocommerce_before_product_object_save', function($product) {
    $changes = $product->get_changes();
    if (isset($changes['stock_quantity'])) {
        $product->set_stock_quantity(floatval($changes['stock_quantity']));
        if (floatval($changes['stock_quantity']) > 0) {
            $product->set_stock_status('instock');
        }
    }
}, 10, 1);

// Add "Весовой товар" checkbox to product editor
function gls_weighted_product_field() {
    woocommerce_wp_checkbox([
        'id'          => '_gls_weighted',
        'label'       => 'Весовой товар',
        'description' => 'Позволяет покупателю выбирать дробное количество (0.1 кг)',
    ]);
}
add_action('woocommerce_product_options_general_product_data', 'gls_weighted_product_field');

// Save the checkbox value
function gls_save_weighted_field($post_id) {
    $value = isset($_POST['_gls_weighted']) ? 'yes' : 'no';
    update_post_meta($post_id, '_gls_weighted', $value);
}
add_action('woocommerce_process_product_meta', 'gls_save_weighted_field');

// Set qty step=0.1 and min=0.1 for weighted products
function gls_weighted_qty_args($args, $product) {
    if (get_post_meta($product->get_id(), '_gls_weighted', true) === 'yes') {
        $args['min_value'] = 0.01;
        $args['step'] = 0.01;
        $args['input_value'] = max($args['input_value'], 0.01);
    }
    return $args;
}
add_filter('woocommerce_quantity_input_args', 'gls_weighted_qty_args', 10, 2);

// Allow fractional qty in cart for weighted products
function gls_weighted_cart_qty($valid, $product_id, $quantity) {
    if (get_post_meta($product_id, '_gls_weighted', true) === 'yes') {
        return true;
    }
    return $valid;
}
add_filter('woocommerce_add_to_cart_validation', 'gls_weighted_cart_qty', 10, 3);

// Show "/ kg" suffix after price for weighted products
function gls_weighted_price_suffix($price, $product) {
    if (get_post_meta($product->get_id(), '_gls_weighted', true) === 'yes') {
        $price .= ' <span class="gls-price-unit">/ kg</span>';
    }
    return $price;
}
add_filter('woocommerce_get_price_html', 'gls_weighted_price_suffix', 10, 2);

// --- Temporary: Dotypos diagnostics & fix REST endpoints ---

add_action('rest_api_init', function() {
    // Diagnostic: read all dotypos-related options and transients
    register_rest_route('gls/v1', '/dotypos-diag', [
        'methods' => 'GET',
        'callback' => function() {
            global $wpdb;
            $opts = $wpdb->get_results(
                "SELECT option_name, LEFT(option_value, 1000) as option_value FROM {$wpdb->options} WHERE option_name LIKE '%dotypos%' ORDER BY option_name"
            );
            $transients = $wpdb->get_results(
                "SELECT option_name, LEFT(option_value, 500) as option_value FROM {$wpdb->options} WHERE option_name LIKE '%_transient_%dotypos%' ORDER BY option_name"
            );
            // Also check action scheduler for dotypos actions
            $as_table = $wpdb->prefix . 'actionscheduler_actions';
            $as_exists = $wpdb->get_var("SHOW TABLES LIKE '{$as_table}'");
            $as_actions = [];
            if ($as_exists) {
                $as_actions = $wpdb->get_results(
                    "SELECT action_id, hook, status, args, scheduled_date_gmt, last_attempt_gmt FROM {$as_table} WHERE hook LIKE '%dotypos%' ORDER BY action_id DESC LIMIT 20"
                );
            }
            return [
                'options' => $opts,
                'transients' => $transients,
                'action_scheduler' => $as_actions,
            ];
        },
        'permission_callback' => function() {
            return current_user_can('manage_options');
        },
    ]);

    // Fix: clear dotypos lock and stuck actions
    register_rest_route('gls/v1', '/dotypos-fix', [
        'methods' => 'POST',
        'callback' => function($request) {
            global $wpdb;
            $results = [];

            $action = $request->get_param('action');

            if ($action === 'clear_lock') {
                // Delete all dotypos lock options and transients
                $deleted = $wpdb->query(
                    "DELETE FROM {$wpdb->options} WHERE option_name LIKE '%dotypos%lock%' OR option_name LIKE '%_transient_%dotypos%lock%'"
                );
                $results['deleted_lock_options'] = $deleted;

                // Also clear any LOCKED state in dotypos options
                $wpdb->query(
                    "DELETE FROM {$wpdb->options} WHERE option_name LIKE '%dotypos%' AND option_value = 'LOCKED'"
                );
            }

            if ($action === 'clear_as_actions') {
                // Clear stuck dotypos Action Scheduler actions
                $as_table = $wpdb->prefix . 'actionscheduler_actions';
                $deleted = $wpdb->query(
                    "DELETE FROM {$as_table} WHERE hook LIKE '%dotypos%' AND status IN ('pending', 'in-progress', 'failed')"
                );
                $results['deleted_as_actions'] = $deleted;
            }

            if ($action === 'read_dotypos_php') {
                $file = WP_PLUGIN_DIR . '/woocommerce-extension-master/dotypos.php';
                if (file_exists($file)) {
                    $lines = file($file);
                    $results['total_lines'] = count($lines);
                    $from = intval($request->get_param('from')) ?: 1559;
                    $to = intval($request->get_param('to')) ?: ($from + 20);
                    $around = [];
                    for ($i = max(0, $from-1); $i < min(count($lines), $to); $i++) {
                        $around[$i+1] = $lines[$i];
                    }
                    $results['lines'] = $around;
                } else {
                    $results['error'] = 'File not found: ' . $file;
                }
            }

            if ($action === 'grep_dotypos') {
                $file = WP_PLUGIN_DIR . '/woocommerce-extension-master/dotypos.php';
                $pattern = $request->get_param('pattern');
                if (file_exists($file) && $pattern) {
                    $lines = file($file);
                    $matches = [];
                    foreach ($lines as $i => $line) {
                        if (stripos($line, $pattern) !== false) {
                            $matches[$i+1] = $line;
                        }
                    }
                    $results['matches'] = $matches;
                    $results['total_lines'] = count($lines);
                }
            }

            if ($action === 'read_plugin') {
                $plugin = $request->get_param('plugin');
                if ($plugin) {
                    $file = WP_PLUGIN_DIR . '/' . $plugin;
                    if (file_exists($file)) {
                        $results['content'] = file_get_contents($file);
                    } else {
                        $results['error'] = 'Not found: ' . $file;
                    }
                }
            }

            if ($action === 'write_plugin') {
                $plugin = $request->get_param('plugin');
                $content = $request->get_param('content');
                if ($plugin && $content) {
                    $file = WP_PLUGIN_DIR . '/' . $plugin;
                    if (file_exists($file)) {
                        file_put_contents($file, $content);
                        $results['written'] = true;
                    } else {
                        $results['error'] = 'Not found: ' . $file;
                    }
                }
            }

            if ($action === 'update_option') {
                $name = $request->get_param('name');
                $value = $request->get_param('value');
                if ($name && $value !== null) {
                    update_option($name, $value);
                    $results['updated'] = $name;
                    $results['value'] = get_option($name);
                }
            }

            if ($action === 'get_option') {
                $name = $request->get_param('name');
                if ($name) {
                    $results['value'] = get_option($name);
                }
            }

            if ($action === 'patch_dotypos') {
                // Fix the as_enqueue_async_action null args bug
                $file = WP_PLUGIN_DIR . '/woocommerce-extension-master/dotypos.php';
                if (file_exists($file)) {
                    $content = file_get_contents($file);
                    // Find as_enqueue_async_action calls and ensure args is array
                    $patched = preg_replace(
                        '/as_enqueue_async_action\(\s*([^,]+),\s*null\s*\)/',
                        'as_enqueue_async_action($1, [])',
                        $content
                    );
                    // Also fix cases where second arg might be missing
                    $patched = preg_replace(
                        '/as_enqueue_async_action\(\s*([^,\)]+)\s*\)/',
                        'as_enqueue_async_action($1, [])',
                        $patched
                    );
                    if ($patched !== $content) {
                        file_put_contents($file, $patched);
                        $results['patched'] = true;
                    } else {
                        $results['patched'] = false;
                        $results['note'] = 'No changes needed or pattern not found';
                    }
                }
            }

            return $results;
        },
        'permission_callback' => function() {
            return current_user_can('manage_options');
        },
    ]);
});

// Cart/Checkout UX: show pick-up point note on cart page
add_action('woocommerce_after_shipping_rate', function($method) {
    if (!is_cart()) return;

    $rate_id = $method->get_id();
    // Methods that require pick-up point selection
    $pickup_methods = [
        'gls_shipping_method_parcel_shop_zones',
        'gls_shipping_method_parcel_locker',
    ];
    // Also match packeta methods (dynamic instance IDs)
    $is_pickup = in_array($rate_id, $pickup_methods) || strpos($rate_id, 'packeta_method') === 0;

    if ($is_pickup) {
        echo '<p class="gls-pickup-note"><small>';
        echo '<span class="gls-pickup-note-sk">⟶ Výdajné miesto vyberiete v ďalšom kroku</span>';
        echo '<span class="gls-pickup-note-ru" style="display:none;">⟶ Пункт выдачи выберете на следующем шаге</span>';
        echo '</small></p>';
    }
}, 10, 1);
