<?php
declare(strict_types=1);
namespace Gastronom\OrderReview;

/** API v2 adapter. Inject authenticated GET/POST; the review build injects only local doubles. */
final class DotyposTransport
{
    private $get;
    private $post;
    private int $branch;
    private int $paymentMethod;

    public function __construct(callable $get, callable $post, int $branch, int $paymentMethod)
    {
        if ($branch <= 0 || $paymentMethod <= 0) { throw new \DomainException('Не настроена касса или способ оплаты.'); }
        $this->get=$get; $this->post=$post; $this->branch=$branch; $this->paymentMethod=$paymentMethod;
    }

    public function lookup(string $externalId): array
    {
        $body = ($this->get)('/orders?limit=100&include=orderItems&filter='.rawurlencode('externalId|eq|'.$externalId));
        if (!isset($body['data']) || !is_array($body['data'])) { throw new \DomainException('Некорректный ответ сверки Dotypos.'); }
        $result=[];
        foreach ($body['data'] as $row) {
            if (($row['externalId'] ?? '') !== $externalId) { throw new \DomainException('Касса вернула неподходящий номер операции.'); }
            if ((int)($row['_branchId'] ?? 0) !== $this->branch || ($row['currency'] ?? '') !== 'EUR') {
                throw new \DomainException('Чек относится к другой кассе или валюте.');
            }
            $stock=[];
            if (!isset($row['orderItems']) || !is_array($row['orderItems'])) { throw new \DomainException('Состав чека не получен.'); }
            foreach ($row['orderItems'] as $line) {
                if (!empty($line['canceledDate'])) { continue; }
                if (($line['stockDeduct'] ?? false) !== true || !isset($line['_productId'], $line['quantity'])) {
                    throw new \DomainException('Касса не подтверждает списание товара.');
                }
                $id=(int)$line['_productId'];
                $stock[$id]=($stock[$id] ?? 0)+(int)round((float)$line['quantity']*1000);
            }
            $flags=(int)($row['flags'] ?? 0);
            $result[]=['id'=>$row['id'] ?? '', 'external_id'=>$externalId,'paid'=>($row['paid'] ?? false) === true,
                'fiscalized'=>($flags & (1<<29)) !== 0 && ($flags & ((1<<13)|(1<<5))) === 0,
                'cancelled'=>!empty($row['canceledDate']) || ($flags & 3) !== 0,
                'total_cents'=>isset($row['totalValueRounded']) ? (int)round((float)$row['totalValueRounded']*100) : null,
                'stock_milliunits'=>$stock, 'completed_at'=>self::timestamp($row['completed'] ?? null)];
        }
        return $result;
    }

    public static function timestamp($value): int
    {
        if (is_numeric($value)) { return (int)floor((float)$value > 100000000000 ? (float)$value/1000 : (float)$value); }
        return is_string($value) ? (int)strtotime($value) : 0;
    }

    public function send(array $plan): array
    {
        $payload=$plan['receipt'];
        $payload['validity']=(time()+120)*1000;
        $payload['payment-method-id']=$this->paymentMethod;
        // Even an error response may mean a fiscalized sale (e.g. printer failure).
        // The authoritative receipt read is therefore mandatory; no speculative stock fallback.
        ($this->post)('/branches/'.$this->branch.'/pos-actions', $payload);
        $matches=$this->lookup($payload['external-id']);
        if (count($matches)!==1) { throw new \DomainException('Ожидается синхронизация результата кассы.'); }
        return $matches[0];
    }
}
