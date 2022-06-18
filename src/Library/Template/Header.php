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
use LightnCandy\LightnCandy as Handlebars;
use CrazyPHP\Exception\CrazyException;
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

    /** Public static methods
     ******************************************************
     */

    /**
     * Get
     * 
     * Get and return header
     * 
     * @param string $extension Extension that we want have the header
     * @param array $input Custom data for header :
     *  - name
     *  - description
     *  - author
     *  - copyright
     * @return string
     */
    public static function get(string $extension = "", array $input = self::DEFAULT_INFO):string {

        # Declare result
        $result = "";

        # Process extension
        $extension = trim(strtolower($extension));

        # Check extension
        if(!$extension)
            return $result;

        # Check method associate to extension exists
        if(!array_key_exists($extension, self::EXTENSION_TO_METHODS))

            # New Exception
            throw new CrazyException(
                "No header associated to extension \"$extension\", please contact author for add it.", 
                501,
                [
                    "custom_code"   =>  "header-001",
                ]
            );

        # Get header
        $result = self::{$extension}($input);

        # Return result
        return $result;

    }

    /**
     * yaml
     * 
     * Get Yaml Header
     * 
     * @param array $input Custom data for header :
     *  - name
     *  - description
     *  - author
     *  - copyright
     * @return string
     */
    public static function yaml(array $input = self::DEFAULT_INFO):string {

        # Declare result
        $result = "";

        # 

        # Return result
        return $result;

    }

    /**
     * json
     * 
     * Get Json Header
     * 
     * @param array $input Custom data for header :
     *  - name
     *  - description
     *  - author
     *  - copyright
     * @return string
     */
    public static function json(array $input = self::DEFAULT_INFO):string {

        # Declare result
        $result = "";

        # Return result
        return $result;

    }

    /**
     * php
     * 
     * Get Php Header
     * 
     * @param array $input Custom data for header :
     *  - name
     *  - description
     *  - author
     *  - copyright
     * @return string
     */
    public static function php(array $input = self::DEFAULT_INFO):string {

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
        "php"   =>  "php",
        # Js
        "js"    =>  "js",
        # TBC ...
    ];

    /**
     * Default value
     */
    private const DEFAULT_INFO = [
        # Name
        "name"          =>  [
            "value"         =>  Composer::get("name") ? 
                                    Composer::get("name") :
                                        Create::REQUIRED_VALUES[0],
        ] + Create::REQUIRED_VALUES[0],
        # Description
        "description"   =>  [
            "value"     =>  Composer::get("description") ?
                                Composer::get("description") : 
                                    Create::REQUIRED_VALUES[1],
        ] + Create::REQUIRED_VALUES[1],
        # Authors
        "author"        =>  [
            "name"      =>  "author",
            "value"     =>  Composer::read("authors") ? 
                                Composer::read("authors") :
                                    "kekefreedog <kevin.zarshenas@gmail.com>",
            "type"          =>  "VARCHAR",
        ],
        # Copyright
        "copyright"     =>  [
            "name"      =>  "copyright",
            "value"     =>  Composer::get("name") ? 
                                date("Y")." "(
                                    Composer::get("name") ?
                                        Composer::get("name") :
                                            Create::REQUIRED_VALUES[0]
                                ) :
                                    "2022-".date("Y")." Kévin Zarshenas",
            "type"          =>  "VARCHAR",
        ],

    ];

    /** Private constant | Template
     ******************************************************
     */

    # ... 

}