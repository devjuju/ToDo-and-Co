# 📋 ToDo & Co – Application modernisée

## 📌 Contexte

Ce dépôt contient la modernisation de l'application **ToDo & Co**, réalisée dans le cadre du projet de spécialisation de la formation **Développeur d'application PHP/Symfony chez OpenClassrooms**.

L'objectif du projet est d'améliorer progressivement l'application originale afin de :

- migrer Symfony vers des versions LTS successives jusqu'à **Symfony 7.4** ;
- moderniser l'architecture de l'application ;
- corriger les anomalies fonctionnelles ;
- renforcer la sécurité et la gestion des autorisations ;
- ajouter des tests automatisés ;
- améliorer la qualité du code ;
- produire une documentation technique destinée aux futurs développeurs ;
- réaliser des audits de qualité du code et de performance avant et après les modifications.

Le dépôt d'origine est conservé séparément afin de servir de référence et de permettre la comparaison entre l'état initial et l'état modernisé de l'application.

---

## ✨ Fonctionnalités

### 📝 Gestion des tâches

L'application permet de :

- créer une tâche ;
- modifier une tâche ;
- marquer une tâche comme faite ou non faite ;
- supprimer une tâche selon les règles d'autorisation ;
- associer automatiquement une nouvelle tâche à l'utilisateur authentifié.

Lors de la modification d'une tâche, son auteur ne peut pas être modifié.

Les tâches historiques qui ne possédaient pas d'auteur sont rattachées à un utilisateur technique `anonymous`.

### 👤 Gestion des utilisateurs

L'application permet de :

- créer un compte utilisateur ;
- modifier un utilisateur ;
- attribuer un rôle lors de la création d'un utilisateur ;
- modifier le rôle d'un utilisateur existant.

Deux rôles sont disponibles :

- `ROLE_USER` : utilisateur standard ;
- `ROLE_ADMIN` : administrateur.

### 🔐 Sécurité

L'application utilise le composant **Security de Symfony** afin de :

- authentifier les utilisateurs ;
- protéger les pages nécessitant une authentification ;
- réserver la gestion des utilisateurs aux administrateurs ;
- rattacher automatiquement une nouvelle tâche à son auteur ;
- empêcher la modification de l'auteur d'une tâche ;
- autoriser uniquement le propriétaire d'une tâche à la supprimer ;
- réserver la suppression des tâches `anonymous` aux administrateurs.

Les règles d'autorisation liées aux tâches sont centralisées dans un **Voter Symfony**.

### 🧪 Qualité

Le projet comprend :

- des tests unitaires ;
- des tests fonctionnels ;
- un rapport de couverture de code ;
- une documentation technique ;
- une documentation de contribution ;
- un audit de qualité du code ;
- un audit de performance.

---

## 🛠️ Stack technique

| Technologie          | Utilisation                    |
| -------------------- | ------------------------------ |
| Symfony 7.4          | Framework PHP                  |
| PHP 8.2+             | Langage serveur                |
| Apache               | Serveur web                    |
| MySQL                | Base de données                |
| Doctrine ORM         | Persistance des données        |
| Twig                 | Moteur de templates            |
| PHPUnit              | Tests automatisés              |
| Docker               | Environnement de développement |
| Codacy               | Analyse de la qualité du code  |
| Symfony Web Profiler | Analyse des performances       |

---

## 🚀 Installation

### Prérequis

Le projet nécessite notamment :

- Docker ;
- Docker Compose ;
- Git.

### Installation

Cloner le dépôt :

```bash
git clone https://github.com/devjuju/ToDo-and-Co.git
cd ToDo-and-Co
```

Démarrer les conteneurs :

```bash
docker compose up -d --build
```

Installer les dépendances PHP :

```bash
docker compose exec php composer install --no-scripts
```

Créer ou mettre à jour le schéma de base de données :

```bash
docker compose exec php php bin/console doctrine:migrations:migrate
```

Pour charger les données de démonstration et de test :

```bash
docker compose exec php php bin/console doctrine:fixtures:load
```

> ⚠️ La commande `doctrine:fixtures:load` réinitialise les données de la base utilisée par les fixtures. Elle doit donc être utilisée avec précaution.

---

## 🛠️ Commandes Symfony utiles

Les commandes Symfony sont exécutées depuis le conteneur PHP avec `php bin/console`.

### 🏗️ Génération de code

