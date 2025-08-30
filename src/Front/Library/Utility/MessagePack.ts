/**
 * Utility
 *
 * Front TS Scrips for multiple tasks
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */

/**
 * Dependances
 */
import { unpack, pack } from 'msgpackr';

/**
 * Arrays
 *
 * Methods for manage message pack
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
export default class MessagePack {

    /** Public static methods
     ******************************************************
     */

    /**
     * Stringify
     * 
     * Convert to message pack string
     * 
     * @param input to stringify
     * @returns {ArrayBuffer}
     */
    public static stringify = (input:any):Buffer|Uint8Array => {

        let result = pack(input);

        // Return result
        return result;

    }

    /**
     * Parse
     * 
     * Parse message pack string
     * 
     * @param input to parse
     * @returns {string}
     */
    public static parse = (input:Buffer|Uint8Array):any => {

        // Check 
        let result = null;

        // Check input
        if(input)

            result = unpack(input);

        // Return result
        return result;

    }

}