# Projet de groupe Arcade

## Installation

### Cloner le projet
```
git clone https://github.com/Kevins713/Arcade.git
```

### Créer un fichier .env.local et réecrire les paramètres d'environnement dans le fichier .env (changer user_db et password_db et les identifiant du compte pour envoyer les mails, voir trello pour le compte)

```

MAILER_DSN=gmail://email:password@default?

DATABASE_URL="mysql://user_db:password_db@127.0.0.1:3306/arcade?serverVersion=8.0.12"


```

### Déplacer le terminal dans le dossier cloné
```
cd arcade
```

### Taper les commandes suivantes :
```
composer install
php bin/console doctrine:database:create
php bin/console make:migration
php bin/console doctrine:migrations:migrate
php bin/console assets:install public
php bin/ console ckeditor:install
php bin/ console assets:install public

```
