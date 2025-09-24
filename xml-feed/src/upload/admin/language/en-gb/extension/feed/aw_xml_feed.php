<?php

/**
 * Age Verification Module
 *
 * @author Alexander Vakhovski (AlexWaha)
 *
 * @link https://alexwaha.com
 *
 * @email support@alexwaha.com
 *
 * @license GPLv3
 */
// Heading
$_['heading_title'] = '<span style="color:#0937cc;">alexwaha.com</span> - <i class="fa fa-puzzle-piece"></i> XML Feed';
$_['heading_main_title'] = 'alexwaha.com - XML Feed';

// Tabs
$_['tab_general'] = 'XML Feed';
$_['tab_fields'] = 'Fields';
$_['tab_category'] = 'Categories';
$_['tab_manufacturer'] = 'Manufacturer';
$_['tab_attributes'] = 'Attributes';
$_['tab_options'] = 'Options';
$_['tab_tags'] = 'Variables';
$_['tab_support'] = 'Support';

// Text
$_['text_feed'] = 'Product Feeds';
$_['text_success'] = 'Data successfully saved!';
$_['text_success_delete'] = 'XML feed successfully deleted';
$_['text_edit'] = 'Edit';
$_['text_extension'] = 'Product Feeds';
$_['text_tags_info'] = '<div class="alert alert-info"><h4><i class="fa fa-info-circle"></i> XML Template Structure</h4><p>XML feeds are generated from three template parts:</p><ul><li><strong>header.twig</strong> - XML file header with general shop and category information</li><li><strong>items.twig</strong> - products section, generated for each product batch</li><li><strong>footer.twig</strong> - closing part of XML file</li></ul><p>Templates are located in: <code>catalog/view/theme/active/template/extension/feed/aw_xml_feed/layout/{template}/</code></p><p><strong>Using variables:</strong> Use variables from the table below in Twig format: <code>{{variable_name}}</code></p></div>';
$_['text_feed_generation_url'] = 'Feed generation URL';

// Entry
$_['entry_name'] = 'Name';
$_['entry_filename'] = 'Filename *.xml';
$_['entry_folder'] = 'XML folder name';
$_['entry_template'] = 'XML Template';
$_['entry_category'] = 'Categories available for export';
$_['entry_category_related'] = 'Category names in export';
$_['entry_manufacturer'] = 'Manufacturers available for export';
$_['entry_attribute'] = 'Attributes available for export';
$_['entry_attribute_warranty'] = 'Attribute - Warranty';
$_['entry_attribute_country'] = 'Attribute - Country of manufacture';
$_['entry_option'] = 'Options available for export';
$_['entry_option_size'] = 'Size option (Google Merchant)';
$_['entry_option_color'] = 'Color option (Google Merchant)';
$_['entry_currency'] = 'Export currency';
$_['entry_language'] = 'Export language';
$_['entry_image_origin'] = 'Use original images';
$_['entry_image_width'] = 'Image width';
$_['entry_image_height'] = 'Image height';
$_['entry_image_quantity'] = 'Number of images';
$_['entry_image_count'] = 'Number of images';
$_['entry_batch_size'] = 'Generation batch size';
$_['entry_access_key'] = 'Access key';
$_['entry_status'] = 'Status';
$_['entry_warranty_text'] = 'Static text - Warranty';
$_['entry_shop_name'] = 'Shop name';
$_['entry_company_name'] = 'Company name';
$_['entry_shop_description'] = 'Shop description';
$_['entry_shop_country'] = 'Shop country';
$_['entry_show_delivery_info'] = 'Show delivery information';
$_['entry_delivery_service'] = 'Delivery service';
$_['entry_delivery_days'] = 'Delivery days';
$_['entry_delivery_price'] = 'Delivery price';

// Columns
$_['column_name'] = 'Name';
$_['column_url'] = 'Price list link';
$_['column_filename'] = 'Filename';
$_['column_template'] = 'Template';
$_['column_status'] = 'Status';
$_['column_action'] = 'Action';
$_['column_tag'] = 'Variable';
$_['column_descrition'] = 'Description';

