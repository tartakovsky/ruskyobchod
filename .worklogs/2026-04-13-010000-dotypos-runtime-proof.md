## Dotypos Runtime Proof

Date: 2026-04-13

### Scope

Read-only runtime proof after owner-file parity.

Goal:

- prove that the new owner files are loaded on live
- prove that the compatibility wrapper surface is available
- prove that critical preorder/Dotypos hooks are registered

### Method

Loaded `wp-load.php` on the live server through a temporary CLI script and checked:

- `function_exists('rdsb_apply_dotypos_stock_to_preorder_product')`
- `function_exists('rwp_order_requires_confirmation')`
- `function_exists('gastronom_apply_dotypos_stock_to_wc_product')`
- `function_exists('gastronom_sync_confirmed_preorder_items_to_dotypos')`
- `function_exists('gastronom_prepare_checkout_processed_preorder')`
- `has_action('woocommerce_checkout_order_processed', 'gastronom_prepare_checkout_processed_preorder')`
- `has_action('woocommerce_order_status_cancelled', 'gastronom_restore_confirmed_preorder_items_to_dotypos')`
- `has_action('woocommerce_order_status_refunded', 'gastronom_restore_confirmed_preorder_items_to_dotypos')`
- `has_action('wp_ajax_gastronom_confirm_weight', 'gastronom_handle_weight_confirmation_ajax')`
- `has_filter('wc_order_statuses', 'gastronom_inject_await_weight_status')`

### Runtime proof result

Confirmed true or active priority values on live:

- `rdsb_apply_dotypos_stock_to_preorder_product=true`
- `rwp_order_requires_confirmation=true`
- `gastronom_apply_dotypos_stock_to_wc_product=true`
- `gastronom_sync_confirmed_preorder_items_to_dotypos=true`
- `gastronom_prepare_checkout_processed_preorder=true`
- `checkout_processed_hook=20`
- `cancelled_restore_hook=20`
- `refunded_restore_hook=20`
- `ajax_confirm_weight_hook=10`
- `await_weight_status_hook=10`

### Interpretation

This proves:

- the owner-file parity rollout is active at runtime, not just on disk
- the current wrapper/hook path is live and callable
- the next phase can move from parity into business-flow proof

This does not yet prove:

- successful preorder checkout mutation on a real order
- successful actual-weight confirmation end-to-end
- successful Dotypos stock mutation against a real product/order pair
