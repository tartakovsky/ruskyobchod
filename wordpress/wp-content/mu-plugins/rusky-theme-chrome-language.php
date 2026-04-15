<?php
/**
 * Plugin Name: Rusky Theme Chrome Language
 * Description: Localizes small theme chrome strings on the public storefront.
 */

if (!defined('ABSPATH')) {
    exit;
}

function rtcl_current_lang(): string {
    if (function_exists('gls_server_lang')) {
        return gls_server_lang() === 'ru' ? 'ru' : 'sk';
    }

    $request_uri = isset($_SERVER['REQUEST_URI']) ? (string) wp_unslash($_SERVER['REQUEST_URI']) : '';
    if ($request_uri !== '') {
        if (preg_match('~^/ru(?:/|$)~', $request_uri)) {
            return 'ru';
        }
        if (preg_match('~^/sk(?:/|$)~', $request_uri)) {
            return 'sk';
        }
    }

    if (isset($_GET['lang'])) {
        $lang = sanitize_key(wp_unslash($_GET['lang']));
        if ($lang === 'ru' || $lang === 'sk') {
            return $lang;
        }
    }

    if (isset($_COOKIE['gastronom_lang'])) {
        $lang = sanitize_key(wp_unslash($_COOKIE['gastronom_lang']));
        if ($lang === 'ru' || $lang === 'sk') {
            return $lang;
        }
    }

    return 'sk';
}

