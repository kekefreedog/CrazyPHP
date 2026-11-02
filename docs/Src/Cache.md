# Cache

`CrazyPHP\Library\Cache\Cache` exposes Phpfastcache's PSR-16 operations: `get`, `set`, `has`, `delete`, and their multiple-key equivalents. Pass an integer TTL in seconds to `set`; arrays are serialized by the driver.

## Redis

Redis is opt-in and requires the PHP `redis` extension in the runtime serving the application. Select it explicitly:

```php
use CrazyPHP\Library\Cache\Cache;

# Select the Redis backend
$cache = new Cache("Redis");

# Store the response for twenty-four hours
$cache->set($hash, $result, 86400);

# Read the cached response or null on a miss
$result = $cache->get($hash);
```

Configure the connection through process environment variables (for example the existing Docker `env_file`):

```dotenv
REDIS_HOST=redis
REDIS_PORT=6379
```

The wrapper reads these values with `getenv()` and does not load `.env` files itself. Docker already exposes variables from its `env_file`; native PHP needs them exported or loaded into the process environment. `Database.collection.redis` is not read.

Use the Redis service hostname for containers and an accessible hostname or IP for native PHP. Unset or empty variables default to `127.0.0.1:6379`. The port must be an integer from 1 through 65535. The native driver's other defaults remain database `0` and a five-second connection timeout.

Pass a `Phpfastcache\Drivers\Redis\Config` object as the second constructor argument for options such as password, database, timeout or key prefix. An explicit object takes precedence over the environment. A preauthenticated native Redis client can be supplied using `RedisConfig::setRedisClient()` when custom authentication is needed.

Existing `new Cache()` behavior is unchanged: it selects MongoDB when configured, otherwise Files. Adding Redis settings alone does not switch the default. The existing `phpunit_test` and `cache_driver = Files` overrides still force file caching, including when Redis was explicitly requested. Missing extensions and connection failures are reported rather than silently switching Redis requests to another backend.

Use `delete($hash)` for targeted invalidation. The underlying Redis driver's `clear()` flushes the selected Redis database; `optPrefix` does not limit that operation.

## Verification

Run `tests/Library/Cache/RedisCacheTest.php` for configuration and compatibility checks. The live round-trip test is opt-in: set `CRAZYPHP_REDIS_TEST_HOST` to an isolated Redis server on port 6379 and optionally set `CRAZYPHP_REDIS_TEST_PASSWORD`. It uses database 13 and unique keys, verifies a 24-hour TTL, replacement, expiration and deletion, and cleans up its own key without flushing the database.
