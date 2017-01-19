php52 = /Applications/MAMP/bin/php/php5.2.17/bin/php

test:
	$(php52) vendor/bin/phpunit-php52
	vendor/bin/phpunit-php52
