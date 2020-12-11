# Use bash as shell.
SHELL := /bin/bash
# If you see pwd_unknown showing up, this is why. Re-calibrate your system.
PWD ?= pwd_unknown
# PROJECT_NAME defaults to name of the current directory.
PROJECT_NAME = $(notdir $(PWD))
# Suppress `make` own output.
.SILENT:
# Make `help` the default target to make sure it will display when make is called without a target.
.DEFAULT_GOAL := help
# Create a script to support command line arguments for targets.
# The specified targets will be callable like this `make target_w_args_1 foo bar 23`.
# In the target, use the `$(TARGET_ARGS)` var to get the arguments.
SUPPORTED_COMMANDS := benchmark_run benchmark_profile
SUPPORTS_MAKE_ARGS := $(findstring $(firstword $(MAKECMDGOALS)), $(SUPPORTED_COMMANDS))
ifneq "$(SUPPORTS_MAKE_ARGS)" ""
  TARGET_ARGS := $(wordlist 2,$(words $(MAKECMDGOALS)),$(MAKECMDGOALS))
  $(eval $(TARGET_ARGS):;@:)
endif

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
	docker build \
		--build-arg PHP_VERSION=$@ \
		--build-arg XDEBUG_OUTPUT_DIR=${PWD}/_build/profile \
		_build/containers/profile \
		--tag lucatume/di52-profile:php-v$@

build: $(build_php_versions) ## Builds the project PHP images.

test_php_versions = 'php-v5.6' 'php-v7.0' 'php-v7.1' 'php-v7.2' 'php-v7.3' 'php-v7.4' 'php-v8.0'
$(test_php_versions): %:
	docker run --rm \
	   -v "${PWD}:${PWD}" \
	   --entrypoint ${PWD}/vendor/bin/phpunit \
	   lucatume/di52-dev:$@ \
	   --bootstrap ${PWD}/tests/bootstrap.php \
	   ${PWD}/tests

test: $(test_php_versions) ## Runs the project PHPUnit tests on all PHP versions.
.PHONY: test

coverage_56: ## Run the tests on the specified PHP version and generate code coverage reports.
	docker run --rm \
		-v "${PWD}:${PWD}" \
	   --entrypoint ${PWD}/vendor/bin/phpunit \
		lucatume/di52-dev:php-v5.6 \
		-c ${PWD}/phpunit.xml \
	   ${PWD}/tests
.PHONY: coverage_56

coverage_70: ## Utility target to run the tests on PHP 7.0 and generate code coverage reports.
	docker run --rm \
		-v "${PWD}:${PWD}" \
		--entrypoint phpdbg \
		lucatume/di52-dev:php-v7.0 \
		-qrr ${PWD}/vendor/bin/phpunit -c ${PWD}/phpunit.xml \
		${PWD}/tests
.PHONY: coverage_70

coverage_71: ## Utility target to run the tests on PHP 7.1 and generate code coverage reports.
	docker run --rm \
		-v "${PWD}:${PWD}" \
		--entrypoint phpdbg \
		lucatume/di52-dev:php-v7.1 \
		-qrr ${PWD}/vendor/bin/phpunit -c ${PWD}/phpunit.xml \
		${PWD}/tests
.PHONY: coverage_71

coverage_72: ## Utility target to run the tests on PHP 7.2 and generate code coverage reports.
	docker run --rm \
		-v "${PWD}:${PWD}" \
		--entrypoint phpdbg \
		lucatume/di52-dev:php-v7.2 \
		-qrr ${PWD}/vendor/bin/phpunit -c ${PWD}/phpunit.xml \
		${PWD}/tests
.PHONY: coverage_72

coverage_73: ## Utility target to run the tests on PHP 7.3 and generate code coverage reports.
	docker run --rm \
		-v "${PWD}:${PWD}" \
		--entrypoint phpdbg \
		lucatume/di52-dev:php-v7.3 \
		-qrr ${PWD}/vendor/bin/phpunit -c ${PWD}/phpunit.xml \
		${PWD}/tests
.PHONY: coverage_73

coverage_74: ## Utility target to run the tests on PHP 7.4 and generate code coverage reports.
	docker run --rm \
		-v "${PWD}:${PWD}" \
		--entrypoint phpdbg \
		lucatume/di52-dev:php-v7.4 \
		-qrr ${PWD}/vendor/bin/phpunit -c ${PWD}/phpunit.xml \
		${PWD}/tests
.PHONY: coverage_74

test_56: ## Utility target to run the tests on PHP 5.6 with XDebug support.
	docker run --rm \
	   -v "${PWD}:${PWD}" \
	   --entrypoint ${PWD}/vendor/bin/phpunit \
	   lucatume/di52-dev:php-v5.6 \
	   --bootstrap ${PWD}/tests/bootstrap.php \
	   --stop-on-failure \
	   ${PWD}/tests
.PHONY: test_56

test_70: ## Utility target to run the tests on PHP 7.0 with XDebug support.
	docker run --rm \
	   -v "${PWD}:${PWD}" \
	   --entrypoint ${PWD}/vendor/bin/phpunit \
	   lucatume/di52-dev:php-v7.0 \
	   --bootstrap ${PWD}/tests/bootstrap.php \
	   --stop-on-failure \
	   ${PWD}/tests
.PHONY: test_70

test_71: ## Utility target to run the tests on PHP 7.1 with XDebug support.
	docker run --rm \
	   -v "${PWD}:${PWD}" \
	   --entrypoint ${PWD}/vendor/bin/phpunit \
	   lucatume/di52-dev:php-v7.1 \
	   --bootstrap ${PWD}/tests/bootstrap.php \
	   --stop-on-failure \
	   ${PWD}/tests
