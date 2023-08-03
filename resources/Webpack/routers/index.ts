/**
 * Routers
 *
 * Script for load routers from app config
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2023 Kévin Zarshenas
 */

/**
 * Load
 *
 * Function for load routers from app config
 * 
 * @return string[]
 */
let load = (yaml, fs) => {

    // Set result
    let result = {};

    // Get doc
    let doc = yaml.load(fs.readFileSync('./config/Router.yml', 'utf8'));

    // Check if router
    if(("Router" in doc))

        // Iteration of type
        for(let type of ["app"/* , "api", "asset" */])

            // Check router
            if((type in doc["Router"]) && doc["Router"][type].length)

                // Iteration of current router type
                for(let router of doc["Router"][type])

                    // Check name and if file exists
                    if(router !== null && ("name" in router) && router["name"] && fs.existsSync("./app/Environment/Page/"+router.name+"/index.ts")){

                        // Current collection
                        result["page/"+type+"/"+router.name] = "./app/Environment/Page/"+router.name+"/index.ts";

                    }

    // Return result
    return result;

}

// Export function
exports.load = load;