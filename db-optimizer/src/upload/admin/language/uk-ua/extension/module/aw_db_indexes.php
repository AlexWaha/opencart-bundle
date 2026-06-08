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
$_['text_home'] = 'Головна';
$_['text_extension'] = 'Розширення';
$_['text_edit'] = 'Налаштування модуля';

// Tabs
$_['tab_general'] = 'Загальні';
$_['tab_analysis'] = 'Аналіз';
$_['tab_applied'] = 'Застосовані';
$_['tab_support'] = 'Підтримка';

// General
$_['entry_status'] = 'Статус';
$_['entry_min_rows'] = 'Мінімум рядків у таблиці';
$_['entry_max_indexes'] = 'Макс. нових індексів на таблицю';
$_['entry_scope'] = 'Охоплення таблиць';
$_['text_scope_all'] = 'Усі таблиці';
$_['text_scope_standard'] = 'Таблиці OpenCart (за префіксом)';
$_['text_scope_custom'] = 'Сторонні таблиці (інший префікс)';
$_['text_min_rows_help'] = 'Таблиці з меншою кількістю рядків евристика пропускає (на малих таблицях індекси марні). Відомі індекси OpenCart пропонуються завжди.';
$_['text_max_indexes_help'] = 'Запобіжник: не пропонувати більше вказаної кількості нових індексів для однієї таблиці.';
$_['text_scope_help'] = 'Які таблиці сканує аналізатор.';

// Analysis
$_['text_analyze_intro'] = 'Сканування бази на відсутні індекси та проблеми оптимізації. Нічого не змінюється, доки ви не застосуєте виправлення.';
$_['text_analyzing'] = 'Аналіз бази даних...';
$_['text_summary'] = 'Проскановано таблиць: %d. Рекомендацій індексів: %d, без первинного ключа: %d, MyISAM: %d, фрагментовано: %d.';
$_['text_recommendations'] = 'Рекомендації щодо індексів';
$_['text_no_recommendations'] = 'Відсутніх індексів не знайдено. База добре проіндексована.';
$_['text_diagnostics'] = 'Діагностика';
$_['text_col_table'] = 'Таблиця';
$_['text_col_column'] = 'Колонка';
$_['text_col_index'] = 'Назва індексу';
$_['text_col_rows'] = 'Рядків';
$_['text_col_confidence'] = 'Впевненість';
$_['text_col_current_indexes'] = 'Поточних індексів';
$_['text_col_sql'] = 'SQL';
$_['text_col_action'] = 'Дія';
$_['text_confidence_curated'] = 'Відомий OpenCart';
$_['text_confidence_recommended'] = 'Рекомендовано';
$_['text_no_pk'] = 'Таблиці без PRIMARY KEY';
$_['text_no_pk_help'] = 'Відсутність первинного ключа шкодить продуктивності та реплікації. Додайте його вручну (авто-виправлення небезпечне).';
$_['text_myisam'] = 'Таблиці MyISAM';
$_['text_myisam_help'] = 'InnoDB надає порядкові блокування та відновлення після збоїв. Конверсія перебудовує таблицю і може зайняти час на великих обсягах.';
$_['text_fragmented'] = 'Фрагментовані таблиці';
$_['text_fragmented_help'] = 'OPTIMIZE TABLE звільняє місце та перебудовує індекси. На час операції таблиця блокується.';
$_['text_none'] = 'Не знайдено.';

// Applied
$_['text_applied_intro'] = 'Індекси, створені модулем (назва = префікс БД + idx_, напр. oc_idx_). Їх можна безпечно видалити будь-коли.';
$_['text_no_applied'] = 'Індекси модуля ще не застосовувалися.';

// Buttons
$_['button_save'] = 'Зберегти';
$_['button_cancel'] = 'Скасувати';
$_['button_analyze'] = 'Аналізувати базу';
$_['button_apply_selected'] = 'Застосувати вибрані';
$_['button_apply_all'] = 'Застосувати всі рекомендовані';
$_['button_rollback_all'] = 'Відкотити всі';
$_['button_drop'] = 'Видалити';
$_['button_convert'] = 'У InnoDB';
$_['button_convert_all'] = 'Усі в InnoDB';
$_['button_optimize'] = 'Оптимізувати';
$_['button_optimize_all'] = 'Оптимізувати всі';

// Confirmations
$_['text_confirm_convert'] = 'Конвертувати таблицю в InnoDB? Таблиця перебудовується, на великих даних це може зайняти час.';
$_['text_confirm_convert_all'] = 'Конвертувати всі %d таблиць MyISAM у InnoDB? Обробка йде по одній, на великих таблицях це може зайняти багато часу.';
$_['text_confirm_optimize'] = 'Виконати OPTIMIZE TABLE? На час операції таблиця блокується.';
$_['text_confirm_optimize_all'] = 'Оптимізувати всі %d таблиць? Кожна таблиця блокується на час операції.';
$_['text_processing'] = 'Обробка %d / %d...';
$_['text_confirm_rollback'] = 'Видалити всі індекси, створені цим модулем?';
$_['text_confirm_drop'] = 'Видалити цей індекс?';

// Messages
$_['text_success'] = 'Налаштування успішно збережено!';
$_['text_apply_done'] = 'Виправлення застосовано.';
$_['text_rollback_done'] = 'Відкат виконано.';

// Support
$_['text_aw_support'] = 'Потрібна допомога або індивідуальний модуль OpenCart? Завітайте на <a href="https://alexwaha.com" target="_blank">alexwaha.com</a> або пишіть у <a href="https://t.me/alexwaha_dev" target="_blank">Telegram</a>.';

// Errors
$_['error_permission'] = 'Увага: у вас немає прав на зміну цього модуля!';
$_['error_no_actions'] = 'Не вибрано жодної дії.';
$_['error_unknown_action'] = 'Невідомий тип дії.';
