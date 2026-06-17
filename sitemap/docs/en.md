# Manual | Alexwaha.com - Unlimited Sitemap

**for Opencart v2.3 - 3.x**

---

## Table of Contents

1. [Module Information](#module-information)
2. [Installation Instructions](#installation-instructions)
3. [General Settings](#general-settings)
4. [Providers](#providers)
5. [Generation Modes](#generation-modes)
6. [Static Generation via Cron](#static-generation-via-cron)
7. [Server Rewrite (dynamic mode)](#server-rewrite-dynamic-mode)
8. [Product Images](#product-images)
9. [For Developers - Writing a Provider](#for-developers---writing-a-provider)
10. [Possible Errors and Recommendations](#possible-errors-and-recommendations)
11. [License and Contacts](#license-and-contacts)

---

## Module Information

**Unlimited Sitemap** generates an XML sitemap for OpenCart that scales to very large catalogs. The standard OpenCart sitemap fails on big stores because it resizes every product image during generation; this module never resizes at generation time and splits the sitemap into shards (`sitemapindex` + child files), so it keeps working on 50,000+ product catalogs.

### Key Features

- **Two generation modes** - dynamic (on request, single file) and static (cron, sharded files on disk)
- **Sharding** - large catalogs are split into multiple sitemap files referenced from a `sitemapindex`
- **Image-cache safety** - product images are added only when the resized thumbnail already exists in `image/cache`; the module never triggers a resize during generation
- **Provider adapters** - each section of the sitemap is a small drop-in file, so you can add your own entities (blog, news, landing pages) without touching the core
- **Multi-language** - generates a separate index and shards per store language
- **Optional cache** - dynamic mode can cache the generated XML with a configurable TTL
- **robots.txt helper** - shows a ready `Sitemap:` line and server rewrite snippets

### Technical Specifications

- **Author:** Alexander Vakhovski (AlexWaha)
- **Website:** [https://alexwaha.com](https://alexwaha.com)
- **License:** GPLv3
- **Compatibility:** OpenCart 2.3.x - 3.x
- **Module Code:** `aw_sitemap`

---

## Installation Instructions

### Step 1: Prerequisites

1. Make sure you have the **[aw_core_oc2.3-3.x.ocmod.zip](https://github.com/AlexWaha/opencart-bundle/blob/main/Core/dist/aw_core_oc2.3_3.x.ocmod.zip)** module installed.

   > **Important:** This is shared helper functionality required by all AlexWaha modules.

2. For OpenCart 2.3.x, make sure FTP upload of extensions is disabled (or your store FTP settings are correct).

   > **Note:** This is not required for OpenCart 3.x.

### Step 2: Module Installation

1. Install the **aw_sitemap_oc2.3-3.x.ocmod.zip** archive through the admin panel:
   - Go to **Extensions → Installer**
   - Upload the module archive
   - Wait for installation to complete

2. Refresh the modification cache:
   - Go to **Extensions → Modifications**
   - Click the **Refresh** button

3. In **Extensions → Feeds**, find and enable the module:

   **alexwaha.com - Unlimited Sitemap**

   > **Important:** When enabled, the module sets the required access permissions.

### Step 3: Self-check After Installation

- ✓ Module is present in the feeds list
- ✓ Module settings page opens without errors
- ✓ The **Providers** tab lists the built-in sections (Products, Categories, Manufacturers, Information pages, Home page, Landing pages)

---

## General Settings

Go to module settings: **Extensions → Feeds → alexwaha.com - Unlimited Sitemap**

### Status
- **Enabled** - the module is active
- **Disabled** - generation is blocked

### Generation mode
- **Dynamic (on request)** - the sitemap is built on each request and returned as a single file. Best for small catalogs.
- **Static (cron, sharded)** - the sitemap is written to disk as sharded files via a cron command. Best for large catalogs.

> When the mode is Dynamic and your product count exceeds the configured limit, the page shows a warning recommending Static mode.

### Output folder
Public folder at the store root where static files are written.

**Default:** `sitemap`

**Requirements:** 2 to 64 characters, letters, digits, dash, underscore. Created automatically on first generation.

### URLs per shard
Maximum number of URLs in a single sitemap file in static mode.

**Default:** 5000 · **Range:** 100 - 50000

> The sitemap protocol allows up to 50,000 URLs per file. 5,000 keeps each file small and fast to serve.

### Product images
Toggle whether `<image:image>` entries are included for products. Only images already cached in `image/cache` are added. See [Product Images](#product-images).

### Languages
Languages to generate. Leave all unchecked to generate every store language.

### Cache (dynamic mode)
When enabled, the generated XML is cached in dynamic mode and served until the lifetime expires.

### Cache lifetime, sec
How long the dynamic cache stays valid. **Default:** 3600

### Dynamic mode product limit
The threshold for the Dynamic-mode warning. **Default:** 1000

---

## Providers

Open the **Providers** tab to see every section that contributes URLs to the sitemap. Each provider can be enabled or disabled.

Built-in providers:

| Provider | Code | URLs it adds |
|---|---|---|
| Products | `product` | `product/product` pages (sharded) |
| Categories | `category` | `product/category` pages |
| Manufacturers | `manufacturer` | `product/manufacturer/info` pages |
| Information pages | `information` | `information/information` pages |
| Home page | `home` | store home page |
| Landing pages | `landing` | `aw_landing_page` pages (only if the Landing Pages module is installed) |

Providers are discovered automatically: any file you drop into the provider folder appears here. See [Writing a Provider](#for-developers---writing-a-provider).

---

## Generation Modes

### Dynamic mode

The sitemap is generated when the route is requested:

```
https://your-site.com/index.php?route=extension/aw_sitemap/sitemap
```

It returns a single `<urlset>` with every enabled provider's URLs. Optionally cached to a file with a TTL. There is no physical `sitemap.xml` file in this mode - to expose a clean `/sitemap/sitemap.xml` URL you need a server rewrite (see below).

### Static mode

Run the cron command (or open the route once). The module writes:

```
sitemap/sitemap.xml          <- index (sitemapindex)
sitemap/en-gb-product-0.xml  <- shard
sitemap/en-gb-product-1.xml
sitemap/en-gb-category-0.xml
...
```

The index references every shard by its real public URL. Files are regenerated each run. This is the recommended mode for large catalogs.

---

## Static Generation via Cron

Add the CLI command shown on the **Cron & robots.txt** tab to your server's cron, for example once a day:

```cron
0 3 * * * php /home/user/public_html/cli/aw_sitemap.php
```

The command is also shown ready-to-copy in the admin. After it runs, the sitemap index is available at:

```
https://your-site.com/sitemap/sitemap.xml
```

Copy the `Sitemap:` line from the same tab into your `robots.txt`:

```
Sitemap: https://your-site.com/sitemap/sitemap.xml
```

> **Recommendation:** generate once or twice a day. There is no need to regenerate more often unless your catalog changes constantly.

---

## Server Rewrite (dynamic mode)

In **dynamic** mode there is no physical file, so `/sitemap/sitemap.xml` will not exist on disk. To serve the sitemap at that clean URL, add a rewrite rule (both snippets are shown ready-to-copy on the Cron tab):

**Apache (.htaccess):**
```apache
RewriteRule ^sitemap/sitemap.xml$ index.php?route=extension/aw_sitemap/sitemap [L]
```

**Nginx:**
```nginx
location = /sitemap/sitemap.xml {
    try_files $uri /index.php?route=extension/aw_sitemap/sitemap;
}
```

> **Static mode needs no rewrite** - it writes a real file that the web server delivers directly.

---

## Product Images

This is the feature that keeps the module stable on large catalogs.

When **Product images** is enabled, each product can include an `<image:image>` entry. The module looks for the resized thumbnail in `image/cache`:

- **If the cached thumbnail exists** - its URL is added to the sitemap.
- **If it does not exist** - the image is skipped. The module never calls the OpenCart image resizer during generation.

This matters because resizing tens of thousands of images in one request is exactly what makes the stock OpenCart sitemap time out or run out of memory. By using only already-cached thumbnails, generation stays fast and predictable.

> **Tip:** product thumbnails are created naturally as customers browse the catalog, or you can warm the cache with any thumbnail-prewarming approach. Images not yet cached simply join the sitemap on a later run.

---

## For Developers - Writing a Provider

A provider is a small catalog controller that contributes one section to the sitemap. The core handles all sharding and looping; a provider only returns URL rows.

### Location and naming

```
catalog/controller/extension/aw_sitemap/provider/<name>.php
```

File `blog.php` -> class `ControllerExtensionAwSitemapProviderBlog`.
File `landing_page.php` -> class `ControllerExtensionAwSitemapProviderLandingPage`.

Files prefixed with `_` (like `_example.php`) are templates and are skipped.

### Contract

```php
class ControllerExtensionAwSitemapProviderBlog extends Controller
{
    public function getCode(): string   { return 'blog'; }
    public function getName(): string   { return 'Blog posts'; }

    public function getTotal(int $languageId): int
    {
        // total number of URLs for sharding
        return 0;
    }

    public function getUrls(int $languageId, int $start, int $limit): array
    {
        // return a slice of URL rows
        return [];
    }
}
```

### URL row shape

```php
[
    'loc'        => 'https://store/...',          // required, absolute URL
    'lastmod'    => '2026-06-16T12:00:00+00:00',  // ISO 8601, optional
    'changefreq' => 'weekly',                     // optional
    'priority'   => '0.5',                        // optional
    'images'     => [                             // optional
        ['loc' => '...', 'caption' => '...', 'title' => '...'],
    ],
]
```

`priority` and `changefreq` are defined in your provider code, not in the admin. A ready-to-copy template lives at `catalog/controller/extension/aw_sitemap/provider/_example.php`, and a working example wired to the Landing Pages module is at `provider/landing.php`.

Once the file is in place it appears automatically on the **Providers** tab, where it can be enabled or disabled.

---

## Possible Errors and Recommendations

### Sitemap is empty or missing sections
- Check that the relevant providers are enabled on the **Providers** tab.
- Make sure products/categories are enabled and assigned to the current store.

### `/sitemap/sitemap.xml` returns 404 in dynamic mode
- Dynamic mode has no physical file. Either switch to **Static** mode (recommended for large catalogs) or add the server rewrite from the Cron tab.

### Static files are not written
- Check that the web server can write to the store root so the output folder can be created.
- Set folder permissions to 755 if needed.

### Products have no images in the sitemap
- This is expected for products whose resized thumbnail is not yet in `image/cache`. The module never resizes during generation by design. The image is added on a later run once cached.

### Generation is slow on a huge catalog
- Use **Static** mode via cron instead of the dynamic route.
- Lower **URLs per shard** if individual files are too large to serve comfortably.

### Getting Support

1. **Collect information:** OpenCart version, module version, problem description, screenshots, and a sample of the generated XML.
2. **Contact support:**
   - Telegram: [@alexwaha_dev](https://t.me/alexwaha_dev)
   - Email: [support@alexwaha.com](mailto:support@alexwaha.com)
   - Bug Report: [GitHub Issues](https://github.com/AlexWaha/opencart-bundle/issues)

> **Important:** Technical support for this module is available on a paid basis only. Bug fixes in the main repository are performed without a fixed schedule.

> **Pull Requests are welcome:** if you can suggest a fix, pull requests on [GitHub](https://github.com/AlexWaha/opencart-bundle/pulls) are welcome.

---

## License and Contacts

### License

This project is distributed under the [GNU General Public License version 3 (GPLv3)](https://github.com/alexwaha/opencart-bundle/blob/main/LICENSE).

Rights and authorship belong to the developer:
**Alexander Vakhovski (Oleksandr Vakhovskyi)**, also known as Alexwaha.

Official website: [https://alexwaha.com](https://alexwaha.com)

### Terms of Use

- ✓ Free use in commercial projects
- ✓ Code modification for your needs
- ✓ Distribution of modified versions (keeping the GPLv3 license)
- ✗ Removal or modification of author information

### Disclaimer

The module has been tested on a clean **OpenCart** installation with the standard (Default) theme.

The author is not responsible for incorrect operation with third-party themes, conflicts with other modules, problems caused by code modification, or data loss due to improper use.

> **Recommendation:** always test on a staging server before installing on a production site.

### Contacts

**Telegram:** [@alexwaha_dev](https://t.me/alexwaha_dev)
**Email:** [support@alexwaha.com](mailto:support@alexwaha.com)
**Bug Report:** [GitHub Issues](https://github.com/AlexWaha/opencart-bundle/issues)
**GitHub:** [https://github.com/AlexWaha/opencart-bundle](https://github.com/AlexWaha/opencart-bundle)

---

**Alexwaha.com - Unlimited Sitemap for OpenCart**

---
