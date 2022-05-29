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
 * @copyright  2022-2022 Kévin Zarshenas
 */
namespace  CrazyPHP\Interface;

/**
 * Dependances
 */
use Throwable;

/**
 * Exception Interface
 * 
 * @source https://www.php.net/manual/en/language.exceptions.php
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
interface Exception{

    /** Public methods | Protected methods inherited from Exception class
     ******************************************************
     */

    /**
     * Exception message
     */
    public function getMessage();

    /**
     * User-defined Exception code
     */
    public function getCode();

    /**
     * Source filename
     */
    public function getFile();

    /**
     * Source line
     */
    public function getLine();

    /**
     * An array of the backtrace()
     */
    public function getTrace();

    /**
     * Formated string of trace
     */
    public function getTraceAsString();
    
    /**
     * Constructor
     * 
     * @param string|null $message Message for Exception
     * @param int $code Code Http of the error
     * @param array|null $extra Extra information about exception
     *  - Ex : {
     *      old_value: "...",
     *      icon: [
     *          classe: "material-icons",
     *          text: "error"
     *      ],
     *      color: [
     *          text: "white",
     *          background: "red",
     *      ],
     *      options: [
     *         [...]
     *      ],
     *      redirection: "...",
     *  }
     * @param Throwable|null $previous Previous exception
     */
    public function __construct(
        string|null $message = null, 
        int $code = 0,
        array|null $extra = null,
        Throwable|null $previous = null
    );

    /** Public methods | Overrideable methods inherited from Exception class
     ******************************************************
     */

    /**
     * Formated string for display
     * 
     * @return string
     */
    public function __toString():string;  

}