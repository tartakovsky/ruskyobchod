<?php
declare(strict_types=1);
namespace Gastronom\OrderReview;

/** Logical holds, distinct from POS deductions. Caller serializes access to the book. */
final class Reservations
{
    public static function reserve(array $book, string $order, array $quote, array $physical): array
    {
        $required = [];
        foreach ($quote['lines'] as $line) {
            if (!empty($line['weight_pending'])) {
                throw new \DomainException('Для точного резерва нужен фактический вес.');
            }
            $key = $line['product_id'] . ':' . $line['unit'];
            $required[$key] = ($required[$key] ?? 0) + $line['qty'];
        }
        $others = [];
        foreach ($book as $id => $hold) {
            if ((string) $id === $order) { continue; }
            foreach ($hold as $key => $qty) { $others[$key] = ($others[$key] ?? 0) + $qty; }
        }
        foreach ($required as $key => $qty) {
            if ($qty > ($physical[$key] ?? 0) - ($others[$key] ?? 0)) {
                throw new \DomainException('Недостаточно доступного остатка для резерва товара ' . $key);
            }
        }
        $book[$order] = $required;
        return $book;
    }

    public static function release(array $book, string $order): array
    {
        unset($book[$order]);
        return $book;
    }
}
