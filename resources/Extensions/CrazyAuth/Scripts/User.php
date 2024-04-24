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
use CrazyAuth\Interface\UserInterface;
use CrazyAuth\Scope;

/**
 * Scope Interface
 *
 * Interface for scope
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class User implements UserInterface {

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
    public function getUserIdentifier():string {

        # Set result
        $result = "";

        # Return result
        return $result;

    }

    /**
     * Get Id
     * 
     * @return ?int
     */
    public function getId():?int {

        # Set result
        $result = 0;

        # Return result
        return $result;

    }

    /**
     * Get Email
     * 
     * @return ?string
     */
    public function getEmail():?string {

        # Set result
        $result = "";

        # Return result
        return $result;

    }

    /**
     * Get Roles
     * 
     * Returns the roles granted to the user.
     *
     * @return array
     */
    public function getRoles():array {

        # Set result
        $result = [];

        # Return result
        return $result;

    }

    /**
     * Get Password
     * 
     * @return string
     */
    public function getPassword():string {

        # Set result
        $result = "";

        # Return result
        return $result;

    }

    /**
     * Get Lock
     * 
     * @return bool
     */
    public function getLock():bool {

        # Set result
        $result = false;

        # Return result
        return $result;

    }

    /**
     * Get Scopes
     * 
     * @return array
     */
    public function getScopes():array {

        # Set result
        $result = [];

        # Return result
        return $result;

    }

    /**
     * Get Scope By Name
     * 
     * @return ?Scope
     */
    public function getScopeByName():?Scope {

        # Set result
        $result = new Scope();

        # Return result
        return $result;

    }

    /**
     * Get Scope By Id
     * 
     * @return ?Scope
     */
    public function getScopeById():?Scope {

        # Set result
        $result = new Scope();

        # Return result
        return $result;

    }

    /** Public method |Has
     ******************************************************
     */

    /**
     * Has Scope
     * 
     * @param int|string
     * @return bool
     */
    public function hasScope(int|string $input):bool {

        # Set result
        $result = false;

        # Return result
        return $result;

    }

    /**
     * Has Scopes
     * 
     * @param array
     * @return bool
     */
    public function hasScopes(array $inputs):bool {

        # Set result
        $result = false;

        # Return result
        return $result;

    }

    /** Public method | Set
     ******************************************************
     */

    /**
     * Set Email
     * 
     * @param string $email
     * @return self
     */
    public function setEmail(string $email):self {

        # Return self
        return $this;

    }

    /**
     * Set Password
     * 
     * @param string $password
     * @return self
     */
    public function setPassword(string $password):self {

        # Return self
        return $this;

    }

    /**
     * Set Roles
     * 
     * @param array $roles
     * @return self
     */
    public function setRoles(array $roles):self {

        # Return self
        return $this;

    }

    /**
     * Set Lock
     * 
     * @return string
     */
    public function setLock(bool $lock = true):self {

        # Return self
        return $this;

    }

    /**
     * Set Scope
     * 
     * @param string $input
     * @param array $options
     * @return self
     */
    public function setScope(string $input, array $options = []):self {

        # Return self
        return $this;

    }

    /**
     * Set Scopes
     * 
     * @param array $inputs
     * @param array $options
     * @return array
     */
    public function setScopes(array $inputs):self {

        # Return self
        return $this;

    }

}