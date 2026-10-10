<?php
declare(strict_types=1);
require_once __DIR__ . '/Consent.php';
require_once __DIR__ . '/AdminLanguage.php';
use Gastronom\OrderReview\Review;
use Gastronom\OrderReview\Mail;
use Gastronom\OrderReview\Fulfillment;

function gor_managed($order): bool {
    if (is_numeric($order)) { $order = wc_get_order($order); }
    if (!$order instanceof WC_Order || !defined('GASTRONOM_REVIEW_FROM_UTC')) { return false; }
    $date = $order->get_date_created();
    if (is_array($order->get_meta('_gor_state', true))) { return true; }
    $cutoff = strtotime((string)get_option('gor_review_cutoff', GASTRONOM_REVIEW_FROM_UTC) . ' UTC');
    return $date && $cutoff !== false && $date->getTimestamp() >= $cutoff;
}

function gor_order_language(WC_Order $order): string {
    return $order->get_meta('_gastronom_lang', true) === 'ru' ? 'ru' : 'sk';
}

function gor_state(WC_Order $order): array {
    $state = $order->get_meta('_gor_state', true);
    if (is_array($state) && ($state['schema'] ?? null) === 1) { $state['language'] = gor_order_language($order); return $state; }
    if ($state !== '' && $state !== null) { throw new DomainException('Данные корректировки повреждены. Требуется восстановить сохранённую версию.'); }
    $lines = [];
    foreach ($order->get_items() as $item) {
        $product = $item->get_product();
        if (!$product) { throw new DomainException('Товар исходного заказа удалён из каталога. Требуется восстановить привязку.'); }
        $weighted = $item->get_meta('_gastronom_weight_preorder', true) === 'yes';
        $weight = (float) $item->get_meta('_gastronom_actual_weight_kg', true);
        $gross = (float) $item->get_total() + (float) $item->get_total_tax();
        $qty = $weighted && $weight > 0 ? $weight : (float) $item->get_quantity();
        $lines[] = ['key' => 'item-' . $item->get_id(), 'product_id' => $product->get_id(), 'name' => $item->get_name(), 'qty' => (int) round($qty * 1000),
            'total' => (int) round($gross * 100), 'unit' => $weighted ? 'kg' : 'pcs', 'weight_pending' => $weighted && $weight <= 0,
            'weight_min_kg' => (float) $item->get_meta('_gastronom_weight_min_kg', true), 'weight_max_kg' => (float) $item->get_meta('_gastronom_weight_max_kg', true), 'ordered_units' => (float) $item->get_quantity(),
            'rate' => $weighted ? (int) round((float) $item->get_meta('_gastronom_price_per_kg', true) * 100) : (int) round($gross / max($qty, 0.001) * 100)];
    }
    $shipping = (int) round(((float) $order->get_shipping_total() + (float) $order->get_shipping_tax()) * 100);
    $fees = (int) round((float) $order->get_total('edit') * 100) - array_sum(array_column($lines, 'total')) - $shipping;
    if ($fees < 0 || $order->get_currency() !== 'EUR') { throw new DomainException('Этот вариант валюты или отрицательных сборов требует отдельной проверки.'); }
    $courier = false;
    foreach ($order->get_shipping_methods() as $shipping) { if ($shipping->get_method_id() !== 'local_pickup') { $courier = true; } }
    $paid = $order->get_payment_method() !== 'cod' && $order->get_date_paid() ? (int) round((float) $order->get_total('edit') * 100) : 0;
    $state = Review::create(['lines' => $lines, 'shipping' => (int) round(((float) $order->get_shipping_total() + (float) $order->get_shipping_tax()) * 100), 'fees' => $fees], $paid, gor_order_language($order), $courier);
    $state['source_fingerprint'] = gor_source_fingerprint($order);
    return $state;
}

function gor_source_fingerprint(WC_Order $order): string {
    $rows = [];
    foreach ($order->get_items() as $item) { $rows[] = [$item->get_id(), $item->get_quantity(), $item->get_total(), $item->get_total_tax()]; }
    return hash('sha256', json_encode([$order->get_currency(), $order->get_total('edit'), $rows], JSON_PRESERVE_ZERO_FRACTION));
}

