# syntax=docker/dockerfile:1.7
ARG FRANKENPHP_VERSION=1.4-php8.3

# -----------------------------------------------------------------------------
# Vendor stage — install Composer deps in a clean layer (cached separately).
# -----------------------------------------------------------------------------
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
        --no-dev \
        --no-interaction \
        --no-progress \
        --prefer-dist \
        --optimize-autoloader

# -----------------------------------------------------------------------------
# Runtime stage — FrankenPHP with pdo_pgsql.
# -----------------------------------------------------------------------------
FROM dunglas/frankenphp:${FRANKENPHP_VERSION} AS runtime

# install-php-extensions ships with the FrankenPHP image.
RUN install-php-extensions pdo_pgsql opcache

WORKDIR /app

# Copy app source first so vendor/ overlay wins.
COPY . /app
COPY --from=vendor /app/vendor /app/vendor

# Generate minified CSS/JS once, baked into the image (served when APP_ENV=production).
RUN php bin/minify.php

# FrankenPHP's default entrypoint reads /etc/caddy/Caddyfile.
COPY docker/Caddyfile /etc/caddy/Caddyfile

# Production-tuned PHP ini (opcache + sane defaults).
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# FrankenPHP runs as root by default and drops to www-data internally.
EXPOSE 80 443 443/udp
