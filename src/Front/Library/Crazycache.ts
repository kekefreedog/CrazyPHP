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
import * as localforage from "localforage";

/**
 * Cache
 *
 * Methods for manage cache in worker service
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
export default class Crazycache {

    /** Parameters
     ******************************************************
     */

    /** Cache Storage instance */
    private instance?:LocalForage;

    /** @param nameReserved  Reserved name of item */
    public nameReserved:Array<string> = [
        "dateCreated", "dateUpdated", "dateLoaded"
    ];

    /**
     * Constructor
     */
    public constructor(name:string = "crazy-cache"){

        /* Check name */
        if(!name)

            /* New error */
            throw new Error("Cache name can't be empty !");

        /* Create instance */
        this.instance = localforage.createInstance({
            name: "crazy-"+name
        });

        /* Get ready moment of localforage */
        this.instance.ready().then(() => {

            /* Set default properties */
            this.setDefaultProperties();

        }).catch(function (e) {

            /* Warning */
            console.warn(e);

        });
            
    }

    /** Public Methods
     ******************************************************
     */

    /**
     * Get
     * 
     * Get item in current cache instance
     * 
     * @param key:string
     * @return any
     */
    public get = async (key:string):Promise<any> => {

        // Get value
        let value = await this.instance?.getItem(key);

        // Return value
        return value;
        

    }

    /**
     * Set
     * 
     * Set item in current cache instance
     * 
     * @param key:string
     * @param value:any
     * @param callback?:CallableFunction
     * @return void
     */
    public set = async (key:string, value:any, callback?:CallableFunction):Promise<void> => {

        // Set value
        let item = this.instance?.setItem(key, value, );

        // Check callback
        if(callback)

            // execute it then
            item?.then(value => {

                // Pass value in callback
                callback(value);

            })

    }    

    /** Private methods
     ******************************************************
     */

    /**
     * Set Default Properties
     * 
     * Set Default Propoerties in LocalForage
     * 
     * @return void
     */
    private setDefaultProperties = ():void => {

        // Check instance
        if(this.instance)

            // Iteration key
            for(let item of ["dateCreated", "dateUpdated", "dateLoaded"])

                // Set created date and update time
                this.instance.getItem(item).then((value) => {

                    // Check if null
                    if(value === null || item == "dateLoaded")

                        // Set current date
                        this.instance?.setItem(item, new Date())


                // Catch error
                }).catch((err) => {

                    /* Warning */
                    console.warn(err);

                });

    }

}