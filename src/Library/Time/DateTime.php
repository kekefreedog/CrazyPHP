<?php declare(strict_types=1);
/**
 * Time
 *
 * Classe for manipulate time
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace CrazyPHP\Library\Time;

/** 
 * Dependances
 */
use \DateTime as LegacyDateTime;

/**
 * DateTime
 *
 * Methods for manipulate date
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class DateTime extends LegacyDateTime {

    /** Public static methods
     ******************************************************
     */

    /**
     * Get Current
     * 
     * Get current date time
     * 
     * @return static
     */
    public static function getCurrent():int {

        # Set result
        $result = hrtime(true);

        # Return result
        return $result;

    }

    /**
     * Last Update File
     * 
     * Get last update from file
     * 
     * @param string $path Path of the file
     * @return static
     */
    public static function lastUpdateFile(string $path = ""):static {

        # Declare result
        $result = static::kz();

        # Check file
        if(!file_exists($path))
            
            # Return result
            return $result;

        # Set raw last modified time
        $raw = strval(filemtime($path));

        # Set
        $result = static::createFromFormat('U', $raw);

        # Return result
        return $result;

    }

    /**
     * kz
     * 
     * Return Kevin Zarshenas birtday datetime object 
     * 
     * @return static
     */
    public static function kz():static {

        # Set result
        $result = new DateTime('1995-07-25');

        # Return result
        return $result;

    }

    /**
     * Current Year Month Day
     * 
     * Return current date in the format YYYY/MM/DD
     * 
     * @return string
     */
    public static function currentYearMonthDay():string {

        # Set result
        $result = (new DateTime())->format("Y/m/d");

        # Return result
        return $result;

    }

}