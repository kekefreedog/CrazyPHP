/**
 * Partials
 *
 * Front TS Scrips for partials components
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */

/**
 * Dependances
 */
import { Crazypartial } from "crazyphp";

/**
 * Hello
 *
 * Script of the partial Hello
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */
export default class Hello extends Crazypartial {

    /**
     * Constructor
     */
    public constructor(input:RegisterPartialScanned){

        // Parent constructor
        super(input);

        console.log("Partial hello loaded");
    
    }

}