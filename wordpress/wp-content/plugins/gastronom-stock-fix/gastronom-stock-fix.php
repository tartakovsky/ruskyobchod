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
add_action('plugins_loaded', function() {
    remove_filter('woocommerce_stock_amount', 'intval');
    add_filter('woocommerce_stock_amount', 'floatval');
}, 99);

// Too noisy for this store: do not email admins about low stock thresholds.
add_filter('woocommerce_email_enabled_low_stock', '__return_false');
add_filter('woocommerce_email_enabled_no_stock', '__return_false');
add_filter('woocommerce_email_enabled_backorder', '__return_false');

add_action('init', function() {
    // Keep Woo settings aligned with store policy: no stock alert emails.
    if (get_option('woocommerce_notify_low_stock', 'yes') !== 'no') {
        update_option('woocommerce_notify_low_stock', 'no');
    }
    if (get_option('woocommerce_notify_no_stock', 'yes') !== 'no') {
        update_option('woocommerce_notify_no_stock', 'no');
    }
}, 5);

add_action('woocommerce_email', function($mailer) {
    if (!$mailer || !is_object($mailer)) {
        return;
    }

    // Hard-disable stock alert emails even if Woo tries to register them.
    remove_action('woocommerce_low_stock_notification', [$mailer, 'low_stock']);
    remove_action('woocommerce_no_stock_notification', [$mailer, 'no_stock']);
    remove_action('woocommerce_product_on_backorder_notification', [$mailer, 'backorder']);
}, 1);

add_action('plugins_loaded', function() {
    global $dotypos;

    if ($dotypos && is_object($dotypos) && method_exists($dotypos, 'handle_product_updated')) {
        remove_action('woocommerce_update_product', [$dotypos, 'handle_product_updated'], 10);
    }
}, 100);

// ============================================================
// 2. REST API: change stock_quantity schema from integer to number
//    so API accepts decimal values like 0.475
// ============================================================
add_filter('rest_endpoints', function($endpoints) {
    // Change stock_quantity type from integer to number in all product endpoints
    foreach ($endpoints as $route => $handlers) {
        if (strpos($route, 'wc/v3/products') === false) continue;
        foreach ($handlers as $key => $handler) {
            if (!is_array($handler) || !isset($handler['args']['stock_quantity'])) continue;
            $endpoints[$route][$key]['args']['stock_quantity']['type'] = 'number';
        }
    }
    return $endpoints;
});

