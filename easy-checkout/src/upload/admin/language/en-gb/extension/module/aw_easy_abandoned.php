<?php

/*
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */

// Heading
$_['heading_title'] = '<span style="color:#0937cc;">alexwaha.com</span> - <i class="fa fa-puzzle-piece"></i> Abandoned Orders';
$_['heading_main_title'] = 'alexwaha.com - Abandoned Orders';
$_['heading_title_setting'] = 'Settings';

// Text
$_['text_extension'] = 'Extensions';
$_['text_list'] = 'Abandoned orders list';
$_['text_modal_title'] = 'Abandoned order information';
$_['text_model'] = 'Product code: ';
$_['text_customer_info'] = 'Customer data';
$_['text_products'] = 'Products';
$_['text_orders'] = 'Orders with this email or phone number';
$_['text_qty'] = 'pcs.';
$_['text_send_message'] = 'You sent a message ';
$_['text_loading'] = 'Sending...';
$_['text_widget_title'] = 'Abandoned Orders';
$_['text_column_left_abandoned_order'] = 'Abandoned Orders <span style="position:absolute; right:14px; margin-top:2px;" class="label label-danger">%s</span>';

// Entry
$_['entry_customer'] = 'Customer';
$_['entry_status'] = 'Status';
$_['entry_created_at'] = 'Date added';
$_['entry_email_subject'] = 'Email subject';
$_['entry_email_template'] = 'Email template';
$_['entry_sms_template'] = 'SMS template';

// Tabs
$_['tab_general'] = 'General';
$_['tab_email'] = 'Email';
$_['tab_sms'] = 'SMS';

// Column
$_['column_abandoned_id'] = '№';
$_['column_customer'] = 'Customer';
$_['column_email'] = 'Email';
$_['column_telephone'] = 'Phone';
$_['column_created_at'] = 'Date Added';
$_['column_action'] = 'Action';
$_['column_product_name'] = 'Product';
$_['column_product_quantity'] = 'Quantity';
$_['column_product_price'] = 'Price';
$_['column_total'] = 'Total';

// Button
$_['button_setting'] = 'Settings';
$_['button_send_email'] = 'Send email message';
$_['button_send_sms'] = 'Send SMS';

// Help
$_['help_status_informer'] = 'Enables or disables the informer in the website header';
$_['help_status_email'] = 'Enables or disables the ability to send messages to customers via email';
$_['help_email_subject'] = 'Available variables: </br> [firstname], [lastname], [created_at]';
$_['help_email_template'] = 'Available variables: </br> [firstname], [lastname], [products], [email], [telephone], [created_at]';
$_['help_status_sms'] = 'Enables or disables the ability to send SMS to customers';
$_['help_sms_template'] = 'Available variables: </br> [firstname], [lastname], [products], [email], [telephone], [created_at]';

// Success
$_['text_success'] = 'You have successfully deleted the abandoned order!';
$_['text_success_save_setting'] = 'You have successfully saved the settings';
$_['text_success_send_email_message'] = 'You have successfully sent an email!';
$_['text_success_send_sms_message'] = 'You have successfully sent an SMS!';
$_['text_send_sms_message'] = 'You sent an SMS ';

// Error
$_['error_permission'] = 'Warning: You do not have permission to modify abandoned orders!';
$_['text_error_email'] = 'Invalid email address';
$_['text_error_email_subject'] = 'You did not fill in the email subject!';
$_['text_error_email_template'] = 'You did not fill in the email template!';
$_['text_error_email_already_sent'] = 'Email for order №%s has already been sent!';
$_['text_error_telephone'] = 'Invalid phone number';
$_['text_error_sms_template'] = 'You did not fill in the SMS template!';
$_['text_error_sms_already_sent'] = 'SMS for order №%s has already been sent!';
$_['text_error_sms_module_not_installed'] = 'SMS notifications module is not installed!';
