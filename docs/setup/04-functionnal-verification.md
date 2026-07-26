# 04 ✅ Vérification fonctionnelle de l'application originale

## Objectif

Cette étape consiste à remettre en fonctionnement l'application originale ToDo & Co afin de disposer d'une base stable pour réaliser les audits de qualité et de performance.

L'objectif est de vérifier que l'application historique fonctionne correctement dans un environnement moderneisé avec Docker, tout en conservant les versions de dépendances compatibles avec Symfony 3.1.

---

# 🐳 Mise en place de l'environnement Docker

## Contexte

L'application originale était initialement développée pour un environnement ancien qui n'est plus disponible sur les machines actuelles.

Afin de faciliter son exécution et garantir un environnement reproductible, l'application a été intégrée dans un environnement Docker.

## Configuration mise en place

L'environnement Docker contient :

- un conteneur PHP avec Apache ;
- un serveur MySQL ;
- un outil d'administration de base de données (phpMyAdmin).

La configuration permet d'exécuter l'application avec :

- PHP 7.4 ;
- MySQL 5.7 ;
- Apache.

## Adaptations réalisées

Les principaux ajustements effectués :

- création du `Dockerfile` PHP ;
- configuration du service MySQL ;
- adaptation de l'hôte de connexion à la base de données ;
- ajout de la compatibilité `linux/amd64` pour MySQL sur architecture Apple Silicon ;
- exposition des ports nécessaires.

## Validation

L'application est accessible depuis :

```
http://localhost:8167
```

La page d'accueil ainsi que la page de connexion sont fonctionnelles.

---

# 📦 Installation des dépendances

## Problème rencontré

Les dépendances présentes dans le projet original n'étaient plus compatibles avec les versions actuelles de Composer.

L'installation initiale provoquait des erreurs liées à des versions trop récentes de certaines librairies, notamment :

- Doctrine DBAL ;
- Doctrine ORM ;
- DoctrineBundle ;
- Twig.

Ces versions n'étaient pas compatibles avec Symfony 3.1.

## Solution appliquée

Les dépendances ont été réalignées sur des versions compatibles avec Symfony 3.1 :

```json
"symfony/symfony": "3.1.*",
"doctrine/orm": "2.6.6",
"doctrine/doctrine-bundle": "1.8.1",
"doctrine/dbal": "2.6.3",
"twig/twig": "1.44.8"
```

Le fichier `composer.lock` a été régénéré afin d'obtenir un ensemble cohérent de dépendances.

Installation effectuée avec :

```bash
composer install --no-scripts
```

Puis mise à jour du verrouillage :

```bash
composer update --no-scripts
```

## Validation

Les dépendances installées sont :

| Package        | Version |
| -------------- | ------- |
| Symfony        | 3.1.10  |
| Doctrine ORM   | 2.6.6   |
| Doctrine DBAL  | 2.6.3   |
| DoctrineBundle | 1.8.1   |
| Twig           | 1.44.8  |

---

# ⚙️ Configuration Symfony

## Configuration de la base de données

Le fichier :

```
app/config/parameters.yml
```

a été adapté afin d'utiliser le service Docker MySQL.

Exemple :

```yaml
database_host: mysql
```

Le nom du service Docker est utilisé comme hôte afin de permettre la communication entre les conteneurs.

---

## Nettoyage du cache Symfony

Après modification des dépendances, les anciens caches Symfony ont été supprimés :

```bash
rm -rf app/cache/*
rm -rf var/cache/*
```

Puis reconstruits :

```bash
php bin/console cache:clear --env=dev
php bin/console cache:clear --env=prod --no-debug
```

---

## Vérification Doctrine

La cohérence entre les entités et la base de données a été contrôlée avec :

```bash
php bin/console doctrine:schema:validate
```

Résultat :

```
[OK] The mapping files are correct.

[OK] The database schema is in sync with the mapping files.
```

---

# Vérification fonctionnelle

Les pages principales ont été testées :

| Page            | Résultat         |
| --------------- | ---------------- |
| `/`             | ✅ Fonctionnelle |
| `/login`        | ✅ Fonctionnelle |
| `/tasks/create` | ✅ Fonctionnelle |

Le formulaire de création de tâche génère correctement les champs attendus :

- titre ;
- contenu ;
- token CSRF.

Le problème initial concernant le champ `content` bloqué a été résolu après restauration d'un thème de formulaire compatible Bootstrap.

---

# Résultat

L'application originale ToDo & Co est maintenant fonctionnelle dans un environnement Docker reproductible.

Cette branche constitue la référence avant modifications pour :

- l'audit qualité du code ;
- l'audit performance ;
- la comparaison avant/après amélioration.
