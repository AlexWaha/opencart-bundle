<?php

/**
 * Age Verification Module
 *
 * @author Alexander Vakhovski (AlexWaha)
 *
 * @link https://alexwaha.com
 *
 * @email support@alexwaha.com
 *
 * @license GPLv3
 */
// Heading
$_['heading_title'] = '<span style="color:#0937cc;">alexwaha.com</span> - <i class="fa fa-puzzle-piece"></i> XML Feed';
$_['heading_main_title'] = 'alexwaha.com - XML Feed';

// Tabs
$_['tab_general'] = 'XML Feed';
$_['tab_fields'] = 'Поля';
$_['tab_category'] = 'Категории';
$_['tab_manufacturer'] = 'Производитель';
$_['tab_attributes'] = 'Атрибуты';
$_['tab_options'] = 'Опции';
$_['tab_tags'] = 'Переменные';
$_['tab_support'] = 'Служба поддержки';

// Text
$_['text_feed'] = 'Каналы продвижения';
$_['text_success'] = 'Данные успещно сохранены!';
$_['text_success_delete'] = 'XML фид успешно удален';
$_['text_edit'] = 'Редактировать';
$_['text_extension'] = 'Каналы продвижения';
$_['text_tags_info'] = '<div class="alert alert-info"><h4><i class="fa fa-info-circle"></i> Структура XML шаблонов</h4><p>XML фиды генерируются из трёх частей шаблонов:</p><ul><li><strong>header.twig</strong> - заголовок XML файла с общей информацией о магазине и категориях</li><li><strong>items.twig</strong> - секция товаров, генерируется для каждого пакета товаров</li><li><strong>footer.twig</strong> - завершающая часть XML файла</li></ul><p>Шаблоны находятся в папке: <code>catalog/view/theme/active/template/extension/feed/aw_xml_feed/layout/{template}/</code></p><p><strong>Использование переменных:</strong> В шаблонах используйте переменные из таблицы ниже в формате Twig: <code>{{variable_name}}</code></p></div>';

// Entry
$_['entry_name'] = 'Название';
$_['entry_filename'] = 'Название файла *.xml';
$_['entry_folder'] = 'Имя папки xml';
$_['entry_template'] = 'Шаблон XML';
$_['entry_category'] = 'Категории доступные для экспорта';
$_['entry_category_related'] = 'Название категорий в экспорте';
$_['entry_manufacturer'] = 'Производители доступных для экспорта';
$_['entry_attribute'] = 'Атрибуты доступны для экспорта';
$_['entry_attribute_shipping'] = 'Атрибут - Доставка';
$_['entry_attribute_warranty'] = 'Атрибут - Гарантия';
$_['entry_attribute_country'] = 'Атрибут - Страна производитель';
$_['entry_option'] = 'Опции доступны для экспорта';
$_['entry_option_size'] = 'Опция размера (Google Merchat)';
$_['entry_option_color'] = 'Опция цвета (Google Merchat)';
$_['entry_currency'] = 'Валюта экспорта';
$_['entry_language'] = 'Язык экспорта';
$_['entry_image_origin'] = 'Использовать оригиналы изображений';
$_['entry_image_width'] = 'Ширина изображений';
$_['entry_image_height'] = 'Высота изображений';
$_['entry_image_quantity'] = 'Кол-во изображений';
$_['entry_image_count'] = 'Кол-во изображений';
$_['entry_batch_size'] = 'Размер пакета генерации';
$_['entry_access_key'] = 'Ключ доступа';
$_['entry_status'] = 'Состояние';
$_['entry_shipping_text'] = 'Статический текст - Доставка';
$_['entry_warranty_text'] = 'Статический текст - Гарантия';
$_['entry_shop_name'] = 'Название магазина';
$_['entry_company_name'] = 'Название компании';
$_['entry_shop_description'] = 'Описание магазина';
$_['entry_shop_country'] = 'Страна магазина';
$_['entry_delivery_service'] = 'Служба доставки';
$_['entry_stock_status_available'] = 'Статусы товаров при которых их можно приобрести';

// Columns
$_['column_name'] = 'Название';
$_['column_url'] = 'Ссылка на прайс';
$_['column_filename'] = 'Имя файла';
$_['column_template'] = 'Шаблон';
$_['column_status'] = 'Состояние';
$_['column_action'] = 'Действие';
$_['column_tag'] = 'Переменная';
$_['column_descrition'] = 'Описание';

