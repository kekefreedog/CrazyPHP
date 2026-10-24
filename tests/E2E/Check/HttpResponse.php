<?php declare(strict_types=1);
/**
 * HTTP Response Check
 *
 * The started stack answers on the published port with what the project type promises.
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
 * HTTP Response
 *
 * Verify the home page and the registered bundle through the published port.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */
class HttpResponse implements Check {

    /** Public Constants
     ******************************************************
     */

    /**
     * @var array HOME_STATUS Accepted statuses for GET / by project type
     *
     * Change here when a project type's contract changes; unlisted types only need to avoid 5xx.
     */
    public const HOME_STATUS = [
        "application"   => [200],
        "website"       => [200],
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

        # Get scenario
        $scenario = $workspace->scenario;
        $base = self::baseUrl($workspace);

        # Assert home page status
        $home = $workspace->request("$base/");
        $accepted = self::HOME_STATUS[$scenario->get("type")] ?? range(100, 499);
        Assert::assertContains(
            $home["status"],
            $accepted,
            "GET $base/ returned ".$home["status"].": ".substr($home["body"], 0, 300)
        );

        # Check browser project
        if(!$scenario->hasFrontend())

            # Nothing else to verify
            return;

        # Assert home page is HTML
        Assert::assertMatchesRegularExpression("/<html/i", $home["body"], "home page is HTML");

        # Iteration of registered bundles
        foreach($workspace->yaml("config/Front.yml")["Front"]["lastBuild"]["files"] ?? [] as $file){

            # Assert the page loads it and the server serves it
            Assert::assertStringContainsString($file, $home["body"], "home page references $file");
            Assert::assertSame(200, $workspace->request("$base/dist/$file")["status"], "GET /dist/$file");

        }

    }

    /** Public Static Methods
     ******************************************************
     */

    /**
     * Base URL
     *
     * @param Workspace $workspace Application
     * @return string
     */
    public static function baseUrl(Workspace $workspace):string {

        # Managed TLS (Caddy or FrankenPHP) answers on the HTTPS port; otherwise plain HTTP
        return $workspace->scenario->get("server.security") === "https" && $workspace->scenario->get("server.proxy") === "no"
            ? "https://localhost:".$workspace->httpsPort
            : "http://localhost:".$workspace->httpPort
        ;

    }

}
