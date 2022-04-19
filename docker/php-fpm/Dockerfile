# subscribers/php-fpm/Dockerfile
FROM php:fpm-alpine
RUN apk --update --no-cache add git
COPY --from=composer /usr/bin/composer /usr/bin/composer
WORKDIR /var/www
CMD composer install; php-fpm;
EXPOSE 9000
RUN docker-php-ext-install mysqli pdo pdo_mysql
