# 🧪 Implémentation des tests fonctionnels

## Contexte et objectif

Après la mise en place des tests unitaires sur les principales entités et règles métier de l'application, cette étape consiste à compléter la stratégie de tests avec des **tests fonctionnels**.

Les tests fonctionnels permettent de vérifier le comportement de l'application dans des conditions proches de son utilisation réelle.

Contrairement aux tests unitaires, ils exécutent l'application Symfony et simulent des requêtes HTTP afin de vérifier notamment :

- les redirections ;
- l'authentification ;
- les codes HTTP retournés ;
- l'accès aux différentes pages ;
- les règles d'autorisation ;
- les actions réalisées sur les tâches ;
- la gestion des utilisateurs.

L'objectif est également d'augmenter la couverture globale du code afin d'atteindre l'objectif fixé par le projet :

```text
> 70 % de couverture de code
```

---

# Organisation des tests fonctionnels

Les tests fonctionnels sont regroupés dans :

```text
tests/Controller/
```

L'organisation actuelle est la suivante :

```text
tests/
├── Controller/
│   ├── HomepageControllerTest.php
│   ├── TaskControllerTest.php
│   └── UserControllerTest.php
│
├── Entity/
│   ├── TaskTest.php
│   └── UserTest.php
│
└── Security/
    └── Voter/
        └── TaskVoterTest.php
```

Les tests fonctionnels concernent principalement les contrôleurs :

```text
HomepageControllerTest.php
TaskControllerTest.php
UserControllerTest.php
```

---

# Utilisation de `WebTestCase`

Les tests fonctionnels des contrôleurs utilisent :

```php
Symfony\Bundle\FrameworkBundle\Test\WebTestCase
```

Cette classe permet de démarrer l'application Symfony et de simuler des requêtes HTTP.

Un client de test est créé avec :

```php
$client = static::createClient();
```

Une requête peut ensuite être simulée avec :

```php
$client->request('GET', '/tasks');
```

Le résultat de la requête peut alors être vérifié avec les assertions Symfony :

```php
$this->assertResponseIsSuccessful();
```

ou :

```php
$this->assertResponseStatusCodeSame(403);
```

ou encore :

```php
$this->assertResponseRedirects('/login');
```

Cette approche permet de tester le comportement réel des contrôleurs, du système de sécurité et du routage Symfony.

---

# Gestion de l'authentification dans les tests

Les tests fonctionnels doivent pouvoir exécuter des requêtes avec différents profils utilisateurs.

Symfony fournit pour cela la méthode :

```php
$client->loginUser($user);
```

Cette méthode permet de simuler la connexion d'un utilisateur sans avoir à effectuer manuellement le formulaire de connexion dans chaque test.

Par exemple :

```php
$user = $this->getUserByUsername('test');

$client->loginUser($user);

$client->request('GET', '/tasks');
```

Le test peut ainsi vérifier le comportement de la page pour un utilisateur authentifié.

Pour certains tests du `UserController`, le formulaire de connexion est utilisé directement afin de vérifier le parcours d'authentification :

```text
Page de connexion
        ↓
Saisie identifiant / mot de passe
        ↓
Soumission du formulaire
        ↓
Utilisateur authentifié
        ↓
Accès à la ressource
```

---

# Tests fonctionnels du `TaskController`

Le fichier :

```text
tests/Controller/TaskControllerTest.php
```

contient les tests fonctionnels du contrôleur :

```text
src/Controller/TaskController.php
```

Ces tests permettent de vérifier les principales règles fonctionnelles liées aux tâches.

Les scénarios couvrent :

- l'accès à la liste des tâches ;
- l'accès à la modification d'une tâche ;
- la modification de sa propre tâche ;
- le refus de modification d'une tâche appartenant à un autre utilisateur ;
- le changement d'état d'une tâche ;
- le refus de changement d'état d'une tâche appartenant à un autre utilisateur ;
- la suppression de sa propre tâche ;
- le refus de suppression d'une tâche appartenant à un autre utilisateur ;
- le refus de suppression d'une tâche `anonymous` par un utilisateur standard ;
- la suppression d'une tâche `anonymous` par un administrateur.

---

## Accès anonyme à la liste des tâches

Le premier scénario vérifie qu'un utilisateur non authentifié ne peut pas accéder à la liste des tâches.

La requête est effectuée sans connexion :

```php
$client->request('GET', '/tasks');
```

Le comportement attendu est une redirection vers :

```text
/login
```

Le test vérifie donc :

```php
$this->assertResponseRedirects('/login');
```

Le scénario est :

