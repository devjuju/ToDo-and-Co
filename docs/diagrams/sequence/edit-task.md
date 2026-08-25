# 🔄 Diagramme de séquence — Modification d'une tâche

## 🎯 Objectif

Ce diagramme représente le parcours de modification d'une tâche.

Avant d'autoriser la modification, `TaskVoter` vérifie que l'utilisateur authentifié est bien le propriétaire de la tâche.

---

## 📊 Diagramme

```mermaid
sequenceDiagram

    actor Utilisateur
    participant Navigateur
    participant Controller as TaskController
    participant Voter as TaskVoter
    participant Doctrine as EntityManager
    participant Base as Base de données

    Utilisateur->>Navigateur: Modifier une tâche

    Navigateur->>Controller: GET /tasks/{id}/edit

    Controller->>Voter: Vérifier le droit EDIT

    Voter->>Voter: Vérifier le propriétaire de la tâche

    alt Utilisateur propriétaire

        Voter-->>Controller: Accès autorisé

        Controller-->>Navigateur: Afficher le formulaire de modification

        Utilisateur->>Navigateur: Soumettre les modifications

        Navigateur->>Controller: POST /tasks/{id}/edit

        Controller->>Doctrine: Mettre à jour Task

        Doctrine->>Base: UPDATE task

        Base-->>Doctrine: Opération réussie

        Controller-->>Navigateur: Rediriger vers /tasks

    else Utilisateur non propriétaire

        Voter-->>Controller: Accès refusé

        Controller-->>Navigateur: 403 Forbidden

    end
```

---

## 🔐 Règle d'autorisation

La modification d'une tâche est autorisée uniquement si l'utilisateur authentifié est le propriétaire de la tâche.

`TaskVoter` effectue cette vérification avant que le contrôleur ne permette la modification.

L'auteur de la tâche n'est pas modifiable depuis le formulaire d'édition.

En cas de tentative d'accès à une tâche appartenant à un autre utilisateur, l'application retourne :

```text
403 Forbidden
```
