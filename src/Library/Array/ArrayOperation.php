<?php declare(strict_types=1);
/**
 * Array
 *
 * Classes for manipulate arrays
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace  CrazyPHP\Library\Array;

/**
 * Dependances
 */
use CrazyPHP\Library\Form\Operation;
use Illuminate\Support\Collection;

/**
 * Process operations in string
 *
 * Parse operations into string
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class ArrayOperation extends Operation {

    /** Private parameters
     ******************************************************
     */

    /** @var Collection $_filtered Table */
    private Collection $_engine;

    /** @var array $_collection Collection */
    private array $_collection = [];

    /**
     * Constructor
     * 
     * Construct and prepare instance
     * 
     * @param array $collection Array collection to filter
     * @param string|array $Operation Exemple ["=", "[]"] or ["contains", "between"] or "@>" or "contains" or "@all" (for all operations)
     * @return self
     */
    private function __construct(array $collection = [], string|array $operations = "@all", array $options = []){

        # Set collection
        $this->_collection = $collection;

        # Set engine
        $this->_engine = new Collection($this->_collection);

        # Parent
        parent::__construct($operations, $options);

    }

    /** Public static methods
     ******************************************************
     */

    /**
     * Filter array
     * 
     * Simple method to filter array
     */
    public static function filter(array $collection = [], array $filters = []):array {

        # New operation
        $operationInstance = new ArrayOperation($collection);

        # Iteration filters
        if(!empty($filters)) foreach($filters as $key => $value) $operationInstance->run($value, ["key" =>  $key]);

        # Get filtered
        $result = $operationInstance->getFiltered();

        # Set result
        return $result;

    }

    /** Public methods | Get Result
     ******************************************************
     */

    /**
     * Get Filtered
     * 
     * @return array
     */
    public function getFiltered():array {

        # Return filtered
        $result = $this->_engine->all();

        # Return result
        return $result;

    }

    /** Public methods | Operations
     ******************************************************
     */

    /**
     * Equal
     * 
     * Exemple : `=value`
     * Description : Checks if a value is equal to `value`
     * 
     * @param string|array $input 
     * @param array $operation
     * @param array $options
     * @return mixed
     */
    public function parseEqual(string|array $input, array $operation, array $options = []):null {

        # Set result of parent
        $parentResult = parent::parseEqual($input, $operation);

        # Process options
        if(($options["key"] ?? false))
        
            # Where
            $this->_engine = $this->_engine->where($options["key"], "=", $parentResult["value"][1] ?? "");

        # Return input
        return null;

    }

    /**
     * Not Equal
     * 
     * Exemple : `!=value`
     * Description : Checks if a value is not equal to `value`
     * 
     * @param string|array $input 
     * @param array $operation
     * @param array $options
     * @return mixed
     */
    public function parseNotEqual(string|array $input, array $operation, array $options = []):mixed {

        # Set result of parent
        $parentResult = parent::parseNotEqual($input, $operation);

        # Process options
        if(($options["key"] ?? false))
        
            # Where
            $this->_engine = $this->_engine->where($options["key"], "!=", $parentResult["value"][1] ?? "");

        # Return input
        return null;

    }

    /**
     * Less Than or Equal
     * 
     * Exemple : `<=10`
     * Description : Checks if a value is less than or equal to 10
     * 
     * @param string|array $input 
     * @param array $operation
     * @param array $options
     * @return mixed
     */
    public function parseLessThanOrEqual(string|array $input, array $operation, array $options = []):mixed {

        # Set result of parent
        $parentResult = parent::parseLessThanOrEqual($input, $operation);

        # Process options
        if(($options["key"] ?? false))
        
            # Where
            $this->_engine = $this->_engine->where($options["key"], "<=", $parentResult["value"][1] ?? "");

        # Return input
        return null;

    }

    /**
     * Greater Than or Equal
     * 
     * Exemple : `>=10`
     * Description : Checks if a value is greater than or equal to 10
     * 
     * @param string|array $input 
     * @param array $operation
     * @param array $options
     * @return mixed
     */
    public function parseGreaterThanOrEqual(string|array $input, array $operation, array $options = []):mixed {

        # Set result of parent
        $parentResult = parent::parseGreaterThanOrEqual($input, $operation);

        # Process options
        if(($options["key"] ?? false))
        
            # Where
            $this->_engine = $this->_engine->where($options["key"], ">=", $parentResult["value"][1] ?? "");

        # Return input
        return null;

    }

    /**
     * Smaller
     * 
     * Exemple : `<10`
     * Description : Checks if a value is smaller than 10.
     * 
     * @param string|array $input 
     * @param array $operation
     * @param array $options
     * @return mixed
     */
    public function parseSmaller(string|array $input, array $operation, array $options = []):mixed {

        # Set result of parent
        $parentResult = parent::parseSmaller($input, $operation);

        # Process options
        if(($options["key"] ?? false))
        
            # Where
            $this->_engine = $this->_engine->where($options["key"], "<", $parentResult["value"][1] ?? "");

        # Return input
        return null;

    }

    /**
     * Greater
     * 
     * Exemple : `>10`
     * Description : Checks if a value is greater than 10
     * 
     * @param string|array $input 
     * @param array $operation
     * @param array $options
     * @return mixed
     */
    public function parseGreater(string|array $input, array $operation, array $options = []):mixed {

        # Set result of parent
        $parentResult = parent::parseGreater($input, $operation);

        # Process options
        if(($options["key"] ?? false))
        
            # Where
            $this->_engine = $this->_engine->where($options["key"], ">", $parentResult["value"][1] ?? "");

        # Return input
        return null;

    }

    /**
     * Not Between
     * 
     * Exemple : `![1:10]`
     * Description : Checks if a value is not between 1 and 10
     * 
     * @param string|array $input 
     * @param array $operation
     * @param array $options
     * @return mixed
     */
    public function parseNotBetween(string|array $input, array $operation, array $options = []):mixed {

        # Set result of parent
        $parentResult = parent::parseNotBetween($input, $operation);

        # Get value A
        $valueA = $parentResult["value"][1];

        # Get value B
        $valueB = $parentResult["value"][2];

        # Process options
        if(($options["key"] ?? false))
        
            # Where
            $this->_engine = $this->_engine->whereNotBetween($options["key"], [$valueA, $valueB]);

        # Return input
        return null;

    }

    /**
     * Between
     * 
     * Exemple : `[1:10]`
     * Description : Checks if a value is between 1 and 10 (inclusive)
     * 
     * @param string|array $input 
     * @param array $operation
     * @param array $options
     * @return mixed
     */
    public function parseBetween(string|array $input, array $operation, array $options = []):mixed {

        # Set result of parent
        $parentResult = parent::parseBetween($input, $operation);

        # Get value A
        $valueA = $parentResult["value"][1];

        # Get value B
        $valueB = $parentResult["value"][2];

        # Process options
        if(($options["key"] ?? false))
        
            # Where
            $this->_engine = $this->_engine->whereBetween($options["key"], [$valueA, $valueB]);

        # Return input
        return null;

    }

    /**
     * Like
     * 
     * Exemple : `*value`
     * Description : Performs a pattern match (like SQL's LIKE)
     * 
     * @param string|array $input 
     * @param array $operation
     * @param array $options
     * @return mixed
     */
    public function parseLike(string|array $input, array $operation, array $options = []):mixed {

        # Set result of parent
        $parentResult = parent::parseLike($input, $operation);

        # Check position
        if($parentResult["position"] == "start"){

            # Set result
            $this->_engine = $this->_engine->filter(function ($item) use ($parentResult, $options) {

                # Prepare item
                $item = Arrays::flatten($item, "", "___");

                # Prepare key
                $key = str_replace(".", "___", $options["key"] ?? "");

                # Set result
                $result = false;

                # check key
                if($key && array_key_exists($key, $item))

                    # Set result
                    $result = str_ends_with($item[$key], $parentResult["value"][1]);

                # Return result
                return $result;

            });

        }else
        if($parentResult["position"] == "end"){

            # Set result
            $this->_engine = $this->_engine->filter(function ($item) use ($parentResult, $options) {

                # Prepare item
                $item = Arrays::flatten($item, "", "___");

                # Prepare key
                $key = str_replace(".", "___", $options["key"] ?? "");

                # Set result
                $result = false;

                # check key
                if($key && array_key_exists($key, $item)){

                    # Set result
                    $result = str_starts_with($item[$key], $parentResult["value"][1]);

                }

                # Return result
                return $result;
            });

        }else
        if($parentResult["position"] == "start,end"){

            # Set result
            $this->_engine = $this->_engine->filter(function ($item) use ($parentResult, $options) {

                # Prepare item
                $item = Arrays::flatten($item, "", "___");

                # Prepare key
                $key = str_replace(".", "___", $options["key"] ?? "");

                # Set result
                $result = false;

                # check key
                if($key && array_key_exists($key, $item))

                    # Set result
                    $result = str_contains($item[$key], $parentResult["value"][1]);

                # Return result
                return $result;

            });

        }

        # Return regex result
        return null;

    }

    /**
     * Parse Default
     * 
     * Description : No operations found
     * 
     * @param string|array $input 
     * @param array $operation
     * @param array $options
     * @return mixed
     */
    public function parseDefault(string|array $input, array $options = []):mixed {

        # Set result of parent
        return $this->parseEqual($input, $options);

    }

}