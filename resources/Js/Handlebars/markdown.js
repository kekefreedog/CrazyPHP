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
 * Markdown
 * 
 * Convert markdown
 * 
 * @param a Object to stringify
 * 
 * @return string
 */
module.exports = function(input, options) {
    
    let result = input;

    if(typeof input === "string" && input){

        let html = input;

        // Escape HTML first (basic safety)
        html = html
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');

        // Headings
        html = html.replace(/^###### (.*)$/gm, '<h6 style="margin: 0 0 0 0;">$1</h6>');
        html = html.replace(/^##### (.*)$/gm, '<h5 style="margin: 0 0 0 0;">$1</h5>');
        html = html.replace(/^#### (.*)$/gm, '<h4 style="margin: 0 0 0 0;">$1</h4>');
        html = html.replace(/^### (.*)$/gm, '<h3 style="margin: 0 0 0 0;">$1</h3>');
        html = html.replace(/^## (.*)$/gm, '<h2 style="margin: 0 0 0 0;">$1</h2>');
        html = html.replace(/^# (.*)$/gm, '<h1 style="margin: 0 0 0 0;">$1</h1>');

        // Bold
        html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');

        // Italic
        html = html.replace(
            /(\*|_)(?=[^*_]*[a-zA-Z])([a-zA-Z][^*_]*[a-zA-Z])\1/g,
            '<em>$2</em>'
        );

        // Inline code
        html = html.replace(/`([^`]+)`/g, '<code>$1</code>');

        // Links
        html = html.replace(
            /\[([^\]]+)\]\(([^)]+)\)/g,
            '<a href="$2" target="_blank" rel="noopener noreferrer">$1</a>'
        );

        // Paragraphs
        html = html
            .split(/\n{2,}/)
            .map(block => {
            if (/^<h\d|^<ul|^<ol|^<p|^<blockquote/.test(block)) {
                return block;
            }
            return `<p style="margin: 0 0 0 0;">${block.replace(/\n/g, '<br>')}</p>`;
            })
            .join('');

        result = html;

    }

    return result;

};