function gor_customer(WC_Order $order): array {
    return ['number' => $order->get_order_number(), 'name' => $order->get_billing_first_name(),
        'email' => $order->get_billing_email(), 'phone' => $order->get_billing_phone(),
        'full_name' => trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name()),
        'address' => trim(wp_strip_all_tags(preg_replace('~<br\s*/?>~i', "\n", $order->get_formatted_shipping_address() ?: $order->get_formatted_billing_address()))),
        'payment' => $order->get_payment_method_title(), 'shipping' => $order->get_shipping_method(), 'cod' => $order->get_payment_method() === 'cod'];
}

function gor_mail_state(array $state): array {
    if (!function_exists('gastronom_localize_title')) { return $state; }
    foreach (['quote','original'] as $key) {
        foreach ($state[$key]['lines'] as &$line) { $line['name'] = gastronom_localize_title(wp_strip_all_tags($line['name']), $state['language']); }
        unset($line);
    }
    return $state;
}

function gor_mail_customer(WC_Order $order, array $state): array {
    $customer = gor_customer($order);
    if (function_exists('gastronom_localize_order_label')) {
        foreach (['payment','shipping'] as $key) { $customer[$key] = gastronom_localize_order_label($customer[$key], $state['language']); }
    }
    return $customer;
}

function gor_save(WC_Order $order, array $state): void { $order->update_meta_data('_gor_state', $state); $order->save_meta_data(); }

function gor_normalize_quote(array $input, array $state): array {
    $lines = [];
    foreach (($input['lines'] ?? []) as $raw) {
        $product = wc_get_product(absint($raw['product_id'] ?? 0));
        if (!$product) { throw new DomainException('Выбранный товар не найден.'); }
        $qty = Review::quantity($raw['quantity'] ?? '');
        $prior = null;
        foreach ($state['quote']['lines'] as $old) {
            if (!empty($raw['key']) && ($old['key'] ?? '') === $raw['key'] && $old['product_id'] === $product->get_id()) { $prior = $old; break; }
        }
        // Product type is a catalog/order fact, never a browser-supplied bypass of weight confirmation.
        $weighted = ($prior && $prior['unit'] === 'kg') || $product->get_meta('_gastronom_weight_preorder', true) === 'yes';
        $rate = Review::money($raw['price'] ?? '');
        if (!$weighted && $qty % 1000 !== 0) { throw new DomainException('Для штучного товара требуется целое количество.'); }
        $lineTotal = $prior && $prior['qty'] === $qty && $prior['rate'] === $rate ? $prior['total'] : (int) round($qty * $rate / 1000, 0, PHP_ROUND_HALF_UP);
        $lines[] = ['key' => $prior['key'] ?? wp_generate_uuid4(), 'product_id' => $product->get_id(), 'name' => $product->get_name(), 'qty' => $qty,
            'rate' => $rate, 'unit' => $weighted ? 'kg' : 'pcs', 'weight_pending' => $weighted && empty($raw['weight_confirmed']),
            'total' => $lineTotal];
    }
    return ['lines' => $lines, 'shipping' => $lines ? $state['original']['shipping'] : 0, 'fees' => $lines ? $state['original']['fees'] : 0];
}

function gor_permission(WP_REST_Request $request): bool {
    return current_user_can('edit_shop_orders') && current_user_can('edit_shop_order', (int) $request['id']);
}

