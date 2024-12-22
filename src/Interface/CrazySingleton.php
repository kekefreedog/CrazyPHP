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
 * Crazy Singleton Interface
 * 
 * Interface for define compatible your singleton
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
interface CrazySingleton {

    /** Public Static Parameters
     ******************************************************
     */

    /** @var mixed instance */
    # private static array $_instances;

    /** Public Static Methods
     ******************************************************
     */

    /**
     * Get Instance
     * 
     * Singleton method
     * 
     * @param array $option
     * @return mixed
     */
    public static function getInstance(array $options = []):mixed;

    /**
     * Connect
     * 
     * Establish connection
     * 
     * @param array $option
     * @return mixed
     */
    public static function connect(array $options = []):void;

    /**
     * Disconnect
     * 
     * isconnect method
     *
     * @param array $option
     * @return mixed
     */
    public static function disconnect(array $options = []):void;

}