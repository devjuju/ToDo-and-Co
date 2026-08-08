# 👤 Gestion des rôles utilisateurs

## Contexte et objectif

L'application ToDo & Co doit permettre de différencier les utilisateurs selon leur niveau d'autorisation.

Deux rôles sont utilisés :

- `ROLE_USER` : utilisateur standard ;
- `ROLE_ADMIN` : administrateur.

Cette évolution répond à deux besoins :

- permettre de choisir le rôle d'un utilisateur lors de sa création ;
- permettre de modifier le rôle d'un utilisateur existant.

La gestion des rôles constitue également un prérequis pour la sécurisation de l'application.

Elle permet notamment de réserver les pages de gestion des utilisateurs aux administrateurs et sera utilisée par la suite pour sécuriser certaines actions sur les tâches.

---

## Stockage des rôles dans User

Les utilisateurs sont stockés dans la table :

```text
user
```

La propriété `roles` de l'entité `User` est stockée au format JSON :

```php
#[ORM\Column(type: 'json')]
private $roles = [];
```

Le rôle est donc enregistré en base sous la forme d'un tableau JSON.

Par exemple :

```json
["ROLE_USER"]
```

ou :

```json
["ROLE_ADMIN"]
```

La valeur par défaut de la propriété est un tableau vide :

```php
private $roles = [];
```

Cependant, Symfony doit toujours disposer d'au moins un rôle utilisateur.

La méthode `getRoles()` garantit donc la présence de `ROLE_USER` :

```php
public function getRoles(): array
{
$roles = $this->roles;

    if (!in_array('ROLE_USER', $roles, true)) {
        $roles[] = 'ROLE_USER';
    }

    return array_unique($roles);

}
```

Ainsi, un utilisateur ne possédant aucun rôle explicitement enregistré est considéré comme un utilisateur standard.

Par exemple, si la base contient :

```json
[]
```

getRoles() retournera :

```php
['ROLE_USER']
```

Cette logique permet notamment de conserver la compatibilité avec les utilisateurs historiques de l'application.

---

## Implémentation de UserInterface

L'entité `User` implémente les interfaces nécessaires à l'authentification Symfony :

```php
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
```

La classe utilise donc :

```php
class User implements UserInterface, PasswordAuthenticatedUserInterface
```

---

### Identification de l'utilisateur

Symfony utilise `getUserIdentifier()` pour identifier l'utilisateur connecté :

```php
public function getUserIdentifier(): string
{
return (string) $this->username;
}
```

Dans l'application, le nom d'utilisateur (`username`) constitue donc l'identifiant utilisé par Symfony Security.

---

### Récupération des rôles

Symfony récupère les rôles de l'utilisateur avec :

```php
public function getRoles(): array
```

Cette méthode est utilisée par le système de sécurité Symfony pour déterminer les autorisations de l'utilisateur.

---

### Mot de passe

L'interface `PasswordAuthenticatedUserInterface` nécessite également que l'entité fournisse le mot de passe :

```php
public function getPassword(): string
{
return $this->password;
}
```

Le mot de passe est ensuite hashé par Symfony lors de la création ou de la modification d'un utilisateur.

---

### Effacement des informations sensibles

L'entité implémente également :

```php
public function eraseCredentials(): void {}
```

Cette méthode permet à Symfony d'effacer d'éventuelles données sensibles temporaires après l'authentification.

---

## Formulaire UserType

Le formulaire de gestion des utilisateurs se trouve dans :

```text
src/Form/UserType.php
```

Le champ permettant de choisir le rôle utilise `ChoiceType` :

```php
->add('roles', ChoiceType::class, [
'label' => 'Rôle',
'choices' => [
'Utilisateur' => 'ROLE_USER',
'Administrateur' => 'ROLE_ADMIN',
],
'multiple' => true,
])
```

Les valeurs présentées à l'utilisateur sont :

| Libellé        | Valeur enregistrée |
| -------------- | ------------------ |
| Utilisateur    | `ROLE_USER`        |
| Administrateur | `ROLE_ADMIN`       |

Le formulaire utilise actuellement une sélection multiple afin de rester compatible avec le fonctionnement standard des rôles Symfony, qui sont représentés sous forme de tableau.

La valeur sélectionnée est ensuite automatiquement associée à la propriété `roles` de l'entité `User`.

---

