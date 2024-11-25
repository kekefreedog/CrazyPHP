<?php declare(strict_types=1);
/**
 * String
 *
 * Usefull class for manipulate strings
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace  CrazyPHP\Library\String;

/**
 * Dependances
 */
use OzdemirBurak\Iris\Color\Factory;
use OzdemirBurak\Iris\BaseColor;

/**
 * Color
 *
 * Methods for manipulate color
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class Color {

    /** Variables
     ******************************************************
     */

    /** @var BaseColor $_irisInstance */
    private BaseColor $_irisInstance;

    /**
     * Constructor
     * 
     * Ingest data
     * 
     * @param string $color (Can be hsl / rgb / hex...)
     * @return self
     */
    public function __construct(string $color = ""){

        # New iris instance
        $this->_irisInstance = Factory::init($color);

    }

    /** Public methods | Converter
     ******************************************************
     */

    /**
     * To Hex
     * 
     * Convert color to hex
     * @return string
     */
    public function toHex():string {

        # Get hex
        $result = (string) $this->_irisInstance->toHex();

        # Return result
        return $result;

    }

    /**
     * To Hexa
     * 
     * Convert color to hex
     * @return string
     */
    public function toHexa():string {

        # Get hex
        $result = (string) $this->_irisInstance->{"toHexa"}();

        # Return result
        return $result;

    }

    /**
     * To Rgb
     * 
     * Convert color to hex
     * @return string
     */
    public function toRgb ():string {

        # Get hex
        $result = (string) $this->_irisInstance->toRgb();

        # Return result
        return $result;

    }

    /**
     * To Rgba
     * 
     * Convert color to hex
     * 
     * @return string
     */
    public function toRgba ():string {

        # Get hex
        $result = (string) $this->_irisInstance->toRgba();

        # Return result
        return $result;

    }

    /**
     * To Hsl
     * 
     * Convert color to hex
     * @return string|bool
     */
    public function toHsl():string {

        # Get hex
        $result = (string) $this->_irisInstance->toHsl();

        # Return result
        return $result;

    }

    /**
     * To Hsla
     * 
     * Convert color to hex
     * @return string|bool
     */
    public function toHsla():string {

        # Get hex
        $result = (string) $this->_irisInstance->toHsla();

        # Return result
        return $result;

    }

    /** Public methods | Converter
     ******************************************************
     */

    /**
     * Is Light
     * 
     * Check if color is light
     * @return string
     */
    public function isLight():bool {

        # Get hex
        $result = $this->_irisInstance->isLight();

        # Return result
        return $result;

    }

    /**
     * Is Dark
     * 
     * Check if color is dark
     * @return bool
     */
    public function isDark():bool {

        # Get hex
        $result = $this->_irisInstance->isDark();

        # Return result
        return $result;

    }

    /**
     * Is Light Or Dark
     * 
     * Check if color is light or dark
     * @return string
     */
    public function isLightOrDark():string {

        # Get hex
        $result = $this->_irisInstance->isLight()
            ? "light"
            : "dark"
        ;

        # Return result
        return $result;

    }

    /** Public static methods | Randome color
     ******************************************************
     */

    /**
     * Random Hex
     * 
     * Return random color hex format
     * 
     * @return string
     */
    public static function randomHex():string {

        # Generate a random integer between 0 and 16777215 (0xFFFFFF)
        $randomColor = mt_rand(0, 16777215);
        
        // Convert to hexadecimal and ensure it is 6 characters long
        return sprintf("#%06X", $randomColor);

    }

}