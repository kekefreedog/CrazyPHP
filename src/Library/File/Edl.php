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
 * @copyright  2022-2022 Kévin Zarshenas
 */
namespace CrazyPHP\Library\File;

/**
 * Dependances
 */
use CrazyPHP\Library\File\File;

/**
 * Edl
 *
 * Methods for interacting with EDL file
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class Edl {

    /** Public static method
     ******************************************************
     */

    /**
     * Open
     * 
     * Open EDL file content
     *
     * @param string $input Parameter to read
     * @return mixed
     */
    public static function open(string $path = ""):mixed {

        # Set result
        $result = null;

        # Check if file exists
        if(!$path || !File::exists($path))

            # Return result
            return $result;

        # Set path
        $path = File::path($path);

        # Set EDL content
        $edl = [];

        # Set current envent
        $currentEvent = null;
    
        # Open file
        $file = fopen($path, 'r');
    
        # Iteration lines of file
        while (($line = fgets($file)) !== false) {

            # Remove any extra whitespace
            $line = trim($line);
    
            # Skip empty lines
            if(empty($line))

                # Continue
                continue;
    
            # Parse title and frame count mode
            if(preg_match('/^TITLE:\s*(.*)$/', $line, $matches)){

                # File title
                $edl['title'] = $matches[1];

            }else
            # Frame settings
            if(preg_match('/^FCM:\s*(.*)$/', $line, $matches)){

                # Set fcm
                $edl['fcm'] = $matches[1];

            }else
            # Cut content
            if(preg_match('/^(\d+)\s+(\w+)\s+(\w+)\s+(\w)\s+(\d{2}:\d{2}:\d{2}:\d{2})\s+(\d{2}:\d{2}:\d{2}:\d{2})\s+(\d{2}:\d{2}:\d{2}:\d{2})\s+(\d{2}:\d{2}:\d{2}:\d{2})$/', $line, $matches)){

                # Parse edit decision
                $currentEvent = [
                    'eventNumber' => (int)$matches[1],
                    'reel' => $matches[2],
                    'track' => $matches[3],
                    'editType' => $matches[4],
                    'sourceIn' => $matches[5],
                    'sourceOut' => $matches[6],
                    'recordIn' => $matches[7],
                    'recordOut' => $matches[8],
                    'comments' => [],
                    'details' => []
                ];

                # Push in events
                $edl['events'][] = $currentEvent;

            }else
            # Set details
            if(preg_match('/^DLEDL:\s*(.*)$/', $line, $matches) && $currentEvent !== null){
                
                # Parse DLEDL lines
                $currentEvent['details'][] = $matches[1];

                # Push in events
                $edl['events'][count($edl['events']) - 1] = $currentEvent;

            }else
            # From Clip Name
            if(preg_match('/^FROM CLIP NAME:\s*(.*)$/', $line, $matches) && $currentEvent !== null){

                # Parse FROM CLIP NAME lines
                $currentEvent['fromClipName'] = $matches[1];

                # Push in events
                $edl['events'][count($edl['events']) - 1] = $currentEvent;

            }else
            # Set misc data
            if(preg_match('/^\*\s*(.*)$/', $line, $matches) && $currentEvent !== null){

                # Parse comment lines
                $currentEvent['comments'][] = $matches[1];

                # Push in events
                $edl['events'][count($edl['events']) - 1] = $currentEvent;

            }
        }
    
        # Close file
        fclose($file);

        # Return edl
        return $edl;

    }

}