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
 * @copyright  2022-2023 Kévin Zarshenas
 */
namespace  CrazyPHP\Library\System;

/** 
 * Dependances
 */
use CrazyPHP\Exception\CrazyException;

/**
 * Form
 *
 * Methods for show info about current os
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */
class Os {

    /** Public Static Methods
     ******************************************************
     */

    /**
     * Is Windows
     * 
     * @source https://stackoverflow.com/questions/15845928/determine-if-operating-system-is-mac
     * 
     * Check if current os is windows
     * 
     * @return bool
     */
    public static function isWindows():bool {

        # Declare result
        $result = false;

        # Get user agent
        $user_agent = getenv("HTTP_USER_AGENT");

        # Get win dir
        $win_dir = getenv("WINDIR");

        # Check user agent and win dir
        if(!$win_dir && !$user_agent)

            # Return result
            return $result;

        # Set result
        if(strpos($user_agent, "Win") !== false) 
            
            # Set result
            $result = true;

        # Set result
        if(!$result && $win_dir) $result = true;

        # Return result
        return $result;

    }

    /**
     * Is Mac
     * 
     * @source https://stackoverflow.com/questions/15845928/determine-if-operating-system-is-mac
     * 
     * Check if current os is windows
     * 
     * @return bool
     */
    public static function isMac():bool {

        # Declare result
        $result = false;

        # Get user agent
        $user_agent = getenv("HTTP_USER_AGENT");
        $vs = getenv("VSCODE_GIT_ASKPASS_NODE");

        # Get win dir
        if(
            (
                is_string($user_agent) &&
                preg_match('/macintosh|mac os x/i', $user_agent)
            ) || (
                is_string($vs) &&
                strpos($vs, "MacOS")!==false
            ) 
        ){

            # Set result
            $result = true;

        }

        # Return result
        return $result;

    }

}