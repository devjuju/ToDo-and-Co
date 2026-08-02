# 📋 ToDo & Co – Application modernisée

## 📌 Contexte

Ce dépôt contient la modernisation de l'application **ToDo & Co** réalisée dans le cadre du projet OpenClassrooms.

L'objectif est d'améliorer progressivement l'application originale en :

- migrant Symfony vers des versions LTS successives jusqu'à Symfony 7.4 ;
- corrigeant les anomalies fonctionnelles ;
- renforçant la sécurité ;
- ajoutant des tests automatisés ;
- améliorant la qualité du code ;
- produisant une documentation technique complète ;
- réalisant des audits de qualité et de performance avant et après les améliorations.

Le dépôt d'origine est conservé séparément afin de servir de référence et de permettre la comparaison entre l'état initial et l'état modernisé de l'application.

---

## ✨ Fonctionnalités

L'application permet de :

### 📝 Gestion des tâches

- créer une tâche ;
- modifier une tâche ;
- marquer une tâche comme faite ou non faite ;
- supprimer une tâche selon les règles d'autorisation.

### 👤 Gestion des utilisateurs

- créer un compte utilisateur ;
- modifier un utilisateur ;
- attribuer un rôle (`ROLE_USER` ou `ROLE_ADMIN`) lors de la création ou de la modification d'un utilisateur.

### 🔐 Sécurité

- s'authentifier via le système de sécurité Symfony ;
- rattacher automatiquement une nouvelle tâche à son auteur ;
- empêcher la modification de l'auteur d'une tâche ;
- limiter l'accès à la gestion des utilisateurs aux administrateurs ;
- autoriser uniquement le propriétaire d'une tâche à la supprimer ;
- réserver la suppression des tâches anonymes aux administrateurs.

### 🧪 Qualité

- couverture par des tests unitaires et fonctionnels ;
- documentation technique ;
- audit de qualité du code ;
- audit de performance.

---

## 🛠️ Stack technique

| Technologie  | Description                            |
| ------------ | -------------------------------------- |
| Symfony      | Migration progressive vers Symfony 7.4 |
| PHP          | Version compatible avec Symfony        |
| Apache       | Serveur web                            |
| MySQL        | Base de données                        |
| Doctrine ORM | Persistance des données                |
| Twig         | Moteur de templates                    |
| PHPUnit      | Tests unitaires et fonctionnels        |
| Docker       | Environnement de développement         |
| Codacy       | Analyse de la qualité du code          |

---

## Installation

```bash
git clone https://github.com/devjuju/ToDo-and-Co.git

cd ToDo-and-Co

docker compose up -d --build

docker compose exec php composer install --no-scripts

docker compose exec php php bin/console doctrine:schema:update --force
```

---

## Accès

- Application :

```text
http://localhost:8267
```

- phpMyAdmin :

```text
http://localhost:8268
```

## 📚 Documentation

La documentation du projet est disponible dans le dossier `docs/`.

Elle couvre l'ensemble des étapes de modernisation de l'application :

### 🐳 Modernisation de l'environnement

- 🐳 Mise à jour de l'environnement Docker
- 🛢️ Configuration de la base de données sous Symfony 3.4

### 🚀 Migration du framework

- 🚀 Migration Symfony 3.1 → 3.4 LTS
- 🚀 Migration Symfony 3.4 → 4.4 LTS
- 🚀 Migration Symfony 4.4 → 5.4 LTS
- 🚀 Migration Symfony 5.4 → 6.4 LTS
- 🚀 Migration Symfony 6.4 → 7.4

### ✅ Vérifications fonctionnelles

- ✅ Validation fonctionnelle sous Symfony 6.4

### 🏗️ Modernisation de l'architecture

- 🏗️ Migration de l'architecture Symfony Standard Edition vers une structure Symfony moderne
- 📦 Remplacement progressif de `AppBundle`
- 🗂️ Migration vers la structure Symfony Flex (`config/`, `src/`, `templates/`, `public/`)
- 🔄 Migration des annotations vers les attributs PHP

### 🛠️ Évolutions fonctionnelles

- 🔐 Authentification Symfony
- 🔗 Association des tâches à un utilisateur
- 👥 Gestion des rôles
- 🔑 Sécurisation des accès

### 🧪 Qualité

- 🧪 Implémentation des tests automatisés
- 📘 Documentation technique
- 🏅 Audit final de qualité du code
- 📈 Audit final de performance

## Audits réalisés

Ces documents constituent l'état de référence de l'application avant sa modernisation et servent de base de comparaison avec la version améliorée.

| Audit           | Outil                |
| --------------- | -------------------- |
| Qualité du code | Codacy               |
| Performance     | Symfony Web Profiler |

---

## Qualité du code

Une analyse initiale de la qualité du code a été réalisée avec Codacy avant toute modification de l'application.

Le rapport détaillé est disponible dans :

`docs/audit/initial/quality-audit.md`

---

## Structure

```bash
docs/
├── setup/
│   ├── 01-docker.md
│   ├── 02-composer.md
│   ├── 03-symfony-configuration.md
│   └── 04-original-functional-verification.md
├── modernization/
│   ├── 01-docker-update.md
│   ├── 02-symfony-3.4.md
│   ├── 03-symfony-4.4.md
│   ├── 04-symfony-5.4.md
│   ├── 05-symfony-6.4.md
│   └── 06-symfony-7.4.md
├── improvements/
├── audit/
└── README.md
```

---

## 🔙 Dépôt de référence

L'application d'origine, utilisée comme base de comparaison, est disponible dans le dépôt :

👉 https://github.com/devjuju/projet8-TodoList

Ce dépôt contient :

- la version originale de l'application ;
- sa conteneurisation avec Docker ;
- la vérification fonctionnelle ;
- l'audit initial de qualité du code ;
- l'audit initial de performance.

Il constitue l'état de référence (« avant ») utilisé pour mesurer les améliorations apportées dans ce dépôt.

---

## Auteur

Projet réalisé dans le cadre de la formation **Développeur d'application PHP/Symfony** chez OpenClassrooms.

Ce dépôt correspond à l'état initial de l'application et sert de référence pour la comparaison avec sa version modernisée.
