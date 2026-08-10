# 🧪 Implémentation des tests unitaires

## Contexte et objectif

Après la mise en place de la gestion des rôles utilisateurs, de l'association obligatoire des tâches à un utilisateur et de la sécurisation des accès, l'application dispose désormais des fonctionnalités métier principales.

L'objectif de cette étape est de mettre en place des **tests automatisés avec PHPUnit** afin de vérifier le comportement interne des principales classes de l'application et de limiter les risques de régression lors des évolutions futures.

Les tests unitaires permettent notamment de vérifier le comportement :

- des entités `User` et `Task` ;
- du `TaskVoter` ;
- de l'association entre une tâche et son propriétaire ;
- de la gestion des rôles ;
- des différentes règles d'autorisation.

Les tests sont exécutés indépendamment des requêtes HTTP et de l'interface utilisateur, à l'exception du test fonctionnel du contrôleur déjà présent dans le projet.

---

# Organisation des tests

Les tests sont regroupés dans le répertoire :

```text
tests/
```

L'organisation retenue correspond aux classes testées :

```text
tests/
├── Controller/
│   └── HomepageControllerTest.php
│
├── Entity/
│   ├── TaskTest.php
│   └── UserTest.php
│
└── Security/
    └── Voter/
        └── TaskVoterTest.php
```

Cette organisation permet de retrouver facilement les tests correspondant aux différentes parties de l'application.

### Tests des entités

```text
tests/Entity/
```

Contient les tests unitaires des entités principales :

```text
TaskTest.php
UserTest.php
```

### Tests de sécurité

```text
tests/Security/Voter/
```

Contient les tests unitaires du :

```text
TaskVoter
```

### Tests fonctionnels

```text
tests/Controller/
```

Le projet contient également un test fonctionnel utilisant `WebTestCase` :

```text
HomepageControllerTest.php
```

Ce test vérifie le comportement HTTP de la page d'accueil pour un utilisateur non authentifié.

Il est conservé car il appartient au périmètre des tests fonctionnels et sera complété dans l'étape dédiée à ces tests.

---

# Configuration PHPUnit

PHPUnit est installé comme dépendance du projet et peut être exécuté depuis le répertoire :

```text
vendor/bin/phpunit
```

La configuration est définie dans :

```text
phpunit.xml.dist
```

L'environnement utilisé pour les tests est :

```text
PHP 8.2.33
```

avec :

```text
PHPUnit 11.5.56
```

Les tests sont exécutés directement depuis le conteneur PHP Docker.

---

## Exécution des tests

Pour exécuter l'ensemble de la suite :

```bash
vendor/bin/phpunit
```

Pour exécuter un fichier de test spécifique :

```bash
vendor/bin/phpunit tests/Security/Voter/TaskVoterTest.php
```

ou :

```bash
vendor/bin/phpunit tests/Entity/UserTest.php
```

ou :

```bash
vendor/bin/phpunit tests/Entity/TaskTest.php
```

Dans ce projet, la commande utilisée est `vendor/bin/phpunit`.

La commande :

```bash
php bin/phpunit
```

n'est pas utilisée car le projet ne possède pas de fichier `bin/phpunit`.

---

# Tests du `TaskVoter`

Le `TaskVoter` est un élément important de la sécurité de l'application.

Il se trouve dans :

```text
src/Security/Voter/TaskVoter.php
```

Le test associé est :

```text
tests/Security/Voter/TaskVoterTest.php
```

Le Voter définit trois actions :

```php
TaskVoter::EDIT
TaskVoter::DELETE
TaskVoter::TOGGLE
```

Les tests vérifient les autorisations et les refus associés à ces trois actions.

---

## Principe du test

Le `TaskVoter` dépend du contexte de sécurité Symfony.

Dans les tests unitaires, il n'est pas nécessaire de démarrer toute l'application Symfony.

Le test utilise un mock de :

```php
TokenInterface
```

afin de représenter l'utilisateur authentifié.

Le scénario est donc construit directement en mémoire :

```text
TokenInterface
      │
      └── utilisateur connecté
              │
              ▼
           TaskVoter
              │
              ▼
             Task
```

Cette approche permet de tester uniquement la logique du Voter.

---

# Scénarios testés avec `TaskVoter`

Les principaux scénarios métier sont couverts.

