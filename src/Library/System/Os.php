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
 * Form
 *
 * Methods for show info about current os
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
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

    /**
     * Get OS
     * 
     * Get OS
     * 
     * @return string
     */
    public static function getOs():string {

        # Set result
        $result = "linux";

        # Check if windows
        if(static::isWindows())

            # Set result
            $result = "windows";

        # Check if mac
        if(static::isMac())

            # Set result
            $result = "mac";

        # Return result
        return $result;

    }


    /**
     * Get Host Path
     * 
     * Get path of the host depending of the os
     * 
     * @return string
     */
    public static function getHostPath():string {

        # Set result
        $result = static::LINUX_HOST_PATH;

        # Check if windows
        if(static::isWindows())

            # Set result
            $result = static::WINDOWS_HOST_PATH;

        # Check if mac
        if(static::isMac())

            # Set result
            $result = static::MAC_HOST_PATH;

        # Return result
        return $result;

    }

    /**
     * Is In Hosts File
     * 
     * Check if ip and hostname is in host file
     * 
     * @param string $ip
     * @param string $hostname
     * @return bool
     */
    public static function isInHostsFile(string $ip, string $hostname):bool {
        
        # Get host path
        $hostsFile = static::getHostPath();
    
        # Ensure the file exists and is readable
        if(!is_readable($hostsFile))

            # New error
            throw new CrazyException(
                "Hosts file `".$hostsFile."` is not readable.",
                500,
                [
                    "custom_code"   =>  "os-010",
                ]
            );
    
        # Read the file into an array of lines
        $lines = file($hostsFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        # Iterate through each line in the hosts file
        foreach ($lines as $line) {

            # Skip comments
            if (strpos(trim($line), '#') === 0)

                # continue
                continue;
    
            # Split the line by whitespace (spaces, tabs, etc.)
            $parts = preg_split('/\s+/', $line);
    
            # Check if the first part is the IP address and if the hostname is in the line
            if ($parts[0] === $ip && in_array($hostname, $parts))

                # Found matching IP and hostname
                return true;
        }
    
        # IP and hostname not found in the hosts file
        return false;

    }

    /**
     * Append To Hosts File
     * 
     * Append ip hostname on hosts file
     * 
     * @param string $ip
     * @param string $hostname
     * @return bool
     */
    public static function appendToHostsFile($ip, $hostname):bool {

        # Get hosts path
        $hostsFile = static::getHostPath();

        # Get os
        $os = static::getOs();

        # Prepare entry
        $entry = "{$ip}\t{$hostname}";

        # Command varies by operating system
        $command = '';

        # Check if windows
        if($os === 'windows')

            # Prepare command (Windows might require a full path to the echo command or running as an Administrator)
            $command = "echo {$entry} >> {$hostsFile}";

        # If linux or mac
        else

            # Assumes Linux or macOS
            $command = "echo '{$entry}' | sudo tee -a {$hostsFile}";

        # Execute the command
        exec($command, $output, $return_var);

        # Check the result
        return $return_var === 0;  
    }

    /** Public Constants
     ******************************************************
     */

    /** @var string MAC_HOST_PATH */
    public const MAC_HOST_PATH = "/private/etc/hosts";

    /** @var string LINUX_HOST_PATH */
    public const LINUX_HOST_PATH = "/etc/hosts";

    /** @var string WINDOWS_HOST_PATH */
    public const WINDOWS_HOST_PATH = "C:\\Windows\\System32\\Drivers\\etc\\hosts";

}