/**
 * Front
 *
 * Front TS Scrips for your Crazy App
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */

/**
 * Python Collection
 *
 * Methods for parse Python Collection
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
export default class PythonCollection {

    /** Methods | Global
     ******************************************************
     */

    
    /**
     * check
     * 
     * Check if input is python collection
     * @returns {boolean}
     */
    public static check = (input:string):boolean => {

        // Set result
        let result:boolean = false;

        // Trim any leading or trailing whitespace
        const trimmedInput = input.trim();

        // Check for a dictionary (e.g., {'key': 'value'})
        const isDict = trimmedInput.startsWith("{") && trimmedInput.endsWith("}");

        // Check for a list (e.g., ['value1', 'value2'])
        const isList = trimmedInput.startsWith("[") && trimmedInput.endsWith("]");

        // A valid Python collection should also contain at least one colon (for dict) or comma (for both)
        const hasDictColon = isDict && trimmedInput.includes(":");
        const hasListComma = isList && trimmedInput.includes(",");

        // Ensure the string is not empty inside the braces
        const isNotEmpty = trimmedInput.length > 2;

        // Return true if it is either a dictionary or a list
        result = (isDict && hasDictColon && isNotEmpty) || (isList && hasListComma && isNotEmpty);

        // Return result
        return result;

    }

    /**
     * Encode
     * 
     * @param input 
     * @returns 
     */
    public static encode = (input:any):string => {

        // Set result
        let result = "";

        // Check input is null
        if(input === null)

            // Set result
            result = "None";

        else
        // Check input is bool
        if(typeof input === "boolean")

            // Set result
            result = input ? "True" : "False";
    
        else
        // If number of string
        if(typeof input === "number")

            // handles quotes and escape sequences correctly
            result = JSON.stringify(result); 
    
        else
        // If is string
        if(typeof input === "string"){

            // Escape single quotes and backslashes within strings
            const escapedStr = input
                // Escape backslashes
                .replace(/\\/g, '\\\\')
                // Escape single quotes
                .replace(/'/g, "\\'")
            ;  

            // Set result
            result = `'${escapedStr}'`;

        }else
        // If array given
        if(Array.isArray(input)) {

            // Set elements
            const elements = input.map(element => PythonCollection.encode(element));

            // Set result
            result = `[${elements.join(", ")}]`;
        
        }else
        // Object given
        if(typeof input === "object"){

            // Map entries
            const entries = Object.entries(input)
                .map(([key, value]) => {
                    
                    // Ensure keys are properly formatted and escaped
                    const escapedKey = key
                        // Escape backslashes in keys
                        .replace(/\\/g, '\\\\')
                        // Escape single quotes in keys
                        .replace(/'/g, "\\'");

                    // Set python key
                    const pythonKey = typeof escapedKey === "string" 
                        ? `'${escapedKey}'` 
                        : escapedKey
                    ;

                    // Set python value
                    const pythonValue = PythonCollection.encode(value);

                    // Return result
                    return `${pythonKey}: ${pythonValue}`;

                });
            
            // Set result
            result = `{${entries.join(", ")}}`;

        }
    
        // Return result
        return result;

    }

    /**
     * Decode
     * 
     * @param input 
     * @returns 
     */
    public static decode = (input:string):any => {

        // Set result
        let result = "";

        // Check if python collection
        if(PythonCollection.check(input)){

            // Placeholder for \\'
            let modifiedInput = input.replace(/\\'/g, '@@@@@@@@');

            // Escape double quotes inside single-quoted values
            modifiedInput = modifiedInput.replace(/'[^']*\"[^']*'/g, (match) => {
                return match.replace(/"/g, '\\"');
            });

            // Convert Python-like collection string to JSON-compatible string
            let jsonString = modifiedInput
                .replace(/([{,]\s*)'([^']+)':\s*'([^']*)'/g, '$1"$2": "$3"') // Handles key-value pairs
                .replace(/([{,]\s*)'([^']+)':\s*([^'{][^,]*)/g, '$1"$2": $3') // Handles other key-value pairs
                .replace(/'/g, '"') // Replaces single quotes with double quotes
                .replace(/\\\\/g, '\\\\'); // Escapes backslashes

            // Replace placeholder by '
            jsonString = jsonString.replace(/@@@@@@@@/g, "'");

            // Handle Python None, True, and False values
            jsonString = jsonString
                .replace(/None/g, 'null')
                .replace(/True/g, 'true')
                .replace(/False/g, 'false');

            // Step 3: Parse the string to JSON
            result = JSON.parse(jsonString);

        }

        // Return result
        return result;

    }

}