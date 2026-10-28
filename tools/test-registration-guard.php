<?php
// Standalone security-path checks. No WordPress bootstrap, customer creation or mail.
define('ABSPATH', __DIR__);
define('MINUTE_IN_SECONDS', 60);
define('HOUR_IN_SECONDS', 3600);
$cache = [];
$options = [];
function add_action(...$args) {}
function add_filter(...$args) {}
function wp_hash($s) { return hash_hmac('sha256', $s, 'test'); }
function wp_salt($s) { return 'test'; }
function wp_unslash($s) { return stripslashes($s); }
function get_transient($k) { return $GLOBALS['cache'][$k] ?? false; }
function set_transient($k, $v, $ttl) { $GLOBALS['cache'][$k] = $v; }
function add_option($key, $value, $deprecated = '', $autoload = false) {
    if (array_key_exists($key, $GLOBALS['options'])) return false;
    $GLOBALS['options'][$key] = $value; return true;
}
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
    $GLOBALS['options'] = [];
    $_COOKIE = ['rrg_session' => str_repeat('a', 64)];
    $_SERVER = ['REQUEST_METHOD' => 'POST', 'REMOTE_ADDR' => '192.0.2.1'];
    $_POST = ['register' => '1', 'rrg_website' => '', 'rrg_issued' => (string) (time() - 10)];
    fresh_token();
}
function fresh_token(): void {
    $_POST['rrg_token'] = bin2hex(random_bytes(16));
    $_POST['rrg_signature'] = rrg_signature($_POST['rrg_issued'], $_POST['rrg_token']);
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
form(); $_POST['rrg_issued'] = (string) time(); fresh_token(); check('instant submission rejected', true);
form(); $_POST['rrg_issued'] = (string) (time() - 7201); fresh_token(); check('expired proof rejected', true);
form(); $_POST['rrg_issued'] = []; check('array input rejected', true);
form(); for ($i = 0; $i < 2; $i++) { fresh_token(); check('allowed attempt ' . ($i + 1), false); } check('third attempt rejected', true);
$_SERVER['REMOTE_ADDR'] = '192.0.2.2'; fresh_token(); check('different IP unaffected', false);
form(); $_POST = []; check('guest checkout unaffected', false);
form(); check('first proof use accepted', false); check('same proof cannot be reused', true);
$_SERVER['REMOTE_ADDR'] = '192.0.2.3'; check('replay on another IP rejected', true);
form(); $_COOKIE['rrg_session'] = str_repeat('b', 64); check('proof bound to browser session', true);
form(); unset($_COOKIE['rrg_session']); check('missing session rejected', true);
form(); $_COOKIE['rrg_session'] = []; check('array cookie rejected', true);
form(); $_POST['rrg_token'] = []; check('array token rejected', true);
form(); unset($_POST['rrg_token']); check('legacy reusable timestamp rejected', true);
foreach (['example+.webonlinepostmail.de@mail.ru', 'example+.webonlinepostmail.de@example.org', 'bot@webonlinepostmail.de'] as $email) {
    if (!rrg_customer_errors(new WP_Error(), '', $email)->errors) throw new Exception('Campaign address not rejected');
    echo "PASS campaign rejected before customer insert\n";
}
foreach (['customer+orders@gmail.com', 'customer@mail.ru', 'customer@mpbio.com', 'webonlinepostmail.de-shop@gmail.com'] as $email) {
    if (rrg_customer_errors(new WP_Error(), '', $email)->errors) throw new Exception('Legitimate alias rejected');
    echo "PASS legitimate email unaffected\n";
}
form(); $errors = rrg_validate(new WP_Error(), '', '', 'bot+.webonlinepostmail.de@mail.ru');
if (!$errors->errors) throw new Exception('Standalone campaign bypass');
echo "PASS standalone campaign rejected\n";
define('RUSKY_TURNSTILE_SITE_KEY', 'test'); define('RUSKY_TURNSTILE_SECRET_KEY', 'test');
form(); check('Turnstile missing token rejected', true);
$_POST['cf-turnstile-response'] = 'test'; $response = new WP_Error(); check('Turnstile outage rejected', true);
form(); $_POST['cf-turnstile-response'] = 'test';
$response = ['success' => true, 'action' => 'registration', 'hostname' => 'evil.example']; check('wrong hostname rejected', true);
form(); $_POST['cf-turnstile-response'] = 'test';
$response['hostname'] = 'gastronom-bratislava.sk'; $response['action'] = 'login'; check('wrong action rejected', true);
$response['action'] = 'registration'; check('valid Turnstile accepted', false);
