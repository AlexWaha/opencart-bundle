# Alexwaha.com - GDPR Consent

**Module for OpenCart v2.3 - 3.x**

---

## Description

This module adds a GDPR / ePrivacy cookie consent banner with full **Google Consent Mode v2** support.

The banner is shown as a Cookiebot-style bar at the bottom of the page (not a centered popup). The visitor can accept all cookies, allow a selection, or deny non-essential cookies. After a choice is made the bar is replaced by a small floating widget that lets the visitor reopen the settings at any time.

Four consent categories are mapped to the seven Google Consent Mode v2 signals, so analytics and advertising tags receive the correct `granted` / `denied` state.

| Category (UI) | Google Consent Mode v2 signals |
|---|---|
| Necessary (always on) | `security_storage`, `functionality_storage` |
| Preferences | `personalization_storage` |
| Statistics | `analytics_storage` |
| Marketing | `ad_storage`, `ad_user_data`, `ad_personalization` |

By default every non-essential category is **off** (GDPR opt-in).

---

## Installation Instructions

### Requirements and Installation Steps

1. **Install Core Module**

   Make sure you have installed the module **[aw_core_oc2.3-3.x.ocmod.zip](https://github.com/AlexWaha/opencart-bundle/blob/main/Core/dist/aw_core_oc2.3_3.x.ocmod.zip)**

   > This is an auxiliary function for all my modules. **Critically required**, the module will not work without it

2. **FTP Upload Settings (OpenCart 2.x only)**

   Make sure you have a module installed that disables FTP uploads for extensions in OpenCart 2.x, or that your FTP settings in the store config are correct

   > [Link to module disabling FTP upload for OpenCart v2.3 only](https://www.opencart.com/index.php?route=extension/extension/info&extension_id=18892) **(Not required for OpenCart 3.x)**

3. **Install module files aw_gdpr_consent_oc2.3-3.x.ocmod.zip**
4. **Enable the Module**

   Go to: **Extensions → Modules** → Enable the module **alexwaha.com - GDPR Consent**

   > When enabled, the module automatically grants view/edit permissions and registers its storefront events. No modification cache refresh is required - the module works purely through the OpenCart event system.

### Self-Check After Installation

- Check for the module in the list of extensions
- Enable and configure the module
- Open the storefront in a fresh browser session (clear cookies first) - the consent bar must appear at the bottom

---

## Configuration

### Module Settings

Go to **Extensions → Modules → alexwaha.com - GDPR Consent**.

**General tab:**

- **Status:** Enable / disable the consent banner
- **Banner Title:** Heading shown on the bar, per language
- **Banner Text:** Explanatory text shown on the bar, per language (rich text editor)
- **Cookie Policy Page:** Information page linked as your cookie / privacy policy ("Read more")
- **Cookie Storage Days:** How long the visitor choice is remembered (1-730 days, default 365)

**Appearance tab:**

- **Theme:** Light or dark colour scheme for the bar
- **Accent Color:** Colour of the primary button, links and toggles

Save the configuration. The banner appears on the storefront until the visitor makes a choice.

---

### Visitor Interaction Flow

1. **First Visit:** the consent bar appears at the bottom of the page. Google Consent Mode v2 defaults are set to `denied` (except the necessary signals) **before** any analytics tag runs.
2. **Accept all:** every category is granted, signals are updated to `granted`, the choice is stored in cookies.
3. **Allow selection:** only the categories the visitor enabled are granted.
4. **Deny:** only the necessary category stays granted.
5. **Return Visits / Reopen:** once a choice exists the bar is hidden and a floating widget is shown in the bottom-left corner. Clicking it reopens the bar so the visitor can change the decision.

### Features

- Cookiebot-style bottom bar with a floating reopen widget
- Google Consent Mode v2 (`gtag('consent', 'default' / 'update', ...)`)
- Four consent categories mapped to the seven Google signals
- Light / dark theme and configurable accent colour
- Multilingual title and text
- Works on OpenCart 2.3 and 3.x

> **Note:** This module manages the consent signals only. The Google Tag / GA4 / Ads tags themselves are added separately (theme, another module or the OpenCart analytics section).

---

## For Developers

### Technical Overview

The module is built entirely on the **OpenCart event system** - no OCMOD file modifications are used.

Two events are registered on install:

| Trigger | Action | Purpose |
|---|---|---|
| `catalog/view/common/header/after` | `extension/module/aw_gdpr_consent/head` | Injects the Consent Mode v2 default state + assets near the top of `<head>` |
| `catalog/view/common/footer/after` | `extension/module/aw_gdpr_consent/footer` | Injects the consent bar + reopen widget before `</body>` |

Both handlers modify the rendered output string, so they work the same on OpenCart 2.3 and 3.x.

### File Locations

- **Storefront template:** `catalog/view/theme/default/template/extension/module/aw_gdpr_consent.twig`
- **Head template:** `catalog/view/theme/default/template/extension/module/aw_gdpr_consent_head.twig`
- **CSS styles:** `catalog/view/javascript/aw_gdpr_consent.css`
- **JavaScript:** `catalog/view/javascript/aw_gdpr_consent.js`

### Key Components

- **Event-based injection** - no template modifications, no modification cache
- **Consent Mode v2** - default state restored from cookies on repeat visits
- **Per-signal cookies** - `ad_storage`, `analytics_storage`, etc., read by the head script
- **Asset versioning** - `?v=filemtime` cache-busting on CSS / JS

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
