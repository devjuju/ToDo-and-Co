# 📊 Audit qualité du code avec Codacy

## Introduction

Dans le cadre de l'amélioration de l'application **ToDo & Co**, un audit de la qualité du code a été réalisé à l'aide de **Codacy**.

L'objectif de cette analyse est d'obtenir une vision objective de la qualité du projet, d'identifier les principaux problèmes détectés automatiquement et de mesurer l'évolution de la qualité après les corrections réalisées.

Cet audit s'inscrit également dans la continuité du projet original `projet8-TodoList`.

L'analyse Codacy constitue une partie de l'audit global de qualité du projet.

---

# 1. 🎯 Objectifs de l'analyse

L'utilisation de Codacy doit permettre de :

- mesurer la qualité globale du projet ;
- identifier les problèmes détectés automatiquement ;
- identifier les problèmes de sécurité ;
- détecter certaines mauvaises pratiques ;
- identifier les fichiers présentant des problèmes ;
- analyser la criticité des problèmes ;
- suivre l'évolution de la qualité après correction ;
- conserver un état de référence du projet.

L'objectif n'est pas de corriger systématiquement toutes les recommandations de Codacy.

Chaque problème doit être analysé dans le contexte réel de l'application afin de déterminer s'il constitue réellement une dette technique.

---

# 2. 🛠️ Présentation de Codacy

