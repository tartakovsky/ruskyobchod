<?php
/** Persistent ownership guard: retain this file when pausing or rolling back the review UI. */
defined('ABSPATH') || exit;

function grg_managed($order): bool {
    if (is_numeric($order)) { $order=wc_get_order($order); }
    if (!$order instanceof WC_Order) { return false; }
    if (function_exists('gor_managed')) { return gor_managed($order); }
    if (is_array($order->get_meta('_gor_state', true))) { return true; }
    $cutoff=get_option('gor_review_cutoff', '');
    $created=$order->get_date_created();
    $time=$cutoff!=='' ? strtotime($cutoff.' UTC') : false;
    return $time!==false && $created && $created->getTimestamp()>=$time;
}

foreach (['woocommerce_can_reduce_order_stock','woocommerce_can_restore_order_stock'] as $hook) {
    add_filter($hook, static function ($allow, $order) { return grg_managed($order) ? false : $allow; }, PHP_INT_MAX, 2);
}

foreach (['customer_processing_order','customer_on_hold_order','customer_completed_order'] as $type) {
    add_filter('woocommerce_email_enabled_'.$type, static function ($enabled, $order) {
        return !function_exists('gor_managed') && grg_managed($order) ? false : $enabled;
    }, PHP_INT_MAX, 2);
}
