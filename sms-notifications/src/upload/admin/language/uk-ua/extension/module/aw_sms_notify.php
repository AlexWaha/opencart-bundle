<?php

/**
 * @author  Alexander Vakhovski (AlexWaha)
 *
 * @link    https://alexwaha.com
 *
 * @email   support@alexwaha.com
 *
 * @license GPLv3
 */

// Heading
$_['heading_title'] = '<span style="color:#0937cc;">alexwaha.com</span> - <i class="fa fa-puzzle-piece"></i> SMS повідомлення';
$_['heading_main_title'] = 'alexwaha.com - SMS повідомлення';

// Lang
$_['lang'] = 'uk-UA';

// Text
$_['text_extension']   = 'Розширення';
$_['text_success'] = 'Налаштування модуля оновлено!';
$_['text_success_sms'] = 'SMS успішно надіслано!';
$_['text_success_log'] = 'Лог очищено!';
$_['text_sms_form'] = 'Довільне SMS повідомлення';
$_['text_edit'] = 'Редагування модуля';
$_['text_length'] = 'Довжина повідомлення <b class="lenght">0</b> символів';
$_['text_phone_placeholder'] = '+38(012)1234567';

// Tabs
$_['tab_sms'] = 'Довільне SMS';
$_['tab_tags'] = 'Змінні';
$_['tab_template'] = 'Шаблони SMS';
$_['tab_viber_setting'] = 'Налаштування Viber';
$_['tab_viber_template'] = 'Шаблони сповіщень';
$_['tab_template_customer'] = 'Шаблони SMS для покупця';
$_['tab_setting'] = 'Налаштування сповіщень';
$_['tab_gate_setting'] = 'Налаштування шлюза';
$_['tab_log'] = 'Логи шлюза';
$_['tab_import_export'] = 'Імпорт/Експорт';
$_['tab_support'] = 'Служба підтримки';

// Entry
$_['entry_template'] = 'Шаблон повідомлення </br>';
$_['entry_sms_template'] = 'Заготовки для SMS при перегляді замовлення';
$_['entry_order_status'] = 'SMS для статусів:';
$_['entry_admin_alert'] = 'Надіслати SMS адміністратору';
$_['entry_client_alert'] = 'Надіслати SMS покупцеві';
$_['entry_order_alert'] = 'SMS при зміні статусу замовлення';
$_['entry_register_alert'] = 'SMS покупцеві при реєстрації';
$_['entry_reviews'] = 'SMS для нових відгуків';
$_['entry_customer_group'] = 'SMS для груп покупців';
$_['entry_payment_alert'] = 'SMS для способів оплати';
$_['entry_force'] = 'Примусова відправка SMS';
$_['entry_translit'] = 'Трансліт тексту SMS';

$_['entry_sms_gatename'] = 'SMS шлюз:';
$_['entry_sms_from'] = 'Відправник';
$_['entry_sms_to'] = 'Номер телефону адміністратора';
$_['entry_sms_copy'] = 'Додаткові номери';
$_['entry_sms_gate_username'] = 'Логін до SMS шлюза (або api_id)';
$_['entry_sms_gate_password'] = 'Пароль до SMS шлюза';
$_['entry_sms_log'] = 'Увімкнути логи';
$_['entry_sms_notify_log_filename'] = 'Назва .log файлу';

$_['entry_client_phone'] = 'Номер телефону:';
$_['entry_client_sms'] = 'Текст повідомлення:';
$_['entry_admin_template'] = 'Шаблон SMS адміністратору (нове замовлення)';
$_['entry_client_template'] = 'Шаблон SMS покупцеві (нове замовлення)';
$_['entry_reviews_template'] = 'Шаблон повідомлень для нових відгуків';
$_['entry_order_status_template'] = 'Шаблон повідомлень для статусів замовлення';
$_['entry_payment_template'] = 'Шаблон повідомлень для способів оплати';
$_['entry_register_template'] = 'Шаблон повідомлень при реєстрації';

