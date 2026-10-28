<?php
/**
 * Plugin Name: Rusky Turnstile Private Configuration
 * Description: Load production-only Turnstile keys from outside the public web root.
 */

defined('ABSPATH') || exit;

// Do not activate production CAPTCHA credentials on staging or legacy domains.
if (strtolower((string) wp_parse_url(get_option('home'), PHP_URL_HOST)) !== 'gastronom-bratislava.sk') {
    return;
}

$rusky_turnstile_private = dirname(ABSPATH, 3) . '/.gastronom-turnstile-private.php';
if (is_readable($rusky_turnstile_private)) {
    require_once $rusky_turnstile_private;
}
unset($rusky_turnstile_private);
