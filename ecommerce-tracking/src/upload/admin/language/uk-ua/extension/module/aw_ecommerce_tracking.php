<?php

/*
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */

// Heading
$_['heading_title'] = '<span style="color:#0937cc;">alexwaha.com</span> - <i class="fa fa-puzzle-piece"></i> E-commerce Tracking (GA4)';
$_['heading_main_title'] = 'alexwaha.com - E-commerce Tracking (GA4)';

// Tab
$_['tab_general'] = 'Загальні';
$_['tab_pages'] = 'Сторінки';
$_['tab_modules'] = 'Модулі';
$_['tab_checkout'] = 'Оформлення';
$_['tab_events'] = 'Події';
$_['tab_advanced'] = 'Розширені';
$_['tab_support'] = 'Підтримка';
$_['tab_ga4_reference'] = 'Довідник GA4';

// Text
$_['text_extension'] = 'Розширення';
$_['text_module'] = 'Модулі';
$_['text_success'] = 'Налаштування успішно збережено!';
$_['text_edit'] = 'Налаштування модуля';
$_['text_info'] = 'Інформація';
$_['text_yes'] = 'Так';
$_['text_no'] = 'Ні';

// General Tab
$_['entry_status'] = 'Статус';
$_['entry_tracking_code'] = 'Код GTM/gtag.js (Head)';
$_['entry_tracking_code_help'] = 'Вставте код Google Tag Manager або gtag.js. Цей код буде додано в секцію &lt;head&gt; вашого сайту.';
$_['entry_tracking_code_body'] = 'Код GTM (Body)';
$_['entry_tracking_code_body_help'] = 'Вставте noscript код Google Tag Manager. Цей код буде вставлено відразу після відкриваючого тега &lt;body&gt;.';
$_['entry_debug_mode'] = 'Режим налагодження';
$_['entry_debug_mode_help'] = 'При увімкненні всі події dataLayer будуть виводитися в консоль браузера для налагодження.';

// Pages Tab
$_['text_pages_description'] = 'Увімкніть відстеження для різних типів сторінок. Подія <code class="badge-code">view_item_list</code> буде надсилатися при відвідуванні цих сторінок.';
$_['entry_track_category'] = 'Сторінки категорій';
$_['entry_track_category_help'] = 'Відстежувати подію <code class="badge-code">view_item_list</code> на сторінках категорій';
$_['entry_track_search'] = 'Результати пошуку';
$_['entry_track_search_help'] = 'Відстежувати подію <code class="badge-code">view_item_list</code> на сторінках результатів пошуку';
$_['entry_track_manufacturer'] = 'Сторінки виробників';
$_['entry_track_manufacturer_help'] = 'Відстежувати подію <code class="badge-code">view_item_list</code> на сторінках виробників/брендів';
$_['entry_track_special'] = 'Сторінки акцій';
$_['entry_track_special_help'] = 'Відстежувати подію <code class="badge-code">view_item_list</code> на сторінках спеціальних пропозицій';
$_['entry_track_product'] = 'Сторінки товарів';
$_['entry_track_product_help'] = 'Відстежувати подію <code class="badge-code">view_item</code> на сторінках товарів';
$_['entry_track_compare'] = 'Сторінка порівняння';
$_['entry_track_compare_help'] = 'Відстежувати подію <code class="badge-code">view_item_list</code> на сторінці порівняння товарів';

// Modules Tab
$_['text_modules_description'] = 'Увімкніть відстеження для модулів списків товарів. Подія <code class="badge-code">view_item_list</code> буде надсилатися при відображенні цих модулів.';
$_['entry_track_module_latest'] = 'Модуль новинок';
$_['entry_track_module_featured'] = 'Модуль рекомендованих';
$_['entry_track_module_bestseller'] = 'Модуль хітів продажів';
$_['entry_track_module_special'] = 'Модуль акцій';
$_['entry_track_module_aw_viewed'] = 'Модуль переглянутих (AW)';

