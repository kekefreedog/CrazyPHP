<?php declare(strict_types=1);
/**
 * Config Tests
 *
 * Verify values written into application configuration files.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */
namespace Tests\Library\File;

/** Dependencies */
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\File\Config;
use CrazyPHP\Library\Cache\Cache;
use CrazyPHP\Library\File\File;
use CrazyPHP\Library\File\Yaml;
use PHPUnit\Framework\TestCase;
use CrazyPHP\Model\Env;

/**
 * Config Test
 *
 * Exercise Config::setValue against an isolated application.
 */
class ConfigTest extends TestCase {

    /** @var string $_root Isolated application directory */
    private string $_root;

    /**
     * Set Up
     *
     * @return void
     */
    protected function setUp():void {

        # Create an application with a previous build of four files
        $this->_root = sys_get_temp_dir()."/crazy config ".bin2hex(random_bytes(6));
        mkdir($this->_root."/config", 0755, true);
        Env::set([
            "app_root"         =>  $this->_root,
            "crazyphp_root"    =>  dirname(__DIR__, 3)
        ]);
        unset($GLOBALS[Config::PREFIX]);
        Yaml::create($this->_root."/config/Front.yml", [
            "Front"    =>  [
                "bundler"      =>  "webpack",
                "lastBuild"    =>  [
                    "files"    =>  [
                        "runtime.old.js",
                        "vendors.old.js",
                        "151.old.js",
                        "index.old.js"
                    ],
                    "hash"     =>  "old",
                ]
            ]
        ]);

    }

    /**
     * Tear Down
     *
     * @return void
     */
    protected function tearDown():void {

        # Clean only this fixture and release process-local configuration
        File::removeWithin(dirname($this->_root), basename($this->_root));
        unset($GLOBALS[Config::PREFIX]);
        Env::reset();

    }

    /**
     * Test Router YAML Uses Files Cache
     *
     * Exercise fresh requests, references and equal-size edits at the same timestamp.
     *
     * @return void
     */
    public function testRouterYamlUsesFilesCache():void {

        # Use the application's custom config location for discovery
        $directory = $this->_root."/settings";

        # Create a fresh directory outside the default config path
        mkdir($directory);

        # Select the reference value for the first request
        Env::set([
            "config_location"    =>  $directory,
            "phpunit_test"       =>  false,
            "router_engine"      =>  "mezon"
        ]);

        # Keep both kinds of references in the raw source
        $yaml = "Router:\n  engine: '@router_engine'\n  vendor: '{{Router.engine}}'\n  page: alpha\n";

        # Write the original source
        $path = $directory."/Router.yml";

        # Set the initial YAML
        file_put_contents($path, $yaml);

        # Use one timestamp for both edits
        $timestamp = time() - 60;

        # Set the initial modification time
        touch($path, $timestamp);

        # Resolve chained config and environment references during the first request
        self::assertSame("mezon", Config::getValue("Router.vendor"));

        # Inspect the same Files pool used by ordinary cache clients
        $cache = new Cache("Files");

        # Locate the entry for this source
        $key = "Yaml.v1.".hash("sha256", realpath($path));

        # Verify that references are stored before request-specific resolution
        $entry = $cache->get($key);

        # Check the raw environment reference
        self::assertSame("@router_engine", $entry["document"]["Router"]["engine"]);

        # Mark the entry so a warm read can be distinguished from reparsing
        $entry["document"]["Router"]["page"] = "cached";

        # Persist the marker through the public cache API
        self::assertTrue($cache->set($key, $entry));

        # Start a new request without the in-memory document
        unset($GLOBALS[Config::PREFIX]["Router"]);

        # Supply a different environment value for the same YAML
        Env::set([
            "router_engine"    =>  "symfony"
        ]);

        # Check that cache hits resolve references using this request's environment
        self::assertSame("symfony", Config::getValue("Router.vendor"));

        # Prove the public lookup consumed the persisted document
        self::assertSame("cached", Config::getValue("Router.page"));

        # Evict the document through the framework cache
        self::assertTrue($cache->clear());

        # Confirm the pool dropped the entry
        self::assertNull($cache->get($key));

        # Start a fresh lookup after clearing the pool
        unset($GLOBALS[Config::PREFIX]["Router"]);

        # Rebuild the entry from the source
        self::assertSame("alpha", Config::getValue("Router.page"));

        # Change content without changing its byte count
        file_put_contents($path, str_replace("alpha", "bravo", $yaml));

        # Preserve the original timestamp to exercise content-based invalidation
        touch($path, $timestamp);

        # Start a new request
        unset($GLOBALS[Config::PREFIX]["Router"]);

        # Read the edited contents and publish a distinct version
        self::assertSame("bravo", Config::getValue("Router.page"));

        # The new document replaces the previous value under the same key
        self::assertSame("bravo", $cache->get($key)["document"]["Router"]["page"]);

        # Introduce a parse error after a valid cache generation exists
        file_put_contents($path, "Router: [");

        # Read the changed document in a fresh request
        unset($GLOBALS[Config::PREFIX]["Router"]);

        # Preserve malformed YAML diagnostics
        self::expectException(CrazyException::class);

        # Include the actual source path in the diagnostic
        self::expectExceptionMessage($path);

        # Reject the invalid source even though a previous generation exists
        Config::getValue("Router.page");

    }

