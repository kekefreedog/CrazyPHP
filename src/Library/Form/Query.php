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
use CrazyPHP\Exception\CrazyException;

/**
 * Query
 *
 * Useful class for manipulate query from request
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class Query {

    /** Public Static Methods
     ******************************************************
     */

    /**
     * Get
     * 
     * Get query parameters
     * 
     * @return array
     */
    public static function get():array {

        # Set result
        $result = $_GET;

        # Return result
        return $result;

    }

    /**
     * Get For Filters
     * 
     * Get query parameters for controller filters
     * 
     * @return array
     */
    public static function getForFilters():array {

        # Set result
        $result = [
            0   =>  $_GET["filters"] ?? $_GET["filter"] ?? null,    # Filters
            1   =>  $_GET["sort"] ?? $_GET["sorting"] ?? "asc",     # Sort
            2   =>  $_GET["group"] ?? $_GET["grouping"] ?? null,    # Group
            3   =>  $_GET["option"] ?? $_GET["options"] ?? null,    # Option
        ];

        # Return result
        return $result;

    }

    /**
     * Get For Id
     * 
     * Get query parameters for controller id
     * 
     * @return array
     */
    public static function getForId():array {

        # Set result
        $result = [
            0   =>  $_GET["option"] ?? $_GET["options"] ?? null,    # Option
        ];

        # Return result
        return $result;

    }

    /**
     * Get For Filters From Array
     * 
     * Get query parameters for controller filters from array given
     * 
     * @return array
     */
    public static function getForFiltersFromArray(array $array = []):array {

        # Set result
        $result = [
            0   =>  $array["filters"] ?? $array["filter"] ?? null,    # Filters
            1   =>  $array["sort"] ?? $array["sorting"] ?? "asc",     # Sort
            2   =>  $array["group"] ?? $array["grouping"] ?? null,    # Group
            3   =>  $array["option"] ?? $array["options"] ?? null,    # Option
        ];

        # Return result
        return $result;

    }

    /**
     * Get Arguments
     * 
     * Get specific arguments for Router... request
     * 
     * @return array
     */
    public static function getArguments():array {

        # Set result
        $result = [];

        # Get options
        $options = $_GET["option"] ?? $_GET["options"] ?? [];

        # Get arguments
        $result = $options["argument"] ?? $options["arguments"] ?? $result;

        # Check result if array
        if(!is_array($result))

            # New error
            throw new CrazyException(
                "Given options.arguments must be an collection / array", 
                500,
                [
                    "custom_code"   =>  "query-001",
                ]
            );

        # Return result
        return $result;

    }

    /** Public Constants
     ******************************************************
     */

    /** @const array FILTERS_NAMES */
    public const FILTERS_NAMES = ["filters", "filter"];

    /** @const array SORT_NAMES */
    public const SORT_NAMES = ["sort", "sorting"];

    /** @const array GROUP_NAMES */
    public const GROUP_NAMES = ["group", "grouping"];

    /** @const array OPTIONS_NAMES */
    public const OPTIONS_NAMES = ["option", "options"];

}