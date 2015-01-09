var gulp       = require('gulp'),
    gutil      = require('gulp-util'),
    cache      = require('gulp-cached'),
    coffee     = require('gulp-coffee'),
    less       = require('gulp-less'),
    sass       = require('gulp-sass'),
    sourcemaps = require('gulp-sourcemaps'),
    watch      = require('gulp-watch'),
    finclude   = require('gulp-file-include'),
    path       = require('path'),
    rename     = require('gulp-rename'),
    rjs        = require('gulp-r'),
    plumber    = require('gulp-plumber'),
    debug      = require('gulp-debug'),
    using      = require('gulp-using'),
    gulpif     = require('gulp-if'),
    lazypipe   = require('lazypipe'),
    del        = require('del'),
    deskpro    = {util: {}, taskGen: {}};


//######################################################################################################################
//# Task Runners
//######################################################################################################################

gulp.task('default', ['coffee', 'less', 'sass', 'cpjs', 'loader']);
gulp.task('prod', ['coffee', 'less', 'sass', 'cpjs', 'loader', 'rjs', 'rjs-agent']);


//######################################################################################################################
//# Setup
//######################################################################################################################

deskpro.isWatching = false;

//------------------------------
// Config
//------------------------------

deskpro.watches = [
  ['./app/Admin*/**/*.coffee', ['coffee-admin']],
  ['./app/Agent*/**/*.coffee', ['coffee-agent']],
  ['./app/Interface*/**/*.coffee', ['coffee-interface']],
  ['./app/Reports/**/*.coffee', ['coffee-reports']],
  ['./app/DeskPRO/**/*.coffee', ['coffee-deskpro']],
  ['./app/**/Resources/style/*.less', ['less-app']],
  ['./app/Admin/Resources/style/*.scss', ['sass-app']],
  ['./app/AdminStart/Resources/style/*.scss', ['sass-app']],
  ['./app/AdminUpgrade/Resources/style/*.scss', ['sass-app']],
  ['./app/Agent/Resources/style/*.scss', ['sass-app']],
  ['./app/DeskPRO/Resources/style/*.scss', ['sass-app']],
  ['./app/Portal/Resources/style/*.scss', ['sass-app']],
  ['./app/Interface/Resources/style/*.scss', ['sass-app-full']],
  ['./app/**/*.js', ['cpjs-all']],
  ['./loader/*', ['loader-requirejs']]
];

//------------------------------
// Util
//------------------------------

