# Retrospective: How I would write this in 2025

> **⚠️ Note to Reviewers:**
> This Garments Barcode Application was built circa 2017 on PHP 5.6 with procedural scripts and jQuery-heavy pages. While the legacy logic still runs, it does not reflect my current engineering standards or what modern PHP 8.x tooling makes possible.

If I were rebuilding this today with PHP 8.3, I would modernize each outdated area like so:

* **Outdated:** Raw `mysqli` queries with interpolated strings inside pages such as `inventory.php` and `checkout.php`.
    * **Modern PHP 8.x fix:** Introduce Doctrine ORM or Laravel's Eloquent with typed models, parameter binding, and database migrations to eliminate SQL injection risk and improve maintainability.

* **Outdated:** Manual `require` chains and global functions spread across flat PHP files.
    * **Modern PHP 8.x fix:** Organize the codebase into namespaced modules with PSR-4 autoloading via Composer, leveraging services, controllers, and repositories.

* **Outdated:** No strict typing, no return types, and loosely-typed arrays being passed between views and business logic.
    * **Modern PHP 8.x fix:** Enable `declare(strict_types=1);`, use typed properties, enums for statuses, readonly DTOs, and expressive value objects to make invariants explicit.

* **Outdated:** Monolithic page scripts that mix HTML rendering, request validation, and data persistence in one file.
    * **Modern PHP 8.x fix:** Adopt a framework like Symfony or Laravel (or Slim + custom layers) with HTTP controllers, FormRequest-style validation, and dedicated service/repository layers for clean separation and testability.

* **Outdated:** Hard-coded configuration (DB paths, credentials) and ad-hoc session handling without CSRF protection.
    * **Modern PHP 8.x fix:** Load environment-specific settings from `.env` files via `symfony/dotenv`, use Laravel/Symfony CSRF middleware, and centralize auth/session logic behind typed guards.

* **Outdated:** No automated tests or CI, making regressions easy.
    * **Modern PHP 8.x fix:** Cover the domain with PHPUnit or Pest tests, add feature smoke tests for billing/inventory flows, and run them in GitHub Actions on every PR.

---

# Garments Barcode Application

## Overview
This repository contains a lightweight point-of-sale workflow tailored for garment retailers who print barcode stickers, maintain stock, and reconcile sales daily. The UI is powered by Bootstrap 3, jQuery, and Chart.js, while persistence is handled by a file-backed SQLite database (`data/safg.sqlite3`). PHP scripts inside the project root expose AJAX endpoints (`ajax-req-handler.php`) that serve inventory counts, sales summaries, and transaction logs to the dashboard in `index.php`.

### Core modules
- **Inventory & Product Master** (`inventory.php`, `products.php`, `fetch-product.php`) – manage SKU metadata, categories, and stock adjustments; print barcodes through the bundled `php-barcode-generator-master` utilities.
- **Checkout & Billing** (`checkout.php`, `update_bill.php`) – scan barcodes, build bills, compute totals/discounts, and persist completed invoices.
- **Sales Dashboard** (`index.php`) – visualize recent performance via Chart.js, filter sales by date range, and export/print reports.
- **Transaction Ledger** (`transaction_rec.php`) – capture non-sales cash movements (accrued/outstanding income) with mode-of-payment tagging.
- **Settings & Backups** (`settings.php`, `backup.php`, `old_to_new_db.php`) – migrate legacy databases, toggle UI behaviors, and snapshot the SQLite file for safekeeping.

## Feature highlights
- Fast barcode-driven checkout with auto-complete and quantity adjustments.
- Real-time stock depletion and low-inventory surfacing on the dashboard.
- Printable sales summaries and transaction receipts via `jQuery-Print`.
- Role-gated login/logout flow backed by PHP sessions.
- Lightweight reporting of accrued vs. outstanding income.
- Optional migration tooling to move from older schema versions into the current SQLite layout.

## Tech stack
- **Backend:** PHP 5.6+ (tested up to PHP 8.2) with SQLite3 extension.
- **Frontend:** Bootstrap 3, Font Awesome 4.7, jQuery 3.2.1, Chart.js 2.x, jquery-confirm, bootstrap-toggle.
- **Barcode:** [picqer/php-barcode-generator](php-barcode-generator-master/) for rendering printable labels.
- **Printing:** [jQuery.print](jQuery-Print/) for in-browser document exports.

## Repository layout
```text
├── index.php                      # Analytics dashboard + quick transaction entry
├── checkout.php                   # Billing workflow driven by barcode scans
├── inventory.php                  # Stock table with inline edits and search
├── ajax-req-handler*.php          # Central AJAX router for dashboard/widgets
├── data/safg.sqlite3              # Primary SQLite database (do not commit sensitive data)
├── php-barcode-generator-master/  # Third-party barcode rendering library
├── jquery-confirm-master/         # Vendored UI plugin
├── jQuery-Print/                  # Vendored UI plugin
├── bootstrap-toggle/              # Vendored UI plugin
├── images/                        # Branding assets
├── fonts/                         # Custom fonts
├── style.css                      # Custom theme tweaks
├── backup.php                     # Database backup helper
├── old_to_new_db.php              # Database migration helper
└── LICENSE
