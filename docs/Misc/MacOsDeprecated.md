# Mac OS Deperecated

## MacPorts

If you are working on Mac OS Catalina (wich is deperecated by brew and apple), and you don't have the right php version. You have to get admin permission, then you can install a recent version of php with Macports :
- https://ports.macports.org/port/php/
- https://ports.macports.org/port/php_select/
- https://trac.macports.org/wiki/howto/PHP#modules

## Docker

You can also use Docker to generate a php container, but it has some limitation like symlink...

- [ ] If you can't install php 8.1 on your mac but you have docker, you can add the bellow lines into the file `~/.zshrc` :
    ```sh
    # Docker php
    alias php="docker run --rm --interactive --tty --volume $PWD:/app -w /app php:8.1-cli-alpine php -dzend_extension=opcache.so -dopcache.enable_cli=1 -dopcache.jit_buffer_size=500000000 -dopcache.jit=1235"
    alias composer="docker run --rm --interactive --tty --volume $PWD:/app composer/composer"
    ```

- [ ] Then use commande the below command to update your alias on your environnement :
    ```sh
    source ~/.zshrc
    ```

