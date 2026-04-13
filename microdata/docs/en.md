# Instructions | Alexwaha.com - Microdata

**Schema.org Structured Data for OpenCart v2.3 - 3.x**

---

## Table of Contents

1. [Module Information](#module-information)
2. [Key Features](#key-features)
3. [Technical Specifications](#technical-specifications)
4. [Installation Instructions](#installation-instructions)
5. [Configuration](#configuration)
   - 5.1 [Diagnostics Tab](#diagnostics-tab)
   - 5.2 [General Settings](#general-settings)
   - 5.3 [Organization](#organization)
   - 5.4 [Products](#products)
   - 5.5 [Categories & Lists](#categories--lists)
   - 5.6 [Content Pages](#content-pages)
   - 5.7 [OpenGraph](#opengraph)
   - 5.8 [Advanced / Tricks](#advanced--tricks)
   - 5.9 [Import / Export](#import--export)
6. [Supported Schema Types](#supported-schema-types)
7. [How It Works (Events Architecture)](#how-it-works-events-architecture)
8. [Troubleshooting](#troubleshooting)
9. [License & Contact](#license--contact)

---

## Module Information

**AW Microdata** is a professional module for OpenCart that automatically generates Schema.org structured data (JSON-LD) for all pages of your online store. The module uses OpenCart's built-in Events system to inject markup, making it completely template-independent and requiring no OCMOD modifications.

Structured data helps search engines better understand your website content, which can lead to rich snippets in search results (star ratings, prices, availability, FAQ accordions, breadcrumbs, and more).

### Key Features:

- **25+ Schema.org Types** - Product, Organization, FAQPage, ContactPage, BreadcrumbList, WebSite, BlogPosting, and many more
- **Template-Independent** - Uses OpenCart Events system, no OCMOD modifications required
- **Built-in Diagnostics** - Validates event registration, detects duplicate markup, checks config completeness
- **OpenGraph & Twitter Cards** - Full OG and Twitter meta tag generation
- **VideoObject Auto-Detection** - Automatically finds YouTube/Vimeo videos in product and blog descriptions
- **ImageObject Extraction** - Extracts images from descriptions and generates ImageObject schema
- **ItemList / Carousel** - Generates ItemList schema for category pages (Google product carousel)
- **Related Products** - Includes related/recommended products as `isRelatedTo` in Product schema
- **Attribute Mapping** - Maps OpenCart attributes to Schema.org properties (color, material, size, gender)
- **Rating & Review Boost** - Configurable fake rating/review count boost for SEO
- **Competitor sameAs Injection** - Grey-hat SEO: associate competitor brand searches with your site
- **Custom JSON-LD Injection** - Insert any custom JSON-LD block on every page
- **Import/Export Settings** - Backup and restore module configuration as JSON
- **No External API Dependencies** - Everything works locally, no third-party API calls
- **Multi-language Support** - English, Russian, Ukrainian

### Technical Specifications:

- **Author:** Alexander Vakhovski (AlexWaha)
- **Website:** [https://alexwaha.com](https://alexwaha.com)
- **License:** GPLv3
- **Compatibility:** OpenCart 2.3.x - 3.x (ocStore supported)
- **Dependency:** AwCore library
- **Module Code:** `aw_microdata`
- **Architecture:** Events-based (`view/*/after`)
- **Output Format:** JSON-LD only (Google recommended)
- **PHP Requirement:** 7.4+
- **Additional Database Tables:** None
- **OCMOD Modifications:** None
- **File Count:** ~20 files
- **Code Style:** PSR-12

---

## Installation Instructions

### Step 1: Prerequisites

1. Make sure you have the **[aw_core_oc2.3-3.x.ocmod.zip](https://github.com/AlexWaha/opencart-bundle/blob/master/Core/dist/aw_core_oc2.3_3.x.ocmod.zip)** module installed

   > **Important:** This is auxiliary functionality required for all AlexWaha modules

2. For OpenCart 2.3.x, make sure you have a module that disables FTP uploading of extensions, or you have correct FTP settings in your store settings

   > **Note:** Not required for OpenCart 3.x

   [Link to module disabling FTP upload (for OpenCart v2.3 only)](https://www.opencart.com/index.php?route=extension/extension/info&extension_id=18892)

### Step 2: Module Installation

1. Install the **aw_microdata_oc2.3-3.x.ocmod.zip** archive through the site admin panel

   - Go to **Extensions -> Installer**
   - Upload the module archive
   - Wait for installation to complete

2. Refresh the modification cache

   - Go to **Extensions -> Modifications**
   - Click the **Refresh** button

3. In **Extensions -> Modules** section find and enable the module:

   **alexwaha.com - Microdata**

   > **Important:** When enabled, the module will automatically register 12 event handlers and set up access permissions. No database tables are created - all configuration is stored in the shared `aw_module_config` table managed by AwCore.

### Step 3: Post-Installation Self-Check

Verify correct installation:

- Module is present in the extensions list
- Module settings page opens without errors
- Diagnostics tab shows all 12 events with status "OK"
- No duplicate Schema.org markup warnings

---

## Configuration

Go to module settings: **Extensions -> Modules -> alexwaha.com - Microdata**

The module settings page contains 9 tabs:

1. Diagnostics
2. General
3. Organization
4. Products
5. Categories & Lists
6. Content Pages
7. OpenGraph
8. Advanced / Tricks
9. Import / Export

---

### Diagnostics Tab

The Diagnostics tab provides a comprehensive health check for the module. It runs automatically when you open it and displays results in three categories.

#### Events Status

Checks whether all 12 event handlers are correctly registered in the OpenCart event system. Each event is listed individually with its status:

| Event Trigger | Purpose | Expected Status |
|---|---|---|
| `catalog/view/common/header/after` | OpenGraph meta tags injection into `<head>` | OK |
| `catalog/view/common/footer/after` | Organization JSON-LD injection before `</body>` | OK |
| `catalog/view/common/home/after` | WebSite + CollectionPage schema on the homepage | OK |
| `catalog/view/product/product/after` | Product + BreadcrumbList schema | OK |
| `catalog/view/product/category/after` | CollectionPage + BreadcrumbList schema | OK |
| `catalog/view/product/search/after` | SearchResultsPage + BreadcrumbList schema | OK |
| `catalog/view/product/manufacturer_info/after` | CollectionPage + Brand + BreadcrumbList schema | OK |
| `catalog/view/product/special/after` | CollectionPage + BreadcrumbList schema | OK |
| `catalog/view/information/information/after` | Article schema for information pages | OK |
| `catalog/view/information/contact/after` | ContactPage schema | OK |
| `catalog/view/blog/article/after` | BlogPosting schema for blog articles | OK |
| `catalog/view/blog/category/after` | Blog schema for blog category pages | OK |

If any event shows "MISSING", reinstall the module (disable and enable again in Extensions -> Modules).

#### Duplicate Markup Detection

Scans all `.twig` template files in the `catalog/view/theme/` directory for hardcoded `<script type="application/ld+json">` blocks that are not generated by AW Microdata. If found, these duplicates can conflict with the module's output and should be removed from the templates.

#### Config Completeness

Validates that recommended fields are filled in:

- **Logo** (Organization tab) - required for Organization and Publisher schemas
- **Phones** (Organization tab) - required for `telephone` and `contactPoint`
- **Address** (Organization tab) - required for `PostalAddress`
- **Email** (Organization tab) - required for Organization schema

Missing fields are listed with a direct link to the relevant settings tab.

#### Google Rich Results Test Links

Provides pre-configured links to Google's Rich Results Test for your key pages:

- Homepage
- First active product
- First active category
- Contact page

Click these links to validate your structured data directly with Google.

---

### General Settings

#### Module Status

- **Enabled** - Module is active and injects structured data on all pages
- **Disabled** - Module is completely disabled, no structured data is generated

#### Output Format

Currently supports **JSON-LD** only (recommended by Google). JSON-LD is injected as `<script type="application/ld+json">` blocks.

#### Website Name

The name of your website as it should appear in the WebSite schema. Configured per language. If left empty, the store name from OpenCart settings is used.

#### Alternate Name

An alternative name for your website (e.g., abbreviation or popular name). Maps to `alternateName` in the WebSite schema.

#### Site Search URL

URL template for the site search action. Used to generate the `SearchAction` in the WebSite schema, which enables the search box in Google search results.

**Default:** `{shop_url}/index.php?route=product/search&search={search_term_string}`

> **Tip:** If you have a custom search page URL, enter it here. The `{search_term_string}` placeholder is required by Google.

#### Default Image

Fallback image used when a page does not have its own image. Applies to OpenGraph tags and Schema.org markup.

---

### Organization

The Organization tab configures the structured data for your business entity. This data appears on every page as an Organization (or subtype) schema.

#### Store Type

Select the Schema.org type that best describes your business. Available options include:

- `Store` (default)
- `OnlineStore`
- `LocalBusiness`
- `LiquorStore`
- `ClothingStore`
- `ElectronicsStore`
- `FurnitureStore`
- `GardenStore`
- `GroceryStore`
- `HardwareStore`
- `HobbyShop`
- `HomeGoodsStore`
- `JewelryStore`
- `MusicStore`
- `OfficeEquipmentStore`
- `PetStore`
- `ShoeStore`
- `SportingGoodsStore`
- `TireShop`
- `ToyStore`
- `WholesaleStore`
- `AutoPartsStore`
- `BikeStore`
- `BookStore`
- `ComputerStore`
- `ConvenienceStore`
- `DepartmentStore`
- `Florist`
- `Optician`
- `OutletStore`
- And more (30+ options)

> **Recommendation:** Choose the most specific type that matches your business. `OnlineStore` is a good default for e-commerce.

#### Legal Name

The official legal name of your organization. Configured per language. Maps to `legalName` in the schema. If left empty, the store name is used as fallback.

#### Email

Business email address. Maps to `email` in the Organization schema.

#### Phones

One or more phone numbers for your business. Click "Add" to add additional numbers. Maps to `telephone` in the schema. The first phone number is also used for the `contactPoint` property.

#### Social Links

URLs to your social media profiles (Facebook, Instagram, Twitter/X, YouTube, Telegram, etc.). Each URL is added as a `sameAs` value in the Organization schema. Click "Add" to add more links.

#### Address

Physical address of your business, configured per language:

- **Street** - Street address (maps to `streetAddress`)
- **City** - City/locality (maps to `addressLocality`)
- **Region** - State/province/region (maps to `addressRegion`)
- **ZIP Code** - Postal code (maps to `postalCode`)
- **Country** - Country code, e.g., UA, US, DE (maps to `addressCountry`). If left empty, the country from OpenCart store settings is used.

#### Geo Coordinates

Latitude and longitude of your business location. Maps to `GeoCoordinates` in the schema.

**Format:** Decimal degrees (e.g., `50.4501` for latitude, `30.5234` for longitude)

> **Tip:** You can find coordinates using Google Maps - right-click on your location and copy the coordinates.

#### Logo

URL or path to your organization's logo image. If left empty, the store logo from OpenCart settings is used. Maps to both `logo` and `image` in the Organization schema.

#### Working Hours

Schedule for each day of the week (Monday through Sunday). For each day, configure:

- **Open** - Opening time (e.g., `09:00`)
- **Close** - Closing time (e.g., `20:00`)
- **Closed** - Check to mark the day as a day off

Maps to `openingHoursSpecification` in the schema.

#### Currency Code

The currency accepted by your business (e.g., `UAH`, `USD`, `EUR`). Defaults to the store's default currency. Maps to `currenciesAccepted`.

#### Price Range

A price range indicator using the dollar-sign convention:

- `$` - Budget
- `$$` - Moderate
- `$$$` - Expensive
- `$$$$` - Premium

Maps to `priceRange` in the Organization schema.

#### Payment Methods

Accepted payment methods (free text). Maps to `paymentAccepted` in the schema.

**Example:** `Cash, Credit Card, PayPal, Bank Transfer`

#### Delivery Areas

Areas where you deliver goods (free text). Maps to `areaServed` in the schema.

**Example:** `Kyiv, Kharkiv, Odesa, Nationwide`

---

### Products

The Products tab configures Schema.org `Product` markup for product pages.

#### Enable Product Schema

Toggle to enable/disable Product schema generation on product pages. Enabled by default.

#### Product Rating

When enabled, includes `aggregateRating` in the Product schema based on product reviews. Enabled by default.

#### Review Source

Determines which reviews are used for the Product schema:

- **Product reviews only** - Uses only reviews for the specific product
- **Store reviews only** - Uses store-level reviews (from the AW Reviews module)
- **Both** - Combines product and store reviews

> **Recommendation:** Use "Product reviews only" for the most accurate representation. "Store reviews" or "Both" can be useful if individual products have few reviews.

#### Min Review Count

Minimum number of reviews required before `aggregateRating` is included in the schema. Default: `1`.

#### Min Rating

Minimum average rating required before `aggregateRating` is included. Default: `1`.

#### Unit Pricing

When enabled, adds `UnitPriceSpecification` to the product offer. Useful for products sold by weight, volume, or other units.

#### Unit Code (UN/CEFACT)

The unit measurement code according to the UN/CEFACT standard:

- `LTR` - Liter
- `KGM` - Kilogram
- `MTR` - Meter
- `CMT` - Centimeter
- `GRM` - Gram

#### Reference Quantity

The reference quantity for unit pricing. Default: `1`.

#### Product Condition

The condition of products in your store:

- `NewCondition` (default) - Brand new products
- `UsedCondition` - Second-hand products
- `RefurbishedCondition` - Refurbished products
- `DamagedCondition` - Damaged products

#### Brand Source

Where to get the brand name for products:

- **Manufacturer** (default) - Uses the product's manufacturer name
- **Store name** - Uses the store name as brand

#### Availability Mapping

Maps OpenCart stock statuses to Schema.org availability values. For each stock status in your store, you can assign the corresponding Schema.org value:

- `https://schema.org/InStock`
- `https://schema.org/OutOfStock`
- `https://schema.org/PreOrder`
- `https://schema.org/BackOrder`
- `https://schema.org/SoldOut`
- `https://schema.org/LimitedAvailability`

#### Delivery Lead Time

When enabled, adds `shippingDetails` with `deliveryTime` to the product offer.

- **Min days** - Minimum delivery time in days (default: `1`)
- **Max days** - Maximum delivery time in days (default: `3`)

#### Return Policy

When enabled, adds `hasMerchantReturnPolicy` to the product offer.

- **Return days** - Number of days for returns (default: `14`)
- **Return type** - Schema.org return policy category:
  - `MerchantReturnFiniteReturnWindow` (default) - Returns accepted within a specific timeframe
  - `MerchantReturnNotPermitted` - No returns accepted
  - `MerchantReturnUnlimitedWindow` - Unlimited return period

#### Shipping Details

When enabled, adds shipping details to the product offer schema.

#### Include Weight

When enabled, adds the product weight to the Product schema as a `QuantitativeValue`.

#### Include Dimensions

When enabled, adds product dimensions (length, width, height) to the Product schema.

#### Include All Images

When enabled, all product images (not just the main one) are included in the Product schema. Enabled by default.

#### Include Attributes

When enabled, all product attributes are included as `additionalProperty` (PropertyValue) in the Product schema.

#### Attribute Mapping

Map specific OpenCart attributes to Schema.org product properties:

- **Attribute for Color** - Select the attribute that represents product color (maps to `color`)
- **Attribute for Material** - Select the attribute that represents product material (maps to `material`)
- **Attribute for Size** - Select the attribute that represents product size (maps to `size`)
- **Target Gender** - Select the target audience gender:
  - Male
  - Female
  - Unisex

> **Tip:** Leave as "Not mapped" if your products don't have these attributes.

#### Related Products (isRelatedTo)

When enabled, includes related/recommended products from the product page as `isRelatedTo` in the Product schema.

#### VideoObject from Description

When enabled, automatically detects YouTube and Vimeo video links in product descriptions and generates `VideoObject` schema for each video. Works without any external API calls - video thumbnails are generated from video IDs.

#### ImageObject from Description

When enabled, extracts `<img>` tags from product descriptions and generates `ImageObject` schema for each image.

---

### Listing Pages (Categories & Lists)

The Listing Pages tab configures structured data for all pages that display product listings: categories, search results, manufacturer pages, specials/sales, landing pages, and the homepage.

All listing pages share a common schema structure: `CollectionPage` (or configured type) with `AggregateOffer` (price range), optional `AggregateRating` (composite rating), optional delivery and return policy info, and optional `ItemList` carousel.

#### Enable Category Schema

Toggle to enable/disable category page schema generation. Enabled by default.

#### Schema Type

The Schema.org type for category pages:

- `CollectionPage` (default) - A page that collects items
- `ItemList` - A list of items
- `OfferCatalog` - A catalog of offers

> **Recommendation:** `CollectionPage` is the most semantically correct for category pages.

#### Search Results Schema

Toggle to enable/disable `SearchResultsPage` schema generation on search result pages. Includes `AggregateOffer` with price range for matching products. Enabled by default.

#### Manufacturer Page Schema

Toggle to enable/disable `CollectionPage` schema generation on manufacturer product pages. Includes `AggregateOffer` with price range and a `Brand` property with the manufacturer name. Enabled by default.

#### Special / Sales Page Schema

Toggle to enable/disable `CollectionPage` schema generation on special offers pages. Uses **special/discounted prices** (not regular prices) for the `AggregateOffer` price range. Enabled by default.

#### Homepage Product Data

When enabled, generates a `CollectionPage` schema on the homepage with an `AggregateOffer` containing the price range across all active products in the store. Disabled by default.

> **Note:** This is in addition to the `WebSite` schema already generated on the homepage.

#### Enable Price Range

When enabled, queries the database for the lowest and highest product prices and generates an `AggregateOffer` with `lowPrice`, `highPrice`, and `offerCount`. Applies to all listing page types.

#### Product Count

When enabled, includes the total number of active products in the listing.

#### Composite Rating on Listings

When enabled, calculates a **composite aggregate rating** combining product reviews (from `review` table) and store reviews (from `aw_review` table) and includes `aggregateRating` in the listing schema. This provides a richer rating signal even when individual pages have few reviews.

> **Note:** The composite rating is used on all listing pages (categories, search, manufacturer, specials, landing pages, homepage). Fake rating boost settings from the Advanced tab also apply.

#### Delivery Info on Listings

When enabled, adds `shippingDetails` with `deliveryTime` to the `AggregateOffer` on all listing pages. Uses the same **Min days** and **Max days** values configured in the Products tab.

#### Return Policy on Listings

When enabled, adds `hasMerchantReturnPolicy` to the `AggregateOffer` on all listing pages. Uses the same **Return days** and **Return type** values configured in the Products tab.

#### Product Carousel (ItemList)

When enabled, generates an `ItemList` schema for products displayed in the listing. Google may display this as a product carousel in search results.

Each product is listed as a `ListItem` with position, URL, and name.

#### Landing Page Schema

When enabled, generates `CollectionPage` schema for landing pages (if AW Landing Pages module is installed). Includes price range for products on the landing page.

#### Area Served

When enabled, uses the landing page title as the `areaServed` value. Useful for location-based landing pages (e.g., "Delivery in Kyiv").

---

### Content Pages

The Content Pages tab configures structured data for various types of content pages: information pages, blog, FAQ, reviews, calculator, and contacts.

#### Enable Info Page Schema

Toggle to enable/disable Article schema for OpenCart information pages. Enabled by default.

#### Schema Type (Information Pages)

The Schema.org type for information pages:

- `Article` (default) - General article
- `NewsArticle` - News article
- `TechArticle` - Technical article
- `ScholarlyArticle` - Academic article

#### Author Name

The author name for articles. If left empty, the store name is used with Organization type.

#### Author URL

The author's URL. Used together with Author Name for the `author` property.

#### Blog Article Schema

When enabled, generates `BlogPosting` (or selected type) schema for blog article pages (requires a blog module).

#### Blog Type

The Schema.org type for blog articles:

- `BlogPosting` (default) - Standard blog post
- `Article` - General article
- `NewsArticle` - News article
- `SocialMediaPosting` - Social media post

#### Word Count

When enabled, calculates and includes the `wordCount` property in blog article schema.

#### FAQ Page Schema

When enabled, generates `FAQPage` schema on pages that contain FAQ data (requires AW FAQ module). Questions and answers are pulled from the `aw_faq` and `aw_faq_description` tables.

The generated schema follows Google's FAQ rich result requirements:

```json
{
  "@type": "FAQPage",
  "mainEntity": [
    {
      "@type": "Question",
      "name": "What is your return policy?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "We accept returns within 14 days."
      }
    }
  ]
}
```

#### Reviews Page Schema

When enabled, generates Organization schema with `aggregateRating` and individual `review` entries on the reviews page. Pulls data from store reviews (AW Reviews module).

#### Calculator Page Schema

When enabled, generates `WebApplication` schema for calculator pages (e.g., AW Moonshine Calculator).

- **Application Category:** `UtilitiesApplication` (default)
- **Price:** Free (Offer with price 0)

#### Calculator HowTo Schema

When enabled, also generates a `HowTo` schema alongside the WebApplication, with step-by-step instructions for using the calculator.

#### Contact Page Schema

When enabled, generates `ContactPage` schema on the contact page, with `LocalBusiness` organization data as `mainEntity`. Includes address, phone, working hours, and other organization details.

---

### OpenGraph

The OpenGraph tab configures Open Graph and Twitter Card meta tags for social media sharing.

#### Enable OpenGraph

Toggle to enable/disable OpenGraph meta tag generation. When enabled, the following tags are injected into `<head>`:

- `og:title` - Page title
- `og:description` - Page description (from meta description or content)
- `og:url` - Canonical page URL
- `og:site_name` - Store name
- `og:locale` - Current language locale
- `og:image` - Page image (product image, article image, or logo)
- `og:type` - Content type (auto-detected: `product` for products, `article` for articles, configurable default for other pages)

For product pages, additional tags are generated:

- `product:price:amount` - Product price
- `product:price:currency` - Currency code

#### Default OG Type

The default Open Graph type for pages that don't have a specific type:

- `website` (default) - General website
- `article` - Article
- `product` - Product

#### Facebook App ID

Your Facebook Application ID for Facebook Insights integration. Optional.

#### Facebook Pages

Your Facebook Page ID(s). Optional.

#### Twitter Card Type

The type of Twitter Card to generate:

- `summary_large_image` (default) - Large image summary card
- `summary` - Standard summary card

#### Twitter Username

Your Twitter/X username (e.g., `@yourusername`). Used for the `twitter:site` meta tag.

#### Default OG Image

Fallback image for OpenGraph when a page doesn't have its own image. If not set, the store logo is used.

---

### Advanced / Tricks

> **Warning:** These settings can affect your SEO positively or negatively. Use with caution.

#### Global AggregateRating

When enabled, injects `AggregateRating` into the Organization schema on all pages, not just product pages. This means your store's aggregate rating appears site-wide.

> **Use case:** Useful if you want star ratings to appear in Google for your homepage or any page.

#### Fake Review Count Boost

A static number added to the real review count in Schema markup. For example, if you have 5 real reviews and set this to `10`, Google will see 15 reviews.

Set to `0` to disable.

> **Warning:** This is a grey-hat SEO technique. Google may penalize sites that misrepresent review data.

#### Fake Rating Boost

A decimal value (0.0-5.0) added to the real average rating. For example, if your real average is 4.5 and you set this to `0.4`, the schema will show 4.9. The maximum is capped at 5.0.

Set to `0` to disable.

> **Warning:** Same as above - use at your own risk.

#### Force InStock

When enabled, all products are shown as `https://schema.org/InStock` in the schema regardless of actual stock status.

> **Use case:** Useful for stores that always have products available but don't track stock in OpenCart.

#### Competitor sameAs URLs

URLs of competitor websites added to your Organization's `sameAs` property. Google may associate competitor brand searches with your site.

> **Warning:** This is a grey-hat SEO technique. Google's algorithms may or may not respond to this. Leave empty if unsure.

Click "Add" to add more URLs.

#### Custom JSON-LD

A raw JSON-LD block injected into every page of your store. Must be valid JSON. Useful for adding custom schemas that the module doesn't generate natively.

**Example:**

```json
{
  "@context": "https://schema.org",
  "@type": "Event",
  "name": "Summer Sale",
  "startDate": "2025-06-01",
  "endDate": "2025-08-31"
}
```

#### Speakable

When enabled, adds the `speakable` property to Article schemas, indicating which parts of the content are suitable for text-to-speech.

#### VideoObject

When enabled globally, auto-detects YouTube and Vimeo video links in product and blog descriptions. For each detected video, a `VideoObject` schema is generated with:

- `name` - Video title (derived from page title)
- `thumbnailUrl` - Auto-generated from video ID (YouTube) or Vimeo
- `embedUrl` - Embed URL
- `contentUrl` - Direct video URL

No external API calls are made - video metadata is constructed from the URL patterns.

---

### Import / Export

#### Export Settings

Click the **Export** button to download your current module configuration as a JSON file. The filename includes the current date and time:

`aw_microdata_settings_2025-01-15_14-30-00.json`

> **Tip:** Export your settings before making major changes as a backup.

#### Import Settings

Upload a previously exported JSON file to restore settings.

1. Click **Choose File** and select a `.json` file
2. Click **Import**
3. The page will reload with the imported settings

> **Warning:** Importing will overwrite ALL current settings. This action cannot be undone. Always export a backup first.

---

## Supported Schema Types

| Schema Type | Page | Description |
|---|---|---|
| `WebSite` | Homepage | Site name, search action, publisher |
| `Organization` / subtypes | Every page (footer) | Business info, address, contacts, working hours |
| `Product` | Product page | Name, price, images, brand, SKU, availability, reviews |
| `Offer` | Product page | Price, currency, availability, delivery, return policy |
| `AggregateOffer` | Category, Search, Manufacturer, Special, Landing, Homepage | Price range (low/high), product count, delivery, return policy |
| `AggregateRating` | Product / Organization / All listing pages | Average rating, review count (composite: product + store reviews) |
| `Review` | Product / Organization | Individual reviews with author, rating, text |
| `Brand` | Product page | Product manufacturer/brand |
| `BreadcrumbList` | Product, Category, Information, Contact, Blog | Navigation breadcrumbs |
| `CollectionPage` | Category, Manufacturer, Special, Landing, Homepage | Listing page with products, price range, rating |
| `SearchResultsPage` | Search results page | Search listing with products, price range, rating |
| `ItemList` | All listing pages | Product carousel/list |
| `Article` | Information page | Headline, description, author, publisher |
| `BlogPosting` | Blog article page | Blog post with author, date, content |
| `Blog` | Blog category page | Blog listing |
| `FAQPage` | FAQ page | Questions and answers |
| `ContactPage` | Contact page | Contact information with LocalBusiness |
| `LocalBusiness` | Contact page | Full business info with address, hours |
| `WebApplication` | Calculator page | Application schema with offer |
| `HowTo` | Calculator page | Step-by-step instructions |
| `VideoObject` | Product / Blog pages | Auto-detected videos from descriptions |
| `ImageObject` | Product / Blog pages | Extracted images from descriptions |
| `PostalAddress` | Organization / Contact | Business address |
| `GeoCoordinates` | Organization / Contact | Business location coordinates |
| `OpeningHoursSpecification` | Organization / Contact | Working hours per day |
| `ContactPoint` | Organization | Customer service contact |
| `PropertyValue` | Product page | Product attributes |
| `QuantitativeValue` | Product page | Weight, dimensions |
| `UnitPriceSpecification` | Product page | Unit pricing |
| `ShippingDeliveryTime` | Product + all listing pages | Delivery lead time |
| `MerchantReturnPolicy` | Product + all listing pages | Return policy details |
| `Rating` | Product / Organization | Individual review rating |
| `PeopleAudience` | Product page | Target gender |
| `SearchAction` | Homepage | Site search box |

---

## How It Works (Events Architecture)

AW Microdata uses OpenCart's **Event system** to inject structured data, making it completely independent of your store's template/theme. This means:

- No template files are modified
- No OCMOD modifications are needed
- Works with any OpenCart 2.3-3.x theme
- Updating your theme won't break the structured data

### Architecture Overview

The module registers 12 event handlers that listen to `view/*/after` events. When OpenCart renders a page template, the event handler fires and injects the appropriate markup into the HTML output.

**JSON-LD** is injected before `</body>` (for most schemas) or before `</head>` (for OpenGraph meta tags).

### Event Flow

```
1. User requests a product page
2. OpenCart renders product/product.twig
3. Event fires: catalog/view/product/product/after
4. Event handler calls:
   - microdata/getProduct($data)     -> Product JSON-LD
   - microdata/getBreadcrumbs($data) -> BreadcrumbList JSON-LD
5. JSON-LD blocks are inserted before </body>
6. Simultaneously, header event injects OG tags before </head>
```

### Event-to-Schema Mapping

| Event | Handler | Schemas Generated |
|---|---|---|
| `view/common/header/after` | `viewHeaderAfter` | OpenGraph + Twitter Card meta tags |
| `view/common/footer/after` | `viewFooterAfter` | Organization |
| `view/common/home/after` | `viewHomeAfter` | WebSite + SearchAction + CollectionPage |
| `view/product/product/after` | `viewProductAfter` | Product + BreadcrumbList |
| `view/product/category/after` | `viewCategoryAfter` | CollectionPage + BreadcrumbList |
| `view/product/search/after` | `viewSearchAfter` | SearchResultsPage + BreadcrumbList |
| `view/product/manufacturer_info/after` | `viewManufacturerAfter` | CollectionPage + Brand + BreadcrumbList |
| `view/product/special/after` | `viewSpecialAfter` | CollectionPage + BreadcrumbList |
| `view/information/information/after` | `viewInformationAfter` | Article + BreadcrumbList |
| `view/information/contact/after` | `viewContactAfter` | ContactPage + BreadcrumbList |
| `view/blog/article/after` | `viewBlogArticleAfter` | BlogPosting + BreadcrumbList |
| `view/blog/category/after` | `viewBlogCategoryAfter` | Blog |

### Data Sources

The module pulls data from multiple sources:

- **Template data (`$data`)** - Product info, breadcrumbs, images, attributes passed to the template
- **OpenCart config** - Store name, URL, logo, currency, country
- **Module config** - All settings from the AW Microdata configuration
- **Database queries** - Price ranges, review aggregates, FAQ items, product identifiers (SKU, EAN, MPN)

---

## Troubleshooting

### Events Not Registered

**Symptom:** Diagnostics tab shows missing events.

**Solution:**
1. Go to **Extensions -> Modules**
2. Find **alexwaha.com - Microdata**
3. Click the red minus button to disable
4. Click the green plus button to enable again
5. Check Diagnostics tab - all events should show "OK"

### Duplicate Markup Found

**Symptom:** Diagnostics tab shows duplicate Schema.org markup in templates.

**Solution:**
1. Note the template files listed in the warning
2. Open each file and remove any hardcoded `<script type="application/ld+json">` blocks
3. Refresh the modification cache

> These duplicates typically come from other SEO modules or template customizations.

### No JSON-LD on Page

**Symptom:** No structured data appears in the page source.

**Checklist:**
1. Is the module status **Enabled** in General settings?
2. Is the specific schema type enabled (e.g., Product Schema for product pages)?
3. Are all events registered (check Diagnostics)?
4. Check for PHP errors in OpenCart error log
5. View page source and search for `application/ld+json`

### Empty Organization Data

**Symptom:** Organization schema is missing fields.

**Solution:** Fill in all fields in the Organization tab: logo, phones, email, address. Check the Diagnostics tab for missing fields.

### OpenGraph Tags Not Appearing

**Symptom:** No `og:` meta tags in page source.

**Checklist:**
1. Is OpenGraph enabled in the OpenGraph tab?
2. Is the `catalog/view/common/header/after` event registered?
3. Check that the header template contains `</head>` (required for injection)

### Incorrect Prices in Schema

**Symptom:** Prices in structured data don't match displayed prices.

**Explanation:** The module reads formatted prices from the template data and parses them. Special/sale prices take priority over regular prices. Ensure your product prices are correctly configured in OpenCart.

### FAQ Schema Not Appearing

**Symptom:** FAQPage schema is not generated.

**Checklist:**
1. Is FAQ Page Schema enabled in Content Pages tab?
2. Is the AW FAQ module installed and configured?
3. Are there active FAQ items with both question and answer filled in?
4. Are the `aw_faq` and `aw_faq_description` tables present in the database?

### Validating with Google

Use the links in the Diagnostics tab to test your pages with Google's Rich Results Test:

[https://search.google.com/test/rich-results](https://search.google.com/test/rich-results)

You can also use the Schema.org Validator:

[https://validator.schema.org/](https://validator.schema.org/)

---

## License & Contact

- **License:** GPLv3
- **Author:** Alexander Vakhovski (AlexWaha)
- **Website:** [https://alexwaha.com](https://alexwaha.com)
- **Email:** support@alexwaha.com
- **Telegram:** [@alexwaha_dev](https://t.me/alexwaha_dev)
