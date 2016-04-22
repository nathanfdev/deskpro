var gulp = require('gulp');
var gutil = require('gulp-util');
var webpack = require('webpack');
var express = require('express');
var cors = require('cors');
var del = require('del');
var runSeq = require('run-sequence');
var path = require('path');
var ExtractTextPlugin = require('extract-text-webpack-plugin');
var WebpackNotifierPlugin = require('webpack-notifier');
var CopyWebpackPlugin = require('copy-webpack-plugin');
var glob = require('glob');
var babel = require('babel-core');
var uglify = require('uglify-js');
var fs = require('fs');
var mkdirp = require('mkdirp');
var watch = require('gulp-watch');
var notifier = require('node-notifier');
var reducerRefresh = require('./build-tools/app-reducer-gen/loader').refreshBundle;

// ######################################################################################################################
// # Task Runners
// ######################################################################################################################

gulp.task('clean', cb => {
  del(['./build']).then(()=> {
    cb();
  });
});

gulp.task('default', ['clean'], cb => {
  runSeq(['bundle'], cb);
});

gulp.task('prod', ['clean', 'priv:start-prod'], cb => {
  runSeq(['bundle'], cb);
});

gulp.task('dev', cb => {
  // prefer to use one at a time, build speed is faster
  // and you can still just open up two terminal winodws if you need both
  console.log('Use:');
  console.log('\tdev:agent    -  For the agent interface');
  console.log('\tdev:portal   -  For the portal');
  console.log('\tdev:widget   -  For the widget');
  console.log('\tdev:all      -  For both');
  cb();
});

gulp.task('dev:agent', cb => {
  runSeq(['bundle:dev-server:agent'], cb);
});

gulp.task('dev:portal', cb => {
  runSeq(['bundle:dev-server:portal'], cb);
});

gulp.task('dev:widget', cb => {
  runSeq(['bundle:dev-server:widget'], cb);
});

gulp.task('dev:all', cb => {
  runSeq(['bundle:dev-server'], cb);
});

// ######################################################################################################################
// # Helpers
// ######################################################################################################################

gulp.task('priv:start-prod', () => {

});

// ######################################################################################################################
// # Bundler
// ######################################################################################################################

function refreshWidgetLoader(loaderFilename) {
  const loaderFilePath = '/' + loaderFilename + '.js';
  const minLoaderFilePath = '/' + loaderFilename + '.min.js';

  console.log('Writing widget_loader');
  var loaderCode = babel.transformFileSync(
    path.join(__dirname, 'src/DeskPRO/Bundle/WidgetBundle') + loaderFilePath,
    { 'presets': ['es2015', 'react', 'stage-0'] }
  ).code;

  try {
    var loaderCodemin = uglify.minify(loaderCode, {
      'fromString': true
    }).code;
  } catch (e) {
    console.log('Trying to minify:\n');
    console.log(loaderCode);
    console.log('\n\n');
    console.error(e);
    return;
  }

  var buildDir = path.join(__dirname, 'build');

  if (!fs.existsSync(buildDir)) {
    fs.mkdirSync(buildDir);
  }

  fs.writeFileSync(buildDir + loaderFilePath, loaderCode);
  fs.writeFileSync(path.join(__dirname, 'build') + minLoaderFilePath, loaderCodemin);
  console.log('.. done writing ' + loaderFilename);
}

function refreshPortalDesignerVariables() {
  var spawn = require('child_process').spawn;
  process.chdir('../web');
  var child = spawn('gulp', ['sassdoc']);

  // Print output from Gulpfile
  child.stdout.on('data', data => {
    if (data) {
      console.log(data.toString());
    }
  });

  process.chdir('../pub');
}

gulp.task('refresh-reducers', () => {
  reducerRefresh('App', path.join(__dirname, 'src/DeskPRO/Bundle/AppBundle'));
  reducerRefresh('Agent', path.join(__dirname, 'src/DeskPRO/Bundle/AgentBundle'));
  reducerRefresh('Widget', path.join(__dirname, 'src/DeskPRO/Bundle/WidgetBundle'));
});

