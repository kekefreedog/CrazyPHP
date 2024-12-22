<?php declare(strict_types=1);
/**
 * String
 *
 * Usefull class for manipulate strings
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace  CrazyPHP\Library\String;

/**
 * Dependances
 */
use CrazyPHP\Library\File\Header;

/**
 * Language
 *
 * Methods for manipulate language
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class Language {

    /** Public static methods
     ******************************************************
     */

    /**
     * Get
     * 
     * Get language
     * 
     * @param bool $shortResponse 
     * @return string
     */
    public static function get(bool $shortResponse = true):string {

        # Set result
        $result = $shortResponse ? "en" : "en-US";

        # Get header
        $acceptLanguageRaw = Header::getValue("Accept-Language");
        
        # Check accept
        if($acceptLanguageRaw){
            
            # Check if separator
            if(strpos($acceptLanguageRaw, Header::CLEAN_EXCEPTION["Accept-Language"]["separator"]) !== false)

                # Get first part
                $acceptLanguageRaw = explode(Header::CLEAN_EXCEPTION["Accept-Language"]["separator"], $acceptLanguageRaw)[0];

            # Check if coma
            if(strpos($acceptLanguageRaw, ",") !== false){

                # Explode accept langiage
                $acceptLanguage = explode(",", $acceptLanguageRaw);

                # Set result
                $result = $shortResponse
                    ? $acceptLanguage[1]
                    : $acceptLanguage[0]
                ;

            }

        }

        # Return result
        return $result;

    }

}