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
import {CustomColor, Theme, argbFromHex, themeFromSourceColor, applyTheme} from "@material/material-color-utilities";
import {default as ColorSchema} from './Utility/ColorSchema';

/**
 * Crazy Color
 *
 * Methods for manage color and theme
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
export default class Crazycolor {

    /** Parameters
     ******************************************************
     */

    /**
     * Theme
     * @var theme
     */
    private theme:Theme|null = null;

    /**
     * Constructor
     * @param source:string
     */
    public constructor(source:string){

        // Check source
        if(source)

            // Set new theme
            this.setNewTheme(source);


    }

    /**
     * Set new theme
     * 
     * @param source:string
     */
    public setNewTheme = (source:string = "#4caf50", CustomColors?:CustomColor[]) =>{

        // Check source
        if(source)

            // Set theme
            this.theme = themeFromSourceColor(argbFromHex(source), CustomColors);

    }

    /**
     * Get theme
     * 
     * @returns {Theme|null}
     */
    public getTheme = ():Theme|null => {

        // Return theme
        return this.theme;

    }

    /**
     * Get Theme As Json
     * 
     * @return {string}
     */
    public getThemeAsJson = ():string => {

        // Set result
        let result = "";

        // Get theme
        let theme = this.getTheme();

        // Check theme
        if(theme)

            // Set result
            result = JSON.stringify(theme, null, 2);

        // Return result
        return result;

    }

    /**
     * Apply Theme
     * 
     * @return {boolean}
     */
    public applyTheme = ():boolean => {

        // Set result
        let result = false;

        // Check theme
        if(this.theme){

            // Apply theme
            applyTheme(
                this.theme, 
                {
                    target: document.body, 
                    dark: ColorSchema.getTheme() == "dark" ? true : false,
                }
            );

            // Set theme
            result = true;

        }

        // Get dark mode preference
        const darkModePreference = window.matchMedia("(prefers-color-scheme: dark)");

        // Detect change
        darkModePreference.addEventListener("change", this._onColorModeChange);

        // Get light mode preference
        const lightModePreference = window.matchMedia("(prefers-color-scheme: light)");

        // Detect change
        lightModePreference.addEventListener("change", this._onColorModeChange);

        // Return result
        return result;

    }

    /** Private methods
     ******************************************************
     */

    /**
     * On Dark Mode
     * 
     * @param e:Event
     * @returns {void}
     */
    private _onColorModeChange = (e:Event):void => {

        if("matches" in e && e.matches && this.theme)

            // Apply theme
            applyTheme(
                this.theme, 
                {
                    target: document.body, 
                    dark: ColorSchema.getTheme() == "dark" ? true : false,
                }
            );

    }


}