const gulp                  = require('gulp');
const gutil                 = require('gulp-util');
const webpack               = require('webpack');
const express               = require('express');
const cors                  = require('cors');
const del                   = require('del');
const runSeq                = require('run-sequence');
const path                  = require('path');
const ExtractTextPlugin     = require('extract-text-webpack-plugin');
const WebpackNotifierPlugin = require('webpack-notifier');
const CopyWebpackPlugin     = require('copy-webpack-plugin');
const babel                 = require('babel-core');
const uglify                = require('uglify-js');
const fs                    = require('fs');
const sass                  = require('node-sass');
const reducerRefresh        = require('./build-tools/app-reducer-gen/loader').refreshBundle;
const spawn                 = require('child_process').spawn;
const webpackDevMiddleware  = require('webpack-dev-middleware');
const webpackHotMiddleware  = require('webpack-hot-middleware');

const bowerDir       = path.resolve(__dirname, './bower_components');
const nodeModulesDir = path.resolve(__dirname, './node_modules');

// ######################################################################################################################
// # Task Runners
// ######################################################################################################################

gulp.task('clean', (cb) => {
  del(['./build']).then(() => {
    cb();
  });
});

gulp.task('default', ['clean'], (cb) => {
  runSeq(['bundle'], cb);
});

// used in init-project-dev, builds to filesystem but without any optimisations
// so its quicker during a new project checkout
gulp.task('default-dev', ['clean'], (cb) => {
  runSeq(['bundle:dev'], cb);
});

gulp.task('prod', ['clean', 'priv:start-prod'], (cb) => {
  runSeq(['bundle'], cb);
});

gulp.task('dev', (cb) => {
  // prefer to use one at a time, build speed is faster
  // and you can still just open up two terminal winodws if you need both
  console.log('Use:');
  console.log('\tdev:agent    -  For the agent interface');
  console.log('\tdev:portal   -  For the portal');
  console.log('\tdev:widget   -  For the widget');
  console.log('\tdev:demo     -  For the demo');
  console.log('\tdev:all      -  For all');
  cb();
});

gulp.task('dev:agent', (cb) => {
  runSeq(['bundle:dev-server:agent'], cb);
});

gulp.task('dev:portal', (cb) => {
  runSeq(['bundle:dev-server:portal'], cb);
});

gulp.task('dev:widget', (cb) => {
  runSeq(['bundle:dev-server:widget'], cb);
});

gulp.task('dev:demo', (cb) => {
  runSeq(['bundle:dev-server:demo'], cb);
});

gulp.task('dev:all', (cb) => {
  runSeq(['bundle:dev-server'], cb);
});

// ######################################################################################################################
// # Helpers
// ######################################################################################################################

gulp.task('priv:start-prod', () => {

});

// ######################################################################################################################
// # Helpers
// ######################################################################################################################

function refreshWidgetLoader(loaderFilename) {
  const loaderFilePath    = `/${loaderFilename}.js`;
  const minLoaderFilePath = `/${loaderFilename}.min.js`;
  const widgetBundlePath  = path.join(__dirname, 'src/DeskPRO/Bundle/WidgetBundle');
  const utilCode          = fs.readFileSync(`${widgetBundlePath}/deskpro_loader_util.js`).toString();
  const buildDir          = path.join(__dirname, 'build');

  if (!fs.existsSync(buildDir)) {
    fs.mkdirSync(buildDir);
  }
  console.log(`Writing ${loaderFilePath}`);

  const loaderCode = fs.readFileSync(widgetBundlePath + loaderFilePath).toString()
    .replace('// #include deskpro_loader_util.js', utilCode);

  const transformedLoaderCode = babel.transform(
    loaderCode,
    { presets: ['es2015', 'react', 'stage-0'] }
  ).code;
  fs.writeFileSync(buildDir + loaderFilePath, transformedLoaderCode);

  try {
    const loaderCodemin = uglify.minify(transformedLoaderCode, { fromString: true }).code;
    fs.writeFileSync(path.join(__dirname, 'build') + minLoaderFilePath, loaderCodemin);
  } catch (e) {
    console.log('Trying to minify:\n');
    console.log(transformedLoaderCode);
    console.log('\n\n');
    console.error(e);
    return;
  }

  console.log(`... done writing ${loaderFilename}`);

  // Refresh precompiled-fontawesome
  console.log('Writing precompiled-fontawesome.css:');
  const faInPath  = `${__dirname}/src/DeskPRO/Bundle/PortalBundle/Resources/style/precompiled-fontawesome.scss`;
  const faOutPath = `${__dirname}/src/DeskPRO/Bundle/PortalBundle/Resources/style/precompiled-fontawesome.css`;
  const faResult  = sass.renderSync({
    file:         faInPath,
    outFile:      faOutPath,
    includePaths: [bowerDir, nodeModulesDir]
  });
  fs.writeFileSync(faOutPath, faResult.css);
  console.log(`... done writing ${faOutPath}`);
}

