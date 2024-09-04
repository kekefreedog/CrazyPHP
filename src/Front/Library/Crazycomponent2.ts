/**
 * Front
 *
 * Front TS Scrips for your Crazy App
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */

import { strict } from "assert";

/**
 * Crazycomponenet2
 *
 * Usefull functions / classes / const for manipulate Web Component
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
export default abstract class Crazycomponent2 extends HTMLElement {

    /** Private parameters
     ******************************************************
     */

    /**
     * @var _properties
     */
    private _properties:null|Record<string,interfaceCrazycomponent2Propoeties> = null;

    /**
     * @var _html
     */
    private _html:string|(()=>string) = "";

    /**
     * @var _style
     */
    private _style:string|(()=>string) = "";

    /**
     * Constructor 
     */
    constructor() {

        // Parent constructor
        super();

        // Get child properties
        this._properties = this._getProperties();

        // Get html
        this._html = this._getHtml();

        // Get style
        this._style = this._getStyle();

    }

    /** Private methods
     ******************************************************
     */

    /**
     * Get Child Properties
     * 
     * @returns {Record<string,interfaceCrazycomponent2Propoeties>|null}
     */
    private _getProperties = ():Record<string,interfaceCrazycomponent2Propoeties>|null => {

        // Set result
        let result:Record<string,interfaceCrazycomponent2Propoeties>|null = null;

        // Check properties
        if("properties" in (this as any))

            // Get properties
            result = (this as any).properties as Record<string,interfaceCrazycomponent2Propoeties>;

        // Return result
        return result;

    }

    /**
     * Get Html
     * 
     * @returns {(()=>string)|string}
     */
    private _getHtml = ():(()=>string)|string => {

        // Set html
        let result = "";

        // Return result
        return result;

    }

    /**
     * Get Style
     * 
     * @returns {(()=>string)|string}
     */
    private _getStyle = ():(()=>string)|string => {

        // Set html
        let result = "";

        // If result
        if(result)

            // Set result
            result = `<style>${result}</style>`;

        // Return result
        return result;

    }

}

/** Interface
 ******************************************************
 */

/**
 * interfaceCrazycomponent2Propoeties
 */
interface interfaceCrazycomponent2Propoeties {
    // Enable attribute
    attribute: boolean
    // Type of attribute
    type: "String"|"Number"|"Boolean"|"Array"|"Object",
}