function rtcl_phrase_map(string $lang): array {
    if ($lang === 'ru') {
        return [
            'Login / Register' => 'Вход / Регистрация',
            'Prihlásenie / Registrácia' => 'Вход / Регистрация',
            'My Account' => 'Мой аккаунт',
            'Môj účet' => 'Мой аккаунт',
            'Môj Účet' => 'Мой аккаунт',
            'shopping cart' => 'корзина',
            'košík' => 'корзина',
            'Open Button' => 'Кнопка Открыть',
            'Close Button' => 'Кнопка Закрыть',
            'Skip to content' => 'Перейти к содержимому',
            'Preskočiť na obsah' => 'Перейти к содержимому',
            'All Categories' => 'Все категории',
            'Search for:' => 'Искать:',
            'Search products…' => 'Поиск по товарам…',
            'Search' => 'Поиск',
            'Shop order' => 'Заказ в магазине',
            'Sort by popularity' => 'По популярности',
            'Sort by average rating' => 'По рейтингу',
            'Sort by latest' => 'По новизне',
            'Sort by price: low to high' => 'По возрастанию цены',
            'Sort by price: high to low' => 'По убыванию цены',
            'Default sorting' => 'Исходная сортировка',
            'Add to cart' => 'Добавить в корзину',
            'Add to cart: “%s”' => 'Добавить в корзину «%s»',
            'Product pagination' => 'Постраничная навигация по товарам',
            'Page %d' => 'Страница %d',
            'Showing %1$d–%2$d of %3$d results' => 'Отображение %1$d–%2$d из %3$d',
            'Showing the single result' => 'Показан единственный результат',
            'Showing all %d results' => 'Показаны все результаты: %d',
            'In stock' => 'В наличии',
            'Related products' => 'Похожие товары',
            'Your cart is currently empty.' => 'Ваша корзина пока пуста',
            'Return to shop' => 'Вернуться в магазин',
            'Username or email address' => 'Имя пользователя или Email',
            'Password' => 'Пароль',
            'Remember me' => 'Запомнить меня',
            'Log in' => 'Войти',
            'Lost your password?' => 'Забыли пароль?',
            'Register' => 'Регистрация',
            'Email address' => 'Email',
            'Cart totals' => 'Сумма корзины',
            'Subtotal' => 'Подытог',
            'Total' => 'Итого',
            'Shipping' => 'Доставка',
            'Proceed to checkout' => 'Оформить заказ',
            'Your order' => 'Ваш заказ',
            'First name' => 'Имя',
            'Last name' => 'Фамилия',
            'Country / Region' => 'Страна/регион',
            'Postcode / ZIP' => 'Почтовый индекс',
            'Town / City' => 'Населённый пункт',
            'Ship to a different address?' => 'Доставка по другому адресу?',
            'Order notes' => 'Примечание к заказу',
            'terms and conditions' => 'правила и условия',
            'Billing details' => 'Платёжные данные',
            'Product' => 'Товар',
            'Price' => 'Цена',
            'Quantity' => 'Количество',
            'Returning customer?' => 'Уже покупали у нас?',
            'Click here to login' => 'Нажмите здесь, чтобы войти',
            'Checkout' => 'Оформление заказа',
            'Order number' => 'Номер заказа',
            'Date' => 'Дата',
            'Pay for order' => 'Оплатить заказ',
            'View cart' => 'Посмотреть корзину',
            'Remove item' => 'Удалить товар',
            'Thumbnail image' => 'Миниатюра',
            'Update cart' => 'Обновить корзину',
            'Place order' => 'Оформить заказ',
            'Enter your address to view shipping options.' => 'Введите адрес, чтобы увидеть варианты доставки.',
            'Select a country / region…' => 'Выберите страну / регион…',
            'Update country / region' => 'Обновите страну / регион',
            'Street address' => 'Улица',
            'House number and street name' => 'Название улицы и номер дома',
            'State / County' => 'Штат / регион',
            'Phone' => 'Телефон',
            'Email' => 'Эл. почта',
            '(optional)' => '(необязательно)',
            'There are no shipping options available. Please ensure that your address has been entered correctly, or contact us if you need any help.' => 'Нет доступных вариантов доставки. Проверьте правильность введённого адреса или свяжитесь с нами, если нужна помощь.',
            'Shipping options will be updated during checkout.' => 'Варианты доставки будут обновлены при оформлении заказа.',
            'Save payment information to my account for future purchases.' => 'Сохранить платёжные данные в моём аккаунте для будущих покупок.',
            'I have read and agree to the website' => 'Я прочитал(а) и соглашаюсь с документом',
            'Shipping to ' => 'Доставка по адресу ',
            'Shipping to %s.' => 'Доставка по адресу %s.',
        ];
    }

    return [
        'Login / Register' => 'Prihlásenie / Registrácia',
        'Вход / Регистрация' => 'Prihlásenie / Registrácia',
        'My Account' => 'Môj účet',
        'Мой аккаунт' => 'Môj účet',
        'Мой Аккаунт' => 'Môj účet',
        'shopping cart' => 'košík',
        'корзина' => 'košík',
        'Open Button' => 'Otvoriť menu',
        'Кнопка Открыть' => 'Otvoriť menu',
        'Close Button' => 'Zavrieť menu',
        'Кнопка Закрыть' => 'Zavrieť menu',
        'Skip to content' => 'Preskočiť na obsah',
        'Перейти к содержимому' => 'Preskočiť na obsah',
        'All Categories' => 'Všetky kategórie',
        'Все категории' => 'Všetky kategórie',
        'Search for:' => 'Hľadať:',
        'Искать:' => 'Hľadať:',
        'Search products…' => 'Hľadať produkty…',
        'Поиск по товарам…' => 'Hľadať produkty…',
        'Search' => 'Vyhľadávanie',
        'Поиск' => 'Vyhľadávanie',
        'Shop order' => 'Zoradenie obchodu',
        'Заказ в магазине' => 'Zoradenie obchodu',
        'Sort by popularity' => 'Zoradiť podľa populárnosti',
        'По популярности' => 'Zoradiť podľa populárnosti',
        'Sort by average rating' => 'Zoradiť podľa hodnotenia',
        'По рейтингу' => 'Zoradiť podľa hodnotenia',
        'Sort by latest' => 'Zoradiť od najnovších',
        'По новизне' => 'Zoradiť od najnovších',
        'Sort by price: low to high' => 'Zoradiť od najlacnejších',
        'По возрастанию цены' => 'Zoradiť od najlacnejších',
        'Sort by price: high to low' => 'Zoradiť od najdrahších',
        'По убыванию цены' => 'Zoradiť od najdrahších',
        'Default sorting' => 'Predvolené zoradenie',
        'Исходная сортировка' => 'Predvolené zoradenie',
        'Add to cart' => 'Pridať do košíka',
        'Добавить в корзину' => 'Pridať do košíka',
        'Add to cart: “%s”' => 'Pridať do košíka: „%s“',
        'Добавить в корзину «%s»' => 'Pridať do košíka: „%s“',
        'Product pagination' => 'Stránkovanie produktu',
        'Постраничная навигация по товарам' => 'Stránkovanie produktu',
        'Page %d' => 'Stránka %d',
        'Страница %d' => 'Stránka %d',
        'Showing %1$d–%2$d of %3$d results' => 'Zobrazených %1$d–%2$d z %3$d',
        'Отображение %1$d–%2$d из %3$d' => 'Zobrazených %1$d–%2$d z %3$d',
        'Showing the single result' => 'Zobrazený jediný výsledok',
        'Показан единственный результат' => 'Zobrazený jediný výsledok',
        'Showing all %d results' => 'Zobrazených všetkých %d výsledkov',
        'Показаны все результаты: %d' => 'Zobrazených všetkých %d výsledkov',
        'In stock' => 'Na sklade',
        'В наличии' => 'Na sklade',
        'Related products' => 'Súvisiace produkty',
        'Похожие товары' => 'Súvisiace produkty',
        'Your cart is currently empty.' => 'Váš košík je momentálne prázdny',
        'Ваша корзина пока пуста' => 'Váš košík je momentálne prázdny',
        'Return to shop' => 'Späť do obchodu',
        'Вернуться в магазин' => 'Späť do obchodu',
        'Username or email address' => 'Používateľské meno alebo email',
        'Имя пользователя или Email' => 'Používateľské meno alebo email',
        'Password' => 'Heslo',
        'Пароль' => 'Heslo',
        'Remember me' => 'Zapamätať si ma',
        'Запомнить меня' => 'Zapamätať si ma',
        'Log in' => 'Prihlásiť sa',
        'Войти' => 'Prihlásiť sa',
        'Lost your password?' => 'Zabudli ste heslo?',
        'Забыли пароль?' => 'Zabudli ste heslo?',
        'Register' => 'Registrácia',
        'Регистрация' => 'Registrácia',
        'Email address' => 'Email',
        'Cart totals' => 'Súčet košíka',
        'Сумма корзины' => 'Súčet košíka',
        'Subtotal' => 'Medzisúčet',
        'Подытог' => 'Medzisúčet',
        'Total' => 'Spolu',
        'Итого' => 'Spolu',
        'Shipping' => 'Doprava',
        'Доставка' => 'Doprava',
        'Proceed to checkout' => 'Pokračovať k pokladni',
        'Оформить заказ' => 'Pokračovať k pokladni',
        'Your order' => 'Vaša objednávka',
        'Ваш заказ' => 'Vaša objednávka',
        'First name' => 'Krstné meno',
        'Имя' => 'Meno',
        'Last name' => 'Priezvisko',
        'Фамилия' => 'Priezvisko',
        'Country / Region' => 'Krajina / región',
        'Страна/регион' => 'Krajina / región',
        'Postcode / ZIP' => 'PSČ',
        'Почтовый индекс' => 'PSČ',
        'Town / City' => 'Mesto',
        'Населённый пункт' => 'Mesto',
        'Ship to a different address?' => 'Odoslať na inú adresu?',
        'Доставка по другому адресу?' => 'Odoslať na inú adresu?',
        'Order notes' => 'Poznámky k objednávke',
        'Примечание к заказу' => 'Poznámky k objednávke',
        'terms and conditions' => 'obchodné podmienky',
        'правила и условия' => 'obchodné podmienky',
        'Billing details' => 'Fakturačné údaje',
        'Платёжные данные' => 'Fakturačné údaje',
        'Product' => 'Produkt',
        'Товар' => 'Produkt',
        'Price' => 'Cena',
        'Цена' => 'Cena',
        'Quantity' => 'Množstvo',
        'Количество' => 'Množstvo',
        'Returning customer?' => 'Už ste u nás nakupovali?',
        'Уже покупали у нас?' => 'Už ste u nás nakupovali?',
        'Click here to login' => 'Kliknite sem pre prihlásenie',
        'Нажмите здесь, чтобы войти' => 'Kliknite sem pre prihlásenie',
        'Checkout' => 'Objednávka',
        'Оформление заказа' => 'Objednávka',
        'Order number' => 'Číslo objednávky',
        'Номер заказа' => 'Číslo objednávky',
        'Date' => 'Dátum',
        'Дата' => 'Dátum',
        'Pay for order' => 'Zaplatiť objednávku',
        'Оплатить заказ' => 'Zaplatiť objednávku',
        'View cart' => 'Zobraziť košík',
        'Посмотреть корзину' => 'Zobraziť košík',
        'Remove item' => 'Odstrániť položku',
        'Удалить товар' => 'Odstrániť položku',
        'Thumbnail image' => 'Náhľad obrázka',
        'Миниатюра' => 'Náhľad obrázka',
        'Update cart' => 'Aktualizovať košík',
        'Обновить корзину' => 'Aktualizovať košík',
        'Place order' => 'Odoslať objednávku',
        'Оформить заказ' => 'Odoslať objednávku',
        'Enter your address to view shipping options.' => 'Zadajte adresu, aby sa zobrazili možnosti dopravy.',
        'Введите адрес, чтобы увидеть варианты доставки.' => 'Zadajte adresu, aby sa zobrazili možnosti dopravy.',
        'Street address' => 'Ulica',
        'Улица' => 'Ulica',
        'House number and street name' => 'Názov ulice a číslo domu',
        'Название улицы и номер дома' => 'Názov ulice a číslo domu',
        'State / County' => 'Štát / kraj',
        'Штат / регион' => 'Štát / kraj',
        'Phone' => 'Telefón',
        'Телефон' => 'Telefón',
        'Email' => 'E-mail',
        'Эл. почта' => 'E-mail',
        '(optional)' => '(voliteľné)',
        '(необязательно)' => '(voliteľné)',
        'There are no shipping options available. Please ensure that your address has been entered correctly, or contact us if you need any help.' => 'Nie sú dostupné žiadne možnosti dopravy. Skontrolujte správnosť zadanej adresy alebo nás kontaktujte, ak potrebujete pomoc.',
        'Нет доступных вариантов доставки. Проверьте правильность введённого адреса или свяжитесь с нами, если нужна помощь.' => 'Nie sú dostupné žiadne možnosti dopravy. Skontrolujte správnosť zadanej adresy alebo nás kontaktujte, ak potrebujete pomoc.',
        'Save payment information to my account for future purchases.' => 'Uložiť platobné údaje do môjho účtu pre budúce nákupy.',
        'Сохранить платёжные данные в моём аккаунте для будущих покупок.' => 'Uložiť platobné údaje do môjho účtu pre budúce nákupy.',
        'I have read and agree to the website' => 'Prečítal/a som si dokument',
        'Я прочитал(а) и соглашаюсь с документом' => 'Prečítal/a som si dokument',
        'Select a country / region…' => 'Vyberte krajinu / región…',
        'Выберите страну / регион…' => 'Vyberte krajinu / región…',
        'Austria' => 'Rakúsko',
        'Slovakia' => 'Slovensko',
        'Update country / region' => 'Aktualizujte krajinu / región',
        'Обновите страну / регион' => 'Aktualizujte krajinu / región',
        'Street address' => 'Ulica',
        'House number and street name' => 'Názov ulice a číslo domu',
        'State / County' => 'Štát / kraj',
        '(optional)' => '(voliteľné)',
        'Notes about your order, e.g. special notes for delivery.' => 'Poznámka k objednávke, napr. upresnenie pre doručenie.',
        'Shipping options will be updated during checkout.' => 'Možnosti dopravy sa aktualizujú pri objednávke.',
        'Варианты доставки будут обновлены при оформлении заказа.' => 'Možnosti dopravy sa aktualizujú pri objednávke.',
        'Shipping to ' => 'Doručenie na adresu ',
        'Доставка по адресу ' => 'Doručenie na adresu ',
        'Shipping to %s.' => 'Doručenie na adresu %s.',
        'Доставка по адресу %s.' => 'Doručenie na adresu %s.',
    ];
}

