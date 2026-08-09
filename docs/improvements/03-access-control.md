# 🔐 Sécurisation des accès et des tâches

## Contexte et objectif

Après la mise en place de la gestion des rôles utilisateurs et l'association obligatoire des tâches à un utilisateur, l'application dispose désormais des informations nécessaires pour appliquer des règles d'autorisation.

L'objectif de cette évolution est de contrôler les actions selon :

- le rôle de l'utilisateur authentifié ;
- le propriétaire de la tâche ;
- le cas particulier des tâches rattachées à l'utilisateur `anonymous`.

Deux niveaux de sécurité sont concernés :

- l'accès aux pages de gestion des utilisateurs ;
- les actions réalisées sur les tâches.

Les règles d'autorisation sur les tâches sont centralisées dans un Symfony Voter afin d'éviter de dupliquer la logique de sécurité dans les contrôleurs.

---

## Authentification et autorisation

Il est important de distinguer deux notions.

### Authentification

L'authentification permet de déterminer **qui est l'utilisateur connecté**.

Dans l'application, l'utilisateur s'authentifie avec son nom d'utilisateur et son mot de passe.

Symfony utilise le composant Security pour charger l'utilisateur depuis la base de données et créer le contexte de sécurité associé à la session.

---

### Autorisation

L'autorisation intervient après l'authentification.

Elle permet de déterminer :

> "Cet utilisateur a-t-il le droit d'effectuer cette action ?"

Par exemple :

```text
Utilisateur connecté : test
Rôle : ROLE_USER

Tâche :
Propriétaire : admin

Action :
Suppression

Résultat :
Access Denied
```

L'authentification identifie donc l'utilisateur tandis que l'autorisation détermine ce qu'il est autorisé à faire.

---

## Rôles utilisateurs

L'application utilise deux rôles :

```text
ROLE_USER
ROLE_ADMIN
```

---

### ROLE_USER

Le rôle `ROLE_USER` correspond à un utilisateur standard.

Il peut notamment :

- consulter les fonctionnalités accessibles aux utilisateurs authentifiés ;
- créer des tâches ;
- modifier ses propres tâches ;
- changer l'état de ses propres tâches ;
- supprimer ses propres tâches.

Il ne peut pas :

- accéder aux pages de gestion des utilisateurs ;
- modifier les tâches appartenant à un autre utilisateur ;
- supprimer les tâches appartenant à un autre utilisateur ;
- supprimer les tâches rattachées à `anonymous`.

---

### ROLE_ADMIN

Le rôle `ROLE_ADMIN` correspond à un administrateur.

Il peut notamment accéder aux pages de gestion des utilisateurs.

Il dispose également d'une autorisation particulière concernant les tâches historiques rattachées à `anonymous`.

---

## Configuration `security.yaml`

La configuration principale se trouve dans :

```text
config/packages/security.yaml
```

La partie `access_control` contient les règles suivantes :

```yaml
access_control:
  - { path: ^/login, roles: PUBLIC_ACCESS }
  - { path: ^/users, roles: ROLE_ADMIN }
  - { path: ^/, roles: ROLE_USER }
```

### Accès à la connexion

La page de connexion est accessible sans authentification :

```yaml
- { path: ^/login, roles: PUBLIC_ACCESS }
```

---

### Gestion des utilisateurs

Toutes les routes commençant par `/users` sont réservées aux administrateurs :

```yaml
- { path: ^/users, roles: ROLE_ADMIN }
```

Cela protège notamment :

```text
/users
/users/create
```

Un utilisateur possédant uniquement `ROLE_USER` obtient :

```text
Access Denied
Autres pages
```

La règle suivante demande une authentification avec le rôle utilisateur :

```yaml
- { path: ^/, roles: ROLE_USER }
```

Elle constitue une règle générale pour les autres routes de l'application.

---

## Gestion des tâches avec un Voter

Les règles d'autorisation liées aux tâches sont regroupées dans :

```text
src/Security/Voter/TaskVoter.php
```

Le Voter utilise trois attributs :

```php
public const EDIT = 'TASK_EDIT';
public const DELETE = 'TASK_DELETE';
public const TOGGLE = 'TASK_TOGGLE';
```

Ces attributs correspondent respectivement aux actions suivantes :

```text
TASK_EDIT
    → modification d'une tâche

TASK_DELETE
    → suppression d'une tâche

TASK_TOGGLE
    → changement de l'état d'une tâche
```

Le Voter reçoit :

- l'action demandée ;
- la tâche concernée ;
- l'utilisateur authentifié.

Il peut ainsi déterminer si l'action doit être autorisée ou refusée.

---

## Règle de propriété

Une tâche appartient obligatoirement à un utilisateur.

La relation est :

```text
User 1 ─────── N Task
```

Le Voter peut donc comparer l'utilisateur connecté au propriétaire de la tâche.

Exemple :

