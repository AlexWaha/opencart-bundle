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
$_['tab_general'] = 'Общие';
$_['tab_pages'] = 'Страницы';
$_['tab_modules'] = 'Модули';
$_['tab_checkout'] = 'Оформление заказа';
$_['tab_events'] = 'События';
$_['tab_advanced'] = 'Расширенные';
$_['tab_support'] = 'Поддержка';
$_['tab_ga4_reference'] = 'Справочник GA4';

// Text
$_['text_extension'] = 'Расширения';
$_['text_module'] = 'Модули';
$_['text_success'] = 'Настройки успешно сохранены!';
$_['text_edit'] = 'Настройки модуля';
$_['text_info'] = 'Информация';
$_['text_yes'] = 'Да';
$_['text_no'] = 'Нет';

// General Tab
$_['entry_status'] = 'Статус';
$_['entry_tracking_code'] = 'Код GTM/gtag.js (Head)';
$_['entry_tracking_code_help'] = 'Вставьте код Google Tag Manager или gtag.js. Этот код будет добавлен в секцию &lt;head&gt; вашего сайта.';
$_['entry_tracking_code_body'] = 'Код GTM (Body)';
$_['entry_tracking_code_body_help'] = 'Вставьте noscript код Google Tag Manager. Этот код будет вставлен сразу после открывающего тега &lt;body&gt;.';
$_['entry_debug_mode'] = 'Режим отладки';
$_['entry_debug_mode_help'] = 'При включении все события dataLayer будут выводиться в консоль браузера для отладки.';

// Pages Tab
$_['text_pages_description'] = 'Включите отслеживание для различных типов страниц. Событие <code class="badge-code">view_item_list</code> будет отправляться при посещении этих страниц.';
$_['entry_track_category'] = 'Страницы категорий';
$_['entry_track_category_help'] = 'Отслеживать событие <code class="badge-code">view_item_list</code> на страницах категорий';
$_['entry_track_search'] = 'Результаты поиска';
$_['entry_track_search_help'] = 'Отслеживать событие <code class="badge-code">view_item_list</code> на страницах результатов поиска';
$_['entry_track_manufacturer'] = 'Страницы производителей';
$_['entry_track_manufacturer_help'] = 'Отслеживать событие <code class="badge-code">view_item_list</code> на страницах производителей/брендов';
$_['entry_track_special'] = 'Страницы акций';
$_['entry_track_special_help'] = 'Отслеживать событие <code class="badge-code">view_item_list</code> на страницах специальных предложений';
$_['entry_track_product'] = 'Страницы товаров';
$_['entry_track_product_help'] = 'Отслеживать событие <code class="badge-code">view_item</code> на страницах товаров';
$_['entry_track_compare'] = 'Страница сравнения';
$_['entry_track_compare_help'] = 'Отслеживать событие <code class="badge-code">view_item_list</code> на странице сравнения товаров';

// Modules Tab
$_['text_modules_description'] = 'Включите отслеживание для модулей списков товаров. Событие <code class="badge-code">view_item_list</code> будет отправляться при отображении этих модулей.';
$_['entry_track_module_latest'] = 'Модуль новинок';
$_['entry_track_module_featured'] = 'Модуль рекомендуемых';
$_['entry_track_module_bestseller'] = 'Модуль хитов продаж';
$_['entry_track_module_special'] = 'Модуль акций';
$_['entry_track_module_aw_viewed'] = 'Модуль просмотренных (AW)';

