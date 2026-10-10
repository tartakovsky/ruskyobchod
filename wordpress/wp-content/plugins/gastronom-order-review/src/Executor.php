<?php
declare(strict_types=1);
namespace Gastronom\OrderReview;

/** Persist-before-send executor. The caller holds the order lock throughout this call. */
final class Executor
{
    public static function run(array $plan, array $journal, callable $save, callable $lookup, callable $send): array
    {
        $hash = hash('sha256', json_encode($plan, JSON_THROW_ON_ERROR));
        if ($journal && ($journal['hash'] ?? '') !== $hash) {
            throw new \DomainException('Состав операции изменился. Повторное выполнение заблокировано.');
        }
        if (($journal['status'] ?? '') === 'done') { return $journal; }
        // Reconcile even on first submission: a local journal may have been restored from backup.
        $matches = $lookup($plan['receipt']['external-id']);
        if (!is_array($matches)) { throw new \DomainException('Не удалось сверить чек с кассой.'); }
        if (count($matches) > 1) { throw new \DomainException('Найдены несколько чеков. Нужна ручная сверка кассы.'); }
        if ($matches) {
            self::validate($matches[0], $plan);
            $journal = ['hash'=>$hash,'status'=>'done','receipt_id'=>(string)$matches[0]['id'],'reconciled'=>true,'completed_at'=>$matches[0]['completed_at'] ?? 0];
            $save($journal);
            return $journal;
        }
        if (in_array($journal['status'] ?? '', ['sending','uncertain'], true)) {
            throw new \DomainException('Результат кассы пока неизвестен. Выполнена только сверка; повторная продажа заблокирована. Проверьте кассу и дождитесь синхронизации.');
        }
        $journal = ['hash'=>$hash,'status'=>'sending','external_id'=>$plan['receipt']['external-id']];
        $save($journal); // A crash from this point onwards must NEVER lead to an automatic resend.
        try {
            $receipt = $send($plan);
            self::validate($receipt, $plan);
        } catch (\Throwable $e) {
            $journal['status'] = 'uncertain';
            $journal['error'] = $e->getMessage();
            $save($journal);
            throw new \DomainException('Ответ кассы требует сверки: ' . $e->getMessage(), 0, $e);
        }
        $journal['status'] = 'done';
        $journal['receipt_id'] = (string)$receipt['id'];
        $journal['reconciled'] = false;
        $journal['completed_at'] = $receipt['completed_at'] ?? 0;
        $save($journal);
        return $journal;
    }

    /** Transport returns normalized, independently verified receipt facts, never guessed defaults. */
    public static function validate(array $receipt, array $plan): void
    {
        if (empty($receipt['id']) || ($receipt['external_id'] ?? '') !== $plan['receipt']['external-id']
            || ($receipt['paid'] ?? false) !== true || ($receipt['fiscalized'] ?? false) !== true
            || ($receipt['cancelled'] ?? true) !== false || ($receipt['total_cents'] ?? null) !== $plan['total_cents']) {
            throw new \DomainException('Чек не подтверждает фискализацию согласованной суммы.');
        }
        $expected = $plan['stock_milliunits'];
        $actual = $receipt['stock_milliunits'] ?? [];
        ksort($expected); ksort($actual);
        if ($expected !== $actual) { throw new \DomainException('Состав чека отличается от утверждённого заказа.'); }
    }
}