.PHONY: test_71

test_72: ## Utility target to run the tests on PHP 7.2 with XDebug support.
	docker run --rm \
	   -v "${PWD}:${PWD}" \
	   --entrypoint ${PWD}/vendor/bin/phpunit \
	   lucatume/di52-dev:php-v7.2 \
	   --bootstrap ${PWD}/tests/bootstrap.php \
	   --stop-on-failure \
	   ${PWD}/tests
.PHONY: test_72

test_73: ## Utility target to run the tests on PHP 7.3 with XDebug support.
	docker run --rm \
	   -v "${PWD}:${PWD}" \
	   --entrypoint ${PWD}/vendor/bin/phpunit \
	   lucatume/di52-dev:php-v7.3 \
	   --bootstrap ${PWD}/tests/bootstrap.php \
	   --stop-on-failure \
	   ${PWD}/tests
.PHONY: test_73

test_74: ## Utility target to run the tests on PHP 7.4 with XDebug support.
	docker run --rm \
	   -v "${PWD}:${PWD}" \
	   --entrypoint ${PWD}/vendor/bin/phpunit \
	   lucatume/di52-dev:php-v7.4 \
	   --bootstrap ${PWD}/tests/bootstrap.php \
	   --stop-on-failure \
	   ${PWD}/tests
.PHONY: test_74

test_80: ## Utility target to run the tests on PHP 8.0 with XDebug support.
	docker run --rm \
	   -v "${PWD}:${PWD}" \
	   --entrypoint ${PWD}/vendor/bin/phpunit \
	   lucatume/di52-dev:php-v8.0 \
	   --bootstrap ${PWD}/tests/bootstrap.php \
	   --stop-on-failure \
	   ${PWD}/tests
.PHONY: test_80

code_lint: ## Lint the project source files to make sure they are PHP 5.6 compatible.
	docker run --rm -v ${PWD}:/${PWD} lucatume/parallel-lint-56 --colors \
			${PWD}/src \
			${PWD}/aliases.php
.PHONY: code_lint

code_sniff: ## Run PHP Code Sniffer on the project source files.
	docker run --rm \
        -u "$$(id -u):$$(id -g)" \
		-v ${PWD}:${PWD} cytopia/phpcs \
		--colors \
		-p \
		-s \
		--standard=${PWD}/phpcs.xml \
		${PWD}/src ${PWD}/aliases.php
.PHONY: code_sniff

code_fix: ## Run PHP Code Sniffer Beautifier on the project source files.
	docker run --rm \
        -u "$$(id -u):$$(id -g)" \
        -v ${PWD}:${PWD} cytopia/phpcbf \
		--colors \
		-p \
		-s \
		--standard=${PWD}/phpcs.xml \
		${PWD}/src ${PWD}/tests ${PWD}/aliases.php
.PHONY: code_fix

PHPSTAN_LEVEL?=max
phpstan: ## Run phpstan on the project source files.
	docker run --rm \
		-v ${PWD}:${PWD} \
		-u "$$(id -u):$$(id -g)" \
		phpstan/phpstan analyze \
		-c ${PWD}/_build/phpstan.neon \
		-l ${PHPSTAN_LEVEL} ${PWD}/src ${PWD}/aliases.php
.PHONY: phpstan

phan: ## Run phan on the project source files.
	docker run --rm \
		-v ${PWD}:/mnt/src \
		-u "$$(id -u):$$(id -g)" \
		phanphp/phan
.PHONY: phan

pre_commit: code_lint code_fix code_sniff test phpstan phan ## Run pre-commit checks: code_lint, code_fix, code_sniff, test, phpstan, phan.
.PHONY: pre_commit

benchmark_build: ## !!WiP!! Build the benchmark suite.
	rm -rf ${PWD}/_build/benchmark
	[ -d ${PWD}/_build/benchmark ] || \
		git clone https://github.com/kocsismate/php-di-container-benchmarks.git _build/benchmark
	cp ${PWD}/_build/benchmark/.env.dist ${PWD}/_build/benchmark/.env
.PHONY: benchmark_build

benchmark_run: ## Runs the benchmark suite in docker.
	(cd ${PWD}/_build/benchmark; ./benchmark.sh docker)
.PHONY: benchmark_run

benchmark_debug: ## Run a benchmark test and debug it. Requires arguments; e.g. `3.1`.
	docker run --rm \
	   -v "${PWD}:${PWD}" \
	   lucatume/di52-dev:php-v8.0 \
	   ${PWD}/_build/run-benchmark.php $(TARGET_ARGS) \
.PHONY: benchmark_profile

benchmark_profile: ## Run a benchmark test suite and profiles it. Requires arguments; e.g. `3` to run suite 3.
	rm -rf _build/profile/cachegrind.out*
	docker run --rm \
	   -v "${PWD}:${PWD}" \
	   lucatume/di52-profile:php-v8.0 \
	   ${PWD}/_build/run-benchmark.php $(TARGET_ARGS).1
	echo ''
	docker run --rm \
	   -v "${PWD}:${PWD}" \
	   lucatume/di52-profile:php-v8.0 \
	   ${PWD}/_build/run-benchmark.php $(TARGET_ARGS).2
	echo ''
	docker run --rm \
	   -v "${PWD}:${PWD}" \
	   lucatume/di52-profile:php-v8.0 \
	   ${PWD}/_build/run-benchmark.php $(TARGET_ARGS).3 \
.PHONY: benchmark_profile
