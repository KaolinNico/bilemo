### Informations utiles

> **Version PHP :** 7.2  
> **Base de données :** MySQL 5.7  
> **Qualité du code :** [![Codacy Badge](https://api.codacy.com/project/badge/Grade/07cef2e9187e477299261facc62f74f0)](https://app.codacy.com/manual/Nicolas_21/snowtricks?utm_source=github.com&utm_medium=referral&utm_content=KaolinNico/snowtricks&utm_campaign=Badge_Grade_Dashboard) - [Détails](https://api.codacy.com/project/badge/Grade/07cef2e9187e477299261facc62f74f0)
> **Documentation :** {votredomaine}/api/v1/documentation  


### Installation

* Se positionner dans le répertoire souhaité et récupérer le projet à l'aide de la commande
```
git clone https://github.com/KaolinNico/bilemo.git
```
* Modifier le fichier .env avec vos informations de base de données
* Pour installer l'ensemble des dépendances nécessaires au fonctionnement du site, éxécutez la commande suivante
```
composer install
```
* Exécutez les commandes suivantes pour installer la base de données
```
php bin/console doctrine:database:create
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```
* Pour intégrer les données de démo dans la base de données, éxécutez la commande suivante
```
php bin/console hautelook:fixtures:load
```

Il est maintenant possible d'obtenir un token d'authentification avec un compte utilisateur d'exemple :
> username : "company_1"  
> password : "password"

