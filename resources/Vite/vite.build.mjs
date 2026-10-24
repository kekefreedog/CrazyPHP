/**
 * Vite Build
 *
 * Compile CrazyPHP's PHP-served entrypoints and lazy pages as classic scripts.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */

/** Dependencies
 ******************************************************
 */
import {existsSync, readFileSync, realpathSync, rmSync, mkdirSync, watch as watchDirectory} from "node:fs";
import {resolve} from "node:path";
import {randomBytes} from "node:crypto";
import {totalmem} from "node:os";
import Handlebars from "handlebars";
import ts from "typescript";
import * as yaml from "js-yaml";
import {build} from "vite";

/** Build Settings
 ******************************************************
 */

// Resolve every generated file from the application working directory.
const root = process.cwd();

// Keep one hash across the main bundle and every lazily loaded page.
const hash = randomBytes(4).toString("hex");

// Read explicit compiler arguments forwarded by CrazyFront or npm.
const buildArguments = process.argv.slice(2);

// Accept both mode syntaxes and let the last option override script defaults.
const modeIndex = buildArguments.findLastIndex(argument => argument === "--mode" || argument.startsWith("--mode="));
const mode = modeIndex < 0 ? "production" : (buildArguments[modeIndex].split("=")[1] ?? buildArguments[modeIndex + 1]);

// Reject an incomplete option before removing existing build outputs.
if(!mode || mode.startsWith("-"))
    throw new Error("--mode requires a value.");

// Support explicit watch overrides while retaining the generated watch script contract.
const watchOption = buildArguments.filter(argument => ["--watch", "--watch=true", "--watch=false", "--no-watch", "-w"].includes(argument)).at(-1);
const watch = ["--watch", "--watch=true", "-w"].includes(watchOption);

// Load the existing route schema without adding bundler-specific fields.
const config = yaml.load(readFileSync(resolve(root, "config/Router.yml"), "utf8"));

// Read the application's declared packages.
const manifest = JSON.parse(readFileSync(resolve(root, "package.json"), "utf8"));

// Resolve them from the application, so linked libraries share one copy and find its optional peers.
const dedupe = Object.keys({...manifest.dependencies, ...manifest.devDependencies}).filter(name => name !== "crazyphp");

// Share framework packages from the main bundle, so every page reuses one instance of each.
const shared = (manifest.crazyphp?.shared ?? ["crazyphp", "kmaterialize", "handlebars/runtime"]).filter(name => existsSync(resolve(root, "node_modules", name.split("/").slice(0, name.startsWith("@") ? 2 : 1).join("/"))));

// Name each shared package on the registry filled by the main bundle.
const sharedGlobals = Object.fromEntries(shared.map(name => [name, `CrazyPHPShared.${name.replace(/[^A-Za-z0-9_$]/g, "_")}`]));

// Always build the application entrypoint.
const entries = {index: resolve(root, "app/Front/index.ts")};

// Discover page entrypoints using the same names as CrazyPHP's lazy loader.
for(const route of config?.Router?.app ?? []){

    // Resolve the optional page source.
    const source = resolve(root, `app/Environment/Page/${route?.name}/index.ts`);

    // Ignore routes that have no browser page.
    if(route?.name && existsSync(source))
        entries[`page/app/${route.name}`] = source;

}

/** Compatibility Plugin
 ******************************************************
 */

/**
 * Crazy Sources
 *
 * Compile existing static require calls, YAML and Handlebars templates.
 *
 * @returns {object} Vite plugin
 */
