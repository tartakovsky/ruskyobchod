## active plugins capture tool added

### scope

Add a read-only tool to capture the current live `active_plugins` option without rebuilding ad hoc probe scripts.

File added:

- `tools/capture-live-active-plugins.sh`

### current snapshot

The live active plugin set currently includes:

- `gastronom-lang-switcher/gastronom-lang-switcher.php`
- `gastronom-stock-fix/gastronom-stock-fix.php`
- `woocommerce-extension-master/dotypos.php`
- `woocommerce-payments/woocommerce-payments.php`
- `woocommerce/woocommerce.php`
- `packeta/packeta.php`
- `gls-shipping-for-woocommerce/gls-shipping-for-woocommerce.php`

Important negative signal:

- `real-time-find-and-replace/real-time-find-and-replace.php` is not active

### value

This supports the deferred `runtime-shim` decision with a stable read-only tool instead of one-off probes.
