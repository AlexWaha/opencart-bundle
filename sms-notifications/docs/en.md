# Alexwaha.com - SMS Notifications

**Module for OpenCart v2.3 - 3.x**

---

## Description

This extension contains a module and SMS gateways for popular messaging services.

The module allows sending SMS notifications to administrators and customers about various store events: new orders, order status changes, registration, new reviews.

> **Video instruction (old):** Before installing the extension, watch the [YouTube video in Russian](https://youtu.be/JPxS5-U6X20)

---

## Installation Instructions

### Requirements and Installation Steps

1. **Install Core Module**

   Make sure you have installed the module **[aw_core_oc2.3-3.x.ocmod.zip](https://github.com/AlexWaha/opencart-bundle/blob/master/Core/dist/aw_core_oc2.3_3.x.ocmod.zip)**

   > This is an auxiliary function for all my modules.

   > **Critically required**, the module will not work without it

2. **FTP Upload Settings (OpenCart 2.x only)**

   Make sure you have a module installed that disables FTP uploads for extensions in OpenCart 2.x, or that your FTP settings in the store config are correct

   > [Link to module disabling FTP upload for OpenCart v2.3 only](https://www.opencart.com/index.php?route=extension/extension/info&extension_id=18892) **(Not required for OpenCart 3.x)**

3. **Install the archive aw_sms_notify_oc2.3-3.x.ocmod.zip via the site's admin panel**

4. **Refresh the modification cache**

5. **Enable the Module**

   Go to: **Extensions → Modules** → Enable the module **alexwaha.com - SMS notifications**

   > When enabled, the module will automatically delete old version files, install Events and Permissions

### Self-Check After Installation

- Check for the module in the list of extensions
- Check for the presence in **Extensions → Events** of values: `aw_sms_notify_order_alert`, `aw_sms_notify_review_alert`, `aw_sms_notify_register_alert`
- Refresh the modification cache

---

## Configuration

### 1. Gateway Settings

Go to the module settings **Extensions → Modules → alexwaha.com - SMS notifications**

On the **"Gateway Settings"** tab, set the necessary settings to connect to the SMS gateway:

- **SMS Gateway** - Select a gateway from the list of available ones
- **Sender** - Specify the registered Sender Name (Alpha name) 11 Latin characters without spaces (registered in your messaging service personal account)
- **Administrator phone number** - Specify the phone number in international format for notifications about new orders or new product reviews, for example: +1(operator code) 1234567
- **Additional numbers** - Numbers to which copies of notifications will be sent (same as for administrator). Enter numbers separated by commas without spaces in international format
- **SMS gateway login (or API KEY)** - Login or API key to connect to the messaging service (this information is located in the personal account of the messaging service used)
- **SMS gateway password** - Password for the SMS gateway (if an API key is used to connect to the gateway, the password can probably be left empty)

### 2. Logging Settings

On the **"Gateway Logs"** tab:

- You can specify a custom name for the log file
- The logs will store information about sends, errors and other things related to the selected gateway for sending SMS

### 3. Notification Settings

On the **"Notification Settings"** tab, you need to enable only the necessary notifications:

- **Send SMS to admin** - notification to the administrator about a new store order
- **Send SMS to customer** - notification to the customer about a new order to the number specified in the order (below in the "SMS for customer groups" block, you need to specify which customer groups will receive the notification)
- **SMS to customer upon registration** - notification to the customer about registration (allows you to send login data)
- **SMS when order status changes** - notification about order status change by manager, delivery module or automatically
- **SMS for new reviews** - notification to the administrator when a new product review is received
- **Force SMS sending** - Forced sending of notifications (necessary if requests to send notifications about order status changes will be made by third-party extensions)
- **SMS text transliteration** - Converting Cyrillic to Latin (to reduce the number of SMS sent, it's better to use transliteration. In Cyrillic 1 SMS contains 70 characters, in Latin 140 characters)

> **Important:** To avoid sending errors, do not enable transliteration if you already specify text in Latin in the templates

- **SMS for customer groups** - Automatic sending of notifications only to selected customer groups. If no groups are marked, notifications will be sent to all customers
- **SMS for payment methods** - Automatic sending of notifications only for selected payment methods after order placement
- **SMS for statuses** - Automatic sending of notifications for selected order statuses when the order status changes
- **SMS templates when viewing an order** - Here you can specify frequently used texts for notifications when sending notifications manually by the manager from the form when viewing an order

### 4. Viber Notification Settings

On the **"Viber Settings - Notification Settings"** tab, if necessary, you can configure sending notifications to Viber:

- **Send Viber messages** - Enable/Disable sending to Viber
- **Viber sender name** - Specify the registered Viber Sender name or common name provided by your messaging service
- **Viber message lifetime** - Time in seconds during which the message in Viber will be delivered (if the Viber notification is not delivered within the specified time period, or there is no Viber account on the specified phone number, an SMS message will be sent)
- **Image in Viber message** - The image that will be displayed in each Viber message (you can specify the store logo. The default image height and width is 400*400px, the image must be square)
- **Button text in Viber message and Button link** - Parameters that need to be specified if you want to show a button with a link in the Viber message

### 5. Notification Templates

On the **"SMS template to customer"** and **"SMS template to administrator"** tabs, you set up SMS notification texts using text and variables from the **"Variables"** tab.

**Available variables** can be viewed on the "List of variables" tab.

### Using the {{products}} Variable

The `{{products}}` variable is not specified directly. To use it, you need to follow the TWIG templating syntax:

```twig
{% for product in products %}
Product: {{product.name}}
Model: {{product.model}}
Price: {{product.price}}
Quantity: {{product.quantity}}
{% endfor %}
```

---

## Custom SMS

On the **"Custom SMS"** tab, a service function is available for testing sending or custom SMS to a specified phone number.

**Application:**
- Testing the SMS gateway
- Sending custom SMS messages
- Checking connection to the messaging service

**Usage:**
1. Specify the phone number in international format (for example: +1234567890)
2. Enter the SMS message text
3. Click the send button

---

## List of Ready Integrations/Gateways

The module supports more than 30 SMS gateways:

1. Alphasms.ua - HTTP protocol (cURL), authorization via Login and Password or only via Login (API key). Hybrid Viber+SMS sending available
2. Bytehand.com - HTTP protocol (cURL), authorization via Login and Password
3. Devinotele.com - HTTP protocol (cURL), authorization via Login and Password
4. Epochtasms - HTTP protocol (XML+cURL), authorization via Login and Password
5. Eskiz.uz - HTTP protocol (cURL), authorization via Login(Email) and Password
6. Infosmska.ru - HTTP protocol (cURL), authorization via Login and Password
7. Intel-tele.com - HTTP protocol (cURL), authorization via Login and Password(API Key)
8. Iqsms.ru - HTTP protocol (cURL), authorization via Login and Password
9. Letsads.com - HTTP protocol (XML+cURL), authorization via Login and Password
10. Mainsms.ru - HTTP protocol (cURL), authorization via Login and Password
11. Nikita.kg - HTTP protocol (XML+cURL), authorization via Login and Password
12. Osonsms.com - HTTP protocol (cURL), authorization via Login and Password
13. Prostor-sms.ru - HTTP protocol (cURL), authorization via Login and Password
14. Rocketsms.by - HTTP protocol (cURL), authorization via Login and Password
15. Smsaero.ru - HTTP protocol (cURL), authorization via Login and Password
16. Smsassistent.by - HTTP protocol (cURL), authorization via Login and Password
17. Smscab.ru - HTTP protocol (cURL), authorization via Login and Password
18. Smsclub.mobi - HTTP protocol (cURL), authorization via Login and Password
19. Smsc.ru, Smsc.ua, Smsc.kz - HTTP protocol (cURL), authorization via Login and Password
20. Smsfeedback.ru - HTTP protocol (cURL), authorization via Login and Password
21. Sms-fly.ua - HTTP protocol (XML+cURL), authorization via Login and Password
22. Smsgorod.ru - HTTP protocol (cURL), authorization via Login (Api key)
23. Smspilot.ru - HTTP protocol (cURL), authorization via Login and Password
24. Sms.ru - HTTP protocol (cURL), authorization via Login and Password
25. Sms-sending.ru - HTTP protocol (XML+cURL), authorization via Login and Password
26. Smstraffic.ru - HTTP protocol (cURL), authorization via Login and Password
27. Smssimple.ru - HTTP protocol (cURL), authorization via Login and Password
28. Sms-uslugi.ru - HTTP protocol, authorization via Login and Password
29. Stream-telecom.ru - HTTP protocol, authorization via Login and Password
30. Targetsms.ru - HTTP protocol, authorization via Login and Password
31. Turbosms.ua (HTTP) - HTTP protocol (cURL), authorization via Login (API key). Hybrid Viber+SMS sending available
32. Unisender.com - HTTP protocol (cURL), authorization via Login (API key)
33. Smssimple.ru - HTTP protocol (cURL), authorization via Login and Password
34. Testsms - Test gateway, only records SMS text and creation date in module logs

---

## Integration with Other Modules

The module supports integration with the following delivery modules:

### NovaPoshta API (from Prorab337)

- Download and install the archive `novaposhta_aw_sms_notify_oc2_3.ocmod.zip`
- In the NovaPoshta API module on the "Cron Tasks" tab, set the necessary settings
- Add the command from the **Track shipments** field to the CRON scheduler on the server

> **Important!** For the SMS to be sent, mark the point opposite the necessary status with "For customer about status change" and create a message template in the NovaPoshta API module. In the SMS notifications module, specify only the variable `{{ order_comment }}` for the corresponding status

### Ukrposhta API (from Prorab337)

- Download and install the archive `ukposhta_aw_sms_notify_oc2_3.ocmod.zip`
- Settings are set similarly to the NovaPoshta API module

### Justin API (from Prorab337)

- Download and install the archive `justin_aw_sms_notify_oc2_3.ocmod.zip`
- Settings are set similarly to the NovaPoshta API module

---

## Import and Export Settings

The module supports import and export functionality for easy configuration transfer between stores.

### Export Settings

On the **"Import and Export"** tab, you can export the current module settings:

1. Click the **"Export"** button
2. The current module settings will be saved to a JSON file
3. The file will be automatically downloaded to your computer

> **Note:** All module settings are exported: gateway, SMS templates, notification parameters, Viber settings, etc.

### Import Settings

To load previously saved settings:

1. On the **"Import and Export"** tab, select a JSON file with settings
2. Click the **"Import"** button
3. The settings will be loaded and applied

> **Important!** Importing settings will replace all current module settings. It is recommended to export current settings before importing.

**Application:**
- Transfer settings between test and production store
- Backup module configuration
- Quick module setup on a new store

---

## For Developers

Through the **alexwaha.com - SMS notifications** module, you can send messages from other sources.

### Loading the model (only for catalog directory):

```php
$this->load->model('extension/module/aw_sms_notify');
```

### Preparing data for sending:

```php
$smsData = [
    'phone' => '+1234567890',
    'message' => 'SMS message text'
];
```

Where, `phone` - Phone number in international format; `message` - Message text

### Sending data:

```php
$this->model_extension_module_aw_sms_notify->sendMessage($smsData);
```

Returns `bool`: **true** - data is correct and was sent, **false** - if phone or message is missing.

Actual SMS sending is visible in the module log and in the SMS service Control Panel.

---

## Possible Errors and Solutions

### If no SMS are being sent:

1. **Check the balance in the messaging service**
2. **Check the correctness of login and password** - Very often the login and password for connecting to the SMS gateway differ from the access to the Personal Account in the SMS service
3. **Check the gateway logs** - If the connection is configured correctly, the gateway will record possible errors in the logs
4. **Check the registration of Alpha name/Sender signature** - Perhaps the signature is not approved or you have not registered it yet

### If SMS is not sent when the order status changes:

1. **Check module settings** - SMS on status change must be enabled, statuses selected, SMS templates set
2. **Check for events** - In the Extensions - Events section, there should be entries `aw_sms_notify_order_alert`
3. **Third-party order management module** - If modules like **Trade Management from Newstore, OrderPro, Order Manager Pro** are used, you need to install additional integrations from the `sms-notification/dist/3rd-party` folder

### Recommendations:

1. Phone numbers when ordering should be in international format, so set a mask on the phone field
2. Use transliteration of Cyrillic messages to save on mailings
3. If you are not sure you can configure the module, contact us for paid setup at the contacts below

---

## License

This project is licensed under the [GNU General Public License version 3 (GPLv3)](https://github.com/alexwaha/opencart-bundle/blob/master/LICENSE).

The rights and authorship of this software belong to the developer Oleksandr Vakhovskyi, also known as: Alexwaha, Ocdev.pro, website [https://alexwaha.com](https://alexwaha.com)

---

## Support and Contacts

> **Attention!** Technical support for this module is only available on a paid basis. Bug fixes in the main repository are done without a schedule as long as the developer has free time. If you can offer a solution to any problem, a [pull-request on GitHub](https://github.com/AlexWaha/opencart-bundle/pulls) is welcome.

**Paid Services:** Setting up the module by the author, customization, resolving conflicts with other modules, integration with a template, integration with another module in your project - only on a **paid basis**, please contact us below.

**Tested on:** Clean OpenCart installation and on the default theme (Default).

### Contact Information

- **Telegram:** [@alexwaha_dev](https://t.me/alexwaha_dev)
- **Email:** [support@alexwaha.com](mailto:support@alexwaha.com)
- **Bug report:** [https://alexwaha.com/bug-report](https://alexwaha.com/bug-report)

---

Made with ☕ by [Alexwaha.com](https://alexwaha.com)
