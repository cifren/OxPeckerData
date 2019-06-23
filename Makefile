## Variables
dkcp=docker-compose
dk_run=$(dkcp) run --rm
dk_composer=$(dk_run) composer
cmd_composer=$(dk_composer) composer
cmd_php=$(dk_run) php
cmd_phpunit=$(cmd_php) bin/phpunit

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

### PHP CS Fixer ###

cmd_phpcsfixer=$(cmd_php) bin/php-cs-fixer

php-cs-fixer.fix:
	$(cmd_phpcsfixer) fix

php-cs-fixer.diff:
	$(cmd_phpcsfixer) fix -v --diff --dry-run"
####################