| Utilisateur       | Tâche                     | Action   | Résultat  |
| ----------------- | ------------------------- | -------- | --------- |
| Propriétaire      | Sa tâche                  | `EDIT`   | ✅        |
| Autre utilisateur | Tâche d'un autre          | `EDIT`   | ❌        |
| Propriétaire      | Sa tâche                  | `TOGGLE` | ✅        |
| Autre utilisateur | Tâche d'un autre          | `TOGGLE` | ❌        |
| Propriétaire      | Sa tâche                  | `DELETE` | ✅        |
| Autre utilisateur | Tâche d'un autre          | `DELETE` | ❌        |
| `ROLE_USER`       | Tâche `anonymous`         | `DELETE` | ❌        |
| `ROLE_ADMIN`      | Tâche `anonymous`         | `DELETE` | ✅        |
| Non authentifié   | Tâche                     | `DELETE` | ❌        |
| Utilisateur       | Sujet autre qu'une `Task` | `DELETE` | `ABSTAIN` |

Ces tests couvrent les règles d'autorisation définies lors de l'implémentation du `TaskVoter`.

---

## Test de la modification

Pour l'action :

```php
TaskVoter::EDIT
```

le propriétaire de la tâche doit être autorisé :

```text
Utilisateur A
    │
    └── Tâche A

Utilisateur A
    ↓
EDIT
    ↓
GRANTED
```

Un autre utilisateur doit être refusé :

```text
Utilisateur A
    │
    └── Tâche A

Utilisateur B
    ↓
EDIT
    ↓
DENIED
```

Le test vérifie donc que la propriété de la tâche est bien respectée.

---

## Test du changement d'état

Pour :

```php
TaskVoter::TOGGLE
```

le comportement est identique à celui de la modification.

Le propriétaire est autorisé :

```text
Propriétaire
    ↓
TOGGLE
    ↓
GRANTED
```

Un autre utilisateur est refusé :

```text
Autre utilisateur
    ↓
TOGGLE
    ↓
DENIED
```

---

## Tests de suppression

L'action :

```php
TaskVoter::DELETE
```

possède une règle particulière pour les tâches `anonymous`.

### Propriétaire

Le propriétaire peut supprimer sa propre tâche :

```text
Propriétaire
    ↓
DELETE
    ↓
GRANTED
```

### Autre utilisateur

Un autre utilisateur ne peut pas supprimer la tâche :

```text
Autre utilisateur
    ↓
DELETE
    ↓
DENIED
```

### Tâche `anonymous`

Les anciennes tâches ne possédant pas de propriétaire connu ont été rattachées à l'utilisateur :

```text
anonymous
```

Un utilisateur standard ne peut pas supprimer ces tâches :

```text
ROLE_USER
    ↓
Tâche anonymous
    ↓
DELETE
    ↓
DENIED
```

Un administrateur peut les supprimer :

```text
ROLE_ADMIN
    ↓
Tâche anonymous
    ↓
DELETE
    ↓
GRANTED
```

---

## Utilisateur non authentifié

Le `TaskVoter` vérifie que l'utilisateur fourni par le token est bien une instance de :

```php
App\Entity\User
```

Si aucun utilisateur valide n'est fourni, l'accès est refusé.

Le test vérifie donc le comportement :

```text
Utilisateur non authentifié
        ↓
TaskVoter
        ↓
DENIED
```

---

## Sujet non pris en charge

Le Voter ne doit prendre en charge que les objets de type :

```php
Task
```

Un test vérifie donc qu'un autre type d'objet n'est pas pris en charge.

Exemple :

```text
TaskVoter
    ↓
stdClass
    ↓
ACCESS_ABSTAIN
```

Ce comportement permet au Voter de respecter le mécanisme standard des Voters Symfony.

---

# Tests de l'entité `User`

Le fichier :

```text
tests/Entity/UserTest.php
```

contient les tests unitaires de l'entité :

```text
src/Entity/User.php
```

Les comportements suivants sont testés :

- `getUserIdentifier()` ;
- `getUsername()` / `setUsername()` ;
- `getPassword()` / `setPassword()` ;
- `getEmail()` / `setEmail()` ;
- `getRoles()` ;
- `setRoles()` ;
- `getRole()` ;
- `setRole()` ;
- rejet d'un rôle invalide ;
- `getSalt()` ;
- `eraseCredentials()` ;
- initialisation de la collection des tâches ;
- ajout d'une tâche ;
- suppression d'une tâche.

---

## Gestion des rôles

Un utilisateur possède au minimum :

```text
ROLE_USER
```

`getRoles()` ajoute automatiquement `ROLE_USER` lorsqu'il n'est pas présent.

