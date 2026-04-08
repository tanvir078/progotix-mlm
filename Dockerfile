FROM php:8.3-cli-alpine

WORKDIR /app

RUN apk add --no-cache \
    bash \
    curl \
    git \
    icu-dev \
    libpq-dev \
    linux-headers \
    nodejs \
    npm \
    oniguruma-dev \
    unzip \
    zip \
    && docker-php-ext-install bcmath intl pcntl pdo_pgsql \
    && rm -rf /var/cache/apk/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./
RUN composer install \
    --no-scripts \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --optimize-autoloader

COPY package.json package-lock.json* ./
RUN if [ -f package-lock.json ]; then npm ci; else npm install; fi

COPY . .

RUN composer dump-autoload --optimize \
    && npm run build \
    && php artisan package:discover --ansi \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chmod +x docker/entrypoint.sh

ENV APP_ENV=production
ENV APP_DEBUG=false
ENV PORT=10000
ENV SERVICE_MODE=web

EXPOSE 10000

CMD ["./docker/entrypoint.sh"]