function gor_api(WP_REST_Request $request, ?array $customerConsent = null) {
    $order = wc_get_order((int) $request['id']);
    if (!$order || !gor_managed($order)) { return new WP_Error('gor_missing', 'Заказ не относится к новой схеме.', ['status' => 404]); }
    $lock = '_gor_lock_' . $order->get_id();
    // add_option is unique at the database level; a crashed request is fail-closed for manual recovery.
    if (!add_option($lock, time(), '', false)) { return new WP_Error('gor_busy', 'Другая операция выполняется. Обновите страницу; при длительной блокировке нужна проверка журнала.', ['status' => 409]); }
    try {
        $order->read_meta_data(true);
        $state = gor_state($order);
        if ($customerConsent !== null) {
            if (!gor_valid_consent($order, $state, $customerConsent)) { throw new DomainException('Предложение устарело или ссылка недействительна.'); }
            if ($state['status'] === 'agreed') { return ['state' => $state]; }
        }
        if (isset($state['source_fingerprint']) && $state['source_fingerprint'] !== gor_source_fingerprint($order)) {
            throw new DomainException('Исходный заказ изменён вне блока Gastronom. Требуется сверить состав и оплату перед продолжением.');
        }
        if ($customerConsent === null && (($request->get_json_params()['action'] ?? '') === 'read')) { return ['state' => $state]; }
        if ($order->get_payment_method() !== 'cod' && $order->get_date_paid() && $state['paid'] !== (int) round((float) $order->get_total('edit') * 100)) {
            $state['paid'] = (int) round((float) $order->get_total('edit') * 100);
            $state['version']++;
            gor_save($order, $state);
            return new WP_Error('gor_payment_changed', 'Получена оплата. Обновите страницу для актуального расчёта.', ['status' => 409]);
        }
        $input = (array) $request->get_json_params();
        $action = (string) ($input['action'] ?? '');
        if ($customerConsent !== null) {
            $action = 'agree';
            $input = ['version' => $state['version'], 'consent_note' => 'customer-link'];
        } elseif ($action === 'agree') {
            $channel = (string) ($input['consent_channel'] ?? '');
            if (in_array($channel, ['email','phone'], true)) { $input['consent_note'] = 'admin-' . $channel; }
        }
        if ($action === 'agree') { $input['consent_at'] = gmdate('Y-m-d H:i:s'); }
        if (in_array($action, ['approve','retry'], true) && in_array($order->get_status(), ['cancelled','refunded','failed','trash'], true)) {
            throw new DomainException('Отменённый или неуспешный заказ нельзя передать в исполнение.');
        }
        if ($action === 'preview_confirmation') { $date = (string) ($input['dispatch_date'] ?? $state['dispatch_date']); $parsed = \DateTimeImmutable::createFromFormat('!Y-m-d', $date); if ($parsed && $parsed->format('Y-m-d') === $date && $date >= current_time('Y-m-d')) { $state['dispatch_date'] = $date; } return ['preview' => Mail::build(gor_mail_state($state), gor_mail_customer($order, $state), 'confirmation')]; }
        if ($action === 'preview') {
                        $customer = gor_mail_customer($order, $state);
            $customer['approval_url'] = gor_approval_url($order, $state, time() + 7 * DAY_IN_SECONDS);
            $customer['contact_email'] = sanitize_email((string) get_option('woocommerce_email_from_address', get_option('admin_email')));
            return ['preview' => Mail::build(gor_mail_state($state), $customer, 'proposal')];
        }
        if ($action === 'mail_retry') {
            if ((int) ($input['version'] ?? 0) !== $state['version']) { throw new DomainException('Обновите страницу.'); }
            $key = (string) ($input['key'] ?? '');
            if ($key !== 'confirmation' && $key !== 'proposal-'.$state['revision']) { throw new DomainException('Это письмо относится к устаревшему составу.'); }
            if (!isset($state['mail'][$key]) || $state['mail'][$key]['status'] === 'sent') { throw new DomainException('Нет неотправленного письма.'); }
            gor_deliver($order, $state, $key);
            return ['state' => $state];
        }
        if ($action === 'save') { $input['quote'] = gor_normalize_quote((array) ($input['quote'] ?? []), $state); }
        $input['cod'] = $order->get_payment_method() === 'cod';
        $input['order_id'] = $order->get_id();
        $state = Review::command($state, $action, $input, current_time('Y-m-d'), $customerConsent !== null ? 'customer' : (string) get_current_user_id());
        if ($action === 'approve' && $state['approved_quote']['lines']) { $preparedPlan=gor_plan($order, $state); }
        if (in_array($action, ['save','weight','cancel_edit','approve'], true)) { gor_reserve($order, $state['quote']); }
        if (isset($preparedPlan)) { $order->update_meta_data('_gor_plan', $preparedPlan); }
        gor_save($order, $state); // Persist intent before any external effect.
        if ($action === 'proposal') { gor_deliver($order, $state, 'proposal-' . $state['revision']); }
        if (($action === 'approve' || $action === 'retry') && !$state['approved_quote']['lines']) {
            gor_release_hold($order);
            $state=Review::complete($state, true, 'cancelled-without-sale');
            gor_save($order, $state);
            gor_deliver($order, $state, 'confirmation');
            return ['state'=>$state];
        }
        if ($action === 'approve' || $action === 'retry') {
            try {
                $result = gor_execute($order, $state);
                if (gor_is_sandbox()) { gor_settle_sandbox_stock($order, $state); }
                else { gor_settle_live_stock($order, $state); }
                $state = Review::complete($state, true, $result['receipt_id']);
            } catch (Throwable $e) {
                $state = Review::complete($state, false, $e->getMessage());
                gor_save($order, $state);
                return ['state' => $state];
            }
            gor_save($order, $state);
            gor_deliver($order, $state, 'confirmation');
        }
        return ['state' => $state];
    } catch (DomainException $e) {
        return new WP_Error('gor_invalid', $e->getMessage(), ['status' => 409]);
    } finally { delete_option($lock); }
}

