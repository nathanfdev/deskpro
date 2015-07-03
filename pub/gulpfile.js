var gulp         = require('gulp'),
    gutil        = require('gulp-util'),
    webpack      = require("webpack"),
    WebpackDevServer = require("webpack-dev-server"),
    sass         = require('gulp-sass'),
    sourcemaps   = require('gulp-sourcemaps'),
    uglify       = require('gulp-uglify'),
    notify       = require("gulp-notify"),
    watch        = require('gulp-watch'),
    cached       = require('gulp-cached'),
    del          = require('del'),
    runSeq       = require('run-sequence'),
    source       = require('vinyl-source-stream'),
    buffer       = require('vinyl-buffer'),
    prettyHrtime = require('pretty-hrtime'),
    path         = require("path"),
    ExtractTextPlugin = require("extract-text-webpack-plugin"),
    _            = require("lodash");

//######################################################################################################################
//# Util
//######################################################################################################################

var deskpro = {
  isProd:  false,
  isWatch: false
};

var bundleLogger = {
  times: {},
  start: function (filepath) {
    bundleLogger.times[filepath] = process.hrtime();
    gutil.log('> Bundle Begin ::', gutil.colors.green(filepath) + '...');
  },
  watch: function (bundleName) {
    gutil.log('Watching files required by', gutil.colors.yellow(bundleName));
  },
  end:   function (filepath) {
    var taskTime = process.hrtime(bundleLogger.times[filepath]);
    var prettyTime = prettyHrtime(taskTime);
    gutil.log('> Bundle Done  ::', gutil.colors.green(filepath), 'in', gutil.colors.magenta(prettyTime));
  }
};

var sassLogger = {
  times: {},
  start: function (filepath) {
    sassLogger.times[filepath] = process.hrtime();
    gutil.log('> SASS Begin ::', gutil.colors.green(filepath) + '...');
  },
  watch: function (filepath) {
    gutil.log('Watching files for', gutil.colors.yellow(filepath));
  },
  end:   function (filepath) {
    if (sassLogger.times[filepath]) {
      var taskTime = process.hrtime(sassLogger.times[filepath]);
      var prettyTime = prettyHrtime(taskTime);
      gutil.log('> SASS Done  ::', gutil.colors.green(filepath), 'in', gutil.colors.magenta(prettyTime));
      delete sassLogger.times[filepath];
    } else {
      gutil.log('> SASS Done ::', gutil.colors.green(filepath));
    }
  }
};

function handleErrors() {
  var args = Array.prototype.slice.call(arguments);

  notify.onError({
    title:   "Compile Error",
    message: "<%= error %>"
  }).apply(this, args);

  this.emit('end');
}

function getSassPipe(glob, noCache, dest) {

  if (noCache || !deskpro.isWatch) {
    noCache = true;
  }

  if (!deskpro.isWatch) {
    sassLogger.start(glob);
  }

  if (!dest) {
    dest = 'build';
  }

  var sassOpts = {
    includePaths: ["node_modules/"]
  };

  // All SCSS are in src/X/Y/Z/Resources/style/_.scss
  // so relative is:
  var mapSourceRoot = '../../../../../../src';

  if (noCache) {
    return gulp.src(glob)
      .pipe(sourcemaps.init())
      .pipe(sass(sassOpts))
      .on('end', function () {
        sassLogger.end(glob);
      })
      .pipe(sourcemaps.write('.', {includeContent: false, sourceRoot: mapSourceRoot}))
      .pipe(gulp.dest(dest));
  } else {
    return gulp.src(glob)
      .pipe(cached('watch', {optimizeMemory: true}))
      .pipe(sourcemaps.init())
      .pipe(sass(sassOpts))
      .on('end', function () {
        sassLogger.end(glob);
      })
      .pipe(sourcemaps.write('.', {includeContent: false, sourceRoot: mapSourceRoot}))
      .pipe(gulp.dest(dest));
  }
}

//######################################################################################################################
//# Task Runners
//######################################################################################################################

gulp.task('priv:start-prod', function () {
  deskpro.isProd = true;
});
gulp.task('priv:start-watch', function () {
  deskpro.isWatch = true;
});

gulp.task('clean', function (cb) {
  del(['./build'], cb);
});

gulp.task('default', ['clean'], function (cb) {
  runSeq(['bundle', 'sass'], cb);
});

gulp.task('prod', ['clean', 'priv:start-prod'], function (cb) {
  runSeq(['bundle', 'sass'], cb);
});