gulp.task('bundle', callback => {
  reducerRefresh('App', path.join(__dirname, 'src/DeskPRO/Bundle/AppBundle'));
  reducerRefresh('Agent', path.join(__dirname, 'src/DeskPRO/Bundle/AgentBundle'));
  reducerRefresh('Widget', path.join(__dirname, 'src/DeskPRO/Bundle/WidgetBundle'));
  refreshWidgetLoader('widget_loader');
  refreshWidgetLoader('hit_recorder');
  refreshWidgetLoader('embed_loader');
  refreshPortalDesignerVariables();
  runWebpackBundle(getWebpackConfig('all', true), callback);
});

gulp.task('bundle:agent', callback => {
  reducerRefresh('App', path.join(__dirname, 'src/DeskPRO/Bundle/AppBundle'));
  reducerRefresh('Agent', path.join(__dirname, 'src/DeskPRO/Bundle/AgentBundle'));
  refreshPortalDesignerVariables();
  runWebpackBundle(getWebpackConfig('agent', true), callback);
});

gulp.task('bundle:portal', callback => {
  runWebpackBundle(getWebpackConfig('portal', true), callback);
});

gulp.task('bundle:widget', callback => {
  reducerRefresh('App', path.join(__dirname, 'src/DeskPRO/Bundle/AppBundle'));
  reducerRefresh('Widget', path.join(__dirname, 'src/DeskPRO/Bundle/WidgetBundle'));
  refreshWidgetLoader('widget_loader');
  refreshWidgetLoader('hit_recorder');
  refreshWidgetLoader('embed_loader');
  runWebpackBundle(getWebpackConfig('widget', true), callback);
});

