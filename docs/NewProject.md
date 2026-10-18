# New CrazyPHP project

Run project creation from a new application directory. The generator follows the project type you choose and displays a summary before writing files.

## 1. Install the framework

With local PHP 8.3 or newer and Composer:

```sh
mkdir MyCrazyProject
cd MyCrazyProject
composer require kzarshenas/crazyphp
```

For a local development checkout, use a Composer path repository as described in [CrazyDevelopment.md](Misc/CrazyDevelopment.md). Keep that checkout available while creating and running the application.

If PHP is unavailable, use Docker and an existing CrazyPHP checkout to bootstrap the dependency:

```sh
# Set this to the absolute path of your CrazyPHP checkout.
CRAZY_SOURCE=/absolute/path/to/CrazyPHP

mkdir MyCrazyProject
cd MyCrazyProject

docker build --tag crazyphp-cli:php8.3-node22 "$CRAZY_SOURCE/resources/Docker/cli"
docker run --rm -it --user "$(id -u):$(id -g)" --env HOME=/tmp \
    --volume "$PWD:$PWD" --workdir "$PWD" \
    crazyphp-cli:php8.3-node22 composer require kzarshenas/crazyphp
```

The Docker image contains PHP 8.3, Composer, Node 22 and the database extensions used by the generator. The first build requires an internet connection. These shell examples run on macOS, Linux and compatible shells; Windows PowerShell users should adapt the mount paths and omit the Unix user/group arguments.

Changes in a local checkout are available only through that checkout or a Composer path repository until released. Installing the published package alone does not install unreleased changes.

## 2. Start the creation wizard

```sh
sh vendor/kzarshenas/crazyphp/bin/crazy new project
```

The launcher uses local PHP 8.3 or newer first. If a compatible PHP interpreter is unavailable, it builds the CLI image using Docker's cache and runs the same wizard inside Docker. Docker must be running for this fallback. The application directory and the resolved library checkout are mounted at their host paths, including when Composer uses a symlink. Those directories must be shared with Docker Desktop or Colima.

The existing PHP entrypoint remains available:

```sh
php vendor/kzarshenas/crazyphp/bin/CrazyCommand new project
```

The generator asks these questions in order:

1. PHP version: 8.5 (latest, proposed by default), 8.4 or 8.3.
2. Project name.
3. Description.
4. Project type: Application, Website, REST API, PHP Library or Undefined.
5. Databases, for Application, Website and REST API: any of MongoDB, MariaDB (MySQL) and PostgreSQL, or none.
6. Authentication library, when a database is selected: `league/oauth2-client`, `delight-im/auth` or None. Delight Auth is available when a SQL database is selected.
7. Router, for Application, Website and REST API: Mezon, FastRoute, LeagueRoute or Symfony.
8. Frontend, for Application and Website: Vite (default) or Webpack.
9. Summary and confirmation.

The PHP version is written to `composer.json` as `require.php` (`^8.5`) and `config.platform.php` (`8.5.0`). Composer resolves dependencies for that version even when your local PHP is older, `crazy docker new` builds its PHP image from it, and the `crazy` launcher runs in the CLI Docker image of that version when your local PHP is older than the project's.

REST API, PHP Library and Undefined projects skip frontend questions, npm installation and compilation. They do not receive `package.json`, TypeScript sources, page templates or frontend build configurations. PHP Library and Undefined also skip database, authentication and router selection.

## 3. Generated files and installation

After confirmation, the generator:

1. Backs up the bootstrap `composer.json` and preserves existing repositories, dependencies and autoload namespaces.
2. Updates `composer.json`, adding selected dependencies, and creates `package.json` when frontend tooling is selected.
3. Creates the appropriate project structure and application configuration.
4. Runs Composer once and, for frontend projects, npm installation once.
5. Writes the selected database's connection settings and default user into `config/Database.yml`.
6. Waits for confirmation that the database passwords have been updated and saved in `config/Database.yml`, when databases are configured.
7. Registers the chosen frontend scripts, compiles once and records the generated assets in `config/Front.yml`.

Composer and npm prefer locally installed tools and use the CLI Docker image when those tools are missing. A failed installation or build stops creation and returns a nonzero exit status. The generator rejects an already initialized application directory; it is a creation command, not an application upgrade command.

### Database configuration

This step writes YAML only. It does not connect to a server, start Docker services, create databases or execute user/table creation. The existing defaults include database `crazy_db` and application user `crazyuser` / `crazypassword`. Review the generated connection and user settings before provisioning your database separately.

