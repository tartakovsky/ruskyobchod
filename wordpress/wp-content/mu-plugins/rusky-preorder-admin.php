<?php
/**
 * Plugin Name: Rusky Preorder Admin
 * Description: Extraction-ready preorder admin helpers.
 *
 * Scaffold status:
 * - local hardening surface only
 * - no live hook registration yet
 * - uses `rpa_*` names to avoid collisions with current `gastronom_*` ownership
 */

if (!defined('ABSPATH')) {
    exit;
}

function rpa_render_product_preorder_fields(): void {
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

function rpa_save_product_preorder_fields($post_id): void {
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

function rpa_render_weight_confirmation_box($order_or_post): void {
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

        echo '<div class="gastronom-weight-item">';
        echo '<div class="gastronom-weight-item__summary">';
        echo '<div class="gastronom-weight-item__title">' . esc_html($title) . '</div>';
        echo '<div class="gastronom-weight-item__meta">' . esc_html((string) $qty) . ' шт. · ' . esc_html(wc_format_localized_decimal($min, 2) . '–' . wc_format_localized_decimal($max, 2)) . ' кг</div>';
        echo '<div class="gastronom-weight-item__status">Статус заказа: <strong>' . esc_html($status_label) . '</strong></div>';
        if ($confirmed) {
            echo '<div class="gastronom-weight-item__confirmed">Вес подтверждён</div>';
        }
        echo '</div>';
        echo '<label class="gastronom-weight-item__field">';
        echo '<span class="gastronom-weight-item__field-label">Фактический вес, кг</span>';
        echo '<input type="number" step="0.01" min="0" placeholder="0.37" name="gastronom_actual_weight[' . esc_attr((string) $item_id) . ']" value="' . esc_attr($actual > 0 ? (string) $actual : '') . '">';
        echo '</label>';
        echo '</div>';
    }

    if (!$has_items) {
        echo '<p>В этом заказе нет товаров с предзаказом по весу.</p>';
        return;
    }

    echo '<div class="gastronom-weight-box__actions">';
    echo '<div class="gastronom-weight-box__hint">Подтверждение применяется ко всем товарам с указанным фактическим весом в этом заказе.</div>';
    echo '<button type="button" class="button button-primary gastronom-confirm-weight-button">Подтвердить вес по заказу</button>';
    echo '</div>';
}

function rpa_order_screen_ids(): array {
    $ids = ['shop_order', 'woocommerce_page_wc-orders'];
    if (function_exists('wc_get_page_screen_id')) {
        $ids[] = wc_get_page_screen_id('shop-order');
    }

    return array_values(array_unique(array_filter($ids)));
}

function rpa_hidden_meta_box_ids(): array {
    return [
        'tsseph_meta_box',
        'woocommerce-order-actions',
        'woocommerce-order-notes',
        'gls_shipping_info_meta_box',
        'gastronom-weight-confirmation',
    ];
}

function rpa_render_order_admin_footer(): void {
    ?>
    <style>
    #woocommerce-order-notes,
    .woocommerce-order-notes {
        display: none !important;
    }
    .gastronom-inline-weight-box {
        margin: 16px 0 8px;
        padding: 18px 20px;
        border: 1px solid #dcdcde;
        border-radius: 10px;
        background: #fff;
    }
    .gastronom-inline-weight-box h3 {
        margin: 0 0 14px;
        font-size: 15px;
        line-height: 1.4;
    }
    .gastronom-weight-item {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 220px;
        gap: 16px;
        align-items: end;
        margin: 0 0 12px;
        padding: 14px 16px;
        border: 1px solid #dcdcde;
        border-radius: 10px;
        background: #f9fafb;
    }
    .gastronom-weight-item__title {
        font-weight: 600;
        line-height: 1.35;
        margin-bottom: 6px;
    }
    .gastronom-weight-item__meta,
    .gastronom-weight-item__status,
    .gastronom-weight-box__hint {
        color: #50575e;
        font-size: 13px;
        line-height: 1.45;
    }
    .gastronom-weight-item__status {
        margin-top: 8px;
    }
    .gastronom-weight-item__confirmed {
        margin-top: 6px;
        color: #067c36;
        font-size: 13px;
        font-weight: 600;
    }
    .gastronom-weight-item__field {
        display: flex;
        flex-direction: column;
        gap: 6px;
        width: 100%;
    }
    .gastronom-weight-item__field-label {
        font-size: 12px;
        font-weight: 600;
        color: #344054;
    }
    .gastronom-weight-item__field input {
        width: 100%;
        min-width: 0;
    }
    .gastronom-weight-box__actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px solid #dcdcde;
    }
    .gastronom-weight-box__hint {
        max-width: 70%;
    }
    @media (max-width: 960px) {
        .gastronom-weight-item {
            grid-template-columns: 1fr;
        }
        .gastronom-weight-box__actions {
            flex-direction: column;
            align-items: flex-start;
        }
        .gastronom-weight-box__hint {
            max-width: none;
        }
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
}

function rpa_handle_weight_confirmation_ajax(): void {
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
}

function rpa_admin_footer_hook(): void {
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    $screen_id = $screen && isset($screen->id) ? (string) $screen->id : '';
    if (!in_array($screen_id, rpa_order_screen_ids(), true)) {
        return;
    }

    rpa_render_order_admin_footer();
}

function rpa_remove_hidden_meta_boxes(): void {
    foreach (rpa_order_screen_ids() as $screen_id) {
        foreach (rpa_hidden_meta_box_ids() as $box_id) {
            remove_meta_box($box_id, $screen_id, 'side');
        }
    }
}

function rpa_render_inline_weight_panel($order): void {
    if (!gastronom_order_requires_weight_confirmation($order)) {
        return;
    }

    echo '<div class="gastronom-inline-weight-box">';
    echo '<h3>Подтверждение фактического веса</h3>';
    rpa_render_weight_confirmation_box($order);
    echo '</div>';
}

if (!function_exists('gastronom_render_product_preorder_fields')) {
    function gastronom_render_product_preorder_fields(): void {
        rpa_render_product_preorder_fields();
    }
}

if (!function_exists('gastronom_save_product_preorder_fields')) {
    function gastronom_save_product_preorder_fields($post_id): void {
        rpa_save_product_preorder_fields($post_id);
    }
}

if (!function_exists('gastronom_render_weight_confirmation_box')) {
    function gastronom_render_weight_confirmation_box($order_or_post): void {
        rpa_render_weight_confirmation_box($order_or_post);
    }
}

if (!function_exists('gastronom_order_screen_ids')) {
    function gastronom_order_screen_ids(): array {
        return rpa_order_screen_ids();
    }
}

if (!function_exists('gastronom_render_order_admin_footer')) {
    function gastronom_render_order_admin_footer(): void {
        rpa_render_order_admin_footer();
    }
}

if (!function_exists('gastronom_admin_footer_hook')) {
    function gastronom_admin_footer_hook(): void {
        rpa_admin_footer_hook();
    }
}

if (!function_exists('gastronom_remove_hidden_meta_boxes')) {
    function gastronom_remove_hidden_meta_boxes(): void {
        rpa_remove_hidden_meta_boxes();
    }
}

if (!function_exists('gastronom_render_inline_weight_panel')) {
    function gastronom_render_inline_weight_panel($order): void {
        rpa_render_inline_weight_panel($order);
    }
}

if (!function_exists('gastronom_handle_weight_confirmation_ajax')) {
    function gastronom_handle_weight_confirmation_ajax(): void {
        rpa_handle_weight_confirmation_ajax();
    }
}
