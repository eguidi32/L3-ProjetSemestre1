# Bresil Burger - Projet L3 Semestre 1

Application Java de gestion de restaurant utilisant MySQL et l'architecture en couches. 

##  Prérequis

- Java 11+
- Maven 3.6+
- MySQL 8.0+
- Git

##  Architecture

```
src/
└── com/bresilburger/
    ├── config/          # Configuration (BDD, Cloudinary)
    ├── entity/          # Entités métier
    ├── repository/      # Accès aux données (DAO)
    ├── service/         # Logique métier
    └── view/            # Interface utilisateur (Swing)
```

##  Installation

1. Cloner le repository
```bash
git clone <votre-repo>
cd bresil-burger
```

2. Configuration base de données (à venir)

3. Compiler le projet
```bash
mvn clean compile
```

##  Auteurs

Projet réalisé dans le cadre du cours de L3 - Semestre 1

##  Licence

Projet académique