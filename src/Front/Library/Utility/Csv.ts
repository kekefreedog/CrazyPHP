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
        // This function converts your results array into CSV format
        // It handles strings containing commas or line breaks by enclosing them in quotes
        // and doubles any quotes within the strings.

        const escapeField = (field: any) => {
            if (typeof field === 'string' && (field.includes(',') || field.includes('\n') || field.includes('"'))) {
                return `"${field.replace(/"/g, '""')}"`; // Enclose in quotes and escape existing quotes
            }
            return field;
        };

        const headers = Object.keys(results[0]).join(',');
        const rows = results.map(obj => 
            Object.values(obj).map(escapeField).join(',')
        );

        return [headers, ...rows].join('\n');
    }

    /**
     * Donwload
     * 
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