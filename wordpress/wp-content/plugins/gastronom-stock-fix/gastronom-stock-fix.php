<?php
/**
 * Plugin Name: Gastronom Stock Fix
 * Description: Decimal stock support + auto outofstock + COD fee (for Dotypos integration)
 * Version: 3.3
 * Author: Gastronom
 */

// ============================================================
// 1. DECIMAL STOCK: allow fractional quantities (0.475 kg etc.)
// ============================================================
// Must run after WC loads (WC adds intval during plugins_loaded)
if (!function_exists('gastronom_setup_decimal_stock_amount')) {
function gastronom_setup_decimal_stock_amount(): void {
    remove_filter('woocommerce_stock_amount', 'intval');
    add_filter('woocommerce_stock_amount', 'floatval');
}
}
add_action('plugins_loaded', 'gastronom_setup_decimal_stock_amount', 99);

// Too noisy for this store: do not email admins about low stock thresholds.
add_filter('woocommerce_email_enabled_low_stock', '__return_false');
add_filter('woocommerce_email_enabled_no_stock', '__return_false');
add_filter('woocommerce_email_enabled_backorder', '__return_false');

if (!function_exists('gastronom_disable_stock_notifications')) {
function gastronom_disable_stock_notifications(): void {
    // Keep Woo settings aligned with store policy: no stock alert emails.
    if (get_option('woocommerce_notify_low_stock', 'yes') !== 'no') {
        update_option('woocommerce_notify_low_stock', 'no');
    }
    if (get_option('woocommerce_notify_no_stock', 'yes') !== 'no') {
        update_option('woocommerce_notify_no_stock', 'no');
    }
}
}
add_action('init', 'gastronom_disable_stock_notifications', 5);

if (!function_exists('gastronom_disable_stock_email_actions')) {
function gastronom_disable_stock_email_actions($mailer): void {
    if (!$mailer || !is_object($mailer)) {
        return;
    }

    // Hard-disable stock alert emails even if Woo tries to register them.
    remove_action('woocommerce_low_stock_notification', [$mailer, 'low_stock']);
    remove_action('woocommerce_no_stock_notification', [$mailer, 'no_stock']);
    remove_action('woocommerce_product_on_backorder_notification', [$mailer, 'backorder']);
}
}
add_action('woocommerce_email', 'gastronom_disable_stock_email_actions', 1);

if (!function_exists('gastronom_detach_dotypos_product_updated_hook')) {
function gastronom_detach_dotypos_product_updated_hook(): void {
    global $dotypos;

    if ($dotypos && is_object($dotypos) && method_exists($dotypos, 'handle_product_updated')) {
        remove_action('woocommerce_update_product', [$dotypos, 'handle_product_updated'], 10);
    }
}
}
add_action('plugins_loaded', 'gastronom_detach_dotypos_product_updated_hook', 100);

// ============================================================
// 2. REST API: change stock_quantity schema from integer to number
//    so API accepts decimal values like 0.475
// ============================================================
if (!function_exists('gastronom_adjust_rest_endpoints_for_decimal_stock')) {
function gastronom_adjust_rest_endpoints_for_decimal_stock($endpoints) {
    // Change stock_quantity type from integer to number in all product endpoints
    foreach ($endpoints as $route => $handlers) {
        if (strpos($route, 'wc/v3/products') === false) continue;
        foreach ($handlers as $key => $handler) {
            if (!is_array($handler) || !isset($handler['args']['stock_quantity'])) continue;
            $endpoints[$route][$key]['args']['stock_quantity']['type'] = 'number';
        }
    }
    return $endpoints;
}
}
add_filter('rest_endpoints', 'gastronom_adjust_rest_endpoints_for_decimal_stock');

if (!function_exists('gastronom_normalize_product_name')) {
function gastronom_normalize_product_name($name) {
    if (function_exists('rsn_normalize_product_name')) {
        return rsn_normalize_product_name($name);
    }

    $name = html_entity_decode(wp_strip_all_tags((string) $name), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    if (function_exists('mb_strtolower')) {
        $name = mb_strtolower($name, 'UTF-8');
    } else {
        $name = strtolower($name);
    }

    return trim(preg_replace('/\s+/', ' ', $name));
}
}

if (!function_exists('gastronom_catalog_policy')) {
function gastronom_catalog_policy($name) {
    if (function_exists('rsn_catalog_policy')) {
        return rsn_catalog_policy($name);
    }

    $normalized = gastronom_normalize_product_name($name);

    foreach ([
        'káva espresso',
        'кофе эспрессо',
        'taška veľká',
        'пакет большой',
        'taška malá',
        'пакет малый',
    ] as $needle) {
        if (strpos($normalized, $needle) !== false) {
            return 'service';
        }
    }

    foreach ([
        'пакет подарочный',
        'пакет нг',
        'пакет крафт нг',
        'novoroč',
        'vianoč',
        'darček',
    ] as $needle) {
        if (strpos($normalized, $needle) !== false) {
            return 'seasonal';
        }
    }

    return 'normal';
}
}

if (!function_exists('gastronom_reconcile_decimal_stock')) {
function gastronom_reconcile_decimal_stock($product_id) {
    if (function_exists('rsn_reconcile_decimal_stock')) {
        rsn_reconcile_decimal_stock($product_id);
        return;
    }

    $product_id = (int) $product_id;
    if ($product_id <= 0 || get_post_type($product_id) !== 'product') {
        return;
    }

    $raw_stock = get_post_meta($product_id, '_stock', true);
    $qty = ($raw_stock === '' || $raw_stock === false) ? 0.0 : floatval($raw_stock);
    $current_status = get_post_meta($product_id, '_stock_status', true);

    if ($qty <= 0 && $current_status !== 'outofstock') {
        wc_update_product_stock_status($product_id, 'outofstock');
        update_post_meta($product_id, '_backorders', 'no');
    } elseif ($qty > 0 && $current_status !== 'instock') {
        wc_update_product_stock_status($product_id, 'instock');
    }
}
}

if (!function_exists('gastronom_apply_catalog_policy')) {
function gastronom_apply_catalog_policy($product_id) {
    if (function_exists('rsn_apply_catalog_policy')) {
        rsn_apply_catalog_policy($product_id);
        return;
    }

    $product_id = (int) $product_id;
    if ($product_id <= 0 || get_post_type($product_id) !== 'product') {
        return;
    }

    $name = get_the_title($product_id);
    $policy = gastronom_catalog_policy($name);

    if ($policy === 'service') {
        remove_action('save_post_product', 'gastronom_enforce_product_rules', 20);
        wp_update_post([
            'ID' => $product_id,
            'post_status' => 'draft',
        ]);
        add_action('save_post_product', 'gastronom_enforce_product_rules', 20, 3);
        update_post_meta($product_id, '_catalog_visibility', 'hidden');
        return;
    }

    if ($policy === 'seasonal') {
        $seasonal_category = get_term_by('slug', 'tovary-po-sluchayu-sezonny-tovar', 'product_cat');
        if ($seasonal_category && !is_wp_error($seasonal_category)) {
            wp_set_post_terms($product_id, [(int) $seasonal_category->term_id], 'product_cat', false);
        }
        update_post_meta($product_id, '_catalog_visibility', 'visible');
    }
}
}

if (!function_exists('gastronom_enforce_product_rules')) {
function gastronom_enforce_product_rules($post_id, $post = null, $update = true) {
    if (function_exists('rsn_enforce_product_rules')) {
        rsn_enforce_product_rules($post_id, $post, $update);
        return;
    }

    if (wp_is_post_revision($post_id) || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)) {
        return;
    }

    gastronom_apply_catalog_policy($post_id);
    gastronom_reconcile_decimal_stock($post_id);
}
}

