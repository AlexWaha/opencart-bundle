<?php

// Heading
$_['heading_title'] = '<span style="color:#0937cc;">alexwaha.com</span> - <i class="fa fa-puzzle-piece"></i> Redirect Manager';
$_['heading_main_title'] = 'alexwaha.com - Redirect Manager';
$_['text_menu'] = 'Redirect Manager';

// Text
$_['text_extension'] = 'Расширения';
$_['text_home'] = 'Главная';
$_['text_list'] = 'Правила редиректов';
$_['text_log'] = 'Резолвер 404';
$_['text_settings'] = 'Настройки';
$_['text_add'] = 'Добавить редирект';
$_['text_edit'] = 'Редактировать редирект';
$_['text_all'] = '--- Все ---';
$_['text_all_stores'] = 'Все магазины';
$_['text_exact'] = 'Точный';
$_['text_wildcard'] = 'Шаблон';
$_['text_enabled'] = 'Включён';
$_['text_disabled'] = 'Отключён';
$_['text_back'] = 'Назад';
$_['text_no_results'] = 'Нет данных';
$_['text_log_info'] = 'URL, вернувшие 404, попадают сюда (с дедупликацией и счётчиком обращений). Сопоставьте их с корректным URL или направьте на главную.';
$_['text_pagination'] = 'Показано %d - %d из %d (страниц: %d)';
$_['text_success'] = 'Настройки сохранены!';
$_['text_success_add'] = 'Редирект добавлен!';
$_['text_success_edit'] = 'Редирект обновлён!';
$_['text_success_delete'] = 'Выбранные записи удалены!';
$_['text_success_clear'] = 'Лог 404 очищен!';
$_['text_success_home'] = 'Редиректы на главную созданы!';
$_['text_import_success'] = 'Импортировано редиректов: %d!';
$_['text_import_confirm'] = 'Импортировать редиректы из этого CSV-файла?';
$_['text_confirm'] = 'Вы уверены?';
$_['text_confirm_clear'] = 'Очистить весь лог 404?';
$_['text_confirm_home'] = 'Создать 301-редиректы на главную для выбранных URL?';
$_['text_aw_support'] = '<div class="panel panel-success"><div class="panel-heading"><h3 class="panel-title"><i class="fa fa-life-ring"></i> Поддержка</h3></div><div class="panel-body"><p>Если у вас есть вопросы по модулю, свяжитесь с разработчиком:</p><ul><li><strong>Email:</strong> <a href="mailto:support@alexwaha.com">support@alexwaha.com</a></li><li><strong>Сайт:</strong> <a href="https://alexwaha.com" target="_blank">alexwaha.com</a></li></ul></div></div>';

// Tabs
$_['tab_general'] = 'Основные';
$_['tab_import_export'] = 'Импорт / Экспорт';
$_['tab_support'] = 'Поддержка';

// Columns
$_['column_source'] = 'Исходный URL';
$_['column_target'] = 'Целевой URL';
$_['column_type'] = 'Тип';
$_['column_code'] = 'Код';
$_['column_hits'] = 'Срабатываний';
$_['column_status'] = 'Статус';
$_['column_url'] = 'Запрошенный URL';
$_['column_last_seen'] = 'Последний раз';
$_['column_action'] = 'Действие';

// Entry
$_['entry_source'] = 'Исходный URL';
$_['entry_target'] = 'Целевой URL';
$_['entry_match_type'] = 'Тип';
$_['entry_match_query'] = 'Учитывать query-строку';
$_['entry_code'] = 'Код редиректа';
$_['entry_store'] = 'Магазин';
$_['entry_status'] = 'Статус';
$_['entry_default_code'] = 'Код по умолчанию';
$_['entry_log_404'] = 'Логировать ошибки 404';
$_['entry_ignore'] = 'Шаблоны исключений';

// Help
$_['help_source'] = 'Путь для сопоставления, напр. <code>/old-page</code>. Используйте <code>*</code> для шаблона, напр. <code>/blog/*</code>. Без учёта регистра и завершающего слеша.';
$_['help_target'] = 'Относительный путь (<code>/new-page</code>) или абсолютный URL (<code>https://...</code>). Не требуется для кода 410.';
$_['help_match_query'] = 'Учитывать query-строку при сопоставлении (напр. <code>catalog.php?id=5</code> с другой платформы).';
$_['help_code'] = '301 - постоянный, 302 - временный, 410 - удалено (без редиректа).';
$_['help_status'] = 'Главный выключатель модуля на витрине.';
$_['help_default_code'] = 'Код, выбранный по умолчанию при добавлении редиректа.';
$_['help_log_404'] = 'Автоматически логировать URL, возвращающие 404.';
$_['help_ignore'] = '404-URL, совпавший с любым из шаблонов, <strong>не логируется</strong> - отсекает шум ботов/сканеров (на редиректы не влияет). Один шаблон на строку, без учёта регистра. <code>*</code> - любые символы, <code>?</code> - один символ; сравнивается с полным путём + query. Примеры: <code>*.php</code> (поиск php), <code>/wp-*</code> (боты WordPress), <code>*.env</code>, <code>/feed/*</code>.';

// Buttons
$_['button_add'] = 'Добавить';
$_['button_delete'] = 'Удалить';
$_['button_filter'] = 'Фильтр';
$_['button_save'] = 'Сохранить';
$_['button_cancel'] = 'Отмена';
$_['button_create'] = 'Редирект';
$_['button_clear'] = 'Очистить лог';
$_['button_redirect_home'] = 'Редирект на главную';
$_['button_export'] = 'Экспорт CSV';
$_['button_import'] = 'Импорт CSV';

// Import / Export
$_['text_export_description'] = 'Скачать все правила редиректов в CSV-файл.';
$_['text_import_description'] = 'Загрузить CSV-файл для массового добавления правил.';
$_['text_import_warning'] = 'Новые строки добавляются. Существующие правила не удаляются.';

// Error
$_['error_permission'] = 'Внимание: у вас нет прав для изменения модуля Redirect Manager!';
$_['error_warning'] = 'Внимание: внимательно проверьте форму на ошибки!';
$_['error_source'] = 'Исходный URL обязателен!';
$_['error_target'] = 'Целевой URL обязателен!';
$_['error_code'] = 'Недопустимый код редиректа!';
$_['error_loop'] = 'Цель должна отличаться от источника (петля редиректа)!';
$_['error_duplicate'] = 'Редирект с таким источником уже существует для этого магазина!';
$_['error_not_found'] = 'Внимание: редирект не найден!';
$_['error_import_file'] = 'Внимание: выберите корректный CSV-файл!';
$_['error_import_format'] = 'Внимание: некорректный CSV - первый столбец должен быть "source"!';
