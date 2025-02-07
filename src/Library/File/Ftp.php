<?php declare(strict_types=1);
/**
 * File
 *
 * Classe for manipulate specific files
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace CrazyPHP\Library\File;

/** Dependances
 * 
 */

use CrazyPHP\Library\Form\Process;
use phpseclib3\Net\SFTP as DriverSFTP;
use phpseclib3\Net\FTP as DriverFTP;

/**
 * Ftp
 *
 * Methods for manipulate FTP
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class Ftp{

    /** Parameters
     ******************************************************
     */

    /** @var array $_options */
    private array $_options = [];

    /** @var array $_driver */
    private DriverFt|DriverSFTP $_driver = [];

    /**
     * Constructor
     * 
     * New FTP Connection
     * 
     * @param string $host
     * @param string $host
     */
    public function __construct(
        string $host,
        ?string $username = null,
        ?string $password = null,
        bool $isFTPs = false,
        ?string $rootPath = null,
    ){

        # Ingest options
        $this->_ingestOptions();

        # Load driver
        $this->_loadDriver();

        # Set root
        $this->setRoot();

    }

    /** Public methods
     ******************************************************
     */

    /** Private methods
     ******************************************************
     */

    /**
     * Ingest Options
     * 
     * @return void
     */
    private function _ingestOptions(...$arguments):void {

        # Push arguments into options
        $this->_options += $arguments;

    }

    /**
     * Ingest Options
     * 
     * @return void
     */
    private function _loadDriver(...$arguments):void {

        # Get is ftp
        if(Process::bool($this->_options["isFTPs"] ?? false))

            # Set driver
            $this->_driver = new DriverSFTP();

        # Not secure 
        else

            # Set driver
            $this->_driver = new DriverFTP();





    }


}