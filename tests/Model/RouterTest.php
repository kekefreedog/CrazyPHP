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
 * @copyright  2022-2022 Kévin Zarshenas
 */
namespace Tests\Core;

/**
 * Dependances
 */

use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\Array\Arrays;
use CrazyPHP\Library\File\Config;
use CrazyPHP\Model\Router\Create;
use CrazyPHP\Library\File\File;
use CrazyPHP\Library\File\Json;
use CrazyPHP\Library\Form\Process;
use PHPUnit\Framework\TestCase;
use CrazyPHP\Model\Env;

/**
 * Router test
 *
 * Methods for test interactions with model
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class RouterTest extends TestCase {

    /** Public attributes
     ******************************************************
     */

    /** @var Create $create */
    public Create $createSimple;
    public Create $createAdvance;

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
        
        File::removeAll("@app_root");

        File::remove("@app_root");

        Env::reset();

    }

    /** Public method | Tests
     ******************************************************
     */

    /**
     * Test Create router simple
     * 
     * Create a simple router and check result
     * 
     * @return void
     */
    public function testCreateRouterSimple():void {

        # Set input
        $input = [
            "router"    =>  [
                [
                    "name"          =>  "type",
                    "description"   =>  "Type of your crazy router",
                    "type"          =>  "VARCHAR",
                    "default"       =>  "app",
                    "select"        =>  [
                        "app"   =>  "App",
                        "api"   =>  "Api",
                        "asset" =>  "Asset",
                    ],
                    "value"         =>  "app"
                ],
                [
                    "name"          =>  "name",
                    "description"   =>  "Name of your crazy router",
                    "type"          =>  "VARCHAR",
                    "default"       =>  "router",
                    "required"      =>  1,
                    "process"       => [
                        "cleanPath",
                        "snakeToCamel",
                        "ucfirst",
                        "trim"
                    ],
                    "value"         =>  "Router"
                ],
                [
                    "name"          =>  "methods",
                    "description"   =>  "Methods allowed by your crazy router",
                    "type"          =>  "ARRAY",
                    "default"       =>  "get",
                    "required"      =>  1,
                    "multiple"      =>  1,
                    "select"        =>  [
                        "get"           =>  "Get",
                        "post"          =>  "Post",
                        "put"           =>  "Put",
                        "delete"        =>  "Delete",
                        "option"        =>  "Option",
                        "patch"         =>  "Patch"
                    ],
                    "value"         =>  [
                        "get"
                    ]
                ],
                [
                    "name"          =>  "prefix",
                    "description"   =>  "Prefix of your crazy router",
                    "type"          =>  "STRING",
                    "default"       =>  "",
                    "value"         =>  ""
                ]
            ]
        ];

        # Prepare create
        $this->createSimple = new Create($input);
        
        # Run create
        $this->createSimple->run();

        ## Asserts

        # Check router config
        $this->assertTrue($this->checkRouterConfig($input));

        # Check index ts
        $this->assertFileIsReadable(File::path("@app_root/app/Environment/Page/Router/index.ts"));

        # Check style
        $this->assertFileIsReadable(File::path("@app_root/app/Environment/Page/Router/style.scss"));
        
        # Check template
        $this->assertFileIsReadable(File::path("@app_root/app/Environment/Page/Router/template.hbs"));
        
        # Check controller
        $this->assertFileIsReadable(File::path("@app_root/app/Controller/App/Router.php"));

    }
    
    /**
     * Test Create simple router
     * 
     * Create a simple router and check result
     * 
     * @depends testCreateRouterSimple
     * @return void
     *//*
    public function testCreateRouterComplex():void {

        # Set input
        $input = [

        ];

        # Prepare create
        $this->createAdvance = new Create($input);

        return;

        # Run create
        $this->createSimple->run();

    }
    */

    /**
     * Test Create router simple
     * 
     * Create a simple router and check result
     * 
     * @depends testCreateRouterSimple
     * @return void
     *//*
    public function testDeleteRouterSimple():void {

    }*/

    /**
     * Test Create simple router
     * 
     * Create a simple router and check result
     * 
     * @depends testCreateRouterComplex
     * @return void
     *//*
    public function testDeleteRouterComplex():void {

        
    }
    */

    /** Private method
     ******************************************************
     */

    /**
     * Check router config
     * 
     * Extract config of the new router and compare it to the expected
     * 
     * @param array $input Input of the create class
     * @return bool
     */
    private function checkRouterConfig(array $input):bool {

        # Set result
        $result = false;

        ## Get name | begin

            # Search name input
            $inputFiltered = Arrays::filterByKey($input["router"], "name", "name");

            # Check filtered input
            if(empty($inputFiltered))

                # Return result
                return $result;

            # Get router name from input
            $routerName = $inputFiltered[array_key_first($inputFiltered)]["value"];

        ## Get name | end

        ## Get type | begin

            # Search name input
            $inputFiltered = Arrays::filterByKey($input["router"], "name", "type");

            # Check filtered input
            if(empty($inputFiltered))

                # Return result
                return $result;

            # Get router name from input
            $routerType = $inputFiltered[array_key_first($inputFiltered)]["value"];

        ## Get type | end

        # Open config
        $routerConfig = Config::getValue("Router.$routerType");

        # Filter Router Config
        $routerConfigFiltered = Arrays::filterByKey($routerConfig, "name", $routerName);

        # Check if empty
        if(empty($routerConfigFiltered))

            # Return result
            return $result;

        # Get routerConfigValue
        $routerConfigValue = $routerConfigFiltered[array_key_first($routerConfigFiltered)];

        # Prepare router
        $inputSummary = Arrays::changeKeyCaseRecursively(Process::getResultSummary($input["router"]));

        # Clean summary
        $inputSummaryCleaned = [
            "name"          =>  $inputSummary['name'],
            "methods"       =>  Json::decode($inputSummary['methods']),
            "patterns"      =>  ["/".strtolower($inputSummary['name'])],
            "controller"    =>  "App\Controller\\".ucfirst($routerType)."\\".$inputSummary['name']
        ];

        # Check awaited reseult and test generated is the same
        if($routerConfigValue == $inputSummaryCleaned)

            # Set result
            $result = true;

        # Return result
        return $result;

    }

}