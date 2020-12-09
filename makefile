# Use bash as shell.
SHELL := /bin/bash
# If you see pwd_unknown showing up, this is why. Re-calibrate your system.
PWD ?= pwd_unknown
# PROJECT_NAME defaults to name of the current directory.
PROJECT_NAME = $(notdir $(PWD))
# Suppress `make` own output.
.SILENT:
.DEFAULT_GOAL := help

help: ## Show this help message.
	@grep -E '^[a-zA-Z0-9_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'
.PHONY: help

composer_update: ## Updates the project Composer dependencies using PHP 5.6.
	docker run --rm \
	--user "$$(id -u):$$(id -g)" \
	-e FIXUID=1 \
	-v "${HOME}/.composer/auth.json:/composer/auth.json" \
	-v "${PWD}:/project" \
	-t \
	lucatume/composer:php5.6-composer-v2 update
.PHONY: composer_update

composer_install: ## Installs the project Composer dependencies using PHP 5.6.
	docker run --rm \
	--user "$$(id -u):$$(id -g)" \
	-e FIXUID=1 \
	-v "${HOME}/.composer/auth.json:/composer/auth.json" \
	-v "${PWD}:/project" \
	-t \
	lucatume/composer:php5.6-composer-v2 install
.PHONY: composer_install

composer_dump_autoload: ## Regenerates the project Composer autoload files on PHP 5.6.
	docker run --rm \
	--user "$$(id -u):$$(id -g)" \
	-e FIXUID=1 \
	-v "${HOME}/.composer/auth.json:/composer/auth.json" \
	-v "${PWD}:/project" \
	-t \
	lucatume/composer:php5.6-composer-v2 dump-autoload
.PHONY: composer_dump_autoload

build_php_versions = '5.6' '7.0' '7.1' '7.2' '7.3' '7.4' '8.0'
$(build_php_versions): %:
	docker build \
		--build-arg PHP_VERSION=$@ \
		--build-arg XDEBUG_REMOTE_HOST=$${XDEBUG_REMOTE_HOST:-host.docker.internal} \
		--build-arg XDEBUG_REMOTE_PORT=$${XDEBUG_REMOTE_PORT:-9009} \
		_build/containers/dev \
		--tag lucatume/di52-dev:php-v$@

build: $(build_php_versions) ## Builds the project PHP images.

test_php_versions = 'php-v5.6' 'php-v7.0' 'php-v7.1' 'php-v7.2' 'php-v7.3' 'php-v7.4' 'php-v8.0'
$(test_php_versions): %:
	docker run --rm \
	   -v "${CURDIR}:${PWD}" \
	   --entrypoint ${PWD}/vendor/bin/phpunit \
	   lucatume/di52-dev:$@ \
	   --bootstrap ${PWD}/tests/bootstrap.php \
	   ${PWD}/tests

test: $(test_php_versions) ## Runs the project PHPUnit tests on all PHP versions.
.PHONY: test

test_56: ## Utility target to run the tests on PHP 5.6.
	docker run --rm \
	   -v "${CURDIR}:${PWD}" \
	   --entrypoint ${PWD}/vendor/bin/phpunit \
	   lucatume/di52-dev:php-5.6 \
	   --bootstrap ${PWD}/tests/bootstrap.php \
	   --stop-on-failure \
	   ${PWD}/tests
.PHONY: test_56

coverage_56: ## Utility target to run the tests on PHP 5.6.
	docker run --rm \
		-v "${CURDIR}:${PWD}" \
	   --entrypoint ${PWD}/vendor/bin/phpunit \
		lucatume/di52-dev:php-5.6 \
		-c ${PWD}/phpunit.xml \
	   ${PWD}/tests
.PHONY: coverage_56

coverage_70: ## Utility target to run the tests on PHP 5.6.
	docker run --rm \
		-v "${CURDIR}:${PWD}" \
	   --entrypoint ${PWD}/vendor/bin/phpunit \
		lucatume/di52-dev:php-7.0 \
		-c ${PWD}/phpunit.xml \
	   ${PWD}/tests
.PHONY: coverage_70

coverage_71: ## Utility target to run the tests on PHP 5.6.
	docker run --rm \
		-v "${CURDIR}:${PWD}" \
	   --entrypoint ${PWD}/vendor/bin/phpunit \
		lucatume/di52-dev:php-7.1 \
		-c ${PWD}/phpunit.xml \
	   ${PWD}/tests
.PHONY: coverage_71

coverage_72: ## Utility target to run the tests on PHP 5.6.
	docker run --rm \
		-v "${CURDIR}:${PWD}" \
	   --entrypoint ${PWD}/vendor/bin/phpunit \
		lucatume/di52-dev:php-7.2 \
		-c ${PWD}/phpunit.xml \
	   ${PWD}/tests
.PHONY: coverage_72

coverage_73: ## Utility target to run the tests on PHP 5.6.
	docker run --rm \
		-v "${CURDIR}:${PWD}" \
	   --entrypoint ${PWD}/vendor/bin/phpunit \
		lucatume/di52-dev:php-7.3 \
		-c ${PWD}/phpunit.xml \
	   ${PWD}/tests
