<?php declare(strict_types=1);
/**
 * Json
 *
 * TDB
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
namespace  CrazyPHP\Exception;

/**
 * Dependances
 */
use CrazyPHP\Interface\Exception as InterfaceException;
use Throwable;
use Exception;

/**
 * Json
 *
 * Methods for interacting with Composer files
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class CrazyException extends Exception implements InterfaceException{

    /** Variables
     ******************************************************
     */

    # Exception message
    protected $message = 'Unknown exception';

    # User-defined exception code                       
    protected $code = 0;

    # Source of the error LuckyPHP or App or Vendor
    public $source = null;

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
        array $extra = [],
        Throwable|null $previous = null
    ){

        // Check message
        if (!$message)
            throw new $this('Unknown '.get_class($this));

        // Construct parent
        parent::__construct($message, $code, $previous);

        // Set source
        // $this->setSource();

        // Right in log file
        // $this->logWrite();

    }

    /** Public methods
     ******************************************************
     */

    /** 
     * Get Source
     * 
     * @return string
     */
    public function getSource():string {

        # Get source
        $result = $this->source;

        # Return result
        return $result;

    }

    /** 
     * Display message as error message in javascript console
     * 
     * @return void
     */
    public function consoleError():void{

        # Put error in console
        // Console::error($this->__toString());

    }

    /** Public methods | Protected methods inherited from Exception class
     ******************************************************
     */

    /****************************************************************
     * Methods
     */

    /** Public methods | Overrideable methods inherited from Exception class
     ******************************************************
     */

}