    /**
     * Test Router YAML Without Writable Cache Storage
     *
     * @return void
     */
    public function testRouterYamlWithoutWritableCacheStorage():void {

        # Select the isolated application pool even when the suite enables its shared test pool
        Env::set(["phpunit_test" => false]);

        # Prevent creation of the nested cache directory for any executing user
        file_put_contents($this->_root."/.cache", "occupied");

        # Supply a valid source in the default directory
        file_put_contents($this->_root."/config/Router.yml", "Router: {vendor: league}");

        # Retrieve the source value even when publication is unavailable
        self::assertSame("league", Config::getValueOrDefault("Router.vendor", "mezon"));

        # Leave existing application files intact
        self::assertSame("occupied", file_get_contents($this->_root."/.cache"));

    }

    /**
     * Test Optional And Required Configuration Values
     *
     * @return void
     */
    public function testOptionalAndRequiredConfigurationValues():void {

        # Seed cached values also used by application-provided configuration
        $GLOBALS[Config::PREFIX]["App"] = [
            "App"    =>  [
                "enabled"    =>  false,
                "empty"      =>  [],
                "unset"      =>  null
            ]
        ];

        # Keep false distinct from a missing option
        self::assertFalse(Config::getValueOrDefault("App/enabled", true));

        # Preserve explicit empty arrays through the alternate key separator
        self::assertSame([], Config::getValueOrDefault("App___empty", true));

        # Null and missing keys keep their established lookup semantics
        self::assertFalse(Config::has("App.unset"));

        # Use the caller's default for a null option
        self::assertSame("fallback", Config::getValueOrDefault("App.unset", "fallback"));

        # Keep the legacy result for a missing key in an existing document
        self::assertSame([], Config::getValue("App.unknown"));

        # Required lookups continue to report missing files
        self::expectExceptionMessage('No config file found for "Absent".');

        # Request a key from an absent document
        Config::getValue("Absent.value");

    }

    /**
     * Test Set Value Replaces Shorter List
     *
     * @return void
     */
    public function testSetValueReplacesShorterList():void {

        # Record a build with fewer files than the previous one
        Config::setValue("Front.lastBuild", [
            "files"    =>  [
                "index.new.js"
            ],
            "hash"     =>  "new"
        ]);

        # Read the file written on disk
        $front = Yaml::open($this->_root."/config/Front.yml")["Front"];

        # Old trailing files must not survive the shorter list
        $this->assertSame([
            "index.new.js"
        ], $front["lastBuild"]["files"]);
        $this->assertSame("new", $front["lastBuild"]["hash"]);

        # Sibling keys outside the updated key are kept
        $this->assertSame("webpack", $front["bundler"]);

    }

    /**
     * Test Set Value Replaces Nested Keys
     *
     * @return void
     */
    public function testSetValueReplacesNestedKeys():void {

        # Set a scope without the previous hash key
        Config::setValue("Front.lastBuild", [
            "files"    =>  []
        ]);

        # Read the file written on disk
        $front = Yaml::open($this->_root."/config/Front.yml")["Front"];

        # The value set is the value stored
        $this->assertSame([
            "files"    =>  []
        ], $front["lastBuild"]);

    }

}
