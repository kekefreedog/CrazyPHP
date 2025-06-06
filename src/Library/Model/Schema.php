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
 * @copyright  2022-2024 Kévin Zarshenas
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
 * @copyright  2022-2024 Kévin Zarshenas
 */
class Schema {

    /** Private parameters
     ******************************************************
     */

    /** @var array $options Options of the current schema */
    private array $options = [];

    /** @var ?array $collection Schema array */
    private ?array $collection = null;

    /** @var array $collectionWithValues */
    private array $collectionWithValues = [];

    /** @var bool $attributesAsValues Indicate if attributes is set as values in current schema */
    private bool $attributesAsValues = false;

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
     * Set Attributes As Values
     * 
     * Switch attributes to values
     * 
     * @return self
     */
    public function setAttributesAsValues():self {

        # Enable attribute $attributesAsValues
        $this->attributesAsValues = true;

        # Return current instance
        return $this;

    }

    /**
     * Change Attributes Status With Filters
     * 
     * Change status of attributes with filters given
     * 
     * @param ?array $whiteListAttributes Attributes to enable
     * @param ?array $blackListAttributes Attributes to disable
     * @param bool $strict Be strict when attributes in filters doesn't exists
     * @return array  
     */
    public function changeAttributesStatusWithFilters(?array $whiteListAttributes = null, ?array $blackListAttributes = null, bool $strict = false):array {

        # Set result
        $result = [];

        # Check whitelist
        if(!empty($whiteListAttributes) && $whiteListAttributes !== null)

        # Iteration of 

        ## To finish

        # Return result
        return $result;

    }

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
     * Filter Values
     * 
     * Filters values from current schema
     * 
     * @param ?array $values Values to remove
     * @param ?array $options Custom options
     * @return void
     */
    public function filtersValues(?array $values = null, ?array $options = null):void {

        # Check filters
        if(!empty($values) && !empty($this->collectionWithValues))

            # Iteration of collectionValues
            foreach($this->collectionWithValues as $kcv => $collectionWithValue){

                # Keep
                $keep = true;

                # Iteration of filters
                foreach($values as $name => $value){

                    # Iteration parameters in collection with value
                    foreach($collectionWithValue as $kp => $parameter){

                        # Set attributes exists
                        $attributesExists = false;

                        # Check if values is string
                        if(is_string($name)){

                            # Check if name equals
                            if($parameter["name"] == $name){

                                # Check value
                                if($parameter["value"] != $value){

                                    # Set keep
                                    $keep = false;

                                }

                                # Set attributesExists
                                $attributesExists = true;

                            }

                        }else{

                            /* # New error
                            throw new CrazyException(
                                "\"".json_encode($value ?: [])."\" isn't supported yet",
                                501,
                                [
                                    "custom_code"   =>  "schema-002",
                                ]
                            ); */

                        }

                        # Check attributes exists
                        if(!$attributesExists && ($options["strict"] ?? true)){

                            # New error
                            /* throw new CrazyException(
                                "Attribute \"".json_encode([$name => $value])."\" doesn't exists in current schema",
                                500,
                                [
                                    "custom_code"   =>  "schema-003",
                                ]
                            ); */

                        }

                    }

                }

                # Check keep
                if(!$keep)

                    # Unset current collection value
                    unset($this->collectionWithValues[$kcv]);

            }

    }

    /**
     * Remove Values
     * 
     * Remove values from current schema
     * 
     * @param ?array $values Values to remove
     * @param ?array $options Custom options
     * @return void
     */
    public function removeValues(?array $values = null, ?array $options = null):void {

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
        if(isset($values["name"]) || !is_int(array_key_first($values))){

            # Add depth in values
            $values = [$values];

        }

        # Iteration values
        foreach($values as $value){

            # Check flatten
            if($this->options["flatten"])

                # Flat value
                $value = Arrays::flatten($value, "", ".", true);

            # Prepare item
            $items = $this->collection;

            # Iteration of item
            foreach($items as $key => &$item){

                # Set value of item
                if(!isset($value[$item["name"]])){

                    # Check skipEmptyValue
                    if($this->options["skipEmptyValue"]){

                        # Remove current item
                        unset($items[$key]);
    
                        # Skip and continue iteration
                        continue;

                    }else
                    # Check if value is requierd
                    if($item["required"] ?? false){
            
                        # New error
                        throw new CrazyException(
                            "\"".$item['name']."\" is required.",
                            500,
                            [
                                "custom_code"   =>  "schema-001",
                            ]
                        );
                        
                    }else
                    # Check default value is et                    
                    if(isset($item["default"])){

                        # Set value
                        $item["value"] = Process::setDefault($item["default"], $value, $items);

                    }else{


                        # Type varchar
                        if(strtoupper(substr(trim($item['type']), 0, 7)) == "VARCHAR")
            
                            # Set value
                            $item["value"] = "";
            
                        # Type array
                        elseif(strtoupper(substr(trim($item['type']), 0, 5)) == "ARRAY")
            
                            # Set value
                            $item["value"] = [];
                            
                        # Type Boolean
                        elseif(strtoupper(substr(trim($item['type']), 0, 4)) == "BOOL")
            
                            # Set value
                            $item["value"] = false;
                            
                        # Type Boolean
                        elseif(strtoupper(substr(trim($item['type']), 0, 4)) == "FILE")
            
                            # Set value
                            $item["value"] = null;
                            
                        # Type Json
                        elseif(strtoupper(substr(trim($item['type']), 0, 4)) == "JSON")
            
                            # Set value
                            $item["value"] = null;

                    }

                }else{

                    # Set value
                    $item["value"] = $value[$item["name"]];

                }


            }

            # Push value
            $this->collectionWithValues[] = $items;

        }

    }

