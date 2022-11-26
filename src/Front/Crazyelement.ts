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
 * Crazyelements
 *
 * Usefull functions / classes / const for manipulate Webcomponent
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
 
/**
 * Crazy Element
 * 
 * Methods for help to create custom elements
 */
 export default abstract class Crazyelement extends HTMLElement {

    /** Parameters
     ******************************************************
     */

    abstract shadowMode:ShadowRootMode|null;

    /**
     * Constructor 
     */
    constructor(){

        // Parent constructor
        super();

        // Set html inner
        this.innerHTML = "";

    }

    /**
     * Set Shadow Mode
     * 
     * Set shadow mode depending of variable shadow mode of if the tag get attrubutes shadow = "open"|"close"
     * 
     * @return void
     */
    attachShadowMode = () => {

        // Declare mode
        let mode:ShadowRootMode|null = this.shadowMode ?? null;

        // Check if shadow is set as open on node attribute 
        if(this.getAttribute("shadow") === "open"){
            mode = "open";
        }

        // Check if shadow is set as closed on node attribute 
        if(this.getAttribute("shadow") === "closed"){
            mode = "closed";
        }
        
        // check
        if(mode === null){

            // Stop function
            return;

        }

        // Attach Shadow
        this.attachShadow({mode:mode});
    }

    /**
     * Set Html Content
     * 
     * Set inner Html
     * 
     * @return void
     */
    public setHtmlContent = (content:string):void => {

        // Check content
        if(!content)

            // Stop function
            return;

        // Check shadow
        if(this.shadowMode === null){

            // Append to inner html
            this.innerHTML += content;
    
        }else if(this.shadowRoot !== null){

            // St shadowUse
            this.shadowRoot.innerHTML += content;

        }

    }

    /**
     * Set Style Content
     * 
     * Set Style
     * 
     * @return void
     */
    public setStyleContent = (content:CrazyelementStyle):void => {

        // Check content
        if(!content)

            // Stop function
            return;

        // Check shadow
        if(this.shadowRoot === null){

            // Append to inner html
            this.innerHTML += "<style>"+content.default.toString()+"</style>";
    
        }else if(this.shadowRoot !== null){

            // St shadowUse
            this.shadowRoot.innerHTML += "<style>"+content.default.toString()+"</style>";

        }

    }

}