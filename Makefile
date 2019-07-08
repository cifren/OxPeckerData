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
phpunit.wip:
	$(cmd_phpunit) --group wip

### PHP CS Fixer ###

cmd_phpcsfixer=$(cmd_php) bin/php-cs-fixer

csfixer.fix:
	$(cmd_phpcsfixer) fix

csfixer.diff:
	$(cmd_phpcsfixer) fix -v --diff --dry-run

####################

## Composer
composer.install:
	$(cmd_composer) install

composer.update:
	$(cmd_composer) update

