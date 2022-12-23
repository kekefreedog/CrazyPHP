/**
 * Front
 *
 * Front TS Scrips for your Crazy App
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */

/**
 * Dependances
 */
import Crazycache from "./Crazycache";
import hash from "object-hash";

/**
 * Crazy Request
 *
 * Methods for fetch http request
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
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

    /**
     * Constructor
     */
    public constructor(url:string, options:CrazyFetchOption = {}) {

        // Prepare header
        this.header = new Headers;

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
    public fetch = (body:BodyInit|undefined = undefined):Promise<Response|any> => {

        // Prepare request options
        this.pushBodyInRequestOptions(body)

        // Prepare request
        let request:Request = new Request(this.url, this.requestOptions);

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
        return fetch(request);
        
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
            this.requestOptions.mode = "cors";

        }

    }

    /**
     * Push Body In Request Options
     * 
     * @param body body:BodyInit|undefined
     * @return void
     */
    private pushBodyInRequestOptions = (body:BodyInit|undefined):void => {

        // Check requestOptions
        if(this.requestOptions === undefined)

            // Stop function
            throw new Error('Request options have te be already set before push body content !');

        // Check if body already set
        if(this.requestOptions.body !== undefined) 

            // Reset body
            delete this.requestOptions.body;

        // Check body
        if(body !== undefined)

            // Set body
            this.requestOptions.body = body;

    }



}