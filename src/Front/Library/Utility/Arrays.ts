/**
 * Utility
 *
 * Front TS Scrips for multiple tasks
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */

/**
 * Dependances
 */

/**
 * Arrays
 *
 * Methods for manage arrays
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
export default class Arrays {

    /** Public static methods
     ******************************************************
     */

    /**
     * Array Filter
     * 
     * @return any
     */
    public static filterByKey = (array:Array<any> = [], key:string, keyValue:string) => array.filter(
        (aEl) => aEl[key] == keyValue
    );
    
}