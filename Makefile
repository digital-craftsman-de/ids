SHELL = /bin/bash

uid = $$(id -u)
gid = $$(id -g)
pwd = $$(pwd)

default: up

## update		Rebuild Docker images and start stack.
.PHONY: update
update: build up

## reset		Teardown stack, install and start.
.PHONY: reset
reset: .reset

.PHONY: .reset
.reset: .down .install .up

##
## Docker
## ------
##

## install	Install PHP dependencies.
.PHONY: install
install: .install

.PHONY: .install
.install:
	docker-compose run --rm php-8.0 composer install

## build		Build the Docker images.
.PHONY: build
build:
	docker-compose build

## up		Start the Docker stack.
.PHONY: up
up: .up

.up:
	docker-compose up -d

## down		Stop the Docker stack.
.PHONY: down
down: .down

.down:
	docker-compose down

## php-8.0-cli	Enter a shell for the PHP 8.0.
.PHONY: php-8.0-cli
php-8.0-cli:
	docker-compose run --rm php-8.0 sh

## php-8.1-cli	Enter a shell for the PHP 8.1.
.PHONY: php-8.1-cli
php-8.1-cli:
	docker-compose run --rm php-8.1 sh

##
## Tests
## -----
##

## php-tests		Run the PHP tests.
.PHONY: php-tests
php-tests: php-8.0-tests php-8.1-tests

## php-8.0-tests		Run the PHP tests.
.PHONY: php-8.0-tests
php-8.0-tests:
	docker-compose run --rm php-8.0 ./vendor/bin/phpunit

## php-8.1-tests		Run the PHP tests.
.PHONY: php-8.1-tests
php-8.1-tests:
	docker-compose run --rm php-8.1 ./vendor/bin/phpunit

## php-8.0-tests-ci		Run the tests for PHP 8.0 with coverage report for CI.
.PHONY: php-8.0-tests-ci
php-8.0-tests-ci:
	docker-compose run --rm php-8.0 ./vendor/bin/phpunit --coverage-clover ./coverage.xml

## php-8.1-tests-ci		Run the tests for PHP 8.1 with coverage report for CI.
.PHONY: php-8.1-tests-ci
php-8.1-tests-ci:
	docker-compose run --rm php-8.1 ./vendor/bin/phpunit --coverage-clover ./coverage.xml

## php-8.0-tests-html-coverage		Run the PHP tests with coverage report as HTML.
.PHONY: php-8.0-tests-html-coverage
php-8.0-tests-html-coverage:
	docker-compose run --rm php-8.0 ./vendor/bin/phpunit --coverage-html ./coverage

##
## Code validations
## ----------------
##

## php-code-validation		Run code fixers and linters for PHP.
.PHONY: php-code-validation
php-code-validation:
	docker-compose run --rm php-8.0 ./vendor/bin/php-cs-fixer fix
	docker-compose run --rm php-8.0 ./vendor/bin/psalm --show-info=false --no-diff
