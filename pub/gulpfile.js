var gulp                  = require('gulp'),
    gutil                 = require('gulp-util'),
    webpack               = require("webpack"),
    express               = require('express'),
    cors                  = require('cors'),
    del                   = require('del'),
    runSeq                = require('run-sequence'),
    path                  = require("path"),
    ExtractTextPlugin     = require("extract-text-webpack-plugin"),
    WebpackNotifierPlugin = require('webpack-notifier'),
    glob                  = require("glob"),
    babel                 = require("babel"),
    uglify                = require("uglify-js"),
    fs                    = require("fs"),
    mkdirp                = require('mkdirp'),
    watch                 = require('gulp-watch'),
    notifier              = require('node-notifier'),
    reducerRefresh        = require("./build-tools/app-reducer-gen/loader").refreshBundle;

//######################################################################################################################
//# Util
//######################################################################################################################

var deskpro = {
  isProd:  false
};

//######################################################################################################################
//# Task Runners
//######################################################################################################################

gulp.task('clean', function (cb) {
  del(['./build']).then(function() { cb(); });
});

gulp.task('default', ['clean'], function (cb) {
  runSeq(['bundle'], cb);
});

gulp.task('prod', ['clean', 'priv:start-prod'], function (cb) {
  runSeq(['bundle'], cb);
});

gulp.task('dev', function (cb) {
  // prefer to use one at a time, build speed is faster
  // and you can still just open up two terminal winodws if you need both
  console.log("Use:");
  console.log("\tdev:agent    -  For the agent interface");
  console.log("\tdev:portal   -  For the portal");
  console.log("\tdev:widget   -  For the widget");
  console.log("\tdev:all      -  For both");
  cb();
});

gulp.task('dev:agent', function (cb) {
  runSeq(['bundle:dev-server:agent'], cb);
});

gulp.task('dev:portal', function (cb) {
  runSeq(['bundle:dev-server:portal'], cb);
});

gulp.task('dev:widget', function (cb) {
  runSeq(['bundle:dev-server:widget'], cb);
});

gulp.task('dev:all', function (cb) {
  runSeq(['bundle:dev-server'], cb);
});

//######################################################################################################################
//# Helpers
//######################################################################################################################

gulp.task('priv:start-prod', function () {
  deskpro.isProd = true;
});

//######################################################################################################################
//# Bundler
//######################################################################################################################

function refreshWidgetLoader() {
  console.log("Writing widget_loader");
  var loaderCode = babel.transformFileSync(path.join(__dirname, "src/DeskPRO/Bundle/WidgetBundle") + "/widget_loader.js", {"stage": "0"}).code;
  var loaderCodemin = uglify.minify(loaderCode, {
    "fromString": true
  }).code;

  var buildDir = path.join(__dirname, "build");

  if (!fs.existsSync(buildDir)){
    fs.mkdirSync(buildDir);
  }

  fs.writeFileSync(buildDir + "/widget_loader.js", loaderCode);
  fs.writeFileSync(path.join(__dirname, "build") + "/widget_loader.min.js", loaderCodemin);
  console.log(".. done writing widget_loader");
}

function refreshHitRecorder() {
  console.log("Writing hit_recorder");
  var loaderCode = babel.transformFileSync(path.join(__dirname, "src/DeskPRO/Bundle/WidgetBundle") + "/hit_recorder.js", {"stage": "0"}).code;
  var loaderCodemin = uglify.minify(loaderCode, {
    "fromString": true
  }).code;

  var buildDir = path.join(__dirname, "build");

  if (!fs.existsSync(buildDir)){
    fs.mkdirSync(buildDir);
  }

  fs.writeFileSync(buildDir + "/hit_recorder.js", loaderCode);
  fs.writeFileSync(path.join(__dirname, "build") + "/hit_recorder.min.js", loaderCodemin);
  console.log(".. done writing hit_recorder");
}

