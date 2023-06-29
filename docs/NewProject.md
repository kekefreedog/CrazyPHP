# New CrazyPHP project

Open a terminal and go on the directory where you want create your project (Exemple : `/mnt/data/projects/MyCrazyProject`) 

## 1. Check requirements

- [ ] Check [PHP](https://www.php.net/) version is >= 8 by using :
    ```sh
    php -v
    # PHP 8.1.8 (cli) (built: Jul  8 2022 10:46:35) (NTS)
    ````
- [ ] Check [Composer](https://getcomposer.org/) version is >= 2 by using :
    ```sh
    composer -V
    # Composer version 2.5.7 2023-05-24 15:00:39
    ````

## 1.1. MacOS without PHP 8.1

- [ ] If you can't install php 8.1 on your mac but you have docker, you can add the bellow lines into the file `~/.zshrc` :
    ```s
    # Docker php
    alias php="docker run --rm --interactive --tty --volume $PWD:/app -w /app php:8.1-cli-alpine php -dzend_extension=opcache.so -dopcache.enable_cli=1 -dopcache.jit_buffer_size=500000000 -dopcache.jit=1235"
    alias composer="docker run --rm --interactive --tty --volume $PWD:/app composer/composer"
    ```

- [ ] Then use commande the below command to update your alias on your environnement :
    ```sh
    source ~/.zshrc
    ```

## 2. Install Crazy PHP
  
- [ ] Install last version of [CrazyPHP](https://github.com/kekefreedog/CrazyPHP) by using :
    ```sh
    composer require kzarshenas/crazyphp
    ````
- [ ] Execute the new project cli command by using :
    ```sh
    php vendor/kzarshenas/crazyphp/bin/CrazyCommand new project
    # 🎉 New project created with success 🎉
    ```

## 3. Install Docker

If you want use Docker to launch your app, read this tuto [Install Docker](Docker/InstallDocker.md)