/** 
 * Dependances
 */
 const ForkTsCheckerNotifierWebpackPlugin = require('fork-ts-checker-notifier-webpack-plugin');
 const ForkTsCheckerWebpackPlugin = require('fork-ts-checker-webpack-plugin');
 const path = require('path');
 
  /** 
   * Config
   */
  module.exports = {
     entry: {
         "index": "./app/Front/index.ts"
     },
     output: {
         filename: '[name].[fullhash:8].js',
         path: path.resolve(__dirname, 'public/dist'),
         clean: true,
     },
     resolve: {
         extensions: ['.tsx', '.ts', '.js'],
         extensionAlias: {
             '.ts': ['.js', '.ts'],
             '.cts': ['.cjs', '.cts'],
             '.mts': ['.mjs', '.mts'],
         },
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
                 test: /\.(woff|woff2|eot|ttf|otf)$/i,
                 type: 'asset/resource',
             },
             {
                 test: /.([cm]?ts|tsx)$/,
                 loader: 'ts-loader',
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
     ],
 };
