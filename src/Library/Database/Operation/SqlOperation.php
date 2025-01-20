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
namespace  CrazyPHP\Library\Database\Operation;

/**
 * Dependances
 */

use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\Form\Operation;
use MongoDB\BSON\Regex;

/**
 * Process operations in string
 *
 * Parse operations into string
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class SqlOperation extends Operation {

    /** Private parameters
     ******************************************************
     */

    /** @var string $entity Table to add as prefix */
    private string $entity = "";

    /**
     * Constructor
     * 
     * Construct and prepare instance
     * 
     * @param string|array $Operation Exemple ["=", "[]"] or ["contains", "between"] or "@>" or "contains" or "*" (for all operations)
     * @return self
     */
    public function __construct(string|array $operations = "@all", string $entity = ""){

        # Parent
        parent::__construct($operations, [
            "prefix"    =>  $entity ? trim($entity)."." : $entity
        ]);

    }

    /** Public parameters | Operations
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

        # Set result of parent
        $parentResult = parent::parseEqual($input, $operation);

        # Get value
        $value = $parentResult["value"][1] ?? "";

        # Process options
        $this->_processOptions($value, $options);

        # Push input in operations
        $result = "= `$value`";

        # Return input
        return $result;

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

        # Get value
        $value = $parentResult["value"][1] ?? "";

        # Process options
        $this->_processOptions($value, $options);

        # Push input in operations
        $result = "<> `$value`";

        # Return input
        return $result;

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

        # Get value
        $value = $parentResult["value"][1] ?? "";

        # Process options
        $this->_processOptions($value, $options);

        # Check is numeric
        if(is_numeric($value))

            # Push input in operations
            $result = '<= '.floatval($value);

        # Error
        else

            # Error
            throw new CrazyException(
                "\"".$parentResult["value"][1]."\" value cannot be less than or equal...",
                500,
                [
                    "custom_code"   =>  "sqloperator-001"
                ]
            );

        # Return input
        return $result;

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

        # Get value
        $value = $parentResult["value"][1] ?? "";

        # Process options
        $this->_processOptions($value, $options);

        # Check is numeric
        if(is_numeric($value))

            # Push input in operations
            $result = '>= '.floatval($value);

        else

            # Error
            throw new CrazyException(
                "\"".$parentResult["value"]."\" value cannot be greater than or equal...",
                500,
                [
                    "custom_code"   =>  "sqloperator-002"
                ]
            );

        # Return input
        return $result;

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

        # Get value
        $value = $parentResult["value"][1] ?? "";

        # Process options
        $this->_processOptions($value, $options);

        # Check is numeric
        if(is_numeric($value))

            # Push input in operations
            $result = '< '.floatval($value);

        else

            # Error
            throw new CrazyException(
                "\"".$parentResult["value"]."\" value cannot be smaller than...",
                500,
                [
                    "custom_code"   =>  "sqloperator-003"
                ]
            );

        # Return input
        return $result;

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

        # Get value
        $value = $parentResult["value"][1] ?? "";

        # Process options
        $this->_processOptions($value, $options);

        # Check is numeric
        if(is_numeric($value))

            # Push input in operations
            $result = '> '.floatval($value);

        else

            # Error
            throw new CrazyException(
                "\"".$parentResult["value"]."\" value cannot be greater than...",
                500,
                [
                    "custom_code"   =>  "sqloperator-004"
                ]
            );

        # Return input
        return $result;

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

        # Set result of parent
        $parentResult = parent::parseNotBetween($input, $operation);

        # Get value A
        $valueA = $parentResult["value"][2];

        # Get value B
        $valueA = $parentResult["value"][3];

        # Process options on value A
        $this->_processOptions($valueA, $options);

        # Process options on value B
        $this->_processOptions($valueB, $options);

        # Push input in operations
        $result = is_numeric($valueA) && is_numeric($valueB)
            ? "NOT BETWEEN ".floatval($valueA)." AND ".floatval($valueB)
            : "NOT BETWEEN `$valueA` AND `$valueB`"
        ;

        # Return input
        return $result;

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

        # Set result of parent
        $parentResult = parent::parseBetween($input, $operation);

        # Push input in operations
        $result = 'BETWEEN '.$parentResult[0].' AND '.$parentResult[1];

        # Return input
        return $result;

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
            $result = 'LIKE "'.$parentResult["value"][1].'%"';

        }else
        if($parentResult["position"] == "end"){

            # Set result
            $result = 'LIKE "%'.$parentResult["value"][1].'"';

        }else
        if($parentResult["position"] == "start,end"){

            # Set result
            $result = 'LIKE "%'.$parentResult["value"][1].'%"';

        }

        # Return regex result
        return $result;

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
        $parentResult = parent::parseDefault($input);

        # Push input in operations
        $result = '= "'.$parentResult["value"].'"';

        # Return input
        return $result;

    }

    /** Private parameters
     ******************************************************
     */

    /**
     * Process Options
     * 
     * @param array &$result
     * @param array $options
     */
    private function _processOptions(&$result, $options):void {

        # Check prefix
        if($options["prefix"] ?? false)

            # Set prefix in result
            $result = $options["prefix"].$result;

        # Check suffix
        if($options["suffix"] ?? false)

            # Set prefix in result
            $result = $result.$options["suffix"];

    }

}