// Checkout Tab
$_['text_checkout_description'] = 'Настройте отслеживание событий корзины и оформления заказа.';
$_['entry_track_add_to_cart'] = 'Добавление в корзину';
$_['entry_track_add_to_cart_help'] = 'Отслеживать событие <code class="badge-code">add_to_cart</code> при добавлении товаров в корзину';
$_['entry_track_remove_from_cart'] = 'Удаление из корзины';
$_['entry_track_remove_from_cart_help'] = 'Отслеживать событие <code class="badge-code">remove_from_cart</code> при удалении товаров из корзины';
$_['entry_track_view_cart'] = 'Просмотр корзины';
$_['entry_track_view_cart_help'] = 'Отслеживать событие <code class="badge-code">view_cart</code> при просмотре страницы корзины';
$_['entry_track_begin_checkout'] = 'Начало оформления';
$_['entry_track_begin_checkout_help'] = 'Отслеживать событие <code class="badge-code">begin_checkout</code> при начале оформления заказа';
$_['entry_track_shipping_info'] = 'Информация о доставке';
$_['entry_track_shipping_info_help'] = 'Отслеживать событие <code class="badge-code">add_shipping_info</code> при выборе способа доставки';
$_['entry_track_payment_info'] = 'Информация об оплате';
$_['entry_track_payment_info_help'] = 'Отслеживать событие <code class="badge-code">add_payment_info</code> при выборе способа оплаты';
$_['entry_track_purchase'] = 'Покупка';
$_['entry_track_purchase_help'] = 'Отслеживать событие <code class="badge-code">purchase</code> на странице успешного заказа';
$_['entry_include_tax'] = 'Включать налог в цены';
$_['entry_include_tax_help'] = 'Включать сумму налога в цены товаров, отправляемые в GA4';
$_['entry_include_shipping'] = 'Отслеживать стоимость доставки';
$_['entry_include_shipping_help'] = 'Включать стоимость доставки в событие purchase';
$_['entry_include_coupons'] = 'Отслеживать купоны/скидки';
$_['entry_include_coupons_help'] = 'Включать коды купонов и информацию о скидках в события';

// Events Tab
$_['text_events_description'] = 'Включите отслеживание дополнительных событий.';
$_['entry_track_login'] = 'Вход пользователя';
$_['entry_track_login_help'] = 'Отслеживать событие <code class="badge-code">login</code> при успешном входе пользователя';
$_['entry_track_signup'] = 'Регистрация';
$_['entry_track_signup_help'] = 'Отслеживать событие <code class="badge-code">sign_up</code> при регистрации нового пользователя';
$_['entry_track_wishlist'] = 'Добавление в избранное';
$_['entry_track_wishlist_help'] = 'Отслеживать событие <code class="badge-code">add_to_wishlist</code> при добавлении товаров в список желаний';
$_['entry_track_select_item'] = 'Выбор товара';
$_['entry_track_select_item_help'] = 'Отслеживать событие <code class="badge-code">select_item</code> при клике на товар в списке';
$_['entry_track_coupon'] = 'Применение купона/сертификата';
$_['entry_track_coupon_help'] = 'Отслеживать события <code class="badge-code">add_coupon</code> и <code class="badge-code">add_voucher</code> при применении купонов или подарочных сертификатов';

// Advanced Tab
$_['text_advanced_description'] = 'Расширенные настройки конфигурации.';
$_['entry_currency_format'] = 'Валюта';
$_['entry_currency_format_help'] = 'Выберите, какую валюту использовать для отслеживания';
$_['text_currency_session'] = 'Валюта сессии (выбранная посетителем)';
$_['text_currency_config'] = 'Валюта магазина по умолчанию';
$_['entry_price_with_tax'] = 'Цены с налогом';
$_['entry_price_with_tax_help'] = 'Отправлять цены с включённым налогом в GA4';
$_['entry_send_product_options'] = 'Отправлять опции товара';
$_['entry_send_product_options_help'] = 'Включать выбранные опции товара как item_variant в данные отслеживания';
$_['entry_custom_dimensions'] = 'Пользовательские параметры (JSON)';
$_['entry_custom_dimensions_help'] = 'Добавить пользовательские параметры ко всем событиям в формате JSON. Пример: {"dimension1": "value1"}';

