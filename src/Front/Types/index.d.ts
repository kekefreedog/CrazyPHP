/**
 * Index Type
 *
 * Index of the front script for declare types
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */

import Crazyobject from "./../Core/Crazyobject";
import Crazycolor from "./../Library/Crazycolor";
import Crazypage from "./../Library/Crazypage";
import Crazypartial from "./../Library/Crazypartial";

/**
 * Dependances
 */
export {default as PythonCollection} from "./../Library/File/PythonCollection";
export {default as Componentregister} from "./../Library/Componentregister";
export {default as NavigatorClient} from "./../Library/Navigator/Client";
export {default as UtilityDateTime} from "./../Library/Utility/DateTime";
export {default as ColorSchema} from "./../Library/Utility/ColorSchema";
export {default as UtilityProcess} from "./../Library/Utility/Process";
export {default as UtilityObjects} from "./../Library/Utility/Objects";
export {default as UtilityStrings} from "./../Library/Utility/Strings";
export {default as Crazycomponent} from "./../Library/Crazycomponent";
export {default as Crazynavigator} from "./../Library/Crazynavigator";
export {default as UtilityArrays} from "./../Library/Utility/Arrays";
export {default as UtilityEvents} from "./../Library/Utility/Events";
export {default as UtilityRunner} from "./../Library/Utility/Runner";
export {default as LoaderPartial} from "./../Library/Loader/Partial";
export {default as Crazylanguage} from "./../Library/Crazylanguage";
export {default as UtilityMoney} from "./../Library/Utility/Money";
export {default as RegisterPage} from "./../Library/Register/Page";
export {default as Crazyconsole} from "./../Library/Crazyconsole";
export {default as Crazyrequest} from "./../Library/Crazyrequest";
export {default as Crazyelement} from "./../Library/Crazyelement";
export {default as Pageregister} from "./../Library/Pageregister";
export {default as Crazypartial} from "./../Library/Crazypartial";
export {default as CurrentPage} from "./../Library/Current/Page";
export {default as HistoryPage} from "./../Library/History/Page";
export {default as Crazyevents} from "./../Library/Crazyevents";
export {default as LoaderPage} from "./../Library/Loader/Page";
export {default as UtilityCsv} from "./../Library/Utility/Csv";
export {default as Crazycache} from "./../Library/Crazycache";
export {default as Crazystate} from "./../Library/Crazystate";
export {default as Crazycolor} from "./../Library/Crazycolor";
export {default as Crazyobject} from "./../Core/Crazyobject";
export {default as Crazypage} from "./../Library/Crazypage";
export {default as Form} from "./../Library/Utility/Form";
export {default as Crazyurl} from "./../Library/Crazyurl";
export {default as Hash} from "./../Library/Utility/Hash";
export {default as DomRoot} from "./../Library/Dom/Root";

/* Modules to export */
/* export {}; */