```text
Alice
└── Tâche A

Bob
└── Tâche B

Si Alice est connectée :

Tâche A → autorisée
Tâche B → refusée
```

Cette règle est utilisée pour les actions `EDIT` et `TOGGLE`, ainsi que pour la suppression des tâches normales.

---

## Modification d'une tâche

L'action `EDIT` est protégée par :

```php
$this->denyAccessUnlessGranted(
TaskVoter::EDIT,
$task
);
```

Le Voter vérifie que l'utilisateur connecté est le propriétaire de la tâche.

```php
case self::EDIT:
return $task->getUser() === $user;
```

Ainsi :

```text
Propriétaire de la tâche
↓
EDIT
↓
✅

Autre utilisateur
↓
EDIT
↓
❌
```

Le formulaire de tâche ne contient par ailleurs aucun champ permettant de modifier le propriétaire.

L'auteur de la tâche reste donc associé à celle-ci.

---

## Changement d'état d'une tâche

L'action `TOGGLE` est également protégée par le Voter :

```php
$this->denyAccessUnlessGranted(
TaskVoter::TOGGLE,
$task
);
```

La règle est identique à celle de la modification :

```php
case self::TOGGLE:
return $task->getUser() === $user;
```

Seul le propriétaire peut donc changer l'état de sa tâche.

Exemple :

```text
Alice
└── Tâche A

Alice → TOGGLE → ✅
Bob → TOGGLE → ❌
```

---

## Suppression d'une tâche

La suppression utilise :

```php
$this->denyAccessUnlessGranted(
TaskVoter::DELETE,
$task
);
```

Le comportement dépend du propriétaire de la tâche.

### Tâche appartenant à un utilisateur

Un utilisateur peut supprimer sa propre tâche :

```text
Alice
└── Tâche A

Alice → DELETE → ✅

Un autre utilisateur ne peut pas la supprimer :

Alice
└── Tâche A

Bob → DELETE → ❌
```

Le Voter applique cette règle :

```php
return $task->getUser() === $user;
```

---

### Cas particulier de l'utilisateur `anonymous`

Lors de la migration des anciennes données, les tâches qui ne possédaient pas de propriétaire ont été rattachées à l'utilisateur :

```text
anonymous
```

Ces tâches représentent des données historiques dont l'auteur réel n'était pas connu.

Elles bénéficient d'une règle spécifique.

```text
Tâche → anonymous

ROLE_USER
↓
DELETE → ❌

ROLE_ADMIN
↓
DELETE → ✅
```

Le Voter vérifie le cas particulier :

```php
if ($task->getUser()->getUsername() === 'anonymous') {
return in_array('ROLE_ADMIN', $user->getRoles(), true);
}
```

Ainsi, un utilisateur standard ne peut pas supprimer les anciennes tâches tandis qu'un administrateur peut les supprimer.

---

## Intégration du Voter dans le contrôleur

Le contrôleur concerné est :

```text
src/Controller/TaskController.php
```

Le Voter est importé :

```php
use App\Security\Voter\TaskVoter;
```

Les trois actions protégées utilisent ensuite :

```php
$this->denyAccessUnlessGranted(TaskVoter::EDIT, $task);
$this->denyAccessUnlessGranted(TaskVoter::TOGGLE, $task);
$this->denyAccessUnlessGranted(TaskVoter::DELETE, $task);
```

Le contrôleur reste ainsi responsable de l'action métier tandis que le Voter prend la décision d'autorisation.

Cette séparation permet de centraliser les règles de sécurité.

---

## Vérification de l'enregistrement du Voter

Symfony détecte automatiquement le Voter comme service de sécurité.

La commande :

```bash
php bin/console debug:container --tag=security.voter
```

permet de vérifier son enregistrement.

Le résultat contient notamment :

```text
App\Security\Voter\TaskVoter
```

Le Voter est donc bien chargé par le conteneur Symfony.

---

Vérifications techniques

Les commandes suivantes ont été utilisées :

```bash
php bin/console cache:clear
```

```bash
php bin/console debug:container --tag=security.voter
```

```bash
php bin/console doctrine:schema:validate
```

```bash
composer validate
```

Ces vérifications permettent notamment de confirmer :

- le bon fonctionnement du cache Symfony ;
- l'enregistrement du Voter ;
- la cohérence du mapping Doctrine ;
- la validité du fichier `composer.json`.

---

## Tests fonctionnels réalisés

Des tests manuels ont été réalisés afin de vérifier les différentes règles d'autorisation.

### Accès aux pages utilisateurs

Avec `ROLE_USER` :

```text
/users/create
```

Résultat :

```text
Access Denied
```

Avec `ROLE_ADMIN` :

```text
/users/create
```

Résultat :

```text
Accès autorisé
```

La restriction des pages de gestion des utilisateurs fonctionne donc correctement.

---

### Tests du `TASK_EDIT`

