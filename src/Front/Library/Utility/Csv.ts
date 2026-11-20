/**
 * Utility
 *
 * Render CSV data and optionally protect spreadsheet exports from formula injection.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2026 Kévin Zarshenas
 */

/**
 * Csv
 *
 * Serialize fields once at the export boundary without changing source records.
 */
export default class Csv {

    /** Public Static Methods
     ******************************************************
     */

    /**
     * Render
     *
     * Spreadsheet protection prefixes suspicious text with an apostrophe and quotes all fields.
     * Native numbers remain numeric; headers follow the same policy as cell values.
     *
     * @param results Ordered records to export
     * @param options Optional spreadsheet protection; ordinary CSV behavior remains the default
     * @returns Serialized CSV
     */
    public static render(results:any[], options:CsvOptions = {}):string {

        // Prepare result
        let result = "";

        // Render nonempty collections
        if(results.length){

            // Apply protection only when the caller explicitly exports for spreadsheet viewing
            const escapeFormulas = options.escapeFormulas === true;

            // Escape column names with the same rules as values
            const headers = Object.keys(results[0]).map(field => Csv._escapeField(field, escapeFormulas)).join(",");

            // Preserve record and column order without mutating their values
            const rows = results.map(record => Object.values(record).map(field => Csv._escapeField(field, escapeFormulas)).join(","));

            // Join complete rows after escaping their embedded line breaks
            result = [headers, ...rows].join("\n");

        }

        // Return result
        return result;

    }

    /**
     * Download
     *
     * @param object Ordered records to export
     * @param filename Browser download filename
     * @param options CSV serialization and spreadsheet protection options
     * @returns Nothing
     */
    public static download = (object:any[], filename:string, options:CsvOptions = {}):void => {

        // Serialize with the requested protection at the final download boundary
        const csvString = Csv.render(object, options);

        // Create the download body
        const blob = new Blob([csvString], {type: "text/csv;charset=utf-8;"});

        // Create a temporary download link
        const link = document.createElement("a");

        // Create a URL for the blob
        const url = URL.createObjectURL(blob);

        // Attach the generated download body
        link.setAttribute("href", url);

        // Preserve the caller's filename
        link.setAttribute("download", filename);

        // Attach the link to the document
        document.body.appendChild(link);

        // Trigger the download
        link.click();

        // Remove the temporary link
        document.body.removeChild(link);

    }

    /** Private Static Methods
     ******************************************************
     */

    /**
     * Escape Field
     *
     * Keep formulas inert on initial spreadsheet import, including whitespace and control prefixes.
     * Re-saving CSV in another application can remove its text marker; CSV has no cell types.
     *
     * @param field Value or header to serialize
     * @param escapeFormulas Whether to protect spreadsheet text
     * @returns One complete CSV field
     */
    private static _escapeField(field:unknown, escapeFormulas:boolean):string {

        // Prepare result using the existing CSV treatment of missing values
        let result = field === null || field === undefined ? "" : String(field);

        // Preserve actual finite numbers while checking every textual representation
        if(escapeFormulas && !(typeof field === "number" && Number.isFinite(field))){

            // Cover formula markers, full-width variants and ignored leading whitespace or controls
            const formula = /^[\s\u0000-\u001f\u007f-\u009f\u200b-\u200f\u202a-\u202e\u2060-\u206f]*[=+@\-\uff1d\uff0b\uff0d\uff20]/u.test(result);

            // Treat leading control characters as text even without a visible formula marker
            if(formula || /^[\u0000-\u001f\u007f-\u009f]/u.test(result)){

                // Place the text marker before the original value without trimming or deleting content
                result = "'" + result;

            }

        }

        // Quote protected fields and all CSV syntax, including lone carriage returns
        if(escapeFormulas || /[,"\r\n]/u.test(result)){

            // Escape embedded quotes exactly once inside the enclosing quotes
            result = `"${result.replace(/"/g, '""')}"`;

        }

        // Return result
        return result;

    }

}

/**
 * CSV Options
 */
export interface CsvOptions {

    /** Protect untrusted spreadsheet text; prefixes may remain visible in other CSV consumers. */
    escapeFormulas?:boolean;

}
