# 🛢️ Configuration de la base de données

## Objectif

Configurer Symfony pour communiquer avec la base de données MySQL de l'environnement Docker.

---

## Création du fichier parameters.yml

Le fichier `parameters.yml` n'est pas versionné.

Il est créé à partir du modèle :

app/config/parameters.yml.dist

```bash
cp app/config/parameters.yml.dist app/config/parameters.yml
```

---

## Configuration

Les paramètres de connexion ont été adaptés à Docker.

| Paramètre | Valeur   |
| --------- | -------- |
| Host      | mysql    |
| Port      | 3306     |
| Database  | todolist |
| User      | root     |
| Password  | root     |

---

## Vérifications

```bash
php bin/console --version
php bin/console doctrine:schema:update --force
php bin/console doctrine:mapping:info
php bin/console doctrine:schema:validate
```

---

## Résultat

Symfony 3.4 est désormais entièrement opérationnel avec MySQL.

La migration vers Symfony 4.4 peut commencer.
