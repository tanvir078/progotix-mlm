#!/usr/bin/env sh
set -eu

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache

if [ "${SERVICE_MODE:-web}" = "worker" ]; then
  php artisan queue:work --verbose --tries=3 --timeout=90 --sleep=3
  exit 0
fi

php artisan migrate --force
php artisan optimize

exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"
