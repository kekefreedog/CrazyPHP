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

    /**
     * Constructor
     * 
     * Construct and prepare instance
     * 
     * @param string|array $Operation Exemple ["=", "[]"] or ["contains", "between"] or "@>" or "contains" or "*" (for all operations)
     * @return self
     */
    public function __construct(string|array $operations = ["*"]){

        # Parent
        parent::__construct($operations);

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
     * @return mixed
     */
    public function parseEqual(string|array $input, array $operation):mixed {

        # Set result of parent
        $parentResult = parent::parseEqual($input, $operation);

        # Push input in operations
        $result = '= "'.$parentResult["value"].'"';

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
     * @return mixed
     */
    public function parseNotEqual(string|array $input, array $operation):mixed {

        # Set result of parent
        $parentResult = parent::parseNotEqual($input, $operation);

        # Push input in operations
        $result = '<> "'.$parentResult["value"].'"';

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
     * @return mixed
     */
    public function parseLessThanOrEqual(string|array $input, array $operation):mixed {

        # Set result of parent
        $parentResult = parent::parseLessThanOrEqual($input, $operation);

        # Check is numeric
        if(is_numeric($parentResult["value"]))

            # Push input in operations
            $result = '<= '.floatval($parentResult["value"]);

        else

            # Error
            throw new CrazyException(
                "\"".$parentResult["value"]."\" value cannot be less than or equal...",
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
     * @return mixed
     */
    public function parseGreaterThanOrEqual(string|array $input, array $operation):mixed {

        # Set result of parent
        $parentResult = parent::parseGreaterThanOrEqual($input, $operation);

        # Check is numeric
        if(is_numeric($parentResult["value"]))

            # Push input in operations
            $result = '>= '.floatval($parentResult["value"]);

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
     * @return mixed
     */
    public function parseSmaller(string|array $input, array $operation):mixed {

        # Set result of parent
        $parentResult = parent::parseSmaller($input, $operation);

        # Check is numeric
        if(is_numeric($parentResult["value"]))

            # Push input in operations
            $result = '< '.floatval($parentResult["value"]);

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
     * @return mixed
     */
    public function parseGreater(string|array $input, array $operation):mixed {

        # Set result of parent
        $parentResult = parent::parseGreater($input, $operation);

        # Check is numeric
        if(is_numeric($parentResult["value"]))

            # Push input in operations
            $result = '> '.floatval($parentResult["value"]);

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
     * @return mixed
     */
    public function parseNotBetween(string|array $input, array $operation):mixed {

        # Set result of parent
        $parentResult = parent::parseNotBetween($input, $operation);

        # Push input in operations
        $result = 'NOT BETWEEN '.$parentResult[0].' AND '.$parentResult[1];

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
     * @return mixed
     */
    public function parseBetween(string|array $input, array $operation):mixed {

        # Set result of parent
        $parentResult = parent::parseBetween($input, $operation);

        # Push input in operations
        $result = 'BETWEEN '.$parentResult[0].' AND '.$parentResult[1];

        # Return input
        return $result;

    }

    /**
     * Parse Default
     * 
     * Description : No operations found
     * 
     * @param string|array $input 
     * @param array $operation
     * @return mixed
     */
    public function parseDefault(string|array $input):mixed {

        # Set result of parent
        $parentResult = parent::parseDefault($input);

        # Push input in operations
        $result = '= "'.$parentResult["value"].'"';

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
     * @return mixed
     */
    public function parseLike(string|array $input, array $operation):mixed {

        # Set result of parent
        $parentResult = parent::parseLike($input, $operation);

        # Check position
        if($operation["position"] == "start"){

            # Set result
            $result = 'LIKE "'.$parentResult["value"].'%"';

        }else
        if($operation["position"] == "end"){

            # Set result
            $result = 'LIKE "%'.$parentResult["value"].'"';

        }else
        if($operation["position"] == "start,end"){

            # Set result
            $result = 'LIKE "%'.$parentResult["value"].'%"';

        }

        # Return regex result
        return $result ;

    }

}