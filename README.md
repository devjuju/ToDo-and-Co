# 📋 ToDo & Co – Application originale

[![Codacy Badge]](https://app.codacy.com/gh/devjuju/projet8-TodoList/dashboard)

## 📌 Contexte

Ce dépôt correspond à la version originale de l'application **ToDo & Co** fournie dans le cadre du projet OpenClassrooms.

Son objectif est de conserver un état de référence de l'application avant toute évolution afin de :

- remettre l'application en fonctionnement dans un environnement moderne grâce à Docker ;
- vérifier son fonctionnement initial ;
- réaliser un audit de qualité du code ;
- réaliser un audit de performance.

Les améliorations fonctionnelles, la migration vers Symfony 3.4 LTS, les corrections de sécurité, les tests automatisés ainsi que la documentation finale sont disponibles dans un dépôt distinct consacré à la modernisation du projet.

---

> **Note :** Ce dépôt n'a pas vocation à évoluer fonctionnellement. Il documente l'état initial de l'application avant les travaux de modernisation réalisés dans un dépôt distinct.

---

## Fonctionnalités

L'application permet de :

- créer un compte utilisateur ;
- se connecter ;
- créer des tâches ;
- modifier une tâche ;
- marquer une tâche comme faite ou non faite ;
- supprimer une tâche.

Cette version correspond au projet d'origine et ne contient pas les améliorations demandées par le cahier des charges.

---

## Stack technique

| Technologie  | Version |
| ------------ | ------- |
| Symfony      | 3.1.10  |
| PHP          | 7.4     |
| Apache       | 2.4     |
| MySQL        | 5.7     |
| Doctrine ORM | 2.6     |
| Twig         | 1.44    |
| Docker       | ✔       |

---

## Installation

```bash
git clone https://github.com/devjuju/projet8-TodoList.git

cd projet8-TodoList

docker compose up -d --build

docker compose exec php composer install --no-scripts

docker compose exec php php bin/console doctrine:schema:update --force
```

---

## Accès

- Application :

```text
http://localhost:8167
```

- phpMyAdmin :

```text
http://localhost:8168
```

---

## Documentation

La documentation du projet est disponible dans le dossier `docs/`.

Elle comprend notamment :

- mise en place de Docker ;
- installation des dépendances ;
- configuration Symfony ;
- vérification fonctionnelle ;
- audit initial de qualité du code ;
- audit initial de performance.

---

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
├── audit/
│   ├── initial/
│   └── screenshots/
└── README.md
```

---

## Dépôt de modernisation

Ce dépôt représente uniquement l'état initial de l'application et sert de référence pour les audits de qualité et de performance.

La modernisation de l'application est réalisée dans un dépôt distinct :

👉 https://github.com/devjuju/ToDo-and-Co

Ce second dépôt documente l'ensemble des évolutions apportées au projet, notamment :

- la migration progressive de Symfony 3.1 vers Symfony 7.4 (3.4 LTS → 4.4 LTS → 5.4 LTS → 6.4 LTS → 7.4) ;
- les corrections fonctionnelles demandées par le cahier des charges ;
- le renforcement de la sécurité et des contrôles d'accès ;
- l'implémentation des tests automatisés ;
- la production de la documentation technique ;
- les audits finaux de qualité du code et de performance.

Il constitue la version modernisée de l'application et permet de comparer l'état initial du projet avec son état final.

---

## Auteur

Projet réalisé dans le cadre de la formation **Développeur d'application PHP/Symfony** chez OpenClassrooms.

Ce dépôt correspond à l'état initial de l'application et sert de référence pour la comparaison avec sa version modernisée.
