<?php declare(strict_types=1);
/**
 * Test Json
 *
 * Test Json
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace Tests\Library\Array;

/**
 * Dependances
 */
use PHPUnit\Framework\TestCase;
use CrazyPHP\Library\Html\Dom;

/**
 * DOM test
 *
 * Methods for test DOM class
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class DomTest extends TestCase {

    /**
     * Test Get Plain Text
     * 
     * @return void
     */
    public function testGetPlainText():void {

        # Set input
        $input = "
<!DOCTYPE html>
<html>
<head>
<title>Page Title</title>
</head>
<body>

<h1>This is a Heading</h1>
<p>This is a paragraph.</p>

</body>
</html> 
        ";

        # Set expected
        $expected = "This is a Heading
This is a paragraph.";

        # Get result
        $result = Dom::getPlainText($input);

        # Asset
        $this->assertEquals($expected, $result);

    }

}