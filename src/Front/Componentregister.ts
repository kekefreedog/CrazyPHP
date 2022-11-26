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
 * Component Register
 *
 * Methods for manage components loaded and to load
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
 export default class Componentregister {

    /** Parameters
     ******************************************************
     */

    /**
     * Constructor
     */
    public constructor(input:CrazyObjectInput){

        // Register Global Components
        this.registerGlobal(input.globalComponentsCollection);

    }

    /** Methods | Global
     ******************************************************
     */

    /**
     * Register Global
     * 
     * Register Global components
     * 
     * @return void
     */
    public registerGlobal = (globalComponentsCollection:object):void => {

        // Check modules
        if(Object.keys(globalComponentsCollection).length > 0)
    
            // Iteration of globalComponentsCollection
            for(let module in globalComponentsCollection){
                
                let classCallable = globalComponentsCollection[module];
    
                // Declare current module
                window.customElements.define(module, classCallable);
    
            }

    }

}