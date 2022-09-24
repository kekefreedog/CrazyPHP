<?php declare(strict_types=1);
/**
 * Index
 *
 * Index actions of your crazy application
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
namespace App;

/** 
 * Register The Auto Loader (composer)
 */
require __DIR__.'/../vendor/autoload.php';

/**
 * Dependances
 */
use CrazyPHP\Exception\CrazyException;
use App\Core\App;

/* Try */
try {

    /** 
     * Register Application
     */
    new App();

/* Catch errors */
} catch (CrazyException $e) {

    /* Display errors */
    echo 'Exception reçue : ',  $e->getMessage(), "\n";

}