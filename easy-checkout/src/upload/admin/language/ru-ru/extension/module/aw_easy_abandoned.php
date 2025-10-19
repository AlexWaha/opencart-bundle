<?php

/*
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */

// Heading
$_['heading_title'] = '<span style="color:#0937cc;">alexwaha.com</span> - <i class="fa fa-puzzle-piece"></i> Брошенные Заказы';
$_['heading_main_title'] = 'alexwaha.com - Брошенные Заказы';
$_['heading_title_setting'] = 'Настройки';

// Text
$_['text_extension'] = 'Расширения';
$_['text_list'] = 'Список брошенных заказов';
$_['text_modal_title'] = 'Информация о брошенном заказе';
$_['text_model'] = 'Код товар: ';
$_['text_customer_info'] = 'Данные покупателя';
$_['text_products'] = 'Товары';
$_['text_orders'] = 'Заказы с такой почтой или номером телефона';
$_['text_qty'] = 'шт.';
$_['text_send_message'] = 'Вы отправили сообщение ';
$_['text_loading'] = 'Отправляем...';
$_['text_widget_title'] = 'Брошенные Заказы';
$_['text_column_left_abandoned_order'] = 'Брошенные Заказы <span style="position:absolute; right:14px; margin-top:2px;" class="label label-danger">%s</span>';

// Entry
$_['entry_customer'] = 'Покупатель';
$_['entry_status'] = 'Статус';
$_['entry_created_at'] = 'Дата добавления';
$_['entry_email_subject'] = 'Тема письма';
$_['entry_email_template'] = 'Шаблон письма';
$_['entry_sms_template'] = 'Шаблон СМС';

// Tabs
$_['tab_general'] = 'Общие';
$_['tab_email'] = 'Email';
$_['tab_sms'] = 'СМС';

// Column
$_['column_abandoned_id'] = '№';
$_['column_customer'] = 'Покупатель';
$_['column_email'] = 'Email';
$_['column_telephone'] = 'Телефон';
$_['column_created_at'] = 'Дата добавления';
$_['column_action'] = 'Действие';
$_['column_product_name'] = 'Товар';
$_['column_product_quantity'] = 'Количество';
$_['column_product_price'] = 'Цена';
$_['column_total'] = 'Итого';

// Button
$_['button_setting'] = 'Настройки';
$_['button_send_email'] = 'Отправить сообщение на почту';
$_['button_send_sms'] = 'Отправить СМС';

// Help
$_['help_status_informer'] = 'Включает или выключает информер в шапке сайта';
$_['help_status_email'] = 'Включает или выключает Возможность отправлять сообщения покупателям на почту';
$_['help_email_subject'] = 'Доступные переменные: </br> [firstname], [lastname], [created_at]';
$_['help_email_template'] = 'Доступные переменные: </br> [firstname], [lastname], [products], [email], [telephone], [created_at]';
$_['help_status_sms'] = 'Включает или выключает возможность отправлять СМС покупателям';
$_['help_sms_template'] = 'Доступные переменные: </br> [firstname], [lastname], [products], [email], [telephone], [created_at]';

// Success
$_['text_success'] = 'Вы успешно удалили брошенный заказ!';
$_['text_success_save_setting'] = 'Вы успешно сохранили настройки';
$_['text_success_send_email_message'] = 'Вы успешно отправили сообщение на почту!';
$_['text_success_send_sms_message'] = 'Вы успешно отправили СМС!';
$_['text_send_sms_message'] = 'Вы отправили СМС ';

// Error
$_['error_permission'] = 'Внимание: У вас нет прав для изменения брошенных заказов!';
$_['text_error_email'] = 'Некорректный адрес электронной почты';
$_['text_error_email_subject'] = 'Вы не заполнили тему письма!';
$_['text_error_email_template'] = 'Вы не заполнили шаблон письма!';
$_['text_error_email_already_sent'] = 'Письмо по заказу №%s уже было отправлено!';
$_['text_error_telephone'] = 'Некорректный номер телефона';
$_['text_error_sms_template'] = 'Вы не заполнили шаблон СМС!';
$_['text_error_sms_already_sent'] = 'СМС по заказу №%s уже было отправлено!';
$_['text_error_sms_module_not_installed'] = 'Модуль SMS уведомлений не установлен!';
