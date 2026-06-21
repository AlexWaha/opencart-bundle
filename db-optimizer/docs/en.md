# Instructions | Alexwaha.com - DB Optimizer

> **Live demo:** see this module on the live demo store - [demo.alexwaha.com/module-db-optimizer](https://demo.alexwaha.com/module-db-optimizer)

## Overview

DB Optimizer is an admin-only OpenCart module that analyzes your database and helps
you add missing indexes and fix common optimization issues. It scans every table
(including non-standard / third-party ones), not just the core OpenCart tables.

The goal is a faster database without overdoing it: suggestions are conservative
and every change is fully reversible.

- **Version:** 1.0.0
- **Author:** Alexander Vakhovski (AlexWaha)
- **Compatibility:** OpenCart 2.3.x, 3.x

## What it does

### Analyzer (read-only)
Reads `INFORMATION_SCHEMA` and reports:
- **Missing indexes** on foreign-key-like columns (`*_id`) and a curated whitelist
  (`language_id`, `store_id`, `customer_id`, `category_id`, `date_added`, ...).
- A **known-good baseline** of indexes from the classic OpenCart optimization list.
- **Tables without a PRIMARY KEY** (reported only - auto-fix is not safe).
- **MyISAM tables** that could be converted to InnoDB.
- **Fragmented tables** that could benefit from `OPTIMIZE TABLE`.

### Apply (your choice, per item)
- **Add index** - creates a single-column index named with your DB prefix + `idx_` (e.g. `oc_idx_`).
- **Convert to InnoDB** - `ALTER TABLE ... ENGINE=InnoDB` (heavy, asks confirmation).
- **Optimize** - `OPTIMIZE TABLE` (heavy, locks the table, asks confirmation).

Nothing is changed until you select a fix and click apply.

## Safety ("don't overdo it")

- Only **ADD** indexes - existing indexes are never touched or dropped.
- All module indexes are named with your DB prefix + **`idx_`** (e.g. `oc_idx_`), so
  they are identifiable and fully reversible. The Applied tab lists them with per-row
  Drop and Rollback all.
- The analyzer **skips small tables** (below the row threshold), **caps** the number
  of new indexes per table, and **ignores flag/boolean columns** (`status`,
  `quantity`, `sort_order`, ...).
- Heavy operations (InnoDB conversion, OPTIMIZE) are always manual and confirmed.

## Settings (General tab)

- **Status** - enable the module.
- **Minimum table rows** - tables with fewer rows are skipped by the heuristic
  (default 500). Known-good OpenCart indexes are still suggested.
- **Max new indexes per table** - safety cap (default 8).
- **Tables scope** - All / OpenCart (prefixed) / Custom (other prefix).

## Usage

1. Install and open the module under **Extensions > Modules**.
2. On the **General** tab set the thresholds and Save.
3. On the **Analysis** tab click **Analyze database**.
4. Review the recommendations, tick the ones you want, and **Apply selected**
   (or **Apply all recommended**).
5. Use the diagnostics section to convert MyISAM tables or optimize fragmented ones.
6. The **Applied** tab shows every module-created index and lets you drop them or
   roll back everything.

> Always back up your database before applying changes on a production store.

## License

This project is licensed under the [GNU General Public License version 3 (GPLv3)](https://github.com/alexwaha/opencart-bundle/blob/main/LICENSE).

The rights and authorship of this software belong to the developer Oleksandr Vakhovskyi (Alexwaha), website [https://alexwaha.com](https://alexwaha.com)

**Paid Services:** Setting up the module by the author, customization, resolving conflicts with other modules, integration with a template, integration with another module in your project - only on a **paid basis**, please contact us below.

[![Telegram](https://img.shields.io/badge/Telegram-alexwaha_dev-9cf)](https://t.me/alexwaha_dev)
