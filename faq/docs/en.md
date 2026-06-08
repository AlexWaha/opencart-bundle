# AW FAQ - Frequently Asked Questions for OpenCart

## Table of Contents

1. [Module Information](#1-module-information)
2. [Key Features](#2-key-features)
3. [Installation](#3-installation)
4. [Configuration](#4-configuration)
5. [Troubleshooting](#5-troubleshooting)
6. [License & Contact](#6-license--contact)

## 1. Module Information

- **Name:** alexwaha.com - FAQ
- **Version:** 1.0.0
- **Author:** Alexander Vakhovski (AlexWaha)
- **Website:** https://alexwaha.com
- **Compatibility:** OpenCart 2.3 - 3.x (ocStore supported)
- **Dependency:** AwCore library
- **License:** GPLv3
- **Languages:** English, Russian, Ukrainian
- **Database tables:** aw_faq, aw_faq_description

## 2. Key Features

- Full CRUD for FAQ questions and answers
- Summernote WYSIWYG editor for rich text answers
- Standalone /faq/ page with Bootstrap accordion (collapsible +/- icons)
- SEO URLs and meta tags per language
- Admin sidebar menu integration via OCMOD
- Sort order management
- Import/Export settings as JSON
- Multi-language support (en-gb, ru-ru, uk-ua)

## 3. Installation

### Prerequisites

- OpenCart 2.3 - 3.x
- AwCore library installed
- PHP 7.4+

### Steps

1. Go to **Extensions - Extension Installer**
2. Upload `aw_faq_oc2.3-3.x.ocmod.zip`
3. Go to **Extensions - Modifications** and click **Refresh**
4. Go to **Extensions - Modules**
5. Find **alexwaha.com - FAQ** and click **Install** (creates database tables)
6. Click **Edit** to configure settings and add FAQ items

## 4. Configuration

### 4.1 FAQ List

The main admin page shows all FAQ items in a table with columns: question, sort order, status, actions (edit/delete). Use the **Add** button to create new FAQ items.

### 4.2 FAQ Form

When adding or editing a FAQ item:

- **Question** - The question text (per language)
- **Answer** - Rich text answer with Summernote WYSIWYG editor (per language). Supports images, formatting, links via built-in file manager.
- **Sort Order** - Numeric order for display
- **Status** - Enable or disable the FAQ item

### 4.3 Settings Tab

- **Status** - Global module status
- **SEO URL** - Custom URL keyword for the FAQ page (per language)
- **Meta Title** - Page title for SEO (per language)
- **Meta Description** - Page description for SEO (per language)

### 4.4 Import / Export

- **Export** - Download FAQ settings as JSON
- **Import** - Upload previously exported JSON

### 4.5 Support

Contact information for module support.

## 5. Troubleshooting

| Problem | Solution |
|---------|----------|
| FAQ page returns 404 | Set SEO URL in settings, clear SEO cache |
| Summernote editor not loading | Check AwCore styles are loaded |
| FAQ not showing on frontend | Verify module status and FAQ item status are enabled |
| OCMOD menu not appearing | Refresh modifications cache |
| Images in answers broken | Use file manager within Summernote to upload |

## 6. License & Contact

- **License:** GPLv3
- **Author:** Alexander Vakhovski
- **Website:** https://alexwaha.com
- **Email:** support@alexwaha.com
- **Telegram:** @alexwaha_dev
