<?php declare(strict_types=1);
/**
 * Frontend Check
 *
 * Browser projects get the chosen bundler, its scripts and a registered first build;
 * backend projects get no frontend tooling at all.
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
 * Frontend
 *
 * Verify bundler files, npm scripts and the first build after creation.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */
class Frontend implements Check {

    /** Public Constants
     ******************************************************
     */

    /** @var array CONFIG_FILES Build configuration owned by each bundler */
    public const CONFIG_FILES = [
        "vite"      => "vite.build.mjs",
        "webpack"   => "webpack.config.js",
    ];

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

        # Every project: absence is verified too
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
        $bundler = $scenario->get("frontend");
        $front = $workspace->yaml("config/Front.yml")["Front"] ?? [];

        # Check backend project
        if(!$scenario->hasFrontend()){

            # Assert no frontend tooling
            Assert::assertFileDoesNotExist($workspace->path("package.json"), "no package.json without frontend");
            Assert::assertDirectoryDoesNotExist($workspace->path("node_modules"), "npm not run without frontend");
            Assert::assertSame([], $front["scripts"] ?? [], "Front.scripts is empty without frontend");

            # Iteration of bundler files
            foreach(self::CONFIG_FILES as $file)

                # Assert absent
                Assert::assertFileDoesNotExist($workspace->path($file));

            # Stop here
            return;

        }

        # Iteration of bundler files
        foreach(self::CONFIG_FILES as $name => $file){

            # Assert only the chosen bundler's configuration exists
            if($name === $bundler){

                # Chosen bundler
                Assert::assertFileExists($workspace->path($file));

            }else{

                # Other bundler
                Assert::assertFileDoesNotExist($workspace->path($file));

            }

        }

        # Get package manifest
        $package = $workspace->json("package.json");

        # Iteration of scripts
        foreach(["build", "dev", "watch"] as $script){

            # Assert declared and registered
            Assert::assertArrayHasKey($script, $package["scripts"] ?? [], "package.json script $script");
            Assert::assertContains($script, $front["scripts"] ?? [], "Front.scripts contains $script");

        }

        # Assert first build registered
        $build = $front["lastBuild"] ?? [];
        Assert::assertNotEmpty($build["hash"] ?? null, "Front.lastBuild.hash");
        Assert::assertSame("production", $build["mode"] ?? null, "Front.lastBuild.mode");
        Assert::assertNotEmpty($build["files"] ?? [], "Front.lastBuild.files");

        # Iteration of built files
        foreach($build["files"] as $file)

            # Assert present on disk
            Assert::assertFileExists($workspace->path("public/dist/$file"), "built asset $file");

    }

}
