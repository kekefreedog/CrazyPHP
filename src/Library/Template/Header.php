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
use CrazyPHP\Library\Cache\Cache;
use CrazyPHP\Library\File\File;
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

        # Merge array
        $input = array_merge(self::DEFAULT_INFO, $input);

        # Get cache name
        $cacheName = Cache::getCacheName(__METHOD__); ## Update EXTENSION_TO_METHODS

        # Copilate template
        $compilatedTemplate = "";

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

        # Merge array
        $input = array_merge(self::DEFAULT_INFO, $input);

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

        # Merge array
        $input = array_merge(self::DEFAULT_INFO, $input);

        # Return result
        return $result;

    }

    /** Private static methods
     ******************************************************
     */

    /**
     * Compilate
     * 
     * Compilate header template
     * 
     * @param string $name Name of the template to compile
     * @return void
     */
    private static function _compilate(string $name = ""):void {

        # Check name
        if(!$name)
            return;

        # Check name in const
        if(array_key_exists($name, self::NAME_TO_TEMPLATE))
            return;

        # Get template
        $template = File::read(self::NAME_TO_TEMPLATE[$name]);

        # Check template
        if(!$template)
            return;

        # Compilate $template
        $compilation = LightnCandy::compile($template);

        # New cache
        $cacheInstance = new Cache();

        # Set cache
        $cacheInstance->set("CrazyPHP/Library/Template/Header/TemplatesCompilated", $compilation);

    }

    /** Private constant
     ******************************************************
     */

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
     * Name associate to template
     */
    private const NAME_TO_TEMPLATE = [
        "c"     =>  "../../../resources/Hbs/Header/style_c.hbs",
        "shell" =>  "../../../resources/Hbs/Header/style_shell.hbs",
        "html"  =>  "../../../resources/Hbs/Header/style_html.hbs",
    ];

}