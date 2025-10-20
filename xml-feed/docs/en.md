# Manual | Alexwaha.com - XML Feed

**for Opencart v2.3 - 3.x**

---

## Table of Contents

1. [Module Information](#module-information)
2. [Installation Instructions](#installation-instructions)
3. [General Settings](#general-settings)
4. [Creating and Configuring XML Feeds](#creating-and-configuring-xml-feeds)
5. [XML Feed Templates](#xml-feed-templates)
6. [XML Feed Generation](#xml-feed-generation)
7. [Automation via Cron](#automation-via-cron)
8. [Template Variables](#template-variables)
9. [For Developers](#for-developers)
10. [Possible Errors and Recommendations](#possible-errors-and-recommendations)
11. [License and Contacts](#license-and-contacts)

---

## Module Information

**XML Feed** is a module for OpenCart that allows you to create and automatically generate product XML feeds for various marketplaces and advertising platforms.

### Key Features:

- **Multiple XML Feeds** - Create unlimited feeds for different platforms
- **Ready-to-use Templates** - Built-in templates for popular platforms (Google, Facebook, Prom.ua, Hotline.ua, YML)
- **Flexible Filtering** - Select categories, manufacturers, attributes, and options for export
- **Automation** - Generate via Cron tasks or manually
- **Image Settings** - Control image size and quantity
- **Multi-language** - Support for multiple languages for export
- **Multi-currency** - Export prices in different currencies
- **Shipping Information** - Configure delivery and warranty data
- **Security** - Protect feed access with a key

### Technical Specifications:

- **Author:** Alexander Vakhovski (AlexWaha)
- **Website:** [https://alexwaha.com](https://alexwaha.com)
- **License:** GPLv3
- **Compatibility:** OpenCart 2.3.x - 3.x
- **Module Code:** `aw_xml_feed`

---

## Installation Instructions

### Step 1: Prerequisites

1. Make sure you have the **[aw_core_oc2.3-3.x.ocmod.zip](https://github.com/AlexWaha/opencart-bundle/blob/master/Core/dist/aw_core_oc2.3_3.x.ocmod.zip)** module installed

   > **Important:** This is a helper functionality for all AlexWaha modules

2. For OpenCart 2.3.x, make sure you have a module that disables FTP upload of extensions installed, or you have correct FTP settings in your store settings

   > **Note:** This is not required for OpenCart 3.x

   [Link to FTP disable module (only for Opencart v2.3)](https://www.opencart.com/index.php?route=extension/extension/info&extension_id=18892)

### Step 2: Module Installation

1. Install the **aw_xml_feed_oc2.3-3.x.ocmod.zip** archive through the site admin panel

   - Go to **Extensions → Installer**
   - Upload the module archive
   - Wait for installation to complete

2. Refresh the modification cache

   - Go to **Extensions → Modifications**
   - Click the **Refresh** button

3. In **Extensions → Feeds** section, find and enable the module:

   **alexwaha.com - XML Feed**

   > **Important:** When enabled, the module will automatically create a database table and set access permissions

### Step 3: Self-check After Installation

Check the installation is correct:

- ✓ Module is present in the feeds list
- ✓ Module settings page opens without errors
- ✓ Folder for XML files has been created (default `xml-feed`)

---

## General Settings

Go to module settings: **Extensions → Feeds → alexwaha.com - XML Feed**

### Basic Parameters

#### Module Status
- **Enabled** - Module is active and working
- **Disabled** - Module is disabled, XML feed generation is not possible

#### XML Folder Name
Specify the folder name for storing generated XML files.

**Default:** `xml-feed`

**Requirements:**
- 3 to 64 characters
- Use only Latin letters, numbers, and hyphens
- No spaces or special characters

**Example:** `product-feeds` or `xml-export`

> **Important:** The folder will be created automatically in the site root on first feed generation

#### Generation Batch Size
Number of products processed at once during XML generation.

**Recommended value:** 250-500

**Maximum value:** 1000

> **Tip:** If the server experiences timeout errors, reduce the batch size to 100-250

#### Access Key
Unique key for secure access to XML feed generation.

**Requirements:**
- Minimum 8 characters
- Generated automatically on installation
- Can be changed manually

> **Security:** Don't use simple keys like `12345678`. The key protects your feeds from unauthorized access

### Store Information

#### Store Name
Official name of your store for XML export.

**Requirements:** 3 to 255 characters

**Separate for each language**

**Example:**
- Russian: `Online Electronics Store`
- English: `Electronics Online Store`

#### Company Name
Legal name of the company or sole proprietorship.

**Requirements:** 3 to 255 characters

**Separate for each language**

**Example:**
- Russian: `Electronics For All LLC`
- English: `Electronics For All LLC`

#### Store Description
Brief description of the store's activities.

**Requirements:** 10 to 255 characters

**Separate for each language**

**Example:**
```
Sale of electronics, computers,
smartphones and accessories with delivery
throughout the country. Official warranty.
```

#### Store Country
Select the country where your store is registered.

> **Application:** Used in XML feeds to indicate country of origin of goods

### Delivery Settings

#### Display Delivery Information
Enables/disables output of delivery blocks in all module XML feeds.

- **Enabled** - Delivery information will be added to XML
- **Disabled** - Delivery information will not be included

#### Delivery Service
Name of delivery service or delivery method.

**Requirements:** 3 to 255 characters (if filled)

**Separate for each language**

**Examples:**
- `Nova Poshta`
- `Courier delivery`
- `DHL Express`

#### Delivery Time in Days
Number of delivery days.

**Requirements:** 1 to 365 days

**Examples:**
- `1` - next day delivery
- `3` - 3 day delivery
- `7` - week delivery

#### Delivery Price
Delivery cost in the store's default currency.

**Requirements:** Positive number or 0 for free delivery

**Examples:**
- `0` - free delivery
- `150` - $150 delivery
- `500` - $500 delivery

### Warranty and Availability Settings

#### Static Text - Warranty
Warranty text to be used if no warranty attribute is specified for the product.

**Requirements:** 3 to 255 characters (if filled)

**Separate for each language**

**Examples:**
- `12 months`
- `24 months manufacturer warranty`
- `Store warranty 1 year`

#### Stock Statuses When Products Can Be Purchased
Select inventory statuses when products are considered available for purchase.

**Examples:**
- In Stock
- To Order
- Pre-order

> **Important:** Products with statuses NOT from this list will be marked as "out of stock"

### Feed Generation Link

After saving general settings, a link will appear to manually run generation of all active XML feeds:

```
https://your-site.com/index.php?route=extension/feed/aw_xml_feed&access_key=your_key
```

> **Application:** Use this link for testing generation or manual feed updates

---

## Creating and Configuring XML Feeds

### XML Feeds List

The module home page displays a table of all created XML feeds:

| Column | Description |
|---------|----------|
| **Name** | Feed name for identification |
| **Template** | XML template used |
| **Filename** | XML file name |
| **Price List Link** | URL of generated XML file |
| **Status** | Enabled / Disabled |
| **Action** | Edit / Delete |

### Creating New XML Feed

Click the **"Create"** button to open the feed creation form.

#### "General" Tab

##### Name
Internal feed name for identification in admin panel.

**Requirements:** 3 to 256 characters

**Examples:**
- `Google Merchant Feed`
- `Facebook Catalog`
- `Prom.ua Export`

##### Filename *.xml
XML file name without extension.

**Requirements:** 3 to 128 characters

**Use:** Latin letters, numbers, hyphens

**Examples:**
- `google-feed`
- `facebook-catalog`
- `prom-export`

> **Important:** The `.xml` extension is added automatically

##### XML Template
Select template for XML feed generation:

- **Google** - Google Merchant Center (Google Shopping)
- **Facebook** - Facebook Product Catalog
- **Hotline** - Hotline.ua (Ukraine)
- **Prom** - Prom.ua (Ukraine)
- **YML** - Rozetka, etc.

> **Note:** Each template has its own XML structure corresponding to platform requirements

##### Export Language
Select the language in which product names and descriptions will be exported.

**Application:**
- Export in Russian for Russian-language platforms
- Export in English for international platforms
- Create different feeds for different languages

##### Export Currency
Select the currency for prices in XML feed.

**Note:** Currency rate is used from OpenCart store settings

**Application:**
- UAH for Ukrainian platforms
- USD/EUR for international platforms

##### Use Original Images
- **Enabled** - Use original images without resizing
- **Disabled** - Images will be resized to specified dimensions

> **Recommendation:** Disable to reduce image size and speed up loading on platforms

##### Image Width
Image width in pixels (if not using originals).

**Default:** 800

**Recommendations:**
- **Google/Facebook:** 800-1200 pixels
- **Prom.ua/Hotline:** 600-800 pixels
- **YML:** 600-1000 pixels

##### Image Height
Image height in pixels (if not using originals).

**Default:** 800

> **Tip:** Use square images (same width and height) for best display on platforms

##### Number of Images
Number of additional product images in export.

**Requirements:** 0 to 8

**Recommended:** 4-6 images

> **Note:** Most platforms accept 1 to 10 images per product

##### Status
- **Enabled** - Feed is active and will be generated
- **Disabled** - Feed will not be generated during automatic updates

---

#### "Categories" Tab

##### Categories Available for Export
Select product categories to be included in the XML feed.

**How to select:**
1. Check the boxes of desired categories
2. When selecting a parent category, all subcategories are automatically selected

**If no categories selected:**
- Products from all categories will be included in the feed (considering manufacturer filter)

##### Category Names in Export
Override category names for export (e.g., for Google Merchant).

**Application:**
- Map to Google Product Taxonomy categories
- Adapt names to platform requirements

**Example:**
- Your category: `Smartphones`
- Name in export: `Electronics > Phones > Smartphones`

**How to configure:**
1. Find the desired category in the category list
2. Enter new name for export in the field on the right

> **For Google Merchant:** Use category hierarchy with ` > ` separator

---

#### "Manufacturer" Tab

##### Manufacturers Available for Export
Select manufacturers (brands) whose products will be included in the XML feed.

**How to select:**
1. Check the boxes of desired manufacturers
2. Multiple manufacturers can be selected

**If no manufacturer selected:**
- Products from all manufacturers will be included in the feed (considering category filter)

**Usage example:**
- Create feed only for Samsung and Apple products
- Export products of a specific brand to a separate platform

---

#### "Attributes" Tab

##### Attributes Available for Export
Select product attributes to be included in the XML feed.

**Attribute examples:**
- Material
- Color
- Size
- Weight
- Manufacturer
- Country of manufacture

**How to select:**
1. Check the boxes of desired attributes
2. Attributes will be available in the `<attributes>` XML section

##### Attribute - Warranty
Select the attribute that contains product warranty period information.

**Application:**
- If this attribute is specified for a product, its value will be used in the `<warranty>` tag
- If attribute is not filled, static text from general settings will be used

**Attribute value examples:**
- `12 months`
- `24 months`
- `Manufacturer warranty 2 years`

---

#### "Options" Tab

##### Options Available for Export
Select product options to be included in the XML feed.

**Option examples:**
- Color
- Size
- Memory
- Volume

**How to select:**
1. Check the boxes of desired options
2. Options will be available in the `<options>` XML section

##### Size Option (Google Merchant)
Select the option that defines product size.

**Application:**
- For Google Merchant Center in `<g:size>` tag
- Special handling for sized products (clothing, footwear)

**Examples:**
- Clothing size (S, M, L, XL)
- Shoe size (36, 37, 38, 39)

##### Color Option (Google Merchant)
Select the option that defines product color.

**Application:**
- For Google Merchant Center in `<g:color>` tag
- Special handling for products with color variations

**Examples:**
- Black
- White
- Red
- Blue

---

### Saving Feed

After configuring all parameters, click the **"Save"** button to create the feed.

The feed will appear in the list on the module home page.

### Editing Feed

1. In the feeds table, click the **"Edit"** button (pencil icon)
2. Make necessary changes
3. Click **"Save"**

### Deleting Feed

1. In the feeds table, click the **"Delete"** button (trash icon)
2. Confirm deletion

> **Warning:** XML file from folder is not deleted automatically, only the database record

---

## XML Feed Templates

The module includes 5 ready-to-use templates for popular platforms.

### Template Structure

Each template consists of 3 parts:

1. **header.twig** - XML file header with general store and category information
2. **items.twig** - Products section, generated for each product batch
3. **footer.twig** - XML file closing part

**Template location:**
```
catalog/view/theme/default/template/extension/feed/aw_xml_feed/layout/{template}/
```

### Available Templates

#### Google Merchant
Format for Google Merchant Center (Google Shopping).

**Files:**
- `layout/google/header.twig`
- `layout/google/items.twig`
- `layout/google/footer.twig`

**Features:**
- Atom 1.0 format
- Google Product Category support
- Size and color options support
- GTIN codes (EAN, UPC, JAN, ISBN)

#### Facebook Product Catalog
Format for Facebook Product Feed.

**Files:**
- `layout/facebook/header.twig`
- `layout/facebook/items.twig`
- `layout/facebook/footer.twig`

**Features:**
- RSS 2.0 format
- Additional fields for Facebook Ads
- Multiple image support
- Stock and shipping information

#### Hotline.ua
Format for Ukrainian marketplace Hotline.

**Files:**
- `layout/hotline/header.twig`
- `layout/hotline/items.twig`
- `layout/hotline/footer.twig`

**Features:**
- Hotline-specific format
- Product attributes support
- Warranty and shipping information

#### Prom.ua
Format for Ukrainian marketplace Prom.ua.

**Files:**
- `layout/prom/header.twig`
- `layout/prom/items.twig`
- `layout/prom/footer.twig`

**Features:**
- YML format with Prom.ua extensions
- Product options support
- Stock and shipping information

#### YML
Universal format for Rozetka and other platforms.

**Files:**
- `layout/yml/header.twig`
- `layout/yml/items.twig`
- `layout/yml/footer.twig`

**Features:**
- YML standard
- Wide compatibility with CIS platforms
- Options and attributes support
- Shipping and warranty information

### Template Customization

You can create your own templates or modify existing ones:

1. Copy template files from:
   ```
   catalog/view/theme/default/template/extension/feed/aw_xml_feed/layout/{template}/
   ```

2. To active theme folder:
   ```
   catalog/view/theme/{your_theme}/template/extension/feed/aw_xml_feed/layout/{template}/
   ```

3. Make necessary changes to TWIG files

4. Use variables from [Template Variables](#template-variables) section

> **Important:** When updating the module, your custom templates in the theme folder will not be affected

---

## XML Feed Generation

### Manual Generation via Browser

#### Generate All Active Feeds

Open the link in your browser (specified in general settings):

```
https://your-site.com/index.php?route=extension/feed/aw_xml_feed&access_key=your_key
```

#### Generate Specific Feed

Add `feed_id` parameter:

```
https://your-site.com/index.php?route=extension/feed/aw_xml_feed&access_key=your_key&feed_id=1
```

> **Note:** Feed ID can be viewed in the feed list (when hovering over edit button)

### Generation Result

After successful generation you will see a page with results:

```
XML Feed Generation Results

Generated Feeds:
- Google Merchant Feed
  Template: google
  URL: https://your-site.com/xml-feed/google-feed.xml

- Facebook Catalog
  Template: facebook
  URL: https://your-site.com/xml-feed/facebook-catalog.xml
```

### Where to Find Generated XML Files

XML files are saved in the folder specified in general settings (default `xml-feed`):

```
https://your-site.com/xml-feed/filename.xml
```

**Example:**
```
https://your-site.com/xml-feed/google-feed.xml
https://your-site.com/xml-feed/facebook-catalog.xml
https://your-site.com/xml-feed/prom-export.xml
```

Use these links to upload feeds to marketplaces.

---

## Automation via Cron

To automatically update XML feeds, configure a Cron task on the server.

### Option 1: Generate via CLI Script (recommended)

#### Generate All Active Feeds

```bash
php /path/to/your/site/cli/aw_xml_feed.php
```

#### Generate Specific Feed

```bash
php /path/to/your/site/cli/aw_xml_feed.php 1
```

Where `1` is the feed ID.

### Option 2: Generate via HTTP Request

```bash
curl "https://your-site.com/index.php?route=extension/feed/aw_xml_feed&access_key=your_key"
```

### Configuring Cron Task

#### Daily Update at 3:00 AM

```cron
0 3 * * * php /home/user/public_html/cli/aw_xml_feed.php
```

#### Update Every 6 Hours

```cron
0 */6 * * * php /home/user/public_html/cli/aw_xml_feed.php
```

#### Update Every Hour

```cron
0 * * * * php /home/user/public_html/cli/aw_xml_feed.php
```

### How to Configure Cron

**In cPanel:**
1. Go to **Cron Jobs** section
2. Add new task
3. Specify execution frequency
4. Paste script path in command field
5. Save

**Via SSH:**
1. Connect to server via SSH
2. Execute command: `crontab -e`
3. Add line with task
4. Save and exit

> **Recommendation:** Update feeds no more than once per hour to avoid unnecessary server load

---

## Template Variables

When creating or modifying XML templates, use the following variables in Twig format: `{{variable_name}}`

### General Variables (available in header.twig)

| Variable | Description | Example Value |
|-----------|----------|-----------------|
| `{{date}}` | Export date | `2025-01-15 14:30:25` |
| `{{url}}` | Feed URL | `https://site.com/xml-feed/feed.xml` |
| `{{language}}` | Export language code | `en-gb` |
| `{{currency}}` | Currency code | `USD` |
| `{{currency_rate}}` | Currency rate | `1.00` |
| `{{shop_name}}` | Store name | `My Store` |
| `{{company_name}}` | Company name | `Company LLC` |
| `{{shop_description}}` | Store description | `Electronics Online Store` |
| `{{shop_country}}` | Country code | `US` |
| `{{show_delivery_info}}` | Display delivery | `true/false` |
| `{{delivery_service}}` | Delivery service | `DHL Express` |
| `{{delivery_days}}` | Delivery time | `3` |
| `{{delivery_price}}` | Delivery price | `150` |
| `{{warranty_text}}` | Warranty text | `12 months` |

### Categories (categories array)

Loop through categories:
```twig
{% for category in categories %}
    {{category.id}}
    {{category.name}}
    {{category.google_name}}
    {{category.parent_id}}
{% endfor %}
```

| Variable | Description |
|-----------|----------|
| `{{category.id}}` | Category ID |
| `{{category.name}}` | Category name |
| `{{category.google_name}}` | Full category path for Google |
| `{{category.parent_id}}` | Parent category ID |

### Products (offers array in items.twig)

Loop through products:
```twig
{% for product in offers %}
    {{product.id}}
    {{product.name}}
    {{product.price}}
{% endfor %}
```

#### Basic Product Information

| Variable | Description |
|-----------|----------|
| `{{product.id}}` | Product ID |
| `{{product.name}}` | Product name |
| `{{product.description}}` | Product description |
| `{{product.model}}` | Product model |
| `{{product.url}}` | Product page URL |

#### Prices and Availability

| Variable | Description |
|-----------|----------|
| `{{product.price}}` | Product price |
| `{{product.special}}` | Sale price (or false) |
| `{{product.quantity}}` | Quantity in stock |
| `{{product.available}}` | Availability (true/false) |
| `{{product.in_stock}}` | In stock (true/false) |
| `{{product.availability_status}}` | Availability status (`in stock`, `preorder`, `out of stock`) |
| `{{product.availability_date}}` | Arrival date (for pre-order) |
| `{{product.condition}}` | Condition (`new`) |

#### Category and Manufacturer

| Variable | Description |
|-----------|----------|
| `{{product.category_id}}` | Product category ID |
| `{{product.vendor}}` | Manufacturer name |
| `{{product.vendorCode}}` | Article/SKU |
| `{{product.google_product_category}}` | Category path for Google |

#### Images

| Variable | Description |
|-----------|----------|
| `{{product.image}}` | Main image |
| `{{product.images}}` | Additional images array |

Loop through images:
```twig
{% for image in product.images %}
    {{image}}
{% endfor %}
```

#### Identifiers

| Variable | Description |
|-----------|----------|
| `{{product.gtin}}` | GTIN code (EAN, UPC, JAN or ISBN) |
| `{{product.ean}}` | EAN code |
| `{{product.upc}}` | UPC code |
| `{{product.jan}}` | JAN code |
| `{{product.isbn}}` | ISBN code |
| `{{product.mpn}}` | Manufacturer part number |

#### Specifications

| Variable | Description |
|-----------|----------|
| `{{product.weight}}` | Weight with unit |
| `{{product.shipping}}` | Shipping information |
| `{{product.warranty}}` | Warranty information |

#### Product Attributes

Loop through attributes:
```twig
{% for attribute in product.attributes %}
    {{attribute.group}}
    {{attribute.name}}
    {{attribute.value}}
{% endfor %}
```

| Variable | Description |
|-----------|----------|
| `{{attribute.group}}` | Attribute group |
| `{{attribute.name}}` | Attribute name |
| `{{attribute.value}}` | Attribute value |

#### Product Options

Loop through options:
```twig
{% for option in product.options %}
    {{option.id}}
    {{option.group}}
    {{option.name}}
    {{option.price}}
    {{option.quantity}}
{% endfor %}
```

| Variable | Description |
|-----------|----------|
| `{{option.id}}` | Option value ID |
| `{{option.group}}` | Option name |
| `{{option.name}}` | Option value |
| `{{option.price}}` | Price with option |
| `{{option.quantity}}` | Quantity for option |
| `{{option.weight}}` | Weight with option |

#### Special Google Options

| Variable | Description |
|-----------|----------|
| `{{product.option_size}}` | Size options array |
| `{{product.option_color}}` | Color options array |

---

## For Developers

### Database Structure

The module creates the `aw_xml_feed` table:

```sql
CREATE TABLE IF NOT EXISTS `oc_aw_xml_feed` (
  `feed_id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(256) NOT NULL,
  `filename` VARCHAR(128) NOT NULL,
  `template` TEXT NOT NULL,
  `language_id` INT(11) NOT NULL,
  `currency_code` VARCHAR(3) NOT NULL,
  `image_origin` TINYINT(1) NOT NULL,
  `image_count` INT(11) NOT NULL,
  `status` TINYINT(1) NOT NULL,
  PRIMARY KEY (`feed_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
```

### Module File Structure

```
xml-feed/
├── src/upload/
│   ├── admin/
│   │   ├── controller/extension/feed/aw_xml_feed.php
│   │   ├── model/extension/feed/aw_xml_feed.php
│   │   ├── language/{lang}/extension/feed/aw_xml_feed.php
│   │   └── view/template/extension/feed/
│   │       ├── aw_xml_feed.twig
│   │       └── aw_xml_feed_form.twig
│   ├── catalog/
│   │   ├── controller/extension/feed/aw_xml_feed.php
│   │   ├── model/extension/feed/aw_xml_feed.php
│   │   ├── language/{lang}/extension/feed/aw_xml_feed.php
│   │   └── view/theme/default/template/extension/feed/aw_xml_feed/
│   │       ├── result.twig
│   │       ├── result_cli.twig
│   │       └── layout/
│   │           ├── google/
│   │           ├── facebook/
│   │           ├── hotline/
│   │           ├── prom/
│   │           └── yml/
│   └── cli/
│       ├── aw_xml_feed.php
│       └── kernel.php
```

### Creating Custom Template

1. Create folder for template:
   ```
   catalog/view/theme/default/template/extension/feed/aw_xml_feed/layout/custom/
   ```

2. Create 3 files:
   - `header.twig` - XML header
   - `items.twig` - products
   - `footer.twig` - XML closing

3. Add template to list (in controller):
   ```php
   $this->params['templates'] = [
       'google' => 'Google',
       'facebook' => 'Facebook',
       'custom' => 'My Custom Template',
   ];
   ```

### Programmatic Feed Management

#### Create Feed

```php
$this->load->model('extension/feed/aw_xml_feed');

$data = [
    'name' => 'My Feed',
    'filename' => 'my-feed',
    'template' => 'google',
    'language_id' => 1,
    'currency_code' => 'USD',
    'image_origin' => 0,
    'image_count' => 6,
    'status' => 1
];

$feedId = $this->model_extension_feed_aw_xml_feed->addFeed($data);
```

#### Get Feed List

```php
$this->load->model('extension/feed/aw_xml_feed');
$feeds = $this->model_extension_feed_aw_xml_feed->getFeeds();
```

#### Delete Feed

```php
$this->load->model('extension/feed/aw_xml_feed');
$this->model_extension_feed_aw_xml_feed->deleteFeed($feedId);
```

### Programmatic Feed Generation

```php
// Generate via controller
$this->request->get['access_key'] = 'your_key';
$this->request->get['feed_id'] = 1; // optional

$controller = new ControllerExtensionFeedAwXmlFeed($registry);
$controller->index();
```

---

## Possible Errors and Recommendations

### Common Issues

#### Issue 1: 401 Unauthorized Error During Generation

**Causes:**
- Incorrect access key in URL
- Access key not configured in module

**Solution:**
1. Check access key in module general settings
2. Make sure you're using correct key in URL
3. Copy generation link from module settings

#### Issue 2: XML File Not Created

**Causes:**
- No write permissions to folder
- Folder for XML doesn't exist
- PHP can't create folder automatically

**Solution:**
1. Create folder manually in site root (e.g., `xml-feed`)
2. Set permissions 755 or 777 on folder:
   ```bash
   chmod 755 xml-feed
   ```
3. Check that web server has write permissions

#### Issue 3: Timeout When Generating Large Feeds

**Causes:**
- Generation batch size too large
- Many products in catalog
- PHP limitations (max_execution_time)

**Solution:**
1. Reduce batch size in settings to 100-250
2. Increase `max_execution_time` in PHP settings:
   ```ini
   max_execution_time = 300
   ```
3. Use CLI generation via Cron instead of HTTP

#### Issue 4: Incorrect Characters in XML

**Causes:**
- Special characters in product names or descriptions
- Incorrect data encoding

**Solution:**
- Module automatically escapes special characters
- Check that database uses UTF-8
- Make sure product names don't have invalid characters

#### Issue 5: Empty or Incomplete XML File

**Causes:**
- Filters excluded all products
- Products don't have images (required for some platforms)
- Products disabled or unpublished

**Solution:**
1. Check filters by categories and manufacturers
2. Make sure products are enabled and published
3. Check products have images
4. Temporarily remove all filters for testing

#### Issue 6: Error When Uploading Feed to Platform

**Causes:**
- Incorrect XML format for platform
- Missing required fields
- Incorrect field values

**Solution:**
1. Check XML file with platform validator
2. Make sure correct template is selected
3. Check platform requirements for mandatory fields
4. For Google Merchant check GTIN codes presence

### Usage Recommendations

#### Recommendation 1: Regularly Update Feeds
Configure automatic updates via Cron 1-2 times per day for data relevance.

#### Recommendation 2: Use Different Feeds for Different Platforms
Create separate feeds with individual settings for each platform.

#### Recommendation 3: Optimize Images
Use image resizing (disable "originals") to reduce feed size.

#### Recommendation 4: Fill All Product Attributes
The more product information, the better it will display on platforms.

#### Recommendation 5: Use GTIN Codes
GTIN codes (EAN, UPC) are required for Google Merchant. Fill them in product cards.

#### Recommendation 6: Test Feeds Before Publishing
Generate feed manually and check it with platform validator before configuring automation.

#### Recommendation 7: Monitor XML File Size
Files too large (>50 MB) may not be accepted by some platforms. Use filters.

#### Recommendation 8: Use CLI Generation for Cron
CLI generation works faster and more stable than HTTP requests.

### Getting Support

If you can't solve the problem yourself:

1. **Collect information:**
   - OpenCart version
   - XML Feed module version
   - Problem description
   - Error screenshots
   - Sample XML file (first 50 lines)

2. **Contact support:**
   - Telegram: [@alexwaha_dev](https://t.me/alexwaha_dev)
   - Email: [support@alexwaha.com](mailto:support@alexwaha.com)
   - Bug report: [https://alexwaha.com/bug-report](https://alexwaha.com/bug-report)

> **Important:** Technical support for this module is available on a paid basis only. Bug fixes in the main repository are performed without schedule, as the developer has free time.

> **Pull Requests are welcome:** If you can suggest a solution to any problem, pull-requests on [GitHub](https://github.com/AlexWaha/opencart-bundle/pulls) are welcome.

---

## License and Contacts

### License

This project is distributed under the [GNU General Public License version 3 (GPLv3)](https://github.com/alexwaha/opencart-bundle/blob/master/LICENSE).

Rights and authorship of this software belong to the developer:
**Alexander Vakhovski (Oleksandr Vakhovskyi)**

Also known as:
- Alexwaha
- Ocdev.pro

Official website: [https://alexwaha.com](https://alexwaha.com)

### Terms of Use

- ✓ Free use in commercial projects
- ✓ Code modification for your needs
- ✓ Distribution of modified versions (keeping GPLv3 license)
- ✗ Removal or modification of author information

### Disclaimer

The module has been tested on clean **OpenCart** installation and standard theme (Default).

The author is not responsible for:
- Incorrect operation with third-party developer themes
- Conflicts with other modules
- Problems arising from module code modification
- Data loss due to improper use

> **Recommendation:** Always test the module on a test server before installing on production site.

### Paid Support

Module setup by author, customization, creating custom templates, resolving conflicts with other modules, platform integration - **on paid basis only**.

**Services:**
- Installation and module setup
- Creating custom XML templates
- Configuring marketplace integration
- Resolving conflicts with other extensions
- Module performance optimization
- Usage consultations

### Contacts

**Telegram:**
[@alexwaha_dev](https://t.me/alexwaha_dev)

**Email:**
[support@alexwaha.com](mailto:support@alexwaha.com)

**Report Bug:**
[https://alexwaha.com/bug-report](https://alexwaha.com/bug-report)

**GitHub:**
[https://github.com/AlexWaha/opencart-bundle](https://github.com/AlexWaha/opencart-bundle)

---

### Acknowledgments

Thanks to all module users for feedback, bug reports and improvement suggestions!

---

**Alexwaha.com - XML Feed for OpenCart**

---
