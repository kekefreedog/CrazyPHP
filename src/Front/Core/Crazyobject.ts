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
import Componentregister from "./../Library/Componentregister";
import Configregister from "./../Library/Configregister";
import RegisterPage from "./../Library/Register/Page";
import Pageregister from "./../Library/Pageregister";
import Crazyconsole from "./../Library/Crazyconsole";
import CurrentPage from "./../Library/Current/Page";
import HistoryPage from "./../Library/History/Page";
import Crazyevents from "./../Library/Crazyevents";
import PageLoader from "./../Library/Loader/Page";
import Crazypage from "./../Library/Crazypage";

/**
 * Crazy Object
 *
 * Methods for build your front interface
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
export default class Crazyobject {

    /** Parameters
     ******************************************************
     */

    /** @var components Components class */
    public components:Componentregister|null = null;

    /** @var pages Pages class */
    public pages:Pageregister;

    /** @var currentPage Pages class */
    public currentPage:CurrentPage;

    /** @var _registerPage Register page */
    private _registerPage:RegisterPage;

    /** @var configs Configs class */
    public configs:Configregister|null = null;

    /** @var console Configs class */
    public console:Crazyconsole|null = null;

    /** @var events Configs class */
    public events:Crazyevents|null = null;

    /** @var hash Hash of the current build */
    private hash:string = "";

    /** @var history History Page instance */
    public historyPage:HistoryPage;

    /**
     * Constructor
     */
    public constructor(input:CrazyObjectInput){

        // New Component Register
        this.components = new Componentregister(input);

        // New Page Register
        this.pages = new Pageregister();

        // New current page
        this.currentPage = new CurrentPage();

        // Page Register
        this._registerPage = RegisterPage;

        // New Config Register
        this.configs = new Configregister();

        // New Config Register
        this.console = new Crazyconsole();

        // New Crazy Events
        this.events = new Crazyevents();

        // Page history
        this.historyPage = new HistoryPage();

        // Set hash
        this.hash = "";

    }

    /** Methods | Component Register
     ******************************************************
     */

    /** Methods | Page Register
     ******************************************************
     */

    /**
     * Register Page
     * 
     * @param page Page to register
     */
    public registerPage = (page:typeof Crazypage) => {

        // Check function
        if("register" in this._registerPage && typeof this._registerPage.register === "function")

            // Call register in window
            this._registerPage.register(page);

        // Check i it is the first page registered
        if(this.currentPage.get() === null){

            // Page loader
            new PageLoader({
                name: page.className,
                // @ts-ignore
                scriptLoaded: page,
                status: {
                    "scriptRegistered": true,
                    "contentLoaded": true,
                    "styleLoaded": true,
                    "urlLoaded": true,
                    "urlUpdated": true
                }
            });

        }

    }

    public getRegisteredPage = (name:string):RegisterPageRegistered|null => {

        // Set result
        let result:RegisterPageRegistered|null = null;

        // Check function
        if("getRegistered" in this._registerPage && typeof this._registerPage.getRegistered === "function")

            // Call register in window
            result = this._registerPage.getRegistered(name);

        // Return result
        return result;

    }

    /** Methods | Config Register
     ******************************************************
     */

    /** Methods | Hash
     ******************************************************
     */

    /**
     * Set Hash
     * 
     * Set hash in crazy object
     * 
     * @param hash:string
     * @return void
     */
    public setHash = (hash:string) => {

        // Check hash given is and hash stored is empty
        if(hash && !this.hash){

            // Set hash
            this.hash = hash;

            // Info
            console.info("Crazy hash set");

        }else
        // If hash already stored and given one is
        if(hash && this.hash){

            // Check if hash are the same
            if(hash != this.hash){

                // Set hash
                this.hash = hash;

                // Info
                console.info("Crazy hash updated");

            }

        }else
        // Given hash is empty
        if(!hash)

            // Hash given is exmpty...
            console.warn("Hash given is exmpty...");

    }

    /**
     * Get Hash
     * 
     * Get Hash of Crazy app
     * 
     * @return string
     */
    public getHash = ():string => {

        // Set result
        let result:string = this.hash;

        // Return result
        return result;

    }

    /** Constants
     ******************************************************
     */

    /**
     * Global Variable
     */
    public readonly GLOBAL_VARIABLE:string = "Crazyobject";


}