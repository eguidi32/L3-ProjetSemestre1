# Brasil Burger - Projet L3 Semestre 1

Application Java Console pour la gestion des ressources du restaurant Brasil Burger (burgers, compléments, menus, zones de livraison) avec PostgreSQL et Cloudinary.

## Prérequis

- Java 17+
- Maven 3.6+
- PostgreSQL 17
- Git

## Architecture

```
src/main/java/com/bresil/
├── config/           # Configuration (DatabaseConfig, CloudinaryConfig)
│   ├── database/     # Connexion BDD et Cloudinary
│   └── factory/      # Factories pour Repository et Service
├── entity/           # Entités métier (Burger, Complement, Menu, Zone)
├── repository/       # Accès aux données (interfaces + implémentations)
├── service/          # Logique métier (interfaces + implémentations)
└── views/            # Interface utilisateur console
    └── components/   # Composants de vue (BurgerView, MenuView, etc.)
```

## Installation

1. Cloner le repository
```bash
git clone https://github.com/eguidi32/L3-ProjetSemestre1.git
cd bresil_burger_java
```

2. Configurer la base de données dans `src/main/resources/application.properties`

3. Compiler le projet
```bash
mvn clean compile
```

4. Créer le JAR exécutable
```bash
mvn clean package assembly:single
```

5. Exécuter l'application
```bash
java -jar target/bresil-burger-java-1.0.0-jar-with-dependencies.jar
```

## Fonctionnalités

- **Gestion des Burgers** : CRUD complet avec upload d'images sur Cloudinary
- **Gestion des Compléments** : Gestion des accompagnements (boissons, desserts, etc.)
- **Gestion des Menus** : Création de menus composés de burgers et compléments
- **Gestion des Zones de livraison** : Configuration des zones et frais de livraison

## Auteurs

Projet réalisé dans le cadre du cours de L3 - Semestre 1

## Licence

Projet académique