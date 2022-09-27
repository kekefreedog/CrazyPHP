# Mongo DB with Docker

## Start shell

Use bellow command

```sh
docker-compose exec mongo bash
```

## Access to database

Use bellow code inside container shell

```sh
mongosh -u ${MONGO_INITDB_ROOT_USERNAME} -p ${MONGO_INITDB_ROOT_PASSWORD}
```