Créer ou modifier une entité :

```bash
docker compose exec php php bin/console make:entity
```

Créer une migration :

```bash
docker compose exec php php bin/console make:migration
```

Créer un contrôleur :

```bash
docker compose exec php php bin/console make:controller
```

Créer un formulaire :

```bash
docker compose exec php php bin/console make:form
```

Créer un Voter :

```bash
docker compose exec php php bin/console make:voter
```

Créer un test unitaire :

```bash
docker compose exec php php bin/console make:unit-test
```

Créer un test fonctionnel :

```bash
docker compose exec php php bin/console make:functional-test
```

Créer une fixture :

```bash
docker compose exec php php bin/console make:fixtures
```

### 🗄️ Base de données

Appliquer les migrations :

```bash
docker compose exec php php bin/console doctrine:migrations:migrate
```

Charger les fixtures :

```bash
docker compose exec php php bin/console doctrine:fixtures:load
```

Vérifier le mapping et le schéma Doctrine :

```bash
docker compose exec php php bin/console doctrine:schema:validate
```

### 🧹 Cache

Vider le cache :

```bash
docker compose exec php php bin/console cache:clear
```

### 🔎 Commandes de diagnostic

Afficher les commandes disponibles :

```bash
docker compose exec php php bin/console list
```

Afficher les commandes de génération :

```bash
docker compose exec php php bin/console list make
```

> Les commandes make:\* sont fournies par Symfony MakerBundle.
> Elles doivent être exécutées dans le conteneur PHP du projet.

---

## 🌐 Accès à l'application

### Application

```text
http://localhost:8267
```

### phpMyAdmin

```text
http://localhost:8268
```

### Comptes de démonstration

Les fixtures fournissent notamment les comptes suivants :

| Utilisateur | Rôle         | Mot de passe   |
| ----------- | ------------ | -------------- |
| `test`      | `ROLE_USER`  | `test123`      |
| `member`    | `ROLE_USER`  | `member123`    |
| `admin`     | `ROLE_ADMIN` | `admin123`     |
| `anonymous` | `ROLE_USER`  | `anonymous123` |

> Ces identifiants sont uniquement destinés à l'environnement de développement et de démonstration.

---

## 🧪 Tests

Les tests automatisés sont réalisés avec PHPUnit.

### ▶️ Exécuter les tests

Exécuter l'ensemble des tests :

```bash
docker compose exec php vendor/bin/phpunit
```

Cette commande exécute les tests unitaires et fonctionnels du projet.

### 📊 Vérifier la couverture de code

Pour afficher le rapport de couverture directement dans le terminal :

```bash
docker compose exec -e XDEBUG_MODE=coverage php \
    vendor/bin/phpunit --coverage-text
```

Pour générer le rapport de couverture HTML avec Xdebug :

```bash
docker compose exec -e XDEBUG_MODE=coverage php \
    vendor/bin/phpunit --coverage-html coverage
```

Le rapport HTML généré est disponible dans le répertoire :

```text
coverage/
```

Il peut être consulté en ouvrant :

```text
coverage/index.html
```

### 🧹 Nettoyer le rapport de couverture

Pour supprimer un ancien rapport avant d'en générer un nouveau :

```bash
rm -rf coverage/
```

Puis relancer :

```bash
docker compose exec -e XDEBUG_MODE=coverage php \
    vendor/bin/phpunit --coverage-html coverage
```

### 🎯 Couverture obtenue

La couverture de code doit être supérieure à **70 %**, conformément aux objectifs du projet.

La dernière exécution permet de vérifier notamment :

- les entités `Task` et `User` ;
- les règles d'autorisation du `TaskVoter` ;
- l'accès aux pages protégées ;
- la création des tâches ;
- la modification des tâches ;
- le changement d'état des tâches ;
- la suppression des tâches ;
- les règles de propriété ;
- la gestion des tâches `anonymous` ;
- la gestion des rôles utilisateur et administrateur.

### 🧪 Organisation des tests

Les tests unitaires utilisent `PHPUnit\Framework\TestCase` et vérifient principalement le comportement des entités et du `TaskVoter`.

Les tests fonctionnels utilisent `WebTestCase` afin de tester l'application à travers des requêtes HTTP, l'authentification, les formulaires, les autorisations et la persistance en base de données.

---

## 📚 Documentation

