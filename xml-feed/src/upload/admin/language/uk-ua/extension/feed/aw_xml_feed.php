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
$_['heading_title'] = '<span style="color:#0937cc;">alexwaha.com</span> - <i class="fa fa-puzzle-piece"></i> XML Фід';
$_['heading_main_title'] = 'alexwaha.com - XML Фід';

// Tabs
$_['tab_general'] = 'XML Фід';
$_['tab_fields'] = 'Поля';
$_['tab_category'] = 'Категорії';
$_['tab_manufacturer'] = 'Виробник';
$_['tab_attributes'] = 'Атрибути';
$_['tab_options'] = 'Опції';
$_['tab_tags'] = 'Змінні';
$_['tab_support'] = 'Підтримка';

// Text
$_['text_feed'] = 'Фіди товарів';
$_['text_success'] = 'Дані успішно збережено!';
$_['text_success_delete'] = 'XML фід успішно видалено';
$_['text_edit'] = 'Редагувати';
$_['text_extension'] = 'Фіди товарів';
$_['text_tags_info'] = '<div class="alert alert-info"><h4><i class="fa fa-info-circle"></i> Структура XML шаблону</h4><p>XML фіди генеруються з трьох частин шаблону:</p><ul><li><strong>header.twig</strong> - заголовок XML файлу з загальною інформацією про магазин та категорії</li><li><strong>items.twig</strong> - секція товарів, генерується для кожної партії товарів</li><li><strong>footer.twig</strong> - закриваюча частина XML файлу</li></ul><p>Шаблони розташовані в: <code>catalog/view/theme/active/template/extension/feed/aw_xml_feed/layout/{template}/</code></p><p><strong>Використання змінних:</strong> Використовуйте змінні з таблиці нижче в форматі Twig: <code>{{variable_name}}</code></p></div>';
$_['text_feed_generation_url'] = 'Посилання для генерації фіду';

// Entry
$_['entry_name'] = 'Назва';
$_['entry_filename'] = 'Ім\'я файлу *.xml';
$_['entry_folder'] = 'Назва папки XML';
$_['entry_template'] = 'XML Шаблон';
$_['entry_category'] = 'Категорії доступні для експорту';
$_['entry_category_related'] = 'Назви категорій в експорті';
$_['entry_manufacturer'] = 'Виробники доступні для експорту';
$_['entry_attribute'] = 'Атрибути доступні для експорту';
$_['entry_attribute_warranty'] = 'Атрибут - Гарантія';
$_['entry_attribute_country'] = 'Атрибут - Країна виробництва';
$_['entry_option'] = 'Опції доступні для експорту';
$_['entry_option_size'] = 'Опція розміру (Google Merchant)';
$_['entry_option_color'] = 'Опція кольору (Google Merchant)';
$_['entry_currency'] = 'Валюта експорту';
$_['entry_language'] = 'Мова експорту';
$_['entry_image_origin'] = 'Використовувати оригінальні зображення';
$_['entry_image_width'] = 'Ширина зображення';
$_['entry_image_height'] = 'Висота зображення';
$_['entry_image_quantity'] = 'Кількість зображень';
$_['entry_image_count'] = 'Кількість зображень';
$_['entry_batch_size'] = 'Розмір партії генерації';
$_['entry_access_key'] = 'Ключ доступу';
$_['entry_status'] = 'Статус';
$_['entry_warranty_text'] = 'Статичний текст - Гарантія';
$_['entry_shop_name'] = 'Назва магазину';
$_['entry_company_name'] = 'Назва компанії';
$_['entry_shop_description'] = 'Опис магазину';
$_['entry_shop_country'] = 'Країна магазину';
$_['entry_delivery_service'] = 'Служба доставки';
$_['entry_delivery_days'] = 'Термін доставки в днях';
$_['entry_delivery_price'] = 'Ціна доставки';

// Columns
$_['column_name'] = 'Назва';
$_['column_url'] = 'Посилання на прайс-лист';
$_['column_filename'] = 'Ім\'я файлу';
$_['column_template'] = 'Шаблон';
$_['column_status'] = 'Статус';
$_['column_action'] = 'Дія';
$_['column_tag'] = 'Змінна';
$_['column_descrition'] = 'Опис';

