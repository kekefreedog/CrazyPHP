<?php declare(strict_types=1);
/**
 * End-to-end Step
 *
 * Run one generator in its own process, as `bin/CrazyCommand` and `bin/CrazyDocker` do after the
 * wizard: `php step.php <create|docker> <application directory> <options json>`.
 *
 * The library's own autoloader is used so the step also runs when the project requires a newer
 * PHP than the local interpreter (Composer's platform check would reject the project autoloader).
 *
 * PHP version 8.3
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */

# Hide dependency deprecations, like the CLI entrypoints
error_reporting(E_ALL & ~E_DEPRECATED);

# Load the library under test
require dirname(__DIR__, 3)."/vendor/autoload.php";

/**
 * Dependencies
 */
use CrazyPHP\Model\Docker\Install;
use CrazyPHP\Library\Cli\Runtime;
use CrazyPHP\Model\App\Create;
use CrazyPHP\Model\Env;

# Read arguments
[, $step, $root, $json] = $argv + [null, null, null, "{}"];

# Run tools from the application directory, like the CLI
chdir($root);

# Define env constants like the CLI entrypoints
Env::set([
    "app_root"      =>  getcwd(),
    "crazyphp_root" =>  getcwd()."/vendor/kzarshenas/crazyphp",
]);

# Use the same confirmation bypass as the CLI's --yes option
Runtime::$assumeYes = true;
$runtime = new Runtime();

# Decode the wizard answers
$options = json_decode($json, true, flags: JSON_THROW_ON_ERROR);

# Build the generator
$instance = match($step){
    "create"    => new Create(["application" => $options], $runtime),
    "docker"    => new Install($options, $runtime),
    default     => throw new InvalidArgumentException("Unknown step \"$step\"."),
};

# Execute the storyline step by step, as the CLI does
foreach($instance->getStoryline() as $action){

    # Log the step
    echo "[e2e] $step: $action".PHP_EOL;

    # Run the step
    $instance->{$action}();

}

# Log success
echo "[e2e] $step: done".PHP_EOL;
