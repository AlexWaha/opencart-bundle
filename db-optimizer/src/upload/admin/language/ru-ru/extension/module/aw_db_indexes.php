<?php

/*
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */

// Heading
$_['heading_title'] = '<span style="color:#0937cc;">alexwaha.com</span> - <i class="fa fa-puzzle-piece"></i> DB Optimizer';
$_['heading_main_title'] = 'alexwaha.com - DB Optimizer';

// Breadcrumbs
$_['text_home'] = 'Главная';
$_['text_extension'] = 'Расширения';
$_['text_edit'] = 'Настройки модуля';

// Tabs
$_['tab_general'] = 'Основные';
$_['tab_analysis'] = 'Анализ';
$_['tab_applied'] = 'Применённые';
$_['tab_support'] = 'Поддержка';

// General
$_['entry_status'] = 'Статус';
$_['entry_min_rows'] = 'Минимум строк в таблице';
$_['entry_max_indexes'] = 'Макс. новых индексов на таблицу';
$_['entry_scope'] = 'Охват таблиц';
$_['text_scope_all'] = 'Все таблицы';
$_['text_scope_standard'] = 'Таблицы OpenCart (по префиксу)';
$_['text_scope_custom'] = 'Сторонние таблицы (другой префикс)';
$_['text_min_rows_help'] = 'Таблицы с меньшим числом строк эвристика пропускает (на маленьких таблицах индексы бесполезны). Известные индексы OpenCart предлагаются всегда.';
$_['text_max_indexes_help'] = 'Предохранитель: не предлагать больше указанного числа новых индексов для одной таблицы.';
$_['text_scope_help'] = 'Какие таблицы сканирует анализатор.';

// Analysis
$_['text_analyze_intro'] = 'Сканирование базы на отсутствующие индексы и проблемы оптимизации. Ничего не меняется, пока вы не примените исправление.';
$_['text_analyzing'] = 'Анализ базы данных...';
$_['text_summary'] = 'Просканировано таблиц: %d. Рекомендаций индексов: %d, без первичного ключа: %d, MyISAM: %d, фрагментировано: %d.';
$_['text_recommendations'] = 'Рекомендации по индексам';
$_['text_no_recommendations'] = 'Отсутствующих индексов не найдено. База хорошо проиндексирована.';
$_['text_diagnostics'] = 'Диагностика';
$_['text_col_table'] = 'Таблица';
$_['text_col_column'] = 'Колонка';
$_['text_col_index'] = 'Имя индекса';
$_['text_col_rows'] = 'Строк';
$_['text_col_confidence'] = 'Уверенность';
$_['text_col_current_indexes'] = 'Тек. индексов';
$_['text_col_sql'] = 'SQL';
$_['text_col_action'] = 'Действие';
$_['text_confidence_curated'] = 'Известный OpenCart';
$_['text_confidence_recommended'] = 'Рекомендуется';
$_['text_no_pk'] = 'Таблицы без PRIMARY KEY';
$_['text_no_pk_help'] = 'Отсутствие первичного ключа вредит производительности и репликации. Добавьте его вручную (авто-исправление небезопасно).';
$_['text_myisam'] = 'Таблицы MyISAM';
$_['text_myisam_help'] = 'InnoDB даёт построчные блокировки и восстановление после сбоев. Конверсия перестраивает таблицу и может занять время на больших объёмах.';
$_['text_fragmented'] = 'Фрагментированные таблицы';
$_['text_fragmented_help'] = 'OPTIMIZE TABLE освобождает место и перестраивает индексы. На время операции таблица блокируется.';
$_['text_none'] = 'Не найдено.';

// Applied
$_['text_applied_intro'] = 'Индексы, созданные модулем (имя = префикс БД + idx_, напр. oc_idx_). Их можно безопасно удалить в любой момент.';
$_['text_no_applied'] = 'Индексы модуля ещё не применялись.';

// Buttons
$_['button_save'] = 'Сохранить';
$_['button_cancel'] = 'Отмена';
$_['button_analyze'] = 'Анализировать базу';
$_['button_apply_selected'] = 'Применить выбранные';
$_['button_apply_all'] = 'Применить все рекомендованные';
$_['button_rollback_all'] = 'Откатить все';
$_['button_drop'] = 'Удалить';
$_['button_convert'] = 'В InnoDB';
$_['button_convert_all'] = 'Все в InnoDB';
$_['button_optimize'] = 'Оптимизировать';
$_['button_optimize_all'] = 'Оптимизировать все';

// Confirmations
$_['text_confirm_convert'] = 'Конвертировать таблицу в InnoDB? Таблица перестраивается, на больших данных это может занять время.';
$_['text_confirm_convert_all'] = 'Конвертировать все %d таблиц MyISAM в InnoDB? Обработка идёт по одной, на больших таблицах это может занять много времени.';
$_['text_confirm_optimize'] = 'Выполнить OPTIMIZE TABLE? На время операции таблица блокируется.';
$_['text_confirm_optimize_all'] = 'Оптимизировать все %d таблиц? Каждая таблица блокируется на время операции.';
$_['text_processing'] = 'Обработка %d / %d...';
$_['text_confirm_rollback'] = 'Удалить все индексы, созданные этим модулем?';
$_['text_confirm_drop'] = 'Удалить этот индекс?';

// Messages
$_['text_success'] = 'Настройки успешно сохранены!';
$_['text_apply_done'] = 'Исправления применены.';
$_['text_rollback_done'] = 'Откат выполнен.';

// Support
$_['text_aw_support'] = 'Нужна помощь или индивидуальный модуль OpenCart? Заходите на <a href="https://alexwaha.com" target="_blank">alexwaha.com</a> или пишите в <a href="https://t.me/alexwaha_dev" target="_blank">Telegram</a>.';

// Errors
$_['error_permission'] = 'Внимание: у вас нет прав на изменение этого модуля!';
$_['error_no_actions'] = 'Не выбрано ни одного действия.';
$_['error_unknown_action'] = 'Неизвестный тип действия.';
