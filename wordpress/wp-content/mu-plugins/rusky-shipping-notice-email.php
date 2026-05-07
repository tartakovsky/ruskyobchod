<?php
/**
 * Plugin Name: Rusky Shipping Notice Email
 * Description: Adds an admin order button for sending bilingual shipping-date notices to customers.
 */

if (!defined('ABSPATH')) {
    exit;
}

function rsne_order_screen_ids(): array {
    $ids = ['shop_order', 'woocommerce_page_wc-orders'];
    if (function_exists('wc_get_page_screen_id')) {
        $ids[] = wc_get_page_screen_id('shop-order');
    }

    return array_values(array_unique(array_filter($ids)));
}

function rsne_order_from_context($order_or_post = null) {
    if ($order_or_post instanceof WC_Order) {
        return $order_or_post;
    }

    if ($order_or_post instanceof WP_Post) {
        $order = wc_get_order($order_or_post->ID);
        return $order instanceof WC_Order ? $order : null;
    }

    $order_id = 0;
    if (isset($_GET['post'])) {
        $order_id = (int) wp_unslash($_GET['post']);
    } elseif (isset($_GET['id'])) {
        $order_id = (int) wp_unslash($_GET['id']);
    }

    if ($order_id <= 0) {
        return null;
    }

    $order = wc_get_order($order_id);
    return $order instanceof WC_Order ? $order : null;
}

function rsne_add_shipping_notice_metabox(): void {
    foreach (rsne_order_screen_ids() as $screen_id) {
        add_meta_box(
            'rusky-shipping-notice-email',
            'Уведомление об отправке',
            'rsne_render_shipping_notice_metabox',
            $screen_id,
            'side',
            'default'
        );
    }
}
add_action('add_meta_boxes', 'rsne_add_shipping_notice_metabox', 60);

function rsne_keep_shipping_notice_metabox_visible(array $hidden): array {
    return array_values(array_diff($hidden, ['rusky-shipping-notice-email']));
}
add_filter('hidden_meta_boxes', 'rsne_keep_shipping_notice_metabox_visible');

function rsne_get_order_shipping_company(WC_Order $order): string {
    foreach ($order->get_shipping_methods() as $shipping_method) {
        $method_id = strtolower((string) $shipping_method->get_method_id());
        $title = trim(wp_strip_all_tags((string) $shipping_method->get_name()));

        if (strpos($method_id, 'gls') !== false || stripos($title, 'gls') !== false) {
            return 'GLS';
        }

        if (strpos($method_id, 'packeta') !== false || stripos($title, 'packeta') !== false) {
            return 'Packeta';
        }

        if ($title !== '') {
            return $title;
        }
    }

    $shipping_title = trim(wp_strip_all_tags((string) $order->get_shipping_method()));
    return $shipping_title !== '' ? $shipping_title : 'dopravca';
}

function rsne_get_order_tracking_numbers(WC_Order $order): array {
    $tracking_numbers = [];

    $gls_tracking_codes = $order->get_meta('_gls_tracking_codes', true);
    if (is_array($gls_tracking_codes)) {
        foreach ($gls_tracking_codes as $tracking_code) {
            $tracking_code = trim((string) $tracking_code);
            if ($tracking_code !== '') {
                $tracking_numbers[] = $tracking_code;
            }
        }
    } elseif (is_string($gls_tracking_codes) && trim($gls_tracking_codes) !== '') {
        $tracking_numbers[] = trim($gls_tracking_codes);
    }

    $legacy_gls_tracking_code = trim((string) $order->get_meta('_gls_tracking_code', true));
    if ($legacy_gls_tracking_code !== '') {
        $tracking_numbers[] = $legacy_gls_tracking_code;
    }

    return array_values(array_unique($tracking_numbers));
}

function rsne_tracking_url(string $carrier, string $tracking_number): string {
    if (strtolower($carrier) === 'gls') {
        return 'https://gls-group.eu/SK/en/parcel-tracking/?match=' . rawurlencode($tracking_number);
    }

    return '';
}

function rsne_latest_gls_pickup_date(): string {
    global $wpdb;

    $table_name = $wpdb->prefix . 'gls_pickup_history';
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reads the GLS plugin's custom pickup-history table.
    $request_data = $wpdb->get_var("SELECT request_data FROM {$table_name} WHERE status = 'success' ORDER BY created_at DESC, id DESC LIMIT 1");
    if (!is_string($request_data) || trim($request_data) === '') {
        return '';
    }

    $decoded = json_decode($request_data, true);
    if (!is_array($decoded) || empty($decoded['pickup_date_from'])) {
        return '';
    }

    $timestamp = strtotime((string) $decoded['pickup_date_from']);
    if ($timestamp === false) {
        return '';
    }

    return wp_date('Y-m-d', $timestamp);
}

function rsne_default_dispatch_date(string $carrier = ''): string {
    if (strtolower($carrier) === 'gls') {
        $pickup_date = rsne_latest_gls_pickup_date();
        if ($pickup_date !== '') {
            return $pickup_date;
        }
    }

    return wp_date('Y-m-d');
}

