cs_fix:
	vendor/bin/php-cs-fixer fix ./

lint:
	./vendor/bin/psalm

test:
	./vendor/bin/phpunit --bootstrap ./Tests/bootstrap.php ./Tests