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

/**
 * Path
 *
 * Methods for process path
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
export default class Path {

    /** Private static methods
     ******************************************************
     */

    /**
     * Normalize Separators
     *
     * Convert Windows "\" separators to "/"
     *
     * @param input
     * @returns {string}
     */
    private static normalizeSeparators = (input:string = ""):string => {

        // Set result
        let result:string = input.replace(/\\/g, "/");

        // Return result
        return result;

    }

    /** Public static methods
     ******************************************************
     */

    /**
     * Dirname
     *
     * Return folder path from file path
     *
     * @param input
     * @returns {string}
     */
    public static dirname = (input:string = ""):string => {

        // Set result
        let result:string = "";

        // Normalize
        const path = Path.normalizeSeparators(input);

        // Get index
        const index:number = path.lastIndexOf("/");

        // Check index
        if(index !== -1)

            // Set result
            result = path.substring(0, index);

        // Return result
        return result;

    }

    /**
     * Basename
     *
     * Return filename from path
     *
     * @param input
     * @param ext
     * @returns {string}
     */
    public static basename = (input:string = "", ext:string = ""):string => {

        // Set result
        let result:string = "";

        // Normalize
        const path = Path.normalizeSeparators(input);

        // Extract name
        result = path.substring(path.lastIndexOf("/") + 1);

        // Remove extension
        if(ext && result.endsWith(ext))

            // Set result
            result = result.substring(0, result.length - ext.length);

        // Return result
        return result;

    }

    /**
     * Extname
     *
     * Return extension from path
     *
     * @param input
     * @returns {string}
     */
    public static extname = (input:string = ""):string => {

        // Set result
        let result:string = "";

        // Get basename
        const base:string = Path.basename(input);

        // Find extension
        const index:number = base.lastIndexOf(".");

        // Check index
        if(index > 0)

            // Set result
            result = base.substring(index);

        // Return result
        return result;

    }

    /**
     * Join
     *
     * Join multiple path segments
     *
     * @param inputs
     * @returns {string}
     */
    public static join = (...inputs:string[]):string => {

        // Set result
        let result:string = inputs
            .filter(Boolean)
            .join("/")
            .replace(/\/+/g, "/");

        // Return result
        return result;

    }

    /**
     * Normalize
     *
     * Normalize path
     *
     * @param input
     * @returns {string}
     */
    public static normalize = (input:string = ""):string => {

        // Set result
        let result:string = "";

        // Normalize separators
        let path:string = Path.normalizeSeparators(input);

        // Split segments
        const segments:string[] = path.split("/");

        // Set stacks
        const stack:string[] = [];

        // Iteration
        segments.forEach(segment => {

            // Check segements
            if(segment === "" || segment === ".")

                // Return
                return;

            // Check
            if(segment === "..")

                // Set stack
                stack.pop();

            // Push into stacks
            else
                stack.push(segment);

        });

        // Set result
        result = stack.join("/");

        // Return result
        return result;

    }

    /**
     * Is Absolute
     *
     * Check if path is absolute
     *
     * @param input
     * @returns {boolean}
     */
    public static isAbsolute = (input:string = ""):boolean => {

        // Set result
        let result:boolean = false;

        // Normalize
        const path:string = Path.normalizeSeparators(input);

        // Check
        if(path.startsWith("/") || /^[A-Za-z]:\//.test(path))

            // Set result
            result = true;

        // Return result
        return result;

    }

    /**
     * Parse
     *
     * Parse path into components
     *
     * @param input
     * @returns {object}
     */
    public static parse = (input:string = ""):any => {

        // Set result
        let result:any = {
            root: "",
            dir: "",
            base: "",
            ext: "",
            name: ""
        };

        // Set result
        result.dir = Path.dirname(input);

        // Set base
        result.base = Path.basename(input);

        // Set ext
        result.ext = Path.extname(input);

        // Check ext
        if(result.ext)

            // Set name
            result.name = result.base.substring(0, result.base.length - result.ext.length);

        // Else
        else

            // Set name
            result.name = result.base;

        // Return result
        return result;

    }

    /**
     * Relative
     *
     * Return relative path from one path to another
     *
     * @param from
     * @param to
     * @returns {string}
     */
    public static relative = (from:string = "", to:string = ""):string => {

        // Set result
        let result:string = "";

        // Set from parts
        const fromParts:string[] = Path.normalize(from).split("/");
        
        // Set to parts
        const toParts:string[] = Path.normalize(to).split("/");

        // Remove common parts
        while(fromParts.length && toParts.length && fromParts[0] === toParts[0]){

            // Set from parts
            fromParts.shift();
            
            // Set shifts
            toParts.shift();
        }

        // Set up
        const up:string[] = new Array(fromParts.length).fill("..");

        // Set result
        result = [...up, ...toParts].join("/");

        // Return result
        return result;

    }

}