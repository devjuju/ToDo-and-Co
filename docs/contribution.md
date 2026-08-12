# Guide de contribution

## Introduction

Ce document présente les règles et le processus à suivre pour contribuer au développement de **ToDo & Co**.

L'objectif est de permettre à plusieurs développeurs de travailler sur le projet de manière organisée, tout en maintenant un niveau de qualité constant et en limitant les risques de régression.

Avant toute modification, il est recommandé de prendre connaissance de la structure du projet et de vérifier que l'environnement de développement fonctionne correctement.

---

# 🏗️ Environnement technique

Le projet utilise notamment :

- **PHP 8.2** ;
- **Symfony 7.4** ;
- **Doctrine ORM** ;
- **Twig** ;
- **MySQL** ;
- **PHPUnit** pour les tests automatisés ;
- **Xdebug** pour mesurer la couverture de code ;
- **Docker** pour l'environnement d'exécution.

L'application est exécutée dans des conteneurs Docker.

Les commandes Symfony doivent donc être exécutées depuis le conteneur PHP.

Exemple :

```bash
docker-compose exec php /bin/bash
```

Puis :

```bash
php bin/console
```

---

# 📁 Organisation du projet

La structure principale du projet est organisée de la manière suivante :

```text
ToDo-and-Co/
├── config/
│   ├── packages/
│   └── routes/
│
├── docs/
│
├── public/
│   └── index.php
│
├── src/
│   ├── Controller/
│   ├── Entity/
│   ├── Form/
│   ├── Repository/
│   ├── Security/
│   └── Kernel.php
│
├── templates/
│
├── tests/
│   ├── Controller/
│   ├── Entity/
│   └── Security/
│
├── migrations/
│
├── var/
│
├── vendor/
│
├── composer.json
└── phpunit.xml.dist
```

Les principales responsabilités sont :

| Répertoire        | Responsabilité                               |
| ----------------- | -------------------------------------------- |
| `src/Controller/` | Contrôleurs et gestion des requêtes HTTP     |
| `src/Entity/`     | Entités Doctrine et règles liées aux données |
| `src/Form/`       | Formulaires Symfony                          |
| `src/Security/`   | Composants de sécurité, notamment les Voters |
| `src/Repository/` | Requêtes vers la base de données             |
| `templates/`      | Vues Twig                                    |
| `tests/`          | Tests automatisés                            |
| `docs/`           | Documentation technique et projet            |
| `migrations/`     | Migrations de la base de données             |
| `public/`         | Point d'entrée HTTP de l'application         |
| `config/`         | Configuration Symfony                        |

---

# 🌿 Gestion des branches Git

Chaque évolution doit être développée dans une branche dédiée.

Il est recommandé de partir de la branche principale à jour :

```bash
git checkout main
git pull
```

Puis de créer une branche :

```bash
git checkout -b feature/nom-de-la-fonctionnalite
```

Pour une correction :

```bash
git checkout -b fix/nom-de-la-correction
```

Pour une amélioration technique :

```bash
git checkout -b refactor/nom-de-l-amelioration
```

Exemples :

```text
feature/functional-tests
fix/task-owner
refactor/task-controller
```

Une branche doit avoir un objectif clairement identifié et éviter de mélanger plusieurs fonctionnalités indépendantes.

---

# 📝 Travail à partir d'une issue

Chaque modification doit être associée à une issue GitHub lorsque cela est possible.

Avant de commencer le développement :

1. prendre connaissance de l'issue ;
2. comprendre les critères d'acceptation ;
3. vérifier les fonctionnalités existantes concernées ;
4. créer une branche dédiée ;
5. implémenter la modification ;
6. ajouter ou mettre à jour les tests ;
7. effectuer les vérifications qualité ;
8. documenter la modification si nécessaire.

Une issue doit permettre de comprendre :

- le problème rencontré ;
- le comportement attendu ;
- les travaux à réaliser ;
- les critères permettant de considérer le travail comme terminé.

---

# 💻 Règles de développement

Le code doit respecter les conventions utilisées par le projet et les bonnes pratiques Symfony et PHP.

Les modifications doivent rester aussi simples et ciblées que possible.

Il est recommandé de :

