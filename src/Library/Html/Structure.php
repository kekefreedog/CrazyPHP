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
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace CrazyPHP\Library\Html;

/** 
 * Dependances
 */
use Pelago\Emogrifier\HtmlProcessor\CssToAttributeConverter;
use Pelago\Emogrifier\HtmlProcessor\HtmlPruner;
use CrazyPHP\Library\Template\Handlebars;
use CrazyPHP\Exception\CrazyException;
use Symfony\Component\Finder\Finder;
use CrazyPHP\Library\String\Color;
use CrazyPHP\Library\Array\Arrays;
use Pelago\Emogrifier\CssInliner;
use CrazyPHP\Library\File\Config;
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
 * @copyright  2022-2024 Kévin Zarshenas
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

    /** @var bool $watch Bool for check if watch mode is enable */
    private $watch = false;

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

        # Set last hash
        $this->_setHash($result);

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
            if(!empty($attributesOrContent) || $attributesOrContent){

                # Set attributes
                $element["attributes"] = is_string($attributesOrContent) ? 
                    [$attributesOrContent => null] :
                        $attributesOrContent;

                # Check if bool in attributes
                /* foreach($element["attributes"] as &$value)

                    # Check if is bool
                    if(is_bool($value))
                    
                        # Convert value to string
                        $value = $value ? "true" : "false"; */

            }

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

        # Push color attribute
        $this->_pushColorSchema($attributes);

        # Set element
        $this->setElement("html", $attributes, "body");

        # Return current instance
        return $this;

    }

    /**
     * Set Body Color Schema
     * 
     * Set Body Tag
     * 
     * @param ?string $source For override current colorschema
     * @return self
     */
    public function setBodyColorSchema(?string $source = null):self {

        # Push color attribute
        $this->_pushColorSchema($attributes, $source);

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
        if($content !== null){

            # Set element
            $this->setElement("html.body", $content, null);

        }

        # Return current instance
        return $this;

    }

    /**
     * Set Body Template
     * 
     * Set Body Hbs Template 
     * 
     * @param string|array|null $template Template to load
     * @param $preset Preset of the template
     * @param ?array $data Data for template
     * @param ?string customNameForTemplateCache
     * @return self
     */
    public function setBodyTemplate(string|array|null $template = null, $preset = null, ?array $data = [], ?string $customNameForTemplateCache = null):self {

        # Check template
        if(!$template || empty($template))

            # Return current instance
            return $this;

        # Check extension of template
        if(in_array(strtolower(pathinfo($template, PATHINFO_EXTENSION)), Handlebars::EXTENSIONS))

            # Set instance
            $instance = "CrazyPHP\Library\Template\Handlebars";

        # Check preset
        if($preset)

            # Prepare option
            $option = [
                'template'  =>  $preset,
                'partials'  =>  Handlebars::loadAppPartials()
            ];

        # Else
        else

            # Set option
            $option = [
                'partials'  =>  Handlebars::loadAppPartials()
            ];

        # Prepare template
        $templateInstance = new $instance($option);

        # Load template
        $templateInstance->load($template, $customNameForTemplateCache ? $customNameForTemplateCache : Context::get("routes.current.name"));

        # Rendered template
        $renderedTemplate = $templateInstance->render($data);

        # Set body with template render
        $this->setBodyContent($renderedTemplate);

        # Return current instance
        return $this;

    }

    /**
     * Set Body Email Template
     * 
     * Set Body Hbs Template format for email 
     * 
     * @param string|array|null $template Template to load
     * @param $preset Preset of the template
     * @param ?array $data Data for template
     * @param ?string customNameForTemplateCache
     * @return self
     */
    public function setBodyEmailTemplate(string|array|null $template = null, $preset = null, ?array $data = [], null|string|array $cssFiles = null, ?string $customNameForTemplateCache = null):self {

        # Check template
        if(!$template || empty($template))

            # Return current instance
            return $this;

        # Check extension of template
        if(in_array(strtolower(pathinfo($template, PATHINFO_EXTENSION)), Handlebars::EXTENSIONS))

            # Set instance
            $instance = "CrazyPHP\Library\Template\Handlebars";

        # Check preset
        if($preset)

            # Prepare option
            $option = [
                'template'  =>  $preset,
                'partials'  =>  Handlebars::loadAppPartials()
            ];

        # Else
        else

            # Set option
            $option = [
                'partials'  =>  Handlebars::loadAppPartials()
            ];

        # Prepare template
        $templateInstance = new $instance($option);

        # Load template
        $templateInstance->load($template, $customNameForTemplateCache ? $customNameForTemplateCache : Context::get("routes.current.name"));

        # Rendered template in html
        $renderedTemplate = $templateInstance->render($data);

        # css inliner
        $cssInliner = CssInliner::fromHtml($renderedTemplate);

        # Check css
        if(!empty($cssFiles)){

            # ->inlineCss($renderedTemplate)

        }

        # Dom Document
        $domDocument = $cssInliner->getDomDocument();
        
        # Clean html
        HtmlPruner::fromDomDocument($domDocument)
            ->removeElementsWithDisplayNone()
            ->removeRedundantClassesAfterCssInlined($cssInliner)
        ;

        # Convert attribute if needed
        $finalHtml = CssToAttributeConverter::fromDomDocument($domDocument)
            # ->convertCssToVisualAttributes()
            ->renderBodyContent()
        ;

        # Set body with template render
        $this->setBodyContent($finalHtml);

        # Return current instance
        return $this;

    }

    /**
     * Set Js Scripts
     * 
     * Set Js Scripts in body
     * 
     * @param string $forceCurrentName Force a current name (error page for exemple)
     * @return self
     */
    public function setJsScripts(string $forceCurrentName = null):self {

        # Declare scripts
        $configFront = [];

        # Get watch value
        $this->watch = Config::getValue("Front.lastBuild.watch");

        # Check watch
        if($this->watch){

            ## Search generic js files

            # New finder
            $finder = new Finder();

            # Search js generated
            $finder
                ->files()
                ->name("*.js")
                ->depth('== 0')
                ->in(File::path("@app_root/public/dist"))
            ;

            # Check files
            if(!$finder->hasResults())
                            
                # New error
                throw new CrazyException(
                    "It looks generation of js files with watch mode enable failed...", 
                    500,
                    [
                        "custom_code"   =>  "structure-003",
                    ]
                );

            # Iteration of finder
            foreach($finder as $file)

                # Push in scripts
                $configFront[] = $file->getRelativePathname();

            ## Search current page js files

            # New finder
            $finder = new Finder();

            # Set routesCurrentName
            $routesCurrentName = $forceCurrentName ?: Context::get("routes.current.name");

            # Check $routesCurrentName
            if($routesCurrentName){

                # Search js generated
                $finder
                    ->files()
                    ->name("$routesCurrentName.*.js")
                    ->depth('== 0')
                    ->in(File::path("@app_root/public/dist/page/app"))
                ;

                # Check files
                if(!$finder->hasResults()){
                                
                    # New error
                    throw new CrazyException(
                        "It looks generation of js files with watch mode enable failed... Impossible to found script for \"$routesCurrentName\”", 
                        500,
                        [
                            "custom_code"   =>  "structure-003",
                        ]
                    );

                }

            }else{

                # Pattern
                $pattern = '/\/dist\/page\/app\/(.*?)\./';
    
                # Search the string with the pattern
                if(preg_match($pattern, $_SERVER["REQUEST_URI"] ?? "", $matches)){
                    
                    # Route of current name
                    $routesCurrentName = $matches[1];

                    # Search js generated
                    $finder
                        ->files()
                        ->name("$routesCurrentName.*.js")
                        ->depth('== 0')
                        ->in(File::path("@app_root/public/dist/page/app"))
                    ;
    
                    # Check files
                    if(!$finder->hasResults()){
                                    
                        # New error
                        throw new CrazyException(
                            "It looks generation of js files with watch mode enable failed... Impossible to found script for \"$routesCurrentName\”", 
                            500,
                            [
                                "custom_code"   =>  "structure-003",
                            ]
                        );
    
                    }

                }else{

                    # New error
                    throw new CrazyException(
                        "It looks generation of js files with watch mode enable failed... Sorry", 
                        500,
                        [
                            "custom_code"   =>  "structure-003",
                        ]
                    );

                }

            }

            # Iteration of finder
            foreach($finder as $file){

                # Push in scripts
                $configFront[] = "page/app/".$file->getRelativePathname();

            }

        }else{

            # Get value from config
            $configFront = Config::getValue("Front.lastBuild.files");

            # Current page
            $currentPage = "page/app/".($forceCurrentName ?: Context::get("routes.current.name")).".".Config::getValue("Front.lastBuild.hash").".js";

            # Js script for current page
            if(file_exists(File::path("@app_root/public/dist/$currentPage")))

                $configFront[] = $currentPage;

            # Check value
            if(!$configFront || empty($configFront))
                        
                # New error
                throw new CrazyException(
                    "Please check that you are correctly build Js Files with this command : `php vendor/kzarshenas/crazyphp/bin/CrazyFront run build`", 
                    500,
                    [
                        "custom_code"   =>  "structure-004",
                    ]
                );

        }

        # Iteration of config value
        foreach($configFront as $file){

            # Add in the body
            $this->setElement("html.body", ["src" => "/dist/$file"], "script");

        }

        # Return current instance
        return $this;

    }

    /** Private methods
     ******************************************************
     */

    private function _setHash(string &$input):void {

        # Get watch
        if(Config::getValue("Front.lastBuild.watch")){

            # New finder
            $finder = new Finder();

            # Prepare finder
            $finder
                ->files()
                ->name('index.*.js')
                ->in(File::path("@app_root/".Config::getValue("App.public")."/dist"))
                ->depth('== 0')
            ;

            # Check finder
            if($finder->hasResults())

                # Iteration file
                foreach ($finder as $file){

                    # Prepare pattern
                    $pattern = '/\w+\.([a-fA-F0-9]+)\.js/';

                    # Search
                    preg_match($pattern, $file->getFilename(), $matches);

                    # Set hash
                    $hash = $matches[1] ?? null;

                }

            else 

                # Set hash
                $hash = null;

        }else


            # Get hash
            $hash = Config::getValue("Front.lastBuild.hash");

        # Prepare pattern
        $pattern = '/<meta\s+name="application-hash"\s+content="([a-fA-F0-9]+)">/';

        # Check hash
        if($hash === null)

            # Stop function
            return;

        # Prepare replacement
        $replacement = "<meta name=\"application-hash\" content=\"$hash\">";

        # Replace
        $input = preg_replace($pattern, $replacement, $input);

    }

    /**
     * Push Color schema on attributes
     * 
     * @param mixed &$attributes attributes to update
     * @param ?string $source to override default color schema
     * @return void
     */
    private function _pushColorSchema(mixed &$attributes, ?string $source = null):void {

        # Get color source
        $appSource = Config::getValue("Style.materialDynamicColors.source");

        # Push language in response
        $attributes["data-default-color"] = $appSource;

        # Check source
        if(!$source){

            # Get url parameter
            $source = $appSource;

        }else
        # Check source
        if($source && Color::isValid($source)){

            # check attributes
            if(!is_array($attributes))

                # Convert to array
                $attributes = [];

            # Set language in response
            $attributes["data-crazy-color"] = $source;

        }

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