//######################################################################################################################
//# Bundler
//######################################################################################################################

gulp.task('clean:bundle', function (cb) {
  del(['./build/bundles'], cb);
});

function getWebpackConfig(isProd) {
  var config = {
    cache: true,
    entry: {
      DeskPRO_PortalBundle: "./src/DeskPRO/Bundle/PortalBundle/DeskPRO_PortalBundle",
      DeskPRO_AgentBundle: [
        "./src/DeskPRO/Bundle/AgentBundle/DeskPRO_AgentBundle"
      ]
    },
    output: {
      path: path.join(__dirname, "build/"),
      publicPath: "/pub/build/",
      filename: "[name].js",
      sourceMapFilename: "[name].map"
    },
    resolve: {
      root: path.join(__dirname, "src")
    },
    devtool: "inline-source-map",
    module: {
      loaders: [
        {
          test: /\.js$/,
          exclude: /(node_modules|bower_components)/,
          loader: "babel-loader?stage=0"
        },
        {
          test: /\.(png|gif|jpg|jpeg)$/,
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
      ]
    },
    plugins: [
      new ExtractTextPlugin("[name].css"),
      new webpack.DefinePlugin({
        'process.env.NODE_ENV': JSON.stringify(process.env.NODE_ENV)
      })
    ]
  };

  if (isProd) {
    config.devool = "source-map";
    config.plugins.push(new webpack.optimize.UglifyJsPlugin({
      exclude: [/(node_modules|bower_components)/]
    }))
  }

  return config;
}

gulp.task('bundle', ['clean:bundle'], function (callback) {
  var config = getWebpackConfig(deskpro.isProd);

  webpack(config, function(err, stats) {
    if(err) throw new gutil.PluginError("bundle", err);
    gutil.log("[bundle:portal]", stats.toString({
      colors: true
    }));
    callback();
  });
});

gulp.task('bundle:dev-server', ['clean:bundle'], function(callback) {
  var config = getWebpackConfig(deskpro.isProd);
  config.debug = true;
  config.devtool = "eval";

  config.output.publicPath = "http://localhost:9666/pub/build/";

  config.plugins.push(new webpack.HotModuleReplacementPlugin());
  config.plugins.push(new webpack.NoErrorsPlugin());

  // .js loader
  config.module.loaders[0].loaders = ['react-hot-loader', 'babel-loader?stage=0'];

  config.entry['DeskPRO_AgentBundle'].unshift('webpack/hot/only-dev-server');
  config.entry['DeskPRO_AgentBundle'].unshift('webpack-dev-server/client?http://localhost:9666');

  var compiler = webpack(config);
  new WebpackDevServer(compiler, {
    publicPath: "http://localhost:9666/pub/build/",
    hot: true,
    historyApiFallback: true,
    stats: {
      colors: true
    }
  }).listen(9666, "localhost", function(err) {
    if(err) throw new gutil.PluginError("webpack-dev-server", err);
    gutil.log("[webpack-dev-server]", "http://localhost:9666/");
    gutil.log("[webpack-dev-server]", "In your config.php, add this line: ");
    gutil.log("[webpack-dev-server]", "$DP_CONFIG['pub_asset_urls'] = array('pub/build' => 'http://localhost:9666/pub/build/');");
  });
});

//######################################################################################################################
//# SASS
//######################################################################################################################

gulp.task('sass:portal', function () {
  return getSassPipe(
    './src/DeskPRO/Bundle/PortalBundle/Resources/style/portal-style.scss',
    false,
    './build/DeskPRO/Bundle/PortalBundle/Resources/style'
  );
});

gulp.task('priv:sass:portal:nocache', function () {
  return getSassPipe(
    './src/DeskPRO/Bundle/PortalBundle/Resources/style/portal-style.scss',
    true,
    './build/DeskPRO/Bundle/PortalBundle/Resources/style'
  );
});

gulp.task('sass', function () {
  return getSassPipe('./src/**/*-style.scss');
});

//######################################################################################################################
//# Watcher
//######################################################################################################################

gulp.task('watch', ['clean', 'priv:start-watch'], function (cb) {
  runSeq([
    'bundle',
    'sass'
  ], function () {
    sassLogger.watch('./src/DeskPRO/Bundle/PortalBundle/Resources/style/*.scss');
    gulp.watch('./src/DeskPRO/Bundle/PortalBundle/Resources/style/*.scss', ['priv:sass:portal:nocache']);
    cb();
  });
});
