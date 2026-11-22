<?php declare(strict_types=1);
/**
 * SQL Filters
 *
 * Compile model predicates and sorting for the SQL database drivers.
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
use CrazyPHP\Core\Model;

/**
 * SQL Filter
 *
 * Keep caller values in parameters and restrict identifiers to model field names.
 */
class SqlFilter {

    /** Public Static Methods
     ******************************************************
     */

    /**
     * Compile Where Clause
     *
     * @param array $filters Original model filter values
     * @param string $table Model table name
     * @param string $alias Main table alias
     * @return array{sql:string, parameters:array} Prepared WHERE clause
     */
    public static function where(array $filters, string $table, string $alias = "a"):array {

        # Prepare result
        $result = [
            "sql"           =>  "",
            "parameters"    =>  []
        ];

        # Validate the source table
        SqlOperation::identifier($table);

        # Quote the main table alias
        $prefix = SqlOperation::identifier($alias).".";

        # Compile filters in their original order
        foreach($filters as $key => $value){

            # Require named filter fields
            if(!is_string($key)){

                # Reject positional filters in SQL models
                throw new CrazyException("SQL filters require named fields.", 400);

            }

            # Prepare the default conjunction
            $conjunction = "AND";

            # Preserve the existing OR field prefixes
            if(in_array(substr($key, 0, 2), [
                "||",
                "!!"
            ], true)){

                # Remove the conjunction prefix
                $key = substr($key, 2);

                # Select the alternate conjunction
                $conjunction = "OR";

            }

            # Read the optional relation prefix
            $relation = substr($key, 0, 2);

            # Compile supported relation predicates
            if(in_array($relation, [
                "<>",
                "@@",
                "><"
            ], true)){

                # Compile the relation and its extra parameters
                $condition = self::_relation(substr($key, 2), $value, $table, $alias, $relation === "><");

            }else{

                # Validate and qualify the selected column
                $column = $prefix.SqlOperation::identifier($key);

                # Create an operation parser for this predicate
                $operation = new SqlOperation();

                # Bind IN values without interpreting them as SQL operations
                if(is_array($value)){

                    # Require a flat list of scalar values
                    if(!array_is_list($value)){

                        # Reject forged compiled fragments and associative lists
                        throw new CrazyException("SQL IN filters require a flat list.", 400);

                    }

                    # Validate each literal before binding
                    foreach($value as $item){

                        # Validate the scalar value
                        $operation->parseDefault($item);

                    }

                    # Make empty IN lists match no records
                    $condition = [
                        "sql"           =>  $value ? $column." IN (".implode(", ", array_fill(0, count($value), "?")).")" : "0 = 1",
                        "parameters"    =>  $value,
                    ];

                }else{

                    # Validate scalar types before invoking the string operation parser
                    $literal = $operation->parseDefault($value);

                    # Preserve zero, false, null and numeric values without string coercion
                    $condition = is_string($value) && $value !== "" && $value !== "0"
                        ? $operation->run($value)
                        : $literal;

                    # Qualify the compiled comparison
                    $condition["sql"] = $column." ".$condition["sql"];

                }

            }

            # Join predicates using only fixed conjunctions
            $result["sql"] .= ($result["sql"] === "" ? " WHERE " : " ".$conjunction." ").$condition["sql"];

            # Preserve positional parameter order
            array_push($result["parameters"], ...$condition["parameters"]);

        }

        # Return result
        return $result;

    }

    /**
     * Compile Sort Fields
     *
     * @param array|string|null $sort Field names, -field or asc/desc aliases
     * @param string $alias Optional table alias
     * @return string ORDER BY contents without the keyword
     */
    public static function order(array|string|null $sort, string $alias = ""):string {

        # Prepare result
        $result = "";

        # Prepare validated fields
        $fields = [];

        # Normalize sort input
        $sort = is_string($sort) ? [
            $sort
        ] : ($sort ?? []);

        # Quote the optional alias
        $prefix = $alias === "" ? "" : SqlOperation::identifier($alias).".";

        # Validate every requested sort field
        foreach($sort as $field){

            # Require scalar nonempty field names
            if(!is_string($field) || $field === ""){

                # Reject malformed sort input
                throw new CrazyException("SQL sort requires field names.", 400);

            }

            # Expand the existing ID sorting aliases
            $field = match(strtolower($field)){
                "asc" => "id",
                "desc" => "-id",
                default => $field,
            };

            # Read the descending prefix
            $descending = str_starts_with($field, "-");

            # Validate and quote the actual field
            $column = SqlOperation::identifier($descending ? substr($field, 1) : $field);

            # Add a fixed direction keyword
            $fields[] = $prefix.$column.($descending ? " DESC" : " ASC");

        }

        # Join validated sort fields
        $result = implode(", ", $fields);

        # Return result
        return $result;

    }

    /** Private Static Methods
     ******************************************************
     */

    /**
     * Compile Related Model Predicate
     *
     * @param string $key Linked field name
     * @param mixed $value Model@field:value relation syntax
     * @param string $table Source table
     * @param string $alias Source alias
     * @param bool $exclude Require the relation to be absent
     * @return array{sql:string, parameters:array} Prepared EXISTS predicate
     */
    private static function _relation(string $key, mixed $value, string $table, string $alias, bool $exclude):array {

        # Prepare result
        $result = [
            "sql"           =>  "",
            "parameters"    =>  []
        ];

        # Validate the linked field
        SqlOperation::identifier($key);

        # Require a plain relation expression
        if(!is_string($value) || $value === ""){

            # Reject nested and empty relation expressions
            throw new CrazyException("SQL relation requires a model name.", 400);

        }

        # Split the model and its literal constraints
        $parts = explode("@", $value);

        # Read the related model name
        $related = array_shift($parts);

        # Validate the table identifier before consulting configuration
        $relatedTable = SqlOperation::identifier($related);

        # Require a configured model
        if(!Model::exists($related)){

            # Reject unknown relations instead of removing the constraint
            throw new CrazyException("Unknown SQL relation model.", 400);

        }

        # Preserve the existing relation-key convention
        $linked = str_contains($key, "_")
            ? (str_starts_with($key, strtolower($related)."_") ? substr($key, strlen($related) + 1) : $key)
            : strtolower($table)."_".$key;

        # Use a local subquery alias distinct from the outer alias
        $subAlias = $alias === "related" ? "related_item" : "related";

        # Quote the subquery alias
        $quotedAlias = SqlOperation::identifier($subAlias);

        # Build the relation using validated identifiers
        $result["sql"] = ($exclude ? "NOT " : "")."EXISTS (SELECT 1 FROM ".$relatedTable." AS ".$quotedAlias
            ." WHERE ".$quotedAlias.".".SqlOperation::identifier($linked)
            ." = ".SqlOperation::identifier($alias).".".SqlOperation::identifier($key);

        # Bind each extra relation constraint
        foreach($parts as $part){

            # Split only the first delimiter to preserve colons in literal values
            $pair = explode(":", $part, 2);

            # Require complete field/value pairs
            if(count($pair) !== 2){

                # Reject malformed relation syntax
                throw new CrazyException("SQL relation constraints require field:value pairs.", 400);

            }

            # Append a validated field and positional placeholder
            $result["sql"] .= " AND ".$quotedAlias.".".SqlOperation::identifier($pair[0])." = ?";

            # Preserve the literal value without SQL interpretation
            $result["parameters"][] = $pair[1];

        }

        # Close the EXISTS expression
        $result["sql"] .= ")";

        # Return result
        return $result;

    }

}
