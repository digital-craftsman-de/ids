# Build with
# docker buildx build --platform linux/arm64,linux/amd64 -t ghcr.io/digital-craftsman-de/ids-php-8.4:latest ./.docker/php-8.4 --push

FROM php:8.4.0RC3-fpm-alpine

RUN apk add --update \
    autoconf \
    g++ \
    make \
    linux-headers \
    curl

# Add php extensions
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod uga+x /usr/local/bin/install-php-extensions && sync

# Debugging
# XDebug is not yet compatible with PHP 8.4, therefore we need to build it from source. This will be replaced with the following line once XDebug is compatible with PHP 8.4:
# RUN pecl install xdebug-3.4.0 && docker-php-ext-enable xdebug
RUN apk add git
#RUN apk add -U php8-dev --repository=http://dl-cdn.alpinelinux.org/alpine/edge/testing
RUN git clone https://github.com/xdebug/xdebug.git \
    && cd xdebug \
    && phpize \
    && ./configure \
    && make \
    && make install \
    && cd .. \
    && docker-php-ext-enable xdebug
#RUN install-php-extensions xdebug

# Ids package
RUN install-php-extensions uuid

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    ln -s $(composer config --global home) /root/composer
ENV PATH=$PATH:/root/composer/vendor/bin COMPOSER_ALLOW_SUPERUSER=1

ADD php.ini /etc/php/conf.d/
ADD php.ini /etc/php/cli/conf.d/
ADD php.ini /usr/local/etc/php
ADD php-fpm.conf /etc/php/php-fpm.d/
ADD docker-php-ext-xdebug.ini /usr/local/etc/php/conf.d/

WORKDIR /var/www/html

CMD ["php-fpm", "-F"]
