const path = require('path');
const fs = require('fs');
const Handlebars = require('handlebars');
const CopyWebpackPlugin = require('copy-webpack-plugin');

class HandlebarsToJSPlugin {
  constructor(options) {
    this.options = options;
    this.cleanDirectory = this.cleanDirectory.bind(this); // Bind cleanDirectory method
  }

  apply(compiler) {
    compiler.hooks.afterEmit.tapAsync('HandlebarsToJSPlugin', (compilation, callback) => {
      const partialsDir = this.options.partialDirs[0];
      const outputDir = path.resolve(__dirname, 'public/dist/partials');

      // Clean output directory before compilation
      this.cleanDirectory(outputDir);

      fs.readdir(partialsDir, (err, files) => {
        if (err) throw err;

        files.forEach(file => {
          const filePath = path.join(partialsDir, file);
          const fileContent = fs.readFileSync(filePath, 'utf-8');
          const compiledTemplate = Handlebars.precompile(fileContent);

          // Retrieve content hash from Webpack output filenames
          const outputFiles = fs.readdirSync(outputDir+"/..");
          const hashRegex = /index\.([a-f0-9]+)\.js/; // Adjust regex as per your filename format
          let contentHash = '';

          outputFiles.forEach(outputFile => {
            const match = outputFile.match(hashRegex);
            if (match) {
              contentHash = match[1];
            }
          });

          const jsFilePath = path.join(outputDir, `${file.replace(/\.hbs$/, '')}.${contentHash}.js`);
          fs.writeFileSync(jsFilePath, `module.exports = ${compiledTemplate};`);
        });

        callback();
      });
    });
  }

  // Function to clean directory
  cleanDirectory(directory) {
    if (fs.existsSync(directory)) {
      fs.readdirSync(directory).forEach(file => {
        const filePath = path.join(directory, file);
        fs.unlinkSync(filePath);
      });
    } else {
      fs.mkdirSync(directory, { recursive: true });
    }
  }
}

module.exports = HandlebarsToJSPlugin;