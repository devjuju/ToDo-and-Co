# 🔐 Authentification de l'application

## Introduction

L'application ToDo & Co utilise le composant **Security de Symfony** pour gérer l'authentification et les autorisations des utilisateurs.

L'objectif de cette documentation est de permettre à un nouveau développeur de comprendre :

- comment un utilisateur est authentifié ;
- où sont stockés les utilisateurs ;
- comment Symfony retrouve un utilisateur ;
- comment les mots de passe sont protégés ;
- comment les rôles sont gérés ;
- comment les accès aux différentes pages sont contrôlés ;
- quels fichiers interviennent dans le processus d'authentification.

L'authentification repose principalement sur :

```text
config/packages/security.yaml
src/Entity/User.php
src/Controller/SecurityController.php
templates/security/login.html.twig
```

---

# 🏗️ Vue d'ensemble

Le fonctionnement général de l'authentification est le suivant :

```text
Utilisateur
     │
     │ GET /login
     ▼
SecurityController
     │
     ▼
login.html.twig
     │
     │ username + password
     ▼
Symfony Security
     │
     ▼
Provider Doctrine
     │
     ▼
App\Entity\User
     │
     │ recherche par username
     ▼
Base de données
     │
     ▼
Vérification du mot de passe
     │
     ▼
Utilisateur authentifié
```

Une fois authentifié, Symfony conserve l'état de l'utilisateur dans la session et utilise ses rôles pour déterminer les fonctionnalités auxquelles il peut accéder.

---

# 📁 Fichiers concernés

Les principaux fichiers intervenant dans l'authentification sont :

```text
config/
└── packages/
    └── security.yaml

src/
├── Controller/
│   └── SecurityController.php
│
└── Entity/
    └── User.php

templates/
└── security/
    └── login.html.twig
```

Chaque fichier possède un rôle différent.

| Fichier                  | Rôle                                                                     |
| ------------------------ | ------------------------------------------------------------------------ |
| `security.yaml`          | Configuration de Symfony Security                                        |
| `User.php`               | Représentation d'un utilisateur et implémentation des interfaces Symfony |
| `SecurityController.php` | Affichage de la page de connexion                                        |
| `login.html.twig`        | Formulaire de connexion                                                  |

---

# ⚙️ Configuration de Symfony Security

La configuration se trouve dans :

```text
config/packages/security.yaml
```

Elle définit notamment :

- le système de hashage des mots de passe ;
- le fournisseur d'utilisateurs ;
- le firewall ;
- le formulaire de connexion ;
- la déconnexion ;
- les règles d'accès aux différentes routes.

---

# 🔑 Hashage des mots de passe

La configuration suivante définit le système utilisé pour protéger les mots de passe :

```yaml
password_hashers:
  App\Entity\User:
    algorithm: auto
```

Symfony détermine automatiquement l'algorithme de hashage approprié.

Lorsqu'un utilisateur est créé ou que son mot de passe est modifié, le mot de passe ne doit donc pas être enregistré directement en clair dans la base de données.

Dans `UserController`, le hashage est réalisé avec :

```php
UserPasswordHasherInterface
```

Le principe est :

```text
Mot de passe saisi
        ↓
UserPasswordHasherInterface
        ↓
Mot de passe hashé
        ↓
Base de données
```

Lors de l'authentification, Symfony compare ensuite le mot de passe fourni avec le hash enregistré.

---

# 👤 Fournisseur d'utilisateurs

La configuration utilise le provider Doctrine :

```yaml
providers:
  doctrine:
    entity:
      class: App\Entity\User
      property: username
```

Cela signifie que Symfony utilise l'entité :

```text
App\Entity\User
```

pour retrouver les utilisateurs.

La recherche est effectuée à partir de la propriété :

```text
username
```

Le parcours est donc :

```text
Username saisi
      ↓
Provider Doctrine
      ↓
App\Entity\User
      ↓
Recherche par username
      ↓
Utilisateur trouvé
```

Le `username` est donc l'identifiant utilisé lors de la connexion.

---

# 👤 Entité `User`

L'entité utilisateur se trouve dans :

```text
src/Entity/User.php
```

Elle implémente deux interfaces Symfony :

```php
UserInterface
PasswordAuthenticatedUserInterface
```

La déclaration est :

