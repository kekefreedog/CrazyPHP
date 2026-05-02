<?php declare(strict_types=1);
/**
 * Array
 *
 * Classes for manipulate arrays
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace CrazyPHP\Library\Array;

/**
 * Dependances
*/
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\File\File;

/**
 * Api
 *
 * Api for interacting with modules
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class Api {

    /** Private Methods
     ******************************************************
     */

    /** @param array $_map */
    private array $_map;

    /** @param array|null $_aliasClass */
    private array|null $_aliasClass = null;

    /** Constructor
     ******************************************************
     */

    /**
     * Constructor
     * 
     * @param string|array $mapOrMapPath
     * @param array|null $aliasClass
     * @return self
     */
    public function __construct(string|array $mapOrMapPath, string|array|null $aliasClass = null) {

        # Check mapOrMapPath
        if(is_array($content = $mapOrMapPath) || ($mapOrMapPath && File::exists($mapOrMapPath) && is_array($content = File::open($mapOrMapPath))))

            # Set map
            $this->_map = $content;

        # If not array given
        else

            # New error
            throw new CrazyException("Invalid map array given", 500, [
                "custom_code"   =>  "api_010"
            ]);

        # Set aliasClass
        $aliasClass && ($this->_aliasClass = is_array($aliasClass) ? $aliasClass : [$aliasClass] );

    }

    /**
     * Get
     * 
     * @return Module
     */
    public function __get(string $name):Module {

        # Check name
        if(!isset($this->_map[$name]))

            # New error
            throw new CrazyException("Module '$name' not found", 500, [
                "custom_code"   =>  "api_020"
            ]);

        # Set result
        $result = new Module($name, $this->_map[$name], $this->_aliasClass);

        # Return result
        return $result;


    }

}