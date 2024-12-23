<?php declare(strict_types=1);
/**
 * Json
 *
 * Classe for manipulate specific files
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace  CrazyPHP\Library\File;

/**
 * Dependances
 */
use CrazyPHP\Library\Template\Handlebars;
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\Time\DateTime;
use CrazyPHP\Library\File\Composer;
use CrazyPHP\Library\Cache\Cache;
use CrazyPHP\Library\File\File;
use CrazyPHP\Model\App\Create;

/**
 * Header
 *
 * Methods for get header depending of type
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
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
     * @param array|null $input Custom data for header :
     *  - name
     *  - description
     *  - author
     *  - copyright
     * @return string
     */
    public static function get(string $extension = "", array|null $input = []):string {

        # Declare result
        $result = "";

        # Fill input if null
        if($input === null) $input = self::_getDefaultInfo();

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
        $result = self::{self::EXTENSION_TO_METHODS[$extension]}($input);

        # Return result
        return $result;

    }

    /**
     * Get Value
     * 
     * Get Value From Header
     * 
     * @param string $name If empty returns all value
     * @return mixed
     */
    public static function getValue(string $name = ""):mixed {

        # Retrieve all request headers
        $result = getallheaders();
    
        # Check if the requested header exists
        if($name && isset($result[$name]))

            # Set result
            $result = $result[$name];

        else

            # Set result as null
            $result = null;

        # Return result
        return $result;

    }

    /**
     * Get All
     * 
     * Return all header
     * 
     * @return array
     */
    public static function getAllValues():array {

        # Return all headers
        return getallheaders();

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
    public static function yaml(array $input = []):string {

        # Declare result
        $result = "";

        # Merge array
        $input = array_merge(self::_getDefaultInfo(), $input);

        # Set result
        $result = self::_compilate("shell", $input);

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
    public static function json(/* array $input = [] */):string {

        # Declare result
        $result = "";

        # !!! Json doesn't support comment !!!

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
    public static function php(array $input = []):string {

        # Declare result
        $result = "";

        # Merge array
        $input = array_merge(self::_getDefaultInfo(), $input);

        # Set result
        $result = self::_compilate("c", $input);

        # Return result
        return $result;

    }

    /**
     * Clean
     * 
     * Clean header from server request
     * 
     * @param array $headers
     * @return array
     */
    public static function clean(array $headers = []):array {

        # Set result
        $result = $headers;

        # Check headers
        if(empty($headers))

            # Return result
            return $result;

        # Iteration headers
        foreach($headers as $header => $content){

            # Prepare option
            $option = null;

            # Prepare option
            if(array_key_exists($header, static::CLEAN_EXCEPTION))

                $option = static::CLEAN_EXCEPTION[$header];

            # Check not exeption
            if((isset($option["continue"]) && $option["continue"]) || empty($content))

                # Continue iteration
                continue;

            # Set key first
            $keyfirst = array_key_first($content);

            # Check separator
            if(isset($option["separator"]) && $option["separator"]){

                # Set content
                $value = explode($option["separator"], $content[$keyfirst]);

            }else{

                # Set value
                $value = $content[$keyfirst];

            }

            # Check value is array and if option has keySeparator
            if(is_array($value) && isset($option["keySeparator"]) && $option["keySeparator"]){

                # Declare temp value
                $tempValue = [];

                # Iteration of value
                foreach($value as $v){

                    # Exemple v with keySeparator
                    $v = explode($option["keySeparator"], $v, 2);

                    # Set temp value
                    $tempValue[$v[0]] = $v[1] ?? null;

                }

                # Replace value
                $value = $tempValue;

            }

            # Set content
            $result[$header] = $value;

        }

        # Return result
        return $result;

    }

    /**
     * Get Header Accept
     * 
     * If empty return text/html
     * 
     * @param ?array $headerList Collection of headers
     * @return string|null
     */
    public static function getHeaderAccept(?array $headerList = null):string {

        # Check headerlist
        if($headerList === null)

            # Get current headers
            $headerList = getallheaders();

        # Get Accept
        $accept = $headerList["Accept"] ?? "text/html";

        # Check if accept has */*
        if(strpos($accept, "*/*") !== false){

            # Set accept
            $firstAccept = "text/html";

        }else{

            # Explod accept
            $accepts = explode(",", $accept);

            # Get first
            $firstAccept = $accepts[0];

            # Split for avoid string after ;
            $firstAccept = explode(";", $firstAccept)[0];

        }

        # Set result
        $result = $firstAccept;

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
     * @return string
     */
    private static function _compilate(string $name = "", array $data = []):string {

        # Declare result
        $result = "";

        # Check name
        if(!$name)
            return $result;

        # Check name in const
        if(!array_key_exists($name, self::NAME_TO_TEMPLATE))
            return $result;

        # Get template
        $templatePath = self::NAME_TO_TEMPLATE[$name];

        # Check if @dir variable
        if(strpos($templatePath, "@dir") !== false)

            # Replace @dir by __DIR__
            $templatePath = str_replace("@dir", __DIR__, $templatePath);

        # Check template
        if(!$templatePath)
            return $result;

        # New Handlebars
        $template = new Handlebars();
        
        # Load path
        $template->load($templatePath);

        # Render template
        $result = $template->render($data);

        # Return result
        return $result;

    }

    /**
     * Get Default Info
     * 
     * Process default info and return it
     * 
     * @return array
     */
    private static function _getDefaultInfo():array {

        # Set result
        $result = self::DEFAULT_INFO;

        # Set name.value
        if(Composer::get("name")) $result['name']['value'] = Composer::get("name");

        # Set description.value
        if(Composer::get("description")) $result['description']['value'] = Composer::get("description");

        # Set author.value
        if(Composer::get("author")) $result['author']['value'] = Composer::get("authors");

        # Set copyright.value
        if(Composer::get("name")) $result['copyright']['value'] = date("Y")." ".(
                                                                                    Composer::get("name") ?
                                                                                        Composer::get("name") :
                                                                                            Create::REQUIRED_VALUES[0]
                                                                                );

        # Return result
        return $result;

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
            "value"         =>  Create::REQUIRED_VALUES[0]['default'],
        ] + Create::REQUIRED_VALUES[0],
        # Description
        "description"   =>  [
            "value"         =>  Create::REQUIRED_VALUES[1]['default'],
        ] + Create::REQUIRED_VALUES[1],
        # Authors
        "author"        =>  [
            "name"          =>  "author",
            "value"         =>  "kekefreedog <kevin.zarshenas@gmail.com>",
            "type"          =>  "VARCHAR",
        ],
        # Copyright
        "copyright"     =>  [
            "name"          =>  "copyright",
            "value"         =>  "2022-2024 Kévin Zarshenas",
            "type"          =>  "VARCHAR",
        ],

    ];

    /**
     * Correspondance between extension and methods
     */
    private const EXTENSION_TO_METHODS = [
        # Yml
        "yml"   =>  "yaml",
        "yaml"  =>  "yaml",
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
     * 
     * @dir will be replace by __DIR__
     */
    private const NAME_TO_TEMPLATE = [
        "c"     =>  "@dir/../../../resources/Hbs/Header/style_c.hbs",
        "shell" =>  "@dir/../../../resources/Hbs/Header/style_shell.hbs",
        "html"  =>  "@dir../../../resources/Hbs/Header/style_html.hbs",
    ];

    /** Public constant
     ******************************************************
     */

    /**
     * Clean exception
     */
    public const CLEAN_EXCEPTION = [
        "Host"              => [
            "keySeparator"      =>  ":"  
        ],
        "Cookie"            =>  [
            "separator"         =>  "; ",
            "keySeparator"      =>  "=" 
        ],
        "Accept-Encoding"   =>  [
            "separator"         =>  ", "
        ],
        "Accept-Language"   =>  [
            "separator"         =>  ";"
        ],
        "Accept"            =>  [
            "separator"         =>  ","
        ]
    ];

}