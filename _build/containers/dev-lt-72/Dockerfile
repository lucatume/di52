# Read the PHP version to build for from the build arguments.
ARG PHP_VERSION

FROM composer:2 as composer

FROM php:${PHP_VERSION}-cli-alpine

# Install the XDebug extension that is current for the PHP version the image is being built for.
# XDebug 2.9.* on PHP 7.1 and below, XDebug 3.* on PHP 7.2 and above.
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/bin/
RUN install-php-extensions xdebug
# Some tests will complain if the timezone is not set; set it now.
RUN echo "date.timezone=UTC" >> /usr/local/etc/php/conf.d/docker-php-config.ini

# Configure XDebug.
ARG XDEBUG_REMOTE_HOST='host.docker.internal'
ARG XDEBUG_REMOTE_PORT='9009'
RUN echo $'xdebug.remote_autostart=1\n\
xdebug.remote_enable=1\n\
xdebug.remote_host='$XDEBUG_REMOTE_HOST$'\n\
xdebug.remote_port='$XDEBUG_REMOTE_PORT$'\n' \
>> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
# Output the PHP and XDebug version for build debugging purposes.
RUN cat /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && php -v
COPY --from=composer /usr/bin/composer /usr/bin/composer
RUN chmod a+x /usr/bin/composer && composer --version
