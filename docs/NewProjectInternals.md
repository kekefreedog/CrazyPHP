# New project: how it works and how it is tested

User guide: [NewProject.md](NewProject.md). This page explains what happens inside `CrazyCommand new project` and `CrazyDocker new`, and how the end-to-end test checks every case.

`CrazyCommand new project` asks up to 8 questions, shows a summary, then runs an 11-step storyline in `src/Model/App/Create.php`. It writes the manifests, scaffold and config, installs Composer and npm dependencies and compiles the frontend once. It never starts a server or a database: that is `CrazyDocker new` followed by `docker compose up`.

## 1. Entrypoint chain

1. `php vendor/kzarshenas/crazyphp/bin/CrazyCommand new project` runs the existing PHP entrypoint.
2. `Cli\Bootstrap` handles `--docker` before Composer loads. Without it, the command and its PHP/Composer/Node subprocesses use the installed tools; missing tools produce an error.
3. With `--docker`, the bootstrap reads `config.platform.php` (minimum/default 8.3), builds `crazyphp-cli:php<version>-node22`, and runs the same entrypoint in Docker. Application and library paths are mounted at their host locations. The container marker prevents recursive dispatch.
4. `Cli\Core` asks for startup confirmation, then `actionCrazyCommandNew()` → `Cli\Form` → `Process` + `Validate` → summary confirmation → `new Create($result)` → every `getStoryline()` step, printed as 🟠 / 🟢.

Use `CrazyDocker` and `CrazyFront` directly for Docker generation and frontend workflows. Use `docker compose up|down` for the stack lifecycle. All seven entrypoints support `--docker` and `--yes` / `-y` before the `--` separator. `--yes` bypasses yes/no prompts through `Runtime::$assumeYes`, including model confirmations; it does not supply form values. The E2E step runner uses the same bypass with explicit scenario values.

## 2. Wizard questions

Questions come from `Create::REQUIRED_VALUES`, ordered by `getRequiredValues()`. A field with `when` is asked only if an earlier answer matches (`Form::isVisible()`). A field with `selectWithAnswers` builds its choices from earlier answers (`Form::getChoices()`).

| # | Field | Input | Default | Asked when |
| --- | --- | --- | --- | --- |
| 1 | `php_version` | radio | 8.5 | always (8.5, 8.4, 8.3) |
| 2 | `name` | text | folder name | always |
| 3 | `description` | text | My Crazy Web Application | always |
| 4 | `type` | radio | application | always |
| 5 | `database` | checkboxes | none | type is application, website or api |
| 6 | `authentication` | radio | none | a database is selected; `delight-im/auth` only with MariaDB or PostgreSQL |
| 7 | `router` | radio | mezon | type is application, website or api |
| 8 | `frontend` | radio | vite | type is application or website |

Author, email and homepage are still defined in `REQUIRED_VALUES` (offsets 2, 3, 5) but are no longer asked. Programmatic callers can still pass them.

The `Create` constructor normalizes the answers again, so direct callers follow the same rules:

- library and other force database, authentication and router to `none`;
- every type except application and website forces `frontend = none`;
- no database forces `authentication = none`;
- an unknown type, engine, router, bundler or PHP version, or an incompatible authentication (Delight with MongoDB only), throws a 400 `CrazyException` before any file is written.

## 3. Generation storyline

`Create::STORYLINE` is the single source of order for the CLI and `Create::run()`. A step that throws stops creation with a nonzero exit code. External tools go through `Library\Cli\Runtime`: tools from the selected local or Docker runtime, with no automatic fallback.

