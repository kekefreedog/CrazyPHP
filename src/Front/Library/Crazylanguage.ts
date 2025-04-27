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
 * Dependances
 */
import Crazynavigator from "./Crazynavigator";

/**
 * Language 
 *
 * Methods for manage language
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
export default class Crazylanguage {

    /** Public static methods
     ******************************************************
     */

    /**
     * 
     * @param shortResponse Return short version of language "fr" else "fr-FR"
     */
    public static getNavigatorLanguage(shortResponse:boolean = true):string {

        // Set result
        let result:string = shortResponse ? "en" : "en-US";

        // Declare variables
        let shortLanguage:string = "";
        let longLanguage:string = "";

        // Get current navigator
        let currentNavigator:string = Crazynavigator.get();

        // Check current Navigator
        if(["firefox"].includes(currentNavigator)){

            // Get languages
            if(window.navigator.languages.length === 2){

                // Get tempShort
                let tempLong = window.navigator.languages.at(0);

                // Get temp short
                let tempShort = window.navigator.languages.at(1);

                // Check temp long
                if(typeof tempLong === "string" && tempLong)

                    // Set long
                    longLanguage = tempLong;

                // Check temp short
                if(typeof tempShort === "string" && tempShort)

                    // Set short
                    shortLanguage = tempShort;

            }else
            // Get languages
            if(window.navigator.languages.length === 1){

                // Get tempShort
                let tempLong = window.navigator.languages.at(0);

                // Get temp short
                let tempShort = window.navigator.languages.at(0)?.split("-").at(0);

                // Check temp long
                if(typeof tempLong === "string" && tempLong)

                    // Set long
                    longLanguage = tempLong;

                // Check temp short
                if(typeof tempShort === "string" && tempShort)

                    // Set short
                    shortLanguage = tempShort;

            }

        }else
        if(["chrome"].includes(currentNavigator)){

            // Get current language
            longLanguage = window.navigator.language;

            // Set short langLanguage
            shortLanguage = longLanguage.split("-").at(0) ?? "";

        }else
        if("language" in window.navigator && window.navigator.language){

            // Get current language
            longLanguage = window.navigator.language;

            // Set short langLanguage
            shortLanguage = longLanguage.split("-").at(0) ?? "";

        }else{

            // Get current language
            longLanguage = "en-US";
            
            // Set short langLanguage
            shortLanguage = "en";

        }

        // Set result
        result = shortResponse ? shortLanguage : longLanguage;

        // Return result
        return result;

    }

    // window.navigator.language

}