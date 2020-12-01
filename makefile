benchmarksFolder = /Users/Luca/Repos/php-dependency-injection-benchmarks

composer_56_update:
	docker run --rm \
	--user "$$(id -u):$$(id -g)" \
	-e FIXUID=1 \
	-v "${HOME}/.composer/auth.json:/composer/auth.json" \
	-v "${PWD}:/project" \
	-t \
	lucatume/composer:php5.6-composer-v2 update
.PHONY: composer_56_update

composer_56_install:
	docker run --rm \
	--user "$$(id -u):$$(id -g)" \
	-e FIXUID=1 \
	-v "${HOME}/.composer/auth.json:/composer/auth.json" \
	-v "${PWD}:/project" \
	-t \
	lucatume/composer:php5.6-composer-v2 install
.PHONY: composer_56_install

build:
	docker build \
		--build-arg XDEBUG_REMOTE_HOST=$${XDEBUG_REMOTE_HOST:-host.docker.internal} \
		--build-arg XDEBUG_REMOTE_PORT=$${XDEBUG_REMOTE_PORT:-9009} \
		_build/containers/dev \
		--tag lucatume/di52-dev
.PHONY: build

test:
	docker run --rm \
		-v "${CURDIR}:${CURDIR}" \
		--entrypoint ${CURDIR}/vendor/bin/phpunit \
		lucatume/di52-dev \
		--bootstrap	${CURDIR}/tests/bootstrap.php \
		${CURDIR}/tests

benchmark:
	cd $(benchmarksFolder); \
	php test1-5_runner.php; \
	ls -p -t ./results/test1-5_results-*.html | head -1 | xargs open;

benchmark6:
	cd $(benchmarksFolder); \
	php test6_runner.php; \
	ls -p -t ./results/test6_results-*.html | head -1 | xargs open;

cover:
	vendor/bin/phpunit-php52 --coverage-html ./tests/coverage tests
	open ./tests/coverage/index.html

lint_52:
	docker run --rm -v "${CURDIR}:/project" --entrypoint php tommylau/php-5.2 -l /project/src/tad/DI52/Container.php
	docker run --rm -v "${CURDIR}:/project" --entrypoint php tommylau/php-5.2 -l /project/src/tad/DI52/ContainerInterface.php
	docker run --rm -v "${CURDIR}:/project" --entrypoint php tommylau/php-5.2 -l /project/src/tad/DI52/ProtectedValue.php
	docker run --rm -v "${CURDIR}:/project" --entrypoint php tommylau/php-5.2 -l /project/src/tad/DI52/ServiceProviderInterface.php

lint_53:
	docker run --rm -v "${CURDIR}:/project" cespi/php-5.3 php -l /project/src/tad/DI52/closuresSupport.php
	docker run --rm -v "${CURDIR}:/project" cespi/php-5.3 php -l /project/src/tad/DI52/Container.php
	docker run --rm -v "${CURDIR}:/project" cespi/php-5.3 php -l /project/src/tad/DI52/ContainerInterface.php
	docker run --rm -v "${CURDIR}:/project" cespi/php-5.3 php -l /project/src/tad/DI52/ProtectedValue.php
	docker run --rm -v "${CURDIR}:/project" cespi/php-5.3 php -l /project/src/tad/DI52/ServiceProviderInterface.php
