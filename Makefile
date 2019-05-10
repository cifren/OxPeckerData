runApp=docker-compose run --rm oxpeckerdata
composer=$(runApp) composer
php=$(runApp) php

#---------------------------------------------------------------

sh:
	docker-compose exec oxpeckerdata bash

build:
	docker-compose build

up:
	docker-compose up

down:
	docker-compose down --remove-orphans

restart: down up

state:
	docker-compose ps

composer.install:
	$(composer) install

composer.update:
	$(composer) update

install: up composer.install

update: up composer.update

composer.version:
	$(composer) --version

php.version:
	$(php) -v

version: composer.version php.version

test.phpunit:
	$(php) vendor/bin/phpunit

test.phpunit:
	$(php) vendor/bin/phpunit

php-cs-fixer.diff:
	$(php) vendor/bin/php-cs-fixer fix --config .php_cs --dry-run --diff

php-cs-fixer.fix:
	$(php) vendor/bin/php-cs-fixer fix --config .php_cs

coverall:
	$(php) vendor/bin/coveralls -v