function refreshPortalDesignerVariables() {
  process.chdir('../web');
  spawn('npm', ['run-script', 'sassdoc']);
  process.chdir('../pub');
}

/**
 * @param {String}  mode          all, agent, portal
 * @param {Boolean} isProd        To add settings for prod such as uglify and source maps
 * @returns {Object}
 */
function getWebpackConfig(mode, isProd) {
  const config = {
    cache:   true,
    entry:   {},
    devtool: isProd ? 'source-map' : 'eval',

    output: {
      path:              path.join(__dirname, 'build/'),
      publicPath:        isProd ? '/pub/build/' : 'http://localhost:9666/pub/build/',
      filename:          '[name].js',
      sourceMapFilename: '[name].map'
    },

    resolve: {
      root: [
        path.join(__dirname, 'src'),
        path.join(__dirname, 'src/DeskPRO/Component'),
        path.join(__dirname, 'src/DeskPRO/Dev'),
        path.join(__dirname, 'built-tools'),
        path.join(__dirname, 'vendor'),
        //path.join(__dirname, 'node_modules')
      ],

      alias: {
        invariant:              'fbjs/lib/invariant',
        warning:                'fbjs/lib/warning',
        'jquery.ui':            'jquery-ui',
        'jquery.ui.widget':     'jquery.ui.widget/jquery.ui.widget',
        'jquery.serializejson': 'jquery-serializejson/jquery.serializejson',
        'mark.js':              'mark.js/dist/jquery.mark.min',
        react:                  path.join(__dirname, 'node_modules', 'react')
      }
    },

    resolveLoader: {
      modulesDirectories: ['web_loaders', 'web_modules', 'node_loaders', 'node_modules', 'build-tools', 'vendor']
    },

    module: {
      preLoaders: [
        {
          test:    /\/Reducers\/.*?\.js$/,
          loader:  'app-reducer-gen',
          include: [
            path.resolve(__dirname, 'src/DeskPRO/Bundle/AdminBundle/Modules'),
            path.resolve(__dirname, 'src/DeskPRO/Bundle/ReportBundle/Modules'),
            path.resolve(__dirname, 'src/DeskPRO/Bundle/AgentBundle/Modules'),
            path.resolve(__dirname, 'src/DeskPRO/Bundle/AppBundle/Modules'),
            path.resolve(__dirname, 'src/DeskPRO/Bundle/DemoBundle/Modules'),
            path.resolve(__dirname, 'src/DeskPRO/Bundle/WidgetBundle/Modules'),
            path.resolve(__dirname, 'src/DeskPRO/Bundle/Apps/Modules'),
          ]
        }
      ],

      loaders: [
        {
          test:    /\.js$/,
          loader:  'babel?cacheDirectory',
          include: [
            path.resolve(__dirname, 'src/DeskPRO')
          ]
        },
        {
          test:    /\.(png|gif|jpg|jpeg|woff|woff2|ttf|eot|svg|mp3|ogg|wav)(\?|$)/,
          loader:  'file-loader?context=src&name=[path][name].[ext]',
          include: [
            path.resolve(__dirname, 'src/DeskPRO'),
            path.resolve(__dirname, 'node_modules/bourbon'),
            path.resolve(__dirname, 'node_modules/bourbon-neat'),
            path.resolve(__dirname, 'node_modules/font-awesome'),
            path.resolve(__dirname, 'node_modules/intl-tel-input'),
            path.resolve(__dirname, 'node_modules/flag-icon-css'),
            path.resolve(__dirname, 'node_modules/cropper')
          ]
        },
        {
          test:    /\.scss$/,
          include: [
            path.resolve(__dirname, 'src/DeskPRO/Bundle/AdminBundle/Resources/style'),
            path.resolve(__dirname, 'src/DeskPRO/Bundle/ReportBundle/Resources/style'),
            path.resolve(__dirname, 'src/DeskPRO/Bundle/AgentBundle/Resources/style'),
            path.resolve(__dirname, 'src/DeskPRO/Bundle/PortalBundle/Resources/style'),
            path.resolve(__dirname, 'src/DeskPRO/Bundle/AppBundle/Resources/style'),
            path.resolve(__dirname, 'src/DeskPRO/Bundle/DemoBundle/Resources/style'),
            path.resolve(__dirname, 'src/DeskPRO/Bundle/WidgetBundle')
          ],

          loader: ExtractTextPlugin.extract('style-loader',
            `css-loader?sourceMap!sass-loader?sourceMap&outputStyle=expanded&includePaths[]=${bowerDir}&includePaths[]=${nodeModulesDir}`,
            { publicPath: './' }
          )
        },
        {
          test:   /\.json/,
          loader: 'json-loader'
        },
        {
          test:    /\.(ttf|eot|woff|woff2)$/,
          loader:  'file-loader',
          options: {
            name: 'fonts/[name].[ext]',
          },
        },
        { test: require.resolve('react'), loader: 'expose-loader?React' },
        { test: require.resolve('react-dom'), loader: 'expose-loader?ReactDOM' },
      ],
      noParse: [/(^(froala|jquery\.mark))\.min\.js/]
    },

    plugins: [
      new WebpackNotifierPlugin(),
      new ExtractTextPlugin('[name].css'),
      new webpack.DefinePlugin({
        'process.env.NODE_ENV': (isProd ? '"production"' : '"development"'),
        __DEV__:                !isProd
      }),
      new webpack.ProvidePlugin({
        $:      'jquery',
        jQuery: 'jquery',
        // React: 'react',
        // ReactDOM: 'react-dom'
      }),
      new CopyWebpackPlugin([
        { from: path.resolve(__dirname, 'src/DeskPRO/Bundle/PortalBundle'), to: 'DeskPRO/Bundle/PortalBundle' }
      ])
    ],

    node: { fs: 'empty' }
  };

  if (mode === 'all' || mode === 'portal') {
    config.entry.widget_loader               = [path.join(__dirname, 'build/widget_loader.js')];
    config.entry['widget_loader.min']        = [path.join(__dirname, 'build/widget_loader.min.js')];
    config.entry.embed_loader                = [path.join(__dirname, 'build/embed_loader.js')];
    config.entry['embed_loader.min']         = [path.join(__dirname, 'build/embed_loader.min.js')];
    config.entry.hit_recorder                = [path.join(__dirname, 'build/hit_recorder.js')];
    config.entry['hit_recorder.min']         = [path.join(__dirname, 'build/hit_recorder.min.js')];
    config.entry.iframeResizer_contentWindow = ['./node_modules/iframe-resizer/js/iframeResizer.contentWindow.js'];
    config.entry.DeskPRO_PortalBundle        = ['./src/DeskPRO/Bundle/PortalBundle/DeskPRO_PortalBundle'];

    config.entry.DeskPRO_PortalBundle_style     = ['./src/DeskPRO/Bundle/PortalBundle/Resources/style/portal-ltr-style.scss'];
    config.entry.DeskPRO_PortalBundle_rtl_style = ['./src/DeskPRO/Bundle/PortalBundle/Resources/style/portal-rtl-style.scss'];

    config.entry.DeskPRO_PortalBundle_vendors_style = ['./src/DeskPRO/Bundle/PortalBundle/Resources/style/vendors-style.scss'];

    config.entry.DeskPRO_PortalBundle_GuidePdf_style = ['./src/DeskPRO/Bundle/PortalBundle/Resources/style/guide_pdf.scss'];

    config.entry.DeskPRO_PortalBundle_iestyle  = ['./src/DeskPRO/Bundle/PortalBundle/Resources/style/ie-overrides.scss'];
    config.entry.DeskPRO_PortalBundle_ie8style = ['./src/DeskPRO/Bundle/PortalBundle/Resources/style/ie8-overrides.scss'];
    config.entry.DeskPRO_PortalBundle_ie9style = ['./src/DeskPRO/Bundle/PortalBundle/Resources/style/ie9-overrides.scss'];

    config.entry.api_message_style = ['./src/DeskPRO/Bundle/AppBundle/Resources/style/api/message.scss'];

    config.entry.DeskPRO_PortalBundle_print_style = ['./src/DeskPRO/Bundle/PortalBundle/Resources/style/print-style.scss'];
  }
  if (mode === 'all' || mode === 'widget') {
    config.entry.DeskPRO_WidgetBundle        = ['./src/DeskPRO/Bundle/WidgetBundle/DeskPRO_WidgetBundle'];
    config.entry.DeskPRO_WidgetBundle_style  = ['./src/DeskPRO/Bundle/WidgetBundle/Resources/style/widget-style.scss'];
    config.entry.DeskPRO_EmbedFormBundle     = ['./src/DeskPRO/Bundle/WidgetBundle/DeskPRO_EmbedFormBundle'];
    config.entry.DeskPRO_EmbedHelpdeskBundle = ['./src/DeskPRO/Bundle/WidgetBundle/DeskPRO_EmbedHelpdeskBundle'];
  }
  if (mode === 'all' || mode === 'agent') {
    config.entry.phonenumber_utils               = ['./node_modules/intl-tel-input/lib/libphonenumber/build/utils'];
    config.entry.DeskPRO_AgentBundle             = ['./src/DeskPRO/Bundle/AgentBundle/DeskPRO_AgentBundle'];
    config.entry.DeskPRO_AgentBundle_style       = ['./src/DeskPRO/Bundle/AgentBundle/Resources/style/agent-style.scss'];
    config.entry.DeskPRO_AgentLegacyBundle       = ['./src/DeskPRO/Bundle/AgentBundle/DeskPRO_AgentLegacyBundle'];
    config.entry.DeskPRO_AgentLegacyBundle_style = ['./src/DeskPRO/Bundle/AgentBundle/Resources/style/legacy-agent.scss'];
  }
  if (mode === 'all' || mode === 'admin') {
    config.entry.DeskPRO_AdminBundle             = ['./src/DeskPRO/Bundle/AdminBundle/DeskPRO_AdminBundle'];
    config.entry.DeskPRO_AdminBundle_style       = ['./src/DeskPRO/Bundle/AdminBundle/Resources/style/admin-style.scss'];
  }
  if (mode === 'all' || mode === 'report') {
    config.entry.DeskPRO_ReportBundle             = ['./src/DeskPRO/Bundle/ReportBundle/DeskPRO_ReportBundle'];
    config.entry.DeskPRO_ReportBundle_style       = ['./src/DeskPRO/Bundle/ReportBundle/Resources/style/report-style.scss'];
  }
  if (mode === 'all' || mode === 'demo') {
    config.entry.DeskPRO_DemoBundle             = ['./src/DeskPRO/Bundle/DemoBundle/DeskPRO_DemoBundle'];
    config.entry.DeskPRO_DemoBundle_style       = ['./src/DeskPRO/Bundle/DemoBundle/Resources/style/demo-style.scss'];
  }

  //---
  // Prod settings
  //---

  if (isProd) {
    config.plugins.push(new webpack.optimize.UglifyJsPlugin({
      exclude: [/(node_modules|bower_components)/]
    }));
  } else {
    //---
    // Dev server stuff
    //---
    config.debug           = true;
    config.output.pathinfo = true;
    config.plugins.push(new webpack.HotModuleReplacementPlugin());
    config.plugins.push(new webpack.NoErrorsPlugin());

    // .js loader
    config.module.loaders[0].loaders = ['react-hot-loader', 'babel-loader?stage=0'];

    if (config.entry.DeskPRO_AdminBundle) {
      config.entry.DeskPRO_AdminBundle.unshift('webpack-hot-middleware/client?path=http://localhost:9666/__webpack_hmr');
    }
    if (config.entry.DeskPRO_ReportBundle) {
      config.entry.DeskPRO_ReportBundle.unshift('webpack-hot-middleware/client?path=http://localhost:9666/__webpack_hmr');
    }
    if (config.entry.DeskPRO_AgentBundle) {
      config.entry.DeskPRO_AgentBundle.unshift('webpack-hot-middleware/client?path=http://localhost:9666/__webpack_hmr');
    }
    if (config.entry.DeskPRO_AgentLegacyBundle) {
      config.entry.DeskPRO_AgentLegacyBundle.unshift('webpack-hot-middleware/client?path=http://localhost:9666/__webpack_hmr');
    }
    if (config.entry.DeskPRO_DemoBundle) {
      config.entry.DeskPRO_DemoBundle.unshift('webpack-hot-middleware/client?path=http://localhost:9666/__webpack_hmr');
    }
    if (config.entry.DeskPRO_WidgetBundle) {
      config.entry.DeskPRO_WidgetBundle.unshift('webpack-hot-middleware/client?path=http://localhost:9666/__webpack_hmr');
    }
  }

  return config;
}
// ######################################################################################################################
// # Bundler
// ######################################################################################################################

