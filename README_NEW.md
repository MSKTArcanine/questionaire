# Questionaire – Guide d'installation et de démarrage

Ce document explique **pas-à-pas** comment installer et lancer le projet en local avec Docker, **en partant de zéro**.

---

## 📋 Table des matières

1. [Prérequis](#-prérequis)
2. [Installation initiale](#-installation-initiale)
3. [Configuration de l'environnement](#-configuration-de-lenvironnement)
4. [Construction et lancement](#-construction-et-lancement)
5. [Initialisation de la base de données](#-initialisation-de-la-base-de-données)
6. [Génération des clés JWT](#-génération-des-clés-jwt)
7. [Vérification et tests](#-vérification-et-tests)
8. [Accès à l'application](#-accès-à-lapplication)
9. [Commandes utiles](#-commandes-utiles)
10. [Dépannage](#-dépannage)
11. [Architecture du projet](#-architecture-du-projet)

---

## 🔧 Prérequis

Avant de commencer, assurez-vous d'avoir installé sur votre machine :

- **[Docker Desktop](https://www.docker.com/)** (inclut Docker Compose)

  - Version minimale : Docker 20.10+, Docker Compose V2+
  - Vérifiez avec : `docker --version` et `docker compose version`

- **`make`** (utilitaire pour exécuter les commandes du Makefile)

  - Linux/macOS : déjà installé généralement
  - Windows : installer via [chocolatey](https://chocolatey.org/) ou WSL2

- **[Composer](https://getcomposer.org/)** (gestionnaire de dépendances PHP)
  - Version minimale : Composer 2.0+
  - Vérifiez avec : `composer --version`
  - Installation : [getcomposer.org/download](https://getcomposer.org/download/)

> **Pourquoi Composer est nécessaire ?**  
> Bien que le backend Symfony s'exécute dans Docker, les **tests API** (`tests-api/`) s'exécutent sur votre machine hôte pour simuler un vrai client externe. Composer est donc requis pour installer PHPUnit et les dépendances de test.

---

## 🚀 Installation rapide

### Installation automatique en une commande

```bash
git clone https://github.com/MSKTArcanine/questionaire.git
cd questionaire
make install
```

**C'est tout !** 🎉

La commande `make install` exécute automatiquement toutes les étapes :

1. ✅ Vérification des prérequis (Docker, Docker Compose, Composer)
2. ✅ Création des fichiers `.env` (si absents)
3. ✅ Construction et démarrage des conteneurs Docker
4. ✅ Attente intelligente du démarrage de PostgreSQL (avec `pg_isready`)
5. ✅ Installation des dépendances Composer (backend et tests-api)
6. ✅ Initialisation de la base de données (migrations + fixtures)
7. ✅ Génération des clés JWT
8. ✅ Affichage du statut des conteneurs
9. ✅ Vérification de la configuration du fichier hosts

**Passez directement à la section [Accès à l'application](#-accès-à-lapplication).**

---

## 📖 Installation manuelle (détaillée)

Si vous souhaitez comprendre chaque étape ou personnaliser l'installation, suivez les sections ci-dessous.

---

### Installation manuelle (pour comprendre les détails)

Si vous souhaitez comprendre chaque étape ou personnaliser l'installation, suivez les sections ci-dessous.

### 1️⃣ Cloner le dépôt

```bash
git clone https://github.com/MSKTArcanine/questionaire.git
cd questionaire
```

**Explication** : Cette commande télécharge tout le code source du projet dans un dossier `questionaire` et vous place à l'intérieur.

---

## ⚙️ Configuration de l'environnement

### 2️⃣ Créer les fichiers de configuration

> **Note** : Si vous avez utilisé `make install`, cette étape est déjà faite automatiquement.

Docker Compose et Symfony ont besoin de **variables d'environnement** pour fonctionner (identifiants de base de données, URLs, etc.). Ces variables sont stockées dans des fichiers `.env`.

#### a) Fichier `.env` à la racine (pour Docker Compose)

```bash
cp .env.example .env
```

**Explication** : Ce fichier contient les **identifiants PostgreSQL** utilisés par Docker Compose pour créer la base de données. Ouvrez `.env` et modifiez si besoin :

```dotenv
# Identifiants de la base de données PostgreSQL
POSTGRES_DB=questionaire          # Nom de la base de données
POSTGRES_USER=questionaire_user   # Nom d'utilisateur PostgreSQL
POSTGRES_PASSWORD=VotreMotDePasse # Changez ce mot de passe !
```

> **Pourquoi ?** Docker Compose lit ces variables pour configurer le conteneur `db` (PostgreSQL). Vous devez les personnaliser pour **sécuriser** votre environnement.

#### b) Fichier `.env` dans `backend/` (pour Symfony)

```bash
cp backend/.env.example backend/.env
```

**Explication** : Symfony a besoin de sa propre configuration. Ouvrez `backend/.env` et **vérifiez que les identifiants correspondent** à ceux du fichier racine `.env` :

```dotenv
# Connexion à la base de données (doit correspondre au .env racine)
POSTGRES_DB=questionaire
POSTGRES_USER=questionaire_user
POSTGRES_PASSWORD=VotreMotDePasse

# URL de connexion utilisée par Doctrine (ORM Symfony)
DATABASE_URL=postgresql://${POSTGRES_USER}:${POSTGRES_PASSWORD}@db:5432/${POSTGRES_DB}?serverVersion=16&charset=utf8

# Configuration JWT (sera générée plus tard)
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=VotrePassphraseSecrete  # Changez cette valeur !
```

> **Points importants** :
>
> - `@db:5432` : `db` est le **nom du service** PostgreSQL dans `docker-compose.yml`. Docker résout ce nom automatiquement grâce au réseau interne Docker.
> - `JWT_PASSPHRASE` : sera utilisée pour chiffrer les clés JWT (sécurité).
> - **IMPORTANT** : Les identifiants doivent être **identiques** entre `.env` racine et `backend/.env`.

---

## 🏗️ Construction et lancement

> **Note** : Si vous avez utilisé `make install`, cette étape est déjà faite automatiquement.

### 3️⃣ Construire et démarrer tous les conteneurs

```bash
docker compose up -d --build
```

**Explication détaillée** :

- `docker compose up` : Lance tous les services définis dans `docker-compose.yml`
- `-d` (detached) : Lance les conteneurs **en arrière-plan** (vous retrouvez la main sur le terminal)
- `--build` : **Force la reconstruction** des images Docker (important la première fois ou après modifications du Dockerfile)

**Ce qui se passe** :

1. **Construction des images** :

   - `db` : Télécharge l'image PostgreSQL 16 Alpine
   - `backend` : Construit l'image Symfony (installe PHP, Composer, dépendances)
   - `frontend` : Construit l'image Next.js (installe Node.js, pnpm, **compile en production avec `pnpm build`**)
   - `caddy` : Télécharge l'image Caddy (reverse proxy)

2. **Démarrage des conteneurs** dans l'ordre de dépendance :
   - `db` démarre en premier
   - `backend` démarre (attend que `db` soit prêt grâce au `depends_on`)
   - `frontend` démarre (attend que `backend` soit prêt)
   - `caddy` démarre en dernier (reverse proxy vers backend et frontend)

**Vérification** :

```bash
docker compose ps
```

Vous devriez voir 4 conteneurs avec le statut `Up` :

```
NAME                        STATUS
questionaire-db-1           Up
questionaire-backend-1      Up
questionaire-frontend-1     Up
questionaire-caddy-1        Up
```

> **Astuce** : Si un conteneur est en `Exit` ou `Restarting`, consultez les logs : `docker compose logs nom-du-service`

---

## 🗄️ Initialisation de la base de données

> **Note** : Si vous avez utilisé `make install`, cette étape est déjà faite automatiquement.

### 4️⃣ Créer le schéma et charger les données de test

```bash
make reset-db
```

**Explication détaillée** : Cette commande Makefile exécute **4 opérations** dans le conteneur backend :

```bash
# 1. Supprime la base de données si elle existe
docker compose exec backend php bin/console doctrine:database:drop --force --if-exists

# 2. Crée une nouvelle base de données vide
docker compose exec backend php bin/console doctrine:database:create --if-not-exists

# 3. Applique toutes les migrations (crée les tables)
docker compose exec backend php bin/console doctrine:migrations:migrate --no-interaction

# 4. Charge les fixtures (données de test : admin, questionnaires, etc.)
docker compose exec backend php bin/console doctrine:fixtures:load --no-interaction --purge-with-truncate
```

**Pourquoi ?** :

- **Migrations** : Doctrine (l'ORM de Symfony) crée les tables en lisant les fichiers dans `backend/migrations/`. C'est le **versioning du schéma de base de données**. Chaque migration est un fichier PHP qui décrit les changements à apporter.
- **Fixtures** : Insère des **données de test** (utilisateurs, questionnaires) pour pouvoir tester l'application immédiatement sans devoir tout créer manuellement.

**Résultat** : Votre base de données PostgreSQL contient maintenant :

- Les tables : `user`, `questionnaire`, `question`, `choice`, `answer_session`, `question_media`, etc.
- Des données de test : un admin, un user, des questionnaires exemples avec questions et choix

---

## 🔐 Génération des clés JWT

> **Note** : Si vous avez utilisé `make install`, cette étape est déjà faite automatiquement.

### 5️⃣ Créer les clés de chiffrement JWT

```bash
docker compose exec backend php bin/console lexik:jwt:generate-keypair --overwrite
```

**Explication** :

- Cette commande génère **2 fichiers** dans `backend/config/jwt/` :
  - `private.pem` : Clé privée (utilisée pour **signer** les tokens lors du login)
  - `public.pem` : Clé publique (utilisée pour **vérifier** les tokens lors des requêtes API)
- `--overwrite` : Remplace les clés existantes si elles existent déjà

**Comment ça marche ?** :

1. Quand un user se connecte, Symfony génère un JWT signé avec `private.pem`
2. Le frontend stocke ce token et l'envoie à chaque requête API
3. Symfony vérifie la signature avec `public.pem` pour s'assurer que le token est valide

**Sécurité** :

- Ces fichiers ne doivent **jamais être partagés** ou versionnés (vérifiez qu'ils sont dans `.gitignore` !)
- Ils sont chiffrés avec la `JWT_PASSPHRASE` de votre `backend/.env`
- Si vous changez la `JWT_PASSPHRASE`, vous devez **régénérer les clés**

---

## ✅ Vérification et tests

### 6️⃣ Lancer les tests API

Pour vérifier que tout fonctionne correctement, lancez la suite de tests :

```bash
make test-api
```

**Explication** : Cette commande exécute **tous les tests PHPUnit** dans le dossier `tests-api/` :

- Tests d'authentification (login admin/user, JWT valide/invalide)
- Tests CRUD (créer/lire/modifier/supprimer questionnaires, questions, choix)
- Tests de sessions de réponses (parcours de questionnaires)
- Tests de streaming de médias (upload/download de fichiers)

**Résultat attendu** :

```
OK (50 tests, 200 assertions)
```

**Pourquoi tester ?** :

- Garantit que l'API fonctionne correctement
- Vérifie que la base de données, JWT, et tous les endpoints sont opérationnels
- Détecte les régressions lors des modifications futures

Si des tests échouent :

1. Vérifiez que `make reset-db` a bien été exécuté
2. Vérifiez que les clés JWT sont générées
3. Consultez les logs : `docker compose logs backend`
4. Consultez la section [Dépannage](#-dépannage)

---

## 🌐 Accès à l'application

### 7️⃣ Configurer votre fichier hosts

> **Note** : Si vous avez utilisé `make install`, la commande a vérifié automatiquement si cette configuration est déjà en place.

Pour accéder à l'application via `https://questionaire.localhost`, ajoutez cette ligne à votre fichier hosts :

**Linux/macOS** : `/etc/hosts`

```bash
sudo nano /etc/hosts
```

**Windows** : `C:\Windows\System32\drivers\etc\hosts`

```bash
# Ouvrir en tant qu'administrateur avec Notepad
```

Ajoutez :

```
127.0.0.1   questionaire.localhost
```

**Pourquoi ?** : Caddy (le reverse proxy) est configuré pour répondre uniquement au domaine `questionaire.localhost`. Le fichier hosts indique à votre navigateur que ce domaine pointe vers votre machine locale (127.0.0.1).

> **Astuce** : La commande `make install` détecte automatiquement si cette ligne est présente et vous avertit si elle manque.

### 8️⃣ Ouvrir l'application

Ouvrez votre navigateur et accédez à :

🔗 **https://questionaire.localhost**

**Ce qui se passe** :

1. Votre navigateur envoie une requête à `questionaire.localhost` (qui pointe vers 127.0.0.1)
2. Caddy (qui écoute sur le port 443) reçoit la requête
3. Selon l'URL, Caddy joue le rôle de **reverse proxy** :
   - `/api/*` → Caddy redirige vers le **backend** Symfony (port 8000)
   - Tout le reste → Caddy redirige vers le **frontend** Next.js (port 3000)
4. Le frontend communique avec le backend via l'API

**Comptes de test (créés par les fixtures)** :

- **Admin** :
  - Email : `admin@example.com`
  - Mot de passe : `admin`
  - Accès : Création de questionnaires, gestion des questions, upload de médias
- **User** :
  - Email : `user@example.com`
  - Mot de passe : `1234` (PIN 4 chiffres)
  - Accès : Répondre aux questionnaires

> **Note** : Votre navigateur affichera un avertissement de certificat SSL car Caddy génère un certificat auto-signé. Cliquez sur "Continuer malgré tout" (safe en développement local).

---

## 🛠️ Commandes utiles

### Installation et réinstallation

```bash
# Installation complète automatique (recommandé)
make install

# Réinstallation complète (supprime TOUT et réinstalle)
make reinstall
```

**`make install`** exécute automatiquement :

- ✅ Vérification des prérequis
- ✅ Création des fichiers .env
- ✅ Construction et démarrage Docker
- ✅ Attente intelligente de PostgreSQL (avec pg_isready)
- ✅ Initialisation de la base de données
- ✅ Génération des clés JWT
- ✅ Vérification du fichier hosts
- ✅ Affichage du statut des conteneurs

**`make reinstall`** :

- ⚠️ Avertissement avec compte à rebours de 5 secondes
- 🗑️ Suppression complète : `docker compose down -v`
- 🚀 Relance : `make install`

### Gestion de Docker

```bash
# Voir les conteneurs actifs
docker compose ps

# Voir les logs d'un service
docker compose logs backend
docker compose logs -f frontend  # -f pour suivre en temps réel

# Redémarrer un service
docker compose restart backend

# Arrêter tous les conteneurs
docker compose down

# Arrêter et SUPPRIMER les volumes (⚠️ efface la base de données)
docker compose down -v

# Reconstruire un service spécifique
docker compose build frontend
docker compose up -d frontend

# Reconstruire TOUS les services
docker compose down
docker compose up -d --build
```

### Gestion de la base de données

```bash
# Réinitialiser complètement la DB (drop + create + migrate + fixtures)
make reset-db

# Créer une nouvelle migration après modification d'une entité
make migrate-make

# Appliquer les migrations en attente
make migrate

# Recharger uniquement les fixtures (sans drop)
make fixtures
```

### Accéder aux conteneurs

```bash
# Ouvrir un shell dans le backend
docker compose exec backend bash

# Ouvrir un shell dans la base de données PostgreSQL
docker compose exec db psql -U questionaire_user -d questionaire

# Ouvrir un shell dans le frontend
docker compose exec frontend sh
```

### Tests

```bash
# Tous les tests API
make test-api

# Un seul fichier de test
make test-api-one t=Api/QuestionnaireApiTest.php

# Un seul test spécifique
make test-api-one t=Api/QuestionnaireApiTest.php::testListQuestionnaires

# Tests frontend (Jest)
make test-front
```

### Installation des dépendances

```bash
# Installer les dépendances Composer du backend (dans le conteneur)
docker compose exec backend composer install

# Installer les dépendances Composer des tests-api (sur l'hôte)
cd tests-api && composer install

# Installer les dépendances pnpm du frontend (dans le conteneur)
docker compose exec frontend pnpm install
```

> **Note** : `make install` exécute déjà ces commandes automatiquement. Elles sont utiles si vous modifiez `composer.json` ou `package.json`.

---

## 🚨 Dépannage

### Problème : Ports déjà utilisés

**Erreur** : `Error starting userland proxy: listen tcp 0.0.0.0:80: bind: address already in use`

**Solution** :

```bash
# Identifier le processus utilisant le port
sudo lsof -i :80   # ou :443, :5432, :3000, :8000
sudo netstat -tuln | grep :80

# Arrêter le service (exemple avec Apache)
sudo systemctl stop apache2
sudo systemctl stop nginx

# Ou changer les ports dans docker-compose.yml
ports:
  - "8080:80"   # Utiliser 8080 au lieu de 80
  - "8443:443"  # Utiliser 8443 au lieu de 443
```

### Problème : La base de données n'est pas accessible

**Symptôme** : `SQLSTATE[08006] Connection refused` ou `could not connect to server`

**Solutions** :

1. Vérifiez que le conteneur `db` est bien démarré :

   ```bash
   docker compose ps db
   docker compose logs db
   ```

2. Attendez quelques secondes (PostgreSQL peut prendre 5-10 secondes à démarrer)

3. Vérifiez les identifiants dans `backend/.env` :

   ```bash
   grep DATABASE_URL backend/.env
   # Doit correspondre aux identifiants du .env racine
   ```

4. Testez la connexion manuellement :
   ```bash
   docker compose exec db psql -U questionaire_user -d questionaire
   # Si ça fonctionne, le problème vient de la configuration Symfony
   ```

### Problème : JWT invalide ou expiré

**Symptôme** : `Invalid JWT Token` ou `Expired JWT Token` dans les requêtes API

**Solutions** :

1. Régénérez les clés JWT :

   ```bash
   docker compose exec backend php bin/console lexik:jwt:generate-keypair --overwrite
   ```

2. Vérifiez que `JWT_PASSPHRASE` est identique dans `backend/.env`

3. Reconnectez-vous (les anciens tokens ne sont plus valides après régénération des clés)

4. Vérifiez que les fichiers existent :
   ```bash
   docker compose exec backend ls -la config/jwt/
   # Doit afficher private.pem et public.pem
   ```

### Problème : Le frontend ne se construit pas

**Symptôme** : Erreur lors de `docker compose build frontend` ou `pnpm build`

**Solutions** :

1. Vérifiez les logs de build :

   ```bash
   docker compose build frontend --no-cache
   ```

2. Supprimez l'ancien build et reconstruisez :

   ```bash
   docker compose down
   docker rmi questionaire-frontend
   docker compose build frontend --no-cache
   docker compose up -d frontend
   ```

3. Si erreur de mémoire, augmentez la RAM allouée à Docker Desktop (Settings → Resources)

### Problème : Les migrations échouent

**Symptôme** : `The metadata storage is not up to date` ou `Migration already executed`

**Solutions** :

1. Réinitialisez complètement la base de données :

   ```bash
   make reset-db
   ```

2. Si le problème persiste, supprimez le volume PostgreSQL :
   ```bash
   docker compose down -v
   docker compose up -d
   make reset-db
   ```

### Problème : Erreur "Dependencies are missing, try running composer install"

**Symptôme** : Erreur lors de `make reset-db` ou `make test-api`

**Solutions** :

1. Installez les dépendances du backend :

   ```bash
   docker compose exec backend composer install
   ```

2. Installez les dépendances des tests :

   ```bash
   cd tests-api && composer install
   ```

3. Ou relancez l'installation complète :
   ```bash
   make reinstall
   ```

> **Note** : `make install` installe automatiquement ces dépendances. Cette erreur survient si vous avez sauté cette étape ou si les fichiers `vendor/` ont été supprimés.

### Problème : Les fixtures ne se chargent pas

**Symptôme** : Login échoue avec "Invalid credentials"

**Solution** :

```bash
# Rechargez les fixtures
make fixtures

# Ou réinitialisez complètement
make reset-db
```
