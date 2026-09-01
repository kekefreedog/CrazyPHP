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
 * Is
 * 
 * Block helper that renders a block if a is equal to b. 
 * If an inverse block is specified it will be rendered when falsy. 
 * Similar to eq but does not do strict equality.
 * 
 * @param a Value to compare
 * @param v Value to compare with
 * 
 * @return boolean
 */
module.exports = function(a, b, options) {

    // Declare result
    let result;
    
    // Set smotth (default: true)
    let exact = [true, "true"].includes(options.hash?.exact)
        ? true
        : false
    ;

    // Check exact
    if(exact)

        // Set result
        result = (a === b) 
            ? options.fn(this) : 
            options.inverse(this)
        ;

    else

        // Return result
        result = (a == b) 
            ? options.fn(this) : 
            options.inverse(this)
        ;

    // Return result
    return result;
    
};