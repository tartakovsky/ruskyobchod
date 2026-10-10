<?php
declare(strict_types=1);
use Gastronom\OrderReview\Consent;
use Gastronom\OrderReview\Mail;

function gor_approval_url(WC_Order $order, array $state, int $expires): string {
    return add_query_arg(['gor_review' => $order->get_id(), 'expires' => $expires,
        'token' => Consent::token($order->get_id(), $state, $order->get_billing_email(), $expires, wp_salt('auth'))], home_url('/'));
}

function gor_valid_consent(WC_Order $order, array $state, array $credentials): bool {
    return !in_array($order->get_status(), ['cancelled','refunded','failed','trash'], true)
        && Consent::valid($order->get_id(), $state, $order->get_billing_email(),
            (int) ($credentials['expires'] ?? 0), (string) ($credentials['token'] ?? ''), wp_salt('auth'), time());
}

// Email scanners may follow GET links. Only an explicit POST can record consent.
add_action('template_redirect', static function (): void {
    if (!isset($_GET['gor_review'])) { return; }
    if (!defined('DONOTCACHEPAGE')) { define('DONOTCACHEPAGE', true); }
    nocache_headers();
    header('Cache-Control: private, no-store, max-age=0');
    header('Referrer-Policy: no-referrer');
    header('X-Robots-Tag: noindex, nofollow, noarchive');
    header("Content-Security-Policy: default-src 'none'; style-src 'unsafe-inline'; form-action 'self'; frame-ancestors 'none'; base-uri 'none'");
    $order = wc_get_order(absint($_GET['gor_review']));
    $credentials = ['expires' => (int) ($_GET['expires'] ?? 0), 'token' => (string) ($_GET['token'] ?? '')];
    $valid = false; $state = null; $error = '';
    if ($order && gor_managed($order)) {
        try { $state = gor_state($order); $valid = gor_valid_consent($order, $state, $credentials); }
        catch (DomainException $e) { $valid = false; }
    }
    $ru = $state && $state['language'] === 'ru';
    $done = $valid && $state['status'] === 'agreed';
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if ($valid && $method === 'POST' && ($_POST['decision'] ?? '') === 'agree') {
        $request = new WP_REST_Request('POST');
        $request->set_url_params(['id' => $order->get_id()]);
        $request->set_body_params(['action' => 'agree']);
        $result = gor_api($request, $credentials);
        if (is_wp_error($result)) { $error = $ru ? 'Не удалось подтвердить изменения. Обновите страницу. Если предложение изменилось, запросите новое письмо у магазина.' : 'Zmeny sa nepodarilo potvrdiť. Obnovte stránku. Ak sa návrh zmenil, požiadajte obchod o nový e-mail.'; }
        else { $state = $result['state']; $done = true; }
    } elseif (!in_array($method, ['GET','HEAD','POST'], true)) { $valid = false; }
    status_header($valid ? ($error ? 409 : 200) : 410);
    $title = $ru ? 'Согласование изменений заказа' : 'Odsúhlasenie zmien objednávky';
    echo '<!doctype html><html lang="'.($ru ? 'ru' : 'sk').'"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'.esc_html($title).'</title><style>body{margin:0;background:#f4f5f7;color:#243443;font:17px/1.6 system-ui,sans-serif}main{max-width:760px;margin:32px auto;background:white;padding:28px;border-radius:12px}table{width:100%;border-collapse:collapse}td,th{padding:10px;text-align:left;border-bottom:1px solid #ddd}button,a{display:inline-block;padding:12px 18px;border-radius:6px}button{background:#185a7b;color:white;border:0;font:inherit;cursor:pointer}.error{color:#a00}@media(max-width:600px){main{margin:12px;padding:16px}td,th{padding:6px;font-size:14px}}</style><main><p>GASTRONOM</p><h1>'.esc_html($title).'</h1>';
    if (!$valid) {
        echo '<p>Ссылка недействительна или устарела. Запросите актуальное предложение у магазина.</p><p>Odkaz je neplatný alebo už nie je aktuálny. Požiadajte obchod o aktuálny návrh.</p>';
    } elseif ($done) {
        echo '<p>'.($ru ? 'Спасибо! Ваше согласие записано. Магазин проверит заказ и отправит окончательное подтверждение.' : 'Ďakujeme! Váš súhlas bol zaznamenaný. Obchod skontroluje objednávku a pošle konečné potvrdenie.').'</p>';
    } else {
        echo '<p>'.esc_html(($ru ? 'Заказ №' : 'Objednávka č. ').$order->get_order_number()).'</p>';
        if ($error) { echo '<p class="error">'.esc_html($error).'</p>'; }
        $mailState = gor_mail_state($state);
        echo Mail::changes($mailState).Mail::table($mailState['quote'], $state['language']);
        echo '<p>'.($ru ? 'Новая стоимость: ' : 'Nová cena: ').'<strong>'.Mail::amount(\Gastronom\OrderReview\Review::total($state['quote'])).'</strong></p>';
        $balance = \Gastronom\OrderReview\Review::balance($state);
        if ($balance !== 0) { echo '<p>'.($balance < 0 ? ($ru ? 'К возврату: ' : 'Na vrátenie: ') : ($ru ? 'Остаток к оплате: ' : 'Zostáva zaplatiť: ')).Mail::amount(abs($balance)).'</p>'; }
        if ($state['comment'] !== '') { echo '<p>'.nl2br(esc_html($state['comment'])).'</p>'; }
        echo '<p>'.($ru ? 'Подтверждая, вы соглашаетесь с этим составом и новой стоимостью заказа.' : 'Potvrdením súhlasíte s týmto zložením a novou cenou objednávky.').'</p><form method="post"><button name="decision" value="agree">'.($ru ? 'Согласен с изменениями' : 'Súhlasím so zmenami').'</button></form>';
    }
    $contact = sanitize_email((string) get_option('woocommerce_email_from_address', get_option('admin_email')));
    echo '<p><a href="'.esc_attr('mailto:'.$contact.'?subject='.rawurlencode($valid ? ($ru ? 'Заказ №' : 'Objednávka č. ').$order->get_order_number() : 'Gastronom')).'">'.($ru ? 'Связаться с магазином' : 'Kontaktovať obchod').'</a></p></main></html>';
    exit;
}, 0);
