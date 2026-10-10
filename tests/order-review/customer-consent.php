<?php
declare(strict_types=1);
require __DIR__.'/wp-fixture.php';
use Gastronom\OrderReview\Consent;
use Gastronom\OrderReview\Mail;
use Gastronom\OrderReview\Review;
function cc(bool $yes,string $name):void {if(!$yes)throw new Exception('FAIL '.$name);echo 'PASS '.$name."\n";}
function action(WC_Order $o,string $a,array $data=[]){$s=gor_state($o);return gor_api(new WP_REST_Request(['id'=>$o->id,'action'=>$a,'version'=>$s['version']]+$data));}
function proposal(int $id):WC_Order {$o=new WC_Order($id);$o->meta['_gastronom_lang']='ru';$GLOBALS['orders'][$id]=$o;action($o,'edit');action($o,'save',['quote'=>['lines'=>[['product_id'=>2,'quantity'=>'1','price'=>'30.50']]],'reason'=>'Absent']);action($o,'proposal');return $o;}
$o=proposal(18001);$s=gor_state($o);$entry=$s['mail']['proposal-1'];
$url=$entry['message']['html'];cc(str_contains($url,'Согласен с изменениями')&&str_contains($url,'Связаться с магазином'),'actual outbox message has both customer actions');
$c=['expires'=>$entry['approval_expires'],'token'=>Consent::token($o->id,$s,$o->get_billing_email(),$entry['approval_expires'],wp_salt('auth'))];
$before=$o->meta;
cc(gor_valid_consent($o,$s,$c)&&$before===$o->meta,'GET validation has no consent or order side effects');
foreach (['token'=>'forged','expires'=>$c['expires']+1] as $field=>$bad) {cc(!gor_valid_consent($o,$s,[$field=>$bad]+$c),'tampered '.$field.' rejected');}
cc(!Consent::valid($o->id,$s,$o->get_billing_email(),$c['expires'],$c['token'],wp_salt('auth'),$c['expires']),'expiry boundary rejected');
cc(!Consent::valid(18002,$s,$o->get_billing_email(),$c['expires'],$c['token'],wp_salt('auth'),time()),'different order rejected');
cc(!Consent::valid($o->id,$s,'other@example.invalid',$c['expires'],$c['token'],wp_salt('auth'),time()),'different recipient rejected');
$pending=$s;$pending['mail']['proposal-1']['status']='failed';cc(!gor_valid_consent($o,$pending,$c),'unsent message cannot be accepted');
$changed=$s;$changed['quote']['lines'][0]['qty']++;cc(!gor_valid_consent($o,$changed,$c),'composition is cryptographically bound');
$lock='_gor_lock_'.$o->id;$GLOBALS['options'][$lock]=time();cc(gor_api(new WP_REST_Request(['id'=>$o->id]),$c) instanceof WP_Error,'customer uses existing order lock');unset($GLOBALS['options'][$lock]);
$r=gor_api(new WP_REST_Request(['id'=>$o->id,'action'=>'approve','dispatch_date'=>'2026-09-12']),$c);
cc($r['state']['status']==='agreed'&&$r['state']['consent_revision']===1,'customer records agreement only, ignoring supplied approve action');
cc($r['state']['consent_note']==='customer-link'&&$r['state']['history'][array_key_last($r['state']['history'])]['actor']==='customer'&&preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/',$r['state']['consent_at'])===1,'customer consent actor and UTC time recorded automatically');
cc(!isset($o->meta['_gor_sandbox_effects'])&&$o->get_total()===40&&count($o->items)===2,'customer consent leaves POS, original source and stock effects untouched');
$agreed=$o->meta;$r=gor_api(new WP_REST_Request(['id'=>$o->id]),$c);cc(is_array($r)&&$o->meta===$agreed,'repeated consent is idempotent');
action($o,'edit');cc(gor_api(new WP_REST_Request(['id'=>$o->id]),$c) instanceof WP_Error,'editing invalidates an already sent customer link');
$o=proposal(18003);$r=action($o,'agree',['consent_channel'=>'phone']);cc($r['state']['status']==='agreed'&&$r['state']['consent_note']==='admin-phone'&&!empty($r['state']['consent_at']),'manual telephone consent requires no date input');
$o=proposal(18004);$before=$o->meta;$r=action($o,'read');cc($before===$o->meta&&$r['state']['status']==='awaiting','admin refresh is read-only');
$old=gor_state($o);$old['mail']['proposal-1']=['status'=>'sent','message'=>['html'=>'old email']];gor_save($o,$old);$r=action($o,'agree',['consent_channel'=>'email']);cc($r['state']['status']==='agreed','existing emails remain compatible with manual consent');
$line=['product_id'=>1,'name'=>'Товар <script>','qty'=>3000,'rate'=>900,'total'=>2700,'unit'=>'pcs','weight_pending'=>false];
$s=Review::create(['lines'=>[$line],'shipping'=>0,'fees'=>0],0,'ru',false);$s['quote']['lines'][0]['qty']=1000;$s['quote']['lines'][0]['total']=900;
$text=Mail::changes($s);cc(str_contains($text,'заказано 3 шт.')&&str_contains($text,'предлагаем 1 шт.')&&str_contains($text,'не хватает 2 шт.')&&str_contains($text,'Приносим извинения'),'partial shortage identifies exact missing quantity with apology');
cc(str_contains($text,'&lt;script&gt;')&&!str_contains($text,'<script>'),'shortage names escaped');
$s['quote']['lines']=[];cc(str_contains(Mail::changes($s),'не хватает 3 шт.'),'full removal identifies full missing quantity');
$s['quote']['lines']=[$line];$s['quote']['lines'][0]['qty']=1000;$s['quote']['lines'][0]['total']=900;$s['cause']='customer';cc(!str_contains(Mail::changes($s),'не хватает')&&str_contains(Mail::changes($s),'по вашей просьбе'),'customer request not misrepresented as stock shortage');
$s['cause']='absence';$s['language']='sk';$text=Mail::changes($s);cc(str_contains($text,'chýba 2 ks')&&str_contains($text,'Ospravedlňujeme sa'),'Slovak partial shortage');
$s['language']='ru';$s['original']['lines'][0]['unit']='kg';$s['original']['lines'][0]['qty']=850;$s['quote']['lines'][0]['unit']='kg';$s['quote']['lines'][0]['qty']=350;cc(str_contains(Mail::changes($s),'не хватает 0,5 кг'),'fractional kg shortage');
$s['original']['lines'][0]['weight_pending']=true;cc(!str_contains(Mail::changes($s),'не хватает'),'actual pack weight not interpreted as missing kilograms');
$s['original']['lines']=[$line,$line];$s['quote']['lines']=[$line];cc(str_contains(Mail::changes($s),'не хватает 3 шт.'),'duplicate original product rows aggregated');
