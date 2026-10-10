<?php
defined('ABSPATH') || exit;
$s=gor_mail_state(gor_state($order));
$ru=$s['language']==='ru';
$intro=$ru ? 'Спасибо за заказ в Gastronom! Мы получили его и проверим наличие товаров. После проверки отправим подтверждение с окончательным составом, стоимостью и, для курьерской доставки, датой передачи курьеру. Если потребуется замена, сначала согласуем её с вами по электронной почте.'
    : 'Ďakujeme za objednávku v Gastronom! Prijali sme ju a overíme dostupnosť tovaru. Následne vám pošleme potvrdenie s konečným zložením, cenou a pri doručení dopravcom aj dátumom odovzdania dopravcovi. Ak bude potrebná náhrada, vopred ju s vami odsúhlasíme e-mailom.';
if (!empty($plain_text)) {
    echo $intro."\n\n";
    foreach ($s['original']['lines'] as $line) { echo $line['name'].' — '.Gastronom\OrderReview\Mail::amount($line['total'])."\n"; }
    echo ($ru?'Первоначальный итог: ':'Pôvodná suma: ').Gastronom\OrderReview\Mail::amount(Gastronom\OrderReview\Review::total($s['original']))."\n";
} else {
    do_action('woocommerce_email_header', $email_heading, $email);
    echo '<p>'.esc_html($intro).'</p>'.Gastronom\OrderReview\Mail::table($s['original'], $s['language']);
    do_action('woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email);
    do_action('woocommerce_email_footer', $email);
}
