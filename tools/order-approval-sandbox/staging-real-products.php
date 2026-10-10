<?php
// Run only as a CLI request against the isolated staging database. All test writes roll back.
declare(strict_types=1);
if(PHP_SAPI!=='cli')exit(1);
$_SERVER['HTTP_HOST']='staging.ruskyobchod.sk';$_SERVER['REQUEST_METHOD']='GET';$_SERVER['REQUEST_URI']='/';
define('DISABLE_WP_CRON',true);define('GASTRONOM_REVIEW_SANDBOX',true);define('GASTRONOM_REVIEW_FROM_UTC',gmdate('Y-m-d 00:00:00'));
require '/home/u595644545/domains/ruskyobchod.sk/public_html/staging-gastronom/wp-load.php';
global $wpdb;
if(wp_get_environment_type()!=='staging'||$wpdb->prefix!=='stg_'||!function_exists('rssg_block_external_http')||wp_using_ext_object_cache())throw new Exception('Isolation not established');
if(!is_wp_error(apply_filters('pre_http_request',false,[],'https://api.dotykacka.cz/'))||apply_filters('pre_wp_mail',null,[])!==true)throw new Exception('Isolation filters failed');
$engines=$wpdb->get_results("SHOW TABLE STATUS WHERE Name LIKE 'stg_%'",ARRAY_A);foreach($engines as$t)if($t['Engine']!=='InnoDB')throw new Exception('Nontransactional table: '.$t['Name']);
$counts=static fn()=>['products'=>(int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type='product'"),'orders'=>(int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type='shop_order'"),'options'=>(int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->options}")];
$before=$counts();$report=['environment'=>'staging','date'=>gmdate('c'),'baseline'=>$before,'checks'=>[],'scenarios'=>[],'external_http_attempts'=>0,'mail_attempts'=>0];
$wpdb->query('START TRANSACTION');
try{
 require __DIR__.'/candidate/gastronom-order-review/gastronom-order-review.php';
 if(!function_exists('gor_api')||!gor_is_sandbox())throw new Exception('Candidate not loaded');
 update_option('gor_review_cutoff',GASTRONOM_REVIEW_FROM_UTC);
 add_filter('pre_http_request',static function($pre)use(&$report){$report['external_http_attempts']++;return new WP_Error('test_block','Isolated test');},PHP_INT_MAX,3);
 add_filter('pre_wp_mail',static function($pre)use(&$report){$report['mail_attempts']++;return true;},PHP_INT_MAX,2);
 $snapshot=json_decode(file_get_contents(__DIR__.'/product-snapshot.json'),true,512,JSON_THROW_ON_ERROR);
 $report['staging_weight_unit_before']=get_option('woocommerce_weight_unit');update_option('woocommerce_weight_unit',$snapshot['weight_unit']);
 $products=[];$source=[];
 foreach($snapshot['products']as$d){if(!in_array($d['source_id'],[10310,10617,10781,10615,10564,11238,11411,11370],true))continue;
  $p=new WC_Product_Simple();$p->set_name($d['name']);$p->set_status('private');$p->set_regular_price((string)$d['gross_price']);$p->set_manage_stock(true);$p->set_stock_quantity((int)$d['stock']);$p->set_weight((string)$d['weight']);$p->set_tax_status('none');
  foreach($d['meta']as$k=>$v)$p->update_meta_data($k,$v);$p->save();$products[$d['source_id']]=$p;$source[$p->get_id()]=$d['source_id'];
 }
 $check=static function($ok,$name)use(&$report){if(!$ok)throw new Exception($name);$report['checks'][]=$name;};
 $api=static function($o,$action,$data=[]) { $o=wc_get_order($o->get_id());$o->read_meta_data(true);$s=gor_state($o);$req=new WP_REST_Request('POST');$req->set_url_params(['id'=>$o->get_id()]);$req->set_header('content-type','application/json');$req->set_body(json_encode(['action'=>$action,'version'=>$s['version']]+$data));return gor_api($req);};
 $make=static function($list) {
  $o=wc_create_order();$o->set_date_created(time());$o->set_currency('EUR');$o->set_payment_method('cod');$o->set_payment_method_title('Dobierka');$o->set_billing_first_name('Gastronom TEST');$o->set_billing_email('staging-check@example.invalid');$o->update_meta_data('_gastronom_lang','sk');
  foreach($list as$p){$weighted=$p->get_meta('_gastronom_weight_preorder')==='yes';$qty=1;$amount=(float)$p->get_price();$item=new WC_Order_Item_Product();$item->set_product($p);$item->set_quantity($qty);
   if($weighted){$min=(float)$p->get_meta('_gastronom_weight_preorder_min_kg');$max=(float)$p->get_meta('_gastronom_weight_preorder_max_kg');$amount*=($min+$max)/2;$item->add_meta_data('_gastronom_weight_preorder','yes');$item->add_meta_data('_gastronom_weight_min_kg',$min);$item->add_meta_data('_gastronom_weight_max_kg',$max);$item->add_meta_data('_gastronom_price_per_kg',(float)$p->get_price());}
   $item->set_subtotal($amount);$item->set_total($amount);$o->add_item($item);
  }
  $ship=new WC_Order_Item_Shipping();$ship->set_method_id('packeta');$ship->set_method_title('Packeta');$ship->set_total(3.5);$o->add_item($ship);$o->calculate_totals(false);$o->save();return $o;
 };
 $zero=$make([$products[10310]]);$zs=gor_state($zero);$zr=$api($zero,'weight',['weights'=>[$zs['quote']['lines'][0]['key']=>'0.4']]);$check(is_wp_error($zr),'Real zero stock rejects reserve');
 $report['catalog_blockers']=['All six weighted products have zero stock in captured catalog'];
 // Only replenish test copies; retain missing ordinary shipping weights from the live catalog.
 foreach($products as$p){$p->set_stock_quantity(20);if($p->get_meta('_gastronom_weight_preorder')==='yes')$p->update_meta_data('_gastronom_cash_stock_kg',10);$p->save();}
 foreach($products as$p){if($p->get_meta('_gastronom_weight_preorder')!=='yes')continue;
  $o=$make([$p]);$s=gor_state($o);$l=$s['quote']['lines'][0];$min=(float)$p->get_meta('_gastronom_weight_preorder_min_kg');$max=(float)$p->get_meta('_gastronom_weight_preorder_max_kg');
  $check($l['weight_min_kg']===$min&&$l['weight_max_kg']===$max,'Saved range matches real product '.$source[$p->get_id()]);
  foreach([$min-.001,$max+.001]as$bad)$check(is_wp_error($api($o,'weight',['weights'=>[$l['key']=>(string)$bad]])),'Range boundary rejected '.$source[$p->get_id()].' '.$bad);
 }
 foreach(['normal','replacement','weight','replacement_weight']as$type){
  try{
   $weighted=in_array($type,['weight','replacement_weight'],true);$changed=in_array($type,['replacement','replacement_weight'],true);
   $stockBefore=[];foreach($products as$pid=>$p){$fresh=wc_get_product($p->get_id());$stockBefore[$pid]=['stock'=>(float)$fresh->get_stock_quantity(),'kg'=>(float)$fresh->get_meta('_gastronom_cash_stock_kg')];}
   $o=$make($weighted?[$products[11411],$products[10310],$products[10781]]:[$products[11411]]);$original=$o->get_total();
   if($weighted){$state=gor_state($o);$weights=[];foreach($state['quote']['lines']as$l)if($l['unit']==='kg')$weights[$l['key']]=$source[$l['product_id']]===10310?'0.4':'0.25';
    $bad=$weights;$bad[array_key_first($bad)]='0.6';$check(is_wp_error($api($o,'weight',['weights'=>$bad])),$type.': upper bound rejected');
    $r=$api($o,'weight',['weights'=>$weights]);if(is_wp_error($r))throw new Exception($r->get_error_message());$check($r['state']['status']==='pending',$type.': weight does not require edit/consent');
   }
   if($changed){$api($o,'edit');$o=wc_get_order($o->get_id());$o->read_meta_data(true);$state=gor_state($o);$lines=[];foreach($state['quote']['lines']as$l){if($source[$l['product_id']]===11411){$p=$products[11370];$lines[]=['product_id'=>$p->get_id(),'quantity'=>'1','price'=>(string)$p->get_price()];}else $lines[]=$l+['quantity'=>(string)($l['qty']/1000),'price'=>(string)($l['rate']/100),'weight_confirmed'=>true];}
    $r=$api($o,'save',['quote'=>['lines'=>$lines],'cause'=>'absence','reason'=>'Auto test replacement']);if(is_wp_error($r))throw new Exception($r->get_error_message());
    $r=$api($o,'proposal');if(is_wp_error($r))throw new Exception($r->get_error_message());$check($r['state']['status']==='awaiting',$type.': proposal waits for consent');
    $check(is_wp_error($api($o,'approve',['dispatch_date'=>gmdate('Y-m-d',time()+86400)])),$type.': approval blocked before consent');
    $api($o,'agree',['consent_note'=>'TEST captured reply']);
   }
   $check(is_wp_error($api($o,'approve')),$type.': missing date rejected');
   $r=$api($o,'approve',['dispatch_date'=>gmdate('Y-m-d',time()+86400)]);if(is_wp_error($r))throw new Exception($r->get_error_message());$s=$r['state'];$check($s['status']==='approved',$type.': approved');
   $check(Gastronom\OrderReview\Mail::variant($s)===$type,$type.': correct mail variant');$o=wc_get_order($o->get_id());$o->read_meta_data(true);$check(count($o->get_meta('_gor_sandbox_effects'))===1,$type.': one simulated fiscal effect');
   $o->read_meta_data(true);$s=gor_state($o);$check(count($s['mail'])===($changed?2:1),$type.': expected mail count');$check((float)$o->get_total()===(float)$original,$type.': original Woo total unchanged');
   $delivery=gor_carrier_values($o);$check(!array_key_exists('weight_grams',$delivery),$type.': no parcel weight override');$check($delivery['value_cents']===Gastronom\OrderReview\Review::total($s['quote']),$type.': carrier value matches final total');
   $parcel=gor_gls_parcel(['CODAmount'=>999,'ServiceList'=>[['Code'=>'INS','INSParameter'=>['Value'=>999]]]],$o);
   $check((int)round($parcel['CODAmount']*100)===$delivery['cod_cents']&&(int)round($parcel['ServiceList'][0]['INSParameter']['Value']*100)===$delivery['value_cents'],$type.': GLS COD and insured value');
   $entity=new class($o->get_id()){private $id;function __construct($id){$this->id=$id;}function getNumber(){return $this->id;}};
   $packet=gor_packeta_payload(['value'=>999,'cod'=>999,'weight'=>.1,'deliverOn'=>'2026-09-20','addressId'=>'TEST-POINT'],$entity);
   $check((int)round($packet['value']*100)===$delivery['value_cents']&&(int)round($packet['cod']*100)===$delivery['cod_cents']&&$packet['weight']===.1&&$packet['deliverOn']==='2026-09-20'&&$packet['addressId']==='TEST-POINT',$type.': Packeta totals and weight preserve carrier fields');
   if($weighted){foreach([10310=>.4,10781=>.25]as$id=>$delta){$fresh=wc_get_product($products[$id]->get_id());$fresh->read_meta_data(true);$check(abs((float)$fresh->get_meta('_gastronom_cash_stock_kg')-($stockBefore[$id]['kg']-$delta))<.00001,$type.': exact kilogram deduction '.$id);}}
   $check(is_wp_error($api($o,'approve',['dispatch_date'=>gmdate('Y-m-d',time()+86400)])),$type.': repeat approval rejected');
   $report['scenarios'][$type]=['status'=>'passed','total_cents'=>$delivery['value_cents'],'packeta_weight_preserved'=>$packet['weight'],'mail_count'=>count($s['mail']),'mail'=>$s['mail']];
  }catch(Throwable$e){$report['scenarios'][$type]=['status'=>'failed','error'=>$e->getMessage()];}
 }
 $report['fixture_adjustments']='Only test copies: gross prices as untaxed final line amounts, synthetic stock 20 pcs/10 kg, ordinary physical shipping weights remain empty as in source. Actual source names, prices, Dotypos IDs and per-product ranges retained.';
}catch(Throwable$e){$report['fatal']=$e->getMessage();}
finally{$wpdb->query('ROLLBACK');$report['after']=$counts();$report['rollback_ok']=$report['after']===$before;$report['weight_unit_after']=$wpdb->get_var("SELECT option_value FROM {$wpdb->options} WHERE option_name='woocommerce_weight_unit'");}
echo json_encode($report,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
