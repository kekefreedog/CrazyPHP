<?php declare(strict_types=1);
/**
 * End-to-end Check
 *
 * Contract for verifications run on every generated scenario.
 *
 * To cover a new framework feature, add a class implementing this interface in tests/E2E/Check/;
 * it is discovered automatically and runs on every scenario it supports.
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

/**
 * Check
 *
 * Verification attached to one lifecycle phase of a scenario.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */
interface Check {

    /** Public Constants
     ******************************************************
     */

    /** @var string CREATED After `crazy new project` */
    public const CREATED = "created";

    /** @var string BUILT After `crazy docker new` (files generated, images built) */
    public const BUILT = "built";

    /** @var string RUNNING After `docker compose up --wait` */
    public const RUNNING = "running";

    /** Public Methods
     ******************************************************
     */

    /**
     * Phase
     *
     * @return string One of CREATED, BUILT, RUNNING
     */
    public function phase():string;

    /**
     * Supports
     *
     * @param Scenario $scenario Scenario about to be verified
     * @return bool
     */
    public function supports(Scenario $scenario):bool;

    /**
     * Verify
     *
     * Use PHPUnit's Assert; a failure stops the scenario and keeps its directory and log.
     *
     * @param Workspace $workspace Generated application
     * @return void
     */
    public function verify(Workspace $workspace):void;

}
