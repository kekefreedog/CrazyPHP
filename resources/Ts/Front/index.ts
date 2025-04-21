/**
 * Index
 *
 * Index of the front script
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */

/** Crazy Object
 ******************************************************
 */
 import {Crazyobject, Crazylanguage} from "crazyphp";

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
//import FullScreenContainer from "./../Environment/Component/FullScreenContainer";
import LoadingScreenBtn from "./../Environment/Component/LoadingScreenBtn";
import RegularBtn from "./../Environment/Component/RegularBtn";
let globalComponentsCollection = {
    //"full-screen-container": FullScreenContainer,
    "loading-screen-btn": LoadingScreenBtn,
    "regular-btn": RegularBtn
};

/** Crazy Global Partials
 ******************************************************
 * Declare only compenent to load on all page of your app
 */
import PreloaderLinearIndeterminate from "../Environment/Partials/PreloaderLinearIndeterminate";
import Navigation from "../Environment/Partials/Navigation";
import Hello from "../Environment/Partials/Hello";
import Form from "../Environment/Partials/Form";
let globalPartials = {
   "preloader_linear_indeterminate": PreloaderLinearIndeterminate,
   "navigation": Navigation,
   "hello": Hello,
   "form": Form,
};

/** Crazy Global Alerts
 ******************************************************
 * Declare only alert instance to load on all page of your app
 * > First driver is considered like the one by default to user
 */
 let globalAlerts = {
 }

/** Actions
 ******************************************************
 */

/** @var Crazyobject:Crazyobject */
window.Crazyobject = new Crazyobject({
    globalComponentsCollection: globalComponentsCollection,     
    globalStateCollection: {
       "language": Crazylanguage.getNavigatorLanguage()
    },
    globalPartials: globalPartials
});