function gastronom_normalize_product_name($name) {
    $name = html_entity_decode(wp_strip_all_tags((string) $name), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    if (function_exists('mb_strtolower')) {
        $name = mb_strtolower($name, 'UTF-8');
    } else {
        $name = strtolower($name);
    }

    return trim(preg_replace('/\s+/', ' ', $name));
}

function gastronom_catalog_policy($name) {
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

function gastronom_reconcile_decimal_stock($product_id) {
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

function gastronom_apply_catalog_policy($product_id) {
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

function gastronom_enforce_product_rules($post_id, $post = null, $update = true) {
    if (wp_is_post_revision($post_id) || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)) {
        return;
    }

    gastronom_apply_catalog_policy($post_id);
    gastronom_reconcile_decimal_stock($post_id);
}

// ============================================================
// 3. AUTO OUTOFSTOCK: when stock changes, update status + visibility
//    Uses raw DB meta to avoid integer truncation
//    Uses wc_update_product_stock_status() to fix BOTH meta AND taxonomy
// ============================================================
add_action('woocommerce_product_set_stock', function($product) {
    $product_id = $product->get_id();
    gastronom_reconcile_decimal_stock($product_id);
}, 10, 1);

// ============================================================
// 4. REST API INSERT: same logic for products updated via REST API
// ============================================================
add_action('woocommerce_rest_insert_product_object', function($product) {
    if (!$product->managing_stock()) return;

    gastronom_reconcile_decimal_stock($product->get_id());
}, 99, 1);

add_action('updated_post_meta', function($meta_id, $object_id, $meta_key) {
    if ($meta_key === '_stock') {
        gastronom_reconcile_decimal_stock($object_id);
    }
}, 20, 3);

add_action('added_post_meta', function($meta_id, $object_id, $meta_key) {
    if ($meta_key === '_stock') {
        gastronom_reconcile_decimal_stock($object_id);
    }
}, 20, 3);

add_action('save_post_product', 'gastronom_enforce_product_rules', 20, 3);

// ============================================================
// 5. ONE-TIME FIX: on first load after update, fix all products
//    that have stock > 0 but are marked outofstock
//    Runs once, then sets a flag so it doesn't repeat
// ============================================================
add_action('admin_init', function() {
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
});

// ============================================================
// 6. COD FEE: +2 EUR at checkout when "Platba pri doruceni" selected
// ============================================================
add_action('woocommerce_cart_calculate_fees', function() {
    if (is_admin() && !defined('DOING_AJAX')) return;
    if (!is_checkout()) return;
    $chosen_payment = WC()->session->get('chosen_payment_method');
    if ($chosen_payment === 'cod') {
        WC()->cart->add_fee('Poplatok za dobierku', 2.00, false);
    }
});

add_filter('woocommerce_gateway_description', function($description, $id) {
    if ($id === 'cod') {
        $description = gastronom_t(
            'К заказу будет добавлена доплата за наложенный платёж 2,00 €.',
            'K objednávke bude pripočítaný poplatok za dobierku vo výške 2,00 €.'
        );
    }
    return $description;
}, 10, 2);

add_action('wp_footer', function() {
    if (!is_checkout()) return;
    ?>
    <script>
    jQuery(document.body).on('payment_method_selected', function() {
        jQuery('body').trigger('update_checkout');
    });
    </script>
    <?php
});

// ============================================================
// 7. VARIABLE-WEIGHT PREORDER
//    Customer orders by pieces, final weight is confirmed later.
//    Cash register is updated only after manager confirms actual weight.
// ============================================================

function gastronom_weight_preorder_enabled($product_id): bool {
    return get_post_meta((int) $product_id, '_gastronom_weight_preorder', true) === 'yes';
}

function gastronom_current_lang(): string {
    $lang = isset($_COOKIE['gastronom_lang']) ? sanitize_key(wp_unslash($_COOKIE['gastronom_lang'])) : 'sk';
    return $lang === 'ru' ? 'ru' : 'sk';
}

function gastronom_order_lang($order = null): string {
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

function gastronom_tt($order, string $ru, string $sk): string {
    return gastronom_order_lang($order) === 'ru' ? $ru : $sk;
}

function gastronom_t(string $ru, string $sk): string {
    return gastronom_current_lang() === 'ru' ? $ru : $sk;
}

function gastronom_split_bilingual_title(string $title): array {
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

function gastronom_localize_title(string $title, string $lang): string {
    $parts = gastronom_split_bilingual_title($title);
    return $parts[$lang === 'ru' ? 'ru' : 'sk'] ?? trim($title);
}

function gastronom_localize_order_label(string $value, string $lang): string {
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

function gastronom_weight_preorder_min_kg($product_id): float {
    return max(0.0, (float) get_post_meta((int) $product_id, '_gastronom_weight_preorder_min_kg', true));
}

function gastronom_weight_preorder_max_kg($product_id): float {
    $max = max(0.0, (float) get_post_meta((int) $product_id, '_gastronom_weight_preorder_max_kg', true));
    $min = gastronom_weight_preorder_min_kg($product_id);
    return $max > 0 ? $max : $min;
}

function gastronom_weight_preorder_avg_kg($product_id): float {
    $min = gastronom_weight_preorder_min_kg($product_id);
    $max = gastronom_weight_preorder_max_kg($product_id);
    if ($min > 0 && $max > 0) {
        return ($min + $max) / 2;
    }
    return max($min, $max, 0.0);
}

function gastronom_weight_preorder_price_per_kg($product_id): float {
    $product = wc_get_product($product_id);
    if (!$product) {
        return 0.0;
    }
    return (float) wc_get_price_to_display($product, ['qty' => 1]);
}

function gastronom_weight_preorder_reserved_qty($product_id, $exclude_order_id = 0): int {
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

function gastronom_weight_preorder_piece_capacity($product_id, ?float $raw_kg = null, $exclude_order_id = 0): int {
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

function gastronom_apply_preorder_piece_stock($product_id, float $raw_kg): void {
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

function gastronom_apply_dotypos_stock_to_wc_product($product, float $raw_qty): bool {
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

function gastronom_resolve_dotypos_order_sync_quantity($order, $item, bool $restore = false) {
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

function gastronom_preorder_item_is_confirmed($item): bool {
    if (!$item instanceof WC_Order_Item_Product) {
        return false;
    }

    if ($item->get_meta('_gastronom_weight_preorder', true) !== 'yes') {
        return false;
    }

    return $item->get_meta('_gastronom_weight_confirmed', true) === 'yes';
}

function gastronom_normalize_preorder_order_state($order): void {
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

function gastronom_product_has_preorder_weight($product): bool {
    if ($product instanceof WC_Product) {
        return gastronom_weight_preorder_enabled($product->get_id());
    }
    return false;
}

function gastronom_order_has_preorder_weight($order): bool {
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

add_filter('woocommerce_can_reduce_order_stock', function($can_reduce, $order) {
    if (gastronom_order_has_preorder_weight($order)) {
        return false;
    }

    return $can_reduce;
}, 10, 2);

add_filter('woocommerce_can_restore_order_stock', function($can_restore, $order) {
    if (gastronom_order_has_preorder_weight($order)) {
        return false;
    }

    return $can_restore;
}, 10, 2);

add_action('woocommerce_product_options_general_product_data', function() {
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
});

add_action('woocommerce_process_product_meta', function($post_id) {
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
});

add_filter('woocommerce_quantity_input_args', function($args, $product) {
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
}, 20, 2);

add_filter('woocommerce_add_to_cart_quantity', function($quantity, $product_id) {
    if (!gastronom_weight_preorder_enabled($product_id)) {
        return $quantity;
    }
    return max(1, (int) round((float) $quantity));
}, 20, 2);

add_filter('woocommerce_add_to_cart_validation', function($passed, $product_id, $quantity) {
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
}, 20, 3);

add_action('woocommerce_before_calculate_totals', function($cart) {
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
}, 20);

add_filter('woocommerce_get_item_data', function($item_data, $cart_item) {
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
}, 20, 2);

add_action('woocommerce_single_product_summary', function() {
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
}, 25);

add_action('woocommerce_before_cart', function() {
    if (!WC()->cart) {
        return;
    }

    foreach (WC()->cart->get_cart() as $item) {
        if (!empty($item['product_id']) && gastronom_weight_preorder_enabled((int) $item['product_id'])) {
            wc_print_notice(gastronom_t('В корзине есть товары с предварительным расчётом по весу. Итоговая сумма будет уточнена после сборки заказа.', 'V košíku máte tovary s predbežným výpočtom podľa hmotnosti. Konečná suma bude upresnená po príprave objednávky.'), 'notice');
            break;
        }
    }
}, 6);

add_filter('woocommerce_available_payment_gateways', function($gateways) {
    if (is_admin()) {
        return $gateways;
    }

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

    if (is_checkout_pay_page()) {
        unset($gateways['cod']);

        if (isset($gateways['bacs'])) {
            $gateways['bacs']->title = gastronom_t(
                'Банковский перевод',
                'Bankový prevod'
            );
        }

        return $gateways;
    }

    foreach ($gateways as $gateway_id => $gateway) {
        if (!in_array($gateway_id, ['bacs', 'cod'], true)) {
            unset($gateways[$gateway_id]);
        }
    }

    if (isset($gateways['bacs'])) {
        $gateways['bacs']->title = gastronom_t(
            'Оплата после подтверждения веса',
            'Platba po potvrdení hmotnosti'
        );
        $gateways['bacs']->description = gastronom_t(
            'Сумма предварительная. После подтверждения веса мы пришлём письмо со ссылкой на оплату.',
            'Suma je predbežná. Po potvrdení hmotnosti vám pošleme e-mail s odkazom na platbu.'
        );
    }

    if (isset($gateways['cod'])) {
        $gateways['cod']->title = gastronom_t(
            'Оплата при получении',
            'Platba pri doručení'
        );
    }

    return $gateways;
}, 20);

add_filter('woocommerce_get_price_html', function($price, $product) {
    if (!gastronom_product_has_preorder_weight($product)) {
        return $price;
    }
    if (strpos($price, '/ kg') !== false) {
        return $price;
    }
    return $price . ' <span class="gls-price-unit">/ kg</span>';
}, 20, 2);

add_action('woocommerce_checkout_create_order', function($order) {
    if (!$order instanceof WC_Order) {
        return;
    }

    $order->update_meta_data('_gastronom_lang', gastronom_current_lang());
}, 20, 1);

add_action('woocommerce_checkout_create_order_line_item', function($item, $cart_item_key, $values, $order) {
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
}, 20, 4);

function gastronom_order_requires_weight_confirmation($order): bool {
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

function gastronom_send_preorder_created_emails($order): void {
    if (is_numeric($order)) {
        $order = wc_get_order($order);
    }
    if (!$order instanceof WC_Order) {
        return;
    }
    if (!gastronom_order_requires_weight_confirmation($order)) {
        return;
    }
    if ($order->get_meta('_gastronom_preorder_created_emails_sent', true) === 'yes') {
        return;
    }

    $mailer = WC()->mailer();
    $emails = $mailer ? $mailer->get_emails() : [];

    foreach ($emails as $email) {
        if ($email instanceof WC_Email_New_Order) {
            $email->trigger($order->get_id(), $order);
        }
        if ($email instanceof WC_Email_Customer_On_Hold_Order) {
            $email->trigger($order->get_id(), $order);
        }
    }

    $order->update_meta_data('_gastronom_preorder_created_emails_sent', 'yes');
    $order->update_meta_data('_gastronom_preorder_created_emails_sent_at', current_time('mysql'));
    $order->save();
}

add_action('woocommerce_checkout_order_processed', function($order_id, $posted_data, $order) {
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
}, 20, 3);

add_action('init', function() {
    register_post_status('wc-await-weight', [
        'label'                     => 'На уточнении веса',
        'public'                    => true,
        'show_in_admin_status_list' => true,
        'show_in_admin_all_list'    => true,
        'label_count'               => _n_noop('На уточнении веса <span class="count">(%s)</span>', 'На уточнении веса <span class="count">(%s)</span>'),
    ]);
});

add_filter('wc_order_statuses', function($statuses) {
    $new = [];
    foreach ($statuses as $key => $label) {
        $new[$key] = $label;
        if ($key === 'wc-pending') {
            $new['wc-await-weight'] = 'На уточнении веса';
        }
    }
    return $new;
});

function gastronom_sync_confirmed_preorder_items_to_dotypos($order): void {
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

function gastronom_restore_confirmed_preorder_items_to_dotypos($order): void {
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

add_action('woocommerce_order_status_cancelled', 'gastronom_restore_confirmed_preorder_items_to_dotypos', 20);
add_action('woocommerce_order_status_refunded', 'gastronom_restore_confirmed_preorder_items_to_dotypos', 20);

function gastronom_get_context_order_for_frontend_language() {
    if (is_admin() || !function_exists('wc_get_order')) {
        return null;
    }

    $order_id = 0;

    if (isset($_GET['order-pay'])) {
        $order_id = (int) wp_unslash($_GET['order-pay']);
    } elseif (isset($_GET['order-received'])) {
        $order_id = (int) wp_unslash($_GET['order-received']);
    } elseif (function_exists('is_wc_endpoint_url') && is_wc_endpoint_url('view-order')) {
        global $wp;
        $order_id = isset($wp->query_vars['view-order']) ? (int) $wp->query_vars['view-order'] : 0;
    }

    if ($order_id <= 0) {
        return null;
    }

    $order = wc_get_order($order_id);
    return $order instanceof WC_Order ? $order : null;
}

function gastronom_normalize_order_page_html(string $html, string $lang): string {
    $is_pay = function_exists('is_checkout_pay_page') && is_checkout_pay_page();
    $is_view_order = function_exists('is_wc_endpoint_url') && is_wc_endpoint_url('view-order');
    $is_order_received = function_exists('is_wc_endpoint_url') && is_wc_endpoint_url('order-received');

    if ($lang === 'ru') {
        $replace = [
            'Môj Účet' => 'Мой аккаунт',
            'Môj účet' => 'Мой аккаунт',
            'Doprava' => 'Доставка',
            'Kontakt' => 'Контакты',
            'Kontakty' => 'Контакты',
            'Prihláste sa do svojho účtu, aby ste mohli pokračovať na platbu.' => 'Пожалуйста, войдите в свою учётную запись, чтобы перейти к оплате.',
            'Používateľské meno alebo e-mail' => 'Имя пользователя или Email',
            'Heslo' => 'Пароль',
            'Zapamätať si ma' => 'Запомнить меня',
            'Prihlásiť sa' => 'Войти',
            'Zabudli ste heslo?' => 'Забыли пароль?',
            'Fakturačná adresa' => 'Платёжный адрес',
            'Informácie o objednávke' => 'Информация о заказе',
            'Skutočná hmotnosť' => 'Фактический вес',
            'Osobné vyzdvihnutie' => 'Самовывоз',
            'Bankový prevod' => 'Банковский перевод',
            'Platba po potvrdení hmotnosti' => 'Оплата после подтверждения веса',
            'Platba kartou' => 'Оплата картой',
            'Medzisúčet' => 'Подытог',
            'Doprava:' => 'Доставка:',
            'Spôsob platby:' => 'Способ оплаты:',
            'Celkom:' => 'Итого:',
            'Zaplatiť' => 'Оплатить',
            'Zrušiť' => 'Отмена',
            '— ALEBO —' => '— ИЛИ —',
            'ALEBO' => 'ИЛИ',
            'Prečítal(a) som si a súhlasím s' => 'Я прочитал(а) и принимаю',
            'obchodné podmienky' => 'правила и условия',
            'Objednávka #' => 'Заказ №',
        ];

        if ($is_pay) {
            $replace['Objednávka'] = 'Оплатить заказ';
        }

        if ($is_view_order || $is_order_received) {
            $replace['Objednávka'] = 'Заказ';
        }

        return strtr($html, $replace);
    }

    $replace = [
        'Мой Аккаунт' => 'Môj účet',
        'Мой аккаунт' => 'Môj účet',
        'Моя учётная запись' => 'Môj účet',
        'Главная' => 'Domov',
        'Доставка' => 'Doprava',
        'Контакты' => 'Kontakt',
        'Пожалуйста, войдите в свою учётную запись, чтобы перейти к оплате.' => 'Prihláste sa do svojho účtu, aby ste mohli pokračovať na platbu.',
        'Пожалуйста войдите в вашу учетную запись ниже чтобы продожить к платежной форме.' => 'Prihláste sa do svojho účtu, aby ste mohli pokračovať na platbu.',
        'Имя пользователя или Email' => 'Používateľské meno alebo e-mail',
        'Пароль' => 'Heslo',
        'Запомнить меня' => 'Zapamätať si ma',
        'Войти' => 'Prihlásiť sa',
        'Забыли пароль?' => 'Zabudli ste heslo?',
        'Платёжный адрес' => 'Fakturačná adresa',
        'Информация о заказе' => 'Informácie o objednávke',
        'Фактический вес' => 'Skutočná hmotnosť',
        'Самовывоз' => 'Osobné vyzdvihnutie',
        'Банковский перевод' => 'Bankový prevod',
        'Оплата после подтверждения веса' => 'Platba po potvrdení hmotnosti',
        'Оплата картой' => 'Platba kartou',
        'Подытог' => 'Medzisúčet',
        'Доставка:' => 'Doprava:',
        'Способ оплаты:' => 'Spôsob platby:',
        'Итого:' => 'Celkom:',
        'Оплатить' => 'Zaplatiť',
        'Отмена' => 'Zrušiť',
        '— ИЛИ —' => '— ALEBO —',
        'ИЛИ' => 'ALEBO',
        'Я прочитал(а) и принимаю' => 'Prečítal(a) som si a súhlasím s',
        'правила и условия' => 'obchodné podmienky',
        'Заказ №' => 'Objednávka č.',
    ];

    if ($is_pay) {
        $replace['Оплатить заказ'] = 'Zaplatiť objednávku';
        $replace['Заказ'] = 'Objednávka';
    }

    if ($is_view_order || $is_order_received) {
        $replace['Заказ'] = 'Objednávka';
    }

    return strtr($html, $replace);
}

add_action('wp_head', function() {
    $order = gastronom_get_context_order_for_frontend_language();
    if (!$order instanceof WC_Order) {
        return;
    }

    $lang = gastronom_order_lang($order);
    if ($lang !== 'ru' && $lang !== 'sk') {
        return;
    }

    echo "<script>window.gastronomForcedOrderLang=" . wp_json_encode($lang) . ";</script>\n";
}, 1);

add_action('template_redirect', function() {
    if (is_admin()) {
        return;
    }

    $order = gastronom_get_context_order_for_frontend_language();
    if (!$order instanceof WC_Order) {
        return;
    }

    $lang = gastronom_order_lang($order);
    if ($lang !== 'ru' && $lang !== 'sk') {
        return;
    }

    $current_lang = isset($_GET['lang']) ? sanitize_key(wp_unslash($_GET['lang'])) : '';
    if ($current_lang === $lang) {
        return;
    }

    $scheme = is_ssl() ? 'https://' : 'http://';
    $host = isset($_SERVER['HTTP_HOST']) ? wp_unslash($_SERVER['HTTP_HOST']) : '';
    $uri = isset($_SERVER['REQUEST_URI']) ? wp_unslash($_SERVER['REQUEST_URI']) : '';
    if ($host === '' || $uri === '') {
        return;
    }

    $target = add_query_arg('lang', $lang, $scheme . $host . $uri);
    wp_safe_redirect($target, 302);
    exit;
}, 1);

add_action('template_redirect', function() {
    if (is_admin()) {
        return;
    }

    $order = gastronom_get_context_order_for_frontend_language();
    if (!$order instanceof WC_Order) {
        return;
    }

    $lang = gastronom_order_lang($order);
    if ($lang !== 'ru' && $lang !== 'sk') {
        return;
    }

    ob_start(static function($html) use ($lang) {
        if (!is_string($html) || $html === '') {
            return $html;
        }

        return gastronom_normalize_order_page_html($html, $lang);
    });
}, 20);

add_action('woocommerce_order_item_meta_end', function($item_id, $item, $order, $plain_text) {
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
}, 10, 4);

function gastronom_send_weight_confirmation_email($order): void {
    if (is_numeric($order)) {
        $order = wc_get_order($order);
    }
    if (!$order instanceof WC_Order) {
        return;
    }

    $to = sanitize_email((string) $order->get_billing_email());
    if ($to === '') {
        return;
    }

    $lang = gastronom_order_lang($order);
    $subject = $lang === 'ru'
        ? sprintf('Уточнён вес заказа №%s', $order->get_order_number())
        : sprintf('Hmotnosť objednávky č. %s bola upresnená', $order->get_order_number());

    $intro = $lang === 'ru'
        ? 'Мы уточнили фактический вес товаров в вашем заказе. Итоговая сумма заказа обновлена.'
        : 'Upresnili sme skutočnú hmotnosť tovarov vo vašej objednávke. Konečná suma objednávky bola aktualizovaná.';

    $payment_method = (string) $order->get_payment_method();
    $cta = $lang === 'ru'
        ? 'Итоговая сумма обновлена. Ниже указан следующий шаг оплаты по выбранному способу.'
        : 'Konečná suma bola aktualizovaná. Nižšie nájdete ďalší krok platby podľa zvoleného spôsobu.';

    $details_label = $lang === 'ru' ? 'Информация о заказе' : 'Informácie o objednávke';
    $shipping_title = gastronom_localize_order_label((string) $order->get_shipping_method(), $lang);
    $payment_title = gastronom_localize_order_label((string) $order->get_payment_method_title(), $lang);
    $details_rows = [
        [$lang === 'ru' ? 'Номер заказа' : 'Číslo objednávky', '#' . $order->get_order_number()],
    ];
    if ($shipping_title !== '') {
        $details_rows[] = [$lang === 'ru' ? 'Доставка' : 'Doprava', $shipping_title];
    }
    if ($payment_title !== '') {
        $details_rows[] = [$lang === 'ru' ? 'Способ оплаты' : 'Spôsob platby', $payment_title];
    }
    $details_rows[] = [$lang === 'ru' ? 'Итоговая сумма' : 'Konečná suma', wp_strip_all_tags($order->get_formatted_order_total())];

    $details_html = '<p><strong>' . esc_html($details_label) . '</strong></p>';
    $details_html .= '<table style="width:100%;border-collapse:collapse;margin:8px 0 16px;">';
    foreach ($details_rows as $row) {
        $details_html .= '<tr>';
        $details_html .= '<td style="padding:8px 8px 8px 0;border-bottom:1px solid #f1f5f9;color:#344054;width:38%;"><strong>' . esc_html($row[0]) . '</strong></td>';
        $details_html .= '<td style="padding:8px 0;border-bottom:1px solid #f1f5f9;color:#101828;">' . esc_html($row[1]) . '</td>';
        $details_html .= '</tr>';
    }
    $details_html .= '</table>';

    $items_label = $lang === 'ru' ? 'Состав заказа' : 'Obsah objednávky';
    $items_html = '<p><strong>' . esc_html($items_label) . '</strong></p>';
    $items_html .= '<table style="width:100%;border-collapse:collapse;margin:8px 0 16px;">';
    $items_html .= '<thead><tr>';
    $items_html .= '<th style="text-align:left;padding:8px;border-bottom:1px solid #e5e7eb;">' . esc_html($lang === 'ru' ? 'Товар' : 'Tovar') . '</th>';
    $items_html .= '<th style="text-align:left;padding:8px;border-bottom:1px solid #e5e7eb;">' . esc_html($lang === 'ru' ? 'Кол-во' : 'Množstvo') . '</th>';
    $items_html .= '<th style="text-align:left;padding:8px;border-bottom:1px solid #e5e7eb;">' . esc_html($lang === 'ru' ? 'Фактический вес' : 'Skutočná hmotnosť') . '</th>';
    $items_html .= '<th style="text-align:left;padding:8px;border-bottom:1px solid #e5e7eb;">' . esc_html($lang === 'ru' ? 'Сумма' : 'Suma') . '</th>';
    $items_html .= '</tr></thead><tbody>';
    foreach ($order->get_items('line_item') as $item) {
        $name = gastronom_localize_title(wp_strip_all_tags($item->get_name()), $lang);
        $qty = (int) $item->get_quantity();
        $line_total = wp_strip_all_tags(wc_price((float) $item->get_total(), ['currency' => $order->get_currency()]));
        $weight_cell = '—';
        if ($item->get_meta('_gastronom_weight_preorder', true) === 'yes') {
            $actual_weight = (float) $item->get_meta('_gastronom_actual_weight_kg', true);
            if ($actual_weight > 0) {
                $weight_cell = wc_format_localized_decimal($actual_weight, 2) . ' kg';
            }
        }
        $items_html .= '<tr>';
        $items_html .= '<td style="padding:8px;border-bottom:1px solid #f1f5f9;">' . esc_html($name) . '</td>';
        $items_html .= '<td style="padding:8px;border-bottom:1px solid #f1f5f9;">' . esc_html((string) $qty) . '</td>';
        $items_html .= '<td style="padding:8px;border-bottom:1px solid #f1f5f9;">' . esc_html($weight_cell) . '</td>';
        $items_html .= '<td style="padding:8px;border-bottom:1px solid #f1f5f9;">' . esc_html($line_total) . '</td>';
        $items_html .= '</tr>';
    }
    $items_html .= '</tbody></table>';

    $total_label = $lang === 'ru' ? 'Новая сумма заказа' : 'Nová suma objednávky';
    $view_label = $lang === 'ru' ? 'Посмотреть заказ' : 'Zobraziť objednávku';
    $view_url = $order->get_view_order_url();
    $pay_label = $lang === 'ru' ? 'Оплатить заказ' : 'Zaplatiť objednávku';
    $payment_html = '';

    if ($payment_method === 'cod') {
        $payment_html = '<p>' . esc_html($lang === 'ru'
            ? 'Вы выбрали оплату при получении. Дополнительно оплачивать заказ сейчас не нужно.'
            : 'Zvolili ste platbu pri prevzatí. Objednávku teraz nemusíte dodatočne platiť.') . '</p>';
    } else {
        $pay_url = $order->get_checkout_payment_url();
        if (!empty($pay_url)) {
            $payment_html = '<p>' . esc_html($lang === 'ru'
                ? 'Перейдите по ссылке ниже, чтобы оплатить заказ по обновлённой сумме.'
                : 'Prejdite na odkaz nižšie a uhraďte objednávku podľa aktualizovanej sumy.') . '</p>'
                . '<p><a href="' . esc_url($pay_url) . '" style="display:inline-block;padding:10px 16px;background:#067c36;color:#fff;text-decoration:none;border-radius:6px;">' . esc_html($pay_label) . '</a></p>';
        }
    }

    $message = '<p>' . esc_html($intro) . '</p>'
        . $details_html
        . $items_html
        . '<p><strong>' . esc_html($total_label) . ':</strong> ' . wp_kses_post($order->get_formatted_order_total()) . '</p>'
        . '<p>' . esc_html($cta) . '</p>'
        . $payment_html
        . '<p><a href="' . esc_url($view_url) . '" style="display:inline-block;padding:10px 16px;background:#294c7a;color:#fff;text-decoration:none;border-radius:6px;">' . esc_html($view_label) . '</a></p>';

    $sent = wc_mail($to, $subject, $message);
    if ($sent) {
        $order->update_meta_data('_gastronom_weight_confirmation_email_sent_at', current_time('mysql'));
        $order->update_meta_data('_gastronom_weight_confirmation_email_to', $to);
        $order->save();
    }
}

function gastronom_mark_weight_confirmed_order_ready($order): void {
    if (is_numeric($order)) {
        $order = wc_get_order($order);
    }
    if (!$order instanceof WC_Order) {
        return;
    }

    gastronom_normalize_preorder_order_state($order);

    if (gastronom_order_requires_weight_confirmation($order)) {
        return;
    }

    $order->update_meta_data('_gastronom_requires_weight_confirmation', 'no');
    $order->save();

    if ($order->get_status() === 'await-weight') {
        $next_status = $order->get_payment_method() === 'cod' ? 'on-hold' : 'pending';
        $note = $order->get_payment_method() === 'cod'
            ? gastronom_tt($order, 'Фактический вес подтверждён. Ожидание оплаты при получении.', 'Skutočná hmotnosť bola potvrdená. Čaká sa na platbu pri doručení.')
            : gastronom_tt($order, 'Фактический вес подтверждён. Ожидание онлайн-оплаты.', 'Skutočná hmotnosť bola potvrdená. Čaká sa na online platbu.');
        $order->update_status($next_status, $note, true);
    }

    gastronom_send_weight_confirmation_email($order);
}

function gastronom_render_weight_confirmation_box($order_or_post): void {
    $order = $order_or_post instanceof WP_Post ? wc_get_order($order_or_post->ID) : $order_or_post;
    if (!$order instanceof WC_Order) {
        echo '<p>Заказ не найден.</p>';
        return;
    }

    $has_items = false;
    wp_nonce_field('gastronom_weight_confirmation', 'gastronom_weight_confirmation_nonce');
    echo '<input type="hidden" name="gastronom_weight_order_id" value="' . esc_attr((string) $order->get_id()) . '">';
    $status_label = wc_get_order_status_name($order->get_status());

    foreach ($order->get_items('line_item') as $item_id => $item) {
        if ($item->get_meta('_gastronom_weight_preorder', true) !== 'yes') {
            continue;
        }
        $has_items = true;
        $product = $item->get_product();
        $title = $product ? $product->get_name() : $item->get_name();
        $qty = (int) $item->get_quantity();
        $min = (float) $item->get_meta('_gastronom_weight_min_kg', true);
        $max = (float) $item->get_meta('_gastronom_weight_max_kg', true);
        $actual = (float) $item->get_meta('_gastronom_actual_weight_kg', true);
        $confirmed = $item->get_meta('_gastronom_weight_confirmed', true) === 'yes';

        echo '<div style="margin:0 0 12px;padding:12px;border:1px solid #dcdcde;border-radius:10px;background:#fff;">';
        echo '<div style="font-weight:600;line-height:1.35;margin-bottom:6px;">' . esc_html($title) . '</div>';
        echo '<div style="margin-bottom:8px;color:#50575e;">' . esc_html((string) $qty) . ' шт. · ' . esc_html(wc_format_localized_decimal($min, 2) . '–' . wc_format_localized_decimal($max, 2)) . ' кг</div>';
        echo '<div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">';
        echo '<input type="number" step="0.01" min="0" placeholder="0.37 кг" name="gastronom_actual_weight[' . esc_attr((string) $item_id) . ']" value="' . esc_attr($actual > 0 ? (string) $actual : '') . '" style="width:120px;">';
        echo '<button type="button" class="button button-primary gastronom-confirm-weight-button">Подтвердить вес</button>';
        echo '</div>';
        echo '<div style="margin-top:8px;color:#50575e;">Статус: <strong>' . esc_html($status_label) . '</strong></div>';
        if ($confirmed) {
            echo '<div style="margin-top:6px;color:#067c36;">Вес подтверждён</div>';
        }
        echo '</div>';
    }

    if (!$has_items) {
        echo '<p>В этом заказе нет товаров с предзаказом по весу.</p>';
        return;
    }
}

function gastronom_order_screen_ids(): array {
    $ids = ['shop_order', 'woocommerce_page_wc-orders'];
    if (function_exists('wc_get_page_screen_id')) {
        $ids[] = wc_get_page_screen_id('shop-order');
    }
    return array_values(array_unique(array_filter($ids)));
}

add_action('admin_footer', function() {
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    $screen_id = $screen && isset($screen->id) ? (string) $screen->id : '';
    if (!in_array($screen_id, gastronom_order_screen_ids(), true)) {
        return;
    }
    ?>
    <style>
    #woocommerce-order-notes,
    .woocommerce-order-notes {
        display: none !important;
    }
    </style>
    <script>
    function gastronomHideClosestContainer(node) {
        if (!node) return;
        const selectors = [
            '.postbox',
            '.components-panel__body',
            '.components-card',
            '.wc-orders__order-preview',
            '.wc-orders__order-panel',
            '.woocommerce-layout__main section',
            '.woocommerce-layout__main .components-flex',
            '.woocommerce-layout__main .components-card',
            '.woocommerce-layout__main .components-panel__body',
            '.misc-pub-section'
        ];
        const container = node.closest(selectors.join(', '));
        if (container) {
            container.style.display = 'none';
        }
    }

    function gastronomHideOrderBoxes() {
        const titlesToHide = [
            'Доступные действия',
            'Order actions',
            'Actions',
            'ePodaci',
            'GLS shipping info',
            'Order notes',
            'Заметки к заказу',
            'Poznámky k objednávke'
        ];

        document.querySelectorAll('.postbox, .components-panel__body, .components-card, .woocommerce-layout__main *').forEach(function(box) {
            const heading = box.querySelector && box.querySelector('.hndle, .postbox-header h2, .postbox-header, h1, h2, h3, h4, button, summary, .components-panel__body-title, .components-card__header');
            if (!heading) return;
            const text = (heading.textContent || '').trim();
            if (!text) return;
            if (titlesToHide.some(function(title) { return text === title || text.indexOf(title) !== -1; })) {
                gastronomHideClosestContainer(heading);
            }
        });

        document.querySelectorAll('h1, h2, h3, h4, button, summary, span, div').forEach(function(el) {
            const text = (el.textContent || '').trim();
            if (!text) return;
            if (titlesToHide.some(function(title) { return text === title || text.indexOf(title) !== -1; })) {
                gastronomHideClosestContainer(el);
            }
        });
    }

    document.addEventListener('DOMContentLoaded', gastronomHideOrderBoxes);
    setTimeout(gastronomHideOrderBoxes, 300);
    setTimeout(gastronomHideOrderBoxes, 1000);
    setTimeout(gastronomHideOrderBoxes, 2500);

    const gastronomAdminObserver = new MutationObserver(function() {
        gastronomHideOrderBoxes();
    });
    document.addEventListener('DOMContentLoaded', function() {
        if (document.body) {
            gastronomAdminObserver.observe(document.body, {childList: true, subtree: true});
        }
    });

    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.gastronom-confirm-weight-button');
        if (!btn) return;
        e.preventDefault();

        const box = btn.closest('.gastronom-inline-weight-box');
        if (!box || !window.ajaxurl) return;

        const nonceField = box.querySelector('input[name="gastronom_weight_confirmation_nonce"]');
        const orderField = box.querySelector('input[name="gastronom_weight_order_id"]');
        if (!nonceField || !orderField) return;

        const params = new URLSearchParams();
        params.set('action', 'gastronom_confirm_weight');
        params.set('nonce', nonceField.value);
        params.set('order_id', orderField.value);

        box.querySelectorAll('input[name^="gastronom_actual_weight["]').forEach(function(input) {
            params.append(input.name, input.value || '');
        });

        btn.disabled = true;
        fetch(window.ajaxurl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
            body: params.toString()
        }).then(function(res) {
            return res.json();
        }).then(function(res) {
            if (!res || !res.success) {
                const msg = res && res.data && res.data.message ? res.data.message : 'Не удалось подтвердить вес.';
                alert(msg);
                btn.disabled = false;
                return;
            }
            window.location.reload();
        }).catch(function() {
            alert('Не удалось подтвердить вес.');
            btn.disabled = false;
        });
    });

    </script>
    <?php
});

add_action('add_meta_boxes', function() {
    $hide_ids = [
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
}, 999);

add_action('woocommerce_admin_order_data_after_order_details', function($order) {
    if (!gastronom_order_requires_weight_confirmation($order)) {
        return;
    }
    echo '<div class="gastronom-inline-weight-box" style="margin:16px 0 8px;padding:16px 18px;border:1px solid #dcdcde;border-radius:8px;background:#fff;">';
    echo '<h3 style="margin:0 0 12px;font-size:15px;">Подтверждение фактического веса</h3>';
    gastronom_render_weight_confirmation_box($order);
    echo '</div>';
});

add_action('wp_ajax_gastronom_confirm_weight', function() {
    if (empty($_POST['nonce']) || !wp_verify_nonce(wp_unslash($_POST['nonce']), 'gastronom_weight_confirmation')) {
        wp_send_json_error(['message' => 'Неверный nonce.'], 403);
    }

    $order_id = isset($_POST['order_id']) ? (int) $_POST['order_id'] : 0;
    if ($order_id <= 0 || !current_user_can('edit_shop_orders')) {
        wp_send_json_error(['message' => 'Нет доступа.'], 403);
    }

    $order = wc_get_order($order_id);
    if (!$order instanceof WC_Order) {
        wp_send_json_error(['message' => 'Заказ не найден.'], 404);
    }

    $actual_weights = isset($_POST['gastronom_actual_weight']) ? wp_unslash($_POST['gastronom_actual_weight']) : [];
    $changed = false;

    foreach ($order->get_items('line_item') as $item_id => $item) {
        if ($item->get_meta('_gastronom_weight_preorder', true) !== 'yes') {
            continue;
        }
        if (!isset($actual_weights[$item_id])) {
            continue;
        }

        $actual_weight = max(0.0, (float) wc_clean($actual_weights[$item_id]));
        if ($actual_weight <= 0) {
            continue;
        }

        $product = $item->get_product();
        if (!$product) {
            continue;
        }

        $price_per_kg = (float) $item->get_meta('_gastronom_price_per_kg', true);
        if ($price_per_kg <= 0) {
            $price_per_kg = gastronom_weight_preorder_price_per_kg($product->get_id());
        }

        $gross_total = $actual_weight * $price_per_kg;
        $net_total = wc_prices_include_tax()
            ? wc_get_price_excluding_tax($product, ['price' => $gross_total, 'qty' => 1])
            : $gross_total;

        $item->set_subtotal($net_total);
        $item->set_total($net_total);
        $item->update_meta_data('_gastronom_actual_weight_kg', $actual_weight);
        $item->update_meta_data('_gastronom_weight_confirmed', 'yes');
        $item->save();
        $changed = true;
    }

    if (!$changed) {
        wp_send_json_error(['message' => 'Не указан фактический вес.'], 400);
    }

    $order->calculate_taxes();
    $order->calculate_totals(false);
    $order->add_order_note(gastronom_tt($order, 'Фактический вес подтверждён, заказ пересчитан.', 'Skutočná hmotnosť bola potvrdená, objednávka bola prepočítaná.'));
    $order->save();

    gastronom_sync_confirmed_preorder_items_to_dotypos($order);
    gastronom_mark_weight_confirmed_order_ready($order);

    wp_send_json_success(['message' => 'ok']);
});
