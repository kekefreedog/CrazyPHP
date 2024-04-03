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
        "*" => [ 
           "name" => "like",
           "operation" => "*",
           "regex" => "/^(.*)\*(.*)$/"
        ],
        "~*" => [ 
           "name" => "caseInsensitiveLike",
           "operation" => "~*",
           "regex" => "/^~\*(.*)$/"
        ],
        "[]" => [ 
           "name" => "between",
           "operation" => "[]",
           "regex" => "/^\[\s*(.+?),\s*(.+?)\s*\]$/"
        ],
        "![]" => [ 
           "name" => "notBetween",
           "operation" => "![]",
           "regex" => "/^!\[\s*(.+?),\s*(.+?)\s*\]$/"
        ],
        "@>" => [ 
           "name" => "contains",
           "operation" => "@>",
           "regex" => "/^@>(.*)$/"
        ],
        "<@" => [ 
           "name" => "containedBy",
           "operation" => "<@",
           "regex" => "/^<@(.*)$/"
        ],
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
     * @param string|array $Operation Exemple ["=", "[]"] or ["contains", "between"] or "@>" or "contains" or "*" (for all operations)
     * @return self
     */
    public function __construct(string|array $operations = "*"){

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
    final public function set(string|array $operations = "*"):void {

        # Reset current operations
        $this->_currentOperations = [];

        # Check if empty
        if($operations == "*"){

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
     */
    final public function run(string|array $input):mixed {

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

    /** Public parameters | Parser
     ******************************************************
     */

    /**
     * Equal
     * 
     * Exemple : `=value`
     * Description : Checks if a value is equal to `value`
     * 
     * @param string $input 
     * @param array $operation
     * @return mixed
     */
    public function parseEqual(string $input, array $operation):mixed {

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
     * @param string $input 
     * @param array $operation
     * @return mixed
     */
    public function parseNotEqual(string $input, array $operation):mixed {

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
     * @param string $input 
     * @param array $operation
     * @return mixed
     */
    public function parseSmaller(string $input, array $operation):mixed {

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
     * @param string $input 
     * @param array $operation
     * @return mixed
     */
    public function parseGreater(string $input, array $operation):mixed {

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
     * @param string $input 
     * @param array $operation
     * @return mixed
     */
    public function parseLessThanOrEqual(string $input, array $operation):mixed {

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
     * @param string $input 
     * @param array $operation
     * @return mixed
     */
    public function parseGreaterThanOrEqual(string $input, array $operation):mixed {

        # Push input in operations
        $operation["value"] = $input;

        # Return input
        return $operation;

    }

    /**
     * Like
     * 
     * Exemple : `*value`
     * Description : Performs a pattern match (like SQL's LIKE)
     * 
     * @param string $input 
     * @param array $operation
     * @return mixed
     */
    public function parseLike(string $input, array $operation):mixed {

        # Push input in operations
        $operation["value"] = $input;

        # Check at start
        if($operation["matches"][1] === '' && !empty($matches[2]))

            # Set position
            $operation["position"] = "start";

        else
        # Check at the end
        if(!empty($operation["matches"][1]) && $operation["matches"][2] === '')

            # Set position
            $operation["position"] = "end";
        
        else
        # Check in the end
        if(!empty($operation["matches"][1]) && !empty($operation["matches"][2]))
            
            # Set position
            $operation["position"] = "middle";

        # If not found
        else

            # Set position
            $operation["position"] = null;

        # Return input
        return $operation;

    }

    /**
     * Case-Insensitive Like
     * 
     * Exemple : `~*Value`
     * Description : Performs a case-insensitive pattern match
     * 
     * @param string $input 
     * @param array $operation
     * @return mixed
     */
    public function parseCaseInsensitiveLike(string $input, array $operation):mixed {

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
     * @param string $input 
     * @param array $operation
     * @return mixed
     */
    public function parseBetween(string $input, array $operation):mixed {

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
     * @param string $input 
     * @param array $operation
     * @return mixed
     */
    public function parseNotBetween(string $input, array $operation):mixed {

        # Push input in operations
        $operation["value"] = $input;

        # Return input
        return $operation;

    }

    /**
     * Contains
     * 
     * Exemple : `@>value`
     * Description : Checks if an array or set contains `value`
     * 
     * @param string $input 
     * @param array $operation
     * @return mixed
     */
    public function parseContains(string $input, array $operation):mixed {

        # Push input in operations
        $operation["value"] = $input;

        # Return input
        return $operation;

    }

    /**
     * Contained By
     * 
     * Exemple : `<@value`
     * Description : Checks if a value is contained by an array or set
     * 
     * @param string $input 
     * @param array $operation
     * @return mixed
     */
    public function parseContainedBy(string $input, array $operation):mixed {

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
     * @param string $input 
     * @param array $operation
     * @return mixed
     */
    public function parseDefault(string $input):mixed {

        # Push input in operations
        $operation = [
            "name"  =>  "default",
            "value" =>  $input
        ];

        # Return input
        return $operation;

    }

}