# Alexwaha.com - Viewed Products

> **Live demo:** see this module on the live demo store - [demo.alexwaha.com/module-viewed-products](https://demo.alexwaha.com/module-viewed-products)

**Module for OpenCart v2.3 - 3.x**

---

## Description

Viewed Products shows each visitor the products they have recently looked at. Every product page view is tracked automatically, and the history follows the visitor from a guest cookie into their customer account after login.

The module has two storefront parts:

1. **Recently Viewed block** - a layout-assignable widget (place it on the product page, home, or anywhere via Design -> Layouts). It renders in the native theme card style and always loads its products over **AJAX**, so it never poisons full-page cache and never shows another visitor's history. The currently opened product is excluded automatically.
2. **Viewed Products account page** - a dedicated page in the customer account (`Viewed Products`) listing the full history with pagination. It also loads over AJAX (single mode - the legacy server-side-render mode was removed for performance), and is excluded from indexing with a `noindex` robots tag.

A logged-in customer can remove any product from the history (the delete button on each card). Guest history is merged onto the customer on login.

---

## Installation Instructions

1. **Install Core Module**

   Make sure you have installed the module **[aw_core_oc2.3-3.x.ocmod.zip](https://github.com/AlexWaha/opencart-bundle/blob/main/Core/dist/aw_core_oc2.3_3.x.ocmod.zip)**

   > This is an auxiliary function for all my modules. **Critically required**, the module will not work without it

2. **FTP Upload Settings (OpenCart 2.x only)**

   Make sure FTP uploads for extensions are allowed (or your FTP settings are correct) on OpenCart 2.x. **(Not required for OpenCart 3.x)**

3. **Install module files aw_viewed_oc2.3-3.x.ocmod.zip**
4. **Enable the Module**

   Go to: **Extensions -> Modules** -> Install and edit the module **alexwaha.com - Viewed Products**

   > On install the module creates its database table, grants view/edit permissions (to the current user group and the Administrator group) and registers its storefront events. No modification cache refresh is required - the module works purely through the OpenCart event system.

5. **Place the widget (optional)**

   Go to **Design -> Layouts**, open the layout where the block should appear (e.g. Product), and add the module **alexwaha.com - Viewed Products** to a position (e.g. Content Bottom).

### Self-Check After Installation

- Open **Extensions -> Modules -> alexwaha.com - Viewed Products** - the settings form opens with the Widget / Tracking / Account Page tabs
- Browse a few product pages as a guest, then open the **Viewed Products** account page - the products should be listed
- On a product page where the widget is placed, the block shows your recently viewed products and excludes the current one

---

## Usage

The settings screen (**Extensions -> Modules -> alexwaha.com - Viewed Products**) has three tabs.

### Widget tab (per block instance)

- **Module Name** - admin label for this widget instance.
- **Block Title** - heading shown above the block (per language). Empty = default "Viewed Products".
- **Products Limit** - how many products the block shows.
- **Image Width / Height** - thumbnail size.
- **Show "View All" Link** - show a link to the account page in the block header.
- **Status** - enable / disable this widget instance.

### Tracking tab (global)

- **Storage Days** - how many days the history is kept (also the guest cookie lifetime).
- **Max Stored Products** - maximum number of products stored per visitor (older ones are trimmed).

### Account Page tab (global)

- **Enable Account Page** - turn the dedicated page on/off (off = the page returns 404).
- **Show Account Menu Link** - inject a `Viewed Products` link into the customer account (both the account dashboard list and the account menu module).
- **Menu Link Text** - the link label (per language). Empty = default "Viewed Products".
- **SEO Keyword** - friendly URL for the page, per store and language (e.g. `viewed-products`). Leave empty to use the default `index.php?route=...` URL. The page is always excluded from indexing (`noindex,follow`).

---

## For Developers

### Technical Overview

The module is built entirely on the **OpenCart event system** - no OCMOD file modifications. Events are registered on install (via `setting/event` on 3.x, `extension/event` on 2.3, chosen automatically through `awCore->isLegacy()`):

| Trigger | Action | Purpose |
|---|---|---|
| `catalog/controller/product/product/before` | `extension/module/aw_viewed/track` | Records the viewed product (once per GET request, only for a real/enabled product) |
| `catalog/controller/account/account/before` | `extension/module/aw_viewed/accountLogin` | Merges the guest cookie history onto the customer on login |
| `catalog/view/account/account/after` | `extension/module/aw_viewed/accountList` | Injects the link into the account dashboard "My Account" list |
| `catalog/view/extension/module/account/after` | `extension/module/aw_viewed/accountMenu` | Injects the link into the account menu module (sidebar) |

### Frontend (AJAX only)

Both the widget and the account page render only a shell server-side and fill the products through AJAX:

- Widget products: `extension/module/aw_viewed/products`
- Account page list: `extension/module/aw_viewed_page/list`
- Delete an item: `extension/module/aw_viewed/delete` (logged-in customers only)

The page shell is therefore safe to full-page cache; per-visitor data is loaded client-side with a small vanilla-JS script (no jQuery dependency).

### Database

- `oc_aw_viewed` - one row per viewed product: `session_token` (guest cookie), `customer_id`, `product_id`, `store_id`, `date_added`. De-duplicated per visitor, trimmed to **Max Stored Products**, cleaned by **Storage Days**.

Module configuration is stored by Core in `oc_aw_module_config` (key `aw_viewed`); the widget instance settings use the native `oc_module` table; the page SEO keyword uses the native `oc_seo_url` table.

### File Locations

- **Widget + events controller:** `catalog/controller/extension/module/aw_viewed.php`
- **Account page controller:** `catalog/controller/extension/module/aw_viewed_page.php`
- **Catalog model (tracking/queries):** `catalog/model/extension/module/aw_viewed.php`
- **Admin controller / model:** `admin/.../extension/module/aw_viewed.php`

> **Critical Requirement:** Use of the module is only possible together with the system module **alexwaha.com - Core**

---

## License

This project is licensed under the [GNU General Public License version 3 (GPLv3)](https://github.com/alexwaha/opencart-bundle/blob/main/LICENSE).

The rights and authorship of this software belong to the developer Oleksandr Vakhovskyi (Alexwaha), website [https://alexwaha.com](https://alexwaha.com)

---

## Support and Contacts

> **Attention!** Technical support for this module is only available on a paid basis. Bug fixes in the main repository are done without a schedule as long as the developer has free time. If you can offer a solution to any problem, a [pull-request on GitHub](https://github.com/AlexWaha/opencart-bundle/pulls) is welcome.

**Paid Services:** Setting up the module by the author, customization, resolving conflicts with other modules, integration with a template, integration with another module in your project - only on a **paid basis**, please contact us below.

**Tested on:** Clean OpenCart installation and on the default theme (Default).

### Contact Information

- **Telegram:** [@alexwaha_dev](https://t.me/alexwaha_dev)
- **Email:** [support@alexwaha.com](mailto:support@alexwaha.com)
- **Bug Report:** [GitHub Issues](https://github.com/AlexWaha/opencart-bundle/issues)
- **Contact Form:** [https://alexwaha.com/contact](https://alexwaha.com/contact)

---

Made with ☕ by [Alexwaha.com](https://alexwaha.com)
