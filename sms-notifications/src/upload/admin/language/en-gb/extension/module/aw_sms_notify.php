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
$_['heading_title'] = '<span style="color:#0937cc;">alexwaha.com</span> - <i class="fa fa-puzzle-piece"></i> SMS notifications';
$_['heading_main_title'] = 'alexwaha.com - SMS notifications';

// Lang
$_['lang'] = 'en-GB';

// Text
$_['text_extension'] = 'Extensions';
$_['text_success'] = 'Success: You have modified module!';
$_['text_success_sms'] = 'Sussess: Sms was send!';
$_['text_success_log'] = 'Sussess: Log cleared!';
$_['text_sms_form'] = 'Manual sms message';
$_['text_edit'] = 'Edit';
$_['text_length'] = 'Message lenght <b class="lenght">0</b> symblols';
$_['text_phone_placeholder'] = '+1(012)1234567';

// Tabs
$_['tab_sms'] = 'Manual sms';
$_['tab_tags'] = 'Vars';
$_['tab_template'] = 'Sms template';
$_['tab_viber_setting'] = 'Viber Settings';
$_['tab_viber_template'] = 'Viber template';
$_['tab_template_customer'] = 'Customer sms template';
$_['tab_setting'] = 'Notify settings';
$_['tab_gate_setting'] = 'Gate settings';
$_['tab_log'] = 'Sms logs';
$_['tab_support'] = 'Support';

// Entry
$_['entry_template'] = 'Message template </br>';
$_['entry_sms_template'] = 'Sms muckups for order info page';
$_['entry_order_status'] = 'Order status sms:';
$_['entry_admin_alert'] = 'Sms for admin';
$_['entry_client_alert'] = 'Sms for customer';
$_['entry_register_alert'] = 'Send SMS to the customer upon registration';
$_['entry_order_alert'] = 'Sms on order status change';
$_['entry_reviews'] = 'Sms for new order';
$_['entry_customer_group'] = 'Sms for customer groups';
$_['entry_payment_alert'] = 'Sms for payment methods';
$_['entry_force'] = 'Force sms sending';
$_['entry_translit'] = 'Translit cyr-to-lat sms text';

$_['entry_sms_gatename'] = 'SMS gate:';
$_['entry_sms_from'] = 'Sender';
$_['entry_sms_to'] = 'Admin phone number';
$_['entry_sms_copy'] = 'Additional admin phones';
$_['entry_sms_gate_username'] = 'Sms gate Login (or API_ID)';
$_['entry_sms_gate_password'] = 'Sms gate Password';
$_['entry_sms_notify_log_filename'] = 'Log filename';
$_['entry_sms_log'] = 'Enable log';

$_['entry_client_phone'] = 'Phone number:';
$_['entry_client_sms'] = 'Message text:';
$_['entry_admin_template'] = 'Admin sms template (New order)';
$_['entry_client_template'] = 'Customer sms template (New order)';
$_['entry_reviews_template'] = 'New review sms template';
$_['entry_order_status_template'] = 'Sms template for order statuses';
$_['entry_payment_template'] = 'Sms template for payment methods';
$_['entry_register_template'] = 'Customer sms for registration';

$_['entry_viber_sender'] = 'Viber Sender:';
$_['entry_viber_alert'] = 'Send Viber message:';
$_['entry_viber_ttl'] = 'Viber message lifetime (sec = 3600):';
$_['entry_viber_caption'] = 'Viber button text:';
$_['entry_viber_image'] = 'Image in Viber message:';
$_['entry_viber_url'] = 'Button link:';
$_['entry_width'] = 'Image width:';
$_['entry_height'] = 'Image height:';

// Order
$_['entry_sendsms'] = 'Send sms on order status change:';
$_['entry_sms_order_status'] = 'Order status';
$_['entry_sms_message'] = 'Sms message';

// Button
$_['button_send'] = 'Send sms';

$_['help_sms_payment'] = 'If a template is set and SMS sending is enabled for <b> payment methods </b>, then the New order template for the customers will be ignored!';
$_['help_sms_from'] = 'Sms sender alphaname';
$_['help_sms_copy'] = 'Enter phone numbers separated by commas (without spaces) in the international format +1 (code) 1234567';
$_['help_phone'] = 'Enter phone number (without spaces) in the international format +1 (code) 1234567';
$_['help_force'] = 'Force SMS for automatic mailings';
$_['help_translit'] = 'Translit text message cyr-to-lat, before - <b>Ваш заказ оформлен</b>, after - <b>Vash zakaz oformlen</b>';
$_['help_order_status'] = 'Send SMS on order status change';
$_['help_customer_group'] = 'Automatic SMS sending for selected groups of customers. If not marked, SMS will be sent to all customers';
$_['help_payment_alert'] = 'Automatic SMS sending for selected payment methods, when order will be completed';
$_['help_product'] = 'Use carefully, don\'t make mistakes! For example: {% for product in products%} Product:{{product.name}} Price:{{product.price}}{% endfor %}';
$_['help_reviews'] = 'Allowed tags {{product.name}}, {{product.model}}, {{product.sku}}, {{product.date}}<br /> <b>Product name is reduced to 50 characters</b>';
$_['help_register_template'] = 'Allowed tags: <br/><b>{{register.firstname}} - First Name</b>, <br/><b>{{register.lastname}} - Last Name</b>, <br/><b>{{register.email}} - E-mail</b>, <br/><b>{{register.phone}} - Phone</b>, <br/><b>{{register.password}} - Password</b><br />';

// Tags
$_['entry_tags'] = 'Vars list';
$_['entry_tag_valiable'] = 'Variable';
$_['entry_tag_description'] = 'Description';
$_['tag_date'] = 'Date';
$_['tag_current_date'] = 'Current date';
$_['tag_time'] = 'Time';
$_['tag_store'] = 'Store name';
$_['tag_url'] = 'Store url';
$_['tag_order_id'] = 'Order number';
$_['tag_order_total'] = 'Order total';
$_['tag_order_total_noship'] = 'Order total without shipping cost';
$_['tag_order_phone'] = 'Customer phone';
$_['tag_order_comment'] = 'Comment';
$_['tag_order_status'] = 'Order status';
$_['tag_payment_method'] = 'Payment method';
$_['tag_payment_city'] = 'Payment city';
$_['tag_payment_address'] = 'Payment address';
$_['tag_shipping_cost'] = 'Shipping cost';
$_['tag_shipping_method'] = 'Shipping method';
$_['tag_shipping_city'] = 'Shipping city';
$_['tag_shipping_address'] = 'Shipping address';
$_['tag_product_total'] = 'Product count';
$_['tag_products'] = 'Products array';
$_['tag_product_name'] = 'Product name';
$_['tag_product_model'] = 'Product model';
$_['tag_product_sku'] = 'Product sku';
$_['tag_product_price'] = 'Product price';
$_['tag_product_quantity'] = 'Product quantity';
$_['tag_firstname'] = 'Customer Firstname';
$_['tag_lastname'] = 'Customer Lastname';
$_['tag_track_no'] = 'Tracking number (if applicable)';

// Error
$_['error_permission'] = 'Warning: You do not have permission to modify this module!';
$_['error_sms_setting'] = 'Error: Please save sms gate settings first!';
$_['error_sms'] = 'Error: Sms not send!';
$_['error_warning'] = 'Attention: Log file %s size %s!';
$_['error_log_file'] = 'Warning: Log file is not exists!';
