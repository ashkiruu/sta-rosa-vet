#!/bin/bash
set -e

# Clear all cached configurations, routes, and views dynamically on boot
# (Get rid of this once Render is disabled)
php artisan optimize:clear || true

echo "Running database migrations and seeders..."
# Runs migrations and executes DatabaseSeeder automatically
php artisan migrate:fresh --seed --force
# Uncomment this if Render is discarded
#php artisan migrate --force

echo "Starting Apache..."
exec apache2-foreground