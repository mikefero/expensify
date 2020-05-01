FROM php:7.4-fpm-alpine

RUN apk add --update --no-cache --virtual .build-dependencies $PHPIZE_DEPS && \
    docker-php-ext-install sockets && \
    pecl install apcu && \
    docker-php-ext-enable apcu && \
    pecl clear-cache && \
    apk del .build-dependencies && \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    # fix work iconv library with alphine
    apk add --no-cache --repository http://dl-cdn.alpinelinux.org/alpine/edge/community/ --allow-untrusted gnu-libiconv

ENV LD_PRELOAD /usr/lib/preloadable_libiconv.so php
WORKDIR /usr/share/nginx/html
