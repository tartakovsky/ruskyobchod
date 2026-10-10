# Авторизованная почта магазина

Сайт сейчас использует обычную PHP-почту Hostinger. В письме корректировки 11654
подтверждены From wordpress@gastronom-bratislava.sk, Hostinger Return-Path,
отсутствие DKIM и DMARC fail / quarantine. Исправление — реальный авторизованный
SMTP-ящик и согласованные SPF/DKIM/DMARC, а не ослабление DMARC.

Выбранный постоянный адрес: objednavky@gastronom-bratislava.sk, имя Gastronom.
Ящик пока не подтверждён. MX/SPF домена уже используют Websupport. Нужно проверить
действующую услугу, создать ящик при отсутствии, включить DKIM и пересылку ответов
в gastronom@trtk.me при необходимости. Не покупать новую услугу без проверки
существующей подписки и согласования стоимости.

Подготовлен один MU-владелец транспорта: rusky-mail-transport.php. Он не меняет
получателей, Reply-To клиента, состав письма и вложения. До наличия константы
GASTRONOM_SMTP_CONFIG не регистрирует никаких почтовых обработчиков. После
включения использует SMTPAuth/TLS и единые From/Sender для Woo и Review.
Если активная конфигурация отсутствует/неверна, wp_mail возвращает false;
скрытого возврата к PHP mail нет. Плагин пока не установлен и не включён.

Рабочий пароль только в приватном файле вне webroot с правами 0600; в Git только
пример SMTP-CONFIG.example.php. В wp-config.php хранится лишь путь:

```php
define('GASTRONOM_SMTP_CONFIG', '/home/u595644545/private/gastronom-smtp.php');
```

Перед включением: резерв текущих файлов/настроек, проверка SSL/SMTP/авторизации
без отправки. После включения: одно контролируемое письмо на собственный Gmail,
проверка Authentication-Results и папки, тест ответа/пересылки. Новые заказы,
кассовые продажи и уведомления реальным покупателям для проверки не создавать.

Откат: отключить SMTP-константу/восстановить прежние файлы из резерва, сохранив
журнал писем и неопределённые sending-состояния; автоматически не пересылать
ранее принятые транспортом письма. Возврат к прежнему PHP-транспорту оставляет
известную проблему DMARC. Базу и сведения по заказам не восстанавливать.

Источники:
- https://www.websupport.sk/podpora/kb/vytvorenie-emailovej-schranky-na-hostingu/
- https://www.websupport.sk/podpora/kb/postove-protokoly/
- https://www.websupport.sk/podpora/kb/zlepsovanie-dorucitelnosti-emailov/
- https://support.google.com/mail/answer/81126
