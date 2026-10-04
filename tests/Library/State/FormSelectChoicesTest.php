<?php declare(strict_types=1);
/**
 * Form Select Choices Test
 *
 * Verify select shorthand at the PHP-to-template state boundary.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */
namespace Tests\Library\State;

/**
 * Dependencies
 */
use CrazyPHP\Library\State\Components\Form;
use PHPUnit\Framework\TestCase;

/**
 * Form Select Choices Test
 *
 * Cover shorthand choices and compatibility with existing select collections.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */
class FormSelectChoicesTest extends TestCase {

    /** Public method | Tests
     ******************************************************
     */

    /**
     * Test Shorthand Choices
     *
     * @return void
     */
    public function testShorthandChoices():void {

        # Test single and multiple choices in forms and filters
        foreach([false, true] as $filter){

            foreach([false, true] as $multiple){

                # Set the default selection
                $default = $multiple ? ["Shot", "Asset"] : "Asset";

                # Normalize shorthand choices
                $form = new Form([
                    "filter" => $filter,
                    "items"  => [[
                        "name"     => "type",
                        "type"     => "select",
                        "multiple" => $multiple,
                        "default"  => $default,
                        "select"   => ["Shot", "Asset", "Sequence"],
                    ]],
                ]);
                $item = $form->getItems()[0];

                # Assert each shorthand supplies both the value and label
                $this->assertSame([
                    ["value" => "Shot", "label" => "Shot"],
                    ["value" => "Asset", "label" => "Asset"],
                    ["value" => "Sequence", "label" => "Sequence"],
                ], $item["select"]);

                # Assert the default selection is preserved
                $this->assertSame($default, $item["default"]);

            }

        }

    }

    /**
     * Test Mixed Choices And Numeric Zero
     *
     * @return void
     */
    public function testMixedChoicesAndNumericZero():void {

        # Set a detailed choice alongside shorthand and numeric values
        $detailed = [
            "value"    => "Asset",
            "label"    => "Production asset",
            "disabled" => true,
            "default"  => true,
        ];
        $form = new Form([
            "items" => [[
                "type"   => "select",
                "select" => ["Shot", $detailed, 0, 1.5],
            ]],
        ]);

        # Assert detailed choices and numeric value types are preserved
        $this->assertSame([
            ["value" => "Shot", "label" => "Shot"],
            $detailed,
            ["value" => 0, "label" => "0"],
            ["value" => 1.5, "label" => "1.5"],
        ], $form->getItems()[0]["select"]);

    }

    /**
     * Test Existing Collections Remain Intact
     *
     * @return void
     */
    public function testExistingCollectionsRemainIntact():void {

        # Test empty, remote and detailed option collections
        foreach([
            [],
            ["url" => "/api/options", "value" => "id", "label" => "name"],
            [["value" => "Shot", "label" => "Shot"]],
        ] as $options){

            # Normalize the existing configuration
            $form = new Form(["items" => [["type" => "select", "select" => $options]]]);

            # Assert the collection is unchanged
            $this->assertSame($options, $form->getItems()[0]["select"]);

        }

    }

}
