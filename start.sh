#!/bin/bash
# Web-service boot script (Render). The Reverb and queue-worker services
# (added separately in Render's dashboard from this same repo/image) skip
# this file entirely — override their own "Start Command" instead:
#   Reverb service : php artisan reverb:start --host=0.0.0.0 --port=$PORT
#   Queue service  : php artisan queue:work --sleep=3 --tries=3
#
# Deliberately NOT using `set -e`: this is a testing deployment, and a failed
# migration or cache step should never prevent the web server itself from
# starting — otherwise the container has nothing listening on $PORT at all,
# which is much harder to debug than a running site missing a cache.
php artisan storage:link 2>&1 || true
php artisan migrate --force 2>&1 || true
php artisan config:cache 2>&1 || true
php artisan route:cache 2>&1 || true
php artisan view:cache 2>&1 || true

exec php artisan serve --host=0.0.0.0 --port="${PORT:-8080}"
