<?php
require __DIR__.'/wp-fixture.php';
function al(bool $yes,string $name):void {if(!$yes)throw new Exception('FAIL '.$name);echo 'PASS '.$name."\n";}
$GLOBALS['admin_locale']='ru_RU';
$o=new WC_Order(17001);$o->meta['_gastronom_lang']='sk';
$original=gor_state($o);$view=gor_admin_state($original);
al($original['language']==='sk'&&$view['language']==='sk'&&$view['quote']['lines'][0]['name']==='Domáce pelmene, 500 g','Russian admin controls preserve Slovak order product names');
$GLOBALS['admin_locale']='sk_SK';$o->meta['_gastronom_lang']='ru';$original=gor_state($o);$view=gor_admin_state($original);
al($view['quote']['lines'][0]['name']==='Пельмени «Домашние», 500 г'&&$original['quote']['lines'][0]['name']==='Пельмени «Домашние», 500 г','Slovak admin controls preserve Russian order product names without modifying source');
al(gor_mail_state($original)['quote']['lines'][0]['name']==='Пельмени «Домашние», 500 г','Russian customer mail remains Russian under Slovak admin');
al(Gastronom\OrderReview\AdminLanguage::message('Согласие относится к другой версии.','sk')==='Súhlas sa vzťahuje na inú verziu.','Slovak validation errors localized at admin boundary');
$GLOBALS['admin']=false;$GLOBALS['ajax']=false;$GLOBALS['lang']='sk';
function is_admin(){return $GLOBALS['admin'];}function wp_doing_ajax(){return $GLOBALS['ajax'];}function gls_server_lang(){return $GLOBALS['lang'];}
require __DIR__.'/../../wordpress/wp-content/mu-plugins/rusky-theme-chrome-language.php';
$source='Please read and accept the terms and conditions to proceed with your order.';
$callback=null;foreach($GLOBALS['hooks']['gettext'][120] as $fn){if(basename((new ReflectionFunction($fn))->getFileName())==='rusky-theme-chrome-language.php')$callback=$fn;}
al($callback!==null,'existing language module owns checkout error translation');
$ru='Чтобы продолжить оформление заказа, прочитайте правила и условия и подтвердите своё согласие с ними.';
$sk='Pred pokračovaním objednávky si prečítajte obchodné podmienky a potvrďte svoj súhlas s nimi.';
al($callback($ru,$source,'woocommerce')===$sk,'Slovak checkout terms error localized despite Russian native catalogue');
$GLOBALS['admin']=true;$GLOBALS['ajax']=true;al($callback($ru,$source,'woocommerce')===$sk,'checkout AJAX terms error follows storefront language');
$GLOBALS['ajax']=false;al($callback($ru,$source,'woocommerce')===$ru,'native administrative catalogue unchanged');
$GLOBALS['admin']=false;$GLOBALS['lang']='ru';al($callback($sk,$source,'woocommerce')===$ru,'Russian checkout terms error localized despite Slovak catalogue');
al($callback('Native translation','Unmapped text','woocommerce')==='Native translation','native translation fallback remains intact');