// Tags
$_['tag_date'] = 'Export date in format [YYYY–MM–DD hh:mm:ss]';
$_['tag_url'] = 'URL where export is available';
$_['tag_currency'] = 'Price list currency';
$_['tag_categories'] = 'Export categories array';
$_['tag_category'] = 'Export category array';
$_['tag_category_id'] = 'Category identifier';
$_['tag_category_parent'] = 'Parent category identifier';
$_['tag_category_name'] = 'Category name';
$_['tag_pages'] = 'Sections array';
$_['tag_page'] = 'Section array';
$_['tag_products'] = 'Export products array';
$_['tag_product'] = 'Export product array';
$_['tag_product_id'] = 'Product identifier';
$_['tag_product_url'] = 'Product URL';
$_['tag_product_price'] = 'Product price';
$_['tag_product_special'] = 'Product special price';
$_['tag_product_category_id'] = 'Product category identifier';
$_['tag_product_h1'] = 'Product H1 heading';
$_['tag_product_name'] = 'Product name';
$_['tag_product_description'] = 'Product description';
$_['tag_product_model'] = 'Product model';
$_['tag_product_quantity'] = 'Product quantity';
$_['tag_product_available'] = 'Product availability (Qty > 1 = true, < 1 = false)';
$_['tag_product_vendor'] = 'Product vendor';
$_['tag_product_vendor_code'] = 'Vendor code (SKU)';
$_['tag_product_images'] = 'Product images links array';
$_['tag_product_image'] = 'Product main image';
$_['tag_product_shipping'] = 'Delivery time (Attribute or text)';
$_['tag_product_warranty'] = 'Warranty period (Attribute or text)';
$_['tag_product_options'] = 'Product options array';
$_['tag_option'] = 'Product option array';
$_['tag_option_id'] = 'Option value ID';
$_['tag_option_group'] = 'Option name';
$_['tag_option_name'] = 'Option value (list)';
$_['tag_option_value'] = 'Option value (date or text field)';
$_['tag_option_price'] = 'Option price';
$_['tag_option_weight'] = 'Option weight';
$_['tag_option_quantity'] = 'Option quantity';
$_['tag_offer_color'] = 'Size option';
$_['tag_offer_size'] = 'Color option';
$_['tag_product_attributes'] = 'Product attributes array';
$_['tag_attribute'] = 'Product attribute array';
$_['tag_attributes_group'] = 'Attribute group name';
$_['tag_attributes_name'] = 'Attribute name';
$_['tag_attributes_value'] = 'Attribute value';

// Template
$_['template_google'] = 'Google';
$_['template_facebook'] = 'Facebook';
$_['template_yml'] = 'YML';
$_['template_prom'] = 'Prom.ua';
$_['template_hotline'] = 'Hotline.ua';

// Help
$_['help_folder'] = 'Folder name for storing XML files. Use only Latin letters and numbers';
$_['help_batch_size'] = 'Number of products processed in one batch during XML generation. Recommended: 250-500';
$_['help_access_key'] = 'Key for secure access to XML feeds. Minimum 8 characters';
$_['help_shop_name'] = 'Official name of your store for export';
$_['help_company_name'] = 'Legal name of the company or individual entrepreneur';
$_['help_shop_description'] = 'Brief description of store activities';
$_['help_shop_country'] = 'Select the country where your store is registered';
$_['help_show_delivery_info'] = 'Enable/disable delivery blocks output in all XML feeds';
$_['help_delivery_service'] = 'Name of delivery service or delivery method';
$_['help_delivery_days'] = 'Number of delivery days (1-365)';
$_['help_delivery_price'] = 'Delivery price, 0 for free delivery';
$_['help_name'] = 'Internal feed name for identification';
$_['help_filename'] = 'XML file name without extension. Use Latin letters';
$_['help_currency'] = 'Currency exchange rates are used from store settings';
$_['help_image_origin'] = 'Use original images';
$_['help_image_quantity'] = 'Number of additional product images in export (Recommended, no more than 8)';
$_['help_image_count'] = 'Number of additional product images in export (Recommended, no more than 8)';
$_['help_limit'] = 'Number of products per generation batch (more products = longer execution) (Recommended up to 1000)';
$_['help_attribute_warranty'] = 'Product attribute specifying warranty period';
$_['help_category_related'] = 'Category name for mapping (Google Merchant, etc.)';
$_['help_category_related_id'] = 'Category ID for mapping (Google Merchant, etc.)';
$_['help_warranty_text'] = 'Specify warranty period, e.g.: 24 months, 12, 3 months from store, etc.';
$_['help_feed_generation'] = 'Copy this URL to manually trigger XML feed generation. For automation via cron use: <code>php /path/to/your/site/cli/aw_xml_feed.php</code>';

// Error
$_['error_permission'] = 'You do not have permission to edit this module.';
$_['error_warning'] = 'Check that all fields are filled correctly.';
$_['error_folder'] = 'Specify folder name for storing XML (3 to 64 characters).';
$_['error_batch_size'] = 'Generation batch size cannot exceed 1000 products.';
$_['error_access_key'] = 'Access key cannot be empty and must contain at least 8 characters!';
$_['error_image_count'] = 'One product can have no more than 8 images.';
$_['error_name'] = 'Specify feed name.';
$_['error_filename'] = 'Specify feed filename (3 to 128 characters).';
$_['error_shop_name'] = 'Specify shop name (3 to 255 characters).';
$_['error_company_name'] = 'Specify company name (3 to 255 characters).';
$_['error_shop_description'] = 'Specify shop description (10 to 255 characters).';
$_['error_warranty_text'] = 'Specify warranty text (3 to 255 characters).';
$_['error_delivery_service'] = 'Specify delivery service (3 to 255 characters).';
$_['error_delivery_days'] = 'Delivery days must be between 1 and 365.';
$_['error_delivery_price'] = 'Delivery price must be a positive number.';

// Delivery text templates
$_['text_delivery_info'] = 'Delivery %s days';
$_['text_delivery_info_with_price'] = 'Delivery %s days, cost %s';
