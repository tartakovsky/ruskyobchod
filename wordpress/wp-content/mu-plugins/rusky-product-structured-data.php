<?php
/**
 * Plugin Name: Rusky Product Structured Data
 * Description: Adds merchant-listing fields missing from WooCommerce product schema.
 */

if (!defined('ABSPATH')) {
    exit;
}

function rpsd_clean_schema_text(string $value): string {
    $value = wp_strip_all_tags(do_shortcode($value));
    $value = preg_replace('/\s+/', ' ', $value);

    return trim((string) $value);
}

function rpsd_localize_schema_text(string $value, string $lang): string {
    if (function_exists('gls_localize_bilingual_text')) {
        return rpsd_clean_schema_text((string) gls_localize_bilingual_text($value, $lang));
    }

    if (strpos($value, '/') !== false) {
        [$left, $right] = array_map('trim', explode('/', $value, 2));
        return rpsd_clean_schema_text($lang === 'ru' ? $left : $right);
    }

    return rpsd_clean_schema_text($value);
}

function rpsd_product_description(WC_Product $product): string {
    $description = rpsd_clean_schema_text((string) ($product->get_short_description() ?: $product->get_description()));
    if ($description !== '') {
        return $description;
    }

    $category_names = wp_get_post_terms($product->get_id(), 'product_cat', ['fields' => 'names']);
    $lang = function_exists('rca_current_lang') ? rca_current_lang() : 'sk';
    $category = '';
    if (!is_wp_error($category_names) && !empty($category_names)) {
        $localized_categories = array_map(function($name) use ($lang) {
            return rpsd_localize_schema_text((string) $name, $lang);
        }, array_slice($category_names, 0, 2));
        $localized_categories = array_filter($localized_categories);
        $category = implode(', ', $localized_categories);
    }

    if ($lang === 'ru') {
        return rpsd_clean_schema_text(sprintf(
            '%s: товар магазина Gastronom Bratislava%s. Доступность, цена и условия доставки указаны на странице товара.',
            $product->get_name(),
            $category !== '' ? ' в категории ' . $category : ''
        ));
    }

    return rpsd_clean_schema_text(sprintf(
        '%s: produkt obchodu Gastronom Bratislava%s. Dostupnost, cena a moznosti dorucenia su uvedene na stranke produktu.',
        $product->get_name(),
        $category !== '' ? ' v kategorii ' . $category : ''
    ));
}

function rpsd_product_brand(WC_Product $product): ?array {
    $brand_taxonomies = ['product_brand', 'pa_brand', 'pa_znacka', 'pa_značka', 'pa_producer', 'pa_vyrobca'];

    foreach ($brand_taxonomies as $taxonomy) {
        if (!taxonomy_exists($taxonomy)) {
            continue;
        }

        $terms = wp_get_post_terms($product->get_id(), $taxonomy);
        if (is_wp_error($terms) || empty($terms)) {
            continue;
        }

        return [
            '@type' => 'Brand',
            'name' => rpsd_clean_schema_text((string) $terms[0]->name),
        ];
    }

    $attribute_names = ['brand', 'znacka', 'značka', 'producer', 'vyrobca'];
    foreach ($attribute_names as $attribute_name) {
        $value = rpsd_clean_schema_text((string) $product->get_attribute($attribute_name));
        if ($value === '') {
            continue;
        }

        $parts = array_filter(array_map('trim', explode(',', $value)));
        $name = reset($parts) ?: $value;

        return [
            '@type' => 'Brand',
            'name' => $name,
        ];
    }

    return null;
}

function rpsd_gtin_checksum_valid(string $gtin): bool {
    if (!preg_match('/^(\d{8}|\d{12,14})$/', $gtin)) {
        return false;
    }

    $digits = array_map('intval', str_split($gtin));
    $check_digit = array_pop($digits);
    $sum = 0;
    $reversed = array_reverse($digits);

    foreach ($reversed as $index => $digit) {
        $sum += $digit * ($index % 2 === 0 ? 3 : 1);
    }

    return ((10 - ($sum % 10)) % 10) === $check_digit;
}