function rsne_render_shipping_notice_metabox($order_or_post): void {
    $order = rsne_order_from_context($order_or_post);
    if (!$order instanceof WC_Order) {
        echo '<p>Заказ не найден.</p>';
        return;
    }

    $email = sanitize_email((string) $order->get_billing_email());
    if ($email === '') {
        echo '<p>У клиента не указан email.</p>';
        return;
    }

    $carrier = rsne_get_order_shipping_company($order);
    $tracking_numbers = rsne_get_order_tracking_numbers($order);
    $last_sent_at = (string) $order->get_meta('_rusky_shipping_notice_sent_at', true);
    $last_sent_date = (string) $order->get_meta('_rusky_shipping_notice_dispatch_date', true);
    $dispatch_date = $last_sent_date !== '' ? $last_sent_date : rsne_default_dispatch_date($carrier);

    echo '<p><strong>Email:</strong><br>' . esc_html($email) . '</p>';
    echo '<p><strong>Перевозчик:</strong><br>' . esc_html($carrier) . '</p>';
    if (!empty($tracking_numbers)) {
        echo '<p><strong>Tracking:</strong><br>' . esc_html(implode(', ', $tracking_numbers)) . '</p>';
    }
    if ($last_sent_at !== '') {
        $last_sent_to = (string) $order->get_meta('_rusky_shipping_notice_sent_to', true);
        echo '<div style="border-left:4px solid #00a32a;background:#f0f6ee;padding:8px 10px;margin:10px 0;">';
        echo '<strong>Письмо клиенту отправлено.</strong><br>';
        echo esc_html($last_sent_at);
        if ($last_sent_to !== '') {
            echo '<br>' . esc_html($last_sent_to);
        }
        echo '</div>';
    }

    $send_url = wp_nonce_url(
        add_query_arg(
            [
                'action' => 'rusky_send_shipping_notice',
                'order_id' => $order->get_id(),
            ],
            admin_url('admin-post.php')
        ),
        'rusky_send_shipping_notice_' . $order->get_id(),
        'rusky_shipping_notice_nonce'
    );

    echo '<p>';
    echo '<label for="rusky_shipping_notice_dispatch_date"><strong>Дата передачи курьеру</strong></label><br>';
    echo '<input type="date" id="rusky_shipping_notice_dispatch_date" name="dispatch_date" value="' . esc_attr($dispatch_date) . '" style="width:100%;">';
    echo '</p>';
    echo '<p><a href="' . esc_url(add_query_arg('dispatch_date', $dispatch_date, $send_url)) . '" class="button button-primary" id="rusky_shipping_notice_send" data-base-url="' . esc_url($send_url) . '" style="width:100%;text-align:center;">Отправить клиенту</a></p>';
    echo '<script>
        (function() {
            var dateInput = document.getElementById("rusky_shipping_notice_dispatch_date");
            var sendButton = document.getElementById("rusky_shipping_notice_send");
            if (!dateInput || !sendButton) {
                return;
            }
            var updateHref = function() {
                var baseUrl = sendButton.getAttribute("data-base-url");
                var separator = baseUrl.indexOf("?") === -1 ? "?" : "&";
                sendButton.href = baseUrl + separator + "dispatch_date=" + encodeURIComponent(dateInput.value || "");
            };
            dateInput.addEventListener("change", updateHref);
            updateHref();
        })();
    </script>';
}

function rsne_format_display_date(string $date): string {
    $timestamp = strtotime($date . ' 00:00:00');
    if ($timestamp === false) {
        return $date;
    }

    return wp_date('d.m.Y', $timestamp);
}

function rsne_build_shipping_notice_message(WC_Order $order, string $carrier, array $tracking_numbers, string $dispatch_date): string {
    $order_number = $order->get_order_number();
    $date_display = rsne_format_display_date($dispatch_date);
    $tracking_number = $tracking_numbers[0] ?? '';
    $tracking_url = $tracking_number !== '' ? rsne_tracking_url($carrier, $tracking_number) : '';

    $tracking_sk = $tracking_number !== ''
        ? '<p><strong>Sledovacie číslo:</strong> ' . esc_html($tracking_number) . '</p>'
        : '';
    $tracking_ru = $tracking_number !== ''
        ? '<p><strong>Номер отслеживания:</strong> ' . esc_html($tracking_number) . '</p>'
        : '';

    $link_sk = $tracking_url !== ''
        ? '<p><a href="' . esc_url($tracking_url) . '">Sledovať zásielku</a></p>'
        : '';
    $link_ru = $tracking_url !== ''
        ? '<p><a href="' . esc_url($tracking_url) . '">Отследить посылку</a></p>'
        : '';

    $html = '<p>Dobrý deň,</p>';
    $html .= '<p>vaša objednávka č. <strong>' . esc_html($order_number) . '</strong> je pripravená na odoslanie.</p>';
    $html .= '<p><strong>Dopravca:</strong> ' . esc_html($carrier) . '<br>';
    $html .= '<strong>Plánovaný dátum odovzdania kuriérskej službe:</strong> ' . esc_html($date_display) . '</p>';
    $html .= $tracking_sk . $link_sk;
    $html .= '<p>Ďakujeme za objednávku.</p>';

    $html .= '<hr style="border:none;border-top:1px solid #e5e7eb;margin:20px 0;">';

    $html .= '<p>Здравствуйте,</p>';
    $html .= '<p>ваш заказ № <strong>' . esc_html($order_number) . '</strong> готов к отправке.</p>';
    $html .= '<p><strong>Служба доставки:</strong> ' . esc_html($carrier) . '<br>';
    $html .= '<strong>Планируемая дата передачи курьерской службе:</strong> ' . esc_html($date_display) . '</p>';
    $html .= $tracking_ru . $link_ru;
    $html .= '<p>Спасибо за заказ.</p>';

    return $html;
}

