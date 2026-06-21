# Manual | Alexwaha.com - Buyer History

> **Live demo:** see this module on the live demo store - [demo.alexwaha.com/module-buyer-history](https://demo.alexwaha.com/module-buyer-history)

**For OpenCart 2.3 - 3.x**

---

## Contents

1. [About the module](#about-the-module)
2. [Installation](#installation)
3. [Dependencies](#dependencies)
4. [Columns in Sales > Orders](#columns-in-sales--orders)
5. [Customer history page](#customer-history-page)
6. [Settings](#settings)
   - [General](#general)
   - [Thresholds & colors](#thresholds--colors)
   - [Status colors](#status-colors)
   - [Duplicates](#duplicates)
   - [Display](#display)
   - [Import / Export](#import--export)
7. [Architecture](#architecture)
8. [License & contacts](#license--contacts)

---

## About the module

**Buyer History** is an OpenCart module that adds:

- A **History** column to the standard order list (`Sales > Orders`) - a coloured badge with the customer's total order count and a click-dropdown breakdown by status with sums in the store base currency.
- A **Duplicates** column - order numbers from the same customer within a configurable time window.
- A dedicated **Customer history** page under `Reports` menu - list of unique customers with aggregates (total orders, average check, first/last order date), filters by tier / email / has-duplicates flag, and an expandable row showing every order of that customer with full line items (products, options, qty, price).

The manager immediately sees repeat customers and avoids calling those who have already ordered many times.

### Specs

- **Author:** Alexander Vakhovski (AlexWaha)
- **Site:** [https://alexwaha.com](https://alexwaha.com)
- **License:** GPLv3
- **Compatibility:** OpenCart 2.3.x - 3.x
- **Module code:** `aw_buyer_history`
- **OCMOD:** **not used** (OpenCart event system only)

---

## Installation

1. Install the `alexwaha.com - Core` module first (required dependency).
2. Open `Extensions → Installer` and upload `aw_buyer_history_oc2.3-3.x.ocmod.zip`.
3. Open `Extensions → Modifications` and click the Refresh button. On OpenCart 2.3 this applies the bundled OCMOD (`system/aw_buyer_history.ocmod.xml`) that injects the column markup into the order list template. On 3.x the OCMOD stays inert - the columns are injected at runtime through the event system.
4. Open `Extensions → Extensions → Modules`, find `alexwaha.com - Buyer History` and click the green `+` (Install). On install the module:
   - Registers two OpenCart events: `admin/view/sale/order_list/before` and `admin/view/common/column_left/before`.
   - Creates a menu item `Reports → Customer history`.
   - Grants `access`/`modify` permissions to the current user group.
5. Open `Extensions → Modules → alexwaha.com - Buyer History → Edit`, enable status and save.

After installation new columns appear in `Sales > Orders` and the new menu item shows up under `Reports`.

---

## Dependencies

- **OpenCart Core 2.3 or 3.x**
- **alexwaha.com - Core** (AwCore - shared library for AW modules)
- **MySQL 5.7+**

---

## Columns in Sales > Orders

After enabling the module, two columns appear next to the Customer column:

### History

A coloured badge with the customer's lifetime order count. Click opens a dropdown with a breakdown by status - count and sum (store base currency):

```
By status
Processing  4   7,100 UAH
Delivered   4   7,300 UAH
Pending     4   7,500 UAH
Total      12  21,900 UAH
```

Badge colours are configured per tier (Low / Mid / High) with thresholds by order count.

### Duplicates

Order numbers of other orders of the same customer that fall within the time window (configurable: 1h to 7d, or custom). When the visible count exceeds the limit, a `+N` button appears with a scrollable dropdown of the rest (handles 50-100 duplicates).

### Customer matching

- Registered: by `customer_id`
- Guests: by `email + normalized phone` (toggleable)

---

## Customer history page

Available via `Reports → Customer history`. Route: `index.php?route=extension/module/aw_buyer_history/report`.

### Columns

| Column | Description |
|--------|-------------|
| Customer | Name, email, phone |
| Orders | Count with coloured tier badge |
| Total spent | Lifetime spend in base currency |
| Avg order | AVG(total) |
| First order | First order date |
| Last order | Last order date |
| Duplicates | ⚠ icon if customer has duplicates in window |

### Filters

- **Search** - partial match by email, phone, name.
- **Tier** - Any / Low / Mid / High.
- **With duplicates only** - keeps only customers who have duplicates in the window.

### Expandable row

Click the chevron → AJAX-loads full order history of that customer. Under each order row there is a list of items:

- Order number
- Status badge with configured colour
- Item count
- Total (bold, centred)
- Date (centred)
- Link to `sale/order/info`
- Item list: product name with link to `catalog/product/edit`, model, selected options as chips, qty × price

Rows of customers with duplicates are highlighted with a light red background.

---

## Settings

### General

- **Status** - main on/off gate. When disabled the columns and menu item disappear.
- **Match guests by email + phone** - link guest orders by contact details.
- **Tracked order statuses** - checkboxes for every OC status. Unticked statuses are not counted in totals / breakdown.

### Thresholds & colors

- **Mid tier threshold** (default: 3 orders)
- **High tier threshold** (default: 10 orders)
- **Badge colours** for each tier (Low / Mid / High) - background + text. Coloris color picker. Live preview updates as you type the hex.

### Status colors

Pick a colour for each order status. On the Customer history page the order status badges are tinted with this colour. The status name on the left of the input also turns into a coloured label as you change the hex (live preview).

### Duplicates

- **Enable duplicates detection** - switch.
- **Time window** - presets `1h / 3h / 6h / 12h / 24h / 48h / 72h / 7d` or custom value in minutes/hours/days.
- **Min orders in window to flag** - default 2 (at least one duplicate).
- **Max numbers shown in cell** - extras collapse into a `+N` dropdown (scrollable for large counts).
- **Duplicate badge colour** - background + text (Coloris).
- **Link target** - `Same window` / `New tab`.

### Display

- **History column** - switch.
- **Duplicates column** - switch.

### Import / Export

- **Export** - downloads `aw_buyer_history_settings_<date>.json`.
- **Import** - pick JSON file → overwrites current settings.

---

## Architecture

Column rendering depends on the OpenCart version. The Reports menu item and all row data always come from the event system; only the order list column markup differs between 2.3 and 3.x.

### Order list columns: platform split

| Platform | Column markup | Row data |
|----------|---------------|----------|
| OpenCart 3.x | Event patches the Twig template in memory (no core files changed) | Event |
| OpenCart 2.3 | Bundled OCMOD patches `order_list.tpl` + `header.tpl` | Event |

On **3.x** the `admin/view/sale/order_list/before` event reads `admin/view/template/sale/order_list.twig`, runs `str_replace` to inject the `<th>`/`<td>` fragments from `injectors/order_list_headers.twig` and `injectors/order_list_cells.twig`, and writes the result back into the view's `$code` parameter, so the Twig engine (`system/library/template/twig.php:14-44`) renders the patched markup. No file on disk is changed.

On **2.3** the legacy template is `order_list.tpl` and the in-memory `$code` patch is intentionally skipped (the controller guards it with `isLegacy()`). The column markup is supplied by the bundled OCMOD `system/aw_buyer_history.ocmod.xml`, which adds the header/cell blocks to `order_list.tpl` and a stylesheet link to `header.tpl`. The event still runs - it only fills each `$data['orders']` row with the `aw_history` field (count, badge, duplicates).

### Event: `admin/view/common/column_left/before`

Adds a `Customer history` item into the Reports submenu by mutating the `$data['menus']` array. If the user has no Reports permission, falls back to a standalone root menu entry.

### Database indexes

To keep per-page aggregation fast on large order tables, on install the module adds two indexes to `oc_order`: `aw_bh_email` (`email`) and `aw_bh_date_added` (`date_added`). They let the history and duplicate lookups use index access instead of a full table scan. Both are dropped on uninstall.

### Cleanup

After uninstalling the module and clicking `Modifications → Refresh`, the OCMOD changes are reverted, the added indexes are dropped and the registered events are removed. OpenCart core templates are left untouched.

---

## License & contacts

- **License:** GPLv3
- **Site:** [https://alexwaha.com](https://alexwaha.com)
- **Email:** support@alexwaha.com
- **Telegram:** [@alexwaha](https://t.me/alexwaha)
