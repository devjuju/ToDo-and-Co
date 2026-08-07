# Association automatique des tâches aux utilisateurs

## Contexte

Avant cette évolution, une tâche pouvait être créée sans utilisateur associé.

Cette absence de relation posait plusieurs problèmes :

- impossible d'identifier l'auteur d'une tâche ;
- impossibilité d'appliquer des règles d'autorisation fiables ;
- difficulté à assurer la traçabilité des actions réalisées dans l'application.

L'objectif de cette évolution est de garantir que chaque tâche possède toujours un utilisateur propriétaire.

---

## Fonctionnement général

Une tâche appartient désormais obligatoirement à un utilisateur.

La relation mise en place est :

```text
User (1) -------- (\*) Task
```

Un utilisateur peut posséder plusieurs tâches.

Une tâche appartient à un seul utilisateur.

Cette relation permet notamment :

- d'identifier l'auteur d'une tâche ;
- de sécuriser les actions de modification et suppression ;
- d'utiliser les données utilisateur dans les règles d'autorisation Symfony.

---

## Modifications réalisées

### Entité Task

Fichier concerné :

```text
src/Entity/Task.php
```

Une relation Doctrine `ManyToOne` vers l'entité `User` a été ajoutée.

Exemple :

```php
/**
 * @ORM\ManyToOne(targetEntity=User::class, inversedBy="tasks")
 * @ORM\JoinColumn(nullable=false)
 */
private $user;
```

La contrainte nullable=false garantit qu'une tâche ne peut pas exister sans utilisateur associé.

La base de données impose donc désormais la présence d'un user_id.

Méthodes ajoutées

L'entité Task possède maintenant les méthodes permettant de manipuler son propriétaire :

```php
public function getUser(): ?User
{
return $this->user;
}

public function setUser(User $user): self
{
$this->user = $user;

    return $this;

}
```

Le setter permet d'associer une tâche à un utilisateur avant son enregistrement.

---

### Entité User

Fichier concerné :

src/Entity/User.php

L'utilisateur possède maintenant une collection de tâches associées.

Relation :

```php
/**
 * @ORM\OneToMany(
 *     targetEntity=Task::class,
 *     mappedBy="user"
 * )
 */
private $tasks;
```

Un utilisateur peut donc récupérer toutes les tâches dont il est propriétaire.

Exemple :

```php
$user->getTasks();
```

---

## Association automatique lors de la création

Lorsqu'un utilisateur connecté crée une tâche, il n'a pas la possibilité de choisir son propriétaire.

Le propriétaire est automatiquement récupéré depuis la session Symfony.

Le processus est le suivant :

- L'utilisateur se connecte.
- Symfony stocke l'utilisateur authentifié dans le contexte de sécurité.
- Lors de la création d'une tâche, le contrôleur récupère cet utilisateur.
- La tâche est associée automatiquement.
- La relation est enregistrée en base.

Exemple dans TaskController :

```php
$user = $this->getUser();

$task->setUser($user);

$entityManager->persist($task);
$entityManager->flush();
```

Le formulaire de création ne contient donc aucun champ permettant de sélectionner un utilisateur.

---

## Modification d'une tâche

Lorsqu'une tâche existante est modifiée :

son utilisateur propriétaire est conservé ;
aucun champ utilisateur n'est affiché dans le formulaire ;
l'auteur ne peut pas être remplacé.

Cela garantit que l'auteur original reste toujours identifiable.

Exemple :

```text
Tâche créée par l'utilisateur test

Modification réalisée par l'utilisateur test
```

Résultat :

```text
La tâche appartient toujours à l'utilisateur test
```

---

## Gestion des anciennes tâches

Lors de la mise en place de cette relation obligatoire, certaines tâches existaient déjà sans utilisateur.

Afin de respecter la nouvelle contrainte NOT NULL, un utilisateur spécifique a été créé :

```text
username : anonymous
email : anonymous@todo.local
```

Cet utilisateur représente les anciennes données dont l'auteur réel n'était pas connu.

Les tâches existantes sans utilisateur ont été associées automatiquement à ce compte.

---

## Migration des données

La migration a effectué les opérations suivantes :

Création du compte utilisateur anonyme.
Recherche des tâches sans utilisateur.
Association de ces tâches au compte anonymous.
Modification de la colonne user_id pour interdire les valeurs nulles.

Après migration :

Avant :

```text
Task
|
+-- user_id = NULL
```

Après :

```text
Task
|
+-- user_id = anonymous
```

---

## Validation de la base de données

Plusieurs contrôles ont été réalisés.

Validation du mapping Doctrine

Commande :

```bash
php bin/console doctrine:schema:validate
```

Résultat attendu :

```text
Mapping : OK
Database : OK
```

---

## Vérification des relations Doctrine

Commande :

```bash
php bin/console doctrine:mapping:info
```

Permet de vérifier que les entités sont correctement reconnues.

---

Vérification du schéma SQL

La colonne :

```text
task.user_id
```

est maintenant obligatoire :

```text
NOT NULL
```

---

## Choix techniques

Pourquoi une relation ManyToOne ?

Une tâche appartient à un seul utilisateur.

Un utilisateur peut cependant posséder plusieurs tâches.

La relation correspond donc naturellement à :

```text
User 1 -> N Task
```

Pourquoi ne pas laisser l'utilisateur choisir son auteur ?

Permettre à un utilisateur de choisir un propriétaire aurait créé plusieurs problèmes :

- usurpation possible de l'auteur ;
- incohérence des données ;
- difficultés pour appliquer les règles de sécurité.

L'auteur doit toujours être déterminé automatiquement par l'utilisateur connecté.

---

## Préparation de la sécurité

Cette évolution prépare l'utilisation des règles d'autorisation Symfony.

Grâce à cette relation, il devient possible de vérifier :

- si un utilisateur est propriétaire d'une tâche ;
- si un administrateur peut intervenir sur toutes les tâches ;
- si une tâche appartient au compte anonyme.

Ces règles seront implémentées avec un Symfony Voter.

---

## Conclusion

L'association automatique des tâches aux utilisateurs est désormais opérationnelle.

Chaque tâche possède maintenant un propriétaire identifié, enregistré automatiquement lors de sa création grâce à l'utilisateur authentifié. Cette évolution garantit une meilleure traçabilité des actions et permet d'assurer l'intégrité des données.

Les anciennes tâches ne possédant pas d'utilisateur ont été migrées vers le compte `anonymous`, permettant de respecter la nouvelle contrainte d'intégrité en base de données (`user_id NOT NULL`).

La relation entre les entités `Task` et `User` constitue également une base technique indispensable pour la mise en place des règles d'autorisation. Elle permettra notamment de contrôler les droits de modification et de suppression des tâches grâce aux mécanismes de sécurité Symfony, notamment les Voters.

Les prochaines étapes concernent donc l'exploitation de cette relation pour sécuriser les actions sur les tâches et ajouter les tests automatisés permettant de garantir le bon fonctionnement de ces règles.
