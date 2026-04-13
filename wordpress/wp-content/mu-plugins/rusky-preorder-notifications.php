<?php
/**
 * Plugin Name: Rusky Preorder Notifications
 * Description: Extraction-ready preorder notification and transition helpers.
 *
 * Scaffold status:
 * - local hardening surface only
 * - no live hook registration yet
 * - uses `rpn_*` names to avoid collisions with current `gastronom_*` ownership
 */

if (!defined('ABSPATH')) {
    exit;
}

function rpn_send_preorder_created_emails($order): void {
    if (is_numeric($order)) {
        $order = wc_get_order($order);
    }
    if (!$order instanceof WC_Order) {
        return;
    }
    if (!function_exists('gastronom_order_requires_weight_confirmation') || !gastronom_order_requires_weight_confirmation($order)) {
        return;
    }
    if ($order->get_meta('_gastronom_preorder_created_emails_sent', true) === 'yes') {
        return;
    }

    $mailer = WC()->mailer();
    $emails = $mailer ? $mailer->get_emails() : [];

    gastronom_with_order_locale($order, static function() use ($emails, $order) {
        foreach ($emails as $email) {
            if ($email instanceof WC_Email_New_Order) {
                $email->trigger($order->get_id(), $order);
            }
            if ($email instanceof WC_Email_Customer_On_Hold_Order) {
                $email->trigger($order->get_id(), $order);
            }
        }
    });

    $order->update_meta_data('_gastronom_preorder_created_emails_sent', 'yes');
    $order->update_meta_data('_gastronom_preorder_created_emails_sent_at', current_time('mysql'));
    $order->save();
}

function rpn_send_weight_confirmation_email($order): void {
    if (is_numeric($order)) {
        $order = wc_get_order($order);
    }
    if (!$order instanceof WC_Order) {
        return;
    }
    if ($order->get_meta('_gastronom_weight_confirmation_email_sent_at', true) !== '') {
        return;
    }

    $to = sanitize_email((string) $order->get_billing_email());
    if ($to === '') {
        return;
    }

    $lang = gastronom_order_lang($order);

    [$subject, $message] = gastronom_with_order_locale($order, static function() use ($order, $lang) {
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
        $view_url = add_query_arg('lang', $lang, $order->get_view_order_url());
        $pay_label = $lang === 'ru' ? 'Оплатить заказ' : 'Zaplatiť objednávku';
        $payment_html = '';

        if ($payment_method === 'cod') {
            $payment_html = '<p>' . esc_html($lang === 'ru'
                ? 'Вы выбрали оплату при получении. Дополнительно оплачивать заказ сейчас не нужно.'
                : 'Zvolili ste platbu pri prevzatí. Objednávku teraz nemusíte dodatočne platiť.') . '</p>';
        } else {
            $pay_url = add_query_arg('lang', $lang, $order->get_checkout_payment_url());
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

        return [$subject, $message];
    });

    $sent = gastronom_with_order_locale($order, static function() use ($to, $subject, $message) {
        return wc_mail($to, $subject, $message);
    });
    if ($sent) {
        $order->update_meta_data('_gastronom_weight_confirmation_email_sent_at', current_time('mysql'));
        $order->update_meta_data('_gastronom_weight_confirmation_email_to', $to);
        $order->save();
    }
}

function rpn_mark_weight_confirmed_order_ready($order): void {
    if (is_numeric($order)) {
        $order = wc_get_order($order);
    }
    if (!$order instanceof WC_Order) {
        return;
    }
    if ($order->get_meta('_gastronom_weight_confirmation_processed_at', true) !== '') {
        return;
    }

    gastronom_normalize_preorder_order_state($order);

    if (gastronom_order_requires_weight_confirmation($order)) {
        return;
    }

    $order->update_meta_data('_gastronom_requires_weight_confirmation', 'no');
    $order->update_meta_data('_gastronom_weight_confirmation_processed_at', current_time('mysql'));
    $order->save();

    if ($order->get_status() === 'await-weight') {
        $next_status = $order->get_payment_method() === 'cod' ? 'on-hold' : 'pending';
        $note = $order->get_payment_method() === 'cod'
            ? gastronom_tt($order, 'Фактический вес подтверждён. Ожидание оплаты при получении.', 'Skutočná hmotnosť bola potvrdená. Čaká sa na platbu pri doručení.')
            : gastronom_tt($order, 'Фактический вес подтверждён. Ожидание онлайн-оплаты.', 'Skutočná hmotnosť bola potvrdená. Čaká sa na online platbu.');
        $order->update_status($next_status, $note, true);
    }

    rpn_send_weight_confirmation_email($order);
}

if (!function_exists('gastronom_send_preorder_created_emails')) {
    function gastronom_send_preorder_created_emails($order): void {
        rpn_send_preorder_created_emails($order);
    }
}

if (!function_exists('gastronom_send_weight_confirmation_email')) {
    function gastronom_send_weight_confirmation_email($order): void {
        rpn_send_weight_confirmation_email($order);
    }
}

if (!function_exists('gastronom_mark_weight_confirmed_order_ready')) {
    function gastronom_mark_weight_confirmed_order_ready($order): void {
        rpn_mark_weight_confirmed_order_ready($order);
    }
}
