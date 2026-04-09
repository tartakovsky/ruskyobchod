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
        'Категории': 'Kategórie',
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
        // Site title in breadcrumbs
        'Русский магазин \u0022Гастроном\u0022': 'Ruský obchod \u0022Gastronom\u0022',
        'Русский магазин "Гастроном"': 'Ruský obchod "Gastronom"',
        'Русский магазин «Гастроном»': 'Ruský obchod «Gastronom»',
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
        'Цена': 'Cena',
        'Количество': 'Množstvo',
        'Удалить': 'Odstrániť',
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
        'Nie': 'Нет',
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
        // Shipping method names (from WC settings, originally in Slovak)
        'GLS доставка на адрес': 'GLS doručenie na adresu',
        'GLS Баликомат': 'GLS Balíkomat',
        'GLS ПарцелШоп': 'GLS ParcelShop',
        // Payment method names
        'Банковский перевод': 'Bankový prevod',
        'Сохранить платёжные данные для будущих покупок.': 'Uložiť platobné údaje pre budúce nákupy.',
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
    let currentLang = (urlLang === 'ru' || urlLang === 'sk') ? urlLang : (localStorage.getItem('gls-lang') || 'sk');
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
                // Reload on shop/category pages so server re-sorts by new language
                if (document.body.classList.contains('woocommerce') &&
                    (document.body.classList.contains('archive') || document.body.classList.contains('post-type-archive-product'))) {
                    location.reload();
                    return;
                }
                updateButtons();
                translateAll();
            });
        });

        // Observe DOM changes for dynamically loaded content (debounced)
        let observerTimer = null;
        let isTranslating = false;
        const observer = new MutationObserver(function(mutations) {
            if (isTranslating) return;
            if (observerTimer) clearTimeout(observerTimer);
            observerTimer = setTimeout(function() {
                let hasNewNodes = false;
                for (const m of mutations) {
                    if (m.addedNodes.length > 0) { hasNewNodes = true; break; }
                }
                if (hasNewNodes) {
                    isTranslating = true;
                    translateAll();
                    isTranslating = false;
                }
            }, 200);
        });
        observer.observe(document.body, { childList: true, subtree: true });
    }

    function translateAll() {
        processBilingualText();
        applyUITranslations(currentLang);
        syncDocumentTitle();
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

    function updateButtons() {
        document.querySelectorAll('.gls-btn').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.lang === currentLang);
        });
    }

    function syncDocumentTitle() {
        if (!document.title) return;
        if (currentLang === 'ru') {
            document.title = document.title
                .replace(/^Košík/, 'Корзина')
                .replace(/^Objednávka/, 'Оформление заказа')
                .replace(/^Domov/, 'Главная')
                .replace(/^Môj účet/, 'Мой аккаунт');
        } else {
            document.title = document.title
                .replace(/^Корзина/, 'Košík')
                .replace(/^Оформление заказа/, 'Objednávka')
                .replace(/^Главная/, 'Domov')
                .replace(/^Мой аккаунт/, 'Môj účet');
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
            // Sidebar/footer
            '.widget-title', '.widget_categories a', '.widget_archive a',
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
            ? {'Тел. номер:': 'Tel. číslo:', 'Тел. номер': 'Tel. číslo'}
            : {'Tel. číslo:': 'Тел. номер:', 'Tel. číslo': 'Тел. номер'};
        document.querySelectorAll('.elementor-widget-text-editor span, .entry-content span, .entry-content p').forEach(el => {
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