function gor_deliver(WC_Order $order, array &$state, string $key): void {
    if (($state['mail'][$key]['status'] ?? '') === 'sent') { return; }
    $entry = $state['mail'][$key];
    if (!isset($entry['message'])) {
        $customer = gor_mail_customer($order, $state);
        if ($key !== 'confirmation') {
            $entry['approval_expires'] = time() + 7 * DAY_IN_SECONDS;
            $customer['approval_url'] = gor_approval_url($order, $state, $entry['approval_expires']);
            $customer['contact_email'] = sanitize_email((string) get_option('woocommerce_email_from_address', get_option('admin_email')));
        }
        $entry['message'] = Mail::build(gor_mail_state($state), $customer, $key === 'confirmation' ? 'confirmation' : 'proposal');
        $entry['to'] = $order->get_billing_email();
        $entry['reply_to'] = sanitize_email((string) get_option('woocommerce_email_from_address', get_option('admin_email')));
    }
    $entry['transport'] = gor_is_sandbox() ? 'sandbox-capture' : 'wordpress-mail';
    $save = static function (array $entry) use ($order, &$state, $key): void {
        $entry['at'] = current_time('mysql');
        $state['mail'][$key] = $entry;
        $state['version']++;
        gor_save($order, $state);
    };
    $transport=gor_is_sandbox() ? static function (): bool { return !get_option('gor_sandbox_fail_mail', false); } : [\Gastronom\OrderReview\Outbox::class, 'wordpress'];
    \Gastronom\OrderReview\Outbox::deliver($entry, $save, $transport);
}

add_action('rest_api_init', static function (): void {
    register_rest_route('gastronom-review/v1', '/orders/(?P<id>\d+)', ['methods' => 'POST', 'callback' => static function (WP_REST_Request $r) { $result = gor_api($r); if (is_array($result) && isset($result['state'])) { $result['state'] = gor_admin_state($result['state']); } elseif ($result instanceof WP_Error) { $result = new WP_Error($result->get_error_code(), \Gastronom\OrderReview\AdminLanguage::message($result->get_error_message(), gor_admin_language()), $result->get_error_data()); } return $result; }, 'permission_callback' => 'gor_permission']);
    register_rest_route('gastronom-review/v1', '/products', ['methods' => 'GET', 'permission_callback' => static function (): bool { return current_user_can('edit_shop_orders'); }, 'callback' => static function (WP_REST_Request $r): array {
        $term = sanitize_text_field((string) $r['search']);
        if (strlen($term) < 2) { return []; }
        $ids = WC_Data_Store::load('product')->search_products($term, '', true, false, 15);
        $result = [];
        foreach ($ids as $id) {
            $p = wc_get_product($id);
            if (!$p || $p->is_type('variable')) { continue; }
            $result[] = ['id' => $id, 'name' => wp_strip_all_tags($p->get_formatted_name()), 'weighted' => $p->get_meta('_gastronom_weight_preorder', true) === 'yes', 'price' => number_format((float) wc_get_price_including_tax($p), 2, '.', '')];
        }
        return $result;
    }]);
});

