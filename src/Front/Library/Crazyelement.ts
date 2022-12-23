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

    /** @var shadowMode:ShadowRootMode|null Shadow Mode : "Open", "Closed" or null */
    abstract shadowMode:ShadowRootMode|null;

    /** @var shadowEl:ShadowRoot */
    abstract shadow:ShadowRoot|null;

    /** @var innerHtmlContent:string|null */
    abstract innerHtmlContent: string|null;

    /** @var innerHtmlCallableFunction:string|null */
    private innerHtmlCallableFunction: CallableFunction|null = null;

    /** @var attributesCollection:Object|null */
    abstract attributesCollection: Object|null;

    /**
     * Constructor 
     */
    constructor(){

        // Parent constructor
        super();

        // Set html inner
        this.innerHTML = "";

    }

    /** Lifr Cycle Callbacks | Methods
     ******************************************************
     */

    /**
     * Connected Callback
     * 
     * Call when element is created
     * 
     * @rreturn void
     */
    public connectedCallback() {

        // Check callable function
        if(this.innerHtmlCallableFunction !== null)

            // Set inner content
            this.innerHTML = this.innerHtmlCallableFunction();

        // Check innerHtmlContent
        if(this.innerHtmlContent)

            // Set inner html
            this.innerHTML = this.innerHtmlContent;

    }

    /**
     * 
     */
    static get observedAttributes() {

        // Set result
        let result =  ['name'];

        // Return result
        return result;

    }

    /**
     * Attributes Changes
     */
    public attributeChangedCallback(name:string, oldValue:any, newValue:any) {

        console.log(name);

    }

    /** Crazy Elements | Methods
     ******************************************************
     */

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

            // Set shadow
            this.shadow = null;

            // Stop function
            return;

        }

        // Attach Shadow
        this.shadow = this.attachShadow({mode:mode});
    }

    /**
     * Set Html Content
     * 
     * Set inner Html
     * 
     * @return void
     */
    public setHtmlContent = (content:string|CallableFunction):void => {

        // Check content
        if(!content)

            // Stop function
            return;

        // Check if CallableFunction
        if(typeof content === "string")

            // Check shadow
            if(this.shadowMode === null){

                // Append to inner html
                this.innerHtmlContent = content;
        
            }else if(this.shadowRoot !== null){

                // St shadowUse
                this.shadowRoot.innerHTML += content;

            }

        else
        // Else
        if(typeof content === "function")

            // Set CallableFunction
            this.innerHtmlCallableFunction = content;

    }

    /**
     * Append Html Node
     * 
     * Append Hyml Node in the root of webcomponent
     * 
     * @return void
     */
    public appendHtmlNode = (node:HTMLElement|Node):void => {

        // Check shadow
        if(this.shadow === null){

            // Append on this
            this.appendChild(node);

        }else{

            // Append on shadow
            this.shadow.appendChild(node);

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
            this.innerHtmlContent += "<style>"+content.default.toString()+"</style>";
    
        }else if(this.shadowRoot !== null){

            // St shadowUse
            this.shadowRoot.innerHTML += "<style>"+content.default.toString()+"</style>";

        }

    }

    /**
     * Set Attributes
     * 
     * Set Attributes of current element
     * 
     * @param attributesCollection:Object|null
     * @retirn void
     */
    public setAttributes = (attributesCollection:Object|null):void => {

        // Check if attributesCollection is null
        if(attributesCollection === null && this.attributesCollection !== undefined)

            // Set atrribute
            attributesCollection = this.attributesCollection;

        // Check attribute collection
        if(attributesCollection!== null && Object.keys(attributesCollection).length > 0)

            // Iteration
            for(let attribute in attributesCollection){

                // Set value
                let value:any = attributesCollection[attribute];

                // Check if bool
                if(typeof value === "boolean")

                    // Set value
                    value = value ? "true" : "false";

                else
                // Array
                if(typeof value === "object")

                    // Set value
                    value = JSON.stringify(value);
                    

                // Set attributes
                this.setAttribute(attribute, attributesCollection[attribute]);

            }

    }

    /**
     * Content Data
     * 
     * Data for callable function
     * 
     * @return void
     */
    public static contentData = () => {

        // Set result
        let result = {};

        // Push attributes
        //result.attributes = this.observedAttributes();

    }

}