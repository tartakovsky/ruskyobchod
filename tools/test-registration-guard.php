<?php
// Standalone security-path checks. No WordPress bootstrap, customer creation or mail.
define('ABSPATH', __DIR__);
define('MINUTE_IN_SECONDS', 60);
define('HOUR_IN_SECONDS', 3600);
$cache = [];
function add_action(...$args) {}
function add_filter(...$args) {}
function wp_hash($s) { return hash_hmac('sha256', $s, 'test'); }
function wp_salt($s) { return 'test'; }
function wp_unslash($s) { return stripslashes($s); }
function get_transient($k) { return $GLOBALS['cache'][$k] ?? false; }
function set_transient($k, $v, $ttl) { $GLOBALS['cache'][$k] = $v; }
function wp_remote_post(...$args) { return $GLOBALS['response']; }
function is_wp_error($v) { return $v instanceof WP_Error; }
function wp_remote_retrieve_body($v) { return json_encode($v); }
function home_url() { return 'https://gastronom-bratislava.sk'; }
function wp_parse_url(...$args) { return parse_url(...$args); }
class WP_Error {
    public $errors = [];
    function add($code, $message) { $this->errors[$code] = $message; }
}
require $argv[1];
function form(): void {
    $GLOBALS['cache'] = [];
    $_SERVER = ['REQUEST_METHOD' => 'POST', 'REMOTE_ADDR' => '192.0.2.1'];
    $_POST = ['register' => '1', 'rrg_website' => '', 'rrg_issued' => (string) (time() - 10)];
    $_POST['rrg_signature'] = wp_hash('rrg:' . $_POST['rrg_issued']);
}
function check($name, $blocked): void {
    $errors = rrg_validate(new WP_Error());
    if ((bool) $errors->errors !== $blocked) { throw new Exception($name); }
    echo "PASS $name\n";
}
form(); check('human form accepted', false);
form(); $_POST['rrg_website'] = 'spam'; check('honeypot rejected', true);
form(); unset($_POST['rrg_signature']); check('missing proof rejected', true);
form(); $_POST['rrg_signature'] = 'tampered'; check('forged proof rejected', true);
form(); $_POST['rrg_issued'] = (string) time(); $_POST['rrg_signature'] = wp_hash('rrg:' . $_POST['rrg_issued']); check('instant submission rejected', true);
form(); $_POST['rrg_issued'] = (string) (time() - 7201); $_POST['rrg_signature'] = wp_hash('rrg:' . $_POST['rrg_issued']); check('expired proof rejected', true);
form(); $_POST['rrg_issued'] = []; check('array input rejected', true);
form(); for ($i = 0; $i < 2; $i++) { check('allowed attempt ' . ($i + 1), false); } check('third attempt rejected', true);
$_SERVER['REMOTE_ADDR'] = '192.0.2.2'; check('different IP unaffected', false);
form(); $_POST = []; check('guest checkout unaffected', false);
define('RUSKY_TURNSTILE_SITE_KEY', 'test'); define('RUSKY_TURNSTILE_SECRET_KEY', 'test');
form(); check('Turnstile missing token rejected', true);
$_POST['cf-turnstile-response'] = 'test'; $response = new WP_Error(); check('Turnstile outage rejected', true);
form(); $_POST['cf-turnstile-response'] = 'test';
$response = ['success' => true, 'action' => 'registration', 'hostname' => 'evil.example']; check('wrong hostname rejected', true);
form(); $_POST['cf-turnstile-response'] = 'test';
$response['hostname'] = 'gastronom-bratislava.sk'; $response['action'] = 'login'; check('wrong action rejected', true);
$response['action'] = 'registration'; check('valid Turnstile accepted', false);
