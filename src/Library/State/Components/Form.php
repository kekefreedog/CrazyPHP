<?php declare(strict_types=1);
/**
 * State
 *
 * Classes for manipulate state
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace  CrazyPHP\Library\State\Components;

/**
 * Dependances
 */
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\Form\Process;
use CrazyPHP\Library\Form\Validate;
use ReflectionClass;
use DateTime;
use Error;

/**
 * Page
 *
 * Class for manage page state
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class Form {

    /** Private parameters
     ******************************************************
     */

    /** @var string $_id ID of the form */
    private string $_id = "";

    /** @var string $_title Title of the form */
    private string $_title = "";

    /** @var string $_description Title of the form */
    private string $_description = "";

    /** @var string|null $_entity Entity of the form */
    private string|null $_entity = null;

    /** @var string|null $_onready On ready */
    private string|null $_onready = null;

    /** @var array $_items of the form */
    private array $_items = [];

    /** @var boolean $_reset */
    private bool $_reset = false;

    /** @var array $_items_schema Schema of an item */
    private array $_item_schema = [
        "name"          =>  "",
        "type"          =>  "",
        "label"         =>  "",
        "readonly"      =>  "",
        "disabled"      =>  "",
        "required"      =>  "",
        "placeholder"   =>  "",
        "select"        =>  [],
        "default"       =>  null,
        "multiple"      =>  false,
        "_style"        =>  [],
    ];

    /**
     * Constructor
     * 
     * Construct
     * 
     * @param bool $process Just instance the class or run all process
     * @return self
     */
    public function __construct(array|null $form = null){

        # Check form
        if($form && !empty($form)){

            # New reflection class
            $reflectionClass = new ReflectionClass($this);

            # Iteration of form
            foreach($form as $k => $v){

                # Prepare method name
                $methodName = (
                    strpos($k, "item") !== false
                        ? "push"
                        : "set"
                ).Process::snakeToCamel($k, true);

                # Check if set method
                if($reflectionClass->hasMethod($methodName))

                    # Call method with value
                    $this->{$methodName}($v);

            }

        }

    }

    /** Public methods setter
     ******************************************************
     */

    /**
     * Set Id
     * 
     * Set Id of the form
     * 
     * @param string $id ID of the form
     * @return Form
     */
    public function setId(string $id = ""):Form {

        # Set id
        $this->_id = $id;

        # Return self
        return $this;

    }

    /**
     * Get Id
     * 
     * Get Id of the form
     * 
     * @return string
     */
    public function getId():string {

        # Get id
        $result = $this->_id;

        # Return self
        return $result;

    }

    /**
     * Set Title
     * 
     * Set Title of the form
     * 
     * @param string $title Title of the form
     * @return Form
     */
    public function setTitle(string $title = ""):Form {

        # Set id
        $this->_title = $title;

        # Return self
        return $this;

    }

    /**
     * Set Entity
     * 
     * Set Entity of the form
     * 
     * @param string|null $title Title of the form
     * @return Form
     */
    public function setEntity(string|null $entity = null):Form {

        # Set id
        $this->_entity = $entity;

        # Return self
        return $this;

    }

    /**
     * Set Description
     * 
     * Set Title of the form
     * 
     * @param string $description Title of the form
     * @return Form
     */
    public function setDescription(string $description = ""):Form {

        # Set id
        $this->_description = $description;

        # Return self
        return $this;

    }

    /**
     * Set OnReady
     * 
     * Set On Ready action of the form
     * 
     * @param string|null $onReady Title of the form
     * @return Form
     */
    public function setOnReady(string|null $onReady = null):Form {

        # Set id
        $this->_onready = $onReady;

        # Return self
        return $this;

    }

    /**
     * Set Reset
     * 
     * Set Reset action of the form
     * 
     * @param bool $reset Reset of the form
     * @return Form
     */
    public function setReset(bool $reset = false):Form {

        # Set id
        $this->_reset = $reset;

        # Return self
        return $this;

    }

    /**
     * Push Item
     * 
     * Push item of the form
     * 
     * @param array $item Item
     * @return Form
     */
    public function pushItem(array $item = []):Form {

        # Set item temp
        $itemTemp = [];

        # Check item not empty
        if(!empty($item))
        
            # Iteration of each key in item
            foreach($item as $k => $v)

                # Check if key in key schema
                if(array_key_exists($k, $this->_item_schema)){

                    # Check is method exists
                    if(($item["type"] ?? false) && method_exists($this, "_pushItems".ucfirst($k)."Type".ucfirst($item["type"])))

                        # Process value
                        $v = $this->{"_pushItems".ucfirst($k)."Type".ucfirst($item["type"])}($v, $item, $itemTemp);

                    # Push value
                    $itemTemp[$k] = $v;

                }

        # Check itemp temp
        if(!empty($itemTemp))

            # Push in items
            $this->_items[] = $itemTemp;

        # Return self
        return $this;

    }

    /**
     * Push Items
     * 
     * Push item of the form
     * 
     * @param array $items Items of the form
     * @return Form
     */
    public function pushItems(array $items = []):Form {

        # Check items
        if(!empty($items))

            # Iteration items
            foreach($items as $item)

                # Check item is array
                if(is_array($item))

                    # Push item
                    $this->pushItem($item);

        # Return self
        return $this;

    }

    /**
     * Render
     * 
     * Render for template
     * 
     * @param bool $minimize If value is empty, parameter is not in the render
     * @return array
     */
    public function render(bool $minimize = true):array {

        # Set result
        $result = [];

        # Check id
        if(!$minimize || $this->_id)

            # Push id
            $result["id"] = $this->_id;

        # Check title
        if(!$minimize || $this->_title)

            # Push title
            $result["title"] = $this->_title;

        # Check description
        if(!$minimize || $this->_description)

            # Push description
            $result["description"] = $this->_description;

        # Check entity
        if(!$minimize || $this->_entity)

            # Push entity
            $result["entity"] = $this->_entity;

        # Check onready
        if(!$minimize || $this->_onready)

            # Push onready
            $result["onready"] = $this->_onready;

        # Check onready
        if(!$minimize || $this->_reset)

            # Push onready
            $result["reset"] = $this->_reset;

        # Check items
        if(!empty($this->_items))

            # Push onready
            $result["items"] = $this->_items;

        # Return result
        return $result;

    }

    /** Private methods | pushItem
     * 
     * Method name nomenclature :
     *  "_pushItems<Attribute>)Type<Type>
     * 
     * Exemple _pushItemsSelectTypeRange
     * 
     ******************************************************
     */

    /**
     * Push Items Default Type Select
     * 
     * Push default on select
     * 
     * @param mixed $value
     * @param array $currentItem
     * @return mixed 
     */
    private function _pushItemsDefaultTypeSelect(mixed $value, array $currentItem):mixed {

        # Set result
        $result = $value;

        # Check tag
        if(isset($currentItem["_style"]["select"]["tag"]) && Process::bool($currentItem["_style"]["select"]["tag"])){

            # Check if result is not array
            if(!is_array($value))

                # Convert to array
                $reset = [strval($value)];

        }

        # Return result
        return $result;

    }
    /**
     * Push Items Type Password
     * 
     * Keep only two first item and check min and max
     * 
     * @param mixed $value
     * @param array $currentItem
     * @return mixed 
     */
    private function _pushItemsDefaultTypePassword(mixed $value, array $currentItem):mixed {

        # Set result
        $result = $value;

        # Check value
        if(
            $value && (
                !isset($currentItem['_style']['password']['visible']) ||
                (
                    isset($currentItem['_style']['password']['visible']) &&
                    !$currentItem['_style']['password']['visible']
                )
            )
        )

            # Set result
            $result = "Password";

        # Return result
        return $result;

    }

    /**
     * Push Items Type Range
     * 
     * Keep only two first item and check min and max
     * 
     * @param mixed $value
     * @param array $currentItem
     * @return mixed 
     */
    private function _pushItemsSelectTypeRange(mixed $value, array $currentItem):mixed {

        # Set result
        $result = [];

        # Check value
        if(is_array($value) && count($value) >= 2){

            # Keep 2 first item of the value
            $resultTemp = array_slice($value, 0, 2);

            # First item
            $firstItem = current($resultTemp);

            # Second item
            $secondItem = next($resultTemp);

            # Set result
            $result = [
                0   =>  [
                    "value" =>  (is_numeric($firstItem["value"] ?? false))
                        ? intval($firstItem["value"])
                        : 0
                ],
                1   =>  [
                    "value" =>  (is_numeric($secondItem["value"] ?? false))
                        ? intval($secondItem["value"])
                        : 100
                ]
            ];

            # Compare first and second value
            if($result[0]["value"] > $result[1]["value"])

                # Reverse array
                $result = array_reverse($result);

        }

        # Return result
        return $result;

    }

    /**
     * Push Items Type Number
     * 
     * Keep only two first item and check min and max
     * 
     * @param mixed $value
     * @param array $currentItem
     * @return mixed 
     */
    private function _pushItemsSelectTypeNumber(mixed $value, array $currentItem):mixed {

        # Set result
        $result = [];

        # Check value
        if(is_array($value) && count($value) >= 2){

            # Keep 2 first item of the value
            $resultTemp = array_slice($value, 0, 2);

            # First item
            $firstItem = current($resultTemp);

            # Second item
            $secondItem = next($resultTemp);

            # Set result
            $result = [
                0   =>  [
                    "value" =>  (is_numeric($firstItem["value"] ?? false))
                        ? intval($firstItem["value"])
                        : 0
                ],
                1   =>  [
                    "value" =>  (is_numeric($secondItem["value"] ?? false))
                        ? intval($secondItem["value"])
                        : 100
                ]
            ];

            # Compare first and second value
            if($result[0]["value"] > $result[1]["value"])

                # Reverse array
                $result = array_reverse($result);

        }

        # Return result
        return $result;

    }

    /**
     * Push Items Type Number
     * 
     * Check default is number
     * 
     * @param mixed $value
     * @param array $currentItem
     * @param array &$itemTemp Current item return to front
     * @return mixed 
     */
    private function _pushItemsDefaultTypeNumber(mixed $value, array $currentItem, array &$itemTemp):mixed {

        # Set result
        $result = null;

        # Check value
        if(is_numeric($value)){

            # Parse number
            $result = intval($value);

        }else{

            # Push error
            $itemTemp["error"][] = [
                "message"   =>  "Default number given \"$value\" is not a valid number",
            ];

        }

        # Return result
        return $result;

    }

    /**
     * Push Items Type Number
     * 
     * Check default is number
     * 
     * @param mixed $value
     * @param array $currentItem
     * @param array &$itemTemp Current item return to front
     * @return mixed 
     */
    private function _pushItemsDefaultTypeDate(mixed $value, array &$currentItem, array &$itemTemp):mixed {

        # Set result
        $result = null;

        # Check value 
        if($value){

            # Check if Today Yesterday Tomorrow in value
            $value = static::_parseTodayYesterdayTomorrow($value);

            # Check value
            if(static::_isYmdDate($value))

                # Set number
                $result = $value;

            # Else
            else

                # Push error
                $itemTemp["error"][] = [
                    "message"   =>  "Default date given \"$value\" is not matching pattern \"Y-m-d\""
                ];

        }

        # Return result
        return $result;

    }

    /**
     * Push Items Type Date
     * 
     * Keep only two first item and check min and max
     * 
     * @param mixed $value
     * @param array $currentItem
     * @return mixed 
     */
    private function _pushItemsSelectTypeDate(mixed $value, array $currentItem):mixed {

        # Set result
        $result = [];

        # Check value
        if(is_array($value) && count($value) >= 2){

            # Keep 2 first item of the value
            $resultTemp = array_slice($value, 0, 2);

            # First item
            $firstItem = static::_parseTodayYesterdayTomorrow(current($resultTemp)["value"] ?? null);

            # Second item
            $secondItem = static::_parseTodayYesterdayTomorrow(next($resultTemp)["value"] ?? null);

            # Check if dates are valid
            if(static::_isYmdDate($firstItem) && static::_isYmdDate($secondItem)){

                # Set result
                $result = [
                    0   =>  [
                        "value" =>  $firstItem
                    ],
                    1   =>  [
                        "value" =>  $secondItem
                    ]
                ];

            # Else
            }else

                # Push error
                $itemTemp["error"][] = [
                    "message"   =>  "Range from \"$firstItem\" to \"$secondItem\" is not valid"
                ];

            # Compare first and second value
            if((new DateTime($result[0]["value"])) > (new DateTime($result[1]["value"])))

                # Reverse array
                $result = array_reverse($result);

        }

        # Return result
        return $result;

    }

    /** Private static methods
     ******************************************************
     */

    /**
     * Parse Today Yesterday Tomorrow
     * 
     * @param mixed $value
     * @return mixed
     */
    private static function _parseTodayYesterdayTomorrow(mixed $value):mixed {

        # Set result
        $result = $value;

        # Check if value is today()
        if($value == "today()")

            # Set current date
            $result = date('Y-m-d');

        else
        # Check if value is yesterday
        if($value == "yesterday()")

            # Set previous date
            $result = date('Y-m-d', strtotime('-1 day'));

        else
        # Check if value is tomorrow
        if($value == "tomorrow()")

            # Set previous date
            $result = date('Y-m-d', strtotime('+1 day'));

        # Return result
        return $result;

    }

    /**
     * Is Y m d date
     * @param mixed $value
     * @return bool
     */
    private static function _isYmdDate(mixed $value):bool {

        # Set result
        $result = false;

        # Check result
        if(is_string($value) && $value){

            # Convert to date
            $dateInstance = DateTime::createFromFormat('Y-m-d', $value);

            # Check value
            if($dateInstance && ($dateInstance->format('Y-m-d') === $value))

                # Set result
                $result = true;

        }

        # Return result
        return $result;

    }


}