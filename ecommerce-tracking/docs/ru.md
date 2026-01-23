# Инструкция | Alexwaha.com - E-commerce Tracking (GA4)

**для Opencart v2.3 - 3.x**

---

## Содержание

1. [Информация о модуле](#информация-о-модуле)
2. [Инструкция по установке](#инструкция-по-установке)
3. [Настройки модуля](#настройки-модуля)
4. [Интеграция с GTM](#интеграция-с-gtm)
5. [Справочник событий GA4](#справочник-событий-ga4)
6. [Для разработчиков](#для-разработчиков)
7. [Возможные ошибки](#возможные-ошибки)
8. [Лицензия и контакты](#лицензия-и-контакты)

---

## Информация о модуле

**E-commerce Tracking (GA4)** - модуль для OpenCart, реализующий полное отслеживание электронной коммерции по стандарту Google Analytics 4. Автоматически отправляет все e-commerce события в dataLayer для Google Tag Manager или gtag.js.

### Основные возможности:

- **Полная поддержка GA4 E-commerce** - Все рекомендованные события Google Analytics 4
- **Гибкая настройка** - Включение/отключение каждого события отдельно
- **Отслеживание страниц** - Категории, поиск, производители, акции, товары
- **Отслеживание модулей** - Рекомендуемые, новинки, хиты продаж, акции
- **Воронка покупки** - От просмотра товара до завершения покупки
- **Режим отладки** - Вывод событий в консоль браузера
- **Совместимость** - Стандартный checkout, Simple Checkout, AW Easy Checkout

### Технические характеристики:

- **Автор:** Alexander Vakhovski (AlexWaha)
- **Сайт:** [https://alexwaha.com](https://alexwaha.com)
- **Лицензия:** GPLv3
- **Совместимость:** OpenCart 2.3.x - 3.x
- **Код модуля:** `aw_ecommerce_tracking`

---

## Инструкция по установке

### Предварительные требования

1. Установите модуль **[aw_core_oc2.3-3.x.ocmod.zip](https://github.com/AlexWaha/opencart-bundle/blob/master/Core/dist/aw_core_oc2.3_3.x.ocmod.zip)**

2. Для Opencart 2.3.x: модуль отключения FTP-загрузки или корректные настройки FTP

### Установка

1. Установите архив **aw_ecommerce_tracking_oc2.3-3x.ocmod.zip** через **Расширения → Установщик**

2. Обновите кеш модификаций: **Расширения → Модификаторы → Обновить**

3. Включите модуль: **Расширения → Модули → alexwaha.com - E-commerce Tracking (GA4)**

---

## Настройки модуля

### Вкладка "Общие"

| Настройка | Описание |
|-----------|----------|
| **Статус** | Включение/отключение модуля |
| **Код GTM/gtag.js (Head)** | Код Google Tag Manager или gtag.js для секции `<head>` |
| **Код GTM (Body)** | Noscript код GTM для `<body>` |
| **Режим отладки** | Вывод событий в консоль браузера |

### Вкладка "Страницы"

Отслеживание `view_item_list` и `view_item`:

| Настройка | Событие | Описание |
|-----------|---------|----------|
| Страницы категорий | `view_item_list` | Списки товаров в категориях |
| Результаты поиска | `view_item_list` | Страница поиска |
| Страницы производителей | `view_item_list` | Страницы брендов |
| Страницы акций | `view_item_list` | Специальные предложения |
| Страницы товаров | `view_item` | Карточка товара |
| Страница сравнения | `view_item_list` | Сравнение товаров |

### Вкладка "Модули"

Отслеживание `view_item_list` для модулей:

- Модуль новинок (Latest)
- Модуль рекомендуемых (Featured)
- Модуль хитов продаж (Bestseller)
- Модуль акций (Special)
- Модуль просмотренных (AW Viewed)

### Вкладка "Оформление заказа"

| Настройка | Событие | Описание |
|-----------|---------|----------|
| Добавление в корзину | `add_to_cart` | Добавление товара |
| Удаление из корзины | `remove_from_cart` | Удаление товара |
| Просмотр корзины | `view_cart` | Страница корзины |
| Начало оформления | `begin_checkout` | Страница checkout |
| Информация о доставке | `add_shipping_info` | Выбор доставки |
| Информация об оплате | `add_payment_info` | Выбор оплаты |
| Покупка | `purchase` | Успешный заказ |

**Дополнительно:**
- Включать налог в цены
- Отслеживать стоимость доставки
- Отслеживать купоны/скидки

### Вкладка "События"

| Настройка | Событие | Описание |
|-----------|---------|----------|
| Вход пользователя | `login` | Авторизация |
| Регистрация | `sign_up` | Новый пользователь |
| Добавление в избранное | `add_to_wishlist` | Wishlist |
| Выбор товара | `select_item` | Клик на товар в списке |
| Применение купона | `add_coupon` / `add_voucher` | Скидочные коды |

### Вкладка "Расширенные"

| Настройка | Описание |
|-----------|----------|
| Валюта | Валюта сессии или магазина |
| Цены с налогом | Включать налог в цены |
| Отправлять опции товара | Добавлять `item_variant` |
| Пользовательские параметры | JSON с дополнительными данными |

---

## Интеграция с GTM

### Настройка Google Tag Manager

1. **Переменная dataLayer:**
   - Variables → New → Data Layer Variable
   - Имя: `ecommerce`, Version 2

2. **Триггер:**
   - Triggers → New → Custom Event
   - Event name: `view_item_list|view_item|select_item|add_to_cart|remove_from_cart|view_cart|begin_checkout|add_shipping_info|add_payment_info|purchase`
   - Use regex matching: ✓

3. **Тег GA4 Event:**
   - Tags → New → Google Analytics: GA4 Event
   - Event Name: `{{Event}}`
   - Event Parameters: `ecommerce` → `{{ecommerce}}`

### Проверка

1. Включите режим отладки в модуле
2. Откройте Preview в GTM
3. Проверьте события в консоли и GTM Preview

---

## Справочник событий GA4

| Событие | Когда срабатывает | Тип |
|---------|-------------------|-----|
| `view_item_list` | Просмотр списка товаров | PHP |
| `view_item` | Просмотр карточки товара | PHP |
| `select_item` | Клик на товар в списке | JS |
| `add_to_cart` | Добавление в корзину | JS |
| `remove_from_cart` | Удаление из корзины | JS |
| `view_cart` | Просмотр корзины | PHP |
| `begin_checkout` | Начало оформления заказа | PHP |
| `add_shipping_info` | Выбор доставки | JS |
| `add_payment_info` | Выбор оплаты | JS |
| `purchase` | Успешный заказ | PHP |
| `login` | Вход в аккаунт | PHP |
| `sign_up` | Регистрация | PHP |
| `add_to_wishlist` | Добавление в избранное | JS |
| `add_coupon` | Применение купона | JS |

**Тип:** PHP = при загрузке страницы, JS = при действии пользователя

---

## Для разработчиков

### JavaScript API

```javascript
// Добавление в корзину
window.awEcommerceTracking.trackAddToCart({
    id: '42',
    name: 'iPhone 15',
    price: 49999.00,
    brand: 'Apple',
    category: 'Смартфоны'
}, 1);

// Удаление из корзины
window.awEcommerceTracking.trackRemoveFromCart(product, quantity);

// Выбор товара
window.awEcommerceTracking.trackSelectItem(product, listName, listId, index);

// Применение купона
window.awEcommerceTracking.trackCoupon('SALE10', 'coupon');

// Произвольное событие
window.awEcommerceTracking.push({ event: 'custom', data: 'value' });
```

### PHP API

```php
// В контроллере
$tracking = $this->load->controller('extension/module/aw_ecommerce_tracking');

if ($tracking->isEnabled()) {
    $eventData = $tracking->prepareViewItemList($products, 'My List', 'my_list');
    $html = $tracking->renderDataLayer($eventData);
}
```

### Интеграция с пользовательским модулем

```php
// Контроллер
$data['awTracking'] = $this->load->controller(
    'extension/module/aw_ecommerce_tracking/viewItemList',
    [$products, 'Custom List', 'custom_list', 'track_module_custom']
);
```

```twig
{# Шаблон #}
{{ awTracking|default('')|raw }}
```

---

## Возможные ошибки

### События не отправляются

1. Проверьте, что модуль включен
2. Проверьте код GTM в настройках
3. Обновите кеш модификаций

### Ошибка "awCore is not defined"

Установите модуль **aw_core_oc2.3-3.x.ocmod.zip**

### События дублируются

Проверьте, что код GTM добавлен только в настройках модуля (не в шаблоне вручную)

### add_to_cart не срабатывает

1. Проверьте консоль на ошибки
2. Убедитесь в наличии `cart.add()` на странице
3. Используйте JS API для отправки

---

## Лицензия и контакты

### Лицензия

[GNU General Public License version 3 (GPLv3)](https://github.com/alexwaha/opencart-bundle/blob/master/LICENSE)

**Автор:** Александр Ваховский (Oleksandr Vakhovskyi) / Alexwaha

### Контакты

- **Telegram:** [@alexwaha_dev](https://t.me/alexwaha_dev)
- **Email:** [support@alexwaha.com](mailto:support@alexwaha.com)
- **GitHub:** [https://github.com/AlexWaha/opencart-bundle](https://github.com/AlexWaha/opencart-bundle)
- **Contact Form:** [https://alexwaha.com/contact](https://alexwaha.com/contact)

> Техническая поддержка доступна на платной основе. Pull Request приветствуются.

---

**Alexwaha.com - E-commerce Tracking (GA4) для OpenCart**
