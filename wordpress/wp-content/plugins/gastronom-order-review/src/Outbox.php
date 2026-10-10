<?php
declare(strict_types=1);
namespace Gastronom\OrderReview;

final class Outbox
{
    public static function deliver(array $entry, callable $save, callable $transport): array
    {
        if (($entry['status'] ?? '')==='sent') { return $entry; }
        if (($entry['status'] ?? '')==='sending') { throw new \DomainException('Результат отправки письма неизвестен. Проверьте почтовый журнал перед повтором.'); }
        if (empty($entry['to']) || empty($entry['reply_to']) || empty($entry['message']['html'])) { throw new \DomainException('Не заполнены адреса или текст письма.'); }
        $entry['attempts']++;
        $entry['status']='sending';
        $save($entry);
        try {
            $ok=$transport($entry);
            $entry['status']=$ok===true ? 'sent' : 'failed';
            $entry['error']=$ok===true ? '' : 'Почтовый транспорт не принял письмо.';
        } catch (\Throwable $e) {
            // A thrown transport may already have accepted the message. Preserve uncertainty.
            $entry['error']=$e->getMessage();
        }
        $save($entry);
        return $entry;
    }

    /** Available for the reviewed release wiring; never selected in this isolated build. */
    public static function wordpress(array $entry): bool
    {
        return wp_mail($entry['to'], $entry['message']['subject'], $entry['message']['html'],
            ['Content-Type: text/html; charset=UTF-8', 'Reply-To: '.$entry['reply_to']]);
    }
}