// Tags
$_['tag_date'] = 'Дата экспорта в формате [YYYY–MM–DD hh:mm:ss]';
$_['tag_url'] = 'URL адрес по которому доступен экспорт';
$_['tag_currency'] = 'Валюта прайса';
$_['tag_categories'] = 'Массив категорий экспорта';
$_['tag_category'] = 'Массив категории экспорта';
$_['tag_category_id'] = 'Идентификатор категории';
$_['tag_category_parent'] = 'Инентификатор родительской категории';
$_['tag_category_name'] = 'Название категории';
$_['tag_pages'] = 'Массив разделов';
$_['tag_page'] = 'Массив раздела';
$_['tag_products'] = 'Массив товаров экспорта';
$_['tag_product'] = 'Массив товара экспорта';
$_['tag_product_id'] = 'Идентификатор товара';
$_['tag_product_url'] = 'URL адрес товара';
$_['tag_product_price'] = 'Цена товара';
$_['tag_product_special'] = 'Новая цена товара';
$_['tag_product_category_id'] = 'Идентификатор категории товара';
$_['tag_product_h1'] = 'Заголовок H1 товара';
$_['tag_product_name'] = 'Название товара';
$_['tag_product_description'] = 'Описание товара';
$_['tag_product_model'] = 'Модель товара';
$_['tag_product_quantity'] = 'Кол-во товара';
$_['tag_product_available'] = 'Наличие товара (Кол-во > 1 = true, < 1 = false)';
$_['tag_product_vendor'] = 'Производитель товара';
$_['tag_product_vendor_code'] = 'Код продавца (SKU)';
$_['tag_product_images'] = 'Массив ссылок на изображения товара';
$_['tag_product_image'] = 'Главное изображения товара';
$_['tag_product_shipping'] = 'Срок доставки (Атрибут или текст)';
$_['tag_product_warranty'] = 'Срок гарантии (Атрибут или текст)';
$_['tag_product_options'] = 'Массив опций товаров';
$_['tag_option'] = 'Массив опции товара';
$_['tag_option_id'] = 'ID значения опции';
$_['tag_option_group'] = 'Название опции';
$_['tag_option_name'] = 'Значение опции (список)';
$_['tag_option_value'] = 'Значение опции (дата или текстовое поле)';
$_['tag_option_price'] = 'Цена опции';
$_['tag_option_weight'] = 'Вес опции';
$_['tag_option_quantity'] = 'Кол-во товара опции';
$_['tag_offer_color'] = 'Опция размера';
$_['tag_offer_size'] = 'Опция цвета';
$_['tag_product_attributes'] = 'Массив атрибутов товаров';
$_['tag_attribute'] = 'Массив атрибута товара';
$_['tag_attributes_group'] = 'Название группы атрибутов';
$_['tag_attributes_name'] = 'Наименование атрибута';
$_['tag_attributes_value'] = 'Значение атрибута';

// Template
$_['template_google'] = 'Google';
$_['template_facebook'] = 'Facebook';
$_['template_yml'] = 'YML';
$_['template_prom'] = 'Prom.ua';
$_['template_hotline'] = 'Hotline.ua';

// Help
$_['help_folder'] = 'Название папки для хранения XML файлов. Используйте только латинские буквы и цифры';
$_['help_batch_size'] = 'Количество товаров, обрабатываемых в одном пакете при генерации XML. Рекомендуется: 250-500';
$_['help_access_key'] = 'Ключ для защищенного доступа к XML фидам. Минимум 8 символов';
$_['help_shop_name'] = 'Официальное название вашего магазина для экспорта';
$_['help_company_name'] = 'Юридическое название компании или ИП';
$_['help_shop_description'] = 'Краткое описание деятельности магазина';
$_['help_shop_country'] = 'Выберите страну, где зарегистрирован ваш магазин';
$_['help_delivery_service'] = 'Название службы доставки или способ доставки';
$_['help_stock_status_available'] = 'Выберите статусы товаров при которых их можно приобрести (в наличии, под заказ, предзаказ и т.д.)';
$_['help_name'] = 'Внутреннее название фида для идентификации';
$_['help_filename'] = 'Имя XML файла без расширения. Используйте латинские буквы';
$_['help_currency'] = 'Курс валют используется из настроек магазина';
$_['help_image_origin'] = 'Использовать оригиналы изображений';
$_['help_image_quantity'] = 'Кол-во доп. изображений товара в экспорте (Рекомендовано, не больше 8)';
$_['help_image_count'] = 'Кол-во доп. изображений товара в экспорте (Рекомендовано, не больше 8)';
$_['help_limit'] = 'Количество товаров за один пакет генерации (больше товаров = дольше выполнение) (Рекомендовано до 1000)';
$_['help_attribute_shipping'] = 'Атрибут товара с указанием сроков доставки';
$_['help_attribute_warranty'] = 'Атрибут товара с указанием гарантийного срока';
$_['help_category_related'] = 'Название категории для сопоставления (Google Merchant и др.)';
$_['help_category_related_id'] = 'ID категории для сопоставления (Google Merchant и др.)';
$_['help_shipping_text'] = 'Укажите информацию о сроках доставки, например: Доставка 1-3 дня';
$_['help_warranty_text'] = 'Указывайте срок гарантии, например: 24 месяца, 12, 3 месяца от магазина итд';

// Error
$_['error_permission'] = 'У вас нет прав для редактирования этого модуля.';
$_['error_warning'] = 'Проверьте правильность заполнения всех полей.';
$_['error_folder'] = 'Укажите название папки для хранения XML (от 3 до 64 символов).';
$_['error_batch_size'] = 'Размер пакета генерации не может превышать 1000 товаров.';
$_['error_access_key'] = 'Ключ доступа не может быть пустым и должен содержать минимум 8 символов!';
$_['error_image_count'] = 'В одном товаре может быть не более 8 изображений.';
$_['error_name'] = 'Укажите название фида.';
$_['error_filename'] = 'Укажите название файла фида (от 3 до 128 символов).';
$_['error_shop_name'] = 'Укажите название магазина (от 3 до 255 символов).';
$_['error_company_name'] = 'Укажите название компании (от 3 до 255 символов).';
$_['error_shop_description'] = 'Укажите описание магазина (от 10 до 255 символов).';
$_['error_shipping_text'] = 'Укажите текст доставки (от 3 до 255 символов).';
$_['error_warranty_text'] = 'Укажите текст гарантии (от 3 до 255 символов).';
$_['error_delivery_service'] = 'Укажите службу доставки (от 3 до 255 символов).';
