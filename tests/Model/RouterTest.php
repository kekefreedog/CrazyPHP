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
use CrazyPHP\Model\Router\Create;
use CrazyPHP\Library\File\File;
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
            "config_location"   =>  "@crazyphp_root/resources/Yml",
            "trash_disable"     =>  true,
        ]);

    }

    /**
     * Test Create router simple
     * 
     * Create a simple router and check result
     * 
     * @return void
     */
    public function testCreateRouterSimple():void {



    }
    
    /**
     * Test Create simple router
     * 
     * Create a simple router and check result
     * 
     * @return void
     */
    public function testCreateRouterComplex():void {

        
    }

    /**
     * Test Create router simple
     * 
     * Create a simple router and check result
     * 
     * @depends testCreateRouterSimple
     * @return void
     */
    public function testDeleteRouterSimple():void {



    }

    /**
     * Test Create simple router
     * 
     * Create a simple router and check result
     * 
     * @depends testCreateRouterComplex
     * @return void
     */
    public function testDeleteRouterComplex():void {

        
    }

}