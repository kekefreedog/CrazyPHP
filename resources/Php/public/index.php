<?php declare(strict_types=1);
/**
 * Page Index
 *
 * Index that redirect to the index of your app
 * 
 * !!! Don't touch this script please !!!
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */

# Dev mode
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

/** 
 * Register Index
 */
require_once __DIR__.'/../app/Index.php';