```php
class User implements UserInterface, PasswordAuthenticatedUserInterface
```

Ces interfaces permettent à l'entité d'être utilisée directement par le composant Security de Symfony.

---

# 🆔 Identifiant de l'utilisateur

Symfony utilise la méthode :

```php
getUserIdentifier()
```

pour obtenir l'identifiant de l'utilisateur.

Dans l'application :

```php
public function getUserIdentifier(): string
{
    return (string) $this->username;
}
```

L'identifiant retourné est donc le :

```text
username
```

Ainsi :

```text
username
    ↓
getUserIdentifier()
    ↓
identifiant Symfony
```

---

# 🗄️ Stockage des utilisateurs

Les utilisateurs sont stockés dans la base de données via l'entité :

```text
App\Entity\User
```

La table correspondante est :

```text
user
```

Elle est explicitement définie dans l'entité :

```php
#[ORM\Table(name: 'user')]
```

Les principales informations stockées sont :

```text
user
├── id
├── username
├── password
├── email
└── roles
```

Les rôles sont stockés dans une colonne JSON :

```php
#[ORM\Column(type: 'json')]
private $roles = [];
```

Un utilisateur peut donc avoir une valeur telle que :

```json
["ROLE_USER"]
```

ou :

```json
["ROLE_ADMIN"]
```

---

# 🔐 Mot de passe

L'entité `User` implémente :

```php
PasswordAuthenticatedUserInterface
```

et fournit la méthode :

```php
public function getPassword(): string
{
    return $this->password;
}
```

Symfony utilise cette méthode pour récupérer le hash du mot de passe enregistré.

Le mot de passe lui-même n'est pas stocké en clair.

---

# 🌐 Page de connexion

La page de connexion est gérée par :

```text
src/Controller/SecurityController.php
```

La route est :

```php
#[Route('/login', name: 'login')]
```

La méthode `login()` récupère les informations nécessaires auprès de :

```php
AuthenticationUtils
```

Elle transmet au template :

```php
[
    'last_username' => $authenticationUtils->getLastUsername(),
    'error' => $authenticationUtils->getLastAuthenticationError(),
]
```

Cela permet notamment d'afficher :

- le dernier nom d'utilisateur saisi ;
- le message d'erreur lorsqu'une authentification échoue.

---

# 🖥️ Formulaire de connexion

Le formulaire est défini dans :

```text
templates/security/login.html.twig
```

Il utilise :

```html
<form action="{{ path('login') }}" method="post"></form>
```

Les champs envoyés à Symfony sont :

```text
_username
_password
```

Le nom d'utilisateur est envoyé avec :

```html
<input type="text" id="username" name="_username" />
```

et le mot de passe avec :

```html
<input type="password" id="password" name="_password" />
```

Le formulaire contient également un token CSRF :

```html
<input
  type="hidden"
  name="_csrf_token"
  value="{{ csrf_token('authenticate') }}"
/>
```

Ce token correspond à la configuration :

```yaml
enable_csrf: true
csrf_token_id: authenticate
```

---

# 🔄 Fonctionnement du formulaire

Lorsqu'un utilisateur valide le formulaire :

```text
Utilisateur
    ↓
POST /login
    ↓
Symfony Security
    ↓
Provider Doctrine
    ↓
Recherche du username
    ↓
Utilisateur trouvé
    ↓
Vérification du mot de passe
    ↓
Authentification
```

Il est important de noter que `SecurityController` n'effectue pas lui-même la vérification du mot de passe.

Le contrôleur sert principalement à afficher le formulaire et à transmettre les éventuelles informations d'erreur.

La gestion de l'authentification est effectuée par le composant Security de Symfony.

---

# 🛡️ Firewall

Le firewall principal est configuré dans :

```text
config/packages/security.yaml
```

La configuration est :

```yaml
main:
  lazy: true
  pattern: ^/
```

Le firewall `main` couvre donc les routes principales de l'application.

Il utilise le système de formulaire Symfony :

```yaml
form_login:
  login_path: login
  check_path: login
  enable_csrf: true
  csrf_token_id: authenticate
```

La route utilisée pour afficher le formulaire et traiter l'authentification est :

```text
/login
```

---

# 🚪 Déconnexion

La déconnexion est également configurée dans le firewall :

