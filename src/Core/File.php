<?php declare(strict_types=1);
/**
 * Core
 *
 * Critical function of your crazy application
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
namespace  CrazyPHP\Core;

/**
 * Dependances
 */
use SplFileObject;

/**
 * File
 *
 * Methods for interacting with files
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class File extends SplFileObject {

    /**
     * Constructor
     * 
     * @see https://www.php.net/manual/en/class.splfileobject.php
     * 
     * @return self
     */
    public function __construct(string $filename){
        
        # Parent constructor
        parent::__construct($filename, 'r', false, null);

    }

}