<?php

/*
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */

// Heading
$_['heading_title'] = '<span style="color:#0937cc;">alexwaha.com</span> - <i class="fa fa-puzzle-piece"></i> E-commerce Tracking (GA4)';
$_['heading_main_title'] = 'alexwaha.com - E-commerce Tracking (GA4)';

// Tab
$_['tab_general'] = 'General';
$_['tab_pages'] = 'Pages';
$_['tab_modules'] = 'Modules';
$_['tab_checkout'] = 'Checkout';
$_['tab_events'] = 'Events';
$_['tab_advanced'] = 'Advanced';
$_['tab_support'] = 'Support';
$_['tab_ga4_reference'] = 'GA4 Reference';

// Text
$_['text_extension'] = 'Extensions';
$_['text_module'] = 'Modules';
$_['text_success'] = 'Settings successfully saved!';
$_['text_edit'] = 'Module Settings';
$_['text_info'] = 'Information';
$_['text_yes'] = 'Yes';
$_['text_no'] = 'No';

// General Tab
$_['entry_status'] = 'Status';
$_['entry_tracking_code'] = 'GTM/gtag.js Code (Head)';
$_['entry_tracking_code_help'] = 'Paste your Google Tag Manager or gtag.js tracking code here. This code will be inserted in the &lt;head&gt; section of your website.';
$_['entry_tracking_code_body'] = 'GTM Code (Body)';
$_['entry_tracking_code_body_help'] = 'Paste your Google Tag Manager noscript code here. This code will be inserted immediately after the opening &lt;body&gt; tag.';
$_['entry_debug_mode'] = 'Debug Mode';
$_['entry_debug_mode_help'] = 'When enabled, all dataLayer events will be logged to the browser console for debugging purposes.';

// Pages Tab
$_['text_pages_description'] = 'Enable tracking for different page types. The <code class="badge-code">view_item_list</code> event will be sent when users visit these pages.';
$_['entry_track_category'] = 'Category Pages';
$_['entry_track_category_help'] = 'Track <code class="badge-code">view_item_list</code> event on category pages';
$_['entry_track_search'] = 'Search Results';
$_['entry_track_search_help'] = 'Track <code class="badge-code">view_item_list</code> event on search results pages';
$_['entry_track_manufacturer'] = 'Manufacturer Pages';
$_['entry_track_manufacturer_help'] = 'Track <code class="badge-code">view_item_list</code> event on manufacturer/brand pages';
$_['entry_track_special'] = 'Special/Sale Pages';
$_['entry_track_special_help'] = 'Track <code class="badge-code">view_item_list</code> event on special offers pages';
$_['entry_track_product'] = 'Product Pages';
$_['entry_track_product_help'] = 'Track <code class="badge-code">view_item</code> event on product detail pages';
$_['entry_track_compare'] = 'Compare Page';
$_['entry_track_compare_help'] = 'Track <code class="badge-code">view_item_list</code> event on product comparison page';

// Modules Tab
$_['text_modules_description'] = 'Enable tracking for product listing modules. The <code class="badge-code">view_item_list</code> event will be sent when these modules are displayed.';
$_['entry_track_module_latest'] = 'Latest Products Module';
$_['entry_track_module_featured'] = 'Featured Products Module';
$_['entry_track_module_bestseller'] = 'Bestseller Products Module';
$_['entry_track_module_special'] = 'Special Products Module';
$_['entry_track_module_aw_viewed'] = 'Recently Viewed Module (AW)';

// Checkout Tab
$_['text_checkout_description'] = 'Configure tracking for cart and checkout events.';
$_['entry_track_add_to_cart'] = 'Add to Cart';
$_['entry_track_add_to_cart_help'] = 'Track <code class="badge-code">add_to_cart</code> event when products are added to the shopping cart';
$_['entry_track_remove_from_cart'] = 'Remove from Cart';
$_['entry_track_remove_from_cart_help'] = 'Track <code class="badge-code">remove_from_cart</code> event when products are removed from the cart';
$_['entry_track_view_cart'] = 'View Cart';
$_['entry_track_view_cart_help'] = 'Track <code class="badge-code">view_cart</code> event when shopping cart page is viewed';
$_['entry_track_begin_checkout'] = 'Begin Checkout';
$_['entry_track_begin_checkout_help'] = 'Track <code class="badge-code">begin_checkout</code> event when checkout process starts';
$_['entry_track_shipping_info'] = 'Shipping Info';
$_['entry_track_shipping_info_help'] = 'Track <code class="badge-code">add_shipping_info</code> event when shipping method is selected';
$_['entry_track_payment_info'] = 'Payment Info';
$_['entry_track_payment_info_help'] = 'Track <code class="badge-code">add_payment_info</code> event when payment method is selected';
$_['entry_track_purchase'] = 'Purchase';
$_['entry_track_purchase_help'] = 'Track <code class="badge-code">purchase</code> event on order success page';
$_['entry_include_tax'] = 'Include Tax in Prices';
$_['entry_include_tax_help'] = 'Include tax amount in product prices sent to GA4';
$_['entry_include_shipping'] = 'Track Shipping Cost';
$_['entry_include_shipping_help'] = 'Include shipping cost in purchase event';
$_['entry_include_coupons'] = 'Track Coupons/Discounts';
$_['entry_include_coupons_help'] = 'Include coupon codes and discount information in events';