## Création d'un utilisateur avec un rôle

La création d'un utilisateur est gérée par :

```text
src/Controller/UserController.php
```

Le contrôleur crée une nouvelle instance de `User` :

```php
$user = new User();
```

Puis construit le formulaire :

```php
$form = $this->createForm(UserType::class, $user);
```

Après soumission :

```php
$form->handleRequest($request);
```

Symfony hydrate automatiquement l'entité `User` avec les valeurs envoyées par le formulaire.

Lorsque le formulaire est valide, le mot de passe est hashé :

```php
$password = $passwordHasher->hashPassword(
$user,
$user->getPassword()
);

$user->setPassword($password);
```

L'utilisateur est ensuite enregistré :

```php
$entityManager->persist($user);
$entityManager->flush();
```

Le rôle sélectionné est donc également enregistré dans la colonne `roles`.

### Exemple

Si l'administrateur sélectionne :

```text
Administrateur
```

la base contient :

```json
["ROLE_ADMIN"]
```

Si l'administrateur sélectionne :

```text
Utilisateur
```

la base contient :

```json
["ROLE_USER"]
```

---

## Modification du rôle

La modification d'un utilisateur est également gérée dans :

```text
src/Controller/UserController.php
```

Le contrôleur récupère l'utilisateur concerné :

```php
#[Route('/users/{id}/edit', name: 'user_edit')]
public function editAction(
User $user,
Request $request,
UserPasswordHasherInterface $passwordHasher,
EntityManagerInterface $entityManager
)
```

Le formulaire est ensuite créé à partir de cet utilisateur :

```php
$form = $this->createForm(UserType::class, $user);
```

Symfony récupère ainsi les données existantes de l'utilisateur et pré-sélectionne le rôle actuellement enregistré.

Après modification et soumission du formulaire :

```php
$form->handleRequest($request);
```

le nouvel état de l'utilisateur est enregistré avec :

```php
$entityManager->flush();
```

### Exemple de vérification

Le rôle de `manager` a été modifié de :

```json
["ROLE_ADMIN"]
```

vers :

```json
["ROLE_USER"]
```

Puis de nouveau vers :

```json
["ROLE_ADMIN"]
```

Les deux modifications ont été correctement enregistrées en base.

---

## Restriction des pages utilisateurs à `ROLE_ADMIN`

Les pages de gestion des utilisateurs doivent être accessibles uniquement aux administrateurs.

Cette règle est définie dans :

```text
config/packages/security.yaml
```

avec :

```yaml
access_control:
  - { path: ^/login, roles: PUBLIC_ACCESS }
  - { path: ^/users, roles: ROLE_ADMIN }
  - { path: ^/, roles: ROLE_USER }
```

La règle :

```yaml
- { path: ^/users, roles: ROLE_ADMIN }
```

signifie que toutes les URL commençant par `/users` nécessitent le rôle :

```text
ROLE_ADMIN
```

Cela protège notamment :

```text
/users
/users/create
/users/{id}/edit
```

### Exemple

Un utilisateur possédant uniquement :

```json
["ROLE_USER"]
```

qui tente d'accéder à :

```text
/users/create
```

obtient une réponse :

```text
Access Denied
```

À l'inverse, un utilisateur possédant :

```json
["ROLE_ADMIN"]
```

peut accéder aux pages de gestion des utilisateurs.

---

## Vérifications en base

Les rôles peuvent être vérifiés directement dans MySQL.

Connexion au conteneur MySQL :

```bash
docker compose exec mysql mysql -u root -p
```

Puis :

```sql
USE todolist;
```

Pour afficher les rôles :

```sql
SELECT username, roles
FROM user;
```

Exemple :

+-----------+----------------+
| username | roles |
+-----------+----------------+
| test | ["ROLE_USER"] |
| admin | ["ROLE_ADMIN"] |
| anonymous | ["ROLE_USER"] |
| manager | ["ROLE_ADMIN"] |
+-----------+----------------+

Il est également possible de vérifier un utilisateur spécifique :

```sql
SELECT username, roles
FROM user
WHERE username = 'manager';
```

Cette vérification permet de confirmer que le rôle sélectionné dans le formulaire a bien été persisté.

---

## Tests fonctionnels réalisés

Plusieurs vérifications fonctionnelles ont été effectuées.

### Accès d'un utilisateur standard

