# CrazyPHP performance benchmarks

Run the commands from the framework checkout after `composer install` and `npm install`.

| Command | Workload | Metrics |
| --- | --- | --- |
| `composer bench` | Four CrazyPHP router adapters on three routes, plus Handlebars compilation and rendering of 100 items | Median, minimum and maximum batch mean in µs per operation |
| `npm run bench` | Decode plain and gzipped MessagePack, then request plaintext, JSON and HTML from a temporary local CrazyPHP server | Encoded bytes, decode time in µs, HTTP p50 and p95 in ms, successful requests/s and error rate |

The PHP and Node microbenchmarks discard one warm-up batch and report eleven measured batches. Router setup includes construction and registration. First dispatch starts with a newly registered router in an already running PHP process. Warm dispatch reuses a primed router. Template compilation uses the CrazyPHP preset and an item partial. Rendering invokes the compiled callable. The Node benchmark packs its input before timing and checks the decoded result for each batch.

To compare commits, run each command several times on the same idle machine with the same PHP, Node, lockfiles and OPcache settings. Capture the same workload on both revisions and compare the medians alongside the observed variation. These commands produce JSON on stdout.

```sh
composer bench -- --json > php-bench.json
npm run --silent bench -- --json > npm-bench.json
```

## Browser API transport

The standard `npm run bench` report includes a `transport` table (and JSON field) measured with Node Fetch. It compares JSON and MessagePack, each with identity and HTTP gzip, on 1,000 records sent through the production PHP response class. For real browser timings run `npm run bench -- --browser`. Open the local URL printed after the command-line report and press Ctrl-C when finished. Both clients run the same measurement module and validate the actual content coding. The browser uses the installed build of msgpackr. The identity control supplies an identity-only request to the response class because browsers manage Accept-Encoding. The gzip case forwards the header actually sent by the client.

The report shows medians across eleven observations after warm-up, rotating the starting case. Bytes are the encoded HTTP body length, excluding headers. PHP preparation includes serialization, content coding selection and compression. Client decode time measures UTF-8 plus JSON parsing or MessagePack unpacking on already decompressed bytes. Total time includes the local fetch, automatic HTTP decompression and the first parse, and excludes verification. Each client validates every field and the actual response headers. PHP's native msgpack extension, when installed, can change the server-side comparison.

## HTTP measurements

`npm run bench` starts PHP on a free loopback port and stops it when the run finishes. By default the client sends 200 measured requests per endpoint at a concurrency of eight, after one warm-up request per client. Use `--requests` and `--concurrency` to change the workload. The npm JSON report contains decode measurements in `results` and HTTP measurements in `http`.

| GET path | Required response |
| --- | --- |
| `/ping` | HTTP 200, `text/plain`, body `ok` |
| `/users/42` | HTTP 200, `application/json`, a single numeric field `{"id":42}` from the route parameter |
| `/catalog` | HTTP 200, `text/html`, one `<ul>` with 100 `<li>Item N</li>` elements in order from 1 to 100 |

The CrazyPHP fixture uses the configured router, YAML collection loader, core middleware, route context, file cache and Handlebars. Each reference fixture uses its framework's HTTP entry point and renders one partial per list item. The client verifies every response, accepts whitespace between HTML tags and fails on mismatches. A preflight failure stops the run. Measured failures count toward the error rate and produce a nonzero exit status. Latency covers the request through body consumption. Throughput includes client-side validation.

## Compare Laravel and Symfony

The commands below create fresh, disposable projects outside the checkout and install the supplied routes and templates. Run the setup from the CrazyPHP repository root. Composer needs network access during setup.

```sh
fixture_dir=$(mktemp -d)
composer create-project --no-interaction --no-dev laravel/laravel "$fixture_dir/laravel" '^12.0'
composer create-project --no-interaction --no-dev symfony/skeleton "$fixture_dir/symfony" '7.4.*'
composer --working-dir="$fixture_dir/symfony" require --no-interaction --update-no-dev 'symfony/twig-bundle:7.4.*'

cp tests/Bench/laravel/web.php "$fixture_dir/laravel/routes/web.php"
cp tests/Bench/laravel/*.blade.php "$fixture_dir/laravel/resources/views/"
cp tests/Bench/symfony/BenchController.php "$fixture_dir/symfony/src/Controller/"
cp tests/Bench/symfony/*.twig "$fixture_dir/symfony/templates/"

(cd "$fixture_dir/laravel" && APP_ENV=production APP_DEBUG=false SESSION_DRIVER=array CACHE_STORE=array php artisan optimize)
(cd "$fixture_dir/symfony" && APP_ENV=prod APP_DEBUG=0 php bin/console cache:warmup)
echo "$fixture_dir"
```

Start each reference server in its own terminal. Set `fixture_dir` to the printed directory in those terminals.

```sh
(cd "$fixture_dir/laravel" && php -S 127.0.0.1:8081 -t public public/index.php)
```

```sh
(cd "$fixture_dir/symfony" && APP_ENV=prod APP_DEBUG=0 php -S 127.0.0.1:8082 -t public public/index.php)
```

Run the comparison from CrazyPHP. The client measures each target in sequence. Repeatable `--target` arguments also accept other loopback applications that implement the same endpoints.

```sh
npm run bench -- \
  --target Laravel=http://127.0.0.1:8081 \
  --target Symfony=http://127.0.0.1:8082 \
  --requests 500 --concurrency 8
```

Use the same PHP binary, server configuration, worker count and client concurrency for all three apps. The example uses the single-worker PHP development server. Its p95 includes time spent waiting for that worker. Keep the fixture lockfiles and record the settings alongside results when comparing revisions. Stop the Laravel and Symfony servers with Ctrl-C.
