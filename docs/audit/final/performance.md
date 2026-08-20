# 📈 Audit final de performance – ToDo & Co

## Contexte

Cet audit constitue la mesure finale des performances de l'application ToDo & Co, après les différentes évolutions réalisées dans le cadre du projet.

Il complète l'audit initial réalisé sur l'application originale et permet de comparer les performances observées avant et après les travaux réalisés.

Les principales évolutions intervenues entre les deux mesures concernent notamment :

- la migration progressive de Symfony vers Symfony 7.4 LTS ;
- a modernisation de l'architecture Symfony ;
- la migration vers la structure Symfony Flex ;
- la mise à jour de PHP ;
- la sécurisation de l'application ;
- l'ajout des règles d'autorisation ;
- la mise en place des tests automatisés ;
- l'amélioration de la gestion des tâches et des utilisateurs.

L'objectif de cet audit n'est pas de réaliser un benchmark de production, mais de disposer d'un état de référence permettant d'évaluer les performances de l'application dans son environnement de développement.

---

## 🎯 Objectif

L'objectif de cet audit est d'évaluer les performances de l'application ToDo & Co après les améliorations réalisées et de comparer les résultats avec ceux observés sur la version originale.

Les mesures ont été réalisées à l'aide des outils suivants :

- **Symfony Web Profiler** ;
- **Symfony Web Debug Toolbar** ;
- navigateur web.

Le Web Profiler permet notamment d'observer :

- le temps d'exécution des requêtes ;
- la consommation mémoire ;
- la route exécutée ;
- les différents traitements effectués par Symfony.

La Web Debug Toolbar permet quant à elle d'observer directement les principales informations de performance depuis l'interface de l'application.

---

## Environnement

L'application finale fonctionne dans l'environnement suivant :

| Élément         | Version / configuration |
| --------------- | ----------------------- |
| Framework       | Symfony 7.4.15 LTS      |
| PHP             | 8.2.33                  |
| Serveur web     | Apache                  |
| Base de données | MySQL                   |
| Environnement   | Docker                  |
| Mode Symfony    | `dev`                   |
| Debug           | Activé                  |
| OPcache         | Activé                  |
| Xdebug          | Activé                  |
| Web Profiler    | Activé                  |

Les informations générales de l'environnement ont été vérifiées avec :

```bash
docker compose exec php php bin/console about
```

Cette commande confirme notamment l'utilisation de Symfony 7.4.15 et de PHP 8.2.33.

---

## 🔎 Vérifications préalables

Avant la réalisation des mesures, l'état technique de l'application a été vérifié.

### Validation de Composer

docker compose exec php composer validate

Résultat :

```text
./composer.json is valid
```

---

### Validation du mapping Doctrine

```bash
docker compose exec php php bin/console doctrine:schema:validate
```

Résultat :

```text
[OK] The mapping files are correct.
[OK] The database schema is in sync with the mapping files.
```

Le schéma de la base de données est donc cohérent avec le mapping Doctrine au moment de l'audit.

---

### Validation des tests automatisés

La suite PHPUnit a également été exécutée avant les mesures :

```bash
docker compose exec php vendor/bin/phpunit
```

Résultat :

```text
55 tests
116 assertions
OK
```

L'audit de performance est donc réalisé sur une version fonctionnelle de l'application dont les tests automatisés passent intégralement.

---

## 🛠️ Outils utilisés

### Symfony Web Profiler

Le **Symfony Web Profiler** est utilisé pour mesurer les caractéristiques d'exécution des différentes pages de l'application.

Les informations observées comprennent notamment :

```text
Temps d'exécution
Mémoire utilisée
Route exécutée
Requête HTTP
```

Les captures correspondantes sont conservées dans :

```text
docs/audit/final/screenshots/profiler/
```

### Symfony Web Debug Toolbar

La **Web Debug Toolbar** permet de consulter directement depuis le navigateur différentes informations concernant la requête exécutée.