/**
 * @param {Object} config
 * @param {Function} callback
 */
function runWebpackBundle(config, callback) {
  webpack(config, (err, stats) => {
    if (err) {
      throw new gutil.PluginError('bundle', err);
    }

    gutil.log('[bundle]', stats.toString({
      colors: true
    }));
    callback();
  });
}

/**
 * @param {Object} config
 * @return {express}
 */
function startWebpackServer(config) {
  const app      = express();
  const compiler = webpack(config);
  app.use(webpackDevMiddleware(compiler, {
    publicPath:         config.output.publicPath,
    hot:                true,
    historyApiFallback: true,
    stats:              {
      colors:       true,
      chunks:       true,
      source:       false,
      chunkOrigins: false,
      reasons:      false,
      cached:       false,
      hash:         false,
      assets:       false,
      version:      false
    }
  }));

  app.use(webpackHotMiddleware(compiler));
  app.use(cors());
  app.listen(9666, '0.0.0.0', (err) => {
    if (err) {
      throw new gutil.PluginError('webpack-dev-server', err);
    }

    gutil.log('[webpack-dev-server]', 'http://localhost:9666/');
    gutil.log('[webpack-dev-server]', 'In your config.paths.php, ensure these lines exist: ');
    gutil.log('[webpack-dev-server]', '\r\n$PATHS_CONFIG[\'asset_paths\'][\'app_assets\'] = [' +
      '\r\n    \'type\' => \'url\',' +
      '\r\n    \'value\' => \'http://localhost:9666/pub/build/\'' +
      '\r\n];'
    );
  });

  return app;
}

