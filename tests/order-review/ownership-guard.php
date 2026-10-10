<?php
define('ABSPATH','/sandbox/');
$GLOBALS['filters']=[];$GLOBALS['cutoff']='2026-09-10 00:00:00';
function add_action(...$args){}
function add_filter($hook,$callback,...$args){$GLOBALS['filters'][$hook]=$callback;}
function get_option($key,$default=false){return $key==='gor_review_cutoff'?$GLOBALS['cutoff']:$default;}
class WC_Order {public $date='2026-09-10';public $state='';function get_date_created(){return new DateTimeImmutable($this->date);}function get_meta($key,$single=true){return $key==='_gor_state'?$this->state:'';}}
require __DIR__.'/../../wordpress/wp-content/mu-plugins/rusky-order-review-guard.php';
require __DIR__.'/../../wordpress/wp-content/mu-plugins/rusky-dotypos-fiscalization.php';
function guard_check($ok,$name){if(!$ok)throw new Exception('FAIL '.$name);echo "PASS $name\n";}
$new=new WC_Order();$old=new WC_Order();$old->date='2026-09-09';
guard_check(grg_managed($new)&&!grg_managed($old),'persistent cutoff retains order ownership with review plugin disabled');
guard_check(!rdf_should_fiscalize($new),'old fiscal handler blocked even when review plugin disabled');
guard_check(!$GLOBALS['filters']['woocommerce_can_reduce_order_stock'](true,$new),'Woo automatic stock blocked during rollback');
guard_check(!$GLOBALS['filters']['woocommerce_email_enabled_customer_processing_order'](true,$new),'rollback suppresses misleading original confirmation');
$old->state=['schema'=>1];$GLOBALS['cutoff']='';guard_check(grg_managed($old),'saved effective state retains ownership even without cutoff option');
$old->state='';guard_check(!grg_managed($old),'unconfigured install leaves ordinary legacy orders untouched');
