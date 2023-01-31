<?php declare(strict_types=1);
/**
 * Model
 *
 * Classes utilities for model
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
namespace  CrazyPHP\Library\Router;

/**
 * Dependances
 */
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\Cache\Cache;
use CrazyPHP\Library\File\Config;
use CrazyPHP\Library\File\File;

/**
 * Schema
 *
 * Class for manage model schema
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class Schema {

    /** Private parameters
     ******************************************************
     */

    /**
     * Constructor
     * 
     * @param array|string $source Source of the schema (array) or path to file
     * @param ?array $values Values to inject in schema
     * @return self
     */
    public function __construct(array|string $source, ?array $values = null){
        
        # Get Collection from source
        $this->getCollectionFromSource($source);

        # Push values in collections
        $this->pushValues($values);

    }

    /** Public method
     ******************************************************
     */

    /**
     * Set Values
     * 
     * Set values in schema
     * > If empty remove all value
     * 
     * @param ?array $values Values to set in schema
     * @param ?array $option Custom options
     * @return void
     */
    public function setValues(?array $values = [], ?array $options = null):void {


    }

    /**
     * Push Values
     * 
     * Push Values in schema
     * 
     * 
     * @param ?array $values Values to push in schema
     * @param ?array $option Custom options
     * @return void
     */
    public function pushValues(?array $values = [], ?array $options = null):void {


    }

    /**
     * Get
     * 
     * Return schema collection
     * 
     * @return array
     */
    public function get():array {

        # Set result
        $result = [];

        # Return result
        return $result;

    }

    /**
     * Summary
     * 
     * Return summary of the schema [(key:value)]
     * 
     * @return array|null
     */
    public function getResultSummary():array|null {

        # Set resulut
        $result = null;

        # Return result
        return $result;

    }

    /** Private method
     ******************************************************
     */

    /**
     * Get Collection From Source
     * 
     * @param array|string $source Source of the model schema
     * @return void
     */
    private function getCollectionFromSource(array|string $source):void {



    }


}