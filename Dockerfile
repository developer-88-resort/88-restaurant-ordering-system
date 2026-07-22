# Testing-purposes deploy image (Render) — single container running the
# Laravel web app via `php artisan serve` (matches local dev). The Reverb
# and queue-worker services (added separately in Render's dashboard from
# this same repo/image) reuse this build with a different Start Command.
FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
        git curl unzip \
        libpng-dev libjpeg62-turbo-dev libfreetype6-dev libzip-dev libonig-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo_mysql zip mbstring exif bcmath \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app
COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# Render auto-injects env vars matching declared ARGs during the build —
# these five must be set in the web service's Environment tab, because Vite
# bakes VITE_* values into the compiled JS at build time, not at runtime
# like the rest of the app's config.
ARG VITE_APP_NAME
ARG VITE_REVERB_APP_KEY
ARG VITE_REVERB_HOST
ARG VITE_REVERB_PORT
ARG VITE_REVERB_SCHEME
ENV VITE_APP_NAME=$VITE_APP_NAME \
    VITE_REVERB_APP_KEY=$VITE_REVERB_APP_KEY \
    VITE_REVERB_HOST=$VITE_REVERB_HOST \
    VITE_REVERB_PORT=$VITE_REVERB_PORT \
    VITE_REVERB_SCHEME=$VITE_REVERB_SCHEME

RUN npm ci && npm run build && rm -rf node_modules

# Plain `mkdir -p dir/{a,b,c}` brace expansion is a bash-ism — Docker's RUN
# uses /bin/sh (dash) by default, which takes it literally and creates one
# oddly-named directory instead of three, silently leaving
# storage/framework/views missing (Laravel's view.compiled config —
# realpath(storage_path('framework/views')) — then resolves to false,
# causing "View path not found" on every view:cache/view:clear call and on
# every rendered page). Each directory is listed explicitly to avoid that.
RUN chmod +x start.sh \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 8080
CMD ["bash", "start.sh"]
