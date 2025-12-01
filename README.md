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

### test
