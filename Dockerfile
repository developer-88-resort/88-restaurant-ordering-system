# Testing-purposes deploy image for Railway — single container running the
# Laravel web app via `php artisan serve` (matches local dev), built once and
# reused by the separate Reverb/queue Railway services via a different start
# command override in each service's dashboard settings (no rebuild needed).
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

# Railway auto-injects Variables of the same name as build ARGs for
# Dockerfile builds — these five must be set as Variables in the Railway
# dashboard, because Vite bakes VITE_* values into the compiled JS at build
# time, not at runtime like the rest of the app's config.
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

RUN chmod +x railway-start.sh \
    && mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 8080
CMD ["bash", "railway-start.sh"]
