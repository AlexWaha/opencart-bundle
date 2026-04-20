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
$_['heading_title'] = '<span style="color:#0937cc;">alexwaha.com</span> - <i class="fa fa-puzzle-piece"></i> Смс уведомления';
$_['heading_main_title'] = 'alexwaha.com - Смс уведомления';

// Lang
$_['lang'] = 'ru-RU';

// Text
$_['text_extension']   = 'Расширения';
$_['text_success'] = 'Настройки модуля обновлены!';
$_['text_success_sms'] = 'Смс успешно отправлено!';
$_['text_success_log'] = 'Лог очищен!';
$_['text_sms_form'] = 'Произвольное смс сообщение';
$_['text_edit'] = 'Редактирование модуля';
$_['text_length'] = 'Длинна сообщения <b class="lenght">0</b> символов';
$_['text_phone_placeholder'] = '+38(012)1234567';

// Tabs
$_['tab_sms'] = 'Произвольное смс';
$_['tab_tags'] = 'Переменные';
$_['tab_template'] = 'Шаблоны смс';
$_['tab_viber_setting'] = 'Настройки Viber';
$_['tab_viber_template'] = 'Шаблоны уведомлений';
$_['tab_template_customer'] = 'Шаблоны смс покупателя';
$_['tab_setting'] = 'Настройки уведомлений';
$_['tab_gate_setting'] = 'Настройки шлюза';
$_['tab_log'] = 'Логи шлюза';
$_['tab_diagnostics'] = 'Диагностика';
$_['tab_import_export'] = 'Импорт/Экспорт';
$_['tab_support'] = 'Служба поддержки';

// Diagnostics
$_['text_diagnostics_loading']     = 'Выполняется диагностика...';
$_['text_diag_events_ok']          = 'Все события зарегистрированы';
$_['text_diag_events_missing']     = 'Некоторые события отсутствуют';
$_['text_diag_events_count']       = '%s из %s';
$_['text_diag_event_ok']           = 'OK';
$_['text_diag_event_fail']         = 'Отсутствует';
$_['text_diag_config_ok']          = 'Обязательные настройки заполнены';
$_['text_diag_config_missing']     = 'Не заполнены обязательные настройки';
$_['text_diag_log_file']           = 'Лог-файл';
$_['text_diag_log_enabled']        = 'включено';
$_['text_diag_log_disabled']       = 'логирование отключено';
$_['text_diag_log_empty']          = 'пусто - создастся при первой записи';
$_['text_diag_go_to_tab']          = 'Перейти к вкладке';
$_['text_diag_failed']             = 'Не удалось загрузить диагностику';

// Entry
$_['entry_template'] = 'Шаблон сообщения </br>';
$_['entry_sms_template'] = 'Заготовки для смс при просмотре заказа';
$_['entry_custom_client_sms_template'] = 'Заготовка для произвольного смс';
$_['entry_order_status'] = 'Смс для статусов:';
$_['entry_admin_alert'] = 'Отправить смс админу';
$_['entry_client_alert'] = 'Отправить смс покупателю';
$_['entry_order_alert'] = 'Смс при смене статуса заказа';
$_['entry_register_alert'] = 'Cмс покупателю при регистрации';
$_['entry_reviews'] = 'Смс для новых отзывов';
$_['entry_customer_group'] = 'Смс для групп покупателей';
$_['entry_payment_alert'] = 'Смс для способов оплаты';
$_['entry_force'] = 'Форсировать отправку смс';
$_['entry_translit'] = 'Транслит текста смс';

$_['entry_sms_gatename'] = 'SMS шлюз:';
$_['entry_sms_from'] = 'Отправитель';
$_['entry_sms_to'] = 'Номер телефона администратора';
$_['entry_sms_copy'] = 'Дополнительные номера';
$_['entry_sms_gate_username'] = 'Логин на SMS шлюз (или api_id)';
$_['entry_sms_gate_password'] = 'Пароль на SMS шлюз';
$_['entry_sms_log'] = 'Включить логи';
$_['entry_sms_notify_log_filename'] = 'Название .log файла';