function crazySources(){

    // Return fresh plugin state for each independently loaded entrypoint.
    return {
        name: "crazyphp-sources",
        enforce: "pre",

        /**
         * Transform
         *
         * @param {string} code Source text
         * @param {string} id Source filename
         * @returns {object|null} Transformed module
         */
        transform(code, id){

            // Compile YAML imports as ordinary data modules.
            if(/\.ya?ml$/.test(id))
                return {code: `export default ${JSON.stringify(yaml.load(code))};`, map: null};

            // Precompile templates and register their referenced helpers and partials.
            if(/\.(hbs|handlebars)$/.test(id)){

                // Parse template references without scanning comments or string literals.
                const ast = Handlebars.parse(code);

                // Collect imported registrations once per template.
                const imports = new Map();

                /**
                 * Visit
                 *
                 * @param {object} node Handlebars syntax node
                 * @returns {void}
                 */
                const visit = node => {

                    // Ignore scalar values in the syntax tree.
                    if(!node || typeof node !== "object")
                        return;

                    // Identify static partial references and named helpers.
                    const partial = node.type === "PartialStatement" || node.type === "PartialBlockStatement";

                    // Resolve helper paths and partial names from their syntax nodes.
                    const name = partial ? node.name?.original : node.path?.original;

                    // Register known files while leaving built-in helpers untouched.
                    if(name && !name.startsWith("@")){

                        // Search application helpers first, then framework defaults.
                        const candidates = partial ? [resolve(root, `assets/Hbs/partials/${name}.hbs`)] : [
                            resolve(root, `assets/Js/Handlebars/${name}.js`),
                            resolve(root, `vendor/kzarshenas/crazyphp/resources/Js/Handlebars/${name}.js`),
                        ];

                        // Find the matching source file.
                        const file = candidates.find(candidate => existsSync(candidate));

                        // Deduplicate imports and avoid importing the current template recursively.
                        if(file && file !== id)
                            imports.set(`${partial ? "Partial" : "Helper"}:${name}`, file);

                    }

                    // Traverse nested blocks and subexpressions.
                    for(const value of Object.values(node)){

                        // Visit every item of syntax arrays.
                        if(Array.isArray(value))
                            value.forEach(visit);

                        // Visit nested syntax nodes.
                        else if(value && typeof value === "object")
                            visit(value);

                    }

                };

                // Gather registrations before emitting the compiled template.
                visit(ast);

                // Share the Handlebars runtime within this bundle.
                let output = 'import Handlebars from "handlebars/runtime";\n';

                // Assign unique local identifiers to imported helpers and partials.
                let index = 0;

                // Emit registrations before creating the template function.
                for(const [key, file] of imports){

                    // Separate registration type from the original reference name.
                    const [kind, name] = key.split(":");

                    // Import the matching helper or compiled partial.
                    output += `import reference${index} from ${JSON.stringify(file)};\n`;

                    // Register the reference under its template name.
                    output += `Handlebars.register${kind}(${JSON.stringify(name)}, reference${index});\n`;

                    // Advance the generated identifier.
                    index++;

                }

                // Export the same callable template contract used by handlebars-loader.
                return {code: output + `export default Handlebars.template(${Handlebars.precompile(code)});`, map: null};

            }

            // Leave third-party CommonJS packages to Vite's native converter.
            if(!/\.[cm]?[jt]sx?$/.test(id) || id.includes("/node_modules/"))
                return null;

            // Parse TypeScript so comments and quoted text remain unchanged.
            const source = ts.createSourceFile(id, code, ts.ScriptTarget.Latest, true);

            // Gather static requires before rewriting their source ranges.
            const replacements = [];

            // Keep generated imports separate from the original module body.
            const imports = [];

            /**
             * Visit Require
             *
             * @param {object} node TypeScript syntax node
             * @returns {void}
             */
            const visitRequire = node => {

                // Convert only static one-argument CommonJS calls.
                if(ts.isCallExpression(node) && node.expression.getText(source) === "require" && node.arguments.length === 1 && ts.isStringLiteral(node.arguments[0])){

                    // Read the original module specifier.
                    let specifier = node.arguments[0].text;

                    // Recognize the scaffold's inline component stylesheet convention.
                    const inlineStyle = specifier.startsWith("!!css-loader!sass-loader!");

                    // Let Vite compile the stylesheet and expose its CSS string.
                    if(inlineStyle)
                        specifier = specifier.slice("!!css-loader!sass-loader!".length) + "?inline";

                    // Assign a unique identifier for this import.
                    const name = `crazyImport${imports.length}`;

                    // Preserve side-effect-only imports without requesting nonexistent exports.
                    if(ts.isExpressionStatement(node.parent)){

                        // Import the module for its initialization.
                        imports.push(`import ${JSON.stringify(specifier)};`);

                        // Replace the original require statement with an inert expression.
                        replacements.push([node.getStart(source), node.end, "void 0"]);

                    }else{

                        // Import a namespace to support both CommonJS and default exports.
                        imports.push(`import * as ${name} from ${JSON.stringify(specifier)};`);

                        // Preserve css-loader's default.toString contract for legacy components.
                        replacements.push([node.getStart(source), node.end, inlineStyle ? `{default: ${name}.default}` : `(${name}.default ?? ${name})`]);

                    }

                }

                // Inspect nested expressions without rewriting comments.
                ts.forEachChild(node, visitRequire);

            };

            // Collect static imports from this source file.
            visitRequire(source);

            // Apply edits backwards so original offsets stay valid.
            for(const [start, end, value] of replacements.reverse())
                code = code.slice(0, start) + value + code.slice(end);

            // Convert the framework's small CommonJS helper modules to default exports.
            if(id.includes("/Handlebars/") && code.includes("module.exports ="))
                code = code.replace("module.exports =", "export default");

            // Return a module that Vite can compile normally.
            return {code: imports.join("\n") + "\n" + code, map: null};

        },

        /**
         * Generate Bundle
         *
         * @param {object} options Output options
         * @param {object} bundle Output files
         * @returns {void}
         */
        generateBundle: {
            order: "post",
            handler(options, bundle){

                // Inject compiled CSS because PHP serves the page without a Vite HTML entry.
                for(const [name, asset] of Object.entries(bundle)){

                    // Handle generated CSS assets only.
                    if(asset.type !== "asset" || !name.endsWith(".css"))
                        continue;

                    // Generate a style initializer shared with the entrypoint.
                    const injection = `var style=document.createElement("style");style.textContent=${JSON.stringify(String(asset.source))};document.head.appendChild(style);`;

                    // Attach CSS to the entrypoint that owns this independent build.
                    for(const chunk of Object.values(bundle))
                        if(chunk.type === "chunk" && chunk.isEntry)
                            chunk.code = `(function(){${injection}})();\n` + chunk.code;

                    // Remove CSS files that no longer need separate registration.
                    delete bundle[name];

                }

            },
        },
    };

}

