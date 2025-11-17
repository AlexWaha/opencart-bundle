# Instructions | Alexwaha.com - Easy Checkout

**for Opencart v2.3 - 3.x**

---

## Table of Contents

1. [Module Information](#module-information)
2. [Installation Instructions](#installation-instructions)
3. [General Settings](#general-settings)
4. [Page Builder](#page-builder)
5. [Customer Fields Configuration](#customer-fields-configuration)
6. [Shipping Address Configuration](#shipping-address-configuration)
7. [Shipping Methods Configuration](#shipping-methods-configuration)
8. [Payment Methods Configuration](#payment-methods-configuration)
9. [Custom Fields](#custom-fields)
10. [Phone Mask](#phone-mask)
11. [JavaScript](#javascript)
12. [Import/Export Settings](#importexport-settings)
13. [For Developers](#for-developers)
14. [Troubleshooting and Recommendations](#troubleshooting-and-recommendations)
15. [License and Contacts](#license-and-contacts)

---

## Module Information

**Easy Checkout** is a professional module for OpenCart that provides a complete replacement for the standard cart and checkout pages. The module offers a streamlined, customizable checkout process with an advanced page builder system that allows you to completely reorganize and configure all checkout elements.

### Key Features:

- **Standard Checkout Replacement** - Complete replacement of cart and checkout pages
- **Page Builder** - Visual drag-and-drop block arrangement for creating the perfect layout
- **Flexible Field Settings** - Control over all form fields (name, phone, address, etc.)
- **Custom Fields** - Create unlimited additional fields
- **Phone Mask** - Dynamic and static masks for phone numbers
- **Shipping Methods Configuration** - Change names, add images, filter by countries
- **Payment Methods Configuration** - Manage visibility, descriptions, and shipping method binding
- **Multi-language Support** - Full multilingual support for all settings
- **SEO URLs** - Friendly URLs for checkout page
- **Import/Export** - Backup and restore settings

### Technical Specifications:

- **Author:** Alexander Vakhovski (AlexWaha)
- **Website:** [https://alexwaha.com](https://alexwaha.com)
- **License:** GPLv3
- **Compatibility:** OpenCart 2.3.x - 3.x
- **Module Code:** `aw_easy_checkout`

---

## Installation Instructions

### Step 1: Prerequisites

1. Make sure you have the **[aw_core_oc2.3-3.x.ocmod.zip](https://github.com/AlexWaha/opencart-bundle/blob/master/Core/dist/aw_core_oc2.3_3.x.ocmod.zip)** module installed

   > **Important:** This is auxiliary functionality for all AlexWaha modules

2. For OpenCart 2.3.x, make sure you have a module that disables FTP uploading of extensions, or you have correct FTP settings in your store settings

   > **Note:** Not required for OpenCart 3.x

   [Link to module disabling FTP upload (for OpenCart v2.3 only)](https://www.opencart.com/index.php?route=extension/extension/info&extension_id=18892)

### Step 2: Module Installation

1. Install the **aw_easy_checkout_oc2.3-3.x.ocmod.zip** archive through the site admin panel

   - Go to **Extensions → Installer**
   - Upload the module archive
   - Wait for installation to complete

2. Refresh the modification cache

   - Go to **Extensions → Modifications**
   - Click the **Refresh** button

3. In **Extensions → Modules** section find and enable the module:

   **alexwaha.com - Easy Checkout**

   > **Important:** When enabled, the module will automatically create Database Tables and Access Rights. The module uses built-in OCMOD modification to integrate with OpenCart's routing system.

### Step 3: Post-Installation Self-Check

Verify correct installation:

- ✓ Module is present in the extensions list
- ✓ Modification cache has been refreshed (OCMOD modification is active)
- ✓ Module settings page opens without errors

---

## General Settings

Go to module settings: **Extensions → Modules → alexwaha.com - Easy Checkout**

### "General" Tab

#### Basic Module Parameters

##### Module Status
- **Enabled** - Module is active and working
- **Disabled** - Module is disabled, standard OpenCart checkout is used

##### Replace Cart Page
If enabled, the standard cart page (`cart`) will be replaced with Easy Checkout page.

> **Recommendation:** Enable this option for a consistent user experience

##### Replace Checkout Page
If enabled, the standard checkout page (`checkout`) will be replaced with Easy Checkout.

##### SEO URL
Specify SEO-friendly URL for checkout page for each language and store.

**Example:**
- Russian: `oformlenie-zakaza`
- English: `checkout`
- Ukrainian: `oformlennya-zamovlennya`

> **Important:** SEO URL must be unique and must not match existing URLs in the system

#### Registration Settings

Choose customer registration mode during checkout:

- **Optional** - Customer can choose: checkout as guest or register
- **Required** - Customer must create an account to checkout
- **Guest Only** - Guest checkout only, no registration option

#### Address Settings

##### Payment Address Same as Shipping Address
If enabled, payment address is automatically copied from shipping address.

> **Recommendation:** Enable to simplify the checkout process

##### Show Customer Address Selection
For logged-in customers, a list of saved addresses will be displayed for quick selection.

#### Page Layout Settings

##### Column Width
Configure width distribution between left and right columns of the checkout page.

- **Left Column** - Width in percentage (default: 65%)
- **Right Column** - Width in percentage (default: 35%)

##### Use Alternative Theme
Connect `theme.css` file to change the module's color scheme.

> **File Path:** `catalog/view/javascript/aw_easy_checkout/theme.css`

This file allows you to easily adapt the checkout appearance to your template colors without changing the main styles.

#### Cart Settings

##### Show Product Weight
Display weight of each product in the cart block.

##### Show "Don't call me, I'm sure about the order"
Adds a checkbox allowing customer to indicate they don't need a confirmation call.

#### Minimum Order Amount

Set minimum order amount for each customer group. If cart total is less than specified, checkout will be blocked.

**Configuration:**
1. Select customer group
2. Specify minimum amount
3. Save settings

> **Application:** Useful for wholesale customers or setting a minimum free shipping threshold

#### Additional Settings

##### Terms Agreement Checked by Default
Agreement checkbox will be checked automatically on page load.

> **Warning:** Make sure this complies with your country's legislation

##### Default Email
Specify default email address if email field is not filled or optional.

**Format:** `noreply@yourdomain.com`

---

## Page Builder

### "Page Builder" Tab

The page builder allows you to visually control the placement of blocks on the checkout page using drag-and-drop.

### Available Blocks

The module provides the following blocks for building the page:

| Block | Description | Default Position |
|------|----------|------------------------|
| **Cart** | List of cart items with quantity modification | Top (left column) |
| **Customer** | Customer information fields (first name, last name, phone, email) | Top (left column) |
| **Shipping Address** | Shipping address input fields | Bottom (left column) |
| **Shipping Method** | Shipping method selection | Center (left column) |
| **Payment Method** | Payment method selection | Center (right column) |
| **Comment** | Order comment field | Bottom (left column) |
| **Coupon** | Coupon code entry field | Fixed block on the right |
| **Voucher** | Gift certificate code entry field | Fixed block on the right |
| **Totals** | Order total with breakdown (subtotal, shipping, taxes, etc.) | Fixed block on the right |
| **Custom Text** | Custom HTML content | Full width at bottom |

### Block Positions

#### Standard Positions:

- **Full Width Top** (`top_full`) - Block spans the full width of the page at top
- **Top (left column)** (`top_left`) - Top part of left column
- **Center (left column)** (`center_left`) - Middle part of left column
- **Bottom (left column)** (`bottom_left`) - Bottom part of left column
- **Full Width Bottom** (`bottom_full`) - Block spans the full width of the page at bottom
- **Center (right column)** (`center_right`) - Middle part of right column
- **Fixed Block Right** (`fix_right`) - Fixed block that remains visible when scrolling the page

### How to Use the Builder

1. **Enable/Disable Blocks**

   Each block has a toggle to enable or disable its display on the page.

2. **Move Blocks**

   - Grab the block with your mouse by the header area
   - Drag it to the desired position
   - Release to place
   - Blocks are automatically sorted within each position

3. **Sort Blocks**

   Within a single position, you can change the display order of blocks by dragging them up or down.

4. **Save Changes**

   After configuration, be sure to click the **"Save"** button to apply changes.

### Layout Examples

#### Classic Layout (default)
```
┌────────────────────────────────┬──────────────────┐
│ Cart                           │                  │
│ Customer                       │                  │
├────────────────────────────────┤ Coupon           │
│ Shipping Method                │ Voucher          │
│ Shipping Address               │ Totals           │
│ Comment                        │                  │
└────────────────────────────────┴──────────────────┘
│ Custom Text (full width)                          │
└───────────────────────────────────────────────────┘
```

#### Simplified Layout (for B2C)
```
┌────────────────────────────────┬──────────────────┐
│ Customer                       │                  │
│ Shipping Address               │ Cart             │
│ Shipping Method                │ Totals           │
│ Comment                        │                  │
└────────────────────────────────┴──────────────────┘
```

> **Tip:** Experiment with different layouts to find the optimal one for your store

---

## Customer Fields Configuration

### "Customer Fields" Tab

On this tab you can configure fields for entering customer information.

### Standard Customer Fields

- **First Name** (`firstname`) - Customer's first name
- **Last Name** (`lastname`) - Customer's last name
- **Phone** (`telephone`) - Phone number
- **E-Mail** (`email`) - Email address

### Field Configuration

The following settings are available for each field:

#### Field Status

- **Disabled** - Field is not displayed
- **Enabled * [required]** - Field is displayed and is required for completion
- **Enabled not required** - Field is displayed but not required for completion

#### Field Visibility

- **Show to All** - Field is visible to all customers
- **Guests Only** - Field is visible only to non-logged-in customers
- **Authorized Only** - Field is visible only to logged-in customers

#### Multi-language Field Settings

For each language you can configure:

##### Field Name
Label text that the customer sees.

**Example:**
- Russian: `Имя`
- English: `First Name`
- Ukrainian: `Ім'я`

##### Placeholder
Hint text inside the empty field.

**Example:**
- Russian: `Введите ваше имя`
- English: `Enter your first name`
- Ukrainian: `Введіть ваше ім'я`

##### Error Text
Message that will be displayed when the field is filled incorrectly.

**Example:**
- Russian: `Имя должно содержать от 1 до 32 символов!`
- English: `First Name must be between 1 and 32 characters!`
- Ukrainian: `Ім'я повинно містити від 1 до 32 символів!`

##### Regular Expression (for validation)
Regex pattern to check entered data.

**Pattern Examples:**

| Purpose | Pattern | Description |
|-----------|---------|----------|
| Digits Only | `/^\d+$/` | Numbers only |
| Letters Only | `/^[a-zA-Zа-яА-ЯёЁіІїЇєЄ]+$/u` | Letters (Latin and Cyrillic) |
| Letters and Digits | `/^[a-zA-Z0-9а-яА-ЯёЁіІїЇєЄ\s_-]+$/u` | Letters, digits, spaces, hyphen, underscore |
| Email | `/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/` | Email address format |
| Phone | `/^\+?[\d\s\-\(\)]{10,15}$/` | Phone (10-15 characters) |

> **Note:** If the field is empty, only non-emptiness will be checked (for required fields)

#### Sort Order

Specify a numeric value to determine the field display order. Fields are sorted in ascending order.

**Example:**
- First Name: `1`
- Last Name: `2`
- Phone: `3`
- Email: `4`

### Adding Custom Fields to Customer Block

You can add custom fields to the customer information block. To do this:

1. Go to the **"Custom Fields"** tab
2. Create a new field with **"Customer"** location
3. Return to the **"Customer Fields"** tab
4. The custom field will appear in the field list
5. Configure its visibility and sort order

---

## Shipping Address Configuration

### "Shipping Address" Tab

On this tab you can configure shipping address input fields.

### Standard Address Fields

- **Country** (`country`) - Country selection from list
- **Region** (`zone_id`) - Region/state selection (depends on selected country)
- **City** (`city`) - City name
- **Address** (`address_1`) - Main address (street, house, apartment)
- **Address 2** (`address_2`) - Additional address
- **Postcode** (`postcode`) - Postal code
- **Company** (`company`) - Company name (for legal entities)

### Address Field Configuration

Similar to customer fields, the following are available for each address field:

#### Field Status
- Disabled
- Enabled * [required]
- Enabled not required

#### Field Visibility
- Show to All
- Guests Only
- Authorized Only

#### Field Type

Some fields can be changed to a different input type:

- **Input** - Text field for free input
- **Select** - Dropdown list with preset options

##### "Select" Type Field Configuration

If you selected "Select" type, you need to specify possible values:

1. Click **"Add Value"**
2. Enter option name for each language
3. Specify default value (optional)
4. Add the required number of options

**Example for "City" field:**
- Kyiv
- Warsaw
- Almaty

#### Multi-language Settings

For each language configure:
- Field name
- Placeholder
- Error text
- Regular expression for validation

#### Sort Order

Numeric value to determine field display order.

**Recommended order:**
1. Country
2. Region
3. City
4. Address
5. Address 2
6. Postcode
7. Company

### "Country" and "Region" Field Features

#### "Country" Field
- Uses built-in OpenCart country directory
- Region list is automatically loaded when country is selected
- Affects shipping cost and tax calculation
- Changes phone mask (when dynamic mask is enabled)

#### "Region" Field
- Depends on selected country
- Region list is loaded dynamically
- Required for some shipping methods

> **Important:** "Country" and "Region" fields must be enabled if you use geography-dependent shipping methods

### Adding Custom Fields to Shipping Address

You can add custom fields to the shipping address block:

1. Go to the **"Custom Fields"** tab
2. Create a new field with **"Address"** location
3. Return to the **"Shipping Address"** tab
4. The custom field will appear in the list
5. Configure its parameters

**Examples of custom fields:**
- Intercom code
- Entrance
- Floor
- Delivery time
- Courier instructions

---

## Shipping Methods Configuration

### "Shipping Methods" Tab

On this tab you can configure the display and behavior of shipping methods.

### General Shipping Settings

#### Free Shipping From
Specify the order amount at which shipping becomes free.

**Configuration:**
1. Enable **"Free Shipping From"** parameter
2. Specify amount (e.g., `1000`)
3. On the checkout page customer will see message:
   - **"Free shipping in: \$150."** (if cart total is \$750)
   - **"Free Shipping!"** (if cart total >= 1000)

> **Application:** Encourages customers to increase order amount to get free shipping

### Individual Shipping Method Configuration

For each installed shipping method you can configure:

#### Change Shipping Method Name

Replace the standard shipping method name with your own.

**Example:**
- Standard: `Flat Rate`
- Changed: `Courier Delivery in the City`

**Configuration:**
1. Enable **"Change Shipping Method Name"**
2. Enter new name for each language

#### Shipping Method Image

Add logo or icon for visual designation of shipping method.

**Recommendations:**
- Size: 36x36 pixels (or proportionally larger)
- Format: PNG with transparent background
- Content: Shipping service logo or thematic icon

**Configuration:**
1. Click on the image field
2. Select image from file manager
3. Specify display width and height (default: 36x36)

#### Country Visibility Configuration

##### Show for All Countries
Shipping method will be available to customers from any country.

##### Only for Selected Countries
Shipping method will be displayed only to customers from specified countries.

**How to configure:**
1. Select **"Only for Selected Countries"**
2. Start typing country name in search field
3. Select desired country from dropdown list
4. Add all required countries

**Usage example:**
- Nova Poshta - only for Ukraine
- DHL Express - for all countries

##### Don't Show for Countries
Shipping method will be hidden for customers from specified countries.

**Usage example:**
- Hide "Pickup" for customers from other countries
- Hide local courier service for international orders

### Integration with Shipping Modules

Easy Checkout module automatically integrates with all installed shipping extensions:

- Flat Rate (Fixed cost)
- Weight Based Shipping
- Free Shipping
- PickPoint
- Nova Poshta
- Ukrposhta
- Boxberry
- And other third-party shipping modules

> **Important:** For shipping methods to display in Easy Checkout block, they must be installed, configured and enabled in the standard **Extensions → Shipping** section

### Shipping Messages

#### Shipping Available
If country and region are selected for which shipping methods are configured, customer will see available options.

#### No Shipping Methods Available
If shipping methods are unavailable (wrong geography, weight exceeded, etc.), message will be displayed:

**"No shipping methods available."**

> **Tip:** Configure at least one universal shipping method (e.g., "Courier Delivery") for all countries

---

## Payment Methods Configuration

### "Payment Methods" Tab

On this tab you can configure the display and behavior of payment methods.

### Individual Payment Method Configuration

The following settings are available for each installed payment method:

#### Change Payment Method Name

Replace the standard payment method name with your own.

**Example:**
- Standard: `Cash On Delivery`
- Changed: `Cash Payment on Delivery`

**Configuration:**
1. Enable **"Change Payment Method Name"**
2. Enter new name for each language

#### Payment Method Description

Add description that will be displayed under the selected payment method.

**Example:**
```
Payment is made to the courier upon order receipt.
Cash and bank cards are accepted.
```

**Configuration:**
1. Enable **"Display Description Under Payment Method"**
2. Enter description text for each language

#### Payment Method Image

Add payment system logo or icon.

**Recommendations:**
- Size: 36x36 pixels (or proportionally)
- Format: PNG with transparent background
- Content: Payment system logo (Visa, MasterCard, PayPal, etc.)

**Configuration:**
1. Click on the image field
2. Select image from file manager
3. Specify display width and height

#### User Type Visibility Configuration

##### Option Available for Guests
Payment method will be displayed to non-logged-in customers.

##### Option Available for Authorized Users
Payment method will be displayed to logged-in customers.

**Usage examples:**

| Payment Method | Guests | Authorized | Reason |
|--------------|-------|----------------|---------|
| Cash on Delivery | ✓ | ✓ | Available to all |
| Bank Transfer | ✗ | ✓ | Requires customer details |
| Bonus Payment | ✗ | ✓ | Bonuses only for registered |
| Online Card Payment | ✓ | ✓ | Available to all |

#### Shipping Method Restriction

Specify for which shipping methods this payment method is available.

**Configuration:**
1. Select shipping methods from list
2. Payment method will be available only when specified shipping methods are selected
3. If nothing is selected, payment method is available for all shipping methods

**Restriction examples:**

| Payment Method | Available for Shipping | Reason |
|--------------|----------------------|---------|
| Cash on Delivery | Courier Delivery, COD Mail | Cannot pay cash at pickup point |
| Payment in Office | Pickup | Payment made when receiving goods in office |
| 100% Prepayment | International Delivery | International delivery requires prepayment |

> **Tip:** Use restrictions to automatically filter incompatible shipping and payment combinations

### Integration with Payment Modules

Easy Checkout module automatically integrates with all installed payment extensions:

- Cash On Delivery
- Bank Transfer
- PayPal
- Stripe
- LiqPay
- WayForPay
- And other third-party payment modules

> **Important:** For payment methods to display in Easy Checkout block, they must be installed, configured and enabled in the standard **Extensions → Payment** section

### Payment Messages

#### No Payment Methods Available
If all payment methods are hidden by filters (guest/authorized, shipping method), message will be displayed:

**"No payment methods available"**

> **Recommendation:** Configure at least one universal payment method available for all user types and shipping methods

---

## Custom Fields

### "Custom Fields" Tab

Custom fields allow you to collect additional information from customers that is not included in the standard OpenCart field set.

### Custom Fields List

The table displays all created custom fields with information:

| Column | Description |
|---------|----------|
| **Name** | Field name (in default language) |
| **Location** | Where field is displayed (Customer / Address) |
| **Field Type** | Field type (Select, Input, Textarea, etc.) |
| **Status** | Enabled / Disabled |
| **Action** | Edit / Delete |

### Creating New Custom Field

Click **"Create Field"** button to open the creation form.

#### Step 1: Basic Settings

##### Location
Choose where the field will be displayed:

- **Customer** - In customer information block
- **Address** - In shipping address block

##### Field Type
Select field type from list:

**Selection Fields (require specifying options):**
- **Select** - Standard dropdown list
- **Select2** - Enhanced dropdown list with search
- **Radio** - Radio button group (choose one of several)
- **Checkbox** - Checkbox group (choose multiple options)

**Input Fields:**
- **Input** - Single-line text field
- **Text** - Single-line text field (Input alias)
- **Textarea** - Multi-line text field
- **Date** - Calendar for date selection (format: YYYY-MM-DD)
- **Date and Time** - Calendar with time (format: YYYY-MM-DD HH:MM)
- **Time** - Time selection (format: HH:MM)

##### Status
- **Enabled** - Field is active and displayed
- **Disabled** - Field is hidden

##### Required
- **Yes** - Field is required for completion
- **No** - Field is optional

##### Save Field Value to Order Comment
- **Yes** - Field value will be added to order comment in admin panel
- **No** - Value is saved only in database

> **Recommendation:** Enable this option for important information that needs to be seen immediately when viewing the order

#### Step 2: Names and Messages (multi-language)

For each language specify:

##### Field Name
Label text that customer sees.

**Example:**
- Russian: `Код домофона`
- English: `Intercom code`

##### Placeholder
Hint text inside empty field (for input fields).

**Example:**
- Russian: `Например: 123К456`
- English: `Example: 123K456`

##### Error Text
Message for incorrect completion or unfilled required field.

**Example:**
- Russian: `Пожалуйста, укажите код домофона`
- English: `Please enter the intercom code`

#### Step 3: Validation

##### Regular Expression
Specify regex pattern to check entered data.

**Common Patterns:**

| Purpose | Pattern | Example Value |
|-----------|---------|----------------|
| Digits Only | `/^\d+$/` | `12345` |
| Letters Only | `/^[a-zA-Zа-яА-ЯёЁіІїЇєЄ]+$/u` | `Johnson` |
| Letters and Digits | `/^[a-zA-Z0-9а-яА-ЯёЁіІїЇєЄ\s_-]+$/u` | `Apartment 5` |
| Email | `/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/` | `user@example.com` |
| Phone (10-15 digits) | `/^\+?[\d\s\-\(\)]{10,15}$/` | `+3 (999) 123-45-67` |

> **Note:** If field is empty, only completion requirement is checked

**Quick Pattern Selection:**
Form has dropdown list with common patterns. Select desired one and it will be automatically inserted into field.

#### Step 4: Values (for Select, Radio, Checkbox)

If you selected **Select**, **Select2**, **Radio** or **Checkbox** field type, you need to specify possible selection options.

##### Adding Value

1. Click **"Add Value"** button
2. Enter option name for each language
3. Specify sort order (numeric value)
4. Repeat for all options

**Example for "Delivery Time" field:**

| Value (RU) | Value (EN) | Sort Order |
|--------------|---------------|------------|
| Утро (9:00 - 12:00) | Morning (9:00 AM - 12:00 PM) | 1 |
| День (12:00 - 18:00) | Afternoon (12:00 PM - 6:00 PM) | 2 |
| Вечер (18:00 - 21:00) | Evening (6:00 PM - 9:00 PM) | 3 |

##### Default Value
For Select type fields you can specify value that will be selected automatically on page load.

#### Step 5: Customer Groups

Specify for which customer groups this field will be displayed.

**Configuration:**
1. Check desired customer groups (Default, Wholesale, Retailer, etc.)
2. If nothing is checked, field will be available to all groups

**Usage example:**
- "Company TIN" field - only for "Wholesale Customers" group
- "Discount Card Number" field - only for "VIP Customers" group

#### Saving Field

Click **"Save"** button to create field. Field will appear in custom fields list and become available for adding to "Customer" or "Shipping Address" blocks.

### Editing Custom Field

1. In custom fields table click **"Edit"** button (pencil icon)
2. Make necessary changes
3. Click **"Save"**

> **Warning:** Changing field location (Customer/Address) is unavailable if field is already used in block configuration

### Deleting Custom Field

1. In custom fields table click **"Delete"** button (trash icon)
2. Confirm deletion

### Custom Field Usage Examples

#### Example 1: Intercom Code
- **Type:** Input
- **Location:** Address
- **Required:** No
- **Save to Comment:** Yes
- **Validation:** `/^\d+[А-Яа-я]?\d*$/u` (digits and optional letter)

#### Example 2: Desired Delivery Date
- **Type:** Date
- **Location:** Address
- **Required:** Yes
- **Save to Comment:** Yes

#### Example 3: Delivery Time
- **Type:** Select
- **Location:** Address
- **Required:** Yes
- **Values:**
  - Morning (9:00 - 13:00)
  - Afternoon (13:00 - 18:00)
  - Evening (18:00 - 22:00)
- **Save to Comment:** Yes

#### Example 4: Special Wishes
- **Type:** Textarea
- **Location:** Address
- **Required:** No
- **Save to Comment:** Yes

#### Example 5: Packaging Type
- **Type:** Radio
- **Location:** Customer
- **Required:** No
- **Values:**
  - Standard Packaging (free)
  - Gift Packaging (+\$20)
  - Premium Packaging (+\$50)

---

## Phone Mask

### Phone Mask Functionality

Easy Checkout module includes advanced phone number formatting system with support for both static and dynamic masks.

### Phone Mask Types

#### Disabled
Mask is not applied. User can enter number in any format.

> **Not Recommended:** May lead to incorrect phone numbers in orders

#### Static Mask
Single mask is used for all countries, specified in module settings.

**When to use:**
- You work with one country only
- You have specific phone format
- You want full control over format

**Configuration:**
1. Select mask type: **"Static Mask (settings only)"**
2. Specify mask in **"Phone Mask for Maska.js"** field

**Example mask for Ukraine:**
```
+3 (###) ###-##-##
```

#### Dynamic Mask (recommended)
Mask automatically changes depending on selected country in shipping address.

**Advantages:**
- Automatic adaptation to country format
- Convenience for international customers
- Mask database for 100+ countries

**Configuration:**
1. Select mask type: **"Dynamic Mask (automatically by country)"**
2. Specify fallback mask for unsupported countries

**How it works:**
1. Customer selects country in "Country" field
2. Module automatically determines mask for this country from built-in database
3. Phone field switches to corresponding mask
4. If mask for country is not found, fallback mask is used

### Mask Format for Maska.js

Module uses **Maska.js** library for phone formatting. Mask is set using special tokens:

| Token | Description | Example |
|-------|----------|--------|
| `#` | Digit (0-9) | `###` → `123` |
| `@` | Letter (a-z, A-Z) | `@@@` → `abc` |
| `*` | Letter or digit | `***` → `a1b` |

**Mask examples for different countries:**

| Country | Mask | Result Format |
|--------|-------|-------------------|
| Ukraine | `+38 (0##) ###-##-##` | `+38 (099) 123-45-67` |
| USA/Canada | `+1 (###) ###-####` | `+1 (555) 123-4567` |
| United Kingdom | `+44 ## #### ####` | `+44 20 1234 5678` |
| Germany | `+49 ### ########` | `+49 123 45678901` |
| Kazakhstan | `+7 (7##) ###-##-##` | `+7 (777) 123-45-67` |

### Country Mask Database

Module includes built-in `country_phone_masks.json` file with masks for 100+ countries.

**File Location:**
```
system/library/Alexwaha/fixtures/country_phone_masks.json
```

**Entry Structure:**
```json
{
  "US": {
    "name": "USA",
    "dial_code": "+1",
    "mask": "+1 (###) ###-##-##"
  },
  "UA": {
    "name": "Ukraine",
    "dial_code": "+380",
    "mask": "+38 (0##) ###-##-##"
  }
}
```

> **For Developers:** You can add or modify masks in this file if needed

### Fallback Mask

Fallback mask is used for countries absent in mask database.

**Recommended fallback mask:**
```
+###############
```

This mask allows entering any international phone number (up to 15 digits with country code).

### Phone Mask Configuration

1. Go to module settings, **"General"** tab
2. Find **"Phone Mask"** section
3. Select mask type:
   - Disabled
   - Static Mask
   - Dynamic Mask (recommended)
4. Specify mask (for static) or fallback mask (for dynamic)
5. Save settings

### Testing Mask

1. Open checkout page on frontend
2. Start entering phone number in "Phone" field
3. Make sure format is automatically applied
4. For dynamic mask: change country and check format change

### Usage Tips

**For single-country stores:**
- Use static mask with your country's format
- This will simplify configuration and ensure uniformity

**For international stores:**
- Use dynamic mask
- Set universal fallback mask
- "Country" field should be required for correct dynamic mask operation

**For B2B stores:**
- Add additional "Extension" field as custom field
- Use static mask for main phone

---

## JavaScript

### "Javascript" Tab

On this tab you can add custom JavaScript code that will execute after updating blocks on checkout page.

### When to Use

Use this function for:

- **Analytics Integration** - Send events to Google Analytics, Facebook, etc.
- **Additional Validation** - Client-side data validation
- **Element Behavior Changes** - Show/hide blocks based on conditions
- **Chat Integration** - Pass data to online chat
- **Interface Customization** - Change element appearance

### Syntax and Available Variables

JavaScript code executes after each checkout page block update.

**Available Libraries:**
- jQuery (alias `$`)
- All global `window` variables

**Execution Context:**
Code executes in global scope, has access to DOM and all page elements.

### Usage Examples

#### Example 1: Send Event to Google Analytics

```javascript
// Send event when reaching checkout page
if (typeof gtag !== 'undefined') {
    gtag('event', 'begin_checkout', {
        'event_category': 'ecommerce',
        'event_label': 'Easy Checkout Page'
    });
}
```

#### Example 2: Hide Coupon Block for Certain Group

```javascript
// Hide coupon block for guests
$(document).ready(function() {
    var isGuest = $('input[name="account"]:checked').val() === 'guest';
    if (isGuest) {
        $('.coupon-block').hide();
    }
});
```

#### Example 3: Auto-fill Field When Selecting Certain Country

```javascript
// Automatically fill postcode for Kyiv
$('select[name="country_id"]').on('change', function() {
    var countryId = $(this).val();
    var cityName = $('input[name="city"]').val().toLowerCase();

    if (countryId == '220' && cityName.includes('kyiv')) {
        $('input[name="postcode"]').val('01001');
    }
});
```

#### Example 4: TIN Validation for Legal Entities

```javascript
// Check TIN (10 or 12 digits for Ukraine)
$('input[name="custom_field[123]"]').on('blur', function() {
    var tin = $(this).val().replace(/\D/g, '');
    var isValid = tin.length === 10 || tin.length === 12;

    if (!isValid && tin.length > 0) {
        alert('TIN must contain 10 or 12 digits');
        $(this).addClass('error');
    } else {
        $(this).removeClass('error');
    }
});
```

#### Example 5: Display Warning When Selecting Certain Payment Method

```javascript
// Warning when selecting cash on delivery
$('input[name="payment_method"]').on('change', function() {
    var paymentCode = $(this).val();

    if (paymentCode === 'cod') {
        var message = 'Attention! Cash on delivery payment includes additional 3% fee.';
        if (!$('.cod-warning').length) {
            $(this).closest('.radio').append('<div class="cod-warning alert alert-warning">' + message + '</div>');
        }
    } else {
        $('.cod-warning').remove();
    }
});
```

### JavaScript Code Debugging

For debugging use browser console:

1. Open checkout page
2. Press `F12` to open developer console
3. Go to **Console** tab
4. Check for errors
5. Use `console.log()` to output debug information

**Debug example:**
```javascript
console.log('Easy Checkout JavaScript loaded');

$('input[name="telephone"]').on('change', function() {
    console.log('Phone number changed:', $(this).val());
});
```

### Important Notes

> **Warning:** Incorrect JavaScript code can break checkout page functionality. Thoroughly test code before using on production site.

> **Security:** Don't use code from unverified sources. Malicious JavaScript can steal customer data.

> **Performance:** Avoid heavy operations and long loops that can slow down page loading.

> **Compatibility:** Use jQuery syntax for compatibility with various OpenCart themes.

---

## Import/Export Settings

### "Import/Export" Tab

Import and export function allows saving module settings backups and transferring them between stores.

### Export Settings

Export creates JSON file with all current module settings.

#### What Export Includes:

- ✓ General settings (status, page replacement, registration)
- ✓ Layout settings (column width, theme.css usage)
- ✓ Block configuration (positions, sorting, status)
- ✓ Customer field settings
- ✓ Shipping address field settings
- ✓ Shipping method settings
- ✓ Payment method settings
- ✓ Custom field schema (structure, not data)
- ✓ JavaScript code
- ✓ Custom text
- ✓ SEO URLs for all languages and stores
- ✓ Phone mask settings
- ✓ Minimum order amounts

#### How to Export Settings:

1. Go to **"Import/Export"** tab
2. Click **"Export"** button
3. File will be automatically downloaded in browser

**File Name:** `aw_easy_checkout_settings_YYYY-MM-DD_HH-ii-ss.json`

**Example:** `aw_easy_checkout_settings_2025-01-15_14-30-25.json`

#### When to Use Export:

- **Before Importing New Settings** - Create backup of current settings
- **Before Module Update** - Save settings in case rollback is needed
- **Transfer to Test Server** - Copy settings from production to test site
- **Store Cloning** - Transfer settings to new store
- **Periodic Backups** - Regularly save copies for security

### Import Settings

Import loads settings from previously exported JSON file.

> **Warning!** Settings import will **REPLACE ALL** current module settings. Before import be sure to export current settings!

#### How to Import Settings:

1. Go to **"Import/Export"** tab
2. Click **"Choose File"** button
3. Select previously exported JSON file
4. Click **"Import"** button
5. Wait for successful import message
6. Refresh settings page to apply changes

#### Post-Import Check:

After successful import check:

- ✓ "General" tab - all settings correct
- ✓ "Page Builder" tab - blocks in their places
- ✓ "Customer Fields" tab - fields configured correctly
- ✓ "Shipping Address" tab - address fields correct
- ✓ "Shipping Methods" tab - settings applied
- ✓ "Payment Methods" tab - settings applied
- ✓ "Custom Fields" tab - fields imported
- ✓ Frontend site - checkout page displays correctly

#### Possible Import Errors:

##### "Please select file for import!"
- You didn't select file before clicking "Import" button
- **Solution:** Select JSON file and try again

##### "Invalid file format. Expected JSON file with module settings."
- File is damaged or has wrong format
- File is not Easy Checkout settings export
- **Solution:** Make sure you're using correct export file

##### "Settings import error: [error description]"
- Technical error occurred while processing file
- **Solution:** Check server logs, contact support

##### "You do not have permission to manage this module!"
- Your account doesn't have rights to change module settings
- **Solution:** Log in with administrator account or get necessary rights

### Exported File Structure

JSON file has the following structure (simplified):

```json
{
  "status": true,
  "replace_cart": true,
  "replace_checkout": true,
  "customer": {
    "default": {
      "title": "Default",
      "firstname": { ... },
      "lastname": { ... },
      "telephone": { ... },
      "email": { ... }
    }
  },
  "shipping_address": { ... },
  "shipping_methods": { ... },
  "payment_methods": { ... },
  "block_status": { ... },
  "block_position": { ... },
  "block_sort_order": { ... },
  "mask_type": "dynamic",
  "mask": "",
  "javascript": "",
  "seo_url": { ... }
}
```

> **For Developers:** You can manually edit JSON file in text editor before import (at your own risk)

### Transferring Settings Between Stores

#### Scenario 1: Copying from Production to Test Server

1. On production server: export settings
2. Download JSON file
3. On test server: install Easy Checkout module
4. Import downloaded JSON file
5. Check settings correctness

#### Scenario 2: Cloning Settings to New Store

1. In source store: export settings
2. In new store: install Easy Checkout module
3. Install same shipping and payment methods as in source store
4. Import settings file
5. Adapt settings for new store (SEO URLs, default email, etc.)

#### Scenario 3: Rollback to Previous Settings Version

1. Find settings backup (previously exported JSON file)
2. In current store: import backup file
3. Check settings restoration

---

## For Developers

### Code Integration

Easy Checkout module provides API for integration with custom code and extensions.

### Module File Structure

```
easy-checkout/
├── admin/
│   ├── controller/extension/module/aw_easy_checkout.php
│   ├── model/extension/module/aw_easy_checkout.php
│   ├── language/{lang}/extension/module/aw_easy_checkout.php
│   └── view/template/extension/module/aw_easy_checkout/
├── catalog/
│   ├── controller/extension/aw_easy_checkout/
│   │   ├── main.php - Main page controller
│   │   ├── customer.php - Customer field processing
│   │   ├── address.php - Address processing
│   │   ├── shipping_method.php - Shipping methods
│   │   ├── payment_method.php - Payment methods
│   │   ├── cart.php - Cart
│   │   ├── comment.php - Order comment
│   │   ├── coupon_voucher.php - Coupons and certificates
│   │   ├── custom_text.php - Custom text
│   │   ├── validation.php - Server validation
│   │   ├── reload.php - AJAX block update
│   │   ├── api.php - API endpoints
│   │   ├── country.php - Get regions by country
│   │   └── event.php - Event handlers
│   ├── model/extension/aw_easy_checkout/model.php
│   ├── language/{lang}/extension/aw_easy_checkout/lang.php
│   └── view/template/extension/aw_easy_checkout/
└── system/library/Alexwaha/
    ├── EasyCheckoutHelper.php - Helper class
    └── fixtures/country_phone_masks.json - Phone mask database
```

### API Endpoints

Module provides following AJAX endpoints for integration:

#### Get Shipping Methods
```php
URL: index.php?route=extension/aw_easy_checkout/shipping_method
Method: POST
Response: JSON with available shipping methods
```

#### Get Payment Methods
```php
URL: index.php?route=extension/aw_easy_checkout/payment_method
Method: POST
Response: JSON with available payment methods
```

#### Form Validation
```php
URL: index.php?route=extension/aw_easy_checkout/validation
Method: POST
Response: JSON with validation results
```

#### Block Update
```php
URL: index.php?route=extension/aw_easy_checkout/reload
Method: POST
Parameters: block (block name to update)
Response: Block HTML markup
```

#### Get Regions by Country
```php
URL: index.php?route=extension/aw_easy_checkout/country/zones
Method: POST
Parameters: country_id
Response: JSON with region list
```

### Template Overriding

You can override any module template by creating file in your theme directory:

**Override Structure:**
```
catalog/view/theme/{your_theme}/template/extension/aw_easy_checkout/
```

**Cart template override example:**

1. Copy file:
   ```
   catalog/view/template/extension/aw_easy_checkout/cart.twig
   ```

2. To new location:
   ```
   catalog/view/theme/mytheme/template/extension/aw_easy_checkout/cart.twig
   ```

3. Make necessary changes in copied file

> **Important:** When updating module your overridden templates won't be affected

### Helper Class

Module provides helper class to simplify data handling:

```php
// Load helper
$helper = new \Alexwaha\EasyCheckoutHelper($registry);

// Get module configuration
$config = $this->awCore->getConfig('aw_easy_checkout');
$status = $config->get('status', false);

// Get phone mask for country
$mask = $helper->getPhoneMaskByCountry('UA');
// Returns: +3 (###) ###-##-##
```

### URL Rewrite Integration

The module uses OpenCart's built-in URL Rewrite system for routing integration.

#### How It Works

Through OCMOD modification, the `catalog/controller/startup/startup.php` file is modified to register the `\Alexwaha\EasyCheckoutHelper` class as a URL Rewrite handler:

```php
$this->url->addRewrite(new \Alexwaha\EasyCheckoutHelper($this->registry));
```

This approach allows the module to intercept and handle requests to cart and checkout pages without using Events.

**Advantages:**
- More performant mechanism (no extra event triggers)
- Lower system load
- Built-in compatibility with OpenCart architecture
- No conflicts with other modules using events

### Adding Custom Block

To add custom block to system:

1. Create controller:
   ```php
   // catalog/controller/extension/aw_easy_checkout/my_custom_block.php

   class ControllerExtensionAwEasyCheckoutMyCustomBlock extends Controller {
       public function index() {
           $data = [];

           // Your logic
           $data['custom_data'] = 'Hello World';

           return $this->load->view('extension/aw_easy_checkout/my_custom_block', $data);
       }
   }
   ```

2. Create template:
   ```twig
   {# catalog/view/template/extension/aw_easy_checkout/my_custom_block.twig #}

   <div class="my-custom-block">
       <h3>My Custom Block</h3>
       <p>{{ custom_data }}</p>
   </div>
   ```

3. Add block to configuration via hook or directly in database

### Working with Custom Fields from Code

#### Getting Custom Field Value in Order

```php
// In controller or model
$this->load->model('extension/module/aw_easy_checkout');

$customFieldId = 123; // Your custom field ID
$orderId = 456; // Order ID

// Get custom field information
$customField = $this->model_extension_module_aw_easy_checkout->getCustomField($customFieldId);

// Get value from session (during checkout)
$value = $this->session->data['custom_field'][$customFieldId] ?? '';

// Get from order data
$this->load->model('checkout/order');
$order = $this->model_checkout_order->getOrder($orderId);
$customFieldsData = json_decode($order['custom_field'], true);
$value = $customFieldsData[$customFieldId] ?? '';
```

#### Programmatic Custom Field Creation

```php
$this->load->model('extension/module/aw_easy_checkout');

$data = [
    'type' => 'text',
    'location' => 'customer',
    'status' => 1,
    'required' => 1,
    'save_to_order' => 1,
    'validation' => '/^\d+$/',
    'custom_field_description' => [
        'name' => [
            1 => 'Customer Code', // language_id => name
            2 => 'Код клиента'
        ],
        'text_error' => [
            1 => 'Please enter customer code',
            2 => 'Пожалуйста, введите код клиента'
        ]
    ],
    'custom_field_customer_group' => [
        ['customer_group_id' => 1]
    ]
];

$customFieldId = $this->model_extension_module_aw_easy_checkout->addCustomField($data);
```

### Connecting to Order Processing

If you need to perform actions when order is placed through Easy Checkout:

```php
// Create event
$this->load->model('setting/event');

$this->model_setting_event->addEvent(
    'my_extension_easy_checkout',
    'catalog/model/checkout/order/addOrder/before',
    'extension/my_extension/event/beforeOrderAdd'
);

// Event handler
// catalog/controller/extension/my_extension/event.php

public function beforeOrderAdd(&$route, &$args) {
    // $args[0] contains order data
    $orderData = &$args[0];

    // Check that order is placed through Easy Checkout
    if (isset($this->session->data['easy_checkout'])) {
        // Your logic
        $this->log->write('Order placed via Easy Checkout');
    }
}
```

### Getting Module Configuration

```php
// Through awCore helper
$config = $this->awCore->getConfig('aw_easy_checkout');

// Get specific values
$replaceCart = $config->get('replace_cart', false);
$replaceCheckout = $config->get('replace_checkout', false);
$maskType = $config->get('mask_type', 'dynamic');
$customerFields = $config->get('customer', []);

// Get blocks
$blockStatus = $config->get('block_status', []);
$blockPosition = $config->get('block_position', []);
```

### Programmatic Settings Change

```php
// Change module settings programmatically
$this->load->model('setting/setting');

$settings = [
    'aw_easy_checkout_status' => 1,
    'aw_easy_checkout_replace_cart' => 1,
    'aw_easy_checkout_replace_checkout' => 1,
    // ... other settings
];

// For main store (store_id = 0)
$this->model_setting_setting->editSetting('aw_easy_checkout', $settings, 0);

// For specific store
$this->model_setting_setting->editSetting('aw_easy_checkout', $settings, $store_id);
```

### Debugging

#### Enable Logging

Add logging in your code:

```php
// In Easy Checkout controller
$this->log->write('Easy Checkout Debug: ' . json_encode($data));
```

Logs are saved in: `system/storage/logs/error.log`

#### View Session Data

```php
// Output all Easy Checkout data from session
echo '<pre>';
print_r($this->session->data);
echo '</pre>';
exit;
```

#### Check Loaded Shipping Methods

```php
$this->load->controller('extension/aw_easy_checkout/shipping_method');
echo '<pre>';
print_r($this->session->data['shipping_methods']);
echo '</pre>';
```

---

## Troubleshooting and Recommendations

### Common Problems

#### Problem 1: Checkout Page Not Opening (404 Error)

**Causes:**
- OCMOD modification not applied
- Modification cache not refreshed
- SEO URL configured incorrectly

**Solution:**
1. Refresh modification cache: **Extensions → Modifications → Refresh**
2. Verify that `alexwaha.com - Easy Checkout` modification is active in modifications list
3. Check SEO URL on "General" tab
4. Try disabling and re-enabling module

#### Problem 2: Blocks Not Displaying on Page

**Causes:**
- Blocks disabled in builder
- Conflict with theme
- JavaScript errors on page

**Solution:**
1. Check block status on **"Page Builder"** tab
2. Make sure blocks have correct position
3. Open browser console (F12) and check for JavaScript errors
4. Try temporarily disabling custom JavaScript on "Javascript" tab
5. Check compatibility with your theme

#### Problem 3: Phone Mask Not Working

**Causes:**
- Mask type set to "Disabled"
- Maska.js library not loading
- Conflict with other scripts

**Solution:**
1. Check mask type on **"General"** tab → should be "Static" or "Dynamic"
2. Make sure file `/catalog/view/javascript/aw_easy_checkout/maska.min.js` exists
3. Open browser console (F12) and check script loading
4. Verify correct mask format (tokens `#`, `@`, `*`)

#### Problem 4: Shipping Methods Not Displaying

**Causes:**
- Shipping methods not configured in OpenCart
- Country and region not selected
- Shipping methods filtered by countries
- Maximum weight or order amount exceeded

**Solution:**
1. Check that shipping methods are installed and enabled: **Extensions → Shipping**
2. Make sure "Country" and "Region" fields are enabled and required
3. Check country filters on **"Shipping Methods"** tab
4. Check geo-zone settings for shipping methods
5. Make sure product weight and dimensions don't exceed limits

#### Problem 5: Payment Methods Not Displaying

**Causes:**
- Payment methods not configured
- User type restriction (guest/authorized)
- Shipping method restriction
- Minimum order amount conditions not met

**Solution:**
1. Check that payment methods are installed and enabled: **Extensions → Payment**
2. Check visibility settings on **"Payment Methods"** tab:
   - Available for guests/authorized users
   - Compatible with selected shipping method
3. Make sure order amount exceeds minimum for payment method

#### Problem 6: Custom Fields Not Saving in Order

**Causes:**
- "Save field value to order comment" option disabled
- Field is optional and not filled
- Field validation error

**Solution:**
1. Open custom field settings
2. Enable **"Save field value to order comment"** option
3. Check that field is filled on frontend
4. Verify correct regex validation

#### Problem 7: JavaScript Code Not Executing

**Causes:**
- Syntax error in code
- Conflict with other scripts
- jQuery not loaded

**Solution:**
1. Open browser console (F12) → "Console" tab
2. Check for errors in red
3. Fix syntax errors
4. Wrap code in:
   ```javascript
   $(document).ready(function() {
       // Your code
   });
   ```

#### Problem 8: Settings Import Not Working

**Causes:**
- File damaged or wrong format
- Insufficient user rights
- Module version incompatibility

**Solution:**
1. Make sure you're importing file exported by Easy Checkout module
2. Check that file has `.json` extension
3. Open file in text editor and verify it's valid JSON
4. Log in with administrator account
5. Try exporting and immediately importing for verification

#### Problem 9: Checkout Page Displays Incorrectly

**Causes:**
- CSS style conflict with theme
- Incorrect column width
- Module CSS files missing

**Solution:**
1. Check that CSS files load:
   - `/catalog/view/javascript/aw_easy_checkout/base.min.css`
   - `/catalog/view/javascript/aw_easy_checkout/select2.min.css`
2. Try enabling **"Use Alternative Theme"** option
3. Change column width on **"General"** tab
4. Check browser console for CSS errors

#### Problem 10: Data Not Saving After Order Placement

**Causes:**
- Server validation error
- Session timeout
- Conflict with other modules

**Solution:**
1. Check error logs: `system/storage/logs/error.log`
2. Make sure all required fields are filled
3. Increase session lifetime in PHP settings
4. Temporarily disable other modules to check for conflicts
5. Verify correct payment method settings

### Usage Recommendations

#### Recommendation 1: Configure Phone Mask
Always configure phone mask (static or dynamic) so numbers are saved in uniform format.

#### Recommendation 2: Use Minimum Required Fields
Don't make fields required that aren't critical for delivery. Fewer fields = higher conversion.

**Minimum Set:**
- First Name
- Phone
- Country
- City
- Address

#### Recommendation 3: Add Descriptions to Payment Methods
Explain payment method details to customers to reduce abandonment.

#### Recommendation 4: Test on Mobile Devices
Always check checkout functionality on smartphones and tablets.

#### Recommendation 5: Regularly Export Settings
Create setting backups before any changes.

#### Recommendation 6: Use Custom Fields to Improve Service
Add fields:
- Desired delivery time
- Courier instructions
- Intercom code
- Floor, entrance

#### Recommendation 7: Configure Minimum Order Amount
Set minimum order amount to optimize logistics and prevent unprofitable orders.

#### Recommendation 8: Use Analytics
Add event sending to Google Analytics via "JavaScript" tab to track funnel.

#### Recommendation 9: Optimize Layout for Your Audience
Experiment with block placement. For example:
- B2C: cart at bottom, focus on quick completion
- B2B: cart at top, more company fields

#### Recommendation 10: Test Entire Checkout Process
Regularly go through entire checkout process as customer to identify issues.

### Getting Support

If you can't solve the problem yourself:

1. **Collect Information:**
   - OpenCart version
   - Easy Checkout module version
   - Problem description
   - Error screenshots
   - Contents of `system/storage/logs/error.log` file

2. **Check Documentation:**
   - Re-read relevant documentation section
   - Check FAQ on author's website

3. **Contact Support:**
   - Telegram: [@alexwaha_dev](https://t.me/alexwaha_dev)
   - Email: [support@alexwaha.com](mailto:support@alexwaha.com)
   - Bug report: [https://alexwaha.com/bug-report](https://alexwaha.com/bug-report)

> **Important:** Technical support for this module is available on paid basis only. Bug fixes in main repository are done without schedule, as developer has free time.

> **Pull Requests Welcome:** If you can offer solution to any problem, pull-request on [GitHub](https://github.com/AlexWaha/opencart-bundle/pulls) is welcome.

---

## License and Contacts

### License

This project is distributed under [GNU General Public License version 3 (GPLv3)](https://github.com/alexwaha/opencart-bundle/blob/master/LICENSE).

Rights and authorship of this software belong to developer:
**Alexander Vakhovski (Oleksandr Vakhovskyi)**

Also known as:
- Alexwaha
- Ocdev.pro

Official website: [https://alexwaha.com](https://alexwaha.com)

### Terms of Use

- ✓ Free use in commercial projects
- ✓ Code modification for your needs
- ✓ Distribution of modified versions (with GPLv3 license)
- ✗ Removal or modification of author information

### Disclaimer

Module tested on clean **OpenCart** installation and default theme (Default).

Author is not responsible for:
- Conflicts with third-party themes
- Conflicts with other modules
- Problems arising from module code modification
- Data loss from improper use

> **Recommendation:** Always test module on test server before installing on production site.

### Paid Support

Module setup by author, customization, conflict resolution with other modules, template integration, integration with another module in your project - on **paid basis** only.

**Services:**
- Module installation and configuration
- Website design integration
- Conflict resolution with other extensions
- Functionality customization to your requirements
- Usage consultations

### Contacts

**Telegram:**
[@alexwaha_dev](https://t.me/alexwaha_dev)

**Email:**
[support@alexwaha.com](mailto:support@alexwaha.com)

**Report a Bug:**
[https://alexwaha.com/bug-report](https://alexwaha.com/bug-report)

**GitHub:**
[https://github.com/AlexWaha/opencart-bundle](https://github.com/AlexWaha/opencart-bundle)

---

### Acknowledgments

Thanks to all module users for feedback, bug reports and improvement suggestions!

---

**Alexwaha.com - Easy Checkout for OpenCart**

---
