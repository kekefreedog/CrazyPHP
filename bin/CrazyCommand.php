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

/** Dependances
 * 
 */
use CrazyPHP\Cli\Core;

// Execute core
(new Core())->run();