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

function rrg_session(): string {
    $value = $_COOKIE['rrg_session'] ?? '';
    return is_string($value) && preg_match('/\A[a-f0-9]{64}\z/D', $value) ? $value : '';
}

function rrg_prepare_session(): void {
    if (is_user_logged_in() || !is_account_page()) {
        return;
    }
    nocache_headers();
    if (!defined('DONOTCACHEPAGE')) {
        define('DONOTCACHEPAGE', true);
    }
    if (rrg_session() !== '' || headers_sent()) {
        return;
    }
    $session = bin2hex(random_bytes(32));
    if (setcookie('rrg_session', $session, [
        'expires' => 0, 'path' => '/', 'secure' => is_ssl(),
        'httponly' => true, 'samesite' => 'Lax',
    ])) {
        $_COOKIE['rrg_session'] = $session;
    }
}
add_action('template_redirect', 'rrg_prepare_session', 1);

function rrg_signature(string $issued, string $token): string {
    return wp_hash('rrg:v2:' . $issued . ':' . $token . ':' . rrg_session());
}

function rrg_abuse_email(string $email): bool {
    // Exact campaign marker seen in two unsolicited customer accounts, not all plus aliases.
    return (bool) preg_match('/(?:^|[+.@])webonlinepostmail\.de(?:@|$)/i', trim($email));
}

function rrg_customer_errors($errors, $username = '', $email = '') {
    if (is_string($email) && rrg_abuse_email($email)) {
        $errors->add('rrg_verification', rrg_message());
    }
    return $errors;
}
// Also runs for customer creation outside the standalone form, before insert/mail.
add_filter('woocommerce_registration_errors', 'rrg_customer_errors', 10, 3);

function rrg_schedule_cleanup(): void {
    if (!wp_next_scheduled('rrg_cleanup_tokens')) {
        wp_schedule_event(time() + HOUR_IN_SECONDS, 'hourly', 'rrg_cleanup_tokens');
    }
}
add_action('init', 'rrg_schedule_cleanup');

function rrg_cleanup_tokens(): void {
    global $wpdb;
    // Atomic replay claims are non-autoloaded and kept only for the token validity window.
    $wpdb->query($wpdb->prepare(
        "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s AND CAST(option_value AS UNSIGNED) < %d LIMIT 1000",
        $wpdb->esc_like('rrg_used_') . '%', time()
    ));
}
add_action('rrg_cleanup_tokens', 'rrg_cleanup_tokens');

function rrg_form(): void {
    $issued = (string) time();
    $token = bin2hex(random_bytes(16));
    echo '<div style="position:absolute;left:-10000px;width:1px;height:1px;overflow:hidden" aria-hidden="true"><label>Leave empty<input type="text" name="rrg_website" value="" tabindex="-1" autocomplete="off"></label></div>';
    echo '<input type="hidden" name="rrg_issued" value="' . esc_attr($issued) . '">';
    echo '<input type="hidden" name="rrg_signature" value="' . esc_attr(rrg_signature($issued, $token)) . '">';
    echo '<input type="hidden" name="rrg_token" value="' . esc_attr($token) . '">';
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
    if (rrg_abuse_email(is_string($email) ? $email : '')) {
        $errors->add('rrg_verification', rrg_message());
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
    $token = rrg_post('rrg_token');
    $valid = $state['attempts'] <= 2
        && rrg_session() !== ''
        && preg_match('/\A[a-f0-9]{32}\z/D', $token)
        && rrg_post('rrg_website') === ''
        && ctype_digit($issued)
        && hash_equals(rrg_signature($issued, $token), rrg_post('rrg_signature'))
        && $now - (int) $issued >= 3
        && $now - (int) $issued <= 2 * HOUR_IN_SECONDS;
    if (!$valid) {
        $errors->add('rrg_verification', rrg_message());
        return $errors;
    }
    [$site, $secret] = rrg_keys();
    if ($site !== '' && $secret !== '') {
        $cf_token = rrg_post('cf-turnstile-response');
        if ($cf_token === '' || strlen($cf_token) > 2048) {
            $errors->add('rrg_verification', rrg_message());
            return $errors;
        }
        $response = wp_remote_post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'timeout' => 10,
            'body' => ['secret' => $secret, 'response' => $cf_token, 'remoteip' => $ip],
        ]);
        $result = is_wp_error($response) ? [] : json_decode(wp_remote_retrieve_body($response), true);
        $hostname = strtolower((string) wp_parse_url(home_url(), PHP_URL_HOST));
        if (empty($result['success']) || ($result['action'] ?? '') !== 'registration'
            || strtolower((string) ($result['hostname'] ?? '')) !== $hostname) {
            $errors->add('rrg_verification', rrg_message());
            return $errors;
        }
    }
    if (!add_option('rrg_used_' . hash('sha256', $token), (int) $issued + 2 * HOUR_IN_SECONDS, '', false)) {
        $errors->add('rrg_verification', rrg_message());
    }
    return $errors;
}
add_filter('woocommerce_process_registration_errors', 'rrg_validate', 10, 4);
