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
import Componentregister from "./Componentregister";
import Configregister from "./Configregister";
import Pageregister from "./Pageregister";
import Crazyconsole from "./Crazyconsole";
import Crazyevents from "./Crazyevents";

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
    public pages:Pageregister|null = null;

    /** @var configs Configs class */
    public configs:Configregister|null = null;

    /** @var console Configs class */
    public console:Crazyconsole|null = null;

    /** @var events Configs class */
    public events:Crazyevents|null = null;

    /**
     * Constructor
     */
    public constructor(input:CrazyObjectInput){

        // New Component Register
        this.components = new Componentregister(input);

        // New Page Register
        this.pages = new Pageregister();

        // New Config Register
        this.configs = new Configregister();

        // New Config Register
        this.console = new Crazyconsole();

        // New Crazy Events
        this.events = new Crazyevents();

    }


    /** Methods | Component Register
     ******************************************************
     */

    /** Methods | Page Register
     ******************************************************
     */

    /** Methods | Config Register
     ******************************************************
     */

    /** Constants
     ******************************************************
     */

    /**
     * Global Variable
     */
    public readonly GLOBAL_VARIABLE:string = "Crazyobject";


}