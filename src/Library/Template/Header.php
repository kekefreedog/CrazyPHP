<?php declare(strict_types=1);
/**
 * Template
 *
 * Classe for templating
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
namespace CrazyPHP\Library\Template;

/** Dependances
 * 
 */
use CrazyPHP\Library\File\Composer;
use CrazyPHP\App\Create;

/**
 * Header
 *
 * Methods for get header depending of type
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class Header{

    /** Public methods
     ******************************************************
     */

    /**
     * Get and return header
     * 
     * @param string $extension Extension that we want have the header
     * 
     * @return string
     */
    public function get(string $extension = "", array $information = self::DEFAULT_INFO):string {

        # Declare result
        $result = "";

        # Return result
        return $result;

    }

    /** Private constant
     ******************************************************
     */

    /**
     * Correspondance between extension and methods
     */
    private const EXTENSION_TO_METHODS = [
        # Yml
        "yml"   =>  "yml",
        "yaml"  =>  "yml",
        # Json
        "json"  =>  "json",
        # Php
        "php"   =>  "php"
        # TBC ...
    ];

    /**
     * Default value
     */
    private const DEFAULT_INFO = [
        # Name
        "name"          =>  Composer::get("name") ? 
                                Composer::get("name") :
                                    Create::REQUIRED_VALUES[0],
        # Description
        "description"   =>  Composer::get("description") ?
                                Composer::get("description") : 
                                    Create::REQUIRED_VALUES[1],
        # Authors
        "author"        =>  Composer::read("authors") ? 
                                Composer::read("authors") :
                                    "kekefreedog <kevin.zarshenas@gmail.com>",
        # Copyright
        "copyright"     =>  Composer::get("name") ? 
                                date("Y")." "(
                                    Composer::get("name") ?
                                        Composer::get("name") :
                                            Create::REQUIRED_VALUES[0]
                                ) :
                                    "2022-".date("Y")." Kévin Zarshenas"

    ];

}