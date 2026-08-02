# 🚀 Migration Symfony 5.4 → Symfony 6.4 LTS

## Objectif

Cette étape consiste à migrer l'application ToDo & Co de Symfony 5.4.53 LTS vers Symfony 6.4.43 LTS.

Symfony 6.4 est une version LTS bénéficiant d'un support long terme. Elle constitue une version stable recommandée avant une future migration vers Symfony 7.4.

Cette migration représente une étape importante dans la modernisation progressive de l'application.

L'objectif est de :

- rendre l'application compatible avec Symfony 6.4 ;
- supprimer les incompatibilités liées aux anciennes versions Symfony ;
- conserver les fonctionnalités existantes ;
- préparer la modernisation complète de l'architecture Symfony.

Cette étape précède la refonte structurelle du projet :

- migration de l'architecture Symfony Standard Edition ;
- suppression progressive de `AppBundle` ;
- adoption d'une organisation Symfony moderne.

---

## Pourquoi Symfony 6.4 ?

Symfony 6.4 est une version LTS permettant de bénéficier :

- d'un support long terme ;
- d'une meilleure compatibilité avec PHP récent ;
- d'améliorations de sécurité ;
- d'une réduction progressive des API obsolètes ;
- d'une préparation vers Symfony 7.x.

La migration vers Symfony 6.4 permet également d'identifier les dernières adaptations nécessaires avant une modernisation complète du projet.

---

## État initial

Avant la migration, l'application fonctionnait sous :

| Élément      |             Version |
| ------------ | ------------------: |
| Symfony      |              5.4.53 |
| PHP          |              7.4.33 |
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
Symfony 5.4.53 (env: dev, debug: true)
```

---

## Préparation de la migration

Le fichier `composer.json` a été adapté afin de cibler Symfony 6.4 LTS.

Les dépendances Symfony principales ont été mises à jour :

| Dépendance   |  Avant |  Après |
| ------------ | -----: | -----: |
| Symfony      | 5.4.53 | 6.4.43 |
| PHP          |    7.4 |    8.x |
| Twig         |    3.x |    3.x |
| Doctrine ORM |    2.x |    2.x |

La contrainte Symfony a été modifiée :

```json
"symfony/symfony": "6.4.*"
```

---

## Mise à jour Composer

La mise à jour des dépendances a été réalisée :

```bash
composer update -W
```

L'option `-W` (`--with-all-dependencies`) permet d'autoriser Composer à mettre également à jour les dépendances liées aux composants Symfony.

Après installation :

```bash
php bin/console --version
```

Résultat :

```text
Symfony 6.4.43 (env: dev, debug: true)
```

---

## Adaptations nécessaires Symfony 6.4

### Adaptation du Kernel

Symfony 6 impose des signatures de méthodes compatibles avec les interfaces PHP modernes.

Modifications réalisées dans :

```text
app/AppKernel.php
```

Ajout des types de retour :

```php
public function getProjectDir(): string
```

```php
public function registerContainerConfiguration(LoaderInterface $loader): void
```

```php
public function registerBundles(): array
```

---

## Migration de la configuration Security

La configuration Symfony Security a évolué.

Ancienne configuration Symfony 5 :

```yaml
encoders:
  AppBundle\Entity\User: bcrypt
```

Nouvelle configuration Symfony 6 :

```yaml
password_hashers:
  AppBundle\Entity\User:
    algorithm: bcrypt
```

La configuration :

```yaml
anonymous: ~
```

a été supprimée.

Symfony 6 utilise désormais :

```yaml
PUBLIC_ACCESS
```

pour les accès anonymes.

---

## Adaptation de l'entité User

Symfony 6 impose une signature compatible avec `UserInterface`.

La méthode :

```php
getRoles()
```

a été adaptée :

```php
public function getRoles(): array
```

Une propriété `roles` a été ajoutée :

```php
/**
 * @ORM\Column(type="json")
 */
private $roles = [];
```

Les utilisateurs peuvent désormais conserver leurs rôles :

- `ROLE_USER`
- `ROLE_ADMIN`

---

## Vérifications finales

### Version Symfony

```bash
php bin/console --version
```

Résultat :

```text
Symfony 6.4.43 (env: dev, debug: true)
```

### Routes

```bash
php bin/console debug:router
```

Les routes principales sont disponibles :

- login ;
- tâches ;
- utilisateurs.

---

## Résultat

La migration Symfony 5.4 → Symfony 6.4 LTS est terminée.

L'application fonctionne désormais avec :

- Symfony 6.4.43 LTS ;
- une authentification opérationnelle ;
- une base Doctrine fonctionnelle ;
- les routes chargées ;
- les fonctionnalités historiques conservées.

L'architecture Symfony Standard Edition est volontairement conservée temporairement afin de limiter les risques.

---

## Travaux restant à réaliser

La prochaine étape consistera à moderniser la structure Symfony :

- remplacement de `AppBundle` ;
- migration vers une architecture Symfony Flex ;
- déplacement des templates ;
- migration annotations → attributs PHP ;
- nettoyage des dépendances obsolètes.

---

## Conclusion

La migration Symfony 5.4 vers Symfony 6.4 LTS est terminée.

Cette étape permet au projet ToDo & Co d'utiliser une version Symfony moderne tout en conservant son fonctionnement.

La prochaine phase sera dédiée à la modernisation complète de l'architecture.
