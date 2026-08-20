# 🎙️ Audit final de la qualité du code – ToDo & Co

## 🎯 Objectif

Ce document présente l'état final de la qualité du code de l'application ToDo & Co après les évolutions réalisées dans le cadre du projet OpenClassrooms.

L'objectif est d'évaluer la qualité actuelle du projet et d'identifier les éventuels éléments de dette technique restant à traiter.

L'analyse porte notamment sur :

- la qualité générale du code ;
- la maintenabilité ;
- la complexité ;
- la duplication ;
- les pratiques de développement ;
- les dépendances ;
- la sécurité du code ;
- les tests automatisés ;
- la couverture de code.

Les résultats obtenus sont interprétés dans le contexte réel de l'application.

L'objectif de cet audit n'est pas de corriger l'ensemble des problèmes détectés, mais de fournir un état des lieux objectif et documenté permettant de prioriser les éventuelles améliorations futures.

---

## 🏗️ Environnement

L'audit final est réalisé sur la version modernisée de l'application.

| Élément          | État final             |
| ---------------- | ---------------------- |
| Framework        | **Symfony 7.4.15 LTS** |
| PHP              | **8.2.33**             |
| Architecture     | Symfony Flex           |
| ORM              | Doctrine ORM           |
| Base de données  | MySQL                  |
| Tests            | PHPUnit 11.5.56        |
| Couverture       | Xdebug 3.5.3           |
| Analyse qualité  | Codacy                 |
| Conteneurisation | Docker                 |
| Serveur web      | Apache                 |
| OPcache          | Activé                 |

Les informations concernant l'environnement ont été vérifiées avec :

```bash
docker compose exec php php bin/console about
```

Le projet utilise actuellement :

```text
Symfony 7.4.15 LTS
PHP 8.2.33
```

Symfony 7.4 est une version LTS dont la maintenance est actuellement prévue jusqu'en novembre 2028.

---

## 🧪 Vérification de l'état du projet

Avant la réalisation de l'audit, plusieurs vérifications techniques ont été effectuées afin de s'assurer que le projet était dans un état cohérent.

### Validation de Composer

Commande :

```bash
docker compose exec php composer validate
```

Résultat :

```bash
./composer.json is valid
```

Le fichier composer.json est donc correctement structuré.

---

### Validation du mapping Doctrine

Commande :

```bash
docker compose exec php php bin/console doctrine:schema:validate
```

Résultat :

```text
[OK] The mapping files are correct.

[OK] The database schema is in sync with the mapping files.
```

Le mapping Doctrine et le schéma de base de données sont donc cohérents.

---

## 📊 Analyse de la qualité du code

L'analyse de la qualité du code s'appuie principalement sur Codacy, complétée par l'analyse du code source et les résultats des tests automatisés.

Codacy permet notamment d'identifier :

- les problèmes de qualité ;
- les problèmes de sécurité ;
- les mauvaises pratiques ;
- certains problèmes de maintenabilité ;
- la complexité ;
- la duplication ;
- la qualité des fichiers analysés.

Les résultats détaillés de l'utilisation de Codacy sont présentés dans :

```text
docs/audit/codacy.md
```

Ce document présente notamment les analyses réalisées avant et après les corrections effectuées pendant le projet.

---

## 🔄 Évolution de l'analyse Codacy

Une première analyse avait été réalisée avant les corrections de qualité.

Le tableau de bord présentait alors :

```text
22 issues
```

Les captures de cet état initial ont été conservées dans :

```text
docs/audit/final/screenshots/codacy/
```

Après les corrections réalisées, le tableau de bord présente :

```text
14 issues
```

L'évolution est donc :

```text
22 issues
     ↓
Corrections
     ↓
14 issues
```

Soit une diminution de :

```text
8 issues
```

Cette évolution montre une amélioration mesurable de la qualité du projet.

Cependant, la diminution du nombre d'issues ne signifie pas que l'ensemble de la dette technique a été supprimé.

Les 14 problèmes restant détectés par Codacy doivent être analysés individuellement afin de déterminer leur impact réel.

---

## 🔐 Corrections de qualité réalisées

Certaines alertes identifiées lors de l'analyse Codacy ont fait l'objet de corrections dans le cadre du projet.

### Configuration du secret Symfony