// Events Tab
$_['text_events_description'] = 'Enable additional event tracking.';
$_['entry_track_login'] = 'User Login';
$_['entry_track_login_help'] = 'Track <code class="badge-code">login</code> event when user successfully logs in';
$_['entry_track_signup'] = 'User Registration';
$_['entry_track_signup_help'] = 'Track <code class="badge-code">sign_up</code> event when new user registers';
$_['entry_track_wishlist'] = 'Add to Wishlist';
$_['entry_track_wishlist_help'] = 'Track <code class="badge-code">add_to_wishlist</code> event when products are added to wishlist';
$_['entry_track_select_item'] = 'Select Item';
$_['entry_track_select_item_help'] = 'Track <code class="badge-code">select_item</code> event when user clicks on a product in a list';
$_['entry_track_coupon'] = 'Coupon/Voucher Application';
$_['entry_track_coupon_help'] = 'Track <code class="badge-code">add_coupon</code> and <code class="badge-code">add_voucher</code> events when coupons or gift vouchers are applied';

// Advanced Tab
$_['text_advanced_description'] = 'Advanced configuration options.';
$_['entry_currency_format'] = 'Currency';
$_['entry_currency_format_help'] = 'Choose which currency to use for tracking';
$_['text_currency_session'] = 'Session Currency (visitor selected)';
$_['text_currency_config'] = 'Store Default Currency';
$_['entry_price_with_tax'] = 'Prices with Tax';
$_['entry_price_with_tax_help'] = 'Send prices including tax to GA4';
$_['entry_send_product_options'] = 'Send Product Options';
$_['entry_send_product_options_help'] = 'Include selected product options as item_variant in tracking data';
$_['entry_custom_dimensions'] = 'Custom Dimensions (JSON)';
$_['entry_custom_dimensions_help'] = 'Add custom parameters to all events in JSON format. Example: {"dimension1": "value1"}';

// GA4 Reference Tab
$_['text_ga4_reference_description'] = 'This module implements <a href="https://developers.google.com/analytics/devguides/collection/ga4/ecommerce" target="_blank">Google Analytics 4 E-commerce</a> tracking. Below is a complete reference of all supported events and when they are triggered.';
$_['text_ga4_doc_link'] = 'Official Documentation';
$_['text_ga4_doc_url'] = 'https://developers.google.com/analytics/devguides/collection/ga4/ecommerce';

$_['text_ga4_event'] = 'Event';
$_['text_ga4_trigger'] = 'When Triggered';
$_['text_ga4_description'] = 'Description';
$_['text_ga4_type'] = 'Type';
$_['text_ga4_type_server'] = 'Server';
$_['text_ga4_type_js'] = 'JavaScript';

$_['text_ga4_view_item_list'] = 'Triggered when a user views a list of products. This includes category pages, search results, manufacturer pages, special offers pages, and product modules (Featured, Latest, Bestseller, Special, Recently Viewed).';
$_['text_ga4_view_item'] = 'Triggered when a user views a product detail page. Sends product information including name, price, brand, and category.';
$_['text_ga4_select_item'] = 'Triggered when a user clicks on a product in a list to view its details. Captures which list the user came from for better funnel analysis.';
$_['text_ga4_add_to_cart'] = 'Triggered when a user adds a product to the shopping cart. Includes product details and quantity added.';
$_['text_ga4_remove_from_cart'] = 'Triggered when a user removes a product from the shopping cart. Useful for understanding cart abandonment.';
$_['text_ga4_view_cart'] = 'Triggered when a user views the shopping cart page. Sends all cart contents with prices and quantities.';
$_['text_ga4_begin_checkout'] = 'Triggered when a user starts the checkout process. Marks the beginning of the purchase funnel.';
$_['text_ga4_add_shipping_info'] = 'Triggered when a user selects a shipping method during checkout. Includes the selected shipping tier.';
$_['text_ga4_add_payment_info'] = 'Triggered when a user selects a payment method during checkout. Includes the payment type selected.';
$_['text_ga4_purchase'] = 'Triggered on the order success page. This is the most important conversion event, including transaction ID, total value, tax, shipping, and all purchased items.';
$_['text_ga4_login'] = 'Triggered when a user successfully logs into their account. Helps track returning customer behavior.';
$_['text_ga4_sign_up'] = 'Triggered when a new user completes registration. Useful for measuring customer acquisition.';
$_['text_ga4_add_to_wishlist'] = 'Triggered when a user adds a product to their wishlist. Indicates high purchase intent.';
$_['text_ga4_add_coupon'] = 'Triggered when a user successfully applies a coupon code. Tracks discount code usage.';
$_['text_ga4_add_voucher'] = 'Triggered when a user successfully applies a gift voucher. Tracks voucher redemption.';

$_['text_ga4_legend'] = 'Legend';
$_['text_ga4_legend_server'] = 'Event triggered by PHP on page load';
$_['text_ga4_legend_js'] = 'Event triggered by JavaScript on user action';

// Buttons
$_['button_save'] = 'Save';
$_['button_cancel'] = 'Cancel';

// Error
$_['error_permission'] = 'Warning: You do not have permission to modify this module!';
$_['error_warning'] = 'Warning: Please check the form for errors!';