// GA4 Reference Tab
$_['text_ga4_reference_description'] = 'Этот модуль реализует отслеживание <a href="https://developers.google.com/analytics/devguides/collection/ga4/ecommerce" target="_blank">Google Analytics 4 E-commerce</a>. Ниже приведён полный справочник всех поддерживаемых событий и условий их срабатывания.';
$_['text_ga4_doc_link'] = 'Официальная документация';
$_['text_ga4_doc_url'] = 'https://developers.google.com/analytics/devguides/collection/ga4/ecommerce';

$_['text_ga4_event'] = 'Событие';
$_['text_ga4_trigger'] = 'Когда срабатывает';
$_['text_ga4_description'] = 'Описание';
$_['text_ga4_type'] = 'Тип';
$_['text_ga4_type_server'] = 'Сервер';
$_['text_ga4_type_js'] = 'JavaScript';

$_['text_ga4_view_item_list'] = 'Срабатывает когда пользователь просматривает список товаров. Включает страницы категорий, результаты поиска, страницы производителей, страницы акций и модули товаров (Рекомендуемые, Новинки, Хиты продаж, Акции, Просмотренные).';
$_['text_ga4_view_item'] = 'Срабатывает когда пользователь просматривает страницу товара. Отправляет информацию о товаре: название, цену, бренд и категорию.';
$_['text_ga4_select_item'] = 'Срабатывает когда пользователь кликает на товар в списке для просмотра деталей. Фиксирует, из какого списка пришёл пользователь, для анализа воронки.';
$_['text_ga4_add_to_cart'] = 'Срабатывает когда пользователь добавляет товар в корзину. Включает детали товара и добавленное количество.';
$_['text_ga4_remove_from_cart'] = 'Срабатывает когда пользователь удаляет товар из корзины. Полезно для понимания причин брошенных корзин.';
$_['text_ga4_view_cart'] = 'Срабатывает когда пользователь просматривает страницу корзины. Отправляет всё содержимое корзины с ценами и количеством.';
$_['text_ga4_begin_checkout'] = 'Срабатывает когда пользователь начинает процесс оформления заказа. Отмечает начало воронки покупки.';
$_['text_ga4_add_shipping_info'] = 'Срабатывает когда пользователь выбирает способ доставки. Включает выбранный тип доставки.';
$_['text_ga4_add_payment_info'] = 'Срабатывает когда пользователь выбирает способ оплаты. Включает выбранный тип оплаты.';
$_['text_ga4_purchase'] = 'Срабатывает на странице успешного заказа. Это самое важное событие конверсии, включающее ID транзакции, общую сумму, налог, доставку и все купленные товары.';
$_['text_ga4_login'] = 'Срабатывает когда пользователь успешно входит в аккаунт. Помогает отслеживать поведение постоянных клиентов.';
$_['text_ga4_sign_up'] = 'Срабатывает когда новый пользователь завершает регистрацию. Полезно для измерения привлечения клиентов.';
$_['text_ga4_add_to_wishlist'] = 'Срабатывает когда пользователь добавляет товар в список желаний. Указывает на высокий интерес к покупке.';
$_['text_ga4_add_coupon'] = 'Срабатывает когда пользователь успешно применяет код купона. Отслеживает использование скидочных кодов.';
$_['text_ga4_add_voucher'] = 'Срабатывает когда пользователь успешно применяет подарочный сертификат. Отслеживает погашение сертификатов.';

$_['text_ga4_legend'] = 'Легенда';
$_['text_ga4_legend_server'] = 'Событие срабатывает на сервере (PHP) при загрузке страницы';
$_['text_ga4_legend_js'] = 'Событие срабатывает через JavaScript при действии пользователя';

// Buttons
$_['button_save'] = 'Сохранить';
$_['button_cancel'] = 'Отмена';

// Error
$_['error_permission'] = 'Внимание: У вас нет прав для управления этим модулем!';
$_['error_warning'] = 'Внимание: Пожалуйста, проверьте форму на наличие ошибок!';
