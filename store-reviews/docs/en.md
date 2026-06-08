# AW Store Reviews - Customer Reviews & Carousel for OpenCart

## Table of Contents

1. [Module Information](#1-module-information)
2. [Key Features](#2-key-features)
3. [Installation](#3-installation)
4. [Store Reviews - Configuration](#4-store-reviews---configuration)
5. [Store Reviews Carousel - Configuration](#5-store-reviews-carousel---configuration)
6. [Frontend Pages](#6-frontend-pages)
7. [Troubleshooting](#7-troubleshooting)
8. [License & Contact](#8-license--contact)

## 1. Module Information

- **Name:** alexwaha.com - Store Reviews
- **Version:** 1.0.0
- **Author:** Alexander Vakhovski (AlexWaha)
- **Website:** https://alexwaha.com
- **Compatibility:** OpenCart 2.3 - 3.x (ocStore supported)
- **Dependency:** AwCore library
- **License:** GPLv3
- **Languages:** English, Russian, Ukrainian
- **Database table:** `aw_review` (auto-created on install)
- **Includes:** 2 modules (Store Reviews + Store Reviews Carousel)

## 2. Key Features

- Store-wide customer reviews (separate from product reviews)
- Full CRUD in admin panel (add, edit, delete)
- Standalone reviews page with pagination
- AJAX review submission form for customers
- Star rating (1-5)
- Author name and city fields
- Review moderation (status toggle)
- Reviews Carousel widget (for homepage, sidebar, etc.)
- Swiper.js carousel (bundled, no CDN required)
- Configurable slides per view with responsive breakpoints
- SEO URLs and meta tags per language with uniqueness validation
- Import/Export settings as JSON
- Integration with AW Microdata (Schema.org AggregateRating + Review)
- OCMOD menu integration in admin sidebar

## 3. Installation

### Prerequisites

- OpenCart 2.3 - 3.x
- AwCore library installed
- PHP 7.4+

### Steps

1. Go to **Extensions - Extension Installer**
2. Upload `aw_store_reviews_oc2.3-3.x.ocmod.zip`
3. Go to **Extensions - Modifications** and click **Refresh**
4. Go to **Extensions - Modules**
5. Find **alexwaha.com - Store Reviews** and click **Install** (creates `aw_review` table)
6. Find **alexwaha.com - Store Reviews Carousel** and click **Install**
7. Configure both modules

## 4. Store Reviews - Configuration

### 4.1 Reviews List

Main admin page displays all reviews in a table:
- Author, City, Rating (stars), Status, Date, Actions (edit/delete)
- Filter by status
- Bulk delete

### 4.2 Add/Edit Review

- **Author** - Reviewer name
- **City** - Reviewer city (optional)
- **Text** - Review text
- **Rating** - Star rating 1-5 (interactive picker)
- **Status** - Enable/disable (moderation)

### 4.3 Settings Tab

- **Status** - Global module status
- **Reviews per page** - Pagination limit on frontend
- **SEO URL** - Per-language URL keyword (validated for uniqueness)
- **Meta Title** - Page title for SEO (per language)
- **Meta Description** - Page description for SEO (per language)
- **Meta H1** - Custom H1 heading (per language)

### 4.4 Import / Export

- **Export** - Download settings as JSON
- **Import** - Restore settings from JSON

## 5. Store Reviews Carousel - Configuration

The carousel is a separate widget module (like OpenCart's Featured module). You can create multiple instances and assign them to different layout positions.

### Instance Settings

- **Name** - Instance name (internal)
- **Heading** - Carousel title text (per language)
- **Reviews** - Select reviews via autocomplete search, reorder with drag-and-drop
- **Fallback Limit** - Number of latest reviews to show when none are manually selected
- **Reviews per slide** - How many reviews to display at once on desktop (tablet: max 2, mobile: 1)
- **Status** - Enable/disable instance

### Carousel Features

- Powered by [Swiper.js](https://swiperjs.com/) (bundled, no CDN)
- Navigation arrows (prev/next) with hover effect
- Dot pagination (clickable)
- Loop mode - infinite sliding
- Auto-play every 5 seconds with pause on hover
- Touch/swipe support on mobile
- Responsive breakpoints (configurable via "Reviews per slide")
- Link to full reviews page

## 6. Frontend Pages

### Reviews Page

Accessible via configured SEO URL (e.g., `/store-reviews/`). Displays:

- List of approved reviews with star ratings, author, city, date
- Pagination
- "Write a Review" form with: name, city, rating picker, text, submit button
- AJAX submission with success/error messages
- Reviews appear after admin moderation

### Carousel Widget

Displays selected reviews in a sliding carousel. Appears in the layout position where the module instance is assigned (homepage, sidebar, footer, etc.).

## 7. Troubleshooting

| Problem | Solution |
|---------|----------|
| Reviews page returns 404 | Set SEO URL in settings, clear SEO cache |
| Carousel not showing | Check module instance is enabled and assigned to a layout position |
| New reviews not visible | Check review status - new submissions require admin approval |
| Carousel arrows missing | Ensure the template is loaded (check browser console for JS errors) |
| OCMOD menu not visible | Refresh modifications cache |
| SEO URL error on save | Each language must have a unique URL |

## 8. License & Contact

- **License:** GPLv3
- **Author:** Alexander Vakhovski
- **Website:** https://alexwaha.com
- **Email:** support@alexwaha.com
- **Telegram:** @alexwaha_dev