.PHONY: coverage_73

coverage_74: ## Utility target to run the tests on PHP 5.6.
	docker run --rm \
		-v "${CURDIR}:${PWD}" \
	   --entrypoint ${PWD}/vendor/bin/phpunit \
		lucatume/di52-dev:php-7.4 \
		-c ${PWD}/phpunit.xml \
	   ${PWD}/tests
.PHONY: coverage_74

coverage_80: ## Utility target to run the tests on PHP 5.6.
	docker run --rm \
		-v "${CURDIR}:${PWD}" \
	   --entrypoint ${PWD}/vendor/bin/phpunit \
		lucatume/di52-dev:php-8.0 \
		-c ${PWD}/phpunit.xml \
	   ${PWD}/tests
.PHONY: coverage_80

test_70: ## Utility target to run the tests on PHP 7.0.
	docker run --rm \
	   -v "${CURDIR}:${PWD}" \
	   --entrypoint ${PWD}/vendor/bin/phpunit \
	   lucatume/di52-dev:php-7.0 \
	   --bootstrap ${PWD}/tests/bootstrap.php \
	   --stop-on-failure \
	   ${PWD}/tests
.PHONY: test_70

test_71: ## Utility target to run the tests on PHP 7.1.
	docker run --rm \
	   -v "${CURDIR}:${PWD}" \
	   --entrypoint ${PWD}/vendor/bin/phpunit \
	   lucatume/di52-dev:php-7.1 \
	   --bootstrap ${PWD}/tests/bootstrap.php \
	   --stop-on-failure \
	   ${PWD}/tests
.PHONY: test_71

test_72: ## Utility target to run the tests on PHP 7.2.
	docker run --rm \
	   -v "${CURDIR}:${PWD}" \
	   --entrypoint ${PWD}/vendor/bin/phpunit \
	   lucatume/di52-dev:php-7.2 \
	   --bootstrap ${PWD}/tests/bootstrap.php \
	   --stop-on-failure \
	   ${PWD}/tests
.PHONY: test_72

test_73: ## Utility target to run the tests on PHP 7.3.
	docker run --rm \
	   -v "${CURDIR}:${PWD}" \
	   --entrypoint ${PWD}/vendor/bin/phpunit \
	   lucatume/di52-dev:php-7.3 \
	   --bootstrap ${PWD}/tests/bootstrap.php \
	   --stop-on-failure \
	   ${PWD}/tests
.PHONY: test_73

test_74: ## Utility target to run the tests on PHP 7.4.
	docker run --rm \
	   -v "${CURDIR}:${PWD}" \
	   --entrypoint ${PWD}/vendor/bin/phpunit \
	   lucatume/di52-dev:php-7.4 \
	   --bootstrap ${PWD}/tests/bootstrap.php \
	   --stop-on-failure \
	   ${PWD}/tests
.PHONY: test_74

test_80: ## Utility target to run the tests on PHP 8.0.
	docker run --rm \
	   -v "${CURDIR}:${PWD}" \
	   --entrypoint ${PWD}/vendor/bin/phpunit \
	   lucatume/di52-dev:php-8.0 \
	   --bootstrap ${PWD}/tests/bootstrap.php \
	   --stop-on-failure \
	   ${PWD}/tests
.PHONY: test_80

code_lint: ## Lint the project source files to make sure they are PHP 5.6 compatible.
	docker run --rm -v ${PWD}:/${PWD} lucatume/parallel-lint-56 --colors \
			${PWD}/src \
			${PWD}/autoload.php
.PHONY: code_lint

code_sniff: ## Run PHP Code Sniffer on the project source files.
	docker run --rm \
        -u "$$(id -u):$$(id -g)" \
		-v ${PWD}:${PWD} cytopia/phpcs \
		--colors \
		-p \
		-s \
		--standard=${PWD}/phpcs.xml \
		${PWD}/src ${PWD}/autoload.php
.PHONY: code_sniff

code_fix: ## Run PHP Code Sniffer Beautifier on the project source files.
	docker run --rm \
        -u "$$(id -u):$$(id -g)" \
        -v ${PWD}:${PWD} cytopia/phpcbf \
		--colors \
		-p \
		-s \
		--standard=${PWD}/phpcs.xml \
		${PWD}/src ${PWD}/tests ${PWD}/autoload.php
.PHONY: code_fix

PHPSTAN_LEVEL?=max
phpstan: ## Run phpstan on the project source files.
	docker run --rm \
		-v ${PWD}:${PWD} \
		-u "$$(id -u):$$(id -g)" \
		phpstan/phpstan analyze \
		-c ${PWD}/_build/phpstan.neon \
		-l ${PHPSTAN_LEVEL} ${PWD}/src ${PWD}/autoload.php
.PHONY: phpstan

phan: ## Run phan on the project source files.
	docker run --rm \
		-v ${PWD}:/mnt/src \
		-u "$$(id -u):$$(id -g)" \
		phanphp/phan
.PHONY: phan

pre_commit: code_lint code_fix code_sniff test phpstan phan ## Run pre-commit checks: code_lint, code_fix, code_sniff, test, phpstan, phan.
.PHONY: pre_commit
