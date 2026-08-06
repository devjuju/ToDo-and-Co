# Migration vers une architecture Symfony Flex

## 1. Contexte

L'application ToDo & Co était initialement développée avec Symfony Standard Edition.

Cette architecture correspondait aux anciennes versions de Symfony (3.x) et utilisait une organisation devenue obsolète :

- configuration dans `app/config`
- templates Twig dans `app/Resources/views`
- contrôleur frontal dans `web`
- code applicatif regroupé dans `src/AppBundle`

Après la migration vers Symfony 7.4 LTS, une modernisation progressive de l'architecture a été réalisée afin d'adopter les conventions Symfony Flex.

Cette évolution permet :

- d'améliorer la maintenabilité du projet ;
- de faciliter l'arrivée de nouveaux développeurs ;
- de respecter les standards Symfony actuels ;
- de réduire progressivement la dette technique.

---

# 2. Architecture avant migration

L'organisation historique était :

```text
app/
├── config/
├── Resources/
│   └── views/

src/
└── AppBundle/

web/
├── app.php
└── app_dev.php
```

Cette structure était adaptée aux versions Symfony Standard Edition mais n'est plus utilisée dans les versions modernes.

---

# 3. Architecture cible Symfony Flex

L'application utilise désormais une organisation Symfony Flex :

```text
config/
├── packages/
├── routes/

public/
└── index.php

src/
├── Controller/
├── Entity/
├── Form/
├── Repository/

templates/

tests/

var/

vendor/
```

Cette organisation correspond aux recommandations Symfony actuelles.

---

# 4. Migration réalisée

## 4.1 Migration de la configuration

Ancien emplacement :

```text
app/config/
```

Nouvel emplacement :

```text
config/
```

Les fichiers de configuration Symfony sont maintenant séparés par domaine :

```text
config/packages/
config/routes/
config/services.yaml
```

Les variables spécifiques aux environnements sont gérées via :

```text
.env
.env.test
```

Exemple :

```dotenv
DATABASE_URL="mysql://root:root@mysql:3306/todolist"
```

---

# 4.2 Migration du code applicatif

L'ancien bundle applicatif :

```text
src/AppBundle/
```

a été remplacé progressivement par :

```text
src/
```

Les namespaces ont été modernisés.

Avant :

```php
namespace AppBundle\Entity;
```

Après :

```php
namespace App\Entity;
```

L'autoload Composer utilise maintenant :

```json
{
  "autoload": {
    "psr-4": {
      "App\\": "src/"
    }
  }
}
```

---

# 4.3 Migration des entités Doctrine

Les entités sont maintenant organisées dans :

```text
src/Entity/
```

Exemple :

```text
src/
└── Entity/
    ├── User.php
    └── Task.php
```

Doctrine détecte automatiquement ces entités grâce à la configuration Symfony Flex.

Validation :

```bash
php bin/console doctrine:mapping:info
```

Résultat :

```text
[OK] App\Entity\User
[OK] App\Entity.Task
```

---

# 4.4 Migration des contrôleurs

Les contrôleurs utilisent maintenant :

```text
src/Controller/
```

avec le namespace :

```php
namespace App\Controller;
```

Les routes Symfony sont déclarées avec les attributs PHP :

```php
#[Route('/tasks')]
```

au lieu des anciennes annotations ou fichiers de routing historiques.

---

# 4.5 Migration des formulaires

Les formulaires sont regroupés dans :

```text
src/Form/
```

Exemple :

```text
src/Form/
├── TaskType.php
└── UserType.php
```

Les namespaces ont été adaptés :

Avant :

```php
AppBundle\Form
```

Après :

```php
App\Form
```

---

# 4.6 Migration des templates Twig

Les templates ont été déplacés :

Avant :

```text
app/Resources/views/
```

Après :

```text
templates/
```

Structure actuelle :

```text
templates/
├── task/
├── user/
├── security/
└── base.html.twig
```

La configuration Twig utilise :

```yaml
twig:
  default_path: "%kernel.project_dir%/templates"
```

Vérification :

```bash
php bin/console debug:twig
```

Résultat :

```text
(None) templates/
```

---

# 4.7 Migration des assets

L'ancien dossier :

```text
web/
```

a été remplacé par :

```text
public/
```

Le point d'entrée HTTP Symfony est maintenant :

```text
public/index.php
```

---

# 5. Configuration des tests

La configuration PHPUnit a été adaptée pour Symfony Flex.

Ancienne configuration :

```xml
bootstrap="app/autoload.php"
```

Nouvelle configuration :

```xml
bootstrap="tests/bootstrap.php"
```

Le fichier :

```text
tests/bootstrap.php
```

charge maintenant automatiquement l'environnement Symfony.

Pour permettre les tests fonctionnels :

```yaml
# config/packages/test/framework.yaml

framework:
  test: true
```

---

# 6. Vérifications effectuées

## Cache Symfony

Commande :

```bash
php bin/console cache:clear
```

Résultat :

```text
[OK] Cache successfully cleared.
```

---

## Routing

Commande :

```bash
php bin/console debug:router
```

Résultat :

```text
homepage      /
login         /login
task_list     /tasks
task_create   /tasks/create
task_edit     /tasks/{id}/edit
task_toggle   /tasks/{id}/toggle
task_delete   /tasks/{id}/delete
user_list     /users
user_create   /users/create
user_edit     /users/{id}/edit
logout        /logout
```

Le routing Symfony Flex est fonctionnel.

---

## Doctrine

Commande :

```bash
php bin/console doctrine:schema:validate
```

Résultat :

```text
[OK] The mapping files are correct.

[OK] The database schema is in sync with the mapping files.
```

---

## Composer

Commande :

```bash
composer validate
```

Résultat :

```text
./composer.json is valid
```

---

## Tests automatisés

Commande :

```bash
vendor/bin/phpunit
```

Résultat :

```text
OK (1 test, 2 assertions)
```

Couverture :

```bash
XDEBUG_MODE=coverage vendor/bin/phpunit --coverage-html coverage
```

Le rapport HTML est généré avec succès.

---

# 7. Difficultés rencontrées

## Migration des namespaces

Le remplacement de :

```text
AppBundle
```

par :

```text
App
```

a nécessité l'adaptation de plusieurs fichiers :

- contrôleurs ;
- entités ;
- formulaires ;
- tests ;
- configurations Doctrine.

---

## Compatibilité Symfony 7.4

Certaines configurations historiques Symfony Standard Edition n'étaient plus compatibles :

- ancienne configuration Security ;
- ancien Kernel ;
- ancien système de chargement des templates ;
- ancienne configuration PHPUnit.

Ces éléments ont été adaptés aux conventions Symfony modernes.

---

# 8. Architecture finale

L'application ToDo & Co fonctionne maintenant avec :

- Symfony 7.4 LTS ;
- Symfony Flex ;
- PHP 8.2 ;
- Doctrine ORM 3 ;
- PHPUnit 11 ;
- autoload PSR-4 ;
- configuration par environnement ;
- templates Twig dans `templates/` ;
- contrôleur frontal dans `public/`.

---

# 9. Conclusion

La migration vers Symfony Flex permet de disposer d'une architecture moderne, maintenable et compatible avec les futures évolutions du framework.

Cette étape finalise la modernisation technique commencée avec la migration Symfony 3.x vers Symfony 7.4 LTS.

L'application conserve son comportement fonctionnel tout en adoptant une organisation conforme aux standards actuels Symfony.