// Tags
$_['tag_date'] = 'Дата експорту у форматі [YYYY–MM–DD hh:mm:ss]';
$_['tag_url'] = 'URL де доступний експорт';
$_['tag_currency'] = 'Валюта прайс-листу';
$_['tag_categories'] = 'Масив категорій експорту';
$_['tag_category'] = 'Масив категорії експорту';
$_['tag_category_id'] = 'Ідентифікатор категорії';
$_['tag_category_parent'] = 'Ідентифікатор батьківської категорії';
$_['tag_category_name'] = 'Назва категорії';
$_['tag_pages'] = 'Масив розділів';
$_['tag_page'] = 'Масив розділу';
$_['tag_products'] = 'Масив товарів експорту';
$_['tag_product'] = 'Масив товару експорту';
$_['tag_product_id'] = 'Ідентифікатор товару';
$_['tag_product_url'] = 'URL товару';
$_['tag_product_price'] = 'Ціна товару';
$_['tag_product_special'] = 'Спеціальна ціна товару';
$_['tag_product_category_id'] = 'Ідентифікатор категорії товару';
$_['tag_product_h1'] = 'Заголовок H1 товару';
$_['tag_product_name'] = 'Назва товару';
$_['tag_product_description'] = 'Опис товару';
$_['tag_product_model'] = 'Модель товару';
$_['tag_product_quantity'] = 'Кількість товару';
$_['tag_product_available'] = 'Доступність товару (Кількість > 1 = true, < 1 = false)';
$_['tag_product_vendor'] = 'Виробник товару';
$_['tag_product_vendor_code'] = 'Код продавця (SKU)';
$_['tag_product_images'] = 'Масив посилань на зображення товару';
$_['tag_product_image'] = 'Основне зображення товару';
$_['tag_product_shipping'] = 'Термін доставки (Атрибут або текст)';
$_['tag_product_warranty'] = 'Термін гарантії (Атрибут або текст)';
$_['tag_product_options'] = 'Масив опцій товару';
$_['tag_option'] = 'Масив опції товару';
$_['tag_option_id'] = 'ID значення опції';
$_['tag_option_group'] = 'Назва опції';
$_['tag_option_name'] = 'Значення опції (список)';
$_['tag_option_value'] = 'Значення опції (дата або текстове поле)';
$_['tag_option_price'] = 'Ціна опції';
$_['tag_option_weight'] = 'Вага опції';
$_['tag_option_quantity'] = 'Кількість опції';
$_['tag_offer_color'] = 'Опція розміру';
$_['tag_offer_size'] = 'Опція кольору';
$_['tag_product_attributes'] = 'Масив атрибутів товару';
$_['tag_attribute'] = 'Масив атрибуту товару';
$_['tag_attributes_group'] = 'Назва групи атрибутів';
$_['tag_attributes_name'] = 'Назва атрибуту';
$_['tag_attributes_value'] = 'Значення атрибуту';

// Template
$_['template_google'] = 'Google';
$_['template_facebook'] = 'Facebook';
$_['template_yml'] = 'YML';
$_['template_prom'] = 'Prom.ua';
$_['template_hotline'] = 'Hotline.ua';

// Help
$_['help_folder'] = 'Назва папки для зберігання XML файлів. Використовуйте тільки латинські літери та цифри';
$_['help_batch_size'] = 'Кількість товарів, що обробляються в одній партії під час генерації XML. Рекомендовано: 250-500';
$_['help_access_key'] = 'Ключ для захищеного доступу до XML фідів. Мінімум 8 символів';
$_['help_shop_name'] = 'Офіційна назва вашого магазину для експорту';
$_['help_company_name'] = 'Юридична назва компанії або ФОП';
$_['help_shop_description'] = 'Короткий опис діяльності магазину';
$_['help_shop_country'] = 'Оберіть країну, де зареєстровано ваш магазин';
$_['help_delivery_service'] = 'Назва служби доставки або спосіб доставки';
$_['help_delivery_days'] = 'Кількість днів доставки (1-365)';
$_['help_delivery_price'] = 'Ціна доставки цілим числом';
$_['help_name'] = 'Внутрішня назва фіду для ідентифікації';
$_['help_filename'] = 'Ім\'я XML файлу без розширення. Використовуйте латинські літери';
$_['help_currency'] = 'Курс валют використовується з налаштувань магазину';
$_['help_image_origin'] = 'Використовувати оригінальні зображення';
$_['help_image_quantity'] = 'Кількість додаткових зображень товару в експорті (Рекомендовано, не більше 8)';
$_['help_image_count'] = 'Кількість додаткових зображень товару в експорті (Рекомендовано, не більше 8)';
$_['help_limit'] = 'Кількість товарів за одну партію генерації (більше товарів = довше виконання) (Рекомендовано до 1000)';
$_['help_attribute_warranty'] = 'Атрибут товару з вказанням гарантійного терміну';
$_['help_category_related'] = 'Назва категорії для співставлення (Google Merchant та ін.)';
$_['help_category_related_id'] = 'ID категорії для співставлення (Google Merchant та ін.)';
$_['help_warranty_text'] = 'Вказуйте термін гарантії, наприклад: 24 місяці, 12, 3 місяці від магазину тощо';
$_['help_feed_generation'] = 'Скопіюйте це посилання для ручного запуску генерації XML фіду. Для автоматизації через cron використовуйте: <code>php /path/to/your/site/cli/aw_xml_feed.php</code>';

// Error
$_['error_permission'] = 'У вас немає прав для редагування цього модуля.';
$_['error_warning'] = 'Перевірте правильність заповнення всіх полів.';
$_['error_folder'] = 'Вкажіть назву папки для зберігання XML (від 3 до 64 символів).';
$_['error_batch_size'] = 'Розмір партії генерації не може перевищувати 1000 товарів.';
$_['error_access_key'] = 'Ключ доступу не може бути пустим і повинен містити мінімум 8 символів!';
$_['error_image_count'] = 'Один товар може мати не більше 8 зображень.';
$_['error_name'] = 'Вкажіть назву фіду.';
$_['error_filename'] = 'Вкажіть назву файлу фіду (від 3 до 128 символів).';
$_['error_shop_name'] = 'Вкажіть назву магазину (від 3 до 255 символів).';
$_['error_company_name'] = 'Вкажіть назву компанії (від 3 до 255 символів).';
$_['error_shop_description'] = 'Вкажіть опис магазину (від 10 до 255 символів).';
$_['error_warranty_text'] = 'Вкажіть текст гарантії (від 3 до 255 символів).';
$_['error_delivery_service'] = 'Вкажіть службу доставки (від 3 до 255 символів).';
$_['error_delivery_days'] = 'Термін доставки повинен бути від 1 до 365 днів.';
$_['error_delivery_price'] = 'Ціна доставки повинна бути позитивним числом.';

// Delivery text templates
$_['text_delivery_info'] = 'Доставка %s днів';
$_['text_delivery_info_with_price'] = 'Доставка %s днів, вартість %s';
