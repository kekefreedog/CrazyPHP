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
     * Get Lock
     * 
     * @return bool
     */
    public function getLock():bool;

    /**
     * Get Scopes
     * 
     * @return array
     */
    public function getScopes():array;

    /**
     * Get Scope By Name
     * 
     * @return ?ScopeInterface
     */
    public function getScopeByName():?ScopeInterface;

    /**
     * Get Scope By Id
     * 
     * @return ?ScopeInterface
     */
    public function getScopeById():?ScopeInterface;

    /** Public method |Has
     ******************************************************
     */

    /**
     * Has Scope
     * 
     * @param int|string
     * @return bool
     */
    public function hasScope(int|string $input):bool;

    /**
     * Has Scopes
     * 
     * @param array
     * @return bool
     */
    public function hasScopes(array $inputs):bool;

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
     * Set Lock
     * 
     * @param bool $lock
     * @return string
     */
    public function setLock(bool $lock = true):self;

    /**
     * Set Scope
     * 
     * @param string $input
     * @param array $options
     * @return self
     */
    public function setScope(string $input, array $options = []):self;

    /**
     * Set Scopes
     * 
     * @param array $inputs
     * @param array $options
     * @return array
     */
    public function setScopes(array $inputs):self;

}