<?php declare(strict_types=1);
/**
 * Running Stack Check
 *
 * After `docker compose up --wait`, the preparation job succeeded and every service is up.
 *
 * PHP version 8.3
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */
namespace Tests\E2E\Check;

/**
 * Dependencies
 */
use Tests\E2E\Scenario\Scenario;
use Tests\E2E\Support\Workspace;
use PHPUnit\Framework\Assert;

/**
 * Running Stack
 *
 * Verify container states reported by docker compose ps.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */
class RunningStack implements Check {

    /** Public Methods
     ******************************************************
     */

    /**
     * Phase
     *
     * @return string
     */
    public function phase():string {

        # Stack started
        return self::RUNNING;

    }

    /**
     * Supports
     *
     * @param Scenario $scenario Scenario about to be verified
     * @return bool
     */
    public function supports(Scenario $scenario):bool {

        # Every stack
        return true;

    }

    /**
     * Verify
     *
     * @param Workspace $workspace Generated application
     * @return void
     */
    public function verify(Workspace $workspace):void {

        # Get containers
        [, $output] = $workspace->run(["docker", "compose", "ps", "--all", "--format", "json"]);

        # Decode one JSON object per line (Compose v2) or one array (older versions)
        $containers = str_starts_with(trim($output), "[")
            ? json_decode($output, true)
            : array_map(static fn($line) => json_decode($line, true), array_filter(explode("\n", trim($output))))
        ;

        # Iteration of containers
        foreach($containers as $container){

            # Check the one-shot preparation job (composer install + database setup)
            if($container["Service"] === "prepare"){

                # Assert it succeeded
                Assert::assertSame(0, $container["ExitCode"], "prepare job exit code");

                # Next container
                continue;

            }

            # Assert services stay up
            Assert::assertSame("running", $container["State"], $container["Service"]." state");

        }

    }

}
