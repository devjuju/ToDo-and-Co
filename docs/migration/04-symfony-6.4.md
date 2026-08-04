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

## Modernisation des contrôleurs

Plusieurs composants historiques ont été remplacés afin d'être compatibles avec Symfony 6.

### Gestion des formulaires

Les formulaires utilisent désormais :

```php
if ($form->isSubmitted() && $form->isValid())
```

Cette vérification est obligatoire avant l'appel à `isValid()`.

---

### Hachage des mots de passe

Le service historique :

```php
UserPasswordEncoderInterface
```

a été remplacé par :

```php
UserPasswordHasherInterface
```

avec :

```php
$passwordHasher->hashPassword(...)
```

conformément aux recommandations de Symfony 6.

---

## Adaptation de Doctrine

La migration vers Symfony 6.4 a également nécessité une mise à jour de l'utilisation de Doctrine.

### Suppression des alias d'entités

Les alias historiques utilisés par Doctrine ne sont plus compatibles avec `doctrine/persistence` 3.

Ancien format :

```php
$this->getDoctrine()->getRepository('AppBundle:User');
```

Nouveau format :

```php
$entityManager->getRepository(User::class);
```

La même modification a été réalisée pour l'entité Task.

---

### Remplacement de getDoctrine()

La méthode :

```php
$this->getDoctrine()
```

n'est plus disponible dans les contrôleurs.

Les contrôleurs utilisent désormais l'injection de dépendances avec :

```php
Doctrine\ORM\EntityManagerInterface
```

Cette évolution est conforme aux bonnes pratiques recommandées par Symfony.

---

## Adaptation de l'entité User

Symfony 6 impose plusieurs évolutions de l'entité utilisateur.

L'entité implémente désormais :

```php
PasswordAuthenticatedUserInterface
```

La déclaration devient :

```php
class User implements UserInterface, PasswordAuthenticatedUserInterface
```

Une nouvelle méthode a été ajoutée :

```php
public function getUserIdentifier(): string
```

afin de remplacer progressivement l'utilisation de `getUsername()`.

Une propriété destinée au stockage des rôles a également été ajoutée :

```php
/**
 * @ORM\Column(type="json")
 */
private $roles = [];
```

La méthode `getRoles()` retourne désormais un tableau et garantit la présence du rôle `ROLE_USER`.

---

## Synchronisation du schéma Doctrine

Après la migration, Doctrine a détecté une différence entre les entités et le schéma de base de données.

La synchronisation a été réalisée avec :

```bash
php bin/console doctrine:schema:update --force --complete
```

La commande :

```bash
php bin/console doctrine:schema:validate
```

retourne désormais :

```text
Mapping OK
Database OK
```

confirmant que les entités et la base de données sont synchronisées.

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

> une configuration Security compatible Symfony 6.

En effet, l'authentification a été **corrigée pendant la validation fonctionnelle**, pas pendant la migration elle-même.

L'application fonctionne désormais avec :

- Symfony 6.4.43 LTS ;
- PHP compatible Symfony 6 ;
- Doctrine compatible avec les nouvelles versions ;
- une configuration Security compatible Symfony 6 ;
- un schéma Doctrine synchronisé ;
- les routes principales chargées.

L'architecture Symfony Standard Edition est volontairement conservée temporairement afin de limiter les risques avant la modernisation de la structure du projet.

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

Cette étape a permis de rendre l'application compatible avec Symfony 6.4 en adaptant le Kernel, Doctrine, Security, les contrôleurs et l'entité `User`.

Une validation fonctionnelle complète a ensuite été réalisée afin de vérifier le bon fonctionnement de l'application. Les résultats de cette phase sont présentés dans le document `05-validation-6.4.md`.

La prochaine étape sera consacrée à la modernisation de l'architecture vers les standards Symfony actuels.
