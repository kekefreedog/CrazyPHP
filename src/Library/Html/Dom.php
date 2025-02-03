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
use DOMDocument;

/**
 * Dom
 *
 * Class for manipulate Dom
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class Dom {

    /** Parameters
     ******************************************************
     */

    /**
     * Get Plain Text
     * 
     * Extract text from html string
     * 
     * @param string $htmlString
     * @param bool $bodyOnly Extract body only
     */
    public static function getPlainText(string $htmlString, bool $bodyOnly = true):string {

        # Set result
        $result = "";

        # Check htmlString
        if($htmlString){

            # New Dom Instance
            $dom = new DOMDocument();

            # Suppress warnings from malformed HTML
            @$dom->loadHTML($htmlString);

            # Check if body
            if($bodyOnly){

                # Get body
                $bodyEls = $dom->getElementsByTagName('body');

                # Check body
                $bodyEl = $bodyEls->item(0);

                # Get text content
                $result = $bodyEl->textContent;


            }else{
                
                # Get text content
                $result = $dom->textContent;

            }

        }

        # Return result
        return trim($result);
    }

}