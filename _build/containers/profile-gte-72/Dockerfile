# Read the PHP version to build for from the build arguments.
ARG PHP_VERSION
FROM php:${PHP_VERSION}-cli-alpine

# Install the XDebug extension that is current for the PHP version the image is being built for.
# XDebug 2.9.* on PHP 7.1 and below, XDebug 3.* on PHP 7.2 and above.
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/bin/
RUN install-php-extensions xdebug
# Some tests will complain if the timezone is not set; set it now.
RUN echo "date.timezone=UTC" >> /usr/local/etc/php/conf.d/docker-php-config.ini

# Configure XDebug.
ARG XDEBUG_OUTPUT_DIR
RUN echo $'xdebug.start_with_request=yes\n\
xdebug.mode=profile\n\
xdebug.output_dir='$XDEBUG_OUTPUT_DIR$'\n' \
 >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
# Output the PHP and XDebug version for build debugging purposes.
 RUN cat /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && php -v