```text
Utilisateur non authentifié
        ↓
GET /tasks
        ↓
Redirection
        ↓
/login
```

---

# Accès authentifié à la liste

Un utilisateur authentifié doit pouvoir accéder à la liste des tâches.

Le test utilise :

```php
$client->loginUser($user);
```

puis :

```php
$client->request('GET', '/tasks');
```

La réponse attendue est une réponse HTTP réussie :

```php
$this->assertResponseIsSuccessful();
```

Ce test vérifie donc que l'authentification permet bien d'accéder à la fonctionnalité.

---

# Modification d'une tâche

Deux scénarios sont testés.

### Propriétaire

Un utilisateur doit pouvoir accéder à la modification de sa propre tâche :

```text
Utilisateur A
    ↓
Tâche A
    ↓
GET /tasks/{id}/edit
    ↓
200 OK
```

Le test vérifie que la page est accessible.

### Autre utilisateur

Un utilisateur ne doit pas pouvoir modifier la tâche d'un autre utilisateur :

```text
Utilisateur B
    ↓
Tâche appartenant à A
    ↓
GET /tasks/{id}/edit
    ↓
403 Forbidden
```

Cette vérification permet de confirmer que la règle d'autorisation définie dans le `TaskVoter` est correctement appliquée au niveau du contrôleur.

---

# Changement d'état d'une tâche

La fonctionnalité de changement d'état est également testée.

Pour sa propre tâche, l'utilisateur authentifié peut appeler :

```text
/tasks/{id}/toggle
```

Le test vérifie :

1. que la requête est acceptée ;
2. que l'utilisateur est redirigé vers la liste des tâches ;
3. que l'état de la tâche a effectivement changé.

Le test vérifie notamment :

```php
$this->assertTrue($task->isDone());
```

Le scénario permet donc de vérifier non seulement la réponse HTTP, mais également la modification réelle de l'entité en base de données.

---

## Refus du changement d'état d'une autre tâche

Un utilisateur ne peut pas modifier l'état d'une tâche dont il n'est pas propriétaire.

Le test vérifie que la requête retourne :

```text
403 Forbidden
```

La tâche reste ensuite disponible en base de données.

---

# Suppression des tâches

La suppression est une fonctionnalité particulièrement importante du point de vue des autorisations.

Trois scénarios sont testés.

### Suppression de sa propre tâche

Un utilisateur authentifié peut supprimer une tâche dont il est propriétaire.

Le test vérifie :

```text
GET /tasks/{id}/delete
        ↓
Redirection vers /tasks
        ↓
Tâche absente de la base
```

Après la requête, l'EntityManager est nettoyé :

```php
$this->getEntityManager()->clear();
```

La tâche est ensuite recherchée en base.

Le résultat attendu est :

```php
$this->assertNull($deletedTask);
```

Cela permet de vérifier que la suppression a réellement été effectuée.

---

## Refus de suppression d'une tâche appartenant à un autre utilisateur

Un utilisateur standard ne peut pas supprimer la tâche d'un autre utilisateur.

Le test vérifie :

```text
Utilisateur B
    ↓
Tâche de A
    ↓
DELETE
    ↓
403 Forbidden
```

La tâche est ensuite recherchée dans la base afin de vérifier qu'elle existe toujours.

Le test utilise :

```php
$this->assertNotNull($existingTask);
```

Cette vérification garantit que l'autorisation ne bloque pas seulement l'affichage mais empêche réellement la suppression.

---

# Gestion des tâches `anonymous`

Les anciennes tâches de l'application sont rattachées à l'utilisateur :

```text
anonymous
```

Une règle spécifique a été mise en place :

```text
ROLE_USER
    ↓
Tâche anonymous
    ↓
DELETE
    ↓
403 Forbidden
```

Le test :

```text
testUserCannotDeleteAnonymousTask()
```

vérifie ce comportement.

La tâche doit rester présente dans la base de données après la tentative de suppression.

---

## Suppression d'une tâche `anonymous` par un administrateur

Un administrateur doit pouvoir supprimer une tâche appartenant à :

```text
anonymous
```

Le scénario testé est :

```text
ROLE_ADMIN
    ↓
Tâche anonymous
    ↓
DELETE
    ↓
Redirection vers /tasks
    ↓
Tâche supprimée
```

Le test vérifie à la fois :

- la redirection ;
- l'absence de la tâche en base de données.

Cette vérification permet de valider la règle métier définie dans le cahier des charges.

---

# Tests fonctionnels du `UserController`

Le fichier :

```text
tests/Controller/UserControllerTest.php
```

