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
# To get the nth argument, use `export TARGET_ARG_2="$(word 2,$(TARGET_ARGS))"`.
SUPPORTED_COMMANDS := wait_file wait_url benchmark_profile benchmark_debug test_coverage test_run composer composer_install composer_update php_shell
SUPPORTS_MAKE_ARGS := $(findstring $(firstword $(MAKECMDGOALS)), $(SUPPORTED_COMMANDS))
ifneq "$(SUPPORTS_MAKE_ARGS)" ""
  TARGET_ARGS := $(wordlist 2,$(words $(MAKECMDGOALS)),$(MAKECMDGOALS))
  $(eval $(TARGET_ARGS):;@:)
endif
# Color definitions for skimmable output.
COLOR_RESET=\x1b[0m
COLOR_GREEN=\x1b[32m
COLOR_RED=\x1b[31m
COLOR_YELLOW=\x1b[33m

help: ## Show this help message.
	@grep -E '^[a-zA-Z0-9\._-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'
.PHONY: help

wait_file: ## Wait for a file; example: `make wait_file some-file.txt 30`
	export target_file="$(word 1,$(TARGET_ARGS))"; \
	export wait_timeout="$(word 2,$(TARGET_ARGS))"; \
	echo -n "Waiting for file $${target_file} for $${wait_timeout}s ..."; \
	c=0; \
	until [ -f "$${target_file}" -o -d "$${target_file}" -o $$c -eq $${wait_timeout} ]; \
		do echo -n '.' && sleep 1 && c=$$(expr $$c + 1); \
	done; \
	[ "$$c" != "$${wait_timeout}" ] \
		&& { echo -e " $(COLOR_GREEN)done$(COLOR_RESET)"; } \
		|| { echo -e " $(COLOR_RED)fail$(COLOR_RESET)"; exit 1; }
.PHONY: wait_file

wait_url: ## Wait for a URL; example: `make wait_url http://example.com 30`
	export target_url="$(word 1,$(TARGET_ARGS))"; \
	export wait_timeout="$(word 2,$(TARGET_ARGS))"; \
	echo -n "Waiting for URL $${target_url} ..."; \
	c=0; \
	until [[ $$(curl --output /dev/null --silent --head --fail $${target_url}) || $$c > "$${wait_timeout:-10}" ]]; \
		do sleep 1 && echo -n '.' && c=$$((c+1)); \
	done; \
	[ "$$c" != "$${wait_timeout}" ] \
		&& { echo -e " $(COLOR_GREEN)done$(COLOR_RESET)"; } \
		|| { echo -e " $(COLOR_RED)fail$(COLOR_RESET)"; exit 1; }
.PHONY: wait_url

composer: ## Runs a Composer command on PHP 5.6. Example: `make composer update`.
	docker run --rm \
	--user "$$(id -u):$$(id -g)" \
	-e FIXUID=1 \
	-v "${HOME}/.composer/auth.json:/composer/auth.json" \
	-v "${PWD}:/project" \
	-t \
	lucatume/composer:php5.6-composer-v2 $(TARGET_ARGS)
.PHONY: composer

composer_install: ## Runs the Composer install command on a target PHP version. Example: `make composer_install 7.2`
	docker run --rm \
	--user "$$(id -u):$$(id -g)" \
	-e FIXUID=1 \
	-v "${HOME}/.composer/auth.json:/composer/auth.json" \
	-v "${PWD}:/project" \
	-t \
	lucatume/composer:php$(TARGET_ARGS)-composer-v2 install
.PHONY: composer_install

composer_update: ## Runs the Composer install command on a target PHP version. Example: `make composer_update 7.2`
	docker run --rm \
	--user "$$(id -u):$$(id -g)" \
	-e FIXUID=1 \
	-v "${HOME}/.composer/auth.json:/composer/auth.json" \
	-v "${PWD}:/project" \
	-t \
	lucatume/composer:php$(TARGET_ARGS)-composer-v2 update
.PHONY: composer_update

build_php_versions_lt_72 = '5.6' '7.0' '7.1'
$(build_php_versions_lt_72): %:
	docker build \
		--build-arg PHP_VERSION=$@ \
		--build-arg XDEBUG_REMOTE_HOST=$${XDEBUG_REMOTE_HOST:-host.docker.internal} \
		--build-arg XDEBUG_REMOTE_PORT=$${XDEBUG_REMOTE_PORT:-9009} \
		_build/containers/dev-lt-72 \
		--tag lucatume/di52-dev:php-v$@
	docker run --rm lucatume/di52-dev:php-v$@ -v
	docker build \
		--build-arg PHP_VERSION=$@ \
		--build-arg XDEBUG_OUTPUT_DIR=${PWD}/_build/profile \
		_build/containers/profile-lt-72 \
		--tag lucatume/di52-profile:php-v$@
	docker run --rm lucatume/di52-profile:php-v$@ -v

build_php_versions_gte_72 = '7.2' '7.3' '7.4' '8.0' '8.1'
$(build_php_versions_gte_72): %:
	docker build \
		--build-arg PHP_VERSION=$@ \
		--build-arg XDEBUG_REMOTE_HOST=$${XDEBUG_REMOTE_HOST:-host.docker.internal} \
		--build-arg XDEBUG_REMOTE_PORT=$${XDEBUG_REMOTE_PORT:-9009} \
		_build/containers/dev-gte-72 \
		--tag lucatume/di52-dev:php-v$@
	docker run --rm lucatume/di52-dev:php-v$@ -v
	docker build \
		--build-arg PHP_VERSION=$@ \
		--build-arg XDEBUG_OUTPUT_DIR=${PWD}/_build/profile \
		_build/containers/profile-gte-72 \
		--tag lucatume/di52-profile:php-v$@
	docker run --rm lucatume/di52-profile:php-v$@ -v

build: $(build_php_versions_lt_72) $(build_php_versions_gte_72) ## Builds the project PHP images.
.PHONY: build

test_php_versions = 'php-v5.6' 'php-v7.0' 'php-v7.1' 'php-v7.2' 'php-v7.3' 'php-v7.4' 'php-v8.0'
$(test_php_versions): %:
	echo "Running tests on $@"
	docker run --rm \
	   -v "${PWD}:${PWD}" \
	   --entrypoint ${PWD}/vendor/bin/phpunit \
	   lucatume/di52-dev:$@ \
	   --bootstrap ${PWD}/tests/bootstrap.php \
	   --stop-on-failure \
	   ${PWD}/tests

test: $(test_php_versions) ## Runs the project PHPUnit tests on all PHP versions.
.PHONY: test

test_coverage: ## Generate code coverage reports for a PHP version. Example: `make coverage 7.1`.
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
.PHONY: test_coverage

test_run: ## Run the test on the specified PHP version with XDebug support. Example `make test_run 7.2`.
	docker run --rm \
	   -v "${PWD}:${PWD}" \
	   --entrypoint ${PWD}/vendor/bin/phpunit \
	   lucatume/di52-dev:php-v$(TARGET_ARGS) \
	   --bootstrap ${PWD}/tests/bootstrap.php \
	   --stop-on-failure \
	   ${PWD}/tests
.PHONY: test_run

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
		${PWD}/src ${PWD}/tests ${PWD}/aliases.php ${PWD}/docs/examples
.PHONY: code_fix

PHPSTAN_LEVEL?=max
phpstan: ## Run phpstan on the project source files.
	docker run --rm \
		-v ${PWD}:${PWD} \
		-u "$$(id -u):$$(id -g)" \
		ghcr.io/phpstan/phpstan analyze \
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

benchmark_run: ## Runs the benchmark suite in docker.
		(cd ${PWD}/_build/benchmark; docker-compose down)
		rsync -azvhP ${PWD}/src ${PWD}/_build/benchmark/vendor/lucatume/di52
		(cd ${PWD}/_build/benchmark; ./benchmark.sh docker --detach)
		docker wait benchmark-cli
		open ${PWD}/_build/benchmark/docs/benchmark.html
.PHONY: benchmark_run

benchmark_debug: ## Run a benchmark on PHP 8.0 and debug it. Example `make benchmark_debug 3.1`.
	docker run --rm \
		-v "${PWD}:${PWD}" \
		lucatume/di52-dev:php-v8.0 \
		${PWD}/_build/run-benchmark.php $(TARGET_ARGS) \
.PHONY: benchmark_debug

benchmark_profile: ## Run a benchmark test suite and profiles it. Example `make benchmark_profile 3`.
	rsync -azvhP ${PWD}/src ${PWD}/_build/benchmark/vendor/lucatume/di52
	rm -rf "${PWD}/_build/profile/cachegrind.out.suite-$(TARGET_ARGS)"
	docker run --rm \
	   -v "${PWD}:${PWD}" \
	   -e XDEBUG_CONFIG="profiler_output_name=callgrind.out.suite-$(TARGET_ARGS)" \
	   lucatume/di52-profile:php-v8.0 \
	   ${PWD}/_build/run-benchmark.php $(TARGET_ARGS).1
	echo ''
	docker run --rm \
	   -v "${PWD}:${PWD}" \
	   -e XDEBUG_CONFIG="profiler_output_name=callgrind.out.suite-$(TARGET_ARGS)" \
	   lucatume/di52-profile:php-v8.0 \
	   ${PWD}/_build/run-benchmark.php $(TARGET_ARGS).2
	echo ''
	docker run --rm \
	   -v "${PWD}:${PWD}" \
	   -e XDEBUG_CONFIG="profiler_output_name=callgrind.out.suite-$(TARGET_ARGS)" \
	   lucatume/di52-profile:php-v8.0 \
	   ${PWD}/_build/run-benchmark.php $(TARGET_ARGS).3 \
.PHONY: benchmark_profile

app_facade: ## Creates or updates the src/App.php file from the current Container API.
	php "${PWD}/_build/create-app-facade.php"
	$(MAKE) code_fix
.PHONY: app_facade

php_shell: ## Opens a shell in a PHP container.
	mkdir -p "${PWD}/.composer/cache"
	docker run --rm -it \
	   -u "$(shell id -u):$(shell id -g)" \
	   -v "${PWD}:${PWD}" \
	   -v "${PWD}/.cache/composer:${PWD}/.cache/composer" \
	   -e COMPOSER_CACHE_DIR="${PWD}/.cache/composer" \
	   -w "${PWD}" \
	   --entrypoint sh \
	   lucatume/di52-dev:php-v$(TARGET_ARGS)
.PHONY: php_shell