/** Shared Plugin
 ******************************************************
 */

/**
 * Crazy Shared
 *
 * Wrap the main entrypoint so it publishes shared packages before the application runs.
 *
 * @param {string} entry Main entrypoint source
 * @returns {object} Vite plugin
 */
function crazyShared(entry){

    // Return the wrapper and registry modules.
    return {
        name: "crazyphp-shared",

        /**
         * Resolve Id
         *
         * @param {string} source Requested module
         * @returns {string|null} Virtual module id
         */
        resolveId(source){

            // Keep virtual modules out of the filesystem resolver.
            return ["virtual:crazyphp-index", "virtual:crazyphp-shared"].includes(source) ? "\0" + source : null;

        },

        /**
         * Load
         *
         * @param {string} id Module id
         * @returns {string|null} Virtual module source
         */
        load(id){

            // Register shared packages first, then run the application entrypoint.
            if(id === "\0virtual:crazyphp-index")
                return `import "virtual:crazyphp-shared";\nimport ${JSON.stringify(entry)};\n`;

            // Publish each shared package namespace under its page global.
            if(id === "\0virtual:crazyphp-shared")
                return shared.map((name, index) => `import * as shared${index} from ${JSON.stringify(name)};`).join("\n")
                    + `\nwindow.CrazyPHPShared = {${shared.map((name, index) => `${sharedGlobals[name].split(".")[1]}: shared${index}`).join(", ")}};\n`;

            // Leave other modules to Vite.
            return null;

        },
    };

}

/** Watch Files Plugin
 ******************************************************
 */

/**
 * Crazy Watch Files
 *
 * Record every file read by a build, including stylesheet partials.
 *
 * @param {Set<string>} files Collected file paths
 * @returns {object} Vite plugin
 */
function crazyWatchFiles(files){

    // Return the collector plugin.
    return {
        name: "crazyphp-watch-files",

        /**
         * Build End
         *
         * @returns {void}
         */
        buildEnd(){

            // Keep real files only, without virtual modules.
            for(const file of this.getWatchFiles())
                if(file.startsWith("/"))
                    files.add(file.split("?")[0]);

        },
    };

}

/** Compilation
 ******************************************************
 */

// Clear stale assets once, before compiling independent page entrypoints.
rmSync(resolve(root, "public/dist"), {recursive: true, force: true});

// Ensure output exists before starting watch mode.
mkdirSync(resolve(root, "public/dist"), {recursive: true});

/**
 * Compile
 *
 * Build one entry as a classic script compatible with initial page loads and the lazy loader.
 *
 * @param {string} name Output name
 * @param {string} entry Entry source
 * @returns {Promise<Set<string>>} Files the entry depends on
 */