// Declare GLobal type
declare global {

    /** Interface | Global
     ******************************************************
     */

    interface Window {
        Crazyobject: Crazyobject;
    }

    /** Interface
     ******************************************************
     */

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
        globalStateCollection?:Object
        globalPartials?:Object
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
    /* interface Crazypage {

        // Name
        name:string;
    } */

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
     * Crazy Component Property
     */
    interface CrazycomponentProperty {
        name?: string
        type: "string"|"number"|"bool"|"boolean",
        select?: string[]
        value?: string|number|boolean|object
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
        instance?:?object = null,
        content?:?CallableFunction|string = null,
        style?:?CallableFunction|string = null,
        preAction?:?CallableFunction = null,
        postAction?:?CallableFunction = null,
        status?:?LoadPageOptionsStatus = null,
        state?:?Partial<PageState> = null,
        color?:?Crazycolor = null,
        partials?:Object = null,
        scriptLoaded?:?typeof Crazypage = null,
        scriptRunning?:?new () => typeof Crazypage = null,
        hash?:?string = null,
        eventsRegistered?:?Array<Object> = null,
        componentsRegistered?:?Array<Object> = null,
        openInNewTab?:boolean = false;
    }

    /**
     * Interface LoadPageOptionsStatus
     */
    interface LoadPageOptionsStatus {
        /* Status of the page */
        // Is current page
        isCurrentPage?:boolean = false,
        // Has state
        hasState?:boolean = false,
        // JS script file registered
        scriptRegistered?:boolean = false,
        // URL loaded
        urlLoaded?:boolean = false,
        /* Action when page is loaded */
        // Pre Action Executed
        preActionExecuted?:boolean = false,
        // Url updated
        urlUpdated?:boolean = false,
        // Title updated
        titleUpdated?:boolean = false,
        // Style css loaded
        styleLoaded?:boolean = false,
        // Content loaded
        contentLoaded?:boolean = false,
        // On Ready Executed
        onReadyExecuted?:boolean = false,
        // Push to History
        historyRegistered?:boolean = false,
        // Post Action Executed
        postActionExecuted?:boolean = false,
        // Partials Scanned
        partialsScanned?:boolean = false,
        // Has crazy color
        hasColor?:Boolean = false,
        // Has crazy color applied
        hasColorApplied?:Boolean = false,
    }

    /**
     * Interface LoaderPageOptions
     */
    interface LoaderPartialOptions {
        name?:?string = null,
        instance?:?object = null,
        content?:?CallableFunction|string = null,
        preAction?:?CallableFunction = null,
        postAction?:?CallableFunction = null,
        status?:?LoadPartialOptionsStatus = null,
        state?:?Object = null,
        domTarget?:?string = null,
        page?:Object = null,
        scriptLoaded?:?Crazypartial = null,
        scriptRunning?:?new () => typeof Crazypartial = null,
        hash?:?string = null,
    }

    /**
     * Interface LoadPageOptionsStatus
     */
    interface LoadPartialOptionsStatus {
        /* Status of the partial */
        // Has state
        hasState?:boolean = false,
        // JS script file registered
        scriptRegistered?:boolean = false,
        /* Action when page is loaded */
        // Pre Action Executed
        preActionExecuted?:boolean = false,
        // Content loaded
        contentLoaded?:boolean = false,
        // Partials Pushed To Dom
        contentPushedToDom?:boolean = false,
        // Post Action Executed
        postActionExecuted?:boolean = false,
    }

    /** Interface | State
     ******************************************************
     */

    /**
     * Interface LoadPageOptionsStatus
     */
    interface PageState {
        _context: {
            routes: {
                current:Partial<PageStateRoute>
            }
        },
        _ui: {
            materialDynamicColors: {
                source: string
            }
        }
    }

    /**
     * Interface LoadPageOptionsStatus
     */
    interface PageStateRoute {
        controller: string,
        group: "app"|"api"|"asset",
        headers: Headers,
        methods: Array<"get"|"post"|"put"|"option"|"patch">,
        middleware: Array<>,
        name: string,
        parameters: object,
        patterns: string[],
        prefix: string|null,
        route: string
    }

    /** Interface | Register
     ******************************************************
     */

    /**
     * Page Registered
     */
    interface RegisterPageRegistered {
        className:string,
        classReference:any,
        hashUsed?:string,
        dateLoaded:Date,
        scriptUrl:URL,
    }

    /**
     * Register Partial Scanned
     */
    interface RegisterPartialScanned {
        name:string,
        target:Element
        callable:Crazypartial,
        id:number,
        scriptRunning?:Crazypartial
    }

    /** Interface | History
     ******************************************************
     */

    /**
     * History Item
     */
    interface HistoryItem {
        value:any,
        dateCreated:Date
        type:string
    }

    /**
     * Page History Item
     */
    interface HistoryPageItem {
        href:string,
        state?:?Object,
        loader:LoaderPageOptions
    }

    /** Interface | Utilities
     ******************************************************
     */

    /**
     * Runner Option
     */
    interface RunnerOption {
        errors?:?Array<CrazyError>,
        result:Object|Array<any>|string|null,
        _info:{
            status:"Waiting"|"Ready"|"In Progress"|"Error"|"Complete",
            name:string,
            run:{
                total:number,
                current:number,
                name:Array<{
                    method:string,
                    label:string
                }>
            }
        },
        _user_interface?:Object,
        extra?:any
    }

    /** Interface | Error
     ******************************************************
     */

    /**
     * Crazy Error Option
     */
    interface CrazyError {
        code:number,
        type?:?string
        detail?:?string,
        _run?:?Object,
        _status_code?:?{
            title?:?string,
            style?:{
                color?:{
                    text?:string,
                    fill?:string
                },
                icon?:{
                    class?:string,
                    text?:string
                }
            }
        }
    }

    

}