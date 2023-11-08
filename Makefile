.PHONY: help

help: ## list all the Makefile commands
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

#############
# COMMANDS #
#############
install: _prune rebuild

rebuild:
	docker-compose up -d --force-recreate --build

start:
	docker-compose up -d

stop:
	docker-compose down

_prune:
	docker-compose down -v --remove-orphans

tests: ## run unit tests
	docker exec -i wallet composer tests:integration

php-sh: ## open bash in the PHP container
	docker exec -it wallet bash