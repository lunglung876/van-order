# ./docker/php/Dockerfile
FROM php:7.2-fpm

RUN docker-php-ext-install pdo_mysql sysvsem

WORKDIR /usr/src/app

COPY code /usr/src/app

RUN PATH=$PATH:/usr/src/apps/vendor/bin:bin
