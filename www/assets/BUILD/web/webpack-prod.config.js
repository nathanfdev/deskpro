var webpack            = require('webpack');
var ExtractTextPlugin  = require('extract-text-webpack-plugin');
var WebpackStripLoader = require('strip-loader');
var devConfig          = require('./webpack.config.js');
var stripLoader        = {
  test:    [/\.js$/, /\.es6$/],
  exclude: /node_modules/,
  loader:  WebpackStripLoader.loader('console.log')
};

// devConfig.devtool         = 'source-map';
devConfig.output.pathinfo = false;
devConfig.plugins         = [
  new webpack.NoErrorsPlugin(),
  new ExtractTextPlugin('[name].css'),
  new webpack.optimize.DedupePlugin(),
  new webpack.optimize.UglifyJsPlugin({
    sourceMap: true,
    mangle:    false,
    exclude:   [/(node_modules|bower_components)/]
  }),
  new webpack.DefinePlugin({
    'process.env.NODE_ENV': '"production"',
    __DEV__:                false
  })
];

devConfig.module.loaders.push(stripLoader);
module.exports = devConfig;
