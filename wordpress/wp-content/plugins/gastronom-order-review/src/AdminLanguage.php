<?php
declare(strict_types=1);
namespace Gastronom\OrderReview;

/** Localization at the administrative presentation boundary; never rewrites stored workflow data. */
final class AdminLanguage
{
    public static function message(string $message, string $lang): string
    {
        if ($lang === 'ru') { return $message; }
        $messages = [
            'Укажите неотрицательную сумму с точностью до цента.' => 'Zadajte nezápornú sumu s presnosťou na cent.',
            'Количество или вес: не более трёх знаков после запятой.' => 'Množstvo alebo hmotnosť: najviac tri desatinné miesta.',
            'Количество должно быть больше нуля. Для удаления используйте «Удалить».' => 'Množstvo musí byť väčšie ako nula. Na odstránenie použite „Odstrániť“.',
            'Заказ должен содержать товары и корректную сумму оплаты.' => 'Objednávka musí obsahovať produkty a správnu sumu platby.',
            'Проверьте IBAN: страна или длина неверны.' => 'Skontrolujte IBAN: nesprávna krajina alebo dĺžka.',
            'Контрольная сумма IBAN неверна.' => 'Kontrolný súčet IBAN je nesprávny.',
            'Заказ уже изменён. Обновите страницу.' => 'Objednávka sa už zmenila. Obnovte stránku.',
            'Вес уточняется до согласования и утверждения.' => 'Hmotnosť sa upresňuje pred súhlasom a schválením.',
            'В заказе не сохранён диапазон веса товара. Проверьте данные товара.' => 'V objednávke chýba rozsah hmotnosti. Skontrolujte údaje produktu.',
            'Утверждённый состав заблокирован.' => 'Schválené zloženie je uzamknuté.',
            'Сначала нажмите «Изменить».' => 'Najprv stlačte „Upraviť“.',
            'Слишком много позиций.' => 'Príliš veľa položiek.',
            'Некорректная позиция заказа.' => 'Nesprávna položka objednávky.',
            'Некорректный итог.' => 'Nesprávna výsledná suma.',
            'Доставка и сборы сохраняются по исходному заказу.' => 'Doprava a poplatky zostávajú podľa pôvodnej objednávky.',
            'Сохраните состав и укажите отсутствующие товары.' => 'Uložte zloženie a uveďte chýbajúce produkty.',
            'Сначала отправьте актуальное предложение клиенту.' => 'Najprv pošlite zákazníkovi aktuálny návrh.',
            'Укажите способ получения согласия клиента.' => 'Uveďte spôsob získania súhlasu zákazníka.',
            'Возврат по текущему расчёту не требуется.' => 'Podľa aktuálneho výpočtu vrátenie peňazí nie je potrebné.',
            'Нужны владелец счёта и согласие клиента на банковский возврат.' => 'Je potrebné meno majiteľa účtu a súhlas zákazníka s bankovým prevodom.',
            'Сначала утвердите заказ, сохраните реквизиты и укажите банковскую ссылку выполненного перевода.' => 'Najprv schváľte objednávku, uložte údaje a uveďte referenciu vykonaného bankového prevodu.',
            'Возврат превышает остаток к возврату.' => 'Vrátenie prevyšuje zostávajúcu sumu na vrátenie.',
            'Доплату отмечают для готового состава с подтверждением платежа.' => 'Doplatok sa zaznamenáva pre hotové zloženie s potvrdením platby.',
            'Проверьте остаток к оплате.' => 'Skontrolujte zostávajúcu sumu na zaplatenie.',
            'Состав ещё не согласован либо уже утверждён.' => 'Zloženie ešte nebolo odsúhlasené alebo už bolo schválené.',
            'Согласие относится к другой версии.' => 'Súhlas sa vzťahuje na inú verziu.',
            'Сначала укажите фактический вес всех весовых товаров.' => 'Najprv zadajte skutočnú hmotnosť všetkých vážených produktov.',
            'Укажите дату передачи курьеру не ранее сегодняшней.' => 'Zadajte dátum odovzdania dopravcovi, ktorý nie je pred dnešným dňom.',
            'Предоплата не покрывает окончательную сумму. Сначала отметьте поступление денег.' => 'Predplatba nepokrýva konečnú sumu. Najprv zaznamenajte prijatie peňazí.',
            'Повтор доступен только для незавершённого выполнения.' => 'Opakovanie je dostupné iba pre nedokončené spracovanie.',
            'Неизвестное действие.' => 'Neznáma operácia.',
            'Эта банковская операция уже учтена.' => 'Táto banková transakcia už bola zaznamenaná.',
            'Нет запущенной операции.' => 'Žiadna operácia neprebieha.',
            'Данные корректировки повреждены. Требуется восстановить сохранённую версию.' => 'Údaje úpravy sú poškodené. Je potrebné obnoviť uloženú verziu.',
            'Товар исходного заказа удалён из каталога. Требуется восстановить привязку.' => 'Produkt pôvodnej objednávky bol odstránený z katalógu. Je potrebné obnoviť prepojenie.',
            'Этот вариант валюты или отрицательных сборов требует отдельной проверки.' => 'Táto mena alebo záporné poplatky vyžadujú samostatnú kontrolu.',
            'Выбранный товар не найден.' => 'Vybraný produkt sa nenašiel.',
            'Для штучного товара требуется целое количество.' => 'Pre kusový produkt je potrebné celé množstvo.',
            'Предложение устарело или ссылка недействительна.' => 'Návrh už nie je aktuálny alebo odkaz je neplatný.',
            'Исходный заказ изменён вне блока Gastronom. Требуется сверить состав и оплату перед продолжением.' => 'Pôvodná objednávka bola zmenená mimo bloku Gastronom. Pred pokračovaním skontrolujte zloženie a platbu.',
            'Отменённый или неуспешный заказ нельзя передать в исполнение.' => 'Zrušenú alebo neúspešnú objednávku nemožno odovzdať na spracovanie.',
            'Обновите страницу.' => 'Obnovte stránku.',
            'Это письмо относится к устаревшему составу.' => 'Tento e-mail sa vzťahuje na neaktuálne zloženie.',
            'Нет неотправленного письма.' => 'Nie je žiadny neodoslaný e-mail.',
            'Заказ не относится к новой схеме.' => 'Objednávka nepatrí do nového postupu.',
            'Другая операция выполняется. Обновите страницу; при длительной блокировке нужна проверка журнала.' => 'Prebieha iná operácia. Obnovte stránku; pri dlhšom uzamknutí je potrebné skontrolovať záznamy.',
            'Получена оплата. Обновите страницу для актуального расчёта.' => 'Platba bola prijatá. Obnovte stránku pre aktuálny výpočet.',
            'Журнал резервов повреждён.' => 'Záznam rezervácií je poškodený.',
            'Резерв обновляется другим заказом. Повторите операцию.' => 'Rezerváciu aktualizuje iná objednávka. Zopakujte operáciu.',
            'Не удалось сохранить резерв.' => 'Rezerváciu sa nepodarilo uložiť.',
            'Резерв занят другой операцией. Повторите освобождение.' => 'Rezerváciu používa iná operácia. Zopakujte uvoľnenie.',
            'Не удалось освободить резерв.' => 'Rezerváciu sa nepodarilo uvoľniť.',
            'Обновление остатков занято. Повторите сверку.' => 'Prebieha aktualizácia zásob. Zopakujte kontrolu.',
            'Не удалось закрыть резерв.' => 'Rezerváciu sa nepodarilo uzavrieť.',
            'Для точного резерва нужен фактический вес.' => 'Na presnú rezerváciu je potrebná skutočná hmotnosť.',
        ];
        if (isset($messages[$message])) { return $messages[$message]; }
        $prefixes = [
            'Фактический вес выходит за диапазон, указанный покупателю: ' => 'Skutočná hmotnosť je mimo rozsahu uvedeného zákazníkovi: ',
            'Недостаточно доступного остатка: ' => 'Nedostatok dostupných zásob: ',
            'Недостаточно доступного остатка для резерва товара ' => 'Nedostatok zásob na rezerváciu produktu ',
            'Для резерва требуется управляемый остаток товара ' => 'Rezervácia vyžaduje riadené zásoby produktu ',
            'Укажите максимальную массу упаковки для резерва ' => 'Zadajte maximálnu hmotnosť balenia na rezerváciu ',
            'Не удалось обновить остаток товара ' => 'Zásoby produktu sa nepodarilo aktualizovať ',
        ];
        foreach ($prefixes as $source => $target) {
            if (str_starts_with($message, $source)) { return $target . str_replace(' кг.', ' kg.', substr($message, strlen($source))); }
        }
        return $message;
    }
}
