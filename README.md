# CrazyPHP

My crazy framework for creating ultra-fast webapps.

## Project

New project :

```sh
php vendor/kzarshenas/crazyphp/bin/CrazyCommand new project
```

Update project :
```sh
php vendor/kzarshenas/crazyphp/bin/CrazyCommand update project
```

Delete project :
```sh
php vendor/kzarshenas/crazyphp/bin/CrazyCommand delete project
```

## Docker (optionnal)

Create docker compose :

```sh
php vendor/kzarshenas/crazyphp/bin/CrazyDocker new
```

## Dev

Run test (usign PHPUnit)

```sh
php vendor/phpunit/phpunit/phpunit
```

## Default env variables

> Env variables of the app are stored in `$GLOBALS["__CRAZY_APP"]`

|Name|Tokken|Type|Description|
|-|-|-|-|
|CRAZY_ROOT|@crazyphp_root|\<string>|Root of crazyphp vendor folder|
|APP_ROOT|@app_root|\<string>|Root of your crazy application|
|PHPUNIT_TEST|@phpunit_test|\<bool>|Determine if we are in a test context|

# Documentation

- Api
    - [Schema of Api Response](docs/Api/SchemaApiResponse.md) : Schema of a json request from backend.
- Docker
    - [Mariadb](docs/Docker/Mariadb.md) : Docker command for manipulate Mariadb
    - [Mongo](docs/Docker/Mongo.md) : Docker command for manipulate Mongo DB
    - [Mysql](docs/Docker/Mysql.md) : Docker command for manipulate Mysql
    - [Node](docs/Docker/Node.md) : Docker command for manipulate NodeJS
    - [Php](docs/Docker/Php.md) : Docker command for manipulate PHP
    - [Postgresql](docs/Docker/Postgresql.md) : Docker command for manipulate Postgresql
- Script
    - [Comment Headers](docs/Src/CommentHeaders.md) : Rules for the comment header of files like PHP, JS, JSON, YAML...
    - [Condig Router](docs/Src/ConfigRouter.md) : Rules for config router
- Structure
    - [Create Folder Structure](docs/Structure/CreateFolderStructure.md) : Tutoriel to learn how to create folder schema
