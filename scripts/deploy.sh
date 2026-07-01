#!/usr/bin/env bash
#
# Production deploy helper for Ronald Ross High Cost Billing System.
# Run from the project root: bash scripts/deploy.sh
#
set -euo pipefail

echo "==> Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader

echo "==> Installing Node dependencies and building assets..."
npm ci
npm run build

echo "==> Running database migrations..."
php artisan migrate --force

echo "==> Caching configuration, routes, and views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Deploy complete."
echo "    Remember to change default seeded passwords before go-live."
echo "    Health check: /up"
