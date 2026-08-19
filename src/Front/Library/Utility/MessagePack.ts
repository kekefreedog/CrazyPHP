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
import { gzip, ungzip } from 'pako';
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
     * @param gzipBrotli Gzip the packed result
     * @returns {ArrayBuffer}
     */
    public static stringify = (input:any, gzipBrotli:boolean = false):Buffer|Uint8Array => {

        // Pack input
        let result:Buffer|Uint8Array = pack(input);

        // Check gzip
        if(gzipBrotli)

            // Gzip packed result
            result = gzip(result as Uint8Array);

        // Return result
        return result;

    }

    /**
     * Parse
     *
     * Parse message pack string
     *
     * @param input to parse
     * @param gzipBrotli Ungzip the input before parsing
     * @returns {string}
     */
    public static parse = (input:Buffer|Uint8Array, gzipBrotli:boolean = false):any => {

        // Check
        let result = null;

        // Check input
        if(input){

            // Check gzip
            let packed:Buffer|Uint8Array = gzipBrotli
                ? ungzip(input as Uint8Array)
                : input
            ;

            // Unpack result
            result = unpack(packed);

        }

        // Return result
        return result;

    }

}