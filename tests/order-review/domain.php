<?php
declare(strict_types=1);
require __DIR__ . '/../../wordpress/wp-content/plugins/gastronom-order-review/src/Review.php';
require __DIR__ . '/../../wordpress/wp-content/plugins/gastronom-order-review/src/Mail.php';
use Gastronom\OrderReview\Review as R;
use Gastronom\OrderReview\Mail as M;
$count = 0;
function check(bool $yes, string $name): void { global $count; if (!$yes) { throw new Exception('FAIL ' . $name); } $count++; echo "PASS $name\n"; }
function rejects(callable $fn, string $name): void { try { $fn(); } catch (DomainException $e) { check(true, $name); return; } check(false, $name); }
function command(array $s, string $action, array $input = []): array { return R::command($s, $action, ['version'=>$s['version'],'order_id'=>12001,'cod'=>true]+$input, '2026-09-10', 'admin:1'); }
function line(int $id=1,int $cents=600): array { return ['product_id'=>$id,'name'=>'Пельмени '.$id,'qty'=>1000,'rate'=>$cents,'total'=>$cents,'unit'=>'pcs','weight_pending'=>false]; }
function initial(int $paid=0): array { return R::create(['lines'=>[line(),line(2,3050)],'shipping'=>350,'fees'=>0],$paid,'ru',true); }
function agree(array $s): array { $s=command($s,'proposal');$s['mail']['proposal-'.$s['revision']]['status']='sent';return command($s,'agree',['consent_note'=>'Ответ клиента 10.09, письмо #42']); }
check(R::money('12,34')===1234,'money uses integer cents');
check(R::quantity('0,375')===375,'weight precision');
foreach (['-1','1e4','NaN','1.001','99999999999999999'] as $bad) { rejects(fn()=>R::money($bad),'reject money '.$bad); }
rejects(fn()=>R::quantity('0'),'zero quantity');
check(R::iban('SK31 1200 0000 1987 4263 7541')==='SK3112000000198742637541','IBAN checksum and normalization');
rejects(fn()=>R::iban('SK3112000000198742637542'),'IBAN bad checksum');
$s=initial();$original=$s['original'];
rejects(fn()=>command($s,'approve'),'courier date required');
rejects(fn()=>command($s,'approve',['dispatch_date'=>'2026-02-30']),'invalid calendar date');
rejects(fn()=>command($s,'approve',['dispatch_date'=>'2026-09-09']),'past dispatch date');
$s=command($s,'approve',['dispatch_date'=>'2026-09-12']);
check($s['status']==='executing'&&!isset($s['mail']['confirmation']),'no confirmation before execution');
$key=$s['effect']['key'];$s=R::complete($s,false,'Касса недоступна');
check($s['status']==='error','execution failure is visible');
$s=command($s,'retry');check($s['effect']['key']===$key,'retry preserves idempotency key');
$s=R::complete($s,true,'receipt-1');
check($s['status']==='approved'&&isset($s['mail']['confirmation']),'confirmation outbox after success');
rejects(fn()=>command($s,'approve',['dispatch_date'=>'2026-09-12']),'double approval blocked');
rejects(fn()=>command($s,'edit'),'approved composition immutable');
check($s['original']===$original,'original order preserved');
$s=command(initial(4000),'edit');$q=$s['quote'];array_shift($q['lines']);
$s=command($s,'save',['quote'=>$q,'reason'=>'Пельмени 1 отсутствуют']);
check(R::balance($s)===-600,'removal shows refund delta');
rejects(fn()=>command($s,'approve',['dispatch_date'=>'2026-09-12']),'unsent proposal cannot approve');
$s=command($s,'proposal');
rejects(fn()=>command($s,'agree',['consent_note'=>'reply']),'failed delivery cannot be agreed');
$s['mail']['proposal-'.$s['revision']]['status']='sent';
rejects(fn()=>command($s,'agree'),'consent evidence required');
$s=command($s,'agree',['consent_note'=>'mail #42']);
$s=command($s,'edit');$q=$s['quote'];$q['lines'][]=line(3,750);$s=command($s,'save',['quote'=>$q,'reason'=>'Пельмени 1 отсутствуют']);
check($s['consent_revision']===null&&R::balance($s)===150,'changed replacement invalidates consent and calculates topup');
$s=agree($s);
rejects(fn()=>R::command($s,'approve',['version'=>$s['version'],'order_id'=>12001,'cod'=>false,'dispatch_date'=>'2026-09-12'],'2026-09-10','1'),'prepaid shortfall blocks approval');
$s=command($s,'payment',['amount'=>'1.50','reference'=>'bank#99']);
check(R::balance($s)===0,'manual extra payment balances order');
$s=command($s,'approve',['dispatch_date'=>'2026-09-12']);$s=R::complete($s,true,'receipt');
$customer=['number'=>'12001','name'=>'Анна <script>','payment'=>'Карта','shipping'=>'Packeta','cod'=>false];
$mail=M::build($s,$customer,'confirmation');
check(strpos($mail['html'],'12.09.2026')!==false,'confirmation includes dispatch date');
check(strpos($mail['html'],'&lt;script&gt;')!==false,'email HTML escaped');
$s=command(initial(4000),'edit');$q=$s['quote'];array_shift($q['lines']);$s=command($s,'save',['quote'=>$q,'reason'=>'Нет пельменей']);$s=agree($s);
$s=command($s,'bank',['iban'=>'SK3112000000198742637541','holder'=>'Anna','bank_consent'=>true]);
rejects(fn()=>command($s,'refund',['amount'=>'6','reference'=>'bank']),'refund not recorded before approval');
$s=command($s,'approve',['dispatch_date'=>'2026-09-12']);$s=R::complete($s,true,'receipt');
$s=command($s,'refund',['amount'=>'2','reference'=>'bank#1']);
check(R::balance($s)===-400,'partial refund leaves exact outstanding balance');
rejects(fn()=>command($s,'refund',['amount'=>'2','reference'=>'bank#1']),'same bank reference cannot be counted twice');
rejects(fn()=>command($s,'refund',['amount'=>'5','reference'=>'bank#2']),'over-refund blocked');
$s=command($s,'refund',['amount'=>'4','reference'=>'bank#2']);
check(R::balance($s)===0&&$s['paid']===4000&&R::total($s['quote'])===3400,'refund does not double subtract order total');
rejects(fn()=>command($s,'refund',['amount'=>'4','reference'=>'bank#2']),'repeated refund blocked after balance zero');
rejects(fn()=>R::command($s,'bank',['version'=>1],'2026-09-10','1'),'stale admin tab rejected');
$s=command(initial(),'edit');$q=$s['quote'];$q['shipping']++;
rejects(fn()=>command($s,'save',['quote'=>$q,'reason'=>'x']),'shipping deal preserved');
$q=$s['quote'];$q['lines']=[];$empty=command($s,'save',['quote'=>$q,'reason'=>'Нет товаров']);check(R::total($empty['quote'])===0,'proposed cancellation removes shipping and fees');$empty=agree($empty);$empty=command($empty,'approve');$empty=R::complete($empty,true,'no-sale');check($empty['status']==='cancelled','empty agreed order cancelled without courier date');
$s=initial();$s['quote']['lines'][0]['weight_pending']=true;
rejects(fn()=>command($s,'approve',['dispatch_date'=>'2026-09-12']),'unknown actual weight blocks approval');
$s=initial();$s['courier']=false;$s=command($s,'approve');check($s['status']==='executing','pickup does not require courier date');
$s=initial(4000);$s['language']='sk';$m=M::build($s,$customer,'proposal');check(strpos($m['subject'],'Upresnenie')!==false,'Slovak template');

$pending=initial();$pending['quote']['lines'][0]['unit']='kg';$pending['quote']['lines'][0]['weight_pending']=true;$m=M::table($pending['quote'],'ru');check(strpos($m,'1 шт.')!==false&&strpos($m,'вес будет уточнён')!==false,'unweighed pack count never labeled as kilograms');
echo "\n$count domain checks passed.\n";
