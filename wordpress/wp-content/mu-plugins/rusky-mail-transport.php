<?php
/**
 * Plugin Name: Gastronom Authenticated Mail
 * Description: One explicitly configured SMTP transport and sender for shop mail.
 */
declare(strict_types=1);
if (!defined('ABSPATH')) { return; }

/** Secrets live only in a private server file, never in options or source control. */
function rmt_config(): array {
    if (!defined('GASTRONOM_SMTP_CONFIG') || !is_string(GASTRONOM_SMTP_CONFIG)
        || !is_file(GASTRONOM_SMTP_CONFIG) || !is_readable(GASTRONOM_SMTP_CONFIG)) {
        throw new RuntimeException('Private SMTP configuration is unavailable.');
    }
    $path = realpath(GASTRONOM_SMTP_CONFIG);
    $public = realpath(ABSPATH);
    if ($path === false || ($public !== false && str_starts_with($path, rtrim($public, '/') . '/'))
        || (fileperms($path) & 0077) !== 0) {
        throw new RuntimeException('SMTP configuration must be private and outside the web root.');
    }
    $config = require $path;
    if (!is_array($config)) { throw new RuntimeException('Invalid SMTP configuration.'); }
    foreach (['host','username','password','from','name','encryption'] as $key) {
        if (!isset($config[$key]) || !is_string($config[$key]) || $config[$key] === '') {
            throw new RuntimeException('Incomplete SMTP configuration.');
        }
    }
    foreach (['host','username','from','name','encryption'] as $key) {
        if (strpbrk($config[$key], "\r\n\0") !== false) { throw new RuntimeException('Invalid SMTP header or host.'); }
    }
    if (!preg_match('/^(?=.{1,253}$)[a-z0-9]+(?:[a-z0-9.-]*[a-z0-9])?$/iD', $config['host'])
        || !filter_var($config['from'], FILTER_VALIDATE_EMAIL)
        || !filter_var($config['username'], FILTER_VALIDATE_EMAIL)
        || strcasecmp($config['username'], $config['from']) !== 0
        || !in_array([$config['port'] ?? null, $config['encryption']], [[465,'ssl'],[587,'tls']], true)) {
        throw new RuntimeException('Invalid SMTP server, sender or TLS configuration.');
    }
    return $config;
}

function rmt_apply($mailer, array $config): void {
    $mailer->isSMTP();
    $mailer->Host = $config['host'];
    $mailer->Port = $config['port'];
    $mailer->SMTPAuth = true;
    $mailer->Username = $config['username'];
    $mailer->Password = $config['password'];
    $mailer->SMTPSecure = $config['encryption'];
    $mailer->SMTPAutoTLS = $config['encryption'] === 'tls';
    $mailer->SMTPDebug = 0;
    $mailer->Timeout = 15;
    // Match the envelope and visible sender. The provider supplies its DKIM signature.
    $mailer->setFrom($config['from'], $config['name'], false);
    $mailer->Sender = $config['from'];
    // Leave recipient, Reply-To, body, charset and attachments exactly as prepared.
}

// Installation alone is dormant. Enable only after validating the real mailbox.
if (!defined('GASTRONOM_SMTP_CONFIG')) { return; }
try { $rmtConfig = rmt_config(); }
catch (Throwable $e) {
    // A broken active SMTP configuration must not silently send through PHP mail again.
    add_filter('pre_wp_mail', static fn($result) => $result === null ? false : $result, 999);
    return;
}
add_filter('wp_mail_from', static fn($from) => $rmtConfig['from'], 999);
add_filter('wp_mail_from_name', static fn($name) => $rmtConfig['name'], 999);
add_action('phpmailer_init', static function ($mailer) use ($rmtConfig): void { rmt_apply($mailer, $rmtConfig); }, 999);
