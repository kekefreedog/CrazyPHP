<?php declare(strict_types=1);
/**
 * Form
 *
 * Useful class for manipulate form
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace  CrazyPHP\Library\Form;

/**
 * Dependances
 */
use CrazyPHP\Library\File\Config as FileConfig;
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\Form\Validate;
use CrazyPHP\Library\Array\Arrays;
use CrazyPHP\Library\File\File;
use CrazyPHP\Model\Config;
use CrazyPHP\Model\Env;

/**
 * Process operations in string
 *
 * Parse operations into string
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class Operation {

    /** Public constant
     ******************************************************
     */

    /** @var array $perations */
    public const LIST = [
        "=" => [ 
           "name" => "equal",
           "operation" => "=",
           "regex" => "/^=(.*)$/"
        ],
        "!=" => [ 
           "name" => "notEqual",
           "operation" => "!=",
           "regex" => "/^!=(.*)$/"
        ],
        "<=" => [ 
           "name" => "lessThanOrEqual",
           "operation" => "<=",
           "regex" => "/^<=(.*)$/"
        ],
        ">=" => [ 
           "name" => "greaterThanOrEqual",
           "operation" => ">=",
           "regex" => "/^>=(.*)$/"
        ],
        "<" => [ 
           "name" => "smaller",
           "operation" => "<",
           "regex" => "/^<(.*)$/"
        ],
        ">" => [ 
           "name" => "greater",
           "operation" => ">",
           "regex" => "/^>(.*)$/"
        ],
        # [1:10]
        "[]" => [ 
           "name" => "between",
           "operation" => "[]",
           "regex" => '/^\[\s*(\d+)\s*:\s*(\d+)\s*\]$/' # REGEX NOT WORKING 🔴
        ],
        "![]" => [ 
           "name" => "notBetween",
           "operation" => "![]",
           "regex" => "/^!\[\s*(.+?):\s*(.+?)\s*\]$/" # REGEX NOT WORKING 🔴
        ],
        "*" => [ 
           "name" => "like",
           "operation" => "*",
           "regex" => "/\*(.*?)\*/" # REGEX WORKING BUT CATCHING [10:10] or [!10:10] 🔴
        ],
    ];

    /** @param array $_options */
    public $options = [
        "prefix"    =>  "",
        "suffix"    =>  ''
    ];

    /** Private parameters
     ******************************************************
     */

    /** @param string $_currentOperations */
    private $_currentOperations = [];

    /**
     * Constructor
     * 
     * Construct and prepare instance
     * 
     * @param string|array $Operation Exemple ["=", "[]"] or ["contains", "between"] or "@>" or "contains" or "@all" (for all operations)
     * @param array $options [
     *      suffix:string
     *      prefix:string
     * ]
     * @return self
     */
    public function __construct(string|array $operations = "@all", $options = []){

        # Ingest options
        $this->_ingestOptions($options);

        # Set operations
        $this->set($operations);

    }

    /** Public parameters | Operations
     ******************************************************
     */

    /**
     * Set
     * 
     * Set Operations
     * 
     * @param string|array $operations
     * @return void
     */
    final public function set(string|array $operations = "@all"):void {

        # Reset current operations
        $this->_currentOperations = [];

        # Check if empty
        if($operations == "@all"){

            # Set operation
            $this->_currentOperations = self::LIST;

        }else
        # If string
        if(is_string($operations) && $operations){

            # Check if key set
            if(array_key_exists($operations, self::LIST))

                # Set operations
                $this->_currentOperations[$operations] = self::LIST[$operations];

            else

                # Iteration of operations
                foreach(self::LIST as $key => $operation)

                    # Check operations name
                    if(($operation["name"] ?? false) == $operations)

                        # Set current operations
                        $this->_currentOperations[$key] = $operations;

        }else
        # If array
        if(is_array($operations) && !empty($operations)){

            # Filter unique value
            $operation = array_unique($operations);

            # Iteration of operations
            foreach($operations as $operation)

                # Check if key set
                if(array_key_exists($operation, self::LIST))

                    # Set operations
                    $this->_currentOperations[$operation] = self::LIST[$operation];

                else

                    # Iteration of operations
                    foreach(self::LIST as $key => $value)

                        # Check operations name
                        if(($value["name"] ?? false) == $operation)

                            # Set current operations
                            $this->_currentOperations[$key] = $value;

        }

    }

    /**
     * Get
     * 
     * Get Operations
     * 
     * @param string|array $operations
     * @return void
     */
    final public function get():string|array {

        # Set result
        $result = $this->_currentOperations;

        # Return result
        return $result;

    }

    /**
     * Run
     * 
     * Process input value
     * 
     * @param string|array $input
     * @param array $options OVerride existing options
     */
    final public function run(string|array $input, array $options = []):mixed {

        # Get options
        $runOptions = $this->options;

        # Check options
        if(!empty($options))

            # Set options
            $this->_ingestOptions($options, $runOptions);

        # Set result
        $result = null;

        # Is string
        $isString = false;

        # Check input is string
        if(is_string($input)){

            # Convert to array
            $input = $input
                ? [$input]
                : []
            ;

            # Set is string
            $isString = true;

        }

        # Iteration of current operation
        if(!empty($input) && !empty($this->_currentOperations)){

            # Operation found
            $operationFound = false;

            # Iterations inputs
            foreach($input as $v)

                # Iteration of current operations
                foreach($this->_currentOperations as $operation){

                    # Check regex
                    if(
                        isset($operation["name"]) && 
                        $operation["name"] && 
                        isset($operation["regex"]) && 
                        Validate::isRegex($operation["regex"]) && 
                        preg_match($operation["regex"], $v, $matches)
                    ){

                        # Set method name
                        $methodName = "parse".ucfirst($operation["name"]);

                        # Process matches
                        $matches = $this->_processMatches($matches, $operation);

                        # Check if method exists
                        if(method_exists($this, $methodName))

                            # Check if isString
                            if($isString)

                                # Run method found
                                $result = $this->{$methodName}($matches, $operation, $runOptions);

                            else

                                # Run method found
                                $result[] = $this->{$methodName}($matches, $operation, $runOptions);

                        # Set operation found
                        $operationFound = true;

                        # Continue
                        break 2;

                    }

                }
            
            # Check operation found
            if(!$operationFound)

                # Set result
                $result = $this->parseDefault($v);
            
        }else
        # check is string
        if($isString){

            # Set result
            $result = $this->parseDefault($input[0] ?? "");

        }else

            # Iteration input
            foreach($input as $v)

                # Set result
                $result[] = $this->parseDefault($v);

        # Return result
        return $result;

    }

    /** Private methods
     ******************************************************
     */

    /**
     * Process Result
     * 
     * Method to filter some specific anomaly produced by regex
     * 
     * @param array $matches
     * @param array $operation
     * @return array
     */
    private function _processMatches(array $matches, array $operation):array {

        # Set result
        $result = $matches;

        # Check if like
        if(($operation["name"] ?? false) == "like" && !empty($matches ?? [])){

            # Iteration value
            foreach($result as $key => $value)

                # Check value
                if($value == '*' || $value == "")

                    # Unset value
                    unset($result[$key]);

            $result = array_values($result);

        }

        # Return result
        return $result;

    }

    /** Public parameters | Parser
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
    public function parseEqual(string|array $input, array $operation, array $options = []):mixed {

        # Push input in operations
        $operation["value"] = $input;

        # Return input
        return $operation;

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

        # Push input in operations
        $operation["value"] = $input;

        # Return input
        return $operation;

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

        # Push input in operations
        $operation["value"] = $input;

        # Return input
        return $operation;

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

        # Push input in operations
        $operation["value"] = $input;

        # Return input
        return $operation;

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

        # Push input in operations
        $operation["value"] = $input;

        # Return input
        return $operation;

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

        # Push input in operations
        $operation["value"] = $input;

        # Return input
        return $operation;

    }

    /**
     * Between
     * 
     * Exemple : `[1,10]`
     * Description : Checks if a value is between 1 and 10 (inclusive)
     * 
     * @param string|array $input 
     * @param array $operation
     * @param array $options
     * @return mixed
     */
    public function parseBetween(string|array $input, array $operation, array $options = []):mixed {

        # Push input in operations
        $operation["value"] = $input;

        # Return input
        return $operation;

    }

    /**
     * Not Between
     * 
     * Exemple : `![1,10]`
     * Description : Checks if a value is not between 1 and 10
     * 
     * @param string|array $input 
     * @param array $operation
     * @param array $options
     * @return mixed
     */
    public function parseNotBetween(string|array $input, array $operation, array $options = []):mixed {

        # Push input in operations
        $operation["value"] = $input;

        # Return input
        return $operation;

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

        # Push input in operations
        $operation = [
            "name"  =>  "default",
            "value" =>  $input
        ];

        # Return input
        return $operation;

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

        # Push input in operations
        $operation["value"] = $input;

       /*  # Check if *
        if(strpos($operation["value"][0], "*") === false)

            # Stop method
            return $this->parseDefault($input); */

        # Set start
        $start = false;

        # Set end
        $end = false;

        # Check * at the start
        if(strpos($operation["value"][0], '*') === 0)

            # Set start
            $start = true;

        if(strrpos($operation["value"][0], '*') === strlen($operation["value"][0]) - 1)
            
            $end = true;

        # Check at start
        if($start && !$end)

            # Set position
            $operation["position"] = "start";

        else
        # Check at the end
        if(!$start && $end)

            # Set position
            $operation["position"] = "end";
        
        else
        # Check in the end
        if($start && $end)
            
            # Set position
            $operation["position"] = "start,end";

        # If not found
        else

            # Set position
            $operation["position"] = null;

        # Set case sensitive
        $operation["case_sensitive"] = false;

        # Return input
        return $operation;

    }

    /** Private methods
     ******************************************************
     */

    /**
     * Ingest Options
     * 
     * @param array $options
     * @param array|null $tempOptions (set options if is array)
     * @return void
     */
    private function _ingestOptions($options = [], array|null &$tempOptions = null):void {

        # Check options
        if(!empty($options))

            # Iteration options
            foreach($this->options as $k => $v)

                # Check key exists in options
                if(array_key_exists($k, $options)){

                    # Check temp options
                    if(is_array($tempOptions))

                        # Set temp options
                        $tempOptions[$k] = $options[$k];

                    # Fill instance option
                    else

                        # Push value in instance option
                        $this->options[$k] = $options[$k];

                }else
                # Check temp options
                if(is_array($tempOptions))

                    # Fill only temp options
                    $tempOptions[$k] = $this->options[$k];

    }

}