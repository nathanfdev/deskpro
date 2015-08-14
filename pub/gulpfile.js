var gulp                  = require('gulp'),
    gutil                 = require('gulp-util'),
    webpack               = require("webpack"),
    WebpackDevServer      = require("webpack-dev-server"),
    del                   = require('del'),
    runSeq                = require('run-sequence'),
    path                  = require("path"),
    ExtractTextPlugin     = require("extract-text-webpack-plugin");

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
  del(['./build'], cb);
});

gulp.task('default', ['clean'], function (cb) {
  runSeq(['bundle'], cb);
});

gulp.task('prod', ['clean', 'priv:start-prod'], function (cb) {
  runSeq(['bundle'], cb);
});

gulp.task('dev', function (cb) {
  runSeq(['bundle:dev-server'], cb);
});

gulp.task('dev:agent', function (cb) {
  runSeq(['bundle:dev-server:agent'], cb);
});

gulp.task('dev:portal', function (cb) {
  runSeq(['bundle:dev-server:portal'], cb);
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

gulp.task('bundle', function (callback) {
  runWebpackBundle(getWebpackConfig('all', deskpro.isProd), callback);
});

gulp.task('bundle:agent', function (callback) {
  runWebpackBundle(getWebpackConfig('agent', deskpro.isProd), callback);
});

gulp.task('bundle:portal', function (callback) {
  runWebpackBundle(getWebpackConfig('portal', deskpro.isProd), callback);
});

gulp.task('bundle:dev-server', function(callback) {
  startWebpackServer(getWebpackConfig('all', true, false));
});

gulp.task('bundle:dev-server:agent', function(callback) {
  startWebpackServer(getWebpackConfig('agent', true, false));
});

gulp.task('bundle:dev-server:portal', function(callback) {
  startWebpackServer(getWebpackConfig('portal', true, false));
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
  var config = {
    cache: true,
    entry: {},
    output: {
      path: path.join(__dirname, "build/"),
      publicPath: "/pub/build/",
      filename: "[name].js",
      sourceMapFilename: "[name].map"
    },
    resolve: {
      root: [
        path.join(__dirname, "src"),
        path.join(__dirname, "src/DeskPRO/Component"),
        path.join(__dirname, "built-tools"),
      ]
    },
    resolveLoader: {
      modulesDirectories: ["web_loaders", "web_modules", "node_loaders", "node_modules", "build-tools"]
    },
    devtool: "eval",
    module: {
      preLoaders: [
        {
          test: /\/Reducers\/.*\.js$/,
          include: [
            path.resolve(__dirname, "src/DeskPRO")
          ],
          loader: "reducer-index-loader"
        },
        {
          test: /\/.*?Bundle\/\w+App.js$/,
          include: [
            path.resolve(__dirname, "src/DeskPRO")
          ],
          loader: "reducer-app-loader"
        }
      ],
      loaders: [
        {
          test: /\.js$/,
          include: [
            path.resolve(__dirname, "src/DeskPRO")
          ]
        },
        {
          test: /\.(png|gif|jpg|jpeg|woff|woff2|ttf|eot|svg)(\?|$)/,
          loader: "file-loader?context=src&name=[path][name].[ext]",
          include: [
            path.resolve(__dirname, "src/DeskPRO"),
            path.resolve(__dirname, "node_modules/node-bourbon"),
            path.resolve(__dirname, "node_modules/node-neat"),
            path.resolve(__dirname, "node_modules/font-awesome"),
          ],
        },
        {
          test: /\.scss$/,
          include: [
            path.resolve(__dirname, "src/DeskPRO")
          ],
          loader: ExtractTextPlugin.extract("style-loader",
            "css-loader?sourceMap!sass-loader?sourceMap&outputStyle=expanded&" +
            "includePaths[]=" + (path.resolve(__dirname, "./bower_components")) + "&" +
            "includePaths[]=" + (path.resolve(__dirname, "./node_modules")),
            { "publicPath": "./" }
          )
        }
      ]
    },
    plugins: [
      new ExtractTextPlugin("[name].css"),
      new webpack.DefinePlugin({
        'process.env.NODE_ENV': JSON.stringify(process.env.NODE_ENV)
      })
    ]
  };

  if (mode == 'all' || mode == 'portal') {
    config.entry['DeskPRO_PortalBundle']       = ["./src/DeskPRO/Bundle/PortalBundle/DeskPRO_PortalBundle"];
    config.entry['DeskPRO_PortalBundle_style'] = ["./src/DeskPRO/Bundle/PortalBundle/Resources/style/portal-style.scss"];
  }
  if (mode == 'all' || mode == 'agent') {
    config.entry['DeskPRO_AgentBundle']       = ["./src/DeskPRO/Bundle/AgentBundle/DeskPRO_AgentBundle"];
    config.entry['DeskPRO_AgentBundle_style'] = ["./src/DeskPRO/Bundle/AgentBundle/Resources/style/agent-style.scss"];
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
      config.entry['DeskPRO_AgentBundle'].unshift('webpack/hot/only-dev-server');
      config.entry['DeskPRO_AgentBundle'].unshift('webpack-dev-server/client?http://localhost:9666');
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
 * @return {WebpackDevServer}
 */
function startWebpackServer(config)
{
  var compiler = webpack(config);
  return new WebpackDevServer(compiler, {
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
  }).listen(9666, "localhost", function(err) {
    if(err) throw new gutil.PluginError("webpack-dev-server", err);
    gutil.log("[webpack-dev-server]", "http://localhost:9666/");
    gutil.log("[webpack-dev-server]", "In your config.php, add this line: ");
    gutil.log("[webpack-dev-server]", "$DP_CONFIG['pub_asset_urls'] = array('pub/build' => 'http://localhost:9666/pub/build/');");
  });
}
