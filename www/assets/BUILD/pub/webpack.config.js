const { resolve, join } = require('path');
const ExtractTextPlugin     = require('extract-text-webpack-plugin');

const bowerDir       = resolve(__dirname, './bower_components');
const nodeModulesDir = resolve(__dirname, './node_modules');
const webpack = require('webpack');
const config = {
  cache: true,
  devtool: 'eval',
  entry: {
    // Portal
    widget_loader:               join(__dirname, 'build/widget_loader.js'),
    'widget_loader.min':         join(__dirname, 'build/widget_loader.min.js'),
    embed_loader:                join(__dirname, 'build/embed_loader.js'),
    'embed_loader.min':          join(__dirname, 'build/embed_loader.min.js'),
    hit_recorder:                join(__dirname, 'build/hit_recorder.js'),
    'hit_recorder.min':          join(__dirname, 'build/hit_recorder.min.js'),
    iframeResizer_contentWindow: './node_modules/iframe-resizer/js/iframeResizer.contentWindow.js',

    DeskPRO_PortalBundle: './src/DeskPRO/Bundle/PortalBundle/DeskPRO_PortalBundle',

    DeskPRO_PortalBundle_style:     './src/DeskPRO/Bundle/PortalBundle/Resources/style/portal-ltr-style.scss',
    DeskPRO_PortalBundle_rtl_style: './src/DeskPRO/Bundle/PortalBundle/Resources/style/portal-rtl-style.scss',

    DeskPRO_PortalBundle_vendors_style: './src/DeskPRO/Bundle/PortalBundle/Resources/style/vendors-style.scss',

    DeskPRO_PortalBundle_GuidePdf_style: './src/DeskPRO/Bundle/PortalBundle/Resources/style/guide_pdf.scss',

    DeskPRO_PortalBundle_iestyle:  './src/DeskPRO/Bundle/PortalBundle/Resources/style/ie-overrides.scss',
    DeskPRO_PortalBundle_ie8style: './src/DeskPRO/Bundle/PortalBundle/Resources/style/ie8-overrides.scss',
    DeskPRO_PortalBundle_ie9style: './src/DeskPRO/Bundle/PortalBundle/Resources/style/ie9-overrides.scss',

    api_message_style: './src/DeskPRO/Bundle/AppBundle/Resources/style/api/message.scss',

    DeskPRO_PortalBundle_print_style: './src/DeskPRO/Bundle/PortalBundle/Resources/style/print-style.scss',

    // Widget
    DeskPRO_WidgetBundle:        [
      'webpack-hot-middleware/client?path=http://localhost:9666/__webpack_hmr',
      './src/DeskPRO/Bundle/WidgetBundle/DeskPRO_WidgetBundle',
    ],
    DeskPRO_WidgetBundle_style:  './src/DeskPRO/Bundle/WidgetBundle/Resources/style/widget-style.scss',
    DeskPRO_EmbedFormBundle:     './src/DeskPRO/Bundle/WidgetBundle/DeskPRO_EmbedFormBundle',
    DeskPRO_EmbedHelpdeskBundle: './src/DeskPRO/Bundle/WidgetBundle/DeskPRO_EmbedHelpdeskBundle',

    // Agent
    phonenumber_utils: './node_modules/intl-tel-input/lib/libphonenumber/build/utils',
    // // DeskPRO_AgentBundle: './src/DeskPRO/Bundle/AgentBundle/DeskPRO_AgentBundle',
    // // DeskPRO_AgentBundle_style: './src/DeskPRO/Bundle/AgentBundle/Resources/style/agent-style.scss',
    DeskPRO_AgentLegacyBundle: [
      'webpack-hot-middleware/client?path=http://localhost:9666/__webpack_hmr',
      './src/DeskPRO/Bundle/AgentBundle/DeskPRO_AgentLegacyBundle'
    ],
    DeskPRO_AgentLegacyBundle_style: './src/DeskPRO/Bundle/AgentBundle/Resources/style/legacy-agent.scss',
    //
    // Admin
    DeskPRO_AdminBundle:       [
      'webpack-hot-middleware/client?path=http://localhost:9666/__webpack_hmr',
      './src/DeskPRO/Bundle/AdminBundle/DeskPRO_AdminBundle'
    ],
    DeskPRO_AdminBundle_style: './src/DeskPRO/Bundle/AdminBundle/Resources/style/admin-style.scss',
    //
    // Report
    DeskPRO_ReportBundle:       [
      'webpack-hot-middleware/client?path=http://localhost:9666/__webpack_hmr',
      './src/DeskPRO/Bundle/ReportBundle/DeskPRO_ReportBundle',
    ],
    DeskPRO_ReportBundle_style: './src/DeskPRO/Bundle/ReportBundle/Resources/style/report-style.scss',
    //
    // Demo
    DeskPRO_DemoBundle:       [
      'webpack-hot-middleware/client?path=http://localhost:9666/__webpack_hmr',
      './src/DeskPRO/Bundle/DemoBundle/DeskPRO_DemoBundle',
    ],
    DeskPRO_DemoBundle_style: './src/DeskPRO/Bundle/DemoBundle/Resources/style/demo-style.scss'
  },

  output: {
    path:              resolve(__dirname, 'build/'),
    pathinfo:          true,
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
            loader: 'react-hot-loader'
          },
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
            loader: 'file-loader'
          }
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
    }),
    new webpack.DefinePlugin({
      'process.env.NODE_ENV': ('"development"'),
      __DEV__:                true
    }),
    new webpack.ProvidePlugin({
      $:      'jquery',
      jQuery: 'jquery',
    }),
    new webpack.LoaderOptionsPlugin({
      debug: true
    }),
    new webpack.HotModuleReplacementPlugin(),
    new webpack.NoEmitOnErrorsPlugin(),
  ],

  node: { fs: 'empty' }
};

module.exports = config;
