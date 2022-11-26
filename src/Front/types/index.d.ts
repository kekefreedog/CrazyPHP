/**
 * Index
 *
 * Index of the front script
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */

/**
 * Dependances
 */
import Crazyobject from "./../Crazyobject";

// Export
export {};

// Declare GLobal type
declare global {

    /**
     * Interface of Window
     */
    interface Window {

        // Add Crazyobject
        Crazyobject: Crazyobject;

    }

    /**
     * Interface of CrazyObjectInput
     */
    interface CrazyObjectInput {
        globalComponentsCollection:Object;
    }

    /**
     * Interface Crazyelement Style
     */
    interface CrazyelementStyle {
        default:any
    }

}
