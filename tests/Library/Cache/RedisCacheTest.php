<?php declare(strict_types=1);
/**
 * Redis Cache Tests
 *
 * Verify optional Redis configuration and isolated cache operations.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */
namespace Tests\Library\Cache;

/** Dependencies */
use Phpfastcache\Drivers\Redis\Config as RedisConfig;
use Phpfastcache\Config\ConfigurationOption;
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\Cache\Cache;
use CrazyPHP\Library\File\Config;
use CrazyPHP\Library\File\File;
use CrazyPHP\Library\File\Yaml;
use PHPUnit\Framework\TestCase;
use CrazyPHP\Model\Env;
use ReflectionClass;
use Redis;

/** Keep configuration and server tests separate from application cache contents. */
class RedisCacheTest extends TestCase {

    /** Temporary application root. */
    private string $_root;

    /** Configuration reader without a live connection. */
    private Cache $_reader;

    /** Previous framework environment. */
    private array $_environment;

    /** Previous in-process configuration cache. */
    private mixed $_configuration;

    /** Original Redis process environment variables. */
    private array $_redisEnvironment = [];

    /** Prepare an isolated application configuration. */
    protected function setUp():void {

        # Preserve the caller's environment
        $this->_environment = isset($GLOBALS["__CRAZY_APP"]) ? Env::getAll() : [];

        # Preserve already loaded configuration
        $this->_configuration = $GLOBALS[Config::PREFIX] ?? null;

        # Isolate Redis environment settings from the invoking runtime
        foreach(["REDIS_HOST", "REDIS_PORT"] as $name){

            # Preserve each original value including unset variables
            $this->_redisEnvironment[$name] = getenv($name);

            # Start each test without inherited Redis options
            putenv($name);

        }

        # Prepare a unique fixture root
        $this->_root = sys_get_temp_dir()."/crazy-redis-".bin2hex(random_bytes(6));

        # Create the fixture configuration directory
        mkdir($this->_root."/config", 0700, true);

        # Release configuration belonging to another application
        unset($GLOBALS[Config::PREFIX]);

        # Use the fixture without forcing the Redis tests onto Files
        Env::set(["app_root" => $this->_root, "crazyphp_root" => dirname(__DIR__, 3), "phpunit_test" => false, "cache_driver" => ""]);

        # Inspect configuration without connecting to a server
        $this->_reader = (new ReflectionClass(Cache::class))->newInstanceWithoutConstructor();

    }

    /** Remove only this fixture and restore process state. */
    protected function tearDown():void {

        # Restore the original Redis process environment
        foreach($this->_redisEnvironment as $name => $value){

            # Restore configured values or remove originally absent variables
            putenv($value === false ? $name : $name."=".$value);

        }

        # Remove fixture files and any isolated file-cache entries
        File::removeWithin(dirname($this->_root), basename($this->_root));

        # Release fixture configuration
        unset($GLOBALS[Config::PREFIX]);

        # Restore previously loaded configuration when present
        if($this->_configuration !== null){

            # Restore the caller's configuration partition
            $GLOBALS[Config::PREFIX] = $this->_configuration;

        }

        # Remove the fixture environment
        Env::reset();

        # Restore the previous framework environment
        Env::set($this->_environment);

    }

    /** Load Redis options from environment variables instead of database configuration. */
    public function testRedisEnvironmentConfiguration():void {

        # Keep conflicting legacy settings to verify they are ignored
        Yaml::create($this->_root."/config/Database.yml", ["Database" => ["collection" => ["mongodb" => false, "redis" => ["host" => "ignored.fixture", "port" => 6390]]]]);

        # Set the host through the process environment
        putenv("REDIS_HOST=redis.fixture");

        # Set the port through the process environment
        putenv("REDIS_PORT=6380");

        # Resolve the explicitly requested backend
        $configuration = $this->_reader->getConfiguration("Redis");

        # Require the selected driver
        $this->assertSame("Redis", $configuration["driver"]);

        # Require native Redis configuration
        $this->assertInstanceOf(RedisConfig::class, $configuration["options"]);

        # Verify the configured host
        $this->assertSame("redis.fixture", $configuration["options"]->getHost());

        # Verify the configured port
        $this->assertSame(6380, $configuration["options"]->getPort());

        # Preserve detailed dates used by the wrapper's freshness methods
        $this->assertTrue($configuration["options"]->isItemDetailedDate());

        # Merely configuring Redis must not change the default backend
        $this->assertSame("Files", $this->_reader->getConfiguration()["driver"]);

    }