- privilégier des méthodes courtes et compréhensibles ;
- utiliser des noms explicites ;
- éviter les duplications de code ;
- limiter les effets de bord ;
- respecter les responsabilités de chaque classe ;
- utiliser l'injection de dépendances ;
- éviter de placer de la logique métier importante directement dans les templates ;
- conserver une séparation claire entre contrôleurs, entités, formulaires et sécurité.

Une modification ne doit pas introduire de fonctionnalité ou de dépendance inutile.

---

# 🔐 Sécurité

Toute fonctionnalité nécessitant une autorisation doit être protégée.

Les règles d'accès de l'application sont notamment définies dans :

```text
config/packages/security.yaml
```

Les pages de gestion des utilisateurs sont réservées aux administrateurs :

```text
/users/*
    → ROLE_ADMIN
```

Les autres pages protégées nécessitent une authentification :

```text
^/
    → ROLE_USER
```

Les règles métier concernant les tâches doivent être protégées par le mécanisme de sécurité approprié.

Par exemple, les droits de modification, de changement d'état et de suppression d'une tâche sont gérés par :

```text
src/Security/Voter/TaskVoter.php
```

Il ne faut donc pas contourner le Voter en ajoutant une vérification de sécurité directement dans chaque vue ou en faisant confiance aux données envoyées par le navigateur.

Les données provenant des utilisateurs doivent toujours être considérées comme non fiables.

---

# 🗄️ Modifications de la base de données

Toute modification du modèle de données doit être réalisée avec Doctrine.

Après une modification d'entité, vérifier le schéma :

```bash
php bin/console doctrine:schema:validate
```

Si une modification de structure de la base est nécessaire, générer une migration :

```bash
php bin/console doctrine:migrations:diff
```

Puis vérifier attentivement le fichier de migration généré.

Les migrations doivent être versionnées avec le projet.

Elles permettent aux autres développeurs de reproduire les mêmes évolutions de la base de données.

---

# 🧪 Tests automatisés

Toute nouvelle fonctionnalité ou correction importante doit être accompagnée de tests adaptés.

Le projet utilise **PHPUnit**.

Les tests sont organisés principalement selon deux catégories :

```text
tests/Entity/
tests/Security/Voter/
    → tests unitaires

tests/Controller/
    → tests fonctionnels
```

---

## Tests unitaires

Les tests unitaires vérifient le comportement d'une classe ou d'une règle métier de manière isolée.

Ils sont notamment utilisés pour :

- les entités ;
- les méthodes métier ;
- les Voters ;
- les règles ne nécessitant pas une requête HTTP complète.

Exemple :

```bash
vendor/bin/phpunit tests/Entity/TaskTest.php
```

---

## Tests fonctionnels

Les tests fonctionnels utilisent :

```php
WebTestCase
```

Ils permettent de vérifier le comportement de l'application à travers de véritables requêtes HTTP.

Exemple :

```bash
vendor/bin/phpunit tests/Controller/TaskControllerTest.php
```

Ils sont notamment utilisés pour vérifier :

- les routes ;
- l'authentification ;
- les redirections ;
- les formulaires ;
- les droits d'accès ;
- la création et modification des tâches ;
- les comportements des contrôleurs.

---

# ▶️ Exécution de la suite de tests

Avant de proposer une modification à intégrer, exécuter l'ensemble des tests :

```bash
APP_ENV=test vendor/bin/phpunit
```

Le résultat attendu est :

```text
OK
```

avec :

```text
Failures: 0
Errors: 0
```

Un test qui échoue doit être analysé et corrigé avant de considérer la modification comme terminée.

Il ne faut pas désactiver ou supprimer un test uniquement pour obtenir une suite verte.

---

# 📊 Couverture de code

Le projet utilise **Xdebug** afin de mesurer la couverture du code par les tests.

La couverture peut être calculée avec :

```bash
XDEBUG_MODE=coverage APP_ENV=test vendor/bin/phpunit --coverage-text
```

Pour générer le rapport HTML :

```bash
XDEBUG_MODE=coverage APP_ENV=test vendor/bin/phpunit --coverage-html var/coverage
```

Le rapport est généré dans :

```text
var/coverage/
```

Le répertoire `var/coverage/` est généré automatiquement et ne doit pas être versionné.

