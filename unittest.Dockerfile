FROM composer as composer
RUN composer global require hirak/prestissimo --no-plugins --no-scripts
COPY composer.json composer.json
COPY composer.lock composer.lock
RUN composer install --no-interaction --ignore-platform-reqs

FROM php:7.1-fpm-alpine

COPY ./ /app
COPY --from=composer /app/vendor /app/vendor
