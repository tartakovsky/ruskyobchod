<?php
declare(strict_types=1);
use Gastronom\OrderReview\Executor;
use Gastronom\OrderReview\Fulfillment;

function gor_plan(WC_Order $order, array $state): array {
    $saved=$order->get_meta('_gor_plan', true);
    if (is_array($saved)) {
        if (($saved['key'] ?? '') !== ($state['effect']['key'] ?? null)) { throw new DomainException('План исполнения относится к другой версии.'); }
        return $saved;
    }
    return Fulfillment::plan($state, $order->get_id(), $order->get_payment_method() === 'cod', static function ($id): array {
        $p = wc_get_product($id);
        return ['dotypos_id'=>$p && class_exists('Dotypos') ? $p->get_meta(Dotypos::$keys['product']['field-id']) : 0];
    });
}

function gor_execute(WC_Order $order, array $state): array {
    $plan = gor_plan($order, $state);
    $journal = $order->get_meta('_gor_execution', true);
    $save = static function (array $value) use ($order): void {
        $order->update_meta_data('_gor_execution', $value);
        $order->save_meta_data();
    };
    if (!gor_is_sandbox()) {
        $transport=gor_live_transport($order);
        return Executor::run($plan, is_array($journal) ? $journal : [], $save, [$transport,'lookup'], [$transport,'send']);
    }
    // Isolated builds select only the in-memory test transport.
    $lookup = static function ($externalId) use ($order): array {
        if (get_option('gor_sandbox_fail_effect', false)) { throw new DomainException('Тестовая касса недоступна. Повторите сверку.'); }
        $effects = $order->get_meta('_gor_sandbox_effects', true);
        $matches = [];
        foreach (is_array($effects) ? $effects : [] as $entry) {
            if (($entry['receipt']['external_id'] ?? '') === $externalId) { $matches[] = $entry['receipt']; }
        }
        return $matches;
    };
    $send = static function (array $plan) use ($order, $state): array {
        $receipt = ['id'=>'sandbox:'.$plan['key'], 'external_id'=>$plan['receipt']['external-id'],
            'paid'=>true,'fiscalized'=>true,'cancelled'=>false,'total_cents'=>$plan['total_cents'],'stock_milliunits'=>$plan['stock_milliunits']];
        $effects = $order->get_meta('_gor_sandbox_effects', true);
        $effects = is_array($effects) ? $effects : [];
        $effects[$plan['key']] = ['kind'=>'fiscal_receipt_with_stock','plan'=>$plan,'quote'=>$state['approved_quote'],
            'total'=>$plan['total_cents'],'receipt'=>$receipt,'date'=>current_time('mysql')];
        $order->update_meta_data('_gor_sandbox_effects', $effects);
        $order->save_meta_data();
        if (get_option('gor_sandbox_lost_response', false)) { throw new DomainException('Тест: ответ потерян после проведения чека.'); }
        return $receipt;
    };
    return Executor::run($plan, is_array($journal) ? $journal : [], $save, $lookup, $send);
}