teste le contrôleur :

```text
src/Controller/UserController.php
```

Les scénarios couvrent l'accès à la gestion des utilisateurs selon le rôle de l'utilisateur connecté.

---

## Utilisateur non authentifié

Un utilisateur non connecté tente d'accéder à :

```text
/users
```

Le comportement attendu est une redirection vers :

```text
/login
```

Le test vérifie donc que l'accès aux fonctionnalités de gestion des utilisateurs nécessite une authentification.

---

# Utilisateur standard

Un utilisateur possédant :

```text
ROLE_USER
```

se connecte puis tente d'accéder à :

```text
/users
```

L'accès doit être refusé.

Le résultat attendu est :

```text
403 Forbidden
```

Ce test permet de vérifier la règle :

```text
ROLE_USER
    ↓
Gestion des utilisateurs
    ↓
DENIED
```

---

# Administrateur

Un utilisateur possédant :

```text
ROLE_ADMIN
```

se connecte puis accède à :

```text
/users
```

L'accès doit être autorisé.

Le test vérifie :

```php
$this->assertResponseIsSuccessful();
```

Le scénario est donc :

```text
ROLE_ADMIN
    ↓
/users
    ↓
200 OK
```

La gestion des utilisateurs est ainsi réservée aux administrateurs conformément au besoin fonctionnel.

---

# Validation des règles d'autorisation

Les tests fonctionnels permettent de vérifier que les règles définies précédemment dans le `TaskVoter` sont bien prises en compte lors de véritables requêtes HTTP.

La chaîne complète est ainsi testée :

```text
Requête HTTP
     ↓
Contrôleur
     ↓
denyAccessUnlessGranted()
     ↓
TaskVoter
     ↓
Décision d'autorisation
     ↓
Réponse HTTP
```

Cela complète les tests unitaires du `TaskVoter`.

Les tests unitaires vérifient :

```text
TaskVoter
    ↓
Règle métier
```

Les tests fonctionnels vérifient :

```text
Requête HTTP
    ↓
Contrôleur
    ↓
TaskVoter
    ↓
Réponse HTTP
```

Les deux niveaux de tests sont donc complémentaires.

---

# Résultat de la suite fonctionnelle

Les tests fonctionnels ont été exécutés avec :

```bash
APP_ENV=test vendor/bin/phpunit
```

Les tests utilisent :

```text
PHP 8.2.33
PHPUnit 11.5.56
```

Le résultat actuel de l'ensemble de la suite PHPUnit est :

```text
55 tests
116 assertions
OK
```

Résultat :

```text
55 / 55 (100 %)
0 échec
0 erreur
```

Tous les tests automatisés passent donc avec succès.

---

# Rapport de couverture

La couverture de code est mesurée avec :

```text
Xdebug 3.5.3
```

La commande utilisée est :

```bash
XDEBUG_MODE=coverage APP_ENV=test vendor/bin/phpunit --coverage-text
```

Le rapport actuel indique :

```text
Classes: 60.00% (6/10)
Methods: 86.96% (40/46)
Lines:   74.02% (151/204)
```

Le critère demandé par le projet est une couverture supérieure à :

```text
70 %
```

L'objectif est donc désormais atteint :

```text
74,02 % > 70 %
```

---

# Couverture des principales classes

La couverture actuelle permet notamment de constater que les classes principales sont correctement testées.

| Classe                              | Méthodes |  Lignes |
| ----------------------------------- | -------: | ------: |
| `App\Controller\SecurityController` |    100 % |   100 % |
| `App\Controller\TaskController`     |     60 % | 61,36 % |
| `App\Controller\UserController`     |    100 % |   100 % |
| `App\Entity\Task`                   |    100 % |   100 % |
| `App\Entity\User`                   |    100 % |   100 % |
| `App\Form\TaskType`                 |    100 % |   100 % |
| `App\Form\UserType`                 |    100 % |   100 % |
| `App\Security\Voter\TaskVoter`      |     50 % | 92,86 % |

La couverture des lignes de :

```text
74,02 %
```

répond donc à l'objectif fixé par le projet.

---

# Évolution de la couverture

Une première mesure avait été réalisée après la mise en place des tests unitaires.

Elle indiquait :

```text
34,12 % des lignes
```

Après l'ajout des tests fonctionnels, la couverture atteint :

```text
74,02 % des lignes
```

L'évolution est donc :

```text
34,12 %
   ↓
74,02 %
```

Soit une augmentation de :

```text
+39,90 points
```