function rsne_send_shipping_notice(WC_Order $order, string $dispatch_date): bool {
    $to = sanitize_email((string) $order->get_billing_email());
    if ($to === '') {
        return false;
    }

    $carrier = rsne_get_order_shipping_company($order);
    $tracking_numbers = rsne_get_order_tracking_numbers($order);
    $subject = sprintf(
        'Odoslanie objednávky č. %1$s / Отправка заказа № %1$s',
        $order->get_order_number()
    );
    $message = rsne_build_shipping_notice_message($order, $carrier, $tracking_numbers, $dispatch_date);

    $sent = wc_mail($to, $subject, $message);
    if (!$sent) {
        return false;
    }

    $order->update_meta_data('_rusky_shipping_notice_sent_at', current_time('mysql'));
    $order->update_meta_data('_rusky_shipping_notice_sent_to', $to);
    $order->update_meta_data('_rusky_shipping_notice_dispatch_date', $dispatch_date);
    $order->update_meta_data('_rusky_shipping_notice_carrier', $carrier);
    $order->update_meta_data('_rusky_shipping_notice_tracking', implode(', ', $tracking_numbers));
    $order->add_order_note(sprintf(
        'Shipping notice sent to customer: %s, carrier: %s, dispatch date: %s, tracking: %s',
        $to,
        $carrier,
        rsne_format_display_date($dispatch_date),
        !empty($tracking_numbers) ? implode(', ', $tracking_numbers) : '-'
    ));
    $order->save();

    return true;
}

function rsne_handle_send_shipping_notice(): void {
    $order_id = isset($_REQUEST['order_id']) ? (int) wp_unslash($_REQUEST['order_id']) : 0;
    $redirect = $order_id > 0 ? get_edit_post_link($order_id, 'raw') : admin_url('edit.php?post_type=shop_order');

    if ($order_id <= 0 || (!current_user_can('edit_post', $order_id) && !current_user_can('manage_woocommerce'))) {
        wp_safe_redirect(add_query_arg('rusky_shipping_notice', 'permission', $redirect));
        exit;
    }

    $nonce = isset($_REQUEST['rusky_shipping_notice_nonce']) ? sanitize_text_field(wp_unslash($_REQUEST['rusky_shipping_notice_nonce'])) : '';
    if (!wp_verify_nonce($nonce, 'rusky_send_shipping_notice_' . $order_id)) {
        wp_safe_redirect(add_query_arg('rusky_shipping_notice', 'nonce', $redirect));
        exit;
    }

    $dispatch_date = isset($_REQUEST['dispatch_date']) ? sanitize_text_field(wp_unslash($_REQUEST['dispatch_date'])) : '';
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dispatch_date)) {
        wp_safe_redirect(add_query_arg('rusky_shipping_notice', 'date', $redirect));
        exit;
    }

    $order = wc_get_order($order_id);
    if (!$order instanceof WC_Order) {
        wp_safe_redirect(add_query_arg('rusky_shipping_notice', 'missing', $redirect));
        exit;
    }

    $result = rsne_send_shipping_notice($order, $dispatch_date);
    wp_safe_redirect(add_query_arg('rusky_shipping_notice', $result ? 'sent' : 'failed', $redirect));
    exit;
}
add_action('admin_post_rusky_send_shipping_notice', 'rsne_handle_send_shipping_notice');

function rsne_shipping_notice_admin_notice(): void {
    if (!isset($_GET['rusky_shipping_notice'])) {
        return;
    }

    $status = sanitize_key(wp_unslash($_GET['rusky_shipping_notice']));
    $messages = [
        'sent' => ['success', 'Уведомление клиенту отправлено.'],
        'failed' => ['error', 'Не удалось отправить уведомление клиенту.'],
        'date' => ['error', 'Укажите корректную дату отправки.'],
        'nonce' => ['error', 'Проверка безопасности не прошла. Повторите действие.'],
        'permission' => ['error', 'Недостаточно прав для отправки уведомления.'],
        'missing' => ['error', 'Заказ не найден.'],
    ];

    if (!isset($messages[$status])) {
        return;
    }

    [$type, $message] = $messages[$status];
    echo '<div class="notice notice-' . esc_attr($type) . ' is-dismissible"><p>' . esc_html($message) . '</p></div>';
}
add_action('admin_notices', 'rsne_shipping_notice_admin_notice');
