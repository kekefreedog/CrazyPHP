<?php declare(strict_types=1);
/**
 * Project Matrix Test
 *
 * Create real projects, generate their Docker stack, start it and verify it, for every scenario
 * selected from the wizard definitions. Slow and networked: disabled unless CRAZY_E2E=1.
 *
 *   composer test:e2e                                       # strategy "each" (every choice at least once)
 *   CRAZY_E2E_STRATEGY=pairwise composer test:e2e           # every pair of choices
 *   CRAZY_E2E_FILTER="type=api;server.type=nginx,apache" composer test:e2e
 *
 * Variables:
 *   CRAZY_E2E           1 to enable
 *   CRAZY_E2E_STRATEGY  default | each (default) | pairwise | full
 *   CRAZY_E2E_FILTER    `param=value[,value];param=value` on Scenario::parameters() names
 *   CRAZY_E2E_LIMIT     maximum number of scenarios
 *   CRAZY_E2E_PHASE     created | built | running (default): how far each scenario goes
 *   CRAZY_E2E_DIR       working directory, shared with Docker (default tests/.cache/e2e)
 *   CRAZY_E2E_KEEP      1 to keep passing scenarios on disk (failing ones are always kept)
 *   CRAZY_E2E_RMI       1 to remove each scenario's images after it ran
 *
 * PHP version 8.3
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */
namespace Tests\E2E;

/**
 * Dependencies
 */
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\E2E\Scenario\Scenario;
use Tests\E2E\Support\Workspace;
use Tests\E2E\Scenario\Matrix;
use PHPUnit\Framework\TestCase;
use CrazyPHP\Model\App\Create;
use Tests\E2E\Check\Check;
use ReflectionMethod;
use ReflectionClass;

/**
 * Project Matrix Test
 *
 * Run every selected scenario through creation, Docker build and start, then the discovered checks.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */
#[Group("e2e")]
class ProjectMatrixTest extends TestCase {

    /** Public Constants
     ******************************************************
     */

    /** @var array PHASES Ordered lifecycle phases */
    public const PHASES = [Check::CREATED, Check::BUILT, Check::RUNNING];

    /** Public method | Tests
     ******************************************************
     */

    /**
     * Test Scenario
     *
     * @param Scenario|null $scenario Scenario, null when e2e tests are disabled
     * @return void
     */
    #[DataProvider("scenarios")]
    public function testScenario(?Scenario $scenario):void {

        # Check e2e tests are enabled
        if($scenario === null)

            # Skip by default
            $this->markTestSkipped("Set CRAZY_E2E=1 (or run composer test:e2e) to create and start real projects.");

        # Iteration of required tools
        foreach(["composer", "docker", "curl"] as $tool)

            # Check tool is installed
            if(!self::_which($tool))

                # Skip without the tool
                $this->markTestSkipped("$tool is required for e2e tests.");

        # Get PHP extensions the project requires but the local PHP lacks
        $missing = self::_missingExtensions($scenario);

        # Check extensions, as `composer update` runs with the local PHP
        if(!empty($missing))

            # Skip with the extensions to install
            $this->markTestSkipped("Local PHP lacks ".implode(", ", $missing)." required by this scenario's databases.");

        # Get lifecycle target
        $target = array_search(getenv("CRAZY_E2E_PHASE") ?: Check::RUNNING, self::PHASES, true);

        # Check target
        if($target === false)

            # Reject unknown phase
            $this->fail("CRAZY_E2E_PHASE must be one of ".implode(", ", self::PHASES).".");

        # Prepare the application folder
        $workspace = new Workspace($scenario, getenv("CRAZY_E2E_DIR") ?: dirname(__DIR__)."/.cache/e2e");
        $passed = false;

        try{

            # Run crazy new project
            $workspace->bootstrap()->create();
            $this->_verify($workspace, Check::CREATED);

            # Check docker phase is requested
            if($target >= 1){

                # Run crazy docker new
                $workspace->docker();
                $this->_verify($workspace, Check::BUILT);

            }

            # Check running phase is requested
            if($target >= 2){

                # Run docker compose up
                $workspace->up();
                $this->_verify($workspace, Check::RUNNING);

            }

            # Mark success
            $passed = true;

        }finally{

            # Always stop the stack and free its ports and volumes
            $workspace->down((bool) getenv("CRAZY_E2E_RMI"));

            # Remove passing scenarios unless kept
            if($passed && !getenv("CRAZY_E2E_KEEP")){

                # Remove files
                $workspace->remove();

            # Keep failing scenarios for diagnosis
            }elseif(!$passed){

                # Show where they are
                fwrite(STDERR, PHP_EOL."Kept ".$workspace->root." (log: ".$workspace->log.")".PHP_EOL);

            }

        }

    }

    /** Public Static Methods | Providers
     ******************************************************
     */