    /** Allow explicit configuration without an application Database.yml file. */
    public function testRedisDefaultsAndExplicitConfiguration():void {

        # Read standalone Redis defaults
        $defaults = $this->_reader->getConfiguration("Redis")["options"];

        # Keep the native default host
        $this->assertSame("127.0.0.1", $defaults->getHost());

        # Keep the native default port when the environment is absent
        $this->assertSame(6379, $defaults->getPort());

        # Supply conflicting environment options before testing an explicit override
        putenv("REDIS_HOST=ignored.fixture");

        # Ensure even an invalid environment port is ignored by an explicit configuration
        putenv("REDIS_PORT=invalid");

        # Prepare explicit connection settings
        $explicit = new RedisConfig(["host" => "explicit.fixture", "port" => 6381]);

        # Preserve the caller's configuration object
        $this->assertSame($explicit, $this->_reader->getConfiguration("Redis", $explicit)["options"]);

        # Require file caching to remain the unconfigured default
        $this->assertSame("Files", $this->_reader->getConfiguration()["driver"]);

    }

    /** Keep explicit Redis configuration compatible with file-cache overrides. */
    public function testForcedFilesRemainUsable():void {

        # Force the existing offline backend
        Env::set(["cache_driver" => "Files"]);

        # Construct without requiring the Redis extension or a connection
        $cache = new Cache("Redis", new RedisConfig(["host" => "unused.fixture"]));

        # Exercise the fallback rather than checking configuration alone
        $this->assertTrue($cache->set("redis-override", ["value" => 42], 60));

        # Read the stored fixture through the unchanged cache interface
        $this->assertSame(["value" => 42], $cache->get("redis-override"));

        # Restore default driver selection and enable the existing test override
        Env::set(["cache_driver" => "", "phpunit_test" => true]);

        # Require unit-test mode to continue using Files
        $this->assertSame("Files", $this->_reader->getConfiguration("Redis")["driver"]);

    }

    /** Reject incompatible option objects before opening a connection. */
    public function testRejectsIncompatibleConfiguration():void {

        # Require a clear configuration error
        $this->expectException(CrazyException::class);

        # Identify the required option type
        $this->expectExceptionMessage("Phpfastcache\\Drivers\\Redis\\Config");

        # Reject a generic Files-style configuration object
        $this->_reader->getConfiguration("Redis", new ConfigurationOption());

    }

    /** Reject malformed environment ports before connecting. */
    public function testRejectsMalformedRedisPort():void {

        # Exercise invalid strings and both boundaries of the TCP port range
        foreach(["invalid", "0", "65536", "6379.5"] as $port){

            # Supply this invalid port through the environment
            putenv("REDIS_PORT=".$port);

            # Require configuration to reject the port
            try{

                # Resolve options without opening a connection
                $this->_reader->getConfiguration("Redis");

                # Reject accidental acceptance of the invalid port
                $this->fail("Invalid REDIS_PORT accepted: ".$port);

            }catch(CrazyException $exception){

                # Identify the variable responsible for the failure
                $this->assertStringContainsString("REDIS_PORT", $exception->getMessage());

            }

        }

    }

    /** Explain the required optional extension when it is absent. */
    public function testMissingRedisExtension():void {

        # Run this assertion only on PHP runtimes without Redis
        if(extension_loaded("redis")){

            # Record why the missing-dependency branch cannot run here
            $this->markTestSkipped("The Redis extension is installed.");

        }

        # Require the wrapper's explicit dependency error
        $this->expectExceptionMessage("requires the PHP redis extension");

        # Request the optional backend without modifying dependencies
        new Cache("Redis");

    }