$_['entry_client_phone'] = 'Номер телефона:';
$_['entry_client_sms'] = 'Текст сообщения:';
$_['entry_admin_template'] = 'Шаблон смс администратору (новый заказ)';
$_['entry_client_template'] = 'Шаблон смс покупателю (новый заказ)';
$_['entry_reviews_template'] = 'Шаблон сообщений для новых отзывов';
$_['entry_order_status_template'] = 'Шаблон сообщений для статусов заказ';
$_['entry_payment_template'] = 'Шаблон сообщений для способов оплаты';
$_['entry_register_template'] = 'Шаблон сообщений при регистрации';

$_['entry_viber_sender'] = 'Имя отправителя Viber:';
$_['entry_viber_alert'] = 'Отправить Viber сообщения:';
$_['entry_viber_ttl'] = 'Время жизни Viber сообщения (сек = 3600):';
$_['entry_viber_caption'] = 'Надпись на кнопке Viber сообщения:';
$_['entry_viber_image'] = 'Изображение в Viber сообщения:';
$_['entry_viber_url'] = 'Ссылка на кнопке:';
$_['entry_width'] = 'Ширины изображения:';
$_['entry_height'] = 'Высота изображения:';

// Order
$_['entry_sendsms'] = 'Отправить смс при смене статуса:';
$_['entry_sms_order_status'] = 'Статус заказа';
$_['entry_sms_message'] = 'Смс сообщение';

// Button
$_['button_send'] = 'Отправить смс';

$_['help_sms_payment'] = 'Если задан шаблон и включена отправка смс для <b>методов оплаты</b>, то шаблон Нового заказа для пользователя будет проигнорирован!';
$_['help_sms_from'] = 'Номер телефона или aлфавитно-цифровой отправитель';
$_['help_sms_copy'] = 'Введите номера через запятую (без пробелов) в международном формате +38(код оператора) или +7(код оператора) 1234567';
$_['help_phone'] = 'Введите телефон в международном формате +38(код оператора) или +7(код оператора) 1234567';
$_['help_force'] = 'Принудительно отправлять смс для автоматических рассылок';
$_['help_translit'] = 'Транслитерация текста, было - <b>Ваш заказ оформлен</b>, стало - <b>Vash zakaz oformlen</b>';
$_['help_order_status'] = 'Отправлять смс при смене статусов заказа';
$_['help_customer_group'] = 'Автоматическая отправка смс для выбранных групп покупателей. Если нет отмеченных, смс будет отправляется всем покупателям';
$_['help_payment_alert'] = 'Автоматическая отправка смс для выбранных способов оплаты после оформления заказа';
$_['help_product'] = 'Используйте осторожно, не допускайте ошибок! Пример: {% for product in products%} Товар:{{product.name}} Цена:{{product.price}}{% endfor %}';
$_['help_reviews'] = 'Разрешенные теги: {{product.name}}, {{product.model}}, {{product.sku}}, {{product.date}}, {{review.author}}, {{review.text}}, {{review.rating}}<br /> <b>Название товара сокращается до 50 символов</b>';
$_['help_register_template'] = 'Разрешенные теги: <br/><b>{{register.firstname}} - Имя</b>, <br/><b>{{register.lastname}} - Фамилия</b>, <br/><b>{{register.email}} - E-mail</b>, <br/><b>{{register.phone}} - Телефон</b>, <br/><b>{{register.password}} - Пароль</b><br />';

