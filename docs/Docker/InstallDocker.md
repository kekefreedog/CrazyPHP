# Create a Docker environment

Run this command from an initialized application's root:

```sh
php vendor/kzarshenas/crazyphp/bin/CrazyDocker new
```

Use `--docker` to run the wizard's PHP in the CLI image instead of the installed PHP:

```sh
php vendor/kzarshenas/crazyphp/bin/CrazyDocker new --docker
```

The CLI image uses the project's PHP version (`config.platform.php` in `composer.json`, 8.3 when unset). The generated PHP, Apache and FrankenPHP images use that same version. `--docker` uses the current local Docker context's Unix socket to manage the host daemon. Remote contexts require local PHP; Windows users can run this mode inside WSL with a local Docker socket.

Every command asks for confirmation at startup. `--yes` / `-y` skips yes/no prompts for automation; creation forms still require answers. Start and stop generated services directly with `docker compose up -d` and `docker compose down`.

## Wizard storyline

1. Check Docker, the Compose plugin and the initialized project.
2. Select **Nginx**, **Apache 2** or **FrankenPHP**.
3. Select **HTTP** or **HTTPS**.
4. For HTTPS, indicate whether an existing reverse proxy already handles certificates and enter the hostname. The default comes from `App.server.name`, or `localhost`.
5. Choose the host ports. The HTTP port is always asked; the HTTPS port only when Caddy or FrankenPHP manages HTTPS. Each question proposes the port saved in `.env`, or the standard port (80, 8080 behind an existing proxy, 443). When that port is already used by a local service or a running container, the next free port from 8080 (HTTP) or 8443 (HTTPS) is proposed instead. Press Enter to accept or type another port.
6. For an existing proxy, display Caddy and Nginx configuration instructions using the chosen port. Otherwise, prepare a separate Caddy 2 service for Nginx/Apache, or use FrankenPHP's integrated Caddy.
7. Display the summary, including the ports and a warning for any port still in use, and ask for confirmation before creating files.
8. Create the Docker directories and server configuration.
9. Save the selected options in `config/Docker.yml` and generate every enabled database service from `Database.collection`.
10. Run `docker compose config --quiet`, then `docker compose build`. A failed command stops the flow with a nonzero exit status.

`new` finishes after building; it does not start services or provision a live database. Existing Docker directories or Compose configuration are preserved: move or back them up before creating a replacement. The command does not rewrite `Front.yml`, `Router.yml` or application metadata.

## Generated files

- `docker-compose.yml`: selected PHP/HTTP services, optional Caddy, database services and persistent volumes.
- `config/Docker.yml`: server type, security, existing-proxy choice, hostname, and logical runtime/edge service names. Container IDs are not cached; helpers use the stable Compose service names.
- `docker/php/`: PHP image and configuration, with the selected database extensions.
- `docker/nginx/`, `docker/apache/` or `docker/frankenphp/`: the selected server's configuration.
- `docker/caddy/`: created only for managed HTTPS with Nginx or Apache.
- `.env`: the chosen `CRAZY_HTTP_PORT`/`CRAZY_HTTPS_PORT` host ports and the host UID/GID used by preparation and HTTP workers. The port values are replaced by the wizard's answers; other existing variables are preserved.
- `docker/config/Database.yml`: generated connection settings mounted over `config/Database.yml` only inside PHP.
- `docker/README.md`: startup, TLS and existing-proxy instructions specific to the generated stack.
- `docker/bin/Prepare`: installs Composer dependencies, then invokes database setup.
- `docker/bin/SetupDatabase`: invokes `CrazyPHP\Library\File\Docker\Bootstrap`.

Nginx uses a separate PHP-FPM container. Apache and FrankenPHP serve PHP within the `webserver` service. FrankenPHP uses classic request mode; this command does not enable worker mode.

New projects default to PHP 8.5. For that version, FrankenPHP uses `dunglas/frankenphp:1-php8.5-bookworm`; projects selecting PHP 8.3 or 8.4 keep their selected version. Changing `composer.json` alone does not upgrade an existing image or container.

Both generated Caddy configurations remove the `Server` and `X-Powered-By` response headers regardless of their values. Both Caddy configurations and Nginx limit request bodies to 512 MiB, matching PHP’s generated `post_max_size=512M`. Individual uploads are limited to `500M`, leaving room for multipart form data. These template defaults apply to newly generated stacks; existing Caddyfiles must be updated separately.

After editing an existing FrankenPHP Caddyfile, validate and reload it without restarting Docker or rebuilding the image:

```sh
docker compose exec webserver frankenphp validate --config /etc/caddy/Caddyfile --adapter caddyfile
docker compose exec webserver frankenphp reload --config /etc/caddy/Caddyfile --adapter caddyfile
```

Run these commands from the target application directory. Verify its PHP runtime with `docker compose exec webserver php -v`, which selects that project’s service.

