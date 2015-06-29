var gulp         = require('gulp'),
    gutil        = require('gulp-util'),
    webpack      = require("webpack"),
    babelify     = require('babelify'),
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

gulp.task('bundle', ['clean:bundle'], function (callback) {
  var config = {
    cache: true,
    entry: {
      DeskPRO_PortalBundle: "./src/DeskPRO/Bundle/PortalBundle/DeskPRO_PortalBundle",
      DeskPRO_AgentBundle: "./src/DeskPRO/Bundle/AgentBundle/DeskPRO_AgentBundle"
    },
    output: {
      path: path.join(__dirname, "build/bundles"),
      publicPath: "build/bundles",
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
          loader: "babel-loader"
        },
        {
          test: /\.scss$/,
          loader: ExtractTextPlugin.extract("style-loader",
            "css-loader?sourceMap!sass-loader?sourceMap&outputStyle=expanded&" +
            "includePaths[]=" + (path.resolve(__dirname, "./bower_components")) + "&" +
            "includePaths[]=" + (path.resolve(__dirname, "./node_modules"))
          )
        }
      ]
    },
    plugins: [
      new ExtractTextPlugin("[name].css")
    ]
  };

  if (deskpro.isProd) {
    config.devool = "source-map";
    config.plugins.push(new webpack.optimize.UglifyJsPlugin({
      exclude: [/(node_modules|bower_components)/]
    }))
  }

  webpack(config, function(err, stats) {
    if(err) throw new gutil.PluginError("bundle", err);
    gutil.log("[bundle:portal]", stats.toString({
      colors: true
    }));
    callback();
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