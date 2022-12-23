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
 * Crazycomponenet
 *
 * Usefull functions / classes / const for manipulate Web Component
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
export default abstract class Crazycomponent extends HTMLElement {
    
    /** Attributes
     ******************************************************
     */

    /** @var properties Propoerties of the current component */
    abstract properties:Object;

    /** @var crazy Crazy methods for Web Component */
    private crazy:CrazycomponentAction;

    /**
     * Constructor 
     */
    constructor(){

        // Parent constructor
        super();

        // Set crazy root
        this.crazy = new CrazycomponentAction();

    }

    /** Methods | Events
     ******************************************************
     */

    /**
     * Post Render
     * 
     * Event executed post render
     * 
     * @return void
     */
    public postRender():void {};

    /** Methods
     ******************************************************
     */

    /**
     * Render
     * 
     * Render current template
     * 
     * @return string
     */
    public render():string {

        // Declare result
        let result:string = "";

        // Get content
        let content:string|Function = this.crazy.getContent();

        // Get style
        let style:string|Function = this.crazy.getStyle();

        // Prepare collection
        let collection:object = this.prepareCollectionForRender();

        // Check if content is callable
        if(typeof content === "function"){

            // Set content in result
            result += content(collection).replace(/\s+/g, ' ').trim();

        }else
        // String
        if(typeof content === "string" && content){

            // Set content in result
            result += content.replace(/\s+/g, ' ').trim();

        }

        // Check if style is callable
        if(typeof style === "function"){

            // Set style
            style = style(collection).replace(/\s+/g, ' ').trim();

        }else
        // String
        if(typeof style === "string" && style){

            // Set style in result
            result += style.replace(/\s+/g, ' ').trim();

        }

        // Return content
        return result;

    }

    /**
     * Set Default Properties
     * 
     * @param properties:Object
     * @return
     */
    public setDefaultProperties(properties:Object):void {

        // Set attributes by default
        this.crazy.setAttributes(properties, true);

    }

    /**
     * Set Properties
     * 
     * @param node:node
     * @return
     */
    public setProperties():void {

        // Check properties
        if(Object.keys(this.properties).length > 0)

            // Iteration of properties
            for(let propertie in this.properties){

                // let value
                let value:string = this.properties[propertie].value ?? "";

                // Check if value exists in attributes
                if(this.hasAttribute(propertie)){

                    // Attribute value
                    let attributeValue = this.getAttribute(propertie);

                    // Else check type
                    if(attributeValue !== null && typeof attributeValue === this.properties[propertie].type){

                        // Check if select
                        if("select" in this.properties && typeof this.properties.select === "object" && this.properties.select !== null){

                            // Check in select
                            if(attributeValue in this.properties.select)

                                // Push value
                                value = attributeValue;
                            
                        // Push directly value
                        }else

                            // Push value
                            value = attributeValue;
                        
                    }

                }

                // Set current attribute
                this.crazy.setAttribute(propertie, value);

            }

    }

    /**
     * Has Current Attribute
     * 
     * Check if current component has attribute
     * 
     * @return boolean
     */
    public hasCurrentAttribute(name:string):boolean {

        // Declare result
        let result = this.crazy.hasAttribute(name);

        // Return result
        return result;

    }

    /**
     * Get Current Attribute
     * 
     * Check if current component has attribute
     * 
     * @return string|boolean|null
     */
    public getCurrentAttribute(name:string):string|boolean|null {

        // Declare result
        let result = this.crazy.getAttribute(name);

        // Return result
        return result;

    }

    /**
     * Set Html And Css
     * 
     * @param html:Function|string Html or callable html generator
     * @param css:Function|string|CrazyelementStyle Css or callable css generator
     * @return void
     */
    public setHtmlAndCss(html:Function|string, css:Function|string|CrazyelementStyle):void {

        // Chech html
        if(html)

            // Set content
            this.crazy.setContent(html);

        // Check css
        if(css)

            // Set css
            this.crazy.setStyle(css);

    }

    /**
     * Prepare Collection
     * 
     * Prepare collection for render
     * 
     * @return Object
     */
    private prepareCollectionForRender():Object {

        // Declare result
        let result = {
            attributes: {},
            name: this.tagName.toLowerCase(),
        };

        /// Attributes
        
        // Prepare attributes
        let attributes = this.crazy.getAttributesName();

        // Check attributes
        if(attributes.length)

            // Iteration of attributes
            for(let attribute of attributes)

                // Get attribute
                result.attributes[attribute] = this.crazy.getAttribute(attribute);

        // Return result
        return result;

    }

    /** Methods | Callbacks
     ******************************************************
     */