The real application path and any external framework checkout are mounted at matching container paths, preserving Composer path-repository symlinks. No machine-specific checkout path is hardcoded in the templates.

The PHP image maps `www-data` to `CRAZY_UID` and `CRAZY_GID` at build time so HTTP workers can write caches created by local commands. FrankenPHP also runs its main process as that user, with writable certificate storage. After changing either ID in `.env`, rebuild with `docker compose build` and recreate the services with `docker compose up -d`. Existing generated Dockerfiles and Compose files need the corresponding template changes before rebuilding.

## HTTPS

With no existing proxy, Caddy owns ports 80 and 443 and persists its state in `caddy_data` and `caddy_config`. Public certificates require a hostname resolving to the server and reachable challenge ports. Certificate issuance happens when the containers start, not during `new`.

For localhost and internal hostnames, Caddy uses a local certificate authority. Trust its root certificate on each client that should trust the site. The generator does not install a certificate authority or modify the hosts file. See [Caddy automatic HTTPS](https://caddyserver.com/docs/automatic-https) and [FrankenPHP Docker configuration](https://frankenphp.dev/docs/docker/).

After starting a local HTTPS stack, export its root certificate:

```sh
# Use webserver instead of caddy when FrankenPHP was selected.
docker compose cp caddy:/data/caddy/pki/authorities/local/root.crt ./caddy-root.crt
```

Import that certificate into the relevant operating system/browser trust store. Use `localhost`, or configure your hostname's DNS/hosts entry yourself.

With an existing HTTPS proxy, the generated backend defaults to `127.0.0.1:8080`. Keep certificates and HTTP-to-HTTPS redirection on that proxy. The generated README includes Caddy and Nginx examples forwarding the host and protocol. For a proxy in another container, attach it to the application's network and use `webserver:80`; its own `127.0.0.1` does not address this backend.

Mkcert and the previous Certbot branch have been removed. Existing installations of those tools and host trust stores are not modified.

## Databases

All enabled MongoDB, MariaDB, MySQL and PostgreSQL definitions produce services; missing `Database.yml` or disabled definitions produce none. Existing local hosts, ports, users and passwords remain intact. Service names are added under `Database.collection.<engine>.docker.service.name`.

Each engine gets a separate named volume and stays private to the Compose network. The generated runtime overlay uses service names and internal ports. Your original local connection settings remain unchanged.

Database credentials are copied from `Database.yml` into the generated Compose environment, with dollar signs escaped for Compose interpolation. The Compose file is written with owner-only permissions. Review and save passwords in `Database.yml` before generating Docker files; later edits require synchronizing the generated environment too.

The official MySQL image interpolates its administrator password into SQL and a client option file. The wizard rejects quotes, backslashes and line breaks in `mysql.root.password` before generating files. MySQL application accounts are created by preparation, which supports quoted passwords. See the [MySQL image entrypoint](https://github.com/docker-library/mysql/blob/master/8.4/docker-entrypoint.sh).

Compose waits for healthy databases, then runs the one-shot `prepare` service. It runs `composer install` and creates every configured database and missing user, granting access to the configured databases. Existing accounts keep their passwords and existing data is preserved. Changing YAML or image environment variables does not reset credentials in existing volumes.

`docker/config` is private to the host user; its generated YAML is readable through the runtime bind mount. The local and container database connection caches use separate keys.

## Start and stop

Review `docker/README.md`, then start the generated services:

```sh
docker compose up -d
```

Stop containers while retaining database and certificate volumes:

```sh
docker compose down
```

PHP starts only after preparation succeeds. Diagnose failures with `docker compose logs prepare`. Preparation runs in the PHP image, so startup and shutdown need Docker and Compose but no host PHP. `docker compose restart` does not rerun preparation; use `down` followed by `up` when setup must run again.

Use Compose directly for startup and shutdown; `CrazyDocker` provides the `new` and `delete` generation commands.

This lifecycle applies to newly generated stacks. Existing Compose files must be regenerated or migrated while preserving their database/certificate volumes and credentials. Keep existing volume names when migrating so the new services attach to the original data.

Frontend builds remain separate: the preparation service installs PHP dependencies and configures databases. Use the project’s frontend build command when assets change.

Ordering uses Compose’s [health and completion conditions](https://docs.docker.com/compose/how-tos/startup-order/).


## Remove the generated Docker environment

```sh
php vendor/kzarshenas/crazyphp/bin/CrazyDocker delete
```

After confirmation, the command runs `docker compose down` and stops immediately if shutdown fails. It then removes Docker service metadata from `Database.yml`, generated files listed in the Docker inventory, and `config/Docker.yml`. Local database hosts, ports and credentials remain unchanged. A partial installation without a Compose file can be cleaned without contacting Docker.

Named database/certificate volumes, host database data, `.env` and custom files inside `docker/` are retained. Directory symlinks are not followed during cleanup. A Compose file without the CrazyPHP Docker marker is rejected instead of treating an unrelated stack as generated configuration.