After writing the database configuration, the wizard displays its full file path and asks you to update every configured database's user and administrator passwords. Edit and save `config/Database.yml` in your editor, then answer Yes to continue. No keeps the confirmation pending; Ctrl+C cancels the command. This also covers multiple databases configured in the YAML file. No passwords are printed, and the wizard preserves your saved edits. Projects without configured databases skip the question.

This is your confirmation that the file has been updated; it does not test passwords against a database server.

Selecting an authentication library installs its Composer package and records the choice in `App.authentication.library`. Provider credentials, authentication endpoints and authentication tables require separate application setup.

### Router configuration

The selected vendor is stored in `config/Router.yml`:

```yaml
Router:
  vendor: symfony
```

Supported values are `mezon`, `fast-route`, `league` and `symfony`. Existing apps without this setting keep Mezon. Route definitions remain unchanged; routing, reverse routing and vendor-specific caches use the configured adapter. PHP Library and Undefined projects record `vendor: none` in a minimal `Router.yml` without application routes.

### Frontend configuration

`Front.bundler` in `config/Front.yml` records `webpack`, `vite` or `none`:

```yaml
Front:
  bundler: vite
  scripts:
    - build
    - dev
    - watch
  lastBuild:
    files: null
    hash: null
    watch: false
    date: null
    mode: null
```

The generator and frontend command use this setting. Backend-only projects record `bundler: none` and `scripts: []`; they still skip frontend installation and compilation.

Both frontend choices provide:

```sh
npm run build
npm run dev
npm run watch
```

Webpack generates `webpack.config.js`; the `--mode` passed by each script selects development or production (minified) output. Vite generates `vite.build.mjs`; it produces classic scripts with the same hashed main/page filename convention used by CrazyPHP's PHP pages and lazy loader. Vite's `dev` and `watch` scripts compile assets for the PHP server; they do not launch a separate Vite HTTP server.

For dependency installation and application-managed asset registration, use the shared frontend command:

```sh
sh vendor/kzarshenas/crazyphp/bin/crazy front run build
sh vendor/kzarshenas/crazyphp/bin/crazy front run watch
# Put compiler options after the CLI separator.
sh vendor/kzarshenas/crazyphp/bin/crazy front run build -- --mode development
```

When your local PHP is older than the project's PHP version, `crazy` runs PHP in the CLI Docker image. If Node and npm are installed on your machine, `crazy front run …` still runs `npm install` and the script on the host, without the Docker VM's memory limit: Docker only checks the script and records the build in `config/Front.yml` before and after. Stopping `watch` with Ctrl+C keeps the last build the watcher published.

`run` defaults to the `build` script. The PHP entrypoint also remains available: `php vendor/kzarshenas/crazyphp/bin/CrazyFront run build`. Scripts come from the application's `package.json`; `Front.bundler` selects `webpack` or `vite`. Older frontend configurations without this field retain Webpack behavior. `none` and missing scripts are rejected before npm installation.

The runner uses local npm first and Docker as fallback, propagates failures, and records assets, hash, mode and watch state in `Front.lastBuild`. Watch state is available to request middleware while compilation runs; a failed compiler restores the previous metadata unless a watcher has already published newer assets, whose references are retained when it stops. Root bundles retain runtime/shared-chunk/entrypoint ordering, and page bundles come from the same build. Projects without page bundles are supported.

The implementation is now `CrazyPHP\Model\Front\Run`, with shared discovery in `CrazyPHP\Library\File\Front`. The old Webpack classes remain compatibility wrappers. Vite `dev`/`watch` continue to build classic scripts for PHP rather than starting a Vite development server.

## 4. Run the application

Project creation does not start a web server or database. For the existing application Docker workflow, see [Install Docker](Docker/InstallDocker.md).


## 5. Remove a project

```sh
sh vendor/kzarshenas/crazyphp/bin/crazy delete project
```

After the existing confirmation, deletion stops the managed Compose stack before removing any application files. If shutdown fails, application cleanup stops. It then restores the newest recognized Composer backup (current or legacy naming), clears the application file cache, and removes application sources, assets, the configured public directory, frontend configuration and `node_modules` for either bundler.

Everything else in the project folder is then removed, including `config/` (with `Database.yml` and custom files), local database data in `.database`, `.env`, `.gitignore`, editor settings and custom root files. Only `composer.json` (the restored bootstrap manifest), `vendor` and `.git` are kept, so `crazy new project` can run again. Named Docker volumes are not removed. Linked source/public directories are unlinked without deleting their targets. No shared database cache is flushed.

If a Composer backup is restored, the generated `composer.lock` is removed because it belongs to the replaced manifest. Without a backup, the current Composer manifest and lock remain. Back up application code and assets you want to retain before confirming deletion.
