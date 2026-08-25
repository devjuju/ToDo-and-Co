# ➕ Diagramme de séquence — Création d'une tâche

## 🎯 Objectif

Ce diagramme représente le parcours de création d'une tâche dans ToDo & Co.

Lors de la création, l'utilisateur authentifié est automatiquement associé à la nouvelle tâche.

L'utilisateur ne choisit donc pas lui-même le propriétaire de la tâche.

---

## 📊 Diagramme

```mermaid
sequenceDiagram

    actor Utilisateur
    participant Navigateur
    participant Controller as TaskController
    participant Formulaire as TaskType
    participant Doctrine as EntityManager
    participant Base as Base de données

    Utilisateur->>Navigateur: Ouvrir la page de création d'une tâche

    Navigateur->>Controller: GET /tasks/create

    Controller-->>Navigateur: Afficher le formulaire de création

    Utilisateur->>Navigateur: Soumettre la tâche

    Navigateur->>Controller: POST /tasks/create

    Controller->>Formulaire: Soumettre les données du formulaire

    Formulaire-->>Controller: Données Task valides

    Controller->>Controller: Récupérer l'utilisateur authentifié

    Controller->>Controller: Associer l'utilisateur à la tâche

    Controller->>Doctrine: Persister Task

    Doctrine->>Base: INSERT task

    Base-->>Doctrine: Opération réussie

    Doctrine-->>Controller: Tâche enregistrée

    Controller-->>Navigateur: Rediriger vers /tasks

    Navigateur-->>Utilisateur: Afficher la liste des tâches
```

---

## 👤 Association automatique de l'utilisateur

Après validation du formulaire, `TaskController` récupère l'utilisateur authentifié et l'associe à la nouvelle tâche.

Le principe est :

```text
Utilisateur authentifié
        ↓
Création de Task
        ↓
Task.user = utilisateur authentifié
        ↓
Persistance Doctrine
        ↓
INSERT en base de données
```

Cette association garantit que chaque nouvelle tâche possède obligatoirement un utilisateur.

La colonne `user_id` est définie comme `NOT NULL` dans la base de données.

---

## 🧩 Composants concernés

```text
src/
├── Controller/
│   └── TaskController.php
│
├── Entity/
│   └── Task.php
│
└── Form/
    └── TaskType.php
```

Doctrine assure ensuite la persistance de l'entité dans la base de données.
