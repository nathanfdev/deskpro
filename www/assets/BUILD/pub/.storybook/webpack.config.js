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
      path.resolve('./src'),
      path.resolve('./src/DeskPRO/Component'),
      path.resolve('./src/tests')
    ]
  }
};

module.exports = config;
