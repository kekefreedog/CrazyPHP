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
use PHPUnit\Framework\TestCase;
use CrazyPHP\Core\Model;
use CrazyPHP\Model\Env;

/**
 * Model test
 *
 * Methods for test interactions with model
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class ModelTest extends TestCase {

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
     * Test List of All Model
     * 
     * @return void
     */
    public function testListOfAllModel():void {

        # Prepare env
        $this->prepareEnv();

        # Get list
        $result = Model::getListAllModel();

        # Get models
        $models = FileConfig::get("Model");

        # Check result
        if(!empty($result))

            # Iteration of result
            foreach($result as $key => $model)

                # Check name
                $this->assertEquals($model["name"], $models["Model"][$key]['name']);

    }

}