La commande :

```bash
git check-ignore -v var/coverage/index.html
```

doit confirmer que le répertoire est ignoré par Git.

L'objectif du projet est de maintenir une couverture globale supérieure à :

```text
70 %
```

La couverture doit être considérée comme un indicateur de qualité et non comme un objectif consistant à écrire artificiellement des tests uniquement pour augmenter un pourcentage.

---

# 🧹 Vérifications avant commit

Avant de créer un commit, effectuer au minimum les vérifications suivantes.

### Vérification de la configuration Composer

```bash
composer validate
```

### Vérification du schéma Doctrine

```bash
php bin/console doctrine:schema:validate
```

### Exécution des tests

```bash
APP_ENV=test vendor/bin/phpunit
```

### Vérification de la couverture

Lorsque la modification concerne une fonctionnalité importante :

```bash
XDEBUG_MODE=coverage APP_ENV=test vendor/bin/phpunit --coverage-text
```

### Vérification Git

```bash
git status
```

Puis :

```bash
git diff
```

Il faut vérifier que seuls les fichiers nécessaires à la modification sont présents.

---

# 📦 Fichiers générés à ne pas versionner

Les fichiers générés automatiquement ne doivent pas être ajoutés au dépôt.

Le fichier `.gitignore` définit notamment les éléments exclus du versionnement.

Exemples :

```text
/var/*
/vendor/
```

Le rapport de couverture doit notamment rester dans :

```text
var/coverage/
```

et ne doit pas être ajouté à Git.

Avant un commit :

```bash
git status
```

permet de vérifier qu'aucun fichier généré inutile n'est présent.

---

# 📝 Commits

Les commits doivent être explicites et décrire la modification réalisée.

Exemple :

```bash
git commit -m "Implémentation des tests fonctionnels"
```

Autres exemples :

```bash
git commit -m "Sécurisation des accès utilisateurs"
```

```bash
git commit -m "Association automatique des tâches aux utilisateurs"
```

```bash
git commit -m "Ajout de la gestion des rôles"
```

Il est préférable d'éviter les messages trop génériques tels que :

```text
update
fix
modif
test
```

Un commit doit idéalement représenter une modification cohérente et identifiable.

---

# 🔎 Relecture avant intégration

Avant de fusionner une branche, vérifier :

- [ ] Les critères d'acceptation de l'issue sont respectés
- [ ] Les tests correspondants ont été ajoutés ou adaptés
- [ ] Tous les tests passent
- [ ] Le schéma Doctrine est valide si nécessaire
- [ ] Aucun fichier généré inutile n'est versionné
- [ ] La couverture de code n'a pas régressé de manière injustifiée
- [ ] Les règles de sécurité sont respectées
- [ ] La documentation a été mise à jour si nécessaire
- [ ] Le code ne contient pas de debug temporaire
- [ ] Le diff Git a été relu

---

# 🔀 Pull Request

Une Pull Request doit permettre à un autre développeur de comprendre rapidement la modification.

Elle doit notamment présenter :

### Contexte

Pourquoi la modification est-elle nécessaire ?

### Modification

Qu'est-ce qui a été changé ?

### Tests

Quels tests ont été ajoutés ou exécutés ?

### Vérifications

Quelles commandes ont été utilisées pour vérifier la modification ?

Exemple :

```text
Tests :
APP_ENV=test vendor/bin/phpunit

Résultat :
55 tests
116 assertions
0 échec
0 erreur
```

Si une couverture a été mesurée, préciser également le résultat :

```text
Coverage :
74.02 % des lignes
```

---

# 🚦 Processus de qualité

Le processus de qualité à suivre pour chaque évolution est le suivant :

```text
Issue
  ↓
Création de la branche
  ↓
Développement
  ↓
Tests unitaires / fonctionnels
  ↓
Vérification Doctrine
  ↓
Vérification de la couverture
  ↓
Relecture du code
  ↓
Commit
  ↓
Pull Request
  ↓
Revue
  ↓
Fusion
```

Ce processus permet de détecter les erreurs le plus tôt possible.

---

# 🧪 Tests avant et après modification

Lorsqu'une fonctionnalité existante est modifiée, il est important de vérifier que les comportements précédents continuent de fonctionner.

