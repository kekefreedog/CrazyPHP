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
        let shortLanguage:string = "en";
        let longLanguage:string = "en-US";

        // Get current navigator
        let currentNavigator:string = Crazynavigator.get();

        // Check current Navigator
        if(["firefox"].includes(currentNavigator)){
            
            // Short already set
            let shortÀlreadySet = false;

            // Check window.navigator.languages
            if(window.navigator.languages.length){

                // Iteration languages
                for(let language of window.navigator.languages){

                    // Check if language has "-"
                    if(language.includes("-")){

                        // Set long
                        longLanguage = language;

                        // Set short
                        if(!shortÀlreadySet) shortLanguage = language.split("-")?.at(0) 
                            ? language.split("-").at(0) as string 
                            : shortLanguage
                        ;

                        // Break
                        break;

                    }else{

                        // Set short
                        shortLanguage = language;

                        // Set short
                        shortÀlreadySet = true;

                    }

                }

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