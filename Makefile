###############################################################################
#                    __        __
#    ____ ___  ___  / /_____ _/ /   ____  ____ _____ ____  _____
#   / __ `__ \/ _ \/ __/ __ `/ /   / __ \/ __ `/ __ `/ _ \/ ___/
#  / / / / / /  __/ /_/ /_/ / /___/ /_/ / /_/ / /_/ /  __/ /
# /_/ /_/ /_/\___/\__/\__,_/_____/\____/\__, /\__, /\___/_/
#                                      /____//____/
#

DEV = devops/dev
PROD = devops/prod

dev-build:
	@echo "##### Building Dev Image #####"
	@test -s ${DEV}/compose.override.yaml || { echo "ERROR: compose.override.yaml is missing"; exit 1; }
	@(cd ${DEV} && docker compose build php)

dev-up:
	@echo "##### Bringing up Dev Containers #####"
	@test -s ${DEV}/compose.override.yaml || { echo "ERROR: compose.override.yaml is missing"; exit 1; }
	@(cd ${DEV} && docker compose up -d)

dev-down: dev-up
	@echo "##### Stopping Dev Containers #####"
	@(cd ${DEV} && docker compose down)

dev-bash: dev-up
	@echo "##### Dev php Container Bash Prompt #####"
	@(cd ${DEV} && docker compose exec php bash)

build:
	@echo "##### Building Prod Image #####"
	@test -s ${PROD}/compose.override.yaml || { echo "ERROR: compose.override.yaml is missing"; exit 1; }
	@(cd ${PROD} && docker compose build php)

phar: build
	@echo "##### Building Phar File #####"
	@test -s ${PROD}/compose.override.yaml || { echo "ERROR: compose.override.yaml is missing"; exit 1; }
	@(cd ${PROD} && docker compose up -d)
	@(cd ${PROD} && docker compose exec php composer install)
	@(cd ${PROD} && docker compose exec php make build)
	@(cd ${PROD} && docker compose down)