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
 * Events
 *
 * Methods for store custom events
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
export default class ColorSchema {


    /** Construct
     ******************************************************
     */

    constructor(){

        // Get stored them
        let storedTheme = ColorSchema.getTheme();

        // Check document already loaded
        if(document.readyState !== 'loading') {

            // Set color theme
            ColorSchema.setTheme(storedTheme);

        }else

            // Check if local storage
            document.addEventListener("DOMContentLoaded", () => {
                
                // Set color theme
                ColorSchema.setTheme(storedTheme);

            });

        // Get dark mode preference
        const darkModePreference = window.matchMedia("(prefers-color-scheme: dark)");

        // Detect change
        darkModePreference.addEventListener("change", e => e.matches && ColorSchema.setTheme(ColorSchema.get()));

        // Get light mode preference
        const lightModePreference = window.matchMedia("(prefers-color-scheme: light)");

        // Detect change
        lightModePreference.addEventListener("change", e => e.matches && ColorSchema.setTheme(ColorSchema.get()));

    }

    /** Public static methods
     ******************************************************
     */

    /**
     * Get
     * 
     * Get color schema
     * 
     * @source https://stackoverflow.com/questions/56393880/how-do-i-detect-dark-mode-using-javascript
     * 
     * @return 'dark'|'light'
     */
    public static get = ():'dark'|'light' => window?.matchMedia?.('(prefers-color-scheme:dark)')?.matches ? 'dark' : 'light';

    /**
     * Set Theme
     * 
     * Set theme of the page
     * 
     * @param theme:'dark'|'light'
     * @param useLocalStorage:boolean
     * 
     * @return void
     */
    public static setTheme = (theme:'dark'|'light', useLocalStorage:boolean = false):void => {

            // Set on html
            document.documentElement.setAttribute('theme', theme);

            // If useLocalStorage
            if("localStorage" in window && useLocalStorage)

                // Set in local storage
                window.localStorage.setItem('crazy-theme', theme);

    }

    /**
     * Get Theme
     * 
     * Get them of the page
     * 
     * @param useLocalStorage:boolean
     * @return 'dark'|'light'
     */
    public static getTheme = (useLocalStorage:boolean = false):'dark'|'light' => {
        
        // Result
        let result:'dark'|'light' = ColorSchema.get();

        // if useLocalStorage
        if("localStorage" in window && useLocalStorage){

            // Get in local storage
            let localStorageValue = localStorage.getItem('crazy-theme') || ColorSchema.get();

            // Check value is light or dark
            if(localStorageValue in ['dark','light'])

                // Set result
                result = localStorageValue as 'dark'|'light';

        }

        // Return result
        return result;

    }

    /**
     * RGB to HEX
     * 
     * @param r 
     * @param g 
     * @param b 
     * @returns {string}
     */
    public static rgbToHex = (r:number|string, g:number|string, b:number|string):string => {
        return "#" + ColorSchema.componentToHex(r) + ColorSchema.componentToHex(g) + ColorSchema.componentToHex(b);
    }

    /**
     * Create Vertical Gradient
     * 
     * @param colors 
     * @param smooth 
     * @returns 
     */
    public static createVerticalGradient = (colors: string[], smooth:boolean = true):string => {

        if (smooth) {
            // Smooth gradient with normal blending between colors
            const colorStops = colors
              .map((color, index) => `rgba(${color}) ${(index / (colors.length - 1)) * 100}%`)
              .join(', ');
            
            return `background: linear-gradient(to bottom, ${colorStops});`;
          } else {
            // Hard stop gradient without blending between colors
            const colorStops = colors
              .map((color, index) => {
                const position = (index / colors.length) * 100;
                return `rgba(${color}) ${position}%, rgba(${color}) ${(position + 100 / colors.length)}%`;
              })
              .join(', ');
        
            return `linear-gradient(to bottom, ${colorStops});`;
          }

      }

    /** Private static methods
     ******************************************************
    */

    /**
     * Component To Hex
     * @param c 
     * @returns 
     */
    private static componentToHex = (c:number|string):string => {
        var hex = c.toString(16);
        return hex.length == 1 ? "0" + hex : hex;
    }

}