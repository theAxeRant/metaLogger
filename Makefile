###############################################################################
#                    __        __
#    ____ ___  ___  / /_____ _/ /   ____  ____ _____ ____  _____
#   / __ `__ \/ _ \/ __/ __ `/ /   / __ \/ __ `/ __ `/ _ \/ ___/
#  / / / / / /  __/ /_/ /_/ / /___/ /_/ / /_/ / /_/ /  __/ /
# /_/ /_/ /_/\___/\__/\__,_/_____/\____/\__, /\__, /\___/_/
#                                      /____//____/
#
.DEFAULT_GOAL := phar
DEV = devops/dev
COMPOSE = docker compose --project-directory "$(DEV)"

.PHONY: check-config build up down bash phar

check-config:
	@test -s "$(DEV)/compose.override.yaml" || { echo "ERROR: $(DEV)/compose.override.yaml is missing; copy compose.override.yaml.example and configure the checkout path." >&2; exit 1; }

build: check-config
	@echo "##### Building Dev Image #####"
	@$(COMPOSE) build php

up: check-config
	@echo "##### Bringing up Dev Containers #####"
	@$(COMPOSE) up -d

down: check-config
	@echo "##### Stopping Dev Containers #####"
	@$(COMPOSE) down

bash: up
	@echo "##### Dev php Container Bash Prompt #####"
	@$(COMPOSE) exec php bash

phar: check-config build
	@echo "##### Building Phar File #####"
	@set -e; \
	cleanup() { \
		status=$$?; \
		trap - 0; \
		$(COMPOSE) down || { \
			cleanup_status=$$?; \
			if [ "$$status" -eq 0 ]; then status=$$cleanup_status; fi; \
		}; \
		exit "$$status"; \
	}; \
	trap cleanup 0; \
	trap 'exit 130' INT; \
	trap 'exit 143' TERM; \
	$(COMPOSE) up -d; \
	$(COMPOSE) exec -T php composer install --no-interaction; \
	$(COMPOSE) exec -T php make build; \
	mkdir -p release; \
	$(COMPOSE) cp php:/var/www/symfony/metaLogger.phar release/metaLogger.phar
