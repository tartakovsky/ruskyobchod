<?php
declare(strict_types=1);
namespace Gastronom\OrderReview;

final class Mail
{
    public static function amount(int $cents): string { return number_format($cents / 100, 2, ',', ' ') . ' €'; }
    public static function escape(string $text): string { return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
    public static function table(array $quote, string $lang): string
    {
        $ru = $lang === 'ru';
        $html = '<table style="width:100%;border-collapse:collapse" cellpadding="8"><thead><tr><th align="left">' . ($ru ? 'Товар' : 'Tovar') . '</th><th>' . ($ru ? 'Количество / вес' : 'Množstvo / hmotnosť') . '</th><th>' . ($ru ? 'Стоимость' : 'Cena') . '</th></tr></thead><tbody>';
        foreach ($quote['lines'] as $line) {
            $html .= '<tr><td>' . self::escape($line['name']) . '</td><td>' . self::escape(rtrim(rtrim(number_format($line['qty'] / 1000, 3, ',', ''), '0'), ',') . ' ' . ($line['unit'] === 'kg' && empty($line['weight_pending']) ? ($ru ? 'кг' : 'kg') : ($ru ? 'шт.' : 'ks'))) . (!empty($line['weight_pending']) ? ($ru ? ' (вес будет уточнён)' : ' (hmotnosť bude upresnená)') : '') . '</td><td>' . self::amount($line['total']) . '</td></tr>';
        }
        foreach ([$ru ? 'Доставка' : 'Doprava' => $quote['shipping'], $ru ? 'Сборы' : 'Poplatky' => $quote['fees']] as $label => $cents) {
            if ($cents === 0 && $label === ($ru ? 'Сборы' : 'Poplatky')) { continue; }
            $html .= '<tr><td colspan="2">' . $label . '</td><td>' . self::amount($cents) . '</td></tr>';
        }
        return $html . '<tr><th colspan="2" align="left">' . ($ru ? 'Итого, включая налоги' : 'Spolu vrátane daní') . '</th><th>' . self::amount(Review::total($quote)) . '</th></tr></tbody></table>';
    }

    public static function variant(array $s): string
    {
        $shape = static function (array $q): array {
            $result = [];
            foreach ($q['lines'] as $l) {
                $id = (string) $l['product_id'];
                $result[$id][] = [$l['unit'], $l['unit'] === 'kg' ? null : $l['qty'], $l['rate']];
            }
            ksort($result);
            foreach ($result as &$rows) { sort($rows); }
            return $result;
        };
        $changed = $shape($s['original']) !== $shape($s['quote']);
        $weighted = (bool) array_filter($s['quote']['lines'], static fn($l) => $l['unit'] === 'kg' && empty($l['weight_pending']));
        return $changed ? ($weighted ? 'replacement_weight' : 'replacement') : ($weighted ? 'weight' : 'normal');
    }

    private static function quantities(array $quote): array
    {
        $rows = [];
        foreach ($quote['lines'] as $line) {
            $key = $line['product_id'];
            if (!isset($rows[$key])) { $rows[$key] = $line; $rows[$key]['qty'] = 0; $rows[$key]['total'] = 0; }
            $rows[$key]['qty'] += $line['qty'];
            $rows[$key]['total'] += $line['total'];
        }
        return $rows;
    }

    private static function quantity(int $qty, array $line, bool $ru): string
    {
        return rtrim(rtrim(number_format($qty / 1000, 3, ',', ''), '0'), ',') . ' '
            . ($line['unit'] === 'kg' && empty($line['weight_pending']) ? ($ru ? 'кг' : 'kg') : ($ru ? 'шт.' : 'ks'));
    }

    /** Explain the actual proposal, including partial shortages; never infer shortages from a pack's actual weight. */
    public static function changes(array $s): string
    {
        $ru = $s['language'] === 'ru';
        $original = self::quantities($s['original']); $proposed = self::quantities($s['quote']);
        $rows = ''; $shortage = false;
        foreach ($original as $id => $old) {
            $new = $proposed[$id] ?? null;
            if ($new && (!empty($old['weight_pending']) || $old['unit'] !== $new['unit'])) { continue; }
            $qty = $new['qty'] ?? 0;
            if ($qty === $old['qty'] && $new && $new['total'] === $old['total']) { continue; }
            $missing = max(0, $old['qty'] - $qty);
            $absence = ($s['cause'] ?? 'absence') === 'absence' && $missing > 0;
            $shortage = $shortage || $absence;
            $rows .= '<li><strong>'.self::escape($old['name']).'</strong>: '
                .($ru ? 'заказано ' : 'objednané ').self::quantity($old['qty'], $old, $ru)
                .($ru ? '; предлагаем ' : '; navrhujeme ').self::quantity($qty, $old, $ru)
                .($absence ? ($ru ? '; не хватает ' : '; chýba ').self::quantity($missing, $old, $ru) : '')
                .($new && $new['total'] !== $old['total'] ? '; '.self::amount($old['total']).' → '.self::amount($new['total']) : '').'</li>';
        }
        foreach ($proposed as $id => $new) {
            if (isset($original[$id])) { continue; }
            $rows .= '<li>'.($ru ? 'Предлагаем добавить: ' : 'Navrhujeme pridať: ').'<strong>'.self::escape($new['name']).'</strong> — '.self::quantity($new['qty'], $new, $ru).', '.self::amount($new['total']).'.</li>';
        }
        $intro = $shortage
            ? ($ru ? 'К сожалению, выбранных товаров недостаточно для выполнения заказа в полном объёме. Приносим извинения. Предлагаем следующие изменения:' : 'Žiaľ, vybraných produktov nemáme dostatok na vybavenie celej objednávky. Ospravedlňujeme sa. Navrhujeme tieto zmeny:')
            : (($s['cause'] ?? '') === 'customer'
                ? ($ru ? 'Мы подготовили изменения по вашей просьбе:' : 'Pripravili sme zmeny podľa vašej požiadavky:')
                : ($ru ? 'Предлагаем согласовать следующие изменения заказа:' : 'Prosíme o odsúhlasenie týchto zmien objednávky:'));
        return '<p>'.$intro.'</p>'.($rows ? '<ul>'.$rows.'</ul>' : '');
    }

    public static function build(array $s, array $customer, string $kind): array
    {
        $ru = $s['language'] === 'ru';
        $proposal = $kind === 'proposal';
        $subject = ($proposal ? ($ru ? 'Уточнение состава заказа №' : 'Upresnenie objednávky č. ') : ($ru ? 'Подтверждение заказа №' : 'Potvrdenie objednávky č. ')) . $customer['number'];
        if (!$proposal && !$s['quote']['lines']) { $subject=($ru ? 'Отмена заказа №' : 'Zrušenie objednávky č. ').$customer['number']; }
        $body = '<p>' . ($ru ? 'Здравствуйте' : 'Dobrý deň') . ($customer['name'] !== '' ? ', ' . self::escape($customer['name']) : '') . '!</p>';
        $variant = self::variant($s);
        $weighted = in_array($variant, ['weight','replacement_weight'], true);
        if ($proposal) {
            $body .= self::changes($s);
            if ($weighted) { $body .= '<p>'.($ru ? 'Также мы уточнили фактический вес весовых товаров. Новый итог учитывает изменения состава и уточнённый вес.' : 'Zároveň sme upresnili skutočnú hmotnosť váženého tovaru. Nová suma zohľadňuje zmeny zloženia aj upresnenú hmotnosť.').'</p>'; }
            $body .= '<p><strong>'.($ru ? 'Стоимость: ' : 'Cena: ').self::amount(Review::total($s['original'])).' → '.self::amount(Review::total($s['quote'])).'</strong></p>';
            $body .= '<h3>'.($ru ? 'Новый состав заказа' : 'Nové zloženie objednávky').'</h3>';
        } elseif ($s['quote']['lines']) {
            if ($variant === 'normal') { $intro = $ru ? 'Спасибо за ваш заказ! Подтверждаем его состав и стоимость.' : 'Ďakujeme za vašu objednávku! Potvrdzujeme jej zloženie a cenu.'; }
            elseif ($variant === 'weight') { $intro = $ru ? 'Спасибо за ваш заказ! Мы уточнили фактический вес товаров и пересчитали стоимость. Ниже указаны окончательный вес и сумма заказа.' : 'Ďakujeme za vašu objednávku! Upresnili sme skutočnú hmotnosť tovaru a prepočítali cenu. Nižšie uvádzame konečnú hmotnosť a sumu objednávky.'; }
            elseif ($variant === 'replacement') { $intro = $ru ? 'Спасибо за ваш ответ! Подтверждаем согласованный с вами состав и стоимость заказа.' : 'Ďakujeme za vašu odpoveď! Potvrdzujeme vami odsúhlasené zloženie a cenu objednávky.'; }
            else { $intro = $ru ? 'Спасибо за ваш ответ! Подтверждаем согласованный с вами состав заказа. Итоговая стоимость учитывает замены и фактический вес товаров.' : 'Ďakujeme za vašu odpoveď! Potvrdzujeme vami odsúhlasené zloženie objednávky. Konečná cena zohľadňuje náhrady aj skutočnú hmotnosť tovaru.'; }
            $body .= '<p>'.$intro.'</p>';
        }
        if (!$s['quote']['lines']) { $body .= '<p><strong>'.($proposal ? ($ru ? 'Предлагаем отменить заказ. Доставка и сборы также отменяются.' : 'Navrhujeme objednávku zrušiť. Doprava a poplatky sa tiež rušia.') : ($ru ? 'По согласованию с вами заказ отменён. Отправка не производится.' : 'Po dohode s vami bola objednávka zrušená. Zásielka nebude odoslaná.')).'</strong></p>'; }
        $body .= self::table($s['quote'], $s['language']);
        $body .= '<p>' . ($ru ? 'Уже оплачено: ' : 'Už zaplatené: ') . self::amount($s['paid'] + $s['additional_paid']) . '</p>';
        if ($s['refunded'] > 0) { $body .= '<p>' . ($ru ? 'Возвращено банковским переводом: ' : 'Vrátené bankovým prevodom: ') . self::amount($s['refunded']) . '</p>'; }
        $balance = Review::balance($s);
        if ($balance < 0) {
            $body .= '<p><strong>' . ($ru ? 'К возврату: ' : 'Na vrátenie: ') . self::amount(-$balance) . '</strong>. ';
            if ($s['iban'] !== '') {
                $iban = substr($s['iban'], 0, 2) . '…' . substr($s['iban'], -4);
                $body .= $ru ? 'Возврат будет осуществлён банковским переводом на согласованный с вами IBAN ' : 'Suma bude vrátená bankovým prevodom na vami odsúhlasený IBAN ';
                $body .= self::escape($iban) . '.</p>';
            } else {
                $body .= ($ru ? 'С вашего согласия возврат будет осуществлён банковским переводом на IBAN, который вы укажете в ответном письме. Пожалуйста, сообщите IBAN, имя владельца счёта и подтвердите согласие на такой способ возврата.' : 'S vaším súhlasom bude suma vrátená bankovým prevodom na IBAN, ktorý uvediete v odpovedi. Prosíme, pošlite IBAN, meno majiteľa účtu a potvrďte súhlas s týmto spôsobom vrátenia peňazí.') . '</p>';
            }
        } elseif ($balance > 0) {
            $body .= '<p><strong>' . (!empty($customer['cod']) ? ($ru ? 'К оплате при получении: ' : 'Na úhradu pri prevzatí: ') : ($ru ? 'К доплате: ' : 'Na doplatenie: ')) . self::amount($balance) . '</strong></p>';
        }
        $body .= '<p>' . ($ru ? 'Оплата: ' : 'Platba: ') . self::escape($customer['payment']) . '<br>' . ($ru ? 'Доставка: ' : 'Doprava: ') . self::escape($customer['shipping']) . '</p>';
        if (!$proposal && $s['dispatch_date'] !== '') {
            $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $s['dispatch_date'])->format('d.m.Y');
            $body .= '<p><strong>' . ($ru ? 'Планируем передать ваш заказ курьерской службе ' : 'Vašu objednávku plánujeme odovzdať dopravcovi ') . self::escape($customer['shipping']) . ' — ' . $date . '.</strong></p>';
        }
        if ($proposal) {
            if ($s['comment'] !== '') { $body .= '<p>' . nl2br(self::escape($s['comment'])) . '</p>'; }
            if (!empty($customer['approval_url'])) {
                $body .= '<p><a style="display:inline-block;background:#185a7b;color:white;padding:12px 18px;text-decoration:none;border-radius:6px" href="'.self::escape($customer['approval_url']).'">'.($ru ? 'Согласен с изменениями' : 'Súhlasím so zmenami').'</a></p>';
                $body .= '<p>'.($ru ? 'Откройте ссылку, проверьте изменения и подтвердите согласие. Ссылка действует 7 дней. Мы продолжим обработку после вашего согласия.' : 'Otvorte odkaz, skontrolujte zmeny a potvrďte súhlas. Odkaz platí 7 dní. Pokračovať budeme po vašom súhlase.').'</p>';
                if (!empty($customer['contact_email'])) { $body .= '<p><a href="'.self::escape('mailto:'.$customer['contact_email'].'?subject='.rawurlencode(($ru ? 'Заказ №' : 'Objednávka č. ').$customer['number'])).'">'.($ru ? 'Связаться с магазином' : 'Kontaktovať obchod').'</a></p>'; }
                $body .= '<p>'.($ru ? 'Если вы хотите другие изменения, ответьте на это письмо или свяжитесь с магазином.' : 'Ak si želáte ďalšie zmeny, odpovedzte na tento e-mail alebo kontaktujte obchod.').'</p>';
            } else {
                $body .= '<p>'.($ru ? 'Пожалуйста, ответьте на это письмо, согласны ли вы с изменениями. Мы продолжим обработку после вашего согласия.' : 'Prosíme, odpovedzte na tento e-mail, či súhlasíte so zmenami. Pokračovať budeme po vašom súhlase.').'</p>';
            }
        }
        $body .= '<p>' . ($ru ? 'С уважением,<br>Команда Gastronom' : 'S pozdravom,<br>Tím Gastronom') . '</p>';
        return ['subject' => $subject, 'html' => '<!doctype html><html lang="' . $s['language'] . '"><meta charset="utf-8"><body style="font:16px/1.55 Arial,sans-serif;color:#172b3a;max-width:680px;margin:24px auto">' . $body . '</body></html>'];
    }
}
