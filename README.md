# Travel Agency Finance Tracker

A PHP/Laravel and MySQL application for recording income and expenditure, managing category budgets and viewing cash-flow forecasts.

Developed by **Mohamed Kemouche** as a coursework and portfolio project, with a family travel agency as its intended business use case.

**Stack:** PHP 8.2-compatible 8.x · Laravel 12 · MySQL · Blade · JavaScript · Chart.js

**Status:** Local demonstration; authentication and forecasting limitations are documented below.

[Features](#features) · [Local setup](#local-setup) · [Usage](#usage) · [Forecasting](#forecasting) · [Project structure](#project-structure) · [Limitations](#limitations)

## Features

- Account registration, sign-in and demonstration verification/password-reset flows.
- Create, view, edit and delete income and expense transactions.
- Filter transactions by type, category, date range and search text.
- View paginated records and totals for the selected filters.
- Export filtered transactions to CSV.
- Manage categories and monthly spending budgets.
- View income, expenditure and category-spending charts.
- Explore three-month forecasts and an interactive what-if simulator.
- Update account settings.

Application pages and dashboard JSON endpoints require authentication. Transaction operations use the signed-in user's identifier, and server-side validation checks amounts, dates and category access.

## Technology

| Component | Implementation |
| --- | --- |
| Runtime | PHP `^8.2`, as declared in `composer.json` |
| Framework | Laravel `^12.0` |
| Application structure | Routing, middleware, Eloquent models and Blade templates |
| Database | MySQL; the existing setup documentation also supports MariaDB |
| Front end | HTML, CSS, JavaScript and a locally bundled Chart.js file |
| Dependencies | Composer |

The manifest specifies version constraints rather than a verified installed patch version. Front-end assets are supplied in `public/`; the inspected project has no separate npm build configuration.

## Local setup

These instructions describe a **fresh local development installation** using an empty database. They are based on the source and its configuration; a clean installation has not yet been executed to verify the complete workflow.

### 1. Install prerequisites

Install a PHP 8.x release meeting the `^8.2` constraint, Composer, and MySQL or MariaDB. Enable the PHP MySQL driver and the extensions required by the Composer dependencies. Start the database service.

Open PowerShell in the project folder containing `artisan` and `composer.json`.

### 2. Install dependencies

```powershell
composer install
```

### 3. Configure the environment

Copy the provided example only if a local `.env` does not already exist:

```powershell
if (-not (Test-Path .env)) {
    Copy-Item .env.example .env
}
```

Edit `.env` to match your local database. A typical local configuration starts with:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=finance_tracker
APP_URL=http://localhost:8000
```

Set `DB_USERNAME` and `DB_PASSWORD` to your own local database credentials. Keep `.env`, database credentials and application keys out of public repositories.

For this new installation, generate the application key:

```powershell
php artisan key:generate
```

### 4. Create the database and tables

Create an empty database using your database administration tool. For example:

```sql
CREATE DATABASE finance_tracker
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
```

Ensure `DB_DATABASE` matches its name, then run:

```powershell
php artisan migrate
```

Optionally populate a disposable development database with demonstration records. Set the private `DEMO_PASSWORD` described below first:

```powershell
php artisan db:seed
```

Before seeding, set `DEMO_PASSWORD` in your private `.env` to a password of your own with at least 12 characters, including a letter and a number. There is no bundled login password. If configuration is cached, run `php artisan config:clear` after changing it.

The seeders create fictional users, categories, transactions and budgets. Sign in as `demo` or `demo_secondary` using your locally chosen password; `demo_unverified` exercises verification. The sample financial history uses fixed historical dates, so a current-month view may be empty. Verification/reset codes are generated only at runtime.

`php artisan migrate:fresh --seed` deletes existing tables before recreating them. It is not needed for routine startup. This public source uses migrations and sanitised seeders rather than a database export.

### 5. Start the application

```powershell
php artisan serve
```

Open the address printed by Artisan, normally `http://localhost:8000`.

## Usage

1. Register a local account and complete the demonstration verification flow.
2. Create categories and add fictional income or expense transactions.
3. Use the transaction filters to review records and export a CSV.
4. Set category budgets and inspect the dashboard charts.
5. Explore the forecasts and what-if simulator with sample data.

### Demonstration authentication

Verification and password-reset codes appear in an onscreen banner and are written to the application log. **No verification or reset email is sent.**

The source includes password hashing, session regeneration, code expiry and attempt limits, but displaying reset codes is unsuitable for protecting real accounts on a public deployment. Replace this demonstration delivery mechanism before hosting the application for real users.

## Forecasting

`app/Services/Forecast.php` implements ordinary least-squares linear regression over monthly transaction totals. The dashboard API returns three future months of income, expenditure and net-balance projections, plus a projected running balance.

When more than one month is available, the current month is excluded from fitting because it may be incomplete. Income and expenditure projections are limited to non-negative values; net-balance projections can be negative.

The reported **R² describes the fit to historical observations**. It does not measure a calibrated probability or confidence interval for future predictions. The model provides a simple trend projection and does not explicitly account for seasonality or unexpected business changes. Forecast accuracy has not been established through a documented out-of-sample evaluation.

## Project structure

| Path | Purpose |
| --- | --- |
| `routes/web.php` | Page routes and dashboard JSON endpoints |
| `app/Http/Controllers/` | Account, transaction, category, budget and dashboard logic |
| `app/Http/Requests/` | Transaction validation |
| `app/Models/` | Eloquent database models |
| `app/Services/Forecast.php` | Regression calculations |
| `database/migrations/` | Database schema |
| `database/seeders/` | Demonstration records |
| `resources/views/` | Blade templates |
| `public/` | Entry point, CSS, JavaScript and Chart.js |

## Validation

Run the standalone forecast checks without installing Laravel dependencies:

```powershell
php tests/forecast_smoke.php
```

The publication copy passed PHP syntax checks across all 55 PHP files and 19 forecast checks on PHP 8.2.12. These cover known linear trends, constant/short/empty histories and non-negative versus signed projections. They do not establish forecast accuracy or validate database and browser workflows.

## Limitations

- Authentication code delivery is designed for a local demonstration.
- Forecasts are simple historical trend extrapolations with no verified predictive-accuracy claim.
- A complete dependency installation, database setup, browser workflow and public deployment have not been verified. The checks above cover syntax and the standalone forecast calculation only.
- Demonstrations should use fictional transactions rather than actual customer or agency financial records.

## Project context and support

The original project documentation identifies the application as **DG1IAD Internet Applications and Databases — Portfolio 3**. Its intended business context is a family travel agency; this does not imply a verified production deployment.

When reporting an issue, include your PHP/database versions, reproduction steps and a sanitised error message. Do not attach `.env`, account credentials or real financial records.
