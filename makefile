test:
	/Applications/Mamp/bin/php/php5.2.17/bin/php vendor/bin/phpunit-php52
	vendor/bin/phpunit-php52

cover:
	vendor/bin/phpunit-php52 --coverage-html ./tests/coverage tests
