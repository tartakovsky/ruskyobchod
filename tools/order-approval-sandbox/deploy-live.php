<?php
/** Explicit CLI release; secrets and the database backup stay outside webroot. */
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { exit(1); }
$mode = $argv[1] ?? 'inspect';
$root = '/home/u595644545/domains/ruskyobchod.sk/public_html';
$release = '/tmp/gor-release-20260914';
$backup = '/home/u595644545/backups/order-review-20260914';
$_SERVER['HTTP_HOST'] = 'gastronom-bratislava.sk';
$_SERVER['HTTPS'] = 'on';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/';
define('DISABLE_WP_CRON', true);
require $root . '/wp-load.php';
global $wpdb;
if (wp_get_environment_type() !== 'production' || $wpdb->prefix !== 'wp_' || home_url() !== 'https://gastronom-bratislava.sk') {
    throw new RuntimeException('Unexpected live environment');
}
$manifest = json_decode(file_get_contents($release . '/docs/order-review/release-manifest.json'), true, 512, JSON_THROW_ON_ERROR);
$files = [];
foreach ($manifest['files'] as $entry) {
    if (!str_starts_with($entry['path'], 'wordpress/wp-content/') || str_contains($entry['path'], '..')) { throw new RuntimeException('Unexpected file path'); }
    $relative = substr($entry['path'], strlen('wordpress/'));
    $source = $release . '/' . $entry['path'];
    if (!is_file($source) || hash_file('sha256', $source) !== $entry['sha256']) { throw new RuntimeException('Candidate hash mismatch: ' . $relative); }
    if (str_ends_with($source, '.php')) { token_get_all(file_get_contents($source), TOKEN_PARSE); }
    $files[] = $entry + ['relative' => $relative, 'source' => $source, 'destination' => $root . '/' . $relative];
}
function atomic_release_write(string $path, string $content): void {
    if (!is_dir(dirname($path)) && !mkdir(dirname($path), 0755, true)) { throw new RuntimeException('Cannot create destination'); }
    $temp = $path . '.gor-release-tmp';
    if (file_put_contents($temp, $content) === false) { throw new RuntimeException('Cannot write candidate'); }
    chmod($temp, is_file($path) ? (fileperms($path) & 0777) : 0644);
    if (!rename($temp, $path)) { throw new RuntimeException('Cannot publish file'); }
    if (function_exists('opcache_invalidate')) { opcache_invalidate($path, true); }
}
function assert_release_baseline(array $files): void {
    foreach ($files as $entry) {
        $actual = is_file($entry['destination']) ? hash_file('sha256', $entry['destination']) : null;
        if ($actual !== $entry['baseline_sha256']) { throw new RuntimeException('Live drift: ' . $entry['relative']); }
    }
}
if ($mode === 'backup') {
    assert_release_baseline($files);
    if (get_option('gor_review_cutoff', null) !== null || function_exists('gor_api')) { throw new RuntimeException('Already enabled'); }
    if (file_exists($backup)) { throw new RuntimeException('Backup directory already exists'); }
    if (!mkdir($backup, 0700, true)) { throw new RuntimeException('Cannot create private backup'); }
    foreach ($files as $entry) {
        if (is_file($entry['destination'])) {
            $dest = $backup . '/files/' . $entry['relative'];
            if (!is_dir(dirname($dest))) { mkdir(dirname($dest), 0700, true); }
            if (!copy($entry['destination'], $dest)) { throw new RuntimeException('File backup failed'); }
        }
    }
    if (!copy($root . '/wp-config.php', $backup . '/wp-config.php')) { throw new RuntimeException('Config backup failed'); }
    chmod($backup . '/wp-config.php', 0600);
    file_put_contents($backup . '/active-plugins.json', json_encode(get_option('active_plugins')));
    copy($release . '/docs/order-review/release-manifest.json', $backup . '/release-manifest.json');
    $quote = static fn($v) => '"' . str_replace(["\\", '"', "\n", "\r"], ["\\\\", '\\"', '\\n', '\\r'], (string)$v) . '"';
    $credentials = $backup . '/dump-client.cnf';
    file_put_contents($credentials, "[client]\nuser=" . $quote(DB_USER) . "\npassword=" . $quote(DB_PASSWORD) . "\nhost=" . $quote(DB_HOST) . "\n");
    chmod($credentials, 0600);
    try {
        $process = proc_open(['/usr/bin/mysqldump', '--defaults-extra-file=' . $credentials, '--single-transaction', '--quick', '--skip-lock-tables', '--no-tablespaces', '--hex-blob', DB_NAME], [0 => ['file','/dev/null','r'], 1 => ['file',$backup . '/database.sql','w'], 2 => ['file',$backup . '/dump-errors.txt','w']], $pipes);
        if (!is_resource($process) || proc_close($process) !== 0 || filesize($backup . '/database.sql') < 1000) { throw new RuntimeException('Database backup failed; private diagnostic retained'); }
        chmod($backup . '/database.sql', 0600);
    } finally { unlink($credentials); }
    $process = proc_open(['/usr/bin/gzip', $backup . '/database.sql'], [0 => ['file','/dev/null','r'], 1 => ['file','/dev/null','w'], 2 => ['file',$backup . '/gzip-errors.txt','w']], $pipes);
    if (!is_resource($process) || proc_close($process) !== 0) { throw new RuntimeException('Backup compression failed'); }
    file_put_contents($backup . '/backup-complete.json', json_encode(['at'=>gmdate('c'),'files'=>count($files),'database_bytes'=>filesize($backup . '/database.sql.gz')]));
    echo file_get_contents($backup . '/backup-complete.json') . "\n";
    exit;
}
if ($mode === 'install') {
    if (!is_file($backup . '/backup-complete.json')) { throw new RuntimeException('Completed backup required'); }
    assert_release_baseline($files);
    if (defined('GASTRONOM_REVIEW_LIVE') || get_option('gor_review_cutoff', null) !== null) { throw new RuntimeException('Unexpected prior configuration'); }
    foreach ($files as $entry) { atomic_release_write($entry['destination'], file_get_contents($entry['source'])); }
    require_once $root . '/wp-admin/includes/plugin.php';
    $result = activate_plugin('gastronom-order-review/gastronom-order-review.php', '', false, true);
    if (is_wp_error($result)) { throw new RuntimeException($result->get_error_message()); }
    if (function_exists('gor_api')) { throw new RuntimeException('Plugin should remain dormant before explicit enable'); }
    file_put_contents($backup . '/installed.json', json_encode(['at'=>gmdate('c'),'dormant'=>true]));
    echo "Installed, dormant\n";
    exit;
}
if ($mode === 'enable') {
    if (!is_file($backup . '/installed.json')) { throw new RuntimeException('Install first'); }
    foreach ($files as $entry) { if (hash_file('sha256', $entry['destination']) !== $entry['sha256']) { throw new RuntimeException('Installed hash mismatch'); } }
    if (defined('GASTRONOM_REVIEW_LIVE') || get_option('gor_review_cutoff', null) !== null) { throw new RuntimeException('Already configured'); }
    $config = file_get_contents($root . '/wp-config.php');
    if (str_contains($config, 'GASTRONOM_REVIEW_') || !str_starts_with($config, '<?php')) { throw new RuntimeException('Unexpected config'); }
    $cutoff = gmdate('Y-m-d H:i:s', time() + 120);
    $config = "<?php\n// Gastronom order review: existing orders retain their previous workflow.\ndefine('GASTRONOM_REVIEW_LIVE', true);\ndefine('GASTRONOM_REVIEW_FROM_UTC', '" . $cutoff . "');\n" . substr($config, 5);
    token_get_all($config, TOKEN_PARSE);
    // Save the ownership boundary before enabling execution; it is still two minutes in the future.
    if (!add_option('gor_review_cutoff', $cutoff, '', false)) { throw new RuntimeException('Cannot save cutoff'); }
    atomic_release_write($root . '/wp-config.php', $config);
    if (function_exists('wp_cache_clear_cache')) { wp_cache_clear_cache(); }
    file_put_contents($backup . '/enabled.json', json_encode(['at'=>gmdate('c'),'cutoff_utc'=>$cutoff]));
    echo file_get_contents($backup . '/enabled.json') . "\n";
    exit;
}
if ($mode !== 'verify') { throw new RuntimeException('Expected backup/install/enable/verify'); }
if (!function_exists('gor_api') || gor_is_sandbox() || !GASTRONOM_REVIEW_LIVE) { throw new RuntimeException('Live runtime not enabled'); }
$cutoff = (string)get_option('gor_review_cutoff');
$old = new WC_Order(); $old->set_date_created(strtotime($cutoff . ' UTC') - 1);
$new = new WC_Order(); $new->set_date_created(strtotime($cutoff . ' UTC') + 1);
if (gor_managed($old) || !gor_managed($new)) { throw new RuntimeException('Cutoff ownership failed'); }
if (apply_filters('woocommerce_can_reduce_order_stock', true, $new) !== false || apply_filters('woocommerce_can_reduce_order_stock', true, $old) !== true) { throw new RuntimeException('Stock guard failed'); }
if (gor_order_language($new) !== 'sk') { throw new RuntimeException('Language fallback failed'); }
$new->update_meta_data('_gastronom_lang', 'ru');
if (gor_order_language($new) !== 'ru') { throw new RuntimeException('Explicit Russian failed'); }
foreach ($files as $entry) { if (hash_file('sha256', $entry['destination']) !== $entry['sha256']) { throw new RuntimeException('Live file hash mismatch'); } }
echo json_encode(['live'=>true,'sandbox'=>false,'cutoff_utc'=>$cutoff,'files'=>count($files),'old_orders_preserved'=>true,'new_stock_guard'=>true,'language_default_sk'=>true,'test_orders_created'=>0,'test_emails_sent'=>0,'test_receipts_created'=>0], JSON_PRETTY_PRINT) . "\n";
