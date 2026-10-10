<?php
declare(strict_types=1);
namespace Gastronom\OrderReview;

/** Exact adapter contract shared by isolated execution and the later reviewed live transport. */
final class Fulfillment
{
    public static function plan(array $s, int $orderId, bool $cod, callable $product): array
    {
        if (!in_array($s['status'], ['executing','error','approved'], true) || !$s['approved_quote']) {
            throw new \DomainException('Передача в исполнение возможна только для утверждённого состава.');
        }
        $quote = $s['approved_quote'];
        $stock = [];
        $catalog = [];
        $items = [];
        $total = Review::total($quote);
        $lineTotal = array_sum(array_column($quote['lines'], 'total'));
        $extra = $total - $lineTotal;
        $allocated = 0;
        foreach ($quote['lines'] as $index => $line) {
            $p = $product($line['product_id']);
            if (empty($p['dotypos_id'])) { throw new \DomainException('Нет привязки Dotypos для товара ' . $line['name']); }
            if (!empty($line['weight_pending'])) { throw new \DomainException('Фактический вес не подтверждён.'); }
            $id = (int) $p['dotypos_id'];
            $catalog[$line['product_id']] = $id;
            $stock[$id] = ($stock[$id] ?? 0) + $line['qty'];
            $share = $index === count($quote['lines'])-1 ? $extra-$allocated : (int) round($extra * $line['total'] / max(1,$lineTotal));
            $allocated += $share;
            $items[] = ['id'=>$id, 'qty'=>$line['qty']/1000, 'manual-price'=>round(($line['total']+$share)*10/$line['qty'],2), 'discount-percent'=>0, 'customizations'=>[]];
        }
        // The existing POS uses two-decimal unit prices. Reject amounts that cannot be represented exactly.
        $sum = static function (array $rows): int { return array_sum(array_map(static function ($i): int { return (int) round($i['manual-price'] * 100 * $i['qty']); },$rows)); };
        $residual = $total-$sum($items);
        if ($residual !== 0) {
            $found = false;
            foreach ($items as $i=>$item) {
                for ($step=1; $step<=1000; $step++) {
                    $candidate=$items;
                    $candidate[$i]['manual-price']=round($item['manual-price']+($residual>0?1:-1)*$step/100,2);
                    if ($candidate[$i]['manual-price'] < 0) { break; }
                    if ($sum($candidate)===$total) { $items=$candidate;$found=true;break 2; }
                }
            }
            // A whole-unit batch may require two adjacent cent prices, as in
            // six units totaling 45.43 EUR. Preserve the product and quantity.
            if (!$found) {
                foreach ($items as $i=>$item) {
                    $qty=(float)$item['qty'];
                    if ($qty<2 || abs($qty-round($qty))>0.000001) { continue; }
                    $units=(int)round($qty);
                    $lineCents=(int)round($item['manual-price']*100*$units)+$residual;
                    if ($lineCents<0) { continue; }
                    $priceCents=intdiv($lineCents,$units);
                    $higherUnits=$lineCents%$units;
                    $lower=$item;
                    $lower['qty']=$units-$higherUnits;
                    $lower['manual-price']=$priceCents/100;
                    $replacement=[$lower];
                    if ($higherUnits>0) {
                        $higher=$item;
                        $higher['qty']=$higherUnits;
                        $higher['manual-price']=($priceCents+1)/100;
                        $replacement[]=$higher;
                    }
                    $candidate=$items;
                    array_splice($candidate,$i,1,$replacement);
                    if ($sum($candidate)===$total) { $items=$candidate;$found=true;break; }
                }
            }
            if (!$found) { throw new \DomainException('Итог не представим в точности на кассе. Требуется проверить округление до утверждения.'); }
        }
        return [
            'key'=>$s['effect']['key'], 'order_id'=>$orderId,
            'receipt'=>['action'=>'order/create-issue-pay','external-id'=>'GOR-'.$orderId.'-r'.$s['revision'], 'idempotency-key'=>$s['effect']['key'], 'items'=>$items, 'print-type'=>'local'],
            'total_cents'=>$total, 'stock_milliunits'=>$stock, 'catalog'=>$catalog,
            'delivery'=>['value_cents'=>$total,'cod_cents'=>$cod?max(0,Review::balance($s)):0,'dispatch_date'=>$s['dispatch_date']],
        ];
    }
}
