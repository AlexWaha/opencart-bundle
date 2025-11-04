# Alexwaha.com - Landing Pages

**Module for OpenCart v2.3 - 3.x**

---

## Description

This extension includes modules for creating landing pages and a module for displaying a block of links to them.

The module allows you to create separate landing pages with a set of products, as well as display a block with links to these pages anywhere on the site.

---

## Installation Instructions

### Requirements and Installation Steps

1. **Install Core Module**

   Make sure you have installed the module **[aw_core_oc2.3-3.x.ocmod.zip](https://github.com/AlexWaha/opencart-bundle/blob/master/Core/dist/aw_core_oc2.3_3.x.ocmod.zip)**

   > This is an auxiliary function for all my modules

2. **FTP Upload Settings (OpenCart 2.x only)**

   Make sure you have a module installed that disables FTP uploads for extensions in OpenCart 2.x, or that your FTP settings in the store config are correct

   > [Link to module disabling FTP upload for OpenCart v2.3 only](https://www.opencart.com/index.php?route=extension/extension/info&extension_id=18892) **(Not required for OpenCart 3.x)**

3. **Install the archive aw_landing_page_oc2.3-3.x.ocmod.zip via the site's admin panel**

4. **Refresh the modification cache**

5. **Enable the Module**

   Go to: **Extensions → Modules** → Enable the module **alexwaha.com - Landing Pages**

   > When enabled, the module will automatically create the required DB tables and grant view/edit permissions

### Self-Check After Installation

- Check for the module in the list of extensions
- Refresh the modification cache

---

## Configuration

### Creating Landing Pages

1. A new item **Catalog → Landing Pages** appeared in the left column, open it

   Or via **Extensions → alexwaha.com - Landing Pages**

2. Add a new landing page, filling in all required fields and adding products to display *(autocomplete works)*

### Configuring the Links Module

3. Go to **Extensions → Modules - alexwaha.com - Landing Pages/Links**

4. Set the system **Module Name**, **Block Title** and **Pages** to display *(autocomplete works)*, **Status**, **Save changes**

5. Go to **Design → Layouts**, choose the layout where you want to display the module with landing page links and its position

> Example: Layout **"Home"**, position **"Bottom of the page"** → **Save changes**

> **Important:** The module will appear on the selected layout as a block with a Title and a list of links

> **Note:** Landing page links will automatically appear in the sitemap **sitemap.xml**

---

## For Developers

### Technical Overview

The module uses the Twig templating engine, and does not require declaring language variables inside the controller, just add the variable to the language file.

The module's language files are always located in the `en-gb`, `ru-ru` folders regardless of the OpenCart version.

### File Locations

- **Links module template:** `../default.../extension/module/aw_langing_links.twig`
- **Landing page template:** `../default.../extension/module/aw_langing_page.twig`

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
