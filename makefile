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
SUPPORTED_COMMANDS := benchmark.profile test.coverage test.run composer
SUPPORTS_MAKE_ARGS := $(findstring $(firstword $(MAKECMDGOALS)), $(SUPPORTED_COMMANDS))
ifneq "$(SUPPORTS_MAKE_ARGS)" ""
  TARGET_ARGS := $(wordlist 2,$(words $(MAKECMDGOALS)),$(MAKECMDGOALS))
  $(eval $(TARGET_ARGS):;@:)
endif

help: ## Show this help message.
	@grep -E '^[a-zA-Z0-9\._-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'
.PHONY: help

composer: ## Runs a Composer command on PHP 5.6. Example: `make composer update`.
	docker run --rm \
	--user "$$(id -u):$$(id -g)" \
	-e FIXUID=1 \
	-v "${HOME}/.composer/auth.json:/composer/auth.json" \
	-v "${PWD}:/project" \
	-t \
	lucatume/composer:php5.6-composer-v2 $(TARGET_ARGS)
.PHONY: composer

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

test.coverage: ## Generate code coverage reports for a PHP version. Example: `make coverage 7.1`.
ifeq ( $(TARGET_ARGS),5.6)
		echo 'Generating coverage for PHP 5.6';
		docker run --rm \
			-v "${PWD}:${PWD}" \
		   --entrypoint ${PWD}/vendor/bin/phpunit \
			lucatume/di52-dev:php-v5.6 -c ${PWD}/phpunit.xml \
		   ${PWD}/test
else
		echo "Generating coverage for PHP $(TARGET_ARGS)";
		docker run --rm \
			-v "${PWD}:${PWD}" \
			--entrypoint phpdbg \
			lucatume/di52-dev:php-v$(TARGET_ARGS) \
			-qrr ${PWD}/vendor/bin/phpunit -c ${PWD}/phpunit.xml \
			${PWD}/tests
endif
	open tests/coverage/index.html
.PHONY: test.coverage

test.run: ## Run the test on the specified PHP version with XDebug support. Example `make test.run 7.2`.
	docker run --rm \
	   -v "${PWD}:${PWD}" \
	   --entrypoint ${PWD}/vendor/bin/phpunit \
	   lucatume/di52-dev:php-v$(TARGET_ARGS) \
	   --bootstrap ${PWD}/tests/bootstrap.php \
	   --stop-on-failure \
	   ${PWD}/tests
.PHONY: test.run

code.lint: ## Lint the project source files to make sure they are PHP 5.6 compatible.
	docker run --rm -v ${PWD}:/${PWD} lucatume/parallel-lint-56 --colors \
			${PWD}/src \
			${PWD}/aliases.php
.PHONY: code.lint

code.sniff: ## Run PHP Code Sniffer on the project source files.
	docker run --rm \
        -u "$$(id -u):$$(id -g)" \
		-v ${PWD}:${PWD} cytopia/phpcs \
		--colors \
		-p \
		-s \
		--standard=${PWD}/phpcs.xml \
		${PWD}/src ${PWD}/aliases.php
.PHONY: code.sniff

code.fix: ## Run PHP Code Sniffer Beautifier on the project source files.
	docker run --rm \
        -u "$$(id -u):$$(id -g)" \
        -v ${PWD}:${PWD} cytopia/phpcbf \
		--colors \
		-p \
		-s \
		--standard=${PWD}/phpcs.xml \
		${PWD}/src ${PWD}/tests ${PWD}/aliases.php
.PHONY: code.fix

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

pre_commit: code.lint code.fix code.sniff test phpstan phan ## Run pre-commit checks: code.lint, code.fix, code.sniff, test, phpstan, phan.
.PHONY: pre_commit

benchmark.build: ## !!WiP!! Build the benchmark suite.
	rm -rf ${PWD}/_build/benchmark
	[ -d ${PWD}/_build/benchmark ] || \
		git clone https://github.com/kocsismate/php-di-container-benchmarks.git _build/benchmark
	cp ${PWD}/_build/benchmark/.env.dist ${PWD}/_build/benchmark/.env
.PHONY: benchmark.build

benchmark.run: ## Runs the benchmark suite in docker.
	(cd ${PWD}/_build/benchmark; ./benchmark.sh docker)
	open docs/benchmark.html
.PHONY: benchmark.run

benchmark.debug: ## Run a benchmark test and debug it. Example `make benchmark_debug 3.1`.
	docker run --rm \
	   -v "${PWD}:${PWD}" \
	   lucatume/di52-dev:php-v8.0 \
	   ${PWD}/_build/run-benchmark.php $(TARGET_ARGS) \
.PHONY: benchmark.debug

benchmark.profile: ## Run a benchmark test suite and profiles it. Example `make benchmark_profile 3`.
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
.PHONY: benchmark.profile