```yaml
logout:
  path: logout
  target: /
```

Lorsqu'un utilisateur demande :

```text
/logout
```

Symfony gère automatiquement la déconnexion.

Après celle-ci, l'utilisateur est redirigé vers :

```text
/
```

---

# 🚦 Contrôle des accès

Les règles d'autorisation sont définies avec :

```yaml
access_control:
```

La configuration actuelle est :

```yaml
access_control:
  - { path: ^/login, roles: PUBLIC_ACCESS }
  - { path: ^/users, roles: ROLE_ADMIN }
  - { path: ^/, roles: ROLE_USER }
```

Ces règles sont évaluées pour déterminer si l'utilisateur peut accéder à une route.

---

# 🌐 Accès à `/login`

La route :

```text
/login
```

utilise :

```text
PUBLIC_ACCESS
```

Elle peut donc être consultée sans être authentifié.

C'est nécessaire puisque l'utilisateur doit pouvoir accéder à la page de connexion avant d'être authentifié.

```text
Utilisateur non authentifié
        ↓
GET /login
        ↓
PUBLIC_ACCESS
        ↓
✅ Page accessible
```

---

# 👑 Gestion des utilisateurs

Les routes commençant par :

```text
/users
```

nécessitent :

```text
ROLE_ADMIN
```

Ainsi :

```text
ROLE_ADMIN
    ↓
/users
    ↓
✅ Accès autorisé
```

alors que :

```text
ROLE_USER
    ↓
/users
    ↓
❌ Access Denied
```

Cette règle protège notamment les fonctionnalités de gestion des utilisateurs.

---

# 👤 Accès aux autres pages

La règle :

```yaml
- { path: ^/, roles: ROLE_USER }
```

protège toutes les autres routes correspondant au chemin `/`.

Un utilisateur doit donc être authentifié et disposer du rôle :

```text
ROLE_USER
```

pour accéder aux fonctionnalités normales de l'application.

---

# 👥 Gestion des rôles

L'application utilise deux rôles :

```text
ROLE_USER
ROLE_ADMIN
```

Dans l'entité `User`, la méthode :

```php
getRoles()
```

garantit qu'un utilisateur possède toujours au minimum :

```text
ROLE_USER
```

Par exemple, si l'utilisateur possède :

```php
['ROLE_ADMIN']
```

la méthode retourne :

```text
ROLE_ADMIN
ROLE_USER
```

Le rôle administrateur bénéficie donc également des permissions accordées à `ROLE_USER`.

---

# 🔄 Modification du rôle

Le rôle peut être défini avec :

```php
setRole()
```

La méthode accepte uniquement :

```text
ROLE_USER
ROLE_ADMIN
```

Toute autre valeur provoque une :

```php
InvalidArgumentException
```

Cela permet de limiter les rôles disponibles aux deux rôles prévus par l'application.

---

# 📝 Association avec les tâches

L'utilisateur possède également une relation avec les tâches :

```php
#[ORM\OneToMany(mappedBy: 'user', targetEntity: Task::class)]
private Collection $tasks;
```

La relation est donc :

```text
User
  │
  │ 1
  │
  │
  │ N
  ▼
Task
```

Lorsqu'une tâche est ajoutée à un utilisateur avec :

```php
$user->addTask($task);
```

l'utilisateur est automatiquement défini comme propriétaire :

```php
$task->setUser($this);
```

Cela permet de maintenir la cohérence de l'association entre les deux entités.

---

# 🛡️ Autorisation des actions sur les tâches

L'authentification permet de connaître l'utilisateur connecté.

L'autorisation détermine ensuite ce que cet utilisateur peut faire.

Pour les tâches, les décisions sont prises notamment par :

```text
src/Security/Voter/TaskVoter.php
```

Le Voter prend en charge :

```text
EDIT
TOGGLE
DELETE
```

Le principe est :

```text
Utilisateur authentifié
        ↓
Action sur une Task
        ↓
TaskVoter
        ↓
Vérification des droits
        ↓
GRANTED / DENIED
```

Le propriétaire peut notamment agir sur sa propre tâche.

Un autre utilisateur ne peut pas modifier ou supprimer une tâche qui ne lui appartient pas.

Les tâches associées à :

```text
anonymous
```

