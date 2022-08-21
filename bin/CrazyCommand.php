#!/usr/bin/php 
<?php 
/**
 * Crazy Command
 *
 * Command for controle application
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
require __DIR__ . '/../vendor/autoload.php';

/**
 * Dependances
 */
use CrazyPHP\Model\Env;
use CrazyPHP\Cli\Core;

# Define env constants
Env::set([
    "app_root"      =>  getcwd(),
    "crazyphp_root" =>  getcwd()."/vendor/kzarshenas/crazyphp",
]);

// Execute core
(new Core())->run();