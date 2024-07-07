# New CrazyPHP project

Open a terminal and go on the directory where you want create your project (Exemple : `/mnt/data/projects/MyCrazyProject`)

## 1. Check requirements

- [ ] Check [PHP](https://www.php.net/) version is >= 8.2 by using :

```sh
php -v
# PHP 8.3.4 (cli) (built: Mar 16 2024 12:05:08) (NTS
```

- [ ] Check [Composer](https://getcomposer.org/) version is >= 2.5.8 by using :

```sh
composer -V
# Composer version 2.5.8 2023-05-24 15:00:39
```

## 2. Install Crazy PHP

- [ ] Install last version of [CrazyPHP](https://github.com/kekefreedog/CrazyPHP) by using :

```sh
composer require kzarshenas/crazyphp
```

> For work on development mode, visit this [link](Misc/CrazyDevelopment.md)

- [ ] **(Optional)** Link to local CrazyPHP repo (for development & debug), update the `composer.json` with the content below :

> If you are using OS Windows, make sure to execute `npm i` into the CrazyPHP folder

```json
{
    "require": {
        "kzarshenas/crazyphp": "@dev"
    },
    "repositories": [
        {
            "type": "path",
            "url": "./../../CrazyProject/CrazyPHP", // Path of your CrazyPHP installation
            "options": {
                "symlink": true
            }
        }
    ]
}
```

> If you are using Docker, do not forget to add `- '/etc/CrazyPHP:/etc/CrazyPHP'` on you docker compose config as volume for the service php-fpm

And then execute the command below in the terminal :

```sh
composer update
```

- [ ] Execute the new project cli command by using :

```sh
php vendor/kzarshenas/crazyphp/bin/CrazyCommand new project
# 🎉 New project created with success 🎉
```

## 3. Install Docker

If you want use Docker to launch your app, read this tuto [Install Docker](Docker/InstallDocker.md)