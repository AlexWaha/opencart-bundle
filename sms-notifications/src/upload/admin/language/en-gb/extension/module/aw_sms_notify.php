<?php

/**
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */

// Heading
$_['heading_title'] = '<span style="color:#0937cc;">alexwaha.com</span> - <i class="fa fa-puzzle-piece"></i> SMS notifications';
$_['heading_main_title'] = 'alexwaha.com - SMS notifications';

// Lang
$_['lang'] = 'en-GB';

// Text
$_['text_extension'] = 'Extensions';
$_['text_success'] = 'Module settings updated!';
$_['text_success_sms'] = 'SMS successfully sent!';
$_['text_success_log'] = 'Log cleared!';
$_['text_sms_form'] = 'Custom SMS message';
$_['text_edit'] = 'Edit module';
$_['text_length'] = 'Message length <b class="lenght">0</b> characters';
$_['text_phone_placeholder'] = '+1(012)1234567';

// Tabs
$_['tab_sms'] = 'Custom SMS';
$_['tab_tags'] = 'Variables';
$_['tab_template'] = 'SMS templates';
$_['tab_viber_setting'] = 'Viber Settings';
$_['tab_viber_template'] = 'Notification templates';
$_['tab_template_customer'] = 'Customer SMS templates';
$_['tab_setting'] = 'Notification settings';
$_['tab_gate_setting'] = 'Gateway settings';
$_['tab_log'] = 'Gateway logs';
$_['tab_import_export'] = 'Import/Export';
$_['tab_support'] = 'Support';

// Entry
$_['entry_template'] = 'Message template </br>';
$_['entry_sms_template'] = 'SMS templates for order viewing';
$_['entry_custom_client_sms_template'] = 'Custom SMS preset';
$_['entry_order_status'] = 'SMS for statuses:';
$_['entry_admin_alert'] = 'Send SMS to admin';
$_['entry_client_alert'] = 'Send SMS to customer';
$_['entry_order_alert'] = 'SMS on order status change';
$_['entry_register_alert'] = 'SMS to customer on registration';
$_['entry_reviews'] = 'SMS for new reviews';
$_['entry_customer_group'] = 'SMS for customer groups';
$_['entry_payment_alert'] = 'SMS for payment methods';
$_['entry_force'] = 'Force SMS sending';
$_['entry_translit'] = 'Transliterate SMS text';

$_['entry_sms_gatename'] = 'SMS gateway:';
$_['entry_sms_from'] = 'Sender';
$_['entry_sms_to'] = 'Administrator phone number';
$_['entry_sms_copy'] = 'Additional numbers';
$_['entry_sms_gate_username'] = 'SMS gateway login (or api_id)';
$_['entry_sms_gate_password'] = 'SMS gateway password';
$_['entry_sms_log'] = 'Enable logs';
$_['entry_sms_notify_log_filename'] = '.log file name';

$_['entry_client_phone'] = 'Phone number:';
$_['entry_client_sms'] = 'Message text:';
$_['entry_admin_template'] = 'SMS template for admin (new order)';
$_['entry_client_template'] = 'SMS template for customer (new order)';
$_['entry_reviews_template'] = 'Message template for new reviews';
$_['entry_order_status_template'] = 'Message template for order statuses';
$_['entry_payment_template'] = 'Message template for payment methods';
$_['entry_register_template'] = 'Message template for registration';

$_['entry_viber_sender'] = 'Viber sender name:';
$_['entry_viber_alert'] = 'Send Viber messages:';
$_['entry_viber_ttl'] = 'Viber message lifetime (sec = 3600):';
$_['entry_viber_caption'] = 'Viber message button text:';
$_['entry_viber_image'] = 'Image in Viber message:';
$_['entry_viber_url'] = 'Button link:';
$_['entry_width'] = 'Image width:';
$_['entry_height'] = 'Image height:';

// Order
$_['entry_sendsms'] = 'Send SMS on status change:';
$_['entry_sms_order_status'] = 'Order status';
$_['entry_sms_message'] = 'SMS message';

// Button
$_['button_send'] = 'Send SMS';

