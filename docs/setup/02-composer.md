# 02 📦 Installation des dépendances Composer

## Objectif

Installer les dépendances PHP du projet Symfony 3.1 afin de rendre l'application exécutable dans l'environnement Docker.

---

## Contexte

Le projet original repose sur **Symfony 3.1** et utilise un fichier `composer.lock` contenant les versions des dépendances de l'époque.

L'objectif est de conserver ces dépendances afin de respecter le comportement du projet d'origine.

---

## Vérification de Composer

Depuis le conteneur PHP :

```bash
composer --version
```

Résultat :

```
Composer version 2.x
```

La présence de Composer dans le conteneur permet de gérer les dépendances directement depuis l'environnement Docker.

---

## Installation des dépendances

Les dépendances ont été installées à partir du fichier `composer.lock` :

```bash
composer install --no-scripts
```

L'option `--no-scripts` évite l'exécution automatique des scripts Symfony pendant l'installation.

Cette précaution permet d'installer les dépendances avant que la configuration complète de l'application (base de données, paramètres, cache) ne soit finalisée.

---

## Vérification

Validation du fichier Composer :

```bash
composer validate
```

Résultat :

- fichier `composer.json` valide ;
- quelques avertissements liés à l'ancien format Symfony Standard Edition, sans empêcher le fonctionnement de l'application.

---

## Résultat

Les dépendances du projet original ont été installées avec succès.

Le dossier `vendor/` est disponible et l'autoload Composer est correctement généré.

Cette étape permet de poursuivre la configuration de Symfony et de Doctrine dans les étapes suivantes.

---

## Remarques

Cette étape ne modifie pas les dépendances du projet.

L'objectif est uniquement de rendre le projet original exécutable tout en conservant les versions définies par le dépôt d'origine.
