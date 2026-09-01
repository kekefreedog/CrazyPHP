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
import {default as PageError} from './../Error/Page';

/**
 * Csv
 *
 * Manipulate Csv file
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
export default class Csv {

    /**
     * Render
     * 
     * Render csv
     * 
     * @param results 
     * @returns {string}
     */
    public static render(results:any[]):string {

        // Set result
        let result = "";

        // Check result
        if(results.length){

            // This function converts your results array into CSV format
            const escapeField = (field: any) => {

                // Check if string
                if(typeof field === 'string' && (field.includes(',') || field.includes('\n') || field.includes('"')))

                    // Enclose in quotes and escape existing quotes
                    return `"${field.replace(/"/g, '""')}"`; 
                    
                // Return field
                return field;

            };

            // Set headers
            const headers = Object.keys(results[0]).join(',');
            
            // Set rows
            const rows = results.map(obj => Object.values(obj).map(escapeField).join(','));

            // Set result
            result = [headers, ...rows].join('\n');

        }

        // Return result
        return result;

    }

    /**
     * Download
     * 
     * @param object 
     * @param filename 
     * @return {void}
     */
    public static download = (object:any[], filename:string):void => {

        // Get csv string
        let csvString:string = Csv.render(object);

        // Create a Blob from the CSV String
        const blob = new Blob([csvString], { type: 'text/csv;charset=utf-8;' });

        // Create a link element
        const link = document.createElement("a");

        // Create a URL for the blob
        const url = URL.createObjectURL(blob);
        link.setAttribute("href", url);
        link.setAttribute("download", filename);

        // Append the link to the body
        document.body.appendChild(link);

        // Programmatically click the link to trigger the download
        link.click();

        // Remove the link after starting the download
        document.body.removeChild(link);

    }

}