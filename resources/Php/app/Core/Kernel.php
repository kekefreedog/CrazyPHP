<?php declare(strict_types=1);
/**
 * Kernel
 *
 * Classes wich allow you app to work
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace App\Core;

/**
 * Dependances
 */
use CrazyPHP\Model\Env;

/**
 * Kernel
 *
 * Allow to overwrite core methods or add custom
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class Kernel{

    /**
     * Constructor
     * 
     * @return self
     */
    public function __construct(){

        # Set default env
        Env::setDefault();

    }

}