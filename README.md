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

## Router

New router

```sh
php vendor/kzarshenas/crazyphp/bin/CrazyCommand new router
```

Delete router

```sh
php vendor/kzarshenas/crazyphp/bin/CrazyCommand delete router
```

## Trash

Clean trash

> When you remove router... a backup of the files removed are copy into the trash.

```sh
php vendor/kzarshenas/crazyphp/bin/CrazyCommand delete trash
```

## Docker (optionnal)

Create docker compose :

```sh
php vendor/kzarshenas/crazyphp/bin/CrazyDocker new
```

Delete docker compose :

```sh
php vendor/kzarshenas/crazyphp/bin/CrazyDocker delete
```

Up docker compose :

```sh
php vendor/kzarshenas/crazyphp/bin/CrazyDocker up
```

Down docker compose :

```sh
php vendor/kzarshenas/crazyphp/bin/CrazyDocker down
```

## Dev

Run test (usign PHPUnit)

```sh
php vendor/phpunit/phpunit/phpunit
```

## Default env variables

> Env variables of the app are stored in `$GLOBALS["__CRAZY_APP"]`

| Name                   | Tokken                  | Type      | Description                                              |
| ---------------------- | ----------------------- | --------- | -------------------------------------------------------- |
| CRAZY_ROOT             | @crazyphp_root          | \<string> | Root of crazyphp vendor folder                           |
| APP_ROOT               | @app_root               | \<string> | Root of your crazy application                           |
| PHPUNIT_TEST           | @phpunit_test           | \<bool>   | Determine if we are in a test context                    |
| CONFIG_LOCATION        | @config_location        | \<string> | Determine the location of the configs files              |
| ROUTER_APP_PATH        | @router_app_path        | \<string> | Determine the path of the front files of the routers     |
| ROUTER_CONTROLLER_PATH | @router_controller_path | \<string> | Determine the path of the back end controller of routers |
| TRASH_PATH             | @trash_path             | \<string> | Determine the path of the trash                          |
| TRASH_DISABLE          | @trash_disable          | \<string> | Determine if the trash is disable                        |

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
