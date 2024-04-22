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
use App\Core\CredentialInterface;
use App\Core\UserInterface;

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
interface AuthInterface {

    /**
     * Constructor
     * 
     * @param CredentialInterface $credentials
     * @param ?UserInterface $user (Null means you are registering a new user)
     * @param array $options
     * @param self
     */
    public function __construct(CredentialInterface $credentials, ?UserInterface $user = null, array $options = []);

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
    public function register():self;

    /**
     * Login 
     * 
     * Login existing user
     * 
     * @return self
     */
    public function login():self;

    /**
     * Logout 
     * 
     * Logout existing user
     * 
     * @return self
     */
    public function logout():self;

    /**
     * Logout 
     * 
     * Logout all user
     * 
     * @return self
     */
    public function logoutAll():self;
    
    /**
     * Activate 
     * 
     * Activate existing user
     * 
     * @return self
     */
    public function activate():self;

    /**
     * Delete 
     * 
     * Delete existing user
     * 
     * @return self
     */
    public function delete():self;

    /**
     * Change Email 
     * 
     * Change email of existing user
     * 
     * @return self
     */
    public function changeEmail();

    /**
     * Change Password 
     * 
     * Change password of existing user
     * 
     * @return self
     */
    public function changePassword();
    

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
    public function isUserConnected():bool;

    /**
     * Is User Valid
     * 
     * Check if user is valid
     * 
     * @return bool
     */
    public function isUserValid():bool;

    /**
     * Is User Locked
     * 
     * Check if user is locked
     * 
     * @return bool
     */
    public function isUserLocked():bool;

    /** Public method | Get
     ******************************************************
     */

    /**
     * Get UID
     * 
     * Get unique ID of the auth
     */
    public function getUID():int;

    /**
     * Get user
     * 
     * @return ?UserInterface
     */
    public function getUser():?UserInterface;

}