<?php
declare(strict_types=1);
require __DIR__.'/../../wordpress/wp-content/plugins/gastronom-order-review/src/Review.php';
require __DIR__.'/../../wordpress/wp-content/plugins/gastronom-order-review/src/Fulfillment.php';
use Gastronom\OrderReview\Review as R;
use Gastronom\OrderReview\Fulfillment as F;
function rounding_check(bool $ok, string $name): void {
    if (!$ok) { throw new RuntimeException('FAIL '.$name); }
    echo 'PASS '.$name."\n";
}
function rounding_plan(int $total, int $quantity=6000, int $goods=3600, string $unit='pcs'): array {
    $s=R::create(['lines'=>[['product_id'=>1,'name'=>'Product','qty'=>$quantity,'total'=>$goods,'rate'=>600,'unit'=>$unit,'weight_pending'=>false]],'shipping'=>$total-$goods,'fees'=>0],0,'ru',true);
    $s=R::command($s,'approve',['version'=>$s['version'],'order_id'=>11612,'cod'=>true,'dispatch_date'=>'2026-10-03'],'2026-10-03','test');
    return F::plan($s,11612,true,fn($id)=>['dotypos_id'=>12345]);
}
foreach ([3600,4540,4541,4542,4543,4544,4545,4546,4547,4548,4549,4550,4551] as $cents) {
    $p=rounding_plan($cents);$sum=0;$qty=0;
    foreach($p['receipt']['items'] as $item) {
        $sum+=(int)round(round($item['manual-price'],2)*$item['qty']*100);
        $qty+=$item['qty'];
        if($item['id']!==12345 || $item['discount-percent']!==0 || $item['customizations']!==[])throw new RuntimeException('Fiscal product/discount changed');
    }
    rounding_check($sum===$cents && $p['total_cents']===$cents && $p['delivery']['cod_cents']===$cents,'exact approved fiscal/COD amount '.$cents);
    rounding_check((float)$qty===6.0 && $p['stock_milliunits']===[12345=>6000] && $p['catalog']===[1=>12345],'stock quantity and mapping unchanged '.$cents);
    if($cents===4543)rounding_check($p['receipt']['items'][0]['qty']===5 && $p['receipt']['items'][0]['manual-price']===7.57 && $p['receipt']['items'][1]['qty']===1 && $p['receipt']['items'][1]['manual-price']===7.58,'same exact split as legacy 11612 fix');
}
$p=rounding_plan(600,1000,600);
rounding_check(count($p['receipt']['items'])===1 && $p['receipt']['items'][0]['manual-price']===6.0,'single-unit exact line unchanged');
$p=rounding_plan(750,375,750,'kg');
rounding_check(count($p['receipt']['items'])===1 && $p['receipt']['items'][0]['qty']===0.375 && $p['receipt']['items'][0]['manual-price']===20.0 && $p['stock_milliunits']===[12345=>375],'fractional weight quantity and amount unchanged');
