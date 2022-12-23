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
 * Webcomponent
 *
 * Usefull functions / classes / const for manipulate Webcomponent
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
 
/**
 * Test
 */
export function declareAll(modules:Object):void {

    // Check modules
    if(Object.keys(modules).length > 0)

        // Iteration of modules
        for(let module in modules){
            
            let classCallable = modules[module];

            // Declare current module
            window.customElements.define(module, classCallable);

        }

}