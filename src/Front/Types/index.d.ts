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
export {default as UtilityMessagePack} from "./../Library/Utility/MessagePack";
export {default as PythonCollection} from "./../Library/File/PythonCollection";
export {default as Componentregister} from "./../Library/Componentregister";
export {default as NavigatorClient} from "./../Library/Navigator/Client";
export {default as UtilityDateTime} from "./../Library/Utility/DateTime";
export {default as ColorSchema} from "./../Library/Utility/ColorSchema";
export {default as UtilityBoolean} from "./../Library/Utility/Boolean";
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
export {default as LoaderEvents} from "./../Library/Loader/Events";
export {default as Crazyconsole} from "./../Library/Crazyconsole";
export {default as Crazyrequest} from "./../Library/Crazyrequest";
export {default as Crazyelement} from "./../Library/Crazyelement";
export {default as Pageregister} from "./../Library/Pageregister";
export {default as Crazypartial} from "./../Library/Crazypartial";
export {default as CurrentPage} from "./../Library/Current/Page";
export {default as HistoryPage} from "./../Library/History/Page";
export {default as DomRedirect} from "./../Library/Dom/Redirect";
export {default as Crazyevents} from "./../Library/Crazyevents";
export {default as LoaderPage} from "./../Library/Loader/Page";
export {default as UtilityCsv} from "./../Library/Utility/Csv";
export {default as Crazycache} from "./../Library/Crazycache";
export {default as Crazystate} from "./../Library/Crazystate";
export {default as Crazycolor} from "./../Library/Crazycolor";
export {default as Crazyalert} from "./../Library/Crazyalert";
export {default as Crazyobject} from "./../Core/Crazyobject";
export {default as Crazypage} from "./../Library/Crazypage";
export {default as Form} from "./../Library/Utility/Form";
export {default as Crazyurl} from "./../Library/Crazyurl";
export {default as Hash} from "./../Library/Utility/Hash";
export {default as DomRoot} from "./../Library/Dom/Root";
export {default as State} from './../Library/State';

