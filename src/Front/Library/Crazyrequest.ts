/**
 * Front
 *
 * Front TS Scrips for your Crazy App
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */

/**
 * Dependances
 */
import { Crazyobject } from "../Types";
import Crazycache from "./Crazycache";
import hash from "object-hash";

/**
 * Crazy Request
 *
 * Methods for fetch http request
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */
export default class Crazyrequest{

    /**
     * Parameters
     */

    /** @vat url */
    public url:string;

    /** @var header */
    public header:Headers|null = null;

    /** @var options */
    public options:CrazyFetchOption = {};

    /** @var defaultOptions */
    public defaultOptions:CrazyFetchOption = {
        method: "get",
        header: {},
        cache: false,
        responseType: "json",
        responseEncoding: "utf8",
        from: "internal"
    };

    /** @var requestOptions */
    public requestOptions:RequestInit|undefined;

    /** @var request */
    public request:Request;

    /** @var lastResponse */
    public lastResponse?:Response;

    /** @var lastResponseContentType */
    public lastResponseContentType?:string;

    /** @var lastResponseCrazyHash */
    public lastResponseCrazyHash?:string;

    /** @var getParameters */
    public getParameters:string = "";

    /**
     * Constructor
     */
    public constructor(url:string, options:CrazyFetchOption = {}) {

        // Set url
        this.url = url;
    
        // Prepare options
        this.prepareGivenOptions(options);

        // Prepare request options
        this.prepareRequestOptions();

    }

    /** Public Methods
     ******************************************************
     */

    /**
     * Fetch
     * 
     * Fetch current request
     * 
     * @param body
     * @return Promise<Response>
     */
    public fetch = (body:Array<any>|Object|string|BodyInit|undefined|FormData = undefined):Promise<Response|any> => {

        // Clean last response & last response type
        this.lastResponse = undefined;
        this.lastResponseContentType = undefined;

        // Prepare request options
        this.pushBodyInRequestOptions(body)

        // Set url
        let url = this.getUrl(true);

        // Prepare request
        let request:Request = new Request(url, this.requestOptions);

        // Get key of current request
        let key = this.options.cache ? this.getKey(request, body) : null;

        // Check if cache exists
        if(key){

            // New cache
            let cache = new Crazycache("request");

            // Check if item has current key
            let cachedData = cache.get(key);

            // Check cachedData
            if(cachedData !== null)

                // Return current cached data
                return cachedData;

        }

        // Return fetch result
        return fetch(request)
            .then(
                result => {   
                    
                    // Set last response
                    this.lastResponse = result;

                    // Check Crazy Hash
                    if(this.options.from === "internal" && result.headers.get("Crazy-Hash")){

                        // Check if ignore hash
                        if(!("ignoreHash" in this.options && this.options.ignoreHash === true)){

                            // Get crazy hash
                            let crazyHash = result.headers.get("Crazy-Hash");

                            // Check hash
                            if(crazyHash){

                                // Set response hash
                                this.lastResponseCrazyHash = crazyHash;

                                // set hash in global
                                window.Crazyobject.hash.set(crazyHash);

                            }

                        }

                    }

                    // Check content type
                    if(result.headers.has("Content-Type")){

                        // Set content type
                        let contentType = result.headers.get("Content-Type");

                        // Check if json
                        if(contentType !== null && contentType.includes("application/json")){

                            // This last response type
                            this.lastResponseContentType = contentType;

                            // Return json
                            return result.json();

                        }

                    }

                    // Return null
                    return null;

                }
            )
        ;
        
    }

    /** Private Methods | Fetch
     ******************************************************
     */

    /**
     * Ger Json
     */
    private getJson = (result:Response) => {result.value = result.json()};

    /** Private Methods
     ******************************************************
     */

    /**
     * Get Key
     * 
     * Get Key from request
     * 
     * @param request:Request
     * @return string
     */
    private getKey = (request:Request, body:any):string => {

        // Declare result
        let result:string;

        // Declare result
        let object = {
            request: request,
            body:body
        };

        // Prepare object to avoid type error inside object
        object = JSON.parse(JSON.stringify(object));

        // Hash object
        result = hash(object);

        // Return result
        return result;

    }

    /**
     * Prepare Given Options
     * 
     * @param options:CrazyFetchOption
     * @return void
     */
    private prepareGivenOptions = (options:CrazyFetchOption):void => {

        // Copy default options in instance options
        this.options = {...this.defaultOptions};

        // Check options given isn't empty
        if(Object.keys(options).length > 0)

            // Iteration of options
            for(let option in options)

                // Push into instance options
                this.options[option] = options[option];

    }

    /**
     * Prepare Request Options
     * 
     * @param url:string
     * @return void
     */
    private prepareRequestOptions = ():void => {

        // Check requestOptions
        if(this.requestOptions === undefined)

            // Switch to object
            this.requestOptions = {};

        // Set method
        this.requestOptions.method = this.options.method;

        // New header
        let headers = new Headers();

        // Set header
        if(this.options.header !== undefined && Object.keys(this.options.header).length > 0)

            // Iteration of header
            for(let key of Object.keys(this.options.header))

                // Append to header
                headers.append(key, this.options.header[key]);

        // Push header in options
        this.requestOptions.headers = headers;

        // Check mode
        if(this.options.from === "internal"){

            // Set mode
            this.requestOptions.mode = "same-origin";

            // Set credentials
            this.requestOptions.credentials = "same-origin";

        }else 
        if(this.options.from === "external"){

            // Set mode
            this.requestOptions.mode = "no-cors";

        }

        // Check cache
        if(this.options.cache === false){

            // Push pragma
            this.requestOptions.headers.set("Pragma", "no-cache");

            // Push Cache-Control
            this.requestOptions.headers.set("Cache-Control", "no-cache");


        }

    }