La configuration du secret Symfony a été sécurisée afin d'éviter de conserver une valeur sensible directement dans le dépôt.

Le fichier concerné est :

```text
.env
```

Après correction, Codacy attribue au fichier :

```text
Grade A
```

Cette amélioration est documentée dans :

```text
docs/audit/codacy.md
```

et illustrée par les captures avant / après.

---

### Dockerfile

Le fichier :

```text
docker/php/Dockerfile
```

a également fait l'objet d'une amélioration concernant l'installation des dépendances.

Après correction, Codacy attribue au fichier :

```text
Grade B
```

Cette note indique que le fichier ne présente plus les mêmes problèmes que lors de l'analyse initiale, mais que certaines améliorations restent potentiellement possibles.

Le choix a été fait de ne pas poursuivre les corrections uniquement dans le but d'obtenir un grade maximal, les recommandations restantes devant être évaluées selon leur pertinence pour le projet.

---

## 🧩 Complexité du code

La complexité constitue un indicateur important de maintenabilité.

Une complexité élevée peut rendre :

- le code plus difficile à comprendre ;
- les évolutions plus risquées ;
- les tests plus difficiles à écrire ;
- les régressions plus probables.

L'analyse Codacy permet d'identifier les composants présentant des niveaux de complexité plus importants.

Les résultats devront être interprétés en tenant compte de la taille relativement limitée de l'application ToDo & Co.

Les composants contenant principalement de la logique métier feront l'objet d'une attention particulière :

```text
Controllers
Voters
Services
Repositories
```

---

## ♻️ Duplication

La duplication de code peut augmenter la dette technique en obligeant les développeurs à maintenir plusieurs implémentations similaires.

L'analyse devra permettre d'identifier :

- les blocs de code similaires ;
- les méthodes répétées ;
- les traitements similaires présents dans plusieurs composants.

Une duplication détectée ne constitue cependant pas systématiquement un problème.

Chaque duplication doit être évaluée selon son contexte afin de déterminer si elle justifie réellement un refactoring.

---

## 🧱 Maintenabilité

La maintenabilité du projet a été améliorée par plusieurs évolutions structurelles réalisées au cours du projet.

L'application a notamment été modernisée vers une architecture Symfony Flex.

L'organisation actuelle du projet permet de distinguer clairement :

```text
src/
├── Controller/
├── Entity/
├── Form/
├── Repository/
└── Security/
```

Cette organisation facilite l'identification des responsabilités de chaque composant.

La séparation des règles d'autorisation dans :

```text
src/Security/Voter/
```

permet notamment d'éviter de concentrer toute la logique de sécurité directement dans les contrôleurs.

---

## 🔐 Sécurité du code

Plusieurs améliorations de sécurité ont été réalisées au cours du projet.

Elles concernent notamment :

- l'authentification ;
- la gestion des rôles ;
- la protection des pages d'administration ;
- les droits de modification des tâches ;
- les droits de suppression des tâches ;
- la gestion spécifique des tâches anonymous ;
- la sécurisation de la configuration.

Les règles d'autorisation sont notamment centralisées dans :

```text
src/Security/Voter/TaskVoter.php
```

Les accès aux pages de gestion des utilisateurs sont protégés par :

```text
ROLE_ADMIN
```

Les règles métier sont également couvertes par les tests automatisés.

---

## 📦 Analyse des dépendances

Les dépendances du projet sont définies dans :

```text
composer.json
```

Le projet a fait l'objet d'une importante modernisation des dépendances lors des migrations Symfony.

La version finale utilise notamment :

```text
Symfony 7.4 LTS
Doctrine ORM
Doctrine DBAL
Doctrine Migrations
PHPUnit
Xdebug
```

La commande :

```bash
docker compose exec php composer validate
```

confirme que la configuration Composer est valide.

Les éventuelles dépendances abandonnées ou présentant un risque de maintenance doivent être distinguées des problèmes directement applicatifs.

L'analyse des dépendances constitue donc un élément de surveillance à poursuivre dans le temps, notamment lors des futures mises à jour du projet.

---

## 🧪 Tests automatisés

La qualité du code est également évaluée à travers les tests automatisés.

La suite PHPUnit comprend actuellement :

```text
55 tests
116 assertions
```

Commande utilisée :

