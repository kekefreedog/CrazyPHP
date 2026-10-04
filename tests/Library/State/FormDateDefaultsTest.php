<?php declare(strict_types=1);
/**
 * Form Date Defaults Test
 *
 * Verify relative date defaults through form state normalization.
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
use DateTimeImmutable;

/**
 * Form Date Defaults Test
 *
 * Cover date aliases, ranges and invalid defaults in forms and filters.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */
class FormDateDefaultsTest extends TestCase {

    /** Public method | Tests
     ******************************************************
     */

    /**
     * Test Relative Defaults
     *
     * Resolve aliases in the configured server timezone for forms and filters.
     *
     * @return void
     */
    public function testRelativeDefaults():void {

        # Save the original timezone
        $timezone = date_default_timezone_get();

        try{

            # Test timezones on both sides of UTC
            foreach(["Europe/Paris", "America/Los_Angeles", "Pacific/Kiritimati"] as $zone){

                # Set the server timezone
                date_default_timezone_set($zone);

                # Test forms and filters with each supported alias
                foreach([false, true] as $filter){

                    foreach(["today", "yesterday", "tomorrow"] as $relative){

                        foreach([$relative, $relative."()", " ".strtoupper($relative)." "] as $alias){

                            # Normalize the date default
                            $form = new Form([
                                "filter" => $filter,
                                "items"  => [[
                                    "type"    => "date",
                                    "name"    => "date",
                                    "default" => $alias,
                                ]],
                            ]);

                            # Assert the resolved local date
                            $this->assertSame((new DateTimeImmutable($relative))->format("Y-m-d"), $form->getItems()[0]["default"]);

                        }

                    }

                }

            }

        }finally{

            # Restore the original timezone
            date_default_timezone_set($timezone);

        }

    }

    /**
     * Test Relative Ranges And Bounds
     *
     * Resolve the same aliases for range endpoints and selection bounds.
     *
     * @return void
     */
    public function testRelativeRangesAndBounds():void {

        # Set the expected endpoints
        $yesterday = (new DateTimeImmutable("yesterday"))->format("Y-m-d");
        $tomorrow = (new DateTimeImmutable("tomorrow"))->format("Y-m-d");

        # Test both range configurations
        foreach([["_style" => ["date" => ["range" => true]]], ["multiple" => true]] as $mode){

            # Normalize the range default
            $form = new Form([
                "items" => [[
                    "type"    => "date",
                    "default" => "yesterday() - tomorrow",
                ] + $mode],
            ]);

            # Assert the resolved range
            $this->assertSame($yesterday." - ".$tomorrow, $form->getItems()[0]["default"]);

        }

        # Normalize the selection bounds
        $form = new Form([
            "items" => [[
                "type"   => "date",
                "select" => [["value" => "yesterday"], ["value" => "tomorrow()"]],
            ]],
        ]);

        # Assert the resolved bounds
        $this->assertSame([["value" => $yesterday], ["value" => $tomorrow]], $form->getItems()[0]["select"]);

    }

    /**
     * Test Explicit And Invalid Defaults
     *
     * Preserve explicit dates and reject unsupported defaults and reversed ranges.
     *
     * @return void
     */
    public function testExplicitAndInvalidDefaults():void {

        # Test explicit, unsupported and invalid dates
        foreach([
            "2026-09-29" => "2026-09-29",
            "next week"  => null,
            "2026-02-30" => null,
        ] as $input => $expected){

            # Normalize the date default
            $form = new Form(["items" => [["type" => "date", "default" => $input]]]);

            # Assert the normalized default
            $this->assertSame($expected, $form->getItems()[0]["default"]);

        }

        # Normalize a reversed range
        $form = new Form([
            "items" => [[
                "type"    => "date",
                "default" => "tomorrow - yesterday",
                "_style"  => ["date" => ["range" => true]],
            ]],
        ]);

        # Assert the reversed range is rejected
        $this->assertNull($form->getItems()[0]["default"]);

    }

}
