(function() {
    'use strict';

    const TRANSLATIONS = {
        // WooCommerce buttons and UI
        'В корзину': 'Do košíka',
        'Добавить в корзину': 'Pridať do košíka',
        'Подробнее': 'Viac info',
        'Корзина': 'Košík',
        'Оформление заказа': 'Objednávka',
        'Оформить заказ': 'Prejsť k platbe',
        'ОФОРМИТЬ ЗАКАЗ': 'PREJSŤ K PLATBE',
        'Перейти к оплате': 'Prejsť k platbe',
        // Checkout page
        'Платная доставка на дом (читать условия доставки)': 'Platene dodanie domov (čítať dodacie podmienky)',
        'Личный самовывоз': 'Osobne vyzdvihnutie',
        'Оплата при доставке': 'Platba pri doručení',
        'Доплата за наложенный платеж': 'Poplatok za dobierku',
        'Платёжные реквизиты': 'Fakturačné údaje',
        'Подтвердить заказ': 'Potvrdiť objednávku',
        'Итого': 'Celkom',
        'Промежуточный итог': 'Medzisúčet',
        'Подытог': 'Medzisúčet',
        'Доставка': 'Doprava',
        'Купон': 'Kupón',
        'Применить купон': 'Použiť kupón',
        'Обновить корзину': 'Aktualizovať košík',
        'Товар добавлен в корзину': 'Produkt pridaný do košíka',
        'Просмотр корзины': 'Zobraziť košík',
        'Нет в наличии': 'Nie je na sklade',
        'В наличии': 'Na sklade',
        'Описание': 'Popis',
        'Отзывы': 'Recenzie',
        'Сопутствующие товары': 'Súvisiace produkty',
        'Похожие товары': 'Súvisiace produkty',
        'Категории': 'Kategórie',
        'Категория:': 'Kategória:',
        'Артикул:': 'Kód:',
        'Количество товара': 'Množstvo produktu',
        'Поиск': 'Hľadať',
        'Найти': 'Nájsť',
        'Результаты поиска': 'Výsledky hľadania',
        'Сортировка': 'Zoradiť',
        'По умолчанию': 'Predvolené',
        'Исходная сортировка': 'Predvolené zoradenie',
        'По популярности': 'Podľa popularity',
        'По рейтингу': 'Podľa hodnotenia',
        'По новизне': 'Podľa novosti',
        'Цена по возрастанию': 'Cena vzostupne',
        'Цена по убыванию': 'Cena zostupne',
        'По возрастанию цены': 'Podľa ceny vzostupne',
        'По убыванию цены': 'Podľa ceny zostupne',
        'По алфавиту': 'Podľa abecedy',
        'Показ': 'Zobrazenie',
        'результатов': 'výsledkov',
        'Главная': 'Domov',
        'Магазин': 'Obchod',
        'Контакт': 'Kontakt',
        'Доставка и оплата': 'Doprava a platba',
        'Новости': 'Novinky',
        'Мой аккаунт': 'Môj účet',
        'Мой Аккаунт': 'Môj Účet',
        // Menu items
        'Напитки': 'Nápoje',
        'Кондитерские изделия': 'Cukrovinky',
        'Бакалея': 'Potraviny',
        // Search form
        'Поиск по товарам...': 'Hľadať produkty…',
        'Поиск по товарам\u2026': 'Hľadať produkty\u2026',
        'Поиск по товарам…': 'Hľadať produkty…',
        'Все рубрики': 'Všetky kategórie',
        // Common
        'шт.': 'ks',
        // Shop/category page strings
        'Товары': 'Produkty',
        'Показано': 'Zobrazených',
        'из': 'z',
        'Распродажа!': 'Zľava!',
        'Выберите параметры': 'Vybrať možnosti',
        'Читать далее': 'Čítať ďalej',
        'Домой': 'Domov',
        // Contact page
        'Адрес': 'Adresa',
        'Часы работы:': 'Otváracie hodiny:',
        'Тел. номер:': 'Tel. číslo:',
        // My Account page (login/register)
        'Вход': 'Prihlásenie',
        'Регистрация': 'Registrácia',
        'Войти': 'Prihlásiť sa',
        'Пароль': 'Heslo',
        'Запомнить меня': 'Zapamätať si ma',
        'Забыли свой пароль?': 'Zabudli ste heslo?',
        'Обязательно': 'Povinné',
        'Имя пользователя или Email': 'Meno používateľa alebo Email',
        'Email': 'Email',
        // My Account dashboard (logged in)
        'Информационная панель': 'Prehľad',
        'Консоль': 'Prehľad',
        'Заказы': 'Objednávky',
        'Загрузки': 'Stiahnutia',
        'Адреса': 'Adresy',
        'Детали аккаунта': 'Detaily účtu',
        'Способы оплаты': 'Spôsoby platby',
        'Выйти': 'Odhlásiť sa',
        'Платёжный адрес': 'Fakturačná adresa',
        'Адрес доставки': 'Dodacia adresa',
        'Редактировать': 'Upraviť',
        'Нет заказов.': 'Žiadne objednávky.',
        'недавние заказы': 'nedávne objednávky',
        'платежный адрес и адрес доставки': 'fakturačnú adresu a dodaciu adresu',
        'изменить пароль и основную информацию': 'zmeniť heslo a základné informácie',
        // Cart page
        'Ваша корзина пока пуста.': 'Váš košík je prázdny.',
        'Вернуться в магазин': 'Vrátiť sa do obchodu',
        'Товар': 'Produkt',
        'Кол-во': 'Množstvo',
        'Цена': 'Cena',
        'Количество': 'Množstvo',
        'Удалить': 'Odstrániť',
        'Фактический вес': 'Skutočná hmotnosť',
        'Фактический вес:': 'Skutočná hmotnosť:',
        'Подытог': 'Medzisúčet',
        'Подытог:': 'Medzisúčet:',
        'Доставка:': 'Doprava:',
        'Способ оплаты:': 'Spôsob platby:',
        'Итого:': 'Celkom:',
        'Оплатить заказ': 'Zaplatiť objednávku',
        // Checkout page extra
        'Имя': 'Meno',
        'Фамилия': 'Priezvisko',
        'Страна/регион': 'Krajina/región',
        'Адрес улицы': 'Adresa ulice',
        'Город': 'Mesto',
        'Населённый пункт': 'Mesto',
        'Номер дома и название улицы': 'Číslo domu a názov ulice',
        'Выберите страну/регион…': 'Vyberte krajinu/región…',
        'Выберите страну/регион\u2026': 'Vyberte krajinu/región\u2026',
        'обязательно': 'povinné',
        'Обязательное поле': 'Povinné pole',
        'Моя учётная запись': 'Môj účet',
        'Область / район': 'Kraj',
        'Почтовый индекс': 'PSČ',
        'Телефон': 'Telefón',
        'Примечание к заказу': 'Poznámka k objednávke',
        'Ваш заказ': 'Vaša objednávka',
        'Способ оплаты': 'Spôsob platby',
        'Прямой банковский перевод': 'Priamy bankový prevod',
        'Оплатите прямым банковским переводом на наш счёт. Заказ будет обработан после поступления оплаты.': 'Zaplaťte priamym prevodom na náš bankový účet. Objednávka bude spracovaná po prijatí platby.',
        'Оплата банковской картой': 'Platba bankovou kartou',
        'Доставка на другой адрес?': 'Doručiť na inú adresu?',
        'Отправить на другой адрес?': 'Doručiť na inú adresu?',
        'Доставка по другому адресу?': 'Doručiť na inú adresu?',
        // Checkout: terms, notes, tax
        'Я прочитал(а) и принимаю': 'Prečítal(a) som si a súhlasím s',
        'правила и условия': 'obchodné podmienky',
        'сайта': 'obchodu',
        'необязательно': 'nepovinné',
        'Примечания к заказу, например, указания по доставке.': 'Poznámky k objednávke, napr. pokyny k doručeniu.',
        'НДС': 'DPH',
        'включая': 'vrátane',
        // WooPayments
        'Card': 'Platba kartou',
        'Оплата картой': 'Platba kartou',
        // Packeta widget
        'Choose pickup point': 'Vybrať výdajné miesto',
        'Нет': 'Nie',
        'Ок': 'Ok',
        // Checkout misc
        'Сумма корзины': 'Súhrn košíka',
        'Изменить': 'Upraviť',
        'Изменить адрес': 'Zmeniť adresu',
        'ОФОРМИТЬ ЗАКАЗ': 'OBJEDNAŤ',
        // Header/footer/sidebar
        'Искать:': 'Hľadať:',
        'Найти:': 'Nájsť:',
        'корзина': 'košík',
        'Перейти к содержимому': 'Prejsť na obsah',
        'Архивы': 'Archívy',
        'Мета': 'Meta',
        'Рубрики': 'Kategórie',
        'Рубрик нет': 'Žiadne kategórie',
        'Прокрутить вверх': 'Posunúť nahor',
        'Кнопка Открыть': 'Otvoriť',
        'Кнопка Закрыть': 'Zavrieť',
        'Вход / Регистрация': 'Prihlásenie / Registrácia',
        // 404 page
        'Не найдено 404': 'Nenájdené 404',
        'Страница не найдена': 'Stránka nenájdená',
        'Похоже, Вы свернули не туда, не волнуйтесь, это случается с лучшими из нас.': 'Zdá sa, že ste zablúdili, nebojte sa, stáva sa to aj tým najlepším z nás.',
        'ВЕРНУТЬСЯ НАЗАД': 'VRÁTIŤ SA SPÄŤ',
        // Comment form (contact page)
        'Имя (обязательно)': 'Meno (povinné)',
        'Email (обязательно)': 'Email (povinné)',
        'Сайт': 'Web',
        'Загружаются Комментарии...': 'Načítavanie komentárov...',
        'Опубликовать комментарий...': 'Odoslať komentár...',
        // Country names
        'Словакия': 'Slovensko',
        'Австрия': 'Rakúsko',
        // Theme credits
        'Тема WordPress для продуктовых магазинов': 'WordPress téma pre obchody s potravinami',
        'От VWThemes': 'Od VWThemes',
        // Cart page extras
        'Удалить товар': 'Odstrániť produkt',
        'Миниатюра': 'Náhľad',
        // Checkout page extras
        'Уже покупали? Нажмите для входа': 'Už ste nakupovali? Kliknite pre prihlásenie',
        'Зарегистрировать вас?': 'Vytvoriť účet?',
        'Создать пароль учетной записи': 'Vytvoriť heslo účtu',
        'Обновить страну/регион': 'Aktualizovať krajinu/región',
        'Обновить итог': 'Aktualizovať celkom',
        'Показать пароль': 'Zobraziť heslo',
        'Скрыть пароль': 'Skryť heslo',
        'Нажмите здесь, чтобы войти': 'Kliknite sem pre prihlásenie',
        'Нажмите для входа': 'Kliknite pre prihlásenie',
        'Платёж': 'Platba',
        'Вы отложили': 'Pridali ste',
        'в свою корзину.': 'do košíka.',
        // Footer strings
        'Контакты': 'Kontakty',
        'Показать на карте': 'Zobraziť na mape',
        'Телефон:': 'Telefón:',
        'Часы работы': 'Otváracie hodiny',
        'Часы работы:': 'Otváracie hodiny:',
        'Пн — Пт:': 'Po — Pi:',
        'Сб:': 'So:',
        'Вс:': 'Ne:',
        'Закрыто': 'Zatvorené',
        'Информация': 'Informácie',
        'Условия торговли': 'Obchodné podmienky',
        'Защита персональных данных': 'Ochrana osobných údajov',
        'Рекламационный порядок': 'Reklamačný poriadok',
        'Вход со стороны ул. Palisády': 'Vstup z ulice Palisády',
        'Широкий ассортимент товаров из стран бывшего СССР в Братиславе.': 'Široký sortiment potravín z krajín bývalého ZSSR v Bratislave.',
        'Словацкая республика': 'Slovenská republika',
        'Зарегистрирована в торговом реестре окружного суда Братислава I,': 'Zapísaná v OR OS Bratislava I,',
        'Раздел: Sro, Вложка № 182562/B': 'Oddiel: Sro, Vložka č. 182562/B',
        // WooCommerce dynamic messages
        'Корзина обновлена.': 'Košík bol aktualizovaný.',
        'Товар удалён.': 'Produkt bol odstránený.',
        'Отменить?': 'Vrátiť späť?',
        'является обязательным полем.': 'je povinné pole.',
        'Введите корректный email': 'Zadajte platný email',
        'Выберите способ оплаты': 'Vyberte spôsob platby',
        'К сожалению, этого товара нет в наличии. Пожалуйста, выберите другие варианты товара.': 'Tento produkt nie je na sklade. Prosím, vyberte iné možnosti.',
        'не может быть добавлен в корзину': 'nemôže byť pridaný do košíka',
        'Пожалуйста, ознакомьтесь и примите правила и условия': 'Prečítajte si a prijmite obchodné podmienky',
        'Адрес:': 'Adresa:',
        'Плата за наложенный платеж': 'Poplatok za dobierku',
        // Shipping method names (from WC settings, originally in Slovak)
        'GLS доставка на адрес': 'GLS doručenie na adresu',
        'GLS Баликомат': 'GLS Balíkomat',
        'GLS ПарцелШоп': 'GLS ParcelShop',
        // Payment method names
        'Банковский перевод': 'Bankový prevod',
        'Сохранить платёжные данные для будущих покупок.': 'Uložiť platobné údaje pre budúce nákupy.',
        'Оплатите на счёт:': 'Zaplaťte na účet:',
        'В качестве переменного символа укажите номер заказа.': 'Ako variabilný symbol uveďte číslo objednávky.',
        'Заказ №': 'Objednávka č.',
        'был оформлен': 'bola vytvorená',
        'и находится в статусе': 'a je v stave',
        'Ожидается оплата': 'Čaká sa na platbu',
        'Информация о заказе': 'Informácie o objednávke',
        'Действия': 'Akcie',
        'Оплатить': 'Zaplatiť',
        'Отмена': 'Zrušiť',
        'Фактический вес': 'Skutočná hmotnosť',
        'Самовывоз': 'Osobne vyzdvihnutie',
        'Оплата после подтверждения веса': 'Platba po potvrdení hmotnosti',
    };

    // Day abbreviation map (for schedule text like "Пн      10:00 – 18:00")
    const DAY_MAP_SK = {'Пн': 'Po', 'Вт': 'Ut', 'Ср': 'St', 'Чт': 'Št', 'Пт': 'Pi', 'Сб': 'So', 'Вс': 'Ne'};
    const DAY_MAP_RU = {'Po': 'Пн', 'Ut': 'Вт', 'St': 'Ср', 'Št': 'Чт', 'Pi': 'Пт', 'So': 'Сб', 'Ne': 'Вс'};

    // Build reverse map
    const REVERSE = {};
    for (const [ru, sk] of Object.entries(TRANSLATIONS)) {
        REVERSE[sk] = ru;
    }

    const urlLang = new URLSearchParams(window.location.search).get('lang');
    let currentLang = (typeof window.gastronomForcedOrderLang !== 'undefined' && (window.gastronomForcedOrderLang === 'ru' || window.gastronomForcedOrderLang === 'sk'))
        ? window.gastronomForcedOrderLang
        : ((urlLang === 'ru' || urlLang === 'sk') ? urlLang : (localStorage.getItem('gls-lang') || 'sk'));
    localStorage.setItem('gls-lang', currentLang);
    // Sync language to cookie for server-side sorting
    document.cookie = 'gastronom_lang=' + currentLang + ';path=/;max-age=31536000';

    function init() {
        updateButtons();

        // Move switcher into header icon row
        const wishlistCol = document.querySelector('.wishlist.mt-2');
        const switcher = document.getElementById('gls-switcher');
        if (wishlistCol && switcher) {
            wishlistCol.appendChild(switcher);
            switcher.classList.add('gls-in-header');
        }

        // Apply all translations
        translateAll();

        // Bind button clicks
        document.querySelectorAll('.gls-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const lang = this.dataset.lang;
                if (lang === currentLang) return;
                currentLang = lang;
                localStorage.setItem('gls-lang', lang);
                document.cookie = 'gastronom_lang=' + lang + ';path=/;max-age=31536000';
                try {
                    const url = new URL(window.location.href);
                    url.searchParams.set('lang', lang);
                    window.location.href = url.toString();
                    return;
                } catch (e) {
                    location.reload();
                    return;
                }
            });
        });

        // Observe DOM changes for dynamically loaded content (debounced)
        let observerTimer = null;
        let isTranslating = false;
        const observer = new MutationObserver(function(mutations) {
            if (isTranslating) return;
            if (observerTimer) clearTimeout(observerTimer);
            observerTimer = setTimeout(function() {
                let needsRetranslate = false;
                for (const m of mutations) {
                    if (m.addedNodes.length > 0 || m.type === 'characterData') {
                        needsRetranslate = true;
                        break;
                    }
                }
                if (needsRetranslate) {
                    isTranslating = true;
                    translateAll();
                    isTranslating = false;
                }
            }, 200);
        });
        observer.observe(document.body, { childList: true, characterData: true, subtree: true });

        // Some theme/plugin blocks rewrite text after load; keep forcing the active language for a while.
        setTimeout(translateAll, 1500);
        setTimeout(translateAll, 3000);
        setTimeout(translateAll, 5000);
        setTimeout(translateAll, 8000);
        window.addEventListener('load', function() {
            setTimeout(translateAll, 100);
            setTimeout(translateAll, 1000);
        });
    }

    function translateAll() {
        processBilingualText();
        applyUITranslations(currentLang);
        normalizeCustomerOrderPages();
        fixFooterTypography();
        syncDocumentTitle();
        persistLanguageInLinks();
        // Switch free shipping banner language
        var bannerSk = document.querySelector('.gls-banner-sk');
        var bannerRu = document.querySelector('.gls-banner-ru');
        if (bannerSk && bannerRu) {
            bannerSk.style.display = currentLang === 'sk' ? '' : 'none';
            bannerRu.style.display = currentLang === 'ru' ? '' : 'none';
        }
        // Switch pick-up note language on cart
        document.querySelectorAll('.gls-pickup-note-sk').forEach(function(el) {
            el.style.display = currentLang === 'sk' ? '' : 'none';
        });
        document.querySelectorAll('.gls-pickup-note-ru').forEach(function(el) {
            el.style.display = currentLang === 'ru' ? '' : 'none';
        });
        // Switch bilingual page content blocks (use setProperty to override !important)
        document.querySelectorAll('.gls-content-sk').forEach(function(el) {
            el.style.setProperty('display', currentLang === 'sk' ? 'block' : 'none', 'important');
        });
        document.querySelectorAll('.gls-content-ru').forEach(function(el) {
            el.style.setProperty('display', currentLang === 'ru' ? 'block' : 'none', 'important');
        });
    }

    function normalizeCustomerOrderPages() {
        const path = window.location.pathname || '';
        const isCustomerOrderPage = path.includes('/my-account/view-order/') || path.includes('/checkout/order-pay/') || path.includes('/checkout/order-received/');
        if (!isCustomerOrderPage) return;

        const map = currentLang === 'ru'
            ? {
                'Môj Účet': 'Мой аккаунт',
                'Môj účet': 'Мой аккаунт',
                'Мой Аккаунт': 'Мой аккаунт',
                'Моя учётная запись': 'Мой аккаунт',
                'Produkt': 'Товар',
                'Množstvo': 'Кол-во',
                'Celkom': 'Итого',
                'Medzisúčet:': 'Подытог:',
                'Doprava:': 'Доставка:',
                'Spôsob platby:': 'Способ оплаты:',
                'Skutočná hmotnosť:': 'Фактический вес:',
                'Zaplatiť objednávku': 'Оплатить заказ',
                'Objednávka': 'Оформление заказа',
                'Informácie o objednávke': 'Информация о заказе',
                'Akcie:': 'Действия:',
                'Zrušiť': 'Отмена',
                'Platba po potvrdení hmotnosti': 'Оплата после подтверждения веса',
                'Osobne vyzdvihnutie': 'Самовывоз',
                'Čaká sa na platbu': 'Ожидается оплата',
            }
            : {
                'Мой Аккаунт': 'Môj účet',
                'Мой аккаунт': 'Môj účet',
                'Моя учётная запись': 'Môj účet',
                'Товар': 'Produkt',
                'Кол-во': 'Množstvo',
                'Итого': 'Celkom',
                'Подытог:': 'Medzisúčet:',
                'Доставка:': 'Doprava:',
                'Способ оплаты:': 'Spôsob platby:',
                'Фактический вес:': 'Skutočná hmotnosť:',
                'Оплатить заказ': 'Zaplatiť objednávku',
                'Оформление заказа': 'Objednávka',
                'Информация о заказе': 'Informácie o objednávke',
                'Действия:': 'Akcie:',
                'Отмена': 'Zrušiť',
                'Оплата после подтверждения веса': 'Platba po potvrdení hmotnosti',
                'Самовывоз': 'Osobne vyzdvihnutie',
                'Ожидается оплата': 'Čaká sa na platbu',
            };

        document.querySelectorAll('a, button, span, div, th, td, h1, h2, h3, h4, p, label').forEach(function(el) {
            if (el.closest('.gls-switcher')) return;
            if (!el.childNodes || el.children.length > 0) return;
            const text = el.textContent ? el.textContent.trim() : '';
            if (!text || !map[text]) return;
            el.textContent = map[text];
        });
    }

    function persistLanguageInLinks() {
        document.querySelectorAll('a[href]').forEach(function(link) {
            if (link.closest('.gls-switcher')) return;
            const href = link.getAttribute('href');
            if (!href) return;
            if (href.startsWith('#') || href.startsWith('mailto:') || href.startsWith('tel:') || href.startsWith('javascript:')) return;
            try {
                const url = new URL(href, window.location.origin);
                if (url.origin !== window.location.origin) return;
                if (url.pathname.startsWith('/wp-admin') || url.pathname.startsWith('/wp-json')) return;
                url.searchParams.set('lang', currentLang);
                const normalized = url.pathname + url.search + url.hash;
                link.setAttribute('href', normalized);
            } catch (e) {
                // Ignore malformed links.
            }
        });
    }

    function updateButtons() {
        document.querySelectorAll('.gls-btn').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.lang === currentLang);
        });
    }

    function fixFooterTypography() {
        const footer = document.querySelector('footer');
        if (!footer) return;

        const footerBrandHeading = footer.querySelector('h3');
        if (footerBrandHeading) {
            const headingText = footerBrandHeading.textContent.trim();
            if (currentLang === 'ru' && headingText === 'Gastronom') {
                footerBrandHeading.textContent = 'Гастроном';
            } else if (currentLang === 'sk' && headingText === 'Гастроном') {
                footerBrandHeading.textContent = 'Gastronom';
            }
        }

        footer.querySelectorAll('.gls-content-ru h3').forEach(el => {
            if (el.textContent.trim() === 'Часы Работы') {
                el.textContent = 'Часы работы';
            }
        });

        footer.querySelectorAll('.gls-content-ru p, .gls-content-ru a, .gls-content-ru strong').forEach(el => {
            if (el.childNodes && el.childNodes.length) {
                el.childNodes.forEach(node => {
                    if (node.nodeType !== Node.TEXT_NODE || !node.textContent) return;
                    node.textContent = node.textContent
                        .replaceAll('ул. Palisády', 'ул.\u00A0Palisády')
                        .replaceAll('Братислава I,', 'Братислава\u00A0I,')
                        .replaceAll('Словацкая республика', 'Словацкая Республика')
                        .replaceAll('Раздел: Sro, Вложка № 182562/B', 'Раздел s.r.o., № записи 182562/B')
                        .replaceAll('Раздел Sro, № записи 182562/B', 'Раздел s.r.o., № записи 182562/B')
                        .replaceAll('Раздел SRO, № записи 182562/B', 'Раздел s.r.o., № записи 182562/B');
                });
            }

            if (el.innerHTML) {
                el.innerHTML = el.innerHTML
                    .replaceAll('ул. Palisády', 'ул.&nbsp;Palisády')
                    .replaceAll('Братислава I,', 'Братислава&nbsp;I,')
                    .replaceAll('Словацкая республика', 'Словацкая Республика')
                    .replaceAll('Раздел: Sro, Вложка № 182562/B', 'Раздел s.r.o., № записи 182562/B')
                    .replaceAll('Раздел Sro, № записи 182562/B', 'Раздел s.r.o., № записи 182562/B')
                    .replaceAll('Раздел SRO, № записи 182562/B', 'Раздел s.r.o., № записи 182562/B');
            }
        });
    }

    function syncDocumentTitle() {
        const productHeading = document.querySelector('.product_title, h1.product_title');
        if (productHeading && productHeading.textContent.trim()) {
            document.title = productHeading.textContent.trim() + ' • ' + (currentLang === 'ru' ? 'Гастроном' : 'Gastronom');
            return;
        }
        if (!document.title) return;
        if (currentLang === 'ru') {
            document.title = document.title
                .replace(/^Košík/, 'Корзина')
                .replace(/^Objednávka/, 'Оформление заказа')
                .replace(/^Domov/, 'Главная')
                .replace(/^Môj účet/, 'Мой аккаунт')
                .replace(/^Kontakt\b/, 'Контакты')
                .replace(/^Контакт\b/, 'Контакты')
                .replace('Dostavka a platba', 'Доставка и оплата')
                .replace('Doprava a platba', 'Доставка и оплата')
                .replace('obchod s ruskými potravinami v Bratislave', 'русский магазин продуктов в Братиславе');
            for (const [sk, ru] of Object.entries(REVERSE)) {
                if (document.title.includes(sk)) {
                    document.title = document.title.replaceAll(sk, ru);
                }
            }
        } else {
            document.title = document.title
                .replace(/^Корзина/, 'Košík')
                .replace(/^Оформление заказа/, 'Objednávka')
                .replace(/^Главная/, 'Domov')
                .replace(/^Мой аккаунт/, 'Môj účet')
                .replace(/^Контакты\b/, 'Kontakt')
                .replace('Dostavka a platba', 'Doprava a platba')
                .replace('русский магазин продуктов в Братиславе', 'obchod s ruskými potravinami v Bratislave');
            for (const [ru, sk] of Object.entries(TRANSLATIONS)) {
                if (document.title.includes(ru)) {
                    document.title = document.title.replaceAll(ru, sk);
                }
            }
        }
    }

    /**
     * Process bilingual text with "/" or "." separator
     * Splits "Русский текст/ Slovak text" into just one language
     */
    function processBilingualText() {
        const selectors = [
            // Product names
            '.woocommerce-loop-product__title',
            '.product_title',
            '.wc-block-grid__product-title',
            '.product-title',
            // Category names
            '.product_cat',
            '.posted_in a',
            '.product_meta a',
            '.widget_product_categories a',
            '.product-category-title',
            '.cat-item a',
            '.product-categories a',
            '.wp-block-woocommerce-product-categories a',
            // Category dropdown in search form (theme-specific)
            '.drp_dwn_menu a',
            '.product-cat a',
            // Headings and content
            'h1', 'h2', 'h3', 'h4',
            '.entry-title',
            '.entry-content p',
            '.wp-block-cover p',
            '.wp-block-button__link',
            'p.has-text-align-center',
            // Breadcrumbs
            '.woocommerce-breadcrumb a',
            '.woocommerce-breadcrumb',
            'nav.woocommerce-breadcrumb',
            // Other
            '.term-description',
            // Cart product names
            '.cart_item .product-name a',
            'td.product-name a',
            '.product-name',
            '.woocommerce-checkout-review-order-table .product-name',
            '.woocommerce-checkout-review-order-table td',
            '.woocommerce-order-details .product-name',
        ];

        document.querySelectorAll(selectors.join(',')).forEach(el => {
            if (el.closest('.gls-switcher')) return;
            processElement(el);
        });
    }

    function processElement(el) {
        const walker = document.createTreeWalker(el, NodeFilter.SHOW_TEXT, null, false);
        const textNodes = [];
        while (walker.nextNode()) textNodes.push(walker.currentNode);

        textNodes.forEach(node => {
            const text = node.textContent;
            if (node._glsOriginal) {
                // Already processed — just apply current language
                const chosen = currentLang === 'ru' ? node._glsRu : node._glsSk;
                node.textContent = (node._glsPrefix || '') + chosen;
                return;
            }

            if (text.trim().length < 3) return;

            // Try " / " separator first (standard product name format: "Slovak / Russian")
            if (text.includes(' / ')) {
                const spParts = text.split(' / ');
                if (spParts.length === 2) {
                    const left = spParts[0].trim();
                    const right = spParts[1].trim();
                    if (left && right) {
                        const leftCyr = /[а-яА-ЯёЁ]/.test(left);
                        const rightCyr = /[а-яА-ЯёЁ]/.test(right);
                        if (leftCyr !== rightCyr) {
                            node._glsOriginal = text;
                            node._glsPrefix = '';
                            node._glsRu = leftCyr ? left : right;
                            node._glsSk = leftCyr ? right : left;
                            const chosen = currentLang === 'ru' ? node._glsRu : node._glsSk;
                            node.textContent = chosen;
                            return;
                        }
                    }
                }
            }

            // Fallback: try "/" separator (for names like "Русский/ Slovak")
            if (text.includes('/')) {
                const result = trySplitBilingual(text, '/');
                if (result) {
                    node._glsOriginal = text;
                    node._glsPrefix = result.prefix;
                    node._glsRu = result.ru;
                    node._glsSk = result.sk;
                    const chosen = currentLang === 'ru' ? node._glsRu : node._glsSk;
                    node.textContent = (node._glsPrefix || '') + chosen;
                    return;
                }
            }

            // Try ". " separator (period+space between two language parts)
            // Pattern: "Slovak text. Русский текст" or "Русский текст. Slovak text"
            if (/[а-яА-ЯёЁ]/.test(text) && /[a-zA-ZáčďéíľňóŕšťúýžÁČĎÉÍĽŇÓŔŠŤÚÝŽ]/.test(text)) {
                const result = trySplitByDot(text);
                if (result) {
                    node._glsOriginal = text;
                    node._glsPrefix = '';
                    node._glsRu = result.ru;
                    node._glsSk = result.sk;
                    const chosen = currentLang === 'ru' ? node._glsRu : node._glsSk;
                    node.textContent = chosen;
                    return;
                }
            }
        });
    }

    function trySplitBilingual(text, separator) {
        let textToSplit = text;
        let prefix = '';

        // Handle breadcrumb prefix: " / Конфеты/ Bonbóny"
        if (separator === '/') {
            const bcMatch = text.match(/^(\s*\/\s*)(.*\/.*)/);
            if (bcMatch) {
                prefix = bcMatch[1];
                textToSplit = bcMatch[2];
            }
        }

        const parts = textToSplit.split(separator);
        if (parts.length !== 2) return null;

        const left = parts[0].trim();
        const right = parts[1].trim();
        if (!left || !right) return null;

        // At least one part must have Cyrillic
        const leftCyr = /[а-яА-ЯёЁ]/.test(left);
        const rightCyr = /[а-яА-ЯёЁ]/.test(right);
        if (!leftCyr && !rightCyr) return null;

        return {
            prefix: prefix,
            ru: leftCyr ? left : right,
            sk: leftCyr ? right : left,
        };
    }

    function trySplitByDot(text) {
        // Find the boundary between Cyrillic and Latin parts
        // Look for ". " followed by a language switch
        const dotParts = text.split(/\.\s+/);
        if (dotParts.length < 2) return null;

        // Try to find where language switches
        for (let i = 1; i < dotParts.length; i++) {
            const before = dotParts.slice(0, i).join('. ');
            const after = dotParts.slice(i).join('. ');

            const beforeCyr = /[а-яА-ЯёЁ]/.test(before);
            const afterCyr = /[а-яА-ЯёЁ]/.test(after);
            const beforeLat = /[a-zA-ZáčďéíľňóŕšťúýžÁČĎÉÍĽŇÓŔŠŤÚÝŽ]/.test(before);
            const afterLat = /[a-zA-ZáčďéíľňóŕšťúýžÁČĎÉÍĽŇÓŔŠŤÚÝŽ]/.test(after);

            // Language switch: one part is mostly Cyrillic, other is mostly Latin
            if (beforeCyr && !beforeLat && afterLat && !afterCyr) {
                return { ru: before, sk: after };
            }
            if (beforeLat && !beforeCyr && afterCyr && !afterLat) {
                return { ru: after, sk: before };
            }
        }
        return null;
    }

    function applyUITranslations(lang) {
        const map = lang === 'sk' ? TRANSLATIONS : REVERSE;

        // Translate buttons, links, and other UI text
        const uiSelectors = [
            'a', 'button', 'span', 'label', 'th', 'td',
            'h1', 'h2', 'h3',
            '.woocommerce-breadcrumb',
            '.button', '.btn', 'input[type="submit"]', 'input[type="button"]',
            '.woocommerce-message', '.woocommerce-info', '.woocommerce-error',
            '.price', '.amount',
            '.order-total', '.cart-subtotal',
            // My Account
            '.woocommerce-MyAccount-navigation a',
            '.woocommerce-MyAccount-content p',
            '.woocommerce-MyAccount-content li',
            '.woocommerce form label',
            '.woocommerce-form-login label',
            '.woocommerce-form-register label',
            '.lost_password a',
            // Cart
            '.cart-empty', '.return-to-shop a',
            '.cart_item td',
            // Checkout order review table
            '.woocommerce-checkout-review-order-table th',
            '.woocommerce-checkout-review-order-table td',
            '.shop_table th', '.shop_table td',
            '.woocommerce-order-overview li',
            '.woocommerce-order-overview strong',
            // Sidebar/footer
            '.widget-title', '.widget_categories a', '.widget_archive a',
            '.copy-right', '.copy-right p', '.site-info',
            '.screen-reader-text',
            // 404
            '.error-404 p', '.error-404 a',
        ];

        document.querySelectorAll(uiSelectors.join(',')).forEach(el => {
            if (el.closest('.gls-switcher')) return;

            if (el.tagName === 'INPUT') {
                const val = el.value;
                if (map[val]) {
                    if (!el._glsOriginalValue) el._glsOriginalValue = val;
                    el.value = map[val];
                }
                return;
            }

            el.childNodes.forEach(child => {
                if (child.nodeType === Node.TEXT_NODE) {
                    const text = child.textContent.trim();
                    if (map[text]) {
                        if (!child._glsUIOriginal) child._glsUIOriginal = child.textContent;
                        child.textContent = child.textContent.replace(text, map[text]);
                    }
                }
            });
        });

        // Translate placeholder attributes
        document.querySelectorAll('input[placeholder], textarea[placeholder]').forEach(el => {
            const ph = el.getAttribute('placeholder');
            if (map[ph]) {
                if (!el._glsOriginalPH) el._glsOriginalPH = ph;
                el.setAttribute('placeholder', map[ph]);
            }
        });

        // Translate select options (sorting dropdown, category filter)
        document.querySelectorAll('select option').forEach(opt => {
            const text = opt.textContent.trim();
            if (map[text]) {
                if (!opt._glsOriginal) opt._glsOriginal = opt.textContent;
                opt.textContent = map[text];
            }
        });

        // Translate WooCommerce result count
        // Handles: "Показаны все результаты (3)", "Отображение 1–12 из 49 результатов"
        document.querySelectorAll('.woocommerce-result-count').forEach(el => {
            // Get only the text content (before any child elements like span)
            const firstText = el.firstChild;
            if (!firstText || firstText.nodeType !== Node.TEXT_NODE) return;
            const text = firstText.textContent.trim();

            if (lang === 'sk' && /[а-яА-ЯёЁ]/.test(text)) {
                if (!firstText._glsRCOriginal) firstText._glsRCOriginal = firstText.textContent;
                let translated = firstText.textContent
                    .replace('Показаны все результаты', 'Zobrazené všetky výsledky')
                    .replace('Показано', 'Zobrazených')
                    .replace('Отображение', 'Zobrazených')
                    .replace(' из ', ' z ')
                    .replace(' результатов', ' výsledkov')
                    .replace(' результат', ' výsledok');
                firstText.textContent = translated;
            } else if (lang === 'ru' && firstText._glsRCOriginal) {
                firstText.textContent = firstText._glsRCOriginal;
            }
        });

        // Translate screen-reader-text inside result count (sorting info)
        document.querySelectorAll('.woocommerce-result-count .screen-reader-text').forEach(el => {
            const text = el.textContent.trim();
            if (lang === 'sk' && /[а-яА-ЯёЁ]/.test(text)) {
                if (!el._glsSortOriginal) el._glsSortOriginal = text;
                el.textContent = text
                    .replace('Сортировка:', 'Zoradenie:')
                    .replace('по популярности', 'podľa popularity')
                    .replace('по рейтингу', 'podľa hodnotenia')
                    .replace('по новизне', 'podľa novosti')
                    .replace('по цене', 'podľa ceny')
                    .replace('по умолчанию', 'predvolené')
                    .replace('по возрастанию цены', 'podľa ceny vzostupne')
                    .replace('по убыванию цены', 'podľa ceny zostupne');
            } else if (lang === 'ru' && el._glsSortOriginal) {
                el.textContent = el._glsSortOriginal;
            }
        });

        // Final wording normalization for visible RU/SK labels.
        document.querySelectorAll('a, button, span, h1, h2, h3, h4, .entry-title, .page-title, .menu-item a').forEach(el => {
            if (el.closest('.gls-switcher')) return;
            const text = el.textContent ? el.textContent.trim() : '';
            if (!text) return;

            if (lang === 'ru') {
                if (text === 'Контакт') el.textContent = 'Контакты';
                if (text === 'Мой Аккаунт' || /^Мой\s+Аккаунт$/u.test(text)) el.textContent = 'Мой аккаунт';
            } else {
                if (text === 'Контакты') el.textContent = 'Kontakt';
                if (text === 'Мой аккаунт' || /^Мой\s+аккаунт$/u.test(text)) el.textContent = 'Môj účet';
            }
        });

        document.querySelectorAll('nav a, .main-navigation a, .primary-menu a').forEach(el => {
            const text = el.textContent ? el.textContent.trim() : '';
            if (lang === 'ru' && (text === 'Мой Аккаунт' || /^Мой\s+Аккаунт$/u.test(text))) {
                el.textContent = 'Мой аккаунт';
            }
            if (lang === 'sk' && (text === 'Мой аккаунт' || /^Мой\s+аккаунт$/u.test(text))) {
                el.textContent = 'Môj účet';
            }
        });

        document.querySelectorAll('a, span, h1, h2, h3, .page-title, .entry-title, .woocommerce-MyAccount-navigation a').forEach(el => {
            const text = el.textContent ? el.textContent.trim() : '';
            if (!text) return;

            if (lang === 'ru') {
                if (text === 'Мой Аккаунт' || /^Мой\s+Аккаунт$/u.test(text)) el.textContent = 'Мой аккаунт';
                if (text === 'Моя учётная запись' || /^Моя\s+уч[её]тная\s+запись$/u.test(text)) el.textContent = 'Мой аккаунт';
            } else {
                if (text === 'Мой аккаунт' || /^Мой\s+аккаунт$/u.test(text)) el.textContent = 'Môj účet';
                if (text === 'Моя учётная запись' || /^Моя\s+уч[её]тная\s+запись$/u.test(text)) el.textContent = 'Môj účet';
            }
        });

        document.querySelectorAll('a, button, span, div').forEach(el => {
            if (!el.innerHTML || el.closest('.gls-switcher')) return;
            if (lang === 'ru') {
                el.innerHTML = el.innerHTML.replaceAll('Мой Аккаунт', 'Мой аккаунт');
            } else {
                el.innerHTML = el.innerHTML.replaceAll('Môj Účet', 'Môj účet');
            }
        });

        // Translate breadcrumb text nodes
        document.querySelectorAll('.woocommerce-breadcrumb, nav.woocommerce-breadcrumb').forEach(bc => {
            bc.childNodes.forEach(child => {
                if (child.nodeType === Node.TEXT_NODE && child.textContent.trim()) {
                    let txt = child.textContent;
                    for (const [from, to] of Object.entries(map)) {
                        if (txt.includes(from)) {
                            if (!child._glsBCOriginal) child._glsBCOriginal = txt;
                            txt = txt.replace(from, to);
                        }
                    }
                    if (child._glsBCOriginal) child.textContent = txt;
                }
            });
        });

        // Translate day abbreviations and partial-match strings
        const dayMap = lang === 'sk' ? DAY_MAP_SK : DAY_MAP_RU;
        const partialMap = lang === 'sk'
            ? {
                'Тел. номер:': 'Tel. číslo:',
                'Тел. номер': 'Tel. číslo',
                'Словацкая республика': 'Slovenská republika',
                'Зарегистрирована в торговом реестре окружного суда Братислава I,': 'Zapísaná v OR OS Bratislava I,',
                'Раздел: Sro, Вложка № 182562/B': 'Oddiel: Sro, Vložka č. 182562/B',
                'Доставка и оплата': 'Doprava a platba',
                'Dostavka a platba': 'Doprava a platba',
                'Оплатите на счёт:': 'Zaplaťte na účet:',
                'В качестве переменного символа укажите номер заказа.': 'Ako variabilný symbol uveďte číslo objednávky.'
            }
            : {
                'Tel. číslo:': 'Тел. номер:',
                'Tel. číslo': 'Тел. номер',
                'Slovenská republika': 'Словацкая республика',
                'Zapísaná v OR OS Bratislava I,': 'Зарегистрирована в торговом реестре окружного суда Братислава I,',
                'Oddiel: Sro, Vložka č. 182562/B': 'Раздел: Sro, Вложка № 182562/B',
                'Dostavka a platba': 'Доставка и оплата',
                'Doprava a platba': 'Доставка и оплата',
                'Zaplaťte na účet:': 'Оплатите на счёт:',
                'Ako variabilný symbol uveďte číslo objednávky.': 'В качестве переменного символа укажите номер заказа.'
            };
        document.querySelectorAll('.elementor-widget-text-editor span, .entry-content span, .entry-content p, footer p, footer div, .copy-right, .copy-right p, .site-info, .footer-widget-area p, .footer-widget-area div').forEach(el => {
            if (el.closest('.gls-switcher')) return;
            el.childNodes.forEach(child => {
                if (child.nodeType === Node.TEXT_NODE) {
                    let txt = child.textContent;
                    let changed = false;
                    for (const [from, to] of Object.entries(dayMap)) {
                        const re = new RegExp('^(\\s*)' + from + '(\\s)');
                        if (re.test(txt)) {
                            if (!child._glsDayOriginal) child._glsDayOriginal = txt;
                            txt = txt.replace(from, to);
                            changed = true;
                            break;
                        }
                    }
                    for (const [from, to] of Object.entries(partialMap)) {
                        if (txt.includes(from)) {
                            if (!child._glsPartialOriginal) child._glsPartialOriginal = txt;
                            txt = txt.replace(from, to);
                            changed = true;
                        }
                    }
                    if (changed) child.textContent = txt;
                }
            });
        });

        // Translate My Account dashboard text (partial replacements for mixed text nodes)
        document.querySelectorAll('.woocommerce-MyAccount-content p, .woocommerce-MyAccount-content li').forEach(el => {
            el.childNodes.forEach(child => {
                if (child.nodeType === Node.TEXT_NODE && child.textContent.trim().length > 2) {
                    let txt = child.textContent;
                    const hasCyr = /[а-яА-ЯёЁ]/.test(txt);
                    if (lang === 'sk' && hasCyr) {
                        if (!child._glsAccOriginal) child._glsAccOriginal = txt;
                        txt = txt
                            .replace('Добро пожаловать,', 'Vitajte,')
                            .replace('(не', '(nie')
                            .replace('Из главной страницы аккаунта вы можете посмотреть ваши', 'Na hlavnej stránke účtu si môžete pozrieť')
                            .replace(', настроить', ', nastaviť')
                            .replace(', а также', ', a tiež');
                        child.textContent = txt;
                    } else if (lang === 'ru' && child._glsAccOriginal) {
                        child.textContent = child._glsAccOriginal;
                    }
                }
            });
        });

        // Translate checkout-specific partial texts
        document.querySelectorAll('.woocommerce-checkout, .woocommerce-cart, .woocommerce form, .order-total, .tax-total, .cart-subtotal, .fee, .woocommerce-terms-and-conditions-wrapper, .woocommerce-shipping-fields, .woocommerce-additional-fields').forEach(el => {
            el.childNodes.forEach(child => {
                if (child.nodeType === Node.TEXT_NODE && child.textContent.trim().length > 1) {
                    let txt = child.textContent;
                    const hasCyr = /[а-яА-ЯёЁ]/.test(txt);
                    if (lang === 'sk' && hasCyr) {
                        if (!child._glsCheckoutOriginal) child._glsCheckoutOriginal = txt;
                        txt = txt
                            .replace('включая', 'vrátane')
                            .replace('НДС', 'DPH')
                            .replace('Я прочитал(а) и принимаю', 'Prečítal(a) som si a súhlasím s')
                            .replace('правила и условия', 'obchodné podmienky')
                            .replace('сайта', 'obchodu')
                            .replace('необязательно', 'nepovinné')
                            .replace('Доставка по другому адресу?', 'Doručiť na inú adresu?');
                        child.textContent = txt;
                    } else if (lang === 'ru' && child._glsCheckoutOriginal) {
                        child.textContent = child._glsCheckoutOriginal;
                    }
                }
            });
            // Also check nested elements
            el.querySelectorAll('label, span, small, p').forEach(sub => {
                sub.childNodes.forEach(child => {
                    if (child.nodeType === Node.TEXT_NODE && child.textContent.trim().length > 1) {
                        let txt = child.textContent;
                        const hasCyr = /[а-яА-ЯёЁ]/.test(txt);
                        if (lang === 'sk' && hasCyr) {
                            if (!child._glsCheckoutOriginal) child._glsCheckoutOriginal = txt;
                            txt = txt
                                .replace('включая', 'vrátane')
                                .replace('НДС', 'DPH')
                                .replace('Я прочитал(а) и принимаю', 'Prečítal(a) som si a súhlasím s')
                                .replace('правила и условия', 'obchodné podmienky')
                                .replace('сайта', 'obchodu')
                                .replace('необязательно', 'nepovinné')
                                .replace('Доставка по другому адресу?', 'Doručiť na inú adresu?');
                            child.textContent = txt;
                        } else if (lang === 'ru' && child._glsCheckoutOriginal) {
                            child.textContent = child._glsCheckoutOriginal;
                        }
                    }
                });
            });
        });

        // Translate cart notices (dynamic: "Вы отложили X в свою корзину.")
        document.querySelectorAll('.woocommerce-message, .woocommerce-info').forEach(el => {
            el.childNodes.forEach(child => {
                if (child.nodeType === Node.TEXT_NODE && child.textContent.trim().length > 2) {
                    let txt = child.textContent;
                    if (lang === 'sk' && /[а-яА-ЯёЁ]/.test(txt)) {
                        if (!child._glsNoticeOriginal) child._glsNoticeOriginal = txt;
                        txt = txt
                            .replace('Вы отложили', 'Pridali ste')
                            .replace('в свою корзину.', 'do košíka.')
                            .replace('Варианты доставки будут обновлены при оформлении заказа.', 'Možnosti dopravy budú aktualizované pri dokončení objednávky.')
                            .replace('Нажмите здесь, чтобы войти', 'Kliknite sem pre prihlásenie')
                            .replace('Уже покупали?', 'Už ste nakupovali?');
                        child.textContent = txt;
                    } else if (lang === 'ru' && child._glsNoticeOriginal) {
                        child.textContent = child._glsNoticeOriginal;
                    }
                }
            });
        });

        // Translate shipping destination text on cart
        document.querySelectorAll('.woocommerce-shipping-destination').forEach(el => {
            el.childNodes.forEach(child => {
                if (child.nodeType === Node.TEXT_NODE) {
                    let txt = child.textContent;
                    if (lang === 'sk' && /[а-яА-ЯёЁ]/.test(txt)) {
                        if (!child._glsShipDestOriginal) child._glsShipDestOriginal = txt;
                        txt = txt
                            .replace('Доставка до ближайшего пункта рядом с', 'Doprava na najbližšie miesto pri')
                            .replace('Изменить адрес', 'Zmeniť adresu');
                        child.textContent = txt;
                    } else if (lang === 'ru' && child._glsShipDestOriginal) {
                        child.textContent = child._glsShipDestOriginal;
                    }
                }
            });
            // Also translate nested links like "Изменить адрес"
            el.querySelectorAll('a').forEach(a => {
                if (lang === 'sk' && a.textContent.trim() === 'Изменить адрес') {
                    if (!a._glsOrigText) a._glsOrigText = a.textContent;
                    a.textContent = 'Zmeniť adresu';
                } else if (lang === 'ru' && a._glsOrigText) {
                    a.textContent = a._glsOrigText;
                }
            });
        });

        // Translate shipping method labels (contains-match for labels with prices)
        const SHIP_LABELS = {
            'GLS doručenie na adresu': 'GLS доставка на адрес',
            'GLS Balíkomat': 'GLS Баликомат',
            'GLS ParcelShop': 'GLS ПарцелШоп',
            'SK Packeta Pick-up Point (Z-Point, Z-Box)': 'Пункт выдачи Packeta (Z-Point, Z-Box)',
            'Osobne vyzdvihnutie': 'Личный самовывоз',
        };
        const shipLabelMap = lang === 'ru' ? SHIP_LABELS : (() => { const r = {}; for (const [k,v] of Object.entries(SHIP_LABELS)) r[v] = k; return r; })();

        document.querySelectorAll('#shipping_method label, .woocommerce-shipping-methods label').forEach(label => {
            if (!label.dataset.glsOriginalHtml) label.dataset.glsOriginalHtml = label.innerHTML;
            let html = label.dataset.glsOriginalHtml;
            for (const [from, to] of Object.entries(shipLabelMap)) {
                if (html.includes(from)) html = html.replaceAll(from, to);
            }
            label.innerHTML = html;
        });

        // Translate payment method labels
        const PAY_LABELS = {
            'Bankový prevod': 'Банковский перевод',
            'Platba pri doručení': 'Оплата при доставке',
            'Platba kartou': 'Оплата картой',
            'Card': 'Оплата картой',
            'Zaplaťte priamym prevodom na náš bankový účet. Objednávka bude spracovaná po prijatí platby.': 'Оплатите прямым банковским переводом на наш счёт. Заказ будет обработан после поступления оплаты.',
            'K objednávke bude pripočítaný poplatok za dobierku vo výške 2,00 €.': 'К заказу будет добавлена доплата за наложенный платеж 2,00 €.',
            'K objednÃ¡vke bude pripoÄÃ­tanÃ½ poplatok za dobierku vo vÃ½Å¡ke 2,00 â¬.': 'К заказу будет добавлена доплата за наложенный платеж 2,00 €.'
        };
        const payLabelMap = lang === 'ru' ? PAY_LABELS : (() => { const r = {}; for (const [k,v] of Object.entries(PAY_LABELS)) r[v] = k; return r; })();

        document.querySelectorAll('.wc_payment_method label').forEach(label => {
            if (!label.dataset.glsOriginalHtml) label.dataset.glsOriginalHtml = label.innerHTML;
            let html = label.dataset.glsOriginalHtml;
            for (const [from, to] of Object.entries(payLabelMap)) {
                if (html.includes(from)) html = html.replaceAll(from, to);
            }
            label.innerHTML = html;
        });

        // Translate payment descriptions
        document.querySelectorAll('.payment_box p').forEach(p => {
            const txt = p.textContent.trim();
            for (const [from, to] of Object.entries(payLabelMap)) {
                if (txt.includes(from)) {
                    p.textContent = txt.replace(from, to);
                }
            }
        });

        const codFeeMap = lang === 'ru'
            ? {
                'Poplatok za dobierku': 'Доплата за наложенный платеж',
                'K objednávke bude pripočítaný poplatok za dobierku vo výške 2,00 €.': 'К заказу будет добавлена доплата за наложенный платеж 2,00 €.',
                'K objednÃ¡vke bude pripoÄÃ­tanÃ½ poplatok za dobierku vo vÃ½Å¡ke 2,00 â¬.': 'К заказу будет добавлена доплата за наложенный платеж 2,00 €.'
            }
            : {
                'Доплата за наложенный платеж': 'Poplatok za dobierku',
                'К заказу будет добавлена доплата за наложенный платеж 2,00 €.': 'K objednávke bude pripočítaný poplatok za dobierku vo výške 2,00 €.'
            };

        document.querySelectorAll('.woocommerce-checkout-review-order-table td, .woocommerce-checkout-review-order-table th, .woocommerce-order-overview *, .woocommerce-order-details *').forEach(el => {
            if (!el || !el.childNodes) return;
            el.childNodes.forEach(child => {
                if (child.nodeType !== Node.TEXT_NODE) return;
                let txt = child.textContent;
                for (const [from, to] of Object.entries(codFeeMap)) {
                    if (txt.includes(from)) {
                        child.textContent = txt.replaceAll(from, to);
                        txt = child.textContent;
                    }
                }
            });
        });

        // Translate "— OR —" separator (WooPayments express checkout)
        document.querySelectorAll('#wc-stripe-payment-request-wrapper, .wcpay-payment-request-wrapper, .wc-payment-request-wrapper, [id*="payment-request"]').forEach(el => {
            const parent = el.parentElement;
            if (parent) {
                parent.childNodes.forEach(child => {
                    if (child.nodeType === Node.TEXT_NODE || (child.nodeType === Node.ELEMENT_NODE && child.textContent.trim() === '— OR —')) {
                        if (child.textContent.includes('— OR —')) {
                            if (lang === 'sk') child.textContent = child.textContent.replace('— OR —', '— ALEBO —');
                            if (lang === 'ru') child.textContent = child.textContent.replace('— OR —', '— ИЛИ —');
                        }
                    }
                });
            }
        });
        // Also try direct text replacement for "— OR —"
        document.querySelectorAll('p, span, div').forEach(el => {
            if (el.children.length === 0 && el.textContent.trim() === '— OR —') {
                if (lang === 'sk') el.textContent = '— ALEBO —';
                if (lang === 'ru') el.textContent = '— ИЛИ —';
            }
        });

        document.querySelectorAll('button').forEach(btn => {
            const text = btn.textContent.trim();
            if (map[text]) btn.textContent = map[text];
        });
        document.querySelectorAll('h1, h2, .entry-title').forEach(el => {
            const text = el.textContent.trim();
            if (lang === 'ru' && (text === 'Dostavka a platba' || text === 'Doprava a platba')) {
                el.textContent = 'Доставка и оплата';
            }
            if (lang === 'sk' && text === 'Dostavka a platba') {
                el.textContent = 'Doprava a platba';
            }
        });

        // Force cookie consent buttons to follow the currently selected language.
        document.querySelectorAll('button, a, span, div').forEach(el => {
            if (el.children.length > 0) return;
            const text = el.textContent.trim();
            if (!text) return;
            if (lang === 'ru') {
                if (text === 'Nie') el.textContent = 'Нет';
                if (text === 'Ok') el.textContent = 'Ок';
            } else {
                if (text === 'Нет') el.textContent = 'Nie';
                if (text === 'Ок') el.textContent = 'Ok';
            }
        });
        const cookieAccept = document.getElementById('cn-accept-cookie');
        const cookieClose = document.getElementById('cn-close-notice');
        if (cookieAccept) {
            cookieAccept.textContent = lang === 'ru' ? 'Ок' : 'Ok';
            cookieAccept.setAttribute('aria-label', lang === 'ru' ? 'Ок' : 'Ok');
        }
        if (cookieClose) {
            cookieClose.setAttribute('aria-label', lang === 'ru' ? 'Нет' : 'Nie');
            cookieClose.setAttribute('title', lang === 'ru' ? 'Нет' : 'Nie');
        }
        document.querySelectorAll('button.show-password-input').forEach(function(btn) {
            btn.setAttribute('aria-label', lang === 'ru' ? 'Показать пароль' : 'Zobraziť heslo');
            btn.setAttribute('title', lang === 'ru' ? 'Показать пароль' : 'Zobraziť heslo');
        });

        // Translate Packeta "Choose pickup point" button
        document.querySelectorAll('.packeta-selector-open, .packeta-widget-button, [class*="packeta"] button, button').forEach(btn => {
            if (btn.textContent.trim() === 'Choose pickup point') {
                if (lang === 'sk') btn.textContent = 'Vybrať výdajné miesto';
            } else if (btn.textContent.trim() === 'Vybrať výdajné miesto') {
                if (lang === 'ru') btn.textContent = 'Choose pickup point';
            }
        });

        // Translate the category dropdown button text
        document.querySelectorAll('.product-btn').forEach(btn => {
            btn.childNodes.forEach(child => {
                if (child.nodeType === Node.TEXT_NODE) {
                    const text = child.textContent.trim();
                    if (map[text]) {
                        if (!child._glsBtnOriginal) child._glsBtnOriginal = child.textContent;
                        child.textContent = child.textContent.replace(text, map[text]);
                    }
                }
            });
        });

        document.querySelectorAll('body *').forEach(el => {
            if (!el.childNodes || el.closest('.gls-switcher')) return;
            el.childNodes.forEach(child => {
                if (child.nodeType !== Node.TEXT_NODE) return;
                let txt = child.textContent;
                if (lang === 'ru' && txt.includes('Zaplaťte na účet:')) {
                    child.textContent = txt.replaceAll('Zaplaťte na účet:', 'Оплатите на счёт:');
                    txt = child.textContent;
                }
                if (lang === 'ru' && txt.includes('Ako variabilný symbol uveďte číslo objednávky.')) {
                    child.textContent = txt.replaceAll('Ako variabilný symbol uveďte číslo objednávky.', 'В качестве переменного символа укажите номер заказа.');
                    txt = child.textContent;
                }
                if (lang === 'sk' && txt.includes('Оплатите на счёт:')) {
                    child.textContent = txt.replaceAll('Оплатите на счёт:', 'Zaplaťte na účet:');
                    txt = child.textContent;
                }
                if (lang === 'sk' && txt.includes('В качестве переменного символа укажите номер заказа.')) {
                    child.textContent = txt.replaceAll('В качестве переменного символа укажите номер заказа.', 'Ako variabilný symbol uveďte číslo objednávky.');
                }
            });
        });
        document.querySelectorAll('.stock').forEach(function(el) {
            const txt = el.textContent.trim();
            if (lang === 'sk' && txt === 'В наличии') el.textContent = 'Na sklade';
            if (lang === 'ru' && txt === 'Na sklade') el.textContent = 'В наличии';
        });
        document.querySelectorAll('.quantity .qty, input.qty').forEach(function(input) {
            const heading = document.querySelector('.product_title, h1.product_title');
            const productName = heading ? heading.textContent.trim() : '';
            input.setAttribute('aria-label', (currentLang === 'ru' ? 'Количество товара' : 'Množstvo produktu') + (productName ? ' ' + productName : ''));
        });
        document.querySelectorAll('.quantity .screen-reader-text').forEach(function(label) {
            const heading = document.querySelector('.product_title, h1.product_title');
            const productName = heading ? heading.textContent.trim() : '';
            label.textContent = (currentLang === 'ru' ? 'Количество товара' : 'Množstvo produktu') + (productName ? ' ' + productName : '');
        });
        document.querySelectorAll('label[for^="quantity_"]').forEach(function(label) {
            const heading = document.querySelector('.product_title, h1.product_title');
            const productName = heading ? heading.textContent.trim() : '';
            label.textContent = (currentLang === 'ru' ? 'Количество товара' : 'Množstvo produktu') + (productName ? ' ' + productName : '');
        });
    }

    // Visually move Google Pay / Apple Pay express checkout below payment methods using CSS
    // (DOM move breaks Stripe initialization, so we use CSS flexbox order instead)
    function styleExpressCheckout() {
        var wrapper = document.querySelector('.wcpay-express-checkout-wrapper');
        var separator = document.getElementById('wcpay-express-checkout-button-separator');
        if (wrapper && !wrapper._glsStyled) {
            // Make the parent form a flex column so we can reorder children
            var form = wrapper.closest('form.checkout');
            if (form) {
                form.style.display = 'flex';
                form.style.flexDirection = 'column';
                // Express checkout goes to the end (after payment)
                wrapper.style.order = '999';
                wrapper.style.marginTop = '10px';
            }
            // Hide the separator
            if (separator) separator.style.display = 'none';
            wrapper._glsStyled = true;
        }
    }
    styleExpressCheckout();
    setTimeout(styleExpressCheckout, 2000);
    setTimeout(styleExpressCheckout, 5000);

    // Re-translate after WooCommerce AJAX updates (checkout, cart)
    if (typeof jQuery !== 'undefined') {
        jQuery(document.body).on('updated_checkout updated_cart_totals updated_shipping_method', function() {
            setTimeout(translateAll, 100);
            setTimeout(translateAll, 500);
        });
    }

    // Initial re-translate passes to catch AJAX-loaded content
    var initPasses = 0;
    var initInterval = setInterval(function() {
        translateAll();
        initPasses++;
        if (initPasses >= 5) clearInterval(initInterval);
    }, 1000);

    // === Cart/Checkout UX: hide unselected shipping on checkout ===
    function hideUnselectedShipping() {
        if (!document.body.classList.contains('woocommerce-checkout')) return;
        var items = document.querySelectorAll('#shipping_method li, .woocommerce-shipping-methods li');
        items.forEach(function(li) {
            var radio = li.querySelector('input[type="radio"]');
            if (radio && !radio.checked) {
                li.style.display = 'none';
            } else if (radio) {
                // Hide the radio itself but keep label and pick-up point button visible
                radio.style.position = 'absolute';
                radio.style.opacity = '0';
                radio.style.pointerEvents = 'none';
            }
        });
    }

    // Run on checkout page
    if (document.body.classList.contains('woocommerce-checkout') || document.querySelector('.woocommerce-checkout')) {
        setTimeout(hideUnselectedShipping, 500);
        setTimeout(hideUnselectedShipping, 1500);
        setTimeout(hideUnselectedShipping, 3000);
    }
    if (typeof jQuery !== 'undefined') {
        jQuery(document.body).on('updated_checkout', function() {
            setTimeout(hideUnselectedShipping, 200);
            setTimeout(hideUnselectedShipping, 800);
        });
    }

    // Wait for DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