$_['entry_viber_sender'] = 'Ім\'я відправника Viber:';
$_['entry_viber_alert'] = 'Надсилати Viber повідомлення:';
$_['entry_viber_ttl'] = 'Час життя Viber повідомлення (сек = 3600):';
$_['entry_viber_caption'] = 'Напис на кнопці Viber повідомлення:';
$_['entry_viber_image'] = 'Зображення у Viber повідомленні:';
$_['entry_viber_url'] = 'Посилання на кнопці:';
$_['entry_width'] = 'Ширина зображення:';
$_['entry_height'] = 'Висота зображення:';

// Order
$_['entry_sendsms'] = 'Надіслати SMS при зміні статусу:';
$_['entry_sms_order_status'] = 'Статус замовлення';
$_['entry_sms_message'] = 'SMS повідомлення';

// Button
$_['button_send'] = 'Надіслати SMS';

$_['help_sms_payment'] = 'Якщо задано шаблон і увімкнено відправку SMS для <b>методів оплати</b>, то шаблон нового замовлення для користувача буде проігноровано!';
$_['help_sms_from'] = 'Номер телефону або буквено-цифровий відправник';
$_['help_sms_copy'] = 'Введіть номери через кому (без пробілів) у міжнародному форматі +38(код оператора) або +7(код оператора) 1234567';
$_['help_phone'] = 'Введіть телефон у міжнародному форматі +38(код оператора) або +7(код оператора) 1234567';
$_['help_force'] = 'Примусово відправляти SMS для автоматичних розсилок';
$_['help_translit'] = 'Транслітерація тексту, було - <b>Ваше замовлення оформлено</b>, стало - <b>Vash zamovlennya oformleno</b>';
$_['help_order_status'] = 'Надсилати SMS при зміні статусів замовлення';
$_['help_customer_group'] = 'Автоматична відправка SMS для обраних груп покупців. Якщо не вибрано жодної, SMS буде надсилатися всім покупцям';
$_['help_payment_alert'] = 'Автоматична відправка SMS для обраних способів оплати після оформлення замовлення';
$_['help_product'] = 'Використовуйте обережно, не допускайте помилок! Приклад: {% for product in products%} Товар:{{product.name}} Ціна:{{product.price}}{% endfor %}';
$_['help_reviews'] = 'Дозволені теги {{product.name}}, {{product.model}}, {{product.sku}}, {{product.date}}<br /> <b>Назва товару скорочується до 50 символів</b>';
$_['help_register_template'] = 'Дозволені теги: <br/><b>{{register.firstname}} - Ім\'я</b>, <br/><b>{{register.lastname}} - Прізвище</b>, <br/><b>{{register.email}} - E-mail</b>, <br/><b>{{register.phone}} - Телефон</b>, <br/><b>{{register.password}} - Пароль</b><br />';

// Tags
$_['entry_tags'] = 'Список змінних';
$_['entry_tag_valiable'] = 'Змінна';
$_['entry_tag_description'] = 'Опис';
$_['tag_date'] = 'Дата';
$_['tag_current_date'] = 'Поточна дата';
$_['tag_time'] = 'Час';
$_['tag_store'] = 'Назва магазину';
$_['tag_url'] = 'Посилання магазину';
$_['tag_order_id'] = 'Номер замовлення';
$_['tag_order_total'] = 'Сума замовлення';
$_['tag_order_total_noship'] = 'Сума замовлення без доставки';
$_['tag_order_phone'] = 'Телефон клієнта';
$_['tag_order_comment'] = 'Коментар';
$_['tag_order_status'] = 'Статус замовлення';
$_['tag_payment_method'] = 'Метод оплати';
$_['tag_payment_city'] = 'Місто (оплати)';
$_['tag_payment_address'] = 'Адреса (оплати)';
$_['tag_shipping_cost'] = 'Вартість доставки';
$_['tag_shipping_method'] = 'Метод доставки';
$_['tag_shipping_city'] = 'Місто (доставка)';
$_['tag_shipping_address'] = 'Адреса (доставка)';
$_['tag_product_total'] = 'Всього товарів';
$_['tag_products'] = 'Масив товарів';
$_['tag_product_name'] = 'Назва товару';
$_['tag_product_model'] = 'Модель товару';
$_['tag_product_sku'] = 'Код товару';
$_['tag_product_price'] = 'Ціна товару';
$_['tag_product_quantity'] = 'Кількість товару';
$_['tag_firstname'] = 'Ім\'я покупця';
$_['tag_lastname'] = 'Прізвище покупця';
$_['tag_track_no'] = 'Трек-номер замовлення (якщо існує)';

