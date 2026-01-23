# Documentation | Alexwaha.com - E-commerce Tracking (GA4)

**for Opencart v2.3 - 3.x**

---

## Table of Contents

1. [Module Information](#module-information)
2. [Installation Guide](#installation-guide)
3. [Module Settings](#module-settings)
4. [GTM Integration](#gtm-integration)
5. [GA4 Events Reference](#ga4-events-reference)
6. [For Developers](#for-developers)
7. [Troubleshooting](#troubleshooting)
8. [License and Contacts](#license-and-contacts)

---

## Module Information

**E-commerce Tracking (GA4)** - OpenCart module implementing full e-commerce tracking according to Google Analytics 4 standard. Automatically sends all e-commerce events to dataLayer for Google Tag Manager or gtag.js.

### Key Features:

- **Full GA4 E-commerce Support** - All recommended Google Analytics 4 events
- **Flexible Configuration** - Enable/disable each event individually
- **Page Tracking** - Categories, search, manufacturers, specials, products
- **Module Tracking** - Featured, latest, bestsellers, specials
- **Purchase Funnel** - From product view to purchase completion
- **Debug Mode** - Output events to browser console
- **Compatibility** - Standard checkout, Simple Checkout, AW Easy Checkout

### Technical Specifications:

- **Author:** Alexander Vakhovski (AlexWaha)
- **Website:** [https://alexwaha.com](https://alexwaha.com)
- **License:** GPLv3
- **Compatibility:** OpenCart 2.3.x - 3.x
- **Module Code:** `aw_ecommerce_tracking`

---

## Installation Guide

### Prerequisites

1. Install **[aw_core_oc2.3-3.x.ocmod.zip](https://github.com/AlexWaha/opencart-bundle/blob/master/Core/dist/aw_core_oc2.3_3.x.ocmod.zip)**

2. For Opencart 2.3.x: FTP disable module or correct FTP settings

### Installation

1. Install **aw_ecommerce_tracking_oc2.3-3x.ocmod.zip** via **Extensions → Installer**

2. Refresh modifications cache: **Extensions → Modifications → Refresh**

3. Enable module: **Extensions → Modules → alexwaha.com - E-commerce Tracking (GA4)**

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
| Search Results | `view_item_list` | Search page |
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
| View Cart | `view_cart` | Cart page |
| Begin Checkout | `begin_checkout` | Checkout page |
| Shipping Info | `add_shipping_info` | Shipping selection |
| Payment Info | `add_payment_info` | Payment selection |
| Purchase | `purchase` | Successful order |

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
   - Event name: `view_item_list|view_item|select_item|add_to_cart|remove_from_cart|view_cart|begin_checkout|add_shipping_info|add_payment_info|purchase`
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

**Type:** PHP = on page load, JS = on user action

---

## For Developers

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

### Custom Module Integration

```php
// Controller
$data['awTracking'] = $this->load->controller(
    'extension/module/aw_ecommerce_tracking/viewItemList',
    [$products, 'Custom List', 'custom_list', 'track_module_custom']
);
```

```twig
{# Template #}
{{ awTracking|default('')|raw }}
```

---

## Troubleshooting

### Events not sending

1. Check that module is enabled
2. Check GTM code in settings
3. Refresh modifications cache

### Error "awCore is not defined"

Install **aw_core_oc2.3-3.x.ocmod.zip** module

### Events duplicating

Check that GTM code is only in module settings (not manually in template)

### add_to_cart not firing

1. Check console for errors
2. Ensure `cart.add()` exists on page
3. Use JS API for sending

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