    /** Connect using only the requested environment variables on an isolated server. */
    public function testRedisEnvironmentRoundTrip():void {

        # Read the explicitly selected integration server
        $host = getenv("CRAZYPHP_REDIS_TEST_HOST");

        # Limit this host-and-port check to an isolated server without authentication
        if(!$host || !extension_loaded("redis") || getenv("CRAZYPHP_REDIS_TEST_PASSWORD")){

            # Keep ordinary unit tests independent of a running Redis server
            $this->markTestSkipped("An isolated Redis server without authentication is required for the environment-only check.");

        }

        # Supply the connection host exactly as Docker environment variables do
        putenv("REDIS_HOST=".$host);

        # Supply the connection port through the environment
        putenv("REDIS_PORT=6379");

        # Connect without Database.yml settings or an explicit configuration object
        $cache = new Cache("Redis");

        # Isolate this fixture's key
        $key = "crazy-env-test-".bin2hex(random_bytes(6));

        # Clean up the key even after a failed assertion
        try{

            # Store a representative response for twenty-four hours
            $this->assertTrue($cache->set($key, ["id" => 42], 86400));

            # Read the stored response through the environment-selected connection
            $this->assertSame(["id" => 42], $cache->get($key));

        }finally{

            # Delete only this fixture's entry
            $cache->delete($key);

        }

    }

    /** Verify real serialization, expiry, refresh, authentication and deletion on an opt-in server. */
    public function testRedisRoundTrip():void {

        # Read the explicitly provided integration-test host
        $host = getenv("CRAZYPHP_REDIS_TEST_HOST");

        # Avoid connecting to application Redis instances during ordinary unit tests
        if(!$host || !extension_loaded("redis")){

            # Explain how to enable the isolated integration check
            $this->markTestSkipped("Set CRAZYPHP_REDIS_TEST_HOST and install ext-redis for the isolated integration check.");

        }

        # Read optional integration-test authentication
        $password = getenv("CRAZYPHP_REDIS_TEST_PASSWORD") ?: "";

        # Isolate fixture keys even on the explicitly selected server
        $prefix = "crazy-test-".bin2hex(random_bytes(6)).".";

        # Configure a nondefault database with fresh reads
        $configuration = new RedisConfig(["host" => $host, "password" => $password, "database" => 13, "optPrefix" => $prefix, "useStaticItemCaching" => false]);

        # Connect through the production wrapper
        $cache = new Cache("Redis", $configuration);

        # Prepare an independent observer for server-side TTL checks
        $observer = new Redis();

        # Open the explicitly selected test connection
        $observer->connect($host, 6379, 5);

        # Authenticate when the test server requires it
        if($password !== ""){

            # Use the same test-only credentials
            $observer->auth($password);

        }

        # Observe the configured cache database
        $observer->select(13);

        # Remove test keys even when an assertion fails
        try{

            # Store a representative project response for twenty-four hours
            $this->assertTrue($cache->set("project", ["data" => [["id" => 42]]], 86400));

            # Verify array serialization through a Redis read
            $this->assertSame(["data" => [["id" => 42]]], $cache->get("project"));

            # Read the actual expiry from the Redis server
            $ttl = $observer->ttl($prefix."project");

            # Require nearly twenty-four hours of remaining lifetime
            $this->assertGreaterThanOrEqual(86390, $ttl);

            # Reject a lifetime longer than requested
            $this->assertLessThanOrEqual(86400, $ttl);

            # Replace the same key as Project::get(force: true) does
            $this->assertTrue($cache->set("project", ["data" => [["id" => 43]]], 86400));

            # Require the replacement value to be visible
            $this->assertSame(["data" => [["id" => 43]]], $cache->get("project"));

            # Expire the fixture immediately without a wall-clock delay
            $observer->expire($prefix."project", 0);

            # Treat the expired server entry as a cache miss
            $this->assertNull($cache->get("project"));

            # Write another value to exercise explicit invalidation
            $this->assertTrue($cache->set("project", "temporary", 60));

            # Delete only the fixture key
            $this->assertTrue($cache->delete("project"));

            # Require the deleted entry to be absent
            $this->assertFalse($cache->has("project"));

        }finally{

            # Remove only this test's namespaced key
            $observer->del($prefix."project");

            # Close the observer connection
            $observer->close();

        }

    }

}
