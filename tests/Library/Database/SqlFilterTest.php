<?php declare(strict_types=1);
/**
 * SQL Filter Regression Tests
 *
 * Verify parameter separation and rejection of caller-supplied SQL syntax.
 *
 * PHP version 8.5
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2026 Kévin Zarshenas
 */
namespace Tests\Library\Database;

/**
 * Dependances
 */
use CrazyPHP\Library\Database\Operation\SqlOperation;
use CrazyPHP\Library\Database\Operation\SqlFilter;
use PHPUnit\Framework\Attributes\DataProvider;
use CrazyPHP\Exception\CrazyException;
use PHPUnit\Framework\TestCase;

/**
 * SQL Filter Test
 *
 * Exercise the compiler without a database connection.
 */
class SqlFilterTest extends TestCase {

    /** Public Methods | Tests
     ******************************************************
     */

    /**
     * Test Literal Parameters
     *
     * @return void
     */
    public function testLiteralParameters():void {

        # Prepare a quote and backslash payload
        $payload = "x\"'\\ OR 1=1 -- ";

        # Compile literal, list and empty-value filters together
        $result = SqlFilter::where([
            "name"        =>  $payload,
            "id"          =>  [
                1,
                $payload
            ],
            "active"      =>  false,
            "score"       =>  0,
            "nullable"    =>  null,
            "empty"       =>  "",
            "zero"        =>  "0",
        ], "Fixture");

        # Verify the SQL contains only placeholders and validated fields
        $this->assertSame(
            " WHERE `a`.`name` = ? AND `a`.`id` IN (?, ?) AND `a`.`active` = ?"
                ." AND `a`.`score` = ? AND `a`.`nullable` IS NULL AND `a`.`empty` = ? AND `a`.`zero` = ?",
            $result["sql"]
        );

        # Verify all literal values remain separate and unchanged
        $this->assertSame([
            $payload,
            1,
            $payload,
            false,
            0,
            "",
            "0"
        ], $result["parameters"]);

        # Ensure an empty IN list cannot broaden the read
        $this->assertSame([
            "sql"           =>  " WHERE 0 = 1",
            "parameters"    =>  []
        ], SqlFilter::where([
            "id"    =>  []
        ], "Fixture"));

    }

    /**
     * Test Pattern And Range Parameters
     *
     * @return void
     */
    public function testPatternAndRangeParameters():void {

        # Prepare a malicious-looking literal
        $payload = "x' OR 1=1 -- ";

        # Compile a wildcard and a range
        $result = SqlFilter::where([
            "name"     =>  "*".$payload."*",
            "other"    =>  "[".$payload.":Z]"
        ], "Fixture");

        # Verify neither operand appears in the SQL
        $this->assertSame(" WHERE `a`.`name` LIKE ? AND `a`.`other` BETWEEN ? AND ?", $result["sql"]);

        # Preserve the caller's values in parameters
        $this->assertSame([
            "%".$payload."%",
            $payload,
            "Z"
        ], $result["parameters"]);

        # Preserve qualified column comparisons
        $this->assertSame(
            [
                "sql"           =>  "= `Fixture`.`name`",
                "parameters"    =>  []
            ],
            (new SqlOperation("@all", "Fixture"))->run("=name")
        );

    }

    /**
     * Test Invalid Filter Syntax
     *
     * @param array $filters Malformed or injected filters
     * @return void
     */
    #[DataProvider("invalidFilters")]
    public function testInvalidFilterSyntax(array $filters):void {

        # Expect a client-input error
        $this->expectException(CrazyException::class);

        # Require an HTTP 400 classification
        $this->expectExceptionCode(400);

        # Reject input before it can reach PDO
        SqlFilter::where($filters, "Fixture");

    }

    /**
     * Provide Invalid Filters
     *
     * @return array Named invalid input cases
     */
    public static function invalidFilters():array {

        # Prepare malicious syntax and unsupported structures
        $result = [
            "field injection"         =>  [
                [
                    "id OR 1=1 -- "    =>  1
                ]
            ],
            "quoted field"            =>  [
                [
                    "name` = 1 -- "    =>  "x"
                ]
            ],
            "qualified filter"        =>  [
                [
                    "a.name"    =>  "x"
                ]
            ],
            "OR field"                =>  [
                [
                    "||id) OR 1=1 -- "    =>  1
                ]
            ],
            "column reference"        =>  [
                [
                    "name"    =>  "=name` OR 1=1 -- "
                ]
            ],
            "negated reference"       =>  [
                [
                    "name"    =>  "!=name` OR 1=1 -- "
                ]
            ],
            "comparison"              =>  [
                [
                    "score"    =>  ">0 OR 1=1"
                ]
            ],
            "nonfinite comparison"    =>  [
                [
                    "score"    =>  ">1e999"
                ]
            ],
            "nonfinite literal"       =>  [
                [
                    "score"    =>  INF
                ]
            ],
            "nested list"             =>  [
                [
                    "id"    =>  [
                        [
                            "x"
                        ]
                    ]
                ]
            ],
            "forged fragment"         =>  [
                [
                    "name"    =>  [
                        "sql"           =>  "OR 1=1",
                        "parameters"    =>  []
                    ]
                ]
            ],
            "positional field"        =>  [
                [
                    "value"
                ]
            ],
            "relation field"          =>  [
                [
                    "<>id) OR 1=1"    =>  "Router"
                ]
            ],
            "relation table"          =>  [
                [
                    "<>id"    =>  "Router WHERE 1=1 -- "
                ]
            ],
            "relation list"           =>  [
                [
                    "<>id"    =>  [
                        "Router"
                    ]
                ]
            ],
        ];

        # Return result
        return $result;

    }

    /**
     * Test Invalid Sort Syntax
     *
     * @param array $sort Malformed sorting
     * @return void
     */
    #[DataProvider("invalidSorts")]
    public function testInvalidSortSyntax(array $sort):void {

        # Expect a client-input error
        $this->expectException(CrazyException::class);

        # Require an HTTP 400 classification
        $this->expectExceptionCode(400);

        # Reject expressions and nested values
        SqlFilter::order($sort);

    }

    /**
     * Provide Invalid Sorts
     *
     * @return array Named malformed sort cases
     */
    public static function invalidSorts():array {

        # Prepare sort expressions that must never become SQL
        $result = [
            "expression"         =>  [
                [
                    "id, (SELECT SLEEP(1))"
                ]
            ],
            "comment"            =>  [
                [
                    "id -- "
                ]
            ],
            "direction"          =>  [
                [
                    "id DESC"
                ]
            ],
            "qualified field"    =>  [
                [
                    "a.id"
                ]
            ],
            "quoted field"       =>  [
                [
                    "`id`"
                ]
            ],
            "double prefix"      =>  [
                [
                    "--id"
                ]
            ],
            "nested field"       =>  [
                [
                    [
                        "id"
                    ]
                ]
            ],
            "numeric field"      =>  [
                [
                    1
                ]
            ],
            "empty field"        =>  [
                [
                    ""
                ]
            ],
        ];

        # Return result
        return $result;

    }

}
