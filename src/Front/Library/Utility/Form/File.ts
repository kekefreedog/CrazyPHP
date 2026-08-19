/**
 * Form
 *
 * Front TS Scrips for form type
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */

/**
 * Dependances
 */
// @ts-ignore
import FilePondPluginImageExifOrientation from 'filepond-plugin-image-exif-orientation';
// @ts-ignore
import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type';
// @ts-ignore
import FilePondPluginImagePreview from 'filepond-plugin-image-preview';
import type { formFilePondValue } from '../Form';
import fr_FR from 'filepond/locale/fr-fr';
import type { FormInputTypeHelpers } from './Type';
import type FormInputType from './Type';
import * as FilePond from 'filepond';

/**
 * File Type
 *
 * Handler for "file" inputs
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
export default class FileType implements FormInputType {

    /** Private Parameters
     ******************************************************
     */

    /** @var _options */
    private _options:Partial<FormOptions> = {
        filter: false
    };

    /**
     * Constructor
     *
     * @param options
     */
    constructor(options:Partial<FormOptions> = {}){

        // Ingest options
        this._options = {...this._options, ...options};

    }

    /** Public Methods
     ******************************************************
     */

    /**
     * Init
     *
     * @param inputEl
     * @returns {void}
     */
    public init = async (inputEl:HTMLSelectElement|HTMLInputElement, formEl:HTMLFormElement, helpers:FormInputTypeHelpers, options:Partial<FormOptions> = {}):Promise<void> => {

        // Resync options
        this._options = {...this._options, ...options};

        // Check maska
        if(inputEl instanceof HTMLInputElement){

            // Check if input has filepond
            if(inputEl.classList.contains("filepond")){

                // Check if preview
                if(typeof inputEl.dataset.filePreview === "string")

                    // Register plugin
                    FilePond.registerPlugin(FilePondPluginImagePreview);

                // Register exif plugin
                FilePond.registerPlugin(FilePondPluginImageExifOrientation);

                // Prepare options
                let options:FilePond.FilePondOptions = {

                    // Set on init
                    oninit: () => {

                        // Check element
                        if(pondInstance.element){

                            // Apply margin
                            pondInstance.element.classList.add("mb-0", "mt-6");

                            // Remove credits

                            // Search credit
                            let els = pondInstance.element.querySelectorAll(".filepond--credits");

                            // Iteration and delete
                            els?.forEach((value:Element):void => value.remove());

                            // Search input with name
                            let el = pondInstance.element.querySelector("input[name]");

                            // Check el
                            if(el instanceof HTMLInputElement){

                                // Check id
                                if(pondInstance.id)

                                    // Add data type
                                    el.dataset.pondId = pondInstance.id;

                                // Set type
                                el.dataset.type = "file";

                            }

                        }

                    }

                }

                // Set name
                // if(inputEl.name)

                    // Set name

                // Check if max file
                if(typeof inputEl.dataset.maxFiles === "string")

                    // set max files
                    options.maxFiles = Number(inputEl.dataset.maxFiles);

                // Check if multiple
                if(inputEl.multiple)

                    // set max files
                    options.allowReorder = true;

                // Check if accept
                if(inputEl.accept){

                    // Register validate type
                    FilePond.registerPlugin(FilePondPluginFileValidateType);

                }

                // Check lang
                if(typeof inputEl.dataset.fileLocale === "string")

                    // Check if fr
                    if(["fr-fr", "fr_FR"].includes(inputEl.dataset.fileLocale)){

                        // Register lang
                        options = {...options, ...fr_FR};

                    }

                // Create pond instance
                let pondInstance = FilePond.create(inputEl, options);

                // Add event
                pondInstance.on("updatefiles", () => {

                    // Search input with name
                    let el = pondInstance.element?.querySelector("input[name]");

                    // Check el
                    if(el instanceof HTMLInputElement){

                        // Check id
                        if(pondInstance.id)

                            // Add data type
                            el.dataset.pondId = pondInstance.id;

                        // Set type
                        el.dataset.type = "file";

                    }


                });

                // Push it into el
                // @ts-ignore
                inputEl.pondInstance = pondInstance;

            }

        }

    }

    /**
     * Get
     *
     * @param itemEl:HTMLElement
     * @return null|Array<any>[]
     */
    public get = (itemEl:HTMLElement, options:Partial<FormOptions> = {}):null|Array<any> => {

        // Resync options
        this._options = {...this._options, ...options};

        // Set result
        let result:null|Array<any> = null;

        // Check value
        if("name" in itemEl && itemEl instanceof HTMLInputElement && itemEl.files?.length){

            let key:string = itemEl.name as string;

            // Set result
            let files:FileList = itemEl.files;

            // Push in result
            result = [key, Array.from(files).at(0)];

        }else
        // Check if pond instance
        if(itemEl.dataset.pondId && itemEl instanceof HTMLInputElement){

            // Search el
            let pondEl = itemEl.closest("form")?.querySelector(`div#${itemEl.dataset.pondId}`);

            // Check pond el
            if(pondEl instanceof HTMLDivElement){

                let key:string = itemEl.name as string;

                // Get pond instance
                let pondInstance = FilePond.find(pondEl);

                // Get files
                let files = pondInstance.getFiles();

                // Set result
                result = [key, files.length ? files[0].file as File : ""];

            }

        }

        // Return result
        return result;

    }

    /**
     * Get Multiple
     *
     * @param itemEl:HTMLElement
     * @return null|Array<any>[]
     */
    public getMultiple = (itemEl:HTMLElement, options:Partial<FormOptions> = {}):null|Array<any>[] => {

        // Resync options
        this._options = {...this._options, ...options};

        // Set result
        let result:null|Array<any>[] = null;

        // Check value
        if("name" in itemEl && itemEl instanceof HTMLInputElement && itemEl.files?.length){

            let key:string = itemEl.name as string;

            // Set result
            let files = Array.from(itemEl.files);

            // Check files
            if(files.length) for(let currentFile of files) if(currentFile instanceof File){

                // Check result
                if(result === null) result = [];

                // Push in result
                result.push([key, currentFile]);

            }

        }else
        // Check if pond instance
        if(itemEl.dataset.pondId && itemEl instanceof HTMLInputElement){

            // Search el
            let pondEl = itemEl.closest("form")?.querySelector(`div#${itemEl.dataset.pondId}`);

            // Check pond el
            if(pondEl instanceof HTMLDivElement){

                let key:string = itemEl.name as string;

                // Get pond instance
                let pondInstance = FilePond.find(pondEl);

                // Get files
                let files = pondInstance.getFiles();

                // Check files
                if(files.length) for(let currentFile of files) if(currentFile instanceof File){

                    // Check result
                    if(result === null) result = [];

                    // Push in result
                    result.push([key, currentFile]);

                }

            }

        }

        // Return result
        return result;

    }

    /**
     * Set
     *
     * Set file in item
     *
     * @param itemEl:HTMLElement
     * @param value:string
     * @param valuesID
     * @param formEl
     * @return void
     */
    public set = (itemEl:HTMLElement, value:formFilePondValue|formFilePondValue[], valuesID:string|Object|null, formEl:HTMLFormElement, options:Partial<FormOptions> = {}):void => {

        // Resync options
        this._options = {...this._options, ...options};

        // Check itemEl
        if(["INPUT", "SELECT"].includes(itemEl.tagName) && value !== null){

            // Check if filepont
            if(itemEl.classList.contains("filepond--browser") && itemEl.parentElement instanceof HTMLElement){

                // Get pond instance
                let pondInstance = FilePond.find(itemEl.parentElement);

                // Check pondInstance
                if(pondInstance){

                    // Set files
                    let files:File[] = [];

                    // Check if value is array
                    if(Array.isArray(value)){

                        // Iteration value
                        for(let item of value){

                            // Set fake content
                            let fakeContent = "";

                            // Set fake content
                            if(item.options.file.type == "application/json"){

                                // Set fakeContent
                                fakeContent = JSON.stringify({});

                            }

                            // New blob
                            const blob = new Blob(
                                [fakeContent],
                                { type: item.options.file.type }
                            );

                            // New file
                            const currentFile = new File(
                                [blob],
                                item.source,
                                { type: item.options.file.type }
                            );

                            // Push files
                            files.push(currentFile);

                        }

                        // Add Files
                        pondInstance.addFiles(files);

                    }else{

                        // Set fake content
                        let fakeContent = "";

                        // Set fake content
                        if(value.options.file.type == "application/json"){

                            // Set fakeContent
                            fakeContent = JSON.stringify({});

                        }

                        // New blob
                        const blob = new Blob(
                            [fakeContent],
                            { type: value.options.file.type }
                        );

                        // New file
                        const file = new File(
                            [blob],
                            value.source,
                            { type: value.options.file.type }
                        );

                        // Add file
                        pondInstance.addFile(file);

                    }

                    // Set value
                    /* itemEl.setAttribute("value", value);

                    // Dispatch event change
                    itemEl.dispatchEvent(new Event("change"));

                    // Set id
                    Form.setId(formEl, valuesID, itemEl); */

                }

            }

        }

    }

}
