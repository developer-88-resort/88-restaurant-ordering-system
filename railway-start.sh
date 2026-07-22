#!/bin/bash
# Web-service boot script for Railway. The Reverb and queue-worker services
# (added separately in the Railway dashboard from this same repo/image) skip
# this file entirely — override their own "Start Command" instead:
#   Reverb service : php artisan reverb:start --host=0.0.0.0 --port=$PORT
#   Queue service  : php artisan queue:work --sleep=3 --tries=3
set -e

php artisan storage:link || true
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

php artisan serve --host=0.0.0.0 --port="${PORT:-8080}"
