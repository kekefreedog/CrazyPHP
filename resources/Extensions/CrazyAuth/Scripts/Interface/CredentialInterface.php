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
use App\Core\UserInterface;

/**
 * Credential Interface
 *
 * Interface for credential
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
interface CredentialInterface {

    /**
     * Constructor
     * 
     * @param array $form
     * @param array $options
     * @param self
     */
    public function __construct(array $form, array $options = []);

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
    public function isEmailTaken():bool;

}