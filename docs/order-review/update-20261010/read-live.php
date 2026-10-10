<?php
if(PHP_SAPI!=='cli')exit(1);
define('DISABLE_WP_CRON',true);
$_SERVER['HTTP_HOST']='gastronom-bratislava.sk';$_SERVER['HTTPS']='on';$_SERVER['REQUEST_METHOD']='GET';$_SERVER['REQUEST_URI']='/';
require '/home/u595644545/domains/ruskyobchod.sk/public_html/wp-load.php';
add_filter('query',static function($sql){if(!preg_match('/^\s*(SELECT|SHOW|DESCRIBE|EXPLAIN)\b/i',$sql))throw new RuntimeException('Read-only verifier blocked SQL mutation');return $sql;},PHP_INT_MAX);
add_filter('pre_http_request',static function(){throw new RuntimeException('External HTTP blocked');},PHP_INT_MAX,3);
add_filter('pre_wp_mail',static function(){throw new RuntimeException('Mail blocked');},PHP_INT_MAX,2);
$r=['at'=>gmdate('c'),'php'=>PHP_VERSION,'wp'=>get_bloginfo('version'),'woo'=>WC_VERSION,'live'=>defined('GASTRONOM_REVIEW_LIVE')&&GASTRONOM_REVIEW_LIVE,'cutoff'=>get_option('gor_review_cutoff'),'home'=>home_url('/'),'site_language'=>get_option('WPLANG')];
$o=wc_get_order(11654);if($o){$s=gor_state($o);$r['test_order']=['id'=>11654,'status'=>$s['status'],'revision'=>$s['revision'],'language'=>$s['language'],'consent_revision'=>$s['consent_revision'],'proposal_mail_status'=>$s['mail']['proposal-'.$s['revision']]['status']??null];}
$r['admin_locales']=[];foreach(get_users(['role'=>'administrator','fields'=>'ID']) as $id)$r['admin_locales'][]=get_user_locale($id);
$r['files']=[];foreach(json_decode(file_get_contents($argv[1]),true) as $path=>$hash){$live=ABSPATH.substr($path,strlen('wordpress/'));$r['files'][$path]=is_file($live)?hash_file('sha256',$live):null;}
echo json_encode($r,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)."\n";
