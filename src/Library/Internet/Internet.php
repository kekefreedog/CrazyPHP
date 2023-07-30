<?php declare(strict_types=1);
/**
 * Model
 *
 * Classes utilities for Internet
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
namespace  CrazyPHP\Library\Model;

/**
 * Dependances
 */
use CrazyPHP\Exception\CrazyException;
use CrazyPHP\Library\Form\Validate;
use CrazyPHP\Library\Form\Process;
use CrazyPHP\Library\Array\Arrays;
use CrazyPHP\Library\Cache\Cache;
use CrazyPHP\Library\File\Config;
use CrazyPHP\Library\File\File;

/**
 * Crazy Model
 *
 * Class for manage internet
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class Internet {

    /** Public static methods
     ******************************************************
     */

    /**
     * Is Connected
     * 
     * Check if we are connected to internet
     * 
     * @source https://stackoverflow.com/questions/4860365/determine-in-php-script-if-connected-to-internet
     * 
     * @param string $pageToTest Page to test
     * @param int $portToTest Port to test
     * @return bool
     */
    public function isConnected(string $pageToTest = "https://www.w3.org", int $portToTest = 80):bool {

        # Set result
        $result = false;

        # Check connection to url
        if(@fsockopen($pageToTest, $portToTest))

            # Switch result
            $result = true;

        # Return result
        return $result;

    }

}