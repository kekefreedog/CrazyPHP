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
 * Disable deprecated message
 */
$errorlevel=error_reporting();
$errorlevel=error_reporting($errorlevel & ~E_DEPRECATED);

/**
 * Dependances
 */
use CrazyPHP\Cli\Core;

// Execute core
(new Core())->run();