// ============================================================
// 3. AUTO OUTOFSTOCK: when stock changes, update status + visibility
//    Uses raw DB meta to avoid integer truncation
//    Uses wc_update_product_stock_status() to fix BOTH meta AND taxonomy
// ============================================================
if (!function_exists('gastronom_sync_stock_status_after_set_stock')) {
function gastronom_sync_stock_status_after_set_stock($product): void {
    $product_id = $product->get_id();
    gastronom_reconcile_decimal_stock($product_id);
}
}
add_action('woocommerce_product_set_stock', 'gastronom_sync_stock_status_after_set_stock', 10, 1);

// ============================================================
// 4. REST API INSERT: same logic for products updated via REST API
// ============================================================
if (!function_exists('gastronom_sync_stock_status_after_rest_insert')) {
function gastronom_sync_stock_status_after_rest_insert($product): void {
    if (!$product->managing_stock()) return;

    gastronom_reconcile_decimal_stock($product->get_id());
}
}
add_action('woocommerce_rest_insert_product_object', 'gastronom_sync_stock_status_after_rest_insert', 99, 1);

if (!function_exists('gastronom_reconcile_on_updated_post_meta')) {
function gastronom_reconcile_on_updated_post_meta($meta_id, $object_id, $meta_key): void {
    if ($meta_key === '_stock') {
        gastronom_reconcile_decimal_stock($object_id);
    }
}
}
add_action('updated_post_meta', 'gastronom_reconcile_on_updated_post_meta', 20, 3);

if (!function_exists('gastronom_reconcile_on_added_post_meta')) {
function gastronom_reconcile_on_added_post_meta($meta_id, $object_id, $meta_key): void {
    if ($meta_key === '_stock') {
        gastronom_reconcile_decimal_stock($object_id);
    }
}
}
add_action('added_post_meta', 'gastronom_reconcile_on_added_post_meta', 20, 3);

add_action('save_post_product', 'gastronom_enforce_product_rules', 20, 3);