function rtcl_translate_phrase(string $value, string $lang): string {
    $map = rtcl_phrase_map($lang);
    return $map[$value] ?? $value;
}

function rtcl_translate_storefront_text(string $value, string $lang): string {
    $value = trim($value);
    if ($value === '') {
        return $value;
    }

    $translated = rtcl_translate_phrase($value, $lang);
    if ($translated !== $value) {
        return $translated;
    }

    if ($lang === 'sk') {
        if (preg_match('/^Add to cart: “(.+)”$/u', $value, $matches)) {
            return sprintf('Pridať do košíka: „%s“', $matches[1]);
        }

        if (preg_match('/^“(.+)” has been added to your cart$/u', $value, $matches)) {
            return sprintf('„%s“ bol pridaný do košíka', $matches[1]);
        }

        if (preg_match('/^Добавить в корзину «(.+)»$/u', $value, $matches)) {
            return sprintf('Pridať do košíka: „%s“', $matches[1]);
        }

        if (preg_match('/^«(.+)» добавлен в вашу корзину$/u', $value, $matches)) {
            return sprintf('„%s“ bol pridaný do košíka', $matches[1]);
        }

        if (preg_match('/^Страница (\d+)$/u', $value, $matches)) {
            return sprintf('Stránka %d', (int) $matches[1]);
        }

        if (preg_match('/^Page (\d+)$/u', $value, $matches)) {
            return sprintf('Stránka %d', (int) $matches[1]);
        }

        if (preg_match('/^Отображение (\d+)([–-])(\d+) из (\d+)$/u', $value, $matches)) {
            return sprintf('Zobrazených %d%s%d z %d', (int) $matches[1], $matches[2], (int) $matches[3], (int) $matches[4]);
        }

        if (preg_match('/^Отображение (\d+)([–-])(\d+) из (\d+)Sorted by popularity$/u', $value, $matches)) {
            return sprintf('Zobrazených %d%s%d z %d', (int) $matches[1], $matches[2], (int) $matches[3], (int) $matches[4]);
        }

        if (preg_match('/^Zobrazených (\d+)([–-])(\d+) z (\d+) výsledkovSorted by .+$/u', $value, $matches)) {
            return sprintf('Zobrazených %d%s%d z %d výsledkov', (int) $matches[1], $matches[2], (int) $matches[3], (int) $matches[4]);
        }

        if (preg_match('/^Showing (\d+)([–-])(\d+) of (\d+) results$/u', $value, $matches)) {
            return sprintf('Zobrazených %d%s%d z %d', (int) $matches[1], $matches[2], (int) $matches[3], (int) $matches[4]);
        }

        if (preg_match('/^(.+) has been added to your cart\.$/u', $value, $matches)) {
            return sprintf('%s bol pridaný do košíka.', $matches[1]);
        }

        if (preg_match('/^\(includes (.+) VAT\)$/u', $value, $matches)) {
            return sprintf('(vrátane %s DPH)', $matches[1]);
        }
    }

    if ($lang === 'ru') {
        if (preg_match('/^Pridať do košíka: „(.+)“$/u', $value, $matches)) {
            return sprintf('Добавить в корзину «%s»', $matches[1]);
        }

        if (preg_match('/^„(.+)“ bol pridaný do košíka$/u', $value, $matches)) {
            return sprintf('«%s» добавлен в вашу корзину', $matches[1]);
        }

        if (preg_match('/^Stránka (\d+)$/u', $value, $matches)) {
            return sprintf('Страница %d', (int) $matches[1]);
        }

        if (preg_match('/^Page (\d+)$/u', $value, $matches)) {
            return sprintf('Страница %d', (int) $matches[1]);
        }

        if (preg_match('/^Zobrazených (\d+)([–-])(\d+) z (\d+)$/u', $value, $matches)) {
            return sprintf('Отображение %d%s%d из %d', (int) $matches[1], $matches[2], (int) $matches[3], (int) $matches[4]);
        }

        if (preg_match('/^Отображение (\d+)([–-])(\d+) из (\d+)Sorted by .+$/u', $value, $matches)) {
            return sprintf('Отображение %d%s%d из %d', (int) $matches[1], $matches[2], (int) $matches[3], (int) $matches[4]);
        }

        if (preg_match('/^(.+) has been added to your cart\.$/u', $value, $matches)) {
            return sprintf('%s добавлен в корзину.', $matches[1]);
        }

        if (preg_match('/^\(includes (.+) VAT\)$/u', $value, $matches)) {
            return sprintf('(включая %s НДС)', $matches[1]);
        }
    }

    return $value;
}