// ######################################################################################################################
// # Task Runners
// ######################################################################################################################

gulp.task('refresh-reducers', () => {
  reducerRefresh('App', path.join(__dirname, 'src/DeskPRO/Bundle/AppBundle'));
  reducerRefresh('Admin', path.join(__dirname, 'src/DeskPRO/Bundle/AdminBundle'));
  reducerRefresh('Report', path.join(__dirname, 'src/DeskPRO/Bundle/ReportBundle'));
  reducerRefresh('Agent', path.join(__dirname, 'src/DeskPRO/Bundle/AgentBundle'));
  reducerRefresh('Demo', path.join(__dirname, 'src/DeskPRO/Bundle/DemoBundle'));
  reducerRefresh('Widget', path.join(__dirname, 'src/DeskPRO/Bundle/WidgetBundle'));
});

gulp.task('bundle', (callback) => {
  reducerRefresh('App', path.join(__dirname, 'src/DeskPRO/Bundle/AppBundle'));
  reducerRefresh('Admin', path.join(__dirname, 'src/DeskPRO/Bundle/AdminBundle'));
  reducerRefresh('Report', path.join(__dirname, 'src/DeskPRO/Bundle/ReportBundle'));
  reducerRefresh('Agent', path.join(__dirname, 'src/DeskPRO/Bundle/AgentBundle'));
  reducerRefresh('Demo', path.join(__dirname, 'src/DeskPRO/Bundle/DemoBundle'));
  reducerRefresh('Widget', path.join(__dirname, 'src/DeskPRO/Bundle/WidgetBundle'));
  refreshWidgetLoader('widget_loader');
  refreshWidgetLoader('hit_recorder');
  refreshWidgetLoader('embed_loader');
  refreshPortalDesignerVariables();
  runWebpackBundle(getWebpackConfig('all', true), callback);
});

