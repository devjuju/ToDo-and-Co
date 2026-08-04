# ✅ Validation fonctionnelle sous Symfony 6.4

## Objectif

Après la migration de l'application vers Symfony 6.4.43 LTS, une phase de validation fonctionnelle a été réalisée afin de vérifier que les principales fonctionnalités historiques de ToDo & Co sont toujours opérationnelles.

Cette étape permet de détecter les régressions introduites par la migration et de corriger les incompatibilités avant de poursuivre les développements fonctionnels (gestion des rôles, sécurisation des accès, tests automatisés...).

---

## Environnement de test

Les vérifications ont été réalisées avec l'environnement suivant :

| Élément      |        Version |
| ------------ | -------------: |
| Symfony      |     6.4.43 LTS |
| PHP          |            8.x |
| Doctrine ORM |            2.x |
| Twig         |            3.x |
| MySQL        |            5.7 |
| Docker       | Docker Compose |

---

## Vérifications techniques

### Version Symfony

Commande exécutée :

```bash
docker compose exec php php bin/console --version
```

Résultat :

```text
Symfony 6.4.43 (env: dev, debug: true)
```

---

### Cache Symfony

Commande :

```bash
php bin/console cache:clear
```

Résultat :

```text
[OK] Cache successfully cleared.
```

Les caches `dev` et `prod` ont également été supprimés manuellement lors de certaines corrections :

```bash
rm -rf var/cache/*
rm -rf app/cache/*
```

Cette opération s'est révélée nécessaire après plusieurs modifications de la configuration Security.

---

### Doctrine

Validation :

```bash
php bin/console doctrine:schema:validate
```

Résultat final :

```text
Mapping OK
Database OK
```

Au cours de la migration, Doctrine signalait une désynchronisation du schéma.

La commande suivante a permis de mettre la base de données en conformité :

```bash
php bin/console doctrine:schema:update --force --complete
```

---

### Mapping Doctrine

Commande :

```bash
php bin/console doctrine:mapping:info
```

Résultat :

```text
AppBundle\Entity\User
AppBundle\Entity\Task
```

Les deux entités sont correctement reconnues.

## Corrections réalisées

La validation fonctionnelle a permis d'identifier plusieurs incompatibilités introduites par Symfony 6.

### Suppression de `getDoctrine()`

La méthode :

```PHP
$this->getDoctrine()
```

n'est plus disponible.

Elle a été remplacée par l'injection de dépendance :

```PHP
EntityManagerInterface
```

Exemple :

```PHP
$entityManager->getRepository(Task::class)->findAll();
```

---

### Gestion des formulaires

Symfony 6 impose de vérifier qu'un formulaire est soumis avant d'appeler `isValid()`.

Ancien code :

```PHP
if ($form->isValid())
```

Nouveau code :

```PHP
if ($form->isSubmitted() && $form->isValid())
```

Cette correction a été appliquée aux formulaires :

- création d'utilisateur ;
- modification d'utilisateur ;
- création de tâche ;
- modification de tâche.

---

### Hachage des mots de passe

L'ancien service :

```PHP
UserPasswordEncoderInterface
```

n'existe plus.

Il a été remplacé par :

```PHP
UserPasswordHasherInterface
```

avec :

```PHP
$passwordHasher->hashPassword(...)
```

---

### Configuration Security

La configuration Security a été adaptée afin d'être compatible avec Symfony 6.

Les principales modifications concernent :

- remplacement de `encoders` par `password_hashers` ;
- remplacement du raccourci Doctrine `AppBundle:User` par le nom de classe complet `AppBundle\Entity\User`;
- activation du gestionnaire d'authentification moderne ;
- correction de la configuration CSRF du formulaire de connexion.

---

### Entité User

L'entité `User` a été adaptée aux nouvelles interfaces Symfony.

Les évolutions principales sont :

- implémentation de `PasswordAuthenticatedUserInterface` ;
- ajout de `getUserIdentifier()` ;
- ajout de la propriété `roles` ;
- mise à jour de `getRoles()`.

---

## Vérifications fonctionnelles

### Authentification

| Fonctionnalité                     | Résultat |
| ---------------------------------- | -------- |
| Accès à la page de connexion       | ✅       |
| Connexion utilisateur              | ✅       |
| Déconnexion                        | ✅       |
| Accès refusé sans authentification | ✅       |

Plusieurs anomalies ont été corrigées :

- erreur `AppBundle:User` avec Doctrine Persistence 3 ;
- erreur CSRF lors de la connexion ;
- route `login_check` non reconnue ;
- route `logout` indisponible ;
- erreur 500 après migration.

---

### Gestion des utilisateurs

Les fonctionnalités suivantes sont opérationnelles :

- consultation de la liste des utilisateurs ;
- création d'un utilisateur ;
- modification d'un utilisateur.

La création d'un utilisateur a été validée avec succès via le formulaire.

Exemple :

```text
Nom : user
Email : user@example.com
Mot de passe : user123
```

L'utilisateur apparaît correctement dans la liste après validation.

---

### Gestion des tâches

Les fonctionnalités historiques sont de nouveau opérationnelles :

- consultation de la liste ;
- création d'une tâche ;
- modification d'une tâche ;
- changement d'état ;
- suppression d'une tâche.

Les erreurs liées à `getDoctrine()` et aux formulaires ont été corrigées.

---

## Fonctionnalités restant à implémenter

La validation fonctionnelle a également permis d'identifier les fonctionnalités encore absentes.

### Gestion des rôles

Le formulaire de création d'utilisateur ne permet pas encore de choisir :

- ROLE_USER
- ROLE_ADMIN

Cette fonctionnalité sera développée dans l'étape **Gestion des rôles**.

---

### Sécurisation des accès

Les contrôles suivants restent à mettre en place :

- accès réservé aux administrateurs pour la gestion des utilisateurs ;
- suppression des tâches selon leur propriétaire ;
- prise en compte des tâches anonymes ;
- vérification des Voters.

Cette partie sera réalisée dans l'étape **Sécurisation des accès**.

---

## Résultat

La migration Symfony 6.4 est désormais validée sur le plan technique et fonctionnel.

Les principales fonctionnalités historiques de l'application sont de nouveau opérationnelles :

- authentification ;
- gestion des utilisateurs ;
- gestion des tâches ;
- Doctrine ;
- configuration Security.

Les incompatibilités majeures introduites par Symfony 6 ont été corrigées avec succès.

---

## Conclusion

Cette phase de validation a permis de confirmer que l'application est stable sous Symfony 6.4.43 LTS.

Les corrections apportées garantissent un fonctionnement équivalent à celui des versions précédentes tout en utilisant les composants modernes de Symfony.

La prochaine étape consistera à implémenter les fonctionnalités demandées par le cahier des charges, notamment la **gestion des rôles**, la **sécurisation des accès** et les **tests automatisés**.
