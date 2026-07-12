#!/usr/bin/env bash
#
# Production deploy helper for Ronald Ross High Cost Billing System.
# Run from the project root: bash scripts/deploy.sh
#
set -euo pipefail

echo "==> Verifying latest code is present..."
grep -q "fromRouteParameter" app/Http/Middleware/EnsureUserHasRole.php \
    || { echo "ERROR: role middleware fix missing — run 'git pull' first."; exit 1; }
grep -q "case Nurse" app/Enums/UserRole.php \
    || { echo "ERROR: legacy nurse enum alias missing — run 'git pull' first."; exit 1; }

echo "==> Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader

echo "==> Installing Node dependencies and building assets..."
npm ci
npm run build

echo "==> Running database migrations..."
php artisan migrate --force

echo "==> Clearing and rebuilding caches..."
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Reloading PHP-FPM (clears OPcache)..."
if command -v systemctl >/dev/null 2>&1; then
    systemctl reload php8.2-fpm 2>/dev/null \
        || systemctl reload php8.3-fpm 2>/dev/null \
        || systemctl reload php-fpm 2>/dev/null \
        || echo "    (php-fpm reload skipped — restart manually if errors persist)"
fi

echo "==> Deploy complete."
echo "    Verify: php artisan route:list --path=visits"
echo "    Health check: /up"
