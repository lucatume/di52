ARG PHP_VERSION
FROM php:${PHP_VERSION}-cli-alpine
ARG XDEBUG_OUTPUT_DIR
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/bin/
RUN install-php-extensions xdebug
RUN echo "date.timezone=UTC" >> /usr/local/etc/php/conf.d/docker-php-config.ini
RUN export PHP_MAJOR_VERSION=""
RUN if [ "$(printf %.1s "${PHP_VERSION}")" != "8" ]; then echo $'xdebug.profiler_enable=1\n\
xdebug.profiler_output_dir='$XDEBUG_OUTPUT_DIR$'\n' \
 >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini; fi
RUN if [ "$(printf %.1s "${PHP_VERSION}")" = "8" ]; then echo $'xdebug.start_with_request=yes\n\
xdebug.mode=profile\n\
xdebug.output_dir='$XDEBUG_OUTPUT_DIR$'\n' \
 >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini; fi
 RUN cat /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && php -v
