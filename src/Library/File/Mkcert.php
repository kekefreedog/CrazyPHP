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

/**
 * Dependances
 */
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\Array\Arrays;
use CrazyPHP\Library\Cli\Command;
use CrazyPHP\Library\File\File;

/**
 * Mkcert
 *
 * Methods for interacting with Mkcert files
 *
 * @source     https://github.com/FiloSottile/mkcert
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class Mkcert {

    /**
     * Is Installed
     * 
     * Check mkcert is well installed
     * 
     * @return bool
     */
    public static function isInstalled():bool {

        # Command to check mkcert version
        $command = 'mkcert -version';

        # Execute command
        $result = Command::exec($command, '');

        # Check if the command executed successfully and the output contains expected content
        if($result["result_code"] === 0 && !empty($result["output"]))
            
            # Mkcert is installed
            return true;
        
        # Mkcert is not installed
        return false;

    }

    /**
     * Run
     * 
     * run creation of certificate
     * 
     * @return void
     */
    public static function run(string $target = "@app_root/docker/mkcert"):array {

        # Set result
        $result = [
            "target"    =>  $target,
            "hosts"     =>  ["localhost", "127.0.0.1"]
        ];

        # Create folder
        if(!File::createDirectory($target))

            # New error
            throw new CrazyException(
                "Folder creation of `$target` failed. Can you create it manually with good permission", 
                500,
                [
                    "custom_code"   =>  "mkcert-001",
                ]
            );

        # Install
        Command::exec("mkcert", "-install", true);

        # Get any server name
        $result["hosts"][] = $serverName = Config::getValue("App.server.name");

        # Certificate
        Command::exec("mkcert", "-key-file ./docker/mkcert/localhost-key.pem -cert-file ./docker/mkcert/localhost.pem ".($serverName ? "$serverName " : "")."localhost 127.0.0.1 ::1", true);

        # Return result
        return $result;

    }

    /**
     * Run
     * 
     * run creation of certificate
     * 
     * @return void
     */
    public static function remove():void {

        # Install
        Command::exec("mkcert", "-uninstall", true);

    }

}