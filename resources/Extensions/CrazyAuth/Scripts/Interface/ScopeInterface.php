<?php declare(strict_types=1);
/**
 * Crazy Auth Interface
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace CrazyAuth\Interface;

/**
 * Dependances
 */
use CrazyPHP\Library\Exception\ExceptionResponse;

/**
 * Scope Interface
 *
 * Interface for scope
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
interface ScopeInterface {

    /**
     * Constructor
     * 
     * @param array $options
     * @param self
     */
    public function __construct(array $options = []);

    /** Public method
     ******************************************************
     */

    /**
     * Get Name
     * 
     * Get name of the scope
     * 
     * @return string
     */
    public function getName():string;

    /**
     * Get Description
     * 
     * Get description of the scope
     * 
     * @return string
     */
    public function getDescription():string;

    /**
     * Get Id
     * 
     * Get Id of the scope
     * 
     * @return int
     */
    public function getId():int;

}