```bash
docker compose exec php vendor/bin/phpunit
```

Résultat :

```text
55 / 55 (100%)
```

Soit :

```text
0 échec
0 erreur
```

Tous les tests automatisés passent donc avec succès.

---

## 🧪 Répartition des tests

Les tests sont organisés dans :

```text
tests/
├── Controller/
├── Entity/
└── Security/
```

Ils couvrent notamment :

### Entités

```text
User
Task
```

### Sécurité

```text
TaskVoter
```

### Contrôleurs

```text
HomepageController
TaskController
UserController
```

Les tests fonctionnels permettent notamment de vérifier :

- l'authentification ;
- les accès utilisateurs ;
- les droits administrateur ;
- la création de tâches ;
- la modification ;
- le changement d'état ;
- la suppression ;
- les règles d'autorisation.

---

## 📈 Couverture de code

La couverture est mesurée avec :

```text
Xdebug 3.5.3
```

Commande utilisée :

```bash
docker compose exec php sh -c \
'XDEBUG_MODE=coverage APP_ENV=test vendor/bin/phpunit --coverage-text'
```

Résultat :

```text
Classes: 60.00% (6/10)
Methods: 86.96% (40/46)
Lines:   74.02% (151/204)
```

Le projet fixe comme objectif :

> 70 %

La couverture actuelle est donc :

```text
74,02 %
```

L'objectif est atteint.

---

## 📊 Évolution de la couverture

Une première mesure réalisée après l'implémentation des tests unitaires avait donné :

```text
34,12 %
```

Après l'ajout des tests fonctionnels :

```text
74,02 %
```

L'évolution est donc :

| Indicateur            | Après tests unitaires | Après tests fonctionnels |
| --------------------- | --------------------: | -----------------------: |
| Couverture des lignes |               34,12 % |              **74,02 %** |
| Tests                 |                    38 |                   **55** |
| Assertions            |                    48 |                  **116** |

La couverture a donc progressé de :

```text
+39,90 points
```

Cette progression constitue l'une des principales améliorations mesurables de la qualité du projet.

---

## 🔎 Couverture des principales classes

Les principales classes disposent désormais d'un niveau de couverture élevé.

| Classe               | Méthodes |  Lignes |
| -------------------- | -------: | ------: |
| `SecurityController` |    100 % |   100 % |
| `TaskController`     |     60 % | 61,36 % |
| `UserController`     |    100 % |   100 % |
| `Task`               |    100 % |   100 % |
| `User`               |    100 % |   100 % |
| `TaskType`           |    100 % |   100 % |
| `UserType`           |    100 % |   100 % |
| `TaskVoter`          |     50 % | 92,86 % |

La couverture des lignes du TaskController reste notamment inférieure à celle des autres composants.

Elle constitue donc un point pouvant faire l'objet d'une amélioration future si de nouveaux scénarios de tests sont nécessaires.

---

## 🚨 Dette technique restante

L'analyse finale montre que la dette technique du projet a été réduite mais qu'elle n'est pas totalement supprimée.

Les principaux axes de surveillance sont :

```text
Codacy
    ↓
14 issues restantes

Tests
    ↓
74,02 % de couverture

Controllers
    ↓
Certaines méthodes restent partiellement couvertes

Dépendances
    ↓
Surveillance nécessaire lors des futures mises à jour

Qualité Docker
    ↓
Dockerfile : Grade B
```

Les problèmes restants devront être évalués selon leur impact réel plutôt que simplement selon leur nombre.

---

## 🏷️ Priorisation

Les éventuels problèmes restant à traiter pourront être classés selon les niveaux suivants :

| Niveau       | Signification                                    |
| ------------ | ------------------------------------------------ |
| 🔴 Critique  | Impact important sur la sécurité ou la stabilité |
| 🟠 Important | Dette susceptible de compliquer les évolutions   |
| 🟡 Modéré    | Amélioration souhaitable mais non bloquante      |
| 🟢 Faible    | Amélioration principalement liée à la lisibilité |

Cette classification doit tenir compte du contexte de ToDo & Co et non uniquement du classement automatique fourni par un outil.

---

## 📈 Synthèse des indicateurs

