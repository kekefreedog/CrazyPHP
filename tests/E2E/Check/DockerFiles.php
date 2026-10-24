<?php declare(strict_types=1);
/**
 * Docker Files Check
 *
 * The Compose stack matches the server, security and database choices.
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
use CrazyPHP\Library\File\Docker;
use Tests\E2E\Scenario\Scenario;
use Tests\E2E\Support\Workspace;
use PHPUnit\Framework\Assert;

/**
 * Docker Files
 *
 * Verify docker-compose.yml services, the PHP image and saved ports after crazy docker new.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */
class DockerFiles implements Check {

    /** Public Methods
     ******************************************************
     */

    /**
     * Phase
     *
     * @return string
     */
    public function phase():string {

        # After crazy docker new
        return self::BUILT;

    }

    /**
     * Supports
     *
     * @param Scenario $scenario Scenario about to be verified
     * @return bool
     */
    public function supports(Scenario $scenario):bool {

        # Every scenario with a Docker stack
        return true;

    }

    /**
     * Verify
     *
     * @param Workspace $workspace Generated application
     * @return void
     */
    public function verify(Workspace $workspace):void {

        # Get scenario
        $scenario = $workspace->scenario;
        $server = $scenario->get("server.type");
        $services = $workspace->yaml("docker-compose.yml")["services"] ?? [];

        # Set runtime services
        $expected = ["prepare", "webserver"];

        # Check nginx
        if($server === "nginx")

            # Add PHP-FPM
            $expected[] = "php-fpm";

        # Check Caddy terminates managed HTTPS (FrankenPHP does it itself)
        if(
            $scenario->get("server.security") === "https" &&
            $scenario->get("server.proxy") === "no" &&
            $server !== "frankenphp"
        )

            # Add Caddy
            $expected[] = "caddy";

        # Iteration of database engines
        foreach($scenario->databases() as $engine)

            # Add one service per engine
            $expected[] = Docker::DATABASE_TO_SERVICE[$engine];

        # Assert same services, no more
        Assert::assertEqualsCanonicalizing($expected, array_keys($services), "docker-compose.yml services");

        # Assert PHP image follows the project's PHP version
        $dockerfile = (string) @file_get_contents($workspace->path("docker/php/Dockerfile"));
        Assert::assertStringContainsString(
            "php".$scenario->get("php_version"),
            str_replace([":", "-"], "", strtolower($dockerfile)),
            "docker/php/Dockerfile uses PHP ".$scenario->get("php_version")
        );

        # Assert chosen ports saved for Compose
        $env = (string) file_get_contents($workspace->path(".env"));
        Assert::assertStringContainsString("CRAZY_HTTP_PORT=".$workspace->httpPort, $env);

    }

}
