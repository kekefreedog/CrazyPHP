/**
 * Loader
 *
 * Front TS Scrips for load elements
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */

/**
 * Dependances
 */
import * as PageError from './../Error/Page';

/**
 * Crazy Page Loader
 *
 * Methods for load a page
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
export default abstract class Page {

    /**
     * Constructor
     */
    public constructor(){

        // Load page detail
        this.loadPageDetail()
            .then(
                // Load Pre Action
                this.loadPreAction
            ).then(
                // Load Script
                this.loadScript
            )
            .then(
                // Load Style
                this.loadStyle
            )
            .then(
                // Load Content
                this.loadContent
            ).then(
                // Load Post Action
                this.loadPostAction
            ).catch(
                err =>  {
                    console.log(PageError);
                }
            )

    }

    /** Punlic methods
     ******************************************************
     */

    /**
     * Load Page Detail
     * 
     * Load Detail of the page
     * Return an object following this schema 
     *  {
     *      preAction:callable
     *      instance:Page
     *      content:
     *      style:
     *      postAction:callable
     *      
     *  }
     */
    public loadPageDetail = async() => {

    }

    /**
     * Load Pre Action
     * 
     * Execute custom pre actions
     */
    public loadPreAction = async() =>  {



    }

    /**
     * Load Script
     * 
     * Load Js scripts of the page
     */
    public loadScript = async() =>  {

    }

    /**
     * Load Style
     * 
     * Load Css styles of the page
     */
    public loadStyle = async() =>  {
        
    }

    /**
     * Load Content
     * 
     * Load html Content of the page
     */
    public loadContent = async() =>  {
        
    }

    /**
     * Load Post Action
     * 
     * Execute custom pre actions
     */
    public loadPostAction = async() =>  {
        
    }


}