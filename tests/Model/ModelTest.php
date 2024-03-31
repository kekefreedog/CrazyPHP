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
 * @copyright  2022-2024 Kévin Zarshenas
 */
class ModelTest extends TestCase {

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
            # App root for composer class
            "phpunit_test"      =>  true,
            "crazyphp_root"     =>  getcwd(),
            "app_root"          =>  getcwd(),
            "config_location"   =>  "@crazyphp_root/resources/Yml",
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

        # Reset env variables
        Env::reset();

    }

    /** Public method | Tests
     ******************************************************
     */

    /**
     * Test List of All Model
     * 
     * @return void
     */
    public function testListOfAllModel():void {

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