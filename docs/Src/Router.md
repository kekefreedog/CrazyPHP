# Router

The Router maps incoming HTTP requests to PHP controller methods. Routes are declared in a YAML config file, parsed and cached by the framework, and managed via the `CrazyCommand` CLI.

## Vendor adapters

`CrazyPHP\Core\Router` delegates routing to `CrazyPHP\Interface\CrazyRouter`.
The default implementation is `CrazyPHP\Library\Router\Adapter\Mezon`; existing route definitions and the default bootstrap stay unchanged.
The core keeps collection loading, request context and middleware preparation. The available adapters are:

| Adapter in `CrazyPHP\Library\Router\Adapter` | Composer package | Matching engine |
|---|---|---|
| `Mezon` (default) | `mezon/router` | Mezon compiled route batches |
| `FastRoute` | `nikic/fast-route` | FastRoute compiled dispatcher |
| `LeagueRoute` | `league/route` | League route registration and dispatcher (built on FastRoute) |
| `Symfony` | `symfony/routing` | Symfony compiled URL matcher |

These packages are included in the framework dependencies. League Route 6 preserves compatibility with the existing PSR-7 v1 dependency.

```php
use CrazyPHP\Library\Router\Adapter\FastRoute;
use CrazyPHP\Core\Router;

$router = new Router(); // Uses Mezon by default.
$router = new Router(new FastRoute()); // Or Mezon, LeagueRoute, Symfony.
$router->pushCollection();
$router->pushMiddlewares();
$router->callRouteExtended();
```

A future vendor adapter implements `CrazyRouter` and can be passed to the constructor. The abstract `CrazyPHP\Library\Router\Adapter` class in `src/Library/Router/Adapter.php` provides shared translation, dispatch and caching for the new engines.
The contract preserves existing registration, dispatch, middleware, URL generation, parameter access and cache methods.
Adapters read the current HTTP method from the server environment and must understand the existing `[type:name]` route syntax.
Set `Router.vendor` in `config/Router.yml` to `mezon`, `fast-route`, `league` or `symfony` to select the application-wide adapter, including for the static URL helper. Omitting this setting preserves Mezon. Constructor injection overrides the configured vendor for that instance.

Custom types continue to implement `CrazyRouterType::searchRegExp()` and `parserRegExp()`.
`Router.type` retains its existing `name` and `class` entries. Types are registered through their static methods without instantiating the class, including before cached routes are loaded.
`CrazyRouterType::PARAMETER_NAME_REGEX` defines placeholder names; `Core\Router::PARAMETER_NAME_REGEX` remains available to existing generated type classes.

Compiled collection cache keys include the adapter class to prevent reuse between vendors.
Each adapter stores its compiled matching tables, callbacks, names and parameter maps through the existing `dumpOnDisk()` / `loadFromDisk()` and CrazyPHP cache flow. Loading a cache does not reparse route patterns or recompile vendor tables; runtime matcher objects are reconstructed from the stored tables.
`Router.yml`, `Api.yml` and `Middleware.yml` modification dates still determine whether a collection must be refreshed. Adding routes after loading invalidates the adapter's compiled tables. No additional application configuration or cache files are required.
Middleware stays outside the compiled cache and is registered separately by `pushMiddlewares()`, as before. Cacheable controllers must use class/method strings or static method arrays; runtime closures and object callbacks can be dispatched but cannot be written by the new adapters' disk cache. Disk cache files are trusted executable PHP, like Mezon's cache.
All adapters preserve CrazyPHP's `[type:name]` syntax, method handling (including the existing `OPTION` spelling), static-route priority, wildcard fallback, middleware argument transformations and URL substitution. League's response strategy is not used: CrazyPHP continues to invoke its own controllers.
The static URL helper contains no vendor-specific replacement logic.
Direct access to Mezon's internal route/cache arrays is no longer exposed by `Core\Router`; use the routing methods instead.

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

### URL parameter syntax

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

`CrazyPHP\Library\Router\Router` parses the YAML config into the shared format accepted by `CrazyRouter` adapters.

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

`CrazyPHP\Core\Router implements CrazyRouter` is the live dispatcher and delegates matching to its selected adapter.

Lifecycle called by `Core\Core` on every request:

```
pushCollection()       restore compiled routes, or parse Router.yml → register and cache routes
  ↓
pushMiddlewares()      register middleware callbacks per route pattern
  ↓
callRouteExtended()    match URL → get callback → set Context → dispatch
```

`callRouteExtended()` extracts the controller class name from the matched callback, stores it in `Context::setCurrentRoute()`, then lets the adapter call the controller method.

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
            └─ CrazyRouter adapter matches URL pattern (Mezon by default)
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
