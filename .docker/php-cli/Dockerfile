FROM php:7.4-cli-alpine as base

ARG XDEBUG_PORT
ARG XDEBUG_HOST

RUN set -xe \
        && apk add --no-cache --virtual .build-deps \
            $PHPIZE_DEPS \
            bash \
            git \
            curl \
            g++ \
            make \
            autoconf ;

RUN docker-php-ext-install mysqli bcmath sockets \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY ./php-cli/configurations/* /usr/local/etc/php/conf.d/

COPY ./shell/ /tmp/scripts/
RUN chmod -R 777 /tmp/scripts/

RUN docker-php-source extract \
    && pecl install redis xdebug \
    && docker-php-ext-enable redis xdebug \
    && /tmp/scripts/distributeConfig.sh /usr/local/etc/php/conf.d/zz-xdebug.ini "{XDEBUG_PORT}" "${XDEBUG_PORT}" \
    && /tmp/scripts/distributeConfig.sh /usr/local/etc/php/conf.d/zz-xdebug.ini "{XDEBUG_HOST}" "${XDEBUG_HOST}" \
    && docker-php-source delete

RUN rm -rf /tmp/*