# Use bash as shell.
SHELL := /bin/bash
# If you see pwd_unknown showing up, this is why. Re-calibrate your system.
PWD ?= pwd_unknown
# PROJECT_NAME defaults to name of the current directory.
PROJECT_NAME = $(notdir $(PWD))
# Suppress `make` own output.
#.SILENT:
PHP_VERSION ?= 7.1

php_versions :=7.1 7.2 7.3 7.4 8.0 8.1 8.2 8.3 8.4
build: $(build_php_versions) ## Builds the project PHP images.
	mkdir -p var/cache/composer
	mkdir -p var/log
	# Foreach PHP version build a Docker image.
	for version in $(php_versions); do \
		docker build \
			--build-arg PHP_VERSION=$${version} \
			--build-arg XDEBUG_REMOTE_HOST=$${XDEBUG_REMOTE_HOST:-host.docker.internal} \
			--build-arg XDEBUG_REMOTE_PORT=$${XDEBUG_REMOTE_PORT:-9009} \
			--build-arg WORKDIR=${PWD} \
			--build-arg XDEBUG_SRC=xdebug \
			--progress plain \
			config/containers/php \
			--tag lucatume/di52-dev:php-v$${version}; \
	done
.PHONY: build

phpcs: ## Run PHP Code Sniffer on the project source files.
	docker run --rm \
        -u "$$(id -u):$$(id -g)" \
		-v ${PWD}:${PWD} cytopia/phpcs \
		--colors \
		-p \
		-s \
		--standard=${PWD}/config/phpcs.xml \
		${PWD}/src
.PHONY: phpcs

phpcbf: ## Run PHP Code Sniffer Beautifier on the project source files.
	docker run --rm \
        -u "$$(id -u):$$(id -g)" \
        -v ${PWD}:${PWD} cytopia/phpcbf \
		--colors \
		-p \
		-s \
		--standard=${PWD}/config/phpcs.xml \
		${PWD}/src ${PWD}/tests
.PHONY: phpcbf

PHPSTAN_LEVEL?=max
phpstan: ## Run phpstan on the project source files, PHP 7.1 version.
	docker run --rm \
		-v ${PWD}:${PWD} \
		-u "$$(id -u):$$(id -g)" \
		ghcr.io/phpstan/phpstan analyze \
		-c ${PWD}/config/phpstan.neon \
		-l ${PHPSTAN_LEVEL} ${PWD}/src
.PHONY: phpstan

phan: ## Run phan on the project source files, PHP 7.1 version.
	docker run --rm \
		-v ${PWD}:/mnt/src \
		-u "$$(id -u):$$(id -g)" \
		phanphp/phan -k config/phan-config.php
.PHONY: phan

composer_update:
	docker run --rm \
		-e COMPOSER_CACHE_DIR=${PWD}/var/cache/composer \
		-v "${PWD}:${PWD}" \
		-w ${PWD} \
  		--entrypoint composer \
		lucatume/di52-dev:php-v${PHP_VERSION} update -W
.PHONY: composer_update

composer_update_71:
	docker run --rm \
		-e COMPOSER_CACHE_DIR=${PWD}/var/cache/composer \
		-v "${PWD}:${PWD}" \
		-w ${PWD} \
  		--entrypoint composer \
		lucatume/di52-dev:php-v7.1 update -W
.PHONY: composer_update_71

composer_update_72:
	docker run --rm \
		-e COMPOSER_CACHE_DIR=${PWD}/var/cache/composer \
		-v "${PWD}:${PWD}" \
		-w ${PWD} \
  		--entrypoint composer \
		lucatume/di52-dev:php-v7.2 update -W
.PHONY: composer_update_72

composer_update_73:
	docker run --rm \
		-e COMPOSER_CACHE_DIR=${PWD}/var/cache/composer \
		-v "${PWD}:${PWD}" \
		-w ${PWD} \
  		--entrypoint composer \
		lucatume/di52-dev:php-v7.3 update -W
.PHONY: composer_update_73

composer_update_74:
	docker run --rm \
		-e COMPOSER_CACHE_DIR=${PWD}/var/cache/composer \
		-v "${PWD}:${PWD}" \
		-w ${PWD} \
  		--entrypoint composer \
		lucatume/di52-dev:php-v7.4 update -W
