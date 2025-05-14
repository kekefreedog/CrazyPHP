/**
 * Handlebars Strings Helpers
 *
 * @source https://github.com/helpers/handlebars-helpers
 * 
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */

/**
 * Background Gradient
 * 
 * Generate background color
 * 
 * @param a Object to stringify
 * 
 * @return string
 */
module.exports = function(colors, options) {
    
    // Set smotth (default: true)
    const smooth = options.hash?.smooth !== true;

    // Check colors
    if (!Array.isArray(colors) || colors.length === 0) 
        
        // Return empty string
        return '';
  
    // Check colors length
    if (colors.length === 1)

        // Return simple background
        return `background: rgb(${colors[0]});`;
  
    // Check smooth
    if(smooth){

        // Set steps
        const stops = colors
            .map((color, i) => {
            const pos = (i / (colors.length - 1)) * 100;
            return `rgba(${color}) ${pos}%`;
            })
            .join(', ')
        ;

        // Return style
        return `background: linear-gradient(to bottom, ${stops});`;

    }else{

        // Set steps
        const stops = colors
            .map((color, i) => {
            const start = (i / colors.length) * 100;
            const end = start + 100 / colors.length;
            return `rgba(${color}) ${start}%, rgba(${color}) ${end}%`;
            })
            .join(', ')
        ;

        // Return style
        return `background: linear-gradient(to bottom, ${stops});`;
        
    }

};