| # | Step | What it does | Skipped for |
| --- | --- | --- | --- |
| 1 | `runBackupComposer` | Refuses if `config/App.yml` is not empty; copies the bootstrap `composer.json` to `assets/Json/backup/composer/<uniqid>-composer.json` | — |
| 2 | `runComposer` | Rewrites `composer.json`: name `app/<slug>`, type, `App\` → `app/`, `require.php ^X.Y`, `config.platform.php X.Y.0`, authentication package, database packages and extensions | — |
| 3 | `runNpm` | Writes `package.json`: devDependencies from `package.yml` for the chosen bundler, `crazyphp`, and the build/dev/watch scripts | backend types |
| 4 | `runStructureFolder` | Copies `Structure::DEFAULT_TEMPLATE`, pruned per branch: no Front, Environment, Hbs or App controllers for backend types; only `app config tests docs` for library and other; `vite.build.mjs` instead of `webpack.config.js` for Vite | — |
| 5 | `runConfig` | `Config::setup()`, then sets `App.project.type`, `Router.vendor`, `App.authentication.library` and `Front.bundler`; empties `Router.app` and `Router.asset` for an API | — |
| 6 | `runComposerUpdate` | `composer update --no-interaction` | — |
| 7 | `runFrontDependencies` | `npm install` | backend types |
| 8 | `runDatabase` | Writes `Database.collection.<engine>`: host `localhost`, database `crazy_db`, user `crazyuser` / `crazypassword` | no database |
| 9 | `runConfirmDatabasePasswords` | Asks `[y/N]` until the user confirms they edited `config/Database.yml`, re-reading the file each time | no configured database |
| 10 | `runFrontScript` | `Front.scripts = [build, dev, watch]` | backend types |
| 11 | `runFirstCompilation` | `npm run build`, `Front::getHash()`, `Front.lastBuild.mode = production` | backend types |

## 4. Branch matrix

The project type decides which other choices exist. Everything else combines freely within that type.

| Type | Database | Authentication | Router | Frontend | Generated layout |
| --- | --- | --- | --- | --- | --- |
| Application | 0 to 3 of MongoDB, MariaDB, PostgreSQL | with a database: OAuth2 client, Delight (SQL only), none | mezon, fast-route, league, symfony | vite, webpack | full scaffold, `package.json`, TypeScript sources, first build |
| Website | same | same | same | vite, webpack | same as Application |
| REST API | same | same | same | none | no frontend; `Router.app` and `Router.asset` emptied |
| PHP Library | none | none | none | none | `app/ config/ tests/ docs/`, `Router.vendor: none` |
| Undefined | none | none | none | none | same as PHP Library |

Each case also takes PHP 8.5, 8.4 or 8.3, which gives 1,266 creation cases. The Docker choices (3 servers × HTTP, HTTPS, HTTPS behind a proxy) bring the total to 11,394.

## 5. Docker step

`CrazyDocker new` (`src/Model/Docker/Install.php`) reads what creation produced, then generates and builds a Compose stack. It does not start it.

Questions: server (nginx + php-fpm, Apache 2, FrankenPHP), security (HTTP or HTTPS), existing reverse proxy (HTTPS only), hostname (HTTPS only), HTTP port and HTTPS port (a free port is proposed; the HTTPS port only for managed TLS).

`Install::STORYLINE`:

1. `runCheck`: runs `docker info` and `docker compose version`; requires `config/App.yml`; refuses if `docker/`, a compose file or `config/Docker.yml` already exists; validates `App.public`; loads every enabled engine from `config/Database.yml` and requires a root password (except PostgreSQL), a database name and a default user.
2. `runStructureFolder`: renders the files listed in `resources/Docker/Structure.yml` for the chosen server.
3. `runDockerConfig`: builds `docker-compose.yml`. The PHP image follows the project's PHP version (`php:<v>-fpm`, `php:<v>-apache`, `dunglas/frankenphp:1-php<v>`). The application and the linked library are bind-mounted at their host paths. A Caddy service is added for managed HTTPS.
4. `runDatabaseServices`: adds one service per engine (`mongo`, `mariadb`, `mysql`, `postgresql`) with a named volume and a healthcheck. A one-shot `prepare` service runs `composer install` and `docker/bin/SetupDatabase` once the databases are healthy, and the web runtime waits for it to finish. A container-only `docker/config/Database.yml` is mounted over the application's file.
5. `runValidate`: `docker compose config --quiet`.
6. `runDockerComposeBuild`: `docker compose build`.

Ports, `CRAZY_UID` and `CRAZY_GID` are written to `.env`, keeping existing entries.

## 6. Known issues (2026-10-20)

Found during a real run on macOS + Colima, with local PHP 8.3.12 and Node 20.19.5. The default path worked: `CrazyCommand new project` with all defaults, then `CrazyDocker new` (nginx, HTTP), then `docker compose up --wait` served the home page with HTTP 200 and its hashed bundle.

| # | Issue | Type | Where |
| --- | --- | --- | --- |
| 1 | Free-port detection is wrong with `--docker`. `Runtime::isPortAvailable()` probes `127.0.0.1`, which is the CLI container's loopback when PHP runs in Docker. Port 80 was proposed as free while another container held it, so `docker compose up` failed with "port is already allocated". | Bug | `src/Library/Cli/Runtime.php` |
| 2 | A project outside the folders shared with Docker (e.g. `/private/tmp` with Colima) is mounted empty in the CLI container, and `bin/CrazyDocker` dies on `require vendor/autoload.php`. The bootstrap could detect this and print the "share this folder" hint. | UX | `src/Cli/Bootstrap.php` |
| 3 | Choosing PHP 8.5 (the default) with an older local PHP requires upgrading local PHP or explicitly using `--docker`; Docker execution exposes #1 and #2. | Note | `src/Cli/Bootstrap.php` |
| 4 | Unknown routes (`/nope`, `/api/v1/nope`) return HTTP 200 instead of 404. | To confirm | router / error page |
| 6 | **Fixed.** Library and Undefined projects crashed in `runConfig`: `Cache::getConfiguration()` read `Database.collection.mongodb`, which throws when no `Database.yml` exists. A missing database configuration now selects the `Files` cache driver. Found by the e2e matrix. | Bug | `src/Library/Cache/Cache.php` |
| 7 | Projects with a database need `ext-mongodb`, `ext-pdo_mysql` or `ext-pdo_pgsql` in the local PHP, because `runComposerUpdate` runs Composer locally. The e2e test skips such scenarios when the extensions are missing or have an incompatible version. | Requirement | `src/Model/App/Create.php` |
| 8 | MongoDB is pinned to the legacy 1.x stack: `ext-mongodb ^1.21`, `mongodb/mongodb ^1.21` and `pecl install mongodb-1.21.12` in the Docker images, because `phpfastcache/mongodb-extension` (latest 9.2.0) requires `ext-mongodb ^1.10`. Current packages (MacPorts `php85-mongodb` 2.5.5, `mongodb/mongodb` 2.x) are 2.x, so local MongoDB projects fail at `composer update`. | To decide | `src/Model/App/Create.php`, `src/Model/Docker/Install.php`, `src/Library/Cache/Cache.php` |
| 5 | Radio questions need a TTY (arrow keys), so the wizard can only be scripted through `expect`. A non-interactive `--answers` option would make CI simpler. | Idea | `src/Cli/Form.php` |

## 7. End-to-end matrix test

`tests/E2E` creates real projects, generates and builds their Docker stack, starts it and checks that it answers, for scenarios derived from the wizard definitions. It is excluded from `composer test`.

```sh
composer test            # fast unit suite (unchanged)
composer test:e2e        # e2e matrix, strategy "each" by default
composer test:all        # both, in sequence

