FROM php:8.3.0alpha1-fpm-alpine3.18

RUN apk add --update \
    autoconf \
    g++ \
    make \
    linux-headers \
    curl

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

# XDebug is not yet compatible with PHP 8.3
#RUN pecl install xdebug-3.3.0 && docker-php-ext-enable xdebug

RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions uuid

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    ln -s $(composer config --global home) /root/composer
ENV PATH=$PATH:/root/composer/vendor/bin COMPOSER_ALLOW_SUPERUSER=1

ADD php.ini /etc/php/conf.d/
ADD php.ini /etc/php/cli/conf.d/
ADD php.ini /usr/local/etc/php
ADD php-fpm.conf /etc/php/php-fpm.d/

WORKDIR /var/www/html

CMD ["php-fpm", "-F"]
