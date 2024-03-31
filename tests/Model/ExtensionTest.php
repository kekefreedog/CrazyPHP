<?php declare(strict_types=1);
/**
 * Test Php Unit
 *
 * Test Php Unit
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace Tests\Core;

/**
 * Dependances
 */
use CrazyPHP\Library\Extension\Extension;
use CrazyPHP\Library\Form\Validate;
use CrazyPHP\Library\File\File;
use PHPUnit\Framework\TestCase;
use CrazyPHP\Model\Env;

/**
 * Extension test
 *
 * Methods for test interactions with extension model
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class ExtensionTest extends TestCase {

    /** Public method | Preparation
     ******************************************************
     */

    /**
     * Set Up Before Class
     * 
     * This method is called before the first test of this test class is run.
     * 
     * @return void
     */
    public static function setUpBeforeClass():void {

        # Setup env
        Env::set([
            "phpunit_test"              =>  true,
            "crazyphp_root"             =>  getcwd(),
            "app_root"                  =>  getcwd()."/tests/.cache/router",
            "config_location"           =>  "@crazyphp_root/resources/Yml",
            "trash_disable"             =>  true,
        ]);

        # Check app root
        if(!File::exists("@app_root")){

            # Create folder
            File::createDirectory("@app_root/config");

        }

        # Copy router config
        File::copy("@crazyphp_root/resources/Yml/Router.yml", "@app_root/config/Router.yml");

    }


    /**
     * Tear Down After Class
     * 
     * This method is called after the last test of this test class is run.
     * 
     * @return void
     */
    public static function tearDownAfterClass():void {
        
        # Remove all file in app root
        File::removeAll("@app_root");

        # Remove file of app root
        File::remove("@app_root");

        # Reset env variables
        Env::reset();

    }

    /** Public method | Tests
     ******************************************************
     */

    /**
     * Test Get Extnesions
     * 
     * Get all extensions
     * 
     * @return void
     */
    public function testGetExtensions():void {

        # Get all extensions available
        $extensions = Extension::getAllAvailable(true);

        # Check not empty
        $this->assertNotEmpty($extensions);

        # Check shotgrid in extensions
        $this->assertContains(static::DEFAULT_EXTENSION_NAME, $extensions);

    }

    /**
     * Test Get Extension By Name
     * 
     * Test Get Extension By Name
     * 
     * @return void
     */
    public function testGetExtensionByName():void {

        # Get extensions available by name
        $extension = Extension::getAvailableByName(static::DEFAULT_EXTENSION_NAME);

        # Check not empty
        $this->assertNotEmpty($extension);

    }

    /**
     * Test All Extensions Available
     * 
     * Test properties of all extensions available
     * 
     * @return void
     */
    public function testAllExtensionsAvailable():void {

        # Get all extensions available
        $extensions = Extension::getAllAvailable(true);

        # Iteration of available extensions
        foreach($extensions as $extensionName){

            # Get extension properties
            $properties = Extension::getAvailableByName($extensionName);

            # Check propoerties
            $this->assertNotEmpty($properties);

            # Check first key
            $key = array_key_first($properties);

            # Check key
            $this->assertEquals($extensionName, $key);

            # Iteration of content
            foreach($properties[$key] as $k => $v){

                # If name
                if($k == "name"){

                    # Check is string
                    $this->assertIsString($v);

                    # Check is extension name
                    $this->assertEquals($extensionName, $v);

                }else
                # If description
                if($k == "description")

                    # Check is string
                    $this->assertIsString($v);

                else
                # If version
                if($k == "description")

                    # Check is string
                    $this->assertIsString($v);

                else
                # If version
                if($k == "version")

                    # Check is string
                    $this->assertTrue(Validate::isSemanticVersioning($v));

                else
                # If version
                if($k == "scripts"){

                    # Check is array
                    $this->assertTrue(is_array($v) || is_null($v));


                }else
                # If version
                if($k == "dependencies"){

                    # Check is array
                    $this->assertTrue(is_array($v) || is_null($v));

                }

            }

        }

    }

    /** Private method
     ******************************************************
     */

    /** @var string DEFAULT_EXTENSION_NAME */
    public const DEFAULT_EXTENSION_NAME = "Shotgrid";

}