test:
	/Applications/Mamp/bin/php/php5.2.17/bin/php vendor/bin/phpunit-php52
	vendor/bin/phpunit-php52

benchmark:
	cd /Users/Luca/Repos/php-dependency-injection-benchmarks; \
	php test1-5_runner.php; \
	ls -p -t ./results/test1-5_results-*.html | head -1 | xargs open;

cover:
	vendor/bin/phpunit-php52 --coverage-html ./tests/coverage tests