    /**
     * Push Body In Request Options
     * 
     * @param body body:BodyInit|undefined
     * @return void
     */
    private pushBodyInRequestOptions = (body:Array<any>|Object|string|BodyInit|undefined):void => {

        // Check requestOptions
        if(this.requestOptions === undefined)

            // Stop function
            throw new Error('Request options have te be already set before push body content !');

        // Check if body already set
        if(this.requestOptions.body !== undefined) 

            // Reset body
            this.requestOptions.body = "";

        // Check body
        if(body !== undefined){

            // Check method get or head
            // if((this.requestOptions.method ?? "GET") in ["GET", "HEAD", "get", "head"]){
            if(["GET", "HEAD", "get", "head"].includes(this.requestOptions.method ?? "get")){

                // Check body is object
                if(typeof body === "object"){

                    // Convert result to URLSearchParams
                    var getParameters = Crazyrequest.toQueryString(body);

                    // Check get parameters 
                    if(this.getParameters)

                        // Append value
                        this.getParameters += `&${getParameters}`;

                    else

                        // Set value
                        this.getParameters = getParameters;

                }

            }else 
            // For post
            if(["POST", "post"].includes(this.requestOptions.method ?? "")){

                // Declare body content
                let bodyContent:FormData|Object|null = null;

                // Check if formdata
                if(body instanceof FormData){

                    // Fill body content
                    bodyContent = body;

                    /* // Check header is defined
                    if(!(this.requestOptions.headers instanceof Headers))

                        // Init headers
                        this.requestOptions.headers = new Headers();

                    // Fill headers
                    this.requestOptions.headers.append('Content-Type', 'multipart/form-data'); */

                }else
                // Push object
                if(typeof body === "object"){

                    // Fill body content
                    bodyContent = JSON.stringify(body);

                    // Check header is defined
                    if(!(this.requestOptions.headers instanceof Headers))

                        // Init headers
                        this.requestOptions.headers = new Headers();

                    // Fill headers
                    this.requestOptions.headers.set('Content-Type', 'application/json');

                }
    
                // Check body content
                if(bodyContent !== null)

                    // Set body
                    // @ts-ignore
                    this.requestOptions.body = bodyContent;

            }else
            // For put
            if(["PUT", "put"].includes(this.requestOptions.method ?? "")){

                // Declare body content
                let bodyContent:FormData|null = null;

                // Check if formdata
                if(body instanceof FormData){

                    // Fill body content
                    bodyContent = body;

                }
    
                // Check body content
                if(bodyContent !== null)

                    // Set body
                    this.requestOptions.body = bodyContent;

            // For other method
            }else{

                // Declare body content
                let bodyContent:string|null = null; 
    
                // Check body given is object or array
                if(Array.isArray(body) || typeof body === "object")
    
                    // Convert body to json
                    bodyContent = JSON.stringify(body);
    
                else
                // Check if is string
                if(typeof body === "string")
    
                    // Set body content
                    bodyContent = body;
    
                // Check body content
                if(bodyContent !== null)

                    // Set body
                    this.requestOptions.body = bodyContent;
            }

        }

    }

    /**
     * Get Url
     * 
     * Get Url of the current request
     * 
     * @param withQueryParameters:boolean Get Query Parameters with it
     * @return string
     */
    private getUrl = (withQueryParameters:boolean = false):string => {

        // Set result
        var result:string;

        result = (this.getParameters && withQueryParameters)
            ? `${this.url}?${this.getParameters}`
            : this.url
        ;
        
        // Return result
        return result;

    }

    /** Public static Methods
     ******************************************************
     */

    /**
     * To Query String
     * 
     * Convert object to query string
     * 
     * @source https://stackoverflow.com/questions/26084733/convert-multidimensional-object-to-query-string
     * 
     * @param obj 
     * @param prefix 
     * @returns 
     */
    public static toQueryString(obj:Object, prefix:string = "") {
        var str:Array<any> = [], k:string, v:Object;
        for(var p in obj) {
            if (!obj.hasOwnProperty(p)) {continue;} // skip things from the prototype
            if (~p.indexOf('[')) {
                k = prefix ? prefix + "[" + p.substring(0, p.indexOf('[')) + "]" + p.substring(p.indexOf('[')) : p;
    // only put whatever is before the bracket into new brackets; append the rest
            } else {
                k = prefix ? prefix + "[" + p + "]" : p;
            }
            v = obj[p];
            var temp:string = typeof v == "object"
                ? Crazyrequest.toQueryString(v, k) 
                : encodeURIComponent(k) + "=" + encodeURIComponent(v)
            ;
            str.push(temp);
        }
        return str.join("&");
    }

}