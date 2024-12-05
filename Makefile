SHELL = /bin/bash

uid = $$(id -u)
gid = $$(id -g)
pwd = $$(pwd)

##
## Docker
## ------
##

.PHONY: build
build:
	docker compose build

.PHONY: up
up: .up

.up:
	docker compose up -d

.PHONY: down
down: .down

.down:
	docker compose down

.PHONY: update
update: build up

.PHONY: reset
reset: .reset

.PHONY: .reset
.reset: .down .install .up

.PHONY: .install
install: install-8.4

.PHONY: install-8.3
install-8.3:
	docker compose run --rm php-8.3 composer install

.PHONY: install-8.4
install-8.4:
	docker compose run --rm php-8.4 composer install

.PHONY: php-cli
php-cli: php-8.4-cli

.PHONY: php-8.3-cli
php-8.3-cli:
	docker compose run --rm php-8.3 sh

.PHONY: php-8.4-cli
php-8.4-cli:
	docker compose run --rm php-8.4 sh

##
## Tests and code validation
## -------------------------
##

.PHONY: verify
verify: php-code-validation php-tests php-mutation-testing

.PHONY: php-tests
php-tests: php-8.3-tests php-8.4-tests

.PHONY: php-tests-coverage
php-tests-coverage: php-8.4-tests-html-coverage

.PHONY: php-8.3-tests
php-8.3-tests:
	docker compose run --rm php-8.3 ./vendor/bin/phpunit

.PHONY: php-8.4-tests
php-8.4-tests:
	docker compose run --rm php-8.4 ./vendor/bin/phpunit

.PHONY: php-8.3-tests-html-coverage
php-8.3-tests-html-coverage:
	docker compose run --rm php-8.3 ./vendor/bin/phpunit --coverage-html ./coverage

.PHONY: php-8.4-tests-html-coverage
php-8.4-tests-html-coverage:
	docker compose run --rm php-8.4 ./vendor/bin/phpunit --coverage-html ./coverage

.PHONY: php-code-validation
php-code-validation:
	docker compose run --rm php-8.3 ./vendor/bin/php-cs-fixer fix
	docker compose run --rm php-8.3 ./vendor/bin/psalm --show-info=false --no-diff
	docker compose run --rm php-8.3 ./vendor/bin/phpstan --xdebug

.PHONY: php-mutation-testing
php-mutation-testing:
	docker compose run --rm php-8.3 ./vendor/bin/infection --show-mutations --only-covered --threads=8

##
## CI
## --
##

.PHONY: php-8.3-tests-ci
php-8.3-tests-ci:
	docker compose run --rm php-8.3 ./vendor/bin/phpunit --coverage-clover ./coverage.xml

.PHONY: php-8.4-tests-ci
php-8.4-tests-ci:
	docker compose run --rm php-8.4 ./vendor/bin/phpunit

.PHONY: php-mutation-testing-ci
php-mutation-testing-ci:
	docker compose run --rm php-8.3 ./vendor/bin/infection --only-covered --threads=max
