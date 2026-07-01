# Ronald Ross High Cost Billing System

Web application for **Ronald Ross General Hospital — High Cost Section**. Staff use it to register patients, load member and company deposits, post bills at point of care, and run financial reports.

Built with **Laravel 12**, **PostgreSQL**, **Tailwind CSS**, and **Font Awesome** (self-hosted via Vite).

## Requirements

- PHP 8.2+ with extensions: `pdo_pgsql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`
- PostgreSQL 14+
- Composer 2
- Node.js 20+ and npm (for building front-end assets)

## Local development

```bash
# 1. Install dependencies
composer install
cp .env.example .env
php artisan key:generate

# 2. Configure PostgreSQL in .env, then create the database
#    DB_DATABASE=billing_system_db

# 3. Run migrations and seed default staff accounts
php artisan migrate
php artisan db:seed

# 4. Build assets and start the dev server
npm install
npm run build   # or: npm run dev
php artisan serve
```

Open `http://localhost:8000` and sign in with one of the seeded accounts (password: `password`):

| Role | Email |
|------|-------|
| Administrator | `admin@ronaldross.local` |
| Accounts Officer | `accounts@ronaldross.local` |
| Registry Clerk | `registry@ronaldross.local` |
| Nurse | `nurse@ronaldross.local` |

Public registration is disabled. Staff accounts are created by administrators only.

## Running tests

Tests use an in-memory SQLite database (configured in `phpunit.xml`):

```bash
php artisan test
```

## Production deployment

### 1. Server preparation

- Point the web server document root to `public/`
- Ensure `storage/` and `bootstrap/cache/` are writable by the web server user
- Install PHP, PostgreSQL, Composer, and Node.js on the server (Node only needed at deploy time for `npm run build`)

### 2. Environment file

Copy `.env.example` to `.env` on the server and set production values:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://billing.ronaldross.local

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_DATABASE=billing_system_db
DB_USERNAME=...
DB_PASSWORD=...

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database

LOG_LEVEL=warning
```

Optional hospital thresholds (defaults shown):

```env
LARGE_DEPOSIT_THRESHOLD=10000
LOW_BALANCE_THRESHOLD=1000
```

Generate the application key once:

```bash
php artisan key:generate
```

### 3. Deploy script

From the project root on the server:

```bash
bash scripts/deploy.sh
```

The script runs `composer install`, migrations, config/route/view caching, and `npm run build`. Review `scripts/deploy.sh` before first use.

### 4. Manual deploy steps

If you prefer to run commands yourself:

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Seed staff accounts only on first install (also seeds the default service catalogue):

```bash
php artisan db:seed --force
```

To refresh only the service catalogue after deployment:

```bash
php artisan db:seed --class=BillableServiceSeeder --force
```

**Change the default `password` for all seeded users before go-live.**

### 5. Health check

Laravel exposes a health endpoint at `/up` for load balancers and uptime monitors.

### 6. Scheduled tasks (optional)

If you add scheduled commands later, add this cron entry:

```
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

## Role access

| Module | Registry Clerk | Accounts | Nurse | Administrator |
|--------|:---:|:---:|:---:|:---:|
| Patient registration & visits | ✓ | | | 👁️ |
| Clinical notes | | | ✓ | 👁️ |
| Membership payments & deposits | | ✓ | | 👁️ |
| Company accounts (create) | | ✓ | | 👁️ |
| Post bills (via visits) | ✓ | | | 👁️ |
| Receipts & reports | | ✓ | | ✓ |
| Users, settings, audit log, service catalogue | | | | ✓ |

👁️ = View only

## Project structure (key paths)

| Path | Purpose |
|------|---------|
| `app/Services/` | Business logic: billing, deposits, reports, audit |
| `app/Http/Requests/` | Form validation |
| `config/hospital.php` | Branding and billing thresholds |
| `database/migrations/` | Schema |
| `database/seeders/DatabaseSeeder.php` | Default staff users |
| `resources/views/` | Blade templates |

## Security notes

- Do not commit `.env` or expose `APP_DEBUG=true` in production
- Use HTTPS in production (`APP_URL` must match)
- Rotate seeded passwords and restrict database access to the application server
- Session lifetime defaults to 120 minutes (`SESSION_LIFETIME`)

## License

Proprietary — Ronald Ross General Hospital.
