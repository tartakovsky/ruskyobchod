<?php
declare(strict_types=1);

/** Small-store durable hold ledger. A global unique DB lock serializes competing checkouts. */
function gor_hold_book(): array {
    wp_cache_delete('gor_stock_holds', 'options');
    $book=get_option('gor_stock_holds', []);
    if (!is_array($book)) { throw new DomainException('Журнал резервов повреждён.'); }
    return $book;
}

function gor_held_quantity(int $product, int $exclude=0): int {
    $qty=0;
    foreach (gor_hold_book() as $id=>$rows) {
        if ((int)$id !== $exclude) { $qty+=(int)($rows[$product]['pieces'] ?? 0); }
    }
    return $qty;
}

function gor_hold_requirements(array $quote): array {
    $rows=[];
    foreach ($quote['lines'] as $line) {
        $p=wc_get_product($line['product_id']);
        if (!$p || !$p->managing_stock()) { throw new DomainException('Для резерва требуется управляемый остаток товара '.$line['name']); }
        $id=$p->get_stock_managed_by_id();
        $qty=$line['qty'];
        if ($line['unit']==='kg') {
            $max=(float)$p->get_meta('_gastronom_weight_preorder_max_kg', true);
            if ($max<=0) { $max=(float)$p->get_meta('_gastronom_weight_preorder_min_kg', true); }
            if ($max<=0) { throw new DomainException('Укажите максимальную массу упаковки для резерва '.$line['name']); }
            // Before weighing, qty is the customer's number of pieces, not kilograms.
            $pieces=!empty($line['weight_pending']) ? (int)ceil($qty/1000) : (int)ceil($qty/($max*1000));
            $milli=!empty($line['weight_pending']) ? (int)ceil($pieces*$max*1000) : $qty;
        } else { $pieces=(int)ceil($qty/1000); $milli=$qty; }
        $rows[$id]=['pieces'=>($rows[$id]['pieces'] ?? 0)+$pieces,'milli'=>($rows[$id]['milli'] ?? 0)+$milli,'unit'=>$line['unit']];
    }
    return $rows;
}

function gor_reserve(WC_Order $order, array $quote): void {
    if (!add_option('gor_stock_lock', time(), '', false)) { throw new DomainException('Резерв обновляется другим заказом. Повторите операцию.'); }
    try {
        $rows=gor_hold_requirements($quote);
        $book=gor_hold_book();
        foreach ($rows as $id=>$row) {
            $p=wc_get_product($id);
            $stock=(float)$p->get_stock_quantity('edit');
            $held=(float)wc_get_held_stock_quantity($p, $order->get_id());
            if ($row['pieces']>$stock-$held) { throw new DomainException('Недостаточно доступного остатка: '.$p->get_name()); }
        }
        $book[$order->get_id()]=$rows;
        if (!update_option('gor_stock_holds', $book, false) && gor_hold_book()!==$book) { throw new DomainException('Не удалось сохранить резерв.'); }
    } finally { delete_option('gor_stock_lock'); }
}

function gor_release_hold(WC_Order $order): void {
    if (!add_option('gor_stock_lock', time(), '', false)) { throw new DomainException('Резерв занят другой операцией. Повторите освобождение.'); }
    try {
        $book=gor_hold_book(); unset($book[$order->get_id()]);
        if (!update_option('gor_stock_holds', $book, false) && gor_hold_book()!==$book) { throw new DomainException('Не удалось освободить резерв.'); }
    } finally { delete_option('gor_stock_lock'); }
}

/** Local authority simulation only. Production must read current Dotypos stock, never subtract twice. */
function gor_settle_sandbox_stock(WC_Order $order, array $state): void {
    if ($order->get_meta('_gor_stock_synced', true) === $state['effect']['key']) { gor_release_hold($order); return; }
    if (!add_option('gor_stock_lock', time(), '', false)) { throw new DomainException('Обновление остатков занято. Повторите сверку.'); }
    try {
        $target=$order->get_meta('_gor_stock_target', true);
        if (!is_array($target)) {
            $target=[];
            foreach (gor_hold_requirements($state['approved_quote']) as $id=>$row) {
                $p=wc_get_product($id);
                if ($row['unit']==='kg') {
                    $max=(float)$p->get_meta('_gastronom_weight_preorder_max_kg', true);
                    if ($max<=0) { $max=(float)$p->get_meta('_gastronom_weight_preorder_min_kg', true); }
                    $raw=$p->get_meta('_gastronom_cash_stock_kg', true);
                    $raw=$raw==='' ? (float)$p->get_stock_quantity('edit')*$max : (float)$raw;
                    $remaining=max(0, $raw-$row['milli']/1000);
                    $target[$id]=['quantity'=>(int)floor(($remaining+.000001)/$max),'kg'=>$remaining];
                } else { $target[$id]=['quantity'=>max(0, (float)$p->get_stock_quantity('edit')-$row['pieces'])]; }
            }
            $order->update_meta_data('_gor_stock_target', $target); $order->save_meta_data();
        }
        foreach ($target as $id=>$snapshot) {
            $p=wc_get_product($id);
            if (isset($snapshot['kg'])) { $p->update_meta_data('_gastronom_cash_stock_kg', $snapshot['kg']); $p->save_meta_data(); }
            $result=wc_update_product_stock($p, $snapshot['quantity'], 'set');
            if (!is_numeric($result)) { throw new DomainException('Не удалось обновить остаток товара '.$id); }
        }
        $order->update_meta_data('_gor_stock_synced', $state['effect']['key']); $order->save_meta_data();
        $book=gor_hold_book(); unset($book[$order->get_id()]);
        if (!update_option('gor_stock_holds', $book, false) && gor_hold_book()!==$book) { throw new DomainException('Не удалось закрыть резерв.'); }
    } finally { delete_option('gor_stock_lock'); }
}

add_filter('woocommerce_order_hold_stock_minutes', static function ($minutes, $order) {
    return gor_managed($order) ? 0 : $minutes;
}, PHP_INT_MAX, 2);

add_filter('woocommerce_query_for_reserved_stock', static function ($query, $product, $exclude) {
    $held=gor_held_quantity((int)$product, (int)$exclude);
    return $held ? 'SELECT COALESCE(('.$query.'), 0) + '.(int)$held : $query;
}, PHP_INT_MAX, 3);

foreach (['woocommerce_checkout_order_created','woocommerce_store_api_checkout_order_processed'] as $hook) {
    add_action($hook, static function ($order): void {
        if (gor_managed($order)) { gor_reserve($order, gor_state($order)['quote']); }
    }, 5);
}

add_action('woocommerce_order_status_changed', static function ($id, $from, $to, $order): void {
    if (!gor_managed($order) || !in_array($to, ['cancelled','failed'], true)) { return; }
    $state=gor_state($order);
    // Unknown remote execution must retain its hold until a human has reconciled the sale.
    if (in_array($state['status'], ['executing','error','approved'], true)) { return; }
    gor_release_hold($order);
}, 1000, 4);