gulp.task('bundle:dev-server', () => {
  refreshPortalDesignerVariables();
  refreshWidgetLoader('widget_loader');
  refreshWidgetLoader('hit_recorder');
  refreshWidgetLoader('embed_loader');
  reducerRefresh('App', path.join(__dirname, 'src/DeskPRO/Bundle/AppBundle'));
  reducerRefresh('Agent', path.join(__dirname, 'src/DeskPRO/Bundle/AgentBundle'));
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

var slate = require('gulp-slate');

gulp.task('slate', function() {

  return new Promise(function(resolve, reject) {
    var options = {
      scss: '../../../../app/BUILD/src/DeskPRO/Bundle/ApiBundle/Resources/apidocs/slate.scss',
      style: 'androidstudio',
      logo: 'static/Common/deskpro-logo_2x.png',
      template: '../../../../app/BUILD/src/DeskPRO/Bundle/ApiBundle/Resources/apidocs/layouts/layout.html'
    };
    gulp.src(
      [
        '../../../../app/BUILD/src/DeskPRO/Bundle/ApiBundle/Resources/apidocs/source/index.html.twig.md'
      ]
      )
      .pipe(slate(options))
      .on('erorr', reject)
      .pipe(gulp.dest('build/apidocs'))
      .on('end', function() {
        gulp.src(['build/apidocs/index.html.twig'])
          .pipe(gulp.dest(
            '../../../../app/BUILD/src/DeskPRO/Bundle/ApiBundle/Resources/views/apidocs/'
          ))
          .on('end', resolve);
      });
  });
});

// ######################################################################################################################
// # Helpers
// ######################################################################################################################

/**
 * @param {String}  mode          all, agent, portal
 * @param {Boolean} isProd        To add settings for prod such as uglify and source maps
 * @returns {Object}
 */
function getWebpackConfig(mode, isProd) {
  var isDevServer = !isProd;

  var config = {
    cache: true,
    entry: {},
    output: {
      path: path.join(__dirname, 'build/'),
      publicPath: '/pub/build/',
      filename: '[name].js',
      sourceMapFilename: '[name].map'
    },
    resolve: {
      root: [
        path.join(__dirname, 'src'),
        path.join(__dirname, 'src/DeskPRO/Component'),
        path.join(__dirname, 'src/DeskPRO/Dev'),
        path.join(__dirname, 'built-tools')
      ],
      alias: {
        'invariant': 'fbjs/lib/invariant',
        'warning': 'fbjs/lib/warning',
        'jquery.ui': 'jquery-ui',
        'jquery.ui.widget': 'jquery.ui.widget/jquery.ui.widget',
        'jquery.serializejson': 'jquery-serializejson/jquery.serializejson'
      }
    },
    resolveLoader: {
      modulesDirectories: ['web_loaders', 'web_modules', 'node_loaders', 'node_modules', 'build-tools']
    },
    devtool: 'eval',
    module: {
      preLoaders: [
        {
          test: /\/Reducers\/.*?\.js$/,
          include: [
            path.resolve(__dirname, 'src/DeskPRO/Bundle/AppBundle/Modules'),
            path.resolve(__dirname, 'src/DeskPRO/Bundle/AgentBundle/Modules'),
            path.resolve(__dirname, 'src/DeskPRO/Bundle/WidgetBundle/Modules')
          ],
          loader: 'app-reducer-gen'
        }
      ],
      loaders: [
        {
          test: /\.js$/,
          include: [
            path.resolve(__dirname, 'src/DeskPRO'),
            path.resolve(__dirname, 'node_modules/formsy-react')
          ],
          exclude: [
            path.resolve(__dirname, 'src/DeskPRO/Bundle/AgentBundle/Legacy')
          ],
          loader: 'babel'
        },
        {
          test: /\.(png|gif|jpg|jpeg|woff|woff2|ttf|eot|svg|mp3|ogg|wav)(\?|$)/,
          loader: 'file-loader?context=src&name=[path][name].[ext]',
          include: [
            path.resolve(__dirname, 'src/DeskPRO'),
            path.resolve(__dirname, 'node_modules/bourbon'),
            path.resolve(__dirname, 'node_modules/bourbon-neat'),
            path.resolve(__dirname, 'node_modules/font-awesome'),
            path.resolve(__dirname, 'node_modules/intl-tel-input'),
            path.resolve(__dirname, 'node_modules/cropper')
          ]
        },
        {
          test: /\.scss$/,
          include: [
            path.resolve(__dirname, 'src/DeskPRO/Bundle/AgentBundle/Resources/style'),
            path.resolve(__dirname, 'src/DeskPRO/Bundle/PortalBundle/Resources/style'),
            path.resolve(__dirname, 'src/DeskPRO/Bundle/AppBundle/Resources/style'),
            path.resolve(__dirname, 'src/DeskPRO/Bundle/WidgetBundle')
          ],
          loader: ExtractTextPlugin.extract('style-loader',
            'css-loader?sourceMap!sass-loader?sourceMap&outputStyle=expanded&' +
            'includePaths[]=' + (path.resolve(__dirname, './bower_components')) + '&' +
            'includePaths[]=' + (path.resolve(__dirname, './node_modules')),
            { 'publicPath': './' }
          )
        },
        {
          test: /\.json/,
          loader: 'json-loader'
        }
      ],
      noParse: []
    },
    plugins: [
      new WebpackNotifierPlugin(),
      new ExtractTextPlugin('[name].css'),
      new webpack.DefinePlugin({
        'process.env.NODE_ENV': (isProd ? '\"production\"' : '\"development\"'),
        '__DEV__': !isProd
      }),
      new webpack.ProvidePlugin({
        $: 'jquery',
        jQuery: 'jquery'
      }),
      new CopyWebpackPlugin([
        { from: path.resolve(__dirname, 'src/DeskPRO/Bundle/PortalBundle'), to: 'DeskPRO/Bundle/PortalBundle' }
      ])
    ]
  };

  if (mode === 'all' || mode === 'portal') {
    config.entry['widget_loader'] = ['./src/DeskPRO/Bundle/WidgetBundle/widget_loader.js'];
    config.entry['embed_loader'] = ['./src/DeskPRO/Bundle/WidgetBundle/embed_loader.js'];
    config.entry['iframeResizer_contentWindow'] = ['./node_modules/iframe-resizer/js/iframeResizer.contentWindow.js'];
    config.entry['DeskPRO_PortalBundle'] = ['./src/DeskPRO/Bundle/PortalBundle/DeskPRO_PortalBundle'];

    config.entry['DeskPRO_PortalBundle_style'] = ['./src/DeskPRO/Bundle/PortalBundle/Resources/style/portal-ltr-style.scss'];
    config.entry['DeskPRO_PortalBundle_rtl_style'] = ['./src/DeskPRO/Bundle/PortalBundle/Resources/style/portal-rtl-style.scss'];
//    config.entry['DeskPRO_PortalBundle_rtl_style'] = ['./src/DeskPRO/Bundle/PortalBundle/Resources/style/portal-ltr-style.scss'];

    config.entry['DeskPRO_PortalBundle_iestyle'] = ['./src/DeskPRO/Bundle/PortalBundle/Resources/style/ie-overrides.scss'];
    config.entry['DeskPRO_PortalBundle_ie8style'] = ['./src/DeskPRO/Bundle/PortalBundle/Resources/style/ie8-overrides.scss'];
    config.entry['DeskPRO_PortalBundle_ie9style'] = ['./src/DeskPRO/Bundle/PortalBundle/Resources/style/ie9-overrides.scss'];
  }
  if (mode === 'all' || mode === 'widget') {
    config.entry['DeskPRO_WidgetBundle'] = ['./src/DeskPRO/Bundle/WidgetBundle/DeskPRO_WidgetBundle'];
    config.entry['DeskPRO_WidgetBundle_style'] = ['./src/DeskPRO/Bundle/WidgetBundle/Resources/style/widget-style.scss'];
  }
  if (mode === 'all' || mode === 'agent') {
    config.entry['phonenumber_utils'] = ['./node_modules/intl-tel-input/lib/libphonenumber/build/utils'];
    config.entry['DeskPRO_AgentBundle'] = ['./src/DeskPRO/Bundle/AgentBundle/DeskPRO_AgentBundle'];
    config.entry['DeskPRO_AgentBundle_style'] = ['./src/DeskPRO/Bundle/AgentBundle/Resources/style/agent-style.scss'];
  }

  //---
  // Prod settings
  //---

  if (isProd) {
    config.devtool = 'source-map';
    config.plugins.push(new webpack.optimize.UglifyJsPlugin({
      exclude: [/(node_modules|bower_components)/]
    }));
  }

  //---
  // Dev server stuff
  //---

  if (isDevServer) {
    config.debug = true;

    config.output.publicPath = 'http://localhost:9666/pub/build/';

    config.plugins.push(new webpack.HotModuleReplacementPlugin());
    config.plugins.push(new webpack.NoErrorsPlugin());

    // .js loader
    config.module.loaders[0].loaders = ['react-hot-loader', 'babel-loader?stage=0'];

    if (config.entry['DeskPRO_AgentBundle']) {
      config.entry['DeskPRO_AgentBundle'].unshift('webpack-hot-middleware/client?path=http://localhost:9666/__webpack_hmr');
    }
    if (config.entry['DeskPRO_WidgetBundle']) {
      config.entry['DeskPRO_WidgetBundle'].unshift('webpack-hot-middleware/client?path=http://localhost:9666/__webpack_hmr');
    }
  }

  return config;
}

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
  var app = express();
  var compiler = webpack(config);

  app.use(require('webpack-dev-middleware')(compiler, {
    publicPath: config.output.publicPath,
    hot: true,
    historyApiFallback: true,
    stats: {
      colors: true,
      chunks: true,
      source: false,
      chunkOrigins: false,
      reasons: false,
      cached: false,
      hash: false,
      assets: false,
      version: false
    }
  }));

  app.use(require('webpack-hot-middleware')(compiler));
  app.use(cors());
  app.listen(9666, '0.0.0.0', (err) => {
    if (err) {
      throw new gutil.PluginError('webpack-dev-server', err);
    }

    gutil.log('[webpack-dev-server]', 'http://localhost:9666/');
    gutil.log('[webpack-dev-server]', 'In your config.paths.php, ensure these lines exists: ');
    gutil.log('[webpack-dev-server]', '\r\n$PATHS_CONFIG[\'asset_paths\'][\'app_assets\'] = [' +
      '\r\n    \'type\' => \'url\',' +
      '\r\n\    \'value\' => \'http://localhost:9666/pub/build/\'' +
      '\r\n];'
    );
  });

  return app;
}