La documentation complète du projet est disponible dans le dossier `docs/`.

### 🐳 Installation et environnement

- configuration Docker ;
- installation de Composer ;
- configuration Symfony ;
- vérification fonctionnelle de l'application originale.

### 🚀 Migration du framework

- migration Symfony 3.1 → 3.4 LTS ;
- migration Symfony 3.4 → 4.4 LTS ;
- migration Symfony 4.4 → 5.4 LTS ;
- migration Symfony 5.4 → 6.4 LTS ;
- migration Symfony 6.4 → 7.4 LTS.

### 🏗️ Modernisation de l'architecture

- migration de Symfony Standard Edition vers une structure moderne ;
- suppression progressive de `AppBundle` ;
- migration vers Symfony Flex ;
- organisation `config/`, `src/`, `templates/` et `public/` ;
- migration des annotations vers les attributs PHP.

### 🔐 Sécurité et évolutions fonctionnelles

- association automatique des tâches aux utilisateurs ;
- gestion des rôles ;
- authentification Symfony ;
- sécurisation des accès ;
- règles d'autorisation avec les Voters.

### 🧪 Qualité et tests

- tests unitaires et fonctionnels ;
- rapport de couverture ;
- documentation technique de l'authentification ;
- règles de contribution et de collaboration.

### 📊 Audits

- audit de qualité du code ;
- audit de performance ;
- comparaison des résultats avant et après modernisation.

---

## 📊 Audits de qualité et de performance

Deux axes ont été étudiés :

| Audit           | Outil                |
| --------------- | -------------------- |
| Qualité du code | Codacy               |
| Performance     | Symfony Web Profiler |

Les audits ont été réalisés sur l'application originale puis après les principales modifications afin de disposer d'un état **avant / après**.

### 🔎 Audit de qualité du code

L'analyse de la qualité du code a été réalisée avec **Codacy**.

Les rapports sont disponibles dans :

```text
docs/audit/
```

Ils présentent notamment :

- les problèmes détectés ;
- leur niveau de criticité ;
- les principaux axes de dette technique ;
- l'évolution de la qualité après les modifications.

### ⚡ Audit de performance

L'analyse des performances a été réalisée avec le **Symfony Web Profiler**.

Elle permet notamment d'observer :

- le temps d'exécution des requêtes ;
- le nombre de requêtes SQL ;
- la consommation mémoire ;
- les performances des contrôleurs ;
- les éventuels points coûteux de l'application.

Les résultats sont documentés dans :

```text
docs/audit/
```

---

## 📁 Structure de la documentation

```text
docs/
├── setup/
│   ├── 01-docker.md
│   ├── 02-composer.md
│   ├── 03-symfony-configuration.md
│   └── 04-original-functional-verification.md
│
├── modernization/
│   ├── 01-docker-update.md
│   ├── 02-symfony-3.4.md
│   ├── 03-symfony-4.4.md
│   ├── 04-symfony-5.4.md
│   ├── 05-symfony-6.4.md
│   └── 06-symfony-7.4.md
│
├── improvements/
│   ├── authentication.md
│   └── ...
│
├── audit/
│   ├── initial/
│   └── final/
│
└── README.md
```

La documentation technique doit être consultée avant toute modification importante du projet.

---

## 🤝 Contribution et qualité

Tout développeur souhaitant intervenir sur le projet doit respecter le processus défini dans la documentation de contribution.

Celui-ci précise notamment :

- l'organisation des branches ;
- les conventions de nommage ;
- les règles de commit ;
- le processus de développement ;
- la réalisation des tests ;
- les contrôles de qualité ;
- les vérifications avant une fusion ;
- le processus de revue du code.

La documentation correspondante est disponible dans le dossier `docs/`.

Avant toute modification, il est recommandé de prendre connaissance de cette documentation ainsi que de la documentation d'authentification.

---

## 🔙 Dépôt de référence

L'application originale utilisée comme base de comparaison est disponible dans le dépôt :

https://github.com/devjuju/projet8-TodoList

Ce dépôt contient la version initiale de l'application et constitue l'état de référence utilisé pour mesurer les évolutions apportées à cette version modernisée.

---

## 👤 Auteur

Projet réalisé dans le cadre de la formation **Développeur d'application PHP/Symfony** chez **OpenClassrooms**.

Ce dépôt correspond à la **version modernisée et finalisée** de l'application ToDo & Co.
