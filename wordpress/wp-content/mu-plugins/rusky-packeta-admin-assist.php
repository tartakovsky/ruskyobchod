<?php
/**
 * Plugin Name: Rusky Packeta Admin Assist
 * Description: Adds a focused save control and validation hints to the Packeta order metabox.
 */

if (!defined('ABSPATH')) {
    exit;
}

function rpaa_is_order_edit_screen(): bool {
    if (!is_admin() || !function_exists('get_current_screen')) {
        return false;
    }

    $screen = get_current_screen();
    if (!$screen) {
        return false;
    }

    return in_array($screen->id, ['shop_order', 'woocommerce_page_wc-orders'], true);
}

function rpaa_render_packeta_admin_assist(): void {
    if (!rpaa_is_order_edit_screen() || !current_user_can('edit_shop_orders')) {
        return;
    }

    $order_id = 0;
    if (isset($_GET['post'])) {
        $order_id = (int) wp_unslash($_GET['post']);
    } elseif (isset($_GET['id'])) {
        $order_id = (int) wp_unslash($_GET['id']);
    }

    $packetery_order = null;
    if ($order_id > 0) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'packetery_order';
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Admin-only diagnostic read for the current order.
        $packetery_order = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT packet_id, point_id, point_name, point_city, point_zip, point_street, point_place, point_url, carrier_id, weight FROM {$table_name} WHERE id = %d",
                $order_id
            ),
            ARRAY_A
        );
    }

    $packetery_options = get_option('packetery', []);
    $has_sender = is_array($packetery_options) && !empty($packetery_options['sender']);
    $saved_data = is_array($packetery_order) ? $packetery_order : [];
    ?>
    <style>
        #packetery_metabox .rpaa-packeta-assist {
            border-left: 4px solid #2271b1;
            background: #f0f6fc;
            margin: 12px 0 0;
            padding: 10px 12px;
        }

        #packetery_metabox .rpaa-packeta-assist p {
            margin: 0 0 8px;
        }

        #packetery_metabox .rpaa-packeta-assist ul {
            margin: 6px 0 0 18px;
        }

        #packetery_metabox .rpaa-packeta-warning {
            color: #8a4b00;
        }
    </style>
    <script>
        (function() {
            function ready(callback) {
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', callback);
                    return;
                }
                callback();
            }

            function numberValue(input) {
                if (!input) {
                    return 0;
                }
                return parseFloat(String(input.value || '').replace(',', '.')) || 0;
            }

            function installAssist() {
                var metabox = document.getElementById('packetery_metabox');
                var form = document.getElementById('post');
                if (!metabox || !form || metabox.querySelector('.rpaa-packeta-assist')) {
                    return;
                }

                var container = metabox.querySelector('[data-packetery-order-metabox]') || metabox.querySelector('.inside');
                if (!container) {
                    return;
                }

                var savedData = <?php echo wp_json_encode($saved_data); ?> || {};
                var hasSender = <?php echo $has_sender ? 'true' : 'false'; ?>;
                if (savedData.packet_id) {
                    return;
                }

                var panel = document.createElement('div');
                panel.className = 'rpaa-packeta-assist';

                var message = document.createElement('p');
                var list = document.createElement('ul');

                panel.appendChild(message);
                panel.appendChild(list);
                container.appendChild(panel);

                function setValue(name, value) {
                    var input = form.querySelector('[name="' + name + '"]');
                    if (!input || input.value || value === undefined || value === null) {
                        return;
                    }
                    input.value = value;
                }

                setValue('packetery_point_id', savedData.point_id);
                setValue('packetery_point_name', savedData.point_name);
                setValue('packetery_point_city', savedData.point_city);
                setValue('packetery_point_zip', savedData.point_zip);
                setValue('packetery_point_street', savedData.point_street);
                setValue('packetery_point_place', savedData.point_place);
                setValue('packetery_carrier_id', savedData.carrier_id);
                setValue('packetery_point_url', savedData.point_url);

                function refresh() {
                    var weight = form.querySelector('[name="packeteryWeight"]');
                    var pointId = form.querySelector('[name="packetery_point_id"]');
                    var issues = [];

                    if (numberValue(weight) <= 0) {
                        issues.push('укажите вес больше 0 кг');
                    }
                    if (!pointId || !String(pointId.value || '').trim()) {
                        issues.push('выберите пункт выдачи через Choose pickup point');
                    }
                    if (!hasSender) {
                        issues.push('в настройках Packeta не задан Sender');
                    }

                    list.innerHTML = '';
                    if (issues.length) {
                        message.innerHTML = '<strong class="rpaa-packeta-warning">Packeta ещё не готова к отправке:</strong>';
                        issues.forEach(function(issue) {
                            var item = document.createElement('li');
                            item.textContent = issue;
                            list.appendChild(item);
                        });
                    } else {
                        message.innerHTML = '<strong>Данные заказа Packeta выглядят заполненными.</strong>';
                    }
                }

                metabox.addEventListener('input', refresh);
                metabox.addEventListener('change', refresh);

                refresh();
            }

            ready(installAssist);
        })();
    </script>
    <?php
}
add_action('admin_footer', 'rpaa_render_packeta_admin_assist', 30);
