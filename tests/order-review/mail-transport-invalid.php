<?php
define('ABSPATH','/app/');define('GASTRONOM_SMTP_CONFIG','/missing-private-smtp.php');
$GLOBALS['hooks']=[];
function add_filter($name,$callback,$priority=10){$GLOBALS['hooks'][$name][]=$callback;}
function add_action($name,$callback,$priority=10){add_filter($name,$callback,$priority);}
require __DIR__.'/../../wordpress/wp-content/mu-plugins/rusky-mail-transport.php';
function invalid_check($yes,$name){if(!$yes)throw new Exception('FAIL '.$name);echo 'PASS '.$name."\n";}
$callback=$GLOBALS['hooks']['pre_wp_mail'][0];
invalid_check($callback(null)===false,'missing active SMTP config fails closed without PHP mail fallback');
invalid_check($callback(true)===true,'isolated mail capture remains intact');
invalid_check(!isset($GLOBALS['hooks']['phpmailer_init']),'invalid settings never create an SMTP connection');