function rpsd_product_gtin_from_sku(WC_Product $product): string {
    $sku = preg_replace('/[^0-9]/', '', (string) $product->get_sku());
    if (!is_string($sku) || !rpsd_gtin_checksum_valid($sku)) {
        return '';
    }

    return $sku;
}

function rpsd_product_shipping_weight(WC_Product $product): float {
    $weight = (float) $product->get_weight();
    if ($weight > 0) {
        return $weight;
    }

    if ($product->is_type('variable')) {
        foreach ($product->get_children() as $child_id) {
            $child = wc_get_product($child_id);
            if ($child instanceof WC_Product && (float) $child->get_weight() > 0) {
                return (float) $child->get_weight();
            }
        }
    }

    return 0.0;
}

function rpsd_product_schema_price(WC_Product $product): float {
    if ($product->is_type('variable')) {
        return (float) $product->get_variation_price('min', true);
    }

    return (float) wc_get_price_including_tax($product);
}

function rpsd_shipping_rate_from_settings(array $settings, WC_Product $product): float {
    $default_price = isset($settings['shipping_price']) ? (float) str_replace(',', '.', (string) $settings['shipping_price']) : 0.0;
    $free_threshold = isset($settings['free_shipping_threshold']) ? (float) str_replace(',', '.', (string) $settings['free_shipping_threshold']) : 0.0;

    if ($free_threshold > 0 && rpsd_product_schema_price($product) >= $free_threshold) {
        return 0.0;
    }

    $raw_rates = (string) ($settings['weight_based_rates'] ?? '');
    if (trim($raw_rates) === '') {
        return $default_price;
    }

    $rates = [];
    foreach (preg_split('/\r\n|\r|\n/', $raw_rates) as $line) {
        $parts = array_map('trim', explode('|', $line));
        if (count($parts) !== 2) {
            continue;
        }

        $weight = (float) str_replace(',', '.', $parts[0]);
        $price = (float) str_replace(',', '.', $parts[1]);
        if ($weight > 0 && $price >= 0) {
            $rates[] = ['weight' => $weight, 'price' => $price];
        }
    }

    if (empty($rates)) {
        return $default_price;
    }

    usort($rates, function($a, $b) {
        return $a['weight'] <=> $b['weight'];
    });

    $product_weight = rpsd_product_shipping_weight($product);
    foreach ($rates as $rate) {
        if ($product_weight <= $rate['weight']) {
            return (float) $rate['price'];
        }
    }

    return (float) end($rates)['price'];
}

function rpsd_offer_shipping_detail(string $country, float $rate, int $min_days, int $max_days): array {
    return [
        '@type' => 'OfferShippingDetails',
        'shippingDestination' => [
            '@type' => 'DefinedRegion',
            'addressCountry' => $country,
        ],
        'shippingRate' => [
            '@type' => 'MonetaryAmount',
            'value' => wc_format_decimal($rate, 2),
            'currency' => get_woocommerce_currency(),
        ],
        'deliveryTime' => [
            '@type' => 'ShippingDeliveryTime',
            'handlingTime' => [
                '@type' => 'QuantitativeValue',
                'minValue' => 0,
                'maxValue' => 1,
                'unitCode' => 'DAY',
            ],
            'transitTime' => [
                '@type' => 'QuantitativeValue',
                'minValue' => $min_days,
                'maxValue' => $max_days,
                'unitCode' => 'DAY',
            ],
        ],
    ];
}

function rpsd_product_shipping_details(WC_Product $product): array {
    $details = [];
    $methods = [
        [
            'country' => 'SK',
            'option' => 'woocommerce_gls_shipping_method_zones_3_settings',
            'min_days' => 1,
            'max_days' => 3,
        ],
        [
            'country' => 'AT',
            'option' => 'woocommerce_gls_shipping_method_zones_7_settings',
            'min_days' => 2,
            'max_days' => 5,
        ],
    ];

    foreach ($methods as $method) {
        $settings = get_option($method['option'], []);
        if (!is_array($settings)) {
            continue;
        }

        $details[] = rpsd_offer_shipping_detail(
            $method['country'],
            rpsd_shipping_rate_from_settings($settings, $product),
            $method['min_days'],
            $method['max_days']
        );
    }

    return $details;
}

