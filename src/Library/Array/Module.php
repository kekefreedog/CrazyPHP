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
use BadMethodCallException;
use CrazyPHP\Library\Array\Module\Map;
use CrazyPHP\Library\Form\Validate;

/**
 * Module
 *
 * Methods for interacting with modules
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class Module {

    /** Constructor
     ******************************************************
     */

    /**
     * Constructor
     * 
     * @return self
     */
    public function __construct(private string $module, private array $methods, private array|null $aliasClass = null) {

        # Override alias class
        $this->_overrideAliasClass();

    }

    /**
     * Caller
     * 
     * @return mixed
     */
    public function __call(string $method, array $args) {

        # Set result
        $result = null;

        # Check moethod in methods
        if(!array_key_exists($method, $this->methods))

            # New error
            throw new BadMethodCallException("Method $method not found in {$this->module}");

        # Set value
        $value = $this->methods[$method];

        # Behavior
        if($value === null)

            # Set default
            $result = $this->_default($method, $args);

        else
        # Is callable
        if(is_callable($value))

            # Set result
            $result = $value(...$args);

        else
        # Check if array alias
        if($value && is_string($value) && is_array($this->aliasClass) && !empty($this->aliasClass) && array_key_exists($value, $this->aliasClass) && Validate::isStaticMethod($this->aliasClass[$value])){

            # Set result
            $result = $this->aliasClass[$value](...$args);

        }else
        # string or scalar

            # Set result
            $result = $value; 

        # Return result
        return $result;

    }

    /** Private methods
     ******************************************************
     */

    /**
     * Ovveride Alias Class
     * 
     * @return void
     */
    private function _overrideAliasClass():void {

        # Set alias class
        $aliasClass = $this->aliasClass;

        # Check module
        if($aliasClass && !empty($aliasClass)){
            
            # Set reset alais
            $resetAlias = false;

            # Set temp
            $temp = [];

            // Iteration alias
            foreach($aliasClass as $key => $class) if(is_int($key) || strpos($class, "\\") !== false) {

                # Set 
                $resetAlias = true;

                // Check class
                if(class_exists($class) && is_subclass_of($class, Map::class)){

                    # Extract alias methods
                    $aliasMethods = $class::getMethodsAlias();

                    # Fil it in temp
                    $temp += $aliasMethods;

                }

            }

            # Check reset
            if($resetAlias)

                # Replace alias
                $this->aliasClass = $temp;

        }

    }

    /**
     * Default
     * 
     * @param string $method
     * @param array $args
     * @return mixed
     */
    private function _default(string $method, array $args):mixed {

        # Set result
        $result = "{$this->module}.{$method} called with " . json_encode($args);

        # Return result
        return $result;

    }

}