| Indicateur              |     État final |
| ----------------------- | -------------: |
| Symfony                 | **7.4.15 LTS** |
| PHP                     |     **8.2.33** |
| PHPUnit                 |    **11.5.56** |
| Tests                   |         **55** |
| Assertions              |        **116** |
| Tests réussis           |      **100 %** |
| Couverture lignes       |    **74,02 %** |
| Couverture méthodes     |    **86,96 %** |
| Couverture classes      |       **60 %** |
| Issues Codacy initiales |         **22** |
| Issues Codacy finales   |         **14** |
| Évolution Codacy        |  **-8 issues** |
| `.env`                  |    **Grade A** |
| `Dockerfile`            |    **Grade B** |

---

## 🔄 Comparaison avant / après

L'évolution globale du projet peut être résumée ainsi :

```text
Projet initial
     ↓
Dette technique importante
     ↓
Migration Symfony
     ↓
Modernisation de l'architecture
     ↓
Sécurisation
     ↓
Tests unitaires
     ↓
Tests fonctionnels
     ↓
Corrections Codacy
     ↓
Audit final
```

Les indicateurs disponibles permettent notamment de mesurer :

```text
Codacy
22 issues → 14 issues

Couverture
34,12 % → 74,02 %

Tests
38 → 55 tests

Assertions
48 → 116 assertions
```

Ces résultats montrent une amélioration significative de la qualité globale du projet.

---

## 💡 Recommandations

Même si l'objectif du projet est atteint, plusieurs améliorations peuvent être envisagées ultérieurement :

- traiter progressivement les issues Codacy restantes ;
- augmenter la couverture du TaskController ;
- maintenir une couverture supérieure à 70 % lors des futures évolutions ;
- surveiller régulièrement les dépendances Composer ;
- maintenir Symfony et ses composants à jour ;
- analyser régulièrement les performances de l'application ;
- intégrer les contrôles de qualité dans un processus d'intégration continue ;
- conserver les tests automatisés lors des futures évolutions.

Ces recommandations constituent des pistes d'amélioration et ne font pas nécessairement partie du périmètre de l'audit actuel.

---

## 📸 Captures d'écran

Les captures utilisées pour documenter l'audit sont regroupées dans :

docs/audit/final/screenshots/

Elles comprennent notamment :

```text
codacy/
├── files/
│   ├── before/
│   └── after/
└── preview/
```

Les captures permettent notamment de conserver les états :

```text
Codacy avant
    ↓
Corrections
    ↓
Codacy après
```

Les résultats PHPUnit et de couverture seront également conservés dans les répertoires dédiés.

---

## ⚠️ Limites de l'analyse

La couverture de code ne constitue pas à elle seule une mesure complète de la qualité des tests.

Un taux de :

```text
74,02 %
```

indique que cette proportion des lignes a été exécutée pendant les tests, mais ne garantit pas que tous les comportements possibles sont couverts.

De la même manière, le nombre d'issues Codacy ne permet pas à lui seul de mesurer toute la qualité du projet.

Les résultats doivent donc être interprétés conjointement avec :

- l'analyse du code ;
- les tests automatisés ;
- l'architecture ;
- la sécurité ;
- les dépendances ;
- l'analyse des performances.

---

## 🏁 Conclusion

L'audit final montre une amélioration significative de la qualité du projet ToDo & Co par rapport à son état initial.

Les principales évolutions mesurables sont :

```text
22 issues Codacy
        ↓
14 issues

34,12 % de couverture
        ↓
74,02 %

38 tests
        ↓
55 tests
```

La suite automatisée présente désormais :

```text
55 tests
116 assertions
0 échec
0 erreur
```

L'objectif de couverture fixé à :

> 70 %

est atteint avec :

```text
74,02 %
```

Le projet dispose également d'une architecture modernisée, d'un système d'authentification et d'autorisation renforcé, ainsi que de tests couvrant les principales règles métier et les parcours fonctionnels.

La dette technique restante doit désormais être considérée comme un **ensemble de travaux d'amélioration continue**, plutôt que comme un obstacle au fonctionnement actuel de l'application.

L'analyse des performances sera présentée dans le document complémentaire :

```text
docs/audit/performance.md
```

Le présent document constitue ainsi la partie qualité du code du rapport d'audit final, qui sera ensuite réunie avec l'audit des performances dans le PDF final destiné à la livraison.
