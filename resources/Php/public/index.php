<?php declare(strict_types=1);
/**
 * Page Index
 *
 * Index page of your crazy application
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */

/** Register The Auto Loader (composer)
 * 
 */
require __DIR__.'/../vendor/autoload.php';

/**
 * Dependances
 */
use CrazyPHP\Exception\CrazyException;

/** Register Application
 * 
 */
require_once __DIR__.'/../app/Core/App.php';