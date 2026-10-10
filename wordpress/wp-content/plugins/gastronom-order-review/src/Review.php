<?php
declare(strict_types=1);

namespace Gastronom\OrderReview;

/** Pure workflow. No WordPress, network, clock, or payment side effects. */
final class Review
{
    public static function money($value): int
    {
        $value = str_replace(',', '.', trim((string) $value));
        if (!preg_match('/^\d{1,7}(?:\.\d{1,2})?$/D', $value)) {
            throw new \DomainException('Укажите неотрицательную сумму с точностью до цента.');
        }
        [$whole, $fraction] = array_pad(explode('.', $value), 2, '');
        return (int) $whole * 100 + (int) str_pad($fraction, 2, '0');
    }

    public static function quantity($value): int
    {
        $value = str_replace(',', '.', trim((string) $value));
        if (!preg_match('/^\d{1,5}(?:\.\d{1,3})?$/D', $value)) {
            throw new \DomainException('Количество или вес: не более трёх знаков после запятой.');
        }
        [$whole, $fraction] = array_pad(explode('.', $value), 2, '');
        $qty = (int) $whole * 1000 + (int) str_pad($fraction, 3, '0');
        if ($qty <= 0) {
            throw new \DomainException('Количество должно быть больше нуля. Для удаления используйте «Удалить».');
        }
        return $qty;
    }

    public static function total(array $quote): int
    {
        return array_sum(array_column($quote['lines'], 'total')) + $quote['shipping'] + $quote['fees'];
    }

    public static function balance(array $state): int
    {
        return self::total($state['quote']) - ($state['paid'] + $state['additional_paid'] - $state['refunded']);
    }

    public static function create(array $original, int $paid, string $language, bool $courier): array
    {
        if (!$original['lines'] || $paid < 0) {
            throw new \DomainException('Заказ должен содержать товары и корректную сумму оплаты.');
        }
        return [
            'schema' => 1, 'version' => 1, 'revision' => 0, 'status' => 'pending',
            'original' => $original, 'quote' => $original, 'paid' => $paid,
            'additional_paid' => 0, 'refunded' => 0, 'language' => $language === 'ru' ? 'ru' : 'sk',
            'courier' => $courier, 'dispatch_date' => '', 'sent_revision' => null,
            'consent_revision' => null, 'consent_note' => '', 'iban' => '', 'holder' => '',
            'bank_consent' => false, 'mail' => [], 'history' => [], 'effect' => null,
            'reason' => '', 'cause' => 'absence', 'comment' => '', 'approved_quote' => null,
        ];
    }

    public static function iban(string $value): string
    {
        $value = strtoupper(preg_replace('/\s+/', '', $value));
        $lengths = ['AD'=>24,'AE'=>23,'AL'=>28,'AT'=>20,'AZ'=>28,'BA'=>20,'BE'=>16,'BG'=>22,'BH'=>22,'BR'=>29,'BY'=>28,'CH'=>21,'CR'=>22,'CY'=>28,'CZ'=>24,'DE'=>22,'DK'=>18,'DO'=>28,'EE'=>20,'EG'=>29,'ES'=>24,'FI'=>18,'FO'=>18,'FR'=>27,'GB'=>22,'GE'=>22,'GI'=>23,'GL'=>18,'GR'=>27,'GT'=>28,'HR'=>21,'HU'=>28,'IE'=>22,'IL'=>23,'IS'=>26,'IT'=>27,'JO'=>30,'KW'=>30,'KZ'=>20,'LB'=>28,'LC'=>32,'LI'=>21,'LT'=>20,'LU'=>20,'LV'=>21,'MC'=>27,'MD'=>24,'ME'=>22,'MK'=>19,'MR'=>27,'MT'=>31,'MU'=>30,'NL'=>18,'NO'=>15,'PK'=>24,'PL'=>28,'PS'=>29,'PT'=>25,'QA'=>29,'RO'=>24,'RS'=>22,'SA'=>24,'SC'=>31,'SE'=>24,'SI'=>19,'SK'=>24,'SM'=>27,'ST'=>25,'SV'=>28,'TL'=>23,'TN'=>24,'TR'=>26,'UA'=>29,'VA'=>22,'VG'=>24,'XK'=>20];
        if (!preg_match('/^[A-Z]{2}\d{2}[A-Z0-9]+$/D', $value) || strlen($value) !== ($lengths[substr($value, 0, 2)] ?? 0)) {
            throw new \DomainException('Проверьте IBAN: страна или длина неверны.');
        }
        $rotated = substr($value, 4) . substr($value, 0, 4);
        $remainder = 0;
        foreach (str_split($rotated) as $char) {
            foreach (str_split(ctype_digit($char) ? $char : (string) (ord($char) - 55)) as $digit) {
                $remainder = ($remainder * 10 + (int) $digit) % 97;
            }
        }
        if ($remainder !== 1) {
            throw new \DomainException('Контрольная сумма IBAN неверна.');
        }
        return $value;
    }

