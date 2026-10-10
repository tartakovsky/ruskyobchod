<?php
require __DIR__.'/../../wordpress/wp-content/plugins/gastronom-order-review/src/Review.php';
require __DIR__.'/../../wordpress/wp-content/plugins/gastronom-order-review/src/Fulfillment.php';
require __DIR__.'/../../wordpress/wp-content/plugins/gastronom-order-review/src/Reservations.php';
use Gastronom\OrderReview\Review as R;
use Gastronom\OrderReview\Fulfillment as F;
use Gastronom\OrderReview\Reservations as H;
$n=0;
function test($ok,$name){global $n;if(!$ok)throw new Exception('FAIL '.$name);$n++;echo "PASS $name\n";}
function fails($fn,$name){try{$fn();}catch(DomainException $e){test(true,$name);return;}test(false,$name);}
$quote=['lines'=>[['product_id'=>1,'name'=>'Сыр','unit'=>'kg','qty'=>375,'total'=>750,'rate'=>2000,'weight_pending'=>false],['product_id'=>2,'name'=>'Пельмени','unit'=>'pcs','qty'=>1000,'total'=>600,'rate'=>600,'weight_pending'=>false]],'shipping'=>350,'fees'=>0];
$s=R::create($quote,0,'ru',true);
fails(fn()=>F::plan($s,12,true,fn($id)=>[]),'unapproved payload blocked');
$s=R::command($s,'approve',['version'=>1,'order_id'=>12,'cod'=>true,'dispatch_date'=>'2026-09-12'],'2026-09-10','1');
$plan=F::plan($s,12,true,fn($id)=>['dotypos_id'=>$id+100,'weight_grams'=>500]);
test($plan['delivery']['cod_cents']===1700,'COD matches effective total');
test(!array_key_exists('weight_grams',$plan['delivery']),'parcel weight belongs to carrier');
test($plan['stock_milliunits']===[101=>375,102=>1000],'stock projection retains units');
test((int)round(array_sum(array_map(fn($i)=>round($i['manual-price']*$i['qty'],2),$plan['receipt']['items']))*100)===1700,'POS rounded total exact');
fails(fn()=>F::plan($s,12,true,fn($id)=>['dotypos_id'=>0,'weight_grams'=>500]),'unmapped SKU blocked');
test(F::plan($s,12,true,fn($id)=>['dotypos_id'=>100+$id])===$plan,'missing ordinary product mass does not block approval');
$physical=['1:kg'=>1000,'2:pcs'=>1000];$book=H::reserve([],'12',$quote,$physical);
test($physical['2:pcs']===1000,'reservation does not deduct physical stock');
test(H::reserve($book,'12',$quote,$physical)===$book,'renewal does not double reserve');
fails(fn()=>H::reserve($book,'13',$quote,$physical),'second order cannot reserve final piece');
$smaller=$quote;$smaller['lines']=[$quote['lines'][0]];$book=H::reserve($book,'12',$smaller,$physical);
test(!isset($book[12]['2:pcs']),'replacement releases removed hold');
$book=H::release($book,'12');test($book===[],'cancellation releases reservation');
$q=$quote;$q['lines'][0]['weight_pending']=true;fails(fn()=>H::reserve([],'1',$q,$physical),'unknown weight not treated as exact reservation');
echo "\n$n adapter and reservation contract checks passed.\n";
