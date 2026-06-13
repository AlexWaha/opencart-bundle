<?php

// Heading
$_['heading_title'] = '<span style="color:#0937cc;">alexwaha.com</span> - <i class="fa fa-puzzle-piece"></i> Redirect Manager';
$_['heading_main_title'] = 'alexwaha.com - Redirect Manager';
$_['text_menu'] = 'Redirect Manager';

// Text
$_['text_extension'] = 'Розширення';
$_['text_home'] = 'Головна';
$_['text_list'] = 'Правила редиректів';
$_['text_log'] = 'Резолвер 404';
$_['text_settings'] = 'Налаштування';
$_['text_add'] = 'Додати редирект';
$_['text_edit'] = 'Редагувати редирект';
$_['text_all'] = '--- Усі ---';
$_['text_all_stores'] = 'Усі магазини';
$_['text_exact'] = 'Точний';
$_['text_wildcard'] = 'Шаблон';
$_['text_enabled'] = 'Увімкнено';
$_['text_disabled'] = 'Вимкнено';
$_['text_back'] = 'Назад';
$_['text_no_results'] = 'Немає даних';
$_['text_log_info'] = 'URL, що повернули 404, потрапляють сюди (з дедуплікацією та лічильником звернень). Зіставте їх з коректним URL або спрямуйте на головну.';
$_['text_pagination'] = 'Показано %d - %d з %d (сторінок: %d)';
$_['text_success'] = 'Налаштування збережено!';
$_['text_success_add'] = 'Редирект додано!';
$_['text_success_edit'] = 'Редирект оновлено!';
$_['text_success_delete'] = 'Вибрані записи видалено!';
$_['text_success_clear'] = 'Лог 404 очищено!';
$_['text_success_home'] = 'Редиректи на головну створено!';
$_['text_import_success'] = 'Імпортовано редиректів: %d!';
$_['text_import_confirm'] = 'Імпортувати редиректи з цього CSV-файлу?';
$_['text_confirm'] = 'Ви впевнені?';
$_['text_confirm_clear'] = 'Очистити весь лог 404?';
$_['text_confirm_home'] = 'Створити 301-редиректи на головну для вибраних URL?';
$_['text_aw_support'] = '<div class="panel panel-success"><div class="panel-heading"><h3 class="panel-title"><i class="fa fa-life-ring"></i> Підтримка</h3></div><div class="panel-body"><p>Якщо у вас є запитання щодо модуля, зв`яжіться з розробником:</p><ul><li><strong>Email:</strong> <a href="mailto:support@alexwaha.com">support@alexwaha.com</a></li><li><strong>Сайт:</strong> <a href="https://alexwaha.com" target="_blank">alexwaha.com</a></li></ul></div></div>';

// Tabs
$_['tab_general'] = 'Основні';
$_['tab_import_export'] = 'Імпорт / Експорт';
$_['tab_support'] = 'Підтримка';

// Columns
$_['column_source'] = 'Початковий URL';
$_['column_target'] = 'Цільовий URL';
$_['column_type'] = 'Тип';
$_['column_code'] = 'Код';
$_['column_hits'] = 'Спрацювань';
$_['column_status'] = 'Статус';
$_['column_url'] = 'Запитаний URL';
$_['column_last_seen'] = 'Останній раз';
$_['column_action'] = 'Дія';

// Entry
$_['entry_source'] = 'Початковий URL';
$_['entry_target'] = 'Цільовий URL';
$_['entry_match_type'] = 'Тип';
$_['entry_match_query'] = 'Враховувати query-рядок';
$_['entry_code'] = 'Код редиректу';
$_['entry_store'] = 'Магазин';
$_['entry_status'] = 'Статус';
$_['entry_default_code'] = 'Код за замовчуванням';
$_['entry_log_404'] = 'Логувати помилки 404';
$_['entry_ignore'] = 'Шаблони винятків';

// Help
$_['help_source'] = 'Шлях для зіставлення, напр. <code>/old-page</code>. Використовуйте <code>*</code> для шаблону, напр. <code>/blog/*</code>. Без урахування регістру та завершального слешу.';
$_['help_target'] = 'Відносний шлях (<code>/new-page</code>) або абсолютний URL (<code>https://...</code>). Не потрібен для коду 410.';
$_['help_match_query'] = 'Враховувати query-рядок при зіставленні (напр. <code>catalog.php?id=5</code> з іншої платформи).';
$_['help_code'] = '301 - постійний, 302 - тимчасовий, 410 - видалено (без редиректу).';
$_['help_status'] = 'Головний вимикач модуля на вітрині.';
$_['help_default_code'] = 'Код, вибраний за замовчуванням при додаванні редиректу.';
$_['help_log_404'] = 'Автоматично логувати URL, що повертають 404.';
$_['help_ignore'] = '404-URL, що збігся з будь-яким із шаблонів, <strong>не логується</strong> - відсікає шум ботів/сканерів (на редиректи не впливає). Один шаблон на рядок, без урахування регістру. <code>*</code> - будь-які символи, <code>?</code> - один символ; порівнюється з повним шляхом + query. Приклади: <code>*.php</code> (пошук php), <code>/wp-*</code> (боти WordPress), <code>*.env</code>, <code>/feed/*</code>.';

// Buttons
$_['button_add'] = 'Додати';
$_['button_delete'] = 'Видалити';
$_['button_filter'] = 'Фільтр';
$_['button_save'] = 'Зберегти';
$_['button_cancel'] = 'Скасувати';
$_['button_create'] = 'Редирект';
$_['button_clear'] = 'Очистити лог';
$_['button_redirect_home'] = 'Редирект на головну';
$_['button_export'] = 'Експорт CSV';
$_['button_import'] = 'Імпорт CSV';

// Import / Export
$_['text_export_description'] = 'Завантажити всі правила редиректів у CSV-файл.';
$_['text_import_description'] = 'Завантажити CSV-файл для масового додавання правил.';
$_['text_import_warning'] = 'Нові рядки додаються. Наявні правила не видаляються.';

// Error
$_['error_permission'] = 'Увага: у вас немає прав для зміни модуля Redirect Manager!';
$_['error_warning'] = 'Увага: уважно перевірте форму на помилки!';
$_['error_source'] = 'Початковий URL обовʼязковий!';
$_['error_target'] = 'Цільовий URL обовʼязковий!';
$_['error_code'] = 'Недопустимий код редиректу!';
$_['error_loop'] = 'Ціль має відрізнятися від джерела (петля редиректу)!';
$_['error_duplicate'] = 'Редирект з таким джерелом уже існує для цього магазину!';
$_['error_not_found'] = 'Увага: редирект не знайдено!';
$_['error_import_file'] = 'Увага: виберіть коректний CSV-файл!';
$_['error_import_format'] = 'Увага: некоректний CSV - перший стовпець має бути "source"!';
