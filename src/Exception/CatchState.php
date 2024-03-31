<?php declare(strict_types=1);
/**
 * Exception
 *
 * Exeption class for manipulate errors
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace  CrazyPHP\Exception;

/**
 * Dependances
 */
use CrazyPHP\Interface\Exception as InterfaceException;
use Exception;
use Throwable;

/**
 * Catch State
 *
 * Methods for catch state
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class CatchState extends Exception implements InterfaceException{

    /** Variables
     ******************************************************
     */

    # Exception message
    protected $message = '';

    # User-defined exception code                       
    protected $code = 0;

    # Source of the error LuckyPHP or App or Vendor
    public $source = null;

    # State
    public $state = [];

    /**
     * Constructor
     * 
     * @param array|null $extra State array
     */
    public function __construct(?string $message = "Catch State", int $code = 0, ?array $extra = null, ?Throwable $previous = null){

        // Fill state
        $this->state = is_array($extra) ? $extra : [];

        // Construct parent
        parent::__construct($message, $code, $previous);

    }

    /** Public methods
     ******************************************************
     */

    /**
     * Get State
     * 
     * @return array
     */
    public function getState():array {

        # Set result
        $result = $this->state;

        # Return result
        return $result;

    }

}