    public static function command(array $s, string $action, array $input, string $today, string $actor): array
    {
        if ((int) ($input['version'] ?? 0) !== $s['version']) {
            throw new \DomainException('Заказ уже изменён. Обновите страницу.');
        }
        $editable = in_array($s['status'], ['pending', 'editing', 'awaiting', 'agreed'], true);
        switch ($action) {
            case 'weight':
                if ($s['status'] !== 'pending') { throw new \DomainException('Вес уточняется до согласования и утверждения.'); }
                foreach ($s['quote']['lines'] as &$line) {
                    if ($line['unit'] !== 'kg') { continue; }
                    $qty = self::quantity($input['weights'][$line['key']] ?? '');
                    $min = (float) ($line['weight_min_kg'] ?? 0);
                    $max = (float) ($line['weight_max_kg'] ?? 0);
                    $units = (float) ($line['ordered_units'] ?? 1);
                    if ($min <= 0 || $max < $min) { throw new \DomainException('В заказе не сохранён диапазон веса товара. Проверьте данные товара.'); }
                    if ($qty < (int) round($min * $units * 1000) || $qty > (int) round($max * $units * 1000)) { throw new \DomainException('Фактический вес выходит за диапазон, указанный покупателю: ' . ($min*$units) . '–' . ($max*$units) . ' кг.'); }
                    $line['qty'] = $qty;
                    $line['total'] = (int) round($qty * $line['rate'] / 1000, 0, PHP_ROUND_HALF_UP);
                    $line['weight_pending'] = false;
                }
                unset($line);
                $s['revision']++;
                $date = (string) ($input['dispatch_date'] ?? '');
                $parsed = \DateTimeImmutable::createFromFormat('!Y-m-d', $date);
                if ($parsed && $parsed->format('Y-m-d') === $date && $date >= $today) { $s['dispatch_date'] = $date; }
                break;
            case 'edit':
                if (!$editable) { throw new \DomainException('Утверждённый состав заблокирован.'); }
                $s['status'] = 'editing';
                $s['consent_revision'] = null;
                break;
            case 'save':
                if ($s['status'] !== 'editing') { throw new \DomainException('Сначала нажмите «Изменить».'); }
                $quote = $input['quote'];
                if (!$quote['lines']) { $quote['shipping']=0; $quote['fees']=0; }
                if (count($quote['lines']) > 100) { throw new \DomainException('Слишком много позиций.'); }
                foreach ($quote['lines'] as $line) {
                    if (!is_int($line['qty']) || $line['qty'] <= 0 || !is_int($line['total']) || $line['total'] < 0 || empty($line['product_id'])) {
                        throw new \DomainException('Некорректная позиция заказа.');
                    }
                }
                foreach (['shipping', 'fees'] as $field) {
                    if (!is_int($quote[$field]) || $quote[$field] < 0) { throw new \DomainException('Некорректный итог.'); }
                }
                // Shipping/fees retain the original deal; no surprise repricing during an absence correction.
                if ($quote['lines'] && ($quote['shipping'] !== $s['original']['shipping'] || $quote['fees'] !== $s['original']['fees'])) {
                    throw new \DomainException('Доставка и сборы сохраняются по исходному заказу.');
                }
                $s['quote'] = $quote;
                $s['revision']++;
                $s['consent_revision'] = null;
                $s['reason'] = trim((string) ($input['reason'] ?? ''));
                $s['cause'] = in_array($input['cause'] ?? '', ['absence','weight','customer'], true) ? $input['cause'] : 'absence';
                $s['comment'] = trim((string) ($input['comment'] ?? ''));
                break;
            case 'proposal':
                if ($s['status'] !== 'editing' || $s['revision'] === 0 || $s['reason'] === '') {
                    throw new \DomainException('Сохраните состав и укажите отсутствующие товары.');
                }
                $s['sent_revision'] = $s['revision'];
                $s['consent_revision'] = null;
                $s['status'] = 'awaiting';
                // Mail is an outbox entry; the delivery adapter never changes composition.
                $s['mail']['proposal-' . $s['revision']] = ['status' => 'pending', 'quote' => $s['quote'], 'attempts' => 0];
                break;
            case 'agree':
                if ($s['status'] !== 'awaiting' || $s['sent_revision'] !== $s['revision'] || ($s['mail']['proposal-' . $s['revision']]['status'] ?? '') !== 'sent') {
                    throw new \DomainException('Сначала отправьте актуальное предложение клиенту.');
                }
                if (trim((string) ($input['consent_note'] ?? '')) === '') { throw new \DomainException('Укажите способ получения согласия клиента.'); }
                $s['consent_revision'] = $s['revision'];
                $s['consent_note'] = trim($input['consent_note']);
                $s['consent_at'] = (string) ($input['consent_at'] ?? $today);
                $s['status'] = 'agreed';
                break;
            case 'cancel_edit':
                if (!$editable) { throw new \DomainException('Утверждённый состав заблокирован.'); }
                $s['quote'] = $s['original'];
                $s['status'] = $s['sent_revision'] === null ? 'pending' : 'editing';
                $s['revision']++;
                $s['consent_revision'] = null;
                $s['reason'] = $s['comment'] = '';
                $s['cause'] = 'customer';
                break;
            case 'bank':
                if (self::balance($s) >= 0) { throw new \DomainException('Возврат по текущему расчёту не требуется.'); }
                $s['iban'] = self::iban((string) ($input['iban'] ?? ''));
                $s['holder'] = trim((string) ($input['holder'] ?? ''));
                $s['bank_consent'] = !empty($input['bank_consent']);
                if (!$s['bank_consent'] || $s['holder'] === '') { throw new \DomainException('Нужны владелец счёта и согласие клиента на банковский возврат.'); }
                break;
            case 'refund':
                if (!in_array($s['status'], ['approved','cancelled'], true) || !$s['bank_consent'] || !$s['iban'] || trim((string) ($input['reference'] ?? '')) === '') {
                    throw new \DomainException('Сначала утвердите заказ, сохраните реквизиты и укажите банковскую ссылку выполненного перевода.');
                }
                $amount = self::money($input['amount'] ?? '');
                self::uniqueReference($s, 'refund', (string) $input['reference']);
                if ($amount <= 0 || $amount > -self::balance($s)) { throw new \DomainException('Возврат превышает остаток к возврату.'); }
                $s['refunded'] += $amount;
                break;
            case 'payment':
                if (!in_array($s['status'], ['pending', 'agreed'], true) || trim((string) ($input['reference'] ?? '')) === '') {
                    throw new \DomainException('Доплату отмечают для готового состава с подтверждением платежа.');
                }
                $amount = self::money($input['amount'] ?? '');
                self::uniqueReference($s, 'payment', (string) $input['reference']);
                if ($amount <= 0 || $amount > self::balance($s)) { throw new \DomainException('Проверьте остаток к оплате.'); }
                $s['additional_paid'] += $amount;
                break;
            case 'approve':
                if (!in_array($s['status'], ['pending', 'agreed'], true)) { throw new \DomainException('Состав ещё не согласован либо уже утверждён.'); }
                if ($s['status'] === 'agreed' && $s['consent_revision'] !== $s['revision']) { throw new \DomainException('Согласие относится к другой версии.'); }
                foreach ($s['quote']['lines'] as $line) {
                    if (!empty($line['weight_pending'])) { throw new \DomainException('Сначала укажите фактический вес всех весовых товаров.'); }
                }
                $date = (string) ($input['dispatch_date'] ?? '');
                if ($s['courier'] && $s['quote']['lines']) {
                    $parsed = \DateTimeImmutable::createFromFormat('!Y-m-d', $date);
                    if (!$parsed || $parsed->format('Y-m-d') !== $date || $date < $today) { throw new \DomainException('Укажите дату передачи курьеру не ранее сегодняшней.'); }
                }
                if (empty($input['cod']) && self::balance($s) > 0) { throw new \DomainException('Предоплата не покрывает окончательную сумму. Сначала отметьте поступление денег.'); }
                $s['dispatch_date'] = $s['courier'] && $s['quote']['lines'] ? $date : '';
                $s['approved_quote'] = $s['quote'];
                $s['status'] = 'executing';
                $s['effect'] = ['key' => 'review-' . $input['order_id'] . '-r' . $s['revision'], 'status' => 'pending'];
                break;
            case 'retry':
                if ($s['status'] !== 'error') { throw new \DomainException('Повтор доступен только для незавершённого выполнения.'); }
                $s['status'] = 'executing';
                break;
            default: throw new \DomainException('Неизвестное действие.');
        }
        $s['version']++;
        $s['history'][] = ['action' => $action, 'actor' => $actor, 'date' => $today, 'revision' => $s['revision'], 'quote' => $s['quote'], 'reference' => (string) ($input['reference'] ?? ''), 'amount' => $amount ?? 0];
        return $s;
    }

    private static function uniqueReference(array $s, string $action, string $reference): void
    {
        foreach ($s['history'] as $entry) {
            if ($entry['action'] === $action && trim($entry['reference']) === trim($reference)) {
                throw new \DomainException('Эта банковская операция уже учтена.');
            }
        }
    }

    public static function complete(array $s, bool $ok, string $reference): array
    {
        if ($s['status'] !== 'executing') { throw new \DomainException('Нет запущенной операции.'); }
        $s['status'] = $ok ? ($s['approved_quote']['lines'] ? 'approved' : 'cancelled') : 'error';
        $s['effect']['status'] = $ok ? 'done' : 'error';
        $s['effect']['reference'] = $reference;
        if ($ok) { $s['mail']['confirmation'] = ['status' => 'pending', 'quote' => $s['approved_quote'], 'attempts' => 0]; }
        $s['version']++;
        return $s;
    }
}
