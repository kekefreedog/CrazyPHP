<?php declare(strict_types=1);
/**
 * Form Persistence Test
 *
 * Verify opt-in persistence metadata survives PHP form rendering.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */
namespace Tests\Library\State;

/** Dependencies */
use CrazyPHP\Library\State\Components\Form;
use PHPUnit\Framework\TestCase;

/** Verify explicit item-level persistence. */
class FormPersistenceTest extends TestCase {

    /** Public Methods | Tests
     ******************************************************
     */

    /**
     * Test Persistence Items
     *
     * @return void
     */
    public function testPersistenceItems():void {

        # Create a form with one opted-in field
        $form = new Form([
            "id" => "reference",
            "items" => [
                ["name" => "filepath", "type" => "text"],
                ["name" => "scenario", "type" => "select", "persist" => true],
            ],
        ]);

        # Render the browser-facing form definition
        $result = $form->render();

        # Preserve the explicit item opt-in
        $this->assertTrue($result["items"][1]["persist"]);

        # Require no form-level persistence policy
        $this->assertArrayNotHasKey("persistence", $result);

        # Preserve the per-field opt-out in serialized form items
        $this->assertFalse($result["items"][0]["persist"] ?? false);

    }

    /**
     * Test Persistence Is Opt In
     *
     * @return void
     */
    public function testPersistenceIsOptIn():void {

        # Render a conventional form without preferences
        $result = (new Form(["id" => "ordinary"]))->render();

        # Preserve existing form output when persistence was not configured
        $this->assertArrayNotHasKey("persistence", $result);

    }

}