// Tags
$_['entry_tags'] = 'Список переменных';
$_['entry_tag_valiable'] = 'Переменная';
$_['entry_tag_description'] = 'Описание';
$_['tag_date'] = 'Дата';
$_['tag_current_date'] = 'Текущая дата';
$_['tag_time'] = 'Время';
$_['tag_store'] = 'Название магазина';
$_['tag_url'] = 'Ссылка магазина';
$_['tag_order_id'] = 'Номер заказа';
$_['tag_order_total'] = 'Сумма заказа';
$_['tag_order_total_noship'] = 'Сумма заказа без доставки';
$_['tag_order_phone'] = 'Телефон клиента';
$_['tag_order_comment'] = 'Комментарий';
$_['tag_order_status'] = 'Статус заказа';
$_['tag_payment_method'] = 'Способ оплаты';
$_['tag_payment_city'] = 'Город (оплаты)';
$_['tag_payment_address'] = 'Адрес (оплаты)';
$_['tag_shipping_cost'] = 'Стоимость доставки';
$_['tag_shipping_method'] = 'Способ доставки';
$_['tag_shipping_city'] = 'Город (доставка)';
$_['tag_shipping_address'] = 'Адрес (доставка)';
$_['tag_product_total'] = 'Всего товаров';
$_['tag_products'] = 'Массив товаров';
$_['tag_product_name'] = 'Название товара';
$_['tag_product_model'] = 'Модель товара';
$_['tag_product_sku'] = 'Код товаров';
$_['tag_product_price'] = 'Цена товара';
$_['tag_product_quantity'] = 'Количество товара';
$_['tag_firstname'] = 'Имя покупателя';
$_['tag_lastname'] = 'Фамилия покупателя';
$_['tag_track_no'] = 'Трек-номер заказа (если существует)';

// Error
$_['error_permission'] = 'У Вас нет прав для управления этим модулем!';
$_['error_sms_setting'] = 'Ошибка: Пожалуйста сперва задайте настройки смс шлюза!';
$_['error_sms'] = 'Ошибка: Смс не отправлено!';
$_['error_warning'] = 'Внимание: Пожалуйста, внимательно проверьте форму на наличие ошибок!';
$_['error_log_size'] = 'Внимание: Файл логов %s занимает %s!';
$_['error_log_file'] = 'Ошибка: Log файл не существует!';

$_['error_log_filename'] = 'Ошибка: Имя Log файла не задано!';
$_['error_gatename'] = 'Ошибка: Шлюз не выбран!';
$_['error_from'] = 'Ошибка: Альфа-имя отправителя не задано!';
$_['error_username'] = 'Ошибка: Логин SMS-шлюза (api_id) не задан!';
$_['error_admin_template'] = 'Ошибка: Отправка смс администратору при заказе включена, но шаблон смс не задан!';
$_['error_reviews_template'] = 'Ошибка: Отправка смс администратору на отзывы включена, но шаблон смс не задан!';
$_['error_client_template'] = 'Ошибка: Отправка смс покупателю при заказе включена, но шаблон смс не задан!';
$_['error_register_template'] = 'Ошибка: Отправка смс покупателю при регистрации включена, но шаблон смс не задан!';
$_['error_viber_sender'] = 'Ошибка: Имя отправителя Viber не задано!';
$_['error_client_viber_template'] = 'Ошибка: Отправка Viber сообщения покупателю при заказе включена, но шаблон смс не задан!';

