# 🔐 Diagramme de séquence — Authentification

## 🎯 Objectif

Ce diagramme représente le parcours d'authentification d'un utilisateur dans ToDo & Co.

Il présente les principales étapes permettant à Symfony Security d'identifier l'utilisateur et de vérifier son mot de passe.

---

## 📊 Diagramme

```mermaid
sequenceDiagram

    actor Utilisateur
    participant Navigateur
    participant Sécurité as Symfony Security
    participant Fournisseur as User Provider
    participant Base as Base de données

    Utilisateur->>Navigateur: Soumettre le formulaire de connexion

    Navigateur->>Sécurité: POST /login

    Sécurité->>Fournisseur: Charger l'utilisateur par username

    Fournisseur->>Base: SELECT user

    Base-->>Fournisseur: Utilisateur

    Fournisseur-->>Sécurité: Utilisateur

    Sécurité->>Sécurité: Vérifier le mot de passe

    alt Identifiants valides

        Sécurité-->>Navigateur: Session authentifiée

        Navigateur-->>Utilisateur: Rediriger vers l'application

    else Identifiants invalides

        Sécurité-->>Navigateur: Échec de l'authentification

        Navigateur-->>Utilisateur: Afficher l'erreur de connexion

    end
```

---

## 🔐 Fonctionnement

L'utilisateur saisit ses identifiants dans le formulaire de connexion.

Symfony Security :

1. reçoit la requête `POST /login` ;
2. recherche l'utilisateur à partir de son `username` ;
3. récupère les informations de l'utilisateur depuis la base de données ;
4. vérifie le mot de passe ;
5. crée la session authentifiée si les identifiants sont valides.

En cas d'échec, l'utilisateur reste non authentifié et une erreur de connexion est affichée.

---

## 🧩 Composants concernés

Les principaux composants impliqués sont :

```text
src/
├── Controller/
│   └── SecurityController.php
│
└── Entity/
    └── User.php
```

La gestion du mécanisme d'authentification est complétée par la configuration Symfony Security.
