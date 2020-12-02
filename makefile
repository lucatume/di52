# Use bash as shell.
SHELL := /bin/bash

# If you see pwd_unknown showing up, this is why. Re-calibrate your system.
PWD ?= pwd_unknown

# PROJECT_NAME defaults to name of the current directory.
PROJECT_NAME = $(notdir $(PWD))

# Suppress `make` own output.
.SILENT:

composer_update:
	docker run --rm \
	--user "$$(id -u):$$(id -g)" \
	-e FIXUID=1 \
	-v "${HOME}/.composer/auth.json:/composer/auth.json" \
	-v "${PWD}:/project" \
	-t \
	lucatume/composer:php5.6-composer-v2 update
.PHONY: composer_update

composer_install:
	docker run --rm \
	--user "$$(id -u):$$(id -g)" \
	-e FIXUID=1 \
	-v "${HOME}/.composer/auth.json:/composer/auth.json" \
	-v "${PWD}:/project" \
	-t \
	lucatume/composer:php5.6-composer-v2 install
.PHONY: composer_install

build_php_versions = '5.6' '7.0' '7.1' '7.2' '7.3' '7.4' '8.0'
$(build_php_versions): %:
	docker build \
		--build-arg PHP_VERSION=$@ \
		--build-arg XDEBUG_REMOTE_HOST=$${XDEBUG_REMOTE_HOST:-host.docker.internal} \
		--build-arg XDEBUG_REMOTE_PORT=$${XDEBUG_REMOTE_PORT:-9009} \
		_build/containers/dev \
		--tag lucatume/di52-dev:php-v$@

build: $(build_php_versions)

test_php_versions = 'php-v5.6' 'php-v7.0' 'php-v7.1' 'php-v7.2' 'php-v7.3' 'php-v7.4' 'php-v8.0'
$(test_php_versions): %:
	docker run --rm \
	   -v "${CURDIR}:${PWD}" \
	   --entrypoint ${PWD}/vendor/bin/phpunit \
	   lucatume/di52-dev:$@ \
	   --bootstrap ${PWD}/tests/bootstrap.php \
	   ${PWD}/tests
.PHONY: test

test: $(test_php_versions)

# Lint the project source files to make sure they are PHP 5.6 compatible.
lint:
	docker run --rm -v ${PWD}:/${PWD} lucatume/parallel-lint-56 --colors \
			${PWD}/src \
			${PWD}/autoload.php
.PHONY: lint