Le développeur doit donc :

1. identifier les tests existants concernés ;
2. ajouter les nouveaux cas nécessaires ;
3. exécuter la suite complète ;
4. vérifier qu'aucune régression n'est introduite.

Une correction de bug doit idéalement être accompagnée d'un test reproduisant le comportement incorrect avant sa correction.

Le test doit ensuite rester dans la suite afin d'éviter que le bug réapparaisse.

---

# 🐛 Gestion des anomalies

Lorsqu'une anomalie est découverte :

1. reproduire le problème ;
2. identifier la cause ;
3. créer ou utiliser une issue ;
4. ajouter un test reproduisant le problème lorsque cela est pertinent ;
5. corriger le problème ;
6. vérifier que le test passe ;
7. exécuter la suite complète ;
8. documenter la correction si nécessaire.

Cette méthode permet de transformer une anomalie ponctuelle en cas de non-régression automatisé.

---

# 📚 Documentation

Toute modification importante de l'architecture ou du fonctionnement de l'application doit être documentée.

La documentation technique est regroupée dans :

```text
docs/
```

Par exemple :

```text
docs/authentication.md
docs/contribution.md
docs/tests/
docs/migration/
```

Lorsqu'une modification rend une documentation existante obsolète, celle-ci doit être mise à jour.

La documentation doit rester accessible à un développeur qui découvre le projet.

---

# 🔄 Mise à jour de la branche

Avant de commencer une nouvelle évolution, il est recommandé de travailler à partir d'une branche principale à jour :

```bash
git checkout main
git pull
```

Puis :

```bash
git checkout -b feature/ma-fonctionnalite
```

Si la branche de développement doit être mise à jour pendant son développement :

```bash
git fetch
git rebase origin/main
```

ou selon les conventions de l'équipe :

```bash
git merge origin/main
```

La méthode retenue doit rester cohérente avec les règles de collaboration de l'équipe.

---

# 🚫 Bonnes pratiques à éviter

Les pratiques suivantes sont à éviter :

- modifier directement la branche principale ;
- commiter des fichiers générés ;
- désactiver un test pour faire passer la suite ;
- ignorer une erreur PHPUnit ;
- ajouter des fonctionnalités sans test lorsque celles-ci peuvent être testées ;
- contourner les mécanismes d'autorisation ;
- stocker des mots de passe en clair ;
- laisser du code de debug dans le projet ;
- mélanger plusieurs fonctionnalités indépendantes dans un même commit ;
- modifier une dépendance sans raison documentée ;
- effectuer une modification de base de données sans migration.

---

# 🔐 Gestion des informations sensibles

Aucune information sensible ne doit être ajoutée au dépôt Git.

Cela concerne notamment :

- mots de passe ;
- clés secrètes ;
- tokens ;
- identifiants de services externes ;
- informations de connexion à une base de données de production.

Les fichiers d'environnement locaux doivent rester exclus du versionnement lorsqu'ils contiennent des informations propres à l'environnement du développeur.

---

# ✅ Checklist développeur

Avant de considérer une contribution comme terminée :

```text
[ ] Issue comprise
[ ] Branche dédiée créée
[ ] Fonctionnalité implémentée
[ ] Tests ajoutés ou adaptés
[ ] Tests PHPUnit au vert
[ ] Sécurité vérifiée
[ ] Doctrine vérifié si nécessaire
[ ] Couverture vérifiée si nécessaire
[ ] Documentation mise à jour
[ ] Aucun fichier généré à commiter
[ ] git diff relu
[ ] Commit explicite
[ ] Pull Request prête
```

---

# Conclusion

Le processus de contribution de ToDo & Co repose sur trois principes :

```text
Code propre
    +
Tests automatisés
    +
Relecture
```

Chaque évolution doit être développée de manière isolée, testée et vérifiée avant son intégration.

L'objectif est de permettre à plusieurs développeurs de faire évoluer l'application tout en conservant sa stabilité, sa sécurité et sa maintenabilité.

Le respect de ce processus doit également faciliter l'arrivée de nouveaux développeurs dans l'équipe en leur fournissant un cadre clair pour comprendre le projet et contribuer sans introduire de régressions.