#### Propriétaire

```text
test
└── tâche n°7
```

Résultat :

```text
Modification autorisée
```

#### Autre utilisateur

```text
test
└── tâche n°6
      └── propriétaire : admin
```

Résultat :

```text
Access Denied
```

---

### Tests du `TASK_TOGGLE`

Les tests ont vérifié que :

```text
admin → sa propre tâche
```

est autorisé.

De même :

```text
test → sa propre tâche
```

est autorisé.

En revanche :

```text
test → tâche de admin
```

est refusé.

Et :

```text
admin → tâche de test
```

est également refusé.

La règle de propriété est donc appliquée au changement d'état.

---

### Tests du `TASK_DELETE`

Les quatre scénarios principaux ont été vérifiés.

#### Propriétaire

```text
test → tâche de test
```

Résultat :

```text
✅ Suppression autorisée
```

---

#### Tâche d'un autre utilisateur

```text
test → tâche de admin
```

Résultat :

```text
❌ Access Denied
```

La tâche est restée présente en base de données.

---

#### Tâche `anonymous` avec ROLE_USER

```text
test → tâche anonymous
```

Résultat :

```text
❌ Access Denied
```

La tâche est restée présente en base de données.

---

#### Tâche `anonymous` avec ROLE_ADMIN

```text
admin → tâche anonymous
```

Résultat :

```text
✅ Suppression autorisée
```

La vérification en base a confirmé que la tâche avait bien été supprimée.

---

### Tableau récapitulatif

| Action   | Propriétaire | Autre utilisateur | `anonymous` + `ROLE_USER` | `anonymous` + `ROLE_ADMIN` |
| -------- | :----------: | :---------------: | :-----------------------: | :------------------------: |
| `EDIT`   |      ✅      |        ❌         |            ❌             |             ❌             |
| `TOGGLE` |      ✅      |        ❌         |            ❌             |             ❌             |
| `DELETE` |      ✅      |        ❌         |            ❌             |             ✅             |

Cette matrice représente les règles d'autorisation actuellement implémentées dans l'application.

---

## Choix techniques

### Pourquoi utiliser un Voter ?

Le Voter permet de centraliser les règles d'autorisation liées aux tâches.

Sans Voter, les contrôleurs pourraient contenir des conditions telles que :

```php
if ($task->getUser() !== $this->getUser()) {
    // refuser
}
```

Ces règles seraient alors répétées dans plusieurs contrôleurs.

Avec le Voter :

```php
$this->denyAccessUnlessGranted(
    TaskVoter::DELETE,
    $task
);
```

le contrôleur délègue la décision de sécurité au composant dédié.

Cela rend le code :

- plus lisible ;
- plus facilement testable ;
- plus maintenable ;
- plus cohérent avec les mécanismes de sécurité de Symfony.

---

### Séparation des responsabilités

L'architecture obtenue sépare les responsabilités.

```text
security.yaml
    │
    └── accès global aux routes
             │
             ▼
       ROLE_USER / ROLE_ADMIN


TaskController
    │
    └── demande une autorisation
             │
             ▼
         TaskVoter
             │
             ├── propriétaire ?
             ├── anonymous ?
             └── ROLE_ADMIN ?
             │
             ▼
       GRANTED / DENIED
```

Cette organisation facilite l'évolution future des règles de sécurité.

---

### Points d'attention

Le compte `anonymous` est actuellement identifié à partir de son nom d'utilisateur :

```php
$task->getUser()->getUsername() === 'anonymous'
```

Ce fonctionnement répond au besoin actuel mais pourrait être amélioré à l'avenir avec un mécanisme plus robuste, par exemple une constante ou un identifiant spécifique permettant d'identifier ce compte système.

Cette amélioration n'est pas nécessaire pour répondre au besoin actuel.

---

## Conclusion

La sécurisation des accès et des tâches est désormais mise en place.

Les pages de gestion des utilisateurs sont réservées aux utilisateurs possédant le rôle `ROLE_ADMIN`.

Les actions sur les tâches sont protégées par le `TaskVoter`, qui centralise les règles d'autorisation.

Les règles suivantes sont respectées :

- un utilisateur peut modifier ses propres tâches ;
- un utilisateur peut changer l'état de ses propres tâches ;
- un utilisateur peut supprimer ses propres tâches ;
- un utilisateur ne peut pas modifier ou supprimer les tâches d'un autre utilisateur ;
- une tâche `anonymous` ne peut pas être supprimée par un utilisateur standard ;
- une tâche `anonymous` peut être supprimée par un administrateur.

Les contrôleurs utilisent explicitement le Voter pour les actions `EDIT`, `TOGGLE` et `DELETE`.

Les tests fonctionnels manuels réalisés confirment le comportement attendu.

Cette étape fournit désormais la base nécessaire pour automatiser les règles de sécurité avec PHPUnit et compléter la couverture de tests du projet.
