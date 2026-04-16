# Documentation | Alexwaha.com - E-commerce Tracking (GA4)

**for Opencart v2.3 - 3.x**

---

## Table of Contents

1. [Module Information](#module-information)
2. [Installation Guide](#installation-guide)
3. [Module Settings](#module-settings)
4. [GTM Integration](#gtm-integration)
5. [GA4 Events Reference](#ga4-events-reference)
6. [Diagnostics](#diagnostics)
7. [Import / Export](#import--export)
8. [For Developers](#for-developers)
9. [Troubleshooting](#troubleshooting)
10. [License and Contacts](#license-and-contacts)

---

## Module Information

**E-commerce Tracking (GA4)** - OpenCart module implementing full e-commerce tracking according to Google Analytics 4 standard. Automatically sends all e-commerce events to dataLayer for Google Tag Manager or gtag.js.

### Key Features:

- **Full GA4 E-commerce Support** - All recommended Google Analytics 4 events
- **Event-Based Integration** - Uses OpenCart event system, no OCMOD template modifications
- **Universal Compatibility** - Works with any theme, any checkout (standard, AW Easy Checkout)
- **Flexible Configuration** - Enable/disable each event individually
- **Page Tracking** - Categories, search, manufacturers, specials, products
- **Module Tracking** - Featured, latest, bestsellers, specials
- **Purchase Funnel** - From product view to purchase completion with F5 protection
- **Search Event** - GA4 `search` event with `search_term` parameter
- **Diagnostics** - Built-in validator checks all events are registered correctly
- **Import / Export** - Backup and restore module settings as JSON
- **Debug Mode** - Output events to browser console

### Technical Specifications:

- **Author:** Alexander Vakhovski (AlexWaha)
- **Website:** [https://alexwaha.com](https://alexwaha.com)
- **License:** GPLv3
- **Compatibility:** OpenCart 2.3.x - 3.x
- **Module Code:** `aw_ecommerce_tracking`
- **Integration:** OpenCart Event System (17 events registered at install)

---

## Installation Guide

### Prerequisites

1. Install **[aw_core_oc2.3-3.x.ocmod.zip](https://github.com/AlexWaha/opencart-bundle/blob/master/Core/dist/aw_core_oc2.3_3.x.ocmod.zip)**

2. For Opencart 2.3.x: FTP disable module or correct FTP settings

### Installation

1. Upload **aw_ecommerce_tracking_oc2.3-3.x.ocmod.zip** via **Extensions → Installer**

2. Enable module: **Extensions → Modules → alexwaha.com - E-commerce Tracking (GA4) → Install**

3. Open module settings and configure GTM/gtag.js code

4. Verify installation via **Diagnostics** tab — all 17 events should show "OK"

### Important Notes

- **No OCMOD refresh needed** — the module uses OpenCart events, not OCMOD modifications
- **No template edits needed** — works with any theme out of the box
- If upgrading from OCMOD-based version: remove the old `aw_ecommerce_tracking` entry from Extensions → Modifications, then refresh modifications cache

---

## Module Settings

### Tab "General"

| Setting | Description |
|---------|-------------|
| **Status** | Enable/disable module |
| **GTM/gtag.js Code (Head)** | Google Tag Manager or gtag.js code for `<head>` |
| **GTM Code (Body)** | GTM noscript code for `<body>` |
| **Debug Mode** | Output events to browser console |

### Tab "Pages"

Tracking `view_item_list` and `view_item`:

| Setting | Event | Description |
|---------|-------|-------------|
| Category Pages | `view_item_list` | Product lists in categories |
| Search Results | `view_item_list` + `search` | Search page with search term |
| Manufacturer Pages | `view_item_list` | Brand pages |
| Special Pages | `view_item_list` | Special offers |
| Product Pages | `view_item` | Product card |
| Compare Page | `view_item_list` | Product comparison |

### Tab "Modules"

Tracking `view_item_list` for modules:

- Latest Module
- Featured Module
- Bestseller Module
- Special Module
- AW Viewed Module

### Tab "Checkout"

| Setting | Event | Description |
|---------|-------|-------------|
| Add to Cart | `add_to_cart` | Adding product |
| Remove from Cart | `remove_from_cart` | Removing product |
| View Cart | `view_cart` | Cart page / Easy Checkout |
| Begin Checkout | `begin_checkout` | Checkout page / Easy Checkout |
| Shipping Info | `add_shipping_info` | Shipping selection |
| Payment Info | `add_payment_info` | Payment selection |
| Purchase | `purchase` | Successful order (F5-protected) |

**Additional:**
- Include tax in prices
- Track shipping cost
- Track coupons/discounts

### Tab "Events"

| Setting | Event | Description |
|---------|-------|-------------|
| User Login | `login` | Authorization |
| Registration | `sign_up` | New user |
| Add to Wishlist | `add_to_wishlist` | Wishlist |
| Select Item | `select_item` | Click on product in list |
| Apply Coupon | `add_coupon` / `add_voucher` | Discount codes |

### Tab "Advanced"

| Setting | Description |
|---------|-------------|
| Currency | Session or store currency |
| Prices with Tax | Include tax in prices |
| Send Product Options | Add `item_variant` |
| Custom Parameters | JSON with additional data |

---

## GTM Integration

### Google Tag Manager Setup

1. **DataLayer Variable:**
   - Variables → New → Data Layer Variable
   - Name: `ecommerce`, Version 2

2. **Trigger:**
   - Triggers → New → Custom Event
   - Event name: `view_item_list|view_item|select_item|add_to_cart|remove_from_cart|view_cart|begin_checkout|add_shipping_info|add_payment_info|purchase|search`
   - Use regex matching: ✓

3. **GA4 Event Tag:**
   - Tags → New → Google Analytics: GA4 Event
   - Event Name: `{{Event}}`
   - Event Parameters: `ecommerce` → `{{ecommerce}}`

### Verification

1. Enable debug mode in module
2. Open GTM Preview
3. Check events in console and GTM Preview

---

## GA4 Events Reference

| Event | When Triggered | Type |
|-------|----------------|------|
| `view_item_list` | Product list view | PHP |
| `view_item` | Product card view | PHP |
| `search` | Search page (with search_term) | PHP |
| `select_item` | Click on product in list | JS |
| `add_to_cart` | Add to cart | JS |
| `remove_from_cart` | Remove from cart | JS |
| `view_cart` | Cart view | PHP |
| `begin_checkout` | Begin checkout | PHP |
| `add_shipping_info` | Shipping selection | JS |
| `add_payment_info` | Payment selection | JS |
| `purchase` | Successful order | PHP |
| `login` | Account login | PHP |
| `sign_up` | Registration | PHP |
| `add_to_wishlist` | Add to wishlist | JS |
| `add_coupon` | Apply coupon | JS |

**Type:** PHP = on page load via event system, JS = on user action via client-side hooks

---

## Diagnostics

The **Diagnostics** tab (first tab in module settings) provides real-time validation:

### Event Registration Check
- Validates all 17 OpenCart events are registered in `oc_event` table
- Shows each event trigger with OK/MISSING status
- If events are missing: reinstall the module (Extensions → Modules → Uninstall → Install)

### Configuration Check
- Verifies GTM/gtag.js code is configured
- Verifies module status is enabled
- Clickable links to navigate to relevant settings tab

### Badge Indicator
- Green "OK" badge on Diagnostics tab = no issues
- Red badge with number = count of problems found

---

## Import / Export

The **Import / Export** tab allows backup and restore of module settings:

### Export
- Downloads current configuration as a JSON file
- Filename format: `aw_ecommerce_tracking_settings_YYYY-MM-DD_HH-MM-SS.json`

### Import
- Upload a previously exported JSON file
- Overwrites all current settings (confirmation required)
- Page auto-reloads after successful import

---

## For Developers

### How It Works

The module uses OpenCart's native event system instead of OCMOD modifications. During installation, 17 events are registered in the `oc_event` database table:

**Global events (every page):**
- `catalog/view/common/header/after` — injects GTM code + JS config into `<head>`
- `catalog/view/common/footer/after` — injects GTM body code + deferred login/signup events

**Page-specific events:**
- `catalog/view/product/category/after` — category page `view_item_list`
- `catalog/view/product/search/after` — search page `view_item_list` + `search`
- `catalog/view/product/manufacturer_info/after` — manufacturer page
- `catalog/view/product/special/after` — specials page
- `catalog/view/product/product/after` — product page `view_item`

**Checkout events:**
- `catalog/view/checkout/cart/after` — standard cart `view_cart`
- `catalog/view/checkout/checkout/after` — standard checkout `begin_checkout`
- `catalog/controller/extension/aw_easy_checkout/main/after` — AW Easy Checkout `view_cart` + `begin_checkout`
- `catalog/view/common/success/after` — order success `purchase`

**Account events:**
- `catalog/controller/account/login/after` — sets login session flag
- `catalog/controller/account/register/after` — sets signup session flag

**Module events:**
- `catalog/view/extension/module/featured/after`
- `catalog/view/extension/module/latest/after`
- `catalog/view/extension/module/bestseller/after`
- `catalog/view/extension/module/special/after`

### Event Handler

All events are handled by `catalog/controller/extension/aw_ecommerce_tracking/event.php`. The handler:
1. Extracts `product_id` values from template `$data['products']`
2. Re-queries raw product data via model (numeric prices, tax_class_id, manufacturer)
3. Delegates to the business logic controller (`extension/module/aw_ecommerce_tracking`)
4. Injects the returned tracking HTML into `$output`

### JavaScript API

```javascript
// Add to cart
window.awEcommerceTracking.trackAddToCart({
    id: '42',
    name: 'iPhone 15',
    price: 999.00,
    brand: 'Apple',
    category: 'Smartphones'
}, 1);

// Remove from cart
window.awEcommerceTracking.trackRemoveFromCart(product, quantity);

// Select item
window.awEcommerceTracking.trackSelectItem(product, listName, listId, index);

// Apply coupon
window.awEcommerceTracking.trackCoupon('SALE10', 'coupon');

// Custom event
window.awEcommerceTracking.push({ event: 'custom', data: 'value' });
```

### PHP API

```php
// In controller
$tracking = $this->load->controller('extension/module/aw_ecommerce_tracking');

if ($tracking->isEnabled()) {
    $eventData = $tracking->prepareViewItemList($products, 'My List', 'my_list');
    $html = $tracking->renderDataLayer($eventData);
}
```

---

## Troubleshooting

### Events not sending

1. Open **Diagnostics** tab — check all events are registered (17/17 OK)
2. Check that module status is enabled
3. Check GTM code is in General settings
4. If events are missing: reinstall the module

### Error "awCore is not defined"

Install **aw_core_oc2.3-3.x.ocmod.zip** module

### Events duplicating

1. Check Extensions → Modifications for old `aw_ecommerce_tracking` OCMOD entry — delete it and refresh cache
2. Check that GTM code is only in module settings (not manually in template)

### add_to_cart not firing

1. Check console for errors
2. Ensure `cart.add()` exists on page
3. Use JS API for custom themes

### Purchase event fires on F5

The module has built-in protection — `purchase` event fires only once per order using a session flag. If you still see duplicates, check for old OCMOD modifications.

### AW Easy Checkout — no events

The module registers a dedicated controller event for Easy Checkout. If events are missing:
1. Check `aw_et_ec_main` in Diagnostics
2. Reinstall the module to register the event

---

## License and Contacts

### License

[GNU General Public License version 3 (GPLv3)](https://github.com/alexwaha/opencart-bundle/blob/master/LICENSE)

**Author:** Alexander Vakhovski (Oleksandr Vakhovskyi) / Alexwaha

### Contacts

- **Telegram:** [@alexwaha_dev](https://t.me/alexwaha_dev)
- **Email:** [support@alexwaha.com](mailto:support@alexwaha.com)
- **GitHub:** [https://github.com/AlexWaha/opencart-bundle](https://github.com/AlexWaha/opencart-bundle)
- **Contact us:** [https://alexwaha.com/contact](https://alexwaha.com/contact)

> Technical support available on paid basis. Pull Requests welcome.

---

**Alexwaha.com - E-commerce Tracking (GA4) for OpenCart**