// Checkout Tab
$_['text_checkout_description'] = 'Налаштуйте відстеження подій кошика та оформлення замовлення.';
$_['entry_track_add_to_cart'] = 'Додавання в кошик';
$_['entry_track_add_to_cart_help'] = 'Відстежувати подію <code class="badge-code">add_to_cart</code> при додаванні товарів в кошик';
$_['entry_track_remove_from_cart'] = 'Видалення з кошика';
$_['entry_track_remove_from_cart_help'] = 'Відстежувати подію <code class="badge-code">remove_from_cart</code> при видаленні товарів з кошика';
$_['entry_track_view_cart'] = 'Перегляд кошика';
$_['entry_track_view_cart_help'] = 'Відстежувати подію <code class="badge-code">view_cart</code> при перегляді сторінки кошика';
$_['entry_track_begin_checkout'] = 'Початок оформлення';
$_['entry_track_begin_checkout_help'] = 'Відстежувати подію <code class="badge-code">begin_checkout</code> при початку оформлення замовлення';
$_['entry_track_shipping_info'] = 'Інформація про доставку';
$_['entry_track_shipping_info_help'] = 'Відстежувати подію <code class="badge-code">add_shipping_info</code> при виборі способу доставки';
$_['entry_track_payment_info'] = 'Інформація про оплату';
$_['entry_track_payment_info_help'] = 'Відстежувати подію <code class="badge-code">add_payment_info</code> при виборі способу оплаты';
$_['entry_track_purchase'] = 'Покупка';
$_['entry_track_purchase_help'] = 'Відстежувати подію <code class="badge-code">purchase</code> на сторінці успішного замовлення';
$_['entry_include_tax'] = 'Включати податок в ціни';
$_['entry_include_tax_help'] = 'Включати суму податку в ціни товарів, що надсилаються в GA4';
$_['entry_include_shipping'] = 'Відстежувати вартість доставки';
$_['entry_include_shipping_help'] = 'Включати вартість доставки в подію purchase';
$_['entry_include_coupons'] = 'Відстежувати купони/знижки';
$_['entry_include_coupons_help'] = 'Включати коди купонів та інформацію про знижки в події';

// Events Tab
$_['text_events_description'] = 'Увімкніть відстеження додаткових подій.';
$_['entry_track_login'] = 'Вхід користувача';
$_['entry_track_login_help'] = 'Відстежувати подію <code class="badge-code">login</code> при успішному вході користувача';
$_['entry_track_signup'] = 'Реєстрація';
$_['entry_track_signup_help'] = 'Відстежувати подію <code class="badge-code">sign_up</code> при реєстрації нового користувача';
$_['entry_track_wishlist'] = 'Додавання в обране';
$_['entry_track_wishlist_help'] = 'Відстежувати подію <code class="badge-code">add_to_wishlist</code> при додаванні товарів в список бажань';
$_['entry_track_select_item'] = 'Вибір товару';
$_['entry_track_select_item_help'] = 'Відстежувати подію <code class="badge-code">select_item</code> при кліку на товар в списку';
$_['entry_track_coupon'] = 'Застосування купона/сертифіката';
$_['entry_track_coupon_help'] = 'Відстежувати події <code class="badge-code">add_coupon</code> та <code class="badge-code">add_voucher</code> при застосуванні купонів або подарункових сертифікатів';

// Advanced Tab
$_['text_advanced_description'] = 'Розширені налаштування конфігурації.';
$_['entry_currency_format'] = 'Валюта';
$_['entry_currency_format_help'] = 'Оберіть, яку валюту використовувати для відстеження';
$_['text_currency_session'] = 'Валюта сесії (обрана відвідувачем)';
$_['text_currency_config'] = 'Валюта магазину за замовчуванням';
$_['entry_price_with_tax'] = 'Ціни з податком';
$_['entry_price_with_tax_help'] = 'Надсилати ціни з включеним податком в GA4';
$_['entry_send_product_options'] = 'Надсилати опції товару';
$_['entry_send_product_options_help'] = 'Включати обрані опції товару як item_variant в дані відстеження';
$_['entry_custom_dimensions'] = 'Користувацькі параметри (JSON)';
$_['entry_custom_dimensions_help'] = 'Додати користувацькі параметри до всіх подій в форматі JSON. Приклад: {"dimension1": "value1"}';

