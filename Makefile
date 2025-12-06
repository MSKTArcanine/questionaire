# INSTALLATION COMPLÈTE

# Installation complète du projet (voir README.md)
install:
	@echo "🚀 Installation du projet Questionaire..."
	@echo ""
	@echo "📋 Étape 1/6 : Vérification des prérequis..."
	@command -v docker >/dev/null 2>&1 || { echo "❌ Docker n'est pas installé. Installez Docker Desktop : https://www.docker.com/"; exit 1; }
	@command -v docker compose >/dev/null 2>&1 || { echo "❌ Docker Compose n'est pas installé."; exit 1; }
	@echo "✅ Docker et Docker Compose sont installés"
	@echo ""
	@echo "📋 Étape 2/6 : Configuration des fichiers .env..."
	@if [ ! -f .env ]; then \
		cp .env.example .env && echo "✅ .env créé (pensez à personnaliser les mots de passe !)"; \
	else \
		echo "⚠️  .env existe déjà (non modifié)"; \
	fi
	@if [ ! -f backend/.env ]; then \
		cp backend/.env.example backend/.env && echo "✅ backend/.env créé (pensez à personnaliser les mots de passe !)"; \
	else \
		echo "⚠️  backend/.env existe déjà (non modifié)"; \
	fi
	@echo ""
	@echo "📋 Étape 3/6 : Construction et démarrage des conteneurs Docker..."
	@docker compose up -d --build
	@echo "✅ Conteneurs démarrés"
	@echo ""
	@echo "📋 Étape 4/6 : Attente du démarrage de PostgreSQL..."
	@until docker compose exec -T db pg_isready -U $$(grep POSTGRES_USER .env | cut -d '=' -f2) -q 2>/dev/null; do \
		printf "."; \
		sleep 1; \
	done
	@echo ""
	@echo "✅ Base de données prête"
	@echo ""
	@echo "📋 Étape 5/6 : Initialisation de la base de données (migrations + fixtures)..."
	@$(MAKE) reset-db
	@echo "✅ Base de données initialisée"
	@echo ""
	@echo "📋 Étape 6/6 : Génération des clés JWT..."
	@docker compose exec backend php bin/console lexik:jwt:generate-keypair --overwrite
	@echo "✅ Clés JWT générées"
	@echo ""
	@echo "🎉 Installation terminée avec succès !"
	@echo ""
	@echo "📊 Statut des conteneurs :"
	@docker compose ps
	@echo ""
	@echo "📖 Prochaines étapes :"
	@if ! grep -q "questionaire.localhost" /etc/hosts 2>/dev/null; then \
		echo "   ⚠️  IMPORTANT : Ajoutez '127.0.0.1 questionaire.localhost' à votre fichier /etc/hosts"; \
		echo "      Linux/macOS : sudo nano /etc/hosts"; \
		echo "      Windows : Notepad en admin : C:\\Windows\\System32\\drivers\\etc\\hosts"; \
	else \
		echo "   ✅ questionaire.localhost déjà configuré dans /etc/hosts"; \
	fi
	@echo "   2. Ouvrez https://questionaire.localhost dans votre navigateur"
	@echo "   3. Connectez-vous avec admin@example.com / admin (admin) ou user@example.com / 1234 (user)"
	@echo ""
	@echo "🧪 Pour vérifier que tout fonctionne : make test-api"
	@echo "📚 Pour plus d'informations : consultez README.md"
	@echo ""

# Réinstallation complète (supprime tout et réinstalle)
reinstall:
	@echo "⚠️  ATTENTION : Cette commande va SUPPRIMER tous les conteneurs et volumes !"
	@echo "Appuyez sur Ctrl+C pour annuler, ou attendez 5 secondes..."
	@sleep 5
	@echo ""
	@echo "🗑️  Suppression des conteneurs et volumes..."
	@docker compose down -v
	@echo "✅ Nettoyage terminé"
	@echo ""
	@$(MAKE) install

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