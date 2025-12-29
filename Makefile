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

.PHONY: .install
install: install-8.5

.PHONY: install-8.4
install-8.4:
	docker compose run --rm php-8.4 composer install

.PHONY: install-8.5
install-8.5:
	docker compose run --rm php-8.5 composer install

.PHONY: php-cli
php-cli: php-8.5-cli

.PHONY: php-8.4-cli
php-8.4-cli:
	docker compose run --rm php-8.4 sh

.PHONY: php-8.5-cli
php-8.5-cli:
	docker compose run --rm php-8.5 sh

##
## Tests and code validation
## -------------------------
##

.PHONY: verify
verify: php-code-validation php-tests php-mutation-testing

.PHONY: php-tests
php-tests: php-8.4-tests php-8.5-tests

.PHONY: php-tests-coverage
php-tests-coverage: php-8.5-tests-html-coverage

.PHONY: php-8.4-tests
php-8.4-tests:
	docker compose run --rm php-8.4 ./vendor/bin/phpunit

.PHONY: php-8.5-tests
php-8.5-tests:
	docker compose run --rm php-8.5 ./vendor/bin/phpunit

.PHONY: php-8.4-tests-html-coverage
php-8.4-tests-html-coverage:
	docker compose run --rm php-8.4 ./vendor/bin/phpunit --coverage-html ./coverage

.PHONY: php-8.5-tests-html-coverage
php-8.5-tests-html-coverage:
	docker compose run --rm php-8.5 ./vendor/bin/phpunit --coverage-html ./coverage

.PHONY: php-code-validation
php-code-validation:
	docker compose run --rm php-8.4 ./vendor/bin/php-cs-fixer fix
	docker compose run --rm php-8.4 ./vendor/bin/psalm --show-info=false --no-diff
	docker compose run --rm php-8.4 ./vendor/bin/phpstan --xdebug

.PHONY: php-mutation-testing
php-mutation-testing:
	docker compose run --rm php-8.4 ./vendor/bin/infection --show-mutations --only-covered --threads=8

##
## CI
## --
##

.PHONY: php-8.4-tests-ci
php-8.4-tests-ci:
	docker compose run --rm php-8.4 ./vendor/bin/phpunit --coverage-clover ./coverage.xml

.PHONY: php-8.5-tests-ci
php-8.5-tests-ci:
	docker compose run --rm php-8.5 ./vendor/bin/phpunit

.PHONY: php-mutation-testing-ci
php-mutation-testing-ci:
	docker compose run --rm php-8.5 ./vendor/bin/infection --only-covered --threads=max
