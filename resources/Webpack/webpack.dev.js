/**
 * Webpack config (Dev)
 *
 * Webpack config for dev and watch
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */

/** 
 * Dependances
 */
const ForkTsCheckerNotifierWebpackPlugin = require('fork-ts-checker-notifier-webpack-plugin');
const ForkTsCheckerWebpackPlugin = require('fork-ts-checker-webpack-plugin');
var helpers = require('handlebars-helpers')(["comparison", "object"]);
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
 */
module.exports = {
    mode: "development",
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
                /* generator: {
                    filename: 'svg/[name].svg',
                }, */
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
    },
    plugins: [
        new ForkTsCheckerWebpackPlugin(),
        new ForkTsCheckerNotifierWebpackPlugin({
            title: 'TypeScript',
            excludeWarnings: false,
        }),
        new DefinePlugin({
            'process.env.NODE_ENV': JSON.stringify('development'),
        }),
    ]
};