    /**
     * Connected Callback
     */
    public connectedCallback() {

        // Set attributes by default
        this.setProperties();

        // Set html content
        this.innerHTML = this.render();

        // Execute post render 
        this.postRender();

    };

    /**
     * Disconnected Callback
     */
    public disconnectedCallback() {


    };

    /**
     * Attribute Changed Callback
     * 
     * @param name Name of the attribute
     * @param string oldValue Old value
     * @param string newValue New value
     */
    public attributeChangedCallback(name:string, oldValue:string, newValue:string):void {

        console.log("toto");
        console.log(newValue + " >> " + oldValue);

        // Check new value isn't the old value
        if(newValue != oldValue){

            // Update attribute collection
            this.crazy.updateAttribute(name, newValue);

            // Set html content
            this.innerHTML = this.render();
    
            // Execute post render 
            this.postRender();
            
        }

    };

    /**
     * Adopted Callback
     */
    public adoptedCallback() {



    };

}

/**
 * CrazycomponenetAction
 *
 * Usefull functions / classes / const for manipulate Web Component
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2022 Kévin Zarshenas
 */
class CrazycomponentAction {

    /** Attributes
     ******************************************************
     */

    /** @var attributesByDefault Collection Default */
    private attributesByDefault:Object = {};

    /** @var attributesCurrent Current attribute */
    private attributesCurrent:Object = {};

    /** @var htmlTemplate Callable function to generate content */
    private htmlTemplate:Function|null;

    /** @var htmlContent Content html */
    private htmlContent:string|null;

    /** @var cssTemplate Callable function to generate content */
    private cssTemplate:Function|null;

    /** @var cssContent Content html */
    private cssContent:string|null;

    // Style Content
    styleContent: string|null;

    /** Methods | Attributes
     ******************************************************
     */

    /**
     * Set Attributes
     * 
     * Set multiple attributes
     * 
     * @param attributes Attributes object
     * @param setAsDefault Set given attributes as default attributes
     * @return void
     */
    public setAttributes(attributes:Object, setAsDefault:boolean = false):void {

        // Check attributes
        if(Object.keys(attributes).length > 0){

            // Iteration of attributes
            for(let attributeName in attributes)

                // Set attribute
                this.setAttribute(attributeName, attributes[attributeName]);

            // Check setAsDefault
            if(setAsDefault)

                // Fill Attributes By Default
                this.attributesByDefault = attributes;

        }

    }

    /**
     * Set Attribute
     * 
     * Set Attribute on current element
     * 
     * @param name 
     * @param value 
     * @return
     */
    public setAttribute(name:string = "", value:string|boolean|Object = ""):void {

        // Check name
        if(!name)

            // Stop
            return;

        // Declare Current Value
        let currentValue = {
            name:name,
            value:"",
            type:"string",    
        };

        // Chack value is type string
        if(typeof value === "string")

            // Set current value
            currentValue.value = value;

        else
        // Check if value is type boolean
        if(typeof value === "boolean")

            // Set value
            currentValue.value = value ? "true" : "false";

        // Check if object
        if(typeof value === "object" && Object.keys(value).length > 0)

            // Iteration of object
            for(let key in value)

                // Check if key in current value
                if(key in currentValue){

                    // Declare v
                    let v = value[key];

                    // Check if value is type boolean
                    if(typeof v === "boolean")
            
                        // Set value
                        v = v ? "true" : "false";

                    // Set current value
                    currentValue[key] = v;

                }

        // Push value in current attributes
        this.attributesCurrent[name] = currentValue;

    }        
    
