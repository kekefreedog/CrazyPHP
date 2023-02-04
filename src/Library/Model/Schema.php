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
namespace  CrazyPHP\Library\Model;

/**
 * Dependances
 */
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\Form\Validate;
use CrazyPHP\Library\Form\Process;
use CrazyPHP\Library\Array\Arrays;
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

    /** @var array $options Options of the current schema */
    private array $options = [];

    /** @var ?array $collection Schema array */
    private ?array $collection = null;

    /** @var array $collectionWithValues */
    private array $collectionWithValues = [];

    /**
     * Constructor
     * 
     * @param array|string $source Source of the schema (array) or path to file
     * @param ?array $values Values to inject in schema
     * @param ?array $options Custom options
     * @return self
     */
    public function __construct(array|string $source, ?array $values = null, ?array $options = null){

        # Parse options
        $this->ingestOptions($options, self::DEFAULT_OPTIONS, $this->options);
        
        # Get Collection from source
        $this->getCollectionFromSource($source);

        # Validate collection
        self::validate($this->collection);

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

        # Check options
        $options = $this->ingestOptions($options, self::DEFAULT_VALUES_OPTIONS);

        # Reset collectionWithValues
        $this->collectionWithValues = [];

        # Push new values
        $this->pushValues($values, $options);

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

        # Check values
        if(empty($values))

            # Stop function
            return;

        # Check options
        $options = $this->ingestOptions($options, self::DEFAULT_VALUES_OPTIONS);

        # Check multiple
        if(!$options["multiple"] ?? false)

            # Add depth in values
            $values = [$values];

        # Iteration values
        foreach($values as $value){

            # Prepare item
            $item = $this->collection;

            # Add value
            $item["value"] = $value;

            # Push value
            $this->collectionWithValues[] = $item;

        }

    }

    /**
     * Get Schema with value
     * 
     * Return schema collection
     * 
     * @param bool $validate ValidateValues schema
     * @return array
     */
    public function getResult():array {

        # Set result
        $result = $this->collectionWithValues;

        # Vrocess values
        $result = (new Process($result))->getResult();

        # Validate values
        $result = (new Validate($result))->getResult();

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

        # Set result
        $result = $this->collectionWithValues;

        # Vrocess values
        $result = (new Process($result))->getResult();

        # Validate values
        $result = (new Validate($result))->getResult();

        # Summarize result
        $result = Validate::getResultSummary($result);

        # Return result
        return $result;

    }

    /** Public static method
     ******************************************************
     */

    /**
     * Validate
     * 
     * Validate Collection
     * 
     * @param ?array $collection Collection to check
     * @return bool
     */
    public static function validate(?array $collection = []):bool {

        # Set result
        $result = false;

        # Check collection
        if($collection !== null && !empty($collection)){

            # Set result
            $result = false;

            # Iteration of collection
            foreach($collection as $k => $v){

                # Get keys
                $keys = array_keys($v);

                # Check name && typein keys
                if(
                    !array_key_exists("name", $keys) ||
                    !array_key_exists("type", $keys)
                );

                    # Set result
                    $result = false;

            }

        }

        # Return result
        return $result;

    }

    /** Private method
     ******************************************************
     */

    /**
     * Ingest options
     * 
     * @param ?array $options
     * @return ?array
     */
    private function ingestOptions(?array $options, array $default = [], ?array &$target = null):?array {
        
        # Set result
        $result = $default;

        # Check options
        if($options !== null && !empty($options))

            # Iteration of options
            foreach($options as $k => $v)

                # Check key is in result options
                if(array_key_exists($k, $result))

                    # Set value in result
                    $result[$k] = $v;

        # Push options in class scope
        $target = $result;

        # Return target
        return $result;

    }

    /**
     * Get Collection From Source
     * 
     * @param array|string $source Source of the model schema
     * @return void
     */
    private function getCollectionFromSource(array|string $source):void {

        # Check if file
        if(is_string($source)){

            # Parse variable in source
            $path = File::path($source);

            # Get content of the file
            $content = File::open($path);

        }else

            # Set content
            $content = $source;

        # Check if root key
        if(isset($this->options["array_root"]) && $this->options["array_root"])

            # Extract key
            $content = Arrays::getKey($content, $this->options["array_root"]);

        # Set content in source
        $this->collection = $content;

    }

    /** Private constant
     ******************************************************
     */

    /** @const array DEFAULT_OPTIONS Options by default */
    private const DEFAULT_OPTIONS = [
        # Define the root of the schema in given array
        "array_root"    =>  ""
    ];

    /** @const array DEFAULT_VALUES_OPTIONS Options by default */
    private const DEFAULT_VALUES_OPTIONS = [
        # Define if multiple value
        "multiple"      =>  false
    ];


}