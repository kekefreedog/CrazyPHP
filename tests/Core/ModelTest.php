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
use CrazyPHP\Library\File\Config as FileConfig;
use CrazyPHP\Library\Array\Arrays;
use CrazyPHP\Library\Cache\Cache;
use CrazyPHP\Library\File\File;
use PHPUnit\Framework\TestCase;
use CrazyPHP\Core\Model;
use CrazyPHP\Model\Env;
use PSpell\Config;

/**
 * Model Core test
 *
 * Methods for test interactions with model into the core
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class ModelTest extends TestCase{
    
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

    /** Public method
     ******************************************************
     */

    /**
     * Prepare cache
     * 
     * @return void
     */
    public function prepareEnv(){

        # Setup env
        Env::set([
            # App root for composer class
            "phpunit_test"      =>  true,
            "crazyphp_root"     =>  getcwd(),
            "app_root"          =>  getcwd(),
            "config_location"   =>  "@crazyphp_root/resources/Yml"
        ]);

    }

    /**
     * Test Model Router Current
     * 
     * @return void
     */
    public function testModelRouterCurrent():void {

        # Prepare env
        $this->prepareEnv();

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
     * Test Model Router Create
     */
    public function testModelRouterCreate():void {

        # Create router
        /* $this->modelInstance->create(self::MODEL_TEST); */

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