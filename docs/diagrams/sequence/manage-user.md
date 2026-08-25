# 🔄 Diagramme de séquence — Gestion des utilisateurs

## 🎯 Objectif

Ce diagramme représente le parcours de gestion des utilisateurs dans ToDo & Co.

L'accès à la gestion des utilisateurs est réservé aux utilisateurs possédant le rôle `ROLE_ADMIN`.

Le diagramme présente notamment :

- la vérification du rôle administrateur ;
- l'accès à la page de gestion des utilisateurs ;
- la création ou la modification d'un utilisateur ;
- la validation du formulaire ;
- l'enregistrement des données en base de données.

---

## 📊 Diagramme

```mermaid
sequenceDiagram

    actor Utilisateur
    participant Navigateur
    participant Sécurité as Symfony Security
    participant Controller as UserController
    participant Formulaire as UserType
    participant Doctrine as EntityManager
    participant Base as Base de données

    Utilisateur->>Navigateur: Accéder à la gestion des utilisateurs

    Navigateur->>Sécurité: Requête /users

    Sécurité->>Sécurité: Vérifier ROLE_ADMIN

    alt Utilisateur administrateur

        Sécurité-->>Controller: Accès autorisé

        Controller-->>Navigateur: Afficher la gestion des utilisateurs

        Utilisateur->>Navigateur: Créer ou modifier un utilisateur

        Navigateur->>Controller: Soumettre le formulaire utilisateur

        Controller->>Formulaire: Valider les données

        Formulaire-->>Controller: Données valides

        Controller->>Doctrine: Enregistrer User

        Doctrine->>Base: INSERT / UPDATE user

        Base-->>Doctrine: Opération réussie

        Controller-->>Navigateur: Rediriger vers la liste des utilisateurs

    else Utilisateur non administrateur

        Sécurité-->>Navigateur: 403 Forbidden

    end
```

---

## 🔐 Règle d'autorisation

L'accès à la gestion des utilisateurs est protégé par le rôle :

```text
ROLE_ADMIN
```

Un utilisateur ne possédant pas ce rôle reçoit une réponse :

```text
403 Forbidden
```

L'autorisation est donc vérifiée avant l'accès aux fonctionnalités de gestion des utilisateurs.
