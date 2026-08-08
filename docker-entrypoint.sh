#!/bin/bash
set -e

echo "Running database migrations and seeders..."
# Runs migrations and executes DatabaseSeeder automatically
php artisan migrate:fresh --seed --force
# Uncomment this if Render is discarded
#php artisan migrate --force

echo "Starting Apache..."
exec apache2-foreground