/**
 * Page
 *
 * Pages of your app
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */

/**
 * Dependances
 */
import {Crazypage} from "crazyphp";
require("./template.hbs");
//require("./style.scss");

/**
 * Crazy Object
 *
 * Methods for build your front interface
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
export default class Index extends Crazypage {

    /**
     * Constructor
     */
    public constructor(){

        /**
         * Parent constructor
         */
        super();

    }

}

/**
 * Register current class
 */
window.Crazyobject.pages.register(Index);