Elle permet notamment de confirmer que le profiler est actif et de visualiser rapidement :

- le temps d'exécution ;
- la mémoire utilisée ;
- le nombre de requêtes ;
- les informations Symfony associées à la requête.

Une capture de la toolbar est également conservée dans :

```text
docs/audit/final/screenshots/profiler/
```

---

## 📸 Captures d'écran

Les captures utilisées pour cet audit sont regroupées dans :

```text
docs/audit/final/screenshots/profiler/
```

Elles permettent de conserver les preuves des mesures présentées dans ce document.

Les captures comprennent notamment :

```text
Accueil
Login
Liste des tâches
Création d'une tâche
Création d'un utilisateur
Web Debug Toolbar
Web Profiler
```

Chaque capture permet de retrouver les informations correspondant aux mesures présentées dans le tableau ci-dessous.

---

## 📊 Mesures observées

### Analyse du profiler Symfony

| Page                      |  Temps | Mémoire | Route           |
| ------------------------- | -----: | ------: | --------------- |
| Accueil                   |  32 ms |    4 MB | `/`             |
| Login                     |  27 ms |    2 MB | `/login`        |
| Liste des tâches          |  52 ms |    4 MB | `/tasks`        |
| Création d'une tâche      |  69 ms |    4 MB | `/tasks/create` |
| Création d'un utilisateur | 100 ms |    4 MB | `/users/create` |

---

### Analyse des résultats

Les mesures obtenues montrent des temps d'exécution relativement faibles pour les différentes pages analysées.

La page de connexion est la plus rapide avec :

```text
27 ms
```

pour une consommation mémoire de :

```text
2 MB
```

La page d'accueil présente également un temps d'exécution faible :

```text
32 ms
```

avec :

```text
4 MB
```

de mémoire utilisée.

La liste des tâches atteint :

```text
52 ms
```

pour :

```text
4 MB
```

de mémoire.

La création d'une tâche présente un temps légèrement supérieur :

```text
69 ms
```

tout en conservant une consommation mémoire de :

```text
4 MB
```

Enfin, la création d'un utilisateur constitue la page la plus longue parmi les pages mesurées :

```text
100 ms
```

avec une consommation mémoire qui reste limitée à :

```text
4 MB
```

Aucune consommation mémoire particulièrement élevée n'est observée dans les mesures réalisées.

---

## 📈 Comparaison avec l'application originale

Une mesure similaire avait été réalisée sur la version originale de ToDo & Co.

Les résultats initiaux étaient :

| Page                      | Version originale | Version finale |
| ------------------------- | ----------------: | -------------: |
| Accueil                   |            149 ms |      **32 ms** |
| Login                     |            147 ms |      **27 ms** |
| Liste des tâches          |            212 ms |      **52 ms** |
| Création d'une tâche      |            304 ms |      **69 ms** |
| Création d'un utilisateur |            264 ms |     **100 ms** |

Les résultats montrent une diminution du temps observé sur chacune des pages comparées.

---

### Évolution observée

| Page                      | Évolution approximative |
| ------------------------- | ----------------------: |
| Accueil                   |             **-78,5 %** |
| Login                     |             **-81,6 %** |
| Liste des tâches          |             **-75,5 %** |
| Création d'une tâche      |             **-77,3 %** |
| Création d'un utilisateur |             **-62,1 %** |

Ces valeurs doivent cependant être interprétées avec prudence.

---

## ⚠️ Limites de la comparaison

Les deux audits ont été réalisés à des moments différents et dans des environnements techniques différents.

La comparaison ne permet donc pas d'attribuer directement l'amélioration des temps d'exécution à une modification précise du code.

Plusieurs facteurs peuvent influencer les résultats :

- version de PHP ;
- version de Symfony ;
- version des bibliothèques ;
- configuration Docker ;
- configuration Apache ;
- cache Symfony ;
- OPcache ;
- configuration du système hôte ;
- charge de la machine ;
- activation du Web Profiler ;
- version du navigateur.

