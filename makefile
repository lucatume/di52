php52 = /Applications/Mamp/bin/php/php5.2.17/bin/php
benchmarksFolder = /Users/Luca/Repos/php-dependency-injection-benchmarks

test:
	docker run --rm \
		-v "${CURDIR}:/project" \
		--entrypoint /project/vendor/bin/phpunit-php52 \
		tommylau/php-5.2 \
		--bootstrap	/project/tests/bootstrap.php \
		/project/tests/52-compat
	vendor/bin/phpunit-php52 -v

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