function rtcl_normalize_storefront_html(string $html, string $lang): string {
    if ($html === '' || !class_exists('DOMDocument')) {
        return $html;
    }

    if (
        strpos($html, 'woocommerce-product-search') === false &&
        strpos($html, 'woocommerce-ordering') === false &&
        strpos($html, 'add_to_cart_button') === false &&
        strpos($html, 'woocommerce-pagination') === false &&
        strpos($html, 'single_add_to_cart_button') === false &&
        strpos($html, 'product_meta') === false &&
        strpos($html, 'related products') === false &&
        strpos($html, 'wc-empty-cart-message') === false &&
        strpos($html, 'return-to-shop') === false &&
        strpos($html, 'woocommerce-form-login') === false &&
        strpos($html, 'woocommerce-form-register') === false &&
        strpos($html, 'cart_totals') === false &&
        strpos($html, 'woocommerce-checkout-review-order-table') === false &&
        strpos($html, 'woocommerce-billing-fields') === false &&
        strpos($html, 'woocommerce-order-overview') === false &&
        strpos($html, 'woocommerce-message') === false
    ) {
        return $html;
    }

    $internal_errors = libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $loaded = $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_NOWARNING | LIBXML_NOERROR);

    if (!$loaded) {
        libxml_clear_errors();
        libxml_use_internal_errors($internal_errors);
        return $html;
    }

    $xpath = new DOMXPath($dom);

    foreach ($xpath->query('//form[contains(concat(" ", normalize-space(@class), " "), " woocommerce-product-search ")]//label') ?: [] as $node) {
        $node->nodeValue = rtcl_translate_storefront_text($node->textContent, $lang);
    }

    foreach ($xpath->query('//form[contains(concat(" ", normalize-space(@class), " "), " woocommerce-product-search ")]//input[contains(concat(" ", normalize-space(@class), " "), " search-field ")]') ?: [] as $node) {
        if ($node instanceof DOMElement && $node->hasAttribute('placeholder')) {
            $node->setAttribute('placeholder', rtcl_translate_storefront_text($node->getAttribute('placeholder'), $lang));
        }
    }

    foreach ($xpath->query('//form[contains(concat(" ", normalize-space(@class), " "), " woocommerce-product-search ")]//button[@type="submit"]') ?: [] as $node) {
        if ($node instanceof DOMElement) {
            $node->nodeValue = rtcl_translate_storefront_text($node->textContent, $lang);
            if ($node->hasAttribute('value')) {
                $node->setAttribute('value', rtcl_translate_storefront_text($node->getAttribute('value'), $lang));
            }
        }
    }

    foreach ($xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " woocommerce-result-count ")]') ?: [] as $node) {
        $node->nodeValue = rtcl_translate_storefront_text($node->textContent, $lang);
    }

    foreach ($xpath->query('//form[contains(concat(" ", normalize-space(@class), " "), " woocommerce-ordering ")]//select[contains(concat(" ", normalize-space(@class), " "), " orderby ")]') ?: [] as $node) {
        if ($node instanceof DOMElement && $node->hasAttribute('aria-label')) {
            $node->setAttribute('aria-label', rtcl_translate_storefront_text($node->getAttribute('aria-label'), $lang));
        }
    }

    foreach ($xpath->query('//form[contains(concat(" ", normalize-space(@class), " "), " woocommerce-ordering ")]//option') ?: [] as $node) {
        $node->nodeValue = rtcl_translate_storefront_text($node->textContent, $lang);
    }

    foreach ($xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " add_to_cart_button ")]') ?: [] as $node) {
        if ($node instanceof DOMElement) {
            $node->nodeValue = rtcl_translate_storefront_text($node->textContent, $lang);

            if ($node->hasAttribute('aria-label')) {
                $node->setAttribute('aria-label', rtcl_translate_storefront_text($node->getAttribute('aria-label'), $lang));
            }

            if ($node->hasAttribute('data-success_message')) {
                $node->setAttribute('data-success_message', rtcl_translate_storefront_text($node->getAttribute('data-success_message'), $lang));
            }
        }
    }

    foreach ($xpath->query('//nav[contains(concat(" ", normalize-space(@class), " "), " woocommerce-pagination ")]') ?: [] as $node) {
        if ($node instanceof DOMElement && $node->hasAttribute('aria-label')) {
            $node->setAttribute('aria-label', rtcl_translate_storefront_text($node->getAttribute('aria-label'), $lang));
        }
    }

    foreach ($xpath->query('//nav[contains(concat(" ", normalize-space(@class), " "), " woocommerce-pagination ")]//*[@aria-label]') ?: [] as $node) {
        if ($node instanceof DOMElement) {
            $node->setAttribute('aria-label', rtcl_translate_storefront_text($node->getAttribute('aria-label'), $lang));
        }
    }

    foreach ($xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " stock ")]') ?: [] as $node) {
        $node->nodeValue = rtcl_translate_storefront_text($node->textContent, $lang);
    }

    foreach ($xpath->query('//div[contains(concat(" ", normalize-space(@class), " "), " quantity ")]//label[contains(concat(" ", normalize-space(@class), " "), " screen-reader-text ")]') ?: [] as $node) {
        $node->nodeValue = $lang === 'ru' ? 'Количество товара' : 'Množstvo produktu';
    }

    foreach ($xpath->query('//div[contains(concat(" ", normalize-space(@class), " "), " quantity ")]//input[contains(concat(" ", normalize-space(@class), " "), " qty ")]') ?: [] as $node) {
        if ($node instanceof DOMElement && $node->hasAttribute('aria-label')) {
            $node->setAttribute('aria-label', $lang === 'ru' ? 'Количество товара' : 'Množstvo produktu');
        }
    }

    foreach ($xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " sku_wrapper ")]') ?: [] as $node) {
        if ($node instanceof DOMElement && $node->firstChild instanceof DOMText) {
            $node->firstChild->nodeValue = $lang === 'ru' ? 'Артикул: ' : 'SKU: ';
        }
    }

    foreach ($xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " posted_in ")]') ?: [] as $node) {
        if ($node instanceof DOMElement && $node->firstChild instanceof DOMText) {
            $node->firstChild->nodeValue = $lang === 'ru' ? 'Категория: ' : 'Kategória: ';
        }
    }

    foreach ($xpath->query('//section[contains(concat(" ", normalize-space(@class), " "), " related ")]//h2') ?: [] as $node) {
        $node->nodeValue = rtcl_translate_storefront_text($node->textContent, $lang);
    }

    foreach ($xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " wc-empty-cart-message ")]//*[contains(concat(" ", normalize-space(@class), " "), " cart-empty ")]') ?: [] as $node) {
        $node->nodeValue = rtcl_translate_storefront_text($node->textContent, $lang);
    }

    foreach ($xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " return-to-shop ")]//a[contains(concat(" ", normalize-space(@class), " "), " button ")]') ?: [] as $node) {
        $node->nodeValue = rtcl_translate_storefront_text($node->textContent, $lang);
    }

    foreach ($xpath->query('//form[contains(concat(" ", normalize-space(@class), " "), " woocommerce-form-login ")]//label | //form[contains(concat(" ", normalize-space(@class), " "), " woocommerce-form-register ")]//label') ?: [] as $node) {
        if ($node instanceof DOMElement && $node->firstChild instanceof DOMText) {
            $node->firstChild->nodeValue = rtcl_translate_storefront_text(trim($node->firstChild->nodeValue), $lang) . "\u{00A0}";
        }
    }

    foreach ($xpath->query('//form[contains(concat(" ", normalize-space(@class), " "), " woocommerce-form-login ")]//span | //form[contains(concat(" ", normalize-space(@class), " "), " woocommerce-form-register ")]//span') ?: [] as $node) {
        if ($node instanceof DOMElement && trim($node->textContent) !== '') {
            $node->nodeValue = rtcl_translate_storefront_text(trim($node->textContent), $lang);
        }
    }

    foreach ($xpath->query('//form[contains(concat(" ", normalize-space(@class), " "), " woocommerce-form-login ")]//button | //form[contains(concat(" ", normalize-space(@class), " "), " woocommerce-form-register ")]//button') ?: [] as $node) {
        if ($node instanceof DOMElement) {
            $translated = rtcl_translate_storefront_text(trim($node->textContent), $lang);
            $node->nodeValue = $translated;
            if ($node->hasAttribute('value')) {
                $node->setAttribute('value', $translated);
            }
        }
    }

    foreach ($xpath->query('//form[contains(concat(" ", normalize-space(@class), " "), " woocommerce-form-login ")]//a | //form[contains(concat(" ", normalize-space(@class), " "), " woocommerce-form-register ")]//a') ?: [] as $node) {
        if ($node instanceof DOMElement) {
            $node->nodeValue = rtcl_translate_storefront_text(trim($node->textContent), $lang);
        }
    }

    foreach ($xpath->query('//div[contains(concat(" ", normalize-space(@class), " "), " woocommerce ")]//h2') ?: [] as $node) {
        if ($node instanceof DOMElement) {
            $node->nodeValue = rtcl_translate_storefront_text(trim($node->textContent), $lang);
        }
    }

    foreach ($xpath->query('//div[contains(concat(" ", normalize-space(@class), " "), " cart_totals ")]//h2 | //div[contains(concat(" ", normalize-space(@class), " "), " woocommerce-billing-fields ")]//h3 | //*[@id="order_review_heading"] | //table[contains(concat(" ", normalize-space(@class), " "), " shop_table ")]//th') ?: [] as $node) {
        if ($node instanceof DOMElement) {
            $node->nodeValue = rtcl_translate_storefront_text(trim($node->textContent), $lang);
        }
    }

    foreach ($xpath->query('//table[contains(concat(" ", normalize-space(@class), " "), " cart ")]//a[contains(concat(" ", normalize-space(@class), " "), " remove ")]') ?: [] as $node) {
        if ($node instanceof DOMElement && $node->hasAttribute('aria-label')) {
            $node->setAttribute('aria-label', rtcl_translate_storefront_text($node->getAttribute('aria-label'), $lang));
        }
    }

    foreach ($xpath->query('//form[contains(@class, "checkout")]//label') ?: [] as $node) {
        if ($node instanceof DOMElement && $node->firstChild instanceof DOMText) {
            $prefix = trim($node->firstChild->nodeValue);
            if ($prefix !== '') {
                $node->firstChild->nodeValue = rtcl_translate_storefront_text($prefix, $lang) . "\u{00A0}";
            }
        }
    }

    foreach ($xpath->query('//form[contains(@class, "checkout")]//input[@placeholder] | //form[contains(@class, "checkout")]//textarea[@placeholder] | //form[contains(@class, "checkout")]//select[@data-placeholder] | //form[contains(@class, "checkout")]//select[@data-label] | //form[contains(@class, "checkout")]//option | //form[contains(@class, "checkout")]//noscript//button') ?: [] as $node) {
        if ($node instanceof DOMElement) {
            foreach (['placeholder', 'data-placeholder', 'data-label', 'value', 'aria-label'] as $attr) {
                if ($node->hasAttribute($attr)) {
                    $node->setAttribute($attr, rtcl_translate_storefront_text($node->getAttribute($attr), $lang));
                }
            }

            if (trim($node->textContent) !== '') {
                $node->nodeValue = rtcl_translate_storefront_text(trim($node->textContent), $lang);
            }
        }
    }

    foreach ($xpath->query('//form[contains(@class, "checkout")]//*[contains(concat(" ", normalize-space(@class), " "), " select2-selection__placeholder ")] | //form[contains(@class, "checkout")]//*[contains(concat(" ", normalize-space(@class), " "), " select2-selection__rendered ")]') ?: [] as $node) {
        if ($node instanceof DOMElement) {
            $text = trim($node->textContent);
            if ($text !== '') {
                $node->nodeValue = rtcl_translate_storefront_text($text, $lang);
            }
        }
    }

    foreach ($xpath->query('//form[contains(@class, "checkout")]//span[contains(concat(" ", normalize-space(@class), " "), " optional ")]') ?: [] as $node) {
        if ($node instanceof DOMElement) {
            $node->nodeValue = rtcl_translate_storefront_text(trim($node->textContent), $lang);
        }
    }

    foreach ($xpath->query('//div[contains(concat(" ", normalize-space(@class), " "), " wc-proceed-to-checkout ")]//a[contains(concat(" ", normalize-space(@class), " "), " button ")]') ?: [] as $node) {
        if ($node instanceof DOMElement) {
            $node->nodeValue = rtcl_translate_storefront_text(trim($node->textContent), $lang);
        }
    }

    foreach ($xpath->query('//div[contains(concat(" ", normalize-space(@class), " "), " woocommerce-message ")]') ?: [] as $node) {
        if ($node instanceof DOMElement) {
            if ($node->firstChild instanceof DOMText) {
                $text = trim($node->firstChild->nodeValue);
                if ($text !== '') {
                    $node->firstChild->nodeValue = rtcl_translate_storefront_text($text, $lang) . ' ';
                }
            }

            foreach ($node->getElementsByTagName('a') as $link) {
                $link->nodeValue = rtcl_translate_storefront_text(trim($link->textContent), $lang);
            }
        }
    }

    foreach ($xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " woocommerce-info ")]//a | //*[contains(concat(" ", normalize-space(@class), " "), " woocommerce-info ")]') ?: [] as $node) {
        if ($node instanceof DOMElement) {
            if ($node->firstChild instanceof DOMText) {
                $text = trim($node->firstChild->nodeValue);
                if ($text !== '') {
                    $node->firstChild->nodeValue = rtcl_translate_storefront_text($text, $lang) . ' ';
                }
            } else {
                $text = trim($node->textContent);
                if ($text !== '') {
                    $node->nodeValue = rtcl_translate_storefront_text($text, $lang);
                }
            }
        }
    }

    foreach ($xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " woocommerce-terms-and-conditions-checkbox-text ")]') ?: [] as $node) {
        if ($node instanceof DOMElement) {
            $template_link = null;
            foreach ($node->getElementsByTagName('a') as $link) {
                $template_link = $link;
                break;
            }

            if ($lang === 'sk') {
                while ($node->firstChild) {
                    $node->removeChild($node->firstChild);
                }

                $node->appendChild($dom->createTextNode('Prečítal/a som si '));

                $link = $dom->createElement('a', 'všeobecné obchodné podmienky');
                if ($template_link instanceof DOMElement) {
                    foreach (['href', 'class', 'target'] as $attr) {
                        if ($template_link->hasAttribute($attr)) {
                            $link->setAttribute($attr, $template_link->getAttribute($attr));
                        }
                    }
                }
                $node->appendChild($link);
                $node->appendChild($dom->createTextNode(' a súhlasím s nimi'));
                continue;
            }

            if ($lang === 'ru') {
                while ($node->firstChild) {
                    $node->removeChild($node->firstChild);
                }

                $node->appendChild($dom->createTextNode('Я прочитал(а) и соглашаюсь с '));

                $link = $dom->createElement('a', 'правилами и условиями');
                if ($template_link instanceof DOMElement) {
                    foreach (['href', 'class', 'target'] as $attr) {
                        if ($template_link->hasAttribute($attr)) {
                            $link->setAttribute($attr, $template_link->getAttribute($attr));
                        }
                    }
                }
                $node->appendChild($link);
                continue;
            }

            foreach ($node->getElementsByTagName('a') as $link) {
                $link->nodeValue = $lang === 'sk'
                    ? 'všeobecné obchodné podmienky'
                    : rtcl_translate_storefront_text(trim($link->textContent), $lang);
            }

        }
    }

    foreach ($xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " includes_tax ")]') ?: [] as $node) {
        if ($node instanceof DOMElement) {
            if ($lang === 'sk') {
                $amount_html = '';
                foreach ($node->childNodes as $child) {
                    if ($child instanceof DOMElement) {
                        $amount_html .= $dom->saveHTML($child);
                    }
                }
                while ($node->firstChild) {
                    $node->removeChild($node->firstChild);
                }
                $fragment = $dom->createDocumentFragment();
                $fragment->appendXML('(vrátane ' . $amount_html . ' DPH)');
                $node->appendChild($fragment);
            } elseif ($lang === 'ru') {
                $amount_html = '';
                foreach ($node->childNodes as $child) {
                    if ($child instanceof DOMElement) {
                        $amount_html .= $dom->saveHTML($child);
                    }
                }
                while ($node->firstChild) {
                    $node->removeChild($node->firstChild);
                }
                $fragment = $dom->createDocumentFragment();
                $fragment->appendXML('(включая ' . $amount_html . ' НДС)');
                $node->appendChild($fragment);
            }
        }
    }

    foreach ($xpath->query('//*[@id="place_order"] | //button[@name="update_cart"]') ?: [] as $node) {
        if ($node instanceof DOMElement) {
            $translated = rtcl_translate_storefront_text(trim($node->textContent), $lang);
            $node->nodeValue = $translated;
            if ($node->hasAttribute('value')) {
                $node->setAttribute('value', $translated);
            }
            if ($node->hasAttribute('data-value')) {
                $node->setAttribute('data-value', $translated);
            }
        }
    }

    foreach ($xpath->query('//table[contains(concat(" ", normalize-space(@class), " "), " woocommerce-checkout-review-order-table ")]//td | //tr[contains(concat(" ", normalize-space(@class), " "), " shipping ")]//p | //tr[contains(concat(" ", normalize-space(@class), " "), " shipping ")]//span') ?: [] as $node) {
        if ($node instanceof DOMElement && $node->childNodes->length === 1 && $node->firstChild instanceof DOMText) {
            $text = trim($node->textContent);
            if ($text !== '') {
                $node->nodeValue = rtcl_translate_storefront_text($text, $lang);
            }
        }
    }

    foreach ($xpath->query('//ul[contains(concat(" ", normalize-space(@class), " "), " woocommerce-order-overview ")]//li') ?: [] as $node) {
        if ($node instanceof DOMElement && $node->firstChild instanceof DOMText) {
            $prefix = trim(rtrim($node->firstChild->nodeValue, ':'));
            if ($prefix !== '') {
                $node->firstChild->nodeValue = rtcl_translate_storefront_text($prefix, $lang) . ': ';
            }
        }
    }

    if (function_exists('is_checkout') && is_checkout()) {
        $checkout_title = $lang === 'ru' ? 'Оформление заказа' : 'Objednávka';

        if (function_exists('is_wc_endpoint_url') && is_wc_endpoint_url('order-received')) {
            $checkout_title = $lang === 'ru' ? 'Заказ получен' : 'Objednávka prijatá';
        } elseif (function_exists('is_checkout_pay_page') && is_checkout_pay_page()) {
            $checkout_title = $lang === 'ru' ? 'Оплатить заказ' : 'Zaplatiť objednávku';
        }

        foreach ($xpath->query('//h1[contains(concat(" ", normalize-space(@class), " "), " vw-page-title ")] | //div[contains(concat(" ", normalize-space(@class), " "), " bradcrumbs ")]/span') ?: [] as $node) {
            if ($node instanceof DOMElement) {
                $node->nodeValue = $checkout_title;
            }
        }

        foreach ($xpath->query('//form[contains(concat(" ", normalize-space(@class), " "), " checkout ")]') ?: [] as $node) {
            if ($node instanceof DOMElement && $node->hasAttribute('aria-label')) {
                $node->setAttribute('aria-label', $checkout_title);
            }
        }
    }

    $result = $dom->saveHTML();
    $result = preg_replace('/^<\?xml.+?\?>/u', '', (string) $result);

    if (function_exists('is_wc_endpoint_url') && is_wc_endpoint_url('order-received')) {
        if ($lang === 'ru') {
            $result = strtr((string) $result, [
                'Order number::' => 'Номер заказа:',
                'Date::' => 'Дата:',
                'Total::' => 'Итого:',
                'Способ оплаты::' => 'Способ оплаты:',
            ]);
        } else {
            $result = strtr((string) $result, [
                'Order number::' => 'Číslo objednávky:',
                'Date::' => 'Dátum:',
                'Total::' => 'Spolu:',
                'Spôsob platby::' => 'Spôsob platby:',
            ]);
        }
    }

    if (function_exists('is_checkout_pay_page') && is_checkout_pay_page()) {
        if ($lang === 'ru') {
            $result = strtr((string) $result, [
                'Qty' => 'Кол-во',
                'Totals' => 'Итого',
                'Total:' => 'Итого:',
            ]);
        } else {
            $result = strtr((string) $result, [
                'Qty' => 'Množstvo',
                'Totals' => 'Spolu',
                'Total:' => 'Spolu:',
            ]);
        }
    }

    if (function_exists('is_checkout') && is_checkout()) {
        $result = strtr((string) $result, [
            'There are no shipping options available. Please ensure that your address has been entered correctly, or contact us if you need any help.' => rtcl_translate_storefront_text('There are no shipping options available. Please ensure that your address has been entered correctly, or contact us if you need any help.', $lang),
            'Save payment information to my account for future purchases.' => rtcl_translate_storefront_text('Save payment information to my account for future purchases.', $lang),
            'I have read and agree to the website ' => rtcl_translate_storefront_text('I have read and agree to the website', $lang) . ' ',
            '(includes ' => $lang === 'ru' ? '(включая ' : '(vrátane ',
            ' VAT)' => $lang === 'ru' ? ' НДС)' : ' DPH)',
        ]);
    }

    if ((function_exists('is_cart') && is_cart()) || (function_exists('is_checkout') && is_checkout())) {
        $result = strtr((string) $result, [
            'Shipping to ' => rtcl_translate_storefront_text('Shipping to ', $lang),
            'Shipping to' => rtrim(rtcl_translate_storefront_text('Shipping to ', $lang)),
            '>Shipping<' => '>' . rtcl_translate_storefront_text('Shipping', $lang) . '<',
        ]);
    }

    libxml_clear_errors();
    libxml_use_internal_errors($internal_errors);

    return (string) $result;
}

