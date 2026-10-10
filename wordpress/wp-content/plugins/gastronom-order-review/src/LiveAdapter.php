<?php
declare(strict_types=1);

/** Dormant until an explicitly configured release. No credentials are copied into this plugin. */
function gor_live_transport(WC_Order $order): Gastronom\OrderReview\DotyposTransport {
    foreach (['rdf_access_context','rdf_branch_id','rdf_api_get','rdf_payment_method_id'] as $function) {
        if (!function_exists($function)) { throw new DomainException('Не подключён адаптер Dotypos: '.$function); }
    }
    $context=rdf_access_context();
    if (is_wp_error($context)) { throw new DomainException($context->get_error_message()); }
    $branch=rdf_branch_id($context);
    if (is_wp_error($branch)) { throw new DomainException($branch->get_error_message()); }
    $get=static function ($path) use ($context): array {
        $value=rdf_api_get($context, $path);
        if (is_wp_error($value)) { throw new DomainException($value->get_error_message()); }
        if (!is_array($value)) { throw new DomainException('Пустой ответ Dotypos.'); }
        return $value;
    };
    $post=static function ($path, $payload) use ($context): array {
        $response=wp_remote_post('https://api.dotykacka.cz/v2/clouds/'.rawurlencode($context['cloud_id']).$path, [
            'headers'=>['Authorization'=>'Bearer '.$context['access_token'],'Content-Type'=>'application/json','Accept'=>'application/json'],
            'body'=>wp_json_encode($payload),'timeout'=>30]);
        if (is_wp_error($response)) { throw new DomainException($response->get_error_message()); }
        return (array)json_decode(wp_remote_retrieve_body($response), true);
    };
    return new Gastronom\OrderReview\DotyposTransport($get, $post, (int)$branch, (int)rdf_payment_method_id($order));
}

function gor_settle_live_stock(WC_Order $order, array $state): void {
    if (!function_exists('rdf_retry_stock_service')) { throw new DomainException('Сервис чтения склада недоступен.'); }
    $context=rdf_retry_stock_service();
    if (is_wp_error($context)) { throw new DomainException($context->get_error_message()); }
    if (!add_option('gor_stock_lock', time(), '', false)) { throw new DomainException('Обновление склада занято. Повторите сверку.'); }
    try {
        $journal=$order->get_meta('_gor_execution', true);
        $completed=(int)($journal['completed_at'] ?? 0);
        if ($completed<=0) { throw new DomainException('Не получено время завершения чека для сверки склада.'); }
        $stocks=[];
        foreach ($state['approved_quote']['lines'] as $line) {
            $p=wc_get_product($line['product_id']);
            if (!$p) { throw new DomainException('Товар удалён из каталога после утверждения.'); }
            $remoteId=gor_plan($order, $state)['catalog'][$line['product_id']];
            $row=$context['service']->getProductOnWarehouse($context['warehouse_id'], (string)$remoteId);
            if (!isset($row['stockQuantityStatus']) || !is_numeric($row['stockQuantityStatus'])) { throw new DomainException('Не получен остаток '.$p->get_name()); }
            if (Gastronom\OrderReview\DotyposTransport::timestamp($row['stockStatusVersionDate'] ?? null)<$completed) { throw new DomainException('Остаток ещё не синхронизирован после чека: '.$p->get_name()); }
            $stocks[$p->get_id()]=(float)$row['stockQuantityStatus'];
        }
        // Set current authoritative quantities. Never call a sale/stock-decrement endpoint here.
        foreach ($stocks as $id=>$quantity) {
            $p=wc_get_product($id);
            if ($p->get_meta('_gastronom_weight_preorder', true)==='yes') {
                if (!function_exists('rwp_apply_piece_stock')) { throw new DomainException('Адаптер весового остатка отсутствует.'); }
                rwp_apply_piece_stock($id, $quantity);
            } elseif (!is_numeric(wc_update_product_stock($p, $quantity, 'set'))) { throw new DomainException('Остаток не сохранён: '.$id); }
        }
        $book=gor_hold_book(); unset($book[$order->get_id()]);
        if (!update_option('gor_stock_holds', $book, false) && gor_hold_book()!==$book) { throw new DomainException('Не удалось закрыть резерв.'); }
        $order->update_meta_data('_gor_stock_synced', $state['effect']['key']); $order->save_meta_data();
    } finally { delete_option('gor_stock_lock'); }
}