gulp.task('bundle:dev', (callback) => {
  reducerRefresh('App', path.join(__dirname, 'src/DeskPRO/Bundle/AppBundle'));
  reducerRefresh('Admin', path.join(__dirname, 'src/DeskPRO/Bundle/AdminBundle'));
  reducerRefresh('Report', path.join(__dirname, 'src/DeskPRO/Bundle/ReportBundle'));
  reducerRefresh('Agent', path.join(__dirname, 'src/DeskPRO/Bundle/AgentBundle'));
  reducerRefresh('Demo', path.join(__dirname, 'src/DeskPRO/Bundle/DemoBundle'));
  reducerRefresh('Widget', path.join(__dirname, 'src/DeskPRO/Bundle/WidgetBundle'));
  refreshWidgetLoader('widget_loader');
  refreshWidgetLoader('hit_recorder');
  refreshWidgetLoader('embed_loader');
  refreshPortalDesignerVariables();
  runWebpackBundle(getWebpackConfig('all', false), callback);
});

gulp.task('bundle:agent', (callback) => {
  reducerRefresh('App', path.join(__dirname, 'src/DeskPRO/Bundle/AppBundle'));
  reducerRefresh('Agent', path.join(__dirname, 'src/DeskPRO/Bundle/AgentBundle'));
  refreshPortalDesignerVariables();
  runWebpackBundle(getWebpackConfig('agent', true), callback);
});

