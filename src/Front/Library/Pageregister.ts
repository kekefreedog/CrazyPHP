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
import Crazyrequest from './Crazyrequest';
import Crazypage from './Crazypage';

/**
 * Page Register
 *
 * Methods for manage page loaded and to load
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
 export default class Pageregister {

    /** Methods
     ******************************************************
     */

    /** @var routerCollection:Object */
    private routerCollection:any = [];

    /** Parameters
     ******************************************************
     */

    /**
     * Constructor
     */
    public constructor(){

        let request = new Crazyrequest("/api/v1/config/");

        let config = request.fetch().then(
            result => {

                console.log(result);

            }
        );

        // Register Router Collection
        // this.registerRouterCollection(routerCollection);

    }

    /** Methods
     ******************************************************
     */

    /**
     * Register
     * 
     * Register page in current context
     * 
     * @param page:Crazypage
     * @return void
     */
    public register(page:Crazypage):void {

        if(!("name" in page) || typeof page.name !== "string")

            // Return;
            return;

        // Check if page register already declared
        let currentContextCollection = this.filterArrayByKeyValue(this.routerCollection.Router.app, "name", page.name);
        
        // Check current context
        if(!currentContextCollection.length)

            // Return
            return;

        // Check if instance already existing
        if(("instance" in this.routerCollection.Router.app[0]) || !this.routerCollection.Router.app[0]){

            // Push class in instance
            this.routerCollection.Router.app[0].instance = page;

            // Push date
            this.routerCollection.Router.app[0].instanceDate = Date.now();

        }

    }

    /** Methods | Private
     ******************************************************
     */

    /**
     * Register Router Collection
     * 
     * @param collection:Object
     * @return void
     */
    private registerRouterCollection(collection:Object){

        // Push object in routerCollection
        this.routerCollection = collection;

    }

    /**
     * Array Filter
     * 
     * @return any
     */
    private filterArrayByKeyValue = (array:Array<any> = [], key:string, keyValue:string) => array.filter(
        (aEl) => aEl[key] == keyValue
    );

}