La comparaison doit donc être considérée comme une indication de l'évolution globale du comportement de l'application, et non comme un benchmark scientifique.

---

## 🔬 Interprétation

Les résultats obtenus permettent néanmoins de constater que la version finale de l'application présente des temps d'exécution inférieurs à ceux relevés sur la version originale dans les mêmes parcours fonctionnels.

La page la plus coûteuse reste :

```text
/users/create
```

avec :

```text
100 ms
```

Ce résultat reste raisonnable dans le contexte de l'environnement de développement utilisé.

La création d'une tâche :

```text
69 ms
```

reste également sous le seuil des 100 ms observés pour les pages analysées.

La consommation mémoire est particulièrement stable :

```text
2 à 4 MB
```

sur les pages étudiées.

Aucun pic mémoire important n'a été identifié lors de cet audit.

---

## 🔍 Points d'attention

Même si les mesures sont satisfaisantes, l'audit ne permet pas de conclure que l'application est optimisée dans toutes les conditions.

Les mesures effectuées portent principalement sur quelques parcours représentatifs :

```text
/
/login
/tasks
/tasks/create
/users/create
```

D'autres scénarios pourraient nécessiter une analyse complémentaire, notamment :

- traitement d'un grand nombre de tâches ;
- utilisation simultanée par plusieurs utilisateurs ;
- augmentation importante du volume de données ;
- requêtes Doctrine complexes ;
- comportement sous forte charge ;
- performances des ressources statiques ;
- performances en environnement de production.

Ces éléments pourraient faire l'objet d'un audit complémentaire si l'application devait passer à une charge importante.

---

## 📊 Synthèse

| Indicateur            | Résultat final |
| --------------------- | -------------: |
| Temps minimum observé |      **27 ms** |
| Temps maximum observé |     **100 ms** |
| Mémoire minimum       |       **2 MB** |
| Mémoire maximum       |       **4 MB** |
| Pages analysées       |          **5** |
| Tests PHPUnit         |    **55 / 55** |
| Assertions            |        **116** |

---

## 🧩 Relation avec l'audit de qualité

L'audit de performance doit être interprété conjointement avec l'audit de qualité du code.

L'objectif du projet n'était pas uniquement d'obtenir une application plus rapide, mais également de disposer d'une application :

```text
Plus moderne
↓
Plus sécurisée
↓
Plus testée
↓
Plus maintenable
↓
Plus facilement évolutive
```

Les performances constituent donc un des axes de l'évaluation globale de la qualité de ToDo & Co.

---

## 📌 État final

À l'issue des travaux, l'application présente :

- Symfony 7.4.15 LTS ;
- PHP 8.2.33 ;
- un schéma Doctrine valide ;
- une suite de 55 tests PHPUnit ;
- 116 assertions ;
- 100 % des tests réussis ;
- une couverture de code de 74,02 % ;
- des temps d'exécution observés compris entre 27 ms et 100 ms sur les parcours analysés.

---

## Conclusion

L'audit final de performance montre des résultats satisfaisants sur les principaux parcours analysés.

Les temps observés sont compris entre :

```text
27 ms
```

et :

```text
100 ms
```

La consommation mémoire reste comprise entre :

```text
2 MB
```

et :

```text
4 MB
```

Aucun goulet d'étranglement majeur n'a été identifié lors des mesures réalisées.

La comparaison avec l'application originale montre également une diminution importante des temps observés sur les cinq parcours étudiés.

Ces résultats doivent néanmoins être considérés dans le contexte d'un environnement Docker de développement avec le Web Profiler activé. Ils ne constituent donc pas un benchmark de production.

L'audit permet néanmoins de disposer d'un état final documenté des performances de ToDo & Co et de fournir une base de référence pour de futures évolutions.