deskpro.util.sourceMapRoot = function (file) {
  var path = file.path.replace(/\\/g, '/')
  var rel = path.replace(/^.*?\/web\/app\-build\//, '');

  // Counts slashes the relative path to decide how many levels up we need to go
  var depth = (rel.match(/(\/|\\)/g) || []).length + 1;

  // The sub-dir that the file is under
  var ns = rel.split('/')[0];

  var up = "";
  for (var i = 0; i < depth; i++) up += "../";

  return up + 'web/app/' + ns;
};

deskpro.util.coffeeError = function (e) {
  gutil.log(gutil.colors.white.bgRed('Coffee Error:'), e.name, ' ', e.message);

  var shortName = (e.filename || "").replace(/.*?\/web\/app\//, 'web/app/');
  gutil.log('              ' + shortName, ' Line: ' + e.location.first_line);
  gutil.beep();

  // workaround for gulp not returning error status
  if (!deskpro.isWatching) {
    throw "CoffeeScript Error";
  }
};

//------------------------------
// Task Methods
//------------------------------

deskpro.taskGen.coffeeScript = function(glob, target_dir) {

  if (!target_dir) {
    target_dir = './app-build/';
  }

  return gulp.src(glob)
    .pipe(cache('watch', {optimizeMemory: true}))
    .pipe(gulpif(deskpro.isWatching, plumber()))
    .pipe(sourcemaps.init())
    .pipe(gulpif(deskpro.isWatching, using({prefix: '<< Build --'})))
    .pipe(coffee().on('error', deskpro.util.coffeeError))
    .pipe(sourcemaps.write('/', {includeContent: false, sourceRoot: deskpro.util.sourceMapRoot}))
    .pipe(gulp.dest(target_dir))
    .pipe(gulpif(deskpro.isWatching, using({prefix: '>> Wrote --'})));
};

deskpro.taskGen.lessCss = function(glob, target_dir) {

  if (!target_dir) {
    target_dir = './app-build/';
  }

  return gulp.src(glob)
    .pipe(cache('watch', {optimizeMemory: true}))
    .pipe(gulpif(deskpro.isWatching, plumber()))
    .pipe(sourcemaps.init())
    .pipe(gulpif(deskpro.isWatching, using({prefix: '<< Build --'})))
    .pipe(less())
    .pipe(sourcemaps.write('/', {includeContent: false, sourceRoot: deskpro.util.sourceMapRoot}))
    .pipe(gulp.dest(target_dir))
    .pipe(gulpif(deskpro.isWatching, using({prefix: '>> Wrote --'})));
};

deskpro.taskGen.sassCss = function(glob, target_dir, no_cache) {

  if (!target_dir) {
    target_dir = './app-build/';
  }

  if (no_cache) {
    return gulp.src(glob)
      .pipe(sourcemaps.init())
      .pipe(gulpif(deskpro.isWatching, using({prefix: '<< Build --'})))
      .pipe(sass({sourceComments: false}))
      .pipe(sourcemaps.write('/', {includeContent: false, sourceRoot: deskpro.util.sourceMapRoot}))
      .pipe(gulp.dest(target_dir))
      .pipe(gulpif(deskpro.isWatching, using({prefix: '>> Wrote --'})));
  } else {
    return gulp.src(glob)
      .pipe(cache('watch', {optimizeMemory: true}))
      .pipe(gulpif(deskpro.isWatching, plumber()))
      .pipe(sourcemaps.init())
      .pipe(gulpif(deskpro.isWatching, using({prefix: '<< Build --'})))
      .pipe(sass({sourceComments: false}))
      .pipe(sourcemaps.write('/', {includeContent: false, sourceRoot: deskpro.util.sourceMapRoot}))
      .pipe(gulp.dest(target_dir))
      .pipe(gulpif(deskpro.isWatching, using({prefix: '>> Wrote --'})));
  }
};

deskpro.taskGen.loaderTpl = function(glob, target_dir) {

  if (!target_dir) {
    target_dir = './loader-build/';
  }

  return gulp.src(['./loader/requirejs-config.js', './loader/rjs-optimizer-config.js'])
    .pipe(gulpif(deskpro.isWatching, using({prefix: '<< Build --'})))
    .pipe(finclude({
      prefix: '!!'
    }))
    .pipe(gulp.dest(target_dir))
    .pipe(gulpif(deskpro.isWatching, using({prefix: '>> Wrote --'})));
};


//######################################################################################################################
//# Tasks
//######################################################################################################################

//------------------------------
// Coffeescript
//------------------------------

gulp.task('coffee-admin', function () {
  return deskpro.taskGen.coffeeScript('./app/Admin*/**/*.coffee');
});

gulp.task('coffee-agent', function () {
  return deskpro.taskGen.coffeeScript('./app/Agent*/**/*.coffee');
});

gulp.task('coffee-interface', function () {
  return deskpro.taskGen.coffeeScript('./app/Interface*/**/*.coffee');
});

gulp.task('coffee-reports', function () {
  return deskpro.taskGen.coffeeScript('./app/Reports*/**/*.coffee');
});

gulp.task('coffee-deskpro', function () {
  return deskpro.taskGen.coffeeScript('./app/DeskPRO*/**/*.coffee');
});

gulp.task('coffee', ['clean'], function() {
  return deskpro.taskGen.coffeeScript([
    './app/Admin*/**/*.coffee',
    './app/Agent*/**/*.coffee',
    './app/Interface*/**/*.coffee',
    './app/Reports*/**/*.coffee',
    './app/DeskPRO*/**/*.coffee'
  ]);
});

//------------------------------
// Copy JS
//------------------------------

gulp.task('cpjs-all', function() {

  var glob       = './app/**/*.js';
  var target_dir = './app-build/';

  return gulp.src(glob)
    .pipe(cache('watch', {optimizeMemory: true}))
    .pipe(gulpif(deskpro.isWatching, plumber()))
    .pipe(gulp.dest(target_dir))
    .pipe(gulpif(deskpro.isWatching, using({prefix: '>> Wrote --'})));
});

gulp.task('cpjs', ['clean'], function() {

  var glob       = './app/**/*.js';
  var target_dir = './app-build/';

  return gulp.src(glob)
    .pipe(cache('watch', {optimizeMemory: true}))
    .pipe(gulpif(deskpro.isWatching, plumber()))
    .pipe(gulp.dest(target_dir))
    .pipe(gulpif(deskpro.isWatching, using({prefix: '>> Wrote --'})));
});

//------------------------------
// Less
//------------------------------

gulp.task('less-app', function () {
  return deskpro.taskGen.lessCss('./app/**/Resources/style/*-style.less');
});

gulp.task('less', ['clean'], function () {
  return deskpro.taskGen.lessCss('./app/**/Resources/style/*-style.less');
});

//------------------------------
// Sass
//------------------------------

gulp.task('sass-app', function () {
  return deskpro.taskGen.sassCss('./app/**/Resources/style/*-style.scss');
});

gulp.task('sass-app-full', function () {
  return deskpro.taskGen.sassCss('./app/**/Resources/style/*-style.scss', null, true);
});

gulp.task('sass', ['clean'], function () {
  return deskpro.taskGen.sassCss('./app/**/Resources/style/*-style.scss');
});

//------------------------------
// Loader
//------------------------------

gulp.task('loader-requirejs', function () {
  return deskpro.taskGen.loaderTpl(['./loader/requirejs-config.js', './loader/rjs-optimizer-config.js']);
});

gulp.task('loader', ['clean'], function () {
  return deskpro.taskGen.loaderTpl(['./loader/requirejs-config.js', './loader/rjs-optimizer-config.js']);
});

//------------------------------
// RJS
//------------------------------

gulp.task('rjs', ['coffee', 'loader'], function () {
  var loadFiles = [
    './app/Admin/AdminLoad.js',
    './app/Admin/Cloud/CloudAdminLoad.js',
    './app/AdminUpgrade/AdminUpgradeLoad.js',
    './app/AdminStart/AdminStartLoad.js',
    './app/Reports/ReportsLoad.js'
  ];

  var rjsConfig = require('./loader-build/rjs-optimizer-config.js').getConfig();

  return gulp.src(loadFiles, {base: './'})
    .pipe(using({prefix: '<< Build --'}))
    .pipe(rjs(rjsConfig))
    .pipe(rename(function (path) {
      switch (path.basename.replace(/\.js$/, '')) {
        case 'AdminLoad':
          path.dirname = 'Admin';
          break;
        case 'AgentLoad':
          path.dirname = 'Agent';
          break;
        case 'CloudAdminLoad':
          path.dirname = 'Admin/Cloud';
          break;
        case 'AdminUpgradeLoad':
          path.dirname = 'AdminUpgrade';
          break;
        case 'AdminStartLoad':
          path.dirname = 'AdminStart';
          break;
        case 'ReportsLoad':
          path.dirname = 'Reports';
          break;
      }

      if (path.extname != '.map') {
        path.extname = '.min.js';
      }
    }))
    .pipe(gulp.dest('./app-build/'))
    .pipe(gulpif(deskpro.isWatching, using({prefix: '>> Wrote --'})));
});

gulp.task('rjs-agent', ['coffee', 'loader'], function () {
  var loadFiles = [
    './app/Agent/AgentLoad.js',
  ];

  // Hack for agent interface
  // jquery is included independantly and is version 1.7
  var rjsConfig = require('./loader-build/rjs-optimizer-config.js').getConfig();
  rjsConfig.paths.jquery = "empty:";

  return gulp.src(loadFiles, {base: './'})
    .pipe(using({prefix: '<< Build --'}))
    .pipe(rjs(rjsConfig))
    .pipe(rename(function (path) {
      switch (path.basename.replace(/\.js$/, '')) {
        case 'AgentLoad':
          path.dirname = 'Agent';
          break;
      }

      if (path.extname != '.map') {
        path.extname = '.min.js';
      }
    }))
    .pipe(gulp.dest('./app-build/'))
    .pipe(gulpif(deskpro.isWatching, using({prefix: '>> Wrote --'})));
});

//------------------------------
// Watcher
//------------------------------

gulp.task('precache', function () {
  var globs = [];
  deskpro.watches.forEach(function (w) {
    globs.push(w[0]);
  });

  return gulp.src(globs)
    .pipe(cache('watch', {optimizeMemory: true}));
});

gulp.task('watch', ['precache'], function () {
  deskpro.isWatching = true;
  deskpro.watches.forEach(function (w) {
    gulp.watch(w[0], w[1]);
  });
});

//------------------------------
// Clean
//------------------------------

gulp.task('clean', function (cb) {
  del(['./app-build', './loader-build'], cb);
});