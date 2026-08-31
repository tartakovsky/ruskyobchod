<?php
/**
 * Plugin Name: Rusky staging safety guard
 * Description: Blocks mail and off-site WordPress HTTP requests on an explicitly configured staging site.
 */

declare(strict_types=1);

if (!defined('RUSKY_STAGING_MODE') || RUSKY_STAGING_MODE !== true) {
    return;
}

function rssg_block_mail($preempt, array $args)
{
    return true;
}

add_filter('pre_wp_mail', 'rssg_block_mail', PHP_INT_MAX, 2);

function rssg_block_external_http($preempt, array $args, string $url)
{
    $host = wp_parse_url($url, PHP_URL_HOST);
    $site_host = wp_parse_url(home_url('/'), PHP_URL_HOST);

    if (is_string($host) && in_array($host, [$site_host, 'localhost', '127.0.0.1', '::1'], true)) {
        return $preempt;
    }

    return new WP_Error(
        'rusky_staging_external_http_blocked',
        'The staging safety guard blocks external HTTP requests.'
    );
}

add_filter('pre_http_request', 'rssg_block_external_http', PHP_INT_MAX, 3);
