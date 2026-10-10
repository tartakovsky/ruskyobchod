<?php
define('ABSPATH','/app/');define('GASTRONOM_SMTP_CONFIG','/private-smtp.php');
file_put_contents(GASTRONOM_SMTP_CONFIG,"<?php return ['host'=>'smtp.m1.websupport.sk','port'=>465,'encryption'=>'ssl','username'=>'shop@example.invalid','password'=>'fake-test-only','from'=>'shop@example.invalid','name'=>'Gastronom'];");
chmod(GASTRONOM_SMTP_CONFIG,0600);
$GLOBALS['hooks']=[];
function add_filter($name,$callback,$priority=10){$GLOBALS['hooks'][$name][]=$callback;}
function add_action($name,$callback,$priority=10){add_filter($name,$callback,$priority);}
require __DIR__.'/../../wordpress/wp-content/mu-plugins/rusky-mail-transport.php';
function active_check($yes,$name){if(!$yes)throw new Exception('FAIL '.$name);echo 'PASS '.$name."\n";}
active_check($GLOBALS['hooks']['wp_mail_from'][0]('wordpress@site.invalid')==='shop@example.invalid','all WordPress mail uses authenticated sender, including custom Review mail');
active_check($GLOBALS['hooks']['wp_mail_from_name'][0]('WordPress')==='Gastronom','all mail uses Gastronom display name');
active_check(count($GLOBALS['hooks']['phpmailer_init'])===1&&!isset($GLOBALS['hooks']['pre_wp_mail']),'one SMTP owner registered for valid private configuration');
