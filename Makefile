#  TESTS

# Tests API externes (tests-api)
test-api:
	cd tests-api && ./vendor/bin/phpunit

# Tests API externes, un seul fichier / test :
# usage :
#   make test-api-one t=Api/QuestionnaireApiTest.php
#   make test-api-one t=Api/QuestionnaireApiTest.php::testListQuestionnaires
test-api-one:
	cd tests-api && ./vendor/bin/phpunit $(t)

# # Tests internes Symfony (dans backend/tests)
# test-back:
# 	docker compose exec backend ./vendor/bin/simple-phpunit

# # Tests internes, un seul fichier :
# #   make test-back-one t=tests/Service/QuestionNavigatorTest.php
# test-back-one:
# 	docker compose exec backend ./vendor/bin/simple-phpunit $(t)

# DB

# Générer une migration
migrate-make:
	docker compose exec backend php bin/console make:migration

# Jouer les migrations
migrate:
	docker compose exec backend php bin/console doctrine:migrations:migrate --no-interaction

# Recharger les fixtures + purge
fixtures:
	docker compose exec backend php bin/console doctrine:fixtures:load --no-interaction --purge-with-truncate
# Test front :
test-front:
	docker compose exec frontend pnpm test
# Bye la DB :
reset-db:
	docker compose exec backend php bin/console doctrine:database:drop --force --if-exists
	docker compose exec backend php bin/console doctrine:database:create --if-not-exists
	docker compose exec backend php bin/console doctrine:migrations:migrate --no-interaction
	docker compose exec backend php bin/console doctrine:fixtures:load --no-interaction --purge-with-truncate