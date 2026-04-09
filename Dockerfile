FROM php:8.3-cli

WORKDIR /app

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        bash \
        curl \
        git \
        libicu-dev \
        libonig-dev \
        libpq-dev \
        libxml2-dev \
        libzip-dev \
        nodejs \
        npm \
        unzip \
        zip \
    && docker-php-ext-install bcmath intl mbstring pcntl pdo_pgsql xml zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_MEMORY_LIMIT=-1

COPY composer.json composer.lock ./
RUN composer install \
    --no-scripts \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --optimize-autoloader

COPY package.json ./
RUN npm install

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
