var gulp         = require('gulp'),
    gutil        = require('gulp-util'),
    browserify   = require('browserify'),
    watchify     = require('watchify'),
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

function getBundler(entryFile) {
  if (deskpro.isWatch) {
    return watchify(browserify(entryFile, _.assign({}, watchify.args, {
      paths:        [
        './src'
      ],
      basedir:      './',
      cache:        {},
      packageCache: {},
      fullPaths:    true
    })));
  } else {
    return browserify(entryFile, _.assign({}, watchify.args, {
      paths:     [
        './src'
      ],
      basedir:   './',
      fullPaths: false
    }));
  }
}

function getBundlePipe(entryFile) {
  var b = getBundler(entryFile)
    .transform(babelify);

  var getBundle = function () {

    bundleLogger.start(entryFile);

    var p = b.bundle()
      .on('error', handleErrors)
      .pipe(source('PortalBundle.js'))
      .pipe(buffer())
      .pipe(sourcemaps.init({loadMaps: true}));

    if (deskpro.isProd) {
      p.pipe(uglify());
    }

    var dest = entryFile.split('/');
    dest.pop();
    dest = dest.join('/')

    p = p.pipe(sourcemaps.write('.', {includeContent: false, sourceRoot: '/pub/src-build/' + dest}))
      .pipe(gulp.dest('src-build/' + dest));

    p.on('end', function () {
      bundleLogger.end(entryFile);
    });

    return p;
  };

  if (deskpro.isWatch) {
    b.on('update', getBundle);
    bundleLogger.watch(entryFile);
  }

  return getBundle();
}

function getSassPipe(glob, noCache, dest) {

  if (noCache || !deskpro.isWatch) {
    noCache = true;
  }

  if (!deskpro.isWatch) {
    sassLogger.start(glob);
  }

  if (!dest) {
    dest = 'src-build';
  }

  if (noCache) {
    return gulp.src(glob)
      .pipe(sourcemaps.init())
      .pipe(sass())
      .on('end', function () {
        sassLogger.end(glob);
      })
      .pipe(sourcemaps.write('.', {includeContent: false, sourceRoot: '/pub/src-build'}))
      .pipe(gulp.dest(dest));
  } else {
    return gulp.src(glob)
      .pipe(cached('watch', {optimizeMemory: true}))
      .pipe(sourcemaps.init())
      .pipe(sass())
      .on('end', function () {
        sassLogger.end(glob);
      })
      .pipe(sourcemaps.write('.', {includeContent: false, sourceRoot: '/pub/src-build'}))
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
  del(['./src-build'], cb);
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

gulp.task('bundle:portal', function () {
  return getBundlePipe('DeskPRO/Bundle/PortalBundle/PortalBundle.js');
});

gulp.task('bundle', function (cb) {
  runSeq(['bundle:portal'], cb);
});

//######################################################################################################################
//# SASS
//######################################################################################################################

gulp.task('sass:portal', function () {
  return getSassPipe(
    './src/DeskPRO/Bundle/PortalBundle/Resources/style/portal-style.scss',
    false,
    './src-build/DeskPRO/Bundle/PortalBundle/Resources/style'
  );
});

gulp.task('priv:sass:portal:nocache', function () {
  return getSassPipe(
    './src/DeskPRO/Bundle/PortalBundle/Resources/style/portal-style.scss',
    true,
    './src-build/DeskPRO/Bundle/PortalBundle/Resources/style'
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
    'bundle:portal',
    'sass'
  ], function () {
    sassLogger.watch('./src/DeskPRO/Bundle/PortalBundle/Resources/style/*.scss');
    gulp.watch('./src/DeskPRO/Bundle/PortalBundle/Resources/style/*.scss', ['priv:sass:portal:nocache']);
    cb();
  });
});