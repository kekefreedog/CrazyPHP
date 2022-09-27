# PostgreSQL with Docker

## Start shell of PostgreSQL

Use bellow command

```sh
docker-compose exec postgresql bash 
```

## Access to database

In the container shell, use bellow code

```sh
psql --username=${POSTGRES_USER} ${POSTGRES_DB}
```