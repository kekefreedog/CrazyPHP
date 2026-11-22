<?php declare(strict_types=1);
/**
 * SQL Filters
 *
 * Compile model filters without interpolating caller values into SQL.
 *
 * PHP version 8.5
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2026 Kévin Zarshenas
 */
namespace CrazyPHP\Library\Database\Operation;

/**
 * Dependances
 */
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\Form\Operation;

/**
 * SQL Operation
 *
 * Return SQL fragments and their positional parameters as separate array entries.
 * Equality operators retain the existing column-comparison syntax (=column / !=column).
 * Literal equality uses an unprefixed value or a one-element filter list.
 */
class SqlOperation extends Operation {

    /** Public Methods
     ******************************************************
     */

    /**
     * Construct Operation Parser
     *
     * @param string|array $operations Enabled operations
     * @param string $entity Optional column-reference prefix
     */
    public function __construct(string|array $operations = "@all", string $entity = ""){

        # Configure column-reference prefixes
        parent::__construct($operations, [
            "prefix"    =>  $entity ? trim($entity)."." : ""
        ]);

    }

    /**
     * Parse Equal
     *
     * @param string|array $input Matched operation values
     * @param array $operation Operation definition
     * @param array $options Prefix and suffix options
     * @return array{sql:string, parameters:array} Prepared fragment
     */
    public function parseEqual(string|array $input, array $operation, array $options = []):mixed {

        # Prepare result
        $result = [];

        # Read parsed values
        $parsed = parent::parseEqual($input, $operation);

        # Validate the existing column-reference syntax
        $column = self::identifier(($options["prefix"] ?? "").$parsed["value"][1].($options["suffix"] ?? ""), true);

        # Compare identifiers without accepting SQL expressions
        $result = [
            "sql"           =>  "= ".$column,
            "parameters"    =>  []
        ];

        # Return result
        return $result;

    }

    /**
     * Parse NotEqual
     *
     * @param string|array $input Matched operation values
     * @param array $operation Operation definition
     * @param array $options Prefix and suffix options
     * @return array{sql:string, parameters:array} Prepared fragment
     */
    public function parseNotEqual(string|array $input, array $operation, array $options = []):mixed {

        # Prepare result
        $result = [];

        # Read parsed values
        $parsed = parent::parseNotEqual($input, $operation);

        # Validate the existing column-reference syntax
        $column = self::identifier(($options["prefix"] ?? "").$parsed["value"][1].($options["suffix"] ?? ""), true);

        # Compare identifiers without accepting SQL expressions
        $result = [
            "sql"           =>  "<> ".$column,
            "parameters"    =>  []
        ];

        # Return result
        return $result;

    }

    /**
     * Parse LessThanOrEqual
     *
     * @param string|array $input Matched operation values
     * @param array $operation Operation definition
     * @param array $options Prefix and suffix options
     * @return array{sql:string, parameters:array} Prepared fragment
     */
    public function parseLessThanOrEqual(string|array $input, array $operation, array $options = []):mixed {

        # Prepare result
        $result = [];

        # Read parsed values
        $parsed = parent::parseLessThanOrEqual($input, $operation);

        # Validate the numeric comparison operand
        $value = self::_number(($options["prefix"] ?? "").$parsed["value"][1].($options["suffix"] ?? ""));

        # Bind the numeric operand
        $result = [
            "sql"           =>  "<= ?",
            "parameters"    =>  [
                $value
            ]
        ];

        # Return result
        return $result;

    }

    /**
     * Parse GreaterThanOrEqual
     *
     * @param string|array $input Matched operation values
     * @param array $operation Operation definition
     * @param array $options Prefix and suffix options
     * @return array{sql:string, parameters:array} Prepared fragment
     */
    public function parseGreaterThanOrEqual(string|array $input, array $operation, array $options = []):mixed {

        # Prepare result
        $result = [];

        # Read parsed values
        $parsed = parent::parseGreaterThanOrEqual($input, $operation);

        # Validate the numeric comparison operand
        $value = self::_number(($options["prefix"] ?? "").$parsed["value"][1].($options["suffix"] ?? ""));

        # Bind the numeric operand
        $result = [
            "sql"           =>  ">= ?",
            "parameters"    =>  [
                $value
            ]
        ];

        # Return result
        return $result;

    }

    /**
     * Parse Smaller
     *
     * @param string|array $input Matched operation values
     * @param array $operation Operation definition
     * @param array $options Prefix and suffix options
     * @return array{sql:string, parameters:array} Prepared fragment
     */
    public function parseSmaller(string|array $input, array $operation, array $options = []):mixed {

        # Prepare result
        $result = [];

        # Read parsed values
        $parsed = parent::parseSmaller($input, $operation);

        # Validate the numeric comparison operand
        $value = self::_number(($options["prefix"] ?? "").$parsed["value"][1].($options["suffix"] ?? ""));

        # Bind the numeric operand
        $result = [
            "sql"           =>  "< ?",
            "parameters"    =>  [
                $value
            ]
        ];

        # Return result
        return $result;

    }

    /**
     * Parse Greater
     *
     * @param string|array $input Matched operation values
     * @param array $operation Operation definition
     * @param array $options Prefix and suffix options
     * @return array{sql:string, parameters:array} Prepared fragment
     */
    public function parseGreater(string|array $input, array $operation, array $options = []):mixed {

        # Prepare result
        $result = [];

        # Read parsed values
        $parsed = parent::parseGreater($input, $operation);

        # Validate the numeric comparison operand
        $value = self::_number(($options["prefix"] ?? "").$parsed["value"][1].($options["suffix"] ?? ""));

        # Bind the numeric operand
        $result = [
            "sql"           =>  "> ?",
            "parameters"    =>  [
                $value
            ]
        ];

        # Return result
        return $result;

    }