function rpsd_return_policy_id(string $country = 'SK'): string {
    return home_url('/obchodne-podmienky/#return-policy-' . strtolower($country));
}

function rpsd_return_policy_schema(string $country = 'SK'): array {
    $country = strtoupper($country);

    return [
        '@type' => 'MerchantReturnPolicy',
        '@id' => rpsd_return_policy_id($country),
        'merchantReturnLink' => home_url('/obchodne-podmienky/'),
        'applicableCountry' => $country,
        'returnPolicyCountry' => 'SK',
        'returnPolicyCategory' => 'https://schema.org/MerchantReturnFiniteReturnWindow',
        'merchantReturnDays' => 14,
        'itemCondition' => 'https://schema.org/NewCondition',
        'returnMethod' => 'https://schema.org/ReturnByMail',
        'returnFees' => 'https://schema.org/ReturnFeesCustomerResponsibility',
        'refundType' => 'https://schema.org/FullRefund',
        'returnLabelSource' => 'https://schema.org/ReturnLabelCustomerResponsibility',
    ];
}

function rpsd_add_offer_shipping_details(array $offer, WC_Product $product): array {
    if (!empty($offer['shippingDetails'])) {
        return $offer;
    }

    $shipping_details = rpsd_product_shipping_details($product);
    if (!empty($shipping_details)) {
        $offer['shippingDetails'] = $shipping_details;
    }

    return $offer;
}

function rpsd_add_offer_return_policy(array $offer): array {
    if (!empty($offer['hasMerchantReturnPolicy'])) {
        return $offer;
    }

    $offer['hasMerchantReturnPolicy'] = [
        '@id' => rpsd_return_policy_id(),
    ];

    return $offer;
}

function rpsd_enhance_product_structured_data($markup, $product) {
    if (!is_array($markup) || !$product instanceof WC_Product) {
        return $markup;
    }

    if (empty($markup['description'])) {
        $markup['description'] = rpsd_product_description($product);
    }

    if (empty($markup['brand'])) {
        $brand = rpsd_product_brand($product);
        if ($brand !== null) {
            $markup['brand'] = $brand;
        }
    }

    if (empty($markup['gtin'])) {
        $gtin = rpsd_product_gtin_from_sku($product);
        if ($gtin !== '') {
            $markup['gtin'] = $gtin;
        }
    }

    if (!empty($markup['offers'])) {
        if (isset($markup['offers']['@type'])) {
            $markup['offers'] = rpsd_add_offer_shipping_details($markup['offers'], $product);
            $markup['offers'] = rpsd_add_offer_return_policy($markup['offers']);
        } elseif (is_array($markup['offers'])) {
            foreach ($markup['offers'] as $index => $offer) {
                if (is_array($offer)) {
                    $markup['offers'][$index] = rpsd_add_offer_shipping_details($offer, $product);
                    $markup['offers'][$index] = rpsd_add_offer_return_policy($markup['offers'][$index]);
                }
            }
        }
    }

    return $markup;
}
add_filter('woocommerce_structured_data_product', 'rpsd_enhance_product_structured_data', 30, 2);

function rpsd_render_store_return_policy_schema(): void {
    if (is_admin() || wp_doing_ajax()) {
        return;
    }

    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        '@id' => home_url('/#online-store'),
        'name' => get_bloginfo('name'),
        'url' => home_url('/'),
        'hasMerchantReturnPolicy' => [
            rpsd_return_policy_schema('SK'),
            rpsd_return_policy_schema('AT'),
        ],
    ];

    echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>' . "\n";
}
add_action('wp_head', 'rpsd_render_store_return_policy_schema', 25);
