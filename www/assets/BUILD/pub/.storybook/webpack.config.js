const path = require('path');

const config = {
  module: {
    loaders: [
      {
        test: /\.css?$/,
        loaders: ['style', 'raw'],
        include: path.resolve(__dirname, '../../')
      },
      {
        test: /\.(svg|png|jpg)$/,
        loader:  'url'
      },
      {
        test: /\.json$/,
        loader: 'json-loader'
      }
    ]
  },
  resolve: {
    root: [
      path.resolve('./src'),
      path.resolve('./src/DeskPRO/Component'),
      path.resolve('./src/tests')
    ]
  }
};

module.exports = config;