function gor_admin_order($context = null) {
    if ($context instanceof WC_Order) { return $context; }
    if ($context instanceof WP_Post) { return wc_get_order($context->ID); }
    return wc_get_order(absint($_GET['id'] ?? $_GET['post'] ?? 0));
}

add_action('add_meta_boxes', static function (): void {
    $order = gor_admin_order();
    if (!$order || !gor_managed($order)) { return; }
    foreach (['shop_order', 'woocommerce_page_wc-orders'] as $screen) {
        // Remove duplicate notice controls; retain the carrier metaboxes.
        foreach (['woocommerce-order-data', 'woocommerce-order-items', 'postcustom', 'woocommerce-order-downloads'] as $id) { remove_meta_box($id, $screen, 'normal'); }
        remove_meta_box('woocommerce-order-actions', $screen, 'side');
        remove_meta_box('woocommerce-order-notes', $screen, 'side');
        remove_meta_box('rusky-shipping-notice-email', $screen, 'side');
        add_meta_box('gastronom-order-review', gor_admin_language() === 'ru' ? 'Заказ' : 'Objednávka', 'gor_render', $screen, 'normal', 'high');
    }
}, 1000);

function gor_admin_language(): string {
    $locale = get_user_locale();
    if (str_starts_with($locale, 'ru')) { return 'ru'; }
    if (str_starts_with($locale, 'sk')) { return 'sk'; }
    return str_starts_with((string) get_option('WPLANG', 'ru_RU'), 'sk') ? 'sk' : 'ru';
}

function gor_admin_state(array $state): array {
    $lang = gor_admin_language();
    if (isset($state['effect']['reference'])) { $state['effect']['reference'] = \Gastronom\OrderReview\AdminLanguage::message((string) $state['effect']['reference'], $lang); }
    if (function_exists('gastronom_localize_title')) {
        foreach (['quote','original'] as $key) { foreach ($state[$key]['lines'] as &$line) { $line['name'] = gastronom_localize_title(wp_strip_all_tags($line['name']), $lang); } unset($line); }
    }
    return $state;
}