function rtcl_translate_tax_suffix_html(string $html, string $lang): string {
    if ($html === '') {
        return $html;
    }

    $prefix = $lang === 'ru' ? '(включая ' : '(vrátane ';
    $suffix = $lang === 'ru' ? ' НДС)' : ' DPH)';

    return strtr($html, [
        '(includes ' => $prefix,
        ' VAT)' => $suffix,
    ]);
}

add_filter('gettext', function($translation, $text, $domain) {
    if (is_admin()) {
        return $translation;
    }

    $lang = rtcl_current_lang();
    $normalized = rtcl_translate_phrase((string) $translation, $lang);
    if ($normalized !== (string) $translation) {
        return $normalized;
    }

    return rtcl_translate_phrase((string) $text, $lang);
}, 120, 3);

add_filter('ngettext', function($translation, $single, $plural, $number, $domain) {
    if (is_admin()) {
        return $translation;
    }

    $lang = rtcl_current_lang();
    $normalized = rtcl_translate_phrase((string) $translation, $lang);
    if ($normalized !== (string) $translation) {
        return $normalized;
    }

    $source = $number === 1 ? (string) $single : (string) $plural;
    return rtcl_translate_phrase($source, $lang);
}, 120, 5);

add_filter('get_product_search_form', function($form) {
    if (is_admin() || !is_string($form) || $form === '') {
        return $form;
    }

    $lang = rtcl_current_lang();
    $placeholder = $lang === 'ru' ? 'Поиск по товарам…' : 'Hľadať produkty…';

    return preg_replace(
        '/(<input\b[^>]*\bclass="[^"]*\bsearch-field\b[^"]*"[^>]*\bplaceholder=")[^"]*(")/u',
        '$1' . esc_attr($placeholder) . '$2',
        $form,
        1
    ) ?: $form;
}, 120);

