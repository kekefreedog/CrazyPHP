<?php declare(strict_types=1);
/**
 * Project Config Check
 *
 * Every wizard answer is recorded in the generated manifests and YAML configuration.
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
 * Project Config
 *
 * Verify composer.json, App.yml, Router.yml, Front.yml and Database.yml after creation.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */
class ProjectConfig implements Check {

    /** Public Methods
     ******************************************************
     */

    /**
     * Phase
     *
     * @return string
     */
    public function phase():string {

        # Right after creation
        return self::CREATED;

    }

    /**
     * Supports
     *
     * @param Scenario $scenario Scenario about to be verified
     * @return bool
     */
    public function supports(Scenario $scenario):bool {

        # Every project
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
        $version = $scenario->get("php_version");
        $library = $scenario->get("authentication");

        # Assert composer manifest: PHP version, platform and installed dependencies
        $composer = $workspace->json("composer.json");
        Assert::assertSame("^$version", $composer["require"]["php"] ?? null, "composer.json require.php");
        Assert::assertSame("$version.0", $composer["config"]["platform"]["php"] ?? null, "composer.json config.platform.php");
        Assert::assertSame("app/".$scenario->id(), $composer["name"] ?? null, "composer.json name");
        Assert::assertFileExists($workspace->path("vendor/autoload.php"), "composer update ran");
        Assert::assertFileExists($workspace->path("composer.lock"));

        # Check authentication library
        if($library !== "none"){

            # Assert it is declared and installed
            Assert::assertArrayHasKey($library, $composer["require"], "authentication package required");
            Assert::assertDirectoryExists($workspace->path("vendor/$library"), "authentication package installed");

        }

        # Assert recorded choices
        $app = $workspace->yaml("config/App.yml")["App"] ?? [];
        $router = $workspace->yaml("config/Router.yml")["Router"] ?? [];
        $front = $workspace->yaml("config/Front.yml")["Front"] ?? [];
        Assert::assertSame($scenario->get("type"), $app["project"]["type"] ?? null, "App.project.type");
        Assert::assertSame($library, $app["authentication"]["library"] ?? null, "App.authentication.library");
        Assert::assertSame($scenario->get("router"), $router["vendor"] ?? null, "Router.vendor");
        Assert::assertSame($scenario->get("frontend"), $front["bundler"] ?? null, "Front.bundler");

        # Check REST API
        if($scenario->get("type") === "api"){

            # Assert there are no browser routes
            Assert::assertEmpty($router["app"] ?? [], "Router.app is empty for an API");
            Assert::assertEmpty($router["asset"] ?? [], "Router.asset is empty for an API");

        }

        # Get selected databases
        $databases = $scenario->databases();

        # Check databases are selected
        if(!empty($databases)){

            # Get configured engines
            $collection = array_filter($workspace->yaml("config/Database.yml")["Database"]["collection"] ?? []);

            # Assert exactly the selected engines are configured
            Assert::assertEqualsCanonicalizing($databases, array_keys($collection), "Database.collection engines");

        }

    }

}
