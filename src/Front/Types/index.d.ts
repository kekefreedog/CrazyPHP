/**
 * Index Type
 *
 * Index of the front script for declare types
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */

/**
 * Dependances
 */
export {default as Componentregister} from "./../Library/Componentregister";
export {default as Crazycomponent} from "./../Library/Crazycomponent";
export {default as Configregister} from "./../Library/Configregister";
export {default as Crazynavigator} from "./../Library/Crazynavigator";
export {default as Crazylanguage} from "./../Library/Crazylanguage";
export {default as Crazyconsole} from "./../Library/Crazyconsole";
export {default as Crazyrequest} from "./../Library/Crazyrequest";
export {default as Crazyelement} from "./../Library/Crazyelement";
export {default as Pageregister} from "./../Library/Pageregister";
export {default as Crazyevents} from "./../Library/Crazyevents";
export {default as LoaderPage} from "./../Library/Loader/Page";
export {default as Crazycache} from "./../Library/Crazycache";
export {default as Crazyobject} from "./../Core/Crazyobject";
export {default as Crazypage} from "./../Library/Crazypage";

/* Modules to export */
/* export {}; */

// Declare GLobal type
declare global {

    /**
     * Crazy Fetch Option
     */
    interface CrazyFetchOption {
        method?: "get"|"GET"|"post"|"POST"|"put"|"PUT"|"delete"|"DELETE",
        header?: object,
        cache?: "local"|"session"|boolean,
        responseType?: "arraybuffer"|"document"|"json"|"text"|"stream"|boolean,
        responseEncoding?: "utf8",
        from?: "internal"|"external",
        ignoreHash?: boolean
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
        default:any;
    }

    /**
     * Interface response
     */
    interface Response {
        value:any;
    }

    /**
     * Crazy Page
     */
    interface Crazypage {

        /** Name */
        name:string;
    }
    /**
     * String Crazy Page
     */
    interface StringCrazyPage {
        new():Crazypage;
    }

    /**
     * Crazy Component Options
     */
    interface CrazycomponentOptions {
        allowChildNodes:boolean;
    }

    /**
     * Redirect By Name Options
     */
    interface RedirectByNameOptions {
        arguments?: Object;
        mimetype?: "json"
    }

    /**
     * Router Response Schema 
     */
    interface RouterResponseSchema {
        name?:string,
        path?:string
    }

    /** Interface | Loader
     ******************************************************
     */

    /**
     * Interface LoaderPageOptions
     */
    interface LoaderPageOptions {
        name?:?string = null,
        url?:?URL = null,
        arguments?:?Object = null,
        instance?:?Crazypage = null,
        content?:?CallableFunction|string = null,
        style?:?CallableFunction|string = null,
        preAction?:?CallableFunction = null,
        postAction?:?CallableFunction = null,
        status?:?LoadPageOptionsStatus = null,
        scriptLoaded?:?Array<string> = null,
        hash?:?string = null,
        eventsRegistered?:?Array<Object> = null,
        componentsRegistered?:?Array<Object> = null
    }

    /**
     * Interface LoadPageOptionsStatus
     */
    interface LoadPageOptionsStatus {
        active:boolean = false,
        preActionLoaded:boolean = false,
        postActionLoaded:boolean = false,
        scriptLoaded:boolean = false,
        styleLoaded:boolean = false,
        contentLoaded:boolean = false,
    }

}