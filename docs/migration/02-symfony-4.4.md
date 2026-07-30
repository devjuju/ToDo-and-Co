# 🚀 Migration Symfony 3.4 → Symfony 4.4 LTS

## Objectif

Cette étape consiste à migrer l'application ToDo & Co de Symfony 3.4.49 vers Symfony 4.4.51 LTS.

Symfony 4.4 est la dernière version LTS de la branche Symfony 4.x. Cette migration constitue une étape intermédiaire importante avant les futures migrations vers :

- Symfony 5.4 LTS ;
- Symfony 6.4 LTS ;
- Symfony 7.4.

L'objectif est de moderniser progressivement l'application tout en conservant son fonctionnement existant.

---

## Pourquoi Symfony 4.4 ?

Symfony 4.4 est une version LTS bénéficiant d'un support long terme.

Cette version apporte notamment :

- une architecture plus moderne ;
- la préparation aux migrations vers Symfony 5.x ;
- la suppression progressive de plusieurs composants historiques ;
- une meilleure compatibilité avec les versions récentes de PHP.

La migration progressive permet d'identifier et de corriger les incompatibilités étape par étape.

---

## État initial

Avant la migration, l'application fonctionnait sous :

| Élément      |             Version |
| ------------ | ------------------: |
| Symfony      |              3.4.49 |
| PHP          |              7.4.33 |
| Doctrine ORM |               2.6.x |
| Twig         |              1.44.8 |
| MySQL        |                 5.7 |
| Docker       | Environnement local |

Vérification réalisée :

```bash
docker compose exec php php bin/console --version
```

Résultat :

```text
Symfony 3.4.49 (kernel: app, env: dev, debug: true)
```

La base de données a également été vérifiée avant migration :

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

Le fichier composer.json a été adapté afin de cibler Symfony 4.4 LTS.

Principales évolutions :

| Dépendance         |  Avant |  Après |
| ------------------ | -----: | -----: |
| Symfony            | 3.4.49 | 4.4.51 |
| Doctrine ORM       |  2.6.x | 2.20.x |
| Doctrine DBAL      |  2.6.x | 2.13.x |
| Twig               | 1.44.8 | 2.16.x |
| Swiftmailer Bundle |    2.x |  3.5.x |
| Monolog Bundle     |    2.x |  3.8.x |
| PHPUnit Bridge     |    3.x |  4.4.x |

---

## Mise à jour Composer

Une première tentative avec conservation du fichier `composer.lock` a montré des incompatibilités de dépendances.

Erreur rencontrée :

```text
Required package "symfony/symfony" is in the lock file as "v3.4.49"
but that does not satisfy your constraint "4.4.*"
```

Le fichier composer.lock contenant les anciennes versions Symfony 3.4 ne permettait pas de résoudre les nouvelles contraintes.

Une régénération complète des dépendances a donc été nécessaire.

Commandes utilisées :

```bash
rm composer.lock
rm -rf vendor

composer update --no-scripts
```

Cette opération a permis de générer un nouveau fichier `composer.lock` compatible Symfony 4.4.

---

## Adaptation de la configuration Symfony

La migration a révélé plusieurs incompatibilités dans les fichiers de configuration YAML.

### Suppression de `trusted_proxies`

Erreur rencontrée :

```text
Unrecognized option "trusted_proxies" under "framework"
```

La configuration historique Symfony 3.x utilisait :

```YAML
framework:
    trusted_proxies: ~
```

Cette option n'est plus disponible sous cette forme dans Symfony 4.4.

La configuration a été supprimée.

---

### Correction de la configuration `templating`

Erreur rencontrée :

```text
Unrecognized options "default_locale, trusted_hosts, session"
under "framework.templating"
```

Une mauvaise indentation YAML avait placé plusieurs paramètres sous :

```YAML
framework:
    templating:
```

Correction :

Avant :

```YAML
templating:
    engines: ["twig"]
    default_locale: "%locale%"
    session:
```

Après :

```YAML
templating:
    engines: ["twig"]

default_locale: "%locale%"
```

Les paramètres ont été replacés au niveau correct.

---

### Correction Monolog

Erreur rencontrée :

```text
Invalid type for path "monolog.handlers.main.channels.elements.0".
Expected scalar, but got object.
```

Cette erreur provenait de la syntaxe :

```YAML
channels: [!event]
```

compatible avec Symfony 3.x mais non interprétée correctement avec la nouvelle configuration.

La syntaxe a été adaptée :

```YAML
channels: ["!event"]
```

Après correction :

```bash
php bin/console --version
```

Retour :

```text
Symfony 4.4.51 (env: dev, debug: true)
```

---

## Adaptation du Kernel

Le passage vers Symfony 4.4 nécessite également une adaptation du noyau.

Les éléments historiques Symfony 3.x ont été vérifiés :

- `AppKernel.php`
- `AppCache.php`
- `web/app.php`
- `web/app_dev.php`

Une erreur était présente dans `web/app.php` :

```text
Call to undefined method AppKernel::loadClassCache()
```

La méthode `loadClassCache()` n'existe plus dans Symfony 4.

La ligne suivante a été supprimée :

```PHP
$kernel->loadClassCache();
```

---

## Nettoyage des caches

Après chaque modification de configuration :

```bash
rm -rf var/cache/*
```

Puis :

```bash
php bin/console cache:clear --env=dev
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
Symfony 4.4.51 (env: dev, debug: true)
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

## Résultat

La migration Symfony 3.4 → Symfony 4.4 LTS est terminée.

L'application fonctionne désormais avec :

- Symfony 4.4.51 ;
- une base de données opérationnelle ;
- les entités Doctrine conservées ;
- les pages principales accessibles ;
- la connexion utilisateur fonctionnelle.

---

## Captures d'écran

Les captures réalisées pendant cette étape sont disponibles dans :

```text
docs/migration/screenshots/02-symfony-4.4/
```

---

## Travaux restant à réaliser

Les prochaines étapes permettront de :

- migrer vers Symfony 5.4 LTS ;
- remplacer progressivement les composants obsolètes ;
- moderniser l'architecture ;
- préparer Symfony 6.4 puis Symfony 7.4.

---

## Conclusion

Cette migration constitue une étape majeure de modernisation.

Le passage par Symfony 4.4 LTS permet de quitter l'architecture Symfony 3.x tout en conservant la stabilité de l'application.

Les corrections effectuées sur la configuration et les dépendances préparent désormais le projet aux migrations suivantes vers les versions LTS récentes de Symfony.
