runApp=docker-compose run --rm oxpeckerdata
composer=$(runApp) composer

#---------------------------------------------------------------

sh:
	docker-compose exec oxpeckerdata bash

up:
	docker-compose up

down:
	docker-compose down --remove-orphans

restart: down up

state:
	docker-compose ps

composer-install:
	$(composer) install

composer-update:
	$(composer) update

install: up composer-install

update: up composer-update

composer:
	$(composer)
