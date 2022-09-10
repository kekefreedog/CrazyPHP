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