CRAZY_E2E_STRATEGY=pairwise composer test:e2e
CRAZY_E2E_FILTER="type=api;server.type=nginx,apache" composer test:e2e
CRAZY_E2E_PHASE=created composer test:e2e      # stop after creation, no Docker
```

Requirements: local Composer and Node/npm, Docker with Compose, curl. The working directory must be shared with Docker (the default `tests/.cache/e2e` is under `$HOME`).

Scenarios with databases also need the matching PHP extensions in the local PHP, since `composer update` runs locally. When one is missing or its version does not match the constraint, the scenario is skipped with the reason, e.g. `Local PHP lacks pdo_mysql required by this scenario's databases.` or `Local PHP lacks mongodb ^1.21 (installed 2.5.5) …`. The list is read from `Create::_databaseDependencies()`, so a new engine is handled automatically. With MacPorts:

```sh
sudo port install php85-mysql php85-postgresql php85-mongodb
```

On GitHub, `.github/workflows/e2e.yml` runs the `each` strategy on PHP 8.5 when a release is published. It does not run on push; `testing.yml` keeps the fast unit suite, where the E2E suite appears as one skipped test. Logs of failing scenarios are uploaded as the `e2e-logs` artifact.

### Strategies

| Strategy | Scenarios | Covers | Duration (measured on one scenario ≈ 10 min) |
| --- | --- | --- | --- |
| `default` | 1 | all default answers | ~10 min |
| `each` | 9 | every choice at least once, every Docker stack variant | ~1.5 h |
| `pairwise` | 55 | every pair of choices (e.g. PostgreSQL with FrankenPHP, Webpack with PHP 8.3) | ~9 h |
| `full` | 11,394 | every combination | not practical |