function refreshLegacy() {
  var legacyPath = path.join(__dirname, "src/DeskPRO/Bundle/AgentBundle/Legacy");
  var targetPath = path.join(__dirname, "build/DeskPRO/Bundle/AgentBundle/Legacy");

  console.log("Refreshing legacy... ");
  glob.sync('**/*.js', { cwd: legacyPath, root: legacyPath }).forEach(function(f) {
    var filePath    = legacyPath + '/' + f;
    var targetFile  = targetPath + '/' + f;
    var targetDir   = path.dirname(targetFile);

    if (!fs.existsSync(targetDir)){
      mkdirp.sync(targetDir);
    }

    try {
      var code = babel.transformFileSync(filePath, {"stage": "0"}).code;
      fs.writeFileSync(targetFile, code);
    } catch (e) {
      notifier.notify({
        title: "Error refreshing legacy",
        message: e,
        sound: true
      });
    }

    console.log("- " + filePath);
  });
}

function refreshPortalDesignerVariables() {
  var spawn = require('child_process').spawn;
  process.chdir('../web');
  var child = spawn('gulp', ['sassdoc']);

  // Print output from Gulpfile
  child.stdout.on('data', function(data) {
    if (data) {
      console.log(data.toString());
    }
  });

  process.chdir('../pub');
}

gulp.task('bundle', function (callback) {
  reducerRefresh("Agent", path.join(__dirname, "src/DeskPRO/Bundle/AgentBundle"));
  reducerRefresh("Widget", path.join(__dirname, "src/DeskPRO/Bundle/WidgetBundle"));
  refreshWidgetLoader();
  refreshHitRecorder();
  refreshLegacy();
  refreshPortalDesignerVariables();
  runWebpackBundle(getWebpackConfig('all', deskpro.isProd), callback);
});

gulp.task('bundle:agent', function (callback) {
  reducerRefresh("Agent", path.join(__dirname, "src/DeskPRO/Bundle/AgentBundle"));
  refreshLegacy();
  refreshPortalDesignerVariables();
  runWebpackBundle(getWebpackConfig('agent', deskpro.isProd), callback);
});

gulp.task('bundle:portal', function (callback) {
  runWebpackBundle(getWebpackConfig('portal', deskpro.isProd), callback);
});

gulp.task('bundle:widget', function(callback) {
  reducerRefresh("Widget", path.join(__dirname, "src/DeskPRO/Bundle/WidgetBundle"));
  refreshWidgetLoader();
  refreshHitRecorder();
  runWebpackBundle(getWebpackConfig('widget', deskpro.isProd), callback);
});

gulp.task('bundle:dev-server', function(callback) {
  refreshLegacy();
  watch(path.join(__dirname, "src/DeskPRO/Bundle/AgentBundle/Legacy/**/*.js"), function() {
    refreshLegacy();
  });
  refreshPortalDesignerVariables();
  refreshWidgetLoader();
  refreshHitRecorder();
  reducerRefresh("Agent", path.join(__dirname, "src/DeskPRO/Bundle/AgentBundle"));
  reducerRefresh("Widget", path.join(__dirname, "src/DeskPRO/Bundle/WidgetBundle"));
  startWebpackServer(getWebpackConfig('all', true, false));
});

gulp.task('bundle:dev-server:agent', function(callback) {
  refreshLegacy();
  reducerRefresh("Agent", path.join(__dirname, "src/DeskPRO/Bundle/AgentBundle"));
  watch(path.join(__dirname, "src/DeskPRO/Bundle/AgentBundle/Legacy/**/*.js"), function() {
    refreshLegacy();
  });
  refreshPortalDesignerVariables();
  startWebpackServer(getWebpackConfig('agent', true, false));
});

gulp.task('bundle:dev-server:portal', function(callback) {
  startWebpackServer(getWebpackConfig('portal', true, false));
});

gulp.task('bundle:dev-server:widget', function(callback) {
  reducerRefresh("Widget", path.join(__dirname, "src/DeskPRO/Bundle/WidgetBundle"));
  startWebpackServer(getWebpackConfig('widget', true, false));
});

//######################################################################################################################
//# Helpers
//######################################################################################################################

/**
 * @param {String}  mode          all, agent, portal
 * @param {Boolean} isDevServer   To add settings needed for the dev server and hot-reloading
 * @param {Boolean} isProd        To add settings for prod such as uglify and source maps
 * @returns {Object}
 */
