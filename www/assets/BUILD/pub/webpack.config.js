const { resolve } = require('path');
const ExtractTextPlugin     = require('extract-text-webpack-plugin');

const bowerDir       = resolve(__dirname, './bower_components');
const nodeModulesDir = resolve(__dirname, './node_modules');
const webpack = require('webpack');
const config = {
  entry: {
    DeskPRO_PortalBundle: './src/DeskPRO/Bundle/PortalBundle/DeskPRO_PortalBundle',

    DeskPRO_PortalBundle_style     : './src/DeskPRO/Bundle/PortalBundle/Resources/style/portal-ltr-style.scss',
    DeskPRO_PortalBundle_rtl_style : './src/DeskPRO/Bundle/PortalBundle/Resources/style/portal-rtl-style.scss',

    DeskPRO_PortalBundle_vendors_style : './src/DeskPRO/Bundle/PortalBundle/Resources/style/vendors-style.scss',

    DeskPRO_PortalBundle_GuidePdf_style : './src/DeskPRO/Bundle/PortalBundle/Resources/style/guide_pdf.scss',

    DeskPRO_PortalBundle_iestyle  : './src/DeskPRO/Bundle/PortalBundle/Resources/style/ie-overrides.scss',
    DeskPRO_PortalBundle_ie8style : './src/DeskPRO/Bundle/PortalBundle/Resources/style/ie8-overrides.scss',
    DeskPRO_PortalBundle_ie9style : './src/DeskPRO/Bundle/PortalBundle/Resources/style/ie9-overrides.scss',
    // DeskPRO_AgentBundle: './src/DeskPRO/Bundle/AgentBundle/DeskPRO_AgentBundle',
    // DeskPRO_AgentBundle_style: './src/DeskPRO/Bundle/AgentBundle/Resources/style/agent-style.scss',
    // DeskPRO_AgentLegacyBundle: './src/DeskPRO/Bundle/AgentBundle/DeskPRO_AgentLegacyBundle',
    DeskPRO_AgentLegacyBundle_style: './src/DeskPRO/Bundle/AgentBundle/Resources/style/legacy-agent.scss'
  },

  output: {
    path:              resolve(__dirname, 'build/'),
    publicPath:        'http://localhost:9666/pub/build/',
    filename:          '[name].js',
    sourceMapFilename: '[name].map'
  },

  resolve: {
    modules: [
      resolve(__dirname, 'node_modules'),
      resolve(__dirname, 'src')
    ],
    alias: {
      'jquery.ui.widget': 'jquery.ui.widget/jquery.ui.widget'
    }
  },

  module: {
    rules: [
      {
        test: /\.js$/,
        use:  [
          {
            loader:  'babel-loader',
            options: {
              cacheDirectory: true
            }
          }
        ],
        include: [
          resolve(__dirname, 'src/DeskPRO')
        ]
      },
      {
        test: /\.(png|gif|jpg|jpeg|woff|woff2|ttf|eot|svg|mp3|ogg|wav)(\?|$)/,
        use:  [
          {
            loader:  'file-loader',
            options: {
              context: 'src',
              name:    '[path][name].[ext]'
            }
          }
        ],
        include: [
          resolve(__dirname, 'src/DeskPRO'),
          resolve(__dirname, 'node_modules/@deskpro'),
          resolve(__dirname, 'node_modules/bourbon'),
          resolve(__dirname, 'node_modules/bourbon-neat'),
          resolve(__dirname, 'node_modules/font-awesome'),
          resolve(__dirname, 'node_modules/intl-tel-input'),
          resolve(__dirname, 'node_modules/flag-icon-css'),
          resolve(__dirname, 'node_modules/cropper')
        ]
      },
      {
        test:    /\.scss$/,
        include: [
          resolve(__dirname, 'src/DeskPRO/Bundle/AdminBundle/Resources/style'),
          resolve(__dirname, 'src/DeskPRO/Bundle/ReportBundle/Resources/style'),
          resolve(__dirname, 'src/DeskPRO/Bundle/AgentBundle/Resources/style'),
          resolve(__dirname, 'src/DeskPRO/Bundle/PortalBundle/Resources/style'),
          resolve(__dirname, 'src/DeskPRO/Bundle/AppBundle/Resources/style'),
          resolve(__dirname, 'src/DeskPRO/Bundle/DemoBundle/Resources/style'),
          resolve(__dirname, 'src/DeskPRO/Bundle/WidgetBundle')
        ],

        use: ExtractTextPlugin.extract({
          fallback: 'style-loader',
          use:      [
            {
              loader:  'css-loader',
              options: {
                sourceMap: true
              }
            },
            {
              loader:  'sass-loader',
              options: {
                sourceMap:    true,
                outputStyle:  'expanded',
                includePaths: [bowerDir, nodeModulesDir],
              }
            }
          ],
          publicPath: './'
        })
      }
    ]
  },
  plugins: [
    new ExtractTextPlugin({
      filename: '[name].css'
    })
  ],

  node: { fs: 'empty' }
};

module.exports = config;