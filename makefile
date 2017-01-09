test:
	vendor/bin/phpunit-php52

cover:
	vendor/bin/phpunit-php52 --coverage-html ./tests/coverage tests
