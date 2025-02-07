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
namespace CrazyPHP\Library\System;

/** Dependances
 * 
 */
use CrazyPHP\Exception\CrazyException;
use Monolog\Logger as LoggerInstance;
use CrazyPHP\Library\Array\Arrays;
use CrazyPHP\Library\File\File;
use Monolog\Level;
use Stringable;

/**
 * Ftp
 *
 * Methods for manipulate FTP
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class Logger {

    /** Parameters
     ******************************************************
     */

    /** @var array $options */
    private array $_options = [
        "type"      =>  "back", # Front / Worker...
        "handlers"  =>  [
            "RotatingFileHandler"   =>  [
                "filename"  =>  null,
                "maxFiles"  =>  14,
                "level"     =>  Level::Debug,
                "formatters" =>  [
                    "LineFormatter" =>  []
                ]
            ]
        ],
    ];

    /** @var string $instance */
    private ?LoggerInstance $_instance = null;

    /**
     * Constructor
     * 
     * New FTP Connection
     * 
     * @param string $host
     * @param string $host
     */
    public function __construct($name = "", $options = []){

        # Ingest options
        $this->_ingestOptions($name, $options);

        # New instance
        $this->_newInstance();

        # Push handlers
        $this->_pushHandlers();

        # Push formatter
        $this->_pushFormatters();

    }

    /** Public parameters
     ******************************************************
     */

    /**
     * Debug
     * 
     * @param string|Stringable $message
     * @param array $context
     * @return void
     */
    public function debug(string|Stringable $message, array $context = []):void {

        # Set debug
        $message && $this->_instance->debug($message, $context);

    }

    /**
     * Info
     * 
     * @param string|Stringable $message
     * @param array $context
     * @return void
     */
    public function info(string|Stringable $message, array $context = []):void {

        # Set info
        $message && $this->_instance->info($message, $context);

    }

    /**
     * Notice
     * 
     * @param string|Stringable $message
     * @param array $context
     * @return void
     */
    public function notice(string|Stringable $message, array $context = []):void {

        # Set notice
        $message && $this->_instance->notice($message, $context);

    }

    /**
     * Warning
     * 
     * @param string|Stringable $message
     * @param array $context
     * @return void
     */
    public function warning(string|Stringable $message, array $context = []):void {

        # Set warning
        $message && $this->_instance->warning($message, $context);

    }

    /**
     * Error
     * 
     * @param string|Stringable $message
     * @param array $context
     * @return void
     */
    public function error(string|Stringable $message, array $context = []):void {

        # Set error
        $message && $this->_instance->error($message, $context);

    }

    /**
     * Critical
     * 
     * @param string|Stringable $message
     * @param array $context
     * @return void
     */
    public function critical(string|Stringable $message, array $context = []):void {

        # Set critical
        $message && $this->_instance->critical($message, $context);

    }

    /**
     * Alert
     * 
     * @param string|Stringable $message
     * @param array $context
     * @return void
     */
    public function alert(string|Stringable $message, array $context = []):void {

        # Set alert
        $message && $this->_instance->alert($message, $context);

    }

    /**
     * Emergency
     * 
     * @param string|Stringable $message
     * @param array $context
     * @return void
     */
    public function emergency(string|Stringable $message, array $context = []):void {

        # Set alert
        $message && $this->_instance->emergency($message, $context);

    }

    /** Private parameters
     ******************************************************
     */

    /**
     * Ingest Options
     * 
     * @return void
     */
    private function _ingestOptions(string $name, array $options):void {

        # Check name
        if(!$name) throw new CrazyException(
            "Logger name is empty and not valid", 
            500,
            ["custom_code" => "logger-001"]
        );

        # Ingest options
        $this->_options = Arrays::mergeMultidimensionalArrays(true, $this->_options, $options, ["name" => $name]);

    }

    /**
     * New Instance
     * 
     * @return void
     */
    private function _newInstance():void {

        # Set instance
        $this->_instance = new LoggerInstance($this->_options['name']);

    }

    /**
     * Push Handlers
     * 
     * @return void
     */
    private function _pushHandlers():void {

        # Check instance
        if($this->_instance){

            # Get handlers
            $handlers = $this->_options["handlers"] ?? [];

            # Iteration handlers
            if(!empty($handlers)) foreach($handlers as $name => $handler){

                # Chech filename
                if(array_key_exists("filename", $handler) && $handler['filename'] == null) 
                
                    # Set filename if empty
                    $handler['filename'] = File::path(
                        static::ROOT."/".
                        ($this->_options["type"] ?? false
                            ? $this->_options["type"]."/"
                            : ""
                        ).
                        $this->_options["name"].".log"
                    );

                if(isset($handler["formatters"])){

                    # Get formatters
                    $formatters = $handler["formatters"];

                    # Remove formatters
                    unset($handler["formatters"]);

                }

                # Set handlerName
                $handlerName = "\\Monolog\\Handler\\$name";

                # check method exists
                if($handlerName && class_exists($handlerName)){

                    # Set handler
                    $handlerInstance = new $handlerName(...$handler);

                    # Check formatters
                    if(isset($formatters)){

                        # Iteration handlers
                        if(!empty($formatters)) foreach($formatters as $nameF => $formatter){
            
                            # Set handlerName
                            $formatterName = "\\Monolog\\Formatter\\$nameF";
            
                            # check method exists
                            if($formatterName && class_exists($formatterName)){

                                # Set formatter instance
                                $formatterInstance = new $formatterName(...$formatter);
            
                                # Push handler
                                $handlerInstance->setFormatter($formatterInstance);
            
                            }
            
                        }

                    }

                    # Push handler
                    $this->_instance->pushHandler($handlerInstance);

                }

            }
        
        }

    }

    /**
     * Push Formatters
     * 
     * @return void
     */
    private function _pushFormatters():void {

        # Check instance
        if($this->_instance){
        
        }

    }

    /** Public constant
     ******************************************************
     */

    /** @param string root */
    public const ROOT = "@app_root/logs";

    /** @param array STREAMHANDLER_TEMPLATE */
    public const STREAMHANDLER_TEMPLATE = [
        'php://stdout',
        Level::Debug,
        "formatters"    =>  [
            "LineFormatter" =>  [
                "format"    =>  "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n"
            ]
        ]
    ];

}