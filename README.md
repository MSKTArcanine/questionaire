# Questionaire – Guide de démarrage

Ce document explique pas-à-pas comment lancer le projet en local avec Docker.

---

## 🔧 Prérequis

Assurez-vous d’avoir installé sur votre machine :

- [Docker](https://www.docker.com/) + Docker Compose
- `make`
- PHP + [Composer](https://getcomposer.org/)
- Node.js + `pnpm`

---

## 1️⃣ Cloner le dépôt

```bash
git clone <URL_DU_REPO_GITHUB>
cd questionaire
```

## 2️⃣ Configurer les variables d’environnement

Dans le backend et la root, copiez les fichiers d’exemple puis adaptez-les si besoin :

```bash
cp backend/.env.example backend/.env
cp .env.example .env
```

## 3️⃣ Lancer les conteneurs Docker

Depuis la racine du projet (questionaire) :

```bash
docker compose up -d --build
```

## 4️⃣ Installer les dépendances applicatives
Backend (Symfony) :

```bash
cd backend
composer install
cd ..
```

Frontend (Next.js)

```bash
cd frontend
pnpm install
cd ..
```

## 5️⃣ Initialiser la base de données

Depuis la racine du projet :
```bash
make reset-db
docker ps
```
Si tous les dockers sont présents et en ligne, continuez à l'étape 6.
Sinon : Vérifiez vos ports 80 443 3000 et 8000, et fermez les services les utilisants.

## 7️⃣ Redémarrer proprement la stack

Toujours à la racine du projet :

```bash
docker compose down
docker compose up -d
```

## 8️⃣ Générer les clés JWT (LexikJWTAuthenticationBundle)

Entrez dans le conteneur backend :

```bash
docker compose exec backend bash
```

Puis à l’intérieur du conteneur :

```bash
php bin/console lexik:jwt:generate-keypair --overwrite
exit
```

## 9️⃣ Vérifier que tout fonctionne (tests API)

Toujours à la racine du projet :

```bash
make test-api
make reset-db
```

✅ Tous les tests doivent être verts.
Si un test échoue, vérifiez d’abord la configuration (.env, base de données, clés JWT, ports, etc.).

## Modélisation de l'arbre :

- Modélisation faites en DB par l'ajout de questions ROOT aux questionnaires, puis l'ajout de choix pouvant ensuite donner suite à une autre question, perpétuant la branche vers une autre Node.
- La fin se déclenche au moment où une node réponse "feuille" est atteinte, sans aucune question par la suite.

## Choix d'architecture :

Architecture en plusieurs services Docker, orchestrés via compose.yml :
- Caddy : faisant acte de reverse proxy, exposant le projet en HTTP/HTTPS via les routes /api pour Symfony, et le reste pour Next.js
- Backend : Symfony (API REST)
- Frontend : Next.js (Interface Web)
- Base de données : PostgreSQL

Architecture backend en monolithe Symfony exposant une API REST :
- Entités Doctrines, Migrations pour versionner le schém, Fixtures pour préremplir les tests
- Controlleurs API : Endpoints admin et utilisateur

Modélisation de l'arbre :
- Un questionnaire possède plusieurs questions, qui elles mêmes possèdent plusieurs choix.
- Ces choix peuvent pointer vers la question suivante, ou rien terminant le questionnaire
- Une AnswerSession garde l'état du parcours en gérant la réponse en cours et les réponses données.

Sécurité :
- Authentification avec autorisation via JWT
- Enpoints protégés par ROLE.

Architecture Frontend :
- Next.js utilisant les réponses de l'API
- Scindage entre la partie répondante et la partie admin


## Migrations & Fixtures :
- Utilisation de Doctrine pour générer les Entités ainsi que les migrations via le dossier migrations/
- Commande Makefile : make reset-db, permettant la migrations et la mise en place des fixtures afin de repeupler la DB.

## Tests unitaires, tests d'intégrations :
- Compilation des deux types dans leur controller respectifs.
- Gérant à la fois la couche Repository et la couche HTTP/JWT.
- Commande Makefile : make test-api, permettant une cinquantaine de test, s'assurant l'intégrité du backend.