function getWebpackConfig(mode, isDevServer, isProd) {
  var node_modules_dir = path.join(__dirname, 'node_modules');

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
        'jquery.ui.widget': 'jquery.ui.widget/jquery.ui.widget'
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
          loader: 'babel',
          query: {
            stage: 0,
            plugins: ['react-transform'],
            extra: {
              'react-transform': {
                'transforms': [
                  {
                    'transform': 'react-transform-hmr',
                    'imports': ['react'],
                    'locals': ['module']
                  },
                  {
                    'transform': 'react-transform-catch-errors',
                    'imports': ['react', 'redbox-react', './redboxOptions']
                  }
                ]
              }
            }
          }
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
            path.resolve(__dirname, 'src/DeskPRO/Bundle/AppBundle/Resources/style')
          ],
          exclude: [
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
          test: /\.scss$/,
          include: [
            path.resolve(__dirname, 'src/DeskPRO/Bundle/WidgetBundle')
          ],
          loader: 'style!css!sass?outputStyle=expanded&'
        },
        {
          test: /\.json/,
          loader: 'json-loader',
          include: [
            path.resolve(__dirname, 'node_modules/mime-db')
          ]
        }
      ],
      noParse: []
    },
    plugins: [
      new WebpackNotifierPlugin(),
      new ExtractTextPlugin("[name].css"),
      new webpack.DefinePlugin({
        'process.env.NODE_ENV': (isProd ? "\"production\"" : "\"development\"")
      }),
      new webpack.ProvidePlugin({
        $: "jquery",
        jQuery: "jquery"
      })
    ]
  };

  if (mode === 'all' || mode === 'portal') {
    config.entry['widget_loader']              = ['./src/DeskPRO/Bundle/WidgetBundle/widget_loader.js'];
    config.entry['DeskPRO_PortalBundle']       = ['./src/DeskPRO/Bundle/PortalBundle/DeskPRO_PortalBundle'];
    config.entry['DeskPRO_PortalBundle_style'] = ['./src/DeskPRO/Bundle/PortalBundle/Resources/style/portal-style.scss'];

    config.entry['DeskPRO_PortalBundle_iestyle'] = ['./src/DeskPRO/Bundle/PortalBundle/Resources/style/ie-overrides.scss'];
    config.entry['DeskPRO_PortalBundle_ie8style'] = ['./src/DeskPRO/Bundle/PortalBundle/Resources/style/ie8-overrides.scss'];
    config.entry['DeskPRO_PortalBundle_ie9style'] = ['./src/DeskPRO/Bundle/PortalBundle/Resources/style/ie9-overrides.scss'];
  }
  if (mode === 'all' || mode === 'widget') {
    config.entry['DeskPRO_WidgetBundle']       = ['./src/DeskPRO/Bundle/WidgetBundle/DeskPRO_WidgetBundle'];
    config.entry['DeskPRO_WidgetBundle_style'] = ['./src/DeskPRO/Bundle/WidgetBundle/Resources/style/widget-style.scss'];
  }
  if (mode === 'all' || mode === 'agent') {
    config.entry['phonenumber_utils']         = ['./node_modules/intl-tel-input/lib/libphonenumber/build/utils'];
    config.entry['DeskPRO_AgentBundle']       = ['./src/DeskPRO/Bundle/AgentBundle/DeskPRO_AgentBundle'];
    config.entry['DeskPRO_AgentBundle_style'] = ['./src/DeskPRO/Bundle/AgentBundle/Resources/style/agent-style.scss'];
  }

  //---
  // Prod settings
  //---

  if (isProd) {
    config.plugins.push(new webpack.optimize.UglifyJsPlugin({
      exclude: [/(node_modules|bower_components)/]
    }))
  }

  //---
  // Dev server stuff
  //---

  if (isDevServer) {
    config.debug = true;

    config.output.publicPath = "http://localhost:9666/pub/build/";

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
function runWebpackBundle(config, callback)
{
  webpack(config, function(err, stats) {
    if(err) throw new gutil.PluginError("bundle", err);
    gutil.log("[bundle]", stats.toString({
      colors: true
    }));
    callback();
  });
}

/**
 * @param {Object} config
 * @return {express}
 */
function startWebpackServer(config)
{
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

  app.listen(9666, '0.0.0.0', function (err) {
    if(err) throw new gutil.PluginError("webpack-dev-server", err);

    gutil.log("[webpack-dev-server]", "http://localhost:9666/");
    gutil.log("[webpack-dev-server]", "In your config.php, add this line: ");
    gutil.log("[webpack-dev-server]", "$DP_CONFIG['pub_asset_urls'] = array('pub/build' => 'http://localhost:9666/pub/build/');");
  });

  return app;
}
