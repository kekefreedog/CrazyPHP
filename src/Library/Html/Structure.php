<?php declare(strict_types=1);
/**
 * Html
 *
 * Class for manage html content
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
namespace CrazyPHP\Library\Html;

/** 
 * Dependances
 */
use CrazyPHP\Library\Template\Handlebars;
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\Form\Process;
use CrazyPHP\Library\Array\Arrays;
use CrazyPHP\Library\Html\Head;
use CrazyPHP\Library\File\File;
use CrazyPHP\Model\Context;

/**
 * Structure
 *
 * Class for generate html structure
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class Structure {

    /** Parameters
     ******************************************************
     */

    /** @var array $response */
    private $response = [
        "doctype"   =>  self::HTML_VERSIONS["HTML5"],
        "elements"  =>  []
    ];

    /** @var array $instance */
    private $instance = [
        "Handlebars"    =>  null,
    ];

    /**
     * Constructor
     * 
     * Construct
     * 
     * @param array $options Options
     * @return self
     */
    public function __construct(){

        # Set html tag
        $this->response["elements"][0]["tag"] = "html";

    }

    /** Public methods
     ******************************************************
     */

    /**
     * Prepare
     * 
     * Prepare template
     * 
     * @return self
     */
    public function prepare():self {

        # New template
        $this->instance["Handlebars"] = new Handlebars([
            "template"  =>  Handlebars::HTML_STRUCTURE,
            "partials"  =>  self::PARTIAL_BLOCK
        ]);

        # Load template
        $this->instance["Handlebars"]->load(File::path(self::TEMPLATES["index"]));

        # Return self
        return $this;

    }

    /**
     * render
     * 
     * Prepare template
     * 
     * @param ?array $customResponse Custom Response in the data
     * @param bool $merge Merge custom response with current data
     * @return self
     */
    public function render(?array $customResponse = null, bool $merge = true):string {

        # Set result
        $result = "";

        # Set response
        $response = $this->response;


        # Check custom response
        if($customResponse !== null)

            # Merge data
            $response = Arrays::mergeMultidimensionalArrays(true, $response, $customResponse);

        # Set result
        $result = $this->instance["Handlebars"]->render($response);

        # Return result
        return $result;

    }

    /** Public methods | Elements
     ******************************************************
     */

    /**
     * Set Element
     * 
     * Set element in html structure
     * 
     * @param string $parent Parent of element
     * @param array|string $attributesOrContent Attributes of element
     * @param string $tag Tag name of element
     * @param bool $createParentIfNotExists Create parent if not exists
     * @return self
     */
    public function setElement(
        string $parent = "html.body", 
        array|string|null $attributesOrContent = null, 
        ?string $tag = null, 
        ?array $children = null,
        bool $createParentIfNotExists = true,
    ):self {

        ## Check inputs values

        # Check tag
        if($tag === "")

            # $et default tag
            $tag = "div";

        else
        # Check case
        if($tag)

            # Set cas of current tag
            $tag = strtolower($tag);

        # Check parent
        if(!$parent)

            # Set default parent
            $parent = "html.body";

        # Else
        else{

            # Check case
            $parent = strtolower($parent);

            # Check if str start by html
            if(!in_array(substr($parent, 0, 4), ["html"]))
                    
                # New error
                throw new CrazyException(
                    "You can only create element in \"html\” element, \"$parent\" isn't valid.", 
                    500,
                    [
                        "custom_code"   =>  "structure-001",
                    ]
                );

        }

        ## Prepare tag in result

        # Prepare parent
        $parent = str_replace([".", "/"], "___", $parent);

        # Explode parents
        $keys = explode("___", $parent);

        # Set cursor
        $cursor = &$this->response["elements"];

        # Loop
        $i=0;while(isset($keys[$i])){

            # Get array key
            $current = Arrays::filterByKey($cursor, "tag", $keys[$i]);

            # Check key in response
            if(!empty($current)){
                
                # Get current key
                $currentKey = array_key_first($current);

                # Check elements is already set
                if(!isset($cursor[$currentKey]["elements"]))
                
                    # Prepare element
                    $cursor[$currentKey]["elements"] = [];

                # Update cursor
                $cursor = &$cursor[$currentKey]["elements"];

            }else
            # Check if parent can be create
            if($createParentIfNotExists){

                # New parent
                $parent = [];

                # Fill tag
                $parent["tag"] = $keys[$i] ? $keys[$i] : "div";

                # Create elements
                $parent["elements"] = [];

                # New key
                $key = (int)array_key_last($cursor) + 1;

                # Push to current cursor
                $cursor[$key] = $parent;

                # Update cursor
                $cursor = &$cursor[$key]["elements"];


            }else

                # Return self
                return $this;

        $i++;}

        # Set elements
        $element = [];

        # Check if content
        if($tag === null){

            # Set html
            $element["html"] = $attributesOrContent;

        }else{
            # Prepare element
            $element["tag"] = $tag;

            # Check attributes
            if(!empty($attributesOrContent) || $attributesOrContent)

                # Set attributes
                $element["attributes"] = is_string($attributesOrContent) ? 
                    [$attributesOrContent => null] :
                        $attributesOrContent;

            # Check children
            if($children && !empty($children))

                # Set children
                $element["elements"] = $children;

        }


        # Create element in cursor
        $cursor[] = $element;

        # Return self
        return $this;

    }

    /** Public methods | html
     ******************************************************
     */

    /**
     * Set Doctype
     * 
     * Doctype Declaration
     * 
     * @param string $version Version of html
     * @return self
     */
    public function setDoctype(string $version = "HTML5"):self {

        # Check doctype
        if(!array_key_exists(strtoupper($version), self::HTML_VERSIONS))

            # New exception
            throw new CrazyException(
                "Version of html \"$version\" isn't supported.",
                500,
                [
                    "custom_code"   =>  "structure-002",
                ]
            );

        # Set doctype
        $this->response["doctype"] = self::HTML_VERSIONS[$version];

        # Return current instance
        return $this;

    }

    /** Public methods | html
     ******************************************************
     */

    /**
     * Set Language
     * 
     * Set Language in html tag
     * (if null, detect if LANGUAGE or LANG in parameters)
     * 
     * @param ?string $language Local language
     */
    public function setLanguage(?string $language = null, array $keys = ["LANG", "LANGUAGE"]):self {

        # Check language
        if(!$language){

            # Check keys
            if(empty($keys))

                # Return self
                return $this;

            # Get url parameter
            $collection = Context::get("routes.current.parameters") ?: [];

            # Get $_REQUEST
            $collection = array_merge($_REQUEST, $collection);

            # Str to upper on keys
            $collection = Arrays::changeKeyCaseRecursively($collection, CASE_UPPER);

            # Convert keys to upper
            $keys = array_map('strtoupper', $keys);

            # Set language
            $language = null;

            # Iteration supposition
            foreach($keys as $key)
            
                # Check if lang is in array
                if(array_key_exists($key, $collection)){

                    # Set language
                    $language = $collection[$key];

                    # Break loop
                    break;

                }

            # Check language
            if(!$language)

                # Return this
                return $this;

        }

        # Set language in response
        $this->response["elements"][0]["attributes"]["lang"] = $language;

        # Return current instance
        return $this;

    }

    /** Public methods | body
     ******************************************************
     */

    /**
     * Set Head
     * 
     * Set Head Tag
     * 
     * @param string $config Config name to load
     * @param string $attributes Attributes for head
     * @return self
     */
    public function setHead(string $config = "main", array $attributes = []):self {

        # Head
        $head = new Head();

        # Get head
        $children = $head->get();

        # Set element
        $this->setElement("html", $attributes, "head", $children);

        # Return current instance
        return $this;

    }

    /** Public methods | body
     ******************************************************
     */

    /**
     * Set Body
     * 
     * Set Body Tag
     * 
     * @return self
     */
    public function setBody(string|array|null $attributes = null):self {

        # Set element
        $this->setElement("html", $attributes, "body");

        # Return current instance
        return $this;

    }

    /**
     * Set Body Content
     * 
     * Set Body Content
     * 
     * @param ?string $content Content to put in body
     * @return self
     */
    public function setBodyContent(?string $content = ""):self {

        # Check content
        if($content !== null)

            # Set element
            $this->setElement("html.body", $content, null);

        # Return current instance
        return $this;

    }

    /** Public constants
     ******************************************************
     */

    /** @const array HTML_VERSIONS */
    public const HTML_VERSIONS = [
        # HTML 4
        "HTML4.01"  =>  "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">",
        # XHTML 1.1
        "XHTML1.1"  =>  "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\" \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">",
        # HTML5
        "HTML5"     =>  "<!DOCTYPE html>"
    ];

    /** @const TEMPLATES */
    public const TEMPLATES = [
        "index" =>  "@crazyphp_root/resources/Hbs/App/index.hbs"
    ];

    /** @const string PARTIAL_BLOCK for Handlbars Js engine */
    public const PARTIAL_BLOCK = [
        # Generate html structure from `elements` objects 
        "htmlElement"      =>  "{{#if elements}}".
                                    "{{#each elements as |element|}}".
                                        "{{#if element.html}}".
                                            "{{{html}}}".
                                        "{{else}}".
                                            "<{{element.tag}}{{> htmlAttribute this=element}}>".
                                                "{{#if element.elements}}{{> htmlElement elements=element.elements}}{{/if}}".
                                            "</{{element.tag}}>".
                                        "{{/if}}".
                                    "{{/each}}".
                                "{{/if}}",
        # Generate attribute, need `element` object
        "htmlAttribute"     =>  "{{#if element.attributes}}{{#each element.attributes}} {{@key}}{{#if this}}=\"{{this}}\"{{/if}}{{/each}}{{/if}}",
    ];

}