### Why it stays valid as the framework grows

- **Scenarios come from the code.** `Tests\E2E\Scenario\Matrix` walks `Create::getRequiredValues()` and `Install::getRequiredValues()` with the same `Form::isVisible()` and `Form::getChoices()` the CLI uses. A new PHP version, router, bundler, database engine or server is tested on the next run without editing the test.
- **Coverage scales.** Pairwise selection is greedy: it keeps choosing the scenario that covers the most uncovered pairs. Docker answers are combined into one value, so all 9 stack variants are always included.
- **Checks are plug-ins.** Every class in `tests/E2E/Check/` implementing `Check` is discovered automatically. A new feature adds one file.
- **Expectations live in one place**, e.g. `HttpResponse::HOME_STATUS` per project type.

### Lifecycle of one scenario

1. **Bootstrap**: empty folder plus a `composer.json` with a path repository to this checkout, then `composer install`.
2. **Create**: the `Create` storyline runs in a subprocess (`tests/E2E/Support/step.php`); confirmations are answered Yes.
3. Phase `created`: `ProjectConfig` (manifests and recorded choices), `Frontend` (bundler files, scripts, registered build, or absence of all of it).
4. **Docker**: the `Install` storyline with two free host ports.
5. Phase `built`: `DockerFiles` (expected services, PHP image version, ports in `.env`).
6. **Up**: `docker compose up --wait`, then phase `running`: `RunningStack` (prepare job exited 0, services running) and `HttpResponse` (home page status, HTML, bundle served).
7. **Down**: always `docker compose down --volumes`. Passing folders are removed; failing ones are kept with their `e2e.log`.

### Options

| Variable | Values | Default |
| --- | --- | --- |
| `CRAZY_E2E` | `1` enables the suite | off |
| `CRAZY_E2E_STRATEGY` | `default`, `each`, `pairwise`, `full` | `each` |
| `CRAZY_E2E_FILTER` | `param=value[,value];param=value`, names from `Scenario::parameters()` (`type`, `router`, `database.mongodb`, `server.type`…) | none |
| `CRAZY_E2E_LIMIT` | maximum number of scenarios | none |
| `CRAZY_E2E_PHASE` | `created`, `built`, `running` | `running` |
| `CRAZY_E2E_DIR` | working directory shared with Docker | `tests/.cache/e2e` |
| `CRAZY_E2E_KEEP` | `1` keeps passing scenarios on disk | off |
| `CRAZY_E2E_RMI` | `1` removes each scenario's images | off |

### Adding a check

```php
namespace Tests\E2E\Check;

use Tests\E2E\Scenario\Scenario;
use Tests\E2E\Support\Workspace;
use PHPUnit\Framework\Assert;

class MyFeature implements Check {
    public function phase():string { return self::RUNNING; }
    public function supports(Scenario $scenario):bool { return $scenario->get("type") === "api"; }
    public function verify(Workspace $workspace):void {
        Assert::assertSame(200, $workspace->request("http://localhost:{$workspace->httpPort}/api/v1/ping")["status"]);
    }
}
```

`Workspace` provides `path()`, `json()`, `yaml()`, `run()` (logged command) and `request()` (curl, TLS-tolerant).

### Not covered yet

- the interactive form itself (arrow-key prompts need `expect`);
- `crazy delete project`;
- application queries against the databases beyond the `prepare` job.
