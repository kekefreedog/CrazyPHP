/**
 * Index
 *
 * Index of the front script
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */

/** Crazy Object
 ******************************************************
 */
import Crazyobject from "./../../vendor/kzarshenas/crazyphp/src/Front/Crazyobject";

/** Styles
 ******************************************************
 */
require("./style");

/** JS Libraires
 ******************************************************
 */
require("./library");

/** TS|JS Libraires
 ******************************************************
 */

/** Crazy GlobalWebcomponents
 ******************************************************
 * Declare only compenent to load on all page of your app
 */
import LoadingScreenBtn from "./../Environment/Component/LoadingScreenBtn";
let globalComponentsCollection = {
     "loading-screen-btn": LoadingScreenBtn,
 };

/** Actions
 ******************************************************
 */

/** @var Crazyobject:Crazyobject */
window.Crazyobject = new Crazyobject({
    globalComponentsCollection: globalComponentsCollection,
});