L'utilisateur `test`, possédant :

```json
["ROLE_USER"]
```

a tenté d'accéder à :

```text
/users/create
```

Résultat :

```
Access Denied
```

Le contrôle d'accès fonctionne donc correctement.

---

### Accès d'un administrateur

L'utilisateur `admin`, possédant :

```json
["ROLE_ADMIN"]
```

peut accéder aux pages :

```text
/users
/users/create
/users/{id}/edit
```

---

### Création avec `ROLE_ADMIN`

Depuis le formulaire de création, le rôle :

```text
Administrateur
```

a été sélectionné.

Après enregistrement :

```sql
SELECT username, roles
FROM user
WHERE username = 'manager';
```

Résultat :

```text
manager | ["ROLE_ADMIN"]
```

---

### Modification du rôle

Le rôle de `manager` a été modifié :

```text
ROLE_ADMIN → ROLE_USER
```

Puis vérifié en base :

```text
manager | ["ROLE_USER"]
```

Le rôle a ensuite été modifié à nouveau :

```text
ROLE_USER → ROLE_ADMIN
```

Résultat :

```text
manager | ["ROLE_ADMIN"]
```

Le rôle actuellement enregistré est également pré-sélectionné dans le formulaire d'édition.

---

## Choix techniques

### Pourquoi stocker les rôles en JSON ?

Symfony représente naturellement les rôles sous forme de tableau :

```php
['ROLE_USER']
```

ou :

```php
['ROLE_ADMIN']
```

Le stockage JSON permet donc de conserver directement cette structure dans la base de données.

---

### Pourquoi conserver `getRoles()` ?

`getRoles()` est une méthode imposée par `UserInterface`.

Symfony Security l'utilise pour connaître les rôles de l'utilisateur authentifié et prendre les décisions d'autorisation.

La méthode garantit également la présence de `ROLE_USER` :

```php
if (!in_array('ROLE_USER', $roles, true)) {
    $roles[] = 'ROLE_USER';
}
```

Ainsi, les utilisateurs historiques ne possédant pas explicitement de rôle restent considérés comme des utilisateurs standards.

---

### Pourquoi ne pas gérer les autorisations directement dans les contrôleurs ?

Les règles générales d'accès aux pages sont centralisées dans :

```text
config/packages/security.yaml
```

Par exemple :

```yaml
- { path: ^/users, roles: ROLE_ADMIN }
```

Cette approche évite de dupliquer les contrôles de rôle dans chaque méthode du contrôleur.

Pour les règles plus spécifiques concernant les tâches, une approche basée sur un Symfony Voter sera utilisée dans l'étape suivante.

---

### Fichiers concernés

La gestion des rôles repose principalement sur les fichiers suivants :

```text
src/Entity/User.php
src/Form/UserType.php
src/Controller/UserController.php
config/packages/security.yaml
```

---

#### `src/Entity/User.php`

Définit :

- les rôles de l'utilisateur ;
- leur stockage ;
- `getRoles()` ;
- `setRoles()` ;
- l'identification de l'utilisateur pour Symfony Security.

#### `src/Form/UserType.php`

Définit le champ permettant de sélectionner :

- `ROLE_USER` ;
- `ROLE_ADMIN`.

---

#### `src/Controller/UserController.php`

Gère :

- la création des utilisateurs ;
- la modification des utilisateurs ;
- la persistance des données.

---

#### `config/packages/security.yaml`

Définit notamment :

- les utilisateurs authentifiés ;
- les firewalls ;
- les règles d'accès ;
- la restriction des pages `/users` à `ROLE_ADMIN`.

---

## Conclusion

La gestion des rôles utilisateurs est désormais fonctionnelle.

L'application permet :

- de définir `ROLE_USER` ou `ROLE_ADMIN` lors de la création d'un utilisateur ;
- de modifier le rôle d'un utilisateur existant ;
- de conserver le rôle en base de données au format JSON ;
- de récupérer les rôles avec `UserInterface` ;
- de réserver les pages de gestion des utilisateurs aux administrateurs.

Les contrôles réalisés ont confirmé que :

- ROLE_USER → accès aux pages utilisateurs refusé
- ROLE_ADMIN → accès aux pages utilisateurs autorisé

La gestion des rôles constitue également un prérequis pour la prochaine étape de sécurisation de l'application.