[Codacy](https://www.codacy.com/) est une plateforme d'analyse automatisée de la qualité du code.

Elle permet notamment d'analyser un dépôt Git et de détecter différents types de problèmes concernant :

- la qualité du code ;
- la sécurité ;
- les bonnes pratiques ;
- la complexité ;
- la duplication ;
- la maintenabilité.

Codacy attribue également des grades permettant d'avoir rapidement une vision synthétique de la qualité du projet ou de certains fichiers.

---

# 3. 🔎 Pourquoi Codacy ?

Codacy a été retenu pour cet audit car il permet d'obtenir rapidement une vision globale de la qualité du projet sans nécessiter l'installation et la configuration de nombreux outils d'analyse indépendants.

Il présente plusieurs intérêts pour ToDo & Co :

- analyse automatisée du dépôt ;
- interface permettant d'identifier rapidement les problèmes ;
- classification des problèmes par niveau de criticité ;
- analyse fichier par fichier ;
- suivi des évolutions après modification du code ;
- présentation synthétique sous forme de grades.

Codacy permet donc de compléter les analyses réalisées localement avec PHPUnit, Composer et les outils Symfony.

---

# 4. 🏗️ Périmètre de l'analyse

L'analyse porte sur le projet **ToDo & Co**.

Le code applicatif principal est notamment organisé autour de :

```text
src/
├── Controller/
├── Entity/
├── Form/
├── Repository/
├── Security/
└── ...
```

L'analyse concerne également certains fichiers de configuration du projet lorsque Codacy les prend en compte.

Deux fichiers ayant remonté des problèmes significatifs ont notamment été analysés :

```text
.env
docker/php/Dockerfile
```

Les fichiers générés automatiquement ou les répertoires ne contenant pas le code source pertinent doivent être exclus de l'analyse lorsque cela est nécessaire.

---

# 5. 📊 Analyse initiale

Une première analyse Codacy a été réalisée avant les corrections.

Elle constitue l'état de référence permettant de comparer les résultats après modification du projet.

## 5.1 Grade global

Le projet **ToDo & Co** obtient :

```text
Grade global : A
Nombre d'issues : 22
```

À titre de comparaison, le projet original `projet8-TodoList` obtenait :

```text
Grade global : B
```

Cette comparaison met en évidence une amélioration globale de la qualité du projet après les différents travaux de modernisation et d'évolution réalisés.

Cependant, le grade global ne doit pas être interprété comme une absence de dette technique.

---

# 6. 🚨 Problèmes identifiés

L'analyse initiale a notamment permis d'identifier des problèmes sur les fichiers suivants :

```text
.env
docker/php/Dockerfile
```

Les deux fichiers présentaient des problèmes de niveau différent.

---

# 7. 🔐 Analyse du fichier `.env`

## 7.1 Problème détecté

Codacy attribue initialement au fichier :

```text
Grade : F
```

Le problème est classé :

```text
CRITICAL
Security
Insecure Storage
```

Codacy détecte notamment la valeur suivante :

```dotenv
APP_SECRET=ThisTokenIsNotSoSecretChangeIt
```

Cette valeur correspond au secret Symfony fourni par défaut.

---

## 7.2 Risque identifié

La présence d'un secret directement dans un fichier susceptible d'être versionné présente un risque de sécurité.

Un secret utilisé par l'application ne doit pas être conservé dans le dépôt Git lorsqu'il est spécifique à l'environnement.

Dans le cas de Symfony, `APP_SECRET` est utilisé pour des mécanismes liés à la sécurité de l'application.

---

## 7.3 Correction appliquée

Le fichier `.env` versionné contient désormais :

```dotenv
APP_ENV=dev
APP_SECRET=
APP_SHARE_DIR=var/share
```

Le secret est désormais défini dans :

```text
.env.local
```

avec une valeur propre à l'environnement local.

Le fichier `.env.local` est exclu du versionnement grâce à `.gitignore` :

```gitignore
.env.local
.env.local.php
.env.\*.local
```

La configuration a également été vérifiée depuis le conteneur PHP avec :

```bash
php bin/console debug:container --env-vars
```

Le résultat confirme que `APP_SECRET` est bien résolu par Symfony.

---

## 7.4 Résultat après correction

Après la correction, Codacy attribue au fichier :

```text
.env
Avant : F
Après : A
```

Le problème de sécurité identifié initialement n'est donc plus remonté pour ce fichier.

---

# 8. 🐳 Analyse du Dockerfile

## 8.1 Problèmes détectés

Codacy attribue initialement au fichier :

```text
docker/php/Dockerfile
```

le grade :

```text
D
```

Deux recommandations de niveau **Medium** sont notamment identifiées.

---

## 8.2 Versions APT non figées

Codacy recommande de fixer explicitement les versions des paquets installés avec `apt-get`.

Le Dockerfile utilisait :

```dockerfile
RUN apt-get update && apt-get install -y \
 git \
 unzip \
 zip \
 ...
```

Codacy considère que l'absence de version explicite peut réduire la reproductibilité des builds.

## Impact

Si les versions disponibles dans les dépôts évoluent, une même construction Docker peut potentiellement produire des résultats différents dans le temps.

Cette recommandation concerne principalement la reproductibilité et la maintenance de l'environnement de build.

---

# 9. 📦 Packages recommandés par APT

Codacy recommande également l'utilisation de :

```bash
--no-install-recommends
```

afin d'éviter l'installation automatique de paquets recommandés mais non indispensables.

Le Dockerfile a donc été modifié afin d'utiliser :

```dockerfile
RUN apt-get update && apt-get install -y --no-install-recommends \
 git \
 unzip \
 zip \
 ...
```

Cette modification permet de limiter les dépendances installées dans l'image Docker.

---

# 10. 🧪 Vérification de l'image Docker

Après modification du Dockerfile, l'image PHP a été reconstruite avec :

```bash
docker compose build --no-cache php
```

Les conteneurs ont ensuite été démarrés avec :

```bash
docker compose up -d
```

L'état des services a été vérifié avec :

```bash
docker compose ps
```

L'application et les tests automatisés doivent également être vérifiés après cette modification.

---

# 11. 📈 Résultats après corrections

Une nouvelle analyse Codacy a été réalisée après les corrections.

Les résultats sont les suivants :

| Indicateur              | Avant | Après |            Évolution |
| ----------------------- | ----: | ----: | -------------------: |
| Grade global du projet  |     A |     A |             Maintenu |
| Nombre d'issues         |    22 |    14 |                   -8 |
| `.env`                  |     F |     A | Amélioration majeure |
| `docker/php/Dockerfile` |     D |     B |         Amélioration |

Le nombre d'issues passe donc de :

```text
22 → 14
```

soit une diminution de :

```text
36,4 %
```

du nombre d'issues détectées par Codacy.

---

# 12. 📊 Interprétation des résultats

## Grade global

Le grade global reste :

```text
A
```

avant et après les corrections.

Cela ne signifie pas que le projet ne présente aucun problème.

Le grade global constitue une synthèse. Une amélioration du nombre d'issues peut ne pas entraîner de changement de grade lorsque le projet se trouve déjà dans la meilleure catégorie.

---

## Nombre d'issues

La diminution :

```text
22 → 14
```

constitue un indicateur concret de l'amélioration obtenue.

Huit problèmes détectés initialement ne sont plus présents après les modifications.

Le projet conserve cependant :

```text
14 issues
```

qui devront être analysées dans le cadre de l'audit global.

---

## `.env`

Le passage :

```text
F → A
```

constitue l'amélioration la plus importante parmi les fichiers analysés.

Le problème concernait une recommandation de sécurité critique liée au stockage du secret Symfony.

---

## Dockerfile

Le passage :

```text
D → B
```

montre également une amélioration significative.

La modification concernant `--no-install-recommends` a permis de répondre à l'une des recommandations détectées.

Certaines recommandations peuvent néanmoins rester présentes, notamment concernant le versionnement explicite des paquets APT.

---

# 13. 📸 Captures d'écran

Les captures permettant de comparer les deux états sont conservées dans :

```text
docs/audit/final/screenshots/codacy/
```

Organisation recommandée :

```text
codacy/
├── preview/
│ ├── codacy-dashboard.png
│ └── codacy-issues.png
└── after/
├── codacy-dashboard.png
└── codacy-issues.png
│
└── files/
├── before/
│ ├── env-grade-f.png
│ └── dockerfile-grade-d.png
│
└── after/
├── env-grade-a.png
└── dockerfile-grade-b.png
```

Les captures `before` permettent de conserver l'état initial du projet.

Les captures `after` permettent de vérifier les résultats obtenus après les corrections.

---

# 14. 🧩 Dette technique restante

L'analyse finale indique encore :

```text
14 issues
```

Ces problèmes ne doivent pas nécessairement être corrigés immédiatement.

Chaque issue devra être analysée selon :

- sa criticité ;
- son impact réel sur l'application ;
- son impact sur la sécurité ;
- son impact sur la maintenabilité ;
- sa probabilité de provoquer une régression ;
- le coût de sa correction.

Les problèmes présentant un risque important pourront faire l'objet d'issues dédiées.

Les recommandations de faible impact peuvent être conservées comme dette technique documentée.

---

# 15. ⚠️ Limites de Codacy

Codacy constitue une aide à l'analyse mais ne remplace pas l'analyse humaine du projet.

Une issue détectée automatiquement ne signifie pas nécessairement qu'une correction est obligatoire.

Inversement, l'absence d'issue ne garantit pas l'absence de problème.

L'analyse humaine reste nécessaire pour évaluer :

- la pertinence de l'architecture ;
- la compréhension du code ;
- la cohérence métier ;
- la qualité des tests ;
- les choix techniques ;
- les performances réelles de l'application.

Codacy doit donc être considéré comme un outil d'aide à la détection et à la priorisation des problèmes.

---

# 16. 🔄 Reproductibilité de l'analyse

Pour reproduire l'analyse :

1. ouvrir le dépôt **ToDo & Co** dans Codacy ;
2. vérifier que la branche analysée correspond à la version souhaitée ;
3. lancer ou attendre l'analyse automatique ;
4. consulter le tableau de bord ;
5. consulter les issues détectées ;
6. vérifier les grades des fichiers concernés.

Les résultats doivent être comparés avec les captures et métriques conservées dans le projet.

---

# 17. 📌 Synthèse

L'audit Codacy a permis d'identifier plusieurs problèmes de qualité dans le projet.

Les corrections ciblées ont notamment permis :

```text
22 issues
↓
14 issues

.env
F → A

Dockerfile
D → B
```

Le grade global du projet reste :

```text
A
```

Le projet présente donc une amélioration mesurable de sa qualité, tout en conservant une dette technique résiduelle qui devra être analysée dans le cadre de l'audit global.

---

# Conclusion

Codacy a permis d'obtenir une première vision objective de la qualité du projet **ToDo & Co** et de mesurer l'impact des corrections réalisées.

L'analyse avant/après montre une réduction de **22 à 14 issues**, soit une diminution de **36,4 %** des problèmes détectés.

Les principales corrections ont concerné :

- la sécurisation du secret Symfony ;
- l'amélioration de l'installation des dépendances dans l'image Docker.

Ces résultats constituent une première partie de l'audit global de qualité du projet.

Les analyses complémentaires concernant les tests, la couverture de code, les dépendances, la complexité, la duplication et les performances doivent être prises en compte avant de tirer une conclusion définitive sur la qualité globale de l'application.
