# Audit de performance – Application originale

## Contexte

Le projet original utilise le front controller `web/app_dev.php`, qui limite par défaut l'accès au mode développement aux requêtes provenant de `127.0.0.1` ou `::1`.

Dans un environnement Docker, les requêtes HTTP sont vues par Symfony comme provenant du réseau interne des conteneurs (adresses de type `172.x.x.x`). Cette restriction empêchait donc l'accès au Web Profiler.

Afin de réaliser l'audit de performance, cette vérification a été temporairement désactivée dans l'environnement Docker de développement. Cette adaptation concerne uniquement l'environnement d'audit et ne modifie ni le fonctionnement métier de l'application ni son comportement en production.

---

## 🎯 Objectif

L'objectif de cet audit est d'évaluer les performances de l'application originale avant toute évolution fonctionnelle.

Les mesures ont été réalisées à l'aide des outils suivants :

- Symfony Web Profiler ;
- Chrome DevTools (Web Vitals et métriques de rendu).

---

## Environnement

- Symfony 3.1
- PHP 7.4
- Apache
- MySQL 5.7
- Docker

---

## Outils utilisés

- Symfony Web Profiler
- Chrome DevTools

---

## Captures d'écran

Les captures d'écran du Web Profiler utilisées dans ce document sont disponibles dans :

```
docs/audit/initial/screenshots/
```

Elles correspondent aux différentes pages analysées (accueil, connexion, liste des tâches, création d'une tâche et création d'un utilisateur) et permettent de justifier les valeurs présentées dans cet audit.

---

## Mesures observées

> **Remarque :** Les mesures présentées dans cet audit ont été relevées en environnement de développement avec le Web Profiler Symfony activé. Elles constituent un instantané des performances de l'application et peuvent varier légèrement d'une exécution à l'autre en fonction du cache, de la charge du système et de l'environnement d'exécution. Si l'application est relancée par l'évaluateur, les valeurs observées pourront donc différer légèrement tout en restant du même ordre de grandeur.

### Analyse du profiler Symfony

| Page                      |      Temps |  Mémoire | Route                       |
| ------------------------- | ---------: | -------: | --------------------------- |
| Accueil                   |     149 ms |     4 MB | `/app_dev.php/`             |
| Login                     |     147 ms |     2 MB | `/app_dev.php/login`        |
| Liste des tâches          | **212 ms** | **6 MB** | `/app_dev.php/tasks`        |
| Création d'une tâche      | **304 ms** | **8 MB** | `/app_dev.php/tasks/create` |
| Création d'un utilisateur | **264 ms** | **8 MB** | `/app_dev.php/users/create` |

---

### Analyse des résultats

Les mesures montrent des performances globalement satisfaisantes pour une application Symfony 3.1 exécutée en environnement de développement sous Docker.

Les pages d'accueil et de connexion sont les plus rapides, avec des temps d'exécution inférieurs à 150 ms et une faible consommation mémoire.

Les pages impliquant davantage de traitements côté serveur présentent des temps plus élevés :

- la liste des tâches atteint 212 ms avec une consommation mémoire de 6 MB ;
- la création d'un utilisateur nécessite 264 ms et 8 MB de mémoire ;
- la création d'une tâche est la page la plus coûteuse avec 304 ms et un pic mémoire de 8 MB.

Ces résultats restent cohérents pour une application exécutée en environnement de développement avec le Web Profiler activé, celui-ci ajoutant naturellement un surcoût au traitement des requêtes.

---

## Limites de l'audit

Les mesures ont été réalisées en environnement de développement avec le Web Profiler activé. Les temps observés incluent donc le surcoût lié aux outils de diagnostic et ne sont pas directement comparables à des mesures réalisées en environnement de production.

L'objectif de cet audit est de disposer d'une référence avant les améliorations du projet afin de comparer l'évolution des performances.

---

## Conclusion

L'audit de performance met en évidence une application globalement réactive dans son état d'origine.

Les principales observations sont les suivantes :

- les temps de réponse varient de **147 ms à 304 ms** selon les pages analysées ;
- la consommation mémoire reste comprise entre **2 MB et 8 MB** ;
- les pages d'accueil et de connexion présentent les meilleures performances ;
- la création d'une tâche est la page la plus coûteuse en temps d'exécution et en mémoire, ce qui s'explique par le traitement du formulaire et le rendu de la vue ;
- aucun goulet d'étranglement majeur n'a été identifié lors de cette analyse.

Les optimisations réalisées dans les étapes suivantes du projet porteront principalement sur la qualité du code, la sécurité, les fonctionnalités et la maintenabilité. Les performances seront de nouveau évaluées après les améliorations afin de comparer l'état initial et l'état final de l'application.
