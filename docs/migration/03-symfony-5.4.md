# 🚀 Migration Symfony 4.4 → Symfony 5.4 LTS

## Objectif

Cette étape consiste à migrer l'application ToDo & Co de Symfony 4.4.51 LTS vers Symfony 5.4.53 LTS.

Symfony 5.4 est la dernière version LTS de la branche Symfony 5.x.

Cette migration constitue une étape intermédiaire avant les futures migrations vers :

- Symfony 6.4 LTS ;
- Symfony 7.4.

L'objectif est de continuer la modernisation progressive de l'application tout en conservant son fonctionnement existant.

---

## Pourquoi Symfony 5.4 ?

Symfony 5.4 est une version LTS permettant de préparer une migration vers Symfony 6.x.

Cette version apporte notamment :

- une meilleure compatibilité avec les versions récentes de PHP ;
- la suppression progressive des anciennes API dépréciées ;
- une transition facilitée vers Symfony 6 ;
- des améliorations de maintenance et de sécurité.

Le passage progressif par Symfony 5.4 permet d'identifier les dernières incompatibilités avant la migration majeure vers Symfony 6.4.

---

## État initial

Avant la migration, l'application fonctionnait sous :

| Élément      |             Version |
| ------------ | ------------------: |
| Symfony      |              4.4.51 |
| PHP          |              7.4.33 |
| Doctrine ORM |                 2.x |
| Twig         |                 2.x |
| MySQL        |                 5.7 |
| Docker       | Environnement local |

Vérification réalisée :

```bash
docker compose exec php php bin/console --version
```

Résultat :

```text
Symfony 4.4.51 (env: dev, debug: true)
```

La base de données a été vérifiée avant migration :

```bash
php bin/console doctrine:schema:validate
```

Résultat :

```text
[OK] The mapping files are correct.

[OK] The database schema is in sync with the mapping files.
```

---

## Préparation de la migration

Le fichier `composer.json` a été adapté afin de cibler Symfony 5.4 LTS.

Les principales dépendances Symfony ont été mises à jour.

| Dépendance     |  Avant |  Après |
| -------------- | -----: | -----: |
| Symfony        | 4.4.51 | 5.4.53 |
| Twig           |    2.x |    3.x |
| Doctrine ORM   |    2.x |    2.x |
| PHPUnit Bridge |  4.4.x |  5.4.x |

Les contraintes Symfony ont été modifiées pour utiliser la branche 5.4 :

```JSON
"symfony/symfony": "5.4.*"
```

---

## Mise à jour Composer

Une mise à jour des dépendances a été réalisée :

```bash
composer update --no-scripts
```

L'utilisation de l'option `--no-scripts` permet d'éviter l'exécution automatique de scripts Symfony incompatibles avec certaines anciennes configurations du projet.

Après installation :

```bash
php bin/console --version
```

Retour :

```text
Symfony 5.4.53 (env: dev, debug: true)
```

## Correction de la configuration Twig

Après la migration, l'application démarrait mais la page de connexion provoquait une erreur :

```text
Unable to find template "security/login.html.twig"
```

Le fichier existait pourtant :

```text
app/Resources/views/security/login.html.twig
```

La cause était liée au changement de comportement du chargement des templates Twig dans Symfony 5.4.

L'application utilise encore l'ancienne structure Symfony Standard Edition :

```text
app/
 └── Resources/
      └── views/
```

Le chemin des templates a donc été déclaré explicitement dans :

```text
app/config/config.yml
```

Ajout :

```YAML
twig:
  debug: "%kernel.debug%"
  strict_variables: "%kernel.debug%"
  paths:
    "%kernel.project_dir%/app/Resources/views": ~
```

Après suppression du cache :

```bash
rm -rf var/cache/*
```

Puis :

```text
php bin/console cache:clear
```

Résultat :

```text
[OK] Cache for the "dev" environment (debug=true) was successfully cleared.
```

La page de connexion est ensuite redevenue accessible :

```text
http://localhost:8267/app_dev.php/login
```

---

## Vérification du chargement Twig

La commande suivante permet de vérifier les chemins connus par Twig :

```bash
php bin/console debug:twig
```

Résultat :

```text
Loader Paths

(None) app/Resources/views/
```

Les templates historiques Symfony Standard sont désormais correctement détectés.

---

## Vérification du Kernel

Le projet utilise toujours l'ancien Kernel Symfony Standard :

```text
app/AppKernel.php
```

La présence des bundles principaux a été vérifiée :

```PHP
new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
new Symfony\Bundle\SecurityBundle\SecurityBundle(),
new Symfony\Bundle\TwigBundle\TwigBundle(),
new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
new AppBundle\AppBundle(),
```

Aucune modification supplémentaire du Kernel n'a été nécessaire.

---

## Nettoyage des caches

Après modification de la configuration :

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
Symfony 5.4.53 (env: dev, debug: true)
```

---

### Vérification Doctrine

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

### Vérification des entités

Commande :

```bash
php bin/console doctrine:mapping:info
```

Résultat :

```text
Found 2 mapped entities:

[OK] AppBundle\Entity\User
[OK] AppBundle\Entity\Task
```

---

### Vérification de l'application

La page de connexion est accessible :

```text
http://localhost:8267/app_dev.php/login
```

Les fonctionnalités principales peuvent être testées :

- authentification utilisateur ;
- accès aux tâches ;
- accès aux utilisateurs ;
- création et modification des données.

---

## Audit Composer

Une vérification des dépendances a été réalisée :

```bash
composer validate
```

Résultat :

Le fichier `composer.json` est valide pour l'utilisation du projet.

Quelques avertissements persistent :

- absence de description du package ;
- namespace PSR-4 historique ;
- contrainte de version non définie sur `symfony/error-handler`.

Ces éléments ne bloquent pas l'application et pourront être traités lors d'une modernisation ultérieure.

---

Une analyse de sécurité a également été effectuée :

```bash
composer audit
```

Des vulnérabilités concernent principalement :

- `twig/twig` ;
- plusieurs dépendances historiques abandonnées.

Ces corrections feront l'objet d'une amélioration dédiée afin de ne pas mélanger la migration Symfony avec une refonte complète des dépendances.

---

## Résultat

La migration Symfony 4.4 → Symfony 5.4 LTS est terminée.

L'application fonctionne désormais avec :

- Symfony 5.4.53 ;
- une base de données opérationnelle ;
- les entités Doctrine conservées ;
- les templates Twig correctement chargés ;
- la page de connexion fonctionnelle ;
- une architecture Symfony Standard conservée temporairement.

---

## Captures d'écran

Les captures réalisées pendant cette étape sont disponibles dans :

```text
docs/migration/screenshots/03-symfony-5.4/
```

---

## Travaux restant à réaliser

Les prochaines étapes permettront de :

- moderniser progressivement l'architecture Symfony ;
- migrer vers Symfony 6.4 LTS ;
- remplacer les composants obsolètes ;
- migrer vers une structure Symfony Flex (`config/`, `public/`, `templates/`).

---

## Conclusion

La migration Symfony 4.4 vers Symfony 5.4 LTS est terminée.

Cette étape a permis de conserver la stabilité de l'application tout en préparant la future migration vers Symfony 6.4.

Le projet dispose maintenant d'une base compatible avec les versions modernes de Symfony.
