const path    = require('path');
const webpack = require('webpack');

const config = {
  module: {
    rules: [
      {
        test: /\.css?$/,
        use: [
          {
            loader: 'style-loader',
          },
          {
            loader: 'raw-loader',
          }
        ],
        include: path.resolve(__dirname, '../../')
      },
      {
        test: /\.(svg|png|jpg|mp3|wav|ogg)$/,
        use: [
          {
            loader:  'url-loader'
          }
        ]
      }
    ]
  },
  resolve: {
    modules: [
      path.resolve('./src'),
      path.resolve('./src/DeskPRO/Component'),
      path.resolve('./src/tests'),
      'node_modules'
    ],
    alias: {
      'jquery.ui':        'jquery-ui',
      'jquery.ui.widget': 'jquery.ui.widget/jquery.ui.widget'
    }
  },
  plugins: [
    new webpack.ProvidePlugin({
      $:      'jquery',
      jQuery: 'jquery'
    })
  ],
  node: { fs: 'empty' }
};

module.exports = config;