// Import/Export
$_['text_import_export_title'] = 'Импорт и экспорт настроек модуля';
$_['text_import_export_info'] = 'Здесь вы можете экспортировать текущие настройки модуля в JSON файл или импортировать настройки из ранее сохраненного файла.';
$_['text_export_description'] = 'Нажмите кнопку "Экспорт", чтобы скачать текущие настройки модуля в формате JSON.';
$_['text_import_description'] = 'Выберите JSON файл с настройками и нажмите "Импорт" для загрузки настроек.';
$_['text_import_warning'] = '<strong>Внимание!</strong> Импорт настроек заменит все текущие настройки модуля. Рекомендуется сделать экспорт текущих настроек перед импортом.';
$_['text_import_success'] = 'Настройки успешно импортированы!';
$_['text_export_success'] = 'Настройки успешно экспортированы!';
$_['error_import_file'] = 'Пожалуйста, выберите файл для импорта!';
$_['error_import_invalid'] = 'Неверный формат файла. Ожидается JSON файл с настройками модуля.';
$_['error_import_failed'] = 'Ошибка при импорте настроек: %s';
$_['error_export_failed'] = 'Ошибка при экспорте настроек: %s';
$_['error_import_read_file'] = 'Не удалось прочитать загруженный файл';
$_['button_export'] = 'Экспорт настроек';
$_['button_import'] = 'Импорт настроек';

// Telegram
$_['tab_telegram'] = 'Telegram';
$_['entry_tg_enabled'] = 'Включить Telegram-уведомления';
$_['entry_tg_bot_token'] = 'Токен бота (из @BotFather)';
$_['entry_tg_chat_id'] = 'Chat ID (группа или личный)';
$_['entry_tg_alert_order'] = 'Уведомлять о новом заказе';
$_['entry_tg_alert_register'] = 'Уведомлять о регистрации нового покупателя';
$_['entry_tg_alert_review'] = 'Уведомлять о новом отзыве';
$_['entry_tg_template_order'] = 'Шаблон: новый заказ';
$_['entry_tg_template_register'] = 'Шаблон: новая регистрация';
$_['entry_tg_template_review'] = 'Шаблон: новый отзыв';
$_['text_tg_setup_title'] = 'Как настроить Telegram-бота';
$_['text_tg_setup_steps'] = <<<'HTML'
<ol class="mb-0">
    <li>Откройте <b>@BotFather</b> в Telegram → отправьте <code>/newbot</code> → задайте имя → задайте username → скопируйте <b>TOKEN</b>.</li>
    <li>Вставьте <b>TOKEN</b> в поле выше.</li>
    <li>Создайте группу (или используйте существующую) → добавьте бота участником.</li>
    <li>Откройте <b>@RawDataBot</b> в Telegram, добавьте его в ту же группу → бот пришлёт <code>chat_id</code> (отрицательное число для групп). После этого удалите RawDataBot из группы.</li>
    <li>Вставьте <b>chat_id</b> в поле выше.</li>
    <li>Включите мастер-переключатель и нужные события.</li>
    <li>Откройте вкладку <b>Диагностика</b> и убедитесь, что «Telegram-бот доступен».</li>
</ol>
HTML;
$_['error_tg_token'] = 'Токен бота не задан';
$_['error_tg_chat_id'] = 'Chat ID не задан';
$_['error_tg_detect_failed'] = 'Не удалось получить список чатов у Telegram';
$_['button_tg_detect_chats'] = 'Получить чаты';
$_['text_tg_detecting'] = 'Получение...';
$_['text_tg_no_chats_found'] = 'Чаты не найдены. Убедитесь, что бот добавлен в группу как админ (или выключите Group Privacy через @BotFather → /mybots), отправьте в группе любое сообщение и нажмите ещё раз.';
$_['text_tg_chats_select'] = 'Нажмите на чат, чтобы подставить его ID:';
$_['text_diag_tg_ok'] = 'Telegram-бот доступен';
$_['text_diag_tg_fail'] = 'Telegram-бот недоступен';
$_['text_diag_tg_disabled'] = 'Telegram отключён';
$_['text_diag_tg_chat_ok'] = 'Chat ID настроен';
$_['text_diag_tg_chat_missing'] = 'Chat ID не настроен';
$_['text_diag_tg_templates_ok'] = 'Шаблоны заданы для включённых событий';
$_['text_diag_tg_templates_missing'] = 'Не заданы шаблоны для включённых событий';
$_['help_tg_template'] = 'Используйте те же теги, что и в SMS-шаблонах. Поддерживается HTML: &lt;b&gt;, &lt;i&gt;, &lt;a href=...&gt;.';