    /** Public method | Get
     ******************************************************
     */

    /**
     * Get Schema with value
     * 
     * Return schema collection
     * 
     * @param array $option Option
     * @return array
     */
    public function getResult($option = []):array {

        # Set result
        $result = [];

        # Set skipAttributes
        $skipAttributes = [];

        # Check option
        if(!empty($option)){

            # Check if skipAttributes
            if(isset($option["skipAttributes"]) && is_array($option["skipAttributes"]))

                # Set skipAttributes
                $skipAttributes = $option["skipAttributes"];

        }

        # Set result
        $results = $this->collectionWithValues;

        # Check is attributes as values is not enable
        if(!$this->attributesAsValues){

            # Check result
            if(!empty($results))

                # Iteration result
                foreach($results as &$currentResult){

                    # Check skip attributes
                    foreach($skipAttributes as $name)

                        # Check name
                        if($name)

                            # Clean in current result
                            $currentResult = Arrays::removeByKey($currentResult, "name", $name);

                    # Vrocess values
                    $currentResult = (new Process($currentResult))->getResult();

                    # Validate values
                    $currentResult = (new Validate($currentResult))->getResult();

                    # Summarize result
                    $result[] = $currentResult;
            
                }

        }
        # If attributes
        else

            # Set result
            $result = $this->collection;

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
        $result = [];

        # Set skip Attributes
        $skipAttributes = [];

        # Check option
        if(!empty($option)){

            # Check if skipAttributes
            if(isset($option["skipAttributes"]) && is_array($option["skipAttributes"]))

                # Set skipAttributes
                $skipAttributes = $option["skipAttributes"];

        }

        # Check is attributes as values is not enable
        if(!$this->attributesAsValues){

            # Set result
            $results = $this->collectionWithValues;

            # Check result
            if(!empty($results))

                # Iteration result
                foreach($results as &$currentResult){

                    # Check skip attributes
                    foreach($skipAttributes as $name)

                        # Check name
                        if($name)

                            # Clean in current result
                            $currentResult = Arrays::removeByKey($currentResult, "name", $name);

                    # Vrocess values
                    $currentResult = (new Process($currentResult))->getResult();

                    # Validate values
                    $currentResult = (new Validate($currentResult))->getResult();

                    # Summarize result
                    $resultTemp = Validate::getResultSummary($currentResult, false, $this->options["flatten"] ? true : false);

                    # Check flatten
                    if($this->options["flatten"])
            
                        # Unflatten result
                        $resultTemp = Arrays::unflatten($resultTemp);

                    # Push result temp in result
                    $result[] = $resultTemp;
            
                }

        }
        # If attributes
        else{

            # Check collection
            if(!empty($this->collection))

                # Iteration collection
                foreach($this->collection as $attribute)

                    # Push value in result
                    $result[$attribute["name"]] = $attribute["type"];

        }

        # Return result
        return $result;

    }

    /**
     * Get Count
     * 
     * Get the number of item in current schema
     * 
     * @return int
     */
    public function getCount():int {

        # Check is attributes as values is not enable
        if(!$this->attributesAsValues)

            # Set result
            $result = count($this->collectionWithValues);

        # If attributes
        else

            # Set result
            $result = count($this->collection);

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
            $result = true;

            # Iteration of collection
            foreach($collection as $v){

                # Get keys
                $keys = array_keys($v);

                # Check name && typein keys
                if(
                    !in_array("name", $keys) ||
                    !in_array("type", $keys)
                ){

                    # Set result
                    $result = false;

                }

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
        "array_root"    =>  "",
        # Flatten value array received
        "flatten"       =>  false,
        # Skip empty values
        "skipEmptyValue"=>  false,
        # Isolate specific schema for test / debug
        "phpunit_test"  =>  false,
    ];

    /** @const array DEFAULT_VALUES_OPTIONS Options by default */
    private const DEFAULT_VALUES_OPTIONS = [
        # Define if multiple value
        "multiple"      =>  false
    ];


}