// GA4 Reference Tab
$_['text_ga4_reference_description'] = 'Цей модуль реалізує відстеження <a href="https://developers.google.com/analytics/devguides/collection/ga4/ecommerce" target="_blank">Google Analytics 4 E-commerce</a>. Нижче наведено повний довідник всіх підтримуваних подій та умов їх спрацювання.';
$_['text_ga4_doc_link'] = 'Офіційна документація';
$_['text_ga4_doc_url'] = 'https://developers.google.com/analytics/devguides/collection/ga4/ecommerce';

$_['text_ga4_event'] = 'Подія';
$_['text_ga4_trigger'] = 'Коли спрацьовує';
$_['text_ga4_description'] = 'Опис';
$_['text_ga4_type'] = 'Тип';
$_['text_ga4_type_server'] = 'Сервер';
$_['text_ga4_type_js'] = 'JavaScript';

$_['text_ga4_view_item_list'] = 'Спрацьовує коли користувач переглядає список товарів. Включає сторінки категорій, результати пошуку, сторінки виробників, сторінки акцій та модулі товарів (Рекомендовані, Новинки, Хіти продажів, Акції, Переглянуті).';
$_['text_ga4_view_item'] = 'Спрацьовує коли користувач переглядає сторінку товару. Надсилає інформацію про товар: назву, ціну, бренд та категорію.';
$_['text_ga4_select_item'] = 'Спрацьовує коли користувач клікає на товар в списку для перегляду деталей. Фіксує, з якого списку прийшов користувач, для аналізу воронки.';
$_['text_ga4_add_to_cart'] = 'Спрацьовує коли користувач додає товар в кошик. Включає деталі товару та додану кількість.';
$_['text_ga4_remove_from_cart'] = 'Спрацьовує коли користувач видаляє товар з кошика. Корисно для розуміння причин покинутих кошиків.';
$_['text_ga4_view_cart'] = 'Спрацьовує коли користувач переглядає сторінку кошика. Надсилає весь вміст кошика з цінами та кількістю.';
$_['text_ga4_begin_checkout'] = 'Спрацьовує коли користувач починає процес оформлення замовлення. Відмічає початок воронки покупки.';
$_['text_ga4_add_shipping_info'] = 'Спрацьовує коли користувач обирає спосіб доставки. Включає обраний тип доставки.';
$_['text_ga4_add_payment_info'] = 'Спрацьовує коли користувач обирає спосіб оплати. Включає обраний тип оплати.';
$_['text_ga4_purchase'] = 'Спрацьовує на сторінці успішного замовлення. Це найважливіша подія конверсії, що включає ID транзакції, загальну суму, податок, доставку та всі куплені товари.';
$_['text_ga4_login'] = 'Спрацьовує коли користувач успішно входить в акаунт. Допомагає відстежувати поведінку постійних клієнтів.';
$_['text_ga4_sign_up'] = 'Спрацьовує коли новий користувач завершує реєстрацію. Корисно для вимірювання залучення клієнтів.';
$_['text_ga4_add_to_wishlist'] = 'Спрацьовує коли користувач додає товар в список бажань. Вказує на високий інтерес до покупки.';
$_['text_ga4_add_coupon'] = 'Спрацьовує коли користувач успішно застосовує код купона. Відстежує використання кодів знижок.';
$_['text_ga4_add_voucher'] = 'Спрацьовує коли користувач успішно застосовує подарунковий сертифікат. Відстежує погашення сертифікатів.';

$_['text_ga4_legend'] = 'Легенда';
$_['text_ga4_legend_server'] = 'Подія спрацьовує на сервері (PHP) при завантаженні сторінки';
$_['text_ga4_legend_js'] = 'Подія спрацьовує через JavaScript при дії користувача';

// Buttons
$_['button_save'] = 'Зберегти';
$_['button_cancel'] = 'Скасувати';

// Error
$_['error_permission'] = 'Увага: У вас немає прав для керування цим модулем!';
$_['error_warning'] = 'Увага: Будь ласка, перевірте форму на наявність помилок!';
