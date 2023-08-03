/**
 * Utility
 *
 * Front TS Scrips for multiple tasks
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */

/**
 * Dependances
 */
import Crazyrequest from "../Crazyrequest";
import History from "../History/History";

/**
 * Hash
 *
 * Methods for store hash value
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */
export default class Hash {

    /** Private parameters
     ******************************************************
     */

    /** @var _hash Variable for store hash */
    private _hash:string|null = null;

    /** @var _history:History */
    private _history:History;

    /**
     * Constructor
     * 
     * @param hash Set hash value
     */
    public constructor(hash:string|null = null){

        // Check hash given
        if(hash)

            // Set hash
            this.set(hash);

        // New history
        this._history = new History();


    }

    /** Public methods
     ******************************************************
     */

    /**
     * Get
     * 
     * Get hash
     * 
     * @returns ?string
     */
    public get = ():string|null => {
    
        // Declare result
        let result:null|string;

        // Check if watch mode
        if(this.isWatch())

            // Set from meta tag
            this.setFromRequest();

        // Set result
        result = this._hash;

        // Return result
        return result;
        
    }


    /**
     * Set
     * 
     * Set hash
     * 
     * @param hash 
     */
    public set = (hash:string):boolean => {

        // Set result
        let result:boolean = false;

        // Check hash
        if(hash){

            // Push old value to history if needed
            this._setIntoHistory();

            // Set new hash
            this._hash = hash;

            // Set result
            result = true;

        }

        // Return result
        return result;

    }

    /**
     * Set From Meta Tag
     * 
     * Exemple of meta tag :
     * `<meta name="application-hash" content="4fe1efd8">`
     * 
     * @param tagName 
     * @returns 
     */
    public setFromMetaTag = (tagName:string = "application-hash"):Boolean => {

        // Set result
        let result:Boolean = false;

        // Check tagname
        if(tagName){

            // Get metatag
            const metaTag = document.querySelector(`meta[name="${tagName}"]`);

            // Check metatage
            if(metaTag instanceof HTMLMetaElement)

                // Set hash
                result = this.set(metaTag.content);

        }

        // Return result
        return result;

    }

    /**
     * Set From Request
     * 
     * Set hash from request to crazy app url
     * > Value stored in the header `Crazy-Hash`
     * 
     * @param url Url to fetch request
     */
    public setFromRequest = async (url:string = "/api/v2/Router/count"):Promise<Object|null> => {

        // Set result
        let result = null;

        // Check url
        if(url){

            // New request instance
            let request = new Crazyrequest(url, {
                from: "internal",
                cache: false
            });

            // Fetch request
            return await request.fetch();

        }

        // Return result
        return result;

    }

    /**
     * Get All From History
     * 
     * Return all old hash stored
     * 
     * @returns Array<string>
     */
    public getAllFromHistory = ():Array<string> => {

        // Prepare result
        let result:Array<string> = [];

        // Check history
        if(this._history !== null)

            // Get all previous value
            result = this._history.getAllPrevious();

        // Return result
        return result;

    }

    /**
     * Is Watch
     * 
     * Check if watch mode is enable in back
     * 
     * @return void 
     */
    public isWatch = ():Boolean => {

        // Set result
        let result:Boolean = false;

        // Get metatag
        const metaTag = document.querySelector(`meta[name="application-watch"]`);

        // Check metatage
        if(metaTag instanceof HTMLMetaElement && metaTag.content && metaTag.content == "true")

            // Set result
            result = true;

        // Return result
        return result;

    }

    /** Private methods
     ******************************************************
     */

    private _getFromHistory = (key:number = 1):null|string|undefined => {

        // Set result
        let result:null|string|undefined = undefined;

        // Prepare result
        if(key <= 0)

            // Set result
            result = this._hash;

        else
        // Check history in instanced
        if(this._history!==null){

            // Get from history
            let valueFromHistory:any = this._history.get(key);

            // Check value is null or is string
            if(valueFromHistory === null || typeof valueFromHistory == "string")

                // Set result
                result = valueFromHistory;

        }

        // Return result
        return result;


    }


    /**
     * Set Into History
     * 
     * Push current hash into history object
     * 
     * @returns void
     */
    private _setIntoHistory = ():Boolean => {

        // Prepare result
        let result:Boolean = true;

        // check current hash
        if(this._hash){

            // Check history
            if(this._history === null)

                // New history instance
                this._history = new History();

            // Set into history
            result = this._history.set(this._hash);

        }

        // Return result
        return result;

    }

}