// ============================================================
// 5. ONE-TIME FIX: on first load after update, fix all products
//    that have stock > 0 but are marked outofstock
//    Runs once, then sets a flag so it doesn't repeat
// ============================================================
if (!function_exists('gastronom_run_stock_fix_repair_once')) {
function gastronom_run_stock_fix_repair_once(): void {
    $fix_version = '3.3';
    if (get_option('gastronom_stock_fix_version') === $fix_version) return;

    global $wpdb;

    // Find products where _stock > 0 but _stock_status = 'outofstock'
    $broken = $wpdb->get_results("
        SELECT p.ID, sm.meta_value as stock_qty
        FROM {$wpdb->posts} p
        JOIN {$wpdb->postmeta} sm ON p.ID = sm.post_id AND sm.meta_key = '_stock'
        JOIN {$wpdb->postmeta} ss ON p.ID = ss.post_id AND ss.meta_key = '_stock_status'
        WHERE p.post_type = 'product'
        AND p.post_status = 'publish'
        AND CAST(sm.meta_value AS DECIMAL(10,3)) > 0
        AND ss.meta_value = 'outofstock'
    ");

    $fixed = 0;
    foreach ($broken as $row) {
        wc_update_product_stock_status($row->ID, 'instock');
        $fixed++;
    }

    // Also fix reverse: stock <= 0 but status = 'instock'
    $broken_reverse = $wpdb->get_results("
        SELECT p.ID
        FROM {$wpdb->posts} p
        JOIN {$wpdb->postmeta} sm ON p.ID = sm.post_id AND sm.meta_key = '_stock'
        JOIN {$wpdb->postmeta} ss ON p.ID = ss.post_id AND ss.meta_key = '_stock_status'
        WHERE p.post_type = 'product'
        AND p.post_status = 'publish'
        AND p.ID IN (SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_manage_stock' AND meta_value = 'yes')
        AND (sm.meta_value IS NULL OR CAST(sm.meta_value AS DECIMAL(10,3)) <= 0)
        AND ss.meta_value = 'instock'
    ");

    foreach ($broken_reverse as $row) {
        wc_update_product_stock_status($row->ID, 'outofstock');
        update_post_meta($row->ID, '_backorders', 'no');
        $fixed++;
    }

    $service_and_seasonal = get_posts([
        'post_type' => 'product',
        'post_status' => ['publish', 'draft', 'pending', 'private'],
        'numberposts' => -1,
        'fields' => 'ids',
    ]);

    foreach ($service_and_seasonal as $product_id) {
        gastronom_apply_catalog_policy($product_id);
        gastronom_reconcile_decimal_stock($product_id);
    }

    update_option('gastronom_stock_fix_version', $fix_version);

    if ($fixed > 0) {
        // Clear WC transients after fixing
        wc_delete_product_transients();

        // Log for debugging
        error_log("Gastronom Stock Fix v{$fix_version}: fixed {$fixed} products stock status + visibility");
    }
}
}
add_action('admin_init', 'gastronom_run_stock_fix_repair_once');

// ============================================================
// 6. COD FEE: +2 EUR at checkout when "Platba pri doruceni" selected
// ============================================================
if (!function_exists('gastronom_add_cod_fee')) {
function gastronom_cod_fee_label(): string {
    if (function_exists('rca_cod_fee_label')) {
        return rca_cod_fee_label();
    }

    return gastronom_t('Доплата за наложенный платёж', 'Poplatok za dobierku');
}

function gastronom_add_cod_fee(): void {
    if (function_exists('rca_add_cod_fee')) {
        rca_add_cod_fee();
        return;
    }

    if (is_admin() && !defined('DOING_AJAX')) return;
    if (!is_checkout()) return;
    $chosen_payment = WC()->session->get('chosen_payment_method');
    if ($chosen_payment === 'cod') {
        WC()->cart->add_fee(gastronom_cod_fee_label(), 2.00, false);
    }
}
}
add_action('woocommerce_cart_calculate_fees', 'gastronom_add_cod_fee');

if (!function_exists('gastronom_gateway_description')) {
function gastronom_gateway_description($description, $id) {
    if (function_exists('rca_current_lang')) {
        if ($id === 'cod') {
            return rca_current_lang() === 'ru'
                ? 'К заказу будет добавлена доплата за наложенный платёж 2,00 €.'
                : 'K objednávke bude pripočítaný poplatok za dobierku vo výške 2,00 €.';
        }

        if ($id === 'bacs') {
            return rca_current_lang() === 'ru'
                ? 'Оплатите заказ прямым банковским переводом на наш счёт. Заказ будет обработан после поступления оплаты.'
                : 'Zaplaťte priamym prevodom na náš bankový účet. Objednávka bude spracovaná po prijatí platby.';
        }

        return $description;
    }

    if ($id === 'cod') {
        $description = gastronom_t(
            'К заказу будет добавлена доплата за наложенный платёж 2,00 €.',
            'K objednávke bude pripočítaný poplatok za dobierku vo výške 2,00 €.'
        );
    }
    return $description;
}
}
add_filter('woocommerce_gateway_description', 'gastronom_gateway_description', 10, 2);

if (!function_exists('gastronom_render_checkout_payment_refresh_script')) {
function gastronom_render_checkout_payment_refresh_script(): void {
    if (function_exists('rca_render_checkout_payment_refresh_script')) {
        rca_render_checkout_payment_refresh_script();
        return;
    }

    if (!is_checkout()) return;
    ?>
    <script>
    jQuery(document.body).on('payment_method_selected', function() {
        jQuery('body').trigger('update_checkout');
    });
    </script>
    <?php
}
}
add_action('wp_footer', 'gastronom_render_checkout_payment_refresh_script');

// ============================================================
// 7. VARIABLE-WEIGHT PREORDER
//    Customer orders by pieces, final weight is confirmed later.
//    Cash register is updated only after manager confirms actual weight.
// ============================================================

if (!function_exists('gastronom_weight_preorder_enabled')) {
function gastronom_weight_preorder_enabled($product_id): bool {
    return get_post_meta((int) $product_id, '_gastronom_weight_preorder', true) === 'yes';
}
}

if (!function_exists('gastronom_current_lang')) {
function gastronom_current_lang(): string {
    if (function_exists('rslh_current_lang')) {
        return rslh_current_lang();
    }

    $lang = isset($_COOKIE['gastronom_lang']) ? sanitize_key(wp_unslash($_COOKIE['gastronom_lang'])) : 'sk';
    return $lang === 'ru' ? 'ru' : 'sk';
}
}

if (!function_exists('gastronom_order_lang')) {
function gastronom_order_lang($order = null): string {
    if (function_exists('rslh_order_lang')) {
        return rslh_order_lang($order);
    }

    if (is_numeric($order)) {
        $order = wc_get_order($order);
    }

    if ($order instanceof WC_Order) {
        $stored = sanitize_key((string) $order->get_meta('_gastronom_lang', true));
        if ($stored === 'ru' || $stored === 'sk') {
            return $stored;
        }
    }

    return gastronom_current_lang();
}
}

if (!function_exists('gastronom_tt')) {
function gastronom_tt($order, string $ru, string $sk): string {
    if (function_exists('rslh_tt')) {
        return rslh_tt($order, $ru, $sk);
    }

    return gastronom_order_lang($order) === 'ru' ? $ru : $sk;
}
}

if (!function_exists('gastronom_t')) {
function gastronom_t(string $ru, string $sk): string {
    if (function_exists('rslh_t')) {
        return rslh_t($ru, $sk);
    }

    return gastronom_current_lang() === 'ru' ? $ru : $sk;
}
}

if (!function_exists('gastronom_locale_for_order')) {
function gastronom_locale_for_order($order = null): string {
    if (function_exists('rslh_locale_for_order')) {
        return rslh_locale_for_order($order);
    }

    return gastronom_order_lang($order) === 'ru' ? 'ru_RU' : 'sk_SK';
}
}

if (!function_exists('gastronom_with_order_locale')) {
function gastronom_with_order_locale($order, callable $callback) {
    if (function_exists('rslh_with_order_locale')) {
        return rslh_with_order_locale($order, $callback);
    }

    $switched = false;
    if (function_exists('switch_to_locale')) {
        $switched = switch_to_locale(gastronom_locale_for_order($order));
    }

    try {
        return $callback();
    } finally {
        if ($switched && function_exists('restore_previous_locale')) {
            restore_previous_locale();
        }
    }
}
}

if (!function_exists('gastronom_split_bilingual_title')) {
function gastronom_split_bilingual_title(string $title): array {
    if (function_exists('rslh_split_bilingual_title')) {
        return rslh_split_bilingual_title($title);
    }

    foreach ([' / ', '/ ', ' /'] as $separator) {
        if (strpos($title, $separator) !== false) {
            $parts = explode($separator, $title, 2);
            $left = trim((string) ($parts[0] ?? ''));
            $right = trim((string) ($parts[1] ?? ''));
            if ($left !== '' && $right !== '') {
                $left_cyr = preg_match('/[А-Яа-яЁё]/u', $left) === 1;
                $right_cyr = preg_match('/[А-Яа-яЁё]/u', $right) === 1;
                if ($left_cyr !== $right_cyr) {
                    return [
                        'sk' => $left_cyr ? $right : $left,
                        'ru' => $left_cyr ? $left : $right,
                    ];
                }
                return ['sk' => $left, 'ru' => $right];
            }
        }
    }

    return ['sk' => trim($title), 'ru' => trim($title)];
}
}

if (!function_exists('gastronom_localize_title')) {
function gastronom_localize_title(string $title, string $lang): string {
    if (function_exists('rslh_localize_title')) {
        return rslh_localize_title($title, $lang);
    }

    $parts = gastronom_split_bilingual_title($title);
    return $parts[$lang === 'ru' ? 'ru' : 'sk'] ?? trim($title);
}
}

if (!function_exists('gastronom_localize_order_label')) {
function gastronom_localize_order_label(string $value, string $lang): string {
    if (function_exists('rslh_localize_order_label')) {
        return rslh_localize_order_label($value, $lang);
    }

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

    return gastronom_localize_title($value, $lang);
}
}

if (!function_exists('gastronom_weight_preorder_min_kg')) {
function gastronom_weight_preorder_min_kg($product_id): float {
    if (function_exists('rwp_min_kg')) {
        return rwp_min_kg($product_id);
    }

    return max(0.0, (float) get_post_meta((int) $product_id, '_gastronom_weight_preorder_min_kg', true));
}
}

if (!function_exists('gastronom_weight_preorder_max_kg')) {
function gastronom_weight_preorder_max_kg($product_id): float {
    if (function_exists('rwp_max_kg')) {
        return rwp_max_kg($product_id);
    }

    $max = max(0.0, (float) get_post_meta((int) $product_id, '_gastronom_weight_preorder_max_kg', true));
    $min = gastronom_weight_preorder_min_kg($product_id);
    return $max > 0 ? $max : $min;
}
}

if (!function_exists('gastronom_weight_preorder_avg_kg')) {
function gastronom_weight_preorder_avg_kg($product_id): float {
    if (function_exists('rwp_avg_kg')) {
        return rwp_avg_kg($product_id);
    }

    $min = gastronom_weight_preorder_min_kg($product_id);
    $max = gastronom_weight_preorder_max_kg($product_id);
    if ($min > 0 && $max > 0) {
        return ($min + $max) / 2;
    }
    return max($min, $max, 0.0);
}
}

if (!function_exists('gastronom_weight_preorder_price_per_kg')) {
function gastronom_weight_preorder_price_per_kg($product_id): float {
    if (function_exists('rwp_price_per_kg')) {
        return rwp_price_per_kg($product_id);
    }

    $product = wc_get_product($product_id);
    if (!$product) {
        return 0.0;
    }
    return (float) wc_get_price_to_display($product, ['qty' => 1]);
}
}

if (!function_exists('gastronom_weight_preorder_reserved_qty')) {
function gastronom_weight_preorder_reserved_qty($product_id, $exclude_order_id = 0): int {
    if (function_exists('rwp_reserved_qty')) {
        return rwp_reserved_qty($product_id, $exclude_order_id);
    }

    $product_id = (int) $product_id;
    $exclude_order_id = (int) $exclude_order_id;

    if ($product_id <= 0 || !function_exists('wc_get_orders')) {
        return 0;
    }

    $orders = wc_get_orders([
        'limit'  => -1,
        'status' => ['wc-await-weight', 'wc-pending', 'wc-on-hold'],
        'return' => 'objects',
    ]);

    $reserved = 0;
    foreach ($orders as $order) {
        if (!$order instanceof WC_Order) {
            continue;
        }
        if ($exclude_order_id > 0 && (int) $order->get_id() === $exclude_order_id) {
            continue;
        }

        foreach ($order->get_items('line_item') as $item) {
            $item_product_id = $item->get_product_id();
            if ((int) $item_product_id !== $product_id) {
                continue;
            }
            if ($item->get_meta('_gastronom_weight_confirmed', true) === 'yes') {
                continue;
            }
            $reserved += max(0, (int) $item->get_quantity());
        }
    }

    return $reserved;
}
}

if (!function_exists('gastronom_weight_preorder_piece_capacity')) {
function gastronom_weight_preorder_piece_capacity($product_id, ?float $raw_kg = null, $exclude_order_id = 0): int {
    if (function_exists('rwp_piece_capacity')) {
        return rwp_piece_capacity($product_id, $raw_kg, $exclude_order_id);
    }

    $product_id = (int) $product_id;
    if (!gastronom_weight_preorder_enabled($product_id)) {
        return 0;
    }

    $max_kg = gastronom_weight_preorder_max_kg($product_id);
    if ($max_kg <= 0) {
        return 0;
    }

    if ($raw_kg === null) {
        $raw_kg = (float) get_post_meta($product_id, '_gastronom_cash_stock_kg', true);
        if ($raw_kg <= 0) {
            $raw_kg = (float) get_post_meta($product_id, '_stock', true);
        }
    }

    $reserved_qty = gastronom_weight_preorder_reserved_qty($product_id, $exclude_order_id);
    $safe_kg = max(0.0, (float) $raw_kg - ($reserved_qty * $max_kg));

    return max(0, (int) floor(($safe_kg + 0.000001) / $max_kg));
}
}

if (!function_exists('gastronom_apply_preorder_piece_stock')) {
function gastronom_apply_preorder_piece_stock($product_id, float $raw_kg): void {
    if (function_exists('rwp_apply_piece_stock')) {
        rwp_apply_piece_stock($product_id, $raw_kg);
        return;
    }

    static $running = [];

    $product_id = (int) $product_id;
    if ($product_id <= 0 || !gastronom_weight_preorder_enabled($product_id)) {
        return;
    }

    if (!empty($running[$product_id])) {
        return;
    }

    $running[$product_id] = true;
    update_post_meta($product_id, '_gastronom_cash_stock_kg', $raw_kg);

    $pieces = gastronom_weight_preorder_piece_capacity($product_id, $raw_kg);
    update_post_meta($product_id, '_stock', $pieces);
    wc_update_product_stock_status($product_id, $pieces > 0 ? 'instock' : 'outofstock');
    update_post_meta($product_id, '_manage_stock', 'yes');
    update_post_meta($product_id, '_backorders', 'no');
    wc_delete_product_transients($product_id);

    unset($running[$product_id]);
}
}

if (!function_exists('gastronom_apply_dotypos_stock_to_wc_product')) {
function gastronom_apply_dotypos_stock_to_wc_product($product, float $raw_qty): bool {
    if (function_exists('rdsb_apply_dotypos_stock_to_preorder_product')) {
        return rdsb_apply_dotypos_stock_to_preorder_product($product, $raw_qty);
    }

    if (!$product instanceof WC_Product) {
        return false;
    }

    $product_id = (int) $product->get_id();
    if (!gastronom_weight_preorder_enabled($product_id)) {
        return false;
    }

    gastronom_apply_preorder_piece_stock($product_id, $raw_qty);
    return true;
}
}

if (!function_exists('gastronom_resolve_dotypos_order_sync_quantity')) {
function gastronom_resolve_dotypos_order_sync_quantity($order, $item, bool $restore = false) {
    if (function_exists('rdsb_resolve_order_sync_quantity')) {
        return rdsb_resolve_order_sync_quantity($order, $item, $restore);
    }

    if (!$item instanceof WC_Order_Item_Product) {
        return null;
    }

    $product = $item->get_product();
    if (!$product) {
        return null;
    }

    if (!gastronom_weight_preorder_enabled($product->get_id())) {
        return null;
    }

    // Variable-weight preorder items must not change Dotypos until the
    // manager confirms the actual weight in the order.
    return false;
}
}

if (!function_exists('gastronom_preorder_item_is_confirmed')) {
function gastronom_preorder_item_is_confirmed($item): bool {
    if (function_exists('rwp_item_is_confirmed')) {
        return rwp_item_is_confirmed($item);
    }

    if (!$item instanceof WC_Order_Item_Product) {
        return false;
    }

    if ($item->get_meta('_gastronom_weight_preorder', true) !== 'yes') {
        return false;
    }

    return $item->get_meta('_gastronom_weight_confirmed', true) === 'yes';
}
}

if (!function_exists('gastronom_normalize_preorder_order_state')) {
function gastronom_normalize_preorder_order_state($order): void {
    if (function_exists('rwp_normalize_order_state')) {
        rwp_normalize_order_state($order);
        return;
    }

    if (is_numeric($order)) {
        $order = wc_get_order($order);
    }
    if (!$order instanceof WC_Order) {
        return;
    }

    $needs_confirmation = false;
    $has_preorder = false;
    $changed = false;

    foreach ($order->get_items('line_item') as $item) {
        if ($item->get_meta('_gastronom_weight_preorder', true) !== 'yes') {
            continue;
        }

        $has_preorder = true;
        if (gastronom_preorder_item_is_confirmed($item)) {
            if ($item->get_meta('_gastronom_weight_confirmed', true) !== 'yes') {
                $item->update_meta_data('_gastronom_weight_confirmed', 'yes');
                $item->save();
                $changed = true;
            }
            continue;
        }

        $needs_confirmation = true;
    }

    if (!$has_preorder) {
        return;
    }

    $desired = $needs_confirmation ? 'yes' : 'no';
    if ($order->get_meta('_gastronom_requires_weight_confirmation', true) !== $desired) {
        $order->update_meta_data('_gastronom_requires_weight_confirmation', $desired);
        $changed = true;
    }

    if ($changed) {
        $order->save();
    }
}
}

if (!function_exists('gastronom_product_has_preorder_weight')) {
function gastronom_product_has_preorder_weight($product): bool {
    if (function_exists('rwp_product_has_preorder_weight')) {
        return rwp_product_has_preorder_weight($product);
    }

    if ($product instanceof WC_Product) {
        return gastronom_weight_preorder_enabled($product->get_id());
    }
    return false;
}
}

if (!function_exists('gastronom_order_has_preorder_weight')) {
function gastronom_order_has_preorder_weight($order): bool {
    if (function_exists('rwp_order_has_preorder_weight')) {
        return rwp_order_has_preorder_weight($order);
    }

    if (is_numeric($order)) {
        $order = wc_get_order($order);
    }

    if (!$order instanceof WC_Order) {
        return false;
    }

    foreach ($order->get_items('line_item') as $item) {
        if ($item->get_meta('_gastronom_weight_preorder', true) === 'yes') {
            return true;
        }
    }

    return false;
}
}

if (!function_exists('gastronom_can_reduce_order_stock')) {
function gastronom_can_reduce_order_stock($can_reduce, $order) {
    if (gastronom_order_has_preorder_weight($order)) {
        return false;
    }

    return $can_reduce;
}
}
add_filter('woocommerce_can_reduce_order_stock', 'gastronom_can_reduce_order_stock', 10, 2);

if (!function_exists('gastronom_can_restore_order_stock')) {
function gastronom_can_restore_order_stock($can_restore, $order) {
    if (gastronom_order_has_preorder_weight($order)) {
        return false;
    }

    return $can_restore;
}
}
add_filter('woocommerce_can_restore_order_stock', 'gastronom_can_restore_order_stock', 10, 2);

if (!function_exists('gastronom_render_product_preorder_fields')) {
function gastronom_render_product_preorder_fields(): void {
    if (function_exists('rpa_render_product_preorder_fields')) {
        rpa_render_product_preorder_fields();
        return;
    }

    echo '<div class="options_group">';
    woocommerce_wp_checkbox([
        'id'          => '_gastronom_weight_preorder',
        'label'       => 'Предзаказ по весу',
        'description' => 'Покупатель заказывает по штукам, точный вес и сумма подтверждаются позже.',
    ]);
    woocommerce_wp_text_input([
        'id'                => '_gastronom_weight_preorder_min_kg',
        'label'             => 'Вес от, кг',
        'type'              => 'number',
        'custom_attributes' => ['step' => '0.01', 'min' => '0'],
    ]);
    woocommerce_wp_text_input([
        'id'                => '_gastronom_weight_preorder_max_kg',
        'label'             => 'Вес до, кг',
        'type'              => 'number',
        'custom_attributes' => ['step' => '0.01', 'min' => '0'],
    ]);
    woocommerce_wp_textarea_input([
        'id'          => '_gastronom_weight_preorder_note',
        'label'       => 'Текст для покупателя',
        'description' => 'Необязательно. Если пусто, будет стандартное сообщение.',
    ]);
    echo '</div>';
}
}
add_action('woocommerce_product_options_general_product_data', 'gastronom_render_product_preorder_fields');

if (!function_exists('gastronom_save_product_preorder_fields')) {
function gastronom_save_product_preorder_fields($post_id): void {
    if (function_exists('rpa_save_product_preorder_fields')) {
        rpa_save_product_preorder_fields($post_id);
        return;
    }

    $enabled = isset($_POST['_gastronom_weight_preorder']) ? 'yes' : 'no';
    update_post_meta($post_id, '_gastronom_weight_preorder', $enabled);

    $min = isset($_POST['_gastronom_weight_preorder_min_kg']) ? (float) wc_clean(wp_unslash($_POST['_gastronom_weight_preorder_min_kg'])) : 0.0;
    $max = isset($_POST['_gastronom_weight_preorder_max_kg']) ? (float) wc_clean(wp_unslash($_POST['_gastronom_weight_preorder_max_kg'])) : 0.0;
    $note = isset($_POST['_gastronom_weight_preorder_note']) ? wp_kses_post(wp_unslash($_POST['_gastronom_weight_preorder_note'])) : '';

    update_post_meta($post_id, '_gastronom_weight_preorder_min_kg', $min);
    update_post_meta($post_id, '_gastronom_weight_preorder_max_kg', $max);
    update_post_meta($post_id, '_gastronom_weight_preorder_note', $note);

    if ($enabled === 'yes') {
        update_post_meta($post_id, '_gls_weighted', 'no');
        $raw = (float) get_post_meta($post_id, '_gastronom_cash_stock_kg', true);
        if ($raw <= 0) {
            $raw = (float) get_post_meta($post_id, '_stock', true);
        }
        gastronom_apply_preorder_piece_stock($post_id, $raw);
    }
}
}
add_action('woocommerce_process_product_meta', 'gastronom_save_product_preorder_fields');

if (!function_exists('gastronom_quantity_input_args')) {
function gastronom_quantity_input_args($args, $product) {
    if (function_exists('rpsf_quantity_input_args')) {
        return rpsf_quantity_input_args($args, $product);
    }

    if (!gastronom_product_has_preorder_weight($product)) {
        return $args;
    }

    $args['min_value'] = 1;
    $args['step'] = 1;
    $args['pattern'] = '[0-9]*';
    $args['inputmode'] = 'numeric';
    $args['input_value'] = max(1, (int) ($args['input_value'] ?: 1));
    $args['max_value'] = max(0, gastronom_weight_preorder_piece_capacity($product->get_id()));
    return $args;
}
}
add_filter('woocommerce_quantity_input_args', 'gastronom_quantity_input_args', 20, 2);

if (!function_exists('gastronom_add_to_cart_quantity')) {
function gastronom_add_to_cart_quantity($quantity, $product_id) {
    if (function_exists('rpsf_add_to_cart_quantity')) {
        return rpsf_add_to_cart_quantity($quantity, $product_id);
    }

    if (!gastronom_weight_preorder_enabled($product_id)) {
        return $quantity;
    }
    return max(1, (int) round((float) $quantity));
}
}
add_filter('woocommerce_add_to_cart_quantity', 'gastronom_add_to_cart_quantity', 20, 2);

if (!function_exists('gastronom_add_to_cart_validation')) {
function gastronom_add_to_cart_validation($passed, $product_id, $quantity) {
    if (function_exists('rpsf_add_to_cart_validation')) {
        return rpsf_add_to_cart_validation($passed, $product_id, $quantity);
    }

    if (!gastronom_weight_preorder_enabled($product_id)) {
        return $passed;
    }

    $qty = (int) round((float) $quantity);
    $max = gastronom_weight_preorder_piece_capacity($product_id);
    if ($qty <= 0) {
        wc_add_notice(gastronom_t('Для этого товара можно заказать только целое количество штук.', 'Tento tovar je možné objednať len po celých kusoch.'), 'error');
        return false;
    }
    if ($max > 0 && $qty > $max) {
        wc_add_notice(sprintf(gastronom_t('Для предварительного заказа доступно только %d шт.', 'Pre predobjednávku sú dostupné len %d ks.'), $max), 'error');
        return false;
    }
    return true;
}
}
add_filter('woocommerce_add_to_cart_validation', 'gastronom_add_to_cart_validation', 20, 3);

if (!function_exists('gastronom_before_calculate_totals')) {
function gastronom_before_calculate_totals($cart): void {
    if (function_exists('rpsf_before_calculate_totals')) {
        rpsf_before_calculate_totals($cart);
        return;
    }

    if (is_admin() && !defined('DOING_AJAX')) {
        return;
    }
    if (!$cart || is_null($cart)) {
        return;
    }

    foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
        if (empty($cart_item['data']) || empty($cart_item['product_id'])) {
            continue;
        }
        $product_id = (int) $cart_item['product_id'];
        if (!gastronom_weight_preorder_enabled($product_id)) {
            continue;
        }

        $avg = gastronom_weight_preorder_avg_kg($product_id);
        $price_per_kg = gastronom_weight_preorder_price_per_kg($product_id);
        if ($avg <= 0 || $price_per_kg <= 0) {
            continue;
        }

        $cart_item['data']->set_price($avg * $price_per_kg);
    }
}
}
add_action('woocommerce_before_calculate_totals', 'gastronom_before_calculate_totals', 20);

if (!function_exists('gastronom_get_item_data')) {
function gastronom_get_item_data($item_data, $cart_item) {
    if (function_exists('rpsf_get_item_data')) {
        return rpsf_get_item_data($item_data, $cart_item);
    }

    $product_id = !empty($cart_item['product_id']) ? (int) $cart_item['product_id'] : 0;
    if (!$product_id || !gastronom_weight_preorder_enabled($product_id)) {
        return $item_data;
    }

    $min = gastronom_weight_preorder_min_kg($product_id);
    $max = gastronom_weight_preorder_max_kg($product_id);
    $note = trim((string) get_post_meta($product_id, '_gastronom_weight_preorder_note', true));
    if ($note === '') {
        $note = gastronom_t(
            'Предварительный заказ. Точный вес и итоговая сумма будут уточнены после сборки заказа.',
            'Predobjednávka. Presná hmotnosť a konečná suma budú upresnené po príprave objednávky.'
        );
    }

    $item_data[] = [
        'name'  => gastronom_t('Формат продажи', 'Forma predaja'),
        'value' => gastronom_t('Поштучно, с уточнением фактического веса', 'Po kusoch, s neskorším upresnením skutočnej hmotnosti'),
    ];
    $item_data[] = [
        'name'  => gastronom_t('Примерный вес 1 шт.', 'Približná hmotnosť 1 ks'),
        'value' => wc_format_localized_decimal($min, 2) . '–' . wc_format_localized_decimal($max, 2) . ' кг',
    ];
    $item_data[] = [
        'name'  => gastronom_t('Важно', 'Dôležité'),
        'value' => $note,
    ];
    return $item_data;
}
}
add_filter('woocommerce_get_item_data', 'gastronom_get_item_data', 20, 2);

if (!function_exists('gastronom_render_single_product_note')) {
function gastronom_render_single_product_note(): void {
    if (function_exists('rpsf_render_single_product_note')) {
        rpsf_render_single_product_note();
        return;
    }

    global $product;
    if (!$product instanceof WC_Product || !gastronom_weight_preorder_enabled($product->get_id())) {
        return;
    }

    $min = gastronom_weight_preorder_min_kg($product->get_id());
    $max = gastronom_weight_preorder_max_kg($product->get_id());
    $note = trim((string) get_post_meta($product->get_id(), '_gastronom_weight_preorder_note', true));
    if ($note === '') {
        $note = gastronom_t(
            'Точный вес и итоговая сумма будут подтверждены после сборки заказа.',
            'Presná hmotnosť a konečná suma budú potvrdené po príprave objednávky.'
        );
    }

    echo '<div class="gastronom-preorder-note" style="margin:16px 0;padding:14px 16px;border:1px solid #d8c38a;border-left:4px solid #c7921b;border-radius:10px;background:#fff8e7;color:#5f4a12;">';
    echo '<strong style="display:block;margin-bottom:6px;">' . esc_html(gastronom_t('Предзаказ по весу', 'Predobjednávka podľa hmotnosti')) . '</strong>';
    echo '<div style="margin-bottom:6px;">' . esc_html(gastronom_t('Продаётся поштучно. Примерный вес одной штуки:', 'Predáva sa po kusoch. Približná hmotnosť jedného kusa:')) . ' <strong>' . esc_html(wc_format_localized_decimal($min, 2) . '–' . wc_format_localized_decimal($max, 2)) . ' кг</strong>.</div>';
    echo '<div>' . esc_html($note) . '</div>';
    echo '</div>';
}
}
add_action('woocommerce_single_product_summary', 'gastronom_render_single_product_note', 25);

if (!function_exists('gastronom_render_cart_notice')) {
function gastronom_render_cart_notice(): void {
    if (function_exists('rpsf_render_cart_notice')) {
        rpsf_render_cart_notice();
        return;
    }

    if (!WC()->cart) {
        return;
    }

    foreach (WC()->cart->get_cart() as $item) {
        if (!empty($item['product_id']) && gastronom_weight_preorder_enabled((int) $item['product_id'])) {
            wc_print_notice(gastronom_t('В корзине есть товары с предварительным расчётом по весу. Итоговая сумма будет уточнена после сборки заказа.', 'V košíku máte tovary s predbežným výpočtom podľa hmotnosti. Konečná suma bude upresnená po príprave objednávky.'), 'notice');
            break;
        }
    }
}
}
add_action('woocommerce_before_cart', 'gastronom_render_cart_notice', 6);

if (!function_exists('gastronom_render_checkout_payment_notice')) {
function gastronom_render_checkout_payment_notice(): void {
    if (function_exists('rpsf_render_checkout_payment_notice')) {
        rpsf_render_checkout_payment_notice();
        return;
    }

    if (!function_exists('is_checkout') || !is_checkout() || (function_exists('is_checkout_pay_page') && is_checkout_pay_page()) || !WC()->cart) {
        return;
    }

    foreach (WC()->cart->get_cart() as $item) {
        if (!empty($item['product_id']) && gastronom_weight_preorder_enabled((int) $item['product_id'])) {
            wc_print_notice(gastronom_t(
                'В корзине есть товары с уточнением веса. Итоговая сумма будет подтверждена после сборки заказа. Для банковского перевода мы пришлём ссылку на оплату после подтверждения веса.',
                'V košíku sú tovary s upresnením hmotnosti. Konečná suma bude potvrdená po príprave objednávky. Pri bankovom prevode vám po potvrdení hmotnosti pošleme odkaz na úhradu.'
            ), 'notice');
            break;
        }
    }
}
}
add_action('woocommerce_review_order_before_payment', 'gastronom_render_checkout_payment_notice', 6);

if (!function_exists('gastronom_available_payment_gateways')) {
function gastronom_available_payment_gateways($gateways) {
    if (function_exists('rpsf_available_payment_gateways')) {
        return rpsf_available_payment_gateways($gateways);
    }

    if (is_admin()) {
        return $gateways;
    }

    unset($gateways['bacs']);

    $has_preorder = false;
    if (is_checkout_pay_page()) {
        global $wp;
        $order_id = isset($wp->query_vars['order-pay']) ? (int) $wp->query_vars['order-pay'] : 0;
        $order = $order_id > 0 ? wc_get_order($order_id) : null;
        if ($order instanceof WC_Order) {
            $has_preorder = gastronom_order_has_preorder_weight($order);
        }
    } elseif (WC()->cart) {
        foreach (WC()->cart->get_cart() as $item) {
            if (!empty($item['product_id']) && gastronom_weight_preorder_enabled((int) $item['product_id'])) {
                $has_preorder = true;
                break;
            }
        }
    }

    if (!$has_preorder) {
        return $gateways;
    }

    if (isset($gateways['cod'])) {
        $gateways['cod']->title = gastronom_t(
            'Оплата при получении',
            'Platba pri doručení'
        );
    }

    return $gateways;
}
}
add_filter('woocommerce_available_payment_gateways', 'gastronom_available_payment_gateways', 20);

if (!function_exists('gastronom_price_html')) {
function gastronom_price_html($price, $product) {
    if (function_exists('rpsf_price_html')) {
        return rpsf_price_html($price, $product);
    }

    if (!gastronom_product_has_preorder_weight($product)) {
        return $price;
    }
    if (strpos($price, '/ kg') !== false) {
        return $price;
    }
    return $price . ' <span class="gls-price-unit">/ kg</span>';
}
}
add_filter('woocommerce_get_price_html', 'gastronom_price_html', 20, 2);

if (!function_exists('gastronom_checkout_create_order')) {
function gastronom_checkout_create_order($order): void {
    if (function_exists('rpsf_checkout_create_order')) {
        rpsf_checkout_create_order($order);
        return;
    }

    if (!$order instanceof WC_Order) {
        return;
    }

    $order->update_meta_data('_gastronom_lang', gastronom_current_lang());
}
}
add_action('woocommerce_checkout_create_order', 'gastronom_checkout_create_order', 20, 1);

if (!function_exists('gastronom_checkout_create_order_line_item')) {
function gastronom_checkout_create_order_line_item($item, $cart_item_key, $values, $order): void {
    if (function_exists('rpsf_checkout_create_order_line_item')) {
        rpsf_checkout_create_order_line_item($item, $cart_item_key, $values, $order);
        return;
    }

    $product_id = !empty($values['product_id']) ? (int) $values['product_id'] : 0;
    if (!$product_id || !gastronom_weight_preorder_enabled($product_id)) {
        return;
    }

    $min = gastronom_weight_preorder_min_kg($product_id);
    $max = gastronom_weight_preorder_max_kg($product_id);
    $avg = gastronom_weight_preorder_avg_kg($product_id);
    $price_per_kg = gastronom_weight_preorder_price_per_kg($product_id);

    $item->add_meta_data('_gastronom_weight_preorder', 'yes', true);
    $item->add_meta_data('_gastronom_weight_min_kg', $min, true);
    $item->add_meta_data('_gastronom_weight_max_kg', $max, true);
    $item->add_meta_data('_gastronom_estimated_weight_kg', $avg * (int) $item->get_quantity(), true);
    $item->add_meta_data('_gastronom_price_per_kg', $price_per_kg, true);
    $item->add_meta_data('_gastronom_weight_confirmed', 'no', true);
}
}
add_action('woocommerce_checkout_create_order_line_item', 'gastronom_checkout_create_order_line_item', 20, 4);

if (!function_exists('gastronom_order_requires_weight_confirmation')) {
function gastronom_order_requires_weight_confirmation($order): bool {
    if (function_exists('rwp_order_requires_confirmation')) {
        return rwp_order_requires_confirmation($order);
    }

    if (is_numeric($order)) {
        $order = wc_get_order($order);
    }
    if (!$order instanceof WC_Order) {
        return false;
    }

    foreach ($order->get_items('line_item') as $item) {
        if ($item->get_meta('_gastronom_weight_preorder', true) === 'yes' && !gastronom_preorder_item_is_confirmed($item)) {
            return true;
        }
    }
    return false;
}
}

if (!function_exists('gastronom_reserve_preorder_site_stock')) {
function gastronom_reserve_preorder_site_stock($order): void {
    if (is_numeric($order)) {
        $order = wc_get_order($order);
    }
    if (!$order instanceof WC_Order) {
        return;
    }
    if ($order->get_meta('_gastronom_preorder_site_reserved', true) === 'yes') {
        return;
    }

    foreach ($order->get_items('line_item') as $item) {
        if ($item->get_meta('_gastronom_weight_preorder', true) !== 'yes') {
            continue;
        }

        $product = $item->get_product();
        if (!$product) {
            continue;
        }

        $product_id = $product->get_id();
        $current = (int) get_post_meta($product_id, '_stock', true);
        $next = max(0, $current - (int) $item->get_quantity());
        update_post_meta($product_id, '_stock', $next);
        wc_update_product_stock_status($product_id, $next > 0 ? 'instock' : 'outofstock');
        wc_delete_product_transients($product_id);
    }

    $order->update_meta_data('_gastronom_preorder_site_reserved', 'yes');
    $order->save();
}
}

if (!function_exists('gastronom_restore_preorder_site_stock')) {
function gastronom_restore_preorder_site_stock($order): void {
    if (is_numeric($order)) {
        $order = wc_get_order($order);
    }
    if (!$order instanceof WC_Order) {
        return;
    }
    if ($order->get_meta('_gastronom_preorder_site_reserved', true) !== 'yes') {
        return;
    }
    if ($order->get_meta('_gastronom_preorder_site_restored', true) === 'yes') {
        return;
    }

    foreach ($order->get_items('line_item') as $item) {
        if ($item->get_meta('_gastronom_weight_preorder', true) !== 'yes') {
            continue;
        }

        $product = $item->get_product();
        if (!$product) {
            continue;
        }

        $product_id = $product->get_id();
        $current = (int) get_post_meta($product_id, '_stock', true);
        $next = max(0, $current + (int) $item->get_quantity());
        update_post_meta($product_id, '_stock', $next);
        wc_update_product_stock_status($product_id, $next > 0 ? 'instock' : 'outofstock');
        wc_delete_product_transients($product_id);
    }

    $order->update_meta_data('_gastronom_preorder_site_restored', 'yes');
    $order->save();
}
}

if (!function_exists('gastronom_send_preorder_created_emails')) {
function gastronom_send_preorder_created_emails($order): void {
    if (function_exists('rpn_send_preorder_created_emails')) {
        rpn_send_preorder_created_emails($order);
    }
}
}

if (!function_exists('gastronom_prepare_checkout_processed_preorder')) {
function gastronom_prepare_checkout_processed_preorder($order_id, $posted_data, $order): void {
    if (!$order instanceof WC_Order || !gastronom_order_requires_weight_confirmation($order)) {
        return;
    }

    foreach ($order->get_items('line_item') as $item) {
        if ($item->get_meta('_gastronom_weight_preorder', true) !== 'yes') {
            continue;
        }

        // A fresh preorder must never carry over confirmation/sync state.
        $item->delete_meta_data('_gastronom_actual_weight_kg');
        $item->update_meta_data('_gastronom_weight_confirmed', 'no');
        $item->update_meta_data('_gastronom_weight_cash_synced', 'no');
        $item->delete_meta_data('_gastronom_weight_cash_restored');
        $item->save();
    }

    // Site pieces are reserved manually for these orders, so Woo must not
    // carry any default stock-reduction flag into later status changes.
    $order->delete_meta_data('_order_stock_reduced');

    if ($order->get_meta('_gastronom_requires_weight_confirmation', true) === 'no') {
        return;
    }

    $order->update_meta_data('_gastronom_requires_weight_confirmation', 'yes');
    $order->save();

    if ($order->get_status() !== 'await-weight') {
        $order->update_status('await-weight', 'Awaiting actual weight confirmation.', true);
    }

    gastronom_reserve_preorder_site_stock($order);
    gastronom_send_preorder_created_emails($order);
}
}
add_action('woocommerce_checkout_order_processed', 'gastronom_prepare_checkout_processed_preorder', 20, 3);

if (!function_exists('gastronom_register_await_weight_status')) {
function gastronom_register_await_weight_status(): void {
    register_post_status('wc-await-weight', [
        'label'                     => 'На уточнении веса',
        'public'                    => true,
        'show_in_admin_status_list' => true,
        'show_in_admin_all_list'    => true,
        'label_count'               => _n_noop('На уточнении веса <span class="count">(%s)</span>', 'На уточнении веса <span class="count">(%s)</span>'),
    ]);
}
}
add_action('init', 'gastronom_register_await_weight_status');

if (!function_exists('gastronom_inject_await_weight_status')) {
function gastronom_inject_await_weight_status($statuses) {
    $new = [];
    foreach ($statuses as $key => $label) {
        $new[$key] = $label;
        if ($key === 'wc-pending') {
            $new['wc-await-weight'] = 'На уточнении веса';
        }
    }
    return $new;
}
}
add_filter('wc_order_statuses', 'gastronom_inject_await_weight_status');

if (!function_exists('gastronom_sync_confirmed_preorder_items_to_dotypos')) {
function gastronom_sync_confirmed_preorder_items_to_dotypos($order): void {
    if (function_exists('rdsb_sync_confirmed_preorder_items')) {
        rdsb_sync_confirmed_preorder_items($order);
        return;
    }

    if (is_numeric($order)) {
        $order = wc_get_order($order);
    }
    if (!$order instanceof WC_Order) {
        return;
    }

    $settings = get_option(Dotypos::$keys['settings']);
    if (empty($settings['product']['movement']['syncToDotypos'])) {
        return;
    }

    $dotypos = Dotypos::instance();
    if (!$dotypos || empty($dotypos->dotyposService)) {
        return;
    }

    foreach ($order->get_items('line_item') as $item_id => $item) {
        if ($item->get_meta('_gastronom_weight_preorder', true) !== 'yes') {
            continue;
        }
        if (!gastronom_preorder_item_is_confirmed($item)) {
            continue;
        }
        if ($item->get_meta('_gastronom_weight_cash_synced', true) === 'yes') {
            continue;
        }

        $product = $item->get_product();
        if (!$product) {
            continue;
        }

        $dotypos_id = $product->get_meta(Dotypos::$keys['product']['field-id']);
        $actual_weight = (float) $item->get_meta('_gastronom_actual_weight_kg', true);
        if (empty($dotypos_id) || $actual_weight <= 0) {
            continue;
        }

        $invoice_number = 'WC-PREORDER-' . $order->get_id() . '-CONFIRM';
        $dotypos->dotyposService->updateProductStock($settings['dotypos']['warehouseId'], $dotypos_id, -$actual_weight, $invoice_number);
        $latest_raw = (float) $dotypos->dotyposService->getProductOnWarehouse($settings['dotypos']['warehouseId'], $dotypos_id)['stockQuantityStatus'];
        gastronom_apply_preorder_piece_stock($product->get_id(), $latest_raw);
        $item->update_meta_data('_gastronom_weight_cash_synced', 'yes');
        $item->save();
    }
}
}

if (!function_exists('gastronom_restore_confirmed_preorder_items_to_dotypos')) {
function gastronom_restore_confirmed_preorder_items_to_dotypos($order): void {
    if (function_exists('rdsb_restore_confirmed_preorder_items')) {
        rdsb_restore_confirmed_preorder_items($order);
        return;
    }

    if (is_numeric($order)) {
        $order = wc_get_order($order);
    }
    if (!$order instanceof WC_Order) {
        return;
    }

    $settings = get_option(Dotypos::$keys['settings']);
    if (empty($settings['product']['movement']['syncToDotypos'])) {
        return;
    }

    $dotypos = Dotypos::instance();
    if (!$dotypos || empty($dotypos->dotyposService)) {
        return;
    }

    foreach ($order->get_items('line_item') as $item) {
        if ($item->get_meta('_gastronom_weight_preorder', true) !== 'yes') {
            continue;
        }
        if ($item->get_meta('_gastronom_weight_cash_synced', true) !== 'yes') {
            continue;
        }
        if ($item->get_meta('_gastronom_weight_cash_restored', true) === 'yes') {
            continue;
        }

        $product = $item->get_product();
        if (!$product) {
            continue;
        }

        $dotypos_id = $product->get_meta(Dotypos::$keys['product']['field-id']);
        $actual_weight = (float) $item->get_meta('_gastronom_actual_weight_kg', true);
        if (empty($dotypos_id) || $actual_weight <= 0) {
            continue;
        }

        $invoice_number = 'WC-PREORDER-' . $order->get_id() . '-RESTORE';
        $dotypos->dotyposService->updateProductStock($settings['dotypos']['warehouseId'], $dotypos_id, $actual_weight, $invoice_number);
        $latest_raw = (float) $dotypos->dotyposService->getProductOnWarehouse($settings['dotypos']['warehouseId'], $dotypos_id)['stockQuantityStatus'];
        gastronom_apply_preorder_piece_stock($product->get_id(), $latest_raw);
        $item->update_meta_data('_gastronom_weight_cash_restored', 'yes');
        $item->save();
    }
}
}

add_action('woocommerce_order_status_cancelled', 'gastronom_restore_confirmed_preorder_items_to_dotypos', 20);
add_action('woocommerce_order_status_refunded', 'gastronom_restore_confirmed_preorder_items_to_dotypos', 20);

if (!function_exists('gastronom_get_context_order_for_frontend_language')) {
function gastronom_get_context_order_for_frontend_language() {
    if (function_exists('ropl_context_order')) {
        return ropl_context_order();
    }
    return null;
}
}

if (!function_exists('gastronom_normalize_order_page_html')) {
function gastronom_normalize_order_page_html(string $html, string $lang): string {
    if (function_exists('ropl_normalize_order_page_html')) {
        return ropl_normalize_order_page_html($html, $lang);
    }
    return $html;
}
}

if (!function_exists('gastronom_localize_order_item_name')) {
function gastronom_localize_order_item_name($item_name, $item) {
    if (function_exists('ropl_localize_order_item_name')) {
        return ropl_localize_order_item_name($item_name, $item);
    }

    if (is_admin() || !($item instanceof WC_Order_Item_Product)) {
        return $item_name;
    }

    $order = gastronom_get_context_order_for_frontend_language();
    if (!$order instanceof WC_Order) {
        return $item_name;
    }

    $lang = gastronom_order_lang($order);
    if ($lang !== 'ru' && $lang !== 'sk') {
        return $item_name;
    }

    $original_name = (string) $item->get_name();
    $localized_name = gastronom_localize_title($original_name, $lang);

    if ($localized_name === $original_name || $localized_name === '') {
        return $item_name;
    }

    $item_name = str_replace($original_name, esc_html($localized_name), $item_name);

    if ($item_name === '') {
        return esc_html($localized_name);
    }

    return $item_name;
}
}
add_filter('woocommerce_order_item_name', 'gastronom_localize_order_item_name', 20, 2);

if (!function_exists('gastronom_render_forced_order_lang_marker')) {
function gastronom_render_forced_order_lang_marker(): void {
    if (function_exists('ropl_render_forced_order_lang_marker')) {
        ropl_render_forced_order_lang_marker();
    }
}
}
add_action('wp_head', 'gastronom_render_forced_order_lang_marker', 1);

if (!function_exists('gastronom_maybe_redirect_context_order_lang')) {
function gastronom_maybe_redirect_context_order_lang(): void {
    if (function_exists('ropl_maybe_redirect_context_order_lang')) {
        ropl_maybe_redirect_context_order_lang();
    }
}
}
add_action('template_redirect', 'gastronom_maybe_redirect_context_order_lang', 1);

if (!function_exists('gastronom_start_order_page_buffer')) {
function gastronom_start_order_page_buffer(): void {
    if (function_exists('ropl_start_order_page_buffer')) {
        ropl_start_order_page_buffer();
    }
}
}
add_action('template_redirect', 'gastronom_start_order_page_buffer', 20);

if (!function_exists('gastronom_render_order_item_actual_weight')) {
function gastronom_render_order_item_actual_weight($item_id, $item, $order, $plain_text): void {
    if ($plain_text) {
        return;
    }
    if (!$item instanceof WC_Order_Item_Product) {
        return;
    }
    if (!$order instanceof WC_Order) {
        return;
    }
    if ($item->get_meta('_gastronom_weight_preorder', true) !== 'yes') {
        return;
    }

    $actual_weight = (float) $item->get_meta('_gastronom_actual_weight_kg', true);
    if ($actual_weight <= 0) {
        return;
    }

    $lang = gastronom_current_lang();
    $label = $lang === 'ru' ? 'Фактический вес' : 'Skutočná hmotnosť';

    echo '<div class="gastronom-order-item-weight" style="margin-top:4px;color:#475467;font-size:13px;">'
        . esc_html($label . ': ' . wc_format_localized_decimal($actual_weight, 2) . ' kg')
        . '</div>';
}
}
add_action('woocommerce_order_item_meta_end', 'gastronom_render_order_item_actual_weight', 10, 4);

if (!function_exists('gastronom_send_weight_confirmation_email')) {
function gastronom_send_weight_confirmation_email($order): void {
    if (function_exists('rpn_send_weight_confirmation_email')) {
        rpn_send_weight_confirmation_email($order);
    }
}
}

if (!function_exists('gastronom_mark_weight_confirmed_order_ready')) {
function gastronom_mark_weight_confirmed_order_ready($order): void {
    if (function_exists('rpn_mark_weight_confirmed_order_ready')) {
        rpn_mark_weight_confirmed_order_ready($order);
    }
}
}

if (!function_exists('gastronom_render_weight_confirmation_box')) {
function gastronom_render_weight_confirmation_box($order_or_post): void {
    if (function_exists('rpa_render_weight_confirmation_box')) {
        rpa_render_weight_confirmation_box($order_or_post);
    }
}
}

if (!function_exists('gastronom_order_screen_ids')) {
function gastronom_order_screen_ids(): array {
    if (function_exists('rpa_order_screen_ids')) {
        return rpa_order_screen_ids();
    }
    return [];
}
}

if (!function_exists('gastronom_admin_footer_hook')) {
function gastronom_admin_footer_hook(): void {
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    $screen_id = $screen && isset($screen->id) ? (string) $screen->id : '';
    if (!in_array($screen_id, gastronom_order_screen_ids(), true)) {
        return;
    }
    if (function_exists('rpa_render_order_admin_footer')) {
        rpa_render_order_admin_footer();
    }
}
}
add_action('admin_footer', 'gastronom_admin_footer_hook');

if (!function_exists('gastronom_remove_hidden_meta_boxes')) {
function gastronom_remove_hidden_meta_boxes(): void {
    $hide_ids = function_exists('rpa_hidden_meta_box_ids')
        ? rpa_hidden_meta_box_ids()
        : [
            'tsseph_meta_box',
            'woocommerce-order-actions',
            'woocommerce-order-notes',
            'gls_shipping_info_meta_box',
            'gastronom-weight-confirmation',
        ];

    foreach (gastronom_order_screen_ids() as $screen_id) {
        foreach ($hide_ids as $box_id) {
            remove_meta_box($box_id, $screen_id, 'side');
        }
    }
}
}
add_action('add_meta_boxes', 'gastronom_remove_hidden_meta_boxes', 999);

if (!function_exists('gastronom_render_inline_weight_panel')) {
function gastronom_render_inline_weight_panel($order): void {
    if (!gastronom_order_requires_weight_confirmation($order)) {
        return;
    }
    echo '<div class="gastronom-inline-weight-box" style="margin:16px 0 8px;padding:16px 18px;border:1px solid #dcdcde;border-radius:8px;background:#fff;">';
    echo '<h3 style="margin:0 0 12px;font-size:15px;">Подтверждение фактического веса</h3>';
    gastronom_render_weight_confirmation_box($order);
    echo '</div>';
}
}
add_action('woocommerce_admin_order_data_after_order_details', 'gastronom_render_inline_weight_panel');

if (!function_exists('gastronom_handle_weight_confirmation_ajax')) {
function gastronom_handle_weight_confirmation_ajax(): void {
    if (function_exists('rpa_handle_weight_confirmation_ajax')) {
        rpa_handle_weight_confirmation_ajax();
    }
}
}
add_action('wp_ajax_gastronom_confirm_weight', 'gastronom_handle_weight_confirmation_ajax');
