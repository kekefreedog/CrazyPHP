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
 * @copyright  2022-2023 Kévin Zarshenas
 */
namespace  CrazyPHP\Library\State\Components;

/**
 * Dependances
 */
use CrazyPHP\Exception\CrazyException;

/**
 * Page
 *
 * Class for manage page state
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
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

    /** @var string $_entity Entity of the form */
    private string $_entity = "";

    /** @var array $_on_ready On ready */
    private string $_on_ready = "";

    /** @var array $_items of the form */
    private array $_items = [];

    /** @var array $_items_schema Schema of an item */
    private array $_item_schema = [
        "name"      =>  "",
        "type"      =>  "",
        "label"     =>  "",
        "readonly"  =>  "",
        "disable"   =>  "",
        "required"  =>  "",
    ];

    /**
     * Constructor
     * 
     * Construct
     * 
     * @param bool $process Just instance the class or run all process
     * @return self
     */
    public function __construct(){

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
     * @param string $onReady Title of the form
     * @return Form
     */
    public function setOnReady(string $onReady = ""):Form {

        # Set id
        $this->_on_ready = $onReady;

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
                if(array_key_exists($k, $this->_item_schema))

                    # Push value
                    $itemTemp[$k] = $v;

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
        $result = [];

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

        # Check on_ready
        if(!$minimize || $this->_on_ready)

            # Push on_ready
            $result["on_ready"] = $this->_on_ready;

        # Return result
        return $result;

    }


}