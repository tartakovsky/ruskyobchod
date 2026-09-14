<?php
if(PHP_SAPI!=='cli')exit;
$_SERVER['HTTP_HOST']='gastronom-bratislava.sk';$_SERVER['HTTPS']='on';$_SERVER['SERVER_PORT']=443;$_SERVER['REQUEST_METHOD']='GET';$_SERVER['REQUEST_URI']='/';define('DISABLE_WP_CRON',true);
require '/home/u595644545/domains/ruskyobchod.sk/public_html/wp-load.php';
if(wp_parse_url(home_url(),PHP_URL_HOST)!=='gastronom-bratislava.sk')throw new RuntimeException('Unexpected domain');
$backup='/home/u595644545/backups/format-domain-20260914-01/contact-description-before.json';
$old=get_post_meta(186,'_yoast_wpseo_metadesc',true);
if(!file_exists($backup))file_put_contents($backup,json_encode(['post_id'=>186,'description'=>$old],JSON_UNESCAPED_UNICODE));
update_post_meta(186,'_yoast_wpseo_metadesc',str_replace('vchod z Palisády','vchod zo Zámockej ulice',$old));
foreach([186,10860]as$id){delete_post_meta($id,'_elementor_element_cache');clean_post_cache($id);}
if(class_exists('Elementor\\Plugin'))\Elementor\Plugin::$instance->files_manager->clear_cache();
wp_cache_flush();if(function_exists('wp_cache_clear_cache'))wp_cache_clear_cache();
$appleInvoked=false;
global $wp_filter;
foreach(($wp_filter['admin_init']->callbacks??[])as$callbacks)foreach($callbacks as$callback){$f=$callback['function'];if(is_array($f)&&is_object($f[0])&&get_class($f[0])==='WC_Payments_Apple_Pay_Registration'&&$f[1]==='verify_domain_on_domain_name_change'){$f[0]->verify_domain_on_domain_name_change();$appleInvoked=true;}}
$gateway=get_option('woocommerce_woocommerce_payments_settings',[]);
echo json_encode(['home'=>home_url('/'),'siteurl'=>site_url('/'),'contact_cache_cleared'=>true,'apple_domain_check_invoked'=>$appleInvoked,'apple_verified_domain'=>$gateway['apple_pay_verified_domain']??null,'apple_domain_error_present'=>(bool)get_option('wcpay_apple_pay_domain_error')],JSON_UNESCAPED_UNICODE)."\n";
