/**
 * Index
 *
 * Index of the front script
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */

/** Styles
 ******************************************************
*/
require("./style");

/** JS Libraires
 ******************************************************
*/
require("@materializecss/materialize/dist/js/materialize.js");

document.addEventListener(
    "click", 
    e => {
        console.log(e);
    },
    { capture: true }
);