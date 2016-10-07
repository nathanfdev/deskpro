var gulp     = require('gulp'),
    finclude = require('gulp-file-include'),
    rjs      = require('gulp-requirejs'),
    using    = require('gulp-using'),
    gulpif   = require('gulp-if'),
    deskpro  = { util: {}, taskGen: {} };


//######################################################################################################################
//# Task Runners
//######################################################################################################################

gulp.task('default', ['loader-requirejs']);
gulp.task('prod', ['rjs-agent']);


//######################################################################################################################
//# Setup
//######################################################################################################################

deskpro.isWatching = false;

//------------------------------
// Task Methods
//------------------------------

deskpro.taskGen.loaderTpl = function(glob, target_dir) {

  if (!target_dir) {
    target_dir = './loader-build/';
  }

  return gulp.src(['./loader/requirejs-config.js', './loader/rjs-optimizer-config.js'])
    .pipe(gulpif(deskpro.isWatching, using({ prefix: '<< Build --' })))
    .pipe(finclude({
      prefix: '!!'
    }))
    .pipe(gulp.dest(target_dir))
    .pipe(gulpif(deskpro.isWatching, using({ prefix: '>> Wrote --' })));
};


//######################################################################################################################
//# Tasks
//######################################################################################################################

//------------------------------
// Loader
//------------------------------

gulp.task('loader-requirejs', function() {
  return deskpro.taskGen.loaderTpl(['./loader/requirejs-config.js', './loader/rjs-optimizer-config.js']);
});

//------------------------------
// RJS
//------------------------------

gulp.task('rjs-agent', ['loader-requirejs'], function() {
  var rjsConfig          = require('./loader-build/rjs-optimizer-config.js').getConfig();
  rjsConfig.out          = 'Agent/AgentLoad.min.js';
  rjsConfig.name         = 'AgentLoad';
  rjsConfig.paths.jquery = "empty:";

  return gulp.src('./app/Agent/AgentLoad.js')
    .pipe(using({ prefix: '<< Build --' }))
    .pipe(rjs(rjsConfig))
    .pipe(gulp.dest('./app-build/'));
});
