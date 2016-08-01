const path = require('path');

const config = {
  module: {
    loaders: [
      {
        test: /\.css?$/,
        loaders: ['style', 'raw'],
        include: path.resolve(__dirname, '../')
      },
      {
        loader: 'json-loader',
        test: /\.json$/
      }
    ]
  },
  resolve: {
    root: [
      path.resolve('./es6'),
      path.resolve('./es6/Components'),
      path.resolve('./es6/tests')
    ],
    alias: {
      invariant:              'fbjs/lib/invariant'
    }
  }
};

module.exports = config;
