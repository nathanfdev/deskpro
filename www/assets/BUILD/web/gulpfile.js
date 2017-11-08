var gulp         = require('gulp'),
  gutil        = require('gulp-util'),
  cache        = require('gulp-cached'),
  coffee       = require('gulp-coffee'),
  less         = require('gulp-less'),
  sass         = require('gulp-sass'),
  sourcemaps   = require('gulp-sourcemaps'),
  watch        = require('gulp-watch'),
  finclude     = require('gulp-file-include'),
  path         = require('path'),
  rename       = require('gulp-rename'),
  rjs          = require('gulp-requirejs'),
  plumber      = require('gulp-plumber'),
  debug        = require('gulp-debug'),
  using        = require('gulp-using'),
  gulpif       = require('gulp-if'),
  lazypipe     = require('lazypipe'),
  clean        = require('gulp-clean'),
  sassdoc      = require('sassdoc'),
  fs           = require('fs'),
  postcss      = require('gulp-postcss'),
  autoprefixer = require('autoprefixer'),
  cssnano      = require('cssnano'),
  comments     = require('postcss-discard-comments'),
  deskpro      = {util: {}, taskGen: {}};


//######################################################################################################################
//# Task Runners
//######################################################################################################################

gulp.task('default', ['less', 'sass', 'semantic-copy', 'less-dp-semantic', 'sassdoc', 'cpjs', 'loader'], function() {
  // hacking coffee here to run after all others
  // because something in the other tasks corrupts
  // the stream and causes coffee compile to fail
  // randomly sometimes
  return deskpro.taskGen.coffeeScript([
    './app/Admin*/**/*.coffee',
    './app/Agent*/**/*.coffee',
    './app/Reports*/**/*.coffee',
    './app/Interface*/**/*.coffee',
    './app/DeskPRO*/**/*.coffee'
  ]);
});
gulp.task('prod', ['coffee', 'less', 'sass', 'sassdoc', 'cpjs', 'semantic-copy-prod', 'less-dp-semantic', 'loader', 'rjs']);


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
  ['./app/Reports/**/*.coffee', ['coffee-reports']],
  ['./app/Interface/**/*.coffee', ['coffee-interface']],
  ['./app/DeskPRO/**/*.coffee', ['coffee-deskpro']],
  ['./app/**/Resources/style/*.less', ['less-app']],
  ['./app/**/Resources/style/*.scss', ['sass-app']],
  ['./app/**/*.js', ['cpjs-all']],
  ['./loader/*', ['loader-requirejs']],
  ['./stylesheets-less/semantic-ui/**', ['semantic-watch']],
  ['./stylesheets-less/admin/**', ['less-dp-semantic-app']]
];

//------------------------------
// Util
//------------------------------

deskpro.util.sourceMapRoot = function (file) {
  var path = file.path.replace(/\\/g, '/').replace(new RegExp(__dirname), '');
  var rel = path.replace(/^.*?\/web\/app\-build\//, '');

  // Counts slashes the relative path to decide how many levels up we need to go
  var depth = (rel.match(/(\/|\\)/g) || []).length - 1;

  // The sub-dir that the file is under
  var ns = rel.split('/')[0];

  var up = "";
  for (var i = 0; i < depth; i++) up += "../";

  return up + 'app/' + ns;
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
    .pipe(gulpif(deskpro.isWatching, cache('watch', {optimizeMemory: true})))
    .pipe(gulpif(deskpro.isWatching, plumber()))
    .pipe(sourcemaps.init())
    .pipe(gulpif(deskpro.isWatching, using({prefix: '<< Build --'})))
    .pipe(coffee().on('error', deskpro.util.coffeeError))
    .pipe(sourcemaps.write({includeContent: false, sourceRoot: deskpro.util.sourceMapRoot}))
    .pipe(gulp.dest(target_dir))
    .pipe(gulpif(deskpro.isWatching, using({prefix: '>> Wrote --'})));
};

deskpro.taskGen.lessCss = function(glob, target_dir) {

  if (!target_dir) {
    target_dir = './app-build/';
  }

  return gulp.src(glob)
    .pipe(gulpif(deskpro.isWatching, cache('watch', {optimizeMemory: true})))
    .pipe(gulpif(deskpro.isWatching, plumber()))
    .pipe(sourcemaps.init())
    .pipe(gulpif(deskpro.isWatching, using({prefix: '<< Build --'})))
    .pipe(less())
    .pipe(sourcemaps.write({includeContent: false, sourceRoot: deskpro.util.sourceMapRoot}))
    .pipe(gulp.dest(target_dir))
    .pipe(gulpif(deskpro.isWatching, using({prefix: '>> Wrote --'})));
};

deskpro.taskGen.sassCss = function(glob, target_dir) {

  if (!target_dir) {
    target_dir = './app-build/';
  }

  return gulp.src(glob)
    .pipe(gulpif(deskpro.isWatching, cache('watch', {optimizeMemory: true})))
    .pipe(gulpif(deskpro.isWatching, plumber()))
    .pipe(sourcemaps.init())
    .pipe(gulpif(deskpro.isWatching, using({prefix: '<< Build --'})))
    .pipe(sass())
    .pipe(sourcemaps.write('/', {includeContent: false, sourceRoot: deskpro.util.sourceMapRoot}))
    .pipe(gulp.dest(target_dir))
    .pipe(gulpif(deskpro.isWatching, using({prefix: '>> Wrote --'})));
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
    './app/Reports*/**/*.coffee',
    './app/Interface*/**/*.coffee',
    './app/DeskPRO*/**/*.coffee'
  ]);
});

