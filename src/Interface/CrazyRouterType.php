<?php declare(strict_types=1);
/**
 * Interface
 *
 * Interface of CrazyPHP
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace  CrazyPHP\Interface;

/**
 * Dependances
 */

/**
 * Crazy Router Type Interface
 * 
 * Interface for define compatible your controller with the api2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
interface CrazyRouterType {
    /**
     * Search Reg Exp
     * 
     * Method to return a regular expression for searching this entity in the URL.
     *
     * @return string Regular expression for searching.
     */
    public static function searchRegExp():string;

    /**
     * Parser Reg Exp
     * 
     * Method to return a regular expression to parse the entity if it occurs in the URL.
     *
     * @return string Regular expression for parsing.
     */
    public static function parserRegExp():string;

}