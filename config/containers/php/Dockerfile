ARG PHP_VERSION=5.6

FROM composer:2.2 as composer

FROM php:${PHP_VERSION}-cli-alpine

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/bin/
RUN install-php-extensions xdebug && apk add gettext && rm -rf /var/cache/apk/*
RUN rm /usr/bin/install-php-extensions

ARG XDEBUG_REMOTE_HOST='host.docker.internal'
ARG XDEBUG_REMOTE_PORT='9009'
ARG WORKDIR='/app'
COPY php.ini /tmp/php.ini
COPY di52-entrypoint.sh /usr/local/bin/di52-entrypoint.sh
RUN envsubst < /tmp/php.ini > /usr/local/etc/php/conf.d/999-docker-php-config.ini &&\
    rm /tmp/php.ini &&\
    chmod -R a+rwx /usr/local/etc/php/conf.d &&\
    chmod a+x /usr/local/bin/di52-entrypoint.sh

COPY --from=composer /usr/bin/composer /usr/bin/composer
RUN chmod a+x /usr/bin/composer
ENTRYPOINT ["/usr/local/bin/di52-entrypoint.sh"]
CMD ["-v"]
