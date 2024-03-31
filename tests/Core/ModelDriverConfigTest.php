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
use CrazyPHP\Library\File\Config as FileConfig;
use CrazyPHP\Library\Array\Arrays;
use CrazyPHP\Library\Cache\Cache;
use PHPUnit\Framework\TestCase;
use CrazyPHP\Core\Model;
use CrazyPHP\Model\Env;

/**
 * Model Core test
 *
 * Methods for test interactions with model into the core
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class ModelDriverConfigTest extends TestCase {
    
    /** Parameters
     ******************************************************
     */

    /**
     * Instance of the cache
     */
    public ?Cache $cache = null;

    /**
     * Model Instance
     */
    public ?Model $modelInstance = null;

    /**
     * Current Model
     */
    public ?array $currentModel = null;

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
            "phpunit_test"      =>  true,
            "crazyphp_root"     =>  getcwd(),
            "app_root"          =>  getcwd(),
            "config_location"   =>  "@crazyphp_root/resources/Yml"
        ]);

    }


    /**
     * Tear Down After Class
     * 
     * This method is called after the last test of this test class is run.
     * 
     * @return void
     */
    public static function tearDownAfterClass():void {

        Env::reset();

    }

    /** Public method | Tests
     ******************************************************
     */

    /**
     * Test Model Router Current
     * 
     * @return void
     */
    public function testModelRouterCurrent():void {

        # Load router model
        $this->modelInstance = new Model("Router");

        # Get current model config
        $this->currentModel = $this->modelInstance->getCurrent();

        # Load item in model
        $modelConfig = FileConfig::get("Model");
        $modelConfig = Arrays::filterByKey($modelConfig["Model"], "name", "Router");
        $modelConfig = array_pop($modelConfig);

        # Check configs equals
        $this->assertEquals($modelConfig, $this->currentModel);

    }

    /**
     * Prepare Model Router Create
     * 
     * @return Model
     */
    public function prepareModelRouter():Model {

        # Check model instance 
        if($this->modelInstance === null){

            # Load router model
            $result = new Model("Router");

        # If already set
        }else{

            # Load router model
            $result = $this->modelInstance;

        }

        # Return result
        return $result;

    }

    /**
     * Test Last
     * 
     * @return void
     */
    public function testLast():void {

        # Get model instance
        $modelInstance = $this->prepareModelRouter();

        # Set result
        $result = $modelInstance
            ->readWithFilters(
                [],
                "DESC",
                null,
                [
                    "limit" =>  1
                ]
            )
        ;

        # Get all routers
        $routers = FileConfig::getValue("Router.app");

        # Get last item
        $lastRouter = array_pop($routers);

        # Check name
        $this->assertEquals($lastRouter["name"], $result[0]["name"]);

    }

    /**
     * Test Fields
     * 
     * @return void
     */
    public function testFields():void {

        # Get model instance
        $modelInstance = $this->prepareModelRouter();

        # Set result
        $result = $modelInstance
            ->readAttributes([
                'summary'   =>  false
            ])
            ->readWithFilters()
        ;

        # Get all routers
        $routers = FileConfig::getValue("Model");

        # Get last item
        $lastRouter = array_pop($routers);

        # Check name
        $this->assertEquals($lastRouter["attributes"], $result);

    }

    /**
     * Test all
     * 
     * @return void
     */
    public function testAll():void {

        # Get model instance
        $modelInstance = $this->prepareModelRouter();

        # Get result
        $result = $modelInstance->readWithFilters();

        # Get all routers
        $routers = FileConfig::getValue("Router.app");

        # Check result
        if(!empty($result))

            # Iteration result
            foreach($result as $key => $item)

                # Check
                $this->assertEquals($routers[$key]["name"], $item["name"]);
        


    }

    /**
     * Test Count
     * 
     * @return void
     */
    public function testCount():void {

        # Get model instance
        $modelInstance = $this->prepareModelRouter();

        # Get result
        $result = $modelInstance->countWithFilters();

        # Get all routers
        $routers = count(FileConfig::getValue("Router.app"));

        # Check
        $this->assertEquals($routers, $result);

    }
    
    /** Constants
     ******************************************************
     */

    /* Path */
    const TEST_PATH = __DIR__."../../../.cache/cache/";

    /* String */
    const TEST_STRING = 'lorem ipsum';

    /* Array */
    const MODEL_TEST = [
        [
            "name"          =>  "Test",
        ]
    ];

    /* Template handlebars */
    const TEST_TEMPLATE = "{{id}} is corresponding to {{name}}";

}