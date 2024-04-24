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
use CrazyAuth\Interface\CredentialInterface;

/**
 * Credential
 *
 * Methods for manipulate credential
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class Credential implements CredentialInterface {

    /**
     * Constructor
     * 
     * @param array $form
     * @param array $options
     * @param self
     */
    public function __construct(array $form, array $options = []) {



    }

    /** Public method | Validator
     ******************************************************
     */

    /**
     * Is Email Taken
     * 
     * Check if email is already given
     * 
     * @return bool
     */
    public function isEmailTaken():bool {

        # Set result
        $result = false;

        # Return result
        return $result;

    }

}