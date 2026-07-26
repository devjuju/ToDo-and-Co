# 03 ⚙️ Configuration Symfony

## Base de données

Le projet Symfony 3.1 utilisait initialement une base locale :

database_host: 127.0.0.1

Dans Docker, le service PHP doit communiquer avec le conteneur MySQL.
La valeur a donc été adaptée :

database_host: mysql

## Validation

Commandes utilisées :

php bin/console doctrine:database:create
php bin/console doctrine:schema:update --force
php bin/console doctrine:schema:validate