Ainsi :

```php
$user->setRoles(['ROLE_ADMIN']);
```

retourne avec :

```php
$user->getRoles();
```

les rôles :

```text
ROLE_ADMIN
ROLE_USER
```

Le comportement est couvert par les tests unitaires.

---

## Rôle administrateur

Le rôle administrateur est également testé :

```php
$user->setRole('ROLE_ADMIN');
```

Le test vérifie que :

```text
getRole()
    ↓
ROLE_ADMIN
```

et que :

```text
getRoles()
    ↓
ROLE_ADMIN + ROLE_USER
```

sont correctement retournés.

---

## Rôle invalide

La méthode `setRole()` refuse les rôles qui ne sont pas :

```text
ROLE_USER
ROLE_ADMIN
```

Un test vérifie qu'une :

```php
InvalidArgumentException
```

est levée lorsqu'un rôle invalide est fourni.

---

# Tests de l'association `User` / `Task`

La relation entre les deux entités est :

```text
User 1 ───────── N Task
```

La méthode :

```php
User::addTask()
```

ajoute la tâche à la collection de l'utilisateur et définit automatiquement l'utilisateur comme propriétaire de la tâche.

Le test vérifie donc :

```php
$user->addTask($task);
```

puis :

```php
$task->getUser() === $user
```

et :

```php
$user->getTasks()->contains($task)
```

L'association bidirectionnelle est ainsi vérifiée.

---

## Absence de doublon

`User::addTask()` vérifie également que la tâche n'est pas déjà présente dans la collection.

Le comportement suivant est testé :

```php
$user->addTask($task);
$user->addTask($task);
```

La collection doit toujours contenir une seule tâche.

---

## Suppression d'une tâche

`User::removeTask()` est également testé.

Après :

```php
$user->removeTask($task);
```

la tâche ne doit plus être présente dans la collection et son propriétaire doit être supprimé :

```text
User
    ↓
removeTask()
    ↓
Task retirée de la collection
    ↓
Task::getUser() → null
```

---

# Tests de l'entité `Task`

Le fichier :

```text
tests/Entity/TaskTest.php
```

teste l'entité :

```text
src/Entity/Task.php
```

Les comportements suivants sont vérifiés :

- création automatique de la date ;
- état initial de la tâche ;
- titre ;
- contenu ;
- changement d'état ;
- modification de la date ;
- association avec un utilisateur.

---

## État initial

Lors de la création d'une tâche :

```php
$task = new Task();
```

le constructeur initialise :

```php
$this->createdAt = new \Datetime();
$this->isDone = false;
```

Les tests vérifient donc :

```text
createdAt → DateTime
isDone    → false
```

---

## Changement d'état

La méthode :

```php
Task::toggle()
```

reçoit directement l'état souhaité.

Exemple :

```php
$task->toggle(true);
```

donne :

```text
isDone → true
```

et :

```php
$task->toggle(false);
```

donne :

```text
isDone → false
```

Ces deux comportements sont couverts par les tests.

---

# Résultat des tests

L'ensemble des tests PHPUnit a été exécuté avec :

```bash
vendor/bin/phpunit
```

Résultat obtenu :

```text
38 tests
48 assertions
OK
```

Tous les tests sont donc actuellement au vert.

La suite comprend :

```text
TaskVoterTest.php
    → 10 tests

UserTest.php
    → 18 tests

TaskTest.php
    → 9 tests

HomepageControllerTest.php
    → 1 test fonctionnel existant
```

Soit un total de :

```text
38 tests
48 assertions
```

---

# Rapport de couverture de code

La couverture de code est générée avec :

```text
Xdebug 3.5.3
```

La version de PHP utilisée est :

```text
PHP 8.2.33
```

Le rapport HTML est généré avec :

```bash
XDEBUG_MODE=coverage vendor/bin/phpunit --coverage-html coverage
```

Le rapport est généré dans :

```text
coverage/
```

Cette commande permet notamment de consulter la couverture classe par classe et méthode par méthode.

---

## Première mesure de couverture

Une première mesure a été réalisée avec :

```bash
XDEBUG_MODE=coverage vendor/bin/phpunit --coverage-text
```

Résultat :

```text
Code Coverage Report:

Classes:  0.00% (0/9)
Methods: 65.91% (29/44)
Lines:   34.12% (58/170)
```

Le taux de couverture des lignes est donc actuellement de :

```text
34.12 %
```

L'objectif défini pour le projet est :

