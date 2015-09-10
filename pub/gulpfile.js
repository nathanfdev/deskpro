var gulp                  = require('gulp'),
    gutil                 = require('gulp-util'),
    webpack               = require("webpack"),
    WebpackDevServer      = require("webpack-dev-server"),
    del                   = require('del'),
    runSeq                = require('run-sequence'),
    path                  = require("path"),
    ExtractTextPlugin     = require("extract-text-webpack-plugin"),
    fs                    = require("fs");

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
  // prefer to use one at a time, build speed is faster
  // and you can still just open up two terminal winodws if you need both
  console.log("Use:");
  console.log("\tdev:agent    -  For the agent interface");
  console.log("\tdev:portal   -  For the portal");
  cb();
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

function createReducers(reducers_path) {
  console.log("Parsing reducers in " + reducers_path);
  if(!fs.existsSync(reducers_path)) {
    return false;
  }
  var files = fs.readdirSync(reducers_path);
  var imports = '';
  var exports = '';
  var processed_files = [];
  for(var k in files) {
    var file = files[k];
    if(file == 'index.js' || !file.match(/\.js$/)) {
      continue;
    }

    processed_files.push(file);
    var store_name = file.substr(0, file.length - 3);
    imports+= "import " + store_name + " from './" + store_name + "';\n";
    exports+= store_name + ",";
  }

  console.log("[" + processed_files.join(", ") + "]");

  var index = imports + "export default {"+ exports +"};";
  fs.writeFileSync(path.join(reducers_path, "index.js"), index);
}

gulp.task('create-reducers', function(callback) {
  var bundles_path = path.join(__dirname, "src/DeskPRO/Bundle");
  var bundles = fs.readdirSync(bundles_path);
  for(var k in bundles) {
    var bundle = bundles[k];
    var modules_path = path.join(bundles_path, bundle, "Modules");
    if(fs.existsSync(modules_path)) {
      var modules = fs.readdirSync(modules_path);
      for(var l in modules) {
        createReducers(path.join(modules_path, modules[l], 'Reducers'));
      }
    }
  }

  return callback();
});

gulp.task('bundle', ['create-reducers'], function (callback) {
  runWebpackBundle(getWebpackConfig('all', deskpro.isProd), callback);
});

gulp.task('bundle:agent', function (callback) {
  runWebpackBundle(getWebpackConfig('agent', deskpro.isProd), callback);
});

gulp.task('bundle:portal', function (callback) {
  runWebpackBundle(getWebpackConfig('portal', deskpro.isProd), callback);
});

gulp.task('bundle:dev-server', ['create-reducers'], function(callback) {
  startWebpackServer(getWebpackConfig('all', true, false));
});

gulp.task('bundle:dev-server:agent', ['create-reducers'], function(callback) {
  startWebpackServer(getWebpackConfig('agent', true, false));
});

gulp.task('bundle:dev-server:portal', function(callback) {
  startWebpackServer(getWebpackConfig('portal', true, false));
});

gulp.task('build-test', function(callback) {
  var config = getWebpackConfig('agent', true, false);
  config.entry = path.join(__dirname, "src/DeskPRO/tests/runner");
  config.module.loader = [{
    test: /\.js$/,
    include: [
      path.resolve(__dirname, "src/DeskPRO")
    ],
    loader: "babel-loader?stage=0"
  }];

  webpack(config).run(function(err, stats) {
    if(err) throw new gutil.PluginError("webpack", err);
    gutil.log("[webpack]", stats.toString({
        // output options
    }));
    callback();
  });
});

gulp.task('test', ['build-test'], function(callback) {
  require('./build/main.js');
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
      path: path.join(__dirname, "build/"),
      publicPath: "/pub/build/",
      filename: "[name].js",
      sourceMapFilename: "[name].map"
    },
    resolve: {
      root: [
        path.join(__dirname, "src"),
        path.join(__dirname, "src/DeskPRO/Component")
      ],
      alias: {}
    },
    devtool: "source-map",
    module: {
      loaders: [
        {
          test: /\.js$/,
          include: [
            path.resolve(__dirname, "src/DeskPRO")
          ],
          loader: "babel-loader?stage=0"
        },
        {
          test: /\.(png|gif|jpg|jpeg|woff|woff2|ttf|eot|svg)(\?|$)/,
          loader: "file-loader?context=src&name=[path][name].[ext]"
        },
        {
          test: /\.scss$/,
          loader: ExtractTextPlugin.extract("style-loader",
            "css-loader?sourceMap!sass-loader?sourceMap&outputStyle=expanded&" +
            "includePaths[]=" + (path.resolve(__dirname, "./bower_components")) + "&" +
            "includePaths[]=" + (path.resolve(__dirname, "./node_modules")),
            { "publicPath": "./" }
          )
        }
      ],
      noParse: []
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
      config.entry['DeskPRO_AgentBundle'].unshift('webpack/hot/dev-server');
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
