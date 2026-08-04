# 🚀 Migration Symfony 6.4 → Symfony 7.4 LTS

## Objectif

Cette étape consiste à migrer l'application ToDo & Co de Symfony 6.4 LTS vers Symfony 7.4 LTS.

Symfony 7.4 est une version LTS permettant de bénéficier d'un support long terme tout en utilisant les composants Symfony les plus récents.

Cette migration constitue la dernière étape de modernisation du framework avant la migration complète de l'architecture historique Symfony Standard Edition vers une structure Symfony Flex moderne.

---

## Pourquoi Symfony 7.4 ?

Symfony 7.4 LTS apporte notamment :

- un support prolongé jusqu'en 2032 ;
- une compatibilité avec les versions récentes de PHP ;
- la suppression de nombreuses API dépréciées ;
- des composants Symfony maintenus à long terme ;
- des améliorations de performances et de sécurité.

Le passage par Symfony 7.4 permet de disposer d'une base moderne avant de poursuivre la migration architecturale du projet.

---

## État initial

Avant la migration, l'application fonctionnait sous :

| Élément      |             Version |
| ------------ | ------------------: |
| Symfony      |             6.4 LTS |
| PHP          |                 8.2 |
| Doctrine ORM |                 2.x |
| Twig         |                 3.x |
| MySQL        |                 5.7 |
| Docker       | Environnement local |

Vérification réalisée :

```bash
docker compose exec php php bin/console --version
```

Résultat :

```text
Symfony 6.4.x (env: dev, debug: true)
```

---

## Préparation de la migration

Le fichier `composer.json` a été adapté afin de cibler Symfony 7.4.

La contrainte principale a été modifiée :

```json
"symfony/symfony": "7.4.*"
```

Le projet utilise également :

```json
"php": "^8.2"
```

Symfony 7.4 nécessite une version récente de PHP. L'environnement Docker a donc été conservé sur PHP 8.2.

---

## Mise à jour Composer

La mise à jour des dépendances a été effectuée avec :

```bash
composer update -W
```

L'option `-W` permet d'autoriser la mise à jour des dépendances liées afin de résoudre les contraintes de versions entre les composants Symfony.

Après installation :

```bash
php bin/console --version
```

Résultat :

```text
Symfony 7.4.x (env: dev, debug: true)
```

---

## Adaptation du Kernel Symfony

Le projet utilisant encore la structure Symfony Standard Edition, le fichier historique :

```text
app/AppKernel.php
```

a dû être adapté aux nouvelles signatures PHP imposées par Symfony 7.4.

Les méthodes du Kernel utilisent désormais les types de retour attendus :

```php
public function getProjectDir(): string
```

Cette modification permet de respecter les signatures définies dans :

```text
Symfony\Component\HttpKernel\Kernel
```

---

## Mise à jour de la configuration Security

La configuration de sécurité historique Symfony 3/4 utilisait :

```yaml
encoders:
```

Cette syntaxe a été remplacée par :

```yaml
password_hashers:
```

La configuration actuelle utilise :

```yaml
security:
  password_hashers:
    AppBundle\Entity\User:
      algorithm: bcrypt
```

Les accès au profiler Symfony ont également été autorisés en environnement de développement :

```yaml
access_control:
  - { path: ^/_wdt, roles: PUBLIC_ACCESS }
  - { path: ^/_profiler, roles: PUBLIC_ACCESS }
```

---

## Correction de l'entité User

Symfony 7 impose une nouvelle signature pour l'interface :

```php
Symfony\Component\Security\Core\User\UserInterface
```

La méthode historique :

```php
getRoles()
```

a été adaptée :

```php
public function getRoles(): array
```

Une méthode compatible avec Symfony 7 a également été ajoutée :

```php
public function getUserIdentifier(): string
```

Cette méthode remplace progressivement l'ancien mécanisme basé sur :

```php
getUsername()
```

---

## Correction du Web Profiler

Après migration, une erreur apparaissait lors du chargement des routes du profiler :

```text
Unable to find file
@WebProfilerBundle/Resources/config/routes/wdt.xml
```

La cause était liée au changement d'organisation interne de `web-profiler-bundle` dans Symfony 7.4.

Après nettoyage :

```bash
rm -rf var/cache/*
composer dump-autoload
php bin/console cache:clear --env=dev
```

Les routes du profiler sont redevenues disponibles :

```bash
php bin/console debug:router | grep wdt
```

Résultat :

```text
_wdt_stylesheet
_wdt
```

La Symfony Web Debug Toolbar fonctionne désormais correctement.

---

## Nettoyage du cache

Après migration :

```bash
rm -rf var/cache/*
```

Puis :

```bash
php bin/console cache:clear
```

Résultat :

```text
[OK] Cache for the "dev" environment was successfully cleared.
```

---

## Vérifications finales

### Version Symfony

Commande :

```bash
php bin/console --version
```

Résultat :

```text
Symfony 7.4.x (env: dev, debug: true)
```

---

### Routes

Commande :

```bash
php bin/console debug:router
```

Résultat :

Les routes principales sont présentes :

- authentification ;
- tâches ;
- utilisateurs ;
- Web Profiler.

---

### Doctrine

Commande :

```bash
php bin/console doctrine:schema:validate
```

Résultat attendu :

```text
[OK] The mapping files are correct.

[OK] The database schema is in sync with the mapping files.
```

---

### Web Profiler

La barre Symfony est visible sur les pages en environnement développement.

Vérification :

```text
http://localhost:8267/app_dev.php/login
```

Résultat :

- page accessible ;
- toolbar Symfony affichée ;
- profiler accessible.

---

## Résultat

La migration Symfony 6.4 → Symfony 7.4 LTS est terminée.

L'application fonctionne désormais avec :

- Symfony 7.4 LTS ;
- PHP 8.2 ;
- une authentification compatible Symfony moderne ;
- WebProfiler opérationnel ;
- Doctrine fonctionnel ;
- les fonctionnalités historiques conservées.

---

## Travaux restant à réaliser

La prochaine étape concerne la modernisation de l'architecture :

- migration de `app/` vers `config/` ;
- migration de `AppBundle` vers `src/` ;
- déplacement des templates vers `templates/` ;
- migration vers une structure Symfony Flex ;
- suppression progressive des éléments historiques Symfony Standard Edition.

---

## Conclusion

La migration du framework Symfony vers Symfony 7.4 LTS est terminée.

L'application dispose maintenant d'une base technique moderne permettant d'engager la dernière phase de modernisation : la migration complète de l'architecture Symfony Standard Edition vers Symfony Flex.
