<?php
if(PHP_SAPI!=='cli')exit(1);
define('WP_DISABLE_FATAL_ERROR_HANDLER',true);
register_shutdown_function(static function(){ $e=error_get_last();if($e&&in_array($e['type'],[E_ERROR,E_PARSE,E_COMPILE_ERROR],true))echo json_encode(['test_error'=>$e['message']])."\n"; });

define('DISABLE_WP_CRON',true);$_SERVER['HTTP_HOST']='gastronom-bratislava.sk';$_SERVER['HTTPS']='on';$_SERVER['REQUEST_METHOD']='GET';$_SERVER['REQUEST_URI']='/';require '/home/u595644545/domains/ruskyobchod.sk/public_html/wp-load.php';
if(is_file(WP_CONTENT_DIR.'/object-cache.php'))throw new RuntimeException('Persistent cache requires isolation');wp_using_ext_object_cache(true);remove_all_actions('shutdown');
add_filter('query',static function($sql){if(!preg_match('/^\s*(SELECT|SHOW|DESCRIBE|EXPLAIN)\b/i',$sql))throw new RuntimeException('SQL write blocked');return $sql;},PHP_INT_MAX);add_filter('pre_http_request',static function(){throw new RuntimeException('HTTP blocked');},PHP_INT_MAX,3);add_filter('pre_wp_mail',static function(){throw new RuntimeException('Mail blocked');},PHP_INT_MAX,2);
use Gastronom\OrderReview\Consent;use Gastronom\OrderReview\Review;use Gastronom\OrderReview\Mail;
$report=['at'=>gmdate('c'),'checks'=>[],'database_writes'=>0,'real_sales'=>0,'real_emails'=>0];
function ct(bool $ok,string $name):void{global $report;if(!$ok)throw new RuntimeException('FAIL '.$name);$report['checks'][]=$name;echo 'PASS '.$name."\n";}
$o=wc_get_order(11654);$realOrder=$o;$o=clone $realOrder;$o->set_status('processing');$report['actual_order_status']=$realOrder->get_status();$before=hash('sha256',serialize($o->get_meta('_gor_state',true)));$s=gor_state($o);$original=$s;
$s['cause']='absence';$s['status']='awaiting';$s['revision']=99;$s['sent_revision']=99;$s['consent_revision']=null;$s['quote']=$s['original'];$s['quote']['lines'][0]['qty']=1000;$s['quote']['lines'][0]['total']=$s['quote']['lines'][0]['rate'];$expires=time()+3600;$s['mail']['proposal-99']=['status'=>'sent','approval_expires'=>$expires];
$c=['expires'=>$expires,'token'=>Consent::token($o->get_id(),$s,$o->get_billing_email(),$expires,wp_salt('auth'))];
ct(gor_valid_consent($o,$s,$c),'installed signer validates exactly bound proposal in memory');
ct(!gor_valid_consent($o,$s,['token'=>str_repeat('0',64)]+$c),'installed signer rejects forged signature');
ct(!Consent::valid($o->get_id(),$s,$o->get_billing_email(),$expires,$c['token'],wp_salt('auth'),$expires),'installed signer rejects expiry boundary');
$changed=$s;$changed['revision']++;ct(!gor_valid_consent($o,$changed,$c),'installed signer rejects different revision');
$changed=$s;$changed['quote']['lines'][0]['qty']++;ct(!gor_valid_consent($o,$changed,$c),'installed signer rejects changed quantity');
ct(!Consent::valid($o->get_id(),$s,'different@example.invalid',$expires,$c['token'],wp_salt('auth'),time()),'installed signer rejects different recipient');
$a=Review::command($s,'agree',['version'=>$s['version'],'consent_note'=>'customer-link','consent_at'=>gmdate('Y-m-d H:i:s')],current_time('Y-m-d'),'customer');
ct($a['status']==='agreed'&&$a['consent_revision']===99&&$a['effect']===$s['effect'],'installed agreement has no execution effect');
ct(count($a['history'])===count($s['history'])+1&&end($a['history'])['actor']==='customer','installed agreement records customer actor');
foreach(['ru','sk'] as $lang){$s['language']=$lang;$mail=Mail::build(gor_mail_state($s),gor_mail_customer($o,$s)+['approval_url'=>'https://example.invalid/review','contact_email'=>'shop@example.invalid'],'proposal');ct(str_contains($mail['html'],$lang==='ru'?'не хватает 1 шт.':'chýba 1 ks'),'installed partial shortage quantified '.$lang);ct(str_contains($mail['html'],$lang==='ru'?'Согласен с изменениями':'Súhlasím so zmenami'),'installed customer button '.$lang);}
foreach(get_users(['role'=>'administrator','fields'=>'ID']) as $id){wp_set_current_user($id);if(gor_admin_language()==='ru')break;}ct(gor_admin_language()==='ru','Russian admin projection selected independently from SK order');$view=gor_admin_state($original);$customer=gor_mail_customer($o,['language'=>gor_admin_language()]);
ct($view['language']===$original['language'],'admin projection preserves customer language');
ct($customer['payment']==='Оплата при получении'&&$customer['shipping']==='Самовывоз','Russian admin localizes real payment and pickup');
$report['product_language_projection']=[];foreach($original['quote']['lines'] as $i=>$l){$p=wc_get_product($l['product_id']);$expected=gastronom_localize_title($p->get_name('edit'),gor_admin_language());$report['product_language_projection'][]=['catalog_bilingual'=>$p->get_name('edit')!==$expected,'view_matches_catalog_language'=>$view['quote']['lines'][$i]['name']===$expected];ct($view['quote']['lines'][$i]['name']===gastronom_localize_title($l['name'],$original['language']),'real SK order item retains its order language under RU admin');}
ct(hash('sha256',serialize(wc_get_order(11654)->get_meta('_gor_state',true)))===$before,'actual order 11654 state unchanged throughout checks');
$report['success']=true;$report['count']=count($report['checks']);file_put_contents(__DIR__.'/live-consent-checks.json',json_encode($report,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));echo json_encode(['success'=>true,'checks'=>$report['count'],'product_language_projection'=>$report['product_language_projection'],'database_writes'=>0,'real_sales'=>0,'real_emails'=>0])."\n";

exit(0);
