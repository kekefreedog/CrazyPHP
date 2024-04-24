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
namespace CrazyAuth;

/**
 * Dependances
 */

use CrazyPHP\Library\Exception\ExceptionResponse;
use CrazyAuth\Interface\ScopeInterface;

/**
 * Scope Interface
 *
 * Interface for scope
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class Scope implements ScopeInterface {

    /** Private Parameters
     ******************************************************
     */

    /** @var array $date Data from database */
    private $_data = [];

    /**
     * Constructor
     * 
     * @param array $options
     * @param self
     */
    public function __construct(array $options = []){

        // TBC

    }

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
    public function getName():string {

        # Set result
        $result = $this->_data["name"];

        # Return result
        return $result;

    }

    /**
     * Get Description
     * 
     * Get description of the scope
     * 
     * @return string
     */
    public function getDescription():string {

        # Set result
        $result = $this->_data["description"];

        # Return result
        return $result;

    }

    /**
     * Get Id
     * 
     * Get Id of the scope
     * 
     * @return int
     */
    public function getId():int {

        # Set result
        $result = $this->_data["id"];

        # Return result
        return $result;

    }

}