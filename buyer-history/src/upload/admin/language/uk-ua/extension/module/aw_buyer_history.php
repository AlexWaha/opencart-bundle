<?php

$_['heading_title']      = '<span style="color:#0937cc;">alexwaha.com</span> - <i class="fa fa-puzzle-piece"></i> Buyer History';
$_['heading_main_title'] = 'alexwaha.com - Buyer History';

$_['text_extension']     = 'Розширення';
$_['text_edit']          = 'Налаштування модуля';
$_['text_success']       = 'Налаштування успішно збережено!';
$_['text_home']          = 'Головна';
$_['text_yes']           = 'Так';
$_['text_no']            = 'Ні';
$_['text_preview']       = 'Перегляд';

$_['tab_general']        = 'Загальні';
$_['tab_thresholds']     = 'Пороги та кольори';
$_['tab_statuses']       = 'Кольори статусів';
$_['tab_import_export']  = 'Імпорт / Експорт';
$_['button_export']               = 'Експортувати';
$_['button_import']               = 'Імпортувати';
$_['text_import_export_title']    = 'Імпорт / Експорт налаштувань';
$_['text_import_export_info']     = 'Експортуйте поточні налаштування модуля у JSON-файл або імпортуйте раніше збережені.';
$_['text_loading']                = 'Завантаження...';
$_['text_export_description']     = 'Завантажити поточну конфігурацію як JSON-файл.';
$_['text_import_description']     = 'Виберіть раніше експортований JSON-файл для відновлення налаштувань.';
$_['text_import_warning']         = '<strong>Увага!</strong> Імпорт перезапише всі поточні налаштування. Дію неможливо скасувати.';
$_['text_import_success']         = 'Налаштування успішно імпортовано! Сторінку буде перезавантажено...';
$_['error_import_failed']         = 'Помилка імпорту: %s';
$_['error_import_read_file']      = 'Не вдалося прочитати завантажений файл';
$_['error_import_file']           = 'Виберіть файл для імпорту';
$_['help_status_colors'] = 'Задайте колір для кожного статусу замовлення. На сторінці «Історія покупців» статуси замовлень у розгорнутому рядку будуть забарвлені цим кольором. Порожнє значення - без кольору.';
$_['tab_duplicates']     = 'Дублікати';
$_['tab_display']        = 'Відображення';
$_['tab_support']        = 'Підтримка';

$_['column_history']     = 'Історія';
$_['column_duplicates']  = 'Дублікати';

$_['entry_status']           = 'Статус';
$_['entry_match_guests']     = 'Враховувати гостей за email + телефон';
$_['help_match_guests']      = 'Зв\'язувати замовлення гостей за нормалізованим email і телефоном.';
$_['entry_tracked_statuses'] = 'Враховувати статуси замовлень';
$_['help_tracked_statuses']  = 'Відмітьте статуси, які зараховуються в лічильник і розбивку. Невідмічені - ігноруються.';

$_['entry_threshold_mid']    = 'Поріг середнього рівня (замовлень)';
$_['entry_threshold_high']   = 'Поріг високого рівня (замовлень)';
$_['entry_color_low']        = 'Бейдж «початковий рівень»';
$_['entry_color_mid']        = 'Бейдж «середній рівень»';
$_['entry_color_high']       = 'Бейдж «високий рівень»';
$_['entry_color_bg']         = 'Фон';
$_['entry_color_text']       = 'Текст';

$_['entry_duplicates_enabled'] = 'Увімкнути детект дублікатів';
$_['entry_duplicate_window']   = 'Часове вікно';
$_['help_duplicate_window']    = 'Інші замовлення того ж клієнта в цьому вікні позначаються як дублікати.';
$_['entry_duplicate_custom_value'] = 'Своє значення';
$_['entry_duplicate_custom_unit']  = 'Одиниця';
$_['text_unit_minutes']        = 'хвилин';
$_['text_unit_hours']          = 'годин';
$_['text_unit_days']           = 'днів';
$_['text_preset_1h']           = '1г';
$_['text_preset_3h']           = '3г';
$_['text_preset_6h']           = '6г';
$_['text_preset_12h']          = '12г';
$_['text_preset_24h']          = '24г';
$_['text_preset_48h']          = '48г';
$_['text_preset_72h']          = '72г';
$_['text_preset_7d']           = '7 днів';
$_['text_preset_custom']       = 'Своє';
$_['entry_duplicate_min']      = 'Мін. замовлень у вікні для позначки';
$_['help_duplicate_min']       = 'Включаючи поточне. 2 = є хоча б один дублікат.';
$_['entry_color_dup']          = 'Бейдж дубліката';
$_['entry_duplicate_target']   = 'Відкривати посилання';
$_['entry_duplicate_max']      = 'Максимум номерів у комірці';
$_['help_duplicate_max']       = 'Зайві згортаються в «+N».';
$_['text_link_self']           = 'В тому ж вікні';
$_['text_link_blank']          = 'В новій вкладці';

$_['entry_show_history']     = 'Колонка «Історія замовлень»';
$_['entry_show_duplicates']  = 'Колонка «Дублікати»';

$_['button_save']        = 'Зберегти';
$_['button_cancel']      = 'Скасувати';

$_['text_tooltip_total']     = 'Всього';
$_['text_tooltip_breakdown'] = 'По статусах';
$_['text_more']              = 'ще';

// Report page
$_['report_menu_label']        = 'Історія покупців';
$_['report_heading']           = 'Історія покупців';
$_['report_column_customer']   = 'Покупець';
$_['report_column_total']      = 'Замовлень';
$_['report_column_total_amount'] = 'Сума всього';
$_['report_column_avg']        = 'Середній чек';
$_['report_column_first']      = 'Перше замовлення';
$_['report_column_last']       = 'Останнє замовлення';
$_['report_column_dup']        = 'Дублі';
$_['report_filter_title']      = 'Фільтр';
$_['report_filter_search']     = 'Пошук (email / телефон / ім\'я)';
$_['report_filter_tier']       = 'Рівень';
$_['report_filter_tier_any']   = 'Усі рівні';
$_['report_filter_tier_low']   = 'Початковий';
$_['report_filter_tier_mid']   = 'Середній';
$_['report_filter_tier_high']  = 'Високий';
$_['report_filter_duplicates'] = 'Тільки з дублікатами';
$_['report_text_loading']      = 'Завантаження замовлень...';
$_['report_text_no_results']   = 'Немає даних';
$_['report_text_has_duplicates'] = 'Є дублікати у вікні';
$_['report_rows_order']        = '№ замовлення';
$_['report_rows_status']       = 'Статус';
$_['report_rows_items']        = 'Позицій';
$_['report_rows_total']        = 'Сума';
$_['report_rows_date']         = 'Дата';
$_['report_rows_action']       = 'Дія';
$_['button_settings']          = 'Налаштування модуля';
$_['button_apply']             = 'Застосувати';
$_['button_clear']             = 'Скинути';
$_['text_pagination']          = 'Показано з %d по %d з %d (сторінок: %d)';
$_['datetime_format']          = 'd.m.Y H:i';

$_['error_permission']   = 'Увага: Ви не маєте прав на редагування цього модуля!';