add_filter('woocommerce_form_field_args', function($args, $key, $value) {
    if (is_admin()) {
        return $args;
    }

    if (!function_exists('is_checkout') || !is_checkout()) {
        return $args;
    }

    $lang = rtcl_current_lang();

    foreach (['label', 'placeholder'] as $field_key) {
        if (!empty($args[$field_key]) && is_string($args[$field_key])) {
            $args[$field_key] = rtcl_translate_storefront_text($args[$field_key], $lang);
        }
    }

    if (!empty($args['custom_attributes']['data-placeholder']) && is_string($args['custom_attributes']['data-placeholder'])) {
        $args['custom_attributes']['data-placeholder'] = rtcl_translate_storefront_text($args['custom_attributes']['data-placeholder'], $lang);
    }

    return $args;
}, 120, 3);

add_filter('woocommerce_get_terms_and_conditions_checkbox_text', function($text) {
    if (is_admin()) {
        return $text;
    }

    $lang = rtcl_current_lang();

    if ($lang === 'ru') {
        return 'Я прочитал(а) и соглашаюсь с [terms]';
    }

    return 'Prečítal/a som si [terms] a súhlasím s nimi';
}, 120);

add_filter('woocommerce_cart_totals_order_total_html', function($html) {
    if (is_admin()) {
        return $html;
    }

    return rtcl_translate_tax_suffix_html((string) $html, rtcl_current_lang());
}, 120);

