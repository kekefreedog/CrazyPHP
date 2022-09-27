# MariaDB with Docker

## Start shell of MySQL

Use bellow command

```sh
docker-compose exec mysql bash 
```

## Connect to MySQL bash

1. Get container name of mysql

```sh
docker-compose ps 
# Result : crazytest-mariadb-1
```

2. Connect to database as admin

```sh
docker exec -it crazytest-mariadb-1 mysql -p
```