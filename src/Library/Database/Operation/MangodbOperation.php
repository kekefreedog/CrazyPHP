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
class MangodbOperation extends Operation {

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

        # Regex result
        /** @disregard P1009 Assume $regex = MongoDB\BSON\Regex is available */
        $regexResult = new Regex($parentResult["value"][1], 'i');

        # Return regex result
        return $regexResult;

    }

}