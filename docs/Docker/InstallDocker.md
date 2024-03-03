# Install Docker

## 1. Create docker compose

- [ ] Use the bellow command to create a docker compose file :
    ```sh
    php vendor/kzarshenas/crazyphp/bin/CrazyDocker new
    ```

## 2. Launch docker compose

- [ ] Use the bellow command to start the docker compose file :
    ```sh
    php vendor/kzarshenas/crazyphp/bin/CrazyDocker up
    ```

> Use the argument `down` to turn off the docker

## Common issues

### Port issue

If you have this error when you up your docker (b2052220039f2cdd5bb40973b4c1b10ba37be6d4a63f3732afcea2f04ea53f9a): `Bind for 0.0.0.0:48000 failed: port is already allocated`, just change the port 48000 (at left) by what you want in the `docker-compose.yml` file :
```yml
[...]
ports:
    - '48000:80'
[...]
```

### Local CrazyPHP issue

On up, if you got this error :
```sh
🟠 Run prepare database
PHP Fatal error:  Uncaught Error: Class "CrazyPHP\Model\Env" not found in /Users/kzarshenas/Sites/RodeoFx/rodeo_toolkit/docker/bin/SetupDatabase:23
🟡 Relaunch prepare database...
```
That maybe because you are requiering CrazyPhp on a local way, so you need to declare is path on the docker compose.

On `docker-compose.yml` you just need to add a line that give access to you crazy php root folder into your docker instance 
```yml
volumes:
- './../../CrazyProject/CrazyPHP:/Users/kzarshenas/Sites/CrazyProject/CrazyPHP'
```