    /**
     * Scenarios
     *
     * @return array<string, array{0: Scenario|null}>
     */
    public static function scenarios():array {

        # Check e2e tests are enabled
        if(!getenv("CRAZY_E2E"))

            # One skipped placeholder, without enumerating thousands of combinations
            return ["disabled" => [null]];

        # Prepare filter
        $filter = [];

        # Iteration of filter rules
        foreach(array_filter(explode(";", (string) getenv("CRAZY_E2E_FILTER"))) as $rule){

            # Split name and values
            [$name, $values] = array_map("trim", explode("=", $rule, 2)) + [1 => ""];

            # Set accepted values
            $filter[$name] = array_map("trim", explode(",", $values));

        }

        # Get limit
        $limit = getenv("CRAZY_E2E_LIMIT");
        $limit = $limit === false || $limit === "" ? null : (int) $limit;

        # Prepare result
        $result = [];

        # Iteration of selected scenarios
        foreach(Matrix::scenarios(getenv("CRAZY_E2E_STRATEGY") ?: "each", $filter, $limit) as $scenario)

            # Name the data set
            $result[$scenario->label()." (".$scenario->id().")"] = [$scenario];

        # Return result
        return $result;

    }

    /** Private Methods
     ******************************************************
     */

    /**
     * Verify
     *
     * @param Workspace $workspace Generated application
     * @param string $phase Lifecycle phase reached
     * @return void
     */
    private function _verify(Workspace $workspace, string $phase):void {

        # Iteration of discovered checks
        foreach(self::_checks() as $check)

            # Run checks of this phase that support the scenario
            if($check->phase() === $phase && $check->supports($workspace->scenario))

                # Verify
                $check->verify($workspace);

    }

    /** Private Static Methods
     ******************************************************
     */

    /**
     * Checks
     *
     * Discover every Check implementation in tests/E2E/Check.
     *
     * @return Check[]
     */
    private static function _checks():array {

        # Cache discovery
        static $checks = null;

        # Check cache
        if($checks !== null)

            # Return cached checks
            return $checks;

        # Prepare checks
        $checks = [];

        # Iteration of check files
        foreach(glob(__DIR__."/Check/*.php") as $file){

            # Get class
            $class = "Tests\\E2E\\Check\\".basename($file, ".php");

            # Keep concrete checks
            if(
                class_exists($class) &&
                is_subclass_of($class, Check::class) &&
                !(new ReflectionClass($class))->isAbstract()
            )

                # New check
                $checks[] = new $class();

        }

        # Return checks
        return $checks;

    }

    /**
     * Missing Extensions
     *
     * Read the `ext-*` requirements `Model\App\Create` adds for the selected databases, so new
     * engines are covered without editing the test, and keep those not loaded locally or loaded
     * with a version outside the constraint (e.g. ext-mongodb 2.x for `^1.21`).
     *
     * @param Scenario $scenario Scenario about to run
     * @return string[]
     */
    private static function _missingExtensions(Scenario $scenario):array {

        # Read the generator's dependency map for these answers
        $dependencies = (new ReflectionMethod(Create::class, "_databaseDependencies"))
            ->invoke(new Create(["application" => $scenario->createOptions()]))
        ;

        # Prepare result
        $result = [];

        # Iteration of dependencies
        foreach($dependencies as $package => $constraint){

            # Keep PHP extensions only
            if(!str_starts_with($package, "ext-"))

                # Next dependency
                continue;

            # Get extension name and loaded version
            $extension = substr($package, 4);
            $version = phpversion($extension);

            # Check extension is loaded
            if(!extension_loaded($extension)){

                # Add missing extension
                $result[] = $extension;

            # Check loaded version matches the constraint
            }elseif(is_string($version) && !self::_satisfies($version, (string) $constraint)){

                # Add extension with its incompatible version
                $result[] = "$extension $constraint (installed $version)";

            }

        }

        # Return result
        return $result;

    }

    /**
     * Satisfies
     *
     * Minimal check of the constraints `Model\App\Create` writes: `*` and caret ranges such as `^1.21`.
     * Other forms are accepted and left to Composer.
     *
     * @param string $version Installed version
     * @param string $constraint Composer constraint
     * @return bool
     */
    private static function _satisfies(string $version, string $constraint):bool {

        # Check caret range
        if(!preg_match('/^\^(\d+)(?:\.(\d+))?(?:\.(\d+))?$/', trim($constraint), $matches))

            # Accept `*` and unhandled forms
            return true;

        # Get minimum version
        $minimum = $matches[1].".".($matches[2] ?? "0").".".($matches[3] ?? "0");

        # Same major and at least the minimum
        return (int) $version === (int) $matches[1] && version_compare($version, $minimum, ">=");

    }

    /**
     * Which
     *
     * @param string $tool Executable
     * @return bool
     */
    private static function _which(string $tool):bool {

        # Iteration of PATH directories
        foreach(explode(PATH_SEPARATOR, (string) getenv("PATH")) as $directory)

            # Check tool is executable
            if(is_executable("$directory/$tool"))

                # Found
                return true;

        # Not found
        return false;

    }

}
