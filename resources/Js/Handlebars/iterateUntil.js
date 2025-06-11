/**
 * Handlebars Comparaison Helpers
 *
 * @source https://github.com/helpers/handlebars-helpers
 * 
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */

/**
 * Iterate Until
 * 
 * Injects a loop range into the template.
 * 
 * @param max Value to compare
 * @param options Value to compare
 */
module.exports = function(max, options) {
    
    // Set start at one
    const startAtOne = true;

    // Check hasj
    if("startAtOne" in options.hash){

        // Update start at one
        startAtOne = options.hash.startAtOne === true;

    }

    // Set start
    const start = startAtOne ? 1 : 0;

    // Set output
    let output = "";

    // Set maxCalculated
    let maxCalculated = 0;

    // Set maxCalculated
    if(Array.isArray(max)){

        // Set max
        maxCalculated = max.length;

    }else
    // Check is numeric
    if(!isNaN(Number(max))){

        // Set max
        maxCalculated = Number(max);

    }else
    // Check is string
    if(typeof max === "string"){

        // Set max
        maxCalculated = max.length;

    }
    
    // Set count
    const count = maxCalculated - start + 1;

    // Iteration
    for (let i = start; i <= maxCalculated; i++) {
    
        // Set current index
        const currentIndex = i - start;

        // Set output
        output += options.fn({
            "i":i,
            "@index": currentIndex,
            "@first": currentIndex === 0,
            "@last": currentIndex === count - 1,
        });

    }

    // Return output
    return output;
    
};