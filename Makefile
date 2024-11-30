ROOT_DIR:=$(shell dirname $(realpath $(firstword $(MAKEFILE_LIST))))
COMPOSE_FILE=$(ROOT_DIR)/docker-compose.yml
COMPOSE_ENV_FILE=$(ROOT_DIR)/.env
DC=docker-compose -f $(COMPOSE_FILE) --env-file $(COMPOSE_ENV_FILE)

.PHONY: up down restart ps logs php rabbitmq supervisor build clean install test lint cs-fix cache-clear db-migrate db-create db-drop redis-cli elastic-cli

# Цель по умолчанию
.DEFAULT_GOAL := help

build:
	$(DC) build

rebuild: down build up

up:
	$(DC) up -d

down:
	$(DC) down --remove-orphans

restart: down up

shv:
	docker exec -it otus-supervisor sh

ps:
	$(DC) ps

logs:
	$(DC) logs -f

php:
	docker exec -it otus-symfony bash

rabbitmq:
	$(DC) exec ${RABBITMQ_CONTAINER_NAME} bash

supervisor:
	$(DC) exec ${SUPERVISOR_CONTAINER_NAME} bash

clean:
	rm -rf var/log/*

# Composer commands
install:
	$(DC) exec -T otus-symfony composer install

update:
	$(DC) exec -T otus-symfony composer update

# Symfony commands
cache-clear:
	$(DC) exec -T otus-symfony php bin/console cache:clear

# Database commands
db-create:
	$(DC) exec -T otus-symfony php bin/console doctrine:database:create --if-not-exists

db-drop:
	$(DC) exec -T otus-symfony php bin/console doctrine:database:drop --force --if-exists

db-migrate:
	$(DC) exec -T otus-symfony php bin/console doctrine:migrations:migrate --no-interaction

db-diff:
	$(DC) exec -T otus-symfony php bin/console doctrine:migrations:diff

# Code quality
lint:
	$(DC) exec -T otus-symfony composer lint
	$(DC) exec -T otus-symfony composer phpstan

cs-fix:
	$(DC) exec -T otus-symfony composer cs-fix

# Tests
test:
	$(DC) exec -T otus-symfony composer test

test-coverage:
	$(DC) exec -T otus-symfony composer test-coverage

# Redis CLI
redis-cli:
	$(DC) exec ${REDIS_CONTAINER_NAME} redis-cli -a ${REDIS_PASSWORD}

# Elasticsearch
elastic-cli:
	$(DC) exec ${ELASTICSEARCH_CONTAINER_NAME} curl localhost:9200

# Logs for specific services
logs-php:
	$(DC) logs -f otus-symfony

logs-nginx:
	$(DC) logs -f nginx

logs-db:
	$(DC) logs -f otus-postgresql

logs-redis:
	$(DC) logs -f otus-redis

logs-rabbit:
	$(DC) logs -f otus-rabbitmq

# Container status
status:
	@echo "Docker containers status:"
	@$(DC) ps
	@echo "\nDocker containers resource usage:"
	@docker stats --no-stream $(shell docker ps --format "{{.Names}}")

# Help
help:
	@echo "Available commands:"
	@echo "  make up              - Start all containers"
	@echo "  make down            - Stop all containers"
	@echo "  make restart         - Restart all containers"
	@echo "  make build           - Build all containers"
	@echo "  make rebuild         - Rebuild all containers"
	@echo "  make install         - Install composer dependencies"
	@echo "  make update          - Update composer dependencies"
	@echo "  make cache-clear     - Clear Symfony cache"
	@echo "  make db-create       - Create database"
	@echo "  make db-drop         - Drop database"
	@echo "  make db-migrate      - Run database migrations"
	@echo "  make db-diff         - Generate migration diff"
	@echo "  make lint            - Run PHP linter and PHPStan"
	@echo "  make cs-fix          - Fix code style"
	@echo "  make test            - Run tests"
	@echo "  make test-coverage   - Run tests with coverage"
	@echo "  make redis-cli       - Open Redis CLI"
	@echo "  make elastic-cli     - Check Elasticsearch status"
	@echo "  make logs-*          - View logs for specific service"
	@echo "  make status          - Show containers status and resource usage"