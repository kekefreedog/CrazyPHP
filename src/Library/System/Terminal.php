<?php declare(strict_types=1);
/**
 * System
 *
 * Usefull class for manipulate system
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace  CrazyPHP\Library\System;

/** 
 * Dependances
 */
use CrazyPHP\Exception\CrazyException;

/**
 * Terminal
 *
 * Methods for check terminal
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class Terminal {

    /** Public Static Methods | Windows
     ******************************************************
     */

    /**
     * Is Windows Power Shell
     * 
     * Checks if the script is running in Windows PowerShell.
     * 
     * @return bool Returns true if the script is running in PowerShell, false otherwise.
     */
    public static function isWindowsPowerShell():bool {

        # Check for a common PowerShell environment variable
        return getenv('PSModulePath') !== false;

    }
    
    /**
     * Is Windows Command Prompt
     * 
     * Checks if the script is running in Windows Command Prompt.
     * 
     * @return bool Returns true if the script is running in Command Prompt, false otherwise.
     */
    public static function isWindowsCommandPrompt() {

        # Check for the ComSpec environment variable typical in CMD that includes 'cmd.exe'
        $comSpec = getenv('ComSpec');

        # Return check
        return $comSpec !== false && strpos(strtolower($comSpec), 'cmd.exe') !== false;
    }

}