```text
> 70 %
```

L'objectif global de couverture n'est donc pas encore atteint à cette étape.

---

## Couverture des principales classes testées

Les principales classes couvertes par les tests unitaires présentent cependant un niveau de couverture élevé :

| Classe                         | Méthodes | Lignes  |
| ------------------------------ | -------- | ------- |
| `App\Entity\Task`              | 91,67 %  | 92,86 % |
| `App\Entity\User`              | 94,44 %  | 96,97 % |
| `App\Security\Voter\TaskVoter` | 50,00 %  | 92,86 % |

Ces résultats montrent que les principales règles métier ciblées par cette étape sont correctement couvertes.

La couverture globale reste plus faible car d'autres classes de l'application, notamment les contrôleurs et autres composants, ne disposent pas encore d'une couverture de tests suffisante.

---

# Différence entre tests réussis et couverture

Il est important de distinguer :

```text
38 / 38 tests réussis
```

et :

```text
34,12 % de lignes couvertes
```

Le premier résultat indique que tous les tests écrits actuellement passent correctement.

Le second indique quelle proportion du code de l'application a réellement été exécutée pendant ces tests.

Ainsi :

```text
Tests PHPUnit
    ↓
38 tests
    ↓
100 % de réussite
```

ne signifie pas :

```text
100 % du code couvert
```

La couverture devra être augmentée avec les tests complémentaires, notamment les tests fonctionnels.

---

# Captures d'écran

Les captures liées aux tests sont regroupées dans :

```text
docs/improvements/screenshots/tests/
```

Les captures recommandées sont :

```text
01-task-voter-tests.png
02-all-tests.png
03-code-coverage-initial.png
```

Elles permettent notamment de conserver une preuve :

- de l'exécution des tests du `TaskVoter` ;
- de l'exécution de l'ensemble de la suite PHPUnit ;
- du premier rapport de couverture.

---

# Vérifications techniques

Les tests ont été exécutés dans le conteneur PHP Docker.

Commande principale :

```bash
vendor/bin/phpunit
```

Résultat :

```text
38 tests
48 assertions
OK
```

La couverture a été générée avec Xdebug :

```bash
XDEBUG_MODE=coverage vendor/bin/phpunit --coverage-html coverage
```

et mesurée avec :

```bash
XDEBUG_MODE=coverage vendor/bin/phpunit --coverage-text
```

---

# Points d'attention

Le répertoire :

```text
coverage/
```

est un répertoire généré automatiquement par PHPUnit.

Il ne doit pas être considéré comme du code source de l'application.

Il est recommandé de l'exclure du versionnement Git :

```gitignore
/coverage/
```

Le rapport peut être régénéré à tout moment à partir des tests et de Xdebug.

---

# Limites actuelles

La couverture globale est actuellement de :

```text
34,12 %
```

L'objectif de :

```text
> 70 %
```

n'est donc pas encore atteint.

Cette première mesure constitue un **état des lieux** après l'implémentation des tests unitaires.

Les tests fonctionnels permettront de couvrir davantage de comportements de l'application, notamment :

- l'authentification ;
- les accès aux différentes pages ;
- la création de tâches ;
- la modification de tâches ;
- la suppression de tâches ;
- les règles d'autorisation ;
- la gestion des utilisateurs.

La couverture pourra ainsi être augmentée progressivement jusqu'à l'objectif fixé.

---

# Conclusion

L'implémentation des tests unitaires permet désormais de sécuriser les principales règles métier de l'application.

Les entités `User` et `Task` ainsi que le `TaskVoter` disposent de tests automatisés couvrant leurs principaux comportements.

La suite PHPUnit comporte actuellement :

```text
38 tests
48 assertions
0 échec
0 erreur
```

Le `TaskVoter` est notamment couvert sur les règles :

- `EDIT` ;
- `TOGGLE` ;
- `DELETE` ;
- propriétaire de la tâche ;
- autre utilisateur ;
- tâche `anonymous` ;
- `ROLE_ADMIN` ;
- utilisateur non authentifié ;
- sujet non pris en charge.

Un premier rapport de couverture a également été généré avec Xdebug.

La couverture actuelle est de :

```text
34,12 % des lignes
```

Cette valeur constitue le point de départ avant l'ajout des tests complémentaires.

L'objectif final du projet reste une couverture supérieure à :

```text
70 %
```

Les tests fonctionnels et les éventuels tests complémentaires permettront de poursuivre l'augmentation de cette couverture.
