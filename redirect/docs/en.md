# Alexwaha.com - Redirect Manager

**Module for OpenCart v2.3 - 3.x**

---

## Description

Redirect Manager handles URL redirects and catches broken links. It is built for store migrations (domain moves, or moving from Shopify / PrestaShop / a custom engine / plain HTML to OpenCart) where the old URLs are unpredictable.

Two tools in one module:

1. **Redirect Rules** - a managed list of `Source URL -> Target URL` with a redirect code (301 / 302 / 410) and an on/off status. Rules fire for both live and dead URLs, served from a hot cache (no database query on a cache hit).
2. **404 Resolver** - every URL that returns a 404 is logged automatically (deduplicated, with a hit counter and "last seen" time). From the log the admin can create a redirect in one click, or send selected URLs to the homepage.

### Matching

- **Exact** match: `/old-page`.
- **Wildcard** match with `*`: `/blog/*` -> `/news`. A source containing `*` is treated as a wildcard automatically.
- **Query string**: by default only the path is matched. Enable "Match Query String" per rule to match legacy non-SEF URLs such as `catalog.php?id=5` (query parameters are order-independent).
- Matching is **case-insensitive** and **trailing-slash-insensitive**. A built-in loop guard prevents a rule whose target equals its source.

### Codes

- **301** Moved Permanently (default)
- **302** Found (temporary)
- **410** Gone - no redirect, returns a "gone" page (target not required)

---

## Installation Instructions

1. **Install Core Module**

   Make sure you have installed the module **[aw_core_oc2.3-3.x.ocmod.zip](https://github.com/AlexWaha/opencart-bundle/blob/master/Core/dist/aw_core_oc2.3_3.x.ocmod.zip)**

   > This is an auxiliary function for all my modules. **Critically required**, the module will not work without it

2. **FTP Upload Settings (OpenCart 2.x only)**

   Make sure FTP uploads for extensions are allowed (or your FTP settings are correct) on OpenCart 2.x. **(Not required for OpenCart 3.x)**

3. **Install module files aw_redirect_oc2.3-3.x.ocmod.zip**
4. **Enable the Module**

   Go to: **Extensions → Modules** → Enable the module **alexwaha.com - Redirect Manager**

   > On enable the module creates its database tables, grants view/edit permissions and registers its storefront events. No modification cache refresh is required - the module works purely through the OpenCart event system.

### Self-Check After Installation

- Open **Extensions → Modules → alexwaha.com - Redirect Manager** - the rules list opens
- Add a test redirect, open the source URL in a fresh browser tab - it should redirect to the target
- Open an unknown URL, then check the **404 Resolver** - the URL should appear there

---

## Usage

Once enabled, the module adds a **Redirect Manager** item to the admin left menu with three sections: **Redirect Rules**, **404 Resolver** and **Settings** (the item is hidden while the module is disabled). You can also open it from **Extensions → Modules**.

### Redirect Rules

The main page lists all rules (source, target, type, code, hit counter, status). Use the filter bar to search by source/target, type or status.

**Add / Edit a rule:**

- **Source URL** - path to match, e.g. `/old-page` or `/blog/*`.
- **Match Query String** - include the query string in the match.
- **Target URL** - relative path (`/new-page`) or absolute URL (`https://...`). Not required for code 410.
- **Redirect Code** - 301 / 302 / 410.
- **Store** - all stores, or a specific store.
- **Status** - enable / disable the rule.

### 404 Resolver

URLs that returned a 404 are collected here (deduplicated, with a hit counter). For each row you can:

- **Redirect** - opens the Add form with the source pre-filled.
- **Redirect to Homepage** - bulk-creates 301 redirects to `/` for the selected rows.
- **Delete** / **Clear Log** - remove entries.

### Settings

- **Status** - master switch for the whole module on the storefront.
- **Default Code** - pre-selected code when adding a new redirect.
- **Log 404 Errors** - turn automatic 404 logging on/off.
- **Ignore Patterns** - one pattern per line (wildcards allowed); matching 404 URLs are not logged (defaults filter out bot noise like `*.php`, `/wp-*`).
- **Import / Export** - export all rules to CSV, or bulk-import rules from a CSV file (`source,target,status_code,match_query,store_id,status`).

---

## For Developers

### Technical Overview

The module is built entirely on the **OpenCart event system** - no OCMOD file modifications.

Two catalog events are registered on install:

| Trigger | Action | Purpose |
|---|---|---|
| `catalog/controller/*/before` | `extension/module/aw_redirect/redirect` | Matches the request against active rules (run once per request) and issues the redirect |
| `catalog/controller/error/not_found/before` | `extension/module/aw_redirect/notFound` | Logs the missing URL (chosen over reading the resolved route so it works under SeoPro too) |

### Performance

Active rules are compiled into a single **hot cache** entry (`aw_redirect.map`) via the OpenCart cache (file or Redis, whichever the store uses). A cache hit performs **zero database queries**: an O(1) exact-hash lookup plus an in-memory pass over the (few) wildcard rules. The cache is rebuilt automatically after any change. For very large rule sets (> 5000 exact rules) the module falls back to an indexed point query on a `source_hash` column instead of caching the whole map.

### Database

- `oc_aw_redirect` - rules (`source`, `source_hash`, `target`, `match_type`, `match_query`, `status_code`, `store_id`, `status`, `hits`).
- `oc_aw_redirect_404` - 404 log, deduplicated by `url_hash` with a `hits` counter.

### File Locations

- **Event controller:** `catalog/controller/extension/module/aw_redirect.php`
- **Matching / cache model:** `catalog/model/extension/module/aw_redirect.php`
- **Admin controller / model:** `admin/.../extension/module/aw_redirect.php`

> **Critical Requirement:** Use of the module is only possible together with the system module **alexwaha.com - Core**

---

## License

This project is licensed under the [GNU General Public License version 3 (GPLv3)](https://github.com/alexwaha/opencart-bundle/blob/master/LICENSE).

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
