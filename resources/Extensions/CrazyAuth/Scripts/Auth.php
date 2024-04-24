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
use CrazyAuth\Interface\AuthInterface;

/**
 * Auth Interface
 *
 * Interface for authentification
 * 
 * @source https://github.com/PHPAuth/PHPAuth/blob/master/sources/AuthInterface.php
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class Auth implements AuthInterface {

    /**
     * Constructor
     * 
     * @param CredentialInterface $credentials
     * @param ?UserInterface $user (Null means you are registering a new user)
     * @param array $options
     * @param self
     */
    public function __construct(Credential $credentials, ?User $user = null, array $options = []) {



    }

    /** Public method | Action
     ******************************************************
     */

    /**
     * Register 
     * 
     * Register new user
     * 
     * @return self
     */
    public function register():self {

        # Return self
        return $this;

    }

    /**
     * Login 
     * 
     * Login existing user
     * 
     * @return self
     */
    public function login():self {

        # Return self
        return $this;

    }

    /**
     * Logout 
     * 
     * Logout existing user
     * 
     * @return self
     */
    public function logout():self {

        # Return self
        return $this;

    }

    /**
     * Logout 
     * 
     * Logout all user
     * 
     * @return self
     */
    public function logoutAll():self {

        # Return self
        return $this;

    }
    
    /**
     * Activate 
     * 
     * Activate existing user
     * 
     * @return self
     */
    public function activate():self {

        # Return self
        return $this;

    }

    /**
     * Delete 
     * 
     * Delete existing user
     * 
     * @return self
     */
    public function delete():self {

        # Return self
        return $this;

    }

    /**
     * Change Email 
     * 
     * Change email of existing user
     * 
     * @return self
     */
    public function changeEmail() {

        # Return self
        return $this;

    }

    /**
     * Change Password 
     * 
     * Change password of existing user
     * 
     * @return self
     */
    public function changePassword() {

        # Return self
        return $this;

    }
    

    /** Public method | Validator
     ******************************************************
     */

    /**
     * Is User Connected
     * 
     * Check if user is connected
     * 
     * @return bool
     */
    public function isUserConnected():bool {

        # Set result
        $result = false;

        # Return result
        return $result;

    }

    /**
     * Is User Valid
     * 
     * Check if user is valid
     * 
     * @return bool
     */
    public function isUserValid():bool {

        # Set result
        $result = false;

        # Return result
        return $result;

    }

    /**
     * Is User Locked
     * 
     * Check if user is locked
     * 
     * @return bool
     */
    public function isUserLocked():bool {

        # Set result
        $result = false;

        # Return result
        return $result;

    }

    /** Public method | Get
     ******************************************************
     */

    /**
     * Get UID
     * 
     * Get unique ID of the auth
     */
    public function getUID():int {

        # Set result
        $result = 0;

        # Return result
        return $result;

    }

    /**
     * Get user
     * 
     * @return ?UserInterface
     */
    public function getUser():?User {

        # Set result
        $result = new User();

        # Return result
        return $result;

    }

}