add_filter('woocommerce_no_shipping_available_html', function($html) {
    if (is_admin()) {
        return $html;
    }

    return rtcl_translate_storefront_text(wp_strip_all_tags((string) $html), rtcl_current_lang());
}, 120);

add_action('template_redirect', function() {
    if (is_admin() || wp_doing_ajax()) {
        return;
    }

    $is_storefront_archive = false;

    if (function_exists('is_shop') && is_shop()) {
        $is_storefront_archive = true;
    }

    if (function_exists('is_product_taxonomy') && is_product_taxonomy()) {
        $is_storefront_archive = true;
    }

    if (!$is_storefront_archive && is_search()) {
        $post_type = isset($_GET['post_type']) ? sanitize_key(wp_unslash($_GET['post_type'])) : '';
        $is_storefront_archive = $post_type === 'product';
    }

    if (!$is_storefront_archive && function_exists('is_product') && is_product()) {
        $is_storefront_archive = true;
    }

    if (!$is_storefront_archive && function_exists('is_cart') && is_cart()) {
        $is_storefront_archive = true;
    }

    if (!$is_storefront_archive && function_exists('is_account_page') && is_account_page()) {
        $is_storefront_archive = true;
    }

    if (!$is_storefront_archive && function_exists('is_checkout') && is_checkout()) {
        $is_storefront_archive = true;
    }

    if (!$is_storefront_archive) {
        return;
    }

    ob_start(static function($html) {
        if (!is_string($html) || $html === '') {
            return $html;
        }

        return rtcl_normalize_storefront_html($html, rtcl_current_lang());
    });
}, 20);