possèdent une règle particulière : leur suppression est réservée aux administrateurs.

---

# 👤 Utilisateur `anonymous`

Les anciennes tâches créées avant l'association obligatoire à un utilisateur sont rattachées à un utilisateur spécifique :

```text
anonymous
```

Cet utilisateur possède un compte dédié dans la table `user`.

Son rôle permet de conserver les anciennes tâches tout en maintenant désormais une association obligatoire :

```text
Task
  ↓
user_id
  ↓
anonymous
```

Les utilisateurs standards ne peuvent pas supprimer ces tâches.

Les administrateurs peuvent les supprimer conformément aux règles définies par le `TaskVoter`.

---

# 🔐 Authentification vs autorisation

Il est important de distinguer les deux notions.

## Authentification

Elle répond à la question :

```text
"Qui est l'utilisateur ?"
```

Elle est principalement gérée par :

```text
Symfony Security
Provider Doctrine
User
Firewall
```

## Autorisation

Elle répond à la question :

```text
"Cet utilisateur a-t-il le droit d'effectuer cette action ?"
```

Elle repose notamment sur :

```text
access_control
rôles
TaskVoter
```

Le fonctionnement global peut être résumé ainsi :

```text
              AUTHENTIFICATION
                     │
                     ▼
              Utilisateur connu
                     │
                     ▼
                Rôle(s)
                     │
                     ▼
               AUTORISATION
                ┌────┴────┐
                ▼         ▼
          access_control TaskVoter
                │         │
                └────┬────┘
                     ▼
              Accès autorisé
              ou refusé
```

---

# 🧭 Que modifier en cas d'évolution ?

## Modifier le comportement de connexion

Consulter principalement :

```text
config/packages/security.yaml
src/Controller/SecurityController.php
templates/security/login.html.twig
```

## Modifier les informations d'un utilisateur

Consulter :

```text
src/Entity/User.php
src/Form/UserType.php
src/Controller/UserController.php
```

## Modifier les rôles disponibles

Consulter :

```text
src/Entity/User.php
src/Form/UserType.php
config/packages/security.yaml
```

et vérifier également les tests associés.

## Modifier les autorisations sur les tâches

Consulter :

```text
src/Security/Voter/TaskVoter.php
```

et les tests :

```text
tests/Security/Voter/TaskVoterTest.php
```

## Modifier les accès aux routes

Consulter :

```text
config/packages/security.yaml
```

Les modifications de sécurité doivent toujours être accompagnées de tests permettant de vérifier les accès autorisés et refusés.

---

# 🧪 Tests de l'authentification

L'authentification et les autorisations sont couvertes par les tests fonctionnels.

Les tests permettent notamment de vérifier :

- l'accès à `/login` ;
- la connexion avec un utilisateur valide ;
- le refus d'identifiants invalides ;
- la redirection d'un utilisateur non authentifié ;
- l'accès d'un `ROLE_USER` aux fonctionnalités normales ;
- l'accès d'un `ROLE_ADMIN` à la gestion des utilisateurs ;
- le refus d'accès d'un `ROLE_USER` à `/users`.

Les tests du `TaskVoter` vérifient quant à eux les autorisations spécifiques aux tâches.

Les tests sont exécutés avec :

```bash
vendor/bin/phpunit
```

---

# 📚 Résumé pour un nouveau développeur

Pour comprendre rapidement l'authentification du projet, retenir les éléments suivants :

```text
1. User.php
   ↓
   Représente l'utilisateur Symfony

2. security.yaml
   ↓
   Configure Security, le provider, le firewall et les accès

3. SecurityController.php
   ↓
   Affiche la page de connexion

4. login.html.twig
   ↓
   Contient le formulaire de connexion

5. UserPasswordHasherInterface
   ↓
   Protège les mots de passe

6. access_control
   ↓
   Protège les routes selon les rôles

7. TaskVoter
   ↓
   Protège les actions sur les tâches
```

Le principe général est donc :

```text
Utilisateur
    ↓
Authentification Symfony
    ↓
User chargé depuis Doctrine
    ↓
Rôles récupérés
    ↓
Contrôle des accès
    ↓
Action autorisée ou refusée
```

Cette architecture permet de centraliser les règles de sécurité tout en séparant clairement l'authentification, la gestion des rôles et les autorisations métier.
