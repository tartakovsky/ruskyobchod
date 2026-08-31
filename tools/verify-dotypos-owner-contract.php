<?php
declare(strict_types=1);

$_SERVER['HTTP_HOST'] = 'ruskyobchod.sk';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/?verify_dotypos_owner_contract=1';

require $argv[1] . '/wp-load.php';

function contract_fail(string $message): void {
    fwrite(STDERR, "FAIL {$message}\n");
    exit(1);
}

function contract_ok(string $message): void {
    echo "OK   {$message}\n";
}

function contract_function_owner(string $function, string $expected_suffix): void {
    if (!function_exists($function)) {
        contract_fail("{$function} is not loaded");
    }

    $file = (new ReflectionFunction($function))->getFileName();
    if (!is_string($file) || !str_ends_with($file, $expected_suffix)) {
        contract_fail("{$function} owner is not {$expected_suffix}");
    }

    contract_ok("{$function} is owned by {$expected_suffix}");
}

function contract_hook_has(string $hook, int $priority, string $function): void {
    global $wp_filter;

    $callbacks = $wp_filter[$hook]->callbacks[$priority] ?? [];
    foreach ($callbacks as $callback) {
        if (($callback['function'] ?? null) === $function) {
            contract_ok("{$hook} priority {$priority} has {$function}");
            return;
        }
    }

    contract_fail("{$hook} priority {$priority} is missing {$function}");
}

function contract_hook_lacks(string $hook, int $priority, string $function): void {
    global $wp_filter;

    $callbacks = $wp_filter[$hook]->callbacks[$priority] ?? [];
    foreach ($callbacks as $callback) {
        if (($callback['function'] ?? null) === $function) {
            contract_fail("{$hook} priority {$priority} still has {$function}");
        }
    }

    contract_ok("{$hook} priority {$priority} does not have {$function}");
}

contract_function_owner(
    'gastronom_apply_dotypos_stock_to_wc_product',
    '/wp-content/mu-plugins/rusky-dotypos-stock-bridge.php'
);
contract_function_owner(
    'gastronom_resolve_dotypos_order_sync_quantity',
    '/wp-content/mu-plugins/rusky-dotypos-stock-bridge.php'
);
contract_function_owner(
    'rdf_fiscalize_order',
    '/wp-content/mu-plugins/rusky-dotypos-fiscalization.php'
);

contract_hook_has('woocommerce_reduce_order_stock', 5, 'rdf_fiscalize_when_stock_reduces');
contract_hook_has('woocommerce_reduce_order_stock', 10, 'rdf_stock_fallback');
contract_hook_lacks('woocommerce_reduce_order_stock', 10, 'rdsb_sync_order_sale_to_dotypos');
contract_hook_has('woocommerce_reduce_order_stock', 20, 'rdf_schedule_quarter_cod_fiscalization');
contract_hook_has('woocommerce_restore_order_stock', 10, 'rdsb_restore_order_stock_to_dotypos');

echo "Dotypos owner contract verification complete.\n";