async function compile(name, entry){

    // Collect the files read for this entry.
    const files = new Set();

    // Compile each entry without shared ESM chunks or an HTML shell.
    await build({
        configFile: false,
        root,
        mode,
        base: "/dist/",
        publicDir: false,
        // Use the selected UI framework for legacy imports inside the linked library.
        resolve: {alias: {"@materializecss/materialize": resolve(root, "node_modules/kmaterialize")}, dedupe},
        // The main bundle publishes shared packages; pages reference them instead of bundling copies.
        plugins: [crazySources(), ...(name === "index" ? [crazyShared(entry)] : []), crazyWatchFiles(files)],
        define: {"process.env.NODE_ENV": JSON.stringify(mode === "development" ? "development" : "production")},
        css: {preprocessorOptions: {scss: {silenceDeprecations: ["legacy-js-api", "import", "global-builtin", "color-functions"]}}},
        build: {
            outDir: resolve(root, "public/dist"),
            emptyOutDir: false,
            minify: mode === "production",
            assetsInlineLimit: 0,
            rollupOptions: {
                input: name === "index" ? "virtual:crazyphp-index" : entry,
                external: name === "index" ? [] : shared,
                output: {
                    format: "iife",
                    name: "CrazyPHPEntry",
                    globals: sharedGlobals,
                    interop: "esModule",
                    entryFileNames: `${name}.${hash}.js`,
                    assetFileNames: "assets/[name]-[hash][extname]",
                    inlineDynamicImports: true,
                },
            },
        },
    });

    // Return the dependencies of this entry.
    return files;

}

// Use one page build per 2 GiB of memory, at most four, unless set explicitly.
const concurrency = Number(process.env.CRAZYPHP_VITE_CONCURRENCY) || Math.max(1, Math.min(4, Math.floor(totalmem() / 2 ** 31)));

// Keep the files each entry depends on for watch mode.
const dependencies = new Map();

// Build the main bundle first, since it publishes the shared packages.
dependencies.set("index", await compile("index", entries.index));

// Build pages a few at a time; nothing stays in memory once a build ends.
const pages = Object.entries(entries).filter(([name]) => name !== "index");
for(let index = 0; index < pages.length; index += concurrency)
    await Promise.all(pages.slice(index, index + concurrency).map(async ([name, entry]) => dependencies.set(name, await compile(name, entry))));

/** Watch
 ******************************************************
 */

// Rebuild only the entries using a changed file, one at a time.
if(watch){

    // Watch application sources and the real folders of linked packages.
    const directories = [resolve(root, "app"), resolve(root, "assets")];

    // Find linked packages, whose real folder lies outside node_modules.
    for(const name of ["crazyphp", ...dedupe]){

        // Resolve the installed package folder.
        const folder = resolve(root, "node_modules", name);

        // Watch linked folders once.
        if(existsSync(folder) && realpathSync(folder) !== folder && !directories.includes(realpathSync(folder)))
            directories.push(realpathSync(folder));

    }

    // Changed files waiting for a rebuild.
    const changed = new Set();

    // Current rebuild and debounce timer.
    let running = null;
    let timer = null;

    /**
     * Rebuild
     *
     * @returns {Promise<void>}
     */
    const rebuild = async () => {

        // Take the pending changes.
        const files = [...changed];
        changed.clear();

        // Select entries that read one of the changed files.
        const targets = Object.keys(entries).filter(name => files.some(file => dependencies.get(name)?.has(file)));

        // Rebuild each affected entry, keeping the watcher alive on errors.
        for(const name of targets){

            // Measure the rebuild.
            const start = Date.now();

            try{

                // Refresh the bundle and its dependencies.
                dependencies.set(name, await compile(name, entries[name]));
                console.log(`\u21bb ${name} rebuilt in ${((Date.now() - start) / 1000).toFixed(1)}s`);

            }catch(error){

                // Report the failure and wait for the next change.
                console.error(`\u2717 ${name} failed: ${error.message}`);

            }

        }

        // Process changes made during this rebuild.
        running = changed.size ? rebuild() : null;

    };

    // Start one recursive watcher per folder.
    for(const directory of directories)
        watchDirectory(directory, {recursive: true}, (event, filename) => {

            // Resolve the changed file, ignoring installed packages.
            const file = filename ? resolve(directory, filename.toString()) : "";
            if(!file || file.includes("/node_modules/") || file.includes("/.git/"))
                return;

            // Queue the change and debounce bursts of saves.
            changed.add(file);
            clearTimeout(timer);
            timer = setTimeout(() => running ??= rebuild(), 150);

        });

    // Show the watcher is ready.
    console.log(`watching for file changes in ${directories.length} folder(s)...`);

}
