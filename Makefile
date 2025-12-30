.PHONY: up down migrate migration assets entity consumer test test-unit test-functional test-all test-coverage password crud consume icon telegram create-user create-admin

up:
	docker compose up -d

down:
	docker compose down

entity:
	symfony console make:entity
	#Per modificarne una esistente basta passargli il percorso App\Domain\BusLine\Entity\BusLine

migrate:
	docker compose exec symfony bash -c "php bin/console doctrine:migrations:migrate --no-interaction"

migration:
	docker compose exec symfony bash -c "php bin/console make:migration"

consumer:
	docker compose exec symfony bash -c "php bin/console messenger:consume async -vv"

# Test commands
test:
	docker compose exec symfony bash -c "vendor/bin/phpunit tests/Unit"

test-unit:
	docker compose exec symfony bash -c "vendor/bin/phpunit tests/Unit --testdox"

test-functional:
	docker compose exec symfony bash -c "vendor/bin/phpunit tests/Functional --testdox"

test-all:
	docker compose exec symfony bash -c "vendor/bin/phpunit tests/Unit --testdox"

test-coverage:
	docker compose exec symfony bash -c "vendor/bin/phpunit tests/Unit --coverage-html coverage"

assets:
	php bin/console asset-map:compile

password:
	php bin/console security:hash-password

crud:
	php bin/console make:crud

consume:
	docker compose exec symfony php bin/console messenger:consume async -vv

icon:
	mkdir -p public/svg
	cp assets/svg/icon-sprite.svg public/svg/

telegram:
	docker compose exec symfony php bin/console app:telegram:polling

#accedere a mysql in prod
#sudo docker exec -it mysql8-luma-prod bash
#mysql -u $MYSQL_USER -p$MYSQL_PASSWORD $MYSQL_DATABAS
#use luma_specific_agents