// OTP
$_['tab_otp'] = 'OTP-подтверждение';
$_['entry_otp_enabled'] = 'Включить OTP-подтверждение по SMS';
$_['entry_otp_protect_register'] = 'Защитить стандартную регистрацию';
$_['entry_otp_protect_checkout_std'] = 'Защитить стандартный гостевой checkout';
$_['entry_otp_protect_checkout_easy'] = 'Защитить aw_easy_checkout';
$_['entry_otp_protect_universal'] = 'Universal mode (любые 3rd-party формы)';
$_['entry_otp_ttl'] = 'Время жизни кода (сек)';
$_['entry_otp_throttle'] = 'Минимальный интервал между запросами кода (сек)';
$_['entry_otp_max_attempts'] = 'Максимум попыток ввода кода';
$_['entry_otp_template'] = 'Шаблон SMS с кодом (используйте {{code}})';
$_['entry_otp_modal_title'] = 'Заголовок модалки подтверждения';
$_['entry_otp_modal_text'] = 'Текст модалки подтверждения';
$_['text_otp_help_universal'] = '<b>Внимание:</b> Universal mode блокирует любое создание заказа/покупателя через model-события. В худшем случае при 3rd-party форме без поддержки OTP пользователь увидит грубую ошибку вместо модалки. Включайте только если стандартные/easy_checkout-перехваты недостаточны.';
$_['help_otp_ttl'] = 'Срок жизни сгенерированного кода в секундах. По умолчанию 300 (5 минут).';
$_['help_otp_throttle'] = 'Минимальная задержка между двумя последовательными запросами кода. По умолчанию 30 секунд.';
$_['help_otp_max_attempts'] = 'Сколько неверных вводов разрешено до сброса кода. По умолчанию 5.';
$_['entry_otp_max_resends'] = 'Максимум повторных запросов кода';
$_['help_otp_max_resends'] = 'Сколько раз пользователь может запросить новый код. По умолчанию 2. После превышения - блокировка.';
$_['entry_otp_lockout_duration'] = 'Длительность блокировки (сек)';
$_['help_otp_lockout_duration'] = 'Как долго пользователь заблокирован для запроса кодов после превышения лимита. По умолчанию 7200 (2 часа).';
$_['text_otp_template_default'] = 'Ваш код подтверждения: {{code}}';
$_['text_otp_modal_title_default'] = 'Подтверждение телефона';
$_['text_otp_modal_text_default'] = 'Введите 6-значный код, отправленный на {{phone}}';
$_['help_otp_template'] = 'Доступный тег: {{code}}';
$_['help_otp_modal_text'] = 'Доступный тег: {{phone}}';
$_['text_diag_otp_ok'] = 'Конфигурация OTP валидна';
$_['text_diag_otp_fail'] = 'Конфигурация OTP некорректна';
$_['text_diag_otp_disabled'] = 'OTP отключён';
$_['text_diag_otp_events_ok'] = 'Все OTP-события зарегистрированы';
$_['text_diag_otp_events_missing'] = 'Часть OTP-событий не зарегистрирована';
$_['text_diag_otp_event_ok'] = 'OK';
$_['text_diag_otp_event_fail'] = 'Отсутствует';
$_['text_diag_otp_no_gateway'] = 'SMS-шлюз не настроен (нужен для OTP)';
$_['text_diag_otp_gateway_ok'] = 'SMS-шлюз настроен';
$_['text_diag_otp_templates_ok'] = 'Все шаблоны OTP заполнены';
$_['text_diag_otp_templates_missing'] = 'Часть шаблонов OTP не заполнена';
$_['text_diag_otp_modal_custom'] = 'Заголовок и текст модалки настроены';
$_['text_diag_otp_modal_defaults'] = 'Модалка: используются дефолты из языкового файла';
