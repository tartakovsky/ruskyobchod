<?php
/**
 * Plugin Name: Gastronom Order Review
 * Description: Explicit order approval and versioned customer corrections. Explicitly enabled release or isolated sandbox.
 * Version: 0.3.0
 * Requires at least: 6.9
 * Requires PHP: 8.1
 * Requires Plugins: woocommerce
 */
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }
require_once __DIR__ . '/src/Review.php';
require_once __DIR__ . '/src/Mail.php';
require_once __DIR__ . '/src/Consent.php';
require_once __DIR__ . '/src/Outbox.php';
require_once __DIR__ . '/src/Fulfillment.php';
require_once __DIR__ . '/src/Executor.php';

// No configuration is installed by this review build. Production requires an explicit release flag.
$sandbox=defined('GASTRONOM_REVIEW_SANDBOX') && GASTRONOM_REVIEW_SANDBOX === true
    && function_exists('wp_get_environment_type') && in_array(wp_get_environment_type(), ['local','staging'], true);
$live=function_exists('wp_get_environment_type') && wp_get_environment_type()==='production'
    && defined('GASTRONOM_REVIEW_LIVE') && GASTRONOM_REVIEW_LIVE === true
    && !(defined('GASTRONOM_REVIEW_SANDBOX') && GASTRONOM_REVIEW_SANDBOX === true);
if (!$sandbox && !$live) { return; }
if (!defined('GASTRONOM_REVIEW_FROM_UTC') || strtotime(GASTRONOM_REVIEW_FROM_UTC.' UTC') === false) { return; }
function gor_is_sandbox(): bool { return defined('GASTRONOM_REVIEW_SANDBOX') && GASTRONOM_REVIEW_SANDBOX === true; }
require_once __DIR__ . '/src/DotyposTransport.php';
require_once __DIR__ . '/src/LiveAdapter.php';
add_action('before_woocommerce_init', static function (): void {
    if (class_exists('Automattic\\WooCommerce\\Utilities\\FeaturesUtil')) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});
require_once __DIR__ . '/src/ExecutionAdapter.php';
require_once __DIR__ . '/src/WordPress.php';
require_once __DIR__ . '/src/Carriers.php';

require_once __DIR__ . '/src/StockHolds.php';

require_once __DIR__ . '/src/Operations.php';
