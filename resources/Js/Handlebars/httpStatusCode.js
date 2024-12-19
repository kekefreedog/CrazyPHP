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
 * Dependances
 */
const http_status_code = require("./../../Json/http_status_code.json");

/**
 * Http Status Code
 * 
 * Get Http Status Code Depending of code given
 * 
 * @param code Code of the error
 * @param what Wich parameter do you want, if you want all, set "*"
 * 
 * @return
 */
module.exports = function(code = 500, what = "*", options) {

    // Check if code is valid and in http_status_code
    if(!code || !(parseInt(code) in http_status_code)){

        // Error
        new Error(`Http code "${code}" given isn't valid`);

        // Stop
        return options.inverse(this);

    }

    // Get 500 code status collection
    let collection500 = http_status_code[500];

    // Declare result
    let result = {
        code: code ? code : 500,
        title: http_status_code[code].title ?? collection500.title,
        description: http_status_code[code].description ?? collection500.description,
        "icon-class": http_status_code[code].icon.class ?? collection500.icon.class,
        "icon-text": http_status_code[code].icon.text ?? collection500.icon.text,
        "primary-color": http_status_code[code].color.primary ?? collection500.color.primary,
        "secondary-color": http_status_code[code].color.secondary ?? collection500.color.secondary
    };

    // Check what
    if(!what || what != "*" || !(what in result)){

        // Error
        new Error(`What "${what}" given isn't valid, please use "*" to get all parameters available`);

        // Stop
        return options.inverse(this);

    }

    // Check if what is valid
    return what == "*" ? options.fn(result) : options.fn(result[what]);
    
};