Cette progression montre l'intérêt des tests fonctionnels pour couvrir les contrôleurs et les différents parcours utilisateurs.

---

# Génération du rapport HTML

Le rapport HTML de couverture est généré avec :

```bash
XDEBUG_MODE=coverage APP_ENV=test \
vendor/bin/phpunit --coverage-html var/coverage
```

Le rapport est généré dans :

```text
var/coverage/
```

Le fichier principal est :

```text
var/coverage/index.html
```

Le rapport peut être consulté localement après avoir démarré un serveur HTTP sur le répertoire de couverture.

Par exemple :

```bash
php -S 0.0.0.0:5500 -t var/coverage
```

Le rapport est alors accessible depuis le navigateur à l'adresse correspondant au serveur local.

Le rapport HTML actuel affiche une couverture de :

```text
74,02 %
```

---

# Gestion du répertoire de couverture

Le répertoire :

```text
var/coverage/
```

est généré automatiquement par PHPUnit.

Il ne doit pas être versionné dans Git.

Le `.gitignore` du projet contient déjà une règle permettant d'ignorer le contenu de `var/` :

```gitignore
/var/*
```

La commande :

```bash
git check-ignore -v var/coverage/index.html
```

confirme que le rapport est bien ignoré par Git.

Le rapport peut donc être supprimé et régénéré à tout moment sans modifier le dépôt.

L'ancien répertoire :

```text
coverage/
```

qui était précédemment versionné a été supprimé du projet. Les fichiers HTML générés automatiquement ne sont ainsi plus conservés dans le dépôt.

---

# Vérifications réalisées

Les tests fonctionnels ont permis de vérifier notamment :

- l'accès anonyme aux pages protégées ;
- l'authentification des utilisateurs ;
- l'accès d'un utilisateur standard ;
- l'accès d'un administrateur ;
- l'accès aux tâches ;
- la modification des tâches ;
- le changement d'état des tâches ;
- la suppression des tâches ;
- les interdictions liées à la propriété d'une tâche ;
- la gestion des tâches `anonymous` ;
- les droits spécifiques des administrateurs ;
- la persistance effective des modifications en base de données.

---

# Tests unitaires et tests fonctionnels : complémentarité

La stratégie de tests du projet repose désormais sur deux niveaux.

## Tests unitaires

Les tests unitaires vérifient principalement les règles métier isolées :

```text
Entity
   ↓
User / Task

Security
   ↓
TaskVoter
```

Ils permettent de vérifier rapidement le comportement des classes indépendamment de l'application complète.

## Tests fonctionnels

Les tests fonctionnels vérifient les parcours à travers l'application :

```text
Client HTTP
    ↓
Route
    ↓
Controller
    ↓
Security / Voter
    ↓
Doctrine
    ↓
Réponse HTTP
```

Ils permettent donc de détecter des problèmes d'intégration entre plusieurs composants.

---

# Résultat final

La suite automatisée présente actuellement :

```text
55 tests
116 assertions
0 échec
0 erreur
```

La couverture obtenue est :

```text
74,02 % des lignes
```

L'objectif du projet :

```text
> 70 %
```

est donc atteint.

Les tests couvrent désormais les principales fonctionnalités et règles de sécurité de l'application, notamment :

- l'authentification ;
- la gestion des utilisateurs ;
- les rôles `ROLE_USER` et `ROLE_ADMIN` ;
- la gestion des tâches ;
- l'association des tâches à leur propriétaire ;
- la modification des tâches ;
- le changement d'état ;
- la suppression des tâches ;
- les tâches `anonymous` ;
- les autorisations associées aux différents profils.

---

# Conclusion

L'ajout des tests fonctionnels complète les tests unitaires précédemment mis en place.

Les tests unitaires permettent de sécuriser les règles métier isolées tandis que les tests fonctionnels vérifient leur bon fonctionnement à travers les contrôleurs et les requêtes HTTP.

La suite PHPUnit compte désormais :

```text
55 tests
116 assertions
0 échec
0 erreur
```

Tous les tests passent avec succès.

La couverture de code est passée de :

```text
34,12 %
```

après l'implémentation initiale des tests unitaires à :

```text
74,02 %
```

après l'ajout des tests fonctionnels.

L'objectif fixé par le projet, à savoir une couverture supérieure à :

```text
70 %
```

est donc atteint.

Le rapport HTML généré avec Xdebug constitue désormais un élément de preuve permettant de documenter ce niveau de couverture.

Les tests automatisés offrent ainsi une base plus fiable pour poursuivre les évolutions de ToDo & Co et limiter les risques de régression lors des prochaines modifications.
