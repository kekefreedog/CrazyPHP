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
     *      custom_code: "form-001"
     *      old_value: "...",
     *      icon: [
     *          classe: "material-icons",
     *          text: "error"
     *      ],
     *      color: [
     *          text: "white",
     *          background: "red",
     *      ],
     *      actions: [
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

    }

}