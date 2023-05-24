FROM composer as composer
COPY composer.json composer.json
COPY composer.lock composer.lock
RUN composer install --no-interaction

FROM php:8.1-fpm-alpine

COPY ./ /app
COPY --from=composer /app/vendor /app/vendor
