<?php declare(strict_types=1);
/**
 * Media
 *
 * Critical function about media for your crazy application
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace CrazyPHP\Core\Media;

/**
 * Dependances
 */

use CrazyPHP\Core\Media;

/**
 * Favicon
 *
 * Class for manage favicon...
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class Favicon extends Media {

    /** Private parameters
     ******************************************************
     */

    /** @var string $name Name of the favicon */
    private string $name = "";

    /**
     * Constructor
     * 
     * @return self
     */
    public function __construct(string $parameter = ""){

        # Parent constructure
        parent::__construct();

        # Set context
        $this->appendContext(__CLASS__);

        # Get Favicon parameter
        $result = $this->get(self::PREFIX.$parameter);

    }

    /** Public constants
     ******************************************************
     */

    /** @cont string PARAMETER_NAME */
    public const PARAMETER_NAME = "FAVICON";

    /** @cont string PREFIX */
    public const PREFIX = "Media.Favicon.";

}