## Variables
dkcp=docker-compose
dk_run=$(dkcp) run --rm
dk_composer=$(dk_run) composer
cmd_composer=$(dk_composer) composer
dk_php_ap=$(dk_run) php
cmd_phpunit=$(dk_php_ap) bin/phpunit

## Init project
install:
	$(composer) install

## Apache
up:
	$(dkcp) up -d

down:
	$(dkcp) down --remove-orphans

restart: down up

## Php
phpunit:
	$(cmd_phpunit)


