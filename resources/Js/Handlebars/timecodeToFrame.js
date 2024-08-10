/**
 * Handlebars Array Helpers
 *
 * @source https://github.com/helpers/handlebars-helpers
 * 
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */

/**
 * Timecode To Frame
 * 
 * Convert timecode to frame
 *
 * @param a value
 * @param b value
 * @param Object options
 * 
 * @return number
 */
module.exports = (tc, fps, options) => {

    // Regular expression to match the TC format "HH:MM:SS:FF"
    const tcRegex = /^\d{2}:\d{2}:\d{2}:\d{2}$/;

    // Validate timecode format and fps
    if (!tcRegex.test(tc) || !Number.isInteger(fps) || fps <= 0)
        
         // Return the original timecode string if invalid
        return tc;

    // Split the timecode into its components (HH:MM:SS:FF)
    const [hours, minutes, seconds, frames] = tc.split(':').map(Number);

    // Additional validation on the components
    if (
        minutes < 0 || minutes >= 60 ||
        seconds < 0 || seconds >= 60 || 
        frames < 0 || frames >= fps
    )

        // Return the original timecode string if components are invalid
        return tc; 

    // Convert the timecode to the total number of frames
    const totalFrames = (hours * 3600 * fps) +
                        (minutes * 60 * fps) +
                        (seconds * fps) +
                        frames;

    return totalFrames;

}