    /**
    * Update Attribute
    * 
    * Update attribute on current element
    * 
    * @param name
    * @param value
    * @return Check if update is a success
    */
    public updateAttribute(name:string = "", value:string|boolean|Object = ""):boolean {

        // Declare result
        let result:boolean = false;
        let maybeBoolean:boolean = false;

        // Check name
        if(!name)

            // Return result
            return result;

        // Get current result
        if(!(name in this.attributesCurrent))

            // Return result
            return result;

        // Check if string
        if(typeof value === "string"){

            // Check if current given value is maybe a boolean
            if(["true", "1", "TRUE", "false", "0", "FALSE"].includes(value))

                // Set maybe boolean
                maybeBoolean = true;

            // Check type is boolean
            if(this.attributesCurrent[name].type === "boolean" && maybeBoolean){

                // Set attribute value
                this.attributesCurrent[name].value = ["true", "1", "TRUE"] ? "true" : "false";

                // Set result
                result = true;

            }else
            // Check if is string
            if(this.attributesCurrent[name].type === "string"){

                // Set attribute value
                this.attributesCurrent[name].value = value;

                // Set result
                result = true;

            }

        }else
        // Check if boolean
        if(typeof value === "boolean"){

            // Check type is boolean
            if(this.attributesCurrent[name].type === "boolean"){

                // Set attribute value
                this.attributesCurrent[name].value = ["true", "1", "TRUE"] ? "true" : "false";

                // Set result
                result = true;

            }

        }else
        // Check if object
        if(typeof value === "object" && ("value" in value) && ("type" in value)){
    
                // Check type match
                if(this.attributesCurrent[name].type == value.type){

                    // Check boolean
                    if(value.type === "boolean"){
    
                        // Set attribute value
                        this.attributesCurrent[name].value = 
                        (
                            (
                                typeof value.value === "string" &&
                                ["true", "1", "TRUE"].includes(value.value)
                            ) ||
                            (
                                typeof value.value === "boolean" &&
                                value.value
                            )
                        ) ? "true" : "false";

                        // Set result
                        result = true;

                    }else
                    // If string
                    if(value.type === "string")

                        // Set attribute value
                        this.attributesCurrent[name].value = value;

                        // Set result
                        result = true;

                }

        }

        // Return result
        return result;

    }

    /**
     * Get Attributes Name
     * 
     * @return string[]
     */
    public getAttributesName():string[]{

        // Declare result
        let result = Object.keys(this.attributesCurrent);

        // Return result
        return result;

    }

    /**
     * Has Attribute
     * 
     * Check if current component has attribute
     * 
     * @return boolean
     */
    public hasAttribute(name:string):boolean {

        // Declare result
        let result:boolean = true;

        // Check name
        if(!name || !(name in this.attributesCurrent))

            // Set value
            result = false;

        // Return result
        return result;

    }

    /**
     * Get Attribute
     * 
     * @param name Name of the attribute to return
     */
    public getAttribute(name:string):string|boolean|null{

        // Declare result
        let result:string|boolean|null = null;

        // Check name
        if(!name || !(name in this.attributesCurrent))

            // Return result
            return result;

        // Get value of attribute current
        let attribute = this.attributesCurrent[name];

        // Check type
        if(attribute.type === "boolean")

            // Set result
            result = (["true", "1", "TRUE"].includes(attribute.value)) ?
                true : 
                    false;

        else

            // Set result
            result = attribute.value;

        // Return result
        return result;

    }

    /** Methods | Content
     ******************************************************
     */

    /**
     * Set Content
     * 
     * Set Content string or contect callable
     * 
     * @param content Content to put inside webcomponenet
     * @return void
     */
    public setContent(content:string|Function):void {

        // Check content
        if(typeof content === "function"){

            // Fill callable
            this.htmlTemplate = content;

            // Set html as null
            this.htmlContent = null;

        }else
        // If string
        if(typeof content === "string"){

            // Fill html content
            this.htmlContent = content;

            // Set callable as null
            this.htmlTemplate = null;

        }

    }

    /**
     * Get Content
     * 
     * Get Content string of content callable
     * @return string|Function
     */
    public getContent():string|Function {

        // Declare result
        let result:Function|string = "";

        // Get content
        if(this.htmlTemplate !== null)

            // Fill result
            result = this.htmlTemplate;

        else
        // Check is content
        if(this.htmlContent !== null)

            // Fill result
            result = this.htmlContent;

        // Return result
        return result;

    }

    /**
     * Set Style
     * 
     * Set Style string or content callable
     * 
     * @param content Content to put inside webcomponenet
     */
    public setStyle(content:string|Function|CrazyelementStyle):void {

        // Checj CrazyelementStyle
        if(typeof content === "object" && "default" in content){

            // Set css content
            this.cssContent = content.default.toString();

            // Set callable as null
            this.cssTemplate = null;

        }else
        // Check content
        if(typeof content === "function"){

            // Fill callable
            this.cssTemplate = content;

            // Set html as null
            this.cssContent = null;

        }else
        // If string
        if(typeof content === "string"){

            // Fill html content
            this.cssContent = content;

            // Set callable as null
            this.cssTemplate = null;

        }

    }

    /**
     * Get Style
     * 
     * Get Style string of content callable
     * @return string|Function
     */
    public getStyle():string|Function {

        // Declare result
        let result:Function|string = "<style>";

        // Get content
        if(this.cssTemplate !== null)

            // Fill result
            result += this.cssTemplate;

        else
        // Check is content
        if(this.cssContent !== null)

            // Fill result
            result += this.cssContent;

        // Check result
        if(result === "<style>"){

            // Set result
            result = "";

        }else{

            // Set result
            result += "</style>";

        }

        // Return result
        return result;

    }

};