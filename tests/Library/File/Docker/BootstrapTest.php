<?php declare(strict_types=1);
/**
 * Docker Bootstrap Tests
 *
 * Verify runtime configuration isolation without contacting database servers.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */
namespace Tests\Library\File\Docker;

/**
 * Dependencies
 */
use CrazyPHP\Library\File\Docker\Bootstrap;
use CrazyPHP\Library\File\File;
use CrazyPHP\Library\File\Yaml;
use PHPUnit\Framework\TestCase;
use CrazyPHP\Model\Env;
use RuntimeException;

/**
 * Bootstrap Test
 *
 * Preserve local endpoints while deriving Compose connection settings.
 */
class BootstrapTest extends TestCase {

    /**
     * Test Managed Endpoints Only
     *
     * @return void
     */
    public function testManagedEndpointsOnly():void {

        # Include disabled and external databases alongside a generated service
        $configuration = ["Database" => ["collection" => [
            "mysql" => false,
            "postgresql" => ["host" => "external.example", "port" => 5433],
            "mongodb" => ["host" => "localhost", "port" => 27018, "docker" => ["service" => ["name" => "mongo"]], "users" => [["login" => "app", "password" => 'literal ${secret}']]],
        ]]];

        # Derive the container copy without writing or modifying the source
        $result = Bootstrap::containerConfiguration($configuration);

        # Replace only the managed host and internal port
        $this->assertSame("mongo", $result["Database"]["collection"]["mongodb"]["host"]);
        $this->assertSame(27017, $result["Database"]["collection"]["mongodb"]["port"]);

        # Preserve disabled entries and remote database settings
        $this->assertFalse($result["Database"]["collection"]["mysql"]);
        $this->assertSame($configuration["Database"]["collection"]["postgresql"], $result["Database"]["collection"]["postgresql"]);

        # Preserve literal credentials and the caller's local endpoint
        $this->assertSame('literal ${secret}', $result["Database"]["collection"]["mongodb"]["users"][0]["password"]);
        $this->assertSame("localhost", $configuration["Database"]["collection"]["mongodb"]["host"]);

    }

    /**
     * Test Unknown Managed Engine
     *
     * @return void
     */
    public function testUnknownManagedEngine():void {

        # Reject unknown mappings before contacting a database
        $this->expectException(RuntimeException::class);

        # Avoid silently assigning a MySQL port to an unsupported protocol
        Bootstrap::containerConfiguration(["Database" => ["collection" => ["unknown" => ["docker" => ["service" => ["name" => "unknown"]]]]]]);

    }

    /**
     * Test Repeated Preparation Without Databases
     *
     * @return void
     */
    public function testRepeatedPreparationWithoutDatabases():void {

        # Prepare an isolated application with no source database document
        $root = sys_get_temp_dir()."/crazy bootstrap ".bin2hex(random_bytes(5));

        # Match the private directory created by the Docker installer
        mkdir($root."/docker/config", 0700, true);

        # Resolve bootstrap paths against the fixture
        Env::set(["app_root" => $root, "crazyphp_root" => dirname(__DIR__, 4)]);

        try{

            # Execute the real preparation twice without requiring any database extension
            Bootstrap::run();
            Bootstrap::run();

            # Generate an empty runtime configuration without inventing a local source file
            $this->assertSame(["Database" => ["collection" => []]], Yaml::open($root."/docker/config/Database.yml"));
            $this->assertFileDoesNotExist($root."/config/Database.yml");

        }finally{

            # Remove only the fixture and restore framework path resolution
            File::removeAll($root);
            Env::reset();

        }

    }

}
