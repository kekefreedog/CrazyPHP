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
use CrazyPHP\Library\File\Config;
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
        Env::set(["app_root" => $this->_root, "crazyphp_root" => dirname(__DIR__, 3)]);
        unset($GLOBALS[Config::PREFIX]);
        Yaml::create($this->_root."/config/Front.yml", ["Front" => ["bundler" => "webpack", "lastBuild" => [
            "files" => ["runtime.old.js", "vendors.old.js", "151.old.js", "index.old.js"],
            "hash" => "old",
        ]]]);

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
     * Test Set Value Replaces Shorter List
     *
     * @return void
     */
    public function testSetValueReplacesShorterList():void {

        # Record a build with fewer files than the previous one
        Config::setValue("Front.lastBuild", ["files" => ["index.new.js"], "hash" => "new"]);

        # Read the file written on disk
        $front = Yaml::open($this->_root."/config/Front.yml")["Front"];

        # Old trailing files must not survive the shorter list
        $this->assertSame(["index.new.js"], $front["lastBuild"]["files"]);
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
        Config::setValue("Front.lastBuild", ["files" => []]);

        # Read the file written on disk
        $front = Yaml::open($this->_root."/config/Front.yml")["Front"];

        # The value set is the value stored
        $this->assertSame(["files" => []], $front["lastBuild"]);

    }

}
