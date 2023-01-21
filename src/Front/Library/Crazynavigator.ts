/**
 * Front
 *
 * Front TS Scrips for your Crazy App
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */

/**
 * Navigator utilities
 *
 * Methods for manage components loaded and to load
 * 
 * @source https://stackoverflow.com/questions/9847580/how-to-detect-safari-chrome-ie-firefox-and-opera-browsers
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
export default class Crazynavigator {

    /** Public static methods | Validator
     ******************************************************
     */

    /**
     * Is Chrome
     * 
     * Chrome 1 - 79
     * 
     * @return boolean
     */
    public static isChrome = ():boolean => (navigator.userAgent.indexOf("Chrome") != -1);

    /**
     * Is Firefox
     * 
     * Firefox 1.0+
     * 
     * @return bool
     */
    public static isFirefox = ():boolean => (navigator.userAgent.indexOf("Firefox") != -1);
    
    /**
     * Is Opera
     * 
     * Opera 8.0+
     * 
     * @return boolean
     */
    public static isOpera = () => (
        (
            navigator.userAgent.indexOf("Opera") || 
            navigator.userAgent.indexOf('OPR')
        ) != -1 
    );

    /**
     * Is Safari
     * 
     *  Safari 3.0+ "[object HTMLElementConstructor]" 
     * 
     * @return bool
     */
    public static isSafari = ():boolean => (navigator.userAgent.indexOf("Safari") != -1);

    /**
     * Is Internet Explorer
     * 
     * Internet Explorer 6-11
     * 
     * @return boolean
     */
    public static isIE = ():boolean => (
        (
            navigator.userAgent.indexOf("MSIE") != -1 
        ) || 
        (
            "documentMode" in document &&
            !!document.documentMode == true 
        )
    );

    /**
     * Is Edge
     * 
     * Edge 20+
     */
    public static isEdge = ():boolean => (navigator.userAgent.indexOf("Edg") != -1);

    /**
     * Is Edge
     * 
     * Edge (based on chromium) detection
     * 
     * @return boolean
     */
    public static isEdgeChromium = ():boolean => this.isChrome() && (navigator.userAgent.indexOf("Edg") != -1);

    /**
     * Blink navigator
     * 
     * Blink engine detection
     * 
     * @returns boolean
     */
    public static isBlink = ():boolean => (this.isChrome() || this.isOpera()) && !!window.CSS;

    /**
     * Get Current Navigator
     * 
     * @return string
     */
    public static get = ():string => {

        // Set result
        let result:string = "";

        // Get all methods of current class starting by is
        let currentMethods:string[] = Object
            .getOwnPropertyNames(this)
            .filter(function(item) {
                return item.startsWith("is");
            })
        ;

        // Iteration of currentMethods
        for(let is of currentMethods){

            // Check if current assert is true
            if(this[is]()){

                // Return 
                result = is.substring(2).toLowerCase();

                // Stop loop
                break;

            }

        }

        // Check result
        if(!result)

            // New error
            throw new Error('Navigator isn\'t implement in Crazy Php');

        // Return result
        return result;

    }

}