$_['help_sms_payment'] = 'If a template is set and SMS sending is enabled for <b>payment methods</b>, the New Order template for customers will be ignored!';
$_['help_sms_from'] = 'Phone number or alphanumeric sender';
$_['help_sms_copy'] = 'Enter numbers separated by commas (no spaces) in international format +1(operator code) 1234567';
$_['help_phone'] = 'Enter phone in international format +1(operator code) 1234567';
$_['help_force'] = 'Force SMS sending for automatic mailings';
$_['help_translit'] = 'Text transliteration, was - <b>Ваш заказ оформлен</b>, became - <b>Vash zakaz oformlen</b>';
$_['help_order_status'] = 'Send SMS on order status change';
$_['help_customer_group'] = 'Automatic SMS sending for selected customer groups. If none selected, SMS will be sent to all customers';
$_['help_payment_alert'] = 'Automatic SMS sending for selected payment methods after order placement';
$_['help_product'] = 'Use carefully, avoid mistakes! Example: {% for product in products%} Product:{{product.name}} Price:{{product.price}}{% endfor %}';
$_['help_reviews'] = 'Allowed tags {{product.name}}, {{product.model}}, {{product.sku}}, {{product.date}}<br /> <b>Product name is shortened to 50 characters</b>';
$_['help_register_template'] = 'Allowed tags: <br/><b>{{register.firstname}} - First Name</b>, <br/><b>{{register.lastname}} - Last Name</b>, <br/><b>{{register.email}} - E-mail</b>, <br/><b>{{register.phone}} - Phone</b>, <br/><b>{{register.password}} - Password</b><br />';

// Tags
$_['entry_tags'] = 'Variables list';
$_['entry_tag_valiable'] = 'Variable';
$_['entry_tag_description'] = 'Description';
$_['tag_date'] = 'Date';
$_['tag_current_date'] = 'Current date';
$_['tag_time'] = 'Time';
$_['tag_store'] = 'Store name';
$_['tag_url'] = 'Store link';
$_['tag_order_id'] = 'Order number';
$_['tag_order_total'] = 'Order total';
$_['tag_order_total_noship'] = 'Order total without shipping';
$_['tag_order_phone'] = 'Customer phone';
$_['tag_order_comment'] = 'Comment';
$_['tag_order_status'] = 'Order status';
$_['tag_payment_method'] = 'Payment method';
$_['tag_payment_city'] = 'City (payment)';
$_['tag_payment_address'] = 'Address (payment)';
$_['tag_shipping_cost'] = 'Shipping cost';
$_['tag_shipping_method'] = 'Shipping method';
$_['tag_shipping_city'] = 'City (shipping)';
$_['tag_shipping_address'] = 'Address (shipping)';
$_['tag_product_total'] = 'Total products';
$_['tag_products'] = 'Products array';
$_['tag_product_name'] = 'Product name';
$_['tag_product_model'] = 'Product model';
$_['tag_product_sku'] = 'Product SKU';
$_['tag_product_price'] = 'Product price';
$_['tag_product_quantity'] = 'Product quantity';
$_['tag_firstname'] = 'Customer first name';
$_['tag_lastname'] = 'Customer last name';
$_['tag_track_no'] = 'Order tracking number (if exists)';

// Error
$_['error_permission'] = 'You do not have permission to manage this module!';
$_['error_sms_setting'] = 'Error: Please configure SMS gateway settings first!';
$_['error_sms'] = 'Error: SMS was not sent!';
$_['error_warning'] = 'Warning: Please carefully check the form for errors!';
$_['error_log_size'] = 'Warning: Log file %s takes up %s!';
$_['error_log_file'] = 'Error: Log file does not exist!';

$_['error_log_filename'] = 'Error: Log file name not specified!';
$_['error_gatename'] = 'Error: Gateway not selected!';
$_['error_from'] = 'Error: Sender alpha-name not specified!';
$_['error_username'] = 'Error: SMS gateway login (api_id) not specified!';
$_['error_admin_template'] = 'Error: Sending SMS to admin on order is enabled, but SMS template not specified!';
$_['error_reviews_template'] = 'Error: Sending SMS to admin on reviews is enabled, but SMS template not specified!';
$_['error_client_template'] = 'Error: Sending SMS to customer on order is enabled, but SMS template not specified!';
$_['error_register_template'] = 'Error: Sending SMS to customer on registration is enabled, but SMS template not specified!';
$_['error_viber_sender'] = 'Error: Viber sender name not specified!';
$_['error_client_viber_template'] = 'Error: Sending Viber message to customer on order is enabled, but SMS template not specified!';

// Import/Export
$_['text_import_export_title'] = 'Module Settings Import and Export';
$_['text_import_export_info'] = 'Here you can export the current module settings to a JSON file or import settings from a previously saved file.';
$_['text_export_description'] = 'Click the "Export" button to download the current module settings in JSON format.';
$_['text_import_description'] = 'Select a JSON file with settings and click "Import" to load the settings.';
$_['text_import_warning'] = '<strong>Warning!</strong> Importing settings will replace all current module settings. It is recommended to export current settings before importing.';
$_['text_import_success'] = 'Settings successfully imported!';
$_['text_export_success'] = 'Settings successfully exported!';
$_['error_import_file'] = 'Please select a file to import!';
$_['error_import_invalid'] = 'Invalid file format. JSON file with module settings expected.';
$_['error_import_failed'] = 'Error importing settings: %s';
$_['error_export_failed'] = 'Error exporting settings: %s';
$_['error_import_read_file'] = 'Could not read uploaded file';
$_['button_export'] = 'Export Settings';
$_['button_import'] = 'Import Settings';
