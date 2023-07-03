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

If you have this error when you up your docker (b2052220039f2cdd5bb40973b4c1b10ba37be6d4a63f3732afcea2f04ea53f9a): `Bind for 0.0.0.0:48000 failed: port is already allocated`, just change the port 48000 (at left) by what you want in the `docker-compose.yml` file :
```yml
[...]
ports:
    - '48000:80'
[...]
```