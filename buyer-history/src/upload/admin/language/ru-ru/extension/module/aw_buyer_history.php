<?php

// Heading
$_['heading_title']      = '<span style="color:#0937cc;">alexwaha.com</span> - <i class="fa fa-puzzle-piece"></i> Buyer History';
$_['heading_main_title'] = 'alexwaha.com - Buyer History';

// Text
$_['text_extension']     = 'Расширения';
$_['text_edit']          = 'Настройки модуля';
$_['text_success']       = 'Настройки успешно сохранены!';
$_['text_home']          = 'Главная';
$_['text_yes']           = 'Да';
$_['text_no']            = 'Нет';
$_['text_preview']       = 'Предпросмотр';

// Tabs
$_['tab_general']        = 'Общие';
$_['tab_thresholds']     = 'Пороги и цвета';
$_['tab_statuses']       = 'Цвета статусов';
$_['tab_import_export']  = 'Импорт / Экспорт';
$_['button_export']               = 'Экспортировать';
$_['button_import']               = 'Импортировать';
$_['text_import_export_title']    = 'Импорт / Экспорт настроек';
$_['text_import_export_info']     = 'Экспортируйте текущие настройки модуля в JSON-файл или импортируйте ранее сохранённые настройки.';
$_['text_loading']                = 'Загрузка...';
$_['text_export_description']     = 'Скачать текущую конфигурацию как JSON-файл.';
$_['text_import_description']     = 'Выберите ранее экспортированный JSON-файл для восстановления настроек.';
$_['text_import_warning']         = '<strong>Внимание!</strong> Импорт перезапишет все текущие настройки. Действие нельзя отменить.';
$_['text_import_success']         = 'Настройки успешно импортированы! Страница будет перезагружена...';
$_['error_import_failed']         = 'Ошибка импорта: %s';
$_['error_import_read_file']      = 'Не удалось прочитать загруженный файл';
$_['error_import_file']           = 'Выберите файл для импорта';
$_['help_status_colors'] = 'Задайте цвет для каждого статуса заказа. На странице «История покупателей» в раскрытой строке клиента статусы заказов будут подсвечены этим цветом. Пустое значение - без цвета.';
$_['tab_duplicates']     = 'Дубликаты';
$_['tab_display']        = 'Отображение';
$_['tab_support']        = 'Поддержка';

// Table column labels
$_['column_history']     = 'История';
$_['column_duplicates']  = 'Дубликаты';

// Tab: General
$_['entry_status']           = 'Статус';
$_['entry_match_guests']     = 'Учитывать гостей по email + телефону';
$_['help_match_guests']      = 'Связывать заказы гостей (customer_id=0) по нормализованным email и телефону.';
$_['entry_tracked_statuses'] = 'Учитывать статусы заказов';
$_['help_tracked_statuses']  = 'Отметьте статусы, которые засчитываются в счётчик и в разбивку. Снятый чекбокс - заказы этого статуса игнорируются.';

// Tab: Thresholds + colors
$_['entry_threshold_mid']    = 'Порог среднего уровня (от N заказов)';
$_['entry_threshold_high']   = 'Порог высокого уровня (от N заказов)';
$_['entry_color_low']        = 'Бейдж «начальный уровень»';
$_['entry_color_mid']        = 'Бейдж «средний уровень»';
$_['entry_color_high']       = 'Бейдж «высокий уровень»';
$_['entry_color_bg']         = 'Фон';
$_['entry_color_text']       = 'Текст';

// Tab: Duplicates
$_['entry_duplicates_enabled'] = 'Включить детект дубликатов';
$_['entry_duplicate_window']   = 'Временное окно';
$_['help_duplicate_window']    = 'Заказы того же клиента в этом окне (от даты строки) помечаются как дубликаты.';
$_['entry_duplicate_custom_value'] = 'Своё значение';
$_['entry_duplicate_custom_unit']  = 'Единица';
$_['text_unit_minutes']        = 'минут';
$_['text_unit_hours']          = 'часов';
$_['text_unit_days']           = 'дней';
$_['text_preset_1h']           = '1ч';
$_['text_preset_3h']           = '3ч';
$_['text_preset_6h']           = '6ч';
$_['text_preset_12h']          = '12ч';
$_['text_preset_24h']          = '24ч';
$_['text_preset_48h']          = '48ч';
$_['text_preset_72h']          = '72ч';
$_['text_preset_7d']           = '7 дней';
$_['text_preset_custom']       = 'Своё';
$_['entry_duplicate_min']      = 'Мин. заказов в окне для пометки';
$_['help_duplicate_min']       = 'Включая текущий. 2 = есть хотя бы один дубликат.';
$_['entry_color_dup']          = 'Бейдж дубликата';
$_['entry_duplicate_target']   = 'Открывать ссылки';
$_['entry_duplicate_max']      = 'Максимум номеров в ячейке';
$_['help_duplicate_max']       = 'Лишние сворачиваются в «+N» подсказку.';
$_['text_link_self']           = 'В том же окне';
$_['text_link_blank']          = 'В новой вкладке';

// Tab: Display
$_['entry_show_history']     = 'Колонка «История заказов»';
$_['entry_show_duplicates']  = 'Колонка «Дубликаты»';

// Buttons
$_['button_save']        = 'Сохранить';
$_['button_cancel']      = 'Отмена';

// Tooltip
$_['text_tooltip_total']     = 'Всего';
$_['text_tooltip_breakdown'] = 'По статусам';
$_['text_more']              = 'ещё';

// Report page
$_['report_menu_label']        = 'История покупателей';
$_['report_heading']           = 'История покупателей';
$_['report_column_customer']   = 'Покупатель';
$_['report_column_total']      = 'Заказов';
$_['report_column_total_amount'] = 'Сумма всего';
$_['report_column_avg']        = 'Средний чек';
$_['report_column_first']      = 'Первый заказ';
$_['report_column_last']       = 'Последний заказ';
$_['report_column_dup']        = 'Дубли';
$_['report_filter_title']      = 'Фильтр';
$_['report_filter_search']     = 'Поиск (email / телефон / имя)';
$_['report_filter_tier']       = 'Уровень';
$_['report_filter_tier_any']   = 'Все уровни';
$_['report_filter_tier_low']   = 'Начальный';
$_['report_filter_tier_mid']   = 'Средний';
$_['report_filter_tier_high']  = 'Высокий';
$_['report_filter_duplicates'] = 'Только с дубликатами';
$_['report_text_loading']      = 'Загрузка заказов...';
$_['report_text_no_results']   = 'Нет данных';
$_['report_text_has_duplicates'] = 'Есть дубликаты в окне';
$_['report_rows_order']        = '№ заказа';
$_['report_rows_status']       = 'Статус';
$_['report_rows_items']        = 'Позиций';
$_['report_rows_total']        = 'Сумма';
$_['report_rows_date']         = 'Дата';
$_['report_rows_action']       = 'Действие';
$_['button_settings']          = 'Настройки модуля';
$_['button_apply']             = 'Применить';
$_['button_clear']             = 'Сбросить';
$_['text_pagination']          = 'Показано с %d по %d из %d (страниц: %d)';
$_['datetime_format']          = 'd.m.Y H:i';

// Errors
$_['error_permission']   = 'Внимание: У вас нет прав на изменение этого модуля!';