.PHONY: composer_update_74

composer_update_80:
	docker run --rm \
		-e COMPOSER_CACHE_DIR=${PWD}/var/cache/composer \
		-v "${PWD}:${PWD}" \
		-w ${PWD} \
  		--entrypoint composer \
		lucatume/di52-dev:php-v8.0 update -W
.PHONY: composer_update_80

composer_update_81:
	docker run --rm \
		-e COMPOSER_CACHE_DIR=${PWD}/var/cache/composer \
		-v "${PWD}:${PWD}" \
		-w ${PWD} \
  		--entrypoint composer \
		lucatume/di52-dev:php-v8.1 update -W
.PHONY: composer_update_81

composer_update_82:
	docker run --rm \
		-e COMPOSER_CACHE_DIR=${PWD}/var/cache/composer \
		-v "${PWD}:${PWD}" \
		-w ${PWD} \
  		--entrypoint composer \
		lucatume/di52-dev:php-v8.2 update -W
.PHONY: composer_update_82

composer_update_83:
	docker run --rm \
		-e COMPOSER_CACHE_DIR=${PWD}/var/cache/composer \
		-v "${PWD}:${PWD}" \
		-w ${PWD} \
  		--entrypoint composer \
		lucatume/di52-dev:php-v8.3 update -W
.PHONY: composer_update_83

composer_update_84:
	docker run --rm \
		-e COMPOSER_CACHE_DIR=${PWD}/var/cache/composer \
		-v "${PWD}:${PWD}" \
		-w ${PWD} \
  		--entrypoint composer \
		lucatume/di52-dev:php-v8.4 update -W
.PHONY: composer_update_84

test_run: ## Run the test on the specified PHP version with XDebug support. Example `PHP_VERSION=7.2 make test_run`.
	docker run --rm \
	  -e COMPOSER_CACHE_DIR="${PWD}/var/cache/composer" \
	  -v "${PWD}:${PWD}" \
	  -w "${PWD}" \
	  "lucatume/di52-dev:php-v${PHP_VERSION}" run_tests --no-coverage
.PHONY: test_run

test: composer_update_71 phpcs phpstan phan ## Runs the project PHPUnit tests on all PHP versions.
	for version in $(php_versions); do \
		docker run --rm \
        	  -e COMPOSER_CACHE_DIR="${PWD}/var/cache/composer" \
        	  -v "${PWD}:${PWD}" \
        	  -w "${PWD}" \
        	  lucatume/di52-dev:php-v$${version} run_tests --no-coverage || exit 1; \
	done
.PHONY: test

clean:
	rm -rf var/cache/composer
	rm -rf var && mkdir var
	docker image rm $$(docker images lucatume/di52-dev -q) || exit 0
	docker image rm $$(docker images lucatume/di52-profile -q) || exit 0
.PHONY: clean

test_coverage: ## Generate code coverage reports for a PHP version. Example: `PHP_VERSION=7.2 make test_coverage`.
	docker run --rm \
	  -e COMPOSER_CACHE_DIR="${PWD}/var/cache/composer" \
	  -v "${PWD}:${PWD}" \
	  -w "${PWD}" \
	  "lucatume/di52-dev:php-v${PHP_VERSION}" run_tests
	open var/coverage/index.html
.PHONY: test_coverage

shell: ## Opens a shell in a PHP container. Example `PHP_VERSION=7.2 make shell`.
	docker run --rm -it \
	   -u "$(shell id -u):$(shell id -g)" \
	   -v "${PWD}:${PWD}" \
	   -e COMPOSER_CACHE_DIR="${PWD}/var/cache/composer" \
	   -w "${PWD}" \
	   --entrypoint sh \
	   lucatume/di52-dev:php-v${PHP_VERSION}
.PHONY: shell

app_facade: ## Creates or updates the src/App.php file from the current Container API.
	docker run --rm \
		  -e COMPOSER_CACHE_DIR="${PWD}/var/cache/composer" \
		  -v "${PWD}:${PWD}" \
		  -w "${PWD}" \
		  lucatume/di52-dev:php-v8.0 php bin/create-app-facade;
	$(MAKE) phpstan
	$(MAKE) phpcbf || exit 0
	$(MAKE) phpcs
.PHONY: app_facade
