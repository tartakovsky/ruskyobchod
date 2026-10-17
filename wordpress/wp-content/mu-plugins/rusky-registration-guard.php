<?php
/**
 * Plugin Name: Rusky Registration Guard
 * Description: Protect standalone WooCommerce registration before customer creation or mail.
 */

defined('ABSPATH') || exit;

function rrg_keys(): array {
    return [
        defined('RUSKY_TURNSTILE_SITE_KEY') ? RUSKY_TURNSTILE_SITE_KEY : '',
        defined('RUSKY_TURNSTILE_SECRET_KEY') ? RUSKY_TURNSTILE_SECRET_KEY : '',
    ];
}

function rrg_message(): string {
    return isset($_GET['lang']) && $_GET['lang'] === 'ru'
        ? 'Не удалось подтвердить регистрацию. Обновите страницу и попробуйте позже.'
        : 'Registráciu sa nepodarilo overiť. Obnovte stránku a skúste to neskôr.';
}

function rrg_form(): void {
    $issued = (string) time();
    echo '<div style="position:absolute;left:-10000px;width:1px;height:1px;overflow:hidden" aria-hidden="true"><label>Leave empty<input type="text" name="rrg_website" value="" tabindex="-1" autocomplete="off"></label></div>';
    echo '<input type="hidden" name="rrg_issued" value="' . esc_attr($issued) . '">';
    echo '<input type="hidden" name="rrg_signature" value="' . esc_attr(wp_hash('rrg:' . $issued)) . '">';
    [$site, $secret] = rrg_keys();
    if ($site !== '' && $secret !== '') {
        wp_enqueue_script('rusky-turnstile', 'https://challenges.cloudflare.com/turnstile/v0/api.js', [], null, true);
        echo '<div class="cf-turnstile" data-sitekey="' . esc_attr($site) . '" data-action="registration"></div>';
    }
}
add_action('woocommerce_register_form', 'rrg_form');

function rrg_post(string $name): string {
    return isset($_POST[$name]) && is_string($_POST[$name]) ? wp_unslash($_POST[$name]) : '';
}

function rrg_validate($errors, $username = '', $password = '', $email = '') {
    // Only the standalone form; checkout and administrator-created customers are separate flows.
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !isset($_POST['register'])) {
        return $errors;
    }
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        $errors->add('rrg_verification', rrg_message());
        return $errors;
    }
    // Trust the connection address, never a client-supplied forwarding header.
    $bucket = 'rrg_' . hash_hmac('sha256', $ip, wp_salt('auth'));
    $state = get_transient($bucket);
    $now = time();
    if (!is_array($state) || ($state['until'] ?? 0) <= $now) {
        $state = ['attempts' => 0, 'until' => $now + 30 * MINUTE_IN_SECONDS];
    }
    $state['attempts']++;
    set_transient($bucket, $state, max(1, $state['until'] - $now));
    $issued = rrg_post('rrg_issued');
    $valid = $state['attempts'] <= 2
        && rrg_post('rrg_website') === ''
        && ctype_digit($issued)
        && hash_equals(wp_hash('rrg:' . $issued), rrg_post('rrg_signature'))
        && $now - (int) $issued >= 3
        && $now - (int) $issued <= 2 * HOUR_IN_SECONDS;
    if (!$valid) {
        $errors->add('rrg_verification', rrg_message());
        return $errors;
    }
    [$site, $secret] = rrg_keys();
    if ($site !== '' && $secret !== '') {
        $token = rrg_post('cf-turnstile-response');
        if ($token === '' || strlen($token) > 2048) {
            $errors->add('rrg_verification', rrg_message());
            return $errors;
        }
        $response = wp_remote_post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'timeout' => 10,
            'body' => ['secret' => $secret, 'response' => $token, 'remoteip' => $ip],
        ]);
        $result = is_wp_error($response) ? [] : json_decode(wp_remote_retrieve_body($response), true);
        $hostname = strtolower((string) wp_parse_url(home_url(), PHP_URL_HOST));
        if (empty($result['success']) || ($result['action'] ?? '') !== 'registration'
            || strtolower((string) ($result['hostname'] ?? '')) !== $hostname) {
            $errors->add('rrg_verification', rrg_message());
        }
    }
    return $errors;
}
add_filter('woocommerce_process_registration_errors', 'rrg_validate', 10, 4);