gulp.task('bundle:portal', (callback) => {
  runWebpackBundle(getWebpackConfig('portal', true), callback);
});

gulp.task('bundle:widget', (callback) => {
  reducerRefresh('App', path.join(__dirname, 'src/DeskPRO/Bundle/AppBundle'));
  reducerRefresh('Widget', path.join(__dirname, 'src/DeskPRO/Bundle/WidgetBundle'));
  refreshWidgetLoader('widget_loader');
  refreshWidgetLoader('hit_recorder');
  refreshWidgetLoader('embed_loader');
  runWebpackBundle(getWebpackConfig('widget', true), callback);
});

gulp.task('bundle:demo', (callback) => {
  reducerRefresh('App', path.join(__dirname, 'src/DeskPRO/Bundle/AppBundle'));
  reducerRefresh('Demo', path.join(__dirname, 'src/DeskPRO/Bundle/DemoBundle'));
  runWebpackBundle(getWebpackConfig('demo', true), callback);
});

gulp.task('bundle:dev-server', () => {
  refreshPortalDesignerVariables();
  refreshWidgetLoader('widget_loader');
  refreshWidgetLoader('hit_recorder');
  refreshWidgetLoader('embed_loader');
  reducerRefresh('App', path.join(__dirname, 'src/DeskPRO/Bundle/AppBundle'));
  reducerRefresh('Admin', path.join(__dirname, 'src/DeskPRO/Bundle/AdminBundle'));
  reducerRefresh('Report', path.join(__dirname, 'src/DeskPRO/Bundle/ReportBundle'));
  reducerRefresh('Agent', path.join(__dirname, 'src/DeskPRO/Bundle/AgentBundle'));
  reducerRefresh('Demo', path.join(__dirname, 'src/DeskPRO/Bundle/DemoBundle'));
  reducerRefresh('Widget', path.join(__dirname, 'src/DeskPRO/Bundle/WidgetBundle'));
  startWebpackServer(getWebpackConfig('all', false));
});

gulp.task('bundle:dev-server:agent', () => {
  reducerRefresh('App', path.join(__dirname, 'src/DeskPRO/Bundle/AppBundle'));
  reducerRefresh('Agent', path.join(__dirname, 'src/DeskPRO/Bundle/AgentBundle'));
  refreshPortalDesignerVariables();
  startWebpackServer(getWebpackConfig('agent', false));
});

gulp.task('bundle:dev-server:portal', () => {
  startWebpackServer(getWebpackConfig('portal', false));
});

gulp.task('bundle:dev-server:widget', () => {
  reducerRefresh('App', path.join(__dirname, 'src/DeskPRO/Bundle/AppBundle'));
  reducerRefresh('Widget', path.join(__dirname, 'src/DeskPRO/Bundle/WidgetBundle'));
  startWebpackServer(getWebpackConfig('widget', false));
});

gulp.task('bundle:dev-server:demo', () => {
  reducerRefresh('App', path.join(__dirname, 'src/DeskPRO/Bundle/AppBundle'));
  reducerRefresh('Demo', path.join(__dirname, 'src/DeskPRO/Bundle/DemoBundle'));
  startWebpackServer(getWebpackConfig('demo', false));
});

gulp.task('compile-zurb', () => {
  // Refresh zurb-foundation email
  console.log('Writing zurb-foundation.css:');
  const faInPath  = `${__dirname}/src/DeskPRO/Bundle/AppBundle/Resources/style/emails/zurb-foundation.scss`;
  const faOutPath = `${__dirname}/src/DeskPRO/Bundle/AppBundle/Resources/style/emails/zurb-foundation.css`;
  const faResult  = sass.renderSync({
    file:         faInPath,
    outFile:      faOutPath,
    includePaths: [bowerDir, nodeModulesDir]
  });
  fs.writeFileSync(faOutPath, faResult.css);
  console.log(`... done writing ${faOutPath}`);
});
