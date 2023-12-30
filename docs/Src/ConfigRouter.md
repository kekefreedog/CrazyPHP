# Fill Config Router

## Where find the router config

You can find the yaml collection here : 
```
@crazy_app/config/Router.yml
```

## Router organization 

### `app`

Exemple of config with all options well filled :
```yaml
  # Name of router
- name: Home
  # Controller to call
  controller: App\Controller\App\Home
  patterns:
    - /index/
  # HTTP Method calling static method of controller with same name
  methods:
    - get
  # List of middlewares
  middleware : {}
```

Full pattern is depending of `pagesPrefix`

### `api`

Exemple of config with all options well filled :
```yaml
  # Name of router
- name: Home
  # Controller to call
  controller: App\Controller\App\Home
  patterns:
    - /index/
  # HTTP Method calling static method of controller with same name
  methods:
    - get
  # List of middlewares
  middleware : {}
```

Full pattern is depending of `apiPrefix`

### `assets`

Exemple of config with all options well filled :
```yaml
  # Name of router
- name: Home
  # Controller to call
  controller: App\Controller\App\Home
  patterns:
    - /index/
  # HTTP Method calling static method of controller with same name
  methods:
    - get
  # List of middlewares
  middleware : {}
```

Full pattern is depending of `assetsPrefix`

## Create, update and remove Router

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