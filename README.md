# CrazyPHP

My crazy framework for creating ultra-fast webapps.

## Installation

Install via composer :

```
composer require kzarshenas/crazyphp
```

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

## Router Type

New router type

```sh
php vendor/kzarshenas/crazyphp/bin/CrazyCommand new routerType
```

Delete router type

```sh
php vendor/kzarshenas/crazyphp/bin/CrazyCommand delete routerType
```

## Extension

New router type

```sh
php vendor/kzarshenas/crazyphp/bin/CrazyCommand new extension
```

Update extension

```sh
php vendor/kzarshenas/crazyphp/bin/CrazyCommand update extension
```

Delete router type

```sh
php vendor/kzarshenas/crazyphp/bin/CrazyCommand delete extension
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
| ROUTER_TYPE_PATH       | @router_type_path       | \<string> | Determine the path of the back end router type           |
| TRASH_PATH             | @trash_path             | \<string> | Determine the path of the trash                          |
| TRASH_DISABLE          | @trash_disable          | \<string> | Determine if the trash is disable                        |
| PARAMETERS_URL_OVERRIDE| @parameters_url_override| \<object> | Override parameters passed by url                        |

# Documentation

- [New Project](docs/NewProject.md) : How to create a new crazy project
- Api
  - Api v2
    - [About Api V2](docs/Api/Api2/About.md) : All you need to know about Api v2
  - [Schema of Api Response](docs/Api/SchemaApiRequest.md) : Schema of a api request.
  - [Schema of Api Response](docs/Api/SchemaApiResponse.md) : Schema of a json request from backend.
  - [Special Get Parameters](docs/Api/SpecialsGetParameters.md) : Special get parameters
- CLI
  - [Crazy Asset](docs/Cli/CrazyAsset.md) : Register a Crazy Asset
  - [Crazy Router](docs/Cli/CrazyRouter.md) : Register a Crazy Router
- Core
  - [Context](docs/Core/Context.md) : Data defined in context
- Docker
  - [Install Docker](docs/Docker/InstallDocker.md) : Setup docker of your app
  - [Mariadb](docs/Docker/Mariadb.md) : Docker command for manipulate Mariadb
  - [Mongo](docs/Docker/Mongo.md) : Docker command for manipulate Mongo DB
  - [Mysql](docs/Docker/Mysql.md) : Docker command for manipulate Mysql
  - [Node](docs/Docker/Node.md) : Docker command for manipulate NodeJS
  - [Php](docs/Docker/Php.md) : Docker command for manipulate PHP
  - [Postgresql](docs/Docker/Postgresql.md) : Docker command for manipulate Postgresql
- Model
  - [About Model](docs/Model/About%20Model.md) : How to define a model with Api v2
- Script
  - [Comment Headers](docs/Src/CommentHeaders.md) : Rules for the comment header of files like PHP, JS, JSON, YAML...
  - [Condig Router](docs/Src/ConfigRouter.md) : Rules for config router
  - [Context](docs/Src/Context.md) : Schema of the context
  - [Favicon](docs/Src/Favicon.md) : Schema of favicons
  - [Test](docs/Src/Test.md) : Test of the code
- Structure
  - [Create Folder Structure](docs/Structure/CreateFolderStructure.md) : Tutoriel to learn how to create folder schema
