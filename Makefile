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

build:
	@echo "##### Building Dev Image #####"
	@test -s ${DEV}/compose.override.yaml || { echo "ERROR: compose.override.yaml is missing"; exit 1; }
	@(cd ${DEV} && docker compose build php)

up:
	@echo "##### Bringing up Dev Containers #####"
	@test -s ${DEV}/compose.override.yaml || { echo "ERROR: compose.override.yaml is missing"; exit 1; }
	@(cd ${DEV} && docker compose up -d)

down: up
	@echo "##### Stopping Dev Containers #####"
	@(cd ${DEV} && docker compose down)

bash: up
	@echo "##### Dev php Container Bash Prompt #####"
	@(cd ${DEV} && docker compose exec php bash)

phar: build
	@echo "##### Building Phar File #####"
	@test -s ${DEV}/compose.override.yaml || { echo "ERROR: compose.override.yaml is missing"; exit 1; }
	@(cd ${DEV} && docker compose up -d)
	@(cd ${DEV} && docker compose exec php composer install)
	@(cd ${DEV} && docker compose exec php make build)
	@(cd ${DEV} && docker compose down)