    /**
     * Parse NotBetween
     *
     * @param string|array $input Matched operation values
     * @param array $operation Operation definition
     * @param array $options Prefix and suffix options
     * @return array{sql:string, parameters:array} Prepared fragment
     */
    public function parseNotBetween(string|array $input, array $operation, array $options = []):mixed {

        # Prepare result
        $result = [];

        # Read parsed values
        $parsed = parent::parseNotBetween($input, $operation);

        # Preserve numeric ranges and bind text ranges literally
        $values = [
            $parsed["value"][1],
            $parsed["value"][2]
        ];

        # Normalize numeric endpoints together
        if(is_numeric($values[0]) && is_numeric($values[1])){

            # Convert numeric range endpoints
            $values = [
                self::_number($values[0]),
                self::_number($values[1])
            ];

        }

        # Bind both range endpoints
        $result = [
            "sql"           =>  "NOT BETWEEN ? AND ?",
            "parameters"    =>  $values
        ];

        # Return result
        return $result;

    }

    /**
     * Parse Between
     *
     * @param string|array $input Matched operation values
     * @param array $operation Operation definition
     * @param array $options Prefix and suffix options
     * @return array{sql:string, parameters:array} Prepared fragment
     */
    public function parseBetween(string|array $input, array $operation, array $options = []):mixed {

        # Prepare result
        $result = [];

        # Read parsed values
        $parsed = parent::parseBetween($input, $operation);

        # Preserve numeric ranges and bind text ranges literally
        $values = [
            $parsed["value"][1],
            $parsed["value"][2]
        ];

        # Normalize numeric endpoints together
        if(is_numeric($values[0]) && is_numeric($values[1])){

            # Convert numeric range endpoints
            $values = [
                self::_number($values[0]),
                self::_number($values[1])
            ];

        }

        # Bind both range endpoints
        $result = [
            "sql"           =>  "BETWEEN ? AND ?",
            "parameters"    =>  $values
        ];

        # Return result
        return $result;

    }

    /**
     * Parse Pattern Match
     *
     * @param string|array $input Matched pattern
     * @param array $operation Operation definition
     * @param array $options Operation options
     * @return array{sql:string, parameters:array} Prepared fragment
     */
    public function parseLike(string|array $input, array $operation, array $options = []):mixed {

        # Prepare result
        $result = [];

        # Read the existing wildcard direction
        $parsed = parent::parseLike($input, $operation);

        # Preserve the established prefix and suffix matching behavior
        $pattern = match($parsed["position"] ?? ""){
            "start" => $parsed["value"][1]."%",
            "end" => "%".$parsed["value"][1],
            "start,end" => "%".$parsed["value"][1]."%",
            default => throw new CrazyException("Invalid SQL filter pattern.", 400),
        };

        # Bind the complete pattern
        $result = [
            "sql"           =>  "LIKE ?",
            "parameters"    =>  [
                $pattern
            ]
        ];

        # Return result
        return $result;

    }

    /**
     * Parse Literal Equality
     *
     * @param mixed $input Literal value
     * @param array $options Operation options
     * @return array{sql:string, parameters:array} Prepared fragment
     */
    public function parseDefault(mixed $input, array $options = []):mixed {

        # Prepare result
        $result = [];

        # Reject nested structures and nonfinite numbers
        if((!is_scalar($input) && $input !== null) || (is_float($input) && !is_finite($input))){

            # Reject unsupported filter values
            throw new CrazyException("SQL filter values must be scalar or null.", 400);

        }

        # Preserve null comparison semantics
        $result = $input === null
            ? [
                "sql"           =>  "IS NULL",
                "parameters"    =>  []
            ]
            : [
                "sql"           =>  "= ?",
                "parameters"    =>  [
                    $input
                ]
            ];

        # Return result
        return $result;

    }

    /** Public Static Methods
     ******************************************************
     */

    /**
     * Quote SQL Identifier
     *
     * @param mixed $input Column, table or alias
     * @param bool $qualified Allow one table or alias prefix
     * @return string Quoted identifier
     */
    public static function identifier(mixed $input, bool $qualified = false):string {

        # Prepare result
        $result = "";

        # Restrict identifiers to the framework's model naming convention
        if(!is_string($input) || !preg_match(
            $qualified
                ? '/\A[A-Za-z_][A-Za-z0-9_]{0,63}(?:\.[A-Za-z_][A-Za-z0-9_]{0,63})?\z/D'
                : '/\A[A-Za-z_][A-Za-z0-9_]{0,63}\z/D',
            $input
        )){

            # Reject SQL expressions and malformed field names
            throw new CrazyException("Invalid SQL filter identifier.", 400);

        }

        # Quote each identifier component
        $result = "`".str_replace(".", "`.`", $input)."`";

        # Return result
        return $result;

    }

    /** Private Static Methods
     ******************************************************
     */

    /**
     * Validate Numeric Operand
     *
     * @param mixed $value Numeric input
     * @return float Finite number
     */
    private static function _number(mixed $value):float {

        # Prepare result
        $result = 0.0;

        # Require a finite numeric operand
        if(!is_numeric($value) || !is_finite((float)$value)){

            # Reject malformed comparisons
            throw new CrazyException("SQL comparison requires a finite number.", 400);

        }

        # Normalize the numeric value
        $result = (float)$value;

        # Return result
        return $result;

    }

}
