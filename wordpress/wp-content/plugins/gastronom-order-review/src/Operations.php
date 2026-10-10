<?php
declare(strict_types=1);
use Gastronom\OrderReview\Review;

function gor_finance_row(WC_Order $order): array {
    $s=gor_state($order);
    return ['order_id'=>$order->get_id(),'state'=>$s['status'],'revision'=>$s['revision'],
        'original_cents'=>Review::total($s['original']),
        'approved_cents'=>in_array($s['status'], ['approved','cancelled'], true) ? Review::total($s['approved_quote']) : null,
        'received_cents'=>$s['paid']+$s['additional_paid'],'refunded_cents'=>$s['refunded'],
        'balance_cents'=>Review::balance($s),'dispatch_date'=>$s['dispatch_date'],
        'receipt'=>(string)($s['effect']['reference'] ?? ''),
        'bank_operations'=>array_values(array_filter($s['history'], static function ($h): bool { return in_array($h['action'], ['payment','refund'], true); }))];
}

/** Recovery never permits a new POS create after an uncertain send. It only restores access to reconciliation. */
function gor_recover_order(WC_Order $order, string $evidence): void {
    if (trim($evidence)==='') { throw new DomainException('Укажите подтверждение завершения зависшего PHP-запроса и результат проверки журнала.'); }
    $lock='_gor_lock_'.$order->get_id();
    $started=get_option($lock, false);
    if ($started!==false && time()-(int)$started<600) { throw new DomainException('Операция запущена менее 10 минут назад.'); }
    if ($started===false && !add_option($lock,time(),'',false)) { throw new DomainException('Операция уже занята.'); }
    // Operator has explicitly verified the old worker is no longer running; no automatic lock expiry.
    $s=gor_state($order);
    if ($s['status']==='executing') { $s=Review::complete($s, false, 'Восстановлен доступ к сверке после прерывания запроса.'); }
    $s['history'][]=['action'=>'operator_recovery','actor'=>(string)get_current_user_id(),'date'=>current_time('mysql'),'revision'=>$s['revision'],'reference'=>$evidence];
    $s['version']++; gor_save($order,$s); delete_option($lock);
}

function gor_resolve_mail(WC_Order $order, string $key, string $result, string $evidence): void {
    if (!in_array($result,['sent','not-sent'],true) || trim($evidence)==='') { throw new DomainException('Укажите результат проверки почтового журнала и подтверждение.'); }
    $lock='_gor_lock_'.$order->get_id();
    if (!add_option($lock,time(),'',false)) { throw new DomainException('Заказ занят. Сначала завершите или восстановите обработчик.'); }
    try {
        $order->read_meta_data(true); $s=gor_state($order);
        if (($s['mail'][$key]['status'] ?? '')!=='sending') { throw new DomainException('Письмо не находится в неопределённом состоянии.'); }
        $s['mail'][$key]['status']=$result==='sent' ? 'sent' : 'failed';
        $s['mail'][$key]['operator_evidence']=$evidence;
        $s['mail'][$key]['at']=current_time('mysql');
        $s['version']++; gor_save($order,$s);
    } finally { delete_option($lock); }
}

if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('gastronom-review export', static function ($args, $flags): void {
        $from=(string)($flags['from'] ?? ''); $to=(string)($flags['to'] ?? '');
        foreach ([$from,$to] as $date) { if (!preg_match('/^\d{4}-\d{2}-\d{2}$/D',$date)) { WP_CLI::error('Use --from=YYYY-MM-DD --to=YYYY-MM-DD'); } }
        if ($from>$to) { WP_CLI::error('Invalid date interval'); }
        // JSON avoids spreadsheet formulas and retains separate paid/refunded figures.
        $page=1;
        do {
            $batch=wc_get_orders(['limit'=>100,'page'=>$page++,'paginate'=>true,'date_created'=>$from.'...'.$to]);
            foreach ($batch->orders as $order) {
                if (gor_managed($order)) { WP_CLI::line(wp_json_encode(gor_finance_row($order), JSON_UNESCAPED_UNICODE)); }
            }
        } while ($page<=$batch->max_num_pages);
    });
    WP_CLI::add_command('gastronom-review recover', static function ($args, $flags): void {
        $order=wc_get_order((int)($args[0] ?? 0));
        if (!$order || !gor_managed($order)) { WP_CLI::error('Managed order required'); }
        try { gor_recover_order($order, (string)($flags['evidence'] ?? '')); }
        catch (DomainException $e) { WP_CLI::error($e->getMessage()); }
        WP_CLI::success('Доступ к сверке восстановлен. Никаких внешних операций не выполнено.');
    });
    WP_CLI::add_command('gastronom-review mail-result', static function ($args,$flags): void {
        $order=wc_get_order((int)($args[0] ?? 0));
        if (!$order || !gor_managed($order)) { WP_CLI::error('Managed order required'); }
        try { gor_resolve_mail($order,(string)($args[1] ?? ''),(string)($flags['result'] ?? ''),(string)($flags['evidence'] ?? '')); }
        catch (DomainException $e) { WP_CLI::error($e->getMessage()); }
        WP_CLI::success('Результат сверки записан; письмо не отправлялось.');
    });
}
