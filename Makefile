#-- VARIABLES -------------------------------------
PROJECT = ApiPlatformReact
AUTHOR = dhaouadi.amir@gmail.com
DOCKER = docker-compose
GIT = git
EXEC_PHP = php
SYMFONY = $(EXEC_PHP) bin/console
PROJECT_PATH = /home/wwwroot/sf5
COMPOSER = composer
JWT_PATH = config/jwt

#-- MAKEFILE HELPER -------------------------------
help: ## Outputs this help screen
	@grep -E '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

#-- DOCKER ENV ------------------------------------
top: ## like start with watching events response
	sudo $(DOCKER) top

start: ## Start env dev docker
	sudo $(DOCKER) start

stop: ## Stop env dev docker
	sudo $(DOCKER) stop

restart: ## Stop and start dev docker
	sudo $(DOCKER) stop && sudo $(DOCKER) start

build:  ## Build containers project into docker
	sudo $(DOCKER) build

up:
	sudo $(DOCKER) -f up -d

down: ## Remove containers project into docker
	sudo $(DOCKER) down --remove-orphans

#-- PROJECT COMPOSER --------------------------------------
install: composer.lock ## Install vendors according to the current composer.lock file
	$(COMPOSER) install

update: composer.json ## Update vendors according to the current composer.json file
	$(COMPOSER) update

#-- PROJECT ENV ------------------------------------
commands: ## Display all Strangebuzz specfic commands
	$(SYMFONY)

jwt-private: ## Generate a jwt private key
	openssl genrsa -out $(JWT_PATH)/private.pem -aes256 4096

jwt-public: ## Generate a jwt private key
	openssl rsa -pubout -in $(JWT_PATH)/private.pem -out $(JWT_PATH)/public.pem

php_container: ## Working into php docker container to execute command
	sudo $(DOCKER) exec $(EXEC_PHP) bash

warmup: ## Warmump the cache
	$(SYMFONY) cache:warmup

cc: ## Clear cache
	$(SYMFONY) c:c

entity: ## make:entity
	$(SYMFONY) make:entity

migration: ## make:migration
	$(SYMFONY) make:migration

assets: ## Install the assets with symlinks in the public folder (web)
	$(SYMFONY) assets:install web/ --symlink  --relative

purge-logs: ## Remove project logs (rm -rf var/logs/*)
	rm -rf var/logs/*

purge-cache: ## Remove project cache (rm -rf var/cache/*)
	rm -rf var/cache/*

fix-permis-var: ## Give 0777 ROLES to var folder
	chmod -R 0777 var/*

fix-permis-public: ## Give 0777 ROLES to public for folder
	chmod -R 0777 public/*

test: phpunit.xml.dist load-fixtures ## Launch all functionnal and unit tests
	bin/phpunit --stop-on-failure

#-- DEPLOY ------------------------------------
deploy: ## Deploy, install composer dependencies and run database migrations
	bin/prod/deploy.sh

git-update: ## Deploy, Update Git only and refresh cache (sf+pagespeed)
	git checkout public/index_dev.php
	git pull
	rm -rf var/cache/* var/logs/*
	php bin/console cache:warmup
	chmod -R 777 var/*
	touch /var/cache/mod_pagespeed/cache.flush
	rm public/index_dev.php

#phpunit-init-db:
#	rm -rf var/test.db3
#	composer dump-autoload -o
#   $(SYMFONY) doctrine:schema:create --env = test
#    $(SYMFONY) doctrine:fixtures:load --env = test

phpunit-run:
	php bin/phpunit