> IN DEV FEATURE

### 1. Docker-composer

- Required to modifify docker composer by adding :

```yml
    # New Workerman service for WebSockets
    php-websocket:
        build: docker/php-websocket
        restart: always
        working_dir: '${PWD}'
        ports:
            - '${CRAZY_WEBSOCKET_PORT:-2346}:2346'
        volumes:
            - '.:${PWD}'
            - './vendor/kzarshenas/crazyphp:/Users/kzarshenas/Sites/CrazyProject/CrazyPHP'
        command: './docker/php-websocket/start-websocket.sh'
        environment:
            - PHP_DISPLAY_ERRORS=1
        networks:
            - backend
```

### 2. Variables

- Required to append below line into variables.env

```
CRAZY_WEBSOCKET_PORT=2346
```