/**
 * Webpack config
 *
 * Webpack config for production builds, dev builds and watch, selected by --mode
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2026 Kévin Zarshenas
 */

/**
 * Dependencies
 */
const ForkTsCheckerNotifierWebpackPlugin = require('fork-ts-checker-notifier-webpack-plugin');
const ForkTsCheckerWebpackPlugin = require('fork-ts-checker-webpack-plugin');
const CssMinimizerPlugin = require('css-minimizer-webpack-plugin');
const TerserPlugin = require('terser-webpack-plugin');
const { DefinePlugin } = require('webpack');
const yaml = require('js-yaml');
const path = require('path');
const fs = require('fs');

/**
 * Open Custom Script
 */
const routers = require("./vendor/kzarshenas/crazyphp/resources/Webpack/routers/index.ts");
const routersCollection = routers.load(yaml, fs);

/**
 * Config
 *
 * @param env Webpack environment variables
 * @param argv Webpack CLI arguments, including --mode
 * @returns {object}
 */
module.exports = (env, argv) => {

    // Webpack builds for production when no mode is given
    const production = (argv.mode ?? "production") === "production";

    // Return config
    return {
        mode: production ? "production" : "development",
        entry: {
            "index": "./app/Front/index.ts",
            ...routersCollection
        },
        output: {
            filename: '[name].[fullhash:8].js',
            path: path.resolve(__dirname, 'public/dist'),
            clean: true,
        },
        resolve: {
            // Let Composer-linked library sources resolve the application's dependencies.
            modules: ["node_modules", path.resolve(__dirname, "node_modules")],
            // Share kmaterialize with library code that still imports the original package name.
            alias: {"@materializecss/materialize": path.resolve(__dirname, "node_modules/kmaterialize")},
            extensions: ['.tsx', '.ts', '.js'],
            extensionAlias: {
                '.ts': ['.ts'],
                '.cts': ['.cjs', '.cts'],
                '.mts': ['.mjs', '.mts'],
                '.js': ['.js'],
            },
            symlinks: true
        },
        module: {
            rules: [
                {
                    test: /\.s[ac]ss$/i,
                    use: [
                        "style-loader",
                        "css-loader",
                        "sass-loader",
                    ],
                },
                {
                    test: /.([cm]?ts|tsx)$/,
                    loader: 'ts-loader',
                },
                {
                    test: /\.ya?ml$/,
                    use: 'yaml-loader'
                },
                {
                    test: /\.(handlebars|hbs)$/,
                    loader: "handlebars-loader",
                    options: {
                        partialDirs: [
                            path.resolve(__dirname, "./assets/Hbs/partials/"),
                        ],
                        helperDirs: [
                            path.resolve(__dirname, "./vendor/kzarshenas/crazyphp/resources/Js/Handlebars"),
                            path.resolve(__dirname, "./assets/Js/Handlebars"),
                        ],
                    },
                },
                {
                    test: /\.(woff|woff2|eot|ttf)$/,
                    type: 'asset/resource',
                    generator: {
                        filename: 'fonts/[name][ext]',
                    }
                },
                {
                    test: /\.svg$/,
                    loader: 'svg-inline-loader'
                },
            ],
        },
        optimization: {
            moduleIds: 'deterministic',
            runtimeChunk: 'single',
            splitChunks: {
                cacheGroups: {
                    vendor: {
                        test: /[\\/]node_modules[\\/]/,
                        name: 'vendors',
                        chunks: 'all',
                    },
                },
            },
            // Minify scripts without console calls and styles in production only
            ...(production ? {
                minimize: true,
                minimizer: [
                    new TerserPlugin({
                        terserOptions: {
                            compress: {
                                drop_console: true,
                            },
                        },
                    }),
                    new CssMinimizerPlugin(),
                ],
            } : {}),
        },
        plugins: [
            new ForkTsCheckerWebpackPlugin(),
            new ForkTsCheckerNotifierWebpackPlugin({
                title: 'TypeScript',
                excludeWarnings: false,
            }),
            new DefinePlugin({
                'process.env.NODE_ENV': JSON.stringify(production ? 'production' : 'development'),
            }),
        ]
    };

};
