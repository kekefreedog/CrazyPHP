# Router

The Router maps incoming HTTP requests to PHP controller methods. Routes are declared in a YAML config file, parsed and cached by the framework, and managed via the `CrazyCommand` CLI.

---

## Config file — `config/Router.yml`

All routes live in a single file:

```yaml
Router:
  app:           # web pages (served as HTML)
    - name: Home
      controller: App\Controller\App\Home
      patterns:
        - /[a:language]
        - /[a:language]/home
      methods:
        - get
      middleware:
        - startSession
        - isLoggedIn
        - getUserInfo

  api:           # JSON endpoints
    - name: Projects
      controller: App\Controller\Api\v1\Projects
      patterns:
        - /api/v1/projects
      methods:
        - get
        - post

  asset:         # static assets
    - name: PublicAsset
      controller: App\Controller\Assets\PublicAsset
      patterns:
        - /asset/[s:file]
      methods:
        - get
```

### URL parameter syntax (Mezon Router)

| Syntax | Matches |
|---|---|
| `[s:param]` | Any string |
| `[a:param]` | Alphanumeric string |
| `[i:param]` | Integer |
| `*` | Wildcard (catch-all) |

Parameters are accessible in the controller via:

```php
$this->getParametersUrl('language');
```

---

## CLI management — `CrazyCommand`

```bash
# Create a new router (interactive)
php vendor/kzarshenas/crazyphp/bin/CrazyCommand new router

# Delete one or more routers (interactive checkbox list)
php vendor/kzarshenas/crazyphp/bin/CrazyCommand delete router
```

### `new router` — what it does

Implemented in `CrazyPHP\Model\Router\Create`. Interactive prompts ask for:

- **Type** — `app`, `api`, or `asset`
- **Name** — converted to PascalCase
- **Methods** — one or more of GET POST PUT DELETE OPTION PATCH
- **Prefix** — optional URL prefix

Then it runs in sequence:

1. **Prepare** — normalise name, check no duplicate in `Router.yml`
2. **Create `index.ts`** — page TypeScript stub (app type only)
3. **Create `style.scss`** — SCSS stub (app type only)
4. **Create `template.hbs`** — Handlebars stub (app type only)
5. **Create PHP controller** — `app/Controller/{Type}/{Name}.php`
6. **Inject into `Router.yml`** — adds the route block under the correct group

Controller paths by type:

| Type | Controller namespace |
|---|---|
| app | `App\Controller\App\{Name}` |
| api | `App\Controller\Api\v1\{Name}` |
| asset | `App\Controller\Assets\{Name}` |

### `delete router` — what it does

Implemented in `CrazyPHP\Model\Router\Delete`. The interactive list is populated by `Router::getSummary()`. Steps:

1. Removes the route block from `Router.yml`
2. Moves TypeScript, SCSS, template, and controller files to `.trash/`

---

## Library layer — `Library\Router\Router.php`

`CrazyPHP\Library\Router\Router` parses the YAML config into the format expected by the Mezon router.

Key static methods:

| Method | Purpose |
|---|---|
| `parseCollection(array $collection)` | Converts all YAML groups into flat route array |
| `parseRouter(array $router, ...)` | Parses one route block — one entry per pattern × method |
| `getSummary()` | Returns `["app.Name" => "(App) Name"]` for the CLI list |
| `getByName(string $name)` | Returns a cached route by its name |
| `reverse(string $name, array $args)` | Generates a URL from a route name + arguments |
| `dumpOnCache()` / `loadFromCache()` | Serialise/deserialise parsed routes to the cache driver |

The parsed cache is invalidated automatically when `Router.yml`, `Api.yml`, or `Middleware.yml` changes (last-modified comparison).

---

## Core layer — `Core\Router.php`

`CrazyPHP\Core\Router extends Mezon\Router\Router` is the live dispatcher.

Lifecycle called by `Core\Core` on every request:

```
pushCollection()       parse Router.yml → register routes with Mezon
  ↓
pushMiddlewares()      register middleware callbacks per route pattern
  ↓
callRouteExtended()    match URL → get callback → set Context → dispatch
```

`callRouteExtended()` extracts the controller class name from the matched callback, stores it in `Context::setCurrentRoute()`, then lets Mezon call the controller method.

---

## Middleware

Middleware is listed per route in `Router.yml` and runs before the controller. Classes are auto-discovered from three locations:

| Location | Namespace |
|---|---|
| Core framework | `CrazyPHP\Library\Router\Middleware\*` |
| App middleware | `App\Library\Middleware\*` |
| API middleware | `App\Controller\Api\Middleware\*` |

Common middleware used in this project:

| Name | Purpose |
|---|---|
| `startSession` | Initialise PHP session |
| `isLoggedIn` | Redirect to login if unauthenticated |
| `getUserInfo` | Load user profile into context |
| `createTables` | Ensure DB tables exist |
| `uuid` | Generate session UUID cookie |

---

## Controller base class

All controllers extend `CrazyPHP\Core\Controller`. Useful methods:

```php
// URL parameters declared in the route pattern
$this->getParametersUrl('language');

// Query string / POST body
$this->getHttpRequestData();

// Request headers
$this->getHeaderFromRequest('Authorization');

// State builder
$state = static::State();          // returns Library\State\Page
```

---

## Request flow

```
HTTP request
  └─ Core\Core::__construct()
       └─ runRoutersPreparation()   → Router::pushCollection()
       └─ runMiddlewaresPreparation() → Router::pushMiddlewares()
       └─ runRouterRedirection()    → Router::callRouteExtended()
            └─ Mezon matches URL pattern
            └─ Middleware executes in order
            └─ App\Controller\App\Home::get()
                 └─ State::render() → JSON or HTML response
```

---

## Reverse routing

Generate a URL from a route name anywhere in PHP:

```php
use CrazyPHP\Library\Router\Router;

$url = Router::reverse('Home', ['language' => 'en']);
// → "/en"
```