function gor_render($context): void {
    $order = gor_admin_order($context);
    try {
        $state = gor_state($order);
        echo '<div id="gor-app"></div>';
        $adminCustomer = gor_mail_customer($order, ['language' => gor_admin_language()]);
        $config = ['state' => gor_admin_state($state), 'customer' => $adminCustomer, 'uiLanguage' => gor_admin_language(), 'today' => current_time('Y-m-d'), 'sandbox' => gor_is_sandbox(),
            'endpoint' => rest_url('gastronom-review/v1/orders/' . $order->get_id()), 'products' => rest_url('gastronom-review/v1/products'), 'nonce' => wp_create_nonce('wp_rest')];
        echo '<script type="application/json" id="gor-config">' . wp_json_encode($config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . '</script>';
        wp_enqueue_style('gor', plugins_url('../assets/review.css', __FILE__), [], '0.3.0');
        wp_enqueue_script('gor', plugins_url('../assets/review.js', __FILE__), [], '0.3.0', true);
    } catch (DomainException $e) { echo '<p>' . esc_html(\Gastronom\OrderReview\AdminLanguage::message($e->getMessage(), gor_admin_language())) . '</p>'; }
}

// Replace the legacy generated title only in this editor's view; keep persisted order data intact.
add_action('edit_form_top', static function ($view): void {
    if (!$view instanceof WP_Post || $view->post_type !== 'shop_order') { return; }
    $order = wc_get_order($view->ID);
    if (!$order || !gor_managed($order)) { return; }
    global $post;
    $post = clone $view;
    $post->post_title = (gor_admin_language() === 'ru' ? 'Заказ №' : 'Objednávka č. ') . $order->get_order_number();
});

// Payment receipt remains recorded by WooCommerce, but its automatic stock effects do not run.
add_filter('woocommerce_can_reduce_order_stock', static function ($allow, $order) { return gor_managed($order) ? false : $allow; }, PHP_INT_MAX, 2);
add_filter('woocommerce_can_restore_order_stock', static function ($allow, $order) { return gor_managed($order) ? false : $allow; }, PHP_INT_MAX, 2);
add_filter('wc_order_is_editable', static function ($allow, $order) { return gor_managed($order) ? false : $allow; }, PHP_INT_MAX, 2);

// Keep receipt acknowledgement distinct from the final confirmation.
add_filter('wc_get_template', static function ($located, $name, $args) {
    if (in_array($name, ['emails/customer-processing-order.php','emails/customer-on-hold-order.php','emails/plain/customer-processing-order.php','emails/plain/customer-on-hold-order.php'], true)
        && isset($args['order']) && gor_managed($args['order'])) {
        return dirname(__DIR__).'/templates/acknowledgement.php';
    }
    return $located;
}, PHP_INT_MAX, 3);

foreach (['customer_processing_order', 'customer_on_hold_order'] as $type) {
    add_filter('woocommerce_email_subject_' . $type, static function ($subject, $order) {
        if (!gor_managed($order)) { return $subject; }
        return (gor_order_language($order) === 'ru' ? 'Заказ получен и ожидает проверки наличия №' : 'Objednávka prijatá, čaká na overenie dostupnosti č. ') . $order->get_order_number();
    }, 1000, 2);
}

add_action('woocommerce_order_details_before_order_table', static function ($order): void {
    if (!gor_managed($order)) { return; }
    $s=gor_state($order);
    if (in_array($s['status'], ['approved','cancelled'], true) && $s['quote']!==$s['original']) {
        echo '<p>'.($s['language']==='ru' ? 'Ниже — первоначальный заказ. Согласованный состав и итог приведены отдельным блоком после него.' : 'Nižšie je pôvodná objednávka. Odsúhlasené zloženie a suma sú uvedené v samostatnej časti za ňou.').'</p>';
    }
});

// Every new-scheme email is captured even when an unrelated Woo component triggers it.
if (gor_is_sandbox()) {
add_filter('pre_wp_mail', static function ($pre, $args) { return true; }, PHP_INT_MAX, 2);
// Sandbox defence in depth: no carrier, fiscal, payment or other HTTP request can leave WordPress.
add_filter('pre_http_request', static function () { return new WP_Error('gor_sandbox_http', 'Order-review sandbox blocks external HTTP.'); }, PHP_INT_MAX, 3);
}

// A payment arriving after the admin first opened the page is recorded once.
add_action('woocommerce_payment_complete', static function ($id): void {
    $order = wc_get_order($id);
    if (!$order || !gor_managed($order)) { return; }
    $s = $order->get_meta('_gor_state', true);
    if ($order->get_payment_method() === 'cod' || !is_array($s) || !$order->get_date_paid()) { return; }
    $lock = '_gor_lock_' . $order->get_id();
    if (!add_option($lock, time(), '', false)) { return; } // gor_api reconciles again on its next request.
    try {
        $amount = (int) round((float) $order->get_total('edit') * 100);
        if ($s['paid'] !== $amount) { $s['paid'] = $amount; $s['version']++; gor_save($order, $s); }
    } finally { delete_option($lock); }
}, 1000);

add_action('woocommerce_order_details_after_order_table', static function ($order): void {
    if (!gor_managed($order)) { return; }
    $s = gor_state($order);
    if (in_array($s['status'], ['approved','cancelled'], true) && $s['quote'] !== $s['original']) {
        echo '<section><h2>' . ($s['status']==='cancelled' ? ($s['language']==='ru' ? 'Заказ отменён по согласованию' : 'Objednávka zrušená po dohode') : ($s['language'] === 'ru' ? 'Согласованный состав заказа' : 'Odsúhlasené zloženie objednávky')) . '</h2>' . Mail::table($s['approved_quote'], $s['language']) . '</section>';
    }
});

add_action('init', static function (): void { add_option('gor_review_cutoff', GASTRONOM_REVIEW_FROM_UTC, '', false); }, 1);

require_once __DIR__ . '/CustomerApproval.php';