gulp.task('dirty-coffee', function() {
  return deskpro.taskGen.coffeeScript([
    './app/Admin*/**/*.coffee',
    './app/Agent*/**/*.coffee',
    './app/Reports*/**/*.coffee',
    './app/Interface*/**/*.coffee',
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
    .pipe(gulpif(deskpro.isWatching, cache('watch', {optimizeMemory: true})))
    .pipe(gulpif(deskpro.isWatching, plumber()))
    .pipe(gulp.dest(target_dir))
    .pipe(gulpif(deskpro.isWatching, using({prefix: '>> Wrote --'})));
});

gulp.task('cpjs', ['clean', 'cpjs-clipboard'], function() {

  var glob       = './app/**/*.js';
  var target_dir = './app-build/';

  return gulp.src(glob)
    .pipe(gulpif(deskpro.isWatching, cache('watch', {optimizeMemory: true})))
    .pipe(gulpif(deskpro.isWatching, plumber()))
    .pipe(gulp.dest(target_dir))
    .pipe(gulpif(deskpro.isWatching, using({prefix: '>> Wrote --'})));
});

// it's unable to add to config.assets.php, failed on yui-compressor filter
gulp.task('cpjs-clipboard', ['clean'], function () {
  gulp.src('./node_modules/clipboard/dist/clipboard.min.js').pipe(gulp.dest('./app-build/'));
});

//------------------------------
// Less
//------------------------------

gulp.task('less-app', function () {
  deskpro.taskGen.copyThemeConfig();
  return deskpro.taskGen.lessCss('./app/**/Resources/style/*-style.less');
});

gulp.task('less', ['clean'], function () {
  deskpro.taskGen.copyThemeConfig();
  return deskpro.taskGen.lessCss('./app/**/Resources/style/*-style.less');
});

gulp.task('less-dp-semantic-app', function () {
  deskpro.taskGen.copyThemeConfig();
  deskpro.taskGen.lessCss('./stylesheets-less/admin/*.less', './app-build/Admin/Resources/style');
  return deskpro.taskGen.lessCss('./stylesheets-less/admin/*.less', './app-build/Interface/Resources/style');
});

gulp.task('less-dp-semantic', ['clean'], function () {
  deskpro.taskGen.copyThemeConfig();
  deskpro.taskGen.lessCss('./stylesheets-less/admin/*.less', './app-build/Admin/Resources/style');
  return deskpro.taskGen.lessCss('./stylesheets-less/admin/*.less', './app-build/Interface/Resources/style');
});

//------------------------------
// Semantic UI
//------------------------------

gulp.task('semantic', function () {
  deskpro.taskGen.copyThemeConfig();
  return deskpro.taskGen.semantic();
});

gulp.task('semantic-watch', function () {
  deskpro.taskGen.semantic('./app-build/Admin/Resources/style/');
  return deskpro.taskGen.semantic('./app-build/Interface/Resources/style/');
});

gulp.task('semantic-copy', ['clean'], function () {
  gulp.src('./stylesheets-less/semantic-ui/semantic.css')
    .pipe(postcss([
      autoprefixer(),
      comments({})
    ]))
    .pipe(gulp.dest('./app-build/Admin/Resources/style/'))
    .pipe(gulp.dest('./app-build/Interface/Resources/style/'));
  gulp.src('./stylesheets-less/semantic-ui/semantic.css.map')
    .pipe(gulp.dest('./app-build/Admin/Resources/style/'))
    .pipe(gulp.dest('./app-build/Interface/Resources/style/'));
});

gulp.task('semantic-copy-prod', ['clean'], function () {
  gulp.src('./stylesheets-less/semantic-ui/semantic.css')
    .pipe(postcss([
      autoprefixer(),
      comments({}),
      cssnano()
    ]))
    .pipe(gulp.dest('./app-build/Admin/Resources/style/'))
    .pipe(gulp.dest('./app-build/Interface/Resources/style/'));
  gulp.src('./stylesheets-less/semantic-ui/semantic.css.map')
    .pipe(gulp.dest('./app-build/Admin/Resources/style/'))
    .pipe(gulp.dest('./app-build/Interface/Resources/style/'));
});

deskpro.taskGen.semantic = function(target_dir) {
  if (!target_dir) {
    target_dir = './stylesheets-less/semantic-ui/';
  }

  return gulp.src('./node_modules/semantic-ui-less/semantic.less')
    .pipe(sourcemaps.init())
    .pipe(gulpif(deskpro.isWatching, using({prefix: '<< Build --'})))
    .pipe(less())
    .pipe(sourcemaps.write('/', {includeContent: false, sourceRoot: './node_modules/semantic-ui-less/'}))
    .pipe(gulp.dest(target_dir))
    .pipe(gulpif(deskpro.isWatching, using({prefix: '>> Wrote --'})));
};

deskpro.taskGen.copyThemeConfig = function() {
  // Semantic looks for a hardcoded theme.config file, so use this hack.
  return gulp.src('./stylesheets-less/semantic-ui/theme.config')
    .pipe(gulp.dest('./node_modules/semantic-ui-less/'));
};

//------------------------------
// Sass
//------------------------------

gulp.task('sass-app', function () {
  return deskpro.taskGen.sassCss('./app/**/Resources/style/*-style.scss');
});

gulp.task('sass', ['clean'], function () {
  return deskpro.taskGen.sassCss('./app/**/Resources/style/*-style.scss');
});

gulp.task('sassdoc', function () {

  /**
   * Transform sassdoc raw output into grouped variables, fixing variable names from - to _
   * @param sassdoc_items
   * @returns {{}}
   */
  function transform(sassdoc_items) {
    var groups = {};
    for (var i = 0; i < sassdoc_items.length; i++) {
      var group = sassdoc_items[i]['group'][0];
      if (!groups.hasOwnProperty(group)) {
        groups[group] = [];
      }

      var name = sassdoc_items[i]['context']['name'];

      var type;
      if (sassdoc_items[i].hasOwnProperty('type')) {
        type = sassdoc_items[i]['type'];
      } else {
        console.error('Error: ' + name + ' has no @type');
      }
      groups[group].push({
        type: type,
        name: name,
        default_value: sassdoc_items[i]['context']['value']
      });
    }

    return groups;
  }

  sassdoc.parse('../pub/src/DeskPRO/Bundle/PortalBundle/Resources/style/vars.scss').then(function(items) {
    fs.writeFileSync('./sassdoc/vars.json', JSON.stringify(transform(items), null, '\t'));
  });
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

var rjsLoadFiles = [
  './app/Admin/AdminLoad.js',
  './app/Admin/Cloud/CloudAdminLoad.js',
  './app/AdminStart/AdminStartLoad.js',
  './app/AdminUpdateWatcher/AdminUpdateWatcherLoad.js',
  './app/Reports/ReportsLoad.js',
  './app/Interface/InterfaceLoad.js',
  './app/Agent/AgentLoad.js'
];

function addRjsTask(rjsBundle) {
  var bundleName = rjsBundle.replace(/^.*\/(.*?)\.js$/, '$1');
  var taskName   = 'rjs-' + bundleName.replace(/Load$/, '').toLowerCase();

  var target;
  switch (bundleName) {
    case 'AdminLoad':
      target = 'Admin/AdminLoad.min.js';
      break;
    case 'CloudAdminLoad':
      target = 'Admin/Cloud/CloudAdminLoad.min.js';
      break;
    case 'AdminUpdateWatcherLoad':
      target = 'AdminUpdateWatcher/AdminUpdateWatcherLoad.min.js';
      break;
    case 'AdminStartLoad':
      target = 'AdminStart/AdminStartLoad.min.js';
      break;
    case 'ReportsLoad':
      target = 'Reports/ReportsLoad.min.js';
      break;
    case 'InterfaceLoad':
      target = 'Interface/InterfaceLoad.min.js';
      break;
    case 'AgentLoad':
      target = 'Agent/AgentLoad.min.js';
      break;
  }

  gulp.task(taskName, ['coffee', 'loader'], function () {
    var rjsConfig = require('./loader-build/rjs-optimizer-config.js').getConfig();
    rjsConfig.out  =  target;
    rjsConfig.name = bundleName;

    if (bundleName == 'AgentLoad') {
      rjsConfig.paths.jquery = "empty:";
    }

    return gulp.src(rjsBundle)
      .pipe(using({prefix: '<< Build --'}))
      .pipe(rjs(rjsConfig))
      .pipe(gulp.dest('./app-build/'));
  });

  return taskName;
}

var rjsTasks = [];

for (var x = 0; x < rjsLoadFiles.length; x++) {
  rjsTasks.push(addRjsTask(rjsLoadFiles[x]));
}

gulp.task('rjs', rjsTasks, function (cb) {
  cb();
});

//------------------------------
// Watcher
//------------------------------

gulp.task('precache', function () {
  deskpro.isWatching = true;
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

gulp.task('clean', function () {
  return gulp.src(['./app-build', './loader-build'])
    .pipe(clean());
});
