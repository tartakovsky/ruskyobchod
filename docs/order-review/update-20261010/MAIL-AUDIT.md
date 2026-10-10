# Причина спама: проверка реальных писем 11654

Прочитаны метаданные трёх писем в подключённом Gmail; новых писем не отправляли,
папки/ярлыки не меняли. Настройки WordPress прочитаны без паролей и записи.

Корректировка: From WordPress <wordpress@gastronom-bratislava.sk>.
Return-Path: noreply@srv1800.main-hosting.eu. Google Authentication-Results:
SPF pass для srv1800.main-hosting.eu; DMARC fail, политика QUARANTINE,
header.from gastronom-bratislava.sk. DKIM-Signature отсутствует.
Прочие два письма Woo используют From gastronom@trtk.me, тот же Hostinger
Return-Path, без DKIM домена отправителя. Транспорт: стандартный wp_mail/
почта хостинга; SMTP-плагинов и phpmailer_init обработчиков нет.

DNS gastronom-bratislava.sk: SPF a mx include:_spf.m1.websupport.sk -all;
DMARC p=quarantine; MX mx10.websupport.sk / mx20.websupport.sk.
Для trtk.me обнаружены Google MX, SPF/DMARC TXT не обнаружены на момент проверки.

Сам адрес objednavky@gastronom-bratislava.sk пока лишь предложен; владелец
сообщил, что такого ящика, вероятно, нет. Не подставлять его как действующий From
без создания/авторизации. Предпочтительный постоянный вариант — брендовый ящик
через Websupport SMTP с DKIM/SPF, ответами/пересылкой на существующий trtk.me.
Альтернатива — текущий gastronom@trtk.me через авторизованный Google Workspace
с настроенными SPF/DKIM/DMARC. Отправитель должен быть единым для Woo и новых
писем Review, а не WordPress по умолчанию. SMTP/OAuth-доступ пока не настроен
и не предоставлен; DNS, почтовые настройки, аккаунты и From в этом релизе не менялись.

Google требует согласования From с SPF либо DKIM для DMARC:
https://support.google.com/mail/answer/81126
Для проверки исправления нужен контролируемый тест и Authentication-Results
на приёмной стороне; отсутствие спама нельзя обещать до этой проверки.