/* Modules to export */

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
        responseType?: "arraybuffer"|"document"|"json"|"text"|"stream"|"msgpack"|boolean,
        requestType?: "json"|"msgpack",
        responseEncoding?: "utf8",
        from?: "internal"|"external",
        ignoreHash?: boolean,
        catchEvents?: boolean,
    }

    /**
     * Interface of CrazyObjectInput
     */
    interface CrazyObjectInput {
        globalComponentsCollection:Object;
        globalStateCollection?:Object
        globalPartials?:Object
        glabalAlerts?:Record<string, typeof CrazyAlertDriver>,
        forceTheme?:"dark"|"light",
        websocket?:string,
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

    /**
     * Loader Event Redirection
     */
    interface LoaderEventRedirection {
        type: "redirect",
        name?: string,
        url?: string,
        target?: "_blank"|null;
        arguments?: object|null
    };

    /**
     * Loader Event Alert
     */
    interface LoaderEventAlert {
        type: "alert",
        messageType: CrazyError["type"],
        message?: string,
        
    };

    /**
     * Loader Event Wait
     */
    interface LoaderEventWait {
        type: "wait",
        second?: number,
        
    };

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
        scriptRunning?:Crazypartial,
        page?:Crazypage,
        html?:CallableFunction|html|null
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
     * CrazyRunner
     */
    interface CrazyRunner {
        // Constructor
        // new(extra:any=null,viewer:RunnerViewerConstructor|null=null):T;
        // Public readonly
        public readonly name:string;
        // Public methods | Preparation
        public async setUpBeforeClass(options:RunnerOption):Promise<RunnerOption>;
        public async setUpBeforeMethod(options:RunnerOption):Promise<RunnerOption>;
        public async tearDownAfterMethod(options:RunnerOption):Promise<RunnerOption>;
        public async tearDownAfterClass(options:RunnerOption):Promise<RunnerOption>;
        // Utility runner | Public parameter
        public viewer:?RunnerViewer;
        // Utility runner | Runner Info
        public setName(name:string):void;
        // Utility runner | Public
        public execute():Promise<RunnerOption>;
        public stop(options:RunnerOption, message:string = "Runner stopped", callback?:(options:RunnerOption)=>void);
        // Utility runner | Options
        public registerOptions(options:RunnerOption):void;
        // Utility runner | State
        public async runGetPageState?(options:RunnerOption):Promise<RunnerOption>;
    }

    /**
     * Runner Viewer
     */
    interface RunnerViewer {
        // Public methods
        public open(data?:ToastProgressionData):void;
        public close():void;
        public update(data?:ToastProgressionData):void;
    }

    /**
     * RunnerViewerConstructor
     */
    interface RunnerViewerConstructor {
        new (viewerOptions?: Partial<ToastProgressionOptions>,runnerOptions?: RunnerOption):RunnerViewer;
    }

    /**
     * Runner Option
     */
    interface RunnerOption {
        errors?:?Array<CrazyError>,
        result:Record<string,any>|Array<any>|string|null,
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

    /** Interface | Utilities Form
     ******************************************************
     */

    interface FormOptions {
        onBeforeSubmit:(entity:string, formData:FormData)=>void,
        onSubmitDone:(result:object, entity:string, formData:FormData)=>void,
        onError:(result:object, entity:string, formData:FormData)=>void,
        alertDriver:string,
        filter:boolean,
    }

    /** Interface | Error
     ******************************************************
     */

    /**
     * Crazy Error Option
     */
    interface CrazyError {
        code:number,
        type?:?"error"|"warning"|"success"|"info"|"news"
        detail?:?string,
        _run?:?Object,
        _status_code?:?{
            title?:?string,
            style?:{
                color?:{
                    text?:string,
                    fill?:string,
                },
                icon?:{
                    class?:string,
                    text?:string
                }
            }
        }
    }

    /** Interface | Alert
     ******************************************************
     */

    /**
     * Crazy Alert Options
     */
    interface CrazyAlertOptions {
        driver: string,
        postAction?: () => void,
    }

    /**
     * Crazy Alert Parser Options
     */
    interface CrazyAlertParserOptions extends CrazyAlertOptions {
        overrideType:CrazyError["type"],
    }

    /**
     * Crazy Alert Input
     */
    interface CrazyAlertInput extends Partial<CrazyError> {
        redirectTo?:LoaderPageOptions,
    }

    /**
     * Crazy Alert News Input
     */
    interface CrazyAlertNewsInput extends Partial<CrazyError> {
        type: "news"
    }

    /**
     * Crazy Alert Info Input
     */
    interface CrazyAlertInfoInput extends Partial<CrazyError> {
        type: "info"
    }

    /**
     * Crazy Alert success Input
     */
    interface CrazyAlertSuccessInput extends Partial<CrazyError> {
        type: "success"
    }

    /**
     * Crazy Alert success Input
     */
    interface CrazyAlertWarningInput extends Partial<CrazyError> {
        type: "warning"
    }

    /**
     * Crazy Alert success Input
     */
    interface CrazyAlertErrorInput extends Partial<CrazyError> {
        type: "error"
    }

    /**
     * Crazy Alert Driver
     */
    interface CrazyAlertDriver {
        public news(input?:CrazyAlertNewsInput, options?:null|Partial<CrazyAlertOptions>):void,
        public info(input?:CrazyAlertInfoInput, options?:null|Partial<CrazyAlertOptions>):void,
        public success(input?:CrazyAlertSuccessInput, options?:null|Partial<CrazyAlertOptions>):void,
        public warning(input?:CrazyAlertWarningInput, options?:null|Partial<CrazyAlertOptions>):void,
        public error(input?:CrazyAlertErrorInput, options?:null|Partial<CrazyAlertOptions>):void,
    }
    interface CrazyAlertDriverConstructor {
        new (): CrazyAlertDriver;
    }

    /** Interface | State
     ******************************************************
     */

    // State Event 
    interface stateEvent {
        name:string,
        callback:(state:any,prevState:any)=>void,
        selector:string
    }

    // Define the state shape
    interface StatePage extends object {
    }

}