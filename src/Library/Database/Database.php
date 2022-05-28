<?php declare(strict_types=1);
/**
 * Database
 *
 * Manipulate databases
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
namespace CrazyPHP\Database;

/**
 * Dependances
 */
use Medoo\Medoo;

/**
 * Database
 *
 * Core of manipulation of your database
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class Database extends Medoo{

    /**
     * Constructor
     * 
     * @param array $options Options for create database
     * @return Medoo
     */
    public function __construct(array $options = []){

        # Super
        return parent::__construct($options);

    }

}