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
namespace App\Library\File;

/** Dependances
 * 
 */
use League\Flysystem\Ftp\FtpConnectionOptions;
use League\Flysystem\Sftp\SftpAdapter;
use League\Flysystem\Ftp\FtpAdapter;
use League\Flysystem\Filesystem;

/**
 * Ftp
 *
 * Methods for manipulate FTP
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class Ftp {

    /** Parameters
     ******************************************************
     */

    /** @var array $_options */
    private array $_options = [
        
    ];

    /** @var array $_driver */
    private SftpAdapter|FtpAdapter $_driver;

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
        int $port = 22,
        ?string $rootPath = null,
    ){

        # Ingest options
        $this->_ingestOptions([
            "host"      =>  $host,
            "username"  =>  $username,
            "password"  =>  $password,
            "isFTPs"    =>  $isFTPs,
            "port"      =>  $port,
            "rootPath"  =>  $rootPath
        ]);

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
    private function _ingestOptions(array $arguments = []):void {

        # Push arguments into options
        $this->_options += $arguments;

    }

    /**
     * Ingest Options
     * 
     * @return void
     */
    private function _loadDriver():void {

        # Check secure or not
        if($this->_options["isFTPs"]){        

            // Define FTP connection options using `FtpConnectionOptions`
            $options = FtpConnectionOptions::fromArray([
                'host'     =>   $this->_options["host"],
                'username' =>   $this->_options["username"],
                'password' =>   $this->_options["password"],
                'root'     =>   $this->_options["rootPath"],
                'passive'  =>   true,
                'ssl'      =>   false,
                'timeout'  =>   30,
            ]);  

            # Prepare adapteur
            $adapter = new FtpAdapter($options);  

        }else{

            // Define FTP connection options using `FtpConnectionOptions`
            $options = FtpConnectionOptions::fromArray([
                'host'     =>   $this->_options["host"],
                'username' =>   $this->_options["username"],
                'password' =>   $this->_options["password"],
                'root'     =>   $this->_options["rootPath"],
                'passive'  =>   true,
                'ssl'      =>   false,
                'timeout'  =>   30,
            ]);  

            # Prepare adapteur
            $adapter = new FtpAdapter($options);

        }

        # Set driver
        # $this->_driver = 

    }


}