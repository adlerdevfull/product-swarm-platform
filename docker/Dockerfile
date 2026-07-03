FROM php:8.2-fpm-alpine AS base

RUN apk add --no-cache \
    nginx \
    git \
    curl \
    unzip \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    rabbitmq-c-dev \
    linux-headers \
    autoconf \
    gcc \
    g++ \
    make \
    && docker-php-ext-install pdo pdo_mysql mbstring xml opcache sockets \
    && pecl install amqp redis \
    && docker-php-ext-enable amqp redis \
    && apk del autoconf gcc g++ make

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["/entrypoint.sh"]

# Production image for Swarm (code baked in)
FROM base AS production

COPY composer.json composer.lock* ./
RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist --no-security-blocking 2>/dev/null \
    || composer update --no-dev --no-scripts --no-interaction --prefer-dist --no-security-blocking

COPY . .
RUN mkdir -p var/cache var/log config/jwt \
    && chmod -R 775 var \
    && composer dump-autoload --optimize 2>/dev/null || true

ENV APP_ENV=prod
ENV APP_DEBUG=0
