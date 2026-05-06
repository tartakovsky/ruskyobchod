<?php
/**
 * Keep the public checkout focused on guest orders.
 *
 * The account/login prompts remain available through "My account", but the
 * checkout itself should not look like registration is required.
 */

if (!defined('ABSPATH')) {
    exit;
}

function rcgf_is_public_checkout(): bool {
    return function_exists('is_checkout')
        && is_checkout()
        && !(function_exists('is_checkout_pay_page') && is_checkout_pay_page())
        && !(function_exists('is_order_received_page') && is_order_received_page());
}

function rcgf_disable_checkout_registration(bool $enabled): bool {
    return rcgf_is_public_checkout() ? false : $enabled;
}

function rcgf_disable_checkout_registration_requirement(bool $required): bool {
    return rcgf_is_public_checkout() ? false : $required;
}

function rcgf_remove_checkout_login_prompt(): void {
    if (!rcgf_is_public_checkout()) {
        return;
    }

    remove_action('woocommerce_before_checkout_form', 'woocommerce_checkout_login_form', 10);
}

add_filter('woocommerce_checkout_registration_enabled', 'rcgf_disable_checkout_registration', 20);
add_filter('woocommerce_checkout_registration_required', 'rcgf_disable_checkout_registration_requirement', 20);
add_action('wp', 'rcgf_remove_checkout_login_prompt', 20);