// Error
$_['error_permission'] = 'У Вас немає прав для керування цим модулем!';
$_['error_sms_setting'] = 'Помилка: Будь ласка, спершу задайте налаштування SMS шлюза!';
$_['error_sms'] = 'Помилка: SMS не надіслано!';
$_['error_warning'] = 'Увага: Будь ласка, уважно перевірте форму на наявність помилок!';
$_['error_log_size'] = 'Увага: Файл логів %s займає %s!';
$_['error_log_file'] = 'Помилка: Log файл не існує!';

$_['error_log_filename'] = 'Помилка: Ім\'я Log файлу не вказано!';
$_['error_gatename'] = 'Помилка: Шлюз не вибрано!';
$_['error_from'] = 'Помилка: Альфа-ім\'я відправника не вказано!';
$_['error_username'] = 'Помилка: Логін SMS шлюза (api_id) не вказано!';
$_['error_admin_template'] = 'Помилка: Надсилання SMS адміністратору при замовленні увімкнено, але шаблон не вказано!';
$_['error_reviews_template'] = 'Помилка: Надсилання SMS адміністратору про відгуки увімкнено, але шаблон не вказано!';
$_['error_client_template'] = 'Помилка: Надсилання SMS покупцеві при замовленні увімкнено, але шаблон не вказано!';
$_['error_register_template'] = 'Помилка: Надсилання SMS покупцеві при реєстрації увімкнено, але шаблон не вказано!';
$_['error_viber_sender'] = 'Помилка: Ім\'я відправника Viber не вказано!';
$_['error_client_viber_template'] = 'Помилка: Надсилання Viber повідомлення покупцеві при замовленні увімкнено, але шаблон не вказано!';

// Import/Export
$_['text_import_export_title'] = 'Імпорт та експорт налаштувань модуля';
$_['text_import_export_info'] = 'Тут ви можете експортувати поточні налаштування модуля у JSON файл або імпортувати налаштування з раніше збереженого файлу.';
$_['text_export_description'] = 'Натисніть кнопку "Експорт", щоб завантажити поточні налаштування модуля у форматі JSON.';
$_['text_import_description'] = 'Виберіть JSON файл з налаштуваннями та натисніть "Імпорт" для завантаження налаштувань.';
$_['text_import_warning'] = '<strong>Увага!</strong> Імпорт налаштувань замінить усі поточні налаштування модуля. Рекомендується зробити експорт поточних налаштувань перед імпортом.';
$_['text_import_success'] = 'Налаштування успішно імпортовано!';
$_['text_export_success'] = 'Налаштування успішно експортовано!';
$_['error_import_file'] = 'Будь ласка, виберіть файл для імпорту!';
$_['error_import_invalid'] = 'Невірний формат файлу. Очікується JSON файл з налаштуваннями модуля.';
$_['error_import_failed'] = 'Помилка при імпорті налаштувань: %s';
$_['error_export_failed'] = 'Помилка при експорті налаштувань: %s';
$_['error_import_read_file'] = 'Не вдалося прочитати завантажений файл';
$_['button_export'] = 'Експорт налаштувань';
$_['button_import'] = 'Імпорт налаштувань';
