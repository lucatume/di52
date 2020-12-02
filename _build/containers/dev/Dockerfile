ARG PHP_VERSION
FROM php:${PHP_VERSION}-cli-alpine
ARG XDEBUG_REMOTE_HOST='host.docker.internal'
ARG XDEBUG_REMOTE_PORT='9009'
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/bin/
RUN install-php-extensions xdebug
RUN echo "date.timezone=UTC" >> /usr/local/etc/php/conf.d/docker-php-config.ini
RUN export PHP_MAJOR_VERSION=""
RUN if [ "$(printf %.1s "${PHP_VERSION}")" != "8" ]; then echo $'xdebug.remote_autostart=1\n\
xdebug.remote_enable=1\n\
xdebug.remote_host='$XDEBUG_REMOTE_HOST$'\n\
xdebug.remote_port='$XDEBUG_REMOTE_PORT$'\n' \
 >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini; fi
RUN if [ "$(printf %.1s "${PHP_VERSION}")" = "8" ]; then echo $'xdebug.start_with_request=1\n\
xdebug.mode=debug\n\
xdebug.client_host='$XDEBUG_REMOTE_HOST$'\n\
xdebug.client_port='$XDEBUG_REMOTE_PORT$'\n' \
 >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini; fi
 RUN cat /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && php -v
