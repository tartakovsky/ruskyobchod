<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli')exit(1);
$_SERVER['HTTP_HOST']='gastronom-bratislava.sk';$_SERVER['HTTPS']='on';$_SERVER['REQUEST_METHOD']='GET';$_SERVER['REQUEST_URI']='/';define('DISABLE_WP_CRON',true);
require '/home/u595644545/domains/ruskyobchod.sk/public_html/wp-load.php';
use Gastronom\OrderReview\Review as R;
use Gastronom\OrderReview\Mail as M;
use Gastronom\OrderReview\Executor as E;
use Gastronom\OrderReview\Outbox as O;
if(!function_exists('gor_api') || gor_is_sandbox() || !GASTRONOM_REVIEW_LIVE || time()<strtotime(get_option('gor_review_cutoff').' UTC'))throw new RuntimeException('Live boundary not reached');
$report=['at'=>gmdate('c'),'live'=>true,'after_cutoff'=>true,'checks'=>[],'failures'=>[],'database_writes'=>0,'http_calls'=>0,'emails_sent'=>0,'orders_saved'=>0];
// These restrictions apply only to this CLI process, never the live web workers.
// Keep WordPress transient cache writes in the default process-local object cache.
if (is_file(WP_CONTENT_DIR.'/object-cache.php')) throw new RuntimeException('Persistent object cache requires separate isolation');
wp_using_ext_object_cache(true);
add_filter('query',static function($sql){if(!preg_match('/^\s*(SELECT|SHOW|DESCRIBE|EXPLAIN)\b/i',$sql))throw new RuntimeException('Read-only scenario blocked SQL mutation');return $sql;},PHP_INT_MAX);
add_filter('pre_http_request',static function(){throw new RuntimeException('Live scenario blocked external HTTP');},PHP_INT_MAX,3);
add_filter('pre_wp_mail',static function(){throw new RuntimeException('Live scenario blocked mail send');},PHP_INT_MAX,2);
function lc(bool $yes,string $name):void {global $report;if(!$yes)throw new RuntimeException('FAIL '.$name);$report['checks'][]=$name;echo 'PASS '.$name."\n";}
function lr(callable $fn,string $name):void {try{$fn();}catch(DomainException $e){lc(true,$name);return;}lc(false,$name);}
function cmd(array $s,string $action,array $input=[],bool $cod=true):array {return R::command($s,$action,['version'=>$s['version'],'order_id'=>0,'cod'=>$cod]+$input,current_time('Y-m-d'),'live-memory-test');}
function approved(array $s,bool $cod=true):array {return cmd($s,'approve',['dispatch_date'=>current_time('Y-m-d')],$cod);}
function agreed(array $s):array {$s=cmd($s,'proposal');$s['mail']['proposal-'.$s['revision']]['status']='sent';return cmd($s,'agree',['consent_note'=>'Simulated consent in memory only']);}
function complete_memory(WC_Order $o,array $s):array {$s=R::complete($s,true,'memory-only-no-pos-call');$o->update_meta_data('_gor_state',$s);return $s;}
function money_text(int $cents):string {return number_format($cents/100,2,'.','');}
function native_order(array $catalog,bool $paid=false,bool $ru=true):WC_Order {
 $o=new WC_Order();$o->set_date_created(time());$o->set_currency('EUR');$o->set_payment_method($paid?'woocommerce_payments':'cod');$o->set_status('processing');$o->set_billing_first_name('Memory test');$o->set_billing_email('test@example.invalid');
 $o->update_meta_data('_gastronom_lang',$ru?'ru':'sk');$total=350;
 foreach(array_slice($catalog,0,2) as $row){$i=new WC_Order_Item_Product();$i->set_product($row['product']);$i->set_quantity(1);$i->set_subtotal($row['cents']/100);$i->set_total($row['cents']/100);$o->add_item($i);$total+=$row['cents'];}
 $shipping=new WC_Order_Item_Shipping();$shipping->set_method_id('packeta');$shipping->set_method_title('Packeta');$shipping->set_total(3.5);$o->add_item($shipping);$o->set_shipping_total(3.5);$o->set_total($total/100);if($paid)$o->set_date_paid(time());return $o;
}
try {
 $source=wc_get_order(11398);$catalog=[];
 foreach($source->get_items() as $item){$p=$item->get_product();if(!$p || $item->get_meta('_gastronom_weight_preorder',true)==='yes' || !$p->get_meta(Dotypos::$keys['product']['field-id']))continue;$cents=(int)round(($item->get_total()+$item->get_total_tax())*100/max(1,$item->get_quantity()));if($cents>0)$catalog[]=['product'=>$p,'cents'=>$cents];if(count($catalog)===3)break;}
 if(count($catalog)<3)throw new RuntimeException('Need three mapped real ordinary products');
 $o=native_order($catalog);$s=gor_state($o);$original=$s['original'];
 lc(gor_managed($o) && $s['status']==='pending','ordinary order enters pending approval');
 lr(fn()=>cmd($s,'approve'),'courier date is required');
 lr(fn()=>cmd($s,'approve',['dispatch_date'=>'2026-02-30']),'invalid courier date rejected');
 $s=approved($s);$plan=gor_plan($o,$s);lc($plan['total_cents']===R::total($original),'ordinary exact approved fiscal total');$s=complete_memory($o,$s);
 lc($s['status']==='approved' && $s['original']===$original,'ordinary approval preserves original composition');
 lc(gor_carrier_values($o)['cod_cents']===R::total($original),'COD carrier amount is approved total');
 $parcel=gor_gls_parcel(['CODAmount'=>999,'CODReference'=>'memory'],$o);lc($parcel['CODAmount']===R::total($original)/100,'GLS COD reflects approved total');
 lr(fn()=>approved($s),'repeated approval blocked');lr(fn()=>cmd($s,'edit'),'approved composition immutable');
 $o=native_order($catalog,true);$s=gor_state($o);$paid=$s['paid'];$s=cmd($s,'edit');$q=$s['quote'];array_shift($q['lines']);$s=cmd($s,'save',['quote'=>$q,'reason'=>'Memory unavailable item']);
 lc(R::balance($s)===-$catalog[0]['cents'],'prepaid removal computes refund difference');
 lr(fn()=>approved($s,false),'unsent correction cannot be approved');$s=cmd($s,'proposal');lr(fn()=>cmd($s,'agree',['consent_note'=>'memory']),'unaccepted proposal mail cannot be agreed');$s['mail']['proposal-'.$s['revision']]['status']='sent';lr(fn()=>cmd($s,'agree'),'consent note is required');$s=cmd($s,'agree',['consent_note'=>'Memory agreement']);
 $s=approved($s,false);$s=complete_memory($o,$s);$d=gor_carrier_values($o);lc($d['cod_cents']===0,'prepaid corrected carrier has zero COD');
 $parcel=gor_gls_parcel(['CODAmount'=>999,'CODReference'=>'memory'],$o);lc(!isset($parcel['CODAmount'],$parcel['CODReference']),'GLS removes COD for prepayment');
 $s=cmd($s,'bank',['iban'=>'SK31 1200 0000 1987 4263 7541','holder'=>'Memory test','bank_consent'=>true],false);$s=cmd($s,'refund',['amount'=>money_text($catalog[0]['cents']),'reference'=>'memory-refund'],false);lc(R::balance($s)===0 && $s['paid']===$paid,'manual refund balances without changing received payment');lr(fn()=>cmd($s,'refund',['amount'=>'0.01','reference'=>'memory-refund'],false),'duplicate refund reference rejected');
 $o=native_order($catalog,true);$s=gor_state($o);$s=cmd($s,'edit');$q=$s['quote'];$q['lines'][]=['key'=>'added','product_id'=>$catalog[2]['product']->get_id(),'name'=>'Real mapped product','qty'=>1000,'total'=>$catalog[2]['cents'],'rate'=>$catalog[2]['cents'],'unit'=>'pcs','weight_pending'=>false];$s=cmd($s,'save',['quote'=>$q,'reason'=>'Memory replacement']);$s=agreed($s);
 lc(R::balance($s)===$catalog[2]['cents'],'replacement/addition computes top-up');lr(fn()=>approved($s,false),'prepaid top-up must be received before approval');$s=cmd($s,'payment',['amount'=>money_text($catalog[2]['cents']),'reference'=>'memory-payment'],false);lc(R::balance($s)===0,'top-up registration settles balance');lr(fn()=>cmd($s,'payment',['amount'=>'0.01','reference'=>'memory-payment'],false),'duplicate payment reference rejected');$s=approved($s,false);lc(gor_plan($o,$s)['delivery']['cod_cents']===0,'paid top-up does not create carrier COD');
 $o=native_order($catalog);$s=gor_state($o);$s=cmd($s,'edit');$q=$s['quote'];$q['lines'][0]=$q['lines'][1];$q['lines'][0]['key']='replacement';$s=cmd($s,'save',['quote'=>$q,'reason'=>'Memory replacement']);$s=agreed($s);$s=cmd($s,'edit');$q=$s['quote'];$q['lines'][0]['qty']=2000;$q['lines'][0]['total']*=2;$s=cmd($s,'save',['quote'=>$q,'reason'=>'New memory version']);lc($s['consent_revision']===null,'changed version invalidates former consent');lr(fn()=>approved($s),'changed version needs fresh agreement');
 $o=native_order($catalog,true);$s=gor_state($o);$fullPaid=$s['paid'];$s=cmd($s,'edit');$q=$s['quote'];$q['lines']=[];$s=cmd($s,'save',['quote'=>$q,'reason'=>'Memory full cancellation']);$s=agreed($s);$s=cmd($s,'approve',[],false);$s=complete_memory($o,$s);lc($s['status']==='cancelled' && R::total($s['quote'])===0 && $s['dispatch_date']==='','full cancellation zeros shipping and needs no courier date');
 lr(fn()=>gor_carrier_values($o),'cancelled approval cannot be shipped');$s=cmd($s,'bank',['iban'=>'SK31 1200 0000 1987 4263 7541','holder'=>'Memory test','bank_consent'=>true],false);$s=cmd($s,'refund',['amount'=>money_text($fullPaid),'reference'=>'memory-full-refund'],false);lc(R::balance($s)===0 && $s['refunded']===$fullPaid,'full manual refund balances cancelled prepayment');
 $weighted=null;foreach(wc_get_order(11415)->get_items() as $item){if($item->get_meta('_gastronom_weight_preorder',true)==='yes'){$weighted=clone $item;break;}}
 if(!$weighted)throw new RuntimeException('Real weighted item unavailable');$p=$weighted->get_product();$min=(float)$p->get_meta('_gastronom_weight_preorder_min_kg',true);$max=(float)$p->get_meta('_gastronom_weight_preorder_max_kg',true);if($min<=0 || $max<$min)throw new RuntimeException('Real weighted product range missing');
 $weighted->set_quantity(1);$weighted->update_meta_data('_gastronom_actual_weight_kg','');$weighted->update_meta_data('_gastronom_weight_min_kg',$min);$weighted->update_meta_data('_gastronom_weight_max_kg',$max);
 $wo=new WC_Order();$wo->set_date_created(time());$wo->set_currency('EUR');$wo->set_payment_method('cod');$wo->add_item($weighted);$wo->set_total($weighted->get_total()+$weighted->get_total_tax());$ws=gor_state($wo);$key=$ws['quote']['lines'][0]['key'];
 lc($ws['quote']['lines'][0]['weight_pending'],'weighted order begins with unknown actual weight');lr(fn()=>approved($ws),'unconfirmed weight blocks approval');lr(fn()=>cmd($ws,'weight',['weights'=>[$key=>number_format(max(0.001,$min-0.001),3,'.','')]]),'weight below purchased range rejected');
 $weight=round(($min+$max)/2,3);$ws=cmd($ws,'weight',['weights'=>[$key=>number_format($weight,3,'.','')]]);lc($ws['status']==='pending' && !$ws['quote']['lines'][0]['weight_pending'] && $ws['consent_revision']===null,'actual weight entered without edit or customer agreement');$ws=approved($ws);$wplan=gor_plan($wo,$ws);lc(array_sum($wplan['stock_milliunits'])===(int)round($weight*1000),'weighted fiscal stock uses exact kilograms');
 foreach([true,false] as $ru){$lo=native_order($catalog,false,$ru);$ls=gor_state($lo);$ls['dispatch_date']=current_time('Y-m-d');$message=M::build(gor_mail_state($ls),gor_customer($lo),'confirmation');lc(str_contains($message['html'],'lang="'.($ru?'ru':'sk').'"'),'confirmation language '.($ru?'RU':'SK'));}
 $journal=[];$sends=0;$remote=[];$receipt=['id'=>'memory','external_id'=>$plan['receipt']['external-id'],'paid'=>true,'fiscalized'=>true,'cancelled'=>false,'total_cents'=>$plan['total_cents'],'stock_milliunits'=>$plan['stock_milliunits']];
 $save=static function($value)use(&$journal){$journal=$value;};$lookup=static function($id)use(&$remote){return $remote;};$send=static function($value)use(&$sends,&$remote,$receipt){$sends++;$remote=[$receipt];throw new DomainException('Memory lost response');};
 lr(fn()=>E::run($plan,[],$save,$lookup,$send),'lost POS response remains uncertain');$done=E::run($plan,$journal,$save,$lookup,$send);lc($done['status']==='done' && $sends===1 && $done['reconciled'],'POS retry reconciles without second sale');E::run($plan,$journal,$save,$lookup,$send);lc($sends===1,'completed execution replay does not send');$bad=$receipt;$bad['total_cents']++;lr(fn()=>E::validate($bad,$plan),'wrong fiscal total blocks completion');$bad=$receipt;$bad['stock_milliunits'][array_key_first($bad['stock_milliunits'])]++;lr(fn()=>E::validate($bad,$plan),'wrong fiscal stock blocks completion');
 $entry=['status'=>'pending','attempts'=>0,'to'=>'test@example.invalid','reply_to'=>'test@example.invalid','message'=>['subject'=>'Memory','html'=>'Memory']];$saved=[];$saveMail=static function($value)use(&$saved){$saved=$value;};$failed=O::deliver($entry,$saveMail,static fn()=>false);lc($failed['status']==='failed','mail transport refusal is visible');$sent=O::deliver($failed,$saveMail,static fn()=>true);lc($sent['status']==='sent' && $sent['attempts']===2,'failed mail can be retried independently');$unknown=O::deliver($entry,$saveMail,static function(){throw new DomainException('Memory uncertain send');});lc($unknown['status']==='sending','uncertain mail keeps sending state');lr(fn()=>O::deliver($unknown,$saveMail,static fn()=>true),'uncertain mail is not blindly resent');
 lc($o->get_id()===0 && $wo->get_id()===0,'native scenario objects remain unsaved');
 $report['success']=true;
} catch(Throwable $e){$report['success']=false;$report['failures'][]=$e->getMessage();echo $e->getMessage()."\n";}
$report['at_end']=gmdate('c');$report['count']=count($report['checks']);file_put_contents(__DIR__.'/live-scenarios.json',json_encode($report,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));echo json_encode(['success'=>$report['success'],'checks'=>$report['count'],'database_writes'=>0,'real_sales'=>0,'real_emails'=>0])."\n";exit($report['success']?0:1);
