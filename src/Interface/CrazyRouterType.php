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
namespace CrazyPHP\Interface;

/**
 * Dependances
 */

/**
 * Crazy Router Type Interface
 * 
 * Define a custom [type:name] placeholder shared by router adapters.
 * Expressions are regex fragments without delimiters.
 * The parser expression must not add capturing groups.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
interface CrazyRouterType {

    /** @var string PARAMETER_NAME_REGEX Supported placeholder names in application routes */
    public const PARAMETER_NAME_REGEX = '[a-zA-Z0-9_\-]*';

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
