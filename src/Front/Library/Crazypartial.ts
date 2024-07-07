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
import Pageregister from "./Pageregister";
import Crazyrequest from "./Crazyrequest";
import { Crazyobject } from "../Types";

/**
 * Crazy Partial
 *
 * Methods for build your partial script
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
export default abstract class Crazypartial {

    /** Parameters
     ******************************************************
     */

    public input:RegisterPartialScanned;

    /** 
     * @param html:string 
     * Duplicate of the class name because build change name of class
     */
    public static readonly html:string|null|CallableFunction = null;

    /**
     * Constructor
     * 
     * @param input
     */
    public constructor(input:RegisterPartialScanned){

        // Set input
        this.input = input;

    }

    /** Public methods
     ******************************************************
     */

    /**
     * Enable
     */
    public enable = () => {

    }

    /**
     * Enable
     */
    public disable = () => {

    }

}