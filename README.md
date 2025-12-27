# Brasil Burger - Application de Gestion de Restaurant

Application Symfony pour la gestion d'un restaurant de burgers brésilien.

## 📋 Prérequis

- PHP 8.5.1 ou supérieur
- PostgreSQL
- Composer
- Symfony CLI

## 🚀 Installation

1. Cloner le repository
```bash
git clone <url-du-repo>
cd bresil_burger_symfony
```

2. Installer les dépendances
```bash
composer install
```

3. Configurer l'environnement
```bash
cp .env.example .env
# Éditer .env avec vos paramètres de base de données
```

4. Créer la base de données et exécuter les migrations
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

5. (Optionnel) Charger les données de test
```bash
php bin/console doctrine:fixtures:load
```

## 🔧 Configuration

### Base de données PostgreSQL (Neon.tech)
Dans le fichier `.env`, configurez :
```
DATABASE_URL="postgresql://user:password@host:5432/database?sslmode=require"
```

## 🎨 Thème

- **Couleurs principales** : Noir (#000000) et Jaune (#FFD700)
- **Background** : Beige (#FFF2C7)

## Connexion par défaut

- **Email** : admin@brasilburger.com
- **Mot de passe** : admin123

## 🛠️ Commandes utiles

### Démarrer le serveur de développement
```bash
symfony server:start --port=8000
```

### Réinitialiser un mot de passe
```bash
php bin/console app:reset-password <email> <nouveau-mot-de-passe>
```

### Vider le cache
```bash
php bin/console cache:clear
```

## 📁 Structure du projet

- `src/Controller/` - Contrôleurs Symfony
- `src/Entity/` - Entités Doctrine
- `src/Repository/` - Repositories Doctrine
- `templates/` - Templates Twig
- `public/` - Fichiers publics (CSS, JS, images)

## 🔐 Sécurité

- CSRF activé sur tous les formulaires
- Passwords hashés avec Bcrypt
- Protection des routes par rôles (ROLE_GESTIONNAIRE)

## 📝 Fonctionnalités

- Dashboard gestionnaire avec KPIs
- Gestion des commandes
- Gestion des livraisons
- Statistiques
- Authentification sécurisée

## 🐛 Développement

Le projet utilise :
- Symfony 7.x
- Doctrine ORM
- Twig
- Bootstrap 5
- Bootstrap Icons

## 📞 Support

Pour toute question, veuillez contacter l'équipe de développement.
