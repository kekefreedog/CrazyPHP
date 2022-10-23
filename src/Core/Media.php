<?php declare(strict_types=1);
/**
 * Core
 *
 * Critical function of your crazy application
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
namespace CrazyPHP\Core;

/**
 * Dependances
 */

/**
 * Media
 *
 * Class for manage media...
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class Media {

    /** Public methods
     ******************************************************
     */

    /**
     * Register From Folder
     * 
     * Register all media in folder
     * 
     * @param string $path
     * @param array $options
     * @return self
     */
    public function registerFromFolder():self {

        # Return self
        return $this;

    }

    /**
     * Register
     * 
     * Register list of media
     * 
     * @param string|array $path
     * @return self
     */
    public function register(string|array $inputs = []):self {

        # Return self
        return $this;

    }

    /**
     * Get
     * 
     * Get list of media
     * 
     * @param string|array $path
     * @return self
     */
    public function get(string|array $inputs = []):self {

        # Return self
        return $this;

    }

    /**
     * Read Content
     * 
     * Get Content of media
     * 
     * @param string $path
     * @return self
     */
    public function readContent(string $input = ""):self {

        # Return self
        return $this;

    }

}