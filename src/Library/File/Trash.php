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
 * @copyright  2022-2023 Kévin Zarshenas
 */
namespace CrazyPHP\Library\File;

/** Dependances
 * 
 */
use CrazyPHP\Library\Time\DateTime;
use CrazyPHP\Library\Form\Process;
use CrazyPHP\Library\File\Json;
use CrazyPHP\Library\File\File;
use CrazyPHP\Model\Env;

/**
 * Trash
 *
 * Methods for manipulate trash
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */
class Trash {

    /** Public constants
     ******************************************************
     */

    /**
     * Trash Path
     * Env "trash_path"
     * Path of the trash
     * @const string TRASH_PATH
     */
    public const TRASH_PATH = "@app_root/.trash/";
    
    /**
     * Trash Disable
     * Env "trash_disable"
     * Disable the trash methods
     * @const bool TRASH_DISABLE
     */
    public const TRASH_DISABLE = false;

    /** Public static method
     ******************************************************
     */

    /**
     * Send
     * 
     * Send file to trash
     * 
     * @param string $target File to send to trash
     * @param string $hierarchy Subfolder in the trash where is sent the file
     * @param bool $moveToTrash After copy in trash, it deletes the original file given
     * @return string|bool Return the path of the file
     */
    public static function send(string $target = "", string $hierarchy = "", bool $moveToTrash = true):string|bool {

        # Set result
        $result = false;

        # Check if file exists
        if(File::exists($target)){

            # Process target
            $target = File::path($target);

            # Check if trash is disable
            if(!static::isDisable()){

                # Get trash path
                $trashPath = static::getPath();

                # Set trash target
                $trashTarget = 
                    rtrim($trashPath, "/")."/".
                    ($hierarchy ? trim($hierarchy, "/")."/" : "").
                    pathinfo($target, PATHINFO_BASENAME)."_".static::_getCurrentDate()
                ;

                # Copy file
                $result = File::copy(
                    $target, 
                    $trashTarget
                );

                # Check trash
                if($result !== false)

                    # Set result
                    $result = $trashTarget;

            }

            # Check move to trash
            if($moveToTrash)

                # Delete index
                File::remove($target);

        }

        # Return result
        return $result;

    }

    /**
     * Send an object to trash
     * 
     * Send object as json in trash
     * 
     * @param array $data Object to send to trash
     * @param ?string $name Name of the object
     * @param string $hierarchy Subfolder in the trash where is sent the file
     * @return void
     */
    public static function sendAnObject(array $data, ?string $name = null, string $hierarchy = ""):void {

        # Check is trash is not disable and check if object valid
        if(!static::isDisable() && !empty($data)){

            # Get trash path
            $trashPath = static::getPath();

            # Copy file
            File::create(
                rtrim($trashPath, "/")."/".
                ($hierarchy ? trim($hierarchy, "/")."/" : "").
                ($name ? "$name." : "" )."json_".static::_getCurrentDate(),
                Json::encode($data, true)
            );

        }

    }

    /**
     * Clear
     * 
     * Clear trash
     * 
     * @return void
     */
    public static function clear():void {

        # Check is trash is not disable
        if(!static::isDisable()){

            # Get trash path
            $trashPath = static::getPath();

            # Remove all file
            File::removeAll($trashPath);

            # Recreate the trash path
            File::createDirectory($trashPath);

        }

    }

    /**
     * Is Disable
     * 
     * Check if the trash is disable
     * > Env : "trash_disable"
     * 
     * @return bool
     */
    public static function isDisable():bool {

        # Set result
        $result = Process::bool(Env::get("trash_disable", true) ?: static::TRASH_DISABLE);

        # Return result
        return $result;

    }

    /**
     * Get Path
     * 
     * Get path of the trash
     * > Env : "trash_path"
     * 
     * @param bool $processPath Process path to replace env...
     * @return string
     */
    public static function getPath(bool $processPath = false):string {

        # Set result
        $result = Env::get("trash_path", true) ?: static::TRASH_PATH;

        # Check process path
        if($processPath)

            # Process path
            $result = File::path($result);

        # Return result
        return $result;

    }

    /**
     * Is Empty
     * 
     * Check if the trash is empty
     * 
     * @return bool
     */
    public static function isEmpty():bool {

        # Set result
        $result = File::isEmpty(static::getPath());

        # Return result
        return $result;

    }


    /** Private static method
     ******************************************************
     */

    /**
     * Get current date
     * 
     * Get date to put on file sent to trash
     * 
     * @return string
     */
    private static function _getCurrentDate():string {

        # Set result
        $result = (new DateTime())->format('Y-m-d_H-i-s_v');

        # Return result
        return $result;

    }

}