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
namespace App\Core;

/**
 * Dependances
 */
use CrazyPHP\Library\Exception\ExceptionResponse;

/**
 * User Interface
 *
 * Interface for users
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
interface UserInterface {

    /** Public method | Get
     ******************************************************
     */

    /**
     * Get User Identifier
     * 
     * The public representation of the user (e.g. a username, an email address, etc.)
     *
     * @return string
     */
    public function getUserIdentifier():string;

    /**
     * Get Id
     * 
     * @return ?int
     */
    public function getId():?int;

    /**
     * Get Email
     * 
     * @return ?string
     */
    public function getEmail():?string;

    /**
     * Get Roles
     * 
     * Returns the roles granted to the user.
     *
     * @return array
     */
    public function getRoles():array;

    /**
     * Get Password
     * 
     * @return string
     */
    public function getPassword():string;

    /**
     * Set Lock
     * 
     * @return string
     */
    public function setLock():string;

    /** Public method | Set
     ******************************************************
     */

    /**
     * Set Email
     * 
     * @param string $email
     * @return self
     */
    public function setEmail(string $email):self;

    /**
     * Set Password
     * 
     * @param string $password
     * @return self
     */
    public function setPassword(string $password):self;

    /**
     * Set Roles
     * 
     * @param array $roles
     * @return self
     */
    public function setRoles(array $roles):self;

    /**
     * Get Lock
     * 
     * @return string
     */
    public function getLock():string;

}