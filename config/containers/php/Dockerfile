ARG PHP_VERSION=5.6

FROM composer:2.2 as composer

FROM php:${PHP_VERSION}-cli-alpine

ARG XDEBUG_SRC='xdebug'

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/bin/
# If the XDEBUG_SRC starts with http, download, extract and prepare it.
RUN if echo ${XDEBUG_SRC} | grep -Eq '^http'; then \
        curl -k -o /tmp/xdebug.tgz ${XDEBUG_SRC} && \
        XDEBUG_VERSION=$(echo ${XDEBUG_SRC} | sed -e 's/.*xdebug-\(.*\)\.tgz/\1/') && \
        tar xzf /tmp/xdebug.tgz -C /tmp && \
        mv /tmp/package.xml /tmp/xdebug-${XDEBUG_VERSION} && \
        rm /tmp/xdebug.tgz && \
        XDEBUG_SRC=/tmp/xdebug-${XDEBUG_VERSION}; \
    fi \
    && install-php-extensions ${XDEBUG_SRC} \
    && apk add gettext \
    && rm -rf /var/cache/apk/* \
    && rm /usr/bin/install-php-extensions \
    && rm -rf /tmp/xdebug.tgz /